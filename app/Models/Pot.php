<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pot extends Model
{
    protected $fillable = [
        'name', 'entry_fee', 'total_amount',
        'type', 'status', 'start_time', 'end_time',
        'distribution_rule','createdBy'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $appends = ['participants'];

    public function footLines()
    {
        return $this->hasMany(LinePotFoot::class, 'pot_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(SubscriptionPot::class);
    }

    public function getParticipantsAttribute()
    {
        return $this->subscriptions()->count();
    }
}

