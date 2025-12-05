<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Admin login
     */
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find admin by email
        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        //  Create API token
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Return response
        return response()->json([
            'admin' => $admin,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        // Revoke all tokens for this admin
        $request->user('admin')->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
