<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProductRequest;
use App\Http\Requests\SaveStockRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUpdateRequest;
use App\Models\SubCategory;
use App\Service\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->middleware('auth');
        $this->productService = $productService;
    }
    public function create(Request $request)
    {
        $category = Category::where('status', true)->get();
        $brand = Brand::where('status', true)->get();
        return view('backend.product.product', compact('category', 'brand'));
    }
    public function getSubcategories($categoryId)
    {
        $subcategories = SubCategory::where('category_id', $categoryId)->where('status', true)->get();
        return response()->json($subcategories);
    }
    public function index(Request $request)
    {
        return $this->productService->index($request);
    }
    public function pendingRequests(Request $request)
    {
        return $this->productService->pendingRequests($request);
    }
    public function store(SaveProductRequest $request){
        return $this->productService->store($request->validated());
    }
    public function edit(Product $product) {
        $category = Category::where('status', true)->get();
        $brand = Brand::where('status', true)->get();
        return view('backend.product.product', [
            'edit'=>$product,
            'category'=>$category,
            'brand'=>$brand,
        ]);
    }
    public function update(UpdateProductRequest $request, Product $product){
        return $this->productService->update($request->validated(), $product);
    }
    public function destroy(Product $product){
        return $this->productService->destroy($product);
    }
    public function view(Product $product){
        return $this->productService->view($product);
    }
    public function stockHistory(Request $request, Product $product){
        return $this->productService->stockHistory($request, $product);
    }
    public function addStock(SaveStockRequest $request, Product $product){
        return $this->productService->addStock($request->validated(), $product);
    }
    public function updateStatus(Request $request)
    {
        return $this->productService->updateStatus($request);
    }
    public function getCounts(Request $request)
    {
        return $this->productService->getCounts($request);
    }

    public function reject(Request $request)
    {
        return $this->productService->reject($request);
    }
    public function approve(Request $request)
    {
        return $this->productService->approve($request);
    }

    public function approveUpdateRequest(Request $request, ProductUpdateRequest $productUpdateRequest)
    {
        return $this->productService->approveUpdateRequest($request, $productUpdateRequest);
    }

    public function rejectUpdateRequest(Request $request, ProductUpdateRequest $productUpdateRequest)
    {
        return $this->productService->rejectUpdateRequest($request, $productUpdateRequest);
    }

    public function getUpdateRequest(Request $request, ProductUpdateRequest $productUpdateRequest)
    {
        return $this->productService->getUpdateRequest($request, $productUpdateRequest);
    }
}
