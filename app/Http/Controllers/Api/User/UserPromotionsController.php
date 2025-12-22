<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PromotionsResource;
use App\Models\Promotion;
use Illuminate\Http\Request;

class UserPromotionsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/user/promotions",
     *     summary="Get all promotions",
     *     tags={"Promotions (User)"},
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
     *         description="Filter by promotion id",
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
     *         description="List of promotions"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Promotion::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by promotion type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $promotions = $query->paginate($perPage);

        return response()->json([
            'data' => PromotionsResource::collection($promotions),
            'meta' => [
                'current_page' => $promotions->currentPage(),
                'total_page' => $promotions->lastPage(),
                'per_page' => $promotions->perPage(),
                'total' => $promotions->total(),
            ],
        ], 200);
    }
}
