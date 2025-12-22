<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\AddressesResource;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAddressesController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $addresses = Address::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'data' => AddressesResource::collection($addresses)
        ]);
    }


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
            'address_line' => $request->address_line,
            'city'         => $request->city,
            'state'        => $request->state,
            'postal_code'  => $request->postal_code,
            'country'      => $request->country,
            'is_default'   => $request->is_default ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data'    => new AddressesResource($address)
        ], 201);
    }


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


    public function update(Request $request, $id)
    {
        // $userId = auth()->id();
        $userId = 2;

        $address = Address::where('user_id', $userId)->find($id);

        if (!$address) {
            return response()->json(['success'=>false,'message'=>'Address not found'],404);
        }

        $validator = Validator::make($request->all(), [
            'address_line' => 'sometimes|required|string|max:255',
            'city'         => 'sometimes|required|string|max:100',
            'state'        => 'nullable|string|max:100',
            'postal_code'  => 'nullable|string|max:20',
            'country'      => 'sometimes|required|string|max:100',
            'is_default'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        if ($request->is_default) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json(['success'=>true,'message'=>'Address updated successfully','data'=>new AddressesResource($address)]);
    }


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
}
