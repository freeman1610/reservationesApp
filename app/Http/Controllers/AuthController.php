<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Services\UserServiceInterface;

class AuthController extends Controller
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
     * @OA\Post(
     * path="/api/register",
     * summary="Register a new user",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"name", "email", "password", "password_confirmation"},
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="password"),
     * @OA\Property(property="avatar", type="string", format="binary", description="Avatar image file.")
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User registered successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Usuario registrado exitosamente."),
     * @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     * @OA\Property(property="access_token", type="string", example="Bearer 1|abc...")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error",
     * @OA\JsonContent(ref="#/components/schemas/ValidationError")
     * )
     * )
     */
    public function register(RegisterRequest $request): UserResource|JsonResponse
    {
        $data = $request->validated();
        $avatar = $request->file('avatar');

        // By default, the role when registering is 'user'
        $data['role'] = 'user';
        $data['password'] = Hash::make($data['password']);

        $user = $this->userService->createUser($data, $avatar);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'user' => new UserResource($user),
            'access_token' => 'Bearer '.$token
        ], 201);
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Log in a user",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","password"},
     * @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Login successful",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Inicio de sesión exitoso."),
     * @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     * @OA\Property(property="access_token", type="string", example="Bearer 2|abc...")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Invalid credentials",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Credenciales inválidas."))
     * )
     * )
     */
    public function login(LoginRequest $request): UserResource|JsonResponse
    {   
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'user' => new UserResource($user),
            'access_token' => 'Bearer '.$token,
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/logout",
     * summary="Log out the current user",
     * tags={"Authentication"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successfully logged out",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Sesión cerrada exitosamente.")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function logout(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }
}

