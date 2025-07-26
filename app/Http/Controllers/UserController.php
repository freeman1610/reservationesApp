<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Services\UserServiceInterface;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;

class UserController extends Controller
{
    protected UserServiceInterface $userService;

    /**
     *
     * @param UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     * path="/api/users",
     * summary="List all users",
     * tags={"Users (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="A list of users",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/UserResource"))
     * ),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $users = $this->userService->getAllUsers();
        return UserResource::collection($users);
    }

    /**
     * @OA\Get(
     * path="/api/users/{id}",
     * summary="Get a specific user",
     * tags={"Users (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="User details",
     * @OA\JsonContent(ref="#/components/schemas/UserResource")
     * ),
     * @OA\Response(response=404, description="Not Found"),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(int $id): UserResource|JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }
        $this->authorize('view', $user);
        return new UserResource($user);
    }

    /**
     * @OA\Post(
     * path="/api/users",
     * summary="Create a new user",
     * tags={"Users (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"name", "email", "password", "role"},
     * @OA\Property(property="name", type="string", example="Jane Doe"),
     * @OA\Property(property="email", type="string", format="email", example="jane.doe@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="newpassword"),
     * @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user"),
     * @OA\Property(property="avatar", type="string", format="binary", description="Avatar image file."),
     * @OA\Property(property="max_simultaneous_reservations", type="integer", example=5)
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Usuario creado exitosamente."),
     * @OA\Property(property="data", ref="#/components/schemas/UserResource")
     * )
     * ),
     * @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(UserStoreRequest $request): UserResource|JsonResponse
    {
        $this->authorize('create', User::class);
        $data = $request->validated();
        $avatar = $request->file('avatar');

        $user = $this->userService->createUser($data, $avatar);

        return response()->json([
            'message' => 'Usuario creado exitosamente.',
            new UserResource($user)
        ], 201);
    }

     /**
     * @OA\Put(
     * path="/api/users/{id}",
     * summary="Update a user",
     * tags={"Users (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * @OA\MediaType(
     * mediaType="application/json",
     * @OA\Schema(
     * @OA\Property(property="name", type="string", example="Jane Doe Updated"),
     * @OA\Property(property="email", type="string", format="email", example="jane.doe.updated@example.com"),
     * @OA\Property(property="password", type="string", format="password", description="Optional. Min 8 characters."),
     * @OA\Property(property="role", type="string", enum={"admin", "user"}, example="admin"),
     * @OA\Property(property="max_simultaneous_reservations", type="integer", example=10)
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="User updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Usuario actualizado exitosamente."),
     * @OA\Property(property="data", ref="#/components/schemas/UserResource")
     * )
     * ),
     * @OA\Response(response=404, description="Not Found"),
     * @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(UserUpdateRequest $request, int $id): UserResource|JsonResponse
    {
        $user = $this->userService->getUserById($id);
        $this->authorize('update', $user); 

        $data = $request->validated();
        $avatar = $request->file('avatar');

        $user = $this->userService->updateUser($id, $data, $avatar);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }
        
        return response()->json([
            'message' => 'Usuario actualizado exitosamente.',
            new UserResource($user)
        ]);
    }

    /**
     * @OA\Delete(
     * path="/api/users/{id}",
     * summary="Delete a user",
     * tags={"Users (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="User deleted",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Usuario eliminado exitosamente."))
     * ),
     * @OA\Response(response=404, description="Not Found"),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        
        $this->authorize('delete', $user);

        $deleted = $this->userService->deleteUser($id);

        if (!$deleted) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }
        
        return response()->json([
            'message' => 'Usuario eliminado exitosamente.'
        ], 200);
    }
}

