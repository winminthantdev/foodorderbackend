<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\MenusResource;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/menus",
     *     summary="Get all menus",
     *     tags={"Menus (Public)"},
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
     *         description="Filter by status id",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
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
        $query = Menu::query()->with(['category:id,name,slug', 'subcategory:id,name,slug', 'status:id,name', 'promotions']);

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter by Status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by Category ID
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category); // or 'id'
            });
        }

        // Filter by Subcategory ID
        if ($request->filled('subcategory')) {
            $query->whereHas('subcategory', function($q) use ($request) {
                $q->where('slug', $request->subcategory);
            });
        }

        // Filter by promotion properly
        if ($request->has('is_promotion') && $request->is_promotion == 'true') {
            $query->whereHas('promotions', function($q) {
                $q->active();
            });
        }

        // Filter by bestseller properly
        if ($request->boolean('is_bestseller')) {
            $query->withCount('orderItems')->orderByDesc('order_items_count');
        }

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
     * @OA\Get(
     *     path="/v1/menus/{id}",
     *     summary="Get menu details",
     *     tags={"Menus (Public)"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="User detail")
     * )
     */
    public function show(Request $request)
    {
        $menu = Menu::findOrFail($request->id);

        return response()->json([
            'success' => true,
            'data' => new MenusResource($menu),
        ]);
    }

    public function relatedMenus(Request $request){
        $menu = Menu::findOrFail($request->id);

        $relatedMenus = Menu::where('subcategory_id', $menu->subcategory_id)
            ->where('id', '!=', $menu->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => MenusResource::collection($relatedMenus),
        ]);
    }
}
