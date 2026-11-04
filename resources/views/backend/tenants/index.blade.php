@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
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
        <div class="col-md-3">
            <div class="tenant-stat-card border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">Active Tenants</div>
                <h3 class="fw-bold my-1 text-primary">{{ $stats['activeTenants'] }}</h3>
                <small class="text-muted">Total registered: {{ $stats['totalTenants'] }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tenant-stat-card border-start border-4 border-success">
                <div class="text-muted small text-uppercase fw-bold">Monthly Rent Pipeline</div>
                <h3 class="fw-bold my-1 text-success">₹{{ number_format($stats['totalRent']) }}</h3>
                <small class="text-muted">From active leases</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tenant-stat-card border-start border-4 border-info">
                <div class="text-muted small text-uppercase fw-bold">Occupied Rooms</div>
                <h3 class="fw-bold my-1 text-info">{{ $stats['occupiedRooms'] }}</h3>
                <small class="text-muted">Assigned units</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tenant-stat-card border-start border-4 border-warning">
                <div class="text-muted small text-uppercase fw-bold">Avg. Rent / Tenant</div>
                <h3 class="fw-bold my-1 text-warning">
                    ₹{{ $stats['activeTenants'] > 0 ? number_format(round($stats['totalRent'] / $stats['activeTenants'])) : 0 }}
                </h3>
                <small class="text-muted">Per room unit</small>
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
                                <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sundaram" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="tenant@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Emergency Contact</label>
                                <input type="tel" name="emergency_contact" class="form-control" placeholder="Parent / Guardian phone">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Room Number <span class="text-danger">*</span></label>
                                <select name="room_number" id="tenant_room_number" class="form-select" required>
                                    <option value="">Select Room...</option>
                                    @if(isset($rooms))
                                        @foreach($rooms as $r)
                                            <option value="{{ $r->room_number }}" data-type="{{ $r->room_type }}" data-rent="{{ $r->monthly_rent }}" data-deposit="{{ $r->security_deposit }}">
                                                Room {{ $r->room_number }} ({{ $r->floor }} - {{ $r->room_type }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Room Type <span class="text-danger">*</span></label>
                                <select name="room_type" class="form-select" required>
                                    <option value="Single Deluxe" selected>Single Deluxe</option>
                                    <option value="2-Sharing AC">2-Sharing AC</option>
                                    <option value="3-Sharing Non-AC">3-Sharing Non-AC</option>
                                    <option value="Executive Suite">Executive Suite</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Commence Date <span class="text-danger">*</span></label>
                                <input type="date" name="commence_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="monthly_rent" class="form-control" placeholder="e.g. 12000" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Security Deposit (₹)</label>
                                <input type="number" name="security_deposit" class="form-control" placeholder="e.g. 24000" min="0" value="0">
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
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" id="edit_phone" name="phone" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" id="edit_email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Emergency Contact</label>
                                <input type="tel" id="edit_emergency_contact" name="emergency_contact" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Room Number <span class="text-danger">*</span></label>
                                <select id="edit_room_number" name="room_number" class="form-select" required>
                                    <option value="">Select Room...</option>
                                    @if(isset($rooms))
                                        @foreach($rooms as $r)
                                            <option value="{{ $r->room_number }}" data-type="{{ $r->room_type }}" data-rent="{{ $r->monthly_rent }}" data-deposit="{{ $r->security_deposit }}">
                                                Room {{ $r->room_number }} ({{ $r->floor }} - {{ $r->room_type }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Room Type <span class="text-danger">*</span></label>
                                <select id="edit_room_type" name="room_type" class="form-select" required>
                                    <option value="Single Deluxe">Single Deluxe</option>
                                    <option value="2-Sharing AC">2-Sharing AC</option>
                                    <option value="3-Sharing Non-AC">3-Sharing Non-AC</option>
                                    <option value="Executive Suite">Executive Suite</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Commence Date</label>
                                <input type="date" id="edit_commence_date" name="commence_date" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" id="edit_monthly_rent" name="monthly_rent" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Security Deposit (₹)</label>
                                <input type="number" id="edit_security_deposit" name="security_deposit" class="form-control" min="0">
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
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

        <script type="text/javascript">
            $(function() {
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/admin/tenants') }}",
                    order: [[0, 'desc']],
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name_email', name: 'name' },
                        { data: 'phone', name: 'phone' },
                        { data: 'room_badge', name: 'room_number' },
                        { data: 'rent_display', name: 'monthly_rent' },
                        { data: 'commence_date_formatted', name: 'commence_date' },
                        { data: 'status_toggle', name: 'status', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                    ]
                });

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // Auto-fill room details when room is selected
                $('#tenant_room_number').on('change', function() {
                    var $opt = $(this).find('option:selected');
                    if ($opt.data('type')) {
                        $('select[name="room_type"]').val($opt.data('type'));
                    }
                    if ($opt.data('rent')) {
                        $('input[name="monthly_rent"]').val($opt.data('rent'));
                    }
                    if ($opt.data('deposit')) {
                        $('input[name="security_deposit"]').val($opt.data('deposit'));
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
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $errors.html(errorMsg).removeClass('d-none');
                        }
                    });
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
                                $('#edit_room_number').val(tenant.room_number);
                                $('#edit_room_type').val(tenant.room_type);
                                if (tenant.commence_date) {
                                    $('#edit_commence_date').val(tenant.commence_date.substring(0, 10));
                                }
                                $('#edit_monthly_rent').val(tenant.monthly_rent);
                                $('#edit_security_deposit').val(tenant.security_deposit);
                                $('#edit_address').val(tenant.address);

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

