<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Space;
use App\Repositories\EloquentSpaceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpaceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentSpaceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentSpaceRepository();
    }

    /** @test */
    public function it_can_create_a_space()
    {
        $spaceData = [
            'name' => 'Nueva Sala de Pruebas',
            'type' => 'room',
            'description' => 'Descripción de la sala.',
            'capacity' => 15,
            'location' => 'Piso 10',
        ];

        $space = $this->repository->create($spaceData);

        $this->assertInstanceOf(Space::class, $space);
        $this->assertDatabaseHas('spaces', ['name' => 'Nueva Sala de Pruebas']);
    }

    /** @test */
    public function it_can_update_a_space()
    {
        $space = Space::factory()->create(['capacity' => 10]);

        $updatedData = ['capacity' => 20, 'location' => 'Piso 12'];

        $lastSpace = Space::latest()->first();

        $result = $this->repository->update($lastSpace->id, $updatedData);

        $this->assertInstanceOf(Space::class, $result);
        $this->assertEquals(20, $result->capacity);
        
        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'capacity' => 20,
            'location' => 'Piso 12',
        ]);
    }

    /** @test */
    public function it_can_get_all_spaces()
    {
        Space::factory()->count(5)->create();

        $spaces = $this->repository->getAll();

        $this->assertCount(5, $spaces);
        $this->assertInstanceOf(Space::class, $spaces->first());
    }

    /** @test */
    public function it_can_find_a_space_by_id()
    {
        $space = Space::factory()->create();

        $foundSpace = $this->repository->findById($space->id);

        $this->assertNotNull($foundSpace);
        $this->assertEquals($space->id, $foundSpace->id);
    }
}
