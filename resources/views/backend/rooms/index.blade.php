@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .room-stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                border: 1px solid #e2e8f0;
                transition: transform 0.2s;
            }
            .room-stat-card:hover {
                transform: translateY(-2px);
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #0ea1b1 !important;
                color: #fff !important;
                border-color: #0ea1b1 !important;
                border-radius: 5px;
            }
            .filter-select {
                border-radius: 8px;
                border: 1px solid #ced4da;
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
        </style>
    @endpush

    <!-- Page Header & Action -->
    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-6">
            <h4 class="page-title-main page">Rooms Management</h4>
            <p class="text-muted small mb-0">Manage residence rooms, floor allocations, occupancies, and rental rates dynamically.</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="mdi mdi-door-closed me-1"></i> Create Room
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mt-3 g-3">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="room-stat-card border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">Total Rooms</div>
                <h3 class="fw-bold my-1 text-primary" id="statTotalRooms">{{ $stats['totalRooms'] }}</h3>
                <small class="text-muted">Total inventory units</small>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="room-stat-card border-start border-4 border-success">
                <div class="text-muted small text-uppercase fw-bold">Available Rooms</div>
                <h3 class="fw-bold my-1 text-success" id="statAvailableRooms">{{ $stats['availableRooms'] }}</h3>
                <small class="text-muted">Ready for lease</small>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="room-stat-card border-start border-4 border-danger">
                <div class="text-muted small text-uppercase fw-bold">Occupied Units</div>
                <h3 class="fw-bold my-1 text-danger" id="statOccupiedRooms">{{ $stats['occupiedRooms'] }}</h3>
                <small class="text-muted">Currently tenanted</small>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="room-stat-card border-start border-4 border-info">
                <div class="text-muted small text-uppercase fw-bold">Owner Occupied (Self)</div>
                <h3 class="fw-bold my-1 text-info" id="statSelfRooms">{{ $stats['selfRooms'] ?? 0 }}</h3>
                <small class="text-muted">Used by owners (no rent)</small>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="room-stat-card border-start border-4 border-warning">
                <div class="text-muted small text-uppercase fw-bold">Under Maintenance</div>
                <h3 class="fw-bold my-1 text-warning" id="statMaintenanceRooms">{{ $stats['maintenanceRooms'] }}</h3>
                <small class="text-muted">Temporarily blocked</small>
            </div>
        </div>
    </div>

    <!-- Room Inventory Financial Summary (Rent Capacity & Deposits) -->
    <div class="row mt-2 g-3">
        <div class="col-md-6 col-xl-3">
            <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted font-11 text-uppercase fw-bold d-block">Total Rent Capacity</span>
                    <span class="fs-6 fw-bold text-dark" id="statTotalRentCapacity">₹{{ number_format($stats['totalRentCapacity'] ?? 0) }} <small class="text-muted fw-normal font-11">/ mo</small></span>
                </div>
                <span class="badge bg-primary-subtle text-primary p-2"><i class="fas fa-coins fa-lg"></i></span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted font-11 text-uppercase fw-bold d-block">Deposit Potential</span>
                    <span class="fs-6 fw-bold text-dark" id="statTotalDepositCapacity">₹{{ number_format($stats['totalDepositCapacity'] ?? 0) }}</span>
                </div>
                <span class="badge bg-info-subtle text-info p-2"><i class="fas fa-shield-alt fa-lg"></i></span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted font-11 text-uppercase fw-bold d-block">Active Leased Rent</span>
                    <span class="fs-6 fw-bold text-success" id="statOccupiedRent">₹{{ number_format($stats['occupiedRent'] ?? 0) }} <small class="text-muted fw-normal font-11">/ mo</small></span>
                </div>
                <span class="badge bg-success-subtle text-success p-2"><i class="fas fa-hand-holding-dollar fa-lg"></i></span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted font-11 text-uppercase fw-bold d-block">Active Deposits Held</span>
                    <span class="fs-6 fw-bold text-primary" id="statHeldDeposits">₹{{ number_format($stats['heldDeposits'] ?? 0) }}</span>
                </div>
                <span class="badge p-2" style="background-color: #f3e8ff; color: #8b5cf6;"><i class="fas fa-vault fa-lg"></i></span>
            </div>
        </div>
    </div>

    <!-- Filters & DataTable -->
    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <!-- Filters row -->
                    <div class="row mb-3 g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Filter by Floor</label>
                            <select id="filterFloor" class="form-select filter-select">
                                <option value="">All Floors</option>
                                <option value="1st Floor">1st Floor</option>
                                <option value="2nd Floor">2nd Floor</option>
                                <option value="3rd Floor">3rd Floor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Filter by Occupancy</label>
                            <select id="filterOccupancy" class="form-select filter-select">
                                <option value="">All Statuses</option>
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="self">Self (Owner Occupied)</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Filter by Room Type</label>
                            <select id="filterType" class="form-select filter-select">
                                <option value="">All Room Types</option>
                                <option value="Single Deluxe">Single Deluxe</option>
                                <option value="Double Sharing">Double Sharing</option>
                                <option value="Triple Sharing">Triple Sharing</option>
                                <option value="Executive Suite">Executive Suite</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <button id="btnResetFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                            <button type="button" id="btnExportRooms" class="btn btn-outline-success btn-sm ms-2">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table job-table w-100" id="roomsTable">
                            <thead class="table-theme text-white text-nowrap">
                                <tr>
                                    <th style="width: 4%;">#</th>
                                    <th style="min-width: 140px;">Room & Floor</th>
                                    <th style="min-width: 160px;">Type & Capacity</th>
                                    <th style="min-width: 150px;" class="text-nowrap">Monthly Rent</th>
                                    <th style="min-width: 140px;" class="text-nowrap">Occupancy</th>
                                    <th style="min-width: 180px;">Amenities</th>
                                    <th style="min-width: 90px;" class="text-nowrap">Active</th>
                                    <th style="min-width: 110px;" class="text-center text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CREATE ROOM MODAL -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="addRoomModalLabel">
                        <i class="mdi mdi-door-closed me-1"></i> Create New Room
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addRoomForm" action="{{ url('/admin/rooms') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div id="addRoomErrors" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Room Number <span class="text-danger">*</span></label>
                                <input type="text" name="room_number" class="form-control" placeholder="e.g. 101, 204, 305" maxlength="20" required>
                                <small class="text-muted">Unique unit identification number.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Floor <span class="text-danger">*</span></label>
                                <select name="floor" class="form-select" required>
                                    <option value="Ground Floor">Ground Floor</option>
                                    <option value="1st Floor" selected>1st Floor</option>
                                    <option value="2nd Floor">2nd Floor</option>
                                    <option value="3rd Floor">3rd Floor</option>
                                    <option value="4th Floor">4th Floor</option>
                                    <option value="5th Floor">5th Floor</option>
                                    <option value="Penthouse">Penthouse</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Room Type <span class="text-danger">*</span></label>
                                <select name="room_type" class="form-select" required>
                                    <option value="Single Deluxe" selected>Single Deluxe</option>
                                    <option value="Double Sharing">Double Sharing</option>
                                    <option value="Triple Sharing">Triple Sharing</option>
                                    <option value="Four Sharing">Four Sharing</option>
                                    <option value="Executive Suite">Executive Suite</option>
                                    <option value="Studio Apartment">Studio Apartment</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bed Capacity <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" class="form-control" value="1" min="1" max="30" required>
                            </div>

                            <div class="col-12">
                                <div class="p-2 px-3 border border-info rounded bg-info-subtle d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-user-shield text-info fs-5"></i>
                                        <div>
                                            <span class="fw-bold font-13 text-dark d-block">Mark as Self (Owner Occupied)</span>
                                            <small class="text-muted font-11">Room is occupied by owners — automatically zeroes all rent and charges.</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_self" id="add_is_self" value="1" style="width: 2.8em; height: 1.4em; cursor: pointer;">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="monthly_rent" id="add_monthly_rent" class="form-control" placeholder="8500" value="8500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Security Deposit (₹)</label>
                                <input type="number" step="0.01" min="0" name="security_deposit" id="add_security_deposit" class="form-control" placeholder="10000" value="10000">
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 p-2 bg-light rounded border">
                                    <span class="fw-bold small text-muted text-uppercase font-11">Rent Structure:</span>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="add_room_rent_type" id="add_room_rent_type_custom" value="custom" checked>
                                        <label class="form-check-label fw-semibold font-12" for="add_room_rent_type_custom">Split Charges</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="add_room_rent_type" id="add_room_rent_type_fixed" value="fixed">
                                        <label class="form-check-label fw-bold text-success font-12" for="add_room_rent_type_fixed">
                                            <i class="fas fa-check-circle me-1"></i> Fixed Rent
                                        </label>
                                    </div>
                                    <small class="text-muted ms-auto d-none d-sm-inline font-11">(Fixed Rent zeroes Maintenance & Other charges)</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">EB Rate (₹/Unit)</label>
                                <input type="number" step="0.01" min="0" max="100" name="eb_unit_rate" id="add_eb_unit_rate" class="form-control" placeholder="10.00" value="10.00">
                                <small class="text-muted">Electricity cost per unit (kWh)</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Maintenance Charges (₹)</label>
                                <input type="number" step="0.01" min="0" name="maintenance_charges" id="add_maintenance_charges" class="form-control" placeholder="0.00" value="0.00">
                                <small class="text-muted">Room upkeep / cleaning</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Other Charges (₹)</label>
                                <input type="number" step="0.01" min="0" name="other_charges" id="add_other_charges" class="form-control" placeholder="0.00" value="0.00">
                                <small class="text-muted">Wi-Fi, water, or miscellaneous</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Initial Occupancy Status <span class="text-danger">*</span></label>
                                <select name="occupancy_status" id="add_occupancy_status" class="form-select" required>
                                    <option value="available">Available (Vacant & Ready)</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="self">Self (Owner Occupied)</option>
                                    <option value="maintenance">Under Maintenance</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Amenities & Features</label>
                                <textarea name="amenities" class="form-control" rows="2" placeholder="e.g. AC, Attached Bathroom, Wi-Fi, Balcony, Wardrobe, Study Table"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white" id="btnSaveRoom">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="saveRoomSpinner"></span>
                            Save Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT ROOM MODAL -->
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="editRoomModalLabel">
                        <i class="mdi mdi-door-closed me-1"></i> Edit Room
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editRoomForm">
                    @csrf
                    <input type="hidden" id="edit_room_id" name="room_id">
                    <div class="modal-body p-4">
                        <div id="editRoomErrors" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Room Number <span class="text-danger">*</span></label>
                                <input type="text" id="edit_room_number" name="room_number" class="form-control" maxlength="20" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Floor <span class="text-danger">*</span></label>
                                <select id="edit_floor" name="floor" class="form-select" required>
                                    <option value="Ground Floor">Ground Floor</option>
                                    <option value="1st Floor">1st Floor</option>
                                    <option value="2nd Floor">2nd Floor</option>
                                    <option value="3rd Floor">3rd Floor</option>
                                    <option value="4th Floor">4th Floor</option>
                                    <option value="5th Floor">5th Floor</option>
                                    <option value="Penthouse">Penthouse</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Room Type <span class="text-danger">*</span></label>
                                <select id="edit_room_type" name="room_type" class="form-select" required>
                                    <option value="Single Deluxe">Single Deluxe</option>
                                    <option value="Double Sharing">Double Sharing</option>
                                    <option value="Triple Sharing">Triple Sharing</option>
                                    <option value="Four Sharing">Four Sharing</option>
                                    <option value="Executive Suite">Executive Suite</option>
                                    <option value="Studio Apartment">Studio Apartment</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bed Capacity <span class="text-danger">*</span></label>
                                <input type="number" id="edit_capacity" name="capacity" class="form-control" min="1" max="30" required>
                            </div>

                            <div class="col-12">
                                <div class="p-2 px-3 border border-info rounded bg-info-subtle d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-user-shield text-info fs-5"></i>
                                        <div>
                                            <span class="fw-bold font-13 text-dark d-block">Mark as Self (Owner Occupied)</span>
                                            <small class="text-muted font-11">Room is occupied by owners — automatically zeroes all rent and charges.</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_self" id="edit_is_self" value="1" style="width: 2.8em; height: 1.4em; cursor: pointer;">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" id="edit_monthly_rent" name="monthly_rent" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Security Deposit (₹)</label>
                                <input type="number" step="0.01" min="0" id="edit_security_deposit" name="security_deposit" class="form-control">
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 p-2 bg-light rounded border">
                                    <span class="fw-bold small text-muted text-uppercase font-11">Rent Structure:</span>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="edit_room_rent_type" id="edit_room_rent_type_custom" value="custom" checked>
                                        <label class="form-check-label fw-semibold font-12" for="edit_room_rent_type_custom">Split Charges</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="edit_room_rent_type" id="edit_room_rent_type_fixed" value="fixed">
                                        <label class="form-check-label fw-bold text-success font-12" for="edit_room_rent_type_fixed">
                                            <i class="fas fa-check-circle me-1"></i> Fixed Rent
                                        </label>
                                    </div>
                                    <small class="text-muted ms-auto d-none d-sm-inline font-11">(Fixed Rent zeroes Maintenance & Other charges)</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">EB Rate (₹/Unit)</label>
                                <input type="number" step="0.01" min="0" max="100" id="edit_eb_unit_rate" name="eb_unit_rate" class="form-control" placeholder="10.00">
                                <small class="text-muted">Electricity cost per unit (kWh)</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Maintenance Charges (₹)</label>
                                <input type="number" step="0.01" min="0" id="edit_maintenance_charges" name="maintenance_charges" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Other Charges (₹)</label>
                                <input type="number" step="0.01" min="0" id="edit_other_charges" name="other_charges" class="form-control" placeholder="0.00">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Occupancy Status <span class="text-danger">*</span></label>
                                <select id="edit_occupancy_status" name="occupancy_status" class="form-select" required>
                                    <option value="available">Available (Vacant & Ready)</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="self">Self (Owner Occupied)</option>
                                    <option value="maintenance">Under Maintenance</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Amenities & Features</label>
                                <textarea id="edit_amenities" name="amenities" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white" id="btnUpdateRoom">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="updateRoomSpinner"></span>
                            Update Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

        <script>
            $(document).ready(function() {
                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                function updateStatCards(stats) {
                    if (stats) {
                        $('#statTotalRooms').text(stats.totalRooms);
                        $('#statAvailableRooms').text(stats.availableRooms);
                        $('#statOccupiedRooms').text(stats.occupiedRooms);
                        $('#statSelfRooms').text(stats.selfRooms || 0);
                        $('#statMaintenanceRooms').text(stats.maintenanceRooms);
                        if (stats.totalRentCapacityFormatted) $('#statTotalRentCapacity').html(stats.totalRentCapacityFormatted + ' <small class="text-muted fw-normal font-11">/ mo</small>');
                        if (stats.totalDepositCapacityFormatted) $('#statTotalDepositCapacity').text(stats.totalDepositCapacityFormatted);
                        if (stats.occupiedRentFormatted) $('#statOccupiedRent').html(stats.occupiedRentFormatted + ' <small class="text-muted fw-normal font-11">/ mo</small>');
                        if (stats.heldDepositsFormatted) $('#statHeldDeposits').text(stats.heldDepositsFormatted);
                    }
                }

                // Initialize DataTable
                var table = $('#roomsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('/admin/rooms') }}",
                        data: function(d) {
                            d.floor = $('#filterFloor').val();
                            d.occupancy_status = $('#filterOccupancy').val();
                            d.room_type = $('#filterType').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'room_badge', name: 'room_number' },
                        { data: 'type_capacity', name: 'room_type' },
                        { data: 'rent_display', name: 'monthly_rent' },
                        { data: 'occupancy_badge', name: 'occupancy_status' },
                        { data: 'amenities_display', name: 'amenities' },
                        { data: 'status_toggle', name: 'status', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    order: [[1, 'asc']],
                    language: {
                        search: "",
                        searchPlaceholder: "Search rooms...",
                        lengthMenu: "Show _MENU_ entries",
                        zeroRecords: "No matching rooms found",
                        info: "Showing _START_ to _END_ of _TOTAL_ rooms",
                        infoEmpty: "Showing 0 to 0 of 0 rooms",
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    }
                });

                // Filter triggers
                $('#filterFloor, #filterOccupancy, #filterType').on('change', function() {
                    table.ajax.reload();
                });

                $('#btnResetFilters').on('click', function() {
                    $('#filterFloor').val('');
                    $('#filterOccupancy').val('');
                    $('#filterType').val('');
                    table.search('').draw();
                });

                $('#btnExportRooms').on('click', function() {
                    var params = {
                        floor: $('#filterFloor').val(),
                        occupancy_status: $('#filterOccupancy').val(),
                        room_type: $('#filterType').val(),
                        search: table.search()
                    };
                    window.location.href = "{{ route('rooms.export') }}?" + $.param(params);
                });

                // Helper to toggle Self (Owner Occupied) state in Create Modal
                function applyAddSelfState(isSelf) {
                    if (isSelf) {
                        $('#add_is_self').prop('checked', true);
                        $('#add_occupancy_status').val('self');
                        $('#add_room_rent_type_fixed').prop('checked', true);
                        $('#add_monthly_rent').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#add_security_deposit').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#add_eb_unit_rate').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#add_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#add_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                    } else {
                        $('#add_is_self').prop('checked', false);
                        if ($('#add_occupancy_status').val() === 'self') {
                            $('#add_occupancy_status').val('available');
                        }
                        $('#add_monthly_rent').prop('readonly', false).removeClass('bg-light');
                        if (parseFloat($('#add_monthly_rent').val()) === 0 || !$('#add_monthly_rent').val()) {
                            $('#add_monthly_rent').val('8500');
                        }
                        $('#add_security_deposit').prop('readonly', false).removeClass('bg-light');
                        if (parseFloat($('#add_security_deposit').val()) === 0 || !$('#add_security_deposit').val()) {
                            $('#add_security_deposit').val('10000');
                        }
                        $('#add_eb_unit_rate').prop('readonly', false).removeClass('bg-light');
                        if (parseFloat($('#add_eb_unit_rate').val()) === 0 || !$('#add_eb_unit_rate').val()) {
                            $('#add_eb_unit_rate').val('10.00');
                        }
                        if ($('#add_room_rent_type_fixed').is(':checked')) {
                            $('#add_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                            $('#add_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        } else {
                            $('#add_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                            $('#add_other_charges').prop('readonly', false).removeClass('bg-light');
                        }
                    }
                }

                // Helper to toggle Self (Owner Occupied) state in Edit Modal
                function applyEditSelfState(isSelf) {
                    if (isSelf) {
                        $('#edit_is_self').prop('checked', true);
                        $('#edit_occupancy_status').val('self');
                        $('#edit_room_rent_type_fixed').prop('checked', true);
                        $('#edit_monthly_rent').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#edit_security_deposit').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#edit_eb_unit_rate').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#edit_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#edit_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                    } else {
                        $('#edit_is_self').prop('checked', false);
                        if ($('#edit_occupancy_status').val() === 'self') {
                            $('#edit_occupancy_status').val('available');
                        }
                        $('#edit_monthly_rent').prop('readonly', false).removeClass('bg-light');
                        $('#edit_security_deposit').prop('readonly', false).removeClass('bg-light');
                        $('#edit_eb_unit_rate').prop('readonly', false).removeClass('bg-light');
                        if ($('#edit_room_rent_type_fixed').is(':checked')) {
                            $('#edit_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                            $('#edit_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        } else {
                            $('#edit_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                            $('#edit_other_charges').prop('readonly', false).removeClass('bg-light');
                        }
                    }
                }

                // Events for Mark as Self in Create Modal
                $(document).on('change', '#add_is_self', function() {
                    applyAddSelfState(this.checked);
                });

                $(document).on('change', '#add_occupancy_status', function() {
                    if ($(this).val() === 'self') {
                        applyAddSelfState(true);
                    } else if ($('#add_is_self').is(':checked')) {
                        applyAddSelfState(false);
                    }
                });

                // Events for Mark as Self in Edit Modal
                $(document).on('change', '#edit_is_self', function() {
                    applyEditSelfState(this.checked);
                });

                $(document).on('change', '#edit_occupancy_status', function() {
                    if ($(this).val() === 'self') {
                        applyEditSelfState(true);
                    } else if ($('#edit_is_self').is(':checked')) {
                        applyEditSelfState(false);
                    }
                });

                // Toggle Fixed Rent vs Split Charges in Create Room Modal
                $(document).on('change', 'input[name="add_room_rent_type"]', function() {
                    if ($('#add_is_self').is(':checked')) return;
                    if ($('#add_room_rent_type_fixed').is(':checked')) {
                        $('#add_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#add_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                    } else {
                        $('#add_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                        $('#add_other_charges').prop('readonly', false).removeClass('bg-light');
                    }
                });

                // Toggle Fixed Rent vs Split Charges in Edit Room Modal
                $(document).on('change', 'input[name="edit_room_rent_type"]', function() {
                    if ($('#edit_is_self').is(':checked')) return;
                    if ($('#edit_room_rent_type_fixed').is(':checked')) {
                        $('#edit_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#edit_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                    } else {
                        $('#edit_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                        $('#edit_other_charges').prop('readonly', false).removeClass('bg-light');
                    }
                });

                $('#addRoomModal').on('show.bs.modal', function() {
                    applyAddSelfState(false);
                    $('#add_room_rent_type_custom').prop('checked', true);
                    $('#add_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                    $('#add_other_charges').prop('readonly', false).removeClass('bg-light');
                });

                // 1. CREATE ROOM VIA AJAX
                $('#addRoomForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveRoom');
                    var $spinner = $('#saveRoomSpinner');
                    var $errors = $('#addRoomErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var roomNum = $.trim($form.find('input[name="room_number"]').val());
                    if (!roomNum) {
                        $errors.html('Room number is required.').removeClass('d-none');
                        $form.find('input[name="room_number"]').addClass('is-invalid').focus();
                        return;
                    }

                    var capacity = parseInt($form.find('input[name="capacity"]').val(), 10);
                    if (isNaN(capacity) || capacity < 1) {
                        $errors.html('Capacity must be at least 1 person.').removeClass('d-none');
                        $form.find('input[name="capacity"]').addClass('is-invalid').focus();
                        return;
                    }

                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/rooms') }}",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#addRoomModal').modal('hide');
                                $form[0].reset();
                                $form.find('.is-invalid').removeClass('is-invalid');
                                table.ajax.reload(null, false);
                                updateStatCards(response.stats);
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Room created successfully!'
                                });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var errorMsg = 'Please verify room details.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                $.each(xhr.responseJSON.errors, function(field, messages) {
                                    $form.find('[name="' + field + '"]').addClass('is-invalid');
                                });
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $errors.html(errorMsg).removeClass('d-none');
                        }
                    });
                });

                // Clear is-invalid on change
                $('#addRoomForm, #editRoomForm').on('input change', 'input, select, textarea', function() {
                    $(this).removeClass('is-invalid');
                });

                // 2. OPEN EDIT MODAL VIA AJAX
                $(document).on('click', '.btn-edit-room', function() {
                    var id = $(this).data('id');
                    $('#editRoomErrors').addClass('d-none').html('');

                    $.ajax({
                        url: "{{ url('/admin/rooms') }}/" + id + "/data",
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                var room = response.room;
                                $('#edit_room_id').val(room.id);
                                $('#edit_room_number').val(room.room_number);
                                $('#edit_floor').val(room.floor);
                                $('#edit_room_type').val(room.room_type);
                                $('#edit_capacity').val(room.capacity);
                                var isSelf = (room.is_self == 1 || room.occupancy_status === 'self');
                                if (isSelf) {
                                    applyEditSelfState(true);
                                } else {
                                    applyEditSelfState(false);
                                    $('#edit_monthly_rent').val(room.monthly_rent);
                                    $('#edit_security_deposit').val(room.security_deposit);
                                    $('#edit_eb_unit_rate').val(room.eb_unit_rate || '10.00');
                                    var maint = parseFloat(room.maintenance_charges) || 0;
                                    var other = parseFloat(room.other_charges) || 0;
                                    $('#edit_maintenance_charges').val(room.maintenance_charges || '0.00');
                                    $('#edit_other_charges').val(room.other_charges || '0.00');

                                    if (maint === 0 && other === 0) {
                                        $('#edit_room_rent_type_fixed').prop('checked', true);
                                        $('#edit_maintenance_charges').prop('readonly', true).addClass('bg-light');
                                        $('#edit_other_charges').prop('readonly', true).addClass('bg-light');
                                    } else {
                                        $('#edit_room_rent_type_custom').prop('checked', true);
                                        $('#edit_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                                        $('#edit_other_charges').prop('readonly', false).removeClass('bg-light');
                                    }
                                    $('#edit_occupancy_status').val(room.occupancy_status);
                                }
                                $('#edit_amenities').val(room.amenities);

                                $('#editRoomModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to fetch room details' });
                        }
                    });
                });

                // 3. UPDATE ROOM VIA AJAX
                $('#editRoomForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var id = $('#edit_room_id').val();
                    var $btn = $('#btnUpdateRoom');
                    var $spinner = $('#updateRoomSpinner');
                    var $errors = $('#editRoomErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var roomNum = $.trim($form.find('#edit_room_number').val());
                    if (!roomNum) {
                        $errors.html('Room number is required.').removeClass('d-none');
                        $form.find('#edit_room_number').addClass('is-invalid').focus();
                        return;
                    }

                    var capacity = parseInt($form.find('#edit_capacity').val(), 10);
                    if (isNaN(capacity) || capacity < 1) {
                        $errors.html('Capacity must be at least 1 person.').removeClass('d-none');
                        $form.find('#edit_capacity').addClass('is-invalid').focus();
                        return;
                    }

                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/rooms') }}/" + id + "/update",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#editRoomModal').modal('hide');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                table.ajax.reload(null, false);
                                updateStatCards(response.stats);
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Room updated successfully!'
                                });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var errorMsg = 'Please check the entered fields.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                $.each(xhr.responseJSON.errors, function(field, messages) {
                                    $form.find('[name="' + field + '"]').addClass('is-invalid');
                                });
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $errors.html(errorMsg).removeClass('d-none');
                        }
                    });
                });

                // 4. DELETE ROOM VIA AJAX
                $(document).on('click', '.btn-delete-room', function() {
                    var id = $(this).data('id');
                    var roomNumber = $(this).data('number') || 'this room';

                    Swal.fire({
                        title: "Remove Room?",
                        text: "Are you sure you want to remove Room " + roomNumber + "?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#f34e4e",
                        confirmButtonText: "Yes, remove"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/rooms') }}/" + id,
                                data: {
                                    "_token": "{{ csrf_token() }}"
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(data) {
                                    if (data.status == 1 || data.success) {
                                        table.ajax.reload(null, false);
                                        updateStatCards(data.stats);
                                        Toast.fire({
                                            icon: 'success',
                                            title: 'Room removed successfully!'
                                        });
                                    } else {
                                        Toast.fire({
                                            icon: 'error',
                                            title: 'Failed to remove room'
                                        });
                                    }
                                }
                            });
                        }
                    });
                });

                // 5. STATUS TOGGLE VIA AJAX
                $(document).on('change', '.room-status-toggle', function() {
                    var $this = $(this);
                    var id = $this.data('id');
                    var status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/rooms/status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        },
                        success: function(data) {
                            if (data.success) {
                                table.ajax.reload(null, false);
                                updateStatCards(data.stats);
                                Toast.fire({ icon: 'success', title: 'Room status updated!' });
                            } else {
                                $this.prop('checked', !$this.prop('checked'));
                                Toast.fire({ icon: 'error', title: data.message || 'Failed to update' });
                            }
                        },
                        error: function() {
                            $this.prop('checked', !$this.prop('checked'));
                            Toast.fire({ icon: 'error', title: 'Failed to update room status' });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

