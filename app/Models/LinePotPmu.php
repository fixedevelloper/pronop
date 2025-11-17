<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinePotPmu extends Model
{
    use HasFactory;

    protected $table = 'line_pot_pmu';

    protected $fillable = [
        'name',
        'course_id',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
