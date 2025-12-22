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
     *             required={"user_id", "ordertype_id", "paymenttype_id", "stage_id", "subtotal", "discount", "delivery_fee", "service_fee", "total", "is_paid"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="ordertype_id", type="integer", example=1),
     *             @OA\Property(property="paymenttype_id", type="integer", example=1),
     *             @OA\Property(property="driver_id", type="integer", example=1),
     *             @OA\Property(property="stage_id", type="integer", example=1),
     *             @OA\Property(property="address_id", type="integer", example=1),
     *             @OA\Property(property="subtotal", type="number", format="float", example=50.00),
     *             @OA\Property(property="discount", type="number", format="float", example=5.00),
     *             @OA\Property(property="delivery_fee", type="number", format="float", example=3.00),
     *             @OA\Property(property="service_fee", type="number", format="float", example=2.00),
     *             @OA\Property(property="total", type="number", format="float", example=50.00),
     *             @OA\Property(property="transaction_id", type="string", example="TX123456789"),
     *             @OA\Property(property="is_paid", type="boolean", example=true),
     *             @OA\Property(property="order_note", type="string", example="Please deliver between 5-6 PM"),
     *             @OA\Property(property="scheduled_at", type="string", format="date-time", example="2025-12-15T17:00:00Z"),
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
        // $userId = auth()->id();
        $userId = 2;

        $validator = Validator::make($request->all(), [
            'ordertype_id' => 'required|exists:ordertypes,id',
            'paymenttype_id' => 'required|exists:paymenttypes,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'stage_id' => 'required|exists:stages,id',
            'address_id' => 'nullable|exists:addresses,id',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'delivery_fee' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'transaction_id' => 'nullable|string',
            'is_paid' => 'required|boolean',
            'order_note' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::create([
            'user_id'=> $userId,
            "ordertype_id"=>  $request->ordertype_id,
            "paymenttype_id"=>  $request->paymenttype_id,
            "driver_id"=>  $request->driver_id,
            "stage_id"=>  $request->stage_id,
            "address_id"=>  $request->address_id,
            "subtotal"=>  $request->subtotal,
            "discount"=>  $request->discount,
            "delivery_fee"=>  $request->delivery_fee,
            "service_fee"=>  $request->service_fee,
            "total"=>  $request->total,
            "transaction_id"=>  $request->transaction_id,
            "is_paid"=>  $request->is_paid,
            "order_note" => $request->order_note,
            "scheduled_at" => $request->scheduled_at,
        ]);

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
     * @OA\Patch(
     *     path="/api/admin/orders/{id}/stage",
     *     tags={"Orders (Admin)"},
     *     summary="Update order",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="order id to update stage",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="stage_id",
     *         in="path",
     *         required=true,
     *         description="update order stage id",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully"
     *     )
     * )
     */
    public function stage( Request $request, Order $order)
    {
        if (!$order) {
            return response()->json(['success'=>false,'message'=>'Order not found'],404);
        }

        $validator = Validator::make($request->all(), [
            'stage_id' => 'required|exists:stages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        try {
            $order->update([
                'stage_id'=> $request->stage_id
            ]);

            return response()->json([
                'success'=> true,
                'messange'=> 'Order State Successfully.'
            ]);

        }catch (\Exception $e) {
            return response()->json([
                'success'=> false,
                'message'=> 'Failed to update order',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

}
