<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\OrderTypesResource;
use App\Models\OrderType;
use Illuminate\Http\Request;

class UserOrderTypesController extends Controller
{

        /**
     * @OA\Get(
     *     path="/v1/user/ordertypes",
     *     summary="Get all ordertypes",
     *     tags={"OrderTypes (User)"},
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
     *         description="Filter by status id",
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
     *         description="List of ordertypes"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = OrderType::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by paymenttype type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $ordertypes = $query->paginate($perPage);

        return response()->json([
            'data' => OrderTypesResource::collection($ordertypes),
            'meta' => [
                'current_page' => $ordertypes->currentPage(),
                'total_page' => $ordertypes->lastPage(),
                'per_page' => $ordertypes->perPage(),
                'total' => $ordertypes->total(),
            ],
        ], 200);
    }

}
