<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UsersResource;
use App\Http\Resources\Admin\UserInfosResource;
use App\Models\User;
use App\Models\Userinfo;
use Illuminate\Http\Request;
use Validator;

class AdminUsersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/users",
     *     summary="Get all users",
     *     tags={"Users (Admin)"},
     *     @OA\Response(response=200, description="List users")
     * )
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter example: by drivers type
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_id', $request->status_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $users = $query->with('userinfo')->paginate($perPage);

        return response()->json([
            'data' => UsersResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'total_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/admin/users",
     *     summary="Create a new user",
     *     tags={"Users (Admin)"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully")
     * )
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            return response()->json([
                'success' => true,
                'data' => new UsersResource($user),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User creation failed',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * @OA\Get(
     *     path="/v1/admin/users/{id}",
     *     summary="Get user details",
     *     tags={"Users (Admin)"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User detail")
     * )
     */
    public function show(string $id)
    {
        $user = User::with('userinfo')->findOrFail($id);
        $user = User::findOrFail($id);

        return response()->json([
            'data' => [
                "user"=> new UsersResource($user),
                "userinfo"=> $user->userinfo ?  new UserInfosResource($user->userinfo) : null,
            ],
        ], 200);
    }

    /**
     * @OA\Patch(
     *     path="/v1/admin/users/{id}/block",
     *     summary="Block a user",
     *     tags={"Users (Admin)"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User blocked")
     * )
     */
    public function block(string $id)
    {
        $userinfo = Userinfo::where('user_id', $id)->first();

        if(!$userinfo){
            return response()->json([
                'success' => false,
                'message' => 'Userinfo not found'
            ], 404);
        }
        $userinfo->update(['is_blocked' => true]);

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully'
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/v1/admin/users/{id}/unblock",
     *     summary="Unblock a user",
     *     tags={"Users (Admin)"},
     *    @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User unblocked")
     * )
     */
    public function unblock(string $id)
    {
        $userinfo = Userinfo::where('user_id', $id)->first();

        if(!$userinfo){
            return response()->json([
                'success' => false,
                'message' => 'Userinfo not found'
            ], 404);
        }
        $userinfo->update(['is_blocked' => false]);

        return response()->json([
            'success' => true,
            'message' => 'User unblocked successfully'
        ]);
    }

     /**
     * @OA\Delete(
     *     path="/v1/admin/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users (Admin)"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User deleted")
     * )
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->userinfo()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }


}
