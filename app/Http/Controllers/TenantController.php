<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Room;
use App\Models\BillPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            $data = Tenant::with(['room', 'rooms'])->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');

            if ($request->filled('status') && $request->status !== 'all') {
                $data->where('status', $request->status === 'active' ? 1 : 0);
            }
            if ($request->filled('room_id') && $request->room_id !== 'all') {
                $data->where(function ($q) use ($request) {
                    $q->where('room_id', $request->room_id)
                        ->orWhereHas('rooms', function ($rq) use ($request) {
                            $rq->where('rooms.id', $request->room_id);
                        });
                });
            }

            return DataTables::of($data)
                ->orderColumn('room_number', function ($query, $order) {
                    $query->orderByRaw("CAST(room_number AS UNSIGNED) {$order}, room_number {$order}");
                })
                ->addIndexColumn()
                ->addColumn('name_email', function ($row) {
                    $viewUrl = url('/admin/tenants/' . $row->id . '/view');
                    return '<div>
                        <a href="' . $viewUrl . '" class="fw-bold text-dark d-block text-decoration-none" title="View Resident Profile">' . e($row->name) . '</a>
                        <small class="text-muted">' . e($row->email ?? 'No email') . '</small>
                    </div>';
                })
                ->addColumn('room_badge', function ($row) {
                    $rooms = $row->assigned_rooms;
                    if ($rooms->isNotEmpty()) {
                        $badges = [];
                        foreach ($rooms as $r) {
                            $badges[] = '<span class="badge bg-primary text-white fw-bold px-2 py-1 me-1 mb-1"><i class="mdi mdi-door-closed me-1"></i> Room ' . e($r->room_number) . '</span>';
                        }
                        $badgeHtml = implode(' ', $badges);
                        $floors = $rooms->pluck('floor')->unique()->filter()->implode(', ');
                        $types = $rooms->pluck('room_type')->unique()->filter()->implode(', ');
                        return '<div class="d-flex flex-wrap align-items-center">' . $badgeHtml . '</div><div class="small text-muted mt-1">' . e($types ?: $row->room_type) . ($floors ? ' (' . e($floors) . ')' : '') . '</div>';
                    }
                    return '<span class="badge bg-secondary text-white fw-bold px-2 py-1">Room ' . e($row->room_number ?? 'N/A') . '</span>';
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
                    $viewUrl = url('/admin/tenants/' . $row->id . '/view');
                    $btn = '<div class="btn-group btn-group-sm" role="group">';
                    $btn .= '<a href="' . $viewUrl . '" class="btn btn-outline-info" title="View Resident Profile"><i class="fas fa-eye"></i></a>';
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
            'totalRent' => (float) (Tenant::where('status', 1)->selectRaw('SUM(monthly_rent + maintenance_charges + other_charges) as total')->value('total') ?? 0),
            'baseRent' => (float) (Tenant::where('status', 1)->sum('monthly_rent') ?? 0),
            'totalSecurityDeposit' => (float) (Tenant::where('status', 1)->sum('security_deposit') ?? 0),
            'allSecurityDeposit' => (float) (Tenant::sum('security_deposit') ?? 0),
            'occupiedRooms' => Room::where('occupancy_status', 'occupied')->count(),
        ];

        $rooms = Room::with(['activeTenants', 'currentTenant'])->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')->get();

        return view('backend.tenants.index', compact('stats', 'rooms'));
    }

    public function store(Request $request)
    {
        $roomIds = $request->input('room_ids');
        if (empty($roomIds) && $request->filled('room_id')) {
            $roomIds = is_array($request->room_id) ? $request->room_id : [$request->room_id];
        }

        if (empty($roomIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one room to assign.',
                'errors' => ['room_ids' => ['Please select at least one room to assign.']]
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\.\'-]+$/u'],
            'email' => 'nullable|email|max:100',
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{10,15}$/'],
            'commence_date' => 'required|date',
            'emergency_contact' => ['nullable', 'string', 'regex:/^[0-9+\-\s]{7,15}$/'],
            'address' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Resident full name is required.',
            'name.min' => 'Resident name must be at least 2 characters.',
            'name.regex' => 'Resident name must contain letters and spaces only.',
            'phone.required' => 'Mobile number is required.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number.',
            'email.email' => 'Please enter a valid email address.',
            'commence_date.required' => 'Move-in / commence date is required.',
            'emergency_contact.regex' => 'Emergency contact must be a valid phone number.',
        ]);

        $assignedRooms = Room::whereIn('id', $roomIds)->get();
        if ($assignedRooms->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Selected room(s) do not exist.',
                'errors' => ['room_ids' => ['Selected room(s) do not exist.']]
            ], 422);
        }

        foreach ($assignedRooms as $r) {
            if ($r->is_self || $r->occupancy_status === 'self') {
                return response()->json([
                    'success' => false,
                    'message' => 'Room ' . $r->room_number . ' is marked as Owner/Self-occupied and cannot be assigned to a tenant.',
                    'errors' => ['room_ids' => ['Room ' . $r->room_number . ' is marked as Owner/Self-occupied.']]
                ], 422);
            }

            // Restrict already occupied rooms
            $isOccupied = $r->occupancy_status === 'occupied' || DB::table('tenant_rooms')
                ->join('tenants', 'tenant_rooms.tenant_id', '=', 'tenants.id')
                ->where('tenant_rooms.room_id', $r->id)
                ->where('tenants.status', 1)
                ->whereNull('tenants.deleted_at')
                ->exists();

            if ($isOccupied) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room ' . $r->room_number . ' is already occupied and cannot be assigned to a new tenant.',
                    'errors' => ['room_ids' => ['Room ' . $r->room_number . ' is already occupied.']]
                ], 422);
            }

            if ($r->occupancy_status === 'maintenance') {
                return response()->json([
                    'success' => false,
                    'message' => 'Room ' . $r->room_number . ' is currently under maintenance and cannot be assigned.',
                    'errors' => ['room_ids' => ['Room ' . $r->room_number . ' is under maintenance.']]
                ], 422);
            }
        }

        $primaryRoom = $assignedRooms->first();
        $roomNumbers = $assignedRooms->sortBy(fn($r) => (int) $r->room_number)->pluck('room_number')->implode(', ');
        $roomTypes = $assignedRooms->pluck('room_type')->unique()->filter()->implode(', ');

        $validated['room_id'] = $primaryRoom->id;
        $validated['room_number'] = $roomNumbers;
        $validated['room_type'] = $roomTypes ?: 'Standard';

        // Inherit financial & utility rates directly from assigned rooms
        $validated['monthly_rent'] = (float) $assignedRooms->sum('monthly_rent');
        $validated['security_deposit'] = (float) $assignedRooms->sum('security_deposit');
        $validated['maintenance_charges'] = (float) $assignedRooms->sum('maintenance_charges');
        $validated['other_charges'] = (float) $assignedRooms->sum('other_charges');
        $validated['eb_unit_rate'] = (float) ($assignedRooms->first()->eb_unit_rate ?? 10.00);
        $validated['eb_start_reading'] = 0.00;
        $validated['status'] = 1;

        $tenant = Tenant::create($validated);
        $tenant->rooms()->sync($assignedRooms->pluck('id')->toArray());

        // Mark all assigned rooms as occupied
        Room::whereIn('id', $assignedRooms->pluck('id')->toArray())->update(['occupancy_status' => 'occupied']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant registered and ' . $assignedRooms->count() . ' room(s) assigned successfully!',
                'tenant' => $tenant
            ]);
        }

        return redirect('/admin/tenants')->with('success', 'Tenant registered successfully!');
    }

    public function getData(Tenant $tenant)
    {
        $tenant->load(['room', 'rooms']);
        $roomIds = $tenant->rooms->pluck('id')->toArray();
        if (empty($roomIds) && $tenant->room_id) {
            $roomIds = [$tenant->room_id];
        }

        return response()->json([
            'success' => true,
            'tenant' => $tenant,
            'room_ids' => $roomIds,
        ]);
    }

    public function view(Tenant $tenant)
    {
        $tenant->load(['room', 'rooms', 'bills.payments']);

        $bills = $tenant->bills()->orderBy('bill_date', 'desc')->get();
        $payments = BillPayment::where('tenant_id', $tenant->id)
            ->with('bill')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalBilled = (float) $bills->sum('amount');
        $totalPaid = (float) $bills->sum('paid_amount');
        $outstandingDues = (float) $bills->sum('due_amount');
        $totalEbUnits = (float) $bills->sum('eb_consumed_units');
        $totalEbAmount = (float) $bills->sum('eb_amount');

        $stats = [
            'totalBilled' => $totalBilled,
            'totalPaid' => $totalPaid,
            'outstandingDues' => $outstandingDues,
            'totalEbUnits' => $totalEbUnits,
            'totalEbAmount' => $totalEbAmount,
            'totalBills' => $bills->count(),
            'paidBills' => $bills->where('status', 'paid')->count(),
            'dueBills' => $bills->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->count(),
        ];

        $rooms = Room::with(['activeTenants', 'currentTenant'])->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')->get();

        return view('backend.tenants.view', compact('tenant', 'bills', 'payments', 'stats', 'rooms'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $roomIds = $request->input('room_ids');
        if (empty($roomIds) && $request->filled('room_id')) {
            $roomIds = is_array($request->room_id) ? $request->room_id : [$request->room_id];
        }

        if (empty($roomIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one room to assign.',
                'errors' => ['room_ids' => ['Please select at least one room to assign.']]
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\.\'-]+$/u'],
            'email' => 'nullable|email|max:100',
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{10,15}$/'],
            'commence_date' => 'required|date',
            'emergency_contact' => ['nullable', 'string', 'regex:/^[0-9+\-\s]{7,15}$/'],
            'address' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Resident full name is required.',
            'name.min' => 'Resident name must be at least 2 characters.',
            'name.regex' => 'Resident name must contain letters and spaces only.',
            'phone.required' => 'Mobile number is required.',
            'phone.regex' => 'Please enter a valid mobile number.',
            'email.email' => 'Please enter a valid email address.',
            'commence_date.required' => 'Move-in / commence date is required.',
        ]);

        $assignedRooms = Room::whereIn('id', $roomIds)->get();
        if ($assignedRooms->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Selected room(s) do not exist.',
                'errors' => ['room_ids' => ['Selected room(s) do not exist.']]
            ], 422);
        }

        // Get previously assigned rooms for this tenant
        $oldRoomIds = $tenant->rooms()->pluck('rooms.id')->toArray();
        if (empty($oldRoomIds) && $tenant->room_id) {
            $oldRoomIds = [$tenant->room_id];
        }

        foreach ($assignedRooms as $r) {
            if ($r->is_self || $r->occupancy_status === 'self') {
                return response()->json([
                    'success' => false,
                    'message' => 'Room ' . $r->room_number . ' is marked as Owner/Self-occupied and cannot be assigned to a tenant.',
                    'errors' => ['room_ids' => ['Room ' . $r->room_number . ' is marked as Owner/Self-occupied.']]
                ], 422);
            }

            // Check if occupied by ANOTHER active tenant
            $isOccupiedByOther = DB::table('tenant_rooms')
                ->join('tenants', 'tenant_rooms.tenant_id', '=', 'tenants.id')
                ->where('tenant_rooms.room_id', $r->id)
                ->where('tenants.id', '!=', $tenant->id)
                ->where('tenants.status', 1)
                ->whereNull('tenants.deleted_at')
                ->exists();

            if (!$isOccupiedByOther && $r->occupancy_status === 'occupied') {
                if (!in_array($r->id, $oldRoomIds)) {
                    $isOccupiedByOther = true;
                }
            }

            if ($isOccupiedByOther) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room ' . $r->room_number . ' is already occupied by another resident and cannot be assigned.',
                    'errors' => ['room_ids' => ['Room ' . $r->room_number . ' is already occupied by another resident.']]
                ], 422);
            }

            if ($r->occupancy_status === 'maintenance') {
                return response()->json([
                    'success' => false,
                    'message' => 'Room ' . $r->room_number . ' is currently under maintenance and cannot be assigned.',
                    'errors' => ['room_ids' => ['Room ' . $r->room_number . ' is under maintenance.']]
                ], 422);
            }
        }

        $newRoomIds = $assignedRooms->pluck('id')->toArray();
        $primaryRoom = $assignedRooms->first();
        $roomNumbers = $assignedRooms->sortBy(fn($r) => (int) $r->room_number)->pluck('room_number')->implode(', ');
        $roomTypes = $assignedRooms->pluck('room_type')->unique()->filter()->implode(', ');

        $validated['room_id'] = $primaryRoom->id;
        $validated['room_number'] = $roomNumbers;
        $validated['room_type'] = $roomTypes ?: $tenant->room_type;

        // Inherit financial & utility rates directly from assigned rooms
        $validated['monthly_rent'] = (float) $assignedRooms->sum('monthly_rent');
        $validated['security_deposit'] = (float) $assignedRooms->sum('security_deposit');
        $validated['maintenance_charges'] = (float) $assignedRooms->sum('maintenance_charges');
        $validated['other_charges'] = (float) $assignedRooms->sum('other_charges');
        $validated['eb_unit_rate'] = (float) ($assignedRooms->first()->eb_unit_rate ?? 10.00);

        $tenant->update($validated);
        $tenant->rooms()->sync($newRoomIds);

        // Mark all new rooms as occupied
        Room::whereIn('id', $newRoomIds)->update(['occupancy_status' => 'occupied']);

        // Check any removed rooms to see if they should revert to available
        $removedRoomIds = array_diff($oldRoomIds, $newRoomIds);
        foreach ($removedRoomIds as $remId) {
            $hasOtherActive = DB::table('tenant_rooms')
                ->join('tenants', 'tenant_rooms.tenant_id', '=', 'tenants.id')
                ->where('tenant_rooms.room_id', $remId)
                ->where('tenants.id', '!=', $tenant->id)
                ->where('tenants.status', 1)
                ->whereNull('tenants.deleted_at')
                ->exists();

            if (!$hasOtherActive) {
                // Also check direct room_id
                $hasOtherDirect = Tenant::where('room_id', $remId)
                    ->where('id', '!=', $tenant->id)
                    ->where('status', 1)
                    ->exists();

                if (!$hasOtherDirect) {
                    Room::where('id', $remId)->update(['occupancy_status' => 'available']);
                }
            }
        }

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
        $roomIds = $tenant->rooms()->pluck('rooms.id')->toArray();
        if (empty($roomIds) && $tenant->room_id) {
            $roomIds = [$tenant->room_id];
        }

        $tenant->rooms()->detach();
        $tenant->delete();

        foreach ($roomIds as $rId) {
            $hasOtherActive = DB::table('tenant_rooms')
                ->join('tenants', 'tenant_rooms.tenant_id', '=', 'tenants.id')
                ->where('tenant_rooms.room_id', $rId)
                ->where('tenants.status', 1)
                ->whereNull('tenants.deleted_at')
                ->exists();

            if (!$hasOtherActive) {
                $hasOtherDirect = Tenant::where('room_id', $rId)
                    ->where('status', 1)
                    ->exists();

                if (!$hasOtherDirect) {
                    Room::where('id', $rId)->update(['occupancy_status' => 'available']);
                }
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

        $roomIds = $tenant->rooms()->pluck('rooms.id')->toArray();
        if (empty($roomIds) && $tenant->room_id) {
            $roomIds = [$tenant->room_id];
        }

        if ($tenant->status == 1) {
            Room::whereIn('id', $roomIds)->update(['occupancy_status' => 'occupied']);
        } else {
            foreach ($roomIds as $rId) {
                $hasOtherActive = DB::table('tenant_rooms')
                    ->join('tenants', 'tenant_rooms.tenant_id', '=', 'tenants.id')
                    ->where('tenant_rooms.room_id', $rId)
                    ->where('tenants.id', '!=', $tenant->id)
                    ->where('tenants.status', 1)
                    ->whereNull('tenants.deleted_at')
                    ->exists();

                if (!$hasOtherActive) {
                    $hasOtherDirect = Tenant::where('room_id', $rId)
                        ->where('id', '!=', $tenant->id)
                        ->where('status', 1)
                        ->exists();

                    if (!$hasOtherDirect) {
                        Room::where('id', $rId)->update(['occupancy_status' => 'available']);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tenant status and room occupancy updated successfully.'
        ]);
    }

    /**
     * Export Tenants to CSV with UTF-8 BOM respecting active filters
     */
    public function export(Request $request): StreamedResponse
    {
        $fileName = 'srm_residence_tenants_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($handle, [
                'S.No',
                'Resident Name',
                'Phone Number',
                'Email Address',
                'Emergency Contact',
                'Assigned Room(s)',
                'Floor',
                'Room Type',
                'Commence Date',
                'Monthly Rent (INR)',
                'Security Deposit (INR)',
                'Initial EB Reading',
                'Total Invoiced (INR)',
                'Total Paid (INR)',
                'Current Dues (INR)',
                'Status',
            ]);

            $query = Tenant::with(['room', 'rooms', 'bills']);

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status === 'active' ? 1 : 0);
            }
            if ($request->filled('room_id') && $request->room_id !== 'all') {
                $query->where(function ($q) use ($request) {
                    $q->where('room_id', $request->room_id)
                        ->orWhereHas('rooms', fn($rq) => $rq->where('rooms.id', $request->room_id));
                });
            }
            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('room_number', 'like', "%{$s}%");
                });
            }

            $query->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');

            $index = 1;
            $query->chunk(100, function ($tenants) use ($handle, &$index) {
                foreach ($tenants as $t) {
                    $rooms = $t->assigned_rooms;
                    $roomNos = $rooms->isNotEmpty() ? $rooms->pluck('room_number')->implode(', ') : ($t->room_number ?? 'N/A');
                    $floors = $rooms->isNotEmpty() ? $rooms->pluck('floor')->filter()->unique()->implode(', ') : ($t->room->floor ?? '');
                    $types = $rooms->isNotEmpty() ? $rooms->pluck('room_type')->filter()->unique()->implode(', ') : ($t->room_type ?? '');

                    $inv = (float) $t->bills->sum('amount');
                    $paid = (float) $t->bills->sum('paid_amount');
                    $due = (float) $t->bills->sum('due_amount');

                    fputcsv($handle, [
                        $index++,
                        $t->name,
                        $t->phone ?: '',
                        $t->email ?: '',
                        $t->emergency_contact ?: '',
                        $roomNos,
                        $floors,
                        $types,
                        $t->commence_date ? $t->commence_date->format('Y-m-d') : '',
                        number_format($t->monthly_rent, 2, '.', ''),
                        number_format($t->security_deposit, 2, '.', ''),
                        $t->eb_start_reading ?? '0',
                        number_format($inv, 2, '.', ''),
                        number_format($paid, 2, '.', ''),
                        number_format($due, 2, '.', ''),
                        $t->status ? 'Active' : 'Inactive',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
