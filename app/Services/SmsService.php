<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Message;
use App\Models\Tenant;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function hasCredits(int $needed = 1): bool
    {
        return $this->account->sms_credits >= $needed;
    }

    public function remainingCredits(): int
    {
        return $this->account->sms_credits;
    }

    public function isLow(): bool
    {
        return $this->account->sms_credits <= 20
            && $this->account->sms_credits > 0;
    }

    public function send(string $phone, string $message, ?int $tenantId = null): array
    {
        // Check credits
        if (!$this->hasCredits()) {
            Message::create([
                'account_id' => $this->account->id,
                'tenant_id'  => $tenantId,
                'channel'    => 'sms',
                'phone'      => $phone,
                'body'       => $message,
                'status'     => 'failed',
            ]);

            return [
                'success' => false,
                'reason'  => 'insufficient_credits',
                'message' => 'Insufficient SMS credits. Please top up to continue sending.',
            ];
        }

        $formattedPhone = $this->formatPhone($phone);

        $messageRecord = Message::create([
            'account_id' => $this->account->id,
            'tenant_id'  => $tenantId,
            'channel'    => 'sms',
            'phone'      => $formattedPhone,
            'body'       => $message,
            'status'     => 'pending',
        ]);

        try {
            $at  = new AfricasTalking(
                config('services.africastalking.username'),
                config('services.africastalking.api_key')
            );
            $sms = $at->sms();

            $response = $sms->send([
                'to'      => $formattedPhone,
                'message' => $message,
                'from'    => config('services.africastalking.from', ''),
            ]);

            $status = 'failed';

            if (isset($response['status']) && $response['status'] === 'success') {
                $recipients = $response['data']->SMSMessageData->Recipients ?? [];
                if (!empty($recipients)) {
                    $atStatus = $recipients[0]->status ?? '';
                    $status   = str_contains(strtolower($atStatus), 'success')
                        ? 'sent'
                        : 'failed';
                }
            }

            $messageRecord->update([
                'status'  => $status,
                'sent_at' => now(),
            ]);

            // Only deduct credits on success
            if ($status === 'sent') {
                $this->account->decrement('sms_credits', 1);

                // Fire low credits notification
                if ($this->isLow()) {
                    \App\Models\Notification::create([
                        'account_id' => $this->account->id,
                        'type'       => 'sms_credits_low',
                        'title'      => 'SMS credits running low',
                        'body'       => 'You have ' . $this->account->fresh()->sms_credits . ' SMS credits remaining. Top up to avoid interruption.',
                    ]);
                }

                // Fire zero credits notification
                if ($this->account->fresh()->sms_credits === 0) {
                    \App\Models\Notification::create([
                        'account_id' => $this->account->id,
                        'type'       => 'sms_credits_empty',
                        'title'      => 'SMS credits exhausted',
                        'body'       => 'You have run out of SMS credits. Top up now to continue sending messages to tenants.',
                    ]);
                }
            }

            return [
                'success' => $status === 'sent',
                'status'  => $status,
                'message' => $messageRecord,
            ];

        } catch (\Exception $e) {
            $messageRecord->update(['status' => 'failed']);
            Log::error('SMS send failed: ' . $e->getMessage());

            return [
                'success' => false,
                'reason'  => 'exception',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function sendBulk(iterable $recipients, string $messageTemplate): array
    {
        $recipientList = collect($recipients);
        $needed        = $recipientList->count();

        if (!$this->hasCredits($needed)) {
            return [
                'success'  => false,
                'reason'   => 'insufficient_credits',
                'message'  => 'You need ' . $needed . ' credits but only have ' . $this->account->sms_credits . '. Please top up.',
                'sent'     => 0,
                'failed'   => $needed,
            ];
        }

        $sent   = 0;
        $failed = 0;

        foreach ($recipientList as $recipient) {
            $phone    = $recipient['phone'];
            $message  = $recipient['message'] ?? $messageTemplate;
            $tenantId = $recipient['tenant_id'] ?? null;

            $result = $this->send($phone, $message, $tenantId);

            $result['success'] ? $sent++ : $failed++;
        }

        return [
            'success' => true,
            'sent'    => $sent,
            'failed'  => $failed,
        ];
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '+254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '254') && !str_starts_with($phone, '+')) {
            return '+' . $phone;
        }

        return $phone;
    }
}