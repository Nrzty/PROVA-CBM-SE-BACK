<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Occurrence;
use App\Enums\OccurrenceEnums\OccurrenceStatus;
use App\Enums\OccurrenceEnums\OccurrenceType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OccurrenceListTest extends TestCase
{
    use RefreshDatabase;

    private array $headers = [
        'X-API-Key' => 'eu-vou-passar',
    ];

    /** @test */
    public function deve_retornar_lista_paginada()
    {
        Occurrence::factory()->count(5)->create();

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/occurrences');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    /** @test */
    public function deve_filtrar_por_status()
    {
        Occurrence::factory()->create([
            'status' => OccurrenceStatus::REPORTED->value,
        ]);

        Occurrence::factory()->create([
            'status' => OccurrenceStatus::RESOLVED->value,
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/occurrences?status=reported');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function deve_filtrar_por_type()
    {
        Occurrence::factory()->create([
            'type' => OccurrenceType::URBAN_FIRE->value,
        ]);

        Occurrence::factory()->create([
            'type' => OccurrenceType::VEHICLE_RESCUE->value,
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/occurrences?type=incendio_urbano');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function deve_filtrar_por_status_e_type()
    {
        Occurrence::factory()->create([
            'status' => OccurrenceStatus::REPORTED->value,
            'type' => OccurrenceType::URBAN_FIRE->value,
        ]);

        Occurrence::factory()->create([
            'status' => OccurrenceStatus::RESOLVED->value,
            'type' => OccurrenceType::URBAN_FIRE->value,
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/occurrences?status=reported&type=incendio_urbano');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function deve_respeitar_limite_maximo_de_per_page()
    {
        Occurrence::factory()->count(150)->create();

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/occurrences?per_page=500');

        $response->assertOk();

        $this->assertLessThanOrEqual(100, count($response->json('data')));
    }

    /** @test */
    public function deve_retornar_422_para_status_invalido()
    {
        $response = $this->withHeaders($this->headers)
            ->getJson('/api/occurrences?status=qualquer_coisa');

        $response->assertUnprocessable();
    }

    /** @test */
    public function deve_retornar_401_sem_api_key()
    {
        $response = $this->getJson('/api/occurrences');

        $response->assertUnauthorized();
    }
}
