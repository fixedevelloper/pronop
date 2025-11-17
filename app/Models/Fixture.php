<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    protected $fillable = [
        'fixture_id', 'referee', 'timezone', 'timestamp', 'date',
        'st_long', 'st_short', 'st_elapsed', 'league_id',
        'team_home_id', 'team_away_id',
        'team_away_winner', 'team_home_winner',
        'goal_home', 'goal_away',
        'score_ht_home', 'score_ht_away',
        'score_ft_home', 'score_ft_away',
        'score_ext_home', 'score_ext_away',
        'score_pt_home', 'score_pt_away',
    ];

    protected $casts = [
        'team_away_winner' => 'boolean',
        'team_home_winner' => 'boolean',
    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function linePotFoot()
    {
        return $this->hasMany(LinePotFoot::class);
    }
}
