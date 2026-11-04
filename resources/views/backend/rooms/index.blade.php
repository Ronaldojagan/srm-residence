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
            <p class="text-muted small mb-0">Manage 23 residence rooms, floor allocations, occupancies, and rental rates.</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="mdi mdi-door-closed me-1"></i> Create Room
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mt-3 g-3">
        <div class="col-md-3">
            <div class="room-stat-card border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">Total Rooms</div>
                <h3 class="fw-bold my-1 text-primary">{{ $stats['totalRooms'] }}</h3>
                <small class="text-muted">Total inventory units</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="room-stat-card border-start border-4 border-success">
                <div class="text-muted small text-uppercase fw-bold">Available Rooms</div>
                <h3 class="fw-bold my-1 text-success">{{ $stats['availableRooms'] }}</h3>
                <small class="text-muted">Ready for lease</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="room-stat-card border-start border-4 border-danger">
                <div class="text-muted small text-uppercase fw-bold">Occupied Units</div>
                <h3 class="fw-bold my-1 text-danger">{{ $stats['occupiedRooms'] }}</h3>
                <small class="text-muted">Currently tenanted</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="room-stat-card border-start border-4 border-warning">
                <div class="text-muted small text-uppercase fw-bold">Under Maintenance</div>
                <h3 class="fw-bold my-1 text-warning">{{ $stats['maintenanceRooms'] }}</h3>
                <small class="text-muted">Temporarily blocked</small>
            </div>
        </div>
    </div>

    <!-- Filters & DataTable -->
    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <!-- Filters row -->
                    <div class="row mb-3 g-2 align-items-center">
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
                        <div class="col-md-3 text-end pt-3">
                            <button id="btnResetFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table job-table w-100" id="roomsTable">
                            <thead class="table-theme text-white">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Room & Floor</th>
                                    <th>Type & Capacity</th>
                                    <th>Monthly Rent</th>
                                    <th>Occupancy</th>
                                    <th>Amenities</th>
                                    <th>Active</th>
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
                <form id="addRoomForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div id="addRoomErrors" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Room Number <span class="text-danger">*</span></label>
                                <input type="text" name="room_number" class="form-control" placeholder="e.g. 101, 204, 305" required>
                                <small class="text-muted">Unique unit identification number.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Floor <span class="text-danger">*</span></label>
                                <select name="floor" class="form-select" required>
                                    <option value="1st Floor">1st Floor</option>
                                    <option value="2nd Floor">2nd Floor</option>
                                    <option value="3rd Floor">3rd Floor</option>
                                    <option value="Ground Floor">Ground Floor</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Room Type <span class="text-danger">*</span></label>
                                <select name="room_type" class="form-select" required>
                                    <option value="Single Deluxe">Single Deluxe</option>
                                    <option value="Double Sharing">Double Sharing</option>
                                    <option value="Triple Sharing">Triple Sharing</option>
                                    <option value="Executive Suite">Executive Suite</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bed Capacity <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" class="form-control" value="1" min="1" max="10" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="monthly_rent" class="form-control" placeholder="8500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Security Deposit (₹)</label>
                                <input type="number" step="0.01" name="security_deposit" class="form-control" placeholder="10000" value="10000">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Initial Occupancy Status <span class="text-danger">*</span></label>
                                <select name="occupancy_status" class="form-select" required>
                                    <option value="available">Available (Vacant & Ready)</option>
                                    <option value="occupied">Occupied</option>
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
                                <input type="text" id="edit_room_number" name="room_number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Floor <span class="text-danger">*</span></label>
                                <select id="edit_floor" name="floor" class="form-select" required>
                                    <option value="1st Floor">1st Floor</option>
                                    <option value="2nd Floor">2nd Floor</option>
                                    <option value="3rd Floor">3rd Floor</option>
                                    <option value="Ground Floor">Ground Floor</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Room Type <span class="text-danger">*</span></label>
                                <select id="edit_room_type" name="room_type" class="form-select" required>
                                    <option value="Single Deluxe">Single Deluxe</option>
                                    <option value="Double Sharing">Double Sharing</option>
                                    <option value="Triple Sharing">Triple Sharing</option>
                                    <option value="Executive Suite">Executive Suite</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bed Capacity <span class="text-danger">*</span></label>
                                <input type="number" id="edit_capacity" name="capacity" class="form-control" min="1" max="10" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" id="edit_monthly_rent" name="monthly_rent" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Security Deposit (₹)</label>
                                <input type="number" step="0.01" id="edit_security_deposit" name="security_deposit" class="form-control">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Occupancy Status <span class="text-danger">*</span></label>
                                <select id="edit_occupancy_status" name="occupancy_status" class="form-select" required>
                                    <option value="available">Available (Vacant & Ready)</option>
                                    <option value="occupied">Occupied</option>
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

    @push('foot_content')
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
                        search: "_INPUT_",
                        searchPlaceholder: "Search rooms...",
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
                    table.ajax.reload();
                });

                // 1. CREATE ROOM VIA AJAX
                $('#addRoomForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveRoom');
                    var $spinner = $('#saveRoomSpinner');
                    var $errors = $('#addRoomErrors');

                    $errors.addClass('d-none').html('');
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
                                table.ajax.reload(null, false);
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
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $errors.html(errorMsg).removeClass('d-none');
                        }
                    });
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
                                $('#edit_monthly_rent').val(room.monthly_rent);
                                $('#edit_security_deposit').val(room.security_deposit);
                                $('#edit_occupancy_status').val(room.occupancy_status);
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
                                table.ajax.reload(null, false);
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
