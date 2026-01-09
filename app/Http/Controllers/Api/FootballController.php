<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
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
     * Get all competitions Arsenal is participating in.
     */
    public function competitions(Request $request): JsonResponse
    {
        $season = $request->input('season', $this->footballApi->getCurrentSeason());
        $includeMatchCount = $request->boolean('include_match_count', true);

        $competitions = Competition::current()
            ->season($season)
            ->orderBy('name')
            ->get();

        // If no competitions in database, try to fetch from API
        if ($competitions->isEmpty()) {
            $apiCompetitions = $this->footballApi->getTeamCompetitions($season);

            if ($apiCompetitions) {
                foreach ($apiCompetitions as $data) {
                    $league = $data['league'] ?? [];
                    $country = $data['country'] ?? [];
                    $seasons = $data['seasons'] ?? [];
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

                $competitions = Competition::current()
                    ->season($season)
                    ->orderBy('name')
                    ->get();
            }
        }

        return response()->json([
            'data' => $competitions->map(fn ($competition) => $this->formatCompetition($competition, $includeMatchCount)),
            'meta' => [
                'total' => $competitions->count(),
                'season' => $season,
            ],
        ]);
    }

    /**
     * Get upcoming fixtures with pagination and venue filter.
     */
    public function fixtures(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $season = $request->input('season');
        $venue = $request->input('venue', 'all'); // all, home, away

        $query = Fixture::where('match_date', '>', now())
            ->orderBy('match_date', 'asc');

        // Apply venue filter
        if ($venue === 'home') {
            $query->homeMatches();
        } elseif ($venue === 'away') {
            $query->awayMatches();
        } else {
            $query->forTeam();
        }

        if ($season) {
            $query->season($season);
        }

        $fixtures = $query->paginate($perPage);

        return response()->json([
            'data' => $fixtures->getCollection()->map(fn ($fixture) => $this->formatFixture($fixture)),
            'meta' => [
                'current_page' => $fixtures->currentPage(),
                'per_page' => $fixtures->perPage(),
                'total' => $fixtures->total(),
                'last_page' => $fixtures->lastPage(),
                'from' => $fixtures->firstItem(),
                'to' => $fixtures->lastItem(),
                'season' => $season ?? $this->footballApi->getCurrentSeason(),
                'venue_filter' => $venue,
            ],
            'links' => [
                'first' => $fixtures->url(1),
                'last' => $fixtures->url($fixtures->lastPage()),
                'prev' => $fixtures->previousPageUrl(),
                'next' => $fixtures->nextPageUrl(),
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

        // Get the configured team's position
        $teamPosition = $standings->firstWhere('team_id', $this->footballApi->getTeamId());

        return response()->json([
            'data' => $standings->map(fn ($team) => $this->formatStanding($team)),
            'team' => $teamPosition ? $this->formatStanding($teamPosition) : null,
            'meta' => [
                'total' => $standings->count(),
                'season' => $season,
                'league_id' => $leagueId,
                'league_name' => $standings->first()?->league_name ?? 'Premier League',
            ],
        ]);
    }

    /**
     * Get standings for all competitions Arsenal is in.
     */
    public function allStandings(Request $request): JsonResponse
    {
        $season = $request->input('season', $this->footballApi->getCurrentSeason());

        // Get all competitions
        $competitions = Competition::current()->season($season)->get();

        if ($competitions->isEmpty()) {
            return response()->json([
                'data' => [],
                'message' => 'No competitions found',
            ]);
        }

        $allStandings = [];

        foreach ($competitions as $competition) {
            $standings = LeagueTable::forLeague($competition->league_id, $season)->get();

            if ($standings->isEmpty()) {
                continue;
            }

            // Get the configured team's position
            $teamPosition = $standings->firstWhere('team_id', $this->footballApi->getTeamId());

            // Group standings by group if applicable (for cup competitions)
            $groupedStandings = $standings->groupBy('group');

            $competitionData = [
                'competition' => $this->formatCompetition($competition, false),
                'team_position' => $teamPosition ? $this->formatStanding($teamPosition) : null,
            ];

            if ($groupedStandings->count() > 1) {
                // Multiple groups (e.g., Champions League groups)
                $competitionData['groups'] = $groupedStandings->map(function ($groupTeams, $groupName) {
                    return [
                        'name' => $groupName ?: 'Group',
                        'standings' => $groupTeams->map(fn ($team) => $this->formatStanding($team))->values(),
                    ];
                })->values();
            } else {
                // Single table (e.g., Premier League)
                $competitionData['standings'] = $standings->map(fn ($team) => $this->formatStanding($team));
            }

            $allStandings[] = $competitionData;
        }

        return response()->json([
            'data' => $allStandings,
            'meta' => [
                'total_competitions' => count($allStandings),
                'season' => $season,
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
        $leaguePosition = LeagueTable::where('team_id', $this->footballApi->getTeamId())
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
        $result = MatchResult::forTeam()->orderBy('match_date', 'desc')->first();

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
     * Get match report detail.
     */
    public function matchReport(int $fixtureId): JsonResponse
    {
        $result = MatchResult::where('fixture_id', $fixtureId)->first();

        if (!$result) {
            return response()->json([
                'data' => null,
                'message' => 'Match not found',
            ], 404);
        }

        // If details not fetched yet, try to fetch from API
        if (!$result->details_fetched) {
            $details = $this->footballApi->getMatchDetails($fixtureId);

            $result->update([
                'events' => $details['events'],
                'lineups' => $details['lineups'],
                'statistics' => $details['statistics'],
                'details_fetched' => true,
            ]);

            $result->refresh();
        }

        return response()->json([
            'data' => $this->formatMatchReport($result),
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

    /**
     * Format match report for response.
     */
    protected function formatMatchReport(MatchResult $result): array
    {
        return [
            'match' => $this->formatMatchResult($result),
            'events' => $this->formatEvents($result->events ?? []),
            'lineups' => $this->formatLineups($result->lineups ?? []),
            'statistics' => $this->formatStatistics($result->statistics ?? []),
        ];
    }

    /**
     * Format match events.
     */
    protected function formatEvents(array $events): array
    {
        return collect($events)->map(function ($event) {
            return [
                'time' => [
                    'elapsed' => $event['time']['elapsed'] ?? null,
                    'extra' => $event['time']['extra'] ?? null,
                ],
                'team' => [
                    'id' => $event['team']['id'] ?? null,
                    'name' => $event['team']['name'] ?? null,
                    'logo' => $event['team']['logo'] ?? null,
                ],
                'player' => [
                    'id' => $event['player']['id'] ?? null,
                    'name' => $event['player']['name'] ?? null,
                ],
                'assist' => [
                    'id' => $event['assist']['id'] ?? null,
                    'name' => $event['assist']['name'] ?? null,
                ],
                'type' => $event['type'] ?? null,
                'detail' => $event['detail'] ?? null,
                'comments' => $event['comments'] ?? null,
            ];
        })->values()->all();
    }

    /**
     * Format match lineups.
     */
    protected function formatLineups(array $lineups): array
    {
        return collect($lineups)->map(function ($lineup) {
            return [
                'team' => [
                    'id' => $lineup['team']['id'] ?? null,
                    'name' => $lineup['team']['name'] ?? null,
                    'logo' => $lineup['team']['logo'] ?? null,
                    'colors' => $lineup['team']['colors'] ?? null,
                ],
                'formation' => $lineup['formation'] ?? null,
                'coach' => [
                    'id' => $lineup['coach']['id'] ?? null,
                    'name' => $lineup['coach']['name'] ?? null,
                    'photo' => $lineup['coach']['photo'] ?? null,
                ],
                'startXI' => collect($lineup['startXI'] ?? [])->map(function ($player) {
                    return [
                        'id' => $player['player']['id'] ?? null,
                        'name' => $player['player']['name'] ?? null,
                        'number' => $player['player']['number'] ?? null,
                        'pos' => $player['player']['pos'] ?? null,
                        'grid' => $player['player']['grid'] ?? null,
                    ];
                })->values()->all(),
                'substitutes' => collect($lineup['substitutes'] ?? [])->map(function ($player) {
                    return [
                        'id' => $player['player']['id'] ?? null,
                        'name' => $player['player']['name'] ?? null,
                        'number' => $player['player']['number'] ?? null,
                        'pos' => $player['player']['pos'] ?? null,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Format match statistics.
     */
    protected function formatStatistics(array $statistics): array
    {
        return collect($statistics)->map(function ($teamStats) {
            return [
                'team' => [
                    'id' => $teamStats['team']['id'] ?? null,
                    'name' => $teamStats['team']['name'] ?? null,
                    'logo' => $teamStats['team']['logo'] ?? null,
                ],
                'statistics' => collect($teamStats['statistics'] ?? [])->mapWithKeys(function ($stat) {
                    $type = strtolower(str_replace(' ', '_', $stat['type'] ?? ''));
                    return [$type => $stat['value']];
                })->all(),
            ];
        })->values()->all();
    }

    /**
     * Get list of available seasons.
     */
    public function seasons(): JsonResponse
    {
        $seasons = Competition::select('season')
            ->distinct()
            ->orderBy('season', 'desc')
            ->pluck('season');

        // Add additional info for each season
        $seasonsData = $seasons->map(function ($season) {
            $competitions = Competition::season($season)->count();
            $matches = MatchResult::season($season)->count();

            return [
                'year' => $season,
                'label' => $season . '/' . ($season + 1),
                'competitions_count' => $competitions,
                'matches_count' => $matches,
                'is_current' => $season === $this->footballApi->getCurrentSeason(),
            ];
        });

        return response()->json([
            'data' => $seasonsData,
            'meta' => [
                'total' => $seasons->count(),
                'current_season' => $this->footballApi->getCurrentSeason(),
            ],
        ]);
    }

    /**
     * Format competition for response.
     */
    protected function formatCompetition(Competition $competition, bool $includeMatchCount = true): array
    {
        $data = [
            'id' => $competition->league_id,
            'name' => $competition->name,
            'type' => $competition->type,
            'logo' => $competition->logo,
            'country' => [
                'name' => $competition->country,
                'code' => $competition->country_code,
                'flag' => $competition->country_flag,
            ],
            'season' => [
                'year' => $competition->season,
                'start' => $competition->season_start?->toDateString(),
                'end' => $competition->season_end?->toDateString(),
            ],
        ];

        if ($includeMatchCount) {
            $data['match_count'] = $competition->match_count;
        }

        return $data;
    }
}
