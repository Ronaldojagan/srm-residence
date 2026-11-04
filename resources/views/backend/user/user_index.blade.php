@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .user-card {
                background: #ffffff;
                border-radius: 16px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
            }
            .user-table thead th {
                background: linear-gradient(135deg, #0ea1b1, #087582);
                color: #ffffff;
                font-weight: 600;
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                border: none;
                padding: 12px 16px;
            }
            .user-table tbody td {
                padding: 14px 16px;
                vertical-align: middle;
            }
            .modal-content {
                border-radius: 18px;
                border: none;
                box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            }
            .modal-header {
                background: linear-gradient(135deg, #0ea1b1, #087582);
                color: #ffffff;
                border-top-left-radius: 18px;
                border-top-right-radius: 18px;
                padding: 1.25rem 1.5rem;
            }
            .modal-header .btn-close {
                filter: brightness(0) invert(1);
            }
        </style>
    @endpush

    <div class="row align-items-center justify-content-between mt-3 mb-3">
        <div class="col-md-6">
            <h4 class="page-title-main page mb-1">
                <i class="fas fa-users text-primary me-2"></i> User & Tenant Management
            </h4>
            <p class="text-muted small mb-0">Manage resident tenants, staff, and administrative users with instant AJAX actions.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary px-3 py-2 rounded-pill fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-1"></i> Add New User
            </button>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card user-card">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover user-table data-table w-100">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="8%">Avatar</th>
                                    <th width="28%">User & Email</th>
                                    <th width="18%">Phone</th>
                                    <th width="15%">Role</th>
                                    <th width="12%">Status</th>
                                    <th width="14%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addUserModalLabel">
                        <i class="fas fa-user-plus me-2"></i> Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addUserForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div id="addFormErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="user@srmresidence.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role / Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="customer" selected>Tenant / Customer</option>
                                    <option value="Admin">Admin</option>
                                    <option value="delivery">Staff / Staff Delivery</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnSaveUser">
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
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editUserModalLabel">
                        <i class="fas fa-user-edit me-2"></i> Edit User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm">
                    @csrf
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="modal-body p-4">
                        <div id="editFormErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" id="edit_phone" name="phone" class="form-control">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role / Type <span class="text-danger">*</span></label>
                                <select id="edit_type" name="type" class="form-select" required>
                                    <option value="customer">Tenant / Customer</option>
                                    <option value="Admin">Admin</option>
                                    <option value="delivery">Staff / Staff Delivery</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">New Password (Optional)</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnUpdateUser">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="updateSpinner"></span>
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

        <script type="text/javascript">
            $(function() {
                // Initialize DataTable
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/admin/users') }}",
                    order: [[0, 'desc']],
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
                        search: "_INPUT_",
                        searchPlaceholder: "Search users..."
                    }
                });

                // Toast Helper
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // 1. CREATE USER VIA AJAX
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
                        title: "Delete User?",
                        text: "Are you sure you want to delete " + userName + "? This action can be undone from trash.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#ef4444",
                        cancelButtonColor: "#64748b",
                        confirmButtonText: "Yes, delete"
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
                                },
                                error: function() {
                                    Toast.fire({ icon: 'error', title: 'Error deleting user' });
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
