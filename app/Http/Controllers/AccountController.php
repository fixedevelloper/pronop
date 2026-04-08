<?php


namespace App\Http\Controllers;

use App\Http\Helpers\Helpers;
use App\Http\Resources\PotResource;
use App\Models\AiPrediction;
use App\Models\IaSubscription;
use App\Models\IaTransaction;
use App\Models\IaUserMatchUnlock;
use App\Models\Pot;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $stats = Cache::remember("user-stats-{$user->id}", 300, function () use ($user) {

            // 1. USER
            $userData = User::select('wallet_balance', 'tokens')
                ->findOrFail($user->id);

            // 2. POTS
            $totalPots = Pot::where('createdBy', $user->id)->count();

            // 3. POINTS (🔥 OPTIMISÉ)
            $totalPoints = Prediction::where('user_id', $user->id)
                ->with('line')
                ->get()
                ->filter(fn($p) => $p->isCorrect())
                ->count();

            // 4. PRONOS
            $totalPronos = IaUserMatchUnlock::where('user_id', $user->id)->count();

            $wonPronos = IaUserMatchUnlock::where('user_id', $user->id)
                ->where('type', 'WON')
                ->count();

            $accuracy = $totalPronos > 0
                ? round(($wonPronos / $totalPronos) * 100, 1)
                : 0;

            // 5. RANKING (🔥 SIMPLE + FIABLE)
            $rankPosition = User::where('wallet_balance', '>', $userData->wallet_balance)->count() + 1;

            return [
                'balance' => (float) $userData->wallet_balance,
                'total_pots' => (int) $totalPots,
                'total_points' => (int) $totalPoints,
                'total_pronostics' => (int) $totalPronos,
                'pronostics_won' => (int) $wonPronos,
                'accuracy_rate' => (float) $accuracy,
                'total_gains' => (float) $userData->tokens,
                'rank_position' => (int) $rankPosition,
                'vip_level' => 0
            ];
        });

        return response()->json($stats);
    }

    public function pots(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $perPage = (int) $request->query('per_page', 10);
        $page = (int) $request->query('page', 1);

        $query = Pot::query()->where('createdBy',$user->id);

        // filtres
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($q = $request->query('q')) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('entry_fee', 'like', "%{$q}%");
            });
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $result = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($result);
    }
    public function pronostics(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $perPage = (int) $request->query('per_page', 10);
        $page = (int) $request->query('page', 1);

        $query = Pot::with(['subscriptions.user', 'footLines.fixture'])
            ->whereHas('subscriptions', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        // filtres
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($q = $request->query('q')) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('entry_fee', 'like', "%{$q}%");
            });
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $result = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' =>  PotResource::collection($result),
            'meta' => [
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
            ],
        ]);
    }
    public function transactions(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $perPage = (int) $request->query('per_page', 10);
        $page = (int) $request->query('page', 1);

        $query = Transaction::query()->where('user_id', $user->id);

        // filtres
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($q = $request->query('q')) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('reference', 'like', "%{$q}%")
                    ->orWhere('amount', 'like', "%{$q}%")
                    ->orWhere('pot_id', 'like', "%{$q}%");
            });
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $query->orderBy('created_at', 'desc');

        $result = $query->paginate($perPage);

        return response()->json($result);
    }
    public function recharge(Request $request)
    {
        $user = Auth::user();

        // 🔹 Validation
        $request->validate([
            'amount' => 'required|integer|min:100',
            'country' => 'required|string',
            'operator' => 'required|string',
        ]);

        DB::beginTransaction();

        try {

            // 🔥 Génération référence unique
            $reference = 'TRX-' . strtoupper(uniqid());

            // 🔹 Création transaction
            $transaction = IaTransaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => 'deposit',
                'reference' => $reference,
                'status' => 'pending' // 🔥 important
            ]);

            // ⚠️ NE PAS créditer ici (paiement pas encore validé)
             $user->wallet_balance += $request->amount;
             $user->save();

            DB::commit();

            return Helpers::success([
                'reference' => $reference,
                'payment_url' => route('paiement.show', ['reference' => $reference])
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return Helpers::error('Erreur lors de la recharge', [
                'message' => $e->getMessage()
            ]);
        }
    }
    public function me(Request $request)
    {
        $user = Auth::user();
        return Helpers::success([
            'wallet_balance'=>$user->wallet_balance,
            'name'=>$user->name,
            'email'=>$user->email,
            'phone'=>$user->phone,
        ]);
    }
}
