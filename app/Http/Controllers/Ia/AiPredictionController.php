<?php


namespace App\Http\Controllers\Ia;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModelCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AiPrediction;
use App\Http\Resources\AiPredictionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class AiPredictionController extends Controller
{
    /**
     * Liste toutes les prédictions avec pagination
     * @param Request $request
     * @return ModelCollection
     */
    public function index2(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $date=$request->predicted_at;
        //$date='2026-03-30';
        try {
            $predictedAt = $request->get('predicted_at')
                ? Carbon::parse($date)->toDateString()
                : now()->toDateString();
        } catch (\Exception $e) {
            $predictedAt = now()->toDateString();
        }

        $predictions = AiPrediction::with(['details', 'stats', 'fixture'])
            ->join('ai_system_unlock_fixtures as unlocks', function ($join) {
                $join->on('ai_predictions.fixture_id', '=', 'unlocks.fixture_id')
                    ->where('unlocks.is_free', true);
            })
            ->whereDate('ai_predictions.predicted_at', $predictedAt)
            ->orderByDesc('ai_predictions.predicted_at')
            ->select('ai_predictions.*')
            ->paginate($perPage);

        return new ModelCollection($predictions);
    }
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $predictedAt = $request->get('predicted_at', date('Y-m-d')); // par défaut aujourd'hui

        // 🔹 Récupérer les prédictions IA pour les fixtures débloquées et filtrées par date
        $predictionsQuery = AiPrediction::with(['details', 'stats', 'fixture'])
            ->join('ai_system_unlock_fixtures as unlocks', function($join) {
                $join->on('ai_predictions.fixture_id', '=', 'unlocks.fixture_id')
                    ->where('unlocks.is_free', true);
            })
            ->orderByDesc('ai_predictions.predicted_at')
            ->select('ai_predictions.*');

        // 🔹 Filtrer si paramètre predicted_at fourni
        if ($predictedAt) {
            $predictionsQuery->whereDate('ai_predictions.predicted_at', $predictedAt);
        }

        $predictions = $predictionsQuery->paginate($perPage);

        // 🔹 Transformer les résultats avec la ressource
        $collection = AiPredictionResource::collection($predictions->items());

        // 🔹 Retourner data + meta propre
        return response()->json([
            'data' => $collection,
            'meta' => [
                'total' => $predictions->total(),
                'per_page' => $predictions->perPage(),
                'current_page' => $predictions->currentPage(),
                'last_page' => $predictions->lastPage(),
            ],
        ]);
    }
    /**
     * Détail d'une prédiction
     * @param $id
     * @return AiPredictionResource
     */
    public function show($id)
    {
        $prediction = AiPrediction::with(['details', 'stats','fixture'])->findOrFail($id);

        return new AiPredictionResource($prediction);
    }

    /**
     * Historique des prédictions pour l'utilisateur connecté
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function history(Request $request)
    {
        $user = Auth::user();
logger($user);
        $perPage = $request->get('per_page', 20);

        $predictions = AiPrediction::query()
            ->whereHas('fixture.unlocks', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['details', 'stats', 'fixture'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return  AiPredictionResource::collection($predictions);
    }
}
