<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\VariantsResource;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminVariantsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/variants",
     *     summary="Get all variants",
     *     tags={"Variants (Admin)"},
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
     *         description="Filter by variant status",
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
     *         description="List of variants"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Variant::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by variant type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $variants = $query->paginate($perPage);

        return response()->json([
            'data' => VariantsResource::collection($variants),
            'meta' => [
                'current_page' => $variants->currentPage(),
                'total_page' => $variants->lastPage(),
                'per_page' => $variants->perPage(),
                'total' => $variants->total(),
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/admin/variants",
     *     summary="Create new variant",
     *     tags={"Variants (Admin)"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"menu_id","name"},
     *             @OA\Property(property="menu_id", type="integer", example=1),
     *            @OA\Property(property="name", type="string", example="Extra Cheese"),
     *            @OA\Property(property="extra_price", type="number", format="float", example=500.00),
     *            @OA\Property(property="status_id", type="integer", example=3),
     *         )
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
            'menu_id' => 'required|exists:menus,id',
            'name' => 'required|string|max:255',
            'extra_price' => 'required|numeric|min:0',
            'status_id' => 'required|in:3,4',
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
            // Create Variant
            $variant = Variant::create([
                'mnenu_id' => $request->menu_id,
                'name' => $request->name,
                'extra_price' => $request->extra_price,
                'status_id' => $request->status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Variant created successfully',
                'data' => new VariantsResource($variant),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create variant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/admin/variants/{id}",
     *     summary="Update a variant",
     *     tags={"Variants (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Variant ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"menu_id","name","extra_price","status_id"},
     *             @OA\Property(property="menu_id", type="integer", example=1),
     *            @OA\Property(property="name", type="string", example="Extra Cheese"),
     *            @OA\Property(property="extra_price", type="number", format="float", example=500.00),
     *            @OA\Property(property="status_id", type="integer", example=3),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Variant updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Variant updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="menu_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Extra Cheese"),
     *                 @OA\Property(property="extra_price", type="number", format="float", example=500.00),
     *                 @OA\Property(
     *                    property="status",
     *                    type="object",
     *                    @OA\Property(property="id", type="integer", example=3),
     *                    @OA\Property(property="name", type="string", example="On"))
     *                 ),
     *                @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-25T10:00:00Z"),
     *                @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-26T12:00:00Z"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Variant not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Variant not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to update variant"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE error details")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Find variant
        $variant = Variant::find($id);

        if (! $variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'menu_id' => 'required|exists:menus,id',
            'name' => 'required|string|max:255',
            'extra_price' => 'required|numeric|min:0',
            'status_id' => 'required|in:3,4',
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
            $variant->menu_id = $request->menu_id;
            $variant->name = $request->name;
            $variant->extra_price = $request->extra_price;
            $variant->status_id = $request->status_id;
            $variant->save();

            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully',
                'data' => new VariantsResource($variant),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update variant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/variants/{id}",
     *     summary="Delete a variant",
     *     tags={"Variants (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Variant ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Variant deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Variant deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Variant not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Variant not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to delete variant"),
     *             @OA\Property(property="error", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find Variant
        $variant = Variant::find($id);

        // If not found → 404
        if (! $variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found',
            ], 404);
        }

        try {
            // Delete record
            $variant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete variant',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
