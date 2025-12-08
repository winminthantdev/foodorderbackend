<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatusesResource;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StatusesController extends Controller
{

    /**
 * @OA\Get(
 *     path="/api/v1/statuses",
 *     summary="Get all statuses",
 *     tags={"Statuses"},
 *     @OA\Response(
 *         response=200,
 *         description="List of statuses"
 *     )
 * )
 */
    public function index(Request $request)
    {
        $query = Status::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Filter example: by status type
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $statuses = $query->paginate($perPage);

        return StatusesResource::collection($statuses)
            ->additional([
                'meta' => [
                    'current_page' => $statuses->currentPage(),
                    'last_page' => $statuses->lastPage(),
                    'per_page' => $statuses->perPage(),
                    'total' => $statuses->total(),
                ],
            ]);
    }


    /**
 * @OA\Post(
 *     path="api/v1/statuses",
 *     summary="Create new status",
 *     tags={"Statuses"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Pending")
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
            'name' => 'required|string|max:255|unique:statuses,name',
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
            // Create Status
            $status = Status::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status created successfully',
                'data' => new StatusesResource($status),
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find status
        $status = Status::find($id);

        if (! $status) {
            return response()->json([
                'success' => false,
                'message' => 'Status not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:statuses,name,'.$id,
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
                'message' => 'Status updated successfully',
                'data' => new StatusesResource($status),
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find Status
        $status = Status::find($id);

        // If not found → 404
        if (! $status) {
            return response()->json([
                'success' => false,
                'message' => 'Status not found',
            ], 404);
        }

        try {
            // Delete record
            $status->delete();

            return response()->json([
                'success' => true,
                'message' => 'Status deleted successfully',
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
