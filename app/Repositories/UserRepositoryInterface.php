<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     *
     * @return Collection<int, User>
     */
    public function all(): Collection;

    /**
     *
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User;

    /**
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function create(array $data): User;

    /**
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return User|null
     */
    public function update(int $id, array $data): ?User;

    /**
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}

