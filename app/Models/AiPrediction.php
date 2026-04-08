<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_id',
        'match_name',
        'score_exact',
        'confidence',
        'raw_response',
        'predicted_at',
        'source',
        'model',
        'analyse_fixture',
        'form_teams',
        'h2h',
        'stat_offensive',
        'stat_defensive',
        'is_cached'
    ];

    protected $casts = [
        'raw_response' => 'array',
        'confidence' => 'float',
        'predicted_at' => 'datetime',
        'is_cached' => 'boolean',
    ];

    // 🔗 Relations

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    public function details()
    {
        return $this->hasOne(AiPredictionDetail::class);
    }

    public function stats()
    {
        return $this->hasOne(AiPredictionStat::class);
    }
}
