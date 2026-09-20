<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatbotService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService,
        protected ChatbotService $chatbotService
    ) {}

    /**
     * Handle Meta Webhook verification handshake (GET request).
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $configuredToken = config('whatsapp.verify_token');

        if ($mode === 'subscribe' && $token === $configuredToken) {
            Log::info('WhatsApp webhook handshake verified successfully.');

            return response((string) $challenge, 200, ['Content-Type' => 'text/plain']);
        }

        Log::warning('WhatsApp webhook verification failed.', [
            'mode' => $mode,
            'received_token' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming WhatsApp Webhook events (POST request).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Check if event is from WhatsApp
        $entry = $payload['entry'][0] ?? null;
        $change = $entry['changes'][0] ?? null;
        $value = $change['value'] ?? null;

        if (! $value) {
            return response()->json(['status' => 'ignored']);
        }

        // 1. Handle delivery status updates (sent, delivered, read, failed)
        if (! empty($value['statuses'])) {
            foreach ($value['statuses'] as $statusUpdate) {
                $waMessageId = $statusUpdate['id'] ?? null;
                $status = $statusUpdate['status'] ?? null;

                if ($waMessageId && $status) {
                    Message::where('wa_message_id', $waMessageId)->update(['status' => $status]);
                }
            }

            return response()->json(['status' => 'statuses_processed']);
        }

        // 2. Handle incoming patient messages
        if (! empty($value['messages'])) {
            foreach ($value['messages'] as $incomingMessage) {
                $this->processIncomingMessage($incomingMessage, $value);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Process an individual incoming message.
     *
     * @param  array<string, mixed>  $incomingMessage
     * @param  array<string, mixed>  $value
     */
    protected function processIncomingMessage(array $incomingMessage, array $value): void
    {
        $waMessageId = $incomingMessage['id'] ?? null;
        $fromPhone = $incomingMessage['from'] ?? null;
        $messageType = $incomingMessage['type'] ?? 'text';

        if (! $fromPhone) {
            return;
        }

        // Deduplicate messages by WhatsApp message ID
        if ($waMessageId && Message::where('wa_message_id', $waMessageId)->exists()) {
            return;
        }

        // Extract message text content
        $body = match ($messageType) {
            'text' => $incomingMessage['text']['body'] ?? '',
            'interactive' => $incomingMessage['interactive']['button_reply']['title']
                ?? $incomingMessage['interactive']['list_reply']['title']
                ?? '',
            default => "[Pesan tipe {$messageType}]",
        };

        if (trim($body) === '') {
            return;
        }

        // Extract profile name if provided by Meta
        $profileName = $value['contacts'][0]['profile']['name'] ?? null;

        // 1. Find or create Contact
        $contact = Contact::updateOrCreate(
            ['phone_number' => $fromPhone],
            [
                'profile_name' => $profileName,
                'name' => $contact->name ?? $profileName,
                'last_interaction_at' => now(),
            ]
        );

        // 2. Find or create active Conversation
        $conversation = Conversation::firstOrCreate(
            [
                'contact_id' => $contact->id,
                'status' => 'active',
            ],
            [
                'channel' => 'whatsapp',
                'started_at' => now(),
                'last_message_at' => now(),
            ]
        );

        // 3. Log incoming message
        Message::create([
            'conversation_id' => $conversation->id,
            'wa_message_id' => $waMessageId,
            'direction' => 'incoming',
            'message_type' => $messageType,
            'body' => $body,
            'raw_payload' => $incomingMessage,
            'status' => 'received',
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Mark as read in WhatsApp
        if ($waMessageId) {
            $this->whatsAppService->markMessageAsRead($waMessageId);
        }

        // 4. Generate automated reply using Chatbot engine
        $replyData = $this->chatbotService->reply($conversation, $body);

        if (! empty($replyData['reply'])) {
            $sendResult = $this->whatsAppService->sendTextMessage($fromPhone, $replyData['reply']);

            // 5. Log outgoing message
            Message::create([
                'conversation_id' => $conversation->id,
                'wa_message_id' => $sendResult['message_id'] ?? null,
                'direction' => 'outgoing',
                'message_type' => 'text',
                'body' => $replyData['reply'],
                'status' => $sendResult['success'] ? 'delivered' : 'failed',
            ]);

            $conversation->update(['last_message_at' => now()]);
        }
    }
}
