<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_id',
        'referee',
        'timezone',
        'match_date',
        'timestamp',
        'venue_id',
        'venue_name',
        'venue_city',
        'league_id',
        'league_name',
        'league_country',
        'league_logo',
        'league_round',
        'season',
        'home_team_id',
        'home_team_name',
        'home_team_logo',
        'away_team_id',
        'away_team_name',
        'away_team_logo',
        'home_goals',
        'away_goals',
        'home_goals_halftime',
        'away_goals_halftime',
        'home_goals_fulltime',
        'away_goals_fulltime',
        'home_goals_extratime',
        'away_goals_extratime',
        'home_goals_penalty',
        'away_goals_penalty',
        'status_long',
        'status_short',
        'status_elapsed',
        'arsenal_result',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'timestamp' => 'integer',
            'fixture_id' => 'integer',
            'venue_id' => 'integer',
            'league_id' => 'integer',
            'season' => 'integer',
            'home_team_id' => 'integer',
            'away_team_id' => 'integer',
            'home_goals' => 'integer',
            'away_goals' => 'integer',
            'home_goals_halftime' => 'integer',
            'away_goals_halftime' => 'integer',
            'home_goals_fulltime' => 'integer',
            'away_goals_fulltime' => 'integer',
            'home_goals_extratime' => 'integer',
            'away_goals_extratime' => 'integer',
            'home_goals_penalty' => 'integer',
            'away_goals_penalty' => 'integer',
            'status_elapsed' => 'integer',
        ];
    }

    /**
     * Scope to get recent results.
     */
    public function scopeRecent($query, int $limit = 5)
    {
        return $query->orderBy('match_date', 'desc')->limit($limit);
    }

    /**
     * Scope to filter by season.
     */
    public function scopeSeason($query, int $season)
    {
        return $query->where('season', $season);
    }

    /**
     * Scope to filter by league.
     */
    public function scopeLeague($query, int $leagueId)
    {
        return $query->where('league_id', $leagueId);
    }

    /**
     * Scope to get wins.
     */
    public function scopeWins($query)
    {
        return $query->where('arsenal_result', 'W');
    }

    /**
     * Scope to get draws.
     */
    public function scopeDraws($query)
    {
        return $query->where('arsenal_result', 'D');
    }

    /**
     * Scope to get losses.
     */
    public function scopeLosses($query)
    {
        return $query->where('arsenal_result', 'L');
    }

    /**
     * Check if Arsenal is home team.
     */
    public function isArsenalHome(): bool
    {
        return $this->home_team_id === 42;
    }

    /**
     * Get Arsenal's goals in this match.
     */
    public function getArsenalGoalsAttribute(): int
    {
        return $this->isArsenalHome() ? $this->home_goals : $this->away_goals;
    }

    /**
     * Get opponent's goals in this match.
     */
    public function getOpponentGoalsAttribute(): int
    {
        return $this->isArsenalHome() ? $this->away_goals : $this->home_goals;
    }

    /**
     * Get opponent team name.
     */
    public function getOpponentAttribute(): string
    {
        return $this->isArsenalHome() ? $this->away_team_name : $this->home_team_name;
    }

    /**
     * Get opponent team logo.
     */
    public function getOpponentLogoAttribute(): ?string
    {
        return $this->isArsenalHome() ? $this->away_team_logo : $this->home_team_logo;
    }

    /**
     * Get home/away indicator for Arsenal.
     */
    public function getVenueTypeAttribute(): string
    {
        return $this->isArsenalHome() ? 'H' : 'A';
    }

    /**
     * Get formatted score string.
     */
    public function getScoreAttribute(): string
    {
        return "{$this->home_goals} - {$this->away_goals}";
    }

    /**
     * Calculate Arsenal's result based on the score.
     */
    public static function calculateArsenalResult(int $homeTeamId, int $homeGoals, int $awayGoals): string
    {
        $arsenalTeamId = 42;
        $isArsenalHome = $homeTeamId === $arsenalTeamId;

        $arsenalGoals = $isArsenalHome ? $homeGoals : $awayGoals;
        $opponentGoals = $isArsenalHome ? $awayGoals : $homeGoals;

        if ($arsenalGoals > $opponentGoals) {
            return 'W';
        } elseif ($arsenalGoals < $opponentGoals) {
            return 'L';
        }

        return 'D';
    }
}
