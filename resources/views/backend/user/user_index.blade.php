@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <style>
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #0ea1b1 !important;
                color: #fff !important;
                border-color: #0ea1b1 !important;
                border-radius: 5px;
            }
            .data-table thead th {
                vertical-align: middle;
            }
            .action-btn-group .btn {
                padding: 4px 8px;
                font-size: 13px;
                margin: 0 2px;
                border-radius: 4px;
            }
        </style>
    @endpush

    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-6">
            <h4 class="page-title-main page">System Users</h4>
            <p class="text-muted mb-0 font-13">System administrators and operational staff accounts</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="mdi mdi-plus-circle me-1"></i> Add New User
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mt-3 g-3">
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="room-stat-card border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">Total System Users</div>
                <h3 class="fw-bold my-1 text-primary" id="statTotalUsers">{{ $stats['totalUsers'] ?? 0 }}</h3>
                <small class="text-muted">Registered accounts</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="room-stat-card border-start border-4 border-success">
                <div class="text-muted small text-uppercase fw-bold">Active Users</div>
                <h3 class="fw-bold my-1 text-success" id="statActiveUsers">{{ $stats['activeUsers'] ?? 0 }}</h3>
                <small class="text-muted">Enabled system access</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="room-stat-card border-start border-4 border-purple" style="border-left-color: #8b5cf6 !important;">
                <div class="text-muted small text-uppercase fw-bold">Administrators</div>
                <h3 class="fw-bold my-1 text-purple" style="color: #8b5cf6;" id="statAdminCount">{{ $stats['adminCount'] ?? 0 }}</h3>
                <small class="text-muted">Full system privilege</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="room-stat-card border-start border-4 border-info">
                <div class="text-muted small text-uppercase fw-bold">Staff & Operational</div>
                <h3 class="fw-bold my-1 text-info" id="statStaffCount">{{ $stats['staffCount'] ?? 0 }}</h3>
                <small class="text-muted">Staff / Delivery roles</small>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <!-- Filter Row -->
                    <div class="row mb-3 g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Filter by Role</label>
                            <select id="filterUserType" class="form-select filter-select">
                                <option value="">All Roles</option>
                                <option value="Admin">Administrator</option>
                                <option value="delivery">Staff / Manager</option>
                                <option value="customer">Standard User</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Filter by Status</label>
                            <select id="filterUserStatus" class="form-select filter-select">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" id="btnResetUserFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                            <button type="button" id="btnExportUsers" class="btn btn-outline-success btn-sm ms-2">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table job-table w-100">
                            <thead class="table-theme text-white text-nowrap">
                                <tr>
                                    <th style="width: 5%;">S.No</th>
                                    <th style="width: 80px;" class="text-center">Avatar</th>
                                    <th style="min-width: 200px;">Name & Email</th>
                                    <th style="min-width: 140px;" class="text-nowrap">Phone</th>
                                    <th style="min-width: 130px;" class="text-nowrap">Role</th>
                                    <th style="min-width: 100px;" class="text-nowrap">Status</th>
                                    <th style="min-width: 130px;" class="text-center text-nowrap">Action</th>
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

    <!-- ADD USER MODAL (AJAX) -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="addUserModalLabel">
                        <i class="mdi mdi-account-plus me-1"></i> Add New User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addUserForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div id="addFormErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="user@srmresidence.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+91 9876543210">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="Admin">Administrator</option>
                                    <option value="delivery">Staff / Manager</option>
                                    <option value="customer">Standard User</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white waves-effect waves-light" id="btnSaveUser">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="saveSpinner"></span>
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT USER MODAL (AJAX) -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="editUserModalLabel">
                        <i class="mdi mdi-account-edit me-1"></i> Edit User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm">
                    @csrf
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="modal-body p-4">
                        <div id="editFormErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" id="edit_phone" name="phone" class="form-control">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select id="edit_type" name="type" class="form-select" required>
                                    <option value="Admin">Administrator</option>
                                    <option value="delivery">Staff / Manager</option>
                                    <option value="customer">Standard User</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password (Optional)</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white waves-effect waves-light" id="btnUpdateUser">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="updateSpinner"></span>
                            Update User
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
                    ajax: {
                        url: "{{ url('/admin/users') }}",
                        data: function(d) {
                            d.type = $('#filterUserType').val();
                            d.status = $('#filterUserStatus').val();
                        }
                    },
                    order: [],
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'image', name: 'image', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'phone', name: 'phone' },
                        { data: 'type', name: 'type' },
                        { data: 'status', name: 'status', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search users...",
                        lengthMenu: "Show _MENU_ entries",
                        zeroRecords: "No matching users found",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)"
                    }
                });

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                function updateStatCards(stats) {
                    if (!stats) return;
                    if (stats.totalUsers !== undefined) $('#statTotalUsers').text(stats.totalUsers);
                    if (stats.activeUsers !== undefined) $('#statActiveUsers').text(stats.activeUsers);
                    if (stats.adminCount !== undefined) $('#statAdminCount').text(stats.adminCount);
                    if (stats.staffCount !== undefined) $('#statStaffCount').text(stats.staffCount);
                }

                function reloadStats() {
                    $.ajax({
                        url: "{{ url('/admin/users/stats') }}",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.stats) {
                                updateStatCards(res.stats);
                            }
                        }
                    });
                }

                // Filter change triggers
                $('#filterUserType, #filterUserStatus').on('change', function() {
                    table.ajax.reload();
                });

                $('#btnResetUserFilters').on('click', function() {
                    $('#filterUserType').val('');
                    $('#filterUserStatus').val('');
                    table.search('').draw();
                });

                $('#btnExportUsers').on('click', function() {
                    var params = {
                        type: $('#filterUserType').val(),
                        status: $('#filterUserStatus').val(),
                        search: table.search()
                    };
                    window.location.href = "{{ route('users.export') }}?" + $.param(params);
                });

                // 1. ADD USER VIA AJAX
                $('#addUserForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveUser');
                    var $spinner = $('#saveSpinner');
                    var $errors = $('#addFormErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/users') }}",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#addUserModal').modal('hide');
                                $form[0].reset();
                                table.ajax.reload(null, false);
                                if (response.stats) {
                                    updateStatCards(response.stats);
                                } else {
                                    reloadStats();
                                }
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'User created successfully!'
                                });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var errorMsg = 'An error occurred. Please check your inputs.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $errors.html(errorMsg).removeClass('d-none');
                        }
                    });
                });

                // 2. OPEN EDIT MODAL & LOAD DATA VIA AJAX
                $(document).on('click', '.btn-edit-user', function() {
                    var encId = $(this).data('id');
                    $('#editFormErrors').addClass('d-none').html('');

                    $.ajax({
                        url: "{{ url('/admin/users') }}/" + encId + "/data",
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                var user = response.user;
                                $('#edit_user_id').val(response.encrypted_id);
                                $('#edit_name').val(user.name);
                                $('#edit_email').val(user.email);
                                $('#edit_phone').val(user.phone);
                                $('#edit_type').val(user.type);
                                $('#editUserModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to fetch user details' });
                        }
                    });
                });

                // 3. UPDATE USER VIA AJAX
                $('#editUserForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var encId = $('#edit_user_id').val();
                    var $btn = $('#btnUpdateUser');
                    var $spinner = $('#updateSpinner');
                    var $errors = $('#editFormErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/users') }}/" + encId + "/update",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#editUserModal').modal('hide');
                                table.ajax.reload(null, false);
                                if (response.stats) {
                                    updateStatCards(response.stats);
                                } else {
                                    reloadStats();
                                }
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'User updated successfully!'
                                });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var errorMsg = 'An error occurred. Please check your inputs.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $errors.html(errorMsg).removeClass('d-none');
                        }
                    });
                });

                // 4. DELETE USER VIA AJAX
                $(document).on('click', '.btn-delete-user', function() {
                    var encId = $(this).data('id');
                    var userName = $(this).data('name') || 'this user';

                    Swal.fire({
                        title: "Are you sure?",
                        text: "Delete " + userName + "?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#f34e4e",
                        confirmButtonText: "Yes, delete it!"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/users') }}/" + encId,
                                data: {
                                    "_token": "{{ csrf_token() }}"
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(data) {
                                    if (data.status == 1 || data.success) {
                                        table.ajax.reload(null, false);
                                        if (data.stats) {
                                            updateStatCards(data.stats);
                                        } else {
                                            reloadStats();
                                        }
                                        Toast.fire({
                                            icon: 'success',
                                            title: 'User deleted successfully!'
                                        });
                                    } else {
                                        Toast.fire({
                                            icon: 'error',
                                            title: 'Failed to delete user'
                                        });
                                    }
                                }
                            });
                        }
                    });
                });

                // 5. STATUS TOGGLE VIA AJAX
                $(document).on('change', '.status-toggle', function() {
                    var $this = $(this);
                    var id = $this.data('id');
                    var status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/users/status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        },
                        success: function(data) {
                            if (data.success) {
                                table.ajax.reload(null, false);
                                if (data.stats) {
                                    updateStatCards(data.stats);
                                } else {
                                    reloadStats();
                                }
                                Toast.fire({ icon: 'success', title: 'User status updated!' });
                            } else {
                                $this.prop('checked', !$this.prop('checked'));
                                Toast.fire({ icon: 'error', title: data.message || 'Status update failed' });
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
