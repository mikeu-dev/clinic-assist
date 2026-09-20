<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FaqCategorySeeder::class,
            FaqSeeder::class,
            ClinicSettingSeeder::class,
        ]);

        // Seed sample patient contacts & conversations
        $contact = Contact::firstOrCreate(
            ['phone_number' => '6281298765432'],
            [
                'name' => 'Budi Santoso',
                'profile_name' => 'Budi S.',
                'last_interaction_at' => now(),
            ]
        );

        $conversation = Conversation::firstOrCreate(
            ['contact_id' => $contact->id, 'status' => 'active'],
            [
                'channel' => 'whatsapp',
                'started_at' => now()->subMinutes(15),
                'last_message_at' => now(),
            ]
        );

        if ($conversation->messages()->count() === 0) {
            Message::create([
                'conversation_id' => $conversation->id,
                'wa_message_id' => 'wamid.sample1',
                'direction' => 'incoming',
                'message_type' => 'text',
                'body' => 'Halo min, mau tanya jam buka klinik hari ini jam berapa ya?',
                'status' => 'received',
                'created_at' => now()->subMinutes(15),
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'wa_message_id' => 'wamid.sample2',
                'direction' => 'outgoing',
                'message_type' => 'text',
                'body' => "Klinik Pratama Sehat buka setiap hari:\n- Senin - Sabtu: Pukul 08.00 - 21.00 WIB\n- Minggu & Hari Libur Nasional: Pukul 09.00 - 15.00 WIB.\nUnit Gawat Darurat (UGD) siaga 24 jam.",
                'status' => 'delivered',
                'created_at' => now()->subMinutes(14),
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'wa_message_id' => 'wamid.sample3',
                'direction' => 'incoming',
                'message_type' => 'text',
                'body' => 'Bisa pakai BPJS Kesehatan?',
                'status' => 'received',
                'created_at' => now()->subMinutes(10),
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'wa_message_id' => 'wamid.sample4',
                'direction' => 'outgoing',
                'message_type' => 'text',
                'body' => 'Ya, Klinik kami melayani pasien BPJS Kesehatan (Faskes Tingkat Pertama / FKTP). Pastikan faskes BPJS Anda terdaftar di klinik kami.',
                'status' => 'delivered',
                'created_at' => now()->subMinutes(10),
            ]);
        }
    }
}
