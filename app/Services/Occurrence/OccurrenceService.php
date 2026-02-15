<?php

namespace App\Services\Occurrence;

use App\DTOs\OccurrenceDTO;
use App\DTOs\OccurrenceFilterDTO;
use App\Models\Occurrence;

class OccurrenceService
{
    private const LIMIT_PER_PAGE = 100;

    public function __construct(

    ) { }

    public function list(OccurrenceFilterDTO $filters, int $amountPerPage)
    {
        $perPage = min($amountPerPage, self::LIMIT_PER_PAGE);

        $query = Occurrence::query();

        if ($filters->getStatus())
        {
            $query->where("status", $filters->getStatus()->value);
        }

        if ($filters->getType())
        {
            $query->where("type", $filters->getType()->value);
        }

        return $query->paginate($perPage);
    }
}
