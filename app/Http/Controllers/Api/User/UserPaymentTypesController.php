<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PaymentTypesResource;
use App\Models\PaymentType;
use Illuminate\Http\Request;

class UserPaymentTypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/user/payment-types",
     *     summary="Get all payment types",
     *     tags={"PaymentTypes (User)"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="status_id",
     *         in="query",
     *
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PaymentTypes list"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = PaymentType::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by paymenttype type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $paymenttypes = $query->paginate($perPage);

        return response()->json([
            'data' => PaymentTypesResource::collection($paymenttypes),
            'meta' => [
                'current_page' => $paymenttypes->currentPage(),
                'total_page' => $paymenttypes->lastPage(),
                'per_page' => $paymenttypes->perPage(),
                'total' => $paymenttypes->total(),
            ],
        ], 200);
    }
}
