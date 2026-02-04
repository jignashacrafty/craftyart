<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{

//    public static function sendMessageFromInterakt(string $campaignName,
//                                                   string $userName,
//                                                   string $mobile,
//                                                   array $templateParams,
//                                                   array $ctaButtons = [],
//                                                   bool $media = false,
//                                                   string $mediaUrl = "")
//    {
//        $url = "https://api.interakt.ai/v1/public/message/";
//
//    }
//    public static function sendTemplateMessage(
//        string $campaignName,
//        string $userName,
//        string $mobile,
//        array $templateParams,
//        array $ctaButtons = [],
//        bool $media = false,
//        string $mediaUrl = ""
//    ): array {
//        $url = "https://api.interakt.ai/v1/public/message/";
//
//        // Extract country code and phone number
//        preg_match('/^\+?(\d{1,4})(\d{7,})$/', preg_replace('/\s+/', '', $mobile), $matches);
//        if (!$matches) {
//            return ['success' => false, 'message' => "Invalid mobile number format"];
//        }
//
//        $countryCode = "+" . $matches[1];
//        $phoneNumber = $matches[2];
//        $fullPhoneNumber = $matches[1] . $matches[2];
//
//        $payload = [
//            "countryCode"     => $countryCode,
//            "phoneNumber"     => $phoneNumber,
//            "fullPhoneNumber" => $fullPhoneNumber, // optional
//            "callbackData"    => "Triggered by system for user: {$userName}",
//            "type"            => "Template",
//            "template"        => [
//                "name"         => $campaignName,
//                "languageCode" => "en",
//                "bodyValues"   => $templateParams,
//            ]
//        ];
//
//        // Add button values if available
//        if (!empty($ctaButtons)) {
//            $payload["template"]["buttonValues"] = $ctaButtons;
//        }
//
//        // Add media if required
//        if ($media && !empty($mediaUrl)) {
//            $payload["media"] = [
//                "url"      => $mediaUrl,
//                "filename" => basename($mediaUrl)
//            ];
//        }
//
//        // Send request to Interakt API
//        $response = Http::withHeaders([
//            "Authorization" => "Basic " . env('INTERAKT_API_KEY'),
//            "Content-Type"  => "application/json"
//        ])->post($url, $payload);
//
//        // Return structured response
//        if ($response->successful()) {
//            return [
//                'success' => true,
//                'message' => 'WhatsApp message sent successfully via Interakt',
//                'response' => $response->json()
//            ];
//        }
//
//        return [
//            'success' => false,
//            'message' => 'Failed to send WhatsApp message via Interakt',
//            'error'   => $response->body()
//        ];
//    }

    public static function sendTemplateMessage(
        string $campaignName,
        string $userName,
        string $mobile,
        array $templateParams,
        array $ctaButtons = [],
        bool $media = false,
        string $mediaUrl = ""
    ): array {
        $url = "https://backend.aisensy.com/campaign/t1/api/v2";
        $payload = [
            "apiKey"        => env('AISENSY_API_KEY'),
            "campaignName"  => $campaignName,
            "destination"   => $mobile,
            "userName"      => $userName,
            "templateParams"=> $templateParams,
        ];
        if ($media && !empty($mediaUrl)) {
            $payload["media"] = [
                "url"      => $mediaUrl,
                "filename" => "file"
            ];
        }

        if (!empty($ctaButtons)) {
            $payload["buttons"] = $ctaButtons;
        }

        $response = Http::withHeaders([
            "Content-Type" => "application/json"
        ])->post($url, $payload);

        return $response->json();
    }

    public function sendTextMessage(string $mobile, string $message): array
    {
        $url = "https://graph.facebook.com/" . env('WHATSAPP_API_VERSION', 'v22.0') . "/" . env('WHATSAPP_PHONE_NUMBER_ID') . "/messages";

        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post($url, [
                "messaging_product" => "whatsapp",
                "to" => $mobile,
                "type" => "text",
                "text" => [
                    "body" => $message
                ],
            ]);

        return $response->json();
    }

    public function sendTemplate(string $mobile, string $message): array
    {
        $url = "https://graph.facebook.com/" . env('WHATSAPP_API_VERSION', 'v22.0') . "/" . env('WHATSAPP_PHONE_NUMBER_ID') . "/messages";

        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post($url, [
                "messaging_product" => "whatsapp",
                "to" => $mobile,
                "type" => "template",
                "template" => [
                    "name" => 'hello_world',
                    "language" => [
                        "code" => "en_US"
                    ]
                ]
            ]);

        return $response->json();
    }

    public function sendCreationTemplate(string $mobile, string $name): array
    {
        $url = "https://graph.facebook.com/" . env('WHATSAPP_API_VERSION', 'v22.0') . "/" . env('WHATSAPP_PHONE_NUMBER_ID') . "/messages";

        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post($url, [
                "messaging_product" => "whatsapp",
                "to" => $mobile,
                "type" => "template",
                "template" => [
                    "name" => "test_msg", // your template name
                    "language" => [
                        "code" => "en_US"
                    ],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $name
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        return $response->json();
    }

    public function sendCreationTemplate2(string $mobile, string $name, string $email): array
    {
        $url = "https://graph.facebook.com/" . env('WHATSAPP_API_VERSION', 'v22.0') . "/" . env('WHATSAPP_PHONE_NUMBER_ID') . "/messages";

        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post($url, [
                "messaging_product" => "whatsapp",
                "to" => $mobile,
                "type" => "template",
                "template" => [
                    "name" => "tete", // your template name
                    "language" => [
                        "code" => "en"
                    ],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $name
                                ],
                                [
                                    "type" => "text",
                                    "text" => $email
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        return $response->json();
    }

}
