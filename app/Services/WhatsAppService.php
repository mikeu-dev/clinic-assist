<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $phoneNumberId;

    protected ?string $accessToken;

    protected string $apiVersion;

    protected string $apiUrl;

    public function __construct()
    {
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->accessToken = config('whatsapp.access_token');
        $this->apiVersion = config('whatsapp.api_version', 'v21.0');
        $this->apiUrl = config('whatsapp.api_url', 'https://graph.facebook.com');
    }

    /**
     * Send a text message to a WhatsApp recipient.
     *
     * @return array{success: bool, message_id: ?string, error: ?string}
     */
    public function sendTextMessage(string $recipientPhone, string $message): array
    {
        // Format phone number: strip non-digits, ensure country code
        $cleanPhone = preg_replace('/\D/', '', $recipientPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        if (empty($this->phoneNumberId) || empty($this->accessToken)) {
            Log::warning('WhatsApp Cloud API credentials not configured. Message simulated.', [
                'recipient' => $cleanPhone,
                'message' => $message,
            ]);

            return [
                'success' => true,
                'message_id' => 'simulated_'.uniqid(),
                'error' => null,
            ];
        }

        $url = "{$this->apiUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        try {
            /** @var Response $response */
            $response = Http::withToken($this->accessToken)
                ->timeout(10)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $cleanPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $waMessageId = $data['messages'][0]['id'] ?? null;

                return [
                    'success' => true,
                    'message_id' => $waMessageId,
                    'error' => null,
                ];
            }

            Log::error('WhatsApp Cloud API send message failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'recipient' => $cleanPhone,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('Exception when sending WhatsApp message: '.$e->getMessage(), [
                'exception' => $e,
                'recipient' => $cleanPhone,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Mark an incoming message as read.
     */
    public function markMessageAsRead(string $messageId): bool
    {
        if (empty($this->phoneNumberId) || empty($this->accessToken)) {
            return true;
        }

        $url = "{$this->apiUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(5)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $messageId,
                ]);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
