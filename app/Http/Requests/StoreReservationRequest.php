<?php
namespace App\Http\Requests;

use Carbon\Carbon;
use App\Models\Space;
use App\Repositories\ReservationRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReservationRequest extends FormRequest
{
    protected $reservationRepository;
    public function __construct(ReservationRepository $reservationRepository) { $this->reservationRepository = $reservationRepository; }
    public function authorize(): bool { return true; }

    /**
     */
    protected function prepareForValidation()
    {
        if ($this->has(['reservation_date', 'start_time', 'end_time'])) {
            $date = $this->reservation_date;
            $startTime = Carbon::parse($this->start_time)->format('H:i:s');
            $endTime = Carbon::parse($this->end_time)->format('H:i:s');

            $this->merge([
                'start_time' => $date . ' ' . $startTime,
                'end_time' => $date . ' ' . $endTime,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'space_id' => 'required|exists:spaces,id',
            'reservation_date' => 'required|date',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'nullable|string|max:255',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) return;

            $startTime = Carbon::parse($this->input('start_time'));
            $endTime = Carbon::parse($this->input('end_time'));
            $spaceId = $this->input('space_id');
            $space = Space::find($spaceId);

            // 1. Validation of Schedule Availability
            if (!$this->isWithinAvailableHours($space, $startTime, $endTime)) {
                $validator->errors()->add('start_time', 'El horario solicitado está fuera de las horas de disponibilidad del espacio.');
                return;
            }

            // 2. Overlay Validation
            $overlapping = $this->reservationRepository->findOverlappingReservations($spaceId, $startTime, $endTime);
            if ($overlapping->isNotEmpty()) {
                $validator->errors()->add('start_time', 'El espacio no está disponible en el horario seleccionado (ya hay otra reserva).');
            }

            // 3. Reservation Limit Validation
            $user = $this->user();
            $limit = $user->max_simultaneous_reservations;
            if ($limit !== null && $limit > 0) {
                $activeReservations = $this->reservationRepository->countActiveUserReservations($user->id);
                if ($activeReservations >= $limit) {
                    $validator->errors()->add('user_id', "Has alcanzado tu límite de {$limit} reservas activas.");
                }
            }
        });
    }
    
    private function isWithinAvailableHours(Space $space, Carbon $startTime, Carbon $endTime): bool
    {
        if (!$space->availability) return false;
        $dayOfWeekName = strtolower($startTime->dayName);
        $availabilityForDay = $space->availability[$dayOfWeekName] ?? null;
        if (!$availabilityForDay) return false;

        $requestedStartTime = $startTime->format('H:i:s');
        $requestedEndTime = $endTime->format('H:i:s');

        foreach ($availabilityForDay as $slot) {
            if ($requestedStartTime >= ($slot['start'] . ':00') && $requestedEndTime <= ($slot['end'] . ':00')) {
                return true;
            }
        }
        return false;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['message' => 'Error de validación', 'errors' => $validator->errors()], 422));
    }
}
