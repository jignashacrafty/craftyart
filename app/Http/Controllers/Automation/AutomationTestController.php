<?php

namespace App\Http\Controllers\Automation;

use App\Enums\AutomationType;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\WhatsappTemplate;
use App\Models\PromoCode;
use App\Models\Subscription;
use App\Models\UserData;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View as ViewFacade;

class AutomationTestController extends AppBaseController
{
    /**
     * Display the automation testing page
     */
    public function index(): Factory|View|Application
    {
        $emailTemplates = EmailTemplate::whereStatus(1)->get();
        $whatsappTemplates = WhatsappTemplate::whereStatus(1)->get();
        $promoCodes = PromoCode::where('status', 1)->get();
        $plans = Subscription::whereStatus(1)->get();

        return view('automation_test.index', compact(
            'emailTemplates',
            'whatsappTemplates',
            'promoCodes',
            'plans'
        ));
    }

    /**
     * Test email sending
     */
    public function testEmail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'name' => 'required|string',
                'template_id' => 'required|exists:email_templates,id',
                'subject' => 'required|string',
                'plan_id' => 'nullable|exists:subscriptions,id',
                'promo_code_id' => 'nullable|exists:promo_codes,id',
            ]);

            $emailTemplate = EmailTemplate::find($request->template_id);
            if (!$emailTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email template not found'
                ], 404);
            }

            // Build test data
            $response = [
                'userData' => [
                    'name' => $request->name,
                    'email' => $request->email,
                ]
            ];

            // Add plan data if selected
            if ($request->plan_id) {
                $plan = Subscription::find($request->plan_id);
                if ($plan) {
                    $response['type'] = "plan";
                    $response['plan'] = $plan;
                    $response['planType'] = 'old_sub';
                    $response['data'] = AutomationUtils::formatOldPlanData(
                        plan: $plan,
                        currency: 'INR'
                    );
                }
            }

            // Add promo code data if selected
            if ($request->promo_code_id) {
                $promoCode = PromoCode::find($request->promo_code_id);
                if ($promoCode) {
                    $rawPrice = 0;
                    if (!empty($response['data']['price'])) {
                        $rawPrice = (float) preg_replace('/[^0-9.]/', '', $response['data']['price']);
                    }

                    $discountPrice = null;
                    if ($rawPrice > 0) {
                        $discountedValue = $rawPrice - ($rawPrice * $promoCode->disc / 100);
                        $currency = $response['data']['currency'] ?? 'INR';
                        $discountedValue = $currency == 'INR' ? round($discountedValue) : number_format((float)$discountedValue, 2, '.', '');
                        $currencySymbol = $response['data']['currency_symbol'] ?? '₹';
                        $discountPrice = $currencySymbol . $discountedValue;
                    }

                    $response['promo'] = [
                        'code' => $promoCode->promo_code,
                        'disc' => (string) $promoCode->disc,
                        'expiry_date' => $promoCode->expiry_date ? $promoCode->expiry_date->format('j F Y') : null,
                        'discount_price' => (string) $discountPrice,
                    ];
                }
            }

            // Render email template
            $htmlBody = ViewFacade::make($emailTemplate->email_template, [
                'data' => $response,
            ])->render();

            // Send email
            $result = AutomationUtils::sendEmail(
                $request->email,
                $request->subject,
                $htmlBody
            );

            if (str_contains($result, "successfully")) {
                Log::info("Test email sent successfully to {$request->email}");
                return response()->json([
                    'success' => true,
                    'message' => 'Email sent successfully!',
                    'details' => [
                        'to' => $request->email,
                        'subject' => $request->subject,
                        'template' => $emailTemplate->name
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $result
            ], 500);

        } catch (\Exception $e) {
            Log::error("Test email error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test WhatsApp sending
     */
    public function testWhatsApp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'name' => 'required|string',
                'template_id' => 'required|exists:whatsapp_template,id',
                'plan_id' => 'nullable|exists:subscriptions,id',
                'promo_code_id' => 'nullable|exists:promo_codes,id',
            ]);

            $whatsappTemplate = WhatsappTemplate::find($request->template_id);
            if (!$whatsappTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp template not found'
                ], 404);
            }

            // Build test data
            $response = [
                'userData' => [
                    'name' => $request->name,
                    'email' => $request->email ?? 'test@example.com',
                ]
            ];

            // Add plan data if selected
            if ($request->plan_id) {
                $plan = Subscription::find($request->plan_id);
                if ($plan) {
                    $response['type'] = "plan";
                    $response['plan'] = $plan;
                    $response['planType'] = 'old_sub';
                    $response['data'] = AutomationUtils::formatOldPlanData(
                        plan: $plan,
                        currency: 'INR'
                    );
                }
            }

            // Add promo code data if selected
            if ($request->promo_code_id) {
                $promoCode = PromoCode::find($request->promo_code_id);
                if ($promoCode) {
                    $rawPrice = 0;
                    if (!empty($response['data']['price'])) {
                        $rawPrice = (float) preg_replace('/[^0-9.]/', '', $response['data']['price']);
                    }

                    $discountPrice = null;
                    if ($rawPrice > 0) {
                        $discountedValue = $rawPrice - ($rawPrice * $promoCode->disc / 100);
                        $currency = $response['data']['currency'] ?? 'INR';
                        $discountedValue = $currency == 'INR' ? round($discountedValue) : number_format((float)$discountedValue, 2, '.', '');
                        $currencySymbol = $response['data']['currency_symbol'] ?? '₹';
                        $discountPrice = $currencySymbol . $discountedValue;
                    }

                    $response['promo'] = [
                        'code' => $promoCode->promo_code,
                        'disc' => (string) $promoCode->disc,
                        'expiry_date' => $promoCode->expiry_date ? $promoCode->expiry_date->format('j F Y') : null,
                        'discount_price' => (string) $discountPrice,
                    ];
                }
            }

            // Resolve template parameters
            $templateParams = $this->resolveWhatsappTemplateParams(
                keys: $whatsappTemplate->template_params ?? [],
                response: $response
            );

            // Validate parameter count
            if ((int) $whatsappTemplate->template_params_count !== count($templateParams)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template parameter count mismatch. Expected: ' . $whatsappTemplate->template_params_count . ', Got: ' . count($templateParams)
                ], 400);
            }

            // Send WhatsApp message
            $wpResponse = WhatsAppService::sendTemplateMessage(
                campaignName: $whatsappTemplate->campaign_name,
                userName: $request->name,
                mobile: $request->phone,
                templateParams: $templateParams,
                media: $whatsappTemplate->media_url == 1,
                mediaUrl: $whatsappTemplate->url ?? ""
            );

            Log::info("Test WhatsApp sent", [
                'phone' => $request->phone,
                'template' => $whatsappTemplate->campaign_name,
                'params' => $templateParams,
                'response' => $wpResponse
            ]);

            // Check response
            if (isset($wpResponse['success']) && $wpResponse['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully!',
                    'details' => [
                        'to' => $request->phone,
                        'template' => $whatsappTemplate->campaign_name,
                        'parameters' => $templateParams,
                        'api_response' => $wpResponse
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp: ' . ($wpResponse['message'] ?? 'Unknown error'),
                'api_response' => $wpResponse
            ], 500);

        } catch (\Exception $e) {
            Log::error("Test WhatsApp error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test both email and WhatsApp
     */
    public function testBoth(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'phone' => 'required|string',
                'name' => 'required|string',
                'email_template_id' => 'required|exists:email_templates,id',
                'email_subject' => 'required|string',
                'whatsapp_template_id' => 'required|exists:whatsapp_template,id',
                'plan_id' => 'nullable|exists:subscriptions,id',
                'promo_code_id' => 'nullable|exists:promo_codes,id',
            ]);

            $results = [];

            // Test Email
            $emailRequest = new Request([
                'email' => $request->email,
                'name' => $request->name,
                'template_id' => $request->email_template_id,
                'subject' => $request->email_subject,
                'plan_id' => $request->plan_id,
                'promo_code_id' => $request->promo_code_id,
            ]);
            $emailResult = $this->testEmail($emailRequest);
            $results['email'] = json_decode($emailResult->getContent(), true);

            // Test WhatsApp
            $whatsappRequest = new Request([
                'phone' => $request->phone,
                'name' => $request->name,
                'template_id' => $request->whatsapp_template_id,
                'plan_id' => $request->plan_id,
                'promo_code_id' => $request->promo_code_id,
            ]);
            $whatsappResult = $this->testWhatsApp($whatsappRequest);
            $results['whatsapp'] = json_decode($whatsappResult->getContent(), true);

            $allSuccess = $results['email']['success'] && $results['whatsapp']['success'];

            return response()->json([
                'success' => $allSuccess,
                'message' => $allSuccess ? 'Both messages sent successfully!' : 'Some messages failed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error("Test both error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get template preview data
     */
    public function getTemplatePreview(Request $request): JsonResponse
    {
        try {
            $type = $request->type; // 'email' or 'whatsapp'
            $templateId = $request->template_id;

            if ($type === 'email') {
                $template = EmailTemplate::find($templateId);
                if (!$template) {
                    return response()->json(['success' => false, 'message' => 'Template not found'], 404);
                }

                return response()->json([
                    'success' => true,
                    'template' => [
                        'id' => $template->id,
                        'name' => $template->name,
                        'view' => $template->email_template
                    ]
                ]);
            }

            if ($type === 'whatsapp') {
                $template = WhatsappTemplate::find($templateId);
                if (!$template) {
                    return response()->json(['success' => false, 'message' => 'Template not found'], 404);
                }

                return response()->json([
                    'success' => true,
                    'template' => [
                        'id' => $template->id,
                        'name' => $template->campaign_name,
                        'params_count' => $template->template_params_count,
                        'params' => $template->template_params,
                        'has_media' => $template->media_url == 1,
                        'media_url' => $template->url
                    ]
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve WhatsApp template parameters
     */
    private function resolveWhatsappTemplateParams(array $keys, array $response): array
    {
        $resolved = [];
        foreach ($keys as $key) {
            if (!str_contains($key, '.')) {
                $resolved[] = match ($key) {
                    'link' => "https://www.craftyartapp.com/plans",
                    default => ''
                };
                continue;
            }

            [$group, $field] = explode('.', $key);
            $resolved[] = match ($group) {
                'UserData' => $response['userData'][$field] ?? '',
                'PlanData' => $response['data'][$field] ?? '',
                'PromoData' => $response['promo'][$field] ?? '',
                default => ''
            };
        }
        return $resolved;
    }
}
