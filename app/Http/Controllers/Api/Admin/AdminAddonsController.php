<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AddonsResource;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminAddonsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/addons",
     *     summary="Get all addons",
     *     tags={"Addons (Admin)"},
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
     *         description="Filter by addon id",
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
     *         description="List of addons"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Addon::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by addon type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $addons = $query->paginate($perPage);

        return response()->json([
            'data' => AddonsResource::collection($addons),
            'meta' => [
                'current_page' => $addons->currentPage(),
                'total_page' => $addons->lastPage(),
                'per_page' => $addons->perPage(),
                'total' => $addons->total(),
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/admin/addons",
     *     summary="Create new addon",
     *     tags={"Addons (Admin)"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","price","status_id"},
     *
     *             @OA\Property(property="name", type="string", example="Soup"),
     *             @OA\Property(property="price", type="string", example="500"),
     *             @OA\Property(property="status_id", type= "integer", example= 3)
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
            'name' => 'required|string|max:255|unique:addons,name',
            'price' => 'required|numeric|min:0',
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
            // Create Addon
            $addon = Addon::create([
                'name' => $request->name,
                'price' => $request->price,
                'status_id' => $request->status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Addon created successfully',
                'data' => new AddonsResource($addon),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create addon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/addons/{id}",
     *     summary="Update a addon",
     *     tags={"Addons (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Addon ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "price", "status_id"},
     *
     *             @OA\Property(property="name", type="string", example="Updated Addon Name")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Addon updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Addon updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Active"),
     *                 @OA\Property(property="price", type="integer", example="500"),
     *                 @OA\Property(
     *                    property="status",
     *                    type="object",
     *                    @OA\Property(property="id", type="integer", example=3),
     *                    @OA\Property(property="name", type="string", example="On"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Addon not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Addon not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to update addon"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE error details")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Find addon
        $addon = Addon::find($id);

        if (! $addon) {
            return response()->json([
                'success' => false,
                'message' => 'Addon not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:addons,name,'.$id,
            'price' => 'required|numeric|min:0',
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
            $addon->name = $request->name;
            $addon->price = $request->price;
            $addon->status_id = $request->status_id;
            $addon->save();

            return response()->json([
                'success' => true,
                'message' => 'Addon updated successfully',
                'data' => new AddonsResource($addon),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update addon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/addons/{id}",
     *     summary="Delete a addon",
     *     tags={"Addons (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Addon ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Addon deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Addon deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Addon not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Addon not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to delete addon"),
     *             @OA\Property(property="error", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find Addon
        $addon = Addon::find($id);

        // If not found → 404
        if (! $addon) {
            return response()->json([
                'success' => false,
                'message' => 'Addon not found',
            ], 404);
        }

        try {
            // Delete record
            $addon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Addon deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete addon',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
