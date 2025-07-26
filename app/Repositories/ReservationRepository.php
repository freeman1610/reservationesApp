<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ReservationRepository
{
    /**
     *
     * @param array $data
     * @return Reservation
     */
    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    /**
     *
     * @param Reservation $reservation
     * @param array $data
     * @return bool
     */
    public function update(Reservation $reservation, array $data): bool
    {
        return $reservation->update($data);
    }

    /**
     *
     * @param Reservation $reservation
     * @return bool
     */
    public function delete(Reservation $reservation): bool
    {
        return $reservation->delete();
    }

    /**
     *
     * @param Reservation $reservation
     * @param string $status
     * @return bool
     */
    public function changeStatus(Reservation $reservation, string $status): bool
    {
        return $reservation->update(['status' => $status]);
    }

    /**
     * Find reservations that overlap a time range for a specific space.
     */
    public function findOverlappingReservations(int $spaceId, Carbon $startTime, Carbon $endTime, ?int $exceptReservationId = null)
    {
        return Reservation::where('space_id', $spaceId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->when($exceptReservationId, fn($query, $id) => $query->where('id', '!=', $id))
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();
    }

    /**
     */
    public function countActiveUserReservations(int $userId): int
    {
        return Reservation::where('user_id', $userId)
            ->whereIn('status', ['pending', 'confirmed', 'cancelled'])
            ->count();
    }

    /**
     */
    public function getForAuthenticatedUser()
    {
        if (Auth::user()->role === 'admin') {
            return Reservation::with('space', 'user')->latest()->get();
        }
        return Reservation::where('user_id', Auth::id())->with('space')->latest()->get();
    }
}
