<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id',
        'name',
        'type',
        'logo',
    ];

    public function fixtures()
    {
        return $this->hasMany(Fixture::class);
    }
}
