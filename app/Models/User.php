<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // ✅ ajoute HasApiTokens ici

    protected $fillable = ['name', 'email', 'password', 'role','phone','address'];

    protected $hidden = ['password', 'remember_token'];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

// Agents reliés à des propriétaires
    public function owners()
    {
        return $this->belongsToMany(User::class, 'agent_owner', 'agent_id', 'owner_id');
    }

    public function agents()
    {
        return $this->belongsToMany(User::class, 'agent_owner', 'owner_id', 'agent_id');
    }



}

