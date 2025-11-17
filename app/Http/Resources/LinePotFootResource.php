<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinePotFootResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'team_home'=>$this->fixture->team_home_name,
            'team_away'=>$this->fixture->team_away_name,
            'score_home'=>$this->fixture->goal_home,
            'score_away'=>$this->fixture->goal_away,
            'team_home_logo'=>$this->fixture->team_home_logo,
            'team_away_logo'=>$this->fixture->team_away_logo,
            'name'=>$this->name,
            'result'=>$this->result,

        ];
    }
}
