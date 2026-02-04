<?php

namespace App\Jobs;
use App\Enums\ConfigType;
use App\Http\Controllers\EmailTemplateController;
use App\Models\EmailTemplate;
use App\Models\MailSendDetail;
use App\Models\MailSendLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCheckoutDropEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $planData;
    protected $frequency;
    protected $mailSendLogId;

    public function __construct($user, $planData, $frequency, $logId)
    {
        $this->user = $user;
        $this->planData = $planData;
        $this->frequency = $frequency;
        $this->mailSendLogId = $logId;
    }

    public function handle(): void
    {
        $log = MailSendLog::find($this->mailSendLogId);
        if (!$log) {
            Log::info("BulkEmailJob: Log not found or stopped.");
            return;
        }
        try {
            $templateId = $this->frequency['template'] ?? null;
            if (!$templateId) {
                Log::warning("Template ID missing for log");
                return;
            }

            $emailTemplate = EmailTemplate::find($templateId);
            if (!$emailTemplate) {
                Log::warning("Template {$templateId} not found for log");
                return;
            }

            $htmlBody = view($emailTemplate->email_template, [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'password' => $this->user->password ?? "",
                'desc' => $this->planData->desc,
                'validity' => $this->planData->validity,
                'actual_price' => $this->planData->actual_price,
                'actual_price_dollar' => $this->planData->actual_price_dollar,
                'price' => $this->planData->price,
                'price_dollar' => $this->planData->price_dollar,
            ])->render();

            $result = EmailTemplateController::sendEmail(
                $this->user->email,
                $this->frequency['subject'] ?? '',
                $htmlBody,
                'text'
            );

            if (!str_contains($result, "successfully")) {
                MailSendDetail::create([
                    'log_id' => $this->mailSendLogId,
                    'user_id' => $this->user->id,
                    'send_type' => ConfigType::EMAIL_CHECKOUT_DROP_AUTOMATION,
                    'email' => $this->user->email,
                    'status' => 'failed',
                    'error_message' => $result,
                ]);

                // Increment failed count
                $log->increment('failed');

                Log::error("Email failed for user {$this->user->uid}: {$result}");
            } else {
                // Increment sent count
                $log->increment('sent');

                Log::info("Email Success for user {$this->user->uid}: {$result}");
            }

        } catch (\Exception $e) {
            MailSendDetail::create([
                'log_id' => $this->mailSendLogId,
                'user_id' => $this->user->id,
                'send_type' => ConfigType::EMAIL_CHECKOUT_DROP_AUTOMATION,
                'email' => $this->user->email,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $log->increment('failed');

            Log::error("Email Exception for user {$this->user->uid}: " . $e->getMessage());
        }
    }
}