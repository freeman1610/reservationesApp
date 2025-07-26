<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     *
     * @return Collection<int, User>
     */
    public function all(): Collection
    {
        return User::all();
    }

    /**
     *
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    /**
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return User|null
     */
    public function update(int $id, array $data): ?User
    {
        $user = $this->find($id);
        if ($user) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            $user->update($data);
        }
        return $user;
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $user = $this->find($id);
        if ($user) {
            if ($user->avatar && $user->avatar !== 'default_avatar.png') {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            return $user->delete();
        }
        return false;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
    
    public function getAllExcept(int $userId): Collection
    {
        return User::where('id', '!=', $userId)->get();
    }
}

