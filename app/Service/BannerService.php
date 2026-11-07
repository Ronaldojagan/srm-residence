<?php

namespace App\Service;

use App\Models\Banner;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BannerService
{
    public function index($request)
    {
        if ($request->ajax()) {
            $data = Banner::orderBy('id', 'desc')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('title', function ($row) {
                    return $row->name ?? '-';
                })
                ->addColumn('image', function ($row) {
                    if ($row->image) {
                        return '<img src="' . url('storage/banner/' . $row->image) . '" data-url="' . url('storage/banner/' . $row->image) . '"
                                alt="banner-img" width="80px" height="50px"
                                class="img-thumbnail open-image-model">';
                    }
                    return '-';
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input banner-status-toggle"
                               type="checkbox" '.$checked.'
                               data-id="'.Crypt::encrypt($row->id).'">
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    $id = Crypt::encrypt($row->id);

                    $editBtn = '<img src="' . url('img/edit-icon.png') . '"
                                alt="Edit" title="Edit" width="30"
                                style="cursor: pointer;"
                                class="edit-banner"
                                data-id="' . $id . '">';

                    $deleteBtn = '<img src="' . url('img/delete-icon.png') . '"
                                  alt="Delete" title="Delete" width="30"
                                  style="cursor: pointer;"
                                  class="delete"
                                  data-id="' . $id . '">';

                    return '<div class="d-flex gap-2 justify-content-start align-items-center action-icons">
                            ' . $editBtn . '
                            ' . $deleteBtn . '
                        </div>';
                })
                ->rawColumns(['status', 'title', 'date', 'action', 'image', 'type', 'target'])
                ->make(true);
        }
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        $banner = new Banner();
        if ($data['image'] ?? null) {
            $data['image'] = $this->saveFile($data['image']);
        }
        $banner->fill($data);
        $banner->save();
        DB::commit();
        session()->flash("success", "Banner has been successfully created!");
        return redirect('/admin/settings?tab=banners');
    }

    public function edit($banner)
    {
        try {
            $banner = Banner::find($banner->id);
            if (!$banner) {
                return response()->json(['error' => 'Banner not found.'], 404);
            }
            $banner->encrypted_id = Crypt::encrypt($banner->id);
            return response()->json($banner);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(array $data, $banner)
    {

        DB::beginTransaction();
        if ($data['image'] ?? null) {
            $data['image'] = $this->saveFile($data['image']);
        }
        $banner->fill($data);
        $banner->save();
        DB::commit();
        session()->flash("success", "Banner has been successfully updated!");
        return redirect('/admin/settings?tab=banners');
    }

    public function destroy(Banner $banner)
    {

        if ($banner) {
            $banner->delete();
            return response()->json(['status' => 1, 'message' => 'Banner deleted successfully.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'Cannot delete. Banner exist.']);
        }
    }


    public function saveFile($data)
    {
        $image = null;
        if ($data->isValid()) {
            $image = time() . '_' . $data->getClientOriginalName();
            Storage::disk('public')->putFileAs('banner/', $data, $image);
        }
        return $image;
    }

    public function updateStatus($request)
    {

        try {
            $id = Crypt::decrypt($request->id);
            $banner = Banner::findOrFail($id);

            $banner->status = $request->status;
            $banner->save();

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
