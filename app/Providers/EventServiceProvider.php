<?php

namespace App\Providers;

use App\Events\ReservationCreated;
use Illuminate\Support\Facades\Event;
use App\Listeners\SendNewReservationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     */
    protected $listen = [
        ReservationCreated::class => [
            SendNewReservationNotification::class,
        ],
    ];

}