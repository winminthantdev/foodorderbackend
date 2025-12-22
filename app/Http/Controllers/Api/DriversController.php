<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\DriversResource;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriversController extends Controller
{
     /**
     * @OA\Get(
     *     path="/v1/drivers",
     *     summary="Get all drivers",
     *     tags={"Drivers (Public)"},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="status_id",
     *         in="query",
     *         description="Filter by drivers id",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of drivers"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Driver::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by drivers type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $drivers = $query->paginate($perPage);

        return response()->json([
            'data' => DriversResource::collection($drivers),
            'meta' => [
                'current_page' => $drivers->currentPage(),
                'total_page' => $drivers->lastPage(),
                'per_page' => $drivers->perPage(),
                'total' => $drivers->total(),
            ],
        ], 200);
    }

}
