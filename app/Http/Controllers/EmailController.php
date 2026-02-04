<?php

namespace App\Http\Controllers;

use App\Enums\ConfigType;
use App\Enums\UserRole;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ResponseHandler;
use App\Http\Controllers\Api\ResponseInterface;
use App\Http\Controllers\Automation\EmailTemplateController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\Config;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\WhatsappTemplate;
use App\Models\Design;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PurchaseHistory;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserData;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use View;

class EmailController extends ApiController
{




    public static function sendUserCreation(UserData $userData, $password): array
    {

        try {

            $config = Config::whereName('account_create_automation')->first();
            if ($config && $config->value && !empty($config->value)) {

                $configValue = $config->value;
                if ($configValue['email']['enable'] ?? false) {
                    $emailConfig = $configValue['email']['config'] ?? [];

                    $emailTemplateId = $emailConfig['template'] ?? null;

                    if (!$emailTemplateId) {
                        return ['success' => false, 'message' => "Email template not defined in config"];
                    }

                    $emailTemplate = EmailTemplate::find($emailTemplateId);

                    if (!$emailTemplate) {
                        return ['success' => false, 'message' => "Email Template not found"];
                    }

                    $emailData = [
                        'userData' => [
                            'name' => $userData->name,
                            'email' => $userData->email,
                            'password' => $password,
                        ],
                    ];

                    $name = str_replace('.', '/', $emailTemplate->email_template);
                    $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";

                    if (!file_exists($viewPath)) {
                        return ['success' => false, 'message' => "Email Template not found"];
                    }

                    $htmlBody = View::file($viewPath, [
                        'data' => $emailData
                    ])->render();

                    $subject = $emailConfig['subject'] ?? '';

                    Mail::mailer('otp')->send([], [], function ($message) use ($userData, $subject, $htmlBody) {
                        $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_OTP_FROM_NAME"))
                            ->to($userData->email)
                            ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
                            ->subject($subject)
                            ->setBody($htmlBody, 'text/html');

                        $message->getHeaders()->addTextHeader('Precedence', 'bulk');
                    });

                    if (count(Mail::failures()) > 0) {
                        return ResponseHandler::sendRealResponse(new ResponseInterface(500, false, 'Email sending failed.', ['failures' => Mail::failures()]));
                    }

                    return ResponseHandler::sendRealResponse(new ResponseInterface(
                        200,
                        true,
                        'Email sent successfully'
                    ));
                }
            }

            return ['success' => false, 'message' => "Email Template not found"];

        } catch (\Throwable $e) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(
                500,
                false,
                'Failed to send email.',
                ['error' => $e->getMessage()]
            ));
        }
    }

    public static function overrideOldData(Order $order): void
    {
        Log::info("Override Started");
        $orders = Order::select('status','id')->where('id',"!=",$order->id)->whereUserId($order->user_id)->whereType($order->type)->whereStatus('failed')->get();
        foreach ($orders as $order2){
            $order2->update(['status'=>'override']);
        }
    }

    public static function sendPurchaseDropoutEmail(Order $order): void
    {

        if (!in_array($order->type, ['old_sub', 'template'], true))
            return;

        $user = UserData::where('uid', $order->user_id)->first();
        if (!$user)
            return;

        $planId = $order->plan_id;
        $currency = $order->currency;

        $config = Config::whereName('checkout_drop_automation')->first();
        if ($config && $config->value && !empty($config->value)) {
            $configValue = collect($config->value)->firstWhere('day', 0);
            if (!$configValue)
                return;

            if (($configValue['email']['enable'] ?? false) || ($configValue['wp']['enable'] ?? false)) {

                $applyPromo = true;
                if ($order->type === 'old_sub') {
//                    $sub = Subscription::getSubs(ids: [$planId], currency: $currency, status: null);
                    $sub = Subscription::whereId($planId)->first();
                    if (!$sub)
                        return;
                    $planData = AutomationUtils::formatOldPlanData($sub, $currency);
                    $response['type'] = "plan";
                    $response['data'] = $planData;
                    Log::info("dsadsasasd".$planId);
                    if (in_array($planId, [23, 24, 25, 26, 29, 30, 33, 34, 35])) {
                        $orderType = "offer";
                        $applyPromo = false;
                        $response['link'] = "https://www.craftyartapp.com/offer/payment/$order->crafty_id";
                    } else {
                        $orderType = "subscription";
                        $response['link'] = "https://www.craftyartapp.com/payment/$order->crafty_id";
                    }

                } else if ($order->type === 'template') {
                    $orderType = "templates";
                    $response = Design::getTempDatas($order);
                } else {
                    return;
                }

                $response['userData'] = [
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                if ($configValue['email']['enable'] ?? false) {
                    $isSent = EmailController::sendMail(order: $order,userData: $user, configValue: $configValue, orderType: $orderType, applyPromo: $applyPromo, response: $response);
                    if ($isSent) $order->increment('email_template_count');
                }

                if ($configValue['wp']['enable'] ?? false) {
                    $isSent = EmailController::sendWa($user, $configValue, $orderType, $applyPromo, $response, order: $order);
                    if ($isSent) $order->increment('whatsapp_template_count');
                }
            }
        }
    }

    public static function sendInstantTemplatePurchaseMessage($id): void
    {
        Log::info("Instant Template Purchase Message Start");
        $purData = PurchaseHistory::whereId($id)->first();
        if(!$purData || !$purData->userData) {
            Log::info("Purchase Data and Userdata not Found");
          return;
        }
        Log::info("Purchase Data and Userdata Found");
        $config = Config::whereName(ConfigType::INSTANT_TEMPLATE_PURCHASE->name)->first();
        if ($config && $config->value && !empty($config->value)) {
            Log::info("Config Value Found");
            $configValue = $config->value;
            if (($configValue['email']['enable'] ?? false) || ($configValue['wp']['enable'] ?? false)) {

                $response['userData'] = [
                    'name' => $purData->userData->name,
                    'email' => $purData->userData->email,
                ];

                $promoId = $configValue['promo'] ?? 0;
                $promoData = PromoCode::whereId($promoId)->first();
                if(!$promoData) return;

                $response['promo'] = [
                    "code" => $promoData->promo_code,
                    "disc" => "$promoData->disc%",
                    "expiry_date" => $promoData->expiry_date ? Carbon::parse($promoData->expiry_date)->format('j F Y') : null,
                    "discount_price" => "0"
                ];

                $response['link'] = "https://www.craftyartapp.com/templates/invitation";

                if ($configValue['email']['enable'] ?? false) {
                        Log::info("Email Enable");
                    $isSent = EmailController::sendEmailTemplateMessage($purData->userData->email,$configValue['email']['config']['subject'],$configValue['email']['config']['template'],$response);
                    if ($isSent) $purData->increment('email_sent');
                }
                if ($configValue['wp']['enable'] ?? false) {

                    $wpConfig = $configValue['wp']['config'] ?? [];
                    if (!$wpConfig || !$wpConfig['template'])
                        return;
                    $waTemplate = WhatsappTemplate::whereId($wpConfig['template'])->first();
                    if (!$waTemplate)
                        return;

                    $waParams = self::resolveWhatsappTemplateParams(
                        keys: $waTemplate->template_params,
                        response: $response,
                        orderType: ""
                    );

                    $purData->userData->contact_no = $purData->contact_no;

                    $ctaButtons[] = [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => 0,
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => str_replace("https://www.craftyartapp.com/", "", $response['link'])
                            ]
                        ],
                    ];
                    $result = EmailController::sendWhatsappTemplateMessage($purData->userData,$waParams,$ctaButtons,$waTemplate->campaign_name,$waTemplate->media_url == 1,$waTemplate->url ?? "");
                    if (is_array($result)) {
                        $isSent = $result['success'] ?? false;
                    } else {
                        $isSent = $result->success ?? false;
                    }
                    if ($isSent) $purData->increment('wp_sent');
                }
            }
        }
    }

    private static function sendMail(Order $order,UserData $userData, $configValue, $orderType, $applyPromo, $response): bool
    {
        $emailConfig = $configValue['email']['config'] ?? [];

        $templateConfig = $emailConfig[$orderType] ?? null;

        $emailTemplateId = $templateConfig['template'] ?? null;
        if (!$emailTemplateId)
            return false;
        $emailTemplate = EmailTemplate::find($emailTemplateId);
        if (!$emailTemplate)
            return false;

        $viewPath = $emailTemplate->email_template;

//        $name = str_replace('.', '/', $emailTemplate->email_template);
//        $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";
//        if (!file_exists($viewPath))
//            return false;
        if (($templateConfig['promo_code'] ?? false) && $applyPromo) {
            $promoCodeId = $templateConfig['promo_code'];
            $promo = PromoCode::find($promoCodeId);
            if ($promo) {
                $expiry_date = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;

                $rawPrice = 0;
                if ($orderType == 'templates') {
                    if (!empty($response['amount'])) {
                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $response['amount']);
                    }
                } else {
                    if (!empty($response['data']['price'])) {
                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $response['data']['price']);
                    }
                }
                Log::info("Order Type " . $orderType . " Raw Price " . $rawPrice);

                $discountPrice = 0;
                if ($rawPrice > 0) {
                    $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
                    $currency = $order->currency;
                    $isInr = $currency == "INR";
                    $discountedValue = $isInr ? round($discountedValue) : number_format((float)$discountedValue, 2, '.', '');
                    $currencySymbol = $isInr ? "₹" : "$";
                    $discountPrice = $currencySymbol . $discountedValue;
                }

                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => "$promo->disc%",
                    'expiry_date' => $expiry_date,
                    'discount_price' => $discountPrice
                ];
                $response['promo'] = $promoObject;
            }
        }

        return EmailController::sendEmailTemplateMessage($userData->email,$templateConfig['subject'] ?? '',$templateConfig['template'] ?? null,$response);

//        try {
//            $htmlBody = View::make($viewPath, [
//                'data' => $response
//            ])->render();
//            $subject = $templateConfig['subject'] ?? '';
//
////            Mail::mailer('otp')->send([], [], function ($message) use ($userData, $subject, $htmlBody) {
////                $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_OTP_FROM_NAME"))
////                    ->to($userData->email)
////                    ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
////                    ->subject($subject)
////                    ->setBody($htmlBody, 'text/html');
////
////                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
////            });
//            Mail::send([], [], function ($message) use ($userData, $subject, $htmlBody) {
//                $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_OTP_FROM_NAME"))
//                    ->to($userData->email)
//                    ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
//                    ->subject($subject)
//                    ->setBody($htmlBody, 'text/html');
//
//                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
//            });
//        } catch (\Exception $e) {
//            Log::info("Error Message " . $e->getMessage());
//            return false;
//        }
//        return true;
    }



    private static function sendWa(UserData $user, $configValue, $orderType, $applyPromo, $response, Order $order): bool
    {
        $wpConfig = $configValue['wp']['config'] ?? [];
        $templateConfig = $wpConfig[$orderType] ?? null;

        if (($templateConfig['promo_code'] ?? false) && $applyPromo) {
            $promoCodeId = $templateConfig['promo_code'];
            $promo = PromoCode::find($promoCodeId);
            if ($promo) {
                $expiry_date = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;

                $rawPrice = 0;
                if (in_array($order->type, ['template', 'video'])) {
                    if (!empty($response['amount'])) {
                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $response['amount']);
                    }
                } else {
                    if (!empty($response['data']['price'])) {
                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $response['data']['price']);
                    }
                }
                Log::info("Order Type " . $order->type . " Raw Price " . $rawPrice);

                $discountPrice = 0;
                if ($rawPrice > 0) {
                    $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
                    $currency = $order->currency;
                    $isInr = $currency == "INR";
                    $discountedValue = $isInr ? round($discountedValue) : number_format((float)$discountedValue, 2, '.', '');
                    $currencySymbol = $isInr ? "₹" : "$";
                    $discountPrice = $currencySymbol . $discountedValue;
                }

                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => "$promo->disc%",
                    'expiry_date' => $expiry_date,
                    'discount_price' => $discountPrice
                ];
                $response['promo'] = $promoObject;
            }
        }

        if (!$templateConfig || !$templateConfig['template'])
            return false;
        $waTemplate = WhatsappTemplate::whereId($templateConfig['template'])->first();
        if (!$waTemplate)
            return false;


        $response['userData'] = [
            "name" => $user->name,
            "email" => $user->email
        ];

        $waParams = self::resolveWhatsappTemplateParams(
            keys: $waTemplate->template_params,
            response: $response,
            orderType: $order->type
        );

        $ctaButtons[] = [
            "type" => "button",
            "sub_type" => "url",
            "index" => 0,
            "parameters" => [
                [
                    "type" => "text",
                    "text" => str_replace("https://www.craftyartapp.com/", "", $response['link'])
                ]
            ],
        ];


//        $result = EmailController::sendWhatsappTemplateMessage($user,$order->contact_no, $waParams, $ctaButtons, $waTemplate->campaign_name,$waTemplate->media_url == 1,$waTemplate->url ?? "");
//        Log::info("Whatsapp Params ".json_encode($result));
//        if (is_array($result)) {
//            $status = $result['success'] ?? false;
//        } else {
//            $status = $result->success ?? false;
//        }
//        return $status;
         return true;
    }

    /*private static function sendWa(UserData $user, $configValue, $orderType, $applyPromo, $response): bool
    {
        $wpConfig = $configValue['wp']['config'] ?? [];

        $promoCode = "";
        $disc = "";

        $templateConfig = $wpConfig[$orderType] ?? null;

        if (($templateConfig['promo_code'] ?? false) && $applyPromo) {
            $promoCodeId = $templateConfig['promo_code'];
            $promo = PromoCode::find($promoCodeId);
            if ($promo) {
                $promoCode = $promo->promo_code;
                $disc = "$promo->disc%";
                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => "$promo->disc%"
                ];
                $response['promo'] = $promoObject;
            }
        }

        if (!$templateConfig || !$templateConfig['template'])
            return false;
        $waTemplate = WhatsappTemplate::whereId($templateConfig['template'])->first();
        if (!$waTemplate)
            return false;

        if ($orderType === 'offer') {
            $waParams = [$user->name, $response['data']['offer_price'], $response['data']['actual_price']];
        } elseif ($orderType === 'subscription') {
            $waParams = [$user->name, $response['data']['offer_price'], $response['data']['actual_price'], $promoCode, $disc];
        } else {
            $waParams = [$user->name, $response['data']['amount'], $promoCode, $disc];
        }



        $response['userData'] = [
            "name"  => $user->name,
            "email" => $user->email
        ];

        $waParams = self::resolveWhatsappTemplateParams(
            keys: $waTemplate->template_params,
            response: $response,
        );

        $ctaButtons[] = [
            "type" => "button",
            "sub_type" => "url",
            "index" => 0,
            "parameters" => [
                [
                    "type" => "text",
                    "text" => str_replace("https://www.craftyartapp.com/", "", $response['link'])
                ]
            ],
        ];

        Log::info("Campaign Name ".$waTemplate->campaign_name." Name ".$user->name." mobile "." TemplateParams ".json_encode($waParams)." Media ".$waTemplate->url);

//        EmailController::sendWhatsappTemplateMessage($user, $waParams, $ctaButtons, $waTemplate->campaign_name,$waTemplate->media_url == 1,$waTemplate->url ?? "");
    }*/

//    private static function resolveWhatsappTemplateParams(array $keys, array $response, $orderType): array
//    {
//        $resolved = [];
//        foreach ($keys as $key) {
//            if(str_contains($key,"."))
//               [$group, $field] = explode('.', $key);
//            else
//                $group = $key;
//            $resolved[] = match ($group) {
//                'UserData' => $response['userData'][$field] ?? '',
//                'PlanData' => (in_array($orderType, ['template', 'video']) ? (
//                ($field === "actual_price" || $field === "offer_price")
//                    ? ($response['data']['amount'] ?? '')
//                    : ($field === "package_name"
//                    ? ($response['data']['templates'][0]['title'] ?? '')
//                    : ($response['data'][$field] ?? '')))
//                    : ($response['data'][$field] ?? '')
//                ),
//                'PromoData' => $response['promo'][$field] ?? ''
//            };
//        }
//        return $resolved;
//    }

    private static function resolveWhatsappTemplateParams(array $keys, array $response, $orderType): array
    {
        $resolved = [];
        foreach ($keys as $key) {
            if(str_contains($key,".")) {
                [$group, $field] = explode('.', $key);
            } else {
                $group = $key;
                $field = null;
            }

            $resolved[] = match ($group) {
                'UserData' => $response['userData'][$field] ?? '',
                'PlanData' => (in_array($orderType, ['template', 'video']) ? (
                ($field === "actual_price" || $field === "offer_price")
                    ? ($response['data']['amount'] ?? '')
                    : ($field === "package_name"
                    ? ($response['data']['templates'][0]['title'] ?? '')
                    : ($response['data'][$field] ?? '')))
                    : ($response['data'][$field] ?? '')
                ),
                'PromoData' => $response['promo'][$field] ?? '',
                'link' => $response['link'] ?? ''
            };
        }
        return $resolved;
    }

    public static function sendEmailTemplateMessage($email, $subject , $emailTemplateId,$response): bool
    {
        try {

            $emailTemplate = EmailTemplate::whereId($emailTemplateId)->first();
            if(!$emailTemplate) return false;

           $viewPath =  $emailTemplate->email_template;
//        $name = str_replace('.', '/', $emailTemplate->email_template);
//        $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";
//        if (!file_exists($viewPath))
//            return false;

            $htmlBody = View::make($viewPath, [
                'data' => $response
            ])->render();

            Mail::mailer()->send([], [], function ($message) use ($email, $subject, $htmlBody) {
                $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_OTP_FROM_NAME"))
                    ->to($email)
                    ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
                    ->subject($subject)
                    ->setBody($htmlBody, 'text/html');

                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });
            return true;
        } catch (\Exception $e){
            Log::info("Error to Send Email ".$e->getMessage());
            return false;
        }
    }


    public static function sendWhatsappTemplateMessage(UserData $userData, array $params, array $ctaBtns, $campName, bool $media = false,
                                                       string $mediaUrl = ""): array
    {
        return WhatsAppService::sendTemplateMessage(
            campaignName: $campName,
            userName: $userData->name,
            mobile: $userData->contact_no,
            templateParams: $params,
            ctaButtons: $ctaBtns, media: $media, mediaUrl: $mediaUrl
        );
    }
}