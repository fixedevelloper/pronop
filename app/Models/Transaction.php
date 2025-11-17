<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'pot_id', 'type', 'amount',
        'status', 'reference'
    ];

    protected $casts = [
        'amount' => 'float'
    ];

    public function pot()
    {
        return $this->belongsTo(Pot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
