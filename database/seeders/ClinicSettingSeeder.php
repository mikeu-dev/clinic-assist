<?php

namespace Database\Seeders;

use App\Models\ClinicSetting;
use Illuminate\Database\Seeder;

class ClinicSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'clinic_name',
                'value' => 'Klinik Pratama Sehat Sejahtera',
                'group' => 'general',
                'label' => 'Nama Klinik',
                'description' => 'Nama resmi fasilitas kesehatan klinik.',
            ],
            [
                'key' => 'clinic_phone',
                'value' => '021-7234567',
                'group' => 'contact',
                'label' => 'Nomor Telepon Hotline',
                'description' => 'Nomor telepon kabel / call center klinik.',
            ],
            [
                'key' => 'clinic_whatsapp',
                'value' => '6281234567890',
                'group' => 'contact',
                'label' => 'Nomor WhatsApp Resmi',
                'description' => 'Nomor WhatsApp bisnis yang digunakan untuk chatbot.',
            ],
            [
                'key' => 'clinic_address',
                'value' => 'Jl. Kesehatan Raya No. 45, Kebayoran Baru, Jakarta Selatan',
                'group' => 'contact',
                'label' => 'Alamat Lengkap',
                'description' => 'Alamat fisik klinik beserta patokan lokasi.',
            ],
            [
                'key' => 'bot_welcome_message',
                'value' => "Halo! Selamat datang di layanan asisten virtual *Klinik Pratama Sehat*. 🏥\n\nSaya dapat membantu Anda dengan informasi mengenai:\n1. 🕒 Jam Buka & Lokasi\n2. 📝 Pendaftaran & Janji Temu\n3. 💳 Layanan BPJS & Asuransi Swasta\n4. 👨‍⚕️ Layanan Poli & Dokter\n5. 💰 Informasi Tarif & Pembayaran\n\nKetik pertanyaan Anda atau ketik *MENU* untuk bantuan.",
                'group' => 'bot',
                'label' => 'Pesan Pembuka (Welcome Message)',
                'description' => 'Pesan otomatis saat pasien pertama kali menyapa chatbot.',
            ],
            [
                'key' => 'bot_fallback_message',
                'value' => "Mohon maaf, saya belum memahami pertanyaan Anda. 🙏\n\nAnda dapat menanyakan hal lain seputar jam buka, pendaftaran, BPJS, atau tarif.\n\nJika membutuhkan bantuan langsung dari petugas administrasi klinik kami, silakan ketik *ADMIN*.",
                'group' => 'bot',
                'label' => 'Pesan Fallback (Pertanyaan Tidak Dikenali)',
                'description' => 'Pesan yang dikirim ketika kecocokan FAQ tidak ditemukan.',
            ],
            [
                'key' => 'bot_escalate_message',
                'value' => "Permintaan Anda telah kami teruskan ke petugas administrasi klinik. 👨‍💼\nPetugas kami akan segera merespons pesan Anda pada jam operasional (08.00 - 21.00 WIB). Mohon ditunggu ya.",
                'group' => 'bot',
                'label' => 'Pesan Eskalasi ke Petugas',
                'description' => 'Pesan ketika percakapan dialihkan ke staf manusia.',
            ],
        ];

        foreach ($settings as $setting) {
            ClinicSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
