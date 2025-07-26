<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    /**
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     *
     * @return Collection<int, User>
     */
    public function getAllUsers(): Collection
    {
        return $this->userRepository->all();
    }

    /**
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $avatar
     * @return User
     */
    public function createUser(array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar) {
            $data['avatar'] = $this->uploadAvatar($avatar);
        } else {
            $data['avatar'] = 'default_avatar.png';
        }

        return $this->userRepository->create($data);
    }

    /**
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @param UploadedFile|null $avatar
     * @return User|null
     */
    public function updateUser(int $id, array $data, ?UploadedFile $avatar = null): ?User
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return null;
        }

        if ($avatar) {
            if ($user->avatar && $user->avatar !== 'default_avatar.png') {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            $data['avatar'] = $this->uploadAvatar($avatar);
        } elseif (isset($data['remove_avatar']) && $data['remove_avatar']) {
            if ($user->avatar && $user->avatar !== 'default_avatar.png') {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            $data['avatar'] = 'default_avatar.png';
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    /**
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @param UploadedFile|null $avatar
     * @return User|null
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): ?User
    {
        if ($avatar) {
            if ($user->avatar && $user->avatar !== 'default_avatar.png') {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            $data['avatar'] = $this->uploadAvatar($avatar);
        } elseif (isset($data['remove_avatar']) && $data['remove_avatar']) {
            if ($user->avatar && $user->avatar !== 'default_avatar.png') {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            $data['avatar'] = 'default_avatar.png';
        }

        // Do not allow changing the role or password from the user profile (admin only)
        unset($data['role']);
        unset($data['password']);

        return $this->userRepository->update($user->id, $data);
    }

    /**
     *
     * @param UploadedFile $avatar
     * @return string
     */
    protected function uploadAvatar(UploadedFile $avatar): string
    {
        $filename = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
        $avatar->storeAs('avatars', $filename, 'public');
        return $filename;
    }
}

