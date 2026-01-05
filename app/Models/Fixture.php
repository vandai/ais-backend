<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
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
        'status_long',
        'status_short',
        'status_elapsed',
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
            'status_elapsed' => 'integer',
        ];
    }

    /**
     * Scope to filter fixtures for the configured team.
     */
    public function scopeForTeam($query)
    {
        $teamId = static::getTeamId();
        return $query->where(function ($q) use ($teamId) {
            $q->where('home_team_id', $teamId)
              ->orWhere('away_team_id', $teamId);
        });
    }

    /**
     * Scope to get upcoming fixtures for the team.
     */
    public function scopeUpcoming($query)
    {
        return $query->forTeam()
            ->where('match_date', '>', now())
            ->orderBy('match_date', 'asc');
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
     * Check if the configured team is home team.
     */
    public function isTeamHome(): bool
    {
        return $this->home_team_id === static::getTeamId();
    }

    /**
     * Alias for isTeamHome for backward compatibility.
     */
    public function isArsenalHome(): bool
    {
        return $this->isTeamHome();
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
}
