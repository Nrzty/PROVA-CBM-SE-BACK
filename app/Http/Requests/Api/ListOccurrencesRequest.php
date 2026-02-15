<?php

namespace App\Http\Requests\Api;

use App\Enums\OccurrenceEnums\OccurrenceStatus;
use App\Enums\OccurrenceEnums\OccurrenceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOccurrencesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                Rule::enum(OccurrenceStatus::class),
            ],
            'type' => [
                'nullable',
                Rule::enum(OccurrenceType::class),
            ]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
