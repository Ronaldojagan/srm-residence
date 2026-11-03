<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Application;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Service\Api\User\ProductService;

class ProductController extends Controller
{
    private $productService;
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function products(Request $request)
    {
        return $this->productService->products($request);
    }

    public function productDetail($productId)
    {
        return $this->productService->productDetail($productId);
    }
}
