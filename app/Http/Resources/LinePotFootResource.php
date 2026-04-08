<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class LinePotFootResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'fixture_id' => $this->fixture_id,
            'result' => $this->result,
            'fixture' => $this->whenLoaded('fixture', function () {
                return new FixtureResource($this->fixture);
            }),
        ];
    }
}
