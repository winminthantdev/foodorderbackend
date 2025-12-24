<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PaymentTypesResource;
use App\Models\PaymentType;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminPaymentTypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/payment-types",
     *     summary="Get all payment types",
     *     tags={"Admin PaymentTypes"},
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

    /**
     * @OA\Post(
     *     path="/v1/admin/payment-types",
     *     summary="Create payment type",
     *     tags={"Admin PaymentTypes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *
     *                 @OA\Schema(
     *                     required={"name","status_id"},
     *
     *                     @OA\Property(property="name", type="string", example="KBZ Pay"),
     *                     @OA\Property(property="status_id", type="integer", example=3),
     *                     @OA\Property(
     *                         property="icon",
     *                         type="string",
     *                         format="binary"
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:paymenttypes,name',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status_id' => 'required|in:3,4',
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

            $iconPath = null;
            // Single Image Upload
            if ($request->hasFile('icon')) {

                $file = $request->file('icon');
                $newfilename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('assets/images/paymenttypes/'), $newfilename);
                $iconPath = 'assets/images/paymenttypes/'.$newfilename;
            }

            // Create PaymentType
            $paymenttype = PaymentType::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'icon' => $iconPath,
                'status_id' => $request->status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PaymentType created successfully',
                'data' => new PaymentTypesResource($paymenttype),
            ], 201);

        } catch (\Exception $e) {
            // Handle other server errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to create paymenttype',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/admin/payment-types/{id}",
     *     summary="Update payment type",
     *     tags={"Admin PaymentTypes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *
     *                 @OA\Schema(
     *                     required={"name","status_id"},
     *
     *                     @OA\Property(property="name", type="string", example="KBZ Pay"),
     *                     @OA\Property(property="status_id", type="integer", example=3),
     *                     @OA\Property(
     *                         property="icon",
     *                         type="string",
     *                         format="binary"
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        // Find paymenttype
        $paymenttype = PaymentType::find($id);

        if (! $paymenttype) {
            return response()->json([
                'success' => false,
                'message' => 'PaymentType not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255|unique:paymenttypes,name,'.$id,
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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

            if($request->hasFile('icon') ) {
                $oldIcon = $paymenttype->icon;

                if(File::exists(public_path($oldIcon))) {
                    File::delete(public_path($oldIcon));
                }

                // Single Image Upload
                $file = $request->file('icon');
                $newfilename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('assets/images/paymenttypes/'), $newfilename);
                $paymenttype->icon = 'assets/images/paymenttypes/'.$newfilename;
            }


            // Update data
            $paymenttype->name = $request->name;
            $paymenttype->slug = Str::slug($request->name);
            $paymenttype->status_id = $request->status_id;
            $paymenttype->save();

            return response()->json([
                'success' => true,
                'message' => 'PaymentType updated successfully',
                'data' => new PaymentTypesResource($paymenttype),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update paymenttype',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/payment-types/{id}",
     *     summary="Delete payment type",
     *     tags={"Admin PaymentTypes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Deleted"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(string $id)
    {
        // Find PaymentType
        $paymenttype = PaymentType::find($id);

        // If not found → 404
        if (! $paymenttype) {
            return response()->json([
                'success' => false,
                'message' => 'PaymentType not found',
            ], 404);
        }

        try {
            // Delete record
            $oldIcon = $paymenttype->icon;

                if(File::exists(public_path($oldIcon))) {
                    File::delete(public_path($oldIcon));
                }
                
            $paymenttype->delete();



            return response()->json([
                'success' => true,
                'message' => 'PaymentType deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete paymenttype',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
