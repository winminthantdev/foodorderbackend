<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PaymentsResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminPaymentsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/payments",
     *     summary="Get all payments (Admin)",
     *     tags={"Payments (Admin)"},
     *
     *     @OA\Parameter(
     *         name="status_id",
     *         in="query",
     *         description="Filter by payment status",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of payments",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="amount", type="number", format="float", example=49.99),
     *                     @OA\Property(property="status_id", type="integer", example=3),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    
    public function index(Request $request)
    {
        $query = Payment::query();

        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $payments = $query->latest()->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => PaymentsResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'total_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/v1/admin/payments/{id}",
     *     summary="Get payment detail",
     *     tags={"Payments (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="amount", type="number", format="float", example=49.99),
     *                 @OA\Property(property="status_id", type="integer", example=3),
     *                 @OA\Property(property="user_id", type="integer", example=12)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function show($id)
    {
        $payment = Payment::find($id);

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'data' => new PaymentsResource($payment),
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/v1/admin/payments/{id}/approve",
     *     summary="Approve payment",
     *     tags={"Payments (Admin)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment approved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment approved successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function approve($id)
    {
        $payment = Payment::find($id);

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $payment->status_id = 3; // Paid
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment approved successfully'
        ]);
    }
}
