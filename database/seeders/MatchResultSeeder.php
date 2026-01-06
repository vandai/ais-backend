<?php

namespace Database\Seeders;

use App\Models\MatchResult;
use Illuminate\Database\Seeder;

class MatchResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $results = [
            [
                'fixture_id' => 1371778,
                'referee' => 'Jansen Foo Chuan Hui',
                'timezone' => 'UTC',
                'match_date' => '2025-07-23 11:30:00',
                'timestamp' => 1753270200,
                'venue_id' => 21415,
                'venue_name' => 'National Stadium',
                'venue_city' => 'Kallang',
                'league_id' => 667,
                'league_name' => 'Friendlies Clubs',
                'league_country' => 'World',
                'league_logo' => 'https://media.api-sports.io/football/leagues/667.png',
                'league_round' => 'Club Friendlies 1',
                'season' => 2025,
                'home_team_id' => 42,
                'home_team_name' => 'Arsenal',
                'home_team_logo' => 'https://media.api-sports.io/football/teams/42.png',
                'away_team_id' => 489,
                'away_team_name' => 'AC Milan',
                'away_team_logo' => 'https://media.api-sports.io/football/teams/489.png',
                'home_goals' => 1,
                'away_goals' => 0,
                'home_goals_halftime' => 0,
                'away_goals_halftime' => 0,
                'home_goals_fulltime' => 1,
                'away_goals_fulltime' => 0,
                'status_long' => 'Match Finished',
                'status_short' => 'FT',
                'status_elapsed' => 90,
                'arsenal_result' => 'W',
            ],
            [
                'fixture_id' => 1371780,
                'referee' => 'Clarence Leow Hong Wei',
                'timezone' => 'UTC',
                'match_date' => '2025-07-27 11:30:00',
                'timestamp' => 1753615800,
                'venue_id' => 21415,
                'venue_name' => 'National Stadium',
                'venue_city' => 'Kallang',
                'league_id' => 667,
                'league_name' => 'Friendlies Clubs',
                'league_country' => 'World',
                'league_logo' => 'https://media.api-sports.io/football/leagues/667.png',
                'league_round' => 'Club Friendlies 1',
                'season' => 2025,
                'home_team_id' => 42,
                'home_team_name' => 'Arsenal',
                'home_team_logo' => 'https://media.api-sports.io/football/teams/42.png',
                'away_team_id' => 34,
                'away_team_name' => 'Newcastle',
                'away_team_logo' => 'https://media.api-sports.io/football/teams/34.png',
                'home_goals' => 3,
                'away_goals' => 2,
                'home_goals_halftime' => 2,
                'away_goals_halftime' => 1,
                'home_goals_fulltime' => 3,
                'away_goals_fulltime' => 2,
                'status_long' => 'Match Finished',
                'status_short' => 'FT',
                'status_elapsed' => 90,
                'arsenal_result' => 'W',
            ],
            [
                'fixture_id' => 1379019,
                'referee' => 'Michael Oliver',
                'timezone' => 'UTC',
                'match_date' => '2025-08-16 11:30:00',
                'timestamp' => 1755348600,
                'venue_id' => 494,
                'venue_name' => 'Emirates Stadium',
                'venue_city' => 'London',
                'league_id' => 39,
                'league_name' => 'Premier League',
                'league_country' => 'England',
                'league_logo' => 'https://media.api-sports.io/football/leagues/39.png',
                'league_round' => 'Regular Season - 1',
                'season' => 2025,
                'home_team_id' => 42,
                'home_team_name' => 'Arsenal',
                'home_team_logo' => 'https://media.api-sports.io/football/teams/42.png',
                'away_team_id' => 39,
                'away_team_name' => 'Wolves',
                'away_team_logo' => 'https://media.api-sports.io/football/teams/39.png',
                'home_goals' => 2,
                'away_goals' => 0,
                'home_goals_halftime' => 1,
                'away_goals_halftime' => 0,
                'home_goals_fulltime' => 2,
                'away_goals_fulltime' => 0,
                'status_long' => 'Match Finished',
                'status_short' => 'FT',
                'status_elapsed' => 90,
                'arsenal_result' => 'W',
            ],
        ];

        foreach ($results as $result) {
            MatchResult::updateOrCreate(
                ['fixture_id' => $result['fixture_id']],
                $result
            );
        }
    }
}
