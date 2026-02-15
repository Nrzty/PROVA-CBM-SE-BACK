<?php

namespace Database\Factories;

use App\Enums\OccurrenceEnums\OccurrenceStatus;
use App\Enums\OccurrenceEnums\OccurrenceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Occurrence>
 */
class OccurrenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => $this->faker->unique()->uuid(),
            'type' => OccurrenceType::URBAN_FIRE->value,
            'status' => OccurrenceStatus::REPORTED->value,
            'description' => $this->faker->sentence(),
            'reported_at' => now(),
        ];
    }

}
