<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class FootballApiService
{
    protected string $baseUrl = 'https://v3.football.api-sports.io';
    protected ?string $apiKey;
    protected int $teamId;
    protected int $premierLeagueId = 39;

    public function __construct()
    {
        $this->apiKey = config('services.football_api.key');
        $this->teamId = (int) config('services.football_api.team_id', 42);
    }

    /**
     * Check if API key is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Make a request to the Football API.
     */
    protected function makeRequest(string $endpoint, array $params = []): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('Football API key not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get("{$this->baseUrl}/{$endpoint}", $params);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['errors']) && !empty($data['errors'])) {
                    Log::error('Football API Error', ['errors' => $data['errors']]);
                    return null;
                }

                return $data['response'] ?? null;
            }

            Log::error('Football API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (RequestException $e) {
            Log::error('Football API Request Exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get Arsenal's fixtures (upcoming matches).
     */
    public function getFixtures(int $season = null, string $status = 'NS'): ?array
    {
        $season = $season ?? $this->getCurrentSeason();

        return $this->makeRequest('fixtures', [
            'team' => $this->teamId,
            'season' => $season,
            'status' => $status,
        ]);
    }

    /**
     * Get Arsenal's next N fixtures.
     */
    public function getNextFixtures(int $count = 5): ?array
    {
        return $this->makeRequest('fixtures', [
            'team' => $this->teamId,
            'next' => $count,
        ]);
    }

    /**
     * Get Arsenal's last N match results.
     */
    public function getLastResults(int $count = 5): ?array
    {
        return $this->makeRequest('fixtures', [
            'team' => $this->teamId,
            'last' => $count,
        ]);
    }

    /**
     * Get Arsenal's match results for a season.
     */
    public function getMatchResults(int $season = null): ?array
    {
        $season = $season ?? $this->getCurrentSeason();

        return $this->makeRequest('fixtures', [
            'team' => $this->teamId,
            'season' => $season,
            'status' => 'FT-AET-PEN',
        ]);
    }

    /**
     * Get league standings.
     */
    public function getLeagueTable(int $season = null, int $leagueId = null): ?array
    {
        $season = $season ?? $this->getCurrentSeason();
        $leagueId = $leagueId ?? $this->premierLeagueId;

        return $this->makeRequest('standings', [
            'league' => $leagueId,
            'season' => $season,
        ]);
    }

    /**
     * Get standings for all competitions the team is in.
     */
    public function getAllCompetitionStandings(int $season = null): array
    {
        $season = $season ?? $this->getCurrentSeason();

        // First get all competitions
        $competitions = $this->getTeamCompetitions($season);

        if (empty($competitions)) {
            return [];
        }

        $standings = [];
        foreach ($competitions as $competition) {
            $leagueId = $competition['league']['id'] ?? null;
            $leagueType = $competition['league']['type'] ?? null;

            if (!$leagueId) {
                continue;
            }

            // Only fetch standings for leagues (not cups without group stages)
            $leagueStandings = $this->getLeagueTable($season, $leagueId);

            if ($leagueStandings) {
                $standings[] = [
                    'league' => $competition['league'],
                    'country' => $competition['country'],
                    'standings' => $leagueStandings,
                ];
            }

            // Small delay to avoid rate limiting
            usleep(100000); // 100ms
        }

        return $standings;
    }

    /**
     * Get team statistics for Arsenal.
     */
    public function getTeamStatistics(int $season = null, int $leagueId = null): ?array
    {
        $season = $season ?? $this->getCurrentSeason();
        $leagueId = $leagueId ?? $this->premierLeagueId;

        return $this->makeRequest('teams/statistics', [
            'team' => $this->teamId,
            'league' => $leagueId,
            'season' => $season,
        ]);
    }

    /**
     * Get fixture by ID.
     */
    public function getFixtureById(int $fixtureId): ?array
    {
        $result = $this->makeRequest('fixtures', [
            'id' => $fixtureId,
        ]);

        return $result[0] ?? null;
    }

    /**
     * Get match events (goals, cards, substitutions) for a fixture.
     */
    public function getFixtureEvents(int $fixtureId): ?array
    {
        return $this->makeRequest('fixtures/events', [
            'fixture' => $fixtureId,
        ]);
    }

    /**
     * Get match lineups for a fixture.
     */
    public function getFixtureLineups(int $fixtureId): ?array
    {
        return $this->makeRequest('fixtures/lineups', [
            'fixture' => $fixtureId,
        ]);
    }

    /**
     * Get match statistics for a fixture.
     */
    public function getFixtureStatistics(int $fixtureId): ?array
    {
        return $this->makeRequest('fixtures/statistics', [
            'fixture' => $fixtureId,
        ]);
    }

    /**
     * Get complete match details (events, lineups, statistics) for a fixture.
     */
    public function getMatchDetails(int $fixtureId): array
    {
        return [
            'events' => $this->getFixtureEvents($fixtureId),
            'lineups' => $this->getFixtureLineups($fixtureId),
            'statistics' => $this->getFixtureStatistics($fixtureId),
        ];
    }

    /**
     * Get live fixtures for the team.
     */
    public function getLiveFixtures(): ?array
    {
        return $this->makeRequest('fixtures', [
            'team' => $this->teamId,
            'live' => 'all',
        ]);
    }

    /**
     * Get all competitions/leagues for the team in current season.
     */
    public function getTeamCompetitions(int $season = null): ?array
    {
        $season = $season ?? $this->getCurrentSeason();

        return $this->makeRequest('leagues', [
            'team' => $this->teamId,
            'season' => $season,
        ]);
    }

    /**
     * Get current football season year.
     */
    public function getCurrentSeason(): int
    {
        $now = now();
        // Football season typically starts in August
        // If we're before August, use previous year
        return $now->month < 8 ? $now->year - 1 : $now->year;
    }

    /**
     * Get team ID.
     */
    public function getTeamId(): int
    {
        return $this->teamId;
    }

    /**
     * Get Premier League ID.
     */
    public function getPremierLeagueId(): int
    {
        return $this->premierLeagueId;
    }
}
