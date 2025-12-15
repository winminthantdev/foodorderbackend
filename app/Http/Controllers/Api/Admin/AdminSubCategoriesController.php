<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SubCategoriesResource;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminSubCategoriesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/subcategories",
     *     summary="Get all subcategories",
     *     tags={"SubCategories (for admin)"},
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

    /**
     * @OA\Post(
     *     path="/v1/subcategories",
     *     summary="Create new status",
     *     tags={"SubCategories"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "category_id", "status_id"},
     *             @OA\Property(property="name", type="string", example="Nodels"),
     *             @OA\Property(property="category_id", type= "integer", example= 1),
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
            'name' => 'required|string|max:255|unique:subcategories,name',
            'category_id' => 'required|integer|exists:categories,id',
            'status_id' => 'required|integer|exists:statuses,id',
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
            // Create SubCategory
            $status = SubCategory::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'category_id' => $request->category_id,
                'status_id' => $request->status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SubCategory created successfully',
                'data' => new SubCategoriesResource($status),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/subcategories/{id}",
     *     summary="Update a status",
     *     tags={"SubCategories"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="SubCategory ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name"},
     *
     *             @OA\Property(property="name", type="string", example="Updated SubCategory Name")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="SubCategory updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SubCategory updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Active"),
     *                 @OA\Property(property="slug", type="string", example="active")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="SubCategory not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="SubCategory not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to update status"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE error details")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Find status
        $status = SubCategory::find($id);

        if (! $status) {
            return response()->json([
                'success' => false,
                'message' => 'SubCategory not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:subcategories,name,'.$id,
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
            $status->name = $request->name;
            $status->slug = Str::slug($request->name);
            $status->save();

            return response()->json([
                'success' => true,
                'message' => 'SubCategory updated successfully',
                'data' => new SubCategoriesResource($status),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/subcategories/{id}",
     *     summary="Delete a status",
     *     tags={"SubCategories"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="SubCategory ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="SubCategory deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SubCategory deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="SubCategory not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="SubCategory not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to delete status"),
     *             @OA\Property(property="error", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find SubCategory
        $status = SubCategory::find($id);

        // If not found → 404
        if (! $status) {
            return response()->json([
                'success' => false,
                'message' => 'SubCategory not found',
            ], 404);
        }

        try {
            // Delete record
            $status->delete();

            return response()->json([
                'success' => true,
                'message' => 'SubCategory deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete status',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
