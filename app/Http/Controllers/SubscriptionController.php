<?php
namespace App\Http\Controllers;

use App\Http\Resources\PotResource;
use App\Models\Pot;
use App\Models\Prediction;
use App\Models\SubscriptionPot;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function joinPot(Request $request, $potId)
    {
        $request->validate([
            'predictions' => 'required|array',
           // 'amount' => 'required'
        ]);

        $user = auth()->user();
        $amount = $request->amount;

        $pot = Pot::findOrFail($potId);

        if ($pot->status !== 'open') {
            return response()->json(['message' => 'Pot fermé'], 400);
        }

        if ($user->wallet_balance < $amount) {
            return response()->json(['message' => 'Solde insuffisant'], 402);
        }

        if (SubscriptionPot::where('pot_id', $potId)->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Vous êtes déjà inscrit à ce pot'], 400);
        }

        DB::transaction(function() use ($request, $user, $pot, $amount) {

            $reference = Str::uuid();

            // 1️⃣ Déduire du wallet
            $user->decrement('wallet_balance', $amount);

            // 2️⃣ Créer subscription
            SubscriptionPot::create([
                'user_id' => $user->id,
                'pot_id' => $pot->id,
                'gateway' => 'wallet',
                'amount' => $amount,
                'status' => 'success',
            ]);

            // 3️⃣ Enregistrer les prédictions
            foreach ($request->predictions as $p) {
                Prediction::create([
                    'user_id' => $user->id,
                    'line_pot_foot_id' => $p['line_id'],
                    'prediction' => $p['prediction'],
                ]);
            }

            // 4️⃣ Créer transaction
            Transaction::create([
                'user_id' => $user->id,
                'pot_id'  => $pot->id,
                'type'    => 'deposit',
                'amount'  => $amount,
                'status'  => 'success',
                'reference' => $reference
            ]);

            // 5️⃣ Ajouter montant au pot
            $pot->increment('total_amount', $amount);
        });

        return response()->json(['message' => 'Participation enregistrée + paiement effectué.']);
    }
    public function potByUser(Request $request)
    {
        $perPage = (int) $request->query('per_page', 5);


        $query = SubscriptionPot::with(['user', 'pot'])
            ->where('status', 'open')
            ->orderByDesc('created_at');


        // 🔹 Pagination
        $pots = $query->paginate($perPage);

        return response()->json([
            'data' =>  PotResource::collection($pots),
            'meta' => [
                'total' => $pots->total(),
                'per_page' => $pots->perPage(),
                'current_page' => $pots->currentPage(),
                'last_page' => $pots->lastPage(),
            ],
        ]);
    }
    public function potsByAuthenticatedUser(Request $request)
    {
        $perPage = (int) $request->query('per_page', 5);
        $user = auth()->user(); // utilisateur connecté
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        $perPage = (int) $request->query('per_page', $perPage);

        // Récupère les pots où l'utilisateur a une subscription
        $query = Pot::whereHas('subscriptions', function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->where('status', 'success');
        })
            ->with([
                'creator:id,name,email',
                'subscriptions.user:id,name'
            ])
            ->orderByDesc('created_at');


        $pots = $query->paginate($perPage);

        return response()->json([
            'data' =>  PotResource::collection($pots),
            'meta' => [
                'total' => $pots->total(),
                'per_page' => $pots->perPage(),
                'current_page' => $pots->currentPage(),
                'last_page' => $pots->lastPage(),
            ],
        ]);
    }

}
