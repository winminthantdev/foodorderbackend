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
     *             required={"order_id", "paymenttype_id"},
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="paymenttype_id", type="integer", example=2),
     *             @OA\Property(property="transaction_id", type="string", example="TXN_123456789")
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response=201,
     *          description="Payment created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Payment completed successfully"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="user_id", type="integer", example=2),
     *                  @OA\Property(property="order_id", type="integer", example=1),
     *                  @OA\Property(property="amount", type="number", example=99.99),
     *                  @OA\Property(property="status", type="string", example="success"),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time")
     *              )
     *          )
     *      ),
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
        $userId = 2;

        $validator = Validator::make($request->all(), [
            'order_id'       => 'required|exists:orders,id',
            'paymenttype_id' => 'required|exists:paymenttypes,id',
            'transaction_id' => 'nullable|string|max:255|unique:payments,transaction_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $payment = \DB::transaction(function () use ($request, $userId) {

                $order = Order::where('id', $request->order_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($order->user_id !== $userId) {
                    abort(403, 'Unauthorized payment attempt');
                }

                if ($order->is_paid) {
                    abort(409, 'Order already fully paid');
                }

                $paidAmount = $order->payments()->sum('amount');

                $remainingAmount = $order->total - $paidAmount;

                if ($remainingAmount <= 0) {
                    abort(409, 'No remaining balance');
                }

                $payment = Payment::create([
                    'user_id'        => $userId,
                    'order_id'       => $order->id,
                    'paymenttype_id' => $request->paymenttype_id,
                    'amount'         => $remainingAmount,
                    'transaction_id' => $request->transaction_id,
                    'stage_id'         => 3,
                ]);

                $order->update([
                    'is_paid'  => true,
                ]);

                return $payment;
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully',
                'data'    => new PaymentsResource($payment),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
