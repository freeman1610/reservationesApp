<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Space;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spaces = Space::all();
        $users = User::all();

        if ($spaces->isEmpty() || $users->isEmpty()) {
            $this->command->info('No se pueden crear reservaciones. Faltan espacios o usuarios.');
            return;
        }

        foreach ($spaces as $space) {
            // Crear 5 reservaciones por cada espacio
            for ($i = 0; $i < 5; $i++) {
                $this->createValidReservationForSpace($space, $users->random());
            }
        }
    }

    /**
     * Crea una reservación válida respetando la disponibilidad del espacio.
     */
    private function createValidReservationForSpace(Space $space, User $user): void
    {
        $availability = $space->availability;
        if (empty($availability)) {
            return; // No se puede reservar si no hay disponibilidad definida
        }
        
        $availableDays = array_keys($availability);
        
        // Intentar hasta 10 veces encontrar un slot válido
        for ($attempt = 0; $attempt < 10; $attempt++) {
            // Elige un día aleatorio de los disponibles (ya en inglés)
            $randomDayName = $availableDays[array_rand($availableDays)];
            $slots = $availability[$randomDayName];
            $randomSlot = $slots[array_rand($slots)];

            // Calcula una fecha futura que corresponda a ese día de la semana
            $date = Carbon::now()->next($randomDayName);

            // Genera una hora de inicio y fin dentro del slot
            $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $randomSlot['start']);
            $slotEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $randomSlot['end']);

            // Asegurarse de que el slot no sea demasiado corto
            if ($slotStart->diffInMinutes($slotEnd) < 60) {
                continue; // Slot muy corto, intentar con otro
            }

            $reservationStartHour = rand($slotStart->hour, $slotEnd->hour - 1);
            $reservationStart = $date->copy()->setTime($reservationStartHour, 0, 0);
            $reservationEnd = $reservationStart->copy()->addHour(); // Reservas de 1 hora

            // Verificar que no se pase del final del slot
            if ($reservationEnd->gt($slotEnd)) {
                continue; // La reserva se sale del slot, intentar de nuevo
            }

            // Verificar que no se superponga con otra reserva ya creada en este seeder
            $isOverlapping = Reservation::where('space_id', $space->id)
                ->where('start_time', '<', $reservationEnd)
                ->where('end_time', '>', $reservationStart)
                ->exists();

            if (!$isOverlapping) {
                Reservation::create([
                    'space_id' => $space->id,
                    'user_id' => $user->id,
                    'reservation_date' => $date,
                    'start_time' => $reservationStart,
                    'end_time' => $reservationEnd,
                    'purpose' => 'Reserva de prueba',
                    'status' => ['pending', 'confirmed', 'cancelled'][array_rand(['pending', 'confirmed', 'cancelled'])],
                ]);
                return;
            }
        }
    }
}

