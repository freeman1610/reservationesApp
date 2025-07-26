<?php
// app/Policies/SpacePolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Space;
use Illuminate\Auth\Access\Response;

class SpacePolicy
{
    /**
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     */
    public function view(User $user, Space $space): bool
    {
        return $user->role === 'admin';
    }

    /**
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     */
    public function update(User $user, Space $space): bool
    {
        return $user->role === 'admin';
    }

    /**
     */
    public function delete(User $user, Space $space): bool
    {
        return $user->role === 'admin';
    }
}

