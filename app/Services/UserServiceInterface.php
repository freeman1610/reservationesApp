<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;

interface UserServiceInterface
{
    /**
     *
     * @return Collection<int, User>
     */
    public function getAllUsers(): Collection;

    /**
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User;

    /**
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $avatar
     * @return User
     */
    public function createUser(array $data, ?UploadedFile $avatar = null): User;

    /**
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @param UploadedFile|null $avatar
     * @return User|null
     */
    public function updateUser(int $id, array $data, ?UploadedFile $avatar = null): ?User;

    /**
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool;

    /**
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @param UploadedFile|null $avatar
     * @return User|null
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): ?User;
}

