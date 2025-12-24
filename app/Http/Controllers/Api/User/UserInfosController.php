<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\InfosResource;
use App\Models\Userinfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserInfosController extends Controller
{
    /**
     * Get current user's profile
     * 
     */
    public function show()
    {
        $user = auth()->user();

        $userinfo = Userinfo::where('user_id', $user->id)->first();

        if (! $userinfo) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new InfosResource($userinfo),
        ]);
    }

    /**
     * Create user profile (one-time)
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        if (Userinfo::where('user_id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already exists'
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'gender' => 'required|in:male,female,other',
            'avatar' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'notification_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userinfo = Userinfo::create([
            'user_id' => $userId,
            'gender' => $request->gender,
            'avatar' => $request->avatar,
            'date_of_birth' => $request->date_of_birth,
            'notification_enabled' => $request->notification_enabled ?? true,
            'loyalty_points' => 0,
            'is_blocked' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile created successfully',
            'data' => new InfosResource($userinfo),
        ], 201);
    }

    /**
     * Update own profile
     */
    public function update(Request $request)
    {
        $userId = auth()->id();

        $userinfo = Userinfo::where('user_id', $userId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'gender' => 'sometimes|in:male,female,other',
            'avatar' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'notification_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userinfo->update($request->only([
            'gender',
            'avatar',
            'date_of_birth',
            'notification_enabled'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new InfosResource($userinfo),
        ]);
    }
}
