<?php

namespace App\Service;

use App\Models\ReturnOrderReason;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReturnOrderReasonService
{
    public function index($request)
    {
        if ($request->ajax()) {
            $data = ReturnOrderReason::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('reason', function ($row) {
                    return $row->reason ?? '-';
                })
                ->addColumn('is_show', function ($row) {
                    $checked = $row->is_show ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input user-account-status-toggle" type="checkbox" '.$checked.' data-id="'.Crypt::encrypt($row->id).'">
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
                            class="edit-user-account-reason"
                            data-id="' . $id . '"
                        >';

                    $deleteBtn = '
                        <img
                            src="' . url('img/delete-icon.png') . '"
                            alt="Delete"
                            title="Delete"
                            width="30"
                            style="cursor: pointer;"
                            class="delete-user-account-reason"
                            data-id="' . $id . '"
                        >';

                    return '
                        <div class="d-flex gap-2 justify-content-start align-items-center action-icons">
                            ' . $editBtn . '
                            ' . $deleteBtn . '
                        </div>';
                })
                ->rawColumns(['is_show', 'action'])
                ->make(true);
        }
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        $reason = ReturnOrderReason::create($data);
        DB::commit();
        session()->flash("success", "User Account Reason has been successfully created!");
        return redirect('/admin/settings');
    }

    public function edit(ReturnOrderReason $returnOrderReason)
    {
        try {
            $reason = ReturnOrderReason::find($returnOrderReason->id);
            if (!$reason) {
                return response()->json(['error' => 'Reason not found.'], 404);
            }
            $reason->encrypted_id = Crypt::encrypt($reason->id);
            return response()->json($reason);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(array $data, $reason)
    {
        DB::beginTransaction();
        $reason->update($data);
        DB::commit();
        session()->flash("success", "User Account Reason has been successfully updated!");
        return redirect('/admin/settings');
    }

    public function destroy(ReturnOrderReason $returnOrderReason)
    {
        if ($returnOrderReason) {
            $returnOrderReason->delete();
            return response()->json(['status' => 1, 'message' => 'Reason deleted successfully.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'Cannot delete. Reason exist.']);
        }
    }

    public function updateStatus($request)
    {
        try {
            $id = Crypt::decrypt($request->id);
            $reason = ReturnOrderReason::findOrFail($id);

            $reason->is_show = $request->status;
            $reason->save();

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
