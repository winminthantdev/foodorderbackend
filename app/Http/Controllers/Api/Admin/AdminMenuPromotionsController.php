<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MenuPromotionsResource;
use App\Models\MenuPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminMenuPromotionsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/menu-promotions",
     *     summary="Get all menupromotions",
     *     tags={"MenuPromotions (Admin)"},
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
     *         description="Filter by menupromotions id",
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
     *         description="List of menupromotions"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = MenuPromotion::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by menupromotions type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $menupromotions = $query->paginate($perPage);

        return response()->json([
            'data' => MenuPromotionsResource::collection($menupromotions),
            'meta' => [
                'current_page' => $menupromotions->currentPage(),
                'total_page' => $menupromotions->lastPage(),
                'per_page' => $menupromotions->perPage(),
                'total' => $menupromotions->total(),
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/admin/menu-promotions",
     *     summary="Create new menupromotions",
     *     tags={"MenuPromotions (Admin)"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"menu_id", "promotion_id", "custom_discount_value"},
     *            @OA\Property(property="menu_id", type="integer", example=1),
     *            @OA\Property(property="promotion_id", type="integer", example=2),
     *            @OA\Property(property="custom_discount_value", type="number", format="float", example=10.5)
     *        )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Created"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */


    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            "menu_id" => "required|integer|exists:menus,id",
            "promotion_id" => "required|integer|exists:promotions,id",
            "custom_discount_value" => "required|numeric|min:0",
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create MenuPromotion
            $menupromotions = MenuPromotion::create([
                'menu_id' => $request->menu_id,
                'promotion_id' => $request->promotion_id,
                'custom_discount_value' => $request->custom_discount_value,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'MenuPromotion created successfully',
                'data' => new MenuPromotionsResource($menupromotions),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create menupromotions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/admin/menu-promotions/{id}",
     *     summary="Update a menupromotions",
     *     tags={"MenuPromotions (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="MenuPromotion ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"menu_id", "promotion_id", "custom_discount_value"},
     *           @OA\Property(property="menu_id", type="integer", example=1),
     *           @OA\Property(property="promotion_id", type="integer", example=2),
     *           @OA\Property(property="custom_discount_value", type="number", format="float", example=10.5)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="MenuPromotion updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="MenuPromotion updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="menu_id", type="integer", example=1),
     *                 @OA\Property(property="promotion_id", type="integer", example=2),
     *                @OA\Property(property="custom_discount_value", type="number", format="float", example=10.5),
     *                @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-02T12:00:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="MenuPromotion not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="MenuPromotion not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="The name has already been taken."))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update menupromotions"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE error details")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Find menupromotions
        $menupromotions = MenuPromotion::find($id);

        if (! $menupromotions) {
            return response()->json([
                'success' => false,
                'message' => 'MenuPromotion not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'menu_id' => 'required|integer|exists:menus,id',
            'promotion_id' => 'required|integer|exists:promotions,id',
            'custom_discount_value' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Update data
            $menupromotions->menu_id = $request->menu_id;
            $menupromotions->promotion_id = $request->promotion_id;
            $menupromotions->custom_discount_value = $request->custom_discount_value;
            $menupromotions->save();

            return response()->json([
                'success' => true,
                'message' => 'MenuPromotion updated successfully',
                'data' => new MenuPromotionsResource($menupromotions),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menupromotions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/menu-promotions/{id}",
     *     summary="Delete a menupromotions",
     *     tags={"MenuPromotions (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="MenuPromotion ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="MenuPromotion deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="MenuPromotion deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="MenuPromotion not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="MenuPromotion not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete menupromotions"),
     *             @OA\Property(property="error", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find MenuPromotion
        $menupromotions = MenuPromotion::find($id);

        // If not found → 404
        if (! $menupromotions) {
            return response()->json([
                'success' => false,
                'message' => 'MenuPromotion not found',
            ], 404);
        }

        try {
            // Delete record
            $menupromotions->delete();

            return response()->json([
                'success' => true,
                'message' => 'MenuPromotion deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete menupromotions',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
