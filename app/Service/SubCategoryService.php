<?php

namespace App\Service;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class SubCategoryService
{
    public function index($request, $category)
    {
        if ($request->ajax()) {
            $data = SubCategory::where('category_id', $category->id)->orderBy('sort_order', 'asc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $btn = $row['created_at'];
                    return $btn;
                })
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('d-m-Y h:i A');
                })
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="select-checkbox" value="' . Crypt::encrypt($row->id) . '" data-id="' . Crypt::encrypt($row->id) . '">';
                })
                ->addColumn('sort_order', function ($row) {
                    return '<span class="sort-order-handle" data-id="'.Crypt::encrypt($row->id).'"><i class="fas fa-arrows-alt"></i> '.$row->sort_order.'</span>';
                })
                ->addColumn('image', function ($row) {
                    $btn = '<img src="' . url('storage/sub-category/' . $row->image) . '" data-url="' . url('storage/sub-category/' . $row->image) . '" data-toggle="modal" data-target="#image-view" alt="user-img" width="35px" height="35px" class="data-table-image edit open-image-model">';
                    return $btn;
                })
                ->addColumn('name', function ($row) {
                    $btn = $row->name ?? '-';
                    return $btn;
                })
                ->addColumn('product_count', function ($row) {
                    return $row->product()->count();
                })
                ->addColumn('category_name', function ($row) {
                    $btn = $row->category->name ?? '-';
                    return $btn;
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" ' . $checked . ' data-id="' . Crypt::encrypt($row->id) . '">
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="' . url('/admin/sub-category/' . Crypt::encrypt($row->id) . '/edit') . '" class="p-1" title="Edit">
                                <img src="' . url('img/edit-icon.png') . '" alt="Edit" width="30">
                            </a>
                            <a href="javascript:void(0)" data-id="' . Crypt::encrypt($row->id) . '" class="p-1 delete" title="Delete">
                                <img src="' . url('img/delete-icon.png') . '" alt="Delete" width="30">
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'name', 'no_of_item', 'image', 'date', 'action', 'category_name', 'checkbox', 'sort_order', 'product_count'])
                ->make(true);
        }
        return view('backend.category.category_view');
    }
    public function store(array $data)
    {
        DB::beginTransaction();
        $subCategory = new SubCategory();
        if ($data['image'] ?? null) {
            $data['image'] = $this->saveFile($data['image']);
        }
        $lastCategory = SubCategory::orderBy('sort_order', 'desc')->first();
        $data['sort_order'] = $lastCategory ? $lastCategory->sort_order + 1 : 1;
        $subCategory->fill($data);
        $subCategory->save();
        DB::commit();
        session()->flash("success", "SubCategory has been successfully created!");
        return redirect('/admin/category/' . Crypt::encrypt($data['category_id']));
    }

    public function update(array $data, SubCategory $subCategory)
    {
        DB::beginTransaction();
        if ($data['image'] ?? null && $data['image']->isValid()) {
            if ($data['image']) {
                Storage::disk('public')->delete('sub-category/' . $data['image']);
            }
            $data['image'] = $this->saveFile($data['image']);
        }
        $subCategory->fill($data);
        $subCategory->save();
        DB::commit();
        session()->flash("success", "Sub Category has been successfully updated!");
        return redirect('/admin/category/' . Crypt::encrypt($data['category_id']));
    }
    public function saveFile($data)
    {
        $image = null;
        if ($data->isValid()) {
            $image = time() . '_' . $data->getClientOriginalName();
            Storage::disk('public')->putFileAs('sub-category/', $data, $image);
        }
        return $image;
    }
    public function destroy($subCategory)
{
    try {
        // Check if subcategory exists
        if (!$subCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found.'
            ], 404);
        }

        // Prevent deletion if subcategory has more than one product
        if ($subCategory->product()->count() > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory cannot be deleted because it contains more than one product.'
            ], 400);
        }

        // Delete the subcategory
        $subCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategory deleted successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while deleting the subcategory: ' . $e->getMessage()
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

            $subcategories = SubCategory::whereIn('id', $decryptedIds)->get();

            $notDeleted = [];
            $deletedCount = 0;

            foreach ($subcategories as $subCategory) {
                if ($subCategory->product()->count() > 1) {
                    $notDeleted[] = $subCategory->name;
                    continue;
                }

                if ($subCategory->image && Storage::exists('public/sub-category/' . $subCategory->image)) {
                    Storage::delete('public/sub-category/' . $subCategory->image);
                }

                $subCategory->delete();
                $deletedCount++;
            }

            if ($deletedCount === 0 && count($notDeleted) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'None of the selected subcategories were deleted because they contain more than one product: ' . implode(', ', $notDeleted) . '.',
                    'type' => 'warning'
                ]);
            }

            if ($deletedCount > 0 && count($notDeleted) > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Some subcategories were deleted. The following were not deleted because they contain more than one product: ' . implode(', ', $notDeleted) . '.',
                    'type' => 'partial'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Selected subcategories have been deleted successfully.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting subcategories: ' . $e->getMessage()
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
            $category = SubCategory::findOrFail($id);

            $category->status = $request->status;
            $category->save();

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

            SubCategory::whereIn('id', $decryptedIds)->update(['status' => $status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated for selected categories.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
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
                SubCategory::where('id', $id)->update(['sort_order' => $item['position']]);
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
}
