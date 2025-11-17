<?php


namespace App\Http\Controllers;

use App\Models\Pot;
use Illuminate\Http\Request;
use App\Models\Transaction;

class AccountController extends Controller
{
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

        $query = Pot::query()
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

        return response()->json($result);
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
}
