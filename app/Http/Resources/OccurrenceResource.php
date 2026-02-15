<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OccurrenceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'externalId' => $this->external_id,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'reportedAt' => $this->reported_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
