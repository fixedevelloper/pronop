<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reunion',
        'date_course',
    ];

    public function partants()
    {
        return $this->hasMany(PartantCourse::class);
    }

    public function lines()
    {
        return $this->hasMany(LinePotPmu::class);
    }
}

