<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competitions = [
            [
                'league_id' => 667,
                'name' => 'Friendlies Clubs',
                'type' => 'Cup',
                'logo' => 'https://media.api-sports.io/football/leagues/667.png',
                'country' => 'World',
                'country_code' => null,
                'country_flag' => null,
                'season' => 2025,
                'season_start' => null,
                'season_end' => null,
                'is_current' => true,
            ],
            [
                'league_id' => 39,
                'name' => 'Premier League',
                'type' => 'League',
                'logo' => 'https://media.api-sports.io/football/leagues/39.png',
                'country' => 'England',
                'country_code' => 'GB-ENG',
                'country_flag' => 'https://media.api-sports.io/flags/gb-eng.svg',
                'season' => 2025,
                'season_start' => '2025-08-15',
                'season_end' => '2026-05-24',
                'is_current' => true,
            ],
            [
                'league_id' => 2,
                'name' => 'UEFA Champions League',
                'type' => 'Cup',
                'logo' => 'https://media.api-sports.io/football/leagues/2.png',
                'country' => 'World',
                'country_code' => null,
                'country_flag' => null,
                'season' => 2025,
                'season_start' => '2025-07-08',
                'season_end' => '2026-01-28',
                'is_current' => true,
            ],
            [
                'league_id' => 48,
                'name' => 'League Cup',
                'type' => 'Cup',
                'logo' => 'https://media.api-sports.io/football/leagues/48.png',
                'country' => 'England',
                'country_code' => 'GB-ENG',
                'country_flag' => 'https://media.api-sports.io/flags/gb-eng.svg',
                'season' => 2025,
                'season_start' => '2025-07-29',
                'season_end' => '2026-02-04',
                'is_current' => true,
            ],
            [
                'league_id' => 45,
                'name' => 'FA Cup',
                'type' => 'Cup',
                'logo' => 'https://media.api-sports.io/football/leagues/45.png',
                'country' => 'England',
                'country_code' => 'GB-ENG',
                'country_flag' => 'https://media.api-sports.io/flags/gb-eng.svg',
                'season' => 2025,
                'season_start' => '2025-08-01',
                'season_end' => '2026-01-10',
                'is_current' => true,
            ],
            [
                'league_id' => 937,
                'name' => 'Emirates Cup',
                'type' => 'Cup',
                'logo' => 'https://media.api-sports.io/football/leagues/937.png',
                'country' => 'World',
                'country_code' => null,
                'country_flag' => null,
                'season' => 2025,
                'season_start' => '2025-08-09',
                'season_end' => '2025-08-09',
                'is_current' => true,
            ],
        ];

        foreach ($competitions as $competition) {
            Competition::updateOrCreate(
                ['league_id' => $competition['league_id']],
                $competition
            );
        }
    }
}
