<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jamLokasi = FaqCategory::where('slug', 'jam-operasional-lokasi')->first();
        $pendaftaran = FaqCategory::where('slug', 'pendaftaran-janji-temu')->first();
        $bpjs = FaqCategory::where('slug', 'bpjs-asuransi')->first();
        $layanan = FaqCategory::where('slug', 'layanan-poli')->first();
        $tarif = FaqCategory::where('slug', 'tarif-pembayaran')->first();

        $faqs = [
            [
                'faq_category_id' => $jamLokasi?->id,
                'question' => 'Kapan jam buka dan jam operasional klinik?',
                'answer' => "Klinik Pratama Sehat buka setiap hari:\n- Senin - Sabtu: Pukul 08.00 - 21.00 WIB\n- Minggu & Hari Libur Nasional: Pukul 09.00 - 15.00 WIB.\nUnit Gawat Darurat (UGD) siaga 24 jam.",
                'keywords' => ['jam buka', 'jam operasional', 'jadwal buka', 'buka jam berapa', 'tutup jam berapa', 'hari libur buka', 'minggu buka'],
                'sort_order' => 1,
            ],
            [
                'faq_category_id' => $jamLokasi?->id,
                'question' => 'Di mana alamat dan lokasi klinik?',
                'answer' => "Klinik kami beralamat di:\nJl. Kesehatan Raya No. 45, Kebayoran Baru, Jakarta Selatan.\nPatokan: 200 meter di sebelah timur Stasiun MRT Blok A.\nGoogle Maps: https://maps.google.com/?q=Klinik+Pratama+Sehat",
                'keywords' => ['alamat', 'lokasi', 'tempat', 'dimana', 'maps', 'google maps', 'arah', 'patokan'],
                'sort_order' => 2,
            ],
            [
                'faq_category_id' => $pendaftaran?->id,
                'question' => 'Bagaimana cara mendaftar periksa atau reservasi antrean?',
                'answer' => "Pendaftaran dapat dilakukan dengan dua cara:\n1. Datang langsung ke bagian resepsionis klinik (on the spot).\n2. Melalui WhatsApp ini dengan format: DAFTAR#Nama Pasien#NIK#Poli Tujuan#Tanggal Kunjungan.\nNomor antrean akan dikirimkan setelah data diverifikasi.",
                'keywords' => ['daftar', 'pendaftaran', 'antrean', 'antri', 'nomor antrian', 'janji temu', 'booking', 'reservasi'],
                'sort_order' => 1,
            ],
            [
                'faq_category_id' => $pendaftaran?->id,
                'question' => 'Apa saja syarat yang perlu dibawa saat berkunjung?',
                'answer' => "Pasien diharapkan membawa:\n1. KTP / KIA (untuk anak-anak) atau fotokopi KK.\n2. Kartu BPJS Kesehatan / Kartu Asuransi (jika menggunakan jaminan).\n3. Buku rekam medis / kartu berobat klinik (khusus pasien lama).",
                'keywords' => ['syarat', 'dokumen', 'bawa apa', 'kartu berobat', 'ktp', 'persyaratan'],
                'sort_order' => 2,
            ],
            [
                'faq_category_id' => $bpjs?->id,
                'question' => 'Apakah klinik melayani pasien BPJS Kesehatan?',
                'answer' => 'Ya, Klinik kami melayani pasien BPJS Kesehatan (Faskes Tingkat Pertama / FKTP). Pastikan faskes BPJS Anda terdaftar di klinik kami. Pasien non-faskes dapat dilayani untuk kondisi darurat (UGD).',
                'keywords' => ['bpjs', 'bpjs kesehatan', 'faskes', 'faskes 1', 'fktp', 'kis', 'kartu indonesia sehat'],
                'sort_order' => 1,
            ],
            [
                'faq_category_id' => $bpjs?->id,
                'question' => 'Asuransi swasta apa saja yang bekerjasama dengan klinik?',
                'answer' => "Kami bekerjasama dengan asuransi swasta rekanan (metode cashless & reimburse), antara lain:\n- Prudential\n- Allianz\n- AXA Mandiri\n- Manulife\n- AdMedika\nSilakan konfirmasi ke kasir sebelum pemeriksaan.",
                'keywords' => ['asuransi', 'cashless', 'reimburse', 'admedika', 'prudential', 'allianz', 'axa'],
                'sort_order' => 2,
            ],
            [
                'faq_category_id' => $layanan?->id,
                'question' => 'Layanan dan poli spesialis apa saja yang tersedia?',
                'answer' => "Layanan medis di klinik kami meliputi:\n- Poli Umum\n- Poli Gigi & Mulut\n- Poli Ibu & Anak (KIA / KB)\n- UGD 24 Jam\n- Laboratorium Darah & Urine sederhana\n- Farmasi / Apotek",
                'keywords' => ['layanan', 'poli', 'poliklinik', 'dokter umum', 'poli gigi', 'cabut gigi', 'tambal gigi', 'kia', 'bidan', 'imunisasi', 'vaksin', 'laboratorium', 'apotek', 'obat'],
                'sort_order' => 1,
            ],
            [
                'faq_category_id' => $tarif?->id,
                'question' => 'Berapa biaya konsultasi dokter umum dan metode pembayarannya?',
                'answer' => 'Tarif konsultasi dokter umum mulai dari Rp 50.000 (di luar biaya obat & tindakan tambahan). Pembayaran menerima Tunai, QRIS (BCA, GoPay, OVO, ShopeePay), Debit ATM, dan Kartu Kredit.',
                'keywords' => ['biaya', 'tarif', 'harga', 'ongkos', 'bayar', 'metode pembayaran', 'qris', 'transfer', 'debit'],
                'sort_order' => 1,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
