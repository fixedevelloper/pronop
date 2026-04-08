<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Fixture extends Model
{
    protected $fillable = [
        'fixture_id', 'referee',
        'timezone', 'timestamp', 'date',
        'st_long', 'st_short', 'st_elapsed',
        'league_id',
        'team_home_logo', 'team_away_logo',
        'team_home_name', 'team_away_name',
        'team_away_winner', 'team_home_winner',
        'goal_home', 'goal_away',
        'score_ht_home', 'score_ht_away',
        'score_ft_home', 'score_ft_away',
        'score_ext_home', 'score_ext_away',
        'score_pt_home', 'score_pt_away',
        'day_timestamp',
    ];

    protected $casts = [
        'team_away_winner' => 'boolean',
        'team_home_winner' => 'boolean',
        'date' => 'datetime',
        'timestamp' => 'datetime',
    ];

    protected $appends = [
        'match_name',
        'score',
        'is_unlocked'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'league_id');
    }

    public function linePotFoot()
    {
        return $this->hasMany(LinePotFoot::class);
    }

    public function unlocks()
    {
        return $this->hasMany(IaUserMatchUnlock::class);
    }

    public function aiPrediction()
    {
        return $this->hasOne(AiPrediction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (🔥 IMPORTANT POUR LE FRONT)
    |--------------------------------------------------------------------------
    */

    public function getMatchNameAttribute()
    {
        return "{$this->team_home_name} vs {$this->team_away_name}";
    }

    public function getScoreAttribute()
    {
        return "{$this->goal_home} - {$this->goal_away}";
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES (🔥 ULTRA UTILE POUR API)
    |--------------------------------------------------------------------------
    */

    public function scopeWithPrediction(Builder $query)
    {
        return $query->whereHas('aiPrediction');
    }

    public function scopePlayed(Builder $query)
    {
        return $query->whereNotNull('goal_home')
            ->whereNotNull('goal_away');
    }

    public function scopeToday(Builder $query)
    {
        return $query->whereDate('date', now()->toDateString());
    }

    public function scopeUpcoming(Builder $query)
    {
        return $query->where('date', '>', now());
    }
    public function getIsUnlockedAttribute(): bool
    {
        return $this->isUnlock();
    }
    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    public function isUnlock(): bool
    {
        return $this->aiPrediction()->exists();
    }

    public function isPlayed(): bool
    {
        return !is_null($this->goal_home) && !is_null($this->goal_away);
    }

    public function winner(): ?string
    {
        if (!$this->isPlayed()) return null;

        if ($this->goal_home > $this->goal_away) return 'home';
        if ($this->goal_home < $this->goal_away) return 'away';

        return 'draw';
    }
}
