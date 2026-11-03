<?php

namespace App\Service\Api\User;

use App\Enums\Status;
use App\Models\Application;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Category;
use App\Models\RecentlyView;
use App\Models\Shop;
use App\Models\ShopProduct;

class ProductService
{
    public function products($request)
    {
        $user = auth()->user();
        $limit = $request->limit ?? 10;

        $query = Product::with([
            'feedbacks:id,product_id,rating',
            'category:id,name'
        ])->where('status', 1);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->sub_category_id) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->sort == "low_to_high") {
            $query->orderBy('amount', 'asc');
        }

        if ($request->sort == "high_to_low") {
            $query->orderBy('amount', 'desc');
        }

        if ($request->ratings) {
            $query->whereHas('feedbacks', function ($q) use ($request) {
                $q->havingRaw('AVG(rating) >= ?', [$request->ratings]);
            });
        }

        $products = $query->paginate($limit);

        $productList = $products->map(function ($product) use ($user) {

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image_url ?? url('img/avatar.jpg'),
                'wishlist' => $product->is_liked,
                'available_stock' => $product->available_stock,
                'total_stock' => $product->product_stock,
                'amount' => $product->amount,
                'discount_amount' => $product->discount_amount,
                'discount_percentage' => $product->discount_percentage,
                'avg_rating' => (string) number_format($product->feedbacks_avg_rating ?? 0, 1),
                'total_rating' => (int) ($product->feedbacks_count ?? 0),
                'cart_quantity' => (int) ($product->cart_quantity ?? 0),
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => [
                'products' => $productList,
                'pagination' => [
                    'current_page'   => $products->currentPage(),
                    'last_page'      => $products->lastPage(),
                    'total'          => $products->total(),
                    'per_page'       => $products->perPage(),
                    'current_page_url' => $products->url($products->currentPage()),
                    'next_page_url'    => $products->nextPageUrl(),
                    'prev_page_url'    => $products->previousPageUrl(),
                ]
            ]
        ]);
    }

    public function productDetail($productId)
    {
        $product = Product::approvedWithShop()->where('id', $productId)
            ->with(['shop', 'feedbacks'])
            ->where('status', Status::ACTIVE)
            ->first();
        if (!$product) {
            return response()->json([
                'status' => 0,
                'data' => [
                    'product' => [],
                    'similar_products' => []
                ],
                'message' => 'Product Detail Not Found'
            ]);
        }
        $productDetail = $this->formatProduct($product);
        $reviews = $product->feedbacks()
            ->with('user:id,name,image')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($review) {
                return [
                    'customer_name' => $review->user->name ?? 'Guest',
                    'customer_image' => $review->user->image == 'avatar.jpg' ? url('img/avatar.jpg') : url('storage/user/'.$review->user->image),
                    'rating'        => (float)$review->rating,
                    'review'        => $review->review ?? '',
                    'created_at'    => $review->created_at->format('d M Y'),
                ];
            });
        $similarProducts = Product::approvedWithShop()->with('feedbacks')
            ->where('status', Status::ACTIVE)
            ->where('sub_category_id', $product->sub_category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($q) => $this->formatCustomizeProduct($q));

        $recentlyView = new RecentlyView();
        $recentlyView->fill([
            'product_id' => $productId,
            'user_id' => auth()->id(),
        ]);
        $recentlyView->save();
        $freeDeliveryAmount = (int)Application::where('name', 'free_delivery_amount_above')->value('value') ?? 0;
        return response()->json([
            'status' => 1,
            'data' => [
                'free_delivery_amount' => $freeDeliveryAmount,
                'total_cart' => Cart::where('user_id', auth()->id())->count(),
                'product' => $productDetail,
                'customer_reviews'  => $reviews,
                'similar_products' => $similarProducts
            ],
            'message' => 'Product Details'
        ]);
    }

    public function formatProduct($product)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category_name' => $product->category->name,
            'sub_category_name' => $product->subCategory->name,
            'image' => $product->full_images,
            'quantity' => $product->quantity,
            'quantity_unit' => $product->quantity_unit,
            'cart_quantity' => (int)Cart::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->value('quantity') ?? 0,
            'product_stock' => $product->product_stock,
            'amount' => $product->amount,
            'discount_amount' => $product->discount_amount,
            'discount_percentage' => $product->discount_percentage,
            'description' => $product->description,
            'is_liked' => $product->is_liked,

            'shop_name' => optional($product->shop)->name,
            'avg_rating' => (string)round($product->averageRating(), 1),
            'total_rating' => (int)$product->feedbacks->count(),

            'additional_images' =>
            $product->additionalImages->map(fn($img) => $img->image_url)
                ->prepend($product->image_url)
                ->toArray(),

            'additional_info' => $product->additional_info ? $this->formatAdditionalInfo($product->additional_info) : [],
            'youtube_videos' => $product->youtube_videos ? $this->formatYoutubeVideos($product->youtube_videos) : [],

        ];
    }

    private function formatYoutubeVideos($videos)
    {
        if (empty($videos)) {
            return [];
        }

        if (is_string($videos)) {
            $decoded = json_decode($videos, true);
        } else {
            $decoded = $videos;
        }

        if (!is_array($decoded)) {
            return [];
        }

        return collect($decoded)->map(function ($video) {
            return [
                'url' => $video['url'] ?? '',
            ];
        })->toArray();
    }

    public function formatCustomizeProduct($product)
    {
        return [
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
            'cart_quantity' => (int)Cart::where('user_id', auth()->id())
                    ->where('product_id', $product->id)
                    ->value('quantity') ?? 0,
        ];
    }

    public function formatAdditionalInfo($info)
    {
        if (empty($info)) {
            return [];
        }

        if (is_string($info)) {
            $decoded = json_decode($info, true);
        } else {
            $decoded = $info;
        }

        if (!is_array($decoded)) {
            return [];
        }

        return collect($decoded)->map(function ($section) {

            return [
                'heading' => $section['heading'] ?? '',
                'items' => collect($section['items'] ?? [])->map(function ($item) {
                    return [
                        'title' => $item['title'] ?? '',
                        'value' => $item['value'] ?? '',
                    ];
                })->toArray()
            ];
        })->toArray();
    }
}
