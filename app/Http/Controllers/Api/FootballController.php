<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\LeagueTable;
use App\Models\MatchResult;
use App\Services\FootballApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FootballController extends Controller
{
    protected FootballApiService $footballApi;

    public function __construct(FootballApiService $footballApi)
    {
        $this->footballApi = $footballApi;
    }

    /**
     * Get upcoming fixtures.
     */
    public function fixtures(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);
        $season = $request->input('season');

        $query = Fixture::upcoming();

        if ($season) {
            $query->season($season);
        }

        $fixtures = $query->limit($limit)->get();

        return response()->json([
            'data' => $fixtures->map(fn ($fixture) => $this->formatFixture($fixture)),
            'meta' => [
                'total' => $fixtures->count(),
                'season' => $season ?? $this->footballApi->getCurrentSeason(),
            ],
        ]);
    }

    /**
     * Get match results.
     */
    public function results(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $season = $request->input('season', $this->footballApi->getCurrentSeason());
        $leagueId = $request->input('league_id');

        $query = MatchResult::season($season)->orderBy('match_date', 'desc');

        if ($leagueId) {
            $query->league($leagueId);
        }

        $results = $query->limit($limit)->get();

        return response()->json([
            'data' => $results->map(fn ($result) => $this->formatMatchResult($result)),
            'meta' => [
                'total' => $results->count(),
                'season' => $season,
            ],
        ]);
    }

    /**
     * Get league standings.
     */
    public function standings(Request $request): JsonResponse
    {
        $season = $request->input('season', $this->footballApi->getCurrentSeason());
        $leagueId = $request->input('league_id', $this->footballApi->getPremierLeagueId());

        $standings = LeagueTable::forLeague($leagueId, $season)->get();

        // Get Arsenal's position
        $arsenalPosition = $standings->firstWhere('team_id', $this->footballApi->getArsenalTeamId());

        return response()->json([
            'data' => $standings->map(fn ($team) => $this->formatStanding($team)),
            'arsenal' => $arsenalPosition ? $this->formatStanding($arsenalPosition) : null,
            'meta' => [
                'total' => $standings->count(),
                'season' => $season,
                'league_id' => $leagueId,
                'league_name' => $standings->first()?->league_name ?? 'Premier League',
            ],
        ]);
    }

    /**
     * Get Arsenal's season statistics.
     */
    public function arsenalStats(Request $request): JsonResponse
    {
        $season = $request->input('season', $this->footballApi->getCurrentSeason());

        // Get match results for the season
        $results = MatchResult::season($season)->get();

        // Calculate stats
        $totalMatches = $results->count();
        $wins = $results->where('arsenal_result', 'W')->count();
        $draws = $results->where('arsenal_result', 'D')->count();
        $losses = $results->where('arsenal_result', 'L')->count();

        $goalsFor = $results->sum(function ($match) {
            return $match->isArsenalHome() ? $match->home_goals : $match->away_goals;
        });

        $goalsAgainst = $results->sum(function ($match) {
            return $match->isArsenalHome() ? $match->away_goals : $match->home_goals;
        });

        // Get current league position
        $leaguePosition = LeagueTable::where('team_id', $this->footballApi->getArsenalTeamId())
            ->where('season', $season)
            ->where('league_id', $this->footballApi->getPremierLeagueId())
            ->first();

        // Recent form (last 5 matches)
        $recentForm = MatchResult::season($season)
            ->orderBy('match_date', 'desc')
            ->limit(5)
            ->pluck('arsenal_result')
            ->reverse()
            ->values()
            ->implode('');

        return response()->json([
            'data' => [
                'season' => $season,
                'matches' => [
                    'played' => $totalMatches,
                    'won' => $wins,
                    'drawn' => $draws,
                    'lost' => $losses,
                ],
                'goals' => [
                    'for' => $goalsFor,
                    'against' => $goalsAgainst,
                    'difference' => $goalsFor - $goalsAgainst,
                ],
                'form' => $recentForm,
                'league_position' => $leaguePosition ? [
                    'rank' => $leaguePosition->rank,
                    'points' => $leaguePosition->points,
                    'played' => $leaguePosition->played,
                ] : null,
            ],
        ]);
    }

    /**
     * Get next fixture.
     */
    public function nextFixture(): JsonResponse
    {
        $fixture = Fixture::upcoming()->first();

        if (!$fixture) {
            return response()->json([
                'data' => null,
                'message' => 'No upcoming fixtures found',
            ]);
        }

        return response()->json([
            'data' => $this->formatFixture($fixture),
        ]);
    }

    /**
     * Get last match result.
     */
    public function lastResult(): JsonResponse
    {
        $result = MatchResult::orderBy('match_date', 'desc')->first();

        if (!$result) {
            return response()->json([
                'data' => null,
                'message' => 'No match results found',
            ]);
        }

        return response()->json([
            'data' => $this->formatMatchResult($result),
        ]);
    }

    /**
     * Format fixture for response.
     */
    protected function formatFixture(Fixture $fixture): array
    {
        return [
            'id' => $fixture->fixture_id,
            'date' => $fixture->match_date->toIso8601String(),
            'timestamp' => $fixture->timestamp,
            'venue' => [
                'name' => $fixture->venue_name,
                'city' => $fixture->venue_city,
            ],
            'league' => [
                'id' => $fixture->league_id,
                'name' => $fixture->league_name,
                'round' => $fixture->league_round,
                'logo' => $fixture->league_logo,
            ],
            'home' => [
                'id' => $fixture->home_team_id,
                'name' => $fixture->home_team_name,
                'logo' => $fixture->home_team_logo,
            ],
            'away' => [
                'id' => $fixture->away_team_id,
                'name' => $fixture->away_team_name,
                'logo' => $fixture->away_team_logo,
            ],
            'arsenal' => [
                'is_home' => $fixture->isArsenalHome(),
                'opponent' => $fixture->opponent,
                'opponent_logo' => $fixture->opponent_logo,
                'venue_type' => $fixture->venue_type,
            ],
            'status' => [
                'long' => $fixture->status_long,
                'short' => $fixture->status_short,
            ],
        ];
    }

    /**
     * Format match result for response.
     */
    protected function formatMatchResult(MatchResult $result): array
    {
        return [
            'id' => $result->fixture_id,
            'date' => $result->match_date->toIso8601String(),
            'venue' => [
                'name' => $result->venue_name,
                'city' => $result->venue_city,
            ],
            'league' => [
                'id' => $result->league_id,
                'name' => $result->league_name,
                'round' => $result->league_round,
                'logo' => $result->league_logo,
            ],
            'home' => [
                'id' => $result->home_team_id,
                'name' => $result->home_team_name,
                'logo' => $result->home_team_logo,
                'goals' => $result->home_goals,
            ],
            'away' => [
                'id' => $result->away_team_id,
                'name' => $result->away_team_name,
                'logo' => $result->away_team_logo,
                'goals' => $result->away_goals,
            ],
            'score' => [
                'home' => $result->home_goals,
                'away' => $result->away_goals,
                'display' => $result->score,
                'halftime' => [
                    'home' => $result->home_goals_halftime,
                    'away' => $result->away_goals_halftime,
                ],
            ],
            'arsenal' => [
                'is_home' => $result->isArsenalHome(),
                'opponent' => $result->opponent,
                'opponent_logo' => $result->opponent_logo,
                'venue_type' => $result->venue_type,
                'goals_for' => $result->arsenal_goals,
                'goals_against' => $result->opponent_goals,
                'result' => $result->arsenal_result,
            ],
        ];
    }

    /**
     * Format standing for response.
     */
    protected function formatStanding(LeagueTable $standing): array
    {
        return [
            'rank' => $standing->rank,
            'team' => [
                'id' => $standing->team_id,
                'name' => $standing->team_name,
                'logo' => $standing->team_logo,
            ],
            'points' => $standing->points,
            'goals_diff' => $standing->goals_diff,
            'form' => $standing->form,
            'description' => $standing->description,
            'stats' => [
                'played' => $standing->played,
                'won' => $standing->win,
                'drawn' => $standing->draw,
                'lost' => $standing->lose,
                'goals_for' => $standing->goals_for,
                'goals_against' => $standing->goals_against,
            ],
            'home' => [
                'played' => $standing->home_played,
                'won' => $standing->home_win,
                'drawn' => $standing->home_draw,
                'lost' => $standing->home_lose,
                'goals_for' => $standing->home_goals_for,
                'goals_against' => $standing->home_goals_against,
            ],
            'away' => [
                'played' => $standing->away_played,
                'won' => $standing->away_win,
                'drawn' => $standing->away_draw,
                'lost' => $standing->away_lose,
                'goals_for' => $standing->away_goals_for,
                'goals_against' => $standing->away_goals_against,
            ],
        ];
    }
}
