<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSystemUnlockFixture extends Model
{
    protected $table = 'ai_system_unlock_fixtures';

    protected $fillable = [
        'fixture_id',
        'date_play',
        'is_free',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'date_play' => 'date',
    ];

    /**
     * 🔗 Relation vers Fixture
     */
    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class, 'fixture_id', 'id');
    }
}
