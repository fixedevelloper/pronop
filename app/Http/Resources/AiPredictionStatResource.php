<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AiPredictionStatResource extends JsonResource
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
            'id'                   => $this->id,
            'ai_prediction_id'     => $this->ai_prediction_id,

            'real_score'           => $this->real_score,
            'is_score_correct'     => $this->is_score_correct,
            'is_1x2_correct'       => $this->is_1x2_correct,
            'is_over25_correct'    => $this->is_over25_correct,
            'is_btts_correct'      => $this->is_btts_correct,

            'accuracy_score'       => $this->accuracy_score,

            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
