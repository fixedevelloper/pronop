<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IaUserMatchUnlock extends Model
{
    protected $fillable = [
        'user_id',
        'fixture_id',
        'type',
        'price',
        'unlocked_at'
    ];

    protected $casts = [
        'price' => 'integer',
        'unlocked_at' => 'datetime'
    ];

    protected $appends = [
        'is_free'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES (🔥 ULTRA UTILE)
    |--------------------------------------------------------------------------
    */

    public function scopeForUser(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAnalysis(Builder $query)
    {
        return $query->where('type', 'analysis');
    }

    public function scopePrediction(Builder $query)
    {
        return $query->where('type', 'prediction');
    }

    public function scopePaid(Builder $query)
    {
        return $query->where('price', '>', 0);
    }

    public function scopeFree(Builder $query)
    {
        return $query->where('price', 0);
    }

    public function scopeToday(Builder $query)
    {
        return $query->whereDate('created_at', now());
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getIsFreeAttribute(): bool
    {
        return $this->price === 0;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS (🔥 BUSINESS LOGIC)
    |--------------------------------------------------------------------------
    */

    public function isAnalysis(): bool
    {
        return $this->type === 'analysis';
    }

    public function isPrediction(): bool
    {
        return $this->type === 'prediction';
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC HELPERS (🔥 IMPORTANT POUR TON FLOW)
    |--------------------------------------------------------------------------
    */

    public static function alreadyUnlocked(int $userId, int $fixtureId, string $type = 'analysis'): bool
    {
        return self::where([
            'user_id' => $userId,
            'fixture_id' => $fixtureId,
            'type' => $type
        ])->exists();
    }
}
