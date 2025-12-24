<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\AddressesResource;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAddressesController extends Controller
{

    /**
 * @OA\Get(
 *     path="/v1/user/addresses",
 *     summary="Get all user addresses",
 *     tags={"Addresses (User)"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of addresses",
 *              @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="label", type="string", example="Home"),
 *             @OA\Property(property="address_line1", type="string"),
 *             @OA\Property(property="address_line2", type="string", nullable=true),
 *             @OA\Property(property="city", type="string"),
 *             @OA\Property(property="latitude", type="number", nullable=true),
 *             @OA\Property(property="longitude", type="number", nullable=true),
 *             @OA\Property(property="is_default", type="boolean"),
 *             @OA\Property(property="created_at", type="string", example="2025-12-21"),
 *             @OA\Property(property="updated_at", type="string", example="2025-12-21")
     *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */

    public function index()
    {
        // $userId = auth()->id();
        $userId = 2;

        $addresses = Address::where('user_id', $userId)->get();

        return response()->json([
            'success' => true,
            'data' => AddressesResource::collection($addresses)
        ]);
    }

    /**
 * @OA\Post(
 *     path="/v1/user/addresses",
 *     summary="Create a new address",
 *     tags={"Addresses (User)"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"label","address_line1","city"},
 *             @OA\Property(property="label", type="string", example="Home"),
 *             @OA\Property(property="address_line1", type="string", example="123 Main Street"),
 *             @OA\Property(property="address_line2", type="string", example="Apartment 4B"),
 *             @OA\Property(property="city", type="string", example="Yangon"),
 *             @OA\Property(property="latitude", type="number", example=16.8409),
 *             @OA\Property(property="longitude", type="number", example=96.1735),
 *             @OA\Property(property="is_default", type="boolean", example=true)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Address created",
 *          @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="label", type="string", example="Home"),
 *             @OA\Property(property="address_line1", type="string"),
 *             @OA\Property(property="address_line2", type="string", nullable=true),
 *             @OA\Property(property="city", type="string"),
 *             @OA\Property(property="latitude", type="number", nullable=true),
 *             @OA\Property(property="longitude", type="number", nullable=true),
 *             @OA\Property(property="is_default", type="boolean"),
 *             @OA\Property(property="created_at", type="string", example="2025-12-21"),
 *             @OA\Property(property="updated_at", type="string", example="2025-12-21")
 *         )
 *     ),
 *
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */


    public function store(Request $request)
    {
        // $userId = auth()->id();
        $userId = 2;

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'         => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        if ($request->is_default) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        $address = Address::create([
            'user_id'      => $userId,
            'label' => $request->label,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_default'   => $request->is_default ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data'    => new AddressesResource($address)
        ], 201);
    }

    /**
 * @OA\Get(
 *     path="/v1/user/addresses/{id}",
 *     summary="Get a single address",
 *     tags={"Addresses (User)"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Address found",
 *          @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="label", type="string", example="Home"),
 *             @OA\Property(property="address_line1", type="string"),
 *             @OA\Property(property="address_line2", type="string", nullable=true),
 *             @OA\Property(property="city", type="string"),
 *             @OA\Property(property="latitude", type="number", nullable=true),
 *             @OA\Property(property="longitude", type="number", nullable=true),
 *             @OA\Property(property="is_default", type="boolean"),
 *             @OA\Property(property="created_at", type="string", example="2025-12-21"),
 *             @OA\Property(property="updated_at", type="string", example="2025-12-21")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Address not found"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */


    public function show($id)
    {
        // $userId = auth()->id();
        $userId = 2;

        $address = Address::where('user_id', $userId)->find($id);

        if (!$address) {
            return response()->json(['success'=>false,'message'=>'Address not found'],404);
        }

        return response()->json(['success'=>true,'data'=>new AddressesResource($address)]);
    }


    /**
 * @OA\Put(
 *     path="/v1/user/addresses/{id}",
 *     summary="Update an address",
 *     tags={"Addresses (User)"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"label","address_line1","city"},
 *             @OA\Property(property="label", type="string", example="Office"),
 *             @OA\Property(property="address_line1", type="string", example="456 Office Road"),
 *             @OA\Property(property="address_line2", type="string", example="Floor 3"),
 *             @OA\Property(property="city", type="string", example="Mandalay"),
 *             @OA\Property(property="latitude", type="number", example=21.9588),
 *             @OA\Property(property="longitude", type="number", example=96.0891),
 *             @OA\Property(property="is_default", type="boolean", example=false)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Address updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="label", type="string", example="Home"),
 *             @OA\Property(property="address_line1", type="string"),
 *             @OA\Property(property="address_line2", type="string", nullable=true),
 *             @OA\Property(property="city", type="string"),
 *             @OA\Property(property="latitude", type="number", nullable=true),
 *             @OA\Property(property="longitude", type="number", nullable=true),
 *             @OA\Property(property="is_default", type="boolean"),
 *             @OA\Property(property="created_at", type="string", example="2025-12-21"),
 *             @OA\Property(property="updated_at", type="string", example="2025-12-21")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Address not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */

    public function update(Request $request, $id)
    {
        // $userId = auth()->id();
        $userId = 2;

        $address = Address::where('user_id', $userId)->find($id);

        if (!$address) {
            return response()->json(['success'=>false,'message'=>'Address not found'],404);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'         => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        if ($request->is_default) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        $address->update([
            'user_id'      => $userId,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_default'   => $request->is_default ?? false,
        ]);

        return response()->json(['success'=>true,'message'=>'Address updated successfully','data'=>new AddressesResource($address)]);
    }

    /**
 * @OA\Delete(
 *     path="/v1/user/addresses/{id}",
 *     summary="Delete an address",
 *     tags={"Addresses (User)"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Address deleted",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Address deleted successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Address not found"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */


    public function destroy($id)
    {
        // $userId = auth()->id();
        $userId = 2;

        $address = Address::where('user_id', $userId)->find($id);

        if (!$address) {
            return response()->json(['success'=>false,'message'=>'Address not found'],404);
        }

        $address->delete();

        return response()->json(['success'=>true,'message'=>'Address deleted successfully']);
    }

    // default address change function
    /**
     * @OA\Patch(
     *      path="/v1/user/addresses/{id}/default",
     *      summary="Update address as default",
     *      tags = {"Addresses (User)"},
     *      security={{"bearerAuth": {}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="address id to update as default",
     *
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Default address updated successfully",
     *      )
     * )
     */
    public function setDefault($id){

        // TEMP (replace with auth()->id())
        $userId = 2;

        $address = Address::where("id", $id)->where('user_id', $userId)->first();

        if (!$address) {
            return response()->json(['success'=>false,'message'=> 'Address not found'],404);
        }

        \DB::transaction(function () use ($userId,$address) {

            // Remove previous default
            $address::where('user_id', $userId)->where('is_default', true)->update(['is_default' => false]);

            // Set new default
            $address->update(['is_default'=> true]);
        });

        return response()->json(['success'=>true,'message'=> 'Default address updated successfully']);
    }

}
