<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\Fixture;
use App\Models\LeagueTable;
use App\Models\MatchResult;
use App\Services\FootballApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncFootballData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'football:sync
                            {--fixtures : Sync only fixtures}
                            {--results : Sync only match results}
                            {--standings : Sync only league standings}
                            {--details : Sync match details for last 20 matches}
                            {--competitions : Sync only competitions}
                            {--season= : Specific season to sync (default: current)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync football data from api-football.com for Arsenal';

    protected FootballApiService $footballApi;

    public function __construct(FootballApiService $footballApi)
    {
        parent::__construct();
        $this->footballApi = $footballApi;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting football data sync...');

        $season = $this->option('season') ?? $this->footballApi->getCurrentSeason();
        $syncAll = !$this->option('fixtures') && !$this->option('results') && !$this->option('standings') && !$this->option('details') && !$this->option('competitions');

        try {
            if ($syncAll || $this->option('competitions')) {
                $this->syncCompetitions($season);
            }

            if ($syncAll || $this->option('fixtures')) {
                $this->syncFixtures();
            }

            if ($syncAll || $this->option('results')) {
                $this->syncMatchResults($season);
            }

            if ($syncAll || $this->option('standings')) {
                $this->syncLeagueStandings($season);
            }

            if ($syncAll || $this->option('details')) {
                $this->syncMatchDetails();
            }

            $this->info('Football data sync completed successfully!');
            Log::info('Football data sync completed successfully');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Football data sync failed: ' . $e->getMessage());
            Log::error('Football data sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Sync upcoming fixtures.
     */
    protected function syncFixtures(): void
    {
        $this->info('Syncing upcoming fixtures...');

        $fixtures = $this->footballApi->getNextFixtures(10);

        if (empty($fixtures)) {
            $this->warn('No fixtures data received from API');
            return;
        }

        $count = 0;
        foreach ($fixtures as $fixtureData) {
            $this->upsertFixture($fixtureData);
            $count++;
        }

        $this->info("Synced {$count} fixtures");
    }

    /**
     * Sync match results.
     */
    protected function syncMatchResults(int $season): void
    {
        $this->info("Syncing match results for season {$season}...");

        $results = $this->footballApi->getMatchResults($season);

        if (empty($results)) {
            $this->warn('No match results data received from API');
            return;
        }

        $count = 0;
        foreach ($results as $resultData) {
            $this->upsertMatchResult($resultData);
            $count++;
        }

        $this->info("Synced {$count} match results");
    }

    /**
     * Sync league standings.
     */
    protected function syncLeagueStandings(int $season): void
    {
        $this->info("Syncing Premier League standings for season {$season}...");

        $standings = $this->footballApi->getLeagueTable($season);

        if (empty($standings)) {
            $this->warn('No standings data received from API');
            return;
        }

        // API returns standings nested in league structure
        $leagueData = $standings[0] ?? null;

        if (!$leagueData || empty($leagueData['league']['standings'])) {
            $this->warn('Invalid standings data structure');
            return;
        }

        $league = $leagueData['league'];
        $standingsData = $league['standings'][0] ?? [];

        $count = 0;
        foreach ($standingsData as $teamStanding) {
            $this->upsertLeagueTable($league, $teamStanding, $season);
            $count++;
        }

        $this->info("Synced {$count} league table entries");
    }

    /**
     * Upsert fixture record.
     */
    protected function upsertFixture(array $data): void
    {
        $fixture = $data['fixture'] ?? [];
        $league = $data['league'] ?? [];
        $teams = $data['teams'] ?? [];

        Fixture::updateOrCreate(
            ['fixture_id' => $fixture['id']],
            [
                'referee' => $fixture['referee'] ?? null,
                'timezone' => $fixture['timezone'] ?? 'UTC',
                'match_date' => $fixture['date'] ?? null,
                'timestamp' => $fixture['timestamp'] ?? 0,
                'venue_id' => $fixture['venue']['id'] ?? null,
                'venue_name' => $fixture['venue']['name'] ?? null,
                'venue_city' => $fixture['venue']['city'] ?? null,
                'league_id' => $league['id'] ?? 0,
                'league_name' => $league['name'] ?? '',
                'league_country' => $league['country'] ?? null,
                'league_logo' => $league['logo'] ?? null,
                'league_round' => $league['round'] ?? null,
                'season' => $league['season'] ?? $this->footballApi->getCurrentSeason(),
                'home_team_id' => $teams['home']['id'] ?? 0,
                'home_team_name' => $teams['home']['name'] ?? '',
                'home_team_logo' => $teams['home']['logo'] ?? null,
                'away_team_id' => $teams['away']['id'] ?? 0,
                'away_team_name' => $teams['away']['name'] ?? '',
                'away_team_logo' => $teams['away']['logo'] ?? null,
                'status_long' => $fixture['status']['long'] ?? null,
                'status_short' => $fixture['status']['short'] ?? null,
                'status_elapsed' => $fixture['status']['elapsed'] ?? null,
            ]
        );
    }

    /**
     * Upsert match result record.
     */
    protected function upsertMatchResult(array $data): void
    {
        $fixture = $data['fixture'] ?? [];
        $league = $data['league'] ?? [];
        $teams = $data['teams'] ?? [];
        $goals = $data['goals'] ?? [];
        $score = $data['score'] ?? [];

        $homeTeamId = $teams['home']['id'] ?? 0;
        $homeGoals = $goals['home'] ?? 0;
        $awayGoals = $goals['away'] ?? 0;

        MatchResult::updateOrCreate(
            ['fixture_id' => $fixture['id']],
            [
                'referee' => $fixture['referee'] ?? null,
                'timezone' => $fixture['timezone'] ?? 'UTC',
                'match_date' => $fixture['date'] ?? null,
                'timestamp' => $fixture['timestamp'] ?? 0,
                'venue_id' => $fixture['venue']['id'] ?? null,
                'venue_name' => $fixture['venue']['name'] ?? null,
                'venue_city' => $fixture['venue']['city'] ?? null,
                'league_id' => $league['id'] ?? 0,
                'league_name' => $league['name'] ?? '',
                'league_country' => $league['country'] ?? null,
                'league_logo' => $league['logo'] ?? null,
                'league_round' => $league['round'] ?? null,
                'season' => $league['season'] ?? $this->footballApi->getCurrentSeason(),
                'home_team_id' => $homeTeamId,
                'home_team_name' => $teams['home']['name'] ?? '',
                'home_team_logo' => $teams['home']['logo'] ?? null,
                'away_team_id' => $teams['away']['id'] ?? 0,
                'away_team_name' => $teams['away']['name'] ?? '',
                'away_team_logo' => $teams['away']['logo'] ?? null,
                'home_goals' => $homeGoals,
                'away_goals' => $awayGoals,
                'home_goals_halftime' => $score['halftime']['home'] ?? null,
                'away_goals_halftime' => $score['halftime']['away'] ?? null,
                'home_goals_fulltime' => $score['fulltime']['home'] ?? null,
                'away_goals_fulltime' => $score['fulltime']['away'] ?? null,
                'home_goals_extratime' => $score['extratime']['home'] ?? null,
                'away_goals_extratime' => $score['extratime']['away'] ?? null,
                'home_goals_penalty' => $score['penalty']['home'] ?? null,
                'away_goals_penalty' => $score['penalty']['away'] ?? null,
                'status_long' => $fixture['status']['long'] ?? null,
                'status_short' => $fixture['status']['short'] ?? null,
                'status_elapsed' => $fixture['status']['elapsed'] ?? null,
                'arsenal_result' => MatchResult::calculateArsenalResult($homeTeamId, $homeGoals, $awayGoals),
            ]
        );
    }

    /**
     * Upsert league table record.
     */
    protected function upsertLeagueTable(array $league, array $standing, int $season): void
    {
        $team = $standing['team'] ?? [];
        $all = $standing['all'] ?? [];
        $home = $standing['home'] ?? [];
        $away = $standing['away'] ?? [];

        LeagueTable::updateOrCreate(
            [
                'league_id' => $league['id'] ?? 0,
                'season' => $season,
                'team_id' => $team['id'] ?? 0,
            ],
            [
                'league_name' => $league['name'] ?? '',
                'league_country' => $league['country'] ?? null,
                'league_logo' => $league['logo'] ?? null,
                'team_name' => $team['name'] ?? '',
                'team_logo' => $team['logo'] ?? null,
                'rank' => $standing['rank'] ?? 0,
                'points' => $standing['points'] ?? 0,
                'goals_diff' => $standing['goalsDiff'] ?? 0,
                'group' => $standing['group'] ?? null,
                'form' => $standing['form'] ?? null,
                'status' => $standing['status'] ?? null,
                'description' => $standing['description'] ?? null,
                'played' => $all['played'] ?? 0,
                'win' => $all['win'] ?? 0,
                'draw' => $all['draw'] ?? 0,
                'lose' => $all['lose'] ?? 0,
                'goals_for' => $all['goals']['for'] ?? 0,
                'goals_against' => $all['goals']['against'] ?? 0,
                'home_played' => $home['played'] ?? 0,
                'home_win' => $home['win'] ?? 0,
                'home_draw' => $home['draw'] ?? 0,
                'home_lose' => $home['lose'] ?? 0,
                'home_goals_for' => $home['goals']['for'] ?? 0,
                'home_goals_against' => $home['goals']['against'] ?? 0,
                'away_played' => $away['played'] ?? 0,
                'away_win' => $away['win'] ?? 0,
                'away_draw' => $away['draw'] ?? 0,
                'away_lose' => $away['lose'] ?? 0,
                'away_goals_for' => $away['goals']['for'] ?? 0,
                'away_goals_against' => $away['goals']['against'] ?? 0,
                'last_updated' => now(),
            ]
        );
    }

    /**
     * Sync match details for the last 20 matches.
     */
    protected function syncMatchDetails(): void
    {
        $this->info('Syncing match details for last 20 matches...');

        // Get last 20 matches that don't have details fetched yet, or all if forced
        $matches = MatchResult::forTeam()
            ->where('details_fetched', false)
            ->orderBy('match_date', 'desc')
            ->limit(20)
            ->get();

        if ($matches->isEmpty()) {
            $this->info('No matches need detail syncing');
            return;
        }

        $count = 0;
        foreach ($matches as $match) {
            $this->info("Fetching details for fixture {$match->fixture_id}...");

            $details = $this->footballApi->getMatchDetails($match->fixture_id);

            $match->update([
                'events' => $details['events'],
                'lineups' => $details['lineups'],
                'statistics' => $details['statistics'],
                'details_fetched' => true,
            ]);

            $count++;

            // Small delay to avoid hitting API rate limits
            usleep(250000); // 250ms delay between requests
        }

        $this->info("Synced details for {$count} matches");
    }

    /**
     * Sync competitions for the team.
     */
    protected function syncCompetitions(int $season): void
    {
        $this->info("Syncing competitions for season {$season}...");

        $competitions = $this->footballApi->getTeamCompetitions($season);

        if (empty($competitions)) {
            $this->warn('No competitions data received from API');
            return;
        }

        // Mark all existing competitions as not current
        Competition::where('season', $season)->update(['is_current' => false]);

        $count = 0;
        foreach ($competitions as $competitionData) {
            $this->upsertCompetition($competitionData, $season);
            $count++;
        }

        $this->info("Synced {$count} competitions");
    }

    /**
     * Upsert competition record.
     */
    protected function upsertCompetition(array $data, int $season): void
    {
        $league = $data['league'] ?? [];
        $country = $data['country'] ?? [];
        $seasons = $data['seasons'] ?? [];

        // Find current season data
        $currentSeason = collect($seasons)->firstWhere('current', true) ?? [];

        Competition::updateOrCreate(
            ['league_id' => $league['id']],
            [
                'name' => $league['name'] ?? '',
                'type' => $league['type'] ?? null,
                'logo' => $league['logo'] ?? null,
                'country' => $country['name'] ?? null,
                'country_code' => $country['code'] ?? null,
                'country_flag' => $country['flag'] ?? null,
                'season' => $currentSeason['year'] ?? $season,
                'season_start' => $currentSeason['start'] ?? null,
                'season_end' => $currentSeason['end'] ?? null,
                'is_current' => true,
            ]
        );
    }
}
