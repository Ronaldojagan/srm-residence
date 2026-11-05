@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .expense-stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                border: 1px solid #e2e8f0;
                transition: transform 0.2s;
            }
            .expense-stat-card:hover {
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
            <h4 class="page-title-main page">Personal Expenses</h4>
            <p class="text-muted small mb-0">Record and track personal expenditures, daily allowance, groceries, and travel.</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#addPersonalExpenseModal">
                <i class="mdi mdi-cash-plus me-1"></i> Add Personal Expense
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mt-3 g-3">
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-danger">
                <div class="text-muted small text-uppercase fw-bold">Total Personal Spend</div>
                <h3 class="fw-bold my-1 text-danger" id="statTotalExpenses">₹{{ number_format($stats['totalExpenses'], 2) }}</h3>
                <small class="text-muted">All-time personal spending</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-warning">
                <div class="text-muted small text-uppercase fw-bold">This Month's Spend</div>
                <h3 class="fw-bold my-1 text-warning" id="statThisMonthExpenses">₹{{ number_format($stats['thisMonthExpenses'], 2) }}</h3>
                <small class="text-muted">Current month total</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">Top Spending Category</div>
                <h4 class="fw-bold my-1 text-primary text-truncate" id="statTopCategory">{{ $stats['topCategory'] }}</h4>
                <small class="text-muted">Highest expense area</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-info">
                <div class="text-muted small text-uppercase fw-bold">Expense Entries</div>
                <h3 class="fw-bold my-1 text-info" id="statTotalRecords">{{ $stats['totalRecords'] }}</h3>
                <small class="text-muted">Logged receipts</small>
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
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Filter by Category</label>
                            <select id="filterCategory" class="form-select filter-select">
                                <option value="">All Categories</option>
                                <option value="Food & Groceries">Food & Groceries</option>
                                <option value="Travel & Fuel">Travel & Fuel</option>
                                <option value="Medical & Health">Medical & Health</option>
                                <option value="Mobile & Utilities">Mobile & Utilities</option>
                                <option value="Shopping & Supplies">Shopping & Supplies</option>
                                <option value="Entertainment">Entertainment</option>
                                <option value="Miscellaneous">Miscellaneous</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Payment Method</label>
                            <select id="filterPaymentMethod" class="form-select filter-select">
                                <option value="">All Payment Modes</option>
                                <option value="Cash">Cash</option>
                                <option value="UPI / GPay">UPI / GPay</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Debit / Credit Card">Debit / Credit Card</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-end pt-3">
                            <button id="btnResetFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table job-table w-100" id="personalExpensesTable">
                            <thead class="table-theme text-white">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Title & Description</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Paid To / Vendor</th>
                                    <th width="10%" class="text-center">Action</th>
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

    <!-- ADD PERSONAL EXPENSE MODAL -->
    <div class="modal fade" id="addPersonalExpenseModal" tabindex="-1" aria-labelledby="addPersonalExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="addPersonalExpenseModalLabel">
                        <i class="mdi mdi-cash-plus me-1"></i> Record Personal Expense
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addPersonalExpenseForm" action="{{ url('/admin/expenses') }}" method="POST">
                    @csrf
                    <input type="hidden" name="expense_type" value="personal">
                    <div class="modal-body p-4">
                        <div id="addExpenseErrors" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="Food & Groceries">Food & Groceries</option>
                                    <option value="Travel & Fuel">Travel & Fuel</option>
                                    <option value="Medical & Health">Medical & Health</option>
                                    <option value="Mobile & Utilities">Mobile & Utilities</option>
                                    <option value="Shopping & Supplies">Shopping & Supplies</option>
                                    <option value="Entertainment">Entertainment</option>
                                    <option value="Miscellaneous" selected>Miscellaneous</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="UPI / GPay" selected>UPI / GPay</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Debit / Credit Card">Debit / Credit Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Expense Title / Item <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Monthly Grocery Bill, Petrol Expense, Pharmacy" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control" placeholder="750" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Paid To / Merchant / Beneficiary</label>
                                <input type="text" name="paid_to" class="form-control" placeholder="e.g. Supermarket, Shell Petrol Pump, Apollo Pharmacy">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Notes & Remarks</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid online via Google Pay"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white" id="btnSaveExpense">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="saveExpenseSpinner"></span>
                            Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT PERSONAL EXPENSE MODAL -->
    <div class="modal fade" id="editPersonalExpenseModal" tabindex="-1" aria-labelledby="editPersonalExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="editPersonalExpenseModalLabel">
                        <i class="mdi mdi-cash-multiple me-1"></i> Edit Personal Expense
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPersonalExpenseForm">
                    @csrf
                    <input type="hidden" id="edit_expense_id" name="expense_id">
                    <div class="modal-body p-4">
                        <div id="editExpenseErrors" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                <select id="edit_category" name="category" class="form-select" required>
                                    <option value="Food & Groceries">Food & Groceries</option>
                                    <option value="Travel & Fuel">Travel & Fuel</option>
                                    <option value="Medical & Health">Medical & Health</option>
                                    <option value="Mobile & Utilities">Mobile & Utilities</option>
                                    <option value="Shopping & Supplies">Shopping & Supplies</option>
                                    <option value="Entertainment">Entertainment</option>
                                    <option value="Miscellaneous">Miscellaneous</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <select id="edit_payment_method" name="payment_method" class="form-select" required>
                                    <option value="UPI / GPay">UPI / GPay</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Debit / Credit Card">Debit / Credit Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Expense Title / Item <span class="text-danger">*</span></label>
                                <input type="text" id="edit_title" name="title" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" id="edit_amount" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" id="edit_expense_date" name="expense_date" class="form-control" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Paid To / Merchant / Beneficiary</label>
                                <input type="text" id="edit_paid_to" name="paid_to" class="form-control">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Notes & Remarks</label>
                                <textarea id="edit_notes" name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white" id="btnUpdateExpense">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="updateExpenseSpinner"></span>
                            Update Expense
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
                        $('#statTotalExpenses').text('₹' + Number(stats.totalExpenses).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#statThisMonthExpenses').text('₹' + Number(stats.thisMonthExpenses).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        if (stats.topCategory) {
                            $('#statTopCategory').text(stats.topCategory);
                        }
                        $('#statTotalRecords').text(stats.totalRecords);
                    }
                }

                // Initialize DataTable
                var table = $('#personalExpensesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('/admin/expenses/personal') }}",
                        data: function(d) {
                            d.category = $('#filterCategory').val();
                            d.payment_method = $('#filterPaymentMethod').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'title_desc', name: 'title' },
                        { data: 'category_badge', name: 'category' },
                        { data: 'amount_display', name: 'amount' },
                        { data: 'expense_date_formatted', name: 'expense_date' },
                        { data: 'paid_to_display', name: 'paid_to' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[4, 'desc']],
                    language: {
                        search: "",
                        searchPlaceholder: "Search personal expenses...",
                        lengthMenu: "Show _MENU_ entries",
                        zeroRecords: "No matching expenses found",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    }
                });

                // Filters trigger
                $('#filterCategory, #filterPaymentMethod').on('change', function() {
                    table.ajax.reload();
                });

                $('#btnResetFilters').on('click', function() {
                    $('#filterCategory').val('');
                    $('#filterPaymentMethod').val('');
                    table.ajax.reload();
                });

                // 1. CREATE PERSONAL EXPENSE VIA AJAX
                $('#addPersonalExpenseForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveExpense');
                    var $spinner = $('#saveExpenseSpinner');
                    var $errors = $('#addExpenseErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/expenses') }}",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#addPersonalExpenseModal').modal('hide');
                                $form[0].reset();
                                table.ajax.reload(null, false);
                                updateStatCards(response.stats);
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Personal expense recorded successfully!'
                                });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var errorMsg = 'Please verify expense details.';
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
                $(document).on('click', '.btn-edit-expense', function() {
                    var id = $(this).data('id');
                    $('#editExpenseErrors').addClass('d-none').html('');

                    $.ajax({
                        url: "{{ url('/admin/expenses') }}/" + id + "/data",
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                var exp = response.expense;
                                $('#edit_expense_id').val(exp.id);
                                $('#edit_category').val(exp.category);
                                $('#edit_title').val(exp.title);
                                $('#edit_amount').val(exp.amount);
                                if (exp.expense_date) {
                                    $('#edit_expense_date').val(exp.expense_date.substring(0, 10));
                                }
                                $('#edit_payment_method').val(exp.payment_method);
                                $('#edit_paid_to').val(exp.paid_to);
                                $('#edit_notes').val(exp.notes);

                                $('#editPersonalExpenseModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to fetch expense details' });
                        }
                    });
                });

                // 3. UPDATE EXPENSE VIA AJAX
                $('#editPersonalExpenseForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var id = $('#edit_expense_id').val();
                    var $btn = $('#btnUpdateExpense');
                    var $spinner = $('#updateExpenseSpinner');
                    var $errors = $('#editExpenseErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/expenses') }}/" + id + "/update",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#editPersonalExpenseModal').modal('hide');
                                table.ajax.reload(null, false);
                                updateStatCards(response.stats);
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Expense updated successfully!'
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

                // 4. DELETE EXPENSE VIA AJAX
                $(document).on('click', '.btn-delete-expense', function() {
                    var id = $(this).data('id');
                    var title = $(this).data('title') || 'this expense';

                    Swal.fire({
                        title: "Remove Expense?",
                        text: "Are you sure you want to delete '" + title + "'?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#f34e4e",
                        confirmButtonText: "Yes, delete"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/expenses') }}/" + id,
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
                                            title: 'Expense removed successfully!'
                                        });
                                    } else {
                                        Toast.fire({
                                            icon: 'error',
                                            title: 'Failed to delete expense'
                                        });
                                    }
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

