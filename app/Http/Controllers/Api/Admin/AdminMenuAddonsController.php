<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MenuAddonsResource;
use App\Models\MenuAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminMenuAddonsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/menu-addons",
     *     summary="Get all menuaddons",
     *     tags={"MenuAddons (Admin)"},
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
     *         description="Filter by menuaddons id",
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
     *         description="List of menuaddons"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = MenuAddon::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by menuaddons type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $menuaddons = $query->paginate($perPage);

        return response()->json([
            'data' => MenuAddonsResource::collection($menuaddons),
            'meta' => [
                'current_page' => $menuaddons->currentPage(),
                'total_page' => $menuaddons->lastPage(),
                'per_page' => $menuaddons->perPage(),
                'total' => $menuaddons->total(),
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/admin/menu-addons",
     *     summary="Create new menuaddons",
     *     tags={"MenuAddons (Admin)"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"menu_id", "addon_id", "max_quantity"},
     *            @OA\Property(property="menu_id", type="integer", example=1),
     *            @OA\Property(property="addon_id", type="integer", example=2),
     *            @OA\Property(property="max_quantity", type="integer", example=5),
     *           @OA\Property(property="custom_price", type="number", format="float", example=1.50)
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
            "addon_id" => "required|integer|exists:addons,id",
            "max_quantity" => "required|integer|min:1",
            "custom_price" => "nullable|numeric|min:0",
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
            // Create MenuAddon
            $menuaddons = MenuAddon::create([
                'menu_id' => $request->menu_id,
                'addon_id' => $request->addon_id,
                'max_quantity' => $request->max_quantity,
                'custom_price' => $request->custom_price,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'MenuAddon created successfully',
                'data' => new MenuAddonsResource($menuaddons),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create menuaddons',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/admin/menu-addons/{id}",
     *     summary="Update a menuaddons",
     *     tags={"MenuAddons (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="MenuAddon ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"menu_id", "addon_id", "max_quantity"},
     *
     *             @OA\Property(property="menu_id", type="integer", example=1),
     *             @OA\Property(property="addon_id", type="integer", example=2),
     *             @OA\Property(property="max_quantity", type="integer", example=5),
     *             @OA\Property(property="custom_price", type="number", format="float", example=1.50)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="MenuAddon updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="MenuAddon updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="menu_id", type="integer", example=1),
     *                 @OA\Property(property="addon_id", type="integer", example=2),
     *                 @OA\Property(property="max_quantity", type="integer", example=5),
     *                 @OA\Property(property="custom_price", type="number", format="float", example=1.50)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="MenuAddon not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="MenuAddon not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to update menuaddons"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE error details")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Find menuaddons
        $menuaddons = MenuAddon::find($id);

        if (! $menuaddons) {
            return response()->json([
                'success' => false,
                'message' => 'MenuAddon not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'menu_id' => 'required|integer|exists:menus,id',
            'addon_id' => 'required|integer|exists:addons,id',
            'max_quantity' => 'required|integer|min:1',
            'custom_price' => 'nullable|numeric|min:0',
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
            $menuaddons->menu_id = $request->menu_id;
            $menuaddons->addon_id = $request->addon_id;
            $menuaddons->max_quantity = $request->max_quantity;
            $menuaddons->custom_price = $request->custom_price;
            $menuaddons->save();

            return response()->json([
                'success' => true,
                'message' => 'MenuAddon updated successfully',
                'data' => new MenuAddonsResource($menuaddons),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menuaddons',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/menu-addons/{id}",
     *     summary="Delete a menuaddons",
     *     tags={"MenuAddons (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="MenuAddon ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="MenuAddon deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="MenuAddon deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="MenuAddon not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="MenuAddon not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to delete menuaddons"),
     *             @OA\Property(property="error", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find MenuAddon
        $menuaddons = MenuAddon::find($id);

        // If not found → 404
        if (! $menuaddons) {
            return response()->json([
                'success' => false,
                'message' => 'MenuAddon not found',
            ], 404);
        }

        try {
            // Delete record
            $menuaddons->delete();

            return response()->json([
                'success' => true,
                'message' => 'MenuAddon deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete menuaddons',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
