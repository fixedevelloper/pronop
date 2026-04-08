<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PotResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'entry_fee' => $this->entry_fee,
            'total_amount' => $this->total_amount,
            'type' => $this->type,
            'status' => $this->status,
            'creator' => $this->creator->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'distribution_rule' => $this->distribution_rule,
            'participants_count' => $this->participants,
            'participants' => SubscriptionPotResource::collection($this->whenLoaded('subscriptions')),
            'lines' => LinePotFootResource::collection($this->whenLoaded('footLines')),
        ];
    }
}
