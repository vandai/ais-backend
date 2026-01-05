<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id',
        'name',
        'type',
        'logo',
        'country',
        'country_code',
        'country_flag',
        'season',
        'season_start',
        'season_end',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'league_id' => 'integer',
            'season' => 'integer',
            'season_start' => 'date',
            'season_end' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /**
     * Scope to get current competitions.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope to filter by season.
     */
    public function scopeSeason($query, int $season)
    {
        return $query->where('season', $season);
    }

    /**
     * Scope to filter by type (League, Cup).
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get match count for this competition.
     */
    public function getMatchCountAttribute(): int
    {
        return MatchResult::where('league_id', $this->league_id)
            ->where('season', $this->season)
            ->count();
    }
}
