<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Services\UserServiceInterface;
use App\Http\Requests\ProfileUpdateRequest;

/**
 * @OA\Schema(
 * schema="UserResource",
 * title="User Resource",
 * description="User resource model",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="John Doe"),
 * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 * @OA\Property(property="role", type="string", example="user"),
 * @OA\Property(property="avatar_url", type="string", example="/storage/avatars/default_avatar.png"),
 * @OA\Property(property="max_simultaneous_reservations", type="integer", example=5),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time"),
 * @OA\Property(property="is_admin", type="boolean", example=false),
 * @OA\Property(property="is_user", type="boolean", example=true)
 * )
 */

class ProfileController extends Controller
{
    protected UserServiceInterface $userService;

    /**
     *
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

     /**
     * @OA\Get(
     * path="/api/profile",
     * summary="Get the authenticated user's profile",
     * tags={"Profile"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(ref="#/components/schemas/UserResource")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * @OA\Post(
     * path="/api/profile",
     * summary="Update the authenticated user's profile",
     * tags={"Profile"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="User data to update. Use multipart/form-data to upload an avatar. Note: _method parameter is not needed.",
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="name", type="string", example="John Doe Updated"),
     * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     * @OA\Property(property="avatar", type="string", format="binary", description="Image file for the avatar."),
     * @OA\Property(property="remove_avatar", type="boolean", description="Set to true to remove current avatar.")
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Profile updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Perfil actualizado exitosamente."),
     * @OA\Property(property="user", ref="#/components/schemas/UserResource")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error",
     * @OA\JsonContent(ref="#/components/schemas/ValidationError")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $avatar = $request->file('avatar');
        $user = $request->user();

        $this->authorize('updateProfile', $user);

        $updatedUser = $this->userService->updateProfile($user, $data, $avatar);

        if (!$updatedUser) {
            return response()->json(['message' => 'Error al actualizar el perfil.'], 500);
        }

        return response()->json([
            'message' => 'Perfil actualizado exitosamente.',
            'user' => new UserResource($updatedUser)
        ]);
    }
}

/**
 * @OA\Schema(
 * schema="UserResource",
 * title="User Resource",
 * description="User resource model",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="John Doe"),
 * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 * @OA\Property(property="role", type="string", example="user"),
 * @OA\Property(property="avatar_url", type="string", example="/storage/avatars/default_avatar.png"),
 * @OA\Property(property="max_simultaneous_reservations", type="integer", example=5),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time"),
 * @OA\Property(property="is_admin", type="boolean", example=true),
 * @OA\Property(property="is_user", type="boolean", example=false)
 * )
 */