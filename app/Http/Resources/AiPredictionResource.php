<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AiPredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'fixture_id'    => $this->fixture_id,
            'fixture_name'  => $this->match_name,
            'source'        => $this->source,
            'score_exact'   => $this->score_exact,
            'confidence'    => $this->confidence,
            'predicted_at'  => $this->predicted_at,
            'analysis'  => $this->analyse_fixture,
            'form_teams'  => $this->form_teams,
            'h2h'  => $this->h2h,
            'stat_offensive'  => $this->stat_offensive,
            'stat_defensive'  => $this->stat_defensive,
            'fixture' => $this->whenLoaded('fixture', function () {
                return new FixtureResource($this->fixture);
            }),
            // 🔗 Détails du match
            'details' => $this->whenLoaded('details', function () {
                return [
                    'home_win_prob'  => $this->details->home_win_prob,
                    'draw_prob'      => $this->details->draw_prob,
                    'away_win_prob'  => $this->details->away_win_prob,
                    'over_1_5'       => $this->details->over_1_5,
                    'over_2_5'       => $this->details->over_2_5,
                    'over_3_5'       => $this->details->over_3_5,
                    'under_2_5'      => $this->details->under_2_5,
                    'btts_yes'       => $this->details->btts_yes,
                    'btts_no'        => $this->details->btts_no,
                    'odds_home'      => $this->details->odds_home,
                    'odds_draw'      => $this->details->odds_draw,
                    'odds_away'      => $this->details->odds_away,
                    'odds_over_2_5'  => $this->details->odds_over_2_5,
                    'odds_under_2_5' => $this->details->odds_under_2_5,
                    'best_bets'      => $this->details->best_bets,
                ];
            }),

            // 🔗 Statistiques
            'stats' => $this->whenLoaded('stats', function () {
                return [
                    'real_score'        => $this->stats->real_score,
                    'is_score_correct'  => $this->stats->is_score_correct,
                    'is_1x2_correct'    => $this->stats->is_1x2_correct,
                    'is_over25_correct' => $this->stats->is_over25_correct,
                    'is_btts_correct'   => $this->stats->is_btts_correct,
                    'accuracy_score'    => $this->stats->accuracy_score,
                ];
            }),
        ];
    }
}
