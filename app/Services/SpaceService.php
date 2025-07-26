<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Repositories\SpaceRepositoryInterface;

/**
 */
class SpaceService
{
    protected $spaceRepository;

    public function __construct(SpaceRepositoryInterface $spaceRepository)
    {
        $this->spaceRepository = $spaceRepository;
    }

    /**
     */
    public function getAllSpaces()
    {
        return $this->spaceRepository->getAll();
    }

    /**
     */
    public function getSpaceById(int $id)
    {
        return $this->spaceRepository->findById($id);
    }

    /**
     */
    public function createNewSpace(array $data)
    {
        return $this->spaceRepository->create($data);
    }

    /**
     */
    public function updateSpace(int $id, array $data)
    {
        return $this->spaceRepository->update($id, $data);
    }

    /**
     */
    public function deleteSpace(int $id)
    {
        return $this->spaceRepository->delete($id);
    }
}
