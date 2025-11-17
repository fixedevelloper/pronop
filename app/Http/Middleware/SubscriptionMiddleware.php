<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionMiddleware
{
    /**
     * Vérifie si l'utilisateur possède un abonnement valide et autorisé.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $plan  (optionnel : 'Pro', 'Agency', etc.)
     */
    public function handle(Request $request, Closure $next, $plan = null)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }

        // 🔹 Les admins passent toujours
        if ($user->role === 'admin') {
            return $next($request);
        }

        // 🔹 Récupère la souscription active la plus récente
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now())
            ->latest('end_date')
            ->first();

        if (!$activeSubscription) {
            return response()->json([
                'error' => 'Aucun abonnement actif trouvé. Veuillez vous abonner.'
            ], 403);
        }

        // 🔹 Vérifie le plan si nécessaire
        if ($plan && strtolower($activeSubscription->plan_name) !== strtolower($plan)) {
            return response()->json([
                'error' => "Votre plan actuel ({$activeSubscription->plan_name}) ne permet pas d’accéder à cette fonctionnalité."
            ], 403);
        }

        return $next($request);
    }
}

