<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Room;
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
            $data = Tenant::with('room')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name_email', function ($row) {
                    return '<div>
                        <span class="fw-bold text-dark d-block">' . e($row->name) . '</span>
                        <small class="text-muted">' . e($row->email ?? 'No email') . '</small>
                    </div>';
                })
                ->addColumn('room_badge', function ($row) {
                    $roomNum = $row->room ? $row->room->room_number : $row->room_number;
                    $floor = $row->room ? $row->room->floor : '';
                    return '<span class="badge bg-primary text-white fw-bold px-2 py-1"><i class="mdi mdi-door-closed me-1"></i> Room ' . e($roomNum) . '</span>
                            <div class="small text-muted mt-1">' . e($row->room_type) . ($floor ? ' (' . e($floor) . ')' : '') . '</div>';
                })
                ->addColumn('rent_display', function ($row) {
                    $total = (float) $row->monthly_rent + (float) $row->maintenance_charges + (float) $row->other_charges;
                    $extra = [];
                    if ($row->eb_unit_rate > 0) $extra[] = 'EB: ₹' . number_format($row->eb_unit_rate, 2) . '/unit';
                    if ($row->maintenance_charges > 0) $extra[] = 'Maint: ₹' . number_format($row->maintenance_charges);
                    if ($row->other_charges > 0) $extra[] = 'Other: ₹' . number_format($row->other_charges);
                    $extraHtml = !empty($extra) ? '<small class="text-muted d-block font-11">+' . implode(' | ', $extra) . '</small>' : '';

                    return '<div>
                        <span class="fw-bold text-dark font-13">₹' . number_format($total) . ' <small class="text-muted fw-normal">/mo</small></span>
                        <small class="text-muted d-block font-11">Rent: ₹' . number_format($row->monthly_rent) . ' | Dep: ₹' . number_format($row->security_deposit) . '</small>
                        ' . $extraHtml . '
                    </div>';
                })
                ->addColumn('commence_date_formatted', function ($row) {
                    return $row->commence_date ? $row->commence_date->format('d M Y') : '-';
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
                ->rawColumns(['name_email', 'room_badge', 'rent_display', 'commence_date_formatted', 'status_toggle', 'action'])
                ->make(true);
        }

        $stats = [
            'totalTenants' => Tenant::count(),
            'activeTenants' => Tenant::where('status', 1)->count(),
            'totalRent' => Tenant::where('status', 1)->selectRaw('SUM(monthly_rent + maintenance_charges + other_charges) as total')->value('total') ?? 0,
            'occupiedRooms' => Tenant::where('status', 1)->whereNotNull('room_id')->distinct('room_id')->count('room_id'),
        ];

        $rooms = Room::orderBy('room_number')->get();

        return view('backend.tenants.index', compact('stats', 'rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'room_number' => 'nullable|string|max:50',
            'room_type' => 'required|string|max:100',
            'monthly_rent' => 'required|numeric|min:0',
            'eb_unit_rate' => 'nullable|numeric|min:0',
            'eb_start_reading' => 'nullable|numeric|min:0',
            'maintenance_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'commence_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $room = Room::findOrFail($validated['room_id']);
        $validated['room_number'] = $room->room_number;
        $validated['eb_unit_rate'] = $validated['eb_unit_rate'] ?? 10.00;
        $validated['eb_start_reading'] = $validated['eb_start_reading'] ?? 0.00;
        $validated['maintenance_charges'] = $validated['maintenance_charges'] ?? 0;
        $validated['other_charges'] = $validated['other_charges'] ?? 0;
        $validated['security_deposit'] = $validated['security_deposit'] ?? 0;
        $validated['status'] = 1;

        $tenant = Tenant::create($validated);

        // Mark room as occupied
        $room->update(['occupancy_status' => 'occupied']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant registered and room occupied successfully!',
                'tenant' => $tenant
            ]);
        }

        return redirect('/admin/tenants')->with('success', 'Tenant registered successfully!');
    }

    public function getData(Tenant $tenant)
    {
        return response()->json([
            'success' => true,
            'tenant' => $tenant->load('room')
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'room_number' => 'nullable|string|max:50',
            'room_type' => 'required|string|max:100',
            'monthly_rent' => 'required|numeric|min:0',
            'eb_unit_rate' => 'nullable|numeric|min:0',
            'eb_start_reading' => 'nullable|numeric|min:0',
            'maintenance_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'commence_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $oldRoomId = $tenant->room_id;
        $newRoomId = $validated['room_id'];

        $newRoom = Room::findOrFail($newRoomId);
        $validated['room_number'] = $newRoom->room_number;
        $validated['eb_unit_rate'] = $validated['eb_unit_rate'] ?? 10.00;
        $validated['eb_start_reading'] = $validated['eb_start_reading'] ?? 0.00;
        $validated['maintenance_charges'] = $validated['maintenance_charges'] ?? 0;
        $validated['other_charges'] = $validated['other_charges'] ?? 0;
        $validated['security_deposit'] = $validated['security_deposit'] ?? 0;

        $tenant->update($validated);

        // If room changed, handle occupancy update for both rooms
        if ($oldRoomId && $oldRoomId != $newRoomId) {
            $hasOtherActive = Tenant::where('room_id', $oldRoomId)
                ->where('id', '!=', $tenant->id)
                ->where('status', 1)
                ->exists();

            if (!$hasOtherActive) {
                Room::where('id', $oldRoomId)->update(['occupancy_status' => 'available']);
            }
        }

        // Mark new room as occupied
        $newRoom->update(['occupancy_status' => 'occupied']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant and room allocation updated successfully!'
            ]);
        }

        return redirect('/admin/tenants')->with('success', 'Tenant updated successfully!');
    }

    public function destroy(Tenant $tenant)
    {
        $roomId = $tenant->room_id;
        $tenant->delete();

        if ($roomId) {
            $hasOtherActive = Tenant::where('room_id', $roomId)
                ->where('status', 1)
                ->exists();

            if (!$hasOtherActive) {
                Room::where('id', $roomId)->update(['occupancy_status' => 'available']);
            }
        }

        return response()->json([
            'success' => true,
            'status' => '1',
            'message' => 'Tenant removed and room occupancy updated!'
        ]);
    }

    public function updateStatus(Request $request)
    {
        $tenant = Tenant::findOrFail($request->id);
        $tenant->status = $request->status;
        $tenant->save();

        if ($tenant->room_id) {
            if ($tenant->status == 1) {
                Room::where('id', $tenant->room_id)->update(['occupancy_status' => 'occupied']);
            } else {
                $hasOtherActive = Tenant::where('room_id', $tenant->room_id)
                    ->where('id', '!=', $tenant->id)
                    ->where('status', 1)
                    ->exists();

                if (!$hasOtherActive) {
                    Room::where('id', $tenant->room_id)->update(['occupancy_status' => 'available']);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tenant status and room occupancy updated successfully.'
        ]);
    }
}
