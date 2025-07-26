<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Services\SpaceService;
use App\Http\Controllers\Controller;
use App\Http\Resources\SpaceResource;
use App\Http\Requests\FindSpacesRequest;
use App\Http\Requests\StoreSpaceRequest;
use App\Http\Requests\UpdateSpaceRequest;
use App\Repositories\EloquentSpaceRepository;

/**
 * @OA\Schema(
 * schema="SpaceResource",
 * title="Space Resource",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="attributes", type="object",
 * @OA\Property(property="name", type="string", example="Meeting Room A"),
 * @OA\Property(property="description", type="string", example="Main meeting room with projector."),
 * @OA\Property(property="type", type="string", enum={"room", "desk", "hall"}, example="room"),
 * @OA\Property(property="capacity", type="integer", example=10),
 * @OA\Property(property="location", type="string", example="Floor 2, North Wing"),
 * @OA\Property(property="availability", type="object", example={"monday":{{"start":"09:00", "end":"18:00"}}}),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * )
 */

class SpaceController extends Controller
{
    protected $spaceService;

    public function __construct(SpaceService $spaceService)
    {
        $this->spaceService = $spaceService;
    }

    /**
     * @OA\Get(
     * path="/api/spaces",
     * summary="List and filter available spaces",
     * tags={"Spaces (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"room", "desk", "hall"}), description="Filter by space type"),
     * @OA\Parameter(name="capacity", in="query", @OA\Schema(type="integer", minimum=1), description="Filter by minimum capacity"),
     * @OA\Parameter(name="date", in="query", @OA\Schema(type="string", format="date"), description="Filter by availability date (e.g., 2025-08-01)"),
     * @OA\Parameter(name="start_time", in="query", @OA\Schema(type="string", format="time"), description="Filter by start time (e.g., 09:00)"),
     * @OA\Parameter(name="end_time", in="query", @OA\Schema(type="string", format="time"), description="Filter by end time (e.g., 10:00)"),
     * @OA\Response(
     * response=200,
     * description="A list of spaces",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SpaceResource"))
     * ),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(FindSpacesRequest $request, EloquentSpaceRepository $spaceRepository)
    {
        $this->authorize('viewAny', Space::class);
        $spaces = $spaceRepository->findAvailable($request->validated());
        return SpaceResource::collection($spaces);
    }

    /**
     * @OA\Post(
     * path="/api/spaces",
     * summary="Create a new space",
     * tags={"Spaces (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "type", "description", "capacity", "location"},
     * @OA\Property(property="name", type="string", example="New Coworking Desk"),
     * @OA\Property(property="type", type="string", enum={"room", "desk", "hall"}, example="desk"),
     * @OA\Property(property="description", type="string", example="A quiet desk near the window."),
     * @OA\Property(property="capacity", type="integer", example=1),
     * @OA\Property(property="location", type="string", example="Floor 3"),
     * @OA\Property(
     * property="availability",
     * type="object",
     * example={"monday": {{"start": "09:00", "end": "18:00"}}},
     * description="Object with days of the week as keys. Each day has an array of time slots."
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Space created",
     * @OA\JsonContent(ref="#/components/schemas/SpaceResource")
     * ),
     * @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreSpaceRequest $request)
    {
        $this->authorize('create', Space::class);
        $space = $this->spaceService->createNewSpace($request->validated());
        return new SpaceResource($space);
    }

    /**
     * @OA\Get(
     * path="/api/spaces/{id}",
     * summary="Get a specific space",
     * tags={"Spaces (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Space details",
     * @OA\JsonContent(ref="#/components/schemas/SpaceResource")
     * ),
     * @OA\Response(response=404, description="Not Found"),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Space $space)
    {
        $this->authorize('view', $space);
        return new SpaceResource($space);
    }

    /**
     * @OA\Put(
     * path="/api/spaces/{id}",
     * summary="Update a space",
     * tags={"Spaces (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Updated Room Name"),
     * @OA\Property(property="capacity", type="integer", example=12)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Space updated",
     * @OA\JsonContent(ref="#/components/schemas/SpaceResource")
     * ),
     * @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(UpdateSpaceRequest $request, Space $space)
    {
        $this->authorize('update', $space);
        $updatedSpace = $this->spaceService->updateSpace($space->id, $request->validated());
        return new SpaceResource($updatedSpace);
    }

    /**
     * @OA\Delete(
     * path="/api/spaces/{id}",
     * summary="Delete a space",
     * tags={"Spaces (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Space deleted",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Espacio eliminado exitosamente."))
     * ),
     * @OA\Response(response=404, description="Not Found"),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(Space $space)
    {
        $this->authorize('delete', $space);
        $this->spaceService->deleteSpace($space->id);
        return response()->json([
            'message' => 'Espacio eliminado exitosamente.'
        ], 200);
    }
}
