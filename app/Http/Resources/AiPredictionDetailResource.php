<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AiPredictionDetailResource extends JsonResource
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
            'id'                 => $this->id,
            'ai_prediction_id'   => $this->ai_prediction_id,

            // Probabilités
            'home_win_prob'      => $this->home_win_prob,
            'draw_prob'          => $this->draw_prob,
            'away_win_prob'      => $this->away_win_prob,

            // Over/Under
            'over_1_5'           => $this->over_1_5,
            'over_2_5'           => $this->over_2_5,
            'over_3_5'           => $this->over_3_5,
            'under_2_5'          => $this->under_2_5,

            // BTTS
            'btts_yes'           => $this->btts_yes,
            'btts_no'            => $this->btts_no,

            // Odds
            'odds_home'          => $this->odds_home,
            'odds_draw'          => $this->odds_draw,
            'odds_away'          => $this->odds_away,
            'odds_over_2_5'      => $this->odds_over_2_5,
            'odds_under_2_5'     => $this->odds_under_2_5,

            // Best bets
            'best_bets'          => $this->best_bets ? json_decode($this->best_bets, true) : null,

            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
