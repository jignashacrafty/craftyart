<?php

namespace App\Jobs;

use App\Enums\AutomationType;
use App\Enums\ConfigType;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\AutomationSendDetail;
use App\Models\AutomationSendLog;
use App\Models\Config;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessCheckoutDropJob2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info("🚀 Checkout Drop Job Started");

        // Fetch configuration
        $config = Config::where('name', ConfigType::CHECKOUT_DROP_AUTOMATION->label())->first();
        if (!$config) {
            Log::warning("❌ CheckoutDropJob: Config not found!");
            return;
        }

        $configData = $config->value;
        if (!is_array($configData) || empty($configData)) {
            Log::warning("❌ CheckoutDropJob: Invalid config data!");
            return;
        }

        Log::info("📊 Processing " . count($configData) . " frequency configurations");

        $now = Carbon::now();

        $allTemplateData = AutomationUtils::preFetchAllTemplatesAndPromoCodes($configData, ConfigType::CHECKOUT_DROP_AUTOMATION->value);

        Log::info("🎯 Starting to process frequencies");

        foreach ($configData as $frequencyIndex => $frequency) {
            $days = (int)($frequency['day'] ?? 0);

            if ($days === 0) {
                Log::info("⏭️ Skipping day 0 configuration");
                continue;
            }

            Log::info("🔁 Processing frequency #{$frequencyIndex} for day: {$days}");

            $this->processFrequency($frequency, $days, $now, $allTemplateData);
        }

        Log::info("🎉 Checkout Drop Job Finished Successfully");
    }

    private function processFrequency($frequency, int $days, Carbon $now, array $allTemplateData): void
    {
        $targetDate = $now->copy()->subDays($days)->toDateString();
        Log::info("📅 Target date for {$days} days ago: {$targetDate}");

        // Skip processing if both channels are disabled
        $emailEnabled = $frequency['email']['enable'] ?? false;
        $whatsappEnabled = $frequency['wp']['enable'] ?? false;

        if (!$emailEnabled && !$whatsappEnabled) {
            Log::info("⏭️ Both email and WhatsApp disabled for day {$days}, skipping processing");
            return;
        }

        // Create automation logs only for enabled channels
        $emailLog = null;
        $whatsappLog = null;
        $campaignName = "Checkout Drop - Day {$days} - {$targetDate}";

        if ($emailEnabled) {
            $emailLog = $this->createAutomationLog($campaignName, [], AutomationType::EMAIL);
            Log::info("📧 Email Automation Log created: #{$emailLog->id}");
        }

        if ($whatsappEnabled) {
            $whatsappLog = $this->createAutomationLog($campaignName, [], AutomationType::WHATSAPP);
            Log::info("💬 WhatsApp Automation Log created: #{$whatsappLog->id}");
        }

        // Process users in chunks without loading all data at once
        $this->processUsersInChunks($frequency, $days, $targetDate, $emailLog, $whatsappLog, $allTemplateData);

        // Mark logs as completed only if they exist
        if ($emailLog) {
            $emailLog->update(['status' => 'completed']);
            Log::info("✅ Email Log #{$emailLog->id} completed - Sent: {$emailLog->sent}, Failed: {$emailLog->failed}");
        }

        if ($whatsappLog) {
            $whatsappLog->update(['status' => 'completed']);
            Log::info("✅ WhatsApp Log #{$whatsappLog->id} completed - Sent: {$whatsappLog->sent}, Failed: {$whatsappLog->failed}");
        }
    }

    private function processUsersInChunks($frequency, int $days, string $targetDate, $emailLog, $whatsappLog, array $allTemplateData): void
    {
        $chunkSize = 100;
        $totalProcessed = 0;
        $chunkIndex = 0;

        Log::info("👥 Starting chunked user processing for day {$days}");

        // Use chunking with eager loading - relationships will handle the data
        Order::with([
            'user.transactionLogs' => function ($q) {
                $q->latest('id')->limit(1);
            }
        ])
            ->whereDate('created_at', $targetDate)
            ->where('status', 'failed')
            ->whereHas('user')
            ->where(function ($q) {
                $q->whereDoesntHave('user.transactionLogs')
                    ->orWhereHas('user.transactionLogs', function ($q2) {
                        $q2->where('expired_at', '<', now());
                    });
            })
            ->chunkById($chunkSize, function ($chunk) use (
                $frequency,
                $days,
                $emailLog,
                $whatsappLog,
                $allTemplateData,
                &$chunkIndex,
                &$totalProcessed
            ) {
                Log::info("🔄 Processing chunk #{$chunkIndex} with " . $chunk->count() . " users for day {$days}");

                foreach ($chunk as $order) {
                    $this->processSingleUser(
                        $order,
                        $frequency,
                        $emailLog,
                        $whatsappLog,
                        $allTemplateData
                    );

                    $totalProcessed++;
                    sleep(2); // Rate limiting
                }
                $chunkIndex++;
                Log::info("📊 Chunk #{$chunkIndex} completed - Total processed: {$totalProcessed}");
            });

        Log::info("✅ Finished processing all chunks for day {$days} - Total users: {$totalProcessed}");
    }

    /**
     * Create automation log entry
     */
    private function createAutomationLog(string $campaignName, array $userIds, AutomationType $type): AutomationSendLog
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
            'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value
        ]);
    }

    private function processSingleUser(
       Order $order,
        $frequency,
        $emailLog,
        $whatsappLog,
        $allTemplateData
    ): void {
        $user = $order->user;
        if (!$user) {
            Log::warning("⚠️ Skipping Order #{$order->id} due to missing user relation");
            $this->logFailedCommunication($emailLog, $whatsappLog, null, null, null, "Missing user for Order {$order->id}");
            return;
        }

        Log::info("👤 Processing User #{$user->id}, Email: {$user->email}, PlanType: {$order->type}");

        try {
            $commonData = $order->getAutomationCommonData();

            if (isset($commonData['success']) && !$commonData['success']) {
                $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $order->contact_no, $commonData['message']);
                return;
            }

            $contactNumber = $order->contact_no;

            $results = $this->handleAutomationForJob($frequency, $user, $commonData, $contactNumber, $allTemplateData);

            $this->processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber);

        } catch (\Exception $e) {
            Log::error("❌ Error processing User #{$user->id}: " . $e->getMessage());
            $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $order->contact_number, $e->getMessage());
        }
    }

    private function handleAutomationForJob($frequencyConfig, $user, $commonData, $contactNumber, $allTemplateData): array
    {
        return AutomationUtils::handleAutomationForJob(
            $frequencyConfig,
            $user,
            $commonData,
            $contactNumber,
            $allTemplateData,
            ConfigType::CHECKOUT_DROP_AUTOMATION->value
        );
    }

    private function processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber): void
    {
        foreach ($results as $channel => $result) {
            $log = $channel === 'email' ? $emailLog : $whatsappLog;

            if (!$log) continue;

            if ($result['success']) {
                Log::info("✅ {$channel} sent successfully to User #{$user->id}");
                $this->logCommunicationResult($log, $user->id, $channel === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP, 'sent', $user->email, $contactNumber);
            } else {
                Log::error("❌ Failed to send {$channel} to User #{$user->id}: {$result['message']}");
                $this->logCommunicationResult($log, $user->id, $channel === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP, 'failed', $user->email, $contactNumber, $result['message']);
            }
        }
    }

    /**
     * Common method to log failed communication for both email and WhatsApp
     * (Keep this for other failure scenarios outside processCommunicationResults)
     */
    private function logFailedCommunication($emailLog, $whatsappLog, $userId, $email, $contactNumber, $error): void
    {
        if ($emailLog) {
            $this->logCommunicationResult($emailLog, $userId, AutomationType::EMAIL, 'failed', $email, null, $error);
        }

        if ($whatsappLog) {
            $this->logCommunicationResult($whatsappLog, $userId, AutomationType::WHATSAPP, 'failed', null, $contactNumber, $error);
        }
    }

    /**
     * Common method to log communication results for both email and WhatsApp
     */
    private function logCommunicationResult($log, $userId, AutomationType $type, string $status, $email = null, $contactNumber = null, $error = null): void
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
                'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value,
                'email' => $email,
                'contact_number' => $contactNumber,
                'status' => 'failed',
                'error_message' => $error,
            ]);
        }
    }


}

//class ProcessCheckoutDropJob implements ShouldQueue
//{
//    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
//
//    public function handle(): void
//    {
//        Log::info("🚀 Checkout Drop Job Started");
//
//        // Fetch configuration
//        $config = Config::where('name', ConfigType::CHECKOUT_DROP_AUTOMATION->label())->first();
//        if (!$config) {
//            Log::warning("❌ CheckoutDropJob: Config not found!");
//            return;
//        }
//
//        $configData = $config->value;
//        if (!is_array($configData) || empty($configData)) {
//            Log::warning("❌ CheckoutDropJob: Invalid config data!");
//            return;
//        }
//
//        Log::info("📊 Processing " . count($configData) . " frequency configurations");
//
//        $now = Carbon::now();
//
//        // Pre-fetch ALL required templates and promo codes once
//        $allTemplateData = $this->preFetchAllTemplatesAndPromoCodes($configData);
//
//        Log::info("🎯 Starting to process frequencies");
//
//        foreach ($configData as $frequencyIndex => $frequency) {
//            $days = (int)($frequency['day'] ?? 0);
//
//            // Skip day 0 as per requirement
//            if ($days === 0) {
//                Log::info("⏭️ Skipping day 0 configuration");
//                continue;
//            }
//
//            Log::info("🔁 Processing frequency #{$frequencyIndex} for day: {$days}");
//
//            $this->processFrequency($frequency, $days, $now, $allTemplateData);
//        }
//
//        Log::info("🎉 Checkout Drop Job Finished Successfully");
//    }
//
//    private function processFrequency($frequency, int $days, Carbon $now, array $allTemplateData): void
//    {
//        $targetDate = $now->copy()->subDays($days)->toDateString();
//        Log::info("📅 Target date for {$days} days ago: {$targetDate}");
//
//        // Skip processing if both channels are disabled
//        $emailEnabled = $frequency['email']['enable'] ?? false;
//        $whatsappEnabled = $frequency['wp']['enable'] ?? false;
//
//        if (!$emailEnabled && !$whatsappEnabled) {
//            Log::info("⏭️ Both email and WhatsApp disabled for day {$days}, skipping processing");
//            return;
//        }
//
//        // Create automation logs only for enabled channels
//        $emailLog = null;
//        $whatsappLog = null;
//        $campaignName = "Checkout Drop - Day {$days} - {$targetDate}";
//
//        if ($emailEnabled) {
//            $emailLog = $this->createAutomationLog($campaignName, [], AutomationType::EMAIL);
//            Log::info("📧 Email Automation Log created: #{$emailLog->id}");
//        }
//
//        if ($whatsappEnabled) {
//            $whatsappLog = $this->createAutomationLog($campaignName, [], AutomationType::WHATSAPP);
//            Log::info("💬 WhatsApp Automation Log created: #{$whatsappLog->id}");
//        }
//
//        // Process users in chunks without loading all data at once
//        $this->processUsersInChunks($frequency, $days, $targetDate, $emailLog, $whatsappLog, $allTemplateData);
//
//        // Mark logs as completed only if they exist
//        if ($emailLog) {
//            $emailLog->update(['status' => 'completed']);
//            Log::info("✅ Email Log #{$emailLog->id} completed - Sent: {$emailLog->sent}, Failed: {$emailLog->failed}");
//        }
//
//        if ($whatsappLog) {
//            $whatsappLog->update(['status' => 'completed']);
//            Log::info("✅ WhatsApp Log #{$whatsappLog->id} completed - Sent: {$emailLog->sent}, Failed: {$emailLog->failed}");
//        }
//    }
//
//    private function processUsersInChunks($frequency, int $days, string $targetDate, $emailLog, $whatsappLog, array $allTemplateData): void
//    {
//        $chunkSize = 100;
//        $totalProcessed = 0;
//        $chunkIndex = 0;
//
//        Log::info("👥 Starting chunked user processing for day {$days}");
//
//        // Use chunking with eager loading to process users without loading all at once
//        Order::with([
//            'user.transactionLogs' => function ($q) {
//                $q->latest('id')->limit(1);
//            },
//            'subPlan', // Relationship for new_sub type
//            'subscription', // Relationship for old_sub/offer type
//            'designs' // Relationship for template/video type
//        ])
//            ->whereDate('created_at', $targetDate)
//            ->where('status', 'failed')
//            ->whereHas('user')
//            ->where(function ($q) {
//                $q->whereDoesntHave('user.transactionLogs')
//                    ->orWhereHas('user.transactionLogs', function ($q2) {
//                        $q2->where('expired_at', '<', now());
//                    });
//            })
//            ->chunkById($chunkSize, function ($chunk) use (
//                $frequency,
//                $days,
//                $emailLog,
//                $whatsappLog,
//                $allTemplateData,
//                &$chunkIndex,
//                &$totalProcessed
//            ) {
//                Log::info("🔄 Processing chunk #{$chunkIndex} with " . $chunk->count() . " users for day {$days}");
//
//                foreach ($chunk as $order) {
//                    $this->processSingleUser(
//                        $order,
//                        $frequency,
//                        $emailLog,
//                        $whatsappLog,
//                        $allTemplateData
//                    );
//
//                    $totalProcessed++;
//                    sleep(2); // Rate limiting
//                }
//
//                // Common update for both logs
//                $this->updateAutomationLogsCommon($emailLog, $whatsappLog, $chunk);
//
//                $chunkIndex++;
//                Log::info("📊 Chunk #{$chunkIndex} completed - Total processed: {$totalProcessed}");
//            });
//
//        Log::info("✅ Finished processing all chunks for day {$days} - Total users: {$totalProcessed}");
//    }
//
//    /**
//     * Common update for both email and WhatsApp logs
//     */
//    private function updateAutomationLogsCommon($emailLog, $whatsappLog, $chunk): void
//    {
//        // Get user IDs from chunk (already unique due to chunkById)
//        $userIds = $chunk->pluck('user.id')
//            ->filter()
//            ->map(fn($id) => (string)$id)
//            ->toArray();
//
//        if (empty($userIds)) {
//            return;
//        }
//
//        // Update both logs in single operation
//        $logs = array_filter([$emailLog, $whatsappLog]);
//
//        foreach ($logs as $log) {
//            $this->appendUsersToAutomationLogOptimized($log, $userIds);
//        }
//    }
//
//    /**
//     * Optimized append without array_unique (since IDs are already unique)
//     */
//    private function appendUsersToAutomationLogOptimized($log, array $newUserIds): void
//    {
//        try {
//            // Get existing user IDs
//            $existingUserIds = json_decode($log->user_ids, true) ?? [];
//
//            // Simple merge - no need for array_unique since newUserIds are already unique
//            $allUserIds = array_merge($existingUserIds, $newUserIds);
//
//            // Update the log
//            $log->update([
//                'user_ids' => json_encode($allUserIds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
//                'total' => count($allUserIds)
//            ]);
//
//        } catch (\Exception $e) {
//            Log::error("❌ Failed to update automation log #{$log->id}: " . $e->getMessage());
//        }
//    }
//
//    /**
//     * Create automation log entry
//     */
//    private function createAutomationLog(string $campaignName, array $userIds, AutomationType $type): AutomationSendLog
//    {
//        // Ensure user_ids are properly formatted as strings and JSON is correctly encoded
//        $userIdsJson = json_encode($userIds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
//
//        Log::info("📝 Creating log for {$type->value} with user_ids: {$userIdsJson}");
//
//        return AutomationSendLog::create([
//            'campaign_name' => $campaignName,
//            'user_ids' => $userIdsJson,
//            'select_users_type' => 3,
//            'total' => count($userIds),
//            'sent' => 0,
//            'failed' => 0,
//            'status' => 'processing',
//            'type' => $type->value,
//            'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value
//        ]);
//    }
//
//
//    private function processSingleUser(
//        $order,
//        $frequency,
//        $emailLog,
//        $whatsappLog,
//        $allTemplateData
//    ): void {
//        $user = $order->user;
//        if (!$user) {
//            Log::warning("⚠️ Skipping Order #{$order->id} due to missing user relation");
//            $this->logFailedCommunication($emailLog, $whatsappLog, null, null, "Missing user for Order {$order->id}");
//            return;
//        }
//
//        Log::info("👤 Processing User #{$user->id}, Email: {$user->email}, PlanType: {$order->type}");
//
//        try {
//            // Prepare common data using pre-loaded relationships
//            $commonData = $this->prepareCommonDataForUser($order, $user);
//
//            if (isset($commonData['success']) && !$commonData['success']) {
//                $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $commonData['message']);
//                return;
//            }
//
//            $contactNumber = !empty($order->contact_no)
//                ? $order->contact_no
//                : (!empty($user->number) ? $user->country_code . $user->number : null);
//
//            // Handle both email and WhatsApp with pre-fetched templates
//            $results = $this->handleAutomationForJob($frequency, $user, $commonData, $contactNumber, $allTemplateData);
//
//            // Process results and update logs (only for enabled channels)
//            $this->processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber);
//
//        } catch (\Exception $e) {
//            Log::error("❌ Error processing User #{$user->id}: " . $e->getMessage());
//            $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $e->getMessage());
//        }
//    }
//
//    /**
//     * Pre-fetch ALL required templates and promo codes outside the frequency loop
//     */
//    private function preFetchAllTemplatesAndPromoCodes(array $configData): array
//    {
//        $allTemplateIds = [
//            'email' => [],
//            'whatsapp' => []
//        ];
//        $allPromoCodeIds = [];
//
//        // Collect all template IDs and promo code IDs from all frequencies
//        foreach ($configData as $frequency) {
//            // Email templates and promo codes
//            if (isset($frequency['email']['enable']) && $frequency['email']['enable']) {
//                $emailTemplateId = $frequency['email']['config']['template'] ?? null;
//                if ($emailTemplateId) {
//                    $allTemplateIds['email'][] = $emailTemplateId;
//                }
//
//                // Collect email promo code IDs
//                $emailPromoCodeId = $frequency['email']['config']['promo_code'] ?? null;
//                if ($emailPromoCodeId) {
//                    $allPromoCodeIds[] = $emailPromoCodeId;
//                }
//            }
//
//            // WhatsApp templates
//            if (isset($frequency['wp']['enable']) && $frequency['wp']['enable']) {
//                $wpConfig = $frequency['wp']['config'] ?? [];
//
//                // Check all template types for WhatsApp
//                $templateTypes = ['offer', 'subscription', 'templates'];
//                foreach ($templateTypes as $type) {
//                    $templateId = $wpConfig[$type]['template'] ?? null;
//                    if ($templateId) {
//                        $allTemplateIds['whatsapp'][] = $templateId;
//                    }
//
//                    // Also collect promo code IDs
//                    $promoCodeId = $wpConfig[$type]['promocode'] ?? null;
//                    if ($promoCodeId) {
//                        $allPromoCodeIds[] = $promoCodeId;
//                    }
//                }
//            }
//        }
//
//        // Remove duplicates
//        $allTemplateIds['email'] = array_unique($allTemplateIds['email']);
//        $allTemplateIds['whatsapp'] = array_unique($allTemplateIds['whatsapp']);
//        $allPromoCodeIds = array_unique($allPromoCodeIds);
//
//        Log::info("📋 Pre-fetching templates - Email: " . count($allTemplateIds['email']) .
//            ", WhatsApp: " . count($allTemplateIds['whatsapp']) .
//            ", PromoCodes: " . count($allPromoCodeIds));
//
//        // Fetch all data in single queries
//        $emailTemplates = EmailTemplate::whereIn('id', $allTemplateIds['email'])->get()->keyBy('id');
//        $whatsappTemplates = WhatsappTemplate::whereIn('id', $allTemplateIds['whatsapp'])->get()->keyBy('id');
//        $promoCodes = PromoCode::whereIn('id', $allPromoCodeIds)->get()->keyBy('id');
//
//        return [
//            'emailTemplates' => $emailTemplates,
//            'whatsappTemplates' => $whatsappTemplates,
//            'promoCodes' => $promoCodes
//        ];
//    }
//
//    private function prepareCommonDataForUser($order, $user): array
//    {
//        $planType = $order->type;
//        $currency = $order->currency;
//
//        $commonData['userData'] = [
//            'name' => $user->name,
//            'email' => $user->email,
//            'password' => "",
//        ];
//
//        if (in_array($planType, ['template', 'video'])) {
//            // Use the designs relationship
//            $designs = $order->designs;
//            if ($designs->isEmpty()) {
//                return ['success' => false, 'message' => "No designs found for template/video order {$order->id}"];
//            }
//
//            $newArray = [];
//            $paymentProps = [];
//
//            foreach ($designs as $design) {
//                $paymentProps[] = ["id" => $design->string_id, "type" => 0];
//                $newArray[] = [
//                    "title" => $design->post_name,
//                    "image" => HelperController::generatePublicUrl($design->post_thumb),
//                    "width" => $design->width,
//                    "height" => $design->height,
//                    "amount" => $currency == "INR" ? $design->inrAmount : $design->usdAmount,
//                    "link" => HelperController::getFrontendPageUrl(0, $design->id_name),
//                ];
//            }
//
//            $paymentLink = HelperController::$frontendUrl . "/redirect/" . AutomationUtils::generateBuyLink($paymentProps);
//
//            $commonData['type'] = "template";
//            $commonData['data'] = [
//                "templates" => $newArray,
//                "amount" => ($currency == "INR" ? "₹" : "$") . $order->amount,
//            ];
//            $commonData['link'] = $paymentLink;
//            $commonData['paymentLink'] = $paymentLink;
//            $commonData['planType'] = $planType;
//            $commonData['paymentProps'] = $paymentProps;
//
//        } elseif ($planType === 'new_sub') {
//            // Use the subPlan relationship
//            $plan = $order->subPlan;
//            if (!$plan) {
//                return ['success' => false, 'message' => "New SubPlan not found for order {$order->id}"];
//            }
//
//            $paymentLink = HelperController::$frontendUrl . "payment/redirect=https://editor.craftyartapp.com/payment";
//
//            $commonData['type'] = "plan";
//            $commonData['data'] = AutomationUtils::formatNewPlanData($plan, $currency);
//            $commonData['link'] = "https://editor.craftyartapp.com/payment";
//            $commonData['paymentLink'] = $paymentLink;
//            $commonData['plan'] = $plan;
//            $commonData['planType'] = $planType;
//
//        } elseif ($planType === 'old_sub' || $planType === 'offer') {
//            // Use the subscription relationship
//            $plan = $order->subscription;
//            if (!$plan) {
//                return ['success' => false, 'message' => "Subscription not found for order {$order->id}"];
//            }
//
//            if (in_array($plan->id, [23, 24, 26])) {
//                $paymentLink = "https://www.craftyartapp.com/offer-package";
//            } else {
//                $paymentLink = HelperController::$frontendUrl . "payment/redirect=https://editor.craftyartapp.com/payment";
//            }
//
//            $commonData['type'] = "plan";
//            $commonData['data'] = AutomationUtils::formatOldPlanData($plan, $currency);
//            $commonData['link'] = in_array($plan->id, [23, 24, 26])
//                ? "https://www.craftyartapp.com/offer-package"
//                : "https://editor.craftyartapp.com/payment";
//            $commonData['paymentLink'] = $paymentLink;
//            $commonData['plan'] = $plan;
//            $commonData['planType'] = $planType;
//        } else {
//            return ['success' => false, 'message' => "Invalid plan type provided for order {$order->id}"];
//        }
//
//        return $commonData;
//    }
//
//    private function handleAutomationForJob($frequencyConfig, $user, $commonData, $contactNumber, $allTemplateData): array
//    {
//        $results = [];
//
//        // Handle Email Automation (if enabled)
//        if ($frequencyConfig['email']['enable'] ?? false) {
//            $emailConfig = $frequencyConfig['email']['config'] ?? [];
//            $results['email'] = $this->sendEmailFromConfig($emailConfig, $user, $commonData, $allTemplateData['emailTemplates'], $allTemplateData['promoCodes']);
//        }
//
//        // Handle WhatsApp Automation (if enabled)
//        if ($frequencyConfig['wp']['enable'] ?? false) {
//            $wpConfig = $frequencyConfig['wp']['config'] ?? [];
//            $results['whatsapp'] = $this->sendWhatsAppFromConfig(
//                $wpConfig,
//                $user,
//                $commonData,
//                $contactNumber,
//                ConfigType::CHECKOUT_DROP_AUTOMATION->value,
//                $allTemplateData['promoCodes'],
//                $allTemplateData['whatsappTemplates']
//            );
//        }
//
//        return $results;
//    }
//
//    /**
//     * Send WhatsApp from config with pre-fetched promo codes
//     */
//    private function sendWhatsAppFromConfig($wpConfig, $user, $commonData, $contactNumber, $type, $promoCodes, $whatsappTemplates): array
//    {
//        if (!$contactNumber) {
//            return ['success' => false, 'message' => "Contact number not found"];
//        }
//
//        // Determine which template config to use based on plan type
//        $templateConfig = null;
//        if ($commonData['planType'] === 'offer') {
//            $templateConfig = $wpConfig['offer'] ?? null;
//        } elseif (in_array($commonData['planType'], ['new_sub', 'old_sub'])) {
//            $templateConfig = $wpConfig['subscription'] ?? null;
//        } elseif (in_array($commonData['planType'], ['template', 'video'])) {
//            $templateConfig = $wpConfig['templates'] ?? null;
//        }
//
//        if (!$templateConfig || !$templateConfig['template']) {
//            return ['success' => false, 'message' => "WhatsApp template not defined for plan type: {$commonData['planType']}"];
//        }
//
//        // Use pre-fetched template
//        $whatsappTemplate = $whatsappTemplates[$templateConfig['template']] ?? null;
//        if (!$whatsappTemplate) {
//            return ['success' => false, 'message' => "WhatsApp Template not found"];
//        }
//
//        $templateParams = $this->prepareWhatsAppParams($user, $commonData, $templateConfig, $type, $promoCodes);
//
//        $count = (int)$whatsappTemplate->template_params_count;
//
//        if ($count != count($templateParams)) {
//            return ['success' => false, 'message' => "WhatsApp Template parameter count mismatch"];
//        }
//
//        // Prepare dynamic button with payment link
//        $dynamicButton = $this->prepareWhatsAppButton($commonData, $type);
//
//        // Send WhatsApp message
////        $result = WhatsAppService::sendTemplateMessage(
////            $whatsappTemplate->campaign_name,
////            $user->name,
////            $contactNumber,
////            $templateParams,
////            $dynamicButton
////        );
////
////        return $this->handleWhatsAppResponse($result, $templateParams);
//        $result = [
//            "success"=> true,
//            "message"=>"Generated",
//            "campaign_name"=> $whatsappTemplate->campaign_name,
//            "name"=> $user->name,
//            "contact_number" => $contactNumber,
//            "template_params" => $templateParams,
//            "dynamic_button" => $dynamicButton
//        ];
//        return $result;
//    }
//
//    /**
//     * Send Email from config with pre-fetched templates
//     */
//    private function sendEmailFromConfig($emailConfig, $user, $commonData, $emailTemplates, $promoCodes): array
//    {
//        $emailTemplateId = $emailConfig['template'] ?? null;
//        if (!$emailTemplateId) {
//            return ['success' => false, 'message' => "Email template not defined in config"];
//        }
//
//        // Use pre-fetched template
//        $emailTemplate = $emailTemplates[$emailTemplateId] ?? null;
//        if (!$emailTemplate) {
//            return ['success' => false, 'message' => "Email Template not found"];
//        }
//
//        // Add promo object to email data if available
//        $promoObject = null;
//        if ($emailConfig['promo_code'] ?? false) {
//            $promoCodeId = $emailConfig['promo_code'];
//            if (isset($promoCodes[$promoCodeId])) {
//                $promo = $promoCodes[$promoCodeId];
//                $promoObject = [
//                    'code' => $promo->promo_code,
//                    'disc' => $promo->disc
//                ];
//            }
//        }
//
//        $emailData = [
//            'userData' => $commonData['userData'],
//            'type' => $commonData['type'],
//            'data' => $commonData['data'],
//            'link' => $commonData['link'],
//            'promo' => $promoObject // Add promo object to email data
//        ];
//
//        $htmlBody = View::make($emailTemplate->email_template, [
//            'data' => $emailData
//        ])->render();
//
//        $result = EmailTemplateController::sendEmail($user->email, $emailConfig['subject'] ?? '', $htmlBody);
//
//        if (str_contains($result, "successfully")) {
//            return ['success' => true, 'message' => 'Email Sent Successfully'];
//        }
//
//        return ['success' => false, 'message' => $result];
//    }
//
//    /**
//     * Prepare WhatsApp parameters with pre-fetched promo codes
//     */
//    private function prepareWhatsAppParams($user, $commonData, $templateConfig, $type, $promoCodes): array
//    {
//        $name = $user->name;
//        $promoCode = "";
//        $promoDiscount = "";
//
//        if($type == ConfigType::OFFER_PURCHASE_AUTOMATION->value){
//            return [
//                $name,
//                $user->email
//            ];
//        }
//
//        if ($templateConfig['promocode'] ?? false) {
//            $promoCodeId = $templateConfig['promocode'];
//            if (isset($promoCodes[$promoCodeId])) {
//                $promo = $promoCodes[$promoCodeId];
//                $promoCode = $promo->promo_code;
//                $promoDiscount = $promo->disc . "%";
//            }
//        }
//
//        if (in_array($commonData['planType'], ['template', 'video'])) {
//            $firstTemplate = $commonData['data']['templates'][0] ?? null;
//            if (!$firstTemplate) {
//                return ['success' => false, 'message' => "No design found in plan"];
//            }
//
//            return [
//                $name,
//                $commonData['data']['amount'],
//                $promoCode,
//                $promoDiscount,
//                $commonData['paymentLink']
//            ];
//
//        } elseif ($commonData['planType'] === 'new_sub') {
//            return [
//                $name,
//                $commonData['data']['offer_price'],
//                $commonData['data']['actual_price'],
//                $promoCode,
//                $promoDiscount,
//                $commonData['paymentLink']
//            ];
//
//        } elseif (in_array($commonData['planType'], ['old_sub', 'offer'])) {
//            if ($commonData['planType'] === "offer") {
//                return [
//                    $name,
//                    $commonData['data']['offer_price'],
//                    $commonData['data']['actual_price'],
//                    $commonData['paymentLink']
//                ];
//            } else {
//                return [
//                    $name,
//                    $commonData['data']['offer_price'],
//                    $commonData['data']['actual_price'],
//                    $promoCode,
//                    $promoDiscount,
//                    $commonData['paymentLink']
//                ];
//            }
//        }
//
//        return ['success' => false, 'message' => "Invalid plan type for WhatsApp parameters"];
//    }
//
//    private function processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber): void
//    {
//        foreach ($results as $channel => $result) {
//            if ($result['success']) {
//                Log::info("✅ {$channel} sent successfully to User #{$user->id}");
//                if ($channel === 'email' && $emailLog) {
//                    $emailLog->increment('sent');
//                } elseif ($channel === 'whatsapp' && $whatsappLog) {
//                    $whatsappLog->increment('sent');
//                }
//            } else {
//                Log::error("❌ Failed to send {$channel} to User #{$user->id}: {$result['message']}");
//                if ($channel === 'email' && $emailLog) {
//                    $emailLog->increment('failed');
//                    $this->logFailedEmail($emailLog, $user->id, $user->email, "{$channel}: {$result['message']}");
//                } elseif ($channel === 'whatsapp' && $whatsappLog) {
//                    $whatsappLog->increment('failed');
//                    $this->logFailedWhatsApp($whatsappLog, $user->id, $contactNumber, "{$channel}: {$result['message']}");
//                }
//            }
//        }
//    }
//
//    private function logFailedCommunication($emailLog, $whatsappLog, $userId, $email, $error): void
//    {
//        // Log failure only for enabled channels
//        if ($emailLog) {
//            $emailLog->increment('failed');
//            $this->logFailedEmail($emailLog, $userId, $email, $error);
//        }
//
//        if ($whatsappLog) {
//            $whatsappLog->increment('failed');
//            $this->logFailedWhatsApp($whatsappLog, $userId, null, $error);
//        }
//    }
//
//    private function logFailedEmail($emailLog, $userId, $email, $error): void
//    {
//        AutomationSendDetail::create([
//            'log_id' => $emailLog->id,
//            'user_id' => $userId,
//            'type' => AutomationType::EMAIL->value,
//            'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value,
//            'email' => $email,
//            'contact_number' => null,
//            'status' => 'failed',
//            'error_message' => $error,
//        ]);
//    }
//
//    private function logFailedWhatsApp($whatsappLog, $userId, $contactNumber, $error): void
//    {
//        AutomationSendDetail::create([
//            'log_id' => $whatsappLog->id,
//            'user_id' => $userId,
//            'type' => AutomationType::WHATSAPP->value,
//            'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value,
//            'email' => null,
//            'contact_number' => $contactNumber,
//            'status' => 'failed',
//            'error_message' => $error,
//        ]);
//    }
//
//    private function prepareWhatsAppButton($commonData, $type): array
//    {
//        if($type == ConfigType::OFFER_PURCHASE_AUTOMATION->value){
//            return [];
//        }
//
//        $buttonText = $commonData['paymentLink'];
//
//        if (in_array($commonData['planType'], ['template', 'video'])) {
//            $buttonText = "/redirect/" . $commonData['paymentLink'];
//        } elseif ($commonData['planType'] === 'new_sub') {
//            $buttonText = "/redirect/" . $commonData['paymentLink'];
//        } elseif ($commonData['planType'] === 'old_sub') {
//            if (str_contains($commonData['paymentLink'], 'offer-package')) {
//                $buttonText = $commonData['paymentLink'];
//            } else {
//                $buttonText = "/payment/redirect=" . $commonData['paymentLink'];
//            }
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
//                        "text" => $buttonText
//                    ]
//                ],
//            ]
//        ];
//    }
//
//    private function handleWhatsAppResponse($result, $templateParams = []): array
//    {
//        if (is_array($result)) {
//            $status = $result['status'] ?? false;
//            $message = $result['message'] ?? 'Something went wrong';
//        } else {
//            $status = $result->status ?? false;
//            $message = $status ? 'Message Sent Successfully' : ($result->message ?? 'Something went wrong');
//        }
//
//        return [
//            'success' => $status,
//            'message' => $message,
//        ];
//    }
//}

//class ProcessCheckoutDropJob implements ShouldQueue
//{
//    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
//
//    public function handle(): void
//    {
//        Log::info("🚀 Checkout Drop Job Started");
//
//        // Fetch configuration
//        $config = Config::where('name', ConfigType::CHECKOUT_DROP_AUTOMATION->label())->first();
//        if (!$config) {
//            Log::warning("❌ CheckoutDropJob: Config not found!");
//            return;
//        }
//
//        $configData = $config->value;
//        if (!is_array($configData) || empty($configData)) {
//            Log::warning("❌ CheckoutDropJob: Invalid config data!");
//            return;
//        }
//
//        Log::info("📊 Processing " . count($configData) . " frequency configurations");
//
//        $now = Carbon::now();
//
//        // Step 1: Collect ALL unique user data across all frequencies
//        $allUserData = $this->collectAllUserData($configData, $now);
//        if (empty($allUserData['allUsers'])) {
//            Log::info("👥 No users found across all frequencies");
//            return;
//        }
//
//        // Step 2: Pre-fetch ALL required data outside loops
//        $allTemplateData = $this->preFetchAllTemplatesAndPromoCodes($configData);
//        $allEntityData = $this->preFetchAllEntityData($allUserData);
//
//        Log::info("🎯 Starting to process frequencies");
//
//        foreach ($configData as $frequencyIndex => $frequency) {
//            $days = (int)($frequency['day'] ?? 0);
//
//            // Skip day 0 as per requirement
//            if ($days === 0) {
//                Log::info("⏭️ Skipping day 0 configuration");
//                continue;
//            }
//
//            Log::info("🔁 Processing frequency #{$frequencyIndex} for day: {$days}");
//
//            $targetDate = $now->copy()->subDays($days)->toDateString();
//            Log::info("📅 Target date for {$days} days ago: {$targetDate}");
//
//            // Get users for this specific frequency
//            $frequencyUsers = $allUserData['usersByDay'][$days] ?? collect();
//            if ($frequencyUsers->isEmpty()) {
//                Log::info("👤 No users found for day {$days} on {$targetDate}");
//                continue;
//            }
//
//            Log::info("📊 Total users found for day {$days}: " . $frequencyUsers->count());
//
//            // Convert user IDs to string array format ["7", "8", ...]
//            $userIds = $frequencyUsers->pluck('user.id')->map(fn($id) => (string)$id)->toArray();
//            $campaignName = "Checkout Drop - Day {$days} - {$targetDate}";
//
//            // Create logs only for enabled channels
//            $emailLog = null;
//            $whatsappLog = null;
//
//            if ($frequency['email']['enable'] ?? false) {
//                $emailLog = $this->createAutomationLog($campaignName, $userIds, AutomationType::EMAIL);
//                Log::info("📧 Email Automation Log created: #{$emailLog->id}");
//            } else {
//                Log::info("📧 Email automation disabled for day {$days}");
//            }
//
//            if ($frequency['wp']['enable'] ?? false) {
//                $whatsappLog = $this->createAutomationLog($campaignName, $userIds, AutomationType::WHATSAPP);
//                Log::info("💬 WhatsApp Automation Log created: #{$whatsappLog->id}");
//            } else {
//                Log::info("💬 WhatsApp automation disabled for day {$days}");
//            }
//
//            // Skip processing if both channels are disabled
//            if (!$emailLog && !$whatsappLog) {
//                Log::info("⏭️ Both email and WhatsApp disabled for day {$days}, skipping processing");
//                continue;
//            }
//
//            // Process users in chunks
//            $frequencyUsers->chunk(100)->each(function ($chunk, $chunkIndex) use (
//                $frequency,
//                $emailLog,
//                $whatsappLog,
//                $allEntityData,
//                $allTemplateData,
//                $days
//            ) {
//                Log::info("🔄 Processing chunk #{$chunkIndex} with " . $chunk->count() . " users for day {$days}");
//
//                foreach ($chunk as $orderUser) {
//                    $this->processSingleUser(
//                        $orderUser,
//                        $frequency,
//                        $emailLog,
//                        $whatsappLog,
//                        $allEntityData,
//                        $allTemplateData
//                    );
//                    sleep(2); // Rate limiting
//                }
//            });
//
//            // Mark logs as completed only if they exist
//            if ($emailLog) {
//                $emailLog->update(['status' => 'completed']);
//                Log::info("✅ Email Log #{$emailLog->id} completed - Sent: {$emailLog->sent}, Failed: {$emailLog->failed}");
//            }
//
//            if ($whatsappLog) {
//                $whatsappLog->update(['status' => 'completed']);
//                Log::info("✅ WhatsApp Log #{$whatsappLog->id} completed - Sent: {$whatsappLog->sent}, Failed: {$whatsappLog->failed}");
//            }
//        }
//
//        Log::info("🎉 Checkout Drop Job Finished Successfully");
//    }
//
//    /**
//     * Create automation log entry
//     */
//    private function createAutomationLog(string $campaignName, array $userIds, AutomationType $type): AutomationSendLog
//    {
//        // Ensure user_ids are properly formatted as strings and JSON is correctly encoded
//        $userIdsJson = json_encode($userIds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
//
//        Log::info("📝 Creating log for {$type->value} with user_ids: {$userIdsJson}");
//
//        return AutomationSendLog::create([
//            'campaign_name' => $campaignName,
//            'user_ids' => $userIdsJson,
//            'select_users_type' => 3,
//            'total' => count($userIds),
//            'sent' => 0,
//            'failed' => 0,
//            'status' => 'processing',
//            'type' => $type->value,
//            'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value
//        ]);
//    }
//
//
//    /**
//     * Process single user for both email and WhatsApp
//     */
//    private function processSingleUser(
//        $orderUser,
//        $frequency,
//        $emailLog,
//        $whatsappLog,
//        $allEntityData,
//        $allTemplateData
//    ): void {
//        $user = $orderUser->user;
//        if (!$user) {
//            Log::warning("⚠️ Skipping Order #{$orderUser->id} due to missing user relation");
//            $this->logFailedCommunication($emailLog, $whatsappLog, null, null, "Missing user for Order {$orderUser->id}");
//            return;
//        }
//
//        Log::info("👤 Processing User #{$user->id}, Email: {$user->email}, PlanType: {$orderUser->type}");
//
//        try {
//            // Prepare common data using pre-fetched entity data
//            $commonData = $this->prepareCommonDataForUser($orderUser, $user, $allEntityData);
//
//            if (isset($commonData['success']) && !$commonData['success']) {
//                $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $commonData['message']);
//                return;
//            }
//
//            $contactNumber = !empty($orderUser->contact_no)
//                ? $orderUser->contact_no
//                : (!empty($user->number) ? $user->country_code . $user->number : null);
//
//            // Handle both email and WhatsApp with pre-fetched templates
//            $results = $this->handleAutomationForJob($frequency, $user, $commonData, $contactNumber, $allTemplateData);
//
//            // Process results and update logs (only for enabled channels)
//            $this->processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber);
//
//        } catch (\Exception $e) {
//            Log::error("❌ Error processing User #{$user->id}: " . $e->getMessage());
//            $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $e->getMessage());
//        }
//    }
//
//    /**
//     * Collect ALL user data across all frequencies in single queries
//     */
//    private function collectAllUserData(array $configData, Carbon $now): array
//    {
//        $allUsers = collect();
//        $usersByDay = [];
//        $allNewSubIds = [];
//        $allOldSubIds = [];
//        $allTemplateIds = [];
//
//        foreach ($configData as $frequency) {
//            $days = (int)($frequency['day'] ?? 0);
//            if ($days === 0) continue;
//
//            $targetDate = $now->copy()->subDays($days)->toDateString();
//
//            $users = Order::with(['user.transactionLogs' => function ($q) {
//                $q->latest('id')->limit(1);
//            }])
//                ->whereDate('created_at', $targetDate)
//                ->where('status', 'failed')
//                ->whereHas('user')
//                ->where(function ($q) {
//                    $q->whereDoesntHave('user.transactionLogs')
//                        ->orWhereHas('user.transactionLogs', function ($q2) {
//                            $q2->where('expired_at', '<', now());
//                        });
//                })
//                ->get();
//
//            if ($users->isEmpty()) {
//                continue;
//            }
//
//            $usersByDay[$days] = $users;
//            $allUsers = $allUsers->merge($users);
//
//            // Collect all unique IDs
//            $allNewSubIds = array_merge($allNewSubIds, $users->where('type', 'new_sub')->pluck('plan_id')->filter()->unique()->toArray());
//            $allOldSubIds = array_merge(
//                $allOldSubIds,
//                $users->filter(function ($user) {
//                    return in_array($user->type, ['old_sub', 'offer']);
//                })->pluck('plan_id')->filter()->unique()->toArray()
//            );
//
//            // Collect all template/video design IDs
//            $templateVideoUsers = $users->whereIn('type', ['template', 'video']);
//            foreach ($templateVideoUsers as $user) {
//                $templateData = json_decode($user->plan_id, true);
//                if (is_array($templateData)) {
//                    $ids = collect($templateData)->pluck('id')->filter()->toArray();
//                    $allTemplateIds = array_merge($allTemplateIds, $ids);
//                }
//            }
//        }
//
//        // Remove duplicates
//        $allNewSubIds = array_unique($allNewSubIds);
//        $allOldSubIds = array_unique($allOldSubIds);
//        $allTemplateIds = array_unique($allTemplateIds);
//
//        Log::info("📦 Collected all user data - TotalUsers: " . $allUsers->count() .
//            ", NewSubs: " . count($allNewSubIds) .
//            ", OldSubs: " . count($allOldSubIds) .
//            ", Templates: " . count($allTemplateIds));
//
//        return [
//            'allUsers' => $allUsers,
//            'usersByDay' => $usersByDay,
//            'allNewSubIds' => $allNewSubIds,
//            'allOldSubIds' => $allOldSubIds,
//            'allTemplateIds' => $allTemplateIds
//        ];
//    }
//
//    /**
//     * Pre-fetch ALL entity data (Subscriptions, Plans, Designs) in single queries
//     */
//    private function preFetchAllEntityData(array $allUserData): array
//    {
//        $allNewSubIds = $allUserData['allNewSubIds'];
//        $allOldSubIds = $allUserData['allOldSubIds'];
//        $allTemplateIds = $allUserData['allTemplateIds'];
//
//        Log::info("🔄 Pre-fetching all entity data - NewSubs: " . count($allNewSubIds) .
//            ", OldSubs: " . count($allOldSubIds) .
//            ", Templates: " . count($allTemplateIds));
//
//        // Fetch all data in single queries
//        $subPlans = SubPlan::whereIn('id', $allNewSubIds)
//            ->orWhereIn('string_id', $allNewSubIds)
//            ->get()
//            ->keyBy(fn($plan) => $plan->string_id ?? $plan->id);
//
//        $subscriptions = Subscription::whereIn('id', $allOldSubIds)
//            ->get()
//            ->keyBy('id');
//
//        $designs = Design::whereIn('string_id', $allTemplateIds)
//            ->get()
//            ->keyBy('string_id');
//
//        Log::info("✅ Pre-fetched entity data - SubPlans: " . $subPlans->count() .
//            ", Subscriptions: " . $subscriptions->count() .
//            ", Designs: " . $designs->count());
//
//        return [
//            'subPlans' => $subPlans,
//            'subscriptions' => $subscriptions,
//            'designs' => $designs
//        ];
//    }
//
//    /**
//     * Pre-fetch ALL required templates and promo codes outside the frequency loop
//     */
//    private function preFetchAllTemplatesAndPromoCodes(array $configData): array
//    {
//        $allTemplateIds = [
//            'email' => [],
//            'whatsapp' => []
//        ];
//        $allPromoCodeIds = [];
//
//        // Collect all template IDs and promo code IDs from all frequencies
//        foreach ($configData as $frequency) {
//            // Email templates and promo codes
//            if (isset($frequency['email']['enable']) && $frequency['email']['enable']) {
//                $emailTemplateId = $frequency['email']['config']['template'] ?? null;
//                if ($emailTemplateId) {
//                    $allTemplateIds['email'][] = $emailTemplateId;
//                }
//
//                // Collect email promo code IDs
//                $emailPromoCodeId = $frequency['email']['config']['promo_code'] ?? null;
//                if ($emailPromoCodeId) {
//                    $allPromoCodeIds[] = $emailPromoCodeId;
//                }
//            }
//
//            // WhatsApp templates
//            if (isset($frequency['wp']['enable']) && $frequency['wp']['enable']) {
//                $wpConfig = $frequency['wp']['config'] ?? [];
//
//                // Check all template types for WhatsApp
//                $templateTypes = ['offer', 'subscription', 'templates'];
//                foreach ($templateTypes as $type) {
//                    $templateId = $wpConfig[$type]['template'] ?? null;
//                    if ($templateId) {
//                        $allTemplateIds['whatsapp'][] = $templateId;
//                    }
//
//                    // Also collect promo code IDs
//                    $promoCodeId = $wpConfig[$type]['promocode'] ?? null;
//                    if ($promoCodeId) {
//                        $allPromoCodeIds[] = $promoCodeId;
//                    }
//                }
//            }
//        }
//
//        // Remove duplicates
//        $allTemplateIds['email'] = array_unique($allTemplateIds['email']);
//        $allTemplateIds['whatsapp'] = array_unique($allTemplateIds['whatsapp']);
//        $allPromoCodeIds = array_unique($allPromoCodeIds);
//
//        Log::info("📋 Pre-fetching templates - Email: " . count($allTemplateIds['email']) .
//            ", WhatsApp: " . count($allTemplateIds['whatsapp']) .
//            ", PromoCodes: " . count($allPromoCodeIds));
//
//        // Fetch all data in single queries
//        $emailTemplates = EmailTemplate::whereIn('id', $allTemplateIds['email'])->get()->keyBy('id');
//        $whatsappTemplates = WhatsappTemplate::whereIn('id', $allTemplateIds['whatsapp'])->get()->keyBy('id');
//        $promoCodes = PromoCode::whereIn('id', $allPromoCodeIds)->get()->keyBy('id');
//
//        return [
//            'emailTemplates' => $emailTemplates,
//            'whatsappTemplates' => $whatsappTemplates,
//            'promoCodes' => $promoCodes
//        ];
//    }
//
//    private function prepareCommonDataForUser($orderUser, $user, array $allEntityData): array
//    {
//        $planType = $orderUser->type;
//        $planId = $orderUser->plan_id;
//        $currency = $orderUser->currency;
//
//        $commonData['userData'] = [
//            'name' => $user->name,
//            'email' => $user->email,
//            'password' => "",
//        ];
//
//        if (in_array($planType, ['template', 'video'])) {
//            $templateData = json_decode($planId, true);
//            if (!is_array($templateData)) {
//                return ['success' => false, 'message' => "Invalid template data for user {$user->id}"];
//            }
//
//            $newArray = [];
//            $paymentProps = [];
//
//            foreach ($templateData as $item) {
//                if (isset($allEntityData['designs'][$item['id']])) {
//                    $design = $allEntityData['designs'][$item['id']];
//                    $paymentProps[] = ["id" => $item['id'], "type" => 0];
//                    $newArray[] = [
//                        "title" => $design->post_name,
//                        "image" => HelperController::generatePublicUrl($design->post_thumb),
//                        "width" => $design->width,
//                        "height" => $design->height,
//                        "amount" => $currency == "INR" ? $item['inrAmount'] : $item['usdAmount'],
//                        "link" => HelperController::getFrontendPageUrl(0, $design->id_name),
//                    ];
//                }
//            }
//
//            if (empty($newArray)) {
//                return ['success' => false, 'message' => "No designs found for template/video user {$user->id}"];
//            }
//
//            $paymentLink = HelperController::$frontendUrl . "/redirect/" . AutomationUtils::generateBuyLink($paymentProps);
//
//            $commonData['type'] = "template";
//            $commonData['data'] = [
//                "templates" => $newArray,
//                "amount" => ($currency == "INR" ? "₹" : "$") . $orderUser->amount,
//            ];
//            $commonData['link'] = $paymentLink;
//            $commonData['paymentLink'] = $paymentLink;
//            $commonData['planType'] = $planType;
//            $commonData['paymentProps'] = $paymentProps;
//
//        } elseif ($planType === 'new_sub') {
//            $plan = $allEntityData['subPlans'][$planId] ?? null;
//            if (!$plan) {
//                return ['success' => false, 'message' => "New SubPlan not found for user {$user->id}"];
//            }
//
//            $paymentLink = HelperController::$frontendUrl . "payment/redirect=https://editor.craftyartapp.com/payment";
//
//            $commonData['type'] = "plan";
//            $commonData['data'] = AutomationUtils::formatNewPlanData($plan, $currency);
//            $commonData['link'] = "https://editor.craftyartapp.com/payment";
//            $commonData['paymentLink'] = $paymentLink;
//            $commonData['plan'] = $plan;
//            $commonData['planType'] = $planType;
//
//        } elseif ($planType === 'old_sub' || $planType === 'offer') {
//            $plan = $allEntityData['subscriptions'][$planId] ?? null;
//            if (!$plan) {
//                return ['success' => false, 'message' => "Subscription not found for user {$user->id}"];
//            }
//
//            if (in_array($planId, [23, 24, 26])) {
//                $paymentLink = "https://www.craftyartapp.com/offer-package";
//            } else {
//                $paymentLink = HelperController::$frontendUrl . "payment/redirect=https://editor.craftyartapp.com/payment";
//            }
//
//            $commonData['type'] = "plan";
//            $commonData['data'] = AutomationUtils::formatOldPlanData($plan, $currency);
//            $commonData['link'] = in_array($planId, [23, 24, 26])
//                ? "https://www.craftyartapp.com/offer-package"
//                : "https://editor.craftyartapp.com/payment";
//            $commonData['paymentLink'] = $paymentLink;
//            $commonData['plan'] = $plan;
//            $commonData['planType'] = $planType;
//        } else {
//            return ['success' => false, 'message' => "Invalid plan type provided for user {$user->id}"];
//        }
//
//        return $commonData;
//    }
//
//    private function handleAutomationForJob($frequencyConfig, $user, $commonData, $contactNumber, $allTemplateData): array
//    {
//        $results = [];
//
//        // Handle Email Automation (if enabled)
//        if ($frequencyConfig['email']['enable'] ?? false) {
//            $emailConfig = $frequencyConfig['email']['config'] ?? [];
//            $results['email'] = $this->sendEmailFromConfig($emailConfig, $user, $commonData, $allTemplateData['emailTemplates'], $allTemplateData['promoCodes']);
//        }
//
//        // Handle WhatsApp Automation (if enabled)
//        if ($frequencyConfig['wp']['enable'] ?? false) {
//            $wpConfig = $frequencyConfig['wp']['config'] ?? [];
//            $results['whatsapp'] = $this->sendWhatsAppFromConfig(
//                $wpConfig,
//                $user,
//                $commonData,
//                $contactNumber,
//                ConfigType::CHECKOUT_DROP_AUTOMATION->value,
//                $allTemplateData['promoCodes'],
//                $allTemplateData['whatsappTemplates']
//            );
//        }
//
//        return $results;
//    }
//
//    /**
//     * Send WhatsApp from config with pre-fetched promo codes
//     */
//    private function sendWhatsAppFromConfig($wpConfig, $user, $commonData, $contactNumber, $type, $promoCodes, $whatsappTemplates): array
//    {
//        if (!$contactNumber) {
//            return ['success' => false, 'message' => "Contact number not found"];
//        }
//
//        // Determine which template config to use based on plan type
//        $templateConfig = null;
//        if ($commonData['planType'] === 'offer') {
//            $templateConfig = $wpConfig['offer'] ?? null;
//        } elseif (in_array($commonData['planType'], ['new_sub', 'old_sub'])) {
//            $templateConfig = $wpConfig['subscription'] ?? null;
//        } elseif (in_array($commonData['planType'], ['template', 'video'])) {
//            $templateConfig = $wpConfig['templates'] ?? null;
//        }
//
//        if (!$templateConfig || !$templateConfig['template']) {
//            return ['success' => false, 'message' => "WhatsApp template not defined for plan type: {$commonData['planType']}"];
//        }
//
//        // Use pre-fetched template
//        $whatsappTemplate = $whatsappTemplates[$templateConfig['template']] ?? null;
//        if (!$whatsappTemplate) {
//            return ['success' => false, 'message' => "WhatsApp Template not found"];
//        }
//
//        $templateParams = $this->prepareWhatsAppParams($user, $commonData, $templateConfig, $type, $promoCodes);
//
//        $count = (int)$whatsappTemplate->template_params_count;
//
//        if ($count != count($templateParams)) {
//            return ['success' => false, 'message' => "WhatsApp Template parameter count mismatch"];
//        }
//
//        // Prepare dynamic button with payment link
//        $dynamicButton = $this->prepareWhatsAppButton($commonData, $type);
//
//        // Send WhatsApp message
////        $result = WhatsAppService::sendTemplateMessage(
////            $whatsappTemplate->campaign_name,
////            $user->name,
////            $contactNumber,
////            $templateParams,
////            $dynamicButton
////        );
////
////        return $this->handleWhatsAppResponse($result, $templateParams);
//        $result = [
//            "success"=> true,
//            "message"=>"Generated",
//            "campaign_name"=> $whatsappTemplate->campaign_name,
//            "name"=> $user->name,
//            "contact_number" => $contactNumber,
//            "template_params" => $templateParams,
//            "dynamic_button" => $dynamicButton
//        ];
//        return $result;
//    }
//
//    /**
//     * Send Email from config with pre-fetched templates
//     */
//    private function sendEmailFromConfig($emailConfig, $user, $commonData, $emailTemplates, $promoCodes): array
//    {
//        $emailTemplateId = $emailConfig['template'] ?? null;
//        if (!$emailTemplateId) {
//            return ['success' => false, 'message' => "Email template not defined in config"];
//        }
//
//        // Use pre-fetched template
//        $emailTemplate = $emailTemplates[$emailTemplateId] ?? null;
//        if (!$emailTemplate) {
//            return ['success' => false, 'message' => "Email Template not found"];
//        }
//
//        // Add promo object to email data if available
//        $promoObject = null;
//        if ($emailConfig['promo_code'] ?? false) {
//            $promoCodeId = $emailConfig['promo_code'];
//            if (isset($promoCodes[$promoCodeId])) {
//                $promo = $promoCodes[$promoCodeId];
//                $promoObject = [
//                    'code' => $promo->promo_code,
//                    'disc' => $promo->disc
//                ];
//            }
//        }
//
//        $emailData = [
//            'userData' => $commonData['userData'],
//            'type' => $commonData['type'],
//            'data' => $commonData['data'],
//            'link' => $commonData['link'],
//            'promo' => $promoObject // Add promo object to email data
//        ];
//
//        $htmlBody = View::make($emailTemplate->email_template, [
//            'data' => $emailData
//        ])->render();
//
//        $result = EmailTemplateController::sendEmail($user->email, $emailConfig['subject'] ?? '', $htmlBody);
//
//        if (str_contains($result, "successfully")) {
//            return ['success' => true, 'message' => 'Email Sent Successfully'];
//        }
//
//        return ['success' => false, 'message' => $result];
//    }
//
//    /**
//     * Prepare WhatsApp parameters with pre-fetched promo codes
//     */
//    private function prepareWhatsAppParams($user, $commonData, $templateConfig, $type, $promoCodes): array
//    {
//        $name = $user->name;
//        $promoCode = "";
//        $promoDiscount = "";
//
//        if($type == ConfigType::OFFER_PURCHASE_AUTOMATION->value){
//            return [
//                $name,
//                $user->email
//            ];
//        }
//
//        if ($templateConfig['promocode'] ?? false) {
//            $promoCodeId = $templateConfig['promocode'];
//            if (isset($promoCodes[$promoCodeId])) {
//                $promo = $promoCodes[$promoCodeId];
//                $promoCode = $promo->promo_code;
//                $promoDiscount = $promo->disc . "%";
//            }
//        }
//
//        if (in_array($commonData['planType'], ['template', 'video'])) {
//            $firstTemplate = $commonData['data']['templates'][0] ?? null;
//            if (!$firstTemplate) {
//                return ['success' => false, 'message' => "No design found in plan"];
//            }
//
//            return [
//                $name,
//                $commonData['data']['amount'],
//                $promoCode,
//                $promoDiscount,
//                $commonData['paymentLink']
//            ];
//
//        } elseif ($commonData['planType'] === 'new_sub') {
//            return [
//                $name,
//                $commonData['data']['offer_price'],
//                $commonData['data']['actual_price'],
//                $promoCode,
//                $promoDiscount,
//                $commonData['paymentLink']
//            ];
//
//        } elseif (in_array($commonData['planType'], ['old_sub', 'offer'])) {
//            if ($commonData['planType'] === "offer") {
//                return [
//                    $name,
//                    $commonData['data']['offer_price'],
//                    $commonData['data']['actual_price'],
//                    $commonData['paymentLink']
//                ];
//            } else {
//                return [
//                    $name,
//                    $commonData['data']['offer_price'],
//                    $commonData['data']['actual_price'],
//                    $promoCode,
//                    $promoDiscount,
//                    $commonData['paymentLink']
//                ];
//            }
//        }
//
//        return ['success' => false, 'message' => "Invalid plan type for WhatsApp parameters"];
//    }
//
//    private function processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber): void
//    {
//        foreach ($results as $channel => $result) {
//            if ($result['success']) {
//                Log::info("✅ {$channel} sent successfully to User #{$user->id}");
//                if ($channel === 'email' && $emailLog) {
//                    $emailLog->increment('sent');
//                } elseif ($channel === 'whatsapp' && $whatsappLog) {
//                    $whatsappLog->increment('sent');
//                }
//            } else {
//                Log::error("❌ Failed to send {$channel} to User #{$user->id}: {$result['message']}");
//                if ($channel === 'email' && $emailLog) {
//                    $emailLog->increment('failed');
//                    $this->logFailedEmail($emailLog, $user->id, $user->email, "{$channel}: {$result['message']}");
//                } elseif ($channel === 'whatsapp' && $whatsappLog) {
//                    $whatsappLog->increment('failed');
//                    $this->logFailedWhatsApp($whatsappLog, $user->id, $contactNumber, "{$channel}: {$result['message']}");
//                }
//            }
//        }
//    }
//
//    private function logFailedCommunication($emailLog, $whatsappLog, $userId, $email, $error): void
//    {
//        // Log failure only for enabled channels
//        if ($emailLog) {
//            $emailLog->increment('failed');
//            $this->logFailedEmail($emailLog, $userId, $email, $error);
//        }
//
//        if ($whatsappLog) {
//            $whatsappLog->increment('failed');
//            $this->logFailedWhatsApp($whatsappLog, $userId, null, $error);
//        }
//    }
//
//    private function logFailedEmail($emailLog, $userId, $email, $error): void
//    {
//        AutomationSendDetail::create([
//            'log_id' => $emailLog->id,
//            'user_id' => $userId,
//            'type' => AutomationType::EMAIL->value,
//            'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value,
//            'email' => $email,
//            'contact_number' => null,
//            'status' => 'failed',
//            'error_message' => $error,
//        ]);
//    }
//
//    private function logFailedWhatsApp($whatsappLog, $userId, $contactNumber, $error): void
//    {
//        AutomationSendDetail::create([
//            'log_id' => $whatsappLog->id,
//            'user_id' => $userId,
//            'type' => AutomationType::WHATSAPP->value,
//            'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value,
//            'email' => null,
//            'contact_number' => $contactNumber,
//            'status' => 'failed',
//            'error_message' => $error,
//        ]);
//    }
//
//    private function prepareWhatsAppButton($commonData, $type): array
//    {
//        if($type == ConfigType::OFFER_PURCHASE_AUTOMATION->value){
//            return [];
//        }
//
//        $buttonText = $commonData['paymentLink'];
//
//        if (in_array($commonData['planType'], ['template', 'video'])) {
//            $buttonText = "/redirect/" . $commonData['paymentLink'];
//        } elseif ($commonData['planType'] === 'new_sub') {
//            $buttonText = "/redirect/" . $commonData['paymentLink'];
//        } elseif ($commonData['planType'] === 'old_sub') {
//            if (str_contains($commonData['paymentLink'], 'offer-package')) {
//                $buttonText = $commonData['paymentLink'];
//            } else {
//                $buttonText = "/payment/redirect=" . $commonData['paymentLink'];
//            }
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
//                        "text" => $buttonText
//                    ]
//                ],
//            ]
//        ];
//    }
//
//    private function handleWhatsAppResponse($result, $templateParams = []): array
//    {
//        if (is_array($result)) {
//            $status = $result['status'] ?? false;
//            $message = $result['message'] ?? 'Something went wrong';
//        } else {
//            $status = $result->status ?? false;
//            $message = $status ? 'Message Sent Successfully' : ($result->message ?? 'Something went wrong');
//        }
//
//        return [
//            'success' => $status,
//            'message' => $message,
//        ];
//    }
//}