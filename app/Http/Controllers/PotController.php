<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helpers;
use App\Http\Resources\LinePotFootResource;
use App\Http\Resources\PotResource;
use App\Http\Resources\PredictionResource;
use App\Models\LinePotFoot;
use App\Models\Pot;
use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Service\PotRankingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PotController extends Controller
{
    public function createPotFoot(Request $request)
    {
        $user = Auth::user();

        // 🔹 Validation
        $request->validate([
            'name'      => 'required|string|max:255',
            'entry_fee' => 'required|numeric|min:100',
            'fixtures'  => 'required|array|min:1',
        ]);
        DB::beginTransaction();
        // 🔹 Création du pot
        $pot = Pot::create([
            'name'              => $request->name,
            'entry_fee'         => $request->entry_fee,
            'total_amount'      => 0,
            'type'              => 'foot',
            'status'            => 'open',
            'createdBy'         => $user->id,
            'start_time'=> now(),
            'distribution_rule' => 'winner_takes_all',
        ]);

        // 🔹 Créer toutes les lignes en une seule requête
        if (!empty($request->fixtures)) {
            $lines = collect($request->fixtures)->map(fn($fixtureId) => [
                'fixture_id' => $fixtureId,
                'pot_id'     => $pot->id,
                'name'       => '',
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            LinePotFoot::insert($lines);
        }

        $endTime = $this->getLastPlayedMatchTimestamp($pot);
        if ($endTime) {
            $potEndTime = Carbon::parse($endTime)->subMinutes(30);
            $pot->update(['end_time' => $potEndTime]);

            logger('Pot end_time updated', [
                'pot_id' => $pot->id,
                'end_time' => $potEndTime->toDateTimeString()
            ]);
        }
        DB::commit();
        // 🔹 Charger les relations et retourner la réponse
        $pot->load('footLines');

        return response()->json([
            'success' => true,
            'message' => 'Pot créé avec succès',
            'pot'     => $pot,
        ]);
    }
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 5);

        // 🔹 Récupérer le paramètre created_at ou utiliser aujourd'hui
        $createdAt = $request->query('created_at', Carbon::today()->toDateString()); // format 'YYYY-MM-DD'

        $query = Pot::with(['subscriptions.user', 'footLines.fixture'])
            ->where('status', 'open')
            ->orderByDesc('start_time');

        // 🔹 Filtrer par date
        if ($createdAt) {
            $query->whereDate('created_at', $createdAt);
        }

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

    public function pronostics(Request $request)
    {
        // 🔹 Pagination dynamique (par défaut 20)
        $perPage = $request->query('per_page', 5);

        // 🔹 Récupération des pots ouverts
        $pots = Pot::where('status', 'open')
            ->orderBy('start_time', 'desc')
            ->paginate($perPage);

        // 🔹 Transformation des données si besoin (facultatif)
        $pots->getCollection()->transform(function ($pot) {
            return [
                'id' => $pot->id,
                'name' => $pot->name,
                'entry_fee' => $pot->entry_fee,
                'total_amount' => $pot->total_amount,
                'type' => $pot->type,
                'status' => $pot->status,
                'start_time' => $pot->start_time,
                'end_time' => $pot->end_time,
                'distribution_rule' => $pot->distribution_rule,
            ];
        });

        return response()->json($pots);
    }

    public function show(Pot $pot)
    {
        // Charger les relations nécessaires
        $pot->load([
            'footLines' => function ($query) {
                $query->with(['fixture.league']);
            },
            'subscriptions' => function ($query) {
                $query->with('user');
            }
        ]);

      /*  return response()->json([
            'pot' => $pot,
        ]);*/
        return Helpers::success(new PotResource($pot));
    }

    public function leaderboard(Pot $pot)
    {
        $user=Auth::user();
        // Récupérer les lignes du pot
        $lineIds = $pot->footLines()->pluck('id');

        // Charger toutes les prédictions liées
        $predictions = Prediction::with(['user', 'line'])
            ->whereIn('line_pot_foot_id', $lineIds)
            ->get();

        // Construire le classement
        $leaderboard = $predictions
            ->groupBy('user_id')
            ->map(function ($userPredictions) {

                $user = $userPredictions->first()->user;

                return [
                    'user_id' => $user->id,
                    'name'    => $user->name,
                    'points'  => $userPredictions->filter->isCorrect()->count(),
                ];
            })
            ->sortByDesc('points')
            ->values()
            ->all();

        $userPredictions=Prediction::with(['user', 'line'])
            ->whereIn('line_pot_foot_id', $lineIds)
            ->where('user_id',$user->id)
            ->get();
        return response()->json([
            'leaderboards' => $leaderboard,
            'lines' => PredictionResource::collection($userPredictions)
        ]);
    }
    public function details(Pot $pot)
    {
        logger($this->getLastPlayedMatchTimestamp($pot));
        $pot->load([
            'footLines' => function ($query) {
                $query->with(['fixture.league']);
            },
            'subscriptions' => function ($query) {
                $query->with('user');
            }
        ]);
        $lineIds = $pot->footLines()->pluck('id');

        $predictions = Prediction::with(['user', 'line'])
            ->whereIn('line_pot_foot_id', $lineIds)
            ->get();


        $leaderboard = $predictions
            ->groupBy('user_id')
            ->map(function ($items) {
                $user = $items->first()->user;

                return [
                    'user_id' => $user->id,
                    'name'    => $user->name,
                    'points'  => $items->filter(fn ($p) => $p->isCorrect())->count(),
                ];
            })
            ->sortByDesc('points')
            ->values()
            ->all();

        return Helpers::success(
            [
                'pot'          => new PotResource($pot),
                'leaderboards' => $leaderboard,
                'predictions'=>PredictionResource::collection($predictions)
            ]
        );

    }

    public function ranking($potId, PotRankingService $rankingService)
    {
        $pot = Pot::findOrFail($potId);
        $ranking = $rankingService->getRanking($pot);
        return response()->json($ranking);
    }
    private function getLastPlayedMatchTimestamp(Pot $pot)
    {
        $timestamp= LinePotFoot::where('pot_id', $pot->id)
            ->join('fixtures', 'line_pot_foot.fixture_id', '=', 'fixtures.id')
            ->orderBy('fixtures.timestamp', 'desc')
            ->value('fixtures.timestamp');
        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }
}
