<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use App\Models\Space;
use App\Repositories\ReservationRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateReservationRequest extends FormRequest
{
    protected $reservationRepository;

    public function __construct(ReservationRepository $reservationRepository) { $this->reservationRepository = $reservationRepository; }
    public function authorize(): bool { return true; }

    protected function prepareForValidation()
    {
        if ($this->hasAny(['reservation_date', 'start_time', 'end_time'])) {
            $reservation = $this->route('reservation');
            
            $date = $this->reservation_date ?? $reservation->start_time->format('Y-m-d');
            
            $startTime = $this->start_time ? Carbon::parse($this->start_time)->format('H:i:s') : $reservation->start_time->format('H:i:s');
            $endTime = $this->end_time ? Carbon::parse($this->end_time)->format('H:i:s') : $reservation->end_time->format('H:i:s');

            $this->merge([
                'start_time' => $date . ' ' . $startTime,
                'end_time' => $date . ' ' . $endTime,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'space_id' => 'sometimes|required|exists:spaces,id',
            'reservation_date' => 'sometimes|required|date',
            'start_time' => 'sometimes|required|date|after:now',
            'end_time' => 'sometimes|required|date|after:start_time',
            'purpose' => 'nullable|string|max:255',
            'status' => 'sometimes|string|in:pending,confirmed,cancelled'
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) return;

            $reservation = $this->route('reservation');
            $startTime = Carbon::parse($this->input('start_time', $reservation->start_time));
            $endTime = Carbon::parse($this->input('end_time', $reservation->end_time));
            $spaceId = $this->input('space_id', $reservation->space_id);
            $space = Space::find($spaceId);

            if (!$this->isWithinAvailableHours($space, $startTime, $endTime)) {
                $validator->errors()->add('start_time', 'El horario solicitado está fuera de las horas de disponibilidad del espacio.');
                return;
            }

            $overlapping = $this->reservationRepository->findOverlappingReservations($spaceId, $startTime, $endTime, $reservation->id);
            if ($overlapping->isNotEmpty()) {
                $validator->errors()->add('start_time', 'El espacio no está disponible en el horario seleccionado (ya hay otra reserva).');
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