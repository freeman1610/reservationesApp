<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendNewReservationNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ReservationCreated $event): void
    {
        $webhookUrl = env('RESERVATION_WEBHOOK_URL');

        if (!$webhookUrl) {
            Log::info('No se envió el webhook de reserva porque la URL no está configurada.');
            return;
        }

        try {
            $reservation = $event->reservation;

            $reservation->load('user', 'space');

            Http::post($webhookUrl, [
                'event' => 'reservation.created',
                'timestamp' => now()->toIso8601String(),
                'data' => $reservation->toArray(),
            ]);

            Log::info('Webhook de nueva reserva enviado exitosamente para la reserva ID: ' . $reservation->id);

        } catch (\Exception $e) {
            Log::error('Falló el envío del webhook de reserva: ' . $e->getMessage());
        }
    }
}