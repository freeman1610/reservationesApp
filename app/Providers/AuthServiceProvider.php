<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Space;
use App\Models\Reservation;
use App\Policies\UserPolicy;
use App\Policies\SpacePolicy;
use App\Policies\ReservationPolicy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Reservation::class => ReservationPolicy::class,
        Space::class => SpacePolicy::class,
        User::class => UserPolicy::class,
    ];
}