<?php

namespace App\Service;

use App\Models\Category;
use App\Models\Package;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class PackageService
{
    public function index($request)
    {
        if ($request->ajax()) {
            $data=Package::all();
            return DataTables::of($data)
                ->addIndexColumn()
                // ->addColumn('date', function ($row) {
                //     return $row->created_at->format('d-m-Y h:i A');
                // })
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="select-checkbox" value="'.Crypt::encrypt($row->id).'" data-id="'.Crypt::encrypt($row->id).'">';
                })
                // ->addColumn('sort_order', function ($row) {
                //     return '<span class="sort-order-handle" data-id="'.Crypt::encrypt($row->id).'"><i class="fas fa-arrows-alt"></i> '.$row->sort_order.'</span>';
                // })
                // ->addColumn('image', function ($row) {
                //     if ($row->image) {
                //         return '<img src="' . url('storage/package/'.$row->image) . '" data-url="' . url('storage/package/'.$row->image) . '" data-toggle="modal" data-target="#image-view" alt="category-img" width="35px" height="35px" class="data-table-image edit open-image-model">';
                //     }
                //     return '<span class="badge bg-secondary">No Image</span>';
                // })
                ->addColumn('package_name', function ($row) {
                    return $row->name ?? '-';
                })
                ->addColumn('totalmembers', function ($row) {
                    return $row->count() ?? '-';
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount ?? '-';
                })
                ->addColumn('days', function ($row) {
                    return $row->days ?? '-';
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" '.$checked.' data-id="'.Crypt::encrypt($row->id).'">
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <div class="d-flex gap-2">
                            <a href="' . url('/admin/package/' . Crypt::encrypt($row->id)) . '" class="p-1" title="View">
                                <img src="' . url('img/eye-icon.png') . '" alt="View" width="30">
                            </a>
                            <a href="' . url('/admin/package/' . Crypt::encrypt($row->id) . '/edit') . '" class="p-1" title="Edit">
                                <img src="' . url('img/edit-icon.png') . '" alt="Edit" width="30">
                            </a>
                            <a href="javascript:void(0)" data-id="' . Crypt::encrypt($row->id) . '" class="p-1 delete" title="Delete">
                                <img src="' . url('img/delete-icon.png') . '" alt="Delete" width="30">
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['checkbox','package_name','totalmembers','amount','days','status','action'])
                ->make(true);
        }
        return view('backend.package.package_index');
    }
    public function store(array $data)
    {
        DB::beginTransaction();
        $package = new Package();
        // if($data['image'] ?? null){
        //     $data['image'] = $this->saveFile($data['image']);
        // }
        // $lastCategory = Category::orderBy('sort_order', 'desc')->first();
        // $data['sort_order'] = $lastCategory ? $lastCategory->sort_order + 1 : 1;
        $package->fill($data);
        $package->save();
        DB::commit();
        session()->flash("success", "Package has been successfully created!");
        return redirect('/admin/package');
    }

    public function update(array $data, Package $package)
    {
        DB::beginTransaction();
        // if($data['image'] ?? null && $data['image']->isValid()){
        //     if($data['image']){
        //         Storage::disk('public')->delete('package/' . $data['image']);
        //     }
        //     $data['image'] = $this->saveFile($data['image']);
        // }
        $package->fill($data);
        $package->save();
        DB::commit();
        session()->flash("success", "Package has been successfully updated!");
        return redirect('/admin/package');
    }
    public function saveFile($data){
        $image = null;
        if($data->isValid()){
            $image=time().'_'.$data->getClientOriginalName();
            Storage::disk('public')->putFileAs('package/', $data, $image);
        }
        return $image;
    }
   public function destroy(Package $package)
{
    try {
        // // Check for subcategories
        // $hasSubCategories = SubCategory::where('category_id', $category->id)->exists();

        // // Check for associated products
        // $hasProducts = Product::where('category_id', $category->id)->exists();

        // if ($hasSubCategories || $hasProducts) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Cannot delete category. It has associated subcategories or products.'
        //     ]);
        // }

        // Delete the category
        $package->delete();

        return response()->json([
            'status' => true,
            'message' => 'Package deleted successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while deleting the Package: ' . $e->getMessage()
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

            $packages = Package::whereIn('id', $decryptedIds)->get();

            $notDeleted = [];
            $deletedCount = 0;

            foreach ($packages as $package) {
                // if ($package->product()->count() > 1) {
                //     $notDeleted[] = $package->name;
                //     continue;
                // }

                // if ($package->image && Storage::exists('public/package/' . $package->image)) {
                //     Storage::delete('public/package/' . $package->image);
                // }

                $package->delete();
                $deletedCount++;
            }

            // if ($deletedCount === 0 && count($notDeleted) > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'None of the selected packages were deleted because they contain more than one product: ' . implode(', ', $notDeleted) . '.',
            //         'type' => 'warning'
            //     ]);
            // }

            // if ($deletedCount > 0 && count($notDeleted) > 0) {
            //     return response()->json([
            //         'success' => true,
            //         'message' => 'Some packages were deleted. The following packages were not deleted because they contain more than one product: ' . implode(', ', $notDeleted) . '.',
            //         'type' => 'partial'
            //     ]);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Selected packages have been deleted successfully.',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting packages: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Update category status
     */
    public function updateStatus($request)
    {

        try {
            $id = Crypt::decrypt($request->id);
            $package = Package::findOrFail($id);

            $package->status = $request->status;
            $package->save();

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

    public function bulkStatus($request)
    {
        try {
            $ids = $request->ids;
            $status = $request->status;

            $decryptedIds = array_map(function($id) {
                return Crypt::decrypt($id);
            }, $ids);

            Package::whereIn('id', $decryptedIds)->update(['status' => $status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated for selected Packages.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: '.$e->getMessage()
            ], 500);
        }
    }

    /**
     * Update category sort order
     */
    public function updateOrder($request)
    {
        try {
            $order = $request->order;

            foreach ($order as $item) {
                $id = Crypt::decrypt($item['id']);
                Category::where('id', $id)->update(['sort_order' => $item['position']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating sort order: '.$e->getMessage()
            ], 500);
        }
    }

    public function view(Package $package)
    {
        return view('backend.package.package_view', compact('package'));
    }

}
