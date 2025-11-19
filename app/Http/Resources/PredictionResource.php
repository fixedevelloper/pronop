<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'team_home'=>$this->line->fixture->team_home_name,
            'team_away'=>$this->line->fixture->team_away_name,
            'score_home'=>$this->line->fixture->score_ft_home,
            'score_away'=>$this->line->fixture->score_ft_away,
            'team_home_logo'=>$this->line->fixture->team_home_logo,
            'team_away_logo'=>$this->line->fixture->team_away_logo,
            'name'=>$this->line->name,
            'result'=>$this->line->result,
            'prediction'=>$this->prediction
        ];
    }
}
