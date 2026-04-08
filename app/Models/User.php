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

    protected $fillable = ['name', 'email', 'password', 'role','phone','address','wallet_balance'];

    protected $hidden = ['password', 'remember_token'];
    // 🔓 Matches débloqués
    public function unlockedMatches()
    {
        return $this->hasMany(IaUserMatchUnlock::class);
    }

    // 💳 Transactions
    public function transactions()
    {
        return $this->hasMany(IaTransaction::class);
    }

    // 👑 Abonnements
    public function subscriptions()
    {
        return $this->hasMany(IaSubscription::class);
    }

    // ✅ abonnement actif
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('active', true)
            ->where('ends_at', '>', now())
            ->exists();
    }
    public function hasAccessToFixture($fixtureId): bool
    {
        // 👑 abonnement
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // 🔓 déjà débloqué
        return $this->unlockedMatches()
            ->where('fixture_id', $fixtureId)
            ->exists();
    }
}

