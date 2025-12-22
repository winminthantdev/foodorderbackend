<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PaymentsResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPaymentsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/user/payments",
     *     summary="Store a new payment",
     *     description="Processes a payment for a specific order and records it in the database.",
     *     tags={"Payments (User)"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "paymenttype_id", "amount"},
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="paymenttype_id", type="integer", example=2),
     *             @OA\Property(property="amount", type="number", example=99.99),
     *             @OA\Property(property="transaction_id", type="string", example="TXN_123456789")
     *         )
     *     ),
     *
     *     @OA\Response(
     *      response=201,
     *      description="Payment created successfully",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="id", type="integer", example=1),
     *          @OA\Property(property="user_id", type="integer", example=2),
     *          @OA\Property(property="order_id", type="integer", example=1),
     *          @OA\Property(property="amount", type="number", format="float", example=99.99),
     *          @OA\Property(property="paymenttype_id", type="integer", example=2),
     *          @OA\Property(property="transaction_id", type="string", example="TXN_123456789"),
     *          @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-22T12:00:00Z"),
     *          @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-22T12:00:00Z")
     *      )
     *  ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // $userId = auth()->id();
        $userId = 3;

        // Find order first
        $order = Order::findOrFail($request->order_id);

        // Calculate remaining balance
        $paidAmount = $order->payments()->sum('amount');
        $remainingAmount = $order->total - $paidAmount;

        if ($remainingAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been fully paid',
            ], 409);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'order_id'       => 'required|exists:orders,id',
            'paymenttype_id' => 'required|exists:paymenttypes,id',
            'amount'         => 'required|numeric|min:0|max:'.$remainingAmount,
            'transaction_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Create payment
            $payment = Payment::create([
                'user_id'        => $userId,
                'order_id'       => $order->id,
                'amount'         => $request->amount,
                'paymenttype_id' => $request->paymenttype_id,
                'transcation_id' => $request->transaction_id,
            ]);

            // Update order paid status if fully paid
            $order->is_paid = ($paidAmount + $request->amount) >= $order->total;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'data'    => new PaymentsResource($payment)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the payment.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
