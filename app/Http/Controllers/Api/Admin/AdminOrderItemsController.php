<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\OrdersResource;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminOrderItemsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/order-items",
     *     summary="Get all order items",
     *     tags={"Order Items (Admin)"},
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         description="Filter by order ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of order items"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = OrderItem::with(['order','menu']);

        if ($request->order_id) {
            $query->where('order_id', $request->order_id);
        }

        $orderItems = $query->latest()->paginate($request->per_page ?? 10);

         return response()->json([
            'data' => OrdersResource::collection($orderItems),
            'meta' => [
                'current_page' => $orderItems->currentPage(),
                'total_page' => $orderItems->lastPage(),
                'per_page' => $orderItems->perPage(),
                'total' => $orderItems->total(),
            ],
        ]);

    }

    /**
     * @OA\Get(
     *     path="/v1/admin/order-items/{id}",
     *     summary="Get order item detail",
     *     tags={"Order Items (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Order item detail"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(string $id)
    {
        $item = OrderItem::with(['order','menu','addons'])->find($id);

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found'
            ], 404);
        }

        return response()->json([
            'data' => new OrdersResource($item),
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/v1/admin/order-items/{id}",
     *     summary="Update order item status",
     *     tags={"Order Items (Admin)"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status_id"},
     *             @OA\Property(property="status_id", type="integer", example=4)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(Request $request, string $id)
    {
        $item = OrderItem::find($id);

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|in:3,4,5' // example: prepared, canceled, refunded
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $item->status_id = $request->status_id;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Order item updated successfully'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/order-items/{id}",
     *     summary="Delete order item",
     *     tags={"Order Items (Admin)"},
     *
     *     @OA\Response(response=200, description="Deleted"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(string $id)
    {
        $item = OrderItem::find($id);

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order item deleted'
        ]);
    }
}
