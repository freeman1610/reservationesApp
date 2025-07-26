<?php

namespace App\Repositories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Collection;

/**
 */
interface SpaceRepositoryInterface
{
    /**
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * @param int $id
     * @return Space|null
     */
    public function findById(int $id): ?Space;

    /**
     * @param array $data
     * @return Space
     */
    public function create(array $data): Space;

    /**
     * @param int $id
     * @param array $data
     * @return Space
     */
    public function update(int $id, array $data): Space;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
