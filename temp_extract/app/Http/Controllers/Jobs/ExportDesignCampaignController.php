<?php

namespace App\Http\Controllers\Jobs;

use App\Enums\AutomationType;
use App\Enums\ConfigType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\Config;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\WhatsappTemplate;
use App\Models\AutomationSendDetail;
use App\Models\AutomationSendLog;
use App\Models\ExportTable;
use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use View;

class ExportDesignCampaignController extends ApiController
{
    public static function instantSend($exportId): void
    {
        $export = ExportTable::whereId($exportId)->whereWatermark(1)->first();
        if($export){
            Log::info("Sent Started");
            self::sendEmailOrWhatsapp($export);
        }

//        $ctaButtons[] = [
//            "type" => "button",
//            "sub_type" => "url",
//            "index" => 0,
//            "parameters" => [
//                [
//                    "type" => "text",
//                    "text" => "123456"
//                ]
//            ],
//        ];
//
//        $response = WhatsAppService::sendTemplateMessage(campaignName: "otp_verification",userName: "Sanjay Vaghela",mobile: "+918460748164",templateParams: ["123456"],ctaButtons: $ctaButtons);
//        Log::info("Response ".json_encode($response));
    }

    public static function sendEmailOrWhatsapp(ExportTable $export): void
    {
        if(!$export->userData)
            return;
        $config = Config::whereName(ConfigType::EXPORT_WITH_WATERMARK_AUTOMATION->label())->first();
        Log::info("Config Fetch".json_encode($config));
        if ($config && $config->value && !empty($config->value)) {
            $configValue = collect($config->value)->firstWhere('day', 0);
            Log::info("Config Value ".json_encode($configValue));
            if (!$configValue)
                return;

            Log::info("Email Enable : ".$configValue['email']['enable']." Wp Enable ".$configValue['wp']['enable']);
            if (($configValue['email']['enable'] ?? false) || ($configValue['wp']['enable'] ?? false)) {

                $response['userData'] = [
                    'name' => $export->userData->name,
                    'email' => $export->userData->email,
                ];

                $tempArray[] = [
                    "title" => $export->name,
                    "image" => HelperController::generatePublicUrl($export->draft->design->post_thumb),
                    "width" => $export->draft->width,
                    "height" => $export->draft->height,
                    "amount" => ($export->currency == "INR" ? "₹" : "$").$export->amount,
                    "link" => $export->draft->design->page_link,
                ];

                $response['data'] = [
                    'templates' => $tempArray,
                    'amount' => ($export->currency == "INR" ? "₹" : "$").$export->amount,
                    "package_name" => $export->name,
                ];

                if ($configValue['email']['enable'] ?? false) {
                    $isSent = self::sendMail(exportTable: $export, configValue: $configValue, response: $response);
                    if ($isSent) $export->increment('email_sent');
                }

                if ($configValue['wp']['enable'] ?? false) {
                    $isSent = self::sendWa(exportTable: $export, configValue: $configValue, response: $response);
                    if ($isSent) $export->increment('wp_sent');
                }
            }
        }

    }

    private static function sendMail(ExportTable $exportTable, $configValue, $response): bool
    {
        $emailConfig = $configValue['email']['config'] ?? [];


        $emailTemplateId = $emailConfig['template'] ?? null;
        if (!$emailTemplateId)
            return false;
        $emailTemplate = EmailTemplate::whereId($emailTemplateId)->first();
        if (!$emailTemplate)
            return false;

        $viewPath = $emailTemplate->email_template;

//        $name = str_replace('.', '/', $emailTemplate->email_template);
//        $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";
//        if (!file_exists($viewPath))
//            return false;

        if (($emailConfig['promo_code'] ?? false)) {
            $promoCodeId = $emailConfig['promo_code'];
            $promo = PromoCode::whereId($promoCodeId)->first();
            if ($promo) {
                $expiry_date = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;

                $rawPrice = $exportTable->amount;

                $discountPrice = 0;
                if ($rawPrice > 0) {
                    $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
                    $isInr = $exportTable->currency == "INR";
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

        Log::info("Email Response " . json_encode($response));
        try {
            $htmlBody = View::make($viewPath, [
                'data' => $response
            ])->render();
            $subject = $emailConfig['subject'] ?? '';
//            Mail::mailer('otp')->send([], [], function ($message) use ($userData, $subject, $htmlBody) {
//                $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_OTP_FROM_NAME"))
//                    ->to($userData->email)
//                    ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
//                    ->subject($subject)
//                    ->setBody($htmlBody, 'text/html');
//
//                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
//            });

            Mail::send([], [], function ($message) use ($exportTable, $subject, $htmlBody) {
                $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_OTP_FROM_NAME"))
                    ->to($exportTable->userData->email)
                    ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
                    ->subject($subject)
                    ->setBody($htmlBody, 'text/html');

                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });
        } catch (\Exception $e) {
            Log::info("Error Message " . $e->getMessage());
            return false;
        }
        return true;
    }

    private static function sendWa(ExportTable $exportTable, $configValue, $response): bool
    {
        $wpConfig = $configValue['wp']['config'] ?? [];

        if (!$wpConfig || !$wpConfig['template'])
            return false;
        $waTemplate = WhatsappTemplate::whereId($wpConfig['template'])->first();
        if (!$waTemplate)
            return false;

        if (($wpConfig['promo_code'] ?? false)) {
            $promoCodeId = $wpConfig['promo_code'];
            $promo = PromoCode::whereId($promoCodeId)->first();
            if ($promo) {
                $expiry_date = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;

                $rawPrice = $exportTable->amount;
                Log::info(" Raw Price " . $rawPrice);
                $discountPrice = 0;
                if ($rawPrice > 0) {
                    $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
                    $isInr = $exportTable->currency == "INR";
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

        $response['userData'] = [
            "name" => $exportTable->userData->name,
            "email" => $exportTable->userData->email
        ];

        $response['link'] = "https://www.craftyartapp.com/offer/payment/$exportTable->crafty_id";

        $waParams = self::resolveWhatsappTemplateParams(
            keys: $waTemplate->template_params,
            response: $response
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

        Log::info("Whatsapp Params " . json_encode($waParams));
        Log::info("Contact No " .$exportTable->getContactNo());

//        $result = WhatsAppService::sendTemplateMessage(
//            campaignName: $waTemplate->campaign_name,
//            userName: $exportTable->user->name,
//            mobile: $exportTable->getContactNo(),
//            templateParams: $waParams,
//            ctaButtons: $ctaButtons, media: $waTemplate->media_url == 1, mediaUrl: $waTemplate->url ?? ""
//        );
//        if (is_array($result)) {
//            $status = $result['success'] ?? false;
//        } else {
//            $status = $result->success ?? false;
//        }
//        return $status;

        return true;
    }

    private static function resolveWhatsappTemplateParams(array $keys, array $response): array
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
                'PlanData' => $field == "offer_price" || $field == 'actual_price' ? $response['data']['amount'] :  $response['data'][$field] ?? '',
                'PromoData' => $response['promo'][$field] ?? '',
                'link' => $response['link'] ?? ''
            };
        }
        return $resolved;
    }

    public function startProcess(): void
    {
        Log::info("🚀 Export With Watermark Drop Job Started");

        // Fetch configuration
        $config = Config::where('name', ConfigType::EXPORT_WITH_WATERMARK_AUTOMATION->label())->first();
        if (!$config) {
            Log::warning("❌ Export With Watermark: Config not found!");
            return;
        }

        $configData = $config->value;
        if (!is_array($configData) || empty($configData)) {
            Log::warning("❌ Export With Watermark: Invalid config data!");
            return;
        }

        Log::info("📊 Processing " . count($configData) . " frequency configurations");

        $now = Carbon::now();

        $allTemplateData = AutomationUtils::preFetchAllTemplatesAndPromoCodes($configData, ConfigType::EXPORT_WITH_WATERMARK_AUTOMATION->value);

        Log::info("🎯 Starting to process frequencies");
//        $activeUserIds = TransactionLog::where('expired_at', '>', now())
//            ->where('status', 1)
//            ->pluck('user_id')
//            ->toArray();
        foreach ($configData as $frequencyIndex => $frequency) {
            $days = (int)($frequency['day'] ?? 0);

            if ($days === 0) {
                Log::info("⏭️ Recent Expire Skipping day 0 configuration");
                continue;
            }

            Log::info("🔁 Recent Expire Processing frequency #{$frequencyIndex} for day: {$days}");

//            self::processRecentExpireFrequency($frequency, $days, $now, $allTemplateData,$activeUserIds);
            self::processExportWithWatermarkFrequency($frequency, $days, $now,$allTemplateData);
        }

        Log::info("🎉 Recent Expire Checkout Drop Job Finished Successfully");
    }

    private static function processExportWithWatermarkFrequency($frequency, int $days, Carbon $now, array $allTemplateData): void
    {
        $targetDate = $now->copy()->subDays($days)->toDateString();
        Log::info("📅 Recent Expire Target date for {$days} days ago: {$targetDate}");

        // Skip processing if both channels are disabled
        $emailEnabled = $frequency['email']['enable'] ?? false;
        $whatsappEnabled = $frequency['wp']['enable'] ?? false;

        if (!$emailEnabled && !$whatsappEnabled) {
            Log::info("⏭️ Recent Expire Both email and WhatsApp disabled for day {$days}, skipping processing");
            return;
        }

        // Create automation logs only for enabled channels
        $emailLog = null;
        $whatsappLog = null;
        $campaignName = "Recent Expire - Day {$days} - {$targetDate}";

        if ($emailEnabled) {
            $emailLog = self::createAutomationLog($campaignName, [], AutomationType::EMAIL);
            Log::info("📧 Recent Expire Email Automation Log created: #{$emailLog->id}");
        }

        if ($whatsappEnabled) {
            $whatsappLog = self::createAutomationLog($campaignName, [], AutomationType::WHATSAPP);
            Log::info("💬 Recent Expire WhatsApp Automation Log created: #{$whatsappLog->id}");
        }

        // Process users in chunks without loading all data at once
        self::processExportWithWatermarkUsersInChunks($frequency, $days, $targetDate, $emailLog, $whatsappLog, $allTemplateData);

        // Mark logs as completed only if they exist
        if ($emailLog) {
            $emailLog->update(['status' => 'completed']);
            Log::info("✅ Recent Expire Email Log #{$emailLog->id} completed - Sent: {$emailLog->sent}, Failed: {$emailLog->failed}");
        }

        if ($whatsappLog) {
            $whatsappLog->update(['status' => 'completed']);
            Log::info("✅ Recent Expire WhatsApp Log #{$whatsappLog->id} completed - Sent: {$whatsappLog->sent}, Failed: {$whatsappLog->failed}");
        }
    }

    private static function processExportWithWatermarkUsersInChunks($frequency, int $days, string $targetDate, $emailLog, $whatsappLog, array $allTemplateData): void
    {
        $chunkSize = 100;
        $totalProcessed = 0;
        $chunkIndex = 0;

        Log::info("👥 Recent Expire Starting chunked user processing for day {$days}");

        // Use chunking with eager loading - relationships will handle the data
        ExportTable::with(['userData'])
            ->whereDate('created_at', $targetDate)
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('transaction_logs')
                    ->groupBy('user_id');
            })
            ->chunkById($chunkSize, function ($chunks) use (
                $frequency,
                $days,
                $emailLog,
                $whatsappLog,
                $allTemplateData,
                &$chunkIndex,
                &$totalProcessed
            ) {
                Log::info("🔄 Recent Expire Processing chunk #{$chunkIndex} with " . $chunks->count() . " users for day {$days}");

                foreach ($chunks as $export) {
                    self::processSingleExportWithWatermarkUser(
                        $export,
                        $frequency,
                        $emailLog,
                        $whatsappLog,
                        $allTemplateData
                    );

                    $totalProcessed++;
                    sleep(2); // Rate limiting
                }
                $chunkIndex++;
                Log::info("📊 Recent Expire Chunk #{$chunkIndex} completed - Total processed: {$totalProcessed}");
            });

        Log::info("✅ Finished processing all chunks for day {$days} - Total users: {$totalProcessed}");
    }

    private static function processSingleExportWithWatermarkUser(
        ExportTable $export,
                       $frequency,
                       $emailLog,
                       $whatsappLog,
                       $allTemplateData
    ): void {
        $user = $export->userData;
        if (!$user) {
            Log::warning("⚠️ Recent Expire Skipping Order #{$export->id} due to missing user relation");
            self::logFailedCommunication($emailLog, $whatsappLog, null, null, null, "Missing user for Order {$export->id}");
            return;
        }

        Log::info("👤 Recent Expire Processing User #{$user->id}, Email: {$user->email}");

        try {
            $commonData = $export->getAutomationCommonData();

            if (isset($commonData['success']) && !$commonData['success']) {
                self::logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $export->getContactNo(), $commonData['message']);
                return;
            }

            $contactNumber = $export->getContactNo();

            $results = self::handleAutomationForJob($frequency, $user, $commonData, $contactNumber, $allTemplateData);

            self::processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber);

        } catch (\Exception $e) {
            Log::error("❌ Recent Expire Error processing User #{$user->id}: " . $e->getMessage());
            self::logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $export->getContactNo(), $e->getMessage());
        }
    }

    private static function handleAutomationForJob($frequencyConfig, $user, $commonData, $contactNumber, $allTemplateData): array
    {
        return AutomationUtils::handleAutomationForJob(
            $frequencyConfig,
            $user,
            $commonData,
            $contactNumber,
            $allTemplateData,
            ConfigType::EXPORT_WITH_WATERMARK_AUTOMATION->value
        );
    }

    private static function processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber): void
    {
        foreach ($results as $channel => $result) {
            $log = $channel === 'email' ? $emailLog : $whatsappLog;

            if (!$log) continue;

            if ($result['success']) {
                Log::info("✅ {$channel} sent successfully to User #{$user->id}");
                self::logCommunicationResult($log, $user->id, $channel === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP, 'sent', $user->email, $contactNumber);
            } else {
                Log::error("❌ Failed to send {$channel} to User #{$user->id}: {$result['message']}");
                self::logCommunicationResult($log, $user->id, $channel === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP, 'failed', $user->email, $contactNumber, $result['message']);
            }
        }
    }

    private static function logFailedCommunication($emailLog, $whatsappLog, $userId, $email, $contactNumber, $error): void
    {
        if ($emailLog) {
            self::logCommunicationResult($emailLog, $userId, AutomationType::EMAIL, 'failed', $email, null, $error);
        }

        if ($whatsappLog) {
            self::logCommunicationResult($whatsappLog, $userId, AutomationType::WHATSAPP, 'failed', null, $contactNumber, $error);
        }
    }

    private static function logCommunicationResult($log, $userId, AutomationType $type, string $status, $email = null, $contactNumber = null, $error = null): void
    {
        // Update the main log counters
        if ($status === 'sent') {
            $log->increment('sent');
        } else {
            $log->increment('failed');
        }

        $log->increment('total');

        // Create detail log only for failures
        if ($status === 'failed') {
            AutomationSendDetail::create([
                'log_id' => $log->id,
                'user_id' => $userId,
                'type' => $type->value,
                'send_type' => ConfigType::EXPORT_WITH_WATERMARK_AUTOMATION->value,
                'email' => $email,
                'contact_number' => $contactNumber,
                'status' => 'failed',
                'error_message' => $error,
            ]);
        }
    }

    private static function createAutomationLog(string $campaignName, array $userIds, AutomationType $type): AutomationSendLog
    {
        $userIdsJson = json_encode($userIds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        Log::info("📝 Creating log for {$type->value} with user_ids: {$userIdsJson}");

        return AutomationSendLog::create([
            'campaign_name' => $campaignName,
            'user_ids' => $userIdsJson,
            'select_users_type' => 3,
            'total' => count($userIds),
            'sent' => 0,
            'failed' => 0,
            'status' => 'processing',
            'type' => $type->value,
            'send_type' => ConfigType::EXPORT_WITH_WATERMARK_AUTOMATION->value
        ]);
    }

}