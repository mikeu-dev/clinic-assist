<?php

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('normalizeText cleans punctuation and trims whitespace', function (): void {
    $service = new ChatbotService;

    $result = $service->normalizeText('  Halo?! Apakah klinik buka Hari Minggu???  ');

    $this->assertSame('halo apakah klinik buka hari minggu', $result);
});

test('reply matches FAQ and returns correct answer', function (): void {
    $category = FaqCategory::create(['name' => 'BPJS', 'slug' => 'bpjs']);
    Faq::create([
        'faq_category_id' => $category->id,
        'question' => 'Apakah bisa berobat menggunakan BPJS Kesehatan?',
        'answer' => 'Ya, kami menerima pasien BPJS Kesehatan FKTP.',
        'keywords' => ['bpjs', 'faskes', 'kis'],
        'is_active' => true,
    ]);

    $contact = Contact::create(['phone_number' => '628123456789']);
    $conversation = Conversation::create(['contact_id' => $contact->id]);

    $service = new ChatbotService;
    $result = $service->reply($conversation, 'Bisa pakai BPJS di sini?');

    $this->assertSame('answered', $result['action']);
    $this->assertSame('Ya, kami menerima pasien BPJS Kesehatan FKTP.', $result['reply']);
});

test('reply returns fallback when no FAQ matches', function (): void {
    $contact = Contact::create(['phone_number' => '628123456789']);
    $conversation = Conversation::create(['contact_id' => $contact->id]);

    $service = new ChatbotService;
    $result = $service->reply($conversation, 'Berapa harga tiket pesawat ke Tokyo?');

    $this->assertSame('fallback', $result['action']);
    $this->assertNotEmpty($result['reply']);
});

test('reply escalates to human admin when requested', function (): void {
    $contact = Contact::create(['phone_number' => '628123456789']);
    $conversation = Conversation::create(['contact_id' => $contact->id, 'status' => 'active']);

    $service = new ChatbotService;
    $result = $service->reply($conversation, 'Saya butuh bantuan admin klinik dong');

    $this->assertSame('escalated', $result['action']);
    $this->assertSame('escalated', $conversation->fresh()->status);
});

test('reply stays silent if conversation is escalated', function (): void {
    $contact = Contact::create(['phone_number' => '628123456789']);
    $conversation = Conversation::create(['contact_id' => $contact->id, 'status' => 'escalated']);

    $service = new ChatbotService;
    $result = $service->reply($conversation, 'Halo masih ada orang?');

    $this->assertSame('silent', $result['action']);
    $this->assertNull($result['reply']);
});
