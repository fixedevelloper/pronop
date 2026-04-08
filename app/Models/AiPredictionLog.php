<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiPredictionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt',
        'response',
        'tokens_input',
        'tokens_output',
        'cost'
    ];

    protected $casts = [
        'cost' => 'float',
    ];
}
