<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixtureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'team_home'=>$this->team_home_name,
            'team_away'=>$this->team_away_name,
            'score_home'=>$this->goal_home,
            'score_away'=>$this->goal_away,
            'team_home_logo'=>$this->team_home_logo,
            'team_away_logo'=>$this->team_away_logo,
        ];
    }
}
