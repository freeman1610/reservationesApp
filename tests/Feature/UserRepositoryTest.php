<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Repositories\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentUserRepository();
    }

    /** @test */
    public function it_can_create_a_user()
    {
        $userData = [
            'name' => 'Usuario de Prueba',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'user',
        ];

        $user = $this->repository->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertEquals('user', $user->role);
    }

    /** @test */
    public function it_can_update_a_user()
    {
        $user = User::factory()->create(['name' => 'Nombre Original']);

        $updatedData = ['name' => 'Nombre Actualizado', 'role' => 'admin'];

        $lastUser = User::latest()->first();

        $result = $this->repository->update($lastUser->id, $updatedData);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Nombre Actualizado', $result->name);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nombre Actualizado',
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function it_can_find_a_user_by_email()
    {
        $user = User::factory()->create(['email' => 'findme@example.com']);

        $foundUser = $this->repository->findByEmail('findme@example.com');

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    /** @test */
    public function it_returns_null_if_user_is_not_found_by_email()
    {
        $foundUser = $this->repository->findByEmail('nonexistent@example.com');
        $this->assertNull($foundUser);
    }

    /** @test */
    public function it_can_get_all_users_except_one()
    {
        $userToExclude = User::factory()->create();
        User::factory()->count(5)->create();

        $users = $this->repository->getAllExcept($userToExclude->id);

        $this->assertCount(5, $users);
        $this->assertFalse($users->contains('id', $userToExclude->id));
    }
}