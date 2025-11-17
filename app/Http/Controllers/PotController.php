<?php

namespace App\Http\Controllers;

use App\Http\Resources\LinePotFootResource;
use App\Http\Resources\PotResource;
use App\Http\Resources\PredictionResource;
use App\Models\LinePotFoot;
use App\Models\Pot;
use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Service\PotRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PotController extends Controller
{
    public function createPotFoot(Request $request)
    {
        $user=Auth::user();
        // 🔹 Validation
        $request->validate([
            'name'       => 'required|string|max:255',
            'entry_fee'  => 'required|numeric|min:100',
            'fixtures'   => 'required|array|min:1',

        ]);

        // 🔹 Création du pot
        $pot = Pot::create([
            'name'              => $request->name,
            'entry_fee'         => $request->entry_fee,
            'total_amount'      => 0,
            'type'              => 'foot',
            'status'            => 'open',
            'createdBy'         => $user->id,
            'distribution_rule' => 'winner_takes_all',
        ]);

        if (!empty($request->fixtures)) {
            $lines = [];
            foreach ($request->fixtures as $fixture) {
                $lines[] = [
                    'fixture_id' => $fixture,
                    'pot_id'     => $pot->id,
                    'name'       => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                LinePotFoot::create([
                    'fixture_id' => $fixture,
                    'pot_id'     => $pot->id,
                    'name'       => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
           // LinePotFoot::insert($lines);
        }


        // 🔹 Retourner la réponse
        return response()->json([
            'success' => true,
            'message' => 'Pot créé avec succès',
            'pot'     => $pot->load('footLines'), // charger les fixtures si relation définie
        ]);
    }
    public function index(Request $request)
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

        return response()->json([
            'pot' => $pot,
        ]);
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

        return response()->json([
            'pot'          => new PotResource($pot),
            'leaderboards' => $leaderboard,
            'lines'        => LinePotFootResource::collection($pot->footLines)
        ]);
    }





    public function ranking($potId, PotRankingService $rankingService)
    {
        $pot = Pot::findOrFail($potId);
        $ranking = $rankingService->getRanking($pot);
        return response()->json($ranking);
    }

}
