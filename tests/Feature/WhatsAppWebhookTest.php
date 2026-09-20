<?php

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['whatsapp.verify_token' => 'my_test_token']);

    $category = FaqCategory::create([
        'name' => 'Jam Operasional',
        'slug' => 'jam-operasional',
    ]);

    Faq::create([
        'faq_category_id' => $category->id,
        'question' => 'Kapan jam operasional klinik buka?',
        'answer' => 'Klinik kami buka setiap hari Senin-Sabtu pukul 08.00-21.00 WIB.',
        'keywords' => ['jam buka', 'operasional', 'jadwal buka'],
        'is_active' => true,
    ]);
});

test('webhook verification succeeds with valid token and mode', function (): void {
    $response = $this->get('/api/webhook/whatsapp?hub.mode=subscribe&hub.verify_token=my_test_token&hub.challenge=1234567890');

    $response->assertStatus(200);
    $this->assertSame('1234567890', $response->getContent());
});

test('webhook verification fails with invalid token', function (): void {
    $response = $this->get('/api/webhook/whatsapp?hub.mode=subscribe&hub.verify_token=wrong_token&hub.challenge=1234567890');

    $response->assertStatus(403);
});

test('webhook handles incoming message and auto replies with matched FAQ', function (): void {
    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'id' => '100000000',
                'changes' => [
                    [
                        'field' => 'messages',
                        'value' => [
                            'messaging_product' => 'whatsapp',
                            'metadata' => [
                                'display_phone_number' => '6281234567890',
                                'phone_number_id' => '123456',
                            ],
                            'contacts' => [
                                [
                                    'profile' => [
                                        'name' => 'Siti Aminah',
                                    ],
                                    'wa_id' => '628111222333',
                                ],
                            ],
                            'messages' => [
                                [
                                    'from' => '628111222333',
                                    'id' => 'wamid.HBgLMjE0NTk0NDc0MBUC',
                                    'timestamp' => '1726830000',
                                    'text' => [
                                        'body' => 'Permisi mau tanya jam buka klinik kapan ya?',
                                    ],
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/webhook/whatsapp', $payload);

    $response->assertOk()
        ->assertJson(['status' => 'success']);

    // Assert Contact was created
    $this->assertDatabaseHas('contacts', [
        'phone_number' => '628111222333',
        'profile_name' => 'Siti Aminah',
    ]);

    $contact = Contact::where('phone_number', '628111222333')->first();
    $this->assertNotNull($contact);

    // Assert Conversation was created
    $this->assertDatabaseHas('conversations', [
        'contact_id' => $contact->id,
        'status' => 'active',
    ]);

    // Assert incoming message was logged
    $this->assertDatabaseHas('messages', [
        'wa_message_id' => 'wamid.HBgLMjE0NTk0NDc0MBUC',
        'direction' => 'incoming',
        'body' => 'Permisi mau tanya jam buka klinik kapan ya?',
    ]);

    // Assert outgoing automated reply was sent and logged
    $this->assertDatabaseHas('messages', [
        'direction' => 'outgoing',
        'body' => 'Klinik kami buka setiap hari Senin-Sabtu pukul 08.00-21.00 WIB.',
    ]);
});

test('webhook handles greeting message and sends welcome reply', function (): void {
    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'contacts' => [
                                [
                                    'profile' => ['name' => 'Ahmad'],
                                    'wa_id' => '628999888777',
                                ],
                            ],
                            'messages' => [
                                [
                                    'from' => '628999888777',
                                    'id' => 'wamid.greet123',
                                    'text' => ['body' => 'Halo'],
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/webhook/whatsapp', $payload);

    $response->assertOk();

    $this->assertDatabaseHas('messages', [
        'direction' => 'outgoing',
    ]);
});

test('webhook handles escalation request and changes conversation status', function (): void {
    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'contacts' => [
                                [
                                    'profile' => ['name' => 'Dewi'],
                                    'wa_id' => '628555666777',
                                ],
                            ],
                            'messages' => [
                                [
                                    'from' => '628555666777',
                                    'id' => 'wamid.adminReq',
                                    'text' => ['body' => 'Tolong hubungkan saya dengan admin'],
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/webhook/whatsapp', $payload);

    $response->assertOk();

    $contact = Contact::where('phone_number', '628555666777')->first();
    $conversation = Conversation::where('contact_id', $contact->id)->first();

    $this->assertSame('escalated', $conversation->status);
    $this->assertNotNull($conversation->escalated_at);
});

test('webhook handles message status delivery updates', function (): void {
    $contact = Contact::create(['phone_number' => '628111222333']);
    $conversation = Conversation::create(['contact_id' => $contact->id]);
    $message = Message::create([
        'conversation_id' => $conversation->id,
        'wa_message_id' => 'wamid.statusCheck',
        'direction' => 'outgoing',
        'body' => 'Test',
        'status' => 'sent',
    ]);

    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'statuses' => [
                                [
                                    'id' => 'wamid.statusCheck',
                                    'status' => 'read',
                                    'timestamp' => '1726830500',
                                    'recipient_id' => '628111222333',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/webhook/whatsapp', $payload);

    $response->assertOk()
        ->assertJson(['status' => 'statuses_processed']);

    $this->assertSame('read', $message->fresh()->status);
});
