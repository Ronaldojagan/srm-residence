<?php

namespace App\Http\Controllers\Api\Shop;

use App\Enums\ProductFilterEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Shop\SaveProductDescriptionRequest;
use App\Http\Requests\Api\Shop\SaveProductDetailsUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Shop\SaveProductRequest;
use App\Http\Requests\Api\Shop\SaveProductPriceRequest;
use App\Http\Requests\Api\Shop\SaveProductStockRequest;
use App\Http\Requests\Api\Shop\SaveShopDetailsUpdateRequest;
use App\Models\Shop;
use App\Service\Api\Shop\ProductService;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->middleware('auth');
        $this->productService = $productService;
    }

   public function getProductList(Request $request)
    {
        $user = auth()->user();

        if (!$user->shop) {
            return response()->json([
                'status' => 0,
                'message' => 'Shop not found'
            ], 404);
        }

        $limit      = $request->input('limit', 10);
        $search     = $request->input('search');
        $orderBy    = $request->input('orderBy', 'id');
        $orderDir   = $request->input('orderDir', 'desc');
        $categoryId = $request->input('category_id');
        $filter     = $request->input('filter');

        $query = Product::where('shop_id', $user->shop->id);

        $categoryIds = (clone $query)->distinct()->pluck('category_id');
        $categoryFilterData = $this->getCategoryFilterData($categoryIds, $categoryId);

        if ($search) {
            $query->search($search);
        }

        if (!is_null($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!is_null($filter)) {
            $query = $this->applyFilter($query, $filter);
        } else {
            $query->orderBy($orderBy, $orderDir);
        }

        $products = $query->paginate($limit);

        $products->getCollection()->transform(function ($product) {
            return [
                'id'                     => $product->id,
                'category_id'            => $product->category_id,
                'name'                   => $product->name,
                'product_stock'          => $product->product_stock,
                'max_product_stock_alert'=> $product->max_product_stock_alert,
                'approved_label' => match ($product->approved) {
                    1   => 'approved',
                    -1  => 'unapproved',
                    2   => 'rejected',
                    default => 'unknown',
                },
                'image_url'              => $product->image_url,
            ];
        });

        return response()->json([
            'status' => 1,
            'message' => 'Product List',
            'filter_data' => $categoryFilterData,
            'data' => $products->items(),
            'pagination' => [
                'current_page'      => $products->currentPage(),
                'last_page'         => $products->lastPage(),
                'total'             => $products->total(),
                'per_page'          => $products->perPage(),
                'current_page_url'  => $products->url($products->currentPage()),
                'next_page_url'     => $products->nextPageUrl(),
                'prev_page_url'     => $products->previousPageUrl(),
            ]
        ], 200);
    }

    public function getCategoryFilterData($categoryIds, $category_id = null)
    {
        return [
            'categories' => Category::whereIn('id', $categoryIds)
                ->where('status', true)
                ->get(['id', 'name', 'image']),
            'active_filter_category_id' => $category_id
        ];
    }

    public function applyFilter($query, $filter)
    {
        switch ($filter) {
            case ProductFilterEnum::RECENTLY_ADDED:
                $query->orderBy('id', 'desc');
                break;
            case ProductFilterEnum::LOW_QUANTITY:
                $query->orderBy('product_stock', 'asc');
                break;
            case ProductFilterEnum::HIGH_QUANTITY:
                $query->orderBy('product_stock', 'desc');
                break;
            case ProductFilterEnum::UNAPPROVED:
                $query->where('approved', -1);
                break;
            case ProductFilterEnum::ACTIVE:
                $query->where('status', true);
                break;
            case ProductFilterEnum::INACTIVE:
                $query->where('status', false);
                break;
        }
        return $query;
    }

    public function store(SaveProductRequest $request){
        return $this->productService->store($request->validated());
    }

    public function updateStatus(Request $request, Product $product){
        return $this->productService->updateStatus($request, $product);
    }

    public function productsDetail($productId)
    {
        return $this->productService->productsDetail($productId);
    }

    public function updatePriceStatus(SaveProductPriceRequest $request, Product $product){
        return $this->productService->updatePriceStatus($request->validated(), $product);
    }

    public function updateStock(SaveProductStockRequest $request, Product $product){
        return $this->productService->updateStock($request->validated(), $product);
    }

    public function updateDescriptionRequestSend(SaveProductDescriptionRequest $request, Product $product)
    {
        return $this->productService->updateDescriptionRequestSend($request->validated(), $product);
    }

    public function updateDetailsRequestSend(SaveProductDetailsUpdateRequest $request, Product $product)
    {
        return $this->productService->updateDetailsRequestSend($request->validated(), $product);
    }

    public function updateShopDetails(SaveShopDetailsUpdateRequest $request, Shop $shop)
    {
        return $this->productService->updateShopDetails($request->validated(), $shop);
    }

    public function productStockHistory($productId)
    {
        return $this->productService->productStockHistory($productId);
    }
}
