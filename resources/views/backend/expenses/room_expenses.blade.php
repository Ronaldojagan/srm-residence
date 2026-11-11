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
            <h4 class="page-title-main page">Room Expenses</h4>
            <p class="text-muted small mb-0">Track and manage room maintenance, utility bills, appliances, and repairs.</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#addRoomExpenseModal">
                <i class="mdi mdi-cash-plus me-1"></i> Add Room Expense
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mt-3 g-3">
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-danger">
                <div class="text-muted small text-uppercase fw-bold">Total Room Expenses</div>
                <h3 class="fw-bold my-1 text-danger" id="statTotalExpenses">₹{{ number_format($stats['totalExpenses'], 2) }}</h3>
                <small class="text-muted">All-time maintenance spend</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-warning">
                <div class="text-muted small text-uppercase fw-bold">This Month's Spend</div>
                <h3 class="fw-bold my-1 text-warning" id="statThisMonthExpenses">₹{{ number_format($stats['thisMonthExpenses'], 2) }}</h3>
                <small class="text-muted">Current billing cycle</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">Avg. Cost / Room</div>
                <h3 class="fw-bold my-1 text-primary" id="statAvgPerRoom">₹{{ number_format($stats['avgPerRoom'], 2) }}</h3>
                <small class="text-muted">Across all units</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-info">
                <div class="text-muted small text-uppercase fw-bold">Expense Entries</div>
                <h3 class="fw-bold my-1 text-info" id="statTotalRecords">{{ $stats['totalRecords'] }}</h3>
                <small class="text-muted">Recorded invoices</small>
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
                            <label class="form-label small text-muted mb-1">Filter by Room</label>
                            <select id="filterRoom" class="form-select filter-select">
                                <option value="">All Rooms</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}">Room {{ $r->room_number }} ({{ $r->floor }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Filter by Category</label>
                            <select id="filterCategory" class="form-select filter-select">
                                <option value="">All Categories</option>
                                <option value="Maintenance & Repairs">Maintenance & Repairs</option>
                                <option value="Electricity Bill">Electricity Bill</option>
                                <option value="Water & Plumbing">Water & Plumbing</option>
                                <option value="Painting & Civil">Painting & Civil</option>
                                <option value="Housekeeping & Cleaning">Housekeeping & Cleaning</option>
                                <option value="Furniture & Appliance">Furniture & Appliance</option>
                                <option value="WiFi & Internet">WiFi & Internet</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3 text-end">
                            <button id="btnResetFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                            <button type="button" id="btnExportRoomExpenses" class="btn btn-outline-success btn-sm ms-2">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </button>
                        </div>
                    </div>

                    <!-- Modern Bulk Action Bar -->
                    <div id="bulkActionBar" class="d-none align-items-center justify-content-between p-2 px-3 mb-2 rounded-3" style="background: linear-gradient(135deg, #0a5858 0%, #0d7373 100%); color: #fff; box-shadow: 0 4px 12px rgba(10, 88, 88, 0.25);">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-dark fw-bold px-2 py-1 font-12" id="bulkSelectedCount">0 Selected</span>
                            <span class="font-12 text-white-50 d-none d-sm-inline">Actions for selected expenses:</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-warning btn-sm fw-semibold btn-bulk-edit px-3 py-1 font-12">
                                <i class="fas fa-edit me-1"></i> Bulk Edit
                            </button>
                            <button type="button" class="btn btn-danger btn-sm fw-semibold btn-bulk-delete px-3 py-1 font-12">
                                <i class="fas fa-trash-alt me-1"></i> Bulk Delete
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light btn-bulk-deselect px-2 py-1 font-11" title="Deselect All">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table job-table w-100" id="roomExpensesTable">
                            <thead class="table-theme text-white text-nowrap">
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox" class="form-check-input bulk-select-all" id="bulkSelectAll">
                                    </th>
                                    <th style="width: 4%;">#</th>
                                    <th style="min-width: 140px;">Room & Floor</th>
                                    <th style="min-width: 200px;">Title & Description</th>
                                    <th style="min-width: 150px;">Category</th>
                                    <th style="min-width: 130px;" class="text-nowrap">Amount</th>
                                    <th style="min-width: 110px;" class="text-nowrap">Date</th>
                                    <th style="min-width: 160px;">Paid To / Vendor</th>
                                    <th style="min-width: 100px;" class="text-center text-nowrap">Action</th>
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

    <!-- ADD ROOM EXPENSE MODAL -->
    <div class="modal fade" id="addRoomExpenseModal" tabindex="-1" aria-labelledby="addRoomExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="addRoomExpenseModalLabel">
                        <i class="mdi mdi-cash-plus me-1"></i> Record Room Expense
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addRoomExpenseForm" action="{{ url('/admin/expenses') }}" method="POST">
                    @csrf
                    <input type="hidden" name="expense_type" value="room">
                    <div class="modal-body p-4">
                        <div id="addExpenseErrors" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Room</label>
                                <select name="room_id" class="form-select">
                                    <option value="">General / Common Area</option>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">Room {{ $r->room_number }} ({{ $r->floor }} - {{ $r->room_type }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty if common residence expense.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="Maintenance & Repairs">Maintenance & Repairs</option>
                                    <option value="Electricity Bill">Electricity Bill</option>
                                    <option value="Water & Plumbing">Water & Plumbing</option>
                                    <option value="Painting & Civil">Painting & Civil</option>
                                    <option value="Housekeeping & Cleaning">Housekeeping & Cleaning</option>
                                    <option value="Furniture & Appliance">Furniture & Appliance</option>
                                    <option value="WiFi & Internet">WiFi & Internet</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Expense Title / Item <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. AC Gas Refill, Water Tap Replacement, Geyser Servicing" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control" placeholder="1500" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="Cash" selected>Cash</option>
                                    <option value="UPI / GPay">UPI / GPay</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Debit / Credit Card">Debit / Credit Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Paid To / Vendor / Technician</label>
                                <input type="text" name="paid_to" class="form-control" placeholder="e.g. SRM Electricals, Plumber Ramesh">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Notes & Invoice Details</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Invoice #4029, work completed with 6-month warranty"></textarea>
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

    <!-- EDIT ROOM EXPENSE MODAL -->
    <div class="modal fade" id="editRoomExpenseModal" tabindex="-1" aria-labelledby="editRoomExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="editRoomExpenseModalLabel">
                        <i class="mdi mdi-cash-multiple me-1"></i> Edit Room Expense
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editRoomExpenseForm">
                    @csrf
                    <input type="hidden" id="edit_expense_id" name="expense_id">
                    <div class="modal-body p-4">
                        <div id="editExpenseErrors" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Room</label>
                                <select id="edit_room_id" name="room_id" class="form-select">
                                    <option value="">General / Common Area</option>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">Room {{ $r->room_number }} ({{ $r->floor }} - {{ $r->room_type }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                <select id="edit_category" name="category" class="form-select" required>
                                    <option value="Maintenance & Repairs">Maintenance & Repairs</option>
                                    <option value="Electricity Bill">Electricity Bill</option>
                                    <option value="Water & Plumbing">Water & Plumbing</option>
                                    <option value="Painting & Civil">Painting & Civil</option>
                                    <option value="Housekeeping & Cleaning">Housekeeping & Cleaning</option>
                                    <option value="Furniture & Appliance">Furniture & Appliance</option>
                                    <option value="WiFi & Internet">WiFi & Internet</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Expense Title / Item <span class="text-danger">*</span></label>
                                <input type="text" id="edit_title" name="title" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" id="edit_amount" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" id="edit_expense_date" name="expense_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <select id="edit_payment_method" name="payment_method" class="form-select" required>
                                    <option value="Cash">Cash</option>
                                    <option value="UPI / GPay">UPI / GPay</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Debit / Credit Card">Debit / Credit Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Paid To / Vendor / Technician</label>
                                <input type="text" id="edit_paid_to" name="paid_to" class="form-control">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Notes & Invoice Details</label>
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

    <!-- BULK EDIT ROOM EXPENSES MODAL -->
    <div class="modal fade" id="bulkEditRoomExpenseModal" tabindex="-1" aria-labelledby="bulkEditRoomExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <div>
                        <h5 class="modal-title text-white" id="bulkEditRoomExpenseModalLabel">
                            <i class="fas fa-edit me-1"></i> Bulk Edit Room Expenses
                        </h5>
                        <small class="text-white-50">Updating <span id="bulkEditCountBadge" class="badge bg-warning text-dark fw-bold"></span></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bulkEditRoomExpenseForm">
                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 px-3 font-12 mb-3">
                            <i class="fas fa-info-circle me-1"></i> <strong>Note:</strong> Only filled fields will be updated across all selected records. Leave fields empty to keep their current values.
                        </div>
                        <div id="bulkEditErrors" class="alert alert-danger d-none py-2 px-3 font-12"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">-- Keep Existing --</option>
                                    <option value="Maintenance & Repairs">Maintenance & Repairs</option>
                                    <option value="Electricity Bill">Electricity Bill</option>
                                    <option value="Water & Plumbing">Water & Plumbing</option>
                                    <option value="Painting & Civil">Painting & Civil</option>
                                    <option value="Housekeeping & Cleaning">Housekeeping & Cleaning</option>
                                    <option value="Furniture & Appliance">Furniture & Appliance</option>
                                    <option value="WiFi & Internet">WiFi & Internet</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Expense Date</label>
                                <input type="date" name="expense_date" class="form-control" placeholder="Keep Existing">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">-- Keep Existing --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="UPI / GPay">UPI / GPay</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Debit / Credit Card">Debit / Credit Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Paid To / Vendor</label>
                                <input type="text" name="paid_to" class="form-control" placeholder="Leave empty to keep existing">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold font-13">General Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Leave empty to keep existing notes"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white" id="btnApplyBulkEdit">
                            <i class="fas fa-check-double me-1"></i> Apply Changes
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
                        $('#statAvgPerRoom').text('₹' + Number(stats.avgPerRoom).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#statTotalRecords').text(stats.totalRecords);
                    }
                }

                // Initialize DataTable
                var table = $('#roomExpensesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('/admin/expenses/rooms') }}",
                        data: function(d) {
                            d.room_id = $('#filterRoom').val();
                            d.category = $('#filterCategory').val();
                            d.payment_method = $('#filterPaymentMethod').val();
                        }
                    },
                    columns: [
                        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'room_badge', name: 'room_id' },
                        { data: 'title_desc', name: 'title' },
                        { data: 'category_badge', name: 'category' },
                        { data: 'amount_display', name: 'amount' },
                        { data: 'expense_date_formatted', name: 'expense_date' },
                        { data: 'paid_to_display', name: 'paid_to' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[6, 'desc']],
                    language: {
                        search: "",
                        searchPlaceholder: "Search room expenses...",
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
                $('#filterRoom, #filterCategory, #filterPaymentMethod').on('change', function() {
                    table.ajax.reload();
                });

                $('#btnResetFilters').on('click', function() {
                    $('#filterRoom').val('');
                    $('#filterCategory').val('');
                    $('#filterPaymentMethod').val('');
                    table.search('').draw();
                });

                $('#btnExportRoomExpenses').on('click', function() {
                    var params = {
                        room_id: $('#filterRoom').val(),
                        category: $('#filterCategory').val(),
                        payment_method: $('#filterPaymentMethod').val(),
                        search: table.search()
                    };
                    window.location.href = "{{ route('expenses.rooms.export') }}?" + $.param(params);
                });

                // 1. CREATE ROOM EXPENSE VIA AJAX
                $('#addRoomExpenseForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveExpense');
                    var $spinner = $('#saveExpenseSpinner');
                    var $errors = $('#addExpenseErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var title = $.trim($form.find('input[name="title"]').val());
                    if (!title || title.length < 3) {
                        $errors.html('Expense title must be at least 3 characters.').removeClass('d-none');
                        $form.find('input[name="title"]').addClass('is-invalid').focus();
                        return;
                    }

                    var amount = parseFloat($form.find('input[name="amount"]').val());
                    if (isNaN(amount) || amount < 1) {
                        $errors.html('Expense amount must be at least ₹1.00.').removeClass('d-none');
                        $form.find('input[name="amount"]').addClass('is-invalid').focus();
                        return;
                    }

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
                                $('#addRoomExpenseModal').modal('hide');
                                $form[0].reset();
                                $form.find('.is-invalid').removeClass('is-invalid');
                                table.ajax.reload(null, false);
                                updateStatCards(response.stats);
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Expense recorded successfully!'
                                });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var errorMsg = 'Please verify expense details.';
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
                $('#addRoomExpenseForm, #editRoomExpenseForm').on('input change', 'input, select, textarea', function() {
                    $(this).removeClass('is-invalid');
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
                                $('#edit_room_id').val(exp.room_id || '');
                                $('#edit_category').val(exp.category);
                                $('#edit_title').val(exp.title);
                                $('#edit_amount').val(exp.amount);
                                if (exp.expense_date) {
                                    $('#edit_expense_date').val(exp.expense_date.substring(0, 10));
                                }
                                $('#edit_payment_method').val(exp.payment_method);
                                $('#edit_paid_to').val(exp.paid_to);
                                $('#edit_notes').val(exp.notes);

                                $('#editRoomExpenseModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to fetch expense details' });
                        }
                    });
                });

                // 3. UPDATE EXPENSE VIA AJAX
                $('#editRoomExpenseForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var id = $('#edit_expense_id').val();
                    var $btn = $('#btnUpdateExpense');
                    var $spinner = $('#updateExpenseSpinner');
                    var $errors = $('#editExpenseErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var title = $.trim($form.find('#edit_title').val());
                    if (!title || title.length < 3) {
                        $errors.html('Expense title must be at least 3 characters.').removeClass('d-none');
                        $form.find('#edit_title').addClass('is-invalid').focus();
                        return;
                    }

                    var amount = parseFloat($form.find('#edit_amount').val());
                    if (isNaN(amount) || amount < 1) {
                        $errors.html('Expense amount must be at least ₹1.00.').removeClass('d-none');
                        $form.find('#edit_amount').addClass('is-invalid').focus();
                        return;
                    }

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
                                $('#editRoomExpenseModal').modal('hide');
                                $form.find('.is-invalid').removeClass('is-invalid');
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

                // =========================================================================
                // 5. BULK SELECTION, BULK EDIT & BULK DELETE
                // =========================================================================
                function updateBulkBar() {
                    var checkedBoxes = $('#roomExpensesTable tbody .bulk-select-row:checked');
                    var count = checkedBoxes.length;
                    var totalBoxes = $('#roomExpensesTable tbody .bulk-select-row').length;

                    if (count > 0) {
                        $('#bulkSelectedCount').text(count + ' Selected');
                        $('#bulkActionBar').removeClass('d-none').addClass('d-flex');
                    } else {
                        $('#bulkActionBar').removeClass('d-flex').addClass('d-none');
                    }

                    if (totalBoxes > 0 && count === totalBoxes) {
                        $('#bulkSelectAll').prop('checked', true).prop('indeterminate', false);
                    } else if (count > 0) {
                        $('#bulkSelectAll').prop('checked', false).prop('indeterminate', true);
                    } else {
                        $('#bulkSelectAll').prop('checked', false).prop('indeterminate', false);
                    }
                }

                $(document).on('change', '#bulkSelectAll', function() {
                    var isChecked = $(this).is(':checked');
                    $('#roomExpensesTable tbody .bulk-select-row').prop('checked', isChecked);
                    updateBulkBar();
                });

                $(document).on('change', '#roomExpensesTable tbody .bulk-select-row', function() {
                    updateBulkBar();
                });

                table.on('draw', function() {
                    updateBulkBar();
                });

                $(document).on('click', '.btn-bulk-deselect', function() {
                    $('#roomExpensesTable tbody .bulk-select-row').prop('checked', false);
                    $('#bulkSelectAll').prop('checked', false).prop('indeterminate', false);
                    updateBulkBar();
                });

                // Bulk Delete
                $(document).on('click', '.btn-bulk-delete', function() {
                    var ids = [];
                    $('#roomExpensesTable tbody .bulk-select-row:checked').each(function() {
                        ids.push($(this).val());
                    });

                    if (ids.length === 0) {
                        Toast.fire({ icon: 'warning', title: 'No expenses selected.' });
                        return;
                    }

                    Swal.fire({
                        title: "Delete " + ids.length + " Expenses?",
                        text: "Are you sure you want to delete these " + ids.length + " selected records? This action cannot be undone.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f34e4e",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete " + ids.length + " records"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "POST",
                                url: "{{ route('expenses.bulk_delete') }}",
                                data: {
                                    ids: ids,
                                    _token: "{{ csrf_token() }}"
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.success) {
                                        Toast.fire({ icon: 'success', title: res.message });
                                        $('#bulkSelectAll').prop('checked', false);
                                        table.ajax.reload(null, false);
                                        // Reload stats
                                        $.get("{{ url('/admin/expenses/rooms') }}", function() {
                                            location.reload();
                                        });
                                    } else {
                                        Toast.fire({ icon: 'error', title: res.message || 'Bulk delete failed.' });
                                    }
                                },
                                error: function(xhr) {
                                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error performing bulk delete.';
                                    Toast.fire({ icon: 'error', title: msg });
                                }
                            });
                        }
                    });
                });

                // Bulk Edit
                $(document).on('click', '.btn-bulk-edit', function() {
                    var count = $('#roomExpensesTable tbody .bulk-select-row:checked').length;
                    if (count === 0) {
                        Toast.fire({ icon: 'warning', title: 'No expenses selected.' });
                        return;
                    }
                    $('#bulkEditCountBadge').text(count + ' selected');
                    $('#bulkEditRoomExpenseForm')[0].reset();
                    $('#bulkEditErrors').addClass('d-none').html('');
                    $('#bulkEditRoomExpenseModal').modal('show');
                });

                $('#bulkEditRoomExpenseForm').on('submit', function(e) {
                    e.preventDefault();
                    var ids = [];
                    $('#roomExpensesTable tbody .bulk-select-row:checked').each(function() {
                        ids.push($(this).val());
                    });

                    if (ids.length === 0) {
                        Toast.fire({ icon: 'warning', title: 'No expenses selected.' });
                        return;
                    }

                    var formData = $(this).serializeArray();
                    formData.push({ name: '_token', value: "{{ csrf_token() }}" });
                    ids.forEach(function(id) {
                        formData.push({ name: 'ids[]', value: id });
                    });

                    var $btn = $('#btnApplyBulkEdit');
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

                    $.ajax({
                        type: "POST",
                        url: "{{ route('expenses.bulk_update') }}",
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Apply Changes');
                            if (res.success) {
                                $('#bulkEditRoomExpenseModal').modal('hide');
                                Toast.fire({ icon: 'success', title: res.message });
                                $('#bulkSelectAll').prop('checked', false);
                                table.ajax.reload(null, false);
                            } else {
                                $('#bulkEditErrors').removeClass('d-none').html(res.message || 'Bulk update failed.');
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Apply Changes');
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update selected expenses.';
                            $('#bulkEditErrors').removeClass('d-none').html(msg);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

