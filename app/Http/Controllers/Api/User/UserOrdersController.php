<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\User\OrdersResource;
use App\Models\Order;
use Illuminate\Http\Request;

class UserOrdersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/user/orders",
     *     summary="Get all orders",
     *     tags={"Orders (User)"},
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
        $userId = auth()->id();

        $query = Order::where('user_id', $userId);

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
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
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/user/orders",
     *     summary="Create new orders",
     *     tags={"Orders (User)"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"user_id", "ordertype_id", "paymenttype_id", "stage_id", "subtotal", "discount", "delivery_fee", "service_fee", "total", "is_paid"},
     *
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="ordertype_id", type="integer", example=1),
     *             @OA\Property(property="paymenttype_id", type="integer", example=1),
     *             @OA\Property(property="driver_id", type="integer", example=1),
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
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = \DB::transaction(function () use ($request) {
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'ordertype_id' => $request->ordertype_id,
                    'paymenttype_id' => $request->paymenttype_id,
                    'driver_id' => $request->driver_id,
                    'address_id' => $request->address_id,
                    'subtotal' => $request->subtotal,
                    'discount' => $request->discount,
                    'delivery_fee' => $request->delivery_fee,
                    'service_fee' => $request->service_fee,
                    'total' => $request->total,
                    'transaction_id' => $request->transaction_id,
                    'order_note' => $request->order_note,
                    'scheduled_at' => $request->scheduled_at,
                    'stage_id'       => 1,
                ]);

                foreach ($request->items as $item) {
                    $menu = \App\Models\Menu::findOrFail($item['menu_id']);

                    if (!$menu) {
                        throw new \Exception("Menu item with ID {$item['menu_id']} not found.");
                    }

                    $order->items()->create([
                        'menu_id'  => $item['menu_id'],
                        'quantity' => $item['quantity'],
                        'price'    => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                    ]);
                }

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => new OrdersResource($order->load('items')),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while processing your order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user/orders/{id}",
     *     tags={"Orders (User)"},
     *     summary="Get order by ID",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully"
     *     )
     * )
     */
    public function show($id)
    {
        $order = Order::find($id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['data' => new OrdersResource($order)]);
    }
}
