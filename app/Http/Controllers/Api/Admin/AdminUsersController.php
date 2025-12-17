<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UsersResource;
use App\Models\User;
use App\Models\Userinfo;
use Illuminate\Http\Request;

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
        $user = User::with('userinfo')->find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'data' => new UsersResource($user),
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
