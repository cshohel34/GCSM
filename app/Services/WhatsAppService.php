<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provider-agnostic WhatsApp sender. Configure WHATSAPP_API_URL + WHATSAPP_API_TOKEN
 * in .env. If not configured, it is a safe no-op (logs only).
 */
class WhatsAppService
{
    public function send(string $to, string $message): bool
    {
        $url = env('WHATSAPP_API_URL');
        $token = env('WHATSAPP_API_TOKEN');
        if (! $url || ! $to) {
            Log::info("[WhatsApp:skipped] to={$to} msg={$message}");
            return false;
        }
        try {
            $res = Http::withToken($token)->asJson()->post($url, [
                'to' => $to,
                'from' => env('WHATSAPP_FROM'),
                'message' => $message,
            ]);
            return $res->successful();
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp:error] '.$e->getMessage());
            return false;
        }
    }
}
