<?php

namespace App\Providers;

use App\Services\UserService;
use App\Services\UserServiceInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\EloquentUserRepository;
use App\Repositories\EloquentSpaceRepository;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\SpaceRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(SpaceRepositoryInterface::class,EloquentSpaceRepository::class);

    }

    /**
     */
    public function boot(): void
    {
        //
    }
}

