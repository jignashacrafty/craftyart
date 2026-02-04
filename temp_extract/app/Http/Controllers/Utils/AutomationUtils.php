<?php

namespace App\Http\Controllers\Utils;

use App\Enums\ConfigType;
use App\Http\Controllers\EmailController;
use App\Models\Automation\EmailTemplate;
use App\Models\PromoCode;
use App\Models\Automation\WhatsappTemplate;
use App\Services\WhatsAppService;
use AWS\CRT\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class AutomationUtils
{
    public static function formatNewPlanData($subPlan, string $currency = 'INR'): array
    {
        $isInr = $currency === "INR";
        $column = $isInr ? 'inr_offer_price' : 'usd_offer_price';
        $actualColumn = $isInr ? 'inr_price' : 'usd_price';
        $currencySymbol = $isInr ? "₹" : "$";

        $price = round($subPlan->plan_details[$column], 2);
        $actualPrice = round($subPlan->plan_details[$actualColumn], 2);

        $discount = 0;
        $offerMsg = null;
        $hasOffer = 0;

        if ($price < $actualPrice) {
            $discount = (int)((($actualPrice - $price) / $actualPrice) * 100);
            $offerMsg = "Best Value ($discount% off)";
            $hasOffer = 1;
        }

        return [
            'id' => $subPlan->id,
            'package_name' => "",
            'desc' => $subPlan->plan->description,
            'validity' => $subPlan->duration->duration + $subPlan->plan_details['additional_duration'],
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'actual_price' => $currencySymbol . $actualPrice,
            'offer_price' => $currencySymbol . $price,
            'price' => $price,
            'has_offer' => $hasOffer,
            'offer_msg' => $offerMsg,
            'discount' => $discount,
        ];
    }

    public static function formatOldPlanData($plan, string $currency = 'INR'): array
    {
        $isInr = $currency === "INR";
        $column = $isInr ? 'price' : 'price_dollar';
        $actualColumn = $isInr ? 'actual_price' : 'actual_price_dollar';
        $currencySymbol = $isInr ? "₹" : "$";

        $price = round($plan->$column, 2);
        $actualPrice = round($plan->$actualColumn, 2);

        $discount = 0;
        $offerMsg = null;
        $hasOffer = 0;

        if ($price < $actualPrice) {
            $discount = (int)((($actualPrice - $price) / $actualPrice) * 100);
            $offerMsg = "Best Value ($discount% off)";
            $hasOffer = 1;
        }

        return [
            'id' => $plan->id,
            'package_name' => $plan->package_name,
            'desc' => $plan->desc,
            'validity' => $plan->validity,
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'actual_price' => $currencySymbol . $actualPrice,
            'offer_price' => $currencySymbol . $price,
            'price' => $currencySymbol . $price,
            'has_offer' => $hasOffer,
            'offer_msg' => $offerMsg,
            'discount' => $discount,
        ];
    }

    /**
     * Common method to prepare WhatsApp parameters
     */
    public static function prepareWhatsAppParams($user, $commonData, $templateConfig, $type,$promoCodes): array
    {
        $name = $user->name;
        $promoCode = "";
        $promoDiscount = "";

        if ($type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value || $type == ConfigType::RECENT_EXPIRE_AUTOMATION) {
            return [
                $name,
                $user->email
            ];
        }

        if ($templateConfig['promo_code'] ?? false) {
            $promoCodeId = $templateConfig['promo_code'];
            if (isset($promoCodes[$promoCodeId])) {
                $promo = $promoCodes[$promoCodeId];
                $promoCode = $promo->promo_code;
                $promoDiscount = $promo->disc . "%";
            }
        }

        if (in_array($commonData['planType'], ['template', 'video'])) {
            $firstTemplate = $commonData['data']['templates'][0] ?? null;
            if (!$firstTemplate) {
                return ['success' => false, 'message' => "No design found in plan"];
            }

            return [
                $name,
                $commonData['data']['amount'],
                $promoCode,
                $promoDiscount
            ];

        } elseif ($commonData['planType'] === 'new_sub') {
            return [
                $name,
                $commonData['data']['offer_price'],
                $commonData['data']['actual_price'],
                $promoCode,
                $promoDiscount
            ];

        } elseif (in_array($commonData['planType'], ['old_sub', 'offer'])) {
            if ($commonData['planType'] === "offer") {
                return [
                    $name,
                    $commonData['data']['offer_price'],
                    $commonData['data']['actual_price'],
                ];
            } else {
                return [
                    $name,
                    $commonData['data']['offer_price'],
                    $commonData['data']['actual_price'],
                    $promoCode,
                    $promoDiscount
                ];
            }
        }

        return ['success' => false, 'message' => "Invalid plan type for WhatsApp parameters"];
    }



    /**
     * Common method to prepare WhatsApp button
     */
    public static function prepareWhatsAppButton(array $commonData, string $type): array
    {
        // For account creation or expire automation, no buttons required
        if (
            $type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value ||
            $type == ConfigType::RECENT_EXPIRE_AUTOMATION
        ) {
            return [];
        }

        // If you have a dynamic WhatsApp button link
        $buttonLink = $commonData['waBtnLink'] ?? null;
        if (!$buttonLink) {
            return [];
        }

        /**
         * Interakt expects:
         * "buttonValues": {
         *   "1": ["https://example.com"]
         * }
         */
        return [
            "1" => [$buttonLink]
        ];
    }

//    public static function prepareWhatsAppButton($commonData, $type): array
//    {
//        if ($type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value || $type == ConfigType::RECENT_EXPIRE_AUTOMATION) {
//            return [];
//        }
//
//        return [
//            [
//                "type" => "button",
//                "sub_type" => "url",
//                "index" => 0,
//                "parameters" => [
//                    [
//                        "type" => "text",
//                        "text" => $commonData['waBtnLink']
//                    ]
//                ],
//            ]
//        ];
//    }

    /**
     * Common method to send email from config
     */
    public static function sendEmailFromConfig($emailConfig, $user, $commonData, $emailTemplates, $type, $promoCodes): array
    {
        // $emailTemplateId = $emailConfig['template'] ?? null;
        $templateConfig = null;
        if ($type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value) {
            $templateConfig = $emailConfig;
        } else if ($commonData['planType'] === 'offer') {
            $templateConfig = $emailConfig['offer'] ?? null;
        } elseif (in_array($commonData['planType'], ['new_sub', 'old_sub'])) {
            $templateConfig = $emailConfig['subscription'] ?? null;
        } elseif (in_array($commonData['planType'], ['template', 'video'])) {
            $templateConfig = $emailConfig['templates'] ?? null;
        }
        if (!$templateConfig || !$templateConfig['template']) {
            return ['success' => false, 'message' => "WhatsApp template not defined for plan type: {$commonData['planType']}"];
        }
        // if (!$emailTemplateId) {
        //     return ['success' => false, 'message' => "Email template not defined in config"];
        // }
        // Use pre-fetched template
        $emailTemplate = $emailTemplates[$templateConfig['template']] ?? null;
        if ($emailTemplate === null) {
            return ['success' => false, 'message' => "Email Template not found"];
        }
        // Add promo object to email data if available
        $promoObject = null;
        if ($templateConfig['promo_code'] ?? false) {
            $promoCodeId = $templateConfig['promo_code'];
            if (isset($promoCodes[$promoCodeId])) {
                $promo = $promoCodes[$promoCodeId];
                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => $promo->disc
                ];
            }
        }
        $emailData = [
            'userData' => $commonData['userData'],
            'type' => $commonData['type'],
            'data' => $commonData['data'],
            'link' => $commonData['link'],
            'promo' => $promoObject
        ];
        // $name = str_replace('.', '/', $emailTemplate->email_template);
        // $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";
        // if (!file_exists($viewPath))
        //     return ['success' => false, 'message' => "Email Template not found"];
        // $htmlBody = View::file($viewPath, [
        //     'data' => $emailData
        // ])->render();
        $htmlBody = View::make($emailTemplate->email_template, [
            'data' => $emailData
        ])->render();
        $result = AutomationUtils::sendEmail($user->email, $emailConfig['subject'] ?? '', $htmlBody);
        if (str_contains($result, "successfully")) {
            return ['success' => true, 'message' => 'Email Sent Successfully'];
        }
        return ['success' => false, 'message' => $result];
    }

    /**
     * Common method to send WhatsApp from config
     */
    public static function sendWhatsAppFromConfig($wpConfig, $user, $commonData, $contactNumber, $type, $whatsappTemplates,$promoCodes)
    {
        if (!$contactNumber) {
            return ['success' => false, 'message' => "Contact number not found"];
        }

        // Determine which template config to use based on plan type
        $templateConfig = null;
        if ($type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value) {
            $templateConfig = $wpConfig;
        } else if ($commonData['planType'] === 'offer') {
            $templateConfig = $wpConfig['offer'] ?? null;
        } elseif (in_array($commonData['planType'], ['new_sub', 'old_sub'])) {
            $templateConfig = $wpConfig['subscription'] ?? null;
        } elseif (in_array($commonData['planType'], ['template', 'video'])) {
            $templateConfig = $wpConfig['templates'] ?? null;
        }

        if (!$templateConfig || !$templateConfig['template']) {
            return ['success' => false, 'message' => "WhatsApp template not defined for plan type: {$commonData['planType']}"];
        }

        // Use pre-fetched template
        $whatsappTemplate = $whatsappTemplates[$templateConfig['template']] ?? null;
        if (!$whatsappTemplate) {
            return ['success' => false, 'message' => "WhatsApp Template not found"];
        }

        $templateParams = self::prepareWhatsAppParams(user: $user, commonData: $commonData, templateConfig: $templateConfig, type: $type,promoCodes: $promoCodes);

        $count = (int)$whatsappTemplate->template_params_count;

        if ($count != count($templateParams)) {
            return ['success' => false, 'message' => "WhatsApp Template parameter count mismatch"];
        }

        // Prepare dynamic button with payment link
        $dynamicButton = self::prepareWhatsAppButton(commonData: $commonData, type: $type);

//
//        $result = WhatsAppService::sendTemplateMessage(
//            $whatsappTemplate->campaign_name,
//            $user->name,
//            $contactNumber,
//            $templateParams,
//            $dynamicButton
//        );
//
//        return self::handleWhatsAppResponse($result, $templateParams);

        \Illuminate\Support\Facades\Log::info("Whatsapp ".$whatsappTemplate->campaign_name);
        \Illuminate\Support\Facades\Log::info("Whatsapp ".$user->name);
        \Illuminate\Support\Facades\Log::info("Whatsapp ".$contactNumber);
        \Illuminate\Support\Facades\Log::info("Whatsapp params ".json_encode($templateParams));


        return [
            "success" => true,
            "message" => "Generated",
            "campaign_name" => $whatsappTemplate->campaign_name,
            "name" => $user->name,
            "contact_number" => $contactNumber,
            "template_params" => $templateParams,
            "dynamic_button" => $dynamicButton
        ];
    }

    /**
     * Common method to handle WhatsApp response
     */
    public static function handleWhatsAppResponse($result, $templateParams = []): array
    {
        if (is_array($result)) {
            $status = $result['status'] ?? false;
            $message = $result['message'] ?? 'Something went wrong';
        } else {
            $status = $result->status ?? false;
            $message = $status ? 'Message Sent Successfully' : ($result->message ?? 'Something went wrong');
        }

        return [
            'success' => $status,
            'message' => $message,
        ];
    }

    /**
     * Common method to handle automation for job
     */
    public static function handleAutomationForJob($frequencyConfig, $user, $commonData, $contactNumber, $allTemplateData, $type): array
    {
        $results = [];

        // Handle Email Automation (if enabled)

        if ($frequencyConfig['email']['enable'] ?? false) {
            $emailConfig = $frequencyConfig['email']['config'] ?? [];

            $results['email'] = self::sendEmailFromConfig(
                emailConfig: $emailConfig,
                user: $user,
                commonData: $commonData,
                emailTemplates: $allTemplateData['emailTemplates'],
                type: $type,
                promoCodes: $allTemplateData['promoCodes']
            );
        }

        // Handle WhatsApp Automation (if enabled)
        if ($frequencyConfig['wp']['enable'] ?? false) {
            $wpConfig = $frequencyConfig['wp']['config'] ?? [];
            $results['whatsapp'] = self::sendWhatsAppFromConfig(
                wpConfig: $wpConfig,
                user: $user,
                commonData: $commonData,
                contactNumber: $contactNumber,
                type: $type,
                whatsappTemplates: $allTemplateData['whatsappTemplates'],
                promoCodes: $allTemplateData['promoCodes']
            );
        }

        return $results;
    }

    /**
     * Common method to pre-fetch templates and promo codes
     */
    public static function preFetchAllTemplatesAndPromoCodes(array $configData, string $configType): array
    {
        $allTemplateIds = [
            'email' => [],
            'whatsapp' => []
        ];
        $allPromoCodeIds = [];
        // Normalize structure for offer purchase type (since it's a single object, not an array)
        $configs = ($configType == ConfigType::ACCOUNT_CREATE_AUTOMATION->value)
            ? [$configData]
            : $configData;
        foreach ($configs as $frequency) {
            if (!empty($frequency['email']['enable']) && !empty($frequency['email']['config'])) {
                $emailConfig = $frequency['email']['config'];
                if (
                    $configType === ConfigType::ACCOUNT_CREATE_AUTOMATION->value ||
                    $configType === ConfigType::RECENT_EXPIRE_AUTOMATION->value
                ) {
                    // Single WhatsApp template & promo code
                    if (!empty($emailConfig['template'])) {
                        $allTemplateIds['email'][] = $emailConfig['template'];
                    }
                    if (!empty($emailConfig['promo_code'])) {
                        $allPromoCodeIds[] = $emailConfig['promo_code'];
                    }
                } else {
                    // Multiple types (offer, subscription, templates)
                    foreach (['offer', 'subscription', 'templates'] as $type) {
                        if (!empty($emailConfig[$type]['template'])) {
                            $allTemplateIds['email'][] = $emailConfig[$type]['template'];
                        }
                        if (!empty($emailConfig[$type]['promo_code'])) {
                            $allPromoCodeIds[] = $emailConfig[$type]['promo_code'];
                        }
                    }
                }
            }
            if (!empty($frequency['wp']['enable']) && !empty($frequency['wp']['config'])) {
                $wpConfig = $frequency['wp']['config'];
                // Different logic depending on config type
                if (
                    $configType === ConfigType::ACCOUNT_CREATE_AUTOMATION->value ||
                    $configType === ConfigType::RECENT_EXPIRE_AUTOMATION->value
                ) {
                    // Single WhatsApp template & promo code
                    if (!empty($wpConfig['template'])) {
                        $allTemplateIds['whatsapp'][] = $wpConfig['template'];
                    }
                    if (!empty($wpConfig['promo_code'])) {
                        $allPromoCodeIds[] = $wpConfig['promo_code'];
                    }
                } else {
                    // Multiple types (offer, subscription, templates)
                    foreach (['offer', 'subscription', 'templates'] as $type) {
                        if (!empty($wpConfig[$type]['template'])) {
                            $allTemplateIds['whatsapp'][] = $wpConfig[$type]['template'];
                        }
                        if (!empty($wpConfig[$type]['promo_code'])) {
                            $allPromoCodeIds[] = $wpConfig[$type]['promo_code'];
                        }
                    }
                }
            }
        }
        // --- REMOVE DUPLICATES ---
        $allTemplateIds['email'] = array_unique($allTemplateIds['email']);
        $allTemplateIds['whatsapp'] = array_unique($allTemplateIds['whatsapp']);
        $allPromoCodeIds = array_unique($allPromoCodeIds);
        // --- FETCH TEMPLATES & PROMO CODES ---
        $emailTemplates = EmailTemplate::whereIn('id', $allTemplateIds['email'])->get()->keyBy('id');
        $whatsappTemplates = WhatsappTemplate::whereIn('id', $allTemplateIds['whatsapp'])->get()->keyBy('id');
        $promoCodes = PromoCode::whereIn('id', $allPromoCodeIds)->get()->keyBy('id');
        return [
            'emailTemplates' => $emailTemplates,
            'whatsappTemplates' => $whatsappTemplates,
            'promoCodes' => $promoCodes
        ];
    }

    public static function sendEmail($to, $subject, $body): string
    {
        try {
            Mail::send([], [], function ($message) use ($to, $subject, $body) {
                $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                    ->to($to)
                    ->replyTo(env("MAIL_FROM_ADDRESS"), 'Reply Support')
                    ->subject($subject)
                    ->setBody($body, 'text/html');

                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });
            return "Email sent successfully";
        } catch (\Throwable $e) {
            return "Failed: " . $e->getMessage();
        }
    }
}
