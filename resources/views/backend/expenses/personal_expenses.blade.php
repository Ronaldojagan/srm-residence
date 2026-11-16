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
            .cursor-pointer {
                cursor: pointer;
            }
            .line-item-row:hover {
                background-color: #f8fafc;
            }
            .font-11 { font-size: 11px; }
            .font-12 { font-size: 12px; }
            .font-13 { font-size: 13px; }
        </style>
    @endpush

    <!-- Page Header & Action -->
    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-6">
            <h4 class="page-title-main page">Personal Expenses (Monthly Wise)</h4>
            <p class="text-muted small mb-0">Record and track personal expenditures monthly-wise with itemized line items, quantity, per rate, and notes.</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#addPersonalExpenseModal">
                <i class="mdi mdi-cash-plus me-1"></i> Add Personal Expense
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards (Updated dynamically per selected month) -->
    <div class="row mt-3 g-3">
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">
                    <span id="statMonthLabel">{{ $stats['monthLabel'] ?? 'This Month' }}</span> Expenses
                </div>
                <h3 class="fw-bold my-1 text-primary" id="statThisMonthExpenses">₹{{ number_format($stats['totalExpenses'] ?? $stats['thisMonthExpenses'], 2) }}</h3>
                <small class="text-muted"><span id="statMonthRecords">{{ $stats['monthRecords'] ?? 0 }}</span> expenses in period</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-success">
                <div class="text-muted small text-uppercase fw-bold">
                    <i class="fas fa-check-circle text-success me-1"></i> Total Expenses Paid
                </div>
                <h3 class="fw-bold my-1 text-success" id="statTotalPaid">₹{{ number_format($stats['totalPaid'] ?? 0, 2) }}</h3>
                <small class="text-muted"><span id="statPaidItemsCount">{{ $stats['paidLineItems'] ?? 0 }}</span> paid items cleared</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-danger">
                <div class="text-muted small text-uppercase fw-bold">
                    <i class="fas fa-exclamation-circle text-danger me-1"></i> Balance Need to Pay
                </div>
                <h3 class="fw-bold my-1 text-danger" id="statTotalBalance">₹{{ number_format($stats['totalBalance'] ?? 0, 2) }}</h3>
                <small class="text-muted"><span id="statPendingItemsCount">{{ $stats['pendingLineItems'] ?? 0 }}</span> pending items</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="expense-stat-card border-start border-4 border-info">
                <div class="text-muted small text-uppercase fw-bold">All-Time Total / Due</div>
                <h3 class="fw-bold my-1 text-info" id="statAllTotal">₹{{ number_format($stats['allTotal'] ?? 0, 2) }}</h3>
                <small class="text-muted">Balance Due: <strong class="text-danger" id="statAllBalance">₹{{ number_format($stats['allBalance'] ?? 0, 2) }}</strong></small>
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
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1"><i class="far fa-calendar-alt me-1 text-primary"></i> Filter by Month</label>
                            <select id="filterMonth" class="form-select filter-select">
                                <option value="all">All Months (Overview)</option>
                                @foreach($availableMonths as $mKey => $mLabel)
                                    <option value="{{ $mKey }}" {{ $selectedMonth === $mKey ? 'selected' : '' }}>{{ $mLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small text-muted mb-1"><i class="fas fa-store me-1 text-info"></i> Paid To / Vendor / Merchant</label>
                            <input type="text" id="filterPaidTo" class="form-control filter-select" placeholder="Search by vendor, supermarket, recipient...">
                        </div>
                        <div class="col-md-3 text-end">
                            <button id="btnResetFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                            <button type="button" id="btnExportPersonalExpenses" class="btn btn-outline-success btn-sm ms-2">
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
                        <table class="table table-striped data-table job-table w-100" id="personalExpensesTable">
                            <thead class="table-theme text-white text-nowrap">
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox" class="form-check-input bulk-select-all" id="bulkSelectAll">
                                    </th>
                                    <th style="width: 4%;">#</th>
                                    <th style="min-width: 240px;">Title, Month & Notes</th>
                                    <th style="min-width: 200px;">Line Items Breakdown</th>
                                    <th style="min-width: 170px;" class="text-nowrap">Amount & Payment Status</th>
                                    <th style="min-width: 110px;" class="text-nowrap">Date</th>
                                    <th style="min-width: 150px;">Paid To / Vendor</th>
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

    <!-- ADD PERSONAL EXPENSE MODAL -->
    <div class="modal fade" id="addPersonalExpenseModal" tabindex="-1" aria-labelledby="addPersonalExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="addPersonalExpenseModalLabel">
                        <i class="mdi mdi-cash-plus me-1"></i> Record Personal Expense (Monthly Wise)
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
                                <label class="form-label fw-bold">Expense Month <span class="text-danger">*</span></label>
                                <select name="expense_month" id="add_expense_month" class="form-select" required>
                                    @foreach($availableMonths as $mKey => $mLabel)
                                        <option value="{{ $mLabel }}" {{ $mKey === $selectedMonth ? 'selected' : '' }}>{{ $mLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" id="add_expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Main Title / Summary <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="add_title" class="form-control" placeholder="e.g. Monthly Grocery Bill, Fuel & Commute, Medical Expenses" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Total Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="amount" id="add_amount" class="form-control fw-bold text-danger font-16" placeholder="0.00" required>
                                <div class="form-text font-10" id="add_amount_help">Auto-calculates if line items are added below</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Paid To / Merchant / Beneficiary</label>
                                <input type="text" name="paid_to" class="form-control" placeholder="e.g. Supermarket, Shell Petrol Pump, Apollo Pharmacy">
                            </div>

                            <!-- Line Items Repeater Section -->
                            <div class="col-md-12">
                                <div class="card border mb-0 bg-light">
                                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold font-13 text-dark"><i class="fas fa-list-ol text-primary me-1"></i> Itemized Line Items</span>
                                            <small class="text-muted d-block font-11">Add individual line items with S.No, quantity, per rate, amount and notes</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddLineItem">
                                            <i class="fas fa-plus me-1"></i> Add Line Item
                                        </button>
                                    </div>
                                    <div class="card-body p-2 bg-white">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle mb-0" id="addItemsTable">
                                                <thead class="table-light font-12 text-nowrap">
                                                    <tr>
                                                        <th style="width: 50px; text-align: center;">S.No</th>
                                                        <th style="min-width: 200px;">Item Title <span class="text-danger">*</span></th>
                                                        <th style="width: 100px; text-align: center;">Quantity</th>
                                                        <th style="width: 130px; text-align: right;">Per Rate (₹)</th>
                                                        <th style="width: 140px; text-align: right;">Amount (₹) <span class="text-danger">*</span></th>
                                                        <th style="width: 110px; text-align: center;">Status (Paid?)</th>
                                                        <th style="min-width: 180px;">Short Notes / Details</th>
                                                        <th style="width: 45px; text-align: center;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="addItemsBody">
                                                    <!-- Dynamic rows populated via JS -->
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-light">
                                                        <td colspan="4" class="text-end fw-bold font-12">Line Items Subtotal:</td>
                                                        <td class="text-end fw-bold text-danger font-13" id="addItemsSumDisplay">₹0.00</td>
                                                        <td colspan="3" class="text-muted font-11">Automatically syncs with Total Amount</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">General Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid online on Sunday for family monthly provisions"></textarea>
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
        <div class="modal-dialog modal-dialog-centered modal-xl">
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
                                <label class="form-label fw-bold">Expense Month <span class="text-danger">*</span></label>
                                <select name="expense_month" id="edit_expense_month" class="form-select" required>
                                    @foreach($availableMonths as $mKey => $mLabel)
                                        <option value="{{ $mLabel }}">{{ $mLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" id="edit_expense_date" name="expense_date" class="form-control" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Main Title / Summary <span class="text-danger">*</span></label>
                                <input type="text" id="edit_title" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Total Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" id="edit_amount" name="amount" class="form-control fw-bold text-danger font-16" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Paid To / Merchant / Beneficiary</label>
                                <input type="text" id="edit_paid_to" name="paid_to" class="form-control">
                            </div>

                            <!-- Edit Line Items Repeater -->
                            <div class="col-md-12">
                                <div class="card border mb-0 bg-light">
                                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold font-13 text-dark"><i class="fas fa-list-ol text-primary me-1"></i> Itemized Line Items</span>
                                            <small class="text-muted d-block font-11">Add, edit, or remove specific item quantity, rates & notes</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnEditAddLineItem">
                                            <i class="fas fa-plus me-1"></i> Add Line Item
                                        </button>
                                    </div>
                                    <div class="card-body p-2 bg-white">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle mb-0" id="editItemsTable">
                                                <thead class="table-light font-12 text-nowrap">
                                                    <tr>
                                                        <th style="width: 50px; text-align: center;">S.No</th>
                                                        <th style="min-width: 200px;">Item Title <span class="text-danger">*</span></th>
                                                        <th style="width: 100px; text-align: center;">Quantity</th>
                                                        <th style="width: 130px; text-align: right;">Per Rate (₹)</th>
                                                        <th style="width: 140px; text-align: right;">Amount (₹) <span class="text-danger">*</span></th>
                                                        <th style="width: 110px; text-align: center;">Status (Paid?)</th>
                                                        <th style="min-width: 180px;">Short Notes / Details</th>
                                                        <th style="width: 45px; text-align: center;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="editItemsBody">
                                                    <!-- Dynamic rows populated via JS -->
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-light">
                                                        <td colspan="4" class="text-end fw-bold font-12">Line Items Subtotal:</td>
                                                        <td class="text-end fw-bold text-danger font-13" id="editItemsSumDisplay">₹0.00</td>
                                                        <td colspan="3" class="text-muted font-11">Syncs with Total Amount above</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">General Notes / Remarks</label>
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

    <!-- VIEW LINE ITEMS BREAKDOWN MODAL -->
    <div class="modal fade" id="viewExpenseItemsModal" tabindex="-1" aria-labelledby="viewExpenseItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="viewExpenseItemsModalLabel">
                        <i class="fas fa-list-ul me-1"></i> Personal Expense & Line Items Breakdown
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <span class="text-muted small">Expense Title:</span>
                                <div class="fw-bold fs-6 text-dark" id="view_exp_title">-</div>
                            </div>
                            <div class="col-md-2">
                                <span class="text-muted small">Expense Month:</span>
                                <div class="fw-semibold text-primary" id="view_exp_month">-</div>
                            </div>
                            <div class="col-md-2">
                                <span class="text-muted small">Expense Date:</span>
                                <div class="fw-semibold text-dark" id="view_exp_date">-</div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="d-flex flex-column align-items-md-end">
                                    <div class="font-12">Total: <strong class="text-dark fs-6" id="view_exp_total">₹0.00</strong></div>
                                    <div class="font-11">
                                        <span class="text-success fw-bold me-2">Paid: <span id="view_exp_paid_amt">₹0.00</span></span>
                                        <span class="text-danger fw-bold">Balance: <span id="view_exp_due_amt">₹0.00</span></span>
                                    </div>
                                    <div class="mt-1" id="view_exp_status_badge"></div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2 pt-2 border-top d-flex justify-content-between align-items-center font-12 flex-wrap gap-2">
                                <div>
                                    <span class="text-muted">Paid To / Merchant:</span>
                                    <span class="fw-semibold text-secondary ms-1" id="view_exp_paid_to">-</span>
                                </div>
                                <div>
                                    <span class="text-muted">Payment Progress:</span>
                                    <span class="fw-bold text-dark ms-1" id="view_exp_progress_pct">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-layer-group text-primary me-1"></i> Itemized Line Items</h6>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-success btn-modal-mark-all-paid" id="btnModalMarkAllPaid">
                                <i class="fas fa-check-double me-1"></i> Mark All as Paid
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-modal-mark-all-unpaid" id="btnModalMarkAllUnpaid">
                                <i class="fas fa-undo me-1"></i> Mark All as Pending
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-theme text-white font-12">
                                <tr>
                                    <th style="width: 5%; text-align: center;">S.No</th>
                                    <th style="width: 28%;">Item Title</th>
                                    <th style="width: 9%; text-align: center;">Qty</th>
                                    <th style="width: 14%; text-align: right;">Per Rate (₹)</th>
                                    <th style="width: 14%; text-align: right;">Total Amount (₹)</th>
                                    <th style="width: 14%; text-align: center;">Status</th>
                                    <th style="width: 16%; text-align: center;">Action (Paid Button)</th>
                                </tr>
                            </thead>
                            <tbody id="view_exp_items_tbody">
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold font-12">
                                    <td colspan="4" class="text-end">Summary:</td>
                                    <td class="text-end text-dark font-13" id="view_exp_footer_total">₹0.00</td>
                                    <td class="text-center text-success" id="view_exp_footer_paid">Paid: ₹0.00</td>
                                    <td class="text-center text-danger" id="view_exp_footer_due">Due: ₹0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div id="view_exp_notes_box" class="p-2 bg-light rounded border text-muted small mt-2 d-none">
                        <strong>General Remarks:</strong> <span id="view_exp_notes_text"></span>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- BULK EDIT PERSONAL EXPENSES MODAL -->
    <div class="modal fade" id="bulkEditPersonalExpenseModal" tabindex="-1" aria-labelledby="bulkEditPersonalExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <div>
                        <h5 class="modal-title text-white" id="bulkEditPersonalExpenseModalLabel">
                            <i class="fas fa-edit me-1"></i> Bulk Edit Personal Expenses
                        </h5>
                        <small class="text-white-50">Updating <span id="bulkEditCountBadge" class="badge bg-warning text-dark fw-bold"></span></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bulkEditPersonalExpenseForm">
                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 px-3 font-12 mb-3">
                            <i class="fas fa-info-circle me-1"></i> <strong>Note:</strong> Only filled fields will be updated across all selected records. Leave fields empty to keep their current values.
                        </div>
                        <div id="bulkEditErrors" class="alert alert-danger d-none py-2 px-3 font-12"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Expense Month</label>
                                <select name="expense_month" class="form-select">
                                    <option value="">-- Keep Existing --</option>
                                    @foreach($availableMonths as $mKey => $mLabel)
                                        <option value="{{ $mLabel }}">{{ $mLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Expense Date</label>
                                <input type="date" name="expense_date" class="form-control" placeholder="Keep Existing">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold font-13">Paid To / Merchant</label>
                                <input type="text" name="paid_to" class="form-control" placeholder="Leave empty to keep existing">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">-- Keep Existing --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="UPI / GPay / PhonePe">UPI / GPay / PhonePe</option>
                                    <option value="Bank Transfer / NEFT / IMPS">Bank Transfer / NEFT / IMPS</option>
                                    <option value="Net Banking">Net Banking</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Category</label>
                                <input type="text" name="category" class="form-control" placeholder="e.g. Groceries, Utilities, Travel">
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
                        var total = stats.totalExpenses !== undefined ? stats.totalExpenses : (stats.thisMonthExpenses || 0);
                        var paid = stats.totalPaid !== undefined ? stats.totalPaid : (stats.thisMonthPaid || 0);
                        var bal = stats.totalBalance !== undefined ? stats.totalBalance : (stats.thisMonthBalance || 0);
                        var allTot = stats.allTotal !== undefined ? stats.allTotal : total;
                        var allBal = stats.allBalance !== undefined ? stats.allBalance : bal;

                        $('#statThisMonthExpenses').text('₹' + Number(total).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#statTotalPaid').text('₹' + Number(paid).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#statTotalBalance').text('₹' + Number(bal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#statAllTotal').text('₹' + Number(allTot).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#statAllBalance').text('₹' + Number(allBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                        if (stats.monthLabel) {
                            $('#statMonthLabel').text(stats.monthLabel);
                        }
                        $('#statMonthRecords').text(stats.monthRecords !== undefined ? stats.monthRecords : 0);
                        $('#statPaidItemsCount').text(stats.paidLineItems !== undefined ? stats.paidLineItems : 0);
                        $('#statPendingItemsCount').text(stats.pendingLineItems !== undefined ? stats.pendingLineItems : 0);
                    }
                }

                function reloadMonthlyStats() {
                    var selectedM = $('#filterMonth').val();
                    $.ajax({
                        url: "{{ url('/admin/expenses/personal/stats') }}",
                        type: "GET",
                        data: { month: selectedM },
                        success: function(res) {
                            if (res.success && res.stats) {
                                updateStatCards(res.stats);
                            }
                        }
                    });
                }

                // Initialize DataTable
                var table = $('#personalExpensesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('/admin/expenses/personal') }}",
                        data: function(d) {
                            d.month = $('#filterMonth').val();
                            d.paid_to = $('#filterPaidTo').val();
                        }
                    },
                    columns: [
                        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'title_desc', name: 'title' },
                        { data: 'items_summary', name: 'items', orderable: false, searchable: false },
                        { data: 'amount_display', name: 'amount' },
                        { data: 'expense_date_formatted', name: 'expense_date' },
                        { data: 'paid_to_display', name: 'paid_to' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[5, 'desc']],
                    language: {
                        search: "",
                        searchPlaceholder: "Search personal expenses...",
                        lengthMenu: "Show _MENU_ entries",
                        zeroRecords: "No matching personal expenses found",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    }
                });

                // Filters trigger
                $('#filterMonth').on('change', function() {
                    table.ajax.reload();
                    reloadMonthlyStats();
                });

                var filterTimer;
                $('#filterPaidTo').on('keyup input', function() {
                    clearTimeout(filterTimer);
                    filterTimer = setTimeout(function() {
                        table.ajax.reload();
                    }, 400);
                });

                $('#btnResetFilters').on('click', function() {
                    $('#filterMonth').val("{{ $selectedMonth }}");
                    $('#filterPaidTo').val('');
                    table.search('').draw();
                    reloadMonthlyStats();
                });

                $('#btnExportPersonalExpenses').on('click', function() {
                    var params = {
                        month: $('#filterMonth').val(),
                        paid_to: $('#filterPaidTo').val(),
                        search: table.search()
                    };
                    window.location.href = "{{ route('expenses.personal.export') }}?" + $.param(params);
                });

                // =========================================================================
                // LINE ITEMS REPEATER LOGIC (ADD & EDIT)
                // =========================================================================
                var addLineCounter = 0;
                var editLineCounter = 0;

                function createLineItemRow(containerId, index, title, quantity, perRate, amount, notes, isPaid) {
                    title = title || '';
                    quantity = (quantity !== undefined && quantity !== null && quantity !== '') ? quantity : 1;
                    perRate = (perRate !== undefined && perRate !== null && perRate !== '') ? perRate : '';
                    amount = (amount !== undefined && amount !== null && amount !== '') ? amount : '';
                    notes = notes || '';
                    isPaid = !!isPaid;
                    var switchId = 'item_paid_' + containerId + '_' + index;

                    return '<tr class="line-item-row" data-index="' + index + '">' +
                        '<td class="text-center align-middle">' +
                            '<span class="badge bg-light text-muted border font-11 s-no">' + (index + 1) + '</span>' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" name="items[' + index + '][title]" class="form-control form-control-sm line-item-title" placeholder="e.g. Milk & Bread, Vegetables" value="' + $('<div>').text(title).html() + '" required>' +
                        '</td>' +
                        '<td>' +
                            '<input type="number" step="any" min="0.01" name="items[' + index + '][quantity]" class="form-control form-control-sm text-center line-item-qty" placeholder="1" value="' + quantity + '">' +
                        '</td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text bg-light p-1 font-11">₹</span>' +
                                '<input type="number" step="0.01" min="0" name="items[' + index + '][per_rate]" class="form-control form-control-sm text-end line-item-rate" placeholder="0.00" value="' + perRate + '">' +
                            '</div>' +
                        '</td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text bg-light p-1 font-11">₹</span>' +
                                '<input type="number" step="0.01" min="0" name="items[' + index + '][amount]" class="form-control form-control-sm text-end line-item-amt fw-bold text-danger" placeholder="0.00" value="' + amount + '" required>' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center align-middle">' +
                            '<div class="form-check form-switch d-inline-block mb-0">' +
                                '<input class="form-check-input line-item-paid-toggle" type="checkbox" name="items[' + index + '][is_paid]" value="1" id="' + switchId + '" ' + (isPaid ? 'checked' : '') + '>' +
                                '<label class="form-check-label font-11 ms-1 ' + (isPaid ? 'text-success fw-bold' : 'text-muted') + '" for="' + switchId + '">' + (isPaid ? 'Paid' : 'Pending') + '</label>' +
                            '</div>' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" name="items[' + index + '][notes]" class="form-control form-control-sm line-item-notes" placeholder="Short notes / details" value="' + $('<div>').text(notes).html() + '">' +
                        '</td>' +
                        '<td class="text-center align-middle">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-line-item" title="Remove Item"><i class="fas fa-trash-alt"></i></button>' +
                        '</td>' +
                    '</tr>';
                }

                $(document).on('change', '.line-item-paid-toggle', function() {
                    var $label = $(this).siblings('label');
                    if ($(this).is(':checked')) {
                        $label.text('Paid').removeClass('text-muted').addClass('text-success fw-bold');
                    } else {
                        $label.text('Pending').removeClass('text-success fw-bold').addClass('text-muted');
                    }
                });

                function renumberSerialNumbers(tbodyId) {
                    $('#' + tbodyId + ' tr').each(function(idx) {
                        $(this).find('.s-no').text(idx + 1);
                    });
                }

                function recalcLineItemsSum(tbodyId) {
                    var isEdit = tbodyId === 'editItemsBody';
                    var sumDisplayId = isEdit ? 'editItemsSumDisplay' : 'addItemsSumDisplay';
                    var mainAmtInputId = isEdit ? 'edit_amount' : 'add_amount';

                    var total = 0;
                    var hasRows = false;

                    $('#' + tbodyId + ' .line-item-amt').each(function() {
                        hasRows = true;
                        var val = parseFloat($(this).val());
                        if (!isNaN(val) && val > 0) {
                            total += val;
                        }
                    });

                    $('#' + sumDisplayId).text('₹' + total.toFixed(2));

                    if (hasRows && total > 0) {
                        $('#' + mainAmtInputId).val(total.toFixed(2));
                    }
                }

                // Auto calculate: when Qty or Rate changes -> Amount = Qty * Rate
                $(document).on('input', '.line-item-qty, .line-item-rate', function() {
                    var $tr = $(this).closest('tr');
                    var qty = parseFloat($tr.find('.line-item-qty').val());
                    var rate = parseFloat($tr.find('.line-item-rate').val());

                    if (isNaN(qty) || qty <= 0) qty = 1;

                    if (!isNaN(rate) && rate >= 0) {
                        var total = qty * rate;
                        $tr.find('.line-item-amt').val(total.toFixed(2));
                    }
                    var tbodyId = $tr.closest('tbody').attr('id');
                    recalcLineItemsSum(tbodyId);
                });

                // Auto calculate: when Amount changes directly -> Rate = Amount / Qty
                $(document).on('input', '.line-item-amt', function() {
                    var $tr = $(this).closest('tr');
                    var amt = parseFloat($(this).val());
                    var qty = parseFloat($tr.find('.line-item-qty').val());

                    if (isNaN(qty) || qty <= 0) qty = 1;

                    if (!isNaN(amt) && amt >= 0 && qty > 0) {
                        var rate = amt / qty;
                        $tr.find('.line-item-rate').val(rate.toFixed(2));
                    }
                    var tbodyId = $tr.closest('tbody').attr('id');
                    recalcLineItemsSum(tbodyId);
                });

                // Add row button (ADD MODAL)
                $('#btnAddLineItem').on('click', function() {
                    var rowHtml = createLineItemRow('addItemsBody', addLineCounter, '', 1, '', '', '');
                    $('#addItemsBody').append(rowHtml);
                    addLineCounter++;
                    renumberSerialNumbers('addItemsBody');
                    recalcLineItemsSum('addItemsBody');
                    $('#addItemsBody tr:last .line-item-title').focus();
                });

                // Remove row (ADD MODAL)
                $(document).on('click', '#addItemsBody .btn-remove-line-item', function() {
                    $(this).closest('tr').remove();
                    renumberSerialNumbers('addItemsBody');
                    recalcLineItemsSum('addItemsBody');
                });

                // Add row button (EDIT MODAL)
                $('#btnEditAddLineItem').on('click', function() {
                    var rowHtml = createLineItemRow('editItemsBody', editLineCounter, '', 1, '', '', '');
                    $('#editItemsBody').append(rowHtml);
                    editLineCounter++;
                    renumberSerialNumbers('editItemsBody');
                    recalcLineItemsSum('editItemsBody');
                    $('#editItemsBody tr:last .line-item-title').focus();
                });

                // Remove row (EDIT MODAL)
                $(document).on('click', '#editItemsBody .btn-remove-line-item', function() {
                    $(this).closest('tr').remove();
                    renumberSerialNumbers('editItemsBody');
                    recalcLineItemsSum('editItemsBody');
                });

                // Sync month dropdown on date change
                $('#add_expense_date').on('change', function() {
                    var val = $(this).val();
                    if (val) {
                        var d = new Date(val);
                        if (!isNaN(d.getTime())) {
                            var mName = d.toLocaleString('default', { month: 'long', year: 'numeric' });
                            $('#add_expense_month option').each(function() {
                                if ($(this).text() === mName) {
                                    $('#add_expense_month').val(mName);
                                }
                            });
                        }
                    }
                });

                // =========================================================================
                // 1. CREATE PERSONAL EXPENSE VIA AJAX
                // =========================================================================
                $('#addPersonalExpenseForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveExpense');
                    var $spinner = $('#saveExpenseSpinner');
                    var $errors = $('#addExpenseErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var title = $.trim($form.find('input[name="title"]').val());
                    if (!title || title.length < 2) {
                        $errors.html('Expense title must be at least 2 characters.').removeClass('d-none');
                        $form.find('input[name="title"]').addClass('is-invalid').focus();
                        return;
                    }

                    var amount = parseFloat($form.find('input[name="amount"]').val());
                    if (isNaN(amount) || amount <= 0) {
                        $errors.html('Expense amount must be greater than ₹0.00. Add line items or enter total.').removeClass('d-none');
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
                                $('#addPersonalExpenseModal').modal('hide');
                                $form[0].reset();
                                $('#addItemsBody').empty();
                                $('#addItemsSumDisplay').text('₹0.00');
                                addLineCounter = 0;
                                $form.find('.is-invalid').removeClass('is-invalid');
                                table.ajax.reload(null, false);
                                reloadMonthlyStats();
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
                $('#addPersonalExpenseForm, #editPersonalExpenseForm').on('input change', 'input, select, textarea', function() {
                    $(this).removeClass('is-invalid');
                });

                // =========================================================================
                // 2. OPEN EDIT MODAL VIA AJAX WITH LINE ITEMS
                // =========================================================================
                $(document).on('click', '.btn-edit-expense', function() {
                    var id = $(this).data('id');
                    $('#editExpenseErrors').addClass('d-none').html('');
                    $('#editItemsBody').empty();
                    editLineCounter = 0;

                    $.ajax({
                        url: "{{ url('/admin/expenses') }}/" + id + "/data",
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                var exp = response.expense;
                                $('#edit_expense_id').val(exp.id);
                                $('#edit_title').val(exp.title);
                                $('#edit_amount').val(parseFloat(exp.amount).toFixed(2));
                                if (exp.expense_date) {
                                    $('#edit_expense_date').val(exp.expense_date.substring(0, 10));
                                }
                                if (exp.expense_month) {
                                    $('#edit_expense_month').val(exp.expense_month);
                                }
                                $('#edit_paid_to').val(exp.paid_to);
                                $('#edit_notes').val(exp.notes);

                                // Populate line items
                                if (exp.items && Array.isArray(exp.items) && exp.items.length > 0) {
                                    exp.items.forEach(function(item) {
                                        var rowHtml = createLineItemRow(
                                            'editItemsBody',
                                            editLineCounter,
                                            item.title,
                                            item.quantity !== undefined ? item.quantity : 1,
                                            item.per_rate !== undefined ? item.per_rate : '',
                                            item.amount,
                                            item.notes,
                                            item.is_paid
                                        );
                                        $('#editItemsBody').append(rowHtml);
                                        editLineCounter++;
                                    });
                                    renumberSerialNumbers('editItemsBody');
                                    recalcLineItemsSum('editItemsBody');
                                } else {
                                    $('#editItemsSumDisplay').text('₹0.00');
                                }

                                $('#editPersonalExpenseModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to fetch expense details' });
                        }
                    });
                });

                // =========================================================================
                // 3. UPDATE EXPENSE VIA AJAX
                // =========================================================================
                $('#editPersonalExpenseForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var id = $('#edit_expense_id').val();
                    var $btn = $('#btnUpdateExpense');
                    var $spinner = $('#updateExpenseSpinner');
                    var $errors = $('#editExpenseErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var title = $.trim($form.find('#edit_title').val());
                    if (!title || title.length < 2) {
                        $errors.html('Expense title must be at least 2 characters.').removeClass('d-none');
                        $form.find('#edit_title').addClass('is-invalid').focus();
                        return;
                    }

                    var amount = parseFloat($form.find('#edit_amount').val());
                    if (isNaN(amount) || amount <= 0) {
                        $errors.html('Expense amount must be greater than ₹0.00.').removeClass('d-none');
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
                                $('#editPersonalExpenseModal').modal('hide');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                table.ajax.reload(null, false);
                                reloadMonthlyStats();
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

                // =========================================================================
                // 4. VIEW & MANAGE LINE ITEMS BREAKDOWN MODAL & PAYMENT STATUS
                // =========================================================================
                window.currentViewingExpense = null;

                function renderExpenseItemsModal(exp) {
                    window.currentViewingExpense = exp;
                    $('#view_exp_title').text(exp.title);
                    $('#view_exp_month').text(exp.expense_month || '-');
                    $('#view_exp_date').text(exp.expense_date ? exp.expense_date.substring(0, 10) : '-');
                    $('#view_exp_paid_to').text(exp.paid_to || '-');

                    var totalAmt = parseFloat(exp.amount) || 0;
                    var paidAmt = parseFloat(exp.paid_amount) || 0;
                    var dueAmt = Math.max(0, totalAmt - paidAmt);
                    var pct = totalAmt > 0 ? Math.min(100, Math.round((paidAmt / totalAmt) * 100)) : 0;

                    $('#view_exp_total').text('₹' + totalAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#view_exp_paid_amt').text('₹' + paidAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#view_exp_due_amt').text('₹' + dueAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#view_exp_footer_total').text('₹' + totalAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#view_exp_footer_paid').text('Paid: ₹' + paidAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#view_exp_footer_due').text('Due: ₹' + dueAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#view_exp_progress_pct').text(pct + '% (' + (dueAmt <= 0 ? 'Fully Cleared' : '₹' + dueAmt.toFixed(2) + ' pending') + ')');

                    var statusBadge = '';
                    if (exp.payment_status === 'paid' || (totalAmt > 0 && dueAmt <= 0)) {
                        statusBadge = '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i> Fully Paid</span>';
                    } else if (paidAmt > 0) {
                        statusBadge = '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-adjust me-1"></i> Partially Paid</span>';
                    } else {
                        statusBadge = '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-clock me-1"></i> Pending Payment</span>';
                    }
                    $('#view_exp_status_badge').html(statusBadge);

                    var rows = '';
                    if (exp.items && Array.isArray(exp.items) && exp.items.length > 0) {
                        exp.items.forEach(function(item, idx) {
                            var qty = item.quantity !== undefined && item.quantity !== null ? item.quantity : 1;
                            var rate = (item.per_rate !== undefined && item.per_rate !== null && item.per_rate !== '') ? parseFloat(item.per_rate).toFixed(2) : '-';
                            var amt = item.amount !== undefined && item.amount !== null ? parseFloat(item.amount).toFixed(2) : '0.00';
                            var isPaid = !!item.is_paid;

                            var statusCol = isPaid
                                ? '<span class="badge bg-success-subtle text-success border border-success font-11 px-2 py-1"><i class="fas fa-check-circle me-1"></i> Paid</span>'
                                : '<span class="badge bg-danger-subtle text-danger border border-danger font-11 px-2 py-1"><i class="fas fa-clock me-1"></i> Pending</span>';

                            var actionBtn = isPaid
                                ? '<button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-line-paid px-2 py-1 font-11" data-index="' + idx + '" data-id="' + exp.id + '" title="Mark as Pending / Unpaid"><i class="fas fa-undo me-1"></i> Unpaid</button>'
                                : '<button type="button" class="btn btn-sm btn-success btn-toggle-line-paid px-2 py-1 font-11 fw-bold" data-index="' + idx + '" data-id="' + exp.id + '" title="Mark as Paid"><i class="fas fa-check me-1"></i> Pay</button>';

                            var notesHtml = item.notes ? '<div class="text-muted font-11 mt-1">' + $('<div>').text(item.notes).html() + '</div>' : '';

                            rows += '<tr class="' + (isPaid ? 'table-success-subtle' : '') + '">' +
                                '<td class="text-center text-muted font-11 fw-bold">' + (idx + 1) + '</td>' +
                                '<td><strong class="text-dark font-13">' + $('<div>').text(item.title).html() + '</strong>' + notesHtml + '</td>' +
                                '<td class="text-center font-12">' + qty + '</td>' +
                                '<td class="text-end font-12">' + (rate !== '-' ? '₹' + rate : '-') + '</td>' +
                                '<td class="text-end fw-bold ' + (isPaid ? 'text-success' : 'text-danger') + ' font-13">₹' + parseFloat(amt).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '</td>' +
                                '<td class="text-center">' + statusCol + '</td>' +
                                '<td class="text-center">' + actionBtn + '</td>' +
                            '</tr>';
                        });
                    } else {
                        rows = '<tr><td colspan="7" class="text-center text-muted py-3">No separate line items logged for this expense. (Recorded as single amount).</td></tr>';
                    }
                    $('#view_exp_items_tbody').html(rows);

                    if (exp.notes) {
                        $('#view_exp_notes_text').text(exp.notes);
                        $('#view_exp_notes_box').removeClass('d-none');
                    } else {
                        $('#view_exp_notes_box').addClass('d-none');
                    }
                }

                $(document).on('click', '.btn-view-items', function() {
                    var id = $(this).data('id');

                    $.ajax({
                        url: "{{ url('/admin/expenses') }}/" + id + "/data",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.expense) {
                                renderExpenseItemsModal(res.expense);
                                $('#viewExpenseItemsModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to load expense items breakdown.' });
                        }
                    });
                });

                // Toggle individual line item paid/unpaid in View Modal
                $(document).on('click', '.btn-toggle-line-paid', function() {
                    var $btn = $(this);
                    var expId = $btn.data('id');
                    var index = $btn.data('index');

                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                    $.ajax({
                        url: "{{ url('/admin/expenses') }}/" + expId + "/toggle-item-paid",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            item_index: index,
                            action: 'toggle'
                        },
                        success: function(res) {
                            if (res.success && res.expense) {
                                Toast.fire({ icon: 'success', title: res.message });
                                renderExpenseItemsModal(res.expense);
                                table.ajax.reload(null, false);
                                if (res.stats) {
                                    updateStatCards(res.stats);
                                }
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            Toast.fire({ icon: 'error', title: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error updating line item payment status.' });
                        }
                    });
                });

                // Mark all line items in view modal as Paid or Unpaid
                $(document).on('click', '#btnModalMarkAllPaid, #btnModalMarkAllUnpaid', function() {
                    if (!window.currentViewingExpense) return;
                    var expId = window.currentViewingExpense.id;
                    var action = $(this).attr('id') === 'btnModalMarkAllPaid' ? 'all_paid' : 'all_unpaid';

                    $.ajax({
                        url: "{{ url('/admin/expenses') }}/" + expId + "/toggle-item-paid",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            action: action
                        },
                        success: function(res) {
                            if (res.success && res.expense) {
                                Toast.fire({ icon: 'success', title: res.message });
                                renderExpenseItemsModal(res.expense);
                                table.ajax.reload(null, false);
                                if (res.stats) {
                                    updateStatCards(res.stats);
                                }
                            }
                        },
                        error: function(xhr) {
                            Toast.fire({ icon: 'error', title: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error updating expense status.' });
                        }
                    });
                });

                // Quick Pay / Unpay from DataTable Row
                $(document).on('click', '.btn-mark-expense-paid, .btn-mark-expense-unpaid', function() {
                    var expId = $(this).data('id');
                    var title = $(this).data('title') || 'this expense';
                    var isMarkPaid = $(this).hasClass('btn-mark-expense-paid');
                    var status = isMarkPaid ? 'paid' : 'unpaid';

                    $.ajax({
                        url: "{{ url('/admin/expenses') }}/" + expId + "/mark-paid",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            status: status
                        },
                        success: function(res) {
                            if (res.success) {
                                Toast.fire({ icon: 'success', title: res.message });
                                table.ajax.reload(null, false);
                                if (res.stats) {
                                    updateStatCards(res.stats);
                                }
                            }
                        },
                        error: function(xhr) {
                            Toast.fire({ icon: 'error', title: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error updating expense payment.' });
                        }
                    });
                });

                // =========================================================================
                // 5. DELETE EXPENSE VIA AJAX
                // =========================================================================
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
                                        reloadMonthlyStats();
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
                // 6. BULK SELECTION, BULK EDIT & BULK DELETE
                // =========================================================================
                function updateBulkBar() {
                    var checkedBoxes = $('#personalExpensesTable tbody .bulk-select-row:checked');
                    var count = checkedBoxes.length;
                    var totalBoxes = $('#personalExpensesTable tbody .bulk-select-row').length;

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
                    $('#personalExpensesTable tbody .bulk-select-row').prop('checked', isChecked);
                    updateBulkBar();
                });

                $(document).on('change', '#personalExpensesTable tbody .bulk-select-row', function() {
                    updateBulkBar();
                });

                table.on('draw', function() {
                    updateBulkBar();
                });

                $(document).on('click', '.btn-bulk-deselect', function() {
                    $('#personalExpensesTable tbody .bulk-select-row').prop('checked', false);
                    $('#bulkSelectAll').prop('checked', false).prop('indeterminate', false);
                    updateBulkBar();
                });

                // Bulk Delete
                $(document).on('click', '.btn-bulk-delete', function() {
                    var ids = [];
                    $('#personalExpensesTable tbody .bulk-select-row:checked').each(function() {
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
                                        reloadMonthlyStats();
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
                    var count = $('#personalExpensesTable tbody .bulk-select-row:checked').length;
                    if (count === 0) {
                        Toast.fire({ icon: 'warning', title: 'No expenses selected.' });
                        return;
                    }
                    $('#bulkEditCountBadge').text(count + ' selected');
                    $('#bulkEditPersonalExpenseForm')[0].reset();
                    $('#bulkEditErrors').addClass('d-none').html('');
                    $('#bulkEditPersonalExpenseModal').modal('show');
                });

                $('#bulkEditPersonalExpenseForm').on('submit', function(e) {
                    e.preventDefault();
                    var ids = [];
                    $('#personalExpensesTable tbody .bulk-select-row:checked').each(function() {
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
                                $('#bulkEditPersonalExpenseModal').modal('hide');
                                Toast.fire({ icon: 'success', title: res.message });
                                $('#bulkSelectAll').prop('checked', false);
                                table.ajax.reload(null, false);
                                reloadMonthlyStats();
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
