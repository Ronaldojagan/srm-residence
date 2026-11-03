<?php

namespace App\Service;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class CouponService
{
    public function index($request)
    {
        if ($request->ajax()) {
            $data = Coupon::orderBy('id','desc')->get();
            return DataTables::of($data)
            ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $btn = $row['created_at'];
                    return $btn;
                })
                ->addColumn('code', function ($row) {
                    $btn = $row->code ?? '-';
                    return $btn;
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" '.$checked.' data-id="'.Crypt::encrypt($row->id).'">
                    </div>';
                })
                ->addColumn('min_purchase_amount', function ($row) {
                    $btn = '₹'.number_format($row['min_purchase_amount'],2);
                    return $btn;
                })
                ->addColumn('amount', function ($row) {
                    $btn = '₹'.number_format($row['amount'],2);
                    return $btn;
                })
                ->addColumn('start_date', function ($row) {
                    $btn = Carbon::parse($row['start_date'])->format('d-m-Y');
                    return $btn;
                })
                ->addColumn('expire_date', function ($row) {
                    $btn = Carbon::parse($row['expire_date'])->format('d-m-Y');
                    return $btn;
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = url('/admin/coupons/' . Crypt::encrypt($row['id']));
                    $editUrl = url('/admin/coupons/' . Crypt::encrypt($row['id']) . '/edit');
                    $deleteId = Crypt::encrypt($row['id']);

                    $btn = '
                        <div class="d-flex gap-2 align-items-center">
                            <a href="' . $viewUrl . '" class="action-icon view-icon" title="View">
                                <img src="' . url('img/eye-icon.png') . '" alt="View" width="30">
                            </a>
                            <a href="' . $editUrl . '" class="action-icon edit-icon" title="Edit">
                                <img src="' . url('img/edit-icon.png') . '" alt="Edit" width="30">
                            </a>
                            <a href="javascript:void(0)" data-id="' . $deleteId . '" class="action-icon delete-icon text-danger delete" title="Delete">
                                <img src="' . url('img/delete-icon.png') . '" alt="Delete" width="30">
                            </a>
                        </div>';
                    return $btn;
                })
                ->rawColumns(['status','code','date','action','amount'])
                ->make(true);
        }
            return view('backend.coupon.coupon_index');
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        $coupon = new Coupon();
        $data['start_date'] = Carbon::createFromFormat('d-m-Y', $data['start_date'])->format('Y-m-d');
        $data['expire_date'] = Carbon::createFromFormat('d-m-Y', $data['expire_date'])->format('Y-m-d');
        if($data['image'] ?? null){
            $data['image'] = $this->saveFile($data['image']);
        }
        $coupon->fill($data);
        $coupon->save();
        DB::commit();
        session()->flash("success", "Coupon has been successfully created!");
        return redirect('/admin/coupons');
    }

    public function update(array $data, Coupon $coupon)
    {
        DB::beginTransaction();
        $data['start_date'] = Carbon::createFromFormat('d-m-Y', $data['start_date'])->format('Y-m-d');
        $data['expire_date'] = Carbon::createFromFormat('d-m-Y', $data['expire_date'])->format('Y-m-d');
        if($data['image'] ?? null){
            $data['image'] = $this->saveFile($data['image']);
        }
        $coupon->fill($data);
        $coupon->save();
        DB::commit();
        session()->flash("success", "Coupon has been successfully updated!");
        return redirect('/admin/coupons');
    }
    public function saveFile($data){
        $image = null;
        if($data->isValid()){
            $image=time().'_'.$data->getClientOriginalName();
            Storage::disk('public')->putFileAs('coupon/', $data, $image);
        }
        return $image;
    }
    public function destroy($coupon)
    {
        if($coupon->count() != 0){
            $coupon->delete();
            return ['status'=>'1'];
        }else{
            return ['status'=>'0'];
        }
    }

    public function view($coupon)
    {
        return view('backend.coupon.coupon_view', compact('coupon'));
    }

    public function updateStatus($request)
    {

        try {
            $id = Crypt::decrypt($request->id);
            $coupon = Coupon::findOrFail($id);

            $coupon->status = $request->status;
            $coupon->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: '.$e->getMessage()
            ], 500);
        }
    }


}
