<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = [
        'line_pot_foot_id', 'user_id', 'prediction'
    ];

    protected $casts = [
        'prediction' => 'string', // 1v, x, 2v
    ];

    public function line()
    {
        return $this->belongsTo(LinePotFoot::class, 'line_pot_foot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isCorrect()
    {
        return $this->line && $this->line->result === $this->prediction;
    }
}
