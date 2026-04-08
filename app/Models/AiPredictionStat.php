<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiPredictionStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_prediction_id',
        'real_score',
        'is_score_correct',
        'is_1x2_correct',
        'is_over25_correct',
        'is_btts_correct',
        'accuracy_score'
    ];

    protected $casts = [
        'is_score_correct' => 'boolean',
        'is_1x2_correct' => 'boolean',
        'is_over25_correct' => 'boolean',
        'is_btts_correct' => 'boolean',
        'accuracy_score' => 'float',
    ];

    // 🔗 Relation

    public function prediction()
    {
        return $this->belongsTo(AiPrediction::class, 'ai_prediction_id');
    }
}
