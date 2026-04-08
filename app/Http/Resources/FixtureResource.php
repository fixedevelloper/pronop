<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixtureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fixture_id' => $this->fixture_id,
            'date' => $this->date,
            'timestamp' => $this->timestamp,
            'timezone' => $this->timezone,
            'referee' => $this->referee,
            'is_unlocked'=>$this->is_unlocked,

            // Équipes
            'home_team' => [
                'name' => $this->team_home_name,
                'logo' => $this->team_home_logo,
                'winner' => (bool) $this->team_home_winner,
                'goals' => [
                    'total' => (int) $this->goal_away,
                    'ht'    => (int) $this->score_ht_away,
                    'ft'    => (int) $this->score_ft_away,
                    'et'    => (int) $this->score_ext_away,
                    'pt'    => (int) $this->score_pt_away,
                ],
            ],
            'away_team' => [
                'name' => $this->team_away_name,
                'logo' => $this->team_away_logo,
                'winner' => (bool) $this->team_away_winner,
                'goals' => [
                    'total' => (int) $this->goal_away,
                    'ht'    => (int) $this->score_ht_away,
                    'ft'    => (int) $this->score_ft_away,
                    'et'    => (int) $this->score_ext_away,
                    'pt'    => (int) $this->score_pt_away,
                ],
            ],

            // League
            'league' => $this->whenLoaded('league', function () {
                return [
                    'id' => $this->league->id,
                    'name' => $this->league->name,
                    'logo' => $this->league->logo,
                    'country_code' => $this->league->country_code,
                ];
            }),

            // Status
            'status' => [
                'elapsed' => $this->st_elapsed,
                'long' => $this->st_long,
                'short' => $this->st_short,
            ],

            // AI Prediction
            'ai_prediction' => $this->whenLoaded('prediction', function () {
                return [
                    'home_score' => $this->aiPrediction->home_score,
                    'away_score' => $this->aiPrediction->away_score,
                    'confidence' => $this->aiPrediction->confidence,
                    'analysis' => $this->aiPrediction->analysis,
                ];
            }),

            // Lineups (si chargé)
            'lineups' => $this->whenLoaded('linePotFoot', function () {
                return LinePotFootResource::collection($this->linePotFoot);
            }),

            // Métadonnées
            'day_timestamp' => $this->timestamp,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
