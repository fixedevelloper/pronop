<?php


namespace App\Http\Controllers\Ia;


use App\Http\Controllers\Controller;
use App\Jobs\PredictAdminMatchesJob;
use App\Jobs\PredictMatchesJob;
use App\Models\AiSystemUnlockFixture;
use App\Models\IaUserMatchUnlock;
use App\Service\Gemini\PredictionEngine;
use Illuminate\Http\Request;
use App\Models\Fixture;
use App\Models\AiPrediction;
use App\Models\AiPredictionDetail;
use Illuminate\Support\Facades\DB;

class PredictionController extends Controller
{
    public function predict(Request $request, PredictionEngine $engine)
    {
        $data = $engine->predict(
            $request->fixture_ids,
            auth()->user(),
            true // avec analyse
        );

        return response()->json($data);
    }
    public function unlock(Request $request)
    {
        try {
        $request->validate([
            'matches' => 'required|array|min:1',
            'matches.*' => 'exists:fixtures,id',
            'use_tokens' => 'boolean'
        ]);

        $user = auth()->user();
        $matches = $request->matches;
        $useTokens = $request->boolean('use_tokens', false);
        $pricePerMatch = 50; // XAF ou 1 token par match
        $totalPrice = count($matches) * $pricePerMatch;


            // 🔥 TRANSACTION DB ATOMIQUE
            return DB::transaction(function () use ($user, $matches, $useTokens, $totalPrice, $pricePerMatch) {

                // 🔒 LOCK utilisateur pour éviter race conditions
                $user->lockForUpdate()->first();

                // Vérification solde/tokens (AVANT débit)
                if ($useTokens && $user->tokens < count($matches)) {
                    throw new \Exception('Tokens insuffisants', 400);
                }
                if (!$useTokens && $user->balance < $totalPrice) {
                    throw new \Exception('Solde insuffisant', 400);
                }

                // 🔥 DÉBIT ATOMIQUE
                if ($useTokens) {
                    $user->decrement('tokens', count($matches));
                    $method = 'tokens';
                } else {
                    $user->decrement('balance', $totalPrice);
                    $method = 'balance';
                }

                // 🔥 Enregistrement unlocks + audit (dans transaction)
                $unlocksData = [];
                foreach ($matches as $matchId) {
                    $unlocksData[] = [
                        'user_id' => $user->id,
                        'fixture_id' => $matchId,
                        'price' => $pricePerMatch,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    DB::table('ia_unlock_audit_logs')->insert([
                        'user_id' => $user->id,
                        'fixture_id' => $matchId,
                        'method' => $method,
                        'amount' => $pricePerMatch,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Bulk insert unlocks (plus performant)
                IaUserMatchUnlock::insert($unlocksData);

                // 🔓 Refresh user après transaction
                $user->refresh();

                // 🔥 Job NON-BLOQUANT (hors transaction)
                PredictMatchesJob::dispatch($matches, $user);

                return response()->json([
                    'success' => true,
                    'message' => 'Matchs débloqués avec succès',
                    'balance' => $user->balance,
                    'tokens' => $user->tokens,
                    'matches_unlocked' => count($matches)
                ], 200);

            }, 3); // 3 tentatives en cas de deadlock

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            logger()->error('Unlock failed', [
                'user_id' => $user->id ?? 'unknown',
                'matches' => $matches ?? [],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $status = $e->getCode() ?: 400;
            $message = match(true) {
            str_contains($e->getMessage(), 'Tokens insuffisants') => 'Tokens insuffisants',
            str_contains($e->getMessage(), 'Solde insuffisant') => 'Solde insuffisant',
            default => 'Erreur lors du déblocage des matchs'
        };

        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
    }
    public function unlockAdmin(Request $request)
    {
        $request->validate([
            'matches' => 'required|array',
            'matches.*' => 'exists:fixtures,id',
            'use_tokens' => 'boolean'
        ]);

        $matches = $request->matches;


        // 🔥 Sauvegarde des unlocks + audit
        foreach ($matches as $matchId) {
            AiSystemUnlockFixture::updateOrCreate(
                ['fixture_id' => $matchId,'date_play'=>date('y-m-d')],
                ['is_free' => true]
            );

        }
        // 🔥 Dispatch job IA (non bloquant)
        PredictAdminMatchesJob::dispatch($matches);

        return response()->json([
            'success' => true,
            'message' => 'Matchs débloqués avec succès',
        ]);
    }
    public function history(Request $request)
    {
        $user = auth()->user();

        // 🔥 Récupérer tous les unlocks de l'utilisateur
        $unlocks = IaUserMatchUnlock::with(['fixture', 'fixture.league'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($unlock) {
                return [
                    'fixture_id'   => $unlock->fixture_id,
                    'home_team'    => $unlock->fixture->home_team->name ?? null,
                    'home_logo'    => $unlock->fixture->home_team->logo ?? null,
                    'away_team'    => $unlock->fixture->away_team->name ?? null,
                    'away_logo'    => $unlock->fixture->away_team->logo ?? null,
                    'league'       => $unlock->fixture->league->name ?? 'Autres',
                    'league_logo'  => $unlock->fixture->league->logo ?? null,
                    'type'         => $unlock->type,      // analysis ou prediction
                    'price'        => $unlock->price,
                    'unlocked_at'  => $unlock->created_at->format('d/m/Y H:i'),
                    'method'       => DB::table('ia_unlock_audit_logs')
                            ->where('user_id', $unlock->user_id)
                            ->where('fixture_id', $unlock->fixture_id)
                            ->where('type', $unlock->type)
                            ->value('method') ?? 'balance'
                ];
            });

        return response()->json([
            'success' => true,
            'history' => $unlocks
        ]);
    }
}
