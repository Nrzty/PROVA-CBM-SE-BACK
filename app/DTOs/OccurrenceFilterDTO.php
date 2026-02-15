<?php

namespace App\DTOs;

use App\Enums\OccurrenceEnums\OccurrenceStatus;
use App\Enums\OccurrenceEnums\OccurrenceType;

class OccurrenceFilterDTO
{
    public function __construct(
        private ?OccurrenceStatus $status,
        private ?OccurrenceType $type
    ) { }

    public function getType(): ?OccurrenceType
    {
        return $this->type;
    }

    public function getStatus(): ?OccurrenceStatus
    {
        return $this->status;
    }
}
