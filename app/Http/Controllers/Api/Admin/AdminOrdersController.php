<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrdersResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class AdminOrdersController extends Controller
{

    /**
     * @OA\Get(
     *     path="/v1/admin/orders",
     *     summary="Get all orders",
     *     tags={"Orders (Admin)"},
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
     *         description="Filter by orders id",
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
     *         description="List of orders"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request){
                $q->where('name', 'like', '%'.$request->search.'%');
            })->orWhere('id', $request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $orders = $query->paginate($perPage);

        return response()->json([
            'data' => OrdersResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'total_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }


       /**
     * @OA\Post(
     *     path="/v1/admin/orders",
     *     summary="Create new orders",
     *     tags={"Orders (Admin)"},
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => new OrdersResource($order)
        ], 201);
    }

     /**
     * @OA\Get(
     *     path="/api/admin/orders/{id}",
     *     tags={"Orders (Admin)"},
     *     summary="Get order by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully"
     *     )
     * )
     */
    public function show($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success'=>false,'message'=>'Order not found'],404);
        }
        return response()->json(['data'=>new OrdersResource($order)]);
    }


    /**
     * @OA\Put(
     *     path="/api/admin/orders/{id}",
     *     tags={"Orders (Admin)"},
     *     summary="Update order",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success'=>false,'message'=>'Order not found'],404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        $order->update($request->only('status'));

        return response()->json(['success'=>true,'message'=>'Order updated successfully','data'=>new OrdersResource($order)]);
    }

   /**
     * @OA\Delete(
     *     path="/api/admin/orders/{id}",
     *     tags={"Orders (Admin)"},
     *     summary="Delete order",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Order deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success'=>false,'message'=>'Order not found'],404);
        }

        $order->delete();
        return response()->json(null, 204);
    }
}
