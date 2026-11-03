<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDeliveryPointsRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Requests\UpdateDeliveryPointsRequest;
use App\Models\Brand;
use App\Models\DeliveryPoint;
use App\Service\DeliveryPointService;
use Illuminate\Http\Request;

class DeliveryPointsController extends Controller
{
    private $deliveryPointsService;

    public function __construct(DeliveryPointService $deliveryPointsService)
    {
        $this->middleware('auth');
        $this->deliveryPointsService = $deliveryPointsService;
    }
    public function create(Request $request)
    {
        return view('backend.delivery_points.delivery_points');
    }
    public function index(Request $request)
    {
        return $this->deliveryPointsService->index($request);
    }
    public function store(SaveDeliveryPointsRequest $request){
        return $this->deliveryPointsService->store($request->validated());
    }
    public function edit(DeliveryPoint $deliveryPoint) {
        return view('backend.delivery_points.delivery_points', [
            'edit'=>$deliveryPoint,
        ]);
    }
    public function update(UpdateDeliveryPointsRequest $request, DeliveryPoint $deliveryPoint){
        return $this->deliveryPointsService->update($request->validated(), $deliveryPoint);
    }
    public function destroy(DeliveryPoint $deliveryPoint){
        return $this->deliveryPointsService->destroy($deliveryPoint);
    }
    public function view(DeliveryPoint $deliveryPoint){
        return $this->deliveryPointsService->view($deliveryPoint);
    }
    public function bulkStatus(Request $request)
    {
        return $this->deliveryPointsService->bulkStatus($request);
    }
    public function bulkDelete(Request $request)
    {
        return $this->deliveryPointsService->bulkDelete($request);
    }
    public function updateOrder(Request $request)
    {
        return $this->deliveryPointsService->updateOrder($request);
    }
    public function updateStatus(Request $request)
    {
        return $this->deliveryPointsService->updateStatus($request);
    }

}
