<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSubCategoryRequest;
use App\Http\Requests\UpdateSubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Service\SubCategoryService;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    private $categoryService;

    public function __construct(SubCategoryService $categoryService)
    {
        $this->middleware('auth');
        $this->categoryService = $categoryService;
    }
    public function create(Request $request, Category $category)
    {
        return view('backend.category.sub_category', compact('category'));
    }
    public function index(Request $request, Category $category)
    {
        return $this->categoryService->index($request, $category);
    }
    public function store(SaveSubCategoryRequest $request){
        return $this->categoryService->store($request->validated());
    }
    public function edit(SubCategory $subCategory) {
        $category = Category::where('id', $subCategory->category_id)->first();
        return view('backend.category.sub_category', [
            'edit'=>$subCategory,
            'category'=>$category,
        ]);
    }
    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory){
        return $this->categoryService->update($request->validated(), $subCategory);
    }
    public function destroy(SubCategory $subCategory){
        return $this->categoryService->destroy($subCategory);
    }

     public function bulkStatus(Request $request)
    {
        return $this->categoryService->bulkStatus($request);
    }
    public function bulkDelete(Request $request)
    {
        return $this->categoryService->bulkDelete($request);
    }
    public function updateOrder(Request $request)
    {
        return $this->categoryService->updateOrder($request);
    }
    public function updateStatus(Request $request)
    {
        return $this->categoryService->updateStatus($request);
    }

}
