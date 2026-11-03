<?php

namespace App\Service;

use App\Models\Brand;
use App\Models\DeliveryPoint;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class DeliveryPointService
{
    public function index($request)
    {
        if ($request->ajax()) {
            $data = DeliveryPoint::all();
            return DataTables::of($data)
                ->addIndexColumn()
                // ->addColumn('date', function ($row) {
                //     return $row->created_at->format('d-m-Y h:i A');
                // })
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="select-checkbox" value="' . Crypt::encrypt($row->id) . '" data-id="' . Crypt::encrypt($row->id) . '">';
                })
                // ->addColumn('sort_order', function ($row) {
                //     return '<span class="sort-order-handle" data-id="'.Crypt::encrypt($row->id).'"><i class="fas fa-arrows-alt"></i> '.$row->sort_order.'</span>';
                // })
                // ->addColumn('image', function ($row) {
                //     if ($row->image) {
                //         return '<img src="' . url('storage/brand/'.$row->image) . '" data-url="' . url('storage/brand/'.$row->image) . '" data-toggle="modal" data-target="#image-view" alt="brand-img" width="35px" height="35px" class="data-table-image edit open-image-model">';
                //     }
                //     return '<span class="badge bg-secondary">No Image</span>';
                // })
                ->addColumn('location', function ($row) {
                    return $row->location ?? '-';
                })
                ->addColumn('sub_location', function ($row) {
                    return $row->sub_location ?? '-';
                })
                // ->addColumn('product_count', function ($row) {
                //     return $row->product()->count();
                // })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" ' . $checked . ' data-id="' . Crypt::encrypt($row->id) . '">
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <div class="d-flex gap-2">
                            <a href="' . url('/admin/delivery-points/' . Crypt::encrypt($row->id)) . '" class="p-1" title="View">
                                <img src="' . url('img/eye-icon.png') . '" alt="View" width="30">
                            </a>
                            <a href="' . url('/admin/delivery-points/' . Crypt::encrypt($row->id) . '/edit') . '" class="p-1" title="Edit">
                                <img src="' . url('img/edit-icon.png') . '" alt="Edit" width="30">
                            </a>
                            <a href="javascript:void(0)" data-id="' . Crypt::encrypt($row->id) . '" class="p-1 delete" title="Delete">
                                <img src="' . url('img/delete-icon.png') . '" alt="Delete" width="30">
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'location','action', 'checkbox','sub_location'])
                ->make(true);
        }
        return view('backend.delivery_points.delivery_points_index');
    }
    public function store(array $data)
    {
        DB::beginTransaction();
        $deliveryPoints = new DeliveryPoint();

        // $lastBrand = Brand::orderBy('sort_order', 'desc')->first();
        // $data['sort_order'] = $lastBrand ? $lastBrand->sort_order + 1 : 1;
        $deliveryPoints->fill($data);
        $deliveryPoints->save();
        DB::commit();
        session()->flash("success", "Delivery Points has been successfully created!");
        return redirect('/admin/delivery-points');
    }

    public function update(array $data, DeliveryPoint $deliveryPoint)
    {
        DB::beginTransaction();
        // if ($data['image'] ?? null && $data['image']->isValid()) {
        //     if ($data['image']) {
        //         Storage::disk('public')->delete('brand/' . $data['image']);
        //     }
        //     $data['image'] = $this->saveFile($data['image']);
        // }
        $deliveryPoint->fill($data);
        $deliveryPoint->save();
        DB::commit();
        session()->flash("success", "Delivery Point has been successfully updated!");
        return redirect('/admin/delivery-points');
    }
    public function saveFile($data)
    {
        $image = null;
        if ($data->isValid()) {
            $image = time() . '_' . $data->getClientOriginalName();
            Storage::disk('public')->putFileAs('brand/', $data, $image);
        }
        return $image;
    }
    public function destroy(DeliveryPoint $deliveryPoint)
    {
        try {
            // // Check for associated products
            // $hasProducts = Product::where('brand_id', $brand->id)->exists();

            // if ($hasProducts) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Cannot delete brand. It has associated products.'
            //     ]);
            // }

            $deliveryPoint->delete();

            return response()->json([
                'status' => true,
                'message' => 'Delivery Point deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the Delivery Point: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDelete($request)
    {
        try {
            $ids = $request->ids;
            $decryptedIds = array_map(function ($id) {
                return Crypt::decrypt($id);
            }, $ids);

            $deliveryPoints = DeliveryPoint::whereIn('id', $decryptedIds)->get();
            $notDeleted = [];
            $deletedCount = 0;

            foreach ($deliveryPoints as $deliveryPoint) {
            //     if ($brand->product()->count() > 1) {
            //         $notDeleted[] = $brand->name;
            //         continue;
            //     }

            //     if ($brand->image && Storage::exists('public/brand/' . $brand->image)) {
            //         Storage::delete('public/brand/' . $brand->image);
            //     }

            $deliveryPoint->delete();
            //     $deletedCount++;
            }

            // if ($deletedCount === 0 && count($notDeleted) > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'None of the selected brands were deleted because they contain more than one product: ' . implode(', ', $notDeleted) . '.',
            //         'type' => 'warning'
            //     ]);
            // }

            // if ($deletedCount > 0 && count($notDeleted) > 0) {
            //     return response()->json([
            //         'success' => true,
            //         'message' => 'Some brands were deleted. The following brands were not deleted because they contain more than one product: ' . implode(', ', $notDeleted) . '.',
            //         'type' => 'partial'
            //     ]);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Selected delivery Points have been deleted successfully.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting delivery Points: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Update brand status
     */
    public function updateStatus($request)
    {

        try {
            $id = Crypt::decrypt($request->id);
            $deliveryPoints = DeliveryPoint::findOrFail($id);

            $deliveryPoints->status = $request->status;
            $deliveryPoints->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkStatus($request)
    {
        try {
            $ids = $request->ids;
            $status = $request->status;

            $decryptedIds = array_map(function ($id) {
                return Crypt::decrypt($id);
            }, $ids);

            DeliveryPoint::whereIn('id', $decryptedIds)->update(['status' => $status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated for selected Delivery Points.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update brand sort order
     */
    public function updateOrder($request)
    {
        try {
            $order = $request->order;

            foreach ($order as $item) {
                $id = Crypt::decrypt($item['id']);
                Brand::where('id', $id)->update(['sort_order' => $item['position']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating sort order: ' . $e->getMessage()
            ], 500);
        }
    }
    public function view(DeliveryPoint $deliveryPoint)
    {
        return view('backend.delivery_points.delivery_points_view', compact('deliveryPoint'));
    }
}
