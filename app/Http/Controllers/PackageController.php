<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\Package;
use App\Service\PackageService;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    private $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->middleware('auth');
        $this->packageService = $packageService;
    }
    public function create(Request $request)
    {
        return view('backend.package.package');
    }
    public function index(Request $request)
    {
        return $this->packageService->index($request);
    }
    public function store(SavePackageRequest $request){
        return $this->packageService->store($request->validated());
    }
    public function edit(Package $package) {
        return view('backend.package.package', [
            'edit'=>$package,
        ]);
    }
    public function update(UpdatePackageRequest $request, Package $package){
        return $this->packageService->update($request->validated(), $package);
    }
    public function destroy(Package $package){
        return $this->packageService->destroy($package);
    }
    public function view(Package $package){
        return $this->packageService->view($package);
    }

    public function bulkStatus(Request $request)
    {
        return $this->packageService->bulkStatus($request);
    }
    public function bulkDelete(Request $request)
    {
        return $this->packageService->bulkDelete($request);
    }
    public function updateOrder(Request $request)
    {
        return $this->packageService->updateOrder($request);
    }
    public function updateStatus(Request $request)
    {
        return $this->packageService->updateStatus($request);
    }

}
