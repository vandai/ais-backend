<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Nobar',
                'slug' => 'nobar',
                'description' => 'Nonton bareng pertandingan Arsenal',
            ],
            [
                'name' => 'Sosial',
                'slug' => 'sosial',
                'description' => 'Kegiatan sosial dan amal komunitas',
            ],
            [
                'name' => 'Olahraga',
                'slug' => 'olahraga',
                'description' => 'Kegiatan olahraga bersama anggota',
            ],
        ];

        foreach ($categories as $category) {
            EventCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
