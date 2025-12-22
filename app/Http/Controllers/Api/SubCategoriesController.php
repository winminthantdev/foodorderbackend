<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\SubCategoriesResource;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoriesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/subcategories",
     *     summary="Get all subcategories",
     *     tags={"SubCategories (Public)"},
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
     *    @OA\Parameter(
     *          name="status_id",
     *          in="query",
     *          description="Filter by status id",
     *          required=false,
     *
     *         @OA\Schema(type="integer")
     *
     *    ),
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
     *         description="List of subcategories"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = SubCategory::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $subcategories = $query->paginate($perPage);

        return response()->json([
            'data' => SubCategoriesResource::collection($subcategories),
            'meta' => [
                'current_page' => $subcategories->currentPage(),
                'total_page' => $subcategories->lastPage(),
                'per_page' => $subcategories->perPage(),
                'total' => $subcategories->total(),
            ],
        ], 200);
    }
}
