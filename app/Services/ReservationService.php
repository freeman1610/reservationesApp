<?php

namespace App\Services;

use Exception;
use App\Models\Reservation;
use App\Events\ReservationCreated;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ReservationRepository;

class ReservationService
{
    protected $reservationRepository;

    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    /**
     *
     * @param array $data
     * @return Reservation
     */
    public function createReservation(array $data): Reservation
    {
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';
        
        $reservation = $this->reservationRepository->create($data);

        ReservationCreated::dispatch($reservation);

        return $reservation;
    }

    /**
     *
     * @param Reservation $reservation
     * @param array $data
     * @return bool
     */
    public function updateReservation(Reservation $reservation, array $data): bool
    {
        return $this->reservationRepository->update($reservation, $data);
    }

    /**
     *
     * @param Reservation $reservation
     * @return bool
     * @throws Exception
     */
    public function cancelReservation(Reservation $reservation): bool
    {
        // if (!in_array($reservation->status, ['pending', 'confirmed'])) {
        //     throw new Exception('Solo se pueden cancelar reservaciones con estado pendiente o confirmado.');
        // }

        return $this->reservationRepository->delete($reservation);
    }

    /**
     */
    public function getUserReservations()
    {
        return $this->reservationRepository->getForAuthenticatedUser();
    }
}