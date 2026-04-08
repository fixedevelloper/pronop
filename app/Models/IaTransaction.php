<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IaTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'reference'
    ];

    protected $casts = [
        'amount' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
