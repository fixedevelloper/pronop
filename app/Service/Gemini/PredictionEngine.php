<?php

namespace App\Service\Gemini;

use App\Models\Fixture;
use App\Models\AiPrediction;
use App\Models\AiPredictionDetail;
use Illuminate\Support\Facades\DB;

class PredictionEngine
{
    protected  $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function predict(array $fixtureIds, $user = null, bool $withAnalysis = true): array
    {
        // 1. Charger fixtures
        $fixtures = Fixture::whereIn('id', $fixtureIds)->get();

        if ($fixtures->isEmpty()) {
            throw new \Exception("Aucun match trouvé");
        }

        $results = [];
        $matchesToPredict = [];
        $fixtureMap = [];

        // 2. Vérifier cache DB
        foreach ($fixtures as $fixture) {
            $existing = AiPrediction::where('fixture_id', $fixture->id)->first();

            if ($existing) {
                $results[] = $existing->load('details');
            } else {
                $matchName = "{$fixture->team_home_name} vs {$fixture->team_away_name}";
                $matchesToPredict[] = $matchName;
                $fixtureMap[$matchName] = $fixture;
            }
        }

        // 3. Gestion quota utilisateur
        if ($user && !$user->hasActiveSubscription()) {
            if ($user->free_predictions_remaining <= 0) {
                throw new \Exception("Quota gratuit épuisé");
            }

            $user->decrement('free_predictions_remaining');
        }

        // 4. Appel Gemini (batch)
        if (!empty($matchesToPredict)) {

            $aiResponse = $this->gemini->predictMatches($matchesToPredict, $withAnalysis);

            DB::beginTransaction();

            try {

                foreach ($aiResponse['predictions'] as $prediction) {

                    $matchName = $prediction['match'];

                    if (!isset($fixtureMap[$matchName])) continue;

                    $fixture = $fixtureMap[$matchName];

                    // 5. Save ai_predictions
                    $ai = AiPrediction::create([
                        'fixture_id' => $fixture->id,
                        'match_name' => $matchName,
                        'score_exact' => $prediction['score_exact'] ?? null,
                        'confidence' => $prediction['confidence'] ?? null,
                        'raw_response' => $prediction,
                        'predicted_at' => now(),
                        'source' => 'gemini',
                        'analyse_fixture' => $prediction['analysis'] ?? null,
                        'form_teams' => $prediction['form_teams'] ?? null,
                        'h2h' => $prediction['h2h'] ?? null,
                        'stat_offensive' => $prediction['stat_offensive'] ?? null,
                        'stat_defensive' => $prediction['stat_defensive'] ?? null,
                    ]);

                    // 6. Save details
                    if ($withAnalysis && isset($prediction['probabilities'])) {

                        AiPredictionDetail::create([
                            'ai_prediction_id' => $ai->id,

                            'home_win_prob' => $prediction['probabilities']['home_win'] ?? null,
                            'draw_prob' => $prediction['probabilities']['draw'] ?? null,
                            'away_win_prob' => $prediction['probabilities']['away_win'] ?? null,

                            'over_2_5' => $prediction['over_2_5'] ?? null,
                            'btts_yes' => $prediction['btts_yes'] ?? null,

                            'odds_home' => $this->calcOdds($prediction['probabilities']['home_win'] ?? null),
                            'odds_draw' => $this->calcOdds($prediction['probabilities']['draw'] ?? null),
                            'odds_away' => $this->calcOdds($prediction['probabilities']['away_win'] ?? null),

                            'best_bets' => $prediction['best_bets'] ?? []
                        ]);
                    }

                    $results[] = $ai->load('details');
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return $results;
    }

    // 🎯 Calcul odds
    private function calcOdds($prob)
    {
        if (!$prob || $prob == 0) return null;

        return round(1 / $prob, 2);
    }
}
