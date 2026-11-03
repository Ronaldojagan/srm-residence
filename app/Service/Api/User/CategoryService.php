<?php

namespace App\Service\Api\User;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;

class CategoryService
{
    public function categories($request)
    {
        $orderBy = $request->input('sort_order', 'id');
        $orderDir = $request->input('orderDir', 'desc');
        $status = $request->input('status', true);

        $categories = Category::where('status', $status)
            ->whereHas('product', function ($query) {
                $query->approved()->where('status', true);
            })
            ->select('id', 'name', 'image')
            ->orderBy($orderBy, $orderDir)
            ->get()
            ->map(function ($category) {

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image_url,
                ];
            });

        return response()->json([
            'status' => 1,
            'data' => $categories,
            'message' => 'Category List'
        ]);
    }

    public function getSubCategories($categoryId)
    {

        $subCategories = SubCategory::where('category_id', $categoryId)
            ->where('status', true)
            ->whereHas('product', function ($q) {
                $q->approved()
                    ->where('status', true);
            })
            ->select('id', 'name', 'image')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($sub) {

                return [
                    'id'    => $sub->id,
                    'name'  => $sub->name,
                    'image' => $sub->image_url,
                ];
            });

        return response()->json([
            'status' => 1,
            'data'   => $subCategories,
            'message' => 'Sub Category List'
        ]);
    }

    public function getProductsBySubCategory($subCategoryId)
    {
        $userId = auth()->id();

        $subCategory = SubCategory::where('id', $subCategoryId)
            ->where('status', true)
            ->first();

        if (!$subCategory) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Subcategory not found.'
            ], 404);
        }

        $banners = $subCategory->banners
            ? $subCategory->banners->pluck('image_url')->values()
            : [];

        $cartItems = Cart::where('user_id', $userId)
            ->pluck('quantity', 'product_id');

        $totalCartQuantity = Cart::where('user_id', $userId)->count();

        $products = Product::approvedWithShop()
            ->where('sub_category_id', $subCategoryId)
            ->where('status', true)
            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->paginate(10);

        $productsData = $products->getCollection()->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $product->image ? $product->image_url : url('img/avatar.jpg'),
            'wishlist' => $product->is_liked,
            'available_stock' => $product->available_stock,
            'total_stock' => $product->product_stock,
            'amount' => (float) $product->amount,
            'discount_amount' => (float) $product->discount_amount,
            'discount_percentage' => (float) $product->discount_percentage,
            'avg_rating' => (string) round($product->feedbacks_avg_rating ?? 0, 1),
            'total_rating' => (int) ($product->feedbacks_count ?? 0),
            'cart_quantity' => (int) ($cartItems[$product->id] ?? 0),
        ]);

        return response()->json([
            'status' => 1,
            'data' => [
                'total_quantity' => $totalCartQuantity,
                'sub_category_name' => $subCategory->name,
                'banners' => $banners,
                'products' => $productsData,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'first_page_url' => $products->url(1),
                    'last_page_url' => $products->url($products->lastPage()),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                ]
            ],
            'message' => 'Products List'
        ]);
    }

    public function categoriesWithSubcategories($request)
    {
        $status = $request->input('status', true);
        $selectedCategoryId = $request->input('category_id');

        $categories = Category::where('status', $status)
            ->whereHas('product', function ($q) {
                $q->approved()->where('status', true);
            })
            ->with(['subCategories' => function ($q) {
                $q->where('status', true)
                    ->whereHas('product', function ($q2) {
                        $q2->approved()->where('status', true);
                    })
                    ->select('id', 'category_id', 'name', 'image')
                    ->orderBy('id', 'asc');
            }])
            ->select('id', 'name', 'image')
            ->orderBy('id', 'asc')
            ->get();

        if (!$selectedCategoryId && $categories->isNotEmpty()) {
            $selectedCategoryId = $categories->first()->id;
        }

        $data = $categories->map(function ($category) use ($selectedCategoryId) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image_url,
                'selected' => $category->id == $selectedCategoryId,
                'sub_categories' => $category->subCategories->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'image' => $sub->image_url,
                    ];
                }),
            ];
        });
        $cartQuantity = (int)Cart::where('user_id', auth()->id())->count() ?? 0;
        return response()->json([
            'status' => 1,
            'data' => [
                'total_quantity' => $cartQuantity,
                'categories' =>$data
            ],
            'selected_category_id' => $selectedCategoryId,
            'message' => 'Categories with Subcategories',
        ]);
    }
}
