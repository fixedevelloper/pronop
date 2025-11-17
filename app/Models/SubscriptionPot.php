<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pot_id',
        'gateway',
        'amount',
        'reference',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pot()
    {
        return $this->belongsTo(Pot::class);
    }
}
