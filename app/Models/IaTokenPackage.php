<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IaTokenPackage extends Model
{
    protected $fillable = [
        'name',
        'tokens',
        'price',
        'is_active'
    ];

    protected $casts = [
        'tokens' => 'integer',
        'price' => 'integer',
        'is_active' => 'boolean'
    ];

    // 🔥 uniquement actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
