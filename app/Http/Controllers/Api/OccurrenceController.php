<?php

namespace App\Http\Controllers\Api;

use App\DTOs\OccurrenceFilterDTO;
use App\Enums\OccurrenceEnums\OccurrenceStatus;
use App\Enums\OccurrenceEnums\OccurrenceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListOccurrencesRequest;
use App\Http\Resources\OccurrenceResource;
use App\Services\Occurrence\OccurrenceService;

class OccurrenceController extends Controller
{
    public function __construct(
       private OccurrenceService $occurrenceService,
    ){ }

    public function listAllOccurences(ListOccurrencesRequest $request)
    {
        $validated = $request->validated();

        $status = isset($validated['status'])
            ? OccurrenceStatus::from($validated['status'])
            : null;

        $type = isset($validated['type'])
            ? OccurrenceType::from($validated['type'])
            : null;

        $filters = new OccurrenceFilterDTO($status, $type);

        $perPage = (int) $request->query('per_page', 15);

        $result = $this->occurrenceService->list($filters, $perPage);

        return OccurrenceResource::collection($result);
    }
}
