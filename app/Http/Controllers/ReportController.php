<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ActiveUserReportResource;
use App\Http\Resources\ReservationReportResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Schema(
 * schema="ReservationReportResource",
 * title="Reservation Report Resource",
 * @OA\Property(property="space_name", type="string", example="Sala de Juntas A"),
 * @OA\Property(property="date", type="string", format="date", example="2025-08-01"),
 * @OA\Property(property="reservation_count", type="integer", example=5)
 * )
 *
 * @OA\Schema(
 * schema="ActiveUserReportResource",
 * title="Active User Report Resource",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="Usuario Admin"),
 * @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 * @OA\Property(property="role", type="string", example="admin"),
 * @OA\Property(property="reservations_count", type="integer", example=12)
 * )
 */

class ReportController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/reports/reservations-by-space",
     * summary="Get reservation report by space",
     * tags={"Reports (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="start_date", in="query", required=true, @OA\Schema(type="string", format="date"), description="e.g. 2025-01-01"),
     * @OA\Parameter(name="end_date", in="query", required=true, @OA\Schema(type="string", format="date"), description="e.g. 2025-12-31"),
     * @OA\Parameter(name="space_id", in="query", @OA\Schema(type="integer"), description="Optional Space ID to filter"),
     * @OA\Response(
     * response=200,
     * description="Reservation report data",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ReservationReportResource"))
     * ),
     * @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function reservationsBySpace(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'space_id' => 'nullable|exists:spaces,id',
        ]);

        $query = DB::table('reservations')
            ->join('spaces', 'reservations.space_id', '=', 'spaces.id')
            ->select(
                'spaces.name as space_name',
                DB::raw('DATE(reservations.start_time) as date'),
                DB::raw('COUNT(reservations.id) as reservation_count')
            )
            ->whereDate('reservations.start_time', '>=', $request->start_date)
            ->whereDate('reservations.start_time', '<=', $request->end_date);

        if ($request->filled('space_id')) {
            $query->where('reservations.space_id', $request->space_id);
        }

        $reportData = $query->groupBy('space_name', 'date')
            ->orderBy('date', 'asc')
            ->orderBy('reservation_count', 'desc')
            ->get();

        return ReservationReportResource::collection($reportData);
    }

    /**
     * @OA\Get(
     * path="/api/reports/active-users",
     * summary="Get a report of active users (most reservations)",
     * tags={"Reports (Admin)"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="List of active users sorted by reservation count",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ActiveUserReportResource"))
     * ),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function activeUsers(): AnonymousResourceCollection
    {   
        $activeUsers = User::select('id', 'name', 'email', 'role')
            ->withCount('reservations')
            ->orderBy('reservations_count', 'desc')
            ->get();

        return ActiveUserReportResource::collection($activeUsers);
    }
}