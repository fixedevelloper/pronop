<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiPredictionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_prediction_id',

        'home_win_prob',
        'draw_prob',
        'away_win_prob',

        'over_1_5',
        'over_2_5',
        'over_3_5',
        'under_2_5',

        'btts_yes',
        'btts_no',

        'odds_home',
        'odds_draw',
        'odds_away',
        'odds_over_2_5',
        'odds_under_2_5',

        'best_bets'
    ];

    protected $casts = [
        'home_win_prob' => 'float',
        'draw_prob' => 'float',
        'away_win_prob' => 'float',

        'over_1_5' => 'float',
        'over_2_5' => 'float',
        'over_3_5' => 'float',
        'under_2_5' => 'float',

        'btts_yes' => 'float',
        'btts_no' => 'float',

        'odds_home' => 'float',
        'odds_draw' => 'float',
        'odds_away' => 'float',
        'odds_over_2_5' => 'float',
        'odds_under_2_5' => 'float',

        'best_bets' => 'array',
    ];

    // 🔗 Relation

    public function prediction()
    {
        return $this->belongsTo(AiPrediction::class, 'ai_prediction_id');
    }
}
