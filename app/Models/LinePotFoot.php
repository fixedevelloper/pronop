<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinePotFoot extends Model
{
    protected $table = 'line_pot_foot';

    protected $fillable = [
        'name', 'fixture_id', 'result', 'pot_id'
    ];

    protected $casts = [
        'result' => 'string', // '1v', '2v', 'x', 'pending'
    ];

    public function pot()
    {
        return $this->belongsTo(Pot::class, 'pot_id');
    }

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'linePotFoot_id');
    }
}
