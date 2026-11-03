<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\Api\User\CategoryService;

class CategoryController extends Controller
{
    private $categoryService;
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function categories(Request $request)
    {
        return $this->categoryService->categories($request);
    }

    public function getSubCategories($categoryId)
    {
        return $this->categoryService->getSubCategories($categoryId);
    }

    public function getProductsBySubCategory($subCategoryId)
    {
        return $this->categoryService->getProductsBySubCategory($subCategoryId);
    }

    public function categoriesWithSubcategories(Request $request)
    {
        return $this->categoryService->categoriesWithSubcategories($request);
    }
}
