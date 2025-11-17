<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Tenant;

class CheckSubscriptionLimitsMiddleware
{
    /**
     * Vérifie les limites d'abonnement pour certaines actions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $type (ex: 'property' ou 'tenant')
     */
    public function handle(Request $request, Closure $next, $type = null)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }

        $subscription = $user->subscription;

        if (!$subscription || $subscription->status !== 'active') {
            return response()->json(['error' => 'Aucun abonnement actif.'], 403);
        }

        // Vérifie les limites en fonction du type de ressource
        switch ($type) {
            case 'property':
                $limit = $subscription->max_properties;
                $count = Property::where('user_id', $user->id)->count();
                $label = 'propriétés';
                break;

            case 'tenant':
                $limit = $subscription->max_tenants;
                $count = Tenant::where('user_id', $user->id)->count();
                $label = 'locataires';
                break;

            default:
                return $next($request);
        }

        // 🔹 Si une limite est définie et atteinte
        if ($limit !== null && $count >= $limit) {
            return response()->json([
                'error' => "Limite atteinte : votre plan ne permet pas d'ajouter plus de {$limit} {$label}.",
                'current' => $count,
                'max_allowed' => $limit
            ], 403);
        }

        return $next($request);
    }
}

