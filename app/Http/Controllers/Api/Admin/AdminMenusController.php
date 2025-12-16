<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MenusResource;
use App\Models\Menu;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminMenusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/menus",
     *     summary="Get all menus",
     *     tags={"Admin Menus"},
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
     *         description="Menus list"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Menu::query();

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
        $menus = $query->paginate($perPage);

        return response()->json([
            'data' => MenusResource::collection($menus),
            'meta' => [
                'current_page' => $menus->currentPage(),
                'total_page' => $menus->lastPage(),
                'per_page' => $menus->perPage(),
                'total' => $menus->total(),
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/admin/menus",
     *     summary="Create payment type",
     *     tags={"Admin Menus"},
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
     *                     required={"name", "description", "price", "rating", "subcategory_id", "category_id", "status_id"},
     *                     @OA\Property(property="name", type="string", example="Shan Noodles"),
     *                    @OA\Property(property="description", type="string", example="Delicious Shan Noodles"),
     *                    @OA\Property(property="price", type="number", format="float", example=5.99),
     *                    @OA\Property(property="rating", type="number", format="float", example=4.5),
     *                    @OA\Property(property="subcategory_id", type="integer", example=2),
     *                    @OA\Property(property="category_id", type="integer", example=1),
     *                     @OA\Property(property="status_id", type="integer", example=3)
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
            'name' => 'required|string|max:255|unique:menus,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'rating' => 'nullable|numeric|between:0,5',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'category_id' => 'nullable|exists:categories,id',
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
            if ($request->hasFile('image')) {

                $file = $request->file('image');
                $newfilename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('assets/images/menus/'), $newfilename);
                $iconPath = 'assets/images/menus/'.$newfilename;
            }

            // Create Menu
            $paymenttype = Menu::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $iconPath,
                'status_id' => $request->status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Menu created successfully',
                'data' => new MenusResource($paymenttype),
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
     *     path="/v1/admin/menus/{id}",
     *     summary="Update payment type",
     *     tags={"Admin Menus"},
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
     *                         property="image",
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
        $paymenttype = Menu::find($id);

        if (! $paymenttype) {
            return response()->json([
                'success' => false,
                'message' => 'Menu not found',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255|unique:menus,name,'.$id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'rating' => 'nullable|numeric|between:0,5',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'category_id' => 'nullable|exists:categories,id',
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

            if($request->hasFile('image') ) {
                $oldIcon = $paymenttype->image;

                if(File::exists(public_path($oldIcon))) {
                    File::delete(public_path($oldIcon));
                }
                // Single Image Upload
                $file = $request->file('image');
                $newfilename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('assets/images/menus/'), $newfilename);
                $paymenttype->image = 'assets/images/menus/'.$newfilename;
            }


            // Update data
            $paymenttype->name = $request->name;
            $paymenttype->slug = Str::slug($request->name);
            $paymenttype->description = $request->description;
            $paymenttype->price = $request->price;
            $paymenttype->rating = $request->rating;
            $paymenttype->subcategory_id = $request->subcategory_id;
            $paymenttype->category_id = $request->category_id;
            $paymenttype->status_id = $request->status_id;
            $paymenttype->save();

            return response()->json([
                'success' => true,
                'message' => 'Menu updated successfully',
                'data' => new MenusResource($paymenttype),
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
     *     path="/v1/admin/menus/{id}",
     *     summary="Delete payment type",
     *     tags={"Admin Menus"},
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
        // Find Menu
        $paymenttype = Menu::find($id);

        // If not found → 404
        if (! $paymenttype) {
            return response()->json([
                'success' => false,
                'message' => 'Menu not found',
            ], 404);
        }

        try {
            // Delete record
            $paymenttype->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menu deleted successfully',
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
