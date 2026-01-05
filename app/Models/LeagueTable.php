<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueTable extends Model
{
    use HasFactory;

    /**
     * Get the configured team ID.
     */
    public static function getTeamId(): int
    {
        return (int) config('services.football_api.team_id', 42);
    }

    protected $fillable = [
        'league_id',
        'league_name',
        'league_country',
        'league_logo',
        'season',
        'team_id',
        'team_name',
        'team_logo',
        'rank',
        'points',
        'goals_diff',
        'group',
        'form',
        'status',
        'description',
        'played',
        'win',
        'draw',
        'lose',
        'goals_for',
        'goals_against',
        'home_played',
        'home_win',
        'home_draw',
        'home_lose',
        'home_goals_for',
        'home_goals_against',
        'away_played',
        'away_win',
        'away_draw',
        'away_lose',
        'away_goals_for',
        'away_goals_against',
        'last_updated',
    ];

    protected function casts(): array
    {
        return [
            'league_id' => 'integer',
            'season' => 'integer',
            'team_id' => 'integer',
            'rank' => 'integer',
            'points' => 'integer',
            'goals_diff' => 'integer',
            'played' => 'integer',
            'win' => 'integer',
            'draw' => 'integer',
            'lose' => 'integer',
            'goals_for' => 'integer',
            'goals_against' => 'integer',
            'home_played' => 'integer',
            'home_win' => 'integer',
            'home_draw' => 'integer',
            'home_lose' => 'integer',
            'home_goals_for' => 'integer',
            'home_goals_against' => 'integer',
            'away_played' => 'integer',
            'away_win' => 'integer',
            'away_draw' => 'integer',
            'away_lose' => 'integer',
            'away_goals_for' => 'integer',
            'away_goals_against' => 'integer',
            'last_updated' => 'datetime',
        ];
    }

    /**
     * Scope to filter by league and season.
     */
    public function scopeForLeague($query, int $leagueId, int $season)
    {
        return $query->where('league_id', $leagueId)
            ->where('season', $season)
            ->orderBy('rank', 'asc');
    }

    /**
     * Scope to get Premier League standings.
     */
    public function scopePremierLeague($query, int $season = null)
    {
        $season = $season ?? $this->getCurrentSeason();

        return $query->where('league_id', 39)
            ->where('season', $season)
            ->orderBy('rank', 'asc');
    }

    /**
     * Scope to get the configured team's position.
     */
    public function scopeForTeam($query)
    {
        return $query->where('team_id', static::getTeamId());
    }

    /**
     * Alias for scopeForTeam for backward compatibility.
     */
    public function scopeArsenal($query)
    {
        return $query->forTeam();
    }

    /**
     * Get current season.
     */
    protected function getCurrentSeason(): int
    {
        $now = now();
        return $now->month < 8 ? $now->year - 1 : $now->year;
    }

    /**
     * Get win percentage.
     */
    public function getWinPercentageAttribute(): float
    {
        if ($this->played === 0) {
            return 0;
        }

        return round(($this->win / $this->played) * 100, 1);
    }

    /**
     * Get points per game.
     */
    public function getPointsPerGameAttribute(): float
    {
        if ($this->played === 0) {
            return 0;
        }

        return round($this->points / $this->played, 2);
    }
}
