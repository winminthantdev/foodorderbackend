<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Admin login
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find admin by email
        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message'=> 'Invalid credentials'
            ],401);
        }

        //  Create API token
        $token = $admin->createToken('admin')->plainTextToken;

        // Return response
        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email'=> $admin->email
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        // Revoke all tokens for this admin
        // $request->user('admin')->tokens()->delete();
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
