<?php

namespace Database\Seeders;

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
            // Users and Members
            UserSeeder::class,
            MemberSeeder::class,

            // Posts (News)
            PostCategorySeeder::class,
            PostSeeder::class,

            // Events
            EventCategorySeeder::class,
            EventSeeder::class,

            // Football Data
            CompetitionSeeder::class,
            FixtureSeeder::class,
            MatchResultSeeder::class,
            LeagueTableSeeder::class,
        ]);
    }
}
