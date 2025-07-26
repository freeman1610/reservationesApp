<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use App\Http\Resources\ReservationResource;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Schema(
 * schema="ReservationResource",
 * title="Reservation Resource",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="purpose", type="string", example="Team meeting"),
 * @OA\Property(property="status", type="string", example="confirmed"),
 * @OA\Property(property="start_time", type="string", format="date-time", example="2025-08-01 10:00:00"),
 * @OA\Property(property="end_time", type="string", format="date-time", example="2025-08-01 11:00:00"),
 * @OA\Property(property="user", ref="#/components/schemas/UserResource"),
 * @OA\Property(property="space", ref="#/components/schemas/SpaceResource")
 * )
 */

class ReservationController extends Controller
{
    protected $reservationService;

     public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * @OA\Get(
     * path="/api/reservations",
     * summary="List user's reservations",
     * tags={"Reservations"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="A list of reservations",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ReservationResource"))
     * ),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $reservations = $this->reservationService->getUserReservations();
        return ReservationResource::collection($reservations);
    }

    /**
     * @OA\Post(
     * path="/api/reservations",
     * summary="Create a new reservation",
     * tags={"Reservations"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"space_id", "reservation_date", "start_time", "end_time"},
     * @OA\Property(property="space_id", type="integer", example=1),
     * @OA\Property(property="reservation_date", type="string", format="date", example="2025-08-01"),
     * @OA\Property(property="start_time", type="string", format="time", example="10:00"),
     * @OA\Property(property="end_time", type="string", format="time", example="11:00"),
     * @OA\Property(property="purpose", type="string", example="Important client meeting")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Reservation created successfully",
     * @OA\JsonContent(ref="#/components/schemas/ReservationResource")
     * ),
     * @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreReservationRequest $request): ReservationResource
    {
        $reservation = $this->reservationService->createReservation($request->validated());
        return new ReservationResource($reservation->load(['user', 'space']));
    }

    /**
     * @OA\Get(
     * path="/api/reservations/{id}",
     * summary="Get a specific reservation",
     * tags={"Reservations"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Reservation details",
     * @OA\JsonContent(ref="#/components/schemas/ReservationResource")
     * ),
     * @OA\Response(response=404, description="Not Found"),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Reservation $reservation): ReservationResource
    {
        $this->authorize('view', $reservation);
        return new ReservationResource($reservation->load(['user', 'space']));
    }

    /**
     * @OA\Put(
     * path="/api/reservations/{id}",
     * summary="Update a reservation",
     * tags={"Reservations"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * @OA\JsonContent(
     * @OA\Property(property="space_id", type="integer", example=2),
     * @OA\Property(property="reservation_date", type="string", format="date", example="2025-08-02"),
     * @OA\Property(property="start_time", type="string", format="time", example="14:00"),
     * @OA\Property(property="end_time", type="string", format="time", example="15:30"),
     * @OA\Property(property="purpose", type="string", example="Updated purpose")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Reservation updated",
     * @OA\JsonContent(ref="#/components/schemas/ReservationResource")
     * ),
     * @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): ReservationResource
    {
        $this->authorize('update', $reservation);
        $this->reservationService->updateReservation($reservation, $request->validated());
        return new ReservationResource($reservation->fresh()->load(['user', 'space']));
    }

    /**
     * @OA\Delete(
     * path="/api/reservations/{id}",
     * summary="Cancel a reservation",
     * tags={"Reservations"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Reservation cancelled",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Reservación cancelada correctamente."))
     * ),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        $this->authorize('delete', $reservation);
        try {
            $this->reservationService->cancelReservation($reservation);
            return response()->json(['message' => 'Reservación cancelada correctamente.']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
