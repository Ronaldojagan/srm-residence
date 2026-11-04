<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Tenant::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name_email', function ($row) {
                    return '<div>
                        <span class="fw-bold text-dark d-block">' . e($row->name) . '</span>
                        <small class="text-muted">' . e($row->email ?? 'No email') . '</small>
                    </div>';
                })
                ->addColumn('room_badge', function ($row) {
                    return '<span class="badge bg-primary text-white fw-bold px-2 py-1">' . e($row->room_number) . '</span>
                            <div class="small text-muted mt-1">' . e($row->room_type) . '</div>';
                })
                ->addColumn('rent_display', function ($row) {
                    return '<div>
                        <span class="fw-bold text-dark">₹' . number_format($row->monthly_rent) . '</span>
                        <small class="text-muted d-block">Dep: ₹' . number_format($row->security_deposit) . '</small>
                    </div>';
                })
                ->addColumn('check_in', function ($row) {
                    return $row->check_in_date ? $row->check_in_date->format('d M Y') : '-';
                })
                ->addColumn('status_toggle', function ($row) {
                    $checked = $row->status ? 'checked' : '';
                    return '<div class="form-check form-switch mb-0">
                        <input class="form-check-input tenant-status-toggle" type="checkbox" ' . $checked . ' data-id="' . $row->id . '" style="cursor:pointer; width: 36px; height: 18px;">
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group btn-group-sm" role="group">';
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-edit-tenant" data-id="' . $row->id . '" title="Edit Tenant"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-tenant" data-id="' . $row->id . '" data-name="' . e($row->name) . '" title="Delete Tenant"><i class="fas fa-trash-alt"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name_email', 'room_badge', 'rent_display', 'check_in', 'status_toggle', 'action'])
                ->make(true);
        }

        $stats = [
            'totalTenants' => Tenant::count(),
            'activeTenants' => Tenant::where('status', 1)->count(),
            'totalRent' => Tenant::where('status', 1)->sum('monthly_rent'),
            'occupiedRooms' => Tenant::where('status', 1)->distinct('room_number')->count('room_number'),
        ];

        return view('backend.tenants.index', compact('stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'room_number' => 'required|string|max:50',
            'room_type' => 'required|string|max:100',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'check_in_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $validated['status'] = 1;
        $tenant = Tenant::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant registered successfully!',
                'tenant' => $tenant
            ]);
        }

        return redirect('/admin/tenants')->with('success', 'Tenant registered successfully!');
    }

    public function getData(Tenant $tenant)
    {
        return response()->json([
            'success' => true,
            'tenant' => $tenant
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'room_number' => 'required|string|max:50',
            'room_type' => 'required|string|max:100',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'check_in_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $tenant->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant updated successfully!'
            ]);
        }

        return redirect('/admin/tenants')->with('success', 'Tenant updated successfully!');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return response()->json([
            'success' => true,
            'status' => '1',
            'message' => 'Tenant removed successfully!'
        ]);
    }

    public function updateStatus(Request $request)
    {
        $tenant = Tenant::findOrFail($request->id);
        $tenant->status = $request->status;
        $tenant->save();

        return response()->json([
            'success' => true,
            'message' => 'Tenant status updated successfully.'
        ]);
    }
}
