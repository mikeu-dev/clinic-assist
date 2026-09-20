<?php

namespace Database\Seeders;

use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Jam Operasional & Lokasi',
                'slug' => 'jam-operasional-lokasi',
                'description' => 'Informasi jam buka klinik, alamat, dan petunjuk arah.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Pendaftaran & Janji Temu',
                'slug' => 'pendaftaran-janji-temu',
                'description' => 'Tata cara pendaftaran pasien baru, pasien lama, dan reservasi.',
                'sort_order' => 2,
            ],
            [
                'name' => 'BPJS & Asuransi',
                'slug' => 'bpjs-asuransi',
                'description' => 'Ketentuan klaim BPJS Kesehatan dan asuransi rekanan.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Layanan & Poli',
                'slug' => 'layanan-poli',
                'description' => 'Daftar poliklinik, layanan medis, laboratorium, dan farmasi.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Tarif & Pembayaran',
                'slug' => 'tarif-pembayaran',
                'description' => 'Metode pembayaran dan kisaran biaya konsultasi umum.',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            FaqCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
