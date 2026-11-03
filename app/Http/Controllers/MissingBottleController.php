<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\MissingBottle;
use App\Models\Order;
use App\Service\MissingBottleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class MissingBottleController extends Controller
{
    private $missingBottleService;

    public function __construct(MissingBottleService $missingBottleService)
    {
        $this->middleware('auth');
        $this->missingBottleService = $missingBottleService;
    }
    public function index(Request $request)
    {
        return $this->missingBottleService->index($request);
    }

    public function destroy($orderId)
    {
        return $this->missingBottleService->destroy(
            Crypt::decrypt($orderId)
        );
    }
    public function view($orderId){
        return $this->missingBottleService->view(Crypt::decrypt($orderId));
    }
    public function orderMissingBottleList(Order $order){
        return $this->missingBottleService->orderMissingBottleList($order);
    }
}
