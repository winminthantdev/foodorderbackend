<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PromotionsResource;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPromotionsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/promotions",
     *     summary="Get all promotions",
     *     tags={"Promotions (Admin)"},
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

    /**
     * @OA\Post(
     *     path="/v1/admin/promotions",
     *     summary="Create new promotion",
     *     tags={"Promotions (Admin)"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"title","code","description","type","value", "max_discount", "min_order_amount","start_date","end_date","status_id"},
     *
     *             @OA\Property(property="title", type="string", example="Summer Sale"),
     *             @OA\Property(property="code", type="string", example="SUMMER2025"),
     *            @OA\Property(property="description", type="string", example="Get 20% off on all orders during summer!"),
     *            @OA\Property(property="type", type="string", example="percentage"),
     *            @OA\Property(property="value", type="number", format="float", example=20.5),
     *            @OA\Property(property="max_discount", type="number", format="float", example=50.0),
     *            @OA\Property(property="min_order_amount", type="number", format="float", example=100.0),
     *           @OA\Property(property="start_date", type="string", format="date", example="2025-06-01"),
     *           @OA\Property(property="end_date", type="string", format="date", example="2025-06-30"),
     *           @OA\Property(property="status_id", type="integer", example=1)
     *
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
            'title' => 'required|string|max:255|unique:promotions,title',
            'code' => 'required|string|unique:promotions,code|max:50',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status_id' => 'required|in:1,2',
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
            // Create Promotion
            $promotion = Promotion::create([
                'title' => $request->title,
                'code' => $request->code,
                'description' => $request->description,
                'type' => $request->type,
                'value' => $request->value,
                'max_discount' => $request->max_discount,
                'min_order_amount' => $request->min_order_amount,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status_id' => $request->status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Promotion created successfully',
                'data' => new PromotionsResource($promotion),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create promotion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/admin/promotions/{id}",
     *     summary="Update a promotion",
     *     tags={"Promotions (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Promotion ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"title","code","description","type","value", "max_discount", "min_order_amount","start_date","end_date","status_id"},
     *            @OA\Property(property="title", type="string", example="New Year Sale"),
     *            @OA\Property(property="code", type="string", example="NEWYEAR2024"),
     *           @OA\Property(property="description", type="string", example="Celebrate the new year with amazing discounts!"),
     *           @OA\Property(property="type", type="string", example="percentage"),
     *          @OA\Property(property="value", type="number", format="float", example=15.0),
     *          @OA\Property(property="max_discount", type="number", format="float", example=30.0),
     *         @OA\Property(property="min_order_amount", type="number", format="float", example=50.0),
     *        @OA\Property(property="start_date", type="string", format="date", example="2024-01-01"),
     *       @OA\Property(property="end_date", type="string", format="date", example="2024-01-10"),
     *      @OA\Property(property="status_id", type="integer", example=3)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Promotion updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="New Year Sale"),
     *                 @OA\Property(property="code", type="string", example="NEWYEAR2024"),
     *                 @OA\Property(property="description", type="string", example="Celebrate the new year with amazing discounts!"),
     *                 @OA\Property(property="type", type="string", example="percentage"),
     *                 @OA\Property(property="value", type="number", format="float", example=15.0),
     *                 @OA\Property(property="max_discount", type="number", format="float", example=30.0),
     *                 @OA\Property(property="min_order_amount", type="number", format="float", example=50.0),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2024-01-01"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2024-01-10"),
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
     *         description="Promotion not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Promotion not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to update promotion"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE error details")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Find promotion
        $promotion = Promotion::find($id);

        if (! $promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:promotions,title,' . $promotion->id,
            'code' => 'required|string|max:50|unique:promotions,code,' . $promotion->id,
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
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
            $promotion->title = $request->title;
            $promotion->code = $request->code;
            $promotion->description = $request->description;
            $promotion->type = $request->type;
            $promotion->value = $request->value;
            $promotion->max_discount = $request->max_discount;
            $promotion->min_order_amount = $request->min_order_amount;
            $promotion->start_date = $request->start_date;
            $promotion->end_date = $request->end_date;
            $promotion->status_id = $request->status_id;
            $promotion->save();

            return response()->json([
                'success' => true,
                'message' => 'Promotion updated successfully',
                'data' => new PromotionsResource($promotion),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update promotion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/promotions/{id}",
     *     summary="Delete a promotion",
     *     tags={"Promotions (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Promotion ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Promotion deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Promotion deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Promotion not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Promotion not found")
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
     *             @OA\Property(property="message", type="string", example="Failed to delete promotion"),
     *             @OA\Property(property="error", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find Promotion
        $promotion = Promotion::find($id);

        // If not found → 404
        if (! $promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found',
            ], 404);
        }

        try {
            // Delete record
            $promotion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Promotion deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete promotion',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
