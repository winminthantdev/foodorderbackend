<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\InfosResource;
use App\Models\User;
use App\Models\Userinfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserInfosController extends Controller
{

    /**
     * Get current user's profile
     *
     * @OA\Get(
     *     path="/v1/user/profile",
     *     summary="Get authenticated user profile",
     *     tags={"User Profile (User)"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="avatar", type="string", example="path/images/users/avatar.png"),
     *                 @OA\Property(property="date_of_birth", type="string", example="1998-05-20"),
     *                 @OA\Property(property="notification_enabled", type="boolean", example=true),
     *                 @OA\Property(property="loyalty_points", type="integer", example=120),
     *                 @OA\Property(property="is_blocked", type="boolean", example=false),
     *                 @OA\Property(property="last_active", type="string", example="2025-12-24 10:30:00")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User profile not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */


    public function show()
    {
        // $userId = auth()->user();
        $userId = 2;

        $userinfo = Userinfo::where('user_id', $userId)->first();

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
     *
     * @OA\Post(
     *     path="/v1/user/profile",
     *     summary="Create user profile",
     *     tags={"User Profile (User)"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"gender"},
     *
     *                 @OA\Property(
     *                     property="gender",
     *                     type="string",
     *                     enum={"male","female"},
     *                     example="male"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="date_of_birth",
     *                     type="string",
     *                     format="date",
     *                     example="1998-05-20"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="notification_enabled",
     *                     type="boolean",
     *                     example=true
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Profile created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="avatar", type="string", example="uploads/users/avatar.png"),
     *                 @OA\Property(property="date_of_birth", type="string", example="1998-05-20"),
     *                 @OA\Property(property="notification_enabled", type="boolean", example=true),
     *                 @OA\Property(property="loyalty_points", type="integer", example=0),
     *                 @OA\Property(property="is_blocked", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Profile already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Profile already exists")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */



    public function store(Request $request)
    {
        // $userId = auth()->id();
        $userId = 2;

        if (Userinfo::where('user_id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already exists'
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'gender' => 'required|in:male,female,other',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'date_of_birth' => 'nullable|date',
            'notification_enabled' => 'sometimes|in:true,false,0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $avatarPath  = null;

            // Single Image Upload

            if ($request->hasFile('avatar')) {

                $file = $request->file('avatar');
                $avatarPath = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('assets/images/users/'), $avatarPath);
                $avatarPath = 'assets/images/users/'.$avatarPath;
            }

            $notificationEnabled = $request->has('notification_enabled')
            ? filter_var($request->notification_enabled, FILTER_VALIDATE_BOOLEAN)
            : true;

            // Create Profile
            $userinfo = Userinfo::create([
                'user_id' => $userId,
                'gender' => $request->gender,
                'avatar' => $avatarPath,
                'date_of_birth' => $request->date_of_birth,
                'notification_enabled' => $notificationEnabled,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile created successfully',
                'data' => new InfosResource($userinfo),
            ], 201);

        }catch (\Exception $e) {
            return response()->json([
                'success'=> false,
                'message'=> 'Failed to create user info.',
                'error' => $e->getMessage(),
            ],500);
        }
    }

    /**
     * Update own profile
     *
     * @OA\Put(
     *     path="/v1/user/profile",
     *     summary="Update user profile",
     *     tags={"User Profile (User)"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"gender"},
     *
     *                 @OA\Property(
     *                     property="gender",
     *                     type="string",
     *                     enum={"male","female"},
     *                     example="male"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="date_of_birth",
     *                     type="date",
     *                     format="date",
     *                     example="1998-05-20"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="notification_enabled",
     *                     type="boolean",
     *                     example=true
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Profile update successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="avatar", type="string", example="uploads/users/avatar.png"),
     *                 @OA\Property(property="date_of_birth", type="string", example="1998-05-20"),
     *                 @OA\Property(property="notification_enabled", type="boolean", example=true),
     *                 @OA\Property(property="loyalty_points", type="integer", example=0),
     *                 @OA\Property(property="is_blocked", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Profile already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Profile already exists")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */


    public function update(Request $request)
    {
        // $userId = auth()->id();
        $userId = 2;

        $userinfo = Userinfo::where('user_id', $userId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'gender' => 'sometimes|in:male,female,other',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'date_of_birth' => 'nullable|date',
            'notification_enabled' => 'sometimes|in:true,false,0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        try {

            if ($request->hasFile('avatar')) {

                $oldImage = $userinfo->avatar;

                if(\File::exists(public_path($oldImage))) {
                    \File::delete(public_path($oldImage));
                }

                // Single Image Upload
                $file = $request->file('avatar');
                $newfilename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('assets/images/users/'), $newfilename);
                $userinfo->avatar = 'assets/images/users/'.$newfilename;
            }

            $notificationEnabled = $request->has('notification_enabled')
            ? filter_var($request->notification_enabled, FILTER_VALIDATE_BOOLEAN)
            : true;

            // Create Profile
            $userinfo->gender = $request->gender;
            $userinfo->date_of_birth = $request->date_of_birth;
            $userinfo->notification_enabled = $notificationEnabled;
            $userinfo->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile created successfully',
                'data' => new InfosResource($userinfo),
            ], 201);

        }catch (\Exception $e) {
            return response()->json([
                'success'=> false,
                'message'=> 'Failed to create user info.',
                'error' => $e->getMessage(),
            ],500);
        }
    }

    /**
     * @OA\Patch(
     *      path="/v1/user/profile/avatar",
     *      summary="Update user profile image",
     *      tags={"User Profile (User)"},
     *     security={{"bearerAuth": {}}},
     *
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *
     *              @OA\Schema(
     *                  required={"avatar"},
     *                  @OA\Property(property="avatar", type="string", format="binary", description="User Profile image")
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Profile image updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Profile image updated successfully"),
     *              @OA\Property(
     *                  property="data",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="avatar", type="string", example="assets/images/users/12345_6789.png")
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(response=422, description="Validation error"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *
     *
     * )
     *
     *
     */

    public function updateAvatar(Request $request){
        // $userId = auth()->id();
        $userId = 2;

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {return response()->json(['success'=>false,'errors'=>$validator->errors()],422);}

        $userinfo = UserInfo::where('user_id',$userId)->firstOrFail();

        // delete old avatar if exists

        if ($request->hasFile('avatar')) {

                $oldImage = $userinfo->avatar;

                if(\File::exists(public_path($oldImage))) {
                    \File::delete(public_path($oldImage));
                }

                // Single Image Upload
                $file = $request->file('avatar');
                $avatarPath = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('assets/images/users/'), $avatarPath);
                $avatarPath = 'assets/images/users/'.$avatarPath;
        }

        $userinfo->avatar = $avatarPath;
        $userinfo->save();

        return response()->json(['success'=>true,'message'=> 'Profile image updated successfully.','data'=>['id'=> $userinfo->id, 'avatar'=> $avatarPath]],200);

    }


    /**
     * @OA\Patch(
     *      path="/v1/user/profile/notification",
     *      summary="Update Notification Status",
     *      tags={"User Profile (User)"},
     *     security={{"bearerAuth": {}}},
     *
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="notification_enabled",
     *                  type="boolean",
     *                  example=true
     *           )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Notification status updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Notification status updated successfully"),
     *              @OA\Property(property="notification_enabled", type="boolean", example=true)
     *          )
     *      ),
     *
     *      @OA\Response(response=422, description="Validation error"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *
     *
     * )
     *
     *
     */

    public function notifyUpdate(Request $request){
        // $userId = auth()->id();
        $userId = 2;

        $validator = Validator::make($request->all(), [
            'notification_enabled' => 'required|sometimes|in:true,false,0,1',
        ]);

        if ($validator->fails()) {return response()->json(['success'=>false,'errors'=>$validator->errors()],422);}

        $userinfo = UserInfo::where('user_id',$userId)->firstOrFail();

        $notificationEnabled = $request->has('notification_enabled')
            ? filter_var($request->notification_enabled, FILTER_VALIDATE_BOOLEAN)
            : true;

        $userinfo->notification_enabled = $notificationEnabled;

        $userinfo->save();

        return response()->json(['success'=>true,'message'=> 'Notification status updated successfully','data'=>['id'=> $userinfo->id, 'notification_enabled'=> $notificationEnabled]],200);

    }
}
