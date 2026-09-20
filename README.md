# ClinicAssist

Sistem Chatbot FAQ Klinik Berbasis WhatsApp Resmi (Meta Cloud API) dengan Panel Administrasi Filament.

## Deskripsi Proyek

ClinicAssist adalah solusi sistem otomasi layanan informasi pasien untuk fasilitas kesehatan klinik berbasis WhatsApp. Sistem ini dirancang menggunakan pendekatan arsitektur monolitik yang andal, hemat biaya, dan mudah dirawat.

Fokus utama sistem adalah menjawab pertanyaan berulang (FAQ) seputar jam operasional, pendaftaran, BPJS/asuransi, layanan poliklinik, dan tarif secara otomatis menggunakan pencocokan kata kunci dan kesamaan teks berbobot sebelum melibatkan tenaga manusia atau model kecerdasan buatan lanjutan.

## Fitur Utama

1. Knowledge Base FAQ Dinamis
   - Pengelompokan FAQ berdasarkan kategori (Pendaftaran, BPJS, Jadwal Operasional, Layanan Poli, Tarif).
   - Pengelolaan kata kunci (keywords) untuk pencocokan pertanyaan pasien.
   - Manajemen visibilitas dan status aktif/nonaktif setiap FAQ melalui panel admin.

2. Chatbot Matching Engine
   - Normalisasi teks pasien (penghapusan tanda baca, konversi huruf kecil, pembersihan spasi ganda).
   - Algoritma pencocokan berbobot (kombinasi kata kunci dan kesamaan teks).
   - Deteksi otomatis sapaan (greeting) dan panduan menu.
   - Deteksi eskalasi ke staf admin klinik ketika pasien meminta bantuan manusia secara eksplisit.
   - Mode senyap otomatis ketika percakapan sedang ditangani oleh staf admin.
   - Pesan fallback otomatis jika pertanyaan di luar data knowledge base.

3. Integrasi Meta WhatsApp Cloud API Resmi
   - Endpoint verifikasi handshake webhook (GET) sesuai standar Meta for Developers.
   - Endpoint pemrosesan pesan masuk dan pembaruan status pengiriman pesan (POST).
   - Pengiriman balasan pesan teks langsung melalui Graph API.
   - Penandaan otomatis pesan telah dibaca (mark as read).

4. Panel Administrasi Filament v5
   - Dashboard pengelolaan kategori FAQ dan daftar FAQ.
   - Pemantauan riwayat percakapan pasien secara langsung.
   - Penampil detail percakapan (timeline chat antara pasien dan bot/admin).
   - Direktori kontak pasien yang terhubung via WhatsApp.
   - Halaman pengaturan umum, informasi kontak klinik, dan template pesan bot.

## Teknologi yang Digunakan

- Framework Backend: Laravel 12 (PHP 8.4)
- Panel Administrasi: Filament v5
- Database: SQLite (pengembangan lokal) / PostgreSQL (lingkungan produksi)
- Kanal Komunikasi: Meta WhatsApp Cloud API
- Test Runner: Pest PHP

## Persyaratan Sistem

- PHP 8.4 atau lebih baru
- Composer 2.x
- Node.js dan NPM

## Panduan Instalasi

1. Clone repositori ini:
   git clone https://github.com/mikeu-dev/clinic-assist.git
   cd clinic-assist

2. Install dependensi PHP:
   composer install

3. Konfigurasi berkas lingkungan:
   cp .env.example .env
   php artisan key:generate

4. Jalankan migrasi database dan pengisian data dummy:
   php artisan migrate --seed

5. Buat akun administrator untuk login panel:
   php artisan make:filament-user

6. Jalankan server lokal:
   php artisan serve

   Akses panel administrasi di: http://localhost:8000/panel

## Konfigurasi WhatsApp Cloud API

Tambahkan konfigurasi berikut pada berkas .env untuk mengaktifkan integrasi Meta:

WHATSAPP_PHONE_NUMBER_ID=nomor_id_telepon_dari_meta
WHATSAPP_ACCESS_TOKEN=token_akses_sistem_meta
WHATSAPP_VERIFY_TOKEN=token_verifikasi_kustom_anda
WHATSAPP_API_VERSION=v21.0
WHATSAPP_API_URL=https://graph.facebook.com
CHATBOT_MIN_CONFIDENCE=0.4

Pada dashboard Meta for Developers, daftarkan URL Webhook:
- Callback URL: https://domain-anda.com/api/webhook/whatsapp
- Verify Token: nilai yang sama dengan WHATSAPP_VERIFY_TOKEN

## Pengujian Otomatis

Untuk menjalankan seluruh test suite (unit test dan feature test):

php artisan test --compact

Cakupan pengujian mencakup:
- Aksesibilitas dan otentikasi seluruh halaman resource Filament.
- Handshake verifikasi webhook WhatsApp dengan token valid dan token tidak valid.
- Penerimaan pesan masuk, pencocokan FAQ otomatis, dan logging pesan.
- Deteksi intent sapaan dan eskalasi ke staf admin.
- Pembaruan status pengiriman pesan (sent, delivered, read).
- Unit test logika normalisasi teks dan algoritma pencocokan kata kunci pada ChatbotService.

## Lisensi

Proyek ini dilisensikan di bawah lisensi MIT. Lihat berkas LICENSE untuk informasi selengkapnya.

## Pengembang

Riki Ruswandi
- GitHub: https://github.com/mikeu-dev
- Email: rikiruswandi28@gmail.com
