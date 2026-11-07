<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Room::with(['currentTenant', 'activeTenants'])->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');

            if ($request->filled('floor')) {
                $query->where('floor', $request->floor);
            }
            if ($request->filled('occupancy_status')) {
                if ($request->occupancy_status === 'self') {
                    $query->where(function($q) {
                        $q->where('occupancy_status', 'self')->orWhere('is_self', 1);
                    });
                } else {
                    $query->where('occupancy_status', $request->occupancy_status);
                }
            }
            if ($request->filled('room_type')) {
                $query->where('room_type', $request->room_type);
            }

            return DataTables::of($query)
                ->orderColumn('room_number', function ($query, $order) {
                    $query->orderByRaw("CAST(room_number AS UNSIGNED) {$order}, room_number {$order}");
                })
                ->orderColumn('room_badge', function ($query, $order) {
                    $query->orderByRaw("CAST(room_number AS UNSIGNED) {$order}, room_number {$order}");
                })
                ->addIndexColumn()
                ->addColumn('room_badge', function ($row) {
                    return '<div class="d-flex align-items-center">
                        <span class="badge bg-primary text-white fw-bold px-2 py-1 fs-6 me-2">
                            <i class="mdi mdi-door-closed me-1"></i> ' . e($row->room_number) . '
                        </span>
                        <div>
                            <span class="small fw-semibold text-secondary d-block">' . e($row->floor) . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('type_capacity', function ($row) {
                    return '<div>
                        <span class="fw-bold text-dark d-block">' . e($row->room_type) . '</span>
                        <small class="text-muted"><i class="fas fa-users me-1"></i> Capacity: ' . $row->capacity . ' ' . Str::plural('bed', $row->capacity) . '</small>
                    </div>';
                })
                ->addColumn('rent_display', function ($row) {
                    if ($row->is_self || $row->occupancy_status === 'self') {
                        return '<div>
                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="fas fa-user-shield me-1"></i> Self / Owner Occupied</span>
                            <small class="text-muted d-block mt-1 font-11">No rent or charges</small>
                        </div>';
                    }

                    $extra = [];
                    if ($row->eb_unit_rate > 0) $extra[] = 'EB: ₹' . number_format($row->eb_unit_rate, 2) . '/unit';
                    if ($row->maintenance_charges > 0) $extra[] = 'Maint: ₹' . number_format($row->maintenance_charges);
                    if ($row->other_charges > 0) $extra[] = 'Other: ₹' . number_format($row->other_charges);
                    $extraHtml = !empty($extra) ? '<small class="text-muted d-block font-11">+' . implode(' | ', $extra) . '</small>' : '';

                    return '<div>
                        <span class="fw-bold text-dark">₹' . number_format($row->monthly_rent) . '</span>
                        ' . $extraHtml . '
                        <small class="text-muted d-block">Deposit: ₹' . number_format($row->security_deposit) . '</small>
                    </div>';
                })
                ->addColumn('occupancy_badge', function ($row) {
                    if ($row->is_self || $row->occupancy_status === 'self') {
                        return '<span class="badge bg-info-subtle text-info border border-info px-2 py-1"><i class="fas fa-user-shield me-1"></i> Self Occupied</span>';
                    }
                    $tenantHtml = '';
                    $activeResidents = $row->active_residents;
                    if ($activeResidents->isNotEmpty()) {
                        $names = $activeResidents->pluck('name')->unique()->implode(', ');
                        $tenantHtml = '<div class="mt-1 small text-dark"><i class="fas fa-user-circle text-primary me-1"></i> <span class="fw-semibold">' . e($names) . '</span></div>';
                    }
                    if ($row->occupancy_status === 'available') {
                        return '<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> Available</span>';
                    } elseif ($row->occupancy_status === 'occupied') {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1"><i class="fas fa-user-lock me-1"></i> Occupied</span>' . $tenantHtml;
                    } else {
                        return '<span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1"><i class="fas fa-tools me-1"></i> Maintenance</span>';
                    }
                })
                ->addColumn('amenities_display', function ($row) {
                    return '<small class="text-muted text-wrap d-inline-block" style="max-width: 200px;">' . e($row->amenities ?? 'Standard amenities') . '</small>';
                })
                ->addColumn('status_toggle', function ($row) {
                    $checked = $row->status ? 'checked' : '';
                    return '<div class="form-check form-switch mb-0">
                        <input class="form-check-input room-status-toggle" type="checkbox" ' . $checked . ' data-id="' . $row->id . '" style="cursor:pointer; width: 36px; height: 18px;">
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group btn-group-sm" role="group">';
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-edit-room" data-id="' . $row->id . '" title="Edit Room"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-room" data-id="' . $row->id . '" data-number="' . e($row->room_number) . '" title="Delete Room"><i class="fas fa-trash-alt"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['room_badge', 'type_capacity', 'rent_display', 'occupancy_badge', 'amenities_display', 'status_toggle', 'action'])
                ->make(true);
        }

        $stats = $this->getStats();

        return view('backend.rooms.index', compact('stats'));
    }

    private function getStats()
    {
        $totalRentCapacity = (float) Room::where('is_self', 0)->sum('monthly_rent');
        $totalDepositCapacity = (float) Room::where('is_self', 0)->sum('security_deposit');
        $occupiedRent = (float) Tenant::where('status', 1)->sum('monthly_rent');
        $heldDeposits = (float) Tenant::where('status', 1)->sum('security_deposit');

        return [
            'totalRooms' => Room::count(),
            'availableRooms' => Room::where('occupancy_status', 'available')->count(),
            'occupiedRooms' => Room::where('occupancy_status', 'occupied')->count(),
            'selfRooms' => Room::where(function($q) {
                $q->where('occupancy_status', 'self')->orWhere('is_self', 1);
            })->count(),
            'maintenanceRooms' => Room::where('occupancy_status', 'maintenance')->count(),
            'totalRentCapacity' => $totalRentCapacity,
            'totalRentCapacityFormatted' => '₹' . number_format($totalRentCapacity),
            'totalDepositCapacity' => $totalDepositCapacity,
            'totalDepositCapacityFormatted' => '₹' . number_format($totalDepositCapacity),
            'occupiedRent' => $occupiedRent,
            'occupiedRentFormatted' => '₹' . number_format($occupiedRent),
            'heldDeposits' => $heldDeposits,
            'heldDepositsFormatted' => '₹' . number_format($heldDeposits),
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:20', Rule::unique('rooms', 'room_number')->whereNull('deleted_at')],
            'floor' => 'required|string|max:50',
            'room_type' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1|max:30',
            'monthly_rent' => 'required|numeric|min:0',
            'eb_unit_rate' => 'nullable|numeric|min:0|max:100',
            'maintenance_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'occupancy_status' => 'required|in:available,occupied,maintenance,self',
            'is_self' => 'nullable|boolean',
            'amenities' => 'nullable|string|max:1000',
        ], [
            'room_number.required' => 'Room number is required.',
            'room_number.unique' => 'Room number already exists. Please enter a unique room number.',
            'room_number.max' => 'Room number may not be greater than 20 characters.',
            'floor.required' => 'Please select a floor.',
            'room_type.required' => 'Please select a room type.',
            'capacity.required' => 'Bed capacity is required.',
            'capacity.min' => 'Bed capacity must be at least 1 person.',
            'capacity.max' => 'Bed capacity cannot exceed 30 persons.',
            'monthly_rent.required' => 'Monthly rent is required.',
            'monthly_rent.min' => 'Monthly rent cannot be negative.',
            'eb_unit_rate.min' => 'EB unit rate cannot be negative.',
            'eb_unit_rate.max' => 'EB unit rate cannot exceed ₹100 per unit.',
            'maintenance_charges.min' => 'Maintenance charges cannot be negative.',
            'other_charges.min' => 'Other charges cannot be negative.',
            'security_deposit.min' => 'Security deposit cannot be negative.',
            'occupancy_status.required' => 'Please select an occupancy status.',
        ]);

        $isSelf = $request->boolean('is_self') || $request->occupancy_status === 'self';
        if ($isSelf) {
            $validated['is_self'] = 1;
            $validated['occupancy_status'] = 'self';
            $validated['monthly_rent'] = 0.00;
            $validated['security_deposit'] = 0.00;
            $validated['eb_unit_rate'] = 0.00;
            $validated['maintenance_charges'] = 0.00;
            $validated['other_charges'] = 0.00;
        } else {
            $validated['is_self'] = 0;
            $validated['security_deposit'] = $validated['security_deposit'] ?? 0;
        }
        $validated['status'] = 1;

        Room::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Room created successfully!',
                'stats' => $this->getStats()
            ]);
        }

        return redirect('/admin/rooms')->with('success', 'Room created successfully!');
    }

    public function getData(Room $room)
    {
        return response()->json([
            'success' => true,
            'room' => $room
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:20', Rule::unique('rooms', 'room_number')->ignore($room->id)->whereNull('deleted_at')],
            'floor' => 'required|string|max:50',
            'room_type' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1|max:30',
            'monthly_rent' => 'required|numeric|min:0',
            'eb_unit_rate' => 'nullable|numeric|min:0|max:100',
            'maintenance_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'occupancy_status' => 'required|in:available,occupied,maintenance,self',
            'is_self' => 'nullable|boolean',
            'amenities' => 'nullable|string|max:1000',
        ], [
            'room_number.required' => 'Room number is required.',
            'room_number.unique' => 'Room number already exists on another unit.',
            'room_number.max' => 'Room number may not be greater than 20 characters.',
            'floor.required' => 'Please select a floor.',
            'room_type.required' => 'Please select a room type.',
            'capacity.required' => 'Bed capacity is required.',
            'capacity.min' => 'Bed capacity must be at least 1 person.',
            'capacity.max' => 'Bed capacity cannot exceed 30 persons.',
            'monthly_rent.required' => 'Monthly rent is required.',
            'monthly_rent.min' => 'Monthly rent cannot be negative.',
            'eb_unit_rate.min' => 'EB unit rate cannot be negative.',
            'eb_unit_rate.max' => 'EB unit rate cannot exceed ₹100 per unit.',
            'maintenance_charges.min' => 'Maintenance charges cannot be negative.',
            'other_charges.min' => 'Other charges cannot be negative.',
            'security_deposit.min' => 'Security deposit cannot be negative.',
            'occupancy_status.required' => 'Please select an occupancy status.',
        ]);

        $isSelf = $request->boolean('is_self') || $request->occupancy_status === 'self';
        if ($isSelf) {
            $validated['is_self'] = 1;
            $validated['occupancy_status'] = 'self';
            $validated['monthly_rent'] = 0.00;
            $validated['security_deposit'] = 0.00;
            $validated['eb_unit_rate'] = 0.00;
            $validated['maintenance_charges'] = 0.00;
            $validated['other_charges'] = 0.00;
        } else {
            $validated['is_self'] = 0;
            $validated['security_deposit'] = $validated['security_deposit'] ?? 0;
        }

        $room->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully!',
                'stats' => $this->getStats()
            ]);
        }

        return redirect('/admin/rooms')->with('success', 'Room updated successfully!');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json([
            'success' => true,
            'status' => '1',
            'message' => 'Room removed successfully!',
            'stats' => $this->getStats()
        ]);
    }

    public function updateStatus(Request $request)
    {
        $room = Room::findOrFail($request->id);
        $room->status = $request->status;
        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Room status updated successfully.',
            'stats' => $this->getStats()
        ]);
    }

    public function updateOccupancy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:rooms,id',
            'occupancy_status' => 'required|in:available,occupied,maintenance,self'
        ]);

        $room = Room::findOrFail($request->id);
        $room->occupancy_status = $request->occupancy_status;
        if ($request->occupancy_status === 'self') {
            $room->is_self = 1;
            $room->monthly_rent = 0.00;
            $room->security_deposit = 0.00;
            $room->eb_unit_rate = 0.00;
            $room->maintenance_charges = 0.00;
            $room->other_charges = 0.00;
        } else {
            $room->is_self = 0;
        }
        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Room occupancy updated successfully.',
            'stats' => $this->getStats()
        ]);
    }
}

