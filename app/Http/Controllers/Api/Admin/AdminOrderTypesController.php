<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderTypesResource;
use App\Models\OrderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminOrderTypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/ordertypes",
     *     summary="Get all ordertypes",
     *     tags={"OrderTypes"},
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
     *         description="Filter by paymenttype id",
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

    /**
     * @OA\Post(
     *     path="/v1/ordertypes",
     *     summary="Create new paymenttype",
     *     tags={"OrderTypes"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","icon","status_id"},
     *             @OA\Property(property="name", type="string", example="Kpay"),
     *             @OA\Property(property="icon_path", type="string", example="/images/icons/kpay.png"),
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
            'name' => 'required|string|max:255|unique:ordertypes,name',
            'description' => 'nullable|string|max:1000',
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
            // Create OrderType
            $paymenttype = OrderType::create([
                'name' => $request->name,
                'description' => $request->description,
                'status_id' => $request->status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OrderType created successfully',
                'data' => new OrderTypesResource($paymenttype),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create paymenttype',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/ordertypes/{id}",
     *     summary="Update a paymenttype",
     *     tags={"OrderTypes"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="OrderType ID",
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
     *             @OA\Property(property="name", type="string", example="Updated OrderType Name")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OrderType updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OrderType updated successfully"),
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
     *         description="OrderType not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="OrderType not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to update paymenttype"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE error details")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Find paymenttype
        $paymenttype = OrderType::find($id);

        if (! $paymenttype) {
            return response()->json([
                'success' => false,
                'message' => 'OrderType not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ordertypes,name,'.$id,
            'icon_path' => 'nullable|string|max:255',
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
            $paymenttype->name = $request->name;
            $paymenttype->slug = Str::slug($request->name);
            $paymenttype->icon = $request->icon_path;
            $paymenttype->status_id = $request->status_id;
            $paymenttype->save();

            return response()->json([
                'success' => true,
                'message' => 'OrderType updated successfully',
                'data' => new OrderTypesResource($paymenttype),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update paymenttype',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/ordertypes/{id}",
     *     summary="Delete a paymenttype",
     *     tags={"OrderTypes"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="OrderType ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OrderType deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OrderType deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="OrderType not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="OrderType not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to delete paymenttype"),
     *             @OA\Property(property="error", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find OrderType
        $paymenttype = OrderType::find($id);

        // If not found → 404
        if (! $paymenttype) {
            return response()->json([
                'success' => false,
                'message' => 'OrderType not found',
            ], 404);
        }

        try {
            // Delete record
            $paymenttype->delete();

            return response()->json([
                'success' => true,
                'message' => 'OrderType deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete paymenttype',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
