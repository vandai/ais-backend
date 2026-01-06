<?php

namespace Database\Seeders;

use App\Models\LeagueTable;
use Illuminate\Database\Seeder;

class LeagueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $standings = [
            [
                'league_id' => 39,
                'league_name' => 'Premier League',
                'league_country' => 'England',
                'league_logo' => 'https://media.api-sports.io/football/leagues/39.png',
                'season' => 2025,
                'team_id' => 42,
                'team_name' => 'Arsenal',
                'team_logo' => 'https://media.api-sports.io/football/teams/42.png',
                'rank' => 1,
                'points' => 48,
                'goals_diff' => 26,
                'group' => 'Premier League',
                'form' => 'WWWWW',
                'status' => 'same',
                'description' => 'Promotion - Champions League (League phase)',
                'played' => 20,
                'win' => 15,
                'draw' => 3,
                'lose' => 2,
                'goals_for' => 40,
                'goals_against' => 14,
                'home_played' => 10,
                'home_win' => 9,
                'home_draw' => 1,
                'home_lose' => 0,
                'home_goals_for' => 26,
                'home_goals_against' => 5,
                'away_played' => 10,
                'away_win' => 6,
                'away_draw' => 2,
                'away_lose' => 2,
                'away_goals_for' => 14,
                'away_goals_against' => 9,
                'last_updated' => now(),
            ],
            [
                'league_id' => 39,
                'league_name' => 'Premier League',
                'league_country' => 'England',
                'league_logo' => 'https://media.api-sports.io/football/leagues/39.png',
                'season' => 2025,
                'team_id' => 50,
                'team_name' => 'Manchester City',
                'team_logo' => 'https://media.api-sports.io/football/teams/50.png',
                'rank' => 2,
                'points' => 42,
                'goals_diff' => 26,
                'group' => 'Premier League',
                'form' => 'DDWWW',
                'status' => 'same',
                'description' => 'Promotion - Champions League (League phase)',
                'played' => 20,
                'win' => 13,
                'draw' => 3,
                'lose' => 4,
                'goals_for' => 44,
                'goals_against' => 18,
                'home_played' => 10,
                'home_win' => 8,
                'home_draw' => 1,
                'home_lose' => 1,
                'home_goals_for' => 26,
                'home_goals_against' => 7,
                'away_played' => 10,
                'away_win' => 5,
                'away_draw' => 2,
                'away_lose' => 3,
                'away_goals_for' => 18,
                'away_goals_against' => 11,
                'last_updated' => now(),
            ],
            [
                'league_id' => 39,
                'league_name' => 'Premier League',
                'league_country' => 'England',
                'league_logo' => 'https://media.api-sports.io/football/leagues/39.png',
                'season' => 2025,
                'team_id' => 40,
                'team_name' => 'Liverpool',
                'team_logo' => 'https://media.api-sports.io/football/teams/40.png',
                'rank' => 3,
                'points' => 40,
                'goals_diff' => 20,
                'group' => 'Premier League',
                'form' => 'WDWWL',
                'status' => 'same',
                'description' => 'Promotion - Champions League (League phase)',
                'played' => 20,
                'win' => 12,
                'draw' => 4,
                'lose' => 4,
                'goals_for' => 38,
                'goals_against' => 18,
                'home_played' => 10,
                'home_win' => 7,
                'home_draw' => 2,
                'home_lose' => 1,
                'home_goals_for' => 22,
                'home_goals_against' => 8,
                'away_played' => 10,
                'away_win' => 5,
                'away_draw' => 2,
                'away_lose' => 3,
                'away_goals_for' => 16,
                'away_goals_against' => 10,
                'last_updated' => now(),
            ],
        ];

        foreach ($standings as $standing) {
            LeagueTable::updateOrCreate(
                [
                    'league_id' => $standing['league_id'],
                    'season' => $standing['season'],
                    'team_id' => $standing['team_id'],
                ],
                $standing
            );
        }
    }
}
