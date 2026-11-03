<?php

namespace App\Service;

use App\Models\CancellationReason;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class CancellationReasonService
{
    public function index($request)
    {
        if ($request->ajax()) {
            $data = CancellationReason::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $btn = $row['created_at'];
                    return $btn;
                })
                ->addColumn('reason', function ($row) {
                    $btn = $row->reason ?? '-';
                    return $btn;
                })
                ->addColumn('is_show', function ($row) {
                    $checked = $row->is_show == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" '.$checked.' data-id="'.Crypt::encrypt($row->id).'">
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    $id = Crypt::encrypt($row->id);

                    $editBtn = '
                        <img
                            src="' . url('img/edit-icon.png') . '"
                            alt="Edit"
                            title="Edit"
                            width="30"
                            style="cursor: pointer;"
                            class="edit-cancellation"
                            data-id="' . $id . '"
                        >';

                    $deleteBtn = '
                        <img
                            src="' . url('img/delete-icon.png') . '"
                            alt="Delete"
                            title="Delete"
                            width="30"
                            style="cursor: pointer;"
                            class="delete-cancellation"
                            data-id="' . $id . '"
                        >';

                    return '
                        <div class="d-flex gap-2 justify-content-start align-items-center action-icons">
                            ' . $editBtn . '
                            ' . $deleteBtn . '
                        </div>';
                })
                ->rawColumns(['is_show', 'reason', 'date', 'action', 'image'])
                ->make(true);
        }
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        $cancellationReason = new CancellationReason();
        if ($data['image'] ?? null) {
            $data['image'] = $this->saveFile($data['image']);
        }
        $cancellationReason->fill($data);
        $cancellationReason->save();
        DB::commit();
        session()->flash("success", "Cancellation Reason has been successfully created!");
        return redirect('/admin/settings');
    }

    public function edit(CancellationReason $cancellationReason)
    {
        try {
            $cancellationReason = CancellationReason::find($cancellationReason->id);
            if (!$cancellationReason) {
                return response()->json(['error' => 'Cancellation Reason not found.'], 404);
            }
            $cancellationReason->encrypted_id = Crypt::encrypt($cancellationReason->id);
            return response()->json($cancellationReason);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(array $data, $cancellationReason)
    {

        DB::beginTransaction();
        if ($data['image'] ?? null) {
            $data['image'] = $this->saveFile($data['image']);
        }
        $cancellationReason->fill($data);
        $cancellationReason->save();
        DB::commit();
        session()->flash("success", "Cancellation Reason has been successfully updated!");
        return redirect('/admin/settings');
    }

    public function destroy(CancellationReason $cancellationReason)
    {

        if ($cancellationReason) {
            $cancellationReason->delete();
            return response()->json(['status' => 1, 'message' => 'Cancellation Reason deleted successfully.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'Cannot delete. Cancellation Reason exist.']);
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
            $cancellationReason = CancellationReason::findOrFail($id);

            $cancellationReason->is_show = $request->status;
            $cancellationReason->save();

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
