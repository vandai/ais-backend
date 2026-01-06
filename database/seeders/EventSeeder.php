<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::first();
        if (!$author) {
            $this->command->warn('No user found. Please create a user first.');
            return;
        }

        $nobarCategory = EventCategory::where('slug', 'nobar')->first();
        $sosialCategory = EventCategory::where('slug', 'sosial')->first();
        $olahragaCategory = EventCategory::where('slug', 'olahraga')->first();

        if (!$nobarCategory || !$sosialCategory || !$olahragaCategory) {
            $this->command->warn('Event categories not found. Please run EventCategorySeeder first.');
            return;
        }

        $events = [
            [
                'author_id' => $author->id,
                'category_id' => $nobarCategory->id,
                'title' => 'Nobar Arsenal vs Manchester United',
                'description' => 'Yuk nonton bareng pertandingan seru Arsenal melawan Manchester United di Emirates Stadium. Akan ada doorprize menarik untuk peserta yang hadir!',
                'slug' => 'nobar-arsenal-vs-manchester-united',
                'location' => 'Warung Kopi Arsenal, Jakarta Selatan',
                'fee' => 50000,
                'start_datetime' => now()->addDays(7)->setHour(20)->setMinute(0),
                'end_datetime' => now()->addDays(7)->setHour(23)->setMinute(0),
                'member_only' => false,
                'status' => 'published',
            ],
            [
                'author_id' => $author->id,
                'category_id' => $nobarCategory->id,
                'title' => 'Nobar Arsenal vs Chelsea - London Derby',
                'description' => 'Saksikan pertandingan derby London antara Arsenal dan Chelsea. Atmosphere dijamin seru dengan sesama Gooners!',
                'slug' => 'nobar-arsenal-vs-chelsea',
                'location' => 'Gooners Cafe, Bandung',
                'fee' => 75000,
                'start_datetime' => now()->addDays(14)->setHour(19)->setMinute(30),
                'end_datetime' => now()->addDays(14)->setHour(22)->setMinute(30),
                'member_only' => true,
                'status' => 'published',
            ],
            [
                'author_id' => $author->id,
                'category_id' => $sosialCategory->id,
                'title' => 'Bakti Sosial AIS ke Panti Asuhan',
                'description' => 'Kegiatan bakti sosial Arsenal Indonesia Supporters ke panti asuhan. Mari berbagi kebahagiaan bersama anak-anak panti.',
                'slug' => 'bakti-sosial-ais-panti-asuhan',
                'location' => 'Panti Asuhan Kasih Ibu, Jakarta Timur',
                'fee' => 0,
                'start_datetime' => now()->addDays(21)->setHour(9)->setMinute(0),
                'end_datetime' => now()->addDays(21)->setHour(14)->setMinute(0),
                'member_only' => false,
                'status' => 'published',
            ],
            [
                'author_id' => $author->id,
                'category_id' => $olahragaCategory->id,
                'title' => 'Futsal Bareng Gooners Jakarta',
                'description' => 'Main futsal bareng sesama Gooners Jakarta. Yuk jaga kebugaran sambil silaturahmi. Tersedia jersey untuk dipinjam.',
                'slug' => 'futsal-bareng-gooners-jakarta',
                'location' => 'Champion Futsal, Kemang',
                'fee' => 100000,
                'start_datetime' => now()->addDays(10)->setHour(16)->setMinute(0),
                'end_datetime' => now()->addDays(10)->setHour(18)->setMinute(0),
                'member_only' => true,
                'status' => 'published',
            ],
            [
                'author_id' => $author->id,
                'category_id' => $olahragaCategory->id,
                'title' => 'Fun Run Gooners 5K',
                'description' => 'Event lari santai 5K untuk para Gooners. Akan ada medali finisher dan kaos eksklusif untuk semua peserta.',
                'slug' => 'fun-run-gooners-5k',
                'location' => 'Gelora Bung Karno, Senayan',
                'fee' => 150000,
                'start_datetime' => now()->addDays(30)->setHour(6)->setMinute(0),
                'end_datetime' => now()->addDays(30)->setHour(10)->setMinute(0),
                'member_only' => false,
                'status' => 'published',
            ],
        ];

        foreach ($events as $event) {
            Event::updateOrCreate(
                ['slug' => $event['slug']],
                $event
            );
        }
    }
}
