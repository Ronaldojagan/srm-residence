<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Service\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    private $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->middleware('auth');
        $this->bannerService = $bannerService;
    }

    public function index(Request $request)
    {
        return $this->bannerService->index($request);
    }
    public function store(SaveBannerRequest $request){
        return $this->bannerService->store($request->validated());
    }
    public function edit(Banner $banner){
        return $this->bannerService->edit($banner);
    }
    public function update(UpdateBannerRequest $request, Banner $banner){
        return $this->bannerService->update($request->validated(),$banner);
    }
    public function destroy(Banner $banner){
        return $this->bannerService->destroy($banner);
    }
     public function updateStatus(Request $request)
    {
        return $this->bannerService->updateStatus($request);
    }

    public function getSubcategories($id)
    {
        $query = SubCategory::where('status', 1);

        if ($id) {
            $query->where('category_id', $id);
        }

        $subcategories = $query->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json($subcategories);
    }

}
