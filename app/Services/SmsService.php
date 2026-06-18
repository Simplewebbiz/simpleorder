<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsService
{
    protected ?Client $client;
    protected string $from;
    protected bool $enabled;

    public function __construct()
    {
        $sid    = config('services.twilio.sid');
        $token  = config('services.twilio.token');
        $this->from    = config('services.twilio.from', '');
        $this->enabled = config('services.twilio.enabled', false) && $sid && $token && $this->from;

        if ($this->enabled) {
            $this->client = new Client($sid, $token);
        }
    }

    /**
     * Send an SMS message. Silently swallows errors so a Twilio failure never
     * breaks the order flow — errors are logged instead.
     *
     * @param  string  $to   Recipient phone number (e.g. "+15551234567")
     * @param  string  $body Message text
     */
    public function send(string $to, string $body): void
    {
        if (! $this->enabled) {
            return;
        }

        $to = $this->normalizePhone($to);
        if (! $to) {
            return;
        }

        try {
            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $body,
            ]);
        } catch (\Throwable $e) {
            Log::warning('SMS send failed', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Normalize a phone number to E.164 format (+1XXXXXXXXXX for US numbers).
     * Returns null if the number can't be parsed into something sendable.
     */
    protected function normalizePhone(string $phone): ?string
    {
        // Strip everything except digits and leading +
        $digits = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($digits, '+')) {
            return strlen($digits) >= 10 ? $digits : null;
        }

        // Assume US if 10 digits
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        // 11 digits starting with 1 — US with country code
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        return null;
    }
}
