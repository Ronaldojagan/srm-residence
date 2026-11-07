@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .tenant-stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                border: 1px solid #e2e8f0;
                transition: transform 0.2s;
            }
            .tenant-stat-card:hover {
                transform: translateY(-2px);
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #0ea1b1 !important;
                color: #fff !important;
                border-color: #0ea1b1 !important;
                border-radius: 5px;
            }
            /* Select2 Custom Multi-select Styling */
            .select2-container--default .select2-selection--multiple {
                border: 1px solid #ced4da;
                border-radius: 0.375rem;
                min-height: 38px;
                padding: 3px 6px;
            }
            .select2-container--default.select2-container--focus .select2-selection--multiple {
                border-color: #0ea1b1;
                box-shadow: 0 0 0 0.25rem rgba(14, 161, 177, 0.25);
            }
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #0ea1b1;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                padding: 2px 8px;
                font-size: 12px;
                font-weight: 600;
                margin-top: 3px;
            }
            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                color: #ffffff;
                margin-right: 5px;
            }
            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
                color: #fecdd3;
            }
            .select2-dropdown {
                z-index: 1065 !important;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #0ea1b1;
            }
        </style>
    @endpush

    <!-- Page Header & Action -->
    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-6">
            <h4 class="page-title-main page">Tenants Management</h4>
            <p class="text-muted small mb-0">Manage resident leases, rooms, and rent collection records.</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#addTenantModal">
                <i class="mdi mdi-account-plus me-1"></i> Create Tenant
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mt-3 g-3">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="tenant-stat-card border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">Active Tenants</div>
                <h3 class="fw-bold my-1 text-primary">{{ $stats['activeTenants'] }}</h3>
                <small class="text-muted">Total registered: {{ $stats['totalTenants'] }}</small>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="tenant-stat-card border-start border-4 border-success">
                <div class="text-muted small text-uppercase fw-bold">Monthly Rent Roll</div>
                <h3 class="fw-bold my-1 text-success">₹{{ number_format($stats['totalRent']) }}</h3>
                <small class="text-muted">Base: ₹{{ number_format($stats['baseRent'] ?? 0) }} / mo</small>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="tenant-stat-card border-start border-4 border-info">
                <div class="text-muted small text-uppercase fw-bold">Security Deposits</div>
                <h3 class="fw-bold my-1 text-info">₹{{ number_format($stats['totalSecurityDeposit'] ?? 0) }}</h3>
                <small class="text-muted">Held in refundable trust</small>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="tenant-stat-card border-start border-4 border-purple" style="border-left-color: #8b5cf6 !important;">
                <div class="text-muted small text-uppercase fw-bold">Occupied Rooms</div>
                <h3 class="fw-bold my-1 text-purple" style="color: #8b5cf6;">{{ $stats['occupiedRooms'] }}</h3>
                <small class="text-muted">Assigned units</small>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="tenant-stat-card border-start border-4 border-warning">
                <div class="text-muted small text-uppercase fw-bold">Avg. Rent / Tenant</div>
                <h3 class="fw-bold my-1 text-warning">
                    ₹{{ $stats['activeTenants'] > 0 ? number_format(round($stats['totalRent'] / $stats['activeTenants'])) : 0 }}
                </h3>
                <small class="text-muted">Per active lease</small>
            </div>
        </div>
    </div>

    <!-- Tenants DataTable -->
    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table job-table w-100">
                            <thead class="table-theme text-white">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Tenant & Email</th>
                                    <th>Phone</th>
                                    <th>Room & Type</th>
                                    <th>Monthly Rent</th>
                                    <th>Commence Date</th>
                                    <th>Status</th>
                                    <th width="12%" class="text-center">Action</th>
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

    <!-- ADD TENANT MODAL (AJAX) -->
    <div class="modal fade" id="addTenantModal" tabindex="-1" aria-labelledby="addTenantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="addTenantModalLabel">
                        <i class="mdi mdi-account-plus me-1"></i> Create Tenant
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addTenantForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div id="addTenantErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tenant Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sundaram" minlength="2" maxlength="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile" pattern="[0-9]{10}" maxlength="15" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="tenant@example.com" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Emergency Contact</label>
                                <input type="tel" name="emergency_contact" class="form-control" placeholder="Parent / Guardian phone" maxlength="15">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                                    <span>Assign Room(s) <span class="text-danger">*</span></span>
                                    <span class="badge bg-primary-subtle text-primary font-11" id="addRoomSelectedCount">Select 1 or more rooms</span>
                                </label>
                                <select name="room_ids[]" id="tenant_room_ids" class="form-select select2-rooms" multiple required>
                                    @if(isset($rooms))
                                        @foreach($rooms as $r)
                                            @php
                                                $isSelf = $r->is_self || $r->occupancy_status === 'self';
                                                $isOccupied = $r->occupancy_status === 'occupied' || $r->active_residents->isNotEmpty();
                                                $isMaint = $r->occupancy_status === 'maintenance';
                                                $isDisabled = $isSelf || $isOccupied || $isMaint;

                                                $occupantNames = $r->active_residents->isNotEmpty() ? $r->active_residents->pluck('name')->implode(', ') : '';
                                                $statusLabel = 'Available';
                                                if ($isSelf) $statusLabel = 'Owner Occupied (Self)';
                                                elseif ($isOccupied) $statusLabel = 'Occupied' . ($occupantNames ? ' by ' . $occupantNames : '');
                                                elseif ($isMaint) $statusLabel = 'Under Maintenance';
                                            @endphp
                                            <option value="{{ $r->id }}" 
                                                {{ $isDisabled ? 'disabled' : '' }}
                                                data-number="{{ $r->room_number }}" 
                                                data-floor="{{ $r->floor }}" 
                                                data-type="{{ $r->room_type }}" 
                                                data-rent="{{ $r->monthly_rent }}" 
                                                data-deposit="{{ $r->security_deposit }}"
                                                data-eb="{{ $r->eb_unit_rate }}"
                                                data-maintenance="{{ $r->maintenance_charges }}"
                                                data-other="{{ $r->other_charges }}">
                                                Room {{ $r->room_number }} ({{ $r->floor }} - {{ $r->room_type }}) [{{ $statusLabel }}]
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <input type="hidden" name="room_number" id="tenant_room_number">
                                <small class="text-muted font-11 d-block mt-1"><i class="fas fa-info-circle me-1"></i> Multi-room selection allowed. Rent and deposits combine automatically.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Commence Date <span class="text-danger">*</span></label>
                                <input type="date" name="commence_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                <input type="hidden" name="room_type" id="tenant_room_type" value="Standard">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Base Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="monthly_rent" id="add_monthly_rent" class="form-control charge-input" placeholder="e.g. 12000" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Security Deposit (₹)</label>
                                <input type="number" step="0.01" name="security_deposit" class="form-control" placeholder="e.g. 24000" min="0" value="0">
                            </div>

                            <!-- Additional Charges based on room -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">EB Rate (₹/Unit)</label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">₹</span>
                                    <input type="number" step="0.01" name="eb_unit_rate" id="add_eb_unit_rate" class="form-control" placeholder="10.00" value="10.00">
                                </div>
                                <small class="text-muted font-11">Cost per unit (kWh)</small>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 p-2 bg-light rounded border">
                                    <span class="fw-bold small text-muted text-uppercase font-11">Rent Structure:</span>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="add_tenant_rent_type" id="add_tenant_rent_type_custom" value="custom" checked>
                                        <label class="form-check-label fw-semibold font-12" for="add_tenant_rent_type_custom">Split Charges</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="add_tenant_rent_type" id="add_tenant_rent_type_fixed" value="fixed">
                                        <label class="form-check-label fw-bold text-success font-12" for="add_tenant_rent_type_fixed">
                                            <i class="fas fa-check-circle me-1"></i> Fixed Rent
                                        </label>
                                    </div>
                                    <small class="text-muted ms-auto d-none d-sm-inline font-11">(Fixed Rent zeroes Maintenance & Other charges)</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Starting EB Units</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="eb_start_reading" id="add_eb_start_reading" class="form-control" placeholder="0.00" value="0.00" min="0">
                                    <span class="input-group-text small">Units</span>
                                </div>
                                <small class="text-muted font-11">Initial meter reading</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Maintenance Charges (₹)</label>
                                <input type="number" step="0.01" name="maintenance_charges" id="add_maintenance_charges" class="form-control charge-input" placeholder="0.00" value="0.00">
                                <small class="text-muted font-11">Upkeep & housekeeping</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Other Charges (₹)</label>
                                <input type="number" step="0.01" name="other_charges" id="add_other_charges" class="form-control charge-input" placeholder="0.00" value="0.00">
                                <small class="text-muted font-11">Wi-Fi / water / services</small>
                            </div>

                            <!-- Live Monthly Package Calculation -->
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small text-uppercase fw-bold d-block">Fixed Monthly Due</span>
                                        <small class="text-muted">Rent + Maintenance + Other (EB billed on usage @ <strong class="text-primary" id="add_eb_rate_display">₹10.00/unit</strong>)</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="fw-bold text-success mb-0" id="add_total_monthly_display">₹0.00</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Permanent Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Street, City, State, Pincode"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white waves-effect waves-light" id="btnSaveTenant">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="saveTenantSpinner"></span>
                            Create Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT TENANT MODAL (AJAX) -->
    <div class="modal fade" id="editTenantModal" tabindex="-1" aria-labelledby="editTenantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="editTenantModalLabel">
                        <i class="mdi mdi-account-edit me-1"></i> Edit Tenant Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTenantForm">
                    @csrf
                    <input type="hidden" id="edit_tenant_id" name="id">
                    <div class="modal-body p-4">
                        <div id="editTenantErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tenant Full Name <span class="text-danger">*</span></label>
                                <input type="text" id="edit_name" name="name" class="form-control" minlength="2" maxlength="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" id="edit_phone" name="phone" class="form-control" pattern="[0-9]{10}" maxlength="15" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" id="edit_email" name="email" class="form-control" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Emergency Contact</label>
                                <input type="tel" id="edit_emergency_contact" name="emergency_contact" class="form-control" maxlength="15">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                                    <span>Assign Room(s) <span class="text-danger">*</span></span>
                                    <span class="badge bg-primary-subtle text-primary font-11" id="editRoomSelectedCount">Select 1 or more rooms</span>
                                </label>
                                <select id="edit_tenant_room_ids" name="room_ids[]" class="form-select select2-rooms" multiple required>
                                    @if(isset($rooms))
                                        @foreach($rooms as $r)
                                            @php
                                                $isSelf = $r->is_self || $r->occupancy_status === 'self';
                                                $isOccupied = $r->occupancy_status === 'occupied' || $r->active_residents->isNotEmpty();
                                                $isMaint = $r->occupancy_status === 'maintenance';
                                                $tenantIds = $r->active_residents->pluck('id')->toArray();
                                                $occupantNames = $r->active_residents->pluck('name')->implode(', ');
                                            @endphp
                                            <option value="{{ $r->id }}" 
                                                data-is-self="{{ $isSelf ? 1 : 0 }}"
                                                data-is-occupied="{{ $isOccupied ? 1 : 0 }}"
                                                data-is-maintenance="{{ $isMaint ? 1 : 0 }}"
                                                data-tenant-ids="{{ json_encode($tenantIds) }}"
                                                data-occupant-name="{{ $occupantNames }}"
                                                data-number="{{ $r->room_number }}" 
                                                data-floor="{{ $r->floor }}" 
                                                data-type="{{ $r->room_type }}" 
                                                data-rent="{{ $r->monthly_rent }}" 
                                                data-deposit="{{ $r->security_deposit }}"
                                                data-eb="{{ $r->eb_unit_rate }}"
                                                data-maintenance="{{ $r->maintenance_charges }}"
                                                data-other="{{ $r->other_charges }}">
                                                Room {{ $r->room_number }} ({{ $r->floor }} - {{ $r->room_type }}) [{{ $isSelf ? 'Owner Occupied (Self)' : ($isOccupied ? 'Occupied' . ($occupantNames ? ' by ' . $occupantNames : '') : ($isMaint ? 'Under Maintenance' : 'Available')) }}]
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <input type="hidden" name="room_number" id="edit_tenant_room_number">
                                <small class="text-muted font-11 d-block mt-1"><i class="fas fa-info-circle me-1"></i> Multi-room selection allowed. Rents and deposits combine automatically.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Commence Date</label>
                                <input type="date" id="edit_commence_date" name="commence_date" class="form-control">
                                <input type="hidden" name="room_type" id="edit_room_type" value="Standard">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Base Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" id="edit_monthly_rent" name="monthly_rent" class="form-control edit-charge-input" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Security Deposit (₹)</label>
                                <input type="number" step="0.01" id="edit_security_deposit" name="security_deposit" class="form-control" min="0">
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 p-2 bg-light rounded border">
                                    <span class="fw-bold small text-muted text-uppercase font-11">Rent Structure:</span>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="edit_tenant_rent_type" id="edit_tenant_rent_type_custom" value="custom" checked>
                                        <label class="form-check-label fw-semibold font-12" for="edit_tenant_rent_type_custom">Split Charges</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="edit_tenant_rent_type" id="edit_tenant_rent_type_fixed" value="fixed">
                                        <label class="form-check-label fw-bold text-success font-12" for="edit_tenant_rent_type_fixed">
                                            <i class="fas fa-check-circle me-1"></i> Fixed Rent
                                        </label>
                                    </div>
                                    <small class="text-muted ms-auto d-none d-sm-inline font-11">(Fixed Rent zeroes Maintenance & Other charges)</small>
                                </div>
                            </div>

                            <!-- Additional Charges based on room -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">EB Rate (₹/Unit)</label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">₹</span>
                                    <input type="number" step="0.01" id="edit_eb_unit_rate" name="eb_unit_rate" class="form-control" placeholder="10.00">
                                </div>
                                <small class="text-muted font-11">Cost per unit (kWh)</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Starting EB Units</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" id="edit_eb_start_reading" name="eb_start_reading" class="form-control" placeholder="0.00" min="0">
                                    <span class="input-group-text small">Units</span>
                                </div>
                                <small class="text-muted font-11">Initial meter reading</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Maintenance Charges (₹)</label>
                                <input type="number" step="0.01" id="edit_maintenance_charges" name="maintenance_charges" class="form-control edit-charge-input" placeholder="0.00">
                                <small class="text-muted font-11">Upkeep & housekeeping</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Other Charges (₹)</label>
                                <input type="number" step="0.01" id="edit_other_charges" name="other_charges" class="form-control edit-charge-input" placeholder="0.00">
                                <small class="text-muted font-11">Wi-Fi / water / services</small>
                            </div>

                            <!-- Live Monthly Package Calculation in Edit -->
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small text-uppercase fw-bold d-block">Fixed Monthly Due</span>
                                        <small class="text-muted">Rent + Maintenance + Other (EB billed on usage @ <strong class="text-primary" id="edit_eb_rate_display">₹10.00/unit</strong>)</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="fw-bold text-success mb-0" id="edit_total_monthly_display">₹0.00</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Permanent Address</label>
                                <textarea id="edit_address" name="address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white waves-effect waves-light" id="btnUpdateTenant">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="updateTenantSpinner"></span>
                            Update Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

        <script type="text/javascript">
            $(function() {
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/admin/tenants') }}",
                    order: [[3, 'asc']],
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name_email', name: 'name' },
                        { data: 'phone', name: 'phone' },
                        { data: 'room_badge', name: 'room_number' },
                        { data: 'rent_display', name: 'monthly_rent' },
                        { data: 'commence_date_formatted', name: 'commence_date' },
                        { data: 'status_toggle', name: 'status', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search tenants...",
                        lengthMenu: "Show _MENU_ entries",
                        zeroRecords: "No matching tenants found",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)"
                    }
                });

                // Check URL query parameters (e.g. from Dashboard "View Tenant")
                var urlParams = new URLSearchParams(window.location.search);
                var searchQ = urlParams.get('search');
                var tenantIdParam = urlParams.get('tenant_id');
                if (searchQ) {
                    table.search(searchQ).draw();
                } else if (tenantIdParam) {
                    table.on('draw', function() {
                        var $editBtn = $('.btn-edit-tenant[data-id="' + tenantIdParam + '"]');
                        if ($editBtn.length && !window.tenantModalAutoOpened) {
                            window.tenantModalAutoOpened = true;
                            $editBtn.trigger('click');
                        }
                    });
                }

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // Calculations for live monthly totals
                function calcAddTenantTotal() {
                    var rent = parseFloat($('#add_monthly_rent').val()) || 0;
                    var maint = parseFloat($('#add_maintenance_charges').val()) || 0;
                    var other = parseFloat($('#add_other_charges').val()) || 0;
                    var total = rent + maint + other;
                    var ebRate = parseFloat($('#add_eb_unit_rate').val()) || 0;
                    $('#add_total_monthly_display').text('₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#add_eb_rate_display').text('₹' + ebRate.toFixed(2) + '/unit');
                }

                function calcEditTenantTotal() {
                    var rent = parseFloat($('#edit_monthly_rent').val()) || 0;
                    var maint = parseFloat($('#edit_maintenance_charges').val()) || 0;
                    var other = parseFloat($('#edit_other_charges').val()) || 0;
                    var total = rent + maint + other;
                    var ebRate = parseFloat($('#edit_eb_unit_rate').val()) || 0;
                    $('#edit_total_monthly_display').text('₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#edit_eb_rate_display').text('₹' + ebRate.toFixed(2) + '/unit');
                }

                $(document).on('keyup change input', '.charge-input, #add_eb_unit_rate', calcAddTenantTotal);
                $(document).on('keyup change input', '.edit-charge-input, #edit_eb_unit_rate', calcEditTenantTotal);

                // Toggle Fixed Rent vs Split Charges in Create Tenant Modal
                $(document).on('change', 'input[name="add_tenant_rent_type"]', function() {
                    if ($('#add_tenant_rent_type_fixed').is(':checked')) {
                        $('#add_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#add_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                    } else {
                        $('#add_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                        $('#add_other_charges').prop('readonly', false).removeClass('bg-light');
                    }
                    calcAddTenantTotal();
                });

                // Toggle Fixed Rent vs Split Charges in Edit Tenant Modal
                $(document).on('change', 'input[name="edit_tenant_rent_type"]', function() {
                    if ($('#edit_tenant_rent_type_fixed').is(':checked')) {
                        $('#edit_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#edit_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                    } else {
                        $('#edit_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                        $('#edit_other_charges').prop('readonly', false).removeClass('bg-light');
                    }
                    calcEditTenantTotal();
                });

                // Initialize Select2 multi-room pickers
                $('#tenant_room_ids').select2({
                    dropdownParent: $('#addTenantModal'),
                    placeholder: 'Click to select room(s)...',
                    width: '100%',
                    closeOnSelect: false
                });

                $('#edit_tenant_room_ids').select2({
                    dropdownParent: $('#editTenantModal'),
                    placeholder: 'Click to select room(s)...',
                    width: '100%',
                    closeOnSelect: false
                });

                $('#addTenantModal').on('show.bs.modal', function() {
                    $('#add_tenant_rent_type_custom').prop('checked', true);
                    $('#add_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                    $('#add_other_charges').prop('readonly', false).removeClass('bg-light');
                    $('#tenant_room_ids').val(null).trigger('change.select2');
                    $('#addRoomSelectedCount').text('Select 1 or more rooms');
                });

                // Auto-fill combined room details when rooms are selected in Create modal
                $('#tenant_room_ids').on('change', function() {
                    var $selected = $(this).find('option:selected');
                    var count = $selected.length;
                    $('#addRoomSelectedCount').text(count > 0 ? count + ' room(s) selected' : 'Select 1 or more rooms');

                    if (count === 0) {
                        $('#tenant_room_number').val('');
                        $('#tenant_room_type').val('Standard');
                        return;
                    }

                    var totalRent = 0;
                    var totalDeposit = 0;
                    var totalMaint = 0;
                    var totalOther = 0;
                    var roomNumbers = [];
                    var roomTypes = [];
                    var ebRate = 10.00;

                    $selected.each(function() {
                        var $opt = $(this);
                        totalRent += parseFloat($opt.data('rent')) || 0;
                        totalDeposit += parseFloat($opt.data('deposit')) || 0;
                        totalMaint += parseFloat($opt.data('maintenance')) || 0;
                        totalOther += parseFloat($opt.data('other')) || 0;
                        if ($opt.data('number')) roomNumbers.push($opt.data('number'));
                        if ($opt.data('type') && !roomTypes.includes($opt.data('type'))) roomTypes.push($opt.data('type'));
                        if ($opt.data('eb')) ebRate = parseFloat($opt.data('eb'));
                    });

                    $('#tenant_room_number').val(roomNumbers.join(', '));
                    $('#tenant_room_type').val(roomTypes.join(', '));
                    $('#add_monthly_rent').val(totalRent);
                    $('#addTenantModal input[name="security_deposit"]').val(totalDeposit);
                    $('#add_eb_unit_rate').val(ebRate.toFixed(2));

                    if (totalMaint === 0 && totalOther === 0) {
                        $('#add_tenant_rent_type_fixed').prop('checked', true);
                        $('#add_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        $('#add_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                    } else {
                        $('#add_tenant_rent_type_custom').prop('checked', true);
                        $('#add_maintenance_charges').val(totalMaint.toFixed(2)).prop('readonly', false).removeClass('bg-light');
                        $('#add_other_charges').val(totalOther.toFixed(2)).prop('readonly', false).removeClass('bg-light');
                    }
                    calcAddTenantTotal();
                });

                var isProgrammaticEditChange = false;

                // Auto-fill combined room details when rooms are selected in Edit modal
                $('#edit_tenant_room_ids').on('change', function() {
                    var $selected = $(this).find('option:selected');
                    var count = $selected.length;
                    $('#editRoomSelectedCount').text(count > 0 ? count + ' room(s) selected' : 'Select 1 or more rooms');

                    if (count === 0) {
                        $('#edit_tenant_room_number').val('');
                        return;
                    }

                    var roomNumbers = [];
                    var roomTypes = [];
                    $selected.each(function() {
                        var $opt = $(this);
                        if ($opt.data('number')) roomNumbers.push($opt.data('number'));
                        if ($opt.data('type') && !roomTypes.includes($opt.data('type'))) roomTypes.push($opt.data('type'));
                    });

                    $('#edit_tenant_room_number').val(roomNumbers.join(', '));
                    $('#edit_room_type').val(roomTypes.join(', '));

                    if (!isProgrammaticEditChange) {
                        var totalRent = 0;
                        var totalDeposit = 0;
                        var totalMaint = 0;
                        var totalOther = 0;
                        var ebRate = 10.00;

                        $selected.each(function() {
                            var $opt = $(this);
                            totalRent += parseFloat($opt.data('rent')) || 0;
                            totalDeposit += parseFloat($opt.data('deposit')) || 0;
                            totalMaint += parseFloat($opt.data('maintenance')) || 0;
                            totalOther += parseFloat($opt.data('other')) || 0;
                            if ($opt.data('eb')) ebRate = parseFloat($opt.data('eb'));
                        });

                        $('#edit_monthly_rent').val(totalRent);
                        $('#edit_security_deposit').val(totalDeposit);
                        $('#edit_eb_unit_rate').val(ebRate.toFixed(2));

                        if (totalMaint === 0 && totalOther === 0) {
                            $('#edit_tenant_rent_type_fixed').prop('checked', true);
                            $('#edit_maintenance_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                            $('#edit_other_charges').val('0.00').prop('readonly', true).addClass('bg-light');
                        } else {
                            $('#edit_tenant_rent_type_custom').prop('checked', true);
                            $('#edit_maintenance_charges').val(totalMaint.toFixed(2)).prop('readonly', false).removeClass('bg-light');
                            $('#edit_other_charges').val(totalOther.toFixed(2)).prop('readonly', false).removeClass('bg-light');
                        }
                        calcEditTenantTotal();
                    }
                });

                // 1. ADD TENANT VIA AJAX
                $('#addTenantForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveTenant');
                    var $spinner = $('#saveTenantSpinner');
                    var $errors = $('#addTenantErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var name = $.trim($form.find('input[name="name"]').val());
                    if (!name || name.length < 2) {
                        $errors.html('Resident name must be at least 2 characters.').removeClass('d-none');
                        $form.find('input[name="name"]').addClass('is-invalid').focus();
                        return;
                    }

                    var phone = $.trim($form.find('input[name="phone"]').val());
                    if (!phone || phone.length < 10) {
                        $errors.html('Please enter a valid 10-digit mobile number.').removeClass('d-none');
                        $form.find('input[name="phone"]').addClass('is-invalid').focus();
                        return;
                    }

                    var roomIds = $form.find('#tenant_room_ids').val();
                    if (!roomIds || roomIds.length === 0) {
                        $errors.html('Please select at least one room to assign to this resident.').removeClass('d-none');
                        $form.find('#tenant_room_ids').addClass('is-invalid').focus();
                        return;
                    }

                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/tenants') }}",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#addTenantModal').modal('hide');
                                $form[0].reset();
                                $('#tenant_room_ids').val(null).trigger('change.select2');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                table.ajax.reload(null, false);
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Tenant registered successfully!'
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

                // Clear is-invalid on change
                $('#addTenantForm, #editTenantForm').on('input change', 'input, select, textarea', function() {
                    $(this).removeClass('is-invalid');
                });

                // 2. OPEN EDIT MODAL & LOAD TENANT VIA AJAX
                $(document).on('click', '.btn-edit-tenant', function() {
                    var id = $(this).data('id');
                    $('#editTenantErrors').addClass('d-none').html('');

                    $.ajax({
                        url: "{{ url('/admin/tenants') }}/" + id + "/data",
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                var tenant = response.tenant;
                                $('#edit_tenant_id').val(tenant.id);
                                $('#edit_name').val(tenant.name);
                                $('#edit_phone').val(tenant.phone);
                                $('#edit_email').val(tenant.email);
                                $('#edit_emergency_contact').val(tenant.emergency_contact);

                                var rIds = response.room_ids && response.room_ids.length > 0 ? response.room_ids : (tenant.room_id ? [tenant.room_id] : []);
                                rIds = rIds.map(function(v) { return parseInt(v); });

                                // Dynamically update room enabled/disabled states for this tenant
                                $('#edit_tenant_room_ids option').each(function() {
                                    var $opt = $(this);
                                    var optId = parseInt($opt.val());
                                    var isSelf = parseInt($opt.data('is-self')) === 1;
                                    var isMaint = parseInt($opt.data('is-maintenance')) === 1;
                                    var isOccupied = parseInt($opt.data('is-occupied')) === 1;
                                    var rawTenantIds = $opt.attr('data-tenant-ids');
                                    var optTenantIds = [];
                                    try {
                                        optTenantIds = typeof rawTenantIds === 'string' ? JSON.parse(rawTenantIds) : (rawTenantIds || []);
                                    } catch(e) {
                                        optTenantIds = [];
                                    }
                                    optTenantIds = optTenantIds.map(Number);

                                    var isAssignedToThis = rIds.indexOf(optId) !== -1 || optTenantIds.indexOf(parseInt(tenant.id)) !== -1;

                                    if (isSelf || isMaint) {
                                        $opt.prop('disabled', true);
                                    } else if (isOccupied && !isAssignedToThis) {
                                        $opt.prop('disabled', true);
                                    } else {
                                        $opt.prop('disabled', false);
                                    }
                                });

                                // Re-initialize Select2 so disabled attributes update in dropdown UI
                                $('#edit_tenant_room_ids').select2({
                                    dropdownParent: $('#editTenantModal'),
                                    placeholder: 'Click to select room(s)...',
                                    width: '100%',
                                    closeOnSelect: false
                                });

                                isProgrammaticEditChange = true;
                                $('#edit_tenant_room_ids').val(rIds).trigger('change.select2');
                                $('#editRoomSelectedCount').text(rIds.length > 0 ? rIds.length + ' room(s) selected' : 'Select 1 or more rooms');
                                isProgrammaticEditChange = false;

                                $('#edit_tenant_room_number').val(tenant.room_number || '');
                                $('#edit_room_type').val(tenant.room_type);
                                if (tenant.commence_date) {
                                    $('#edit_commence_date').val(tenant.commence_date.substring(0, 10));
                                }
                                $('#edit_monthly_rent').val(tenant.monthly_rent);
                                $('#edit_eb_unit_rate').val(tenant.eb_unit_rate !== null && tenant.eb_unit_rate !== undefined ? tenant.eb_unit_rate : '10.00');
                                $('#edit_eb_start_reading').val(tenant.eb_start_reading !== null && tenant.eb_start_reading !== undefined ? tenant.eb_start_reading : '0.00');
                                var tmaint = parseFloat(tenant.maintenance_charges) || 0;
                                var tother = parseFloat(tenant.other_charges) || 0;
                                $('#edit_maintenance_charges').val(tenant.maintenance_charges || '0.00');
                                $('#edit_other_charges').val(tenant.other_charges || '0.00');

                                if (tmaint === 0 && tother === 0) {
                                    $('#edit_tenant_rent_type_fixed').prop('checked', true);
                                    $('#edit_maintenance_charges').prop('readonly', true).addClass('bg-light');
                                    $('#edit_other_charges').prop('readonly', true).addClass('bg-light');
                                } else {
                                    $('#edit_tenant_rent_type_custom').prop('checked', true);
                                    $('#edit_maintenance_charges').prop('readonly', false).removeClass('bg-light');
                                    $('#edit_other_charges').prop('readonly', false).removeClass('bg-light');
                                }
                                $('#edit_security_deposit').val(tenant.security_deposit);
                                $('#edit_address').val(tenant.address);
                                calcEditTenantTotal();

                                $('#editTenantModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to fetch tenant details' });
                        }
                    });
                });

                // 3. UPDATE TENANT VIA AJAX
                $('#editTenantForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var id = $('#edit_tenant_id').val();
                    var $btn = $('#btnUpdateTenant');
                    var $spinner = $('#updateTenantSpinner');
                    var $errors = $('#editTenantErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var name = $.trim($form.find('#edit_name').val());
                    if (!name || name.length < 2) {
                        $errors.html('Resident name must be at least 2 characters.').removeClass('d-none');
                        $form.find('#edit_name').addClass('is-invalid').focus();
                        return;
                    }

                    var phone = $.trim($form.find('#edit_phone').val());
                    if (!phone || phone.length < 10) {
                        $errors.html('Please enter a valid 10-digit mobile number.').removeClass('d-none');
                        $form.find('#edit_phone').addClass('is-invalid').focus();
                        return;
                    }

                    var roomIds = $form.find('#edit_tenant_room_ids').val();
                    if (!roomIds || roomIds.length === 0) {
                        $errors.html('Please select at least one room to assign to this resident.').removeClass('d-none');
                        $form.find('#edit_tenant_room_ids').addClass('is-invalid').focus();
                        return;
                    }

                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/tenants') }}/" + id + "/update",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#editTenantModal').modal('hide');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                table.ajax.reload(null, false);
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Tenant updated successfully!'
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

                // 4. DELETE TENANT VIA AJAX
                $(document).on('click', '.btn-delete-tenant', function() {
                    var id = $(this).data('id');
                    var tenantName = $(this).data('name') || 'this tenant';

                    Swal.fire({
                        title: "Remove Tenant?",
                        text: "Are you sure you want to remove " + tenantName + "?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#f34e4e",
                        confirmButtonText: "Yes, remove"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/tenants') }}/" + id,
                                data: {
                                    "_token": "{{ csrf_token() }}"
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(data) {
                                    if (data.status == 1 || data.success) {
                                        table.ajax.reload(null, false);
                                        Toast.fire({
                                            icon: 'success',
                                            title: 'Tenant removed successfully!'
                                        });
                                    } else {
                                        Toast.fire({
                                            icon: 'error',
                                            title: 'Failed to remove tenant'
                                        });
                                    }
                                }
                            });
                        }
                    });
                });

                // 5. STATUS TOGGLE VIA AJAX
                $(document).on('change', '.tenant-status-toggle', function() {
                    var $this = $(this);
                    var id = $this.data('id');
                    var status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/tenants/status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        },
                        success: function(data) {
                            if (data.success) {
                                Toast.fire({ icon: 'success', title: 'Status updated!' });
                            } else {
                                $this.prop('checked', !$this.prop('checked'));
                                Toast.fire({ icon: 'error', title: data.message || 'Failed to update' });
                            }
                        },
                        error: function() {
                            $this.prop('checked', !$this.prop('checked'));
                            Toast.fire({ icon: 'error', title: 'Failed to update status' });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

