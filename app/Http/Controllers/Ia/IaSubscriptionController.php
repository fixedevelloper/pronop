<?php


namespace App\Http\Controllers\Ia;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helpers;
use App\Models\IaSubscription;
use App\Models\IaTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IaSubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 🔹 Récupérer les abonnements actifs dont la date de fin est > aujourd'hui
        $subscriptions = IaSubscription::where([
            'user_id' => $user->id,
            'active' => true
        ])
            ->where('ends_at', '>', now())
            ->orderBy('starts_at', 'desc')
            ->get();

        // 🔹 Retourner les abonnements dans une réponse JSON standard
        return Helpers::success($subscriptions);
    }

    // 🔹 Créer un nouvel abonnement
    public function store(Request $request)
    {
        $user = Auth::user();

        // 🔹 Validation
        $request->validate([
            'plan' => ['required', 'string', Rule::in(['Basic','Premium','Pro'])],
            'price' => 'required|integer|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'active' => 'required|boolean'
        ]);
        DB::beginTransaction();
        // 🔹 Vérifier si l'utilisateur a déjà un abonnement actif pour ce plan
        $activePlan = IaSubscription::where('user_id', $user->id)
            ->where('plan', $request->plan)
            ->where('active', true)
            ->where('ends_at', '>', now())
            ->latest('ends_at')
            ->first();

        $startsAt = $request->starts_at;
        $endsAt = $request->ends_at;

        if ($activePlan) {
            // Commence après la fin du plan actif
            $startsAt = $activePlan->ends_at->addSecond(); // ajouter 1 seconde pour ne pas chevaucher
            $endsAt = Carbon::parse($startsAt)->addSeconds(
                Carbon::parse($request->ends_at)->diffInSeconds(Carbon::parse($request->starts_at))
            );
        }

        $subscription = IaSubscription::create([
            'user_id' => $user->id,
            'plan' => $request->plan,
            'price' => $request->price,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'active' => $request->active,
        ]);
        // 🔥 Génération référence unique
        $reference = 'TRX-' . strtoupper(uniqid());

        // 🔹 Création transaction
        $transaction = IaTransaction::create([
            'user_id' => $user->id,
            'amount' => $request->price,
            'type' => 'subscription',
            'reference' => $reference,
            'status' => 'pending' // 🔥 important
        ]);

        DB::commit();

        return Helpers::success([
            'payment_url'=>route('paiement.show',['reference'=>$reference])
        ]);
    }

    // 🔹 Récupérer un abonnement par ID
    public function show($id)
    {
        $user = Auth::user();
        $subscription = IaSubscription::where('user_id', $user->id)->findOrFail($id);

        return response()->json([
            'success' => true,
            'subscription' => $subscription
        ]);
    }

    // 🔹 Activer ou désactiver un abonnement
    public function toggleActive($id)
    {
        $user = Auth::user();
        $subscription = IaSubscription::where('user_id', $user->id)->findOrFail($id);

        $subscription->active = !$subscription->active;
        $subscription->save();

        return response()->json([
            'success' => true,
            'active' => $subscription->active
        ]);
    }

    // 🔹 Supprimer un abonnement
    public function destroy($id)
    {
        $user = Auth::user();
        $subscription = IaSubscription::where('user_id', $user->id)->findOrFail($id);
        $subscription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Abonnement supprimé'
        ]);
    }

    // 🔹 Récupérer l'abonnement actif de l'utilisateur
    public function active()
    {
        $user = Auth::user();
        $subscription = IaSubscription::where('user_id', $user->id)
            ->active()
            ->first();

        return response()->json([
            'success' => true,
            'subscription' => $subscription
        ]);
    }
}
