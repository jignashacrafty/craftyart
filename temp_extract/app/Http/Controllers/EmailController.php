<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Api\ResponseHandler;
use App\Http\Controllers\Api\ResponseInterface;
use App\Models\Automation\Config;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\WhatsappTemplate;
use App\Models\Design;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Subscription;
use App\Models\UserData;
use App\Services\WhatsAppService;
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
                    $sub = Subscription::getSubs(ids: [$planId], currency: $currency, status: null);
                    if (!$sub)
                        return;
                    $planData = $sub[0];
                    $response['type'] = "plan";
                    $response['data'] = $planData;

                    if (in_array($planId, [24, 25, 26, 2])) {
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
                    EmailController::sendMail($user, $configValue, $orderType, $applyPromo, $response);
                }

                if ($configValue['wp']['enable'] ?? false) {
                    EmailController::sendWa($user, $configValue, $orderType, $applyPromo, $response);
                }
            }
        }
    }

    private static function sendMail(UserData $userData, $configValue, $orderType, $applyPromo, $response): void
    {
        $emailConfig = $configValue['email']['config'] ?? [];

        $templateConfig = $emailConfig[$orderType] ?? null;

        $emailTemplateId = $templateConfig['template'] ?? null;
        if (!$emailTemplateId)
            return;
        $emailTemplate = EmailTemplate::find($emailTemplateId);
        if (!$emailTemplate)
            return;

        $name = str_replace('.', '/', $emailTemplate->email_template);
        $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";

        if (!file_exists($viewPath))
            return;

        if (($emailConfig['promo_code'] ?? false) && $applyPromo) {
            $promoCodeId = $emailConfig['promo_code'];
            $promo = PromoCode::find($promoCodeId);
            if ($promo) {
                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => $promo->disc
                ];
                $response['promo'] = $promoObject;
            }
        }

        $htmlBody = View::file($viewPath, [
            'data' => $response
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
    }

    private static function sendWa(UserData $user, $configValue, $orderType, $applyPromo, $response): void
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
            return;
        $waTemplate = WhatsappTemplate::find($templateConfig['template']);
        if (!$waTemplate)
            return;

        if ($orderType === 'offer') {
            $waParams = [$user->name, $response['data']['offer_price'], $response['data']['actual_price']];
        } elseif ($orderType === 'subscription') {
            $waParams = [$user->name, $response['data']['offer_price'], $response['data']['actual_price'], $promoCode, $disc];
        } else {
            $waParams = [$user->name, $response['data']['amount'], $promoCode, $disc];
        }

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

        EmailController::sendWhatsappTemplateMessage($user, $waParams, $ctaButtons, $waTemplate->campaign_name);
    }

    public static function sendWhatsappTemplateMessage(UserData $userData, array $params, array $ctaBtns, $campName): array
    {
        return WhatsAppService::sendTemplateMessage(
            campaignName: $campName,
            userName: $userData->name,
            mobile: $userData->contact_no,
            templateParams: $params,
            ctaButtons: $ctaBtns
        );
    }
}