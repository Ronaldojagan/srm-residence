<?php

namespace App\Service\Api\User;

use App\Enums\OrderStatusSelect;
use App\Enums\Status;
use App\Models\User;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Address;
use App\Models\Application;
use App\Models\Brand;
use App\Models\CancellationReason;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReplaceOrderReason;
use App\Models\ReturnOrderReason;
use App\Models\Shop;
use App\Models\SubCategory;
use App\Models\UserAccountReason;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CommonService
{
    public function profile()
    {
        $user = User::select('name', 'phone', 'image', 'status')
            ->where('id', auth()->id())
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'data' => 'User not found.',
            ]);
        }

        $imageUrl = $user->image === 'avatar.jpg'
            ? url('/img/avatar.jpg')
            : url('storage/user/' . $user->image);

        return response()->json([
            'status' => 1,
            'data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'image' => $imageUrl,
            ]
        ]);
    }

    public function home()
    {
        try {
            $finalData = [];

            $finalData['user_data'] = $this->userData();

            $finalData['address_data'] = $this->addressData();

            $finalData['banners'] = $this->banner();

            $finalData['categories'] = $this->getCategories();

            $finalData['brands'] = $this->getBrands();

            $finalData['latest_products'] = $this->getLatestProducts();

            $finalData['lowest_price_sub_category'] = $this->lowestPriceProducts();

            $finalData['top_deals'] = $this->getTopDeals();

            $finalData['best_selling'] = $this->getBestSellingProducts();

            $finalData['offers_for_you'] = $this->offersForYou();

            $finalData['total_cart'] = Cart::where('user_id', auth()->id())->count();

            $status = 1;
        } catch (\Exception $e) {
            $status = 0;
            $finalData = $e->getMessage();
        }

        return ['status' => $status, 'data' => $finalData];
    }

    public function userData()
    {
        $user = User::select('name', 'phone', 'image', 'status')->find(auth()->id());

        if (!$user) {
            return null;
        }

        return [
            'name' => $user->name,
            'phone' => $user->phone,
            'image' => $user->image == 'avatar.jpg'
                ? url('/img/avatar.jpg')
                : url('storage/user/' . $user->image),
        ];
    }

    public function addressData()
    {
        $address = Address::where('user_id', auth()->id())
            ->where('is_default', true)
            ->first();

        if (!$address) return null;

        return [
            'city' => $address->city,
            'district' => $address->district,
            'pincode' => $address->pincode
        ];
    }

    public function banner()
    {
        return Banner::where('status', Status::ACTIVE)
            ->get()
            ->map(fn($b) => url("storage/banner/" . $b->image));
    }

    public function getCategories()
    {
        return Category::where('status', Status::ACTIVE)
            ->orderBy('sort_order')
            ->limit(10)
            ->get()
            ->unique('id')
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => $item->image_url,
                ];
            });
    }


    public function getBrands()
    {
        return Brand::where('status', Status::ACTIVE)
            ->orderBy('sort_order')
            ->limit(10)
            ->get()
            ->unique('id')
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => $item->image_url,
                ];
            });
    }

    public function getLatestProducts()
    {
        $userId = auth()->id();

        return Product::approvedWithShop()
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->unique('id')
            ->values()
            ->map(function ($product) use ($userId) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image ? $product->image_url : url('img/avatar.jpg'),
                    'wishlist' => $product->is_liked,
                    'available_stock' => $product->available_stock,
                    'total_stock' => $product->product_stock,
                    'amount' => $product->amount,
                    'discount_amount' => $product->discount_amount,
                    'discount_percentage' => $product->discount_percentage,
                    'avg_rating' => (string)round($product->feedbacks()->avg('rating'), 1),
                    'total_rating' => $product->feedbacks()->count(),
                    'cart_quantity' => (int)Cart::where('user_id', $userId)
                        ->where('product_id', $product->id)
                        ->pluck('quantity')
                        ->first() ?? 0,
                ];
            });
    }

    public function bestSelling($request)
    {
        $userId     = auth()->id();
        $search     = $request->input('search');
        $categoryId = $request->input('category_id');
        $brandId    = $request->input('brand_id');
        $sortBy     = $request->input('sort_by');
        $perPage    = $request->input('per_page', 10);

        $productIds = OrderItem::select('product_id', DB::raw('COUNT(*) as total'))
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->pluck('product_id');

        $cartItems = Cart::where('user_id', $userId)
            ->pluck('quantity', 'product_id');

        $totalCartQuantity = Cart::where('user_id', $userId)->count();

        $products = Product::approvedWithShop()
            ->whereIn('id', $productIds)

            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('keywords', 'LIKE', "%{$search}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'LIKE', "%{$search}%"))
                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'LIKE', "%{$search}%"));
                });
            })

            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($brandId, fn ($q) => $q->where('brand_id', $brandId))

            ->when($sortBy, function ($q) use ($sortBy) {
                if ($sortBy === 'new') {
                    $q->orderByDesc('id');
                }

                if ($sortBy === 'price_low') {
                    $q->orderByRaw('COALESCE(discount_amount, amount) ASC');
                }

                if ($sortBy === 'price_high') {
                    $q->orderByRaw('COALESCE(discount_amount, amount) DESC');
                }

                if ($sortBy === 'rating') {
                    $q->withAvg('feedbacks as avg_rating', 'rating')
                    ->orderByDesc('avg_rating');
                }
            })

            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->paginate($perPage);

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
                ],
            ],
        ]);
    }

    public function topDeals($request)
    {
        $userId     = auth()->id();
        $search     = $request->input('search');
        $categoryId = $request->input('category_id');
        $brandId    = $request->input('brand_id');
        $sortBy     = $request->input('sort_by', 'discount');
        $perPage    = $request->input('per_page', 10);

        $cartItems = Cart::where('user_id', $userId)
            ->pluck('quantity', 'product_id');

        $totalCartQuantity = Cart::where('user_id', $userId)->count();

        $products = Product::approvedWithShop()

            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('keywords', 'LIKE', "%{$search}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'LIKE', "%{$search}%"))
                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'LIKE', "%{$search}%"));
                });
            })

            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($brandId, fn ($q) => $q->where('brand_id', $brandId))

            ->when($sortBy, function ($q) use ($sortBy) {
                if ($sortBy === 'discount') {
                    $q->orderByDesc('discount_percentage');
                }

                if ($sortBy === 'price_low') {
                    $q->orderByRaw('COALESCE(discount_amount, amount) ASC');
                }

                if ($sortBy === 'price_high') {
                    $q->orderByRaw('COALESCE(discount_amount, amount) DESC');
                }

                if ($sortBy === 'rating') {
                    $q->withAvg('feedbacks as avg_rating', 'rating')
                    ->orderByDesc('avg_rating');
                }
            })

            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->paginate($perPage);

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
                ],
            ],
        ]);
    }

    public function latestProducts()
    {
        $userId = auth()->id();

        $cartItems = Cart::where('user_id', $userId)
            ->pluck('quantity', 'product_id');

        $totalCartQuantity = Cart::where('user_id', $userId)->count();

        $products = Product::approvedWithShop()
            ->orderByDesc('id')
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
            ]
        ]);
    }

    public function lowestPriceProducts()
    {
        return SubCategory::with(['product' => function ($q) {
            $q->approvedWithShop()
                ->whereNotNull('amount')
                ->orderBy('amount', 'asc');
        }])
            ->where('status', Status::ACTIVE)
            ->limit(10)
            ->get()
            ->map(function ($sub) {

                $products = $sub->product ?? collect();

                if ($products->count() == 0) {
                    return null;
                }

                $lowestPrice = $products->min('amount');

                $priceSlab = $this->generatePriceSlabName($lowestPrice);

                $products->take(5)->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'image' => $p->image ? $p->image_url : url('img/avatar.jpg'),
                        'amount' => $p->amount,
                        'discount_amount' => $p->discount_amount,
                        'discount_percentage' => $p->discount_percentage,
                    ];
                });

                return [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'image' => $sub->image_url,
                    'lowest_price' => $lowestPrice,
                    'price_slab_name' => $priceSlab,
                    'total_products' => $products->count(),
                ];
            })
            ->filter()
            ->sortBy('lowest_price')
            ->values();
    }

    private function generatePriceSlabName($price)
    {
        if ($price <= 199) return "Below 199";
        if ($price <= 299) return "Below 299";
        if ($price <= 499) return "Below 499";
        if ($price <= 999) return "Below 999";
        if ($price <= 1999) return "Below 1999";

        return "Premium Products";
    }

    public function productsByLowestPriceSlab($subCategoryId, $slab)
    {
        $userId = auth()->id();

        $priceRange = $this->getPriceRange($slab);
        if (!$priceRange) {
            return [
                'status' => 0,
                'message' => 'Invalid price slab',
            ];
        }

        [$min, $max] = $priceRange;

        $products = Product::approvedWithShop()
            ->where('sub_category_id', $subCategoryId)
            ->whereBetween('amount', [$min, $max])
            ->orderBy('amount', 'asc')
            ->paginate(20)
            ->through(function ($product) use ($userId) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image ? $product->image_url : url('img/avatar.jpg'),
                    'wishlist' => $product->is_liked,
                    'available_stock' => $product->available_stock,
                    'total_stock' => $product->product_stock,
                    'amount' => (float)$product->amount,
                    'discount_amount' => (float)$product->discount_amount,
                    'discount_percentage' => (float)$product->discount_percentage,
                    'avg_rating' => (string) round($product->feedbacks()->avg('rating'), 1),
                    'total_rating' => $product->feedbacks()->count(),
                    'cart_quantity' => (int) Cart::where('user_id', $userId)
                        ->where('product_id', $product->id)
                        ->value('quantity') ?? 0,
                ];
            });

        $cartQuantity = (int)Cart::where('user_id', $userId)
            ->count() ?? 0;

        return [
            'status' => 1,
            'data' => [
                'total_cart' => $cartQuantity,
                'products' => $products->items(),
            ],
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'first_page_url' => $products->url(1),
            'last_page_url' => $products->url($products->lastPage()),
            'next_page_url' => $products->nextPageUrl(),
            'prev_page_url' => $products->previousPageUrl(),
        ];
    }

    private function getPriceRange($slab)
    {
        $slab = strtolower($slab);

        return match ($slab) {
            "below 199" => [0, 199],
            "below 299" => [0, 299],
            "below 499" => [0, 499],
            "below 999" => [0, 999],
            "below 1999" => [0, 1999],
            "premium products" => [2000, 999999],
            default => null,
        };
    }

    public function getTopDeals()
    {
        $userId = auth()->id();

        return Product::approvedWithShop()
            ->orderBy('discount_percentage', 'desc')
            ->limit(10)
            ->get()
            ->unique('id')
            ->values()
            ->map(function ($product) use ($userId) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image ? $product->image_url : url('img/avatar.jpg'),
                    'wishlist' => $product->is_liked,
                    'available_stock' => $product->available_stock,
                    'total_stock' => $product->product_stock,
                    'amount' => $product->amount,
                    'discount_amount' => $product->discount_amount,
                    'discount_percentage' => $product->discount_percentage,
                    'avg_rating' => (string)round($product->feedbacks()->avg('rating'), 1),
                    'total_rating' => $product->feedbacks()->count(),
                    'cart_quantity' => (int)Cart::where('user_id', $userId)
                        ->where('product_id', $product->id)
                        ->pluck('quantity')
                        ->first() ?? 0,
                ];
            });
    }


    public function getBestSellingProducts()
    {
        $userId = auth()->id();

        $productIds = OrderItem::select('product_id', DB::raw('COUNT(*) as total'))
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('product_id');

        $cartItems = Cart::where('user_id', $userId)
            ->pluck('quantity', 'product_id');

        return Product::approvedWithShop()
            ->whereIn('id', $productIds)
            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->get()
            ->map(function ($product) use ($cartItems) {
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
                    'cart_quantity' => (int) ($cartItems[$product->id] ?? 0),
                ];
            })
            ->values();
    }

    public function offersForYou()
    {
        return SubCategory::with(['product' => function ($q) {
            $q->approvedWithShop()
                ->where('discount_percentage', '>', 0)
                ->whereNotNull('discount_percentage')
                ->orderBy('discount_percentage', 'desc');
        }])
            ->where('status', Status::ACTIVE)
            ->limit(7)
            ->get()
            ->map(function ($sub) {
                $products = $sub->product ?? collect();

                $discountedProducts = $products->filter(function ($product) {
                    return $product->discount_percentage > 0;
                });

                $maxDiscount = $discountedProducts->max('discount_percentage');

                $offerName = $this->generateOfferName($maxDiscount);

                return [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'image' => $sub->image_url,
                    'offer_name' => $offerName,
                    'max_discount' => $maxDiscount ?: 0,
                    'total_offers' => $discountedProducts->count(),
                ];
            })
            ->filter(fn($item) => $item['total_offers'] > 0)
            ->sortByDesc('max_discount')
            ->values();
    }

    private function generateOfferName($discountPercentage)
    {
        if (!$discountPercentage) {
            return "Special Offers";
        }

        $discount = (int) $discountPercentage;

        if ($discount >= 70) {
            return "Mega Sale! Up to {$discount}% Off";
        } elseif ($discount >= 50) {
            return "Super Saver! Up to {$discount}% Off";
        } elseif ($discount >= 30) {
            return "Great Deals! Up to {$discount}% Off";
        } elseif ($discount >= 20) {
            return "Hot Offers! Up to {$discount}% Off";
        } else {
            return "Save {$discount}% Off";
        }
    }

    public function productsByOfferPercentage($subCategoryId, $percentage)
    {
        $userId = auth()->id();

        $products = Product::approvedWithShop()
            ->where('sub_category_id', $subCategoryId)
            ->where('discount_percentage', 'like', "%{$percentage}%")
            ->paginate(20)
            ->through(function ($product) use ($userId) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image ? $product->image_url : url('img/avatar.jpg'),
                    'wishlist' => $product->is_liked,
                    'available_stock' => $product->available_stock,
                    'total_stock' => $product->product_stock,
                    'amount' => $product->amount,
                    'discount_amount' => $product->discount_amount,
                    'discount_percentage' => $product->discount_percentage,
                    'avg_rating' => (string) round($product->feedbacks()->avg('rating'), 1),
                    'total_rating' => (int) $product->feedbacks()->count(),
                    'cart_quantity' => (int) Cart::where('user_id', $userId)
                        ->where('product_id', $product->id)
                        ->value('quantity') ?? 0,
                ];
            });

        $cartQuantity = (int)Cart::where('user_id', $userId)->count() ?? 0;

        return [
            'status' => 1,
            'data' => [
                'total_cart' => $cartQuantity,
                'products' => $products->items(),
            ],
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'first_page_url' => $products->url(1),
            'last_page_url' => $products->url($products->lastPage()),
            'next_page_url' => $products->nextPageUrl(),
            'prev_page_url' => $products->previousPageUrl(),
        ];
    }

    public function profileUpdate(array $data)
    {
        DB::beginTransaction();
        $user = auth()->user();
        if ($data['image'] ?? null) {
            $data['image'] = $this->saveFile($data['image'], 'user');
            if ($user['image']) {
                Storage::disk('public')->delete('user/' . $user['image']);
            }
        }
        $user->fill($data);
        $user->save();
        DB::commit();

        return response()->json([
            'status' => 1,
            'message' => 'Profile updated.',
        ]);
    }

    public function saveFile($file, $path)
    {
        $image = null;
        if ($file->isValid()) {
            $imageName = time() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->putFileAs($path, $file, $imageName);
            return $imageName;
        }
        return $image;
    }

    public function coupon($request)
    {
        $user = auth()->user();
        $today = now();
        $perPage = (int) $request->input('per_page', 10);

        $cartAmount = (float) Cart::where('carts.user_id', $user->id)
            ->join('products', 'products.id', '=', 'carts.product_id')
            ->sum(DB::raw(
                'carts.quantity * COALESCE(NULLIF(products.discount_amount, 0), products.amount)'
            ));

        $usedCouponIds = Order::where('user_id', $user->id)
            ->whereNotNull('coupon_id')
            ->pluck('coupon_id')
            ->toArray();

        $coupons = Coupon::where('status', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('expire_date', '>=', $today)
            ->orderBy('expire_date', 'asc')
            ->paginate($perPage);

        $coupons->getCollection()->transform(function ($coupon) use (
            $usedCouponIds,
            $today,
            $cartAmount
        ) {
            $used = in_array($coupon->id, $usedCouponIds);
            $isExpired = $today->greaterThan($coupon->expire_date);

            $usageLimitReached = $coupon->usage_limit !== null
                && $coupon->used_count >= $coupon->usage_limit;

            $minPurchaseNotMet = false;
            if ($cartAmount > 0 && $coupon->min_purchase_amount) {
                $minPurchaseNotMet = $cartAmount < $coupon->min_purchase_amount;
            }

            $invalid = $used || $isExpired || $usageLimitReached || $minPurchaseNotMet;

            if ($used) {
                $eligibilityStatus = 'Already Used';
            } elseif ($isExpired) {
                $eligibilityStatus = 'Expired';
            } elseif ($usageLimitReached) {
                $eligibilityStatus = 'Usage Limit Reached';
            } elseif ($minPurchaseNotMet) {
                $eligibilityStatus = 'Minimum purchase not met';
            } else {
                $eligibilityStatus = 'Available';
            }

            return [
                'id' => $coupon->id,
                'name' => $coupon->name,
                'code' => $coupon->code,
                'amount' => $coupon->amount,
                'start_date' => Carbon::parse($coupon->start_date)->format('d M Y'),
                'expire_date' => Carbon::parse($coupon->expire_date)->format('d M Y'),
                'min_purchase_amount' => $coupon->min_purchase_amount,
                'description' => $coupon->description,
                'image' => $coupon->image ? url('storage/coupon/' . $coupon->image) : null,

                'used' => $used,
                'is_expired' => $isExpired,
                'usage_limit_reached' => $usageLimitReached,
                'min_purchase_not_met' => $minPurchaseNotMet,

                'invalid' => $invalid,
                'eligible' => !$invalid,
                'eligibility_status' => $eligibilityStatus,

                // 'available_uses' => $coupon->usage_limit !== null
                //     ? max(0, $coupon->usage_limit - $coupon->used_count)
                //     : 'Unlimited',

                // 'total_uses' => $coupon->used_count,
                // 'usage_limit' => $coupon->usage_limit,
                // 'status_text' => $invalid ? 'Not Available' : 'Available',
            ];
        });

        $coupons->setCollection(
            $coupons->getCollection()->sortBy('invalid')->values()
        );

        return response()->json([
            'status' => 1,
            'data' => $coupons->items(),
            'pagination' => [
                'current_page'   => $coupons->currentPage(),
                'last_page'      => $coupons->lastPage(),
                'per_page'       => $coupons->perPage(),
                'total'          => $coupons->total(),
                'from'           => $coupons->firstItem(),
                'to'             => $coupons->lastItem(),
                'first_page_url' => $coupons->url(1),
                'last_page_url'  => $coupons->url($coupons->lastPage()),
                'next_page_url'  => $coupons->nextPageUrl(),
                'prev_page_url'  => $coupons->previousPageUrl(),
            ]
        ]);
    }

    public function address()
    {
        try {
            $user = Auth::user();

            $addresses = Address::where('user_id', $user->id)->get();

            $addresses = $addresses->map(function ($address) use ($user) {
                $address->name = $address->name ?? $user->name;
                $address->phone = $address->phone ?? $user->phone;
                return $address;
            });

            return response()->json([
                'status' => 1,
                'message' => $addresses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Unable to retrieve address information at this time.',
            ], 500);
        }
    }

    public function addressPost(array $data)
    {
        DB::beginTransaction();

        try {
            $data['user_id'] = Auth::id();

            $existingAddress = Address::where('user_id', $data['user_id'])
                ->where('flat_no', $data['flat_no'] ?? null)
                ->where('area_name', $data['area_name'] ?? null)
                ->where('street', $data['street'] ?? null)
                ->where('city', $data['city'] ?? null)
                ->where('district', $data['district'] ?? null)
                ->where('pincode', $data['pincode'] ?? null)
                ->where('address_type', $data['address_type'] ?? null)
                ->where('name', $data['name'])
                ->where('phone', $data['phone'])
                ->first();

            if ($existingAddress) {
                return response()->json([
                    'status' => 0,
                    'message' => 'This address already saved.',
                ]);
            }

            $address = new Address();
            $data['name'] = $data['name'] ?? auth()->user()->name;
            $data['phone'] = $data['phone'] ?? auth()->user()->phone;
            $address->fill($data);
            $address->save();

            if ($address->is_default) {
                Address::where('user_id', Auth::id())
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Address saved',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Save failed',
            ], 500);
        }
    }

    public function addressEdit($id)
    {
        try {
            $user = Auth::user();

            $address = Address::where('user_id', $user->id)->where('id', $id)->first();

            if (!$address) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Address not found. Please check and try again.',
                ], 404);
            }

            $address->name = $address->name ?? $user->name;
            $address->phone = $address->phone ?? $user->phone;

            return response()->json([
                'status' => 1,
                'message' => $address,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Unable to retrieve address information at this time.',
            ], 500);
        }
    }

    public function addressUpdate(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $address = Address::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

            $address->fill($data);
            $address->save();

            if ($address->is_default) {
                Address::where('user_id', Auth::id())
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Address updated.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'update failed.',
            ], 500);
        }
    }

    public function addressDelete($id)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            $totalAddresses = Address::where('user_id', $userId)->count();

            if ($totalAddresses <= 1) {
                return response()->json([
                    'status' => 0,
                    'message' => 'One address required',
                ], 400);
            }

            $address = Address::where('user_id', $userId)->where('id', $id)->firstOrFail();

            if ($address->is_default) {
                $anotherAddress = Address::where('user_id', $userId)
                    ->where('id', '!=', $address->id)
                    ->first();

                if ($anotherAddress) {
                    $anotherAddress->is_default = true;
                    $anotherAddress->save();
                }
            }

            $address->delete();

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Address deleted.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'delete address failed.',
            ], 500);
        }
    }

    public function shopChangeGet()
    {
        try {
            $address = Address::where('user_id', Auth::id())->where('is_default', true)->firstOrFail();
            $radius = Application::where('group', 'Shop Radius')->pluck('value')->first() ?? 10;
            $shops = Shop::select('id', 'name', 'latitude', 'longitude', 'address', 'image')
                ->selectRaw(
                    "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                    [$address->lat, $address->long, $address->lat]
                )
                ->having('distance', '<=', $radius)
                ->orderBy('distance')
                ->get();

            $shops->transform(function ($shop) {
                $shop->image = url('storage/shop/' . $shop->image);
                return $shop;
            });

            return response()->json([
                'status' => 1,
                'message' => 'Shop fetched successfully.',
                'data' => $shops,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Failed to get nearby shops.',
                'data' => [],
            ], 500);
        }
    }

    public function shopChangePost($request)
    {
        $user = auth()->user();

        $request->validate([
            'shop_id' => 'required|exists:shops,id',
        ]);

        $user->shop_id = $request->shop_id;
        $user->save();

        return response()->json([
            'status' => 1,
            'message' => 'Shop changed successfully.',
        ]);
    }

    public function wishlist(array $data)
    {
        $userId = auth()->id();
        $productId = $data['product_id'] ?? null;

        if (!$productId) {
            return response()->json([
                'status' => 0,
                'message' => 'Product ID is required.',
                'wishlist' => false
            ]);
        }

        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'status' => 0,
                'message' => 'Product not found.',
                'wishlist' => false
            ]);
        }

        try {
            $wishlist = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($wishlist) {
                $wishlist->delete();

                return response()->json([
                    'status' => 1,
                    'message' => "{$product->name} removed.",
                    'wishlist' => false
                ]);
            }

            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);

            return response()->json([
                'status' => 1,
                'message' => "{$product->name} added.",
                'wishlist' => true
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong.',
                'wishlist' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function wishlistShow($request)
    {
        $userId = auth()->id();
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $query = Wishlist::where('user_id', $userId)
            ->with(['product' => function ($query) {
                $query->where('status', Status::ACTIVE)
                    ->where('approved', true)
                    ->with(['feedbacks']);
            }]);

        $wishlists = $query->paginate($perPage, ['*'], 'page', $page);

        $mappedProducts = $wishlists->through(function ($wishlist) use ($userId) {
            $product = $wishlist->product;

            if (!$product) {
                return null;
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image ? $product->image_url : url('img/avatar.jpg'),
                'wishlist' => $product->is_liked,
                'available_stock' => $product->available_stock,
                'total_stock' => $product->product_stock,
                'amount' => $product->amount,
                'discount_amount' => $product->discount_amount,
                'discount_percentage' => $product->discount_percentage,
                'avg_rating' => (string) round($product->feedbacks()->avg('rating') ?? 0, 1),
                'total_rating' => (int) $product->feedbacks()->count(),
                'cart_quantity' => (int) Cart::where('user_id', $userId)
                    ->where('product_id', $product->id)
                    ->value('quantity') ?? 0,
            ];
        })->filter()->values();

        return response()->json([
            'status' => 1,
            'total_cart' => Cart::where('user_id', auth()->id())->count(),
            'data' => $mappedProducts,
            'current_page' => $wishlists->currentPage(),
            'last_page' => $wishlists->lastPage(),
            'per_page' => $wishlists->perPage(),
            'total' => $wishlists->total(),
            'first_page_url' => $wishlists->url(1),
            'last_page_url' => $wishlists->url($wishlists->lastPage()),
            'next_page_url' => $wishlists->nextPageUrl(),
            'prev_page_url' => $wishlists->previousPageUrl(),
        ]);
    }

    public function defaultAddress($request, $id)
    {
        $orderService = new OrderService();
        $orderService->setDefaultAddress(auth()->id(), $id);

        return response()->json([
            'status' => 1,
            'message' => 'Current address updated.'
        ]);
    }

    public function orderStatus()
    {
        $statuses = [
            "pending"    => "Pending",
            "processing" => "Processing",
            "shipped"    => "Shipped",
            "completed"  => "Completed",
            "cancelled"  => "Cancelled",
            "returned"   => "Returned",
            "replaced"   => "Replaced",
        ];

        return response()->json([
            'status' => 1,
            'data'   => array_values($statuses),
        ]);
    }

    public function search($request)
    {
        $search      = $request->input('search');
        $categoryId  = $request->input('category_id');
        $brandId     = $request->input('brand_id');
        $sortBy      = $request->input('sort_by');
        $status      = $request->input('status', true);
        $perPage     = $request->input('per_page', 20);
        $page        = $request->input('page', 1);

        $userId = auth()->id();

        $query = Product::approvedWithShop()
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($brandId, fn($q) => $q->where('brand_id', $brandId))
            ->when($search, function ($query) use ($search) {
                $query->where(
                    fn($q) => $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('keywords', 'LIKE', "%{$search}%")
                        ->orWhereHas('category', fn($q2) => $q2->where('name', 'LIKE', "%{$search}%")->where('status', true))
                        ->orWhereHas('subCategory', fn($q3) => $q3->where('name', 'LIKE', "%{$search}%")->where('status', true))
                        ->orWhereHas('brand', fn($q4) => $q4->where('name', 'LIKE', "%{$search}%")->where('status', true))
                );
            })
            ->when($sortBy, function ($q) use ($sortBy) {
                if ($sortBy === "new") $q->orderBy("id", "DESC");
                if ($sortBy === "price_low") $q->orderByRaw('COALESCE(discount_amount, amount) ASC');
                if ($sortBy === "price_high") $q->orderByRaw('COALESCE(discount_amount, amount) DESC');
                if ($sortBy === "rating") $q->withAvg('feedbacks as avg_rating', 'rating')->orderBy('avg_rating', 'DESC');
            });

        $products = $query->paginate($perPage, ['*'], 'page', $page);

        $mappedProducts = $products->through(function ($product) use ($userId) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image ? $product->image_url : url('img/avatar.jpg'),
                'wishlist' => $product->is_liked,
                'available_stock' => $product->available_stock,
                'total_stock' => $product->product_stock,
                'amount' => $product->amount,
                'discount_amount' => $product->discount_amount,
                'discount_percentage' => $product->discount_percentage,
                'avg_rating' => (string)round($product->feedbacks()->avg('rating'), 1),
                'total_rating' => (int)$product->feedbacks()->count(),
                'cart_quantity' => (int)Cart::where('user_id', $userId)
                    ->where('product_id', $product->id)
                    ->value('quantity') ?? 0,
            ];
        });

        return response()->json([
            'status' => 1,
            'message' => "Product List",
            'data' => $mappedProducts->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'first_page_url' => $products->url(1),
            'last_page_url' => $products->url($products->lastPage()),
            'next_page_url' => $products->nextPageUrl(),
            'prev_page_url' => $products->previousPageUrl(),
        ]);
    }

    public function brandList()
    {
        $brands = Brand::where('status', Status::ACTIVE)
            ->orderBy('sort_order')
            ->get()
            ->unique('id')
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => $item->image_url,
                ];
            });

        return response()->json([
            'status' => 1,
            'data' => $brands,
        ]);
    }

    public function brandDetails($brandId)
    {
        $userId = auth()->id();

        $brand = Brand::where('id', $brandId)
            ->where('status', true)
            ->first();

        if (!$brand) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Brand not found.'
            ], 404);
        }

        $banners = $brand->banners
            ? $brand->banners->pluck('image_url')->values()
            : [];

        $cartItems = Cart::where('user_id', $userId)
            ->pluck('quantity', 'product_id');

        $totalCartQuantity = Cart::where('user_id', $userId)->count();

        $products = Product::approvedWithShop()
            ->where('brand_id', $brandId)
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
                'brand_name' => $brand->name,
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

    public function wallet($request)
    {
        $user = auth()->user();

        $wallet = Wallet::getUserWallet($user->id);
        $limit = $request->input('page', 20);

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'transaction_id' => $transaction->id,
                'date_time' => $transaction->formatted_date,
                'amount' => $transaction->formatted_amount,
                'type' => ucfirst($transaction->type),
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => [
                'wallet' => (float) $wallet->balance,
                'transactions' => $formattedTransactions,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page_url' => $transactions->url($transactions->currentPage()),
                    'next_page_url' => $transactions->nextPageUrl(),
                    'prev_page_url' => $transactions->previousPageUrl(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ]
            ]
        ]);
    }

    public function notification($request)
    {
        $user = auth()->user();

        $limit = $request->input('page', 20);

        $notifications = Notification::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type_label' => $notification->notification_type_label,
                'title' => $notification->title,
                'message' => $notification->message,
                'time_ago' => $notification->time_ago,
                'date_time' => $notification->formatted_date,
            ];
        });

        $unreadCount = Notification::unreadCount($user->id);

        Notification::markAllAsRead($user->id);

        return response()->json([
            'status' => 1,
            'data' => [
                'unread_count' => $unreadCount,
                'notifications' => $formattedNotifications,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page_url' => $notifications->url($notifications->currentPage()),
                    'next_page_url' => $notifications->nextPageUrl(),
                    'prev_page_url' => $notifications->previousPageUrl(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                ]
            ]
        ]);
    }

    public function cancellationReasons()
    {
        $reasons = CancellationReason::where('is_show', true)->get(['id', 'reason']);
        return response()->json(['status' => 1, 'data' => $reasons]);
    }

    public function userAccountReasons()
    {
        $reasons = UserAccountReason::where('is_show', true)->get(['id', 'reason']);
        return response()->json(['status' => 1, 'data' => $reasons]);
    }

    public function returnOrderReasons()
    {
        $reasons = ReturnOrderReason::where('is_show', true)->get(['id', 'reason']);
        return response()->json(['status' => 1, 'data' => $reasons]);
    }

    public function replaceOrderReasons()
    {
        $reasons = ReplaceOrderReason::where('is_show', true)->get(['id', 'reason']);
        return response()->json(['status' => 1, 'data' => $reasons]);
    }

    public function categorySelectBoxList()
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['status' => 1, 'data' => $categories]);
    }

    public function brandSelectBoxList()
    {
        $brands = Brand::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['status' => 1, 'data' => $brands]);
    }

    public function filterOptions()
    {
        return response()->json([
            'status' => 1,
            'data' => [
                'sort_by' => [
                    ['id' => 'new', 'name' => 'Newest'],
                    ['id' => 'price_low', 'name' => 'Price Low to High'],
                    ['id' => 'price_high', 'name' => 'Price High to Low'],
                    ['id' => 'rating', 'name' => 'Top Rated'],
                ]
            ]
        ]);
    }

}
