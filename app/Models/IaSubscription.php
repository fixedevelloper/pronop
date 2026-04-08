<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IaSubscription extends Model
{
    // 🔹 Champs remplissables (mass assignable)
    protected $fillable = [
        'user_id',
        'plan',
        'price',
        'starts_at',
        'ends_at',
        'active'
    ];

    // 🔹 Cast automatique des types
    protected $casts = [
        'price' => 'integer',          // prix stocké comme entier
        'starts_at' => 'datetime',     // début de l'abonnement
        'ends_at' => 'datetime',       // fin de l'abonnement
        'active' => 'boolean'          // actif ou non
    ];

    // 🔹 Relation avec l'utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Scope pour récupérer uniquement les abonnements actifs
    public function scopeActive($query)
    {
        return $query
            ->where('active', true)
            ->where('ends_at', '>', now());
    }

    // 🔹 Méthode pour vérifier rapidement si l'abonnement est actif
    public function isActive(): bool
    {
        return $this->active && $this->ends_at > now();
    }
}
