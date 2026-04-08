<?php


namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ModelCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => (int) $this->resource->total(),
                'per_page' => (int) $this->resource->perPage(),
                'current_page' => (int) $this->resource->currentPage(),
                'last_page' => (int) $this->resource->lastPage(),
            ],
        ];
    }
}
