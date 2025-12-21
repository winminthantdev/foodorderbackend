<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DriversResource;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminDriversController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/drivers",
     *     summary="Get all drivers",
     *     tags={"Drivers (Admin)"},
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
     *         description="Filter by drivers id",
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
     *         description="List of drivers"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Driver::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by drivers type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $drivers = $query->paginate($perPage);

        return response()->json([
            'data' => DriversResource::collection($drivers),
            'meta' => [
                'current_page' => $drivers->currentPage(),
                'total_page' => $drivers->lastPage(),
                'per_page' => $drivers->perPage(),
                'total' => $drivers->total(),
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/admin/drivers",
     *     summary="Create new drivers",
     *     tags={"Drivers (Admin)"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "phone_number", "status_id", "rating"},
     *
     *            @OA\Property(property="name", type="string", example="Driver Name"),
     *            @OA\Property(property="phone_number", type="string", example="+1234567890"),
     *            @OA\Property(property="status_id", type="integer", example=3),
     *            @OA\Property(property="latitude", type="number", format="float", example=37.7749),
     *            @OA\Property(property="longitude", type="number", format="float", example=-122.4194),
     *            @OA\Property(property="rating", type="number", format="float", example=4.5)
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
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:drivers,phone_number',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'rating' => 'required|numeric|between:0,5',
            'status_id' => 'nullable|in:6,7,8',
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
            // Create Driver
            $drivers = Driver::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'rating' => $request->rating,
                'status_id' => $request->status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Driver created successfully',
                'data' => new DriversResource($drivers),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create drivers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/admin/drivers/{id}",
     *     summary="Update a drivers",
     *     tags={"Drivers (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Driver ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "phone_number", "status_id", "rating"},
     *
     *            @OA\Property(property="name", type="string", example="Driver Name"),
     *            @OA\Property(property="phone_number", type="string", example="+1234567890"),
     *            @OA\Property(property="status_id", type="integer", example=3),
     *            @OA\Property(property="latitude", type="number", format="float", example=37.7749),
     *            @OA\Property(property="longitude", type="number", format="float", example=-122.4194),
     *            @OA\Property(property="rating", type="number", format="float", example=4.5)
     *        )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Driver updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Driver updated successfully"),
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
     *         description="Driver not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Driver not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to update drivers"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE error details")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Find drivers
        $drivers = Driver::find($id);

        if (! $drivers) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:drivers,phone_number,' .$drivers->id,
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'rating' => 'required|numeric|between:0,5',
            'status_id' => 'nullable|in:6,7,8',
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
            $drivers->name = $request->name;
            $drivers->phone_number = $request->phone_number;
            $drivers->latitude = $request->latitude;
            $drivers->longitude = $request->longitude;
            $drivers->rating = $request->rating;
            $drivers->status_id = $request->status_id;
            $drivers->save();

            return response()->json([
                'success' => true,
                'message' => 'Driver updated successfully',
                'data' => new DriversResource($drivers),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update drivers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/drivers/{id}",
     *     summary="Delete a drivers",
     *     tags={"Drivers (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Driver ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Driver deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Driver deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Driver not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to delete drivers"),
     *             @OA\Property(property="error", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find Driver
        $drivers = Driver::find($id);

        // If not found → 404
        if (! $drivers) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found',
            ], 404);
        }

        try {
            // Delete record
            $drivers->delete();

            return response()->json([
                'success' => true,
                'message' => 'Driver deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete drivers',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
