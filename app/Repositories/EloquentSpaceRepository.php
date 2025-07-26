<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Space;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\SpaceRepositoryInterface;

/**
 * Implementación de SpaceRepositoryInterface usando Eloquent.
 */
class EloquentSpaceRepository implements SpaceRepositoryInterface
{
    public function getAll(): Collection
    {
        return Space::all();
    }

    public function findById(int $id): ?Space
    {
        return Space::find($id);
    }

    public function create(array $data): Space
    {
        return Space::create($data);
    }

    public function update(int $id, array $data): Space
    {
        $space = $this->findById($id);
        $space->update($data);
        return $space;
    }

    public function delete(int $id): bool
    {
        return Space::destroy($id) > 0;
    }
    public function findAvailable(array $filters)
    {
        $query = Space::query();

        $query->when($filters['type'] ?? null, function ($q, $type) {
            return $q->where('type', $type);
        });

        $query->when($filters['capacity'] ?? null, function ($q, $capacity) {
            return $q->where('capacity', '>=', $capacity);
        });

        if (!isset($filters['date'])) {
            return $query->get();
        }

        $date = Carbon::parse($filters['date']);
        $dayOfWeek = strtolower($date->format('l'));

        $query->whereJsonContainsKey("availability->{$dayOfWeek}");

        if (!isset($filters['start_time']) || !isset($filters['end_time'])) {
            return $query->get();
        }

        $startTime = $filters['start_time'];
        $endTime = $filters['end_time'];

        $potentialSpaces = $query->get();

        $availableSpaces = $potentialSpaces->filter(function ($space) use ($dayOfWeek, $startTime, $endTime) {
            $slots = $space->availability[$dayOfWeek] ?? [];
            foreach ($slots as $slot) {
                if ($startTime >= $slot['start'] && $endTime <= $slot['end']) {
                    return true;
                }
            }
            return false;
        });

        return $availableSpaces;
    }
}
