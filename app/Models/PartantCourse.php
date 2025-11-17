<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartantCourse extends Model
{
    use HasFactory;

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
