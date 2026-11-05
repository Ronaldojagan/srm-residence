@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .stat-pill-card {
                background: #ffffff;
                border-radius: 14px;
                padding: 1.25rem 1.5rem;
                border: 1px solid var(--res-border);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .stat-pill-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
            }
            .nav-tabs-billing .nav-link {
                font-weight: 600;
                font-size: 14px;
                color: #64748b;
                padding: 10px 20px;
                border: none;
                border-bottom: 2px solid transparent;
                border-radius: 0;
            }
            .nav-tabs-billing .nav-link.active {
                color: var(--res-primary);
                border-bottom: 2px solid var(--res-primary);
                background: transparent;
            }
            /* Printable Invoice Styling */
            @media print {
                body * {
                    visibility: hidden !important;
                }
                #printableInvoiceArea, #printableInvoiceArea * {
                    visibility: visible !important;
                }
                #printableInvoiceArea {
                    position: absolute !important;
                    left: 0 !important;
                    top: 0 !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 20px !important;
                }
            }
            .invoice-box {
                background: #fff;
                padding: 30px;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
            }
            .invoice-status-stamp {
                display: inline-block;
                padding: 6px 14px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 1px;
                border-radius: 8px;
                font-size: 13px;
            }
            .invoice-status-stamp.paid {
                background: #ecfdf5;
                color: #059669;
                border: 2px solid #059669;
            }
            .invoice-status-stamp.unpaid {
                background: #fffbeb;
                color: #d97706;
                border: 2px solid #d97706;
            }
            .invoice-status-stamp.overdue {
                background: #fef2f2;
                color: #dc2626;
                border: 2px solid #dc2626;
            }
            .invoice-status-stamp.partial {
                background: #eff6ff;
                color: #2563eb;
                border: 2px solid #2563eb;
            }
        </style>
    @endpush

    <!-- Header Row -->
    <div class="row justify-content-between align-items-center mt-3 mb-2">
        <div class="col-md-6">
            <h4 class="page-title-main page">Bills & Payments</h4>
            <p class="text-muted small mb-0">Track tenant rent invoices, utilities, payment collections, and official receipts.</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-outline-primary waves-effect me-2" data-bs-toggle="modal" data-bs-target="#quickMonthlyModal">
                <i class="fas fa-bolt me-1"></i> Auto-Generate Rent Bills
            </button>
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#createBillModal">
                <i class="fas fa-plus-circle me-1"></i> Create Invoice
            </button>
        </div>
    </div>

    <!-- Overview Metric Cards -->
    <div class="row g-3 mt-1">
        <div class="col-md-3">
            <div class="stat-pill-card border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Total Invoiced</span>
                    <span class="badge bg-primary-subtle text-primary font-12"><i class="fas fa-file-invoice"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-dark" id="statTotalBilled">₹{{ number_format($stats['totalBilled']) }}</h3>
                <small class="text-muted">This Month: ₹{{ number_format($stats['thisMonthBilled']) }}</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-pill-card border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Total Collected</span>
                    <span class="badge bg-success-subtle text-success font-12"><i class="fas fa-check-double"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-success" id="statTotalCollected">₹{{ number_format($stats['totalCollected']) }}</h3>
                <small class="text-muted">Rate: <span class="fw-bold text-success">{{ $stats['collectionRate'] }}%</span></small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-pill-card border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Outstanding Dues</span>
                    <span class="badge bg-warning-subtle text-warning font-12"><i class="fas fa-clock"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-warning" id="statTotalDue">₹{{ number_format($stats['totalDue']) }}</h3>
                <small class="text-muted">Pending balance to collect</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-pill-card border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Overdue Invoices</span>
                    <span class="badge bg-danger-subtle text-danger font-12"><i class="fas fa-exclamation-circle"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-danger" id="statOverdueCount">{{ $stats['overdueCount'] }}</h3>
                <small class="text-muted">Bills past due date</small>
            </div>
        </div>
    </div>

    <!-- Main Navigation Card with Tabs -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-shadow">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs nav-tabs-billing px-3" id="billingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bills-tab" data-bs-toggle="tab" data-bs-target="#billsTabPane" type="button" role="tab" aria-controls="billsTabPane" aria-selected="true">
                                <i class="fas fa-file-invoice me-1"></i> Invoices & Bills
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#paymentsTabPane" type="button" role="tab" aria-controls="paymentsTabPane" aria-selected="false">
                                <i class="fas fa-receipt me-1"></i> Payment Records & Receipts
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content" id="billingTabContent">

                        <!-- TAB 1: INVOICES & BILLS -->
                        <div class="tab-pane fade show active" id="billsTabPane" role="tabpanel" aria-labelledby="bills-tab">
                            <!-- Filters for Bills -->
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Filter by Tenant</label>
                                    <select id="filterBillTenant" class="form-select filter-select">
                                        <option value="">All Tenants</option>
                                        @foreach($activeTenants as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} (Room {{ $t->room ? $t->room->room_number : $t->room_number }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted mb-1">Bill Type</label>
                                    <select id="filterBillType" class="form-select filter-select">
                                        <option value="">All Types</option>
                                        <option value="Monthly Rent">Monthly Rent</option>
                                        <option value="Electricity">Electricity</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Security Deposit">Security Deposit</option>
                                        <option value="Mess / Food">Mess / Food</option>
                                        <option value="Water">Water</option>
                                        <option value="Penalty / Fine">Penalty / Fine</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted mb-1">Status</label>
                                    <select id="filterBillStatus" class="form-select filter-select">
                                        <option value="">All Statuses</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="partially_paid">Partially Paid</option>
                                        <option value="paid">Fully Paid</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Billing Month</label>
                                    <input type="text" id="filterBillMonth" class="form-control filter-select" placeholder="e.g. November 2026">
                                </div>
                                <div class="col-md-2 text-end pt-3">
                                    <button type="button" id="btnResetBillFilters" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                </div>
                            </div>

                            <!-- Bills DataTable -->
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table job-table w-100" id="billsTable">
                                    <thead class="table-theme text-white">
                                        <tr>
                                            <th width="4%">#</th>
                                            <th width="14%">Invoice No</th>
                                            <th width="20%">Tenant & Room</th>
                                            <th width="15%">Type & Month</th>
                                            <th width="10%">Amount</th>
                                            <th width="16%">Paid / Due Progress</th>
                                            <th width="11%">Due Date</th>
                                            <th width="10%">Status</th>
                                            <th width="14%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: PAYMENT RECORDS -->
                        <div class="tab-pane fade" id="paymentsTabPane" role="tabpanel" aria-labelledby="payments-tab">
                            <!-- Filters for Payments -->
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Filter by Tenant</label>
                                    <select id="filterPayTenant" class="form-select filter-select">
                                        <option value="">All Tenants</option>
                                        @foreach($activeTenants as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} (Room {{ $t->room ? $t->room->room_number : $t->room_number }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Payment Method</label>
                                    <select id="filterPayMethod" class="form-select filter-select">
                                        <option value="">All Methods</option>
                                        <option value="Cash">Cash</option>
                                        <option value="UPI / GPay">UPI / GPay</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Credit / Debit Card">Credit / Debit Card</option>
                                        <option value="Cheque">Cheque</option>
                                    </select>
                                </div>
                                <div class="col-md-5 text-end pt-3">
                                    <button type="button" id="btnResetPayFilters" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-undo me-1"></i> Reset Filters
                                    </button>
                                </div>
                            </div>

                            <!-- Payments DataTable -->
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table job-table w-100" id="paymentsTable">
                                    <thead class="table-theme text-white">
                                        <tr>
                                            <th width="4%">#</th>
                                            <th width="16%">Receipt No</th>
                                            <th width="16%">Invoice Reference</th>
                                            <th width="20%">Tenant & Room</th>
                                            <th width="12%">Amount Paid</th>
                                            <th width="12%">Payment Date</th>
                                            <th width="12%">Method</th>
                                            <th width="8%" class="text-center">Action</th>
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
        </div>
    </div>

    <!-- =========================================================================
         MODALS
         ========================================================================= -->

    <!-- 1. CREATE BILL MODAL (AJAX) -->
    <div class="modal fade" id="createBillModal" tabindex="-1" aria-labelledby="createBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="createBillModalLabel">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Create Tenant Invoice
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createBillForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div id="createBillErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Select Tenant <span class="text-danger">*</span></label>
                                <select name="tenant_id" id="bill_tenant_id" class="form-select" required>
                                    <option value="" disabled selected>Select tenant...</option>
                                    @foreach($activeTenants as $t)
                                        <option value="{{ $t->id }}" 
                                            data-rent="{{ $t->monthly_rent }}" 
                                            data-room="{{ $t->room ? $t->room->room_number : $t->room_number }}">
                                            {{ $t->name }} (Room {{ $t->room ? $t->room->room_number : $t->room_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bill Type <span class="text-danger">*</span></label>
                                <select name="bill_type" id="bill_type_select" class="form-select" required>
                                    <option value="Monthly Rent" selected>Monthly Rent</option>
                                    <option value="Electricity">Electricity Bill</option>
                                    <option value="Maintenance">Maintenance Charge</option>
                                    <option value="Security Deposit">Security Deposit</option>
                                    <option value="Mess / Food">Mess / Food Fee</option>
                                    <option value="Water">Water Charges</option>
                                    <option value="Penalty / Fine">Penalty / Late Fine</option>
                                    <option value="Other">Other Charges</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Billing Month <span class="text-danger">*</span></label>
                                <input type="text" name="billing_month" class="form-control" value="{{ $currentMonth }}" placeholder="e.g. November 2026" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+5 days')) }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Invoice Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">₹</span>
                                    <input type="number" step="0.01" name="amount" id="bill_amount_input" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Notes / Description (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. November monthly room rent with laundry"></textarea>
                            </div>

                            <!-- Immediate Payment Option -->
                            <div class="col-12 mt-3 pt-3 border-top">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="immediate_payment_check" name="immediate_payment" value="1">
                                    <label class="form-check-label fw-semibold" for="immediate_payment_check">
                                        Record payment immediately (paid right now)
                                    </label>
                                </div>

                                <div id="immediate_payment_fields" class="row g-2 mt-1 d-none bg-light p-3 rounded">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Amount Paid (₹)</label>
                                        <input type="number" step="0.01" name="payment_amount" id="immediate_pay_amt" class="form-control form-control-sm" placeholder="Amount">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Payment Method</label>
                                        <select name="payment_method" class="form-select form-select-sm">
                                            <option value="Cash" selected>Cash</option>
                                            <option value="UPI / GPay">UPI / GPay</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Credit / Debit Card">Credit / Debit Card</option>
                                            <option value="Cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Transaction Ref / ID</label>
                                        <input type="text" name="transaction_id" class="form-control form-control-sm" placeholder="Optional ref">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white waves-effect waves-light" id="btnSaveBill">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="saveBillSpinner"></span>
                            Generate Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. QUICK GENERATE MONTHLY RENT MODAL -->
    <div class="modal fade" id="quickMonthlyModal" tabindex="-1" aria-labelledby="quickMonthlyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="quickMonthlyModalLabel">
                        <i class="fas fa-bolt me-1"></i> Auto-Generate Monthly Rent Bills
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickMonthlyForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div id="quickMonthlyErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="fas fa-info-circle me-1"></i> This will automatically generate a monthly rent invoice for all <strong>{{ $activeTenants->count() }} active tenant(s)</strong> based on their allocated room rent. Duplicate invoices will be skipped.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Billing Month <span class="text-danger">*</span></label>
                            <input type="text" name="billing_month" class="form-control" value="{{ $currentMonth }}" placeholder="e.g. November 2026" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bill Date <span class="text-danger">*</span></label>
                                <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+5 days')) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light" id="btnQuickGenerate">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickSpinner"></span>
                            Generate For All Active Tenants
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. RECORD PAYMENT MODAL -->
    <div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="recordPaymentModalLabel">
                        <i class="fas fa-credit-card me-1"></i> Record Payment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="recordPaymentForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="bill_id" id="pay_bill_id">
                    <div class="modal-body p-4">
                        <div id="recordPayErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="p-3 bg-light rounded mb-3 border">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Invoice:</span>
                                <span class="fw-bold text-dark font-13" id="pay_bill_no">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Tenant:</span>
                                <span class="fw-bold text-dark" id="pay_tenant_name">-</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Pending Due:</span>
                                <span class="fw-bold text-danger font-14" id="pay_due_display">₹0.00</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" step="0.01" name="amount" id="pay_amount_input" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="Cash" selected>Cash</option>
                                    <option value="UPI / GPay">UPI / GPay</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Credit / Debit Card">Credit / Debit Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transaction Reference / UTR (Optional)</label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. UPI Ref, Check No, Bank Ref">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Receipt / Screenshot (Optional)</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid in full via GPay"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light" id="btnSavePayment">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="savePaySpinner"></span>
                            Confirm & Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. VIEW & PRINT INVOICE MODAL -->
    <div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title text-white" id="viewInvoiceModalLabel">
                        <i class="fas fa-file-invoice me-1"></i> Official Invoice Preview
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="printableInvoiceArea">
                    <div class="invoice-box">
                        <!-- Invoice Header -->
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-primary text-white p-2 rounded me-2 font-16"><i class="fas fa-building"></i></span>
                                    <h4 class="fw-bold text-dark mb-0">SRM RESIDENCE</h4>
                                </div>
                                <div class="text-muted small">Student & Living Hub</div>
                                <div class="text-muted small">SRM Campus Road, Chennai, Tamil Nadu</div>
                                <div class="text-muted small">Contact: +91 98765 43210 | support@srmresidence.com</div>
                            </div>
                            <div class="text-end">
                                <h3 class="fw-bold text-primary mb-1">INVOICE</h3>
                                <div class="fw-bold font-14" id="inv_no">INV-XXXX</div>
                                <div class="mt-2" id="inv_status_stamp"></div>
                            </div>
                        </div>

                        <!-- Bill Details Grid -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Billed To</div>
                                <h5 class="fw-bold text-dark mb-1" id="inv_tenant_name">Tenant Name</h5>
                                <div class="text-muted small" id="inv_tenant_room">Room Number</div>
                                <div class="text-muted small" id="inv_tenant_phone">Phone Number</div>
                                <div class="text-muted small" id="inv_tenant_email">Email</div>
                            </div>
                            <div class="col-6 text-end">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Invoice Info</div>
                                <div class="small"><strong>Billing Period:</strong> <span id="inv_period">-</span></div>
                                <div class="small"><strong>Invoice Date:</strong> <span id="inv_date">-</span></div>
                                <div class="small"><strong>Payment Due Date:</strong> <span id="inv_due_date" class="text-danger fw-bold">-</span></div>
                            </div>
                        </div>

                        <!-- Invoice Line Items Table -->
                        <table class="table table-bordered mb-4">
                            <thead class="bg-light">
                                <tr>
                                    <th>Description / Item</th>
                                    <th>Billing Month</th>
                                    <th class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong id="inv_item_title">Monthly Rent</strong>
                                        <div class="text-muted small" id="inv_item_desc">Room accommodation and resident amenities</div>
                                    </td>
                                    <td id="inv_item_month">-</td>
                                    <td class="text-end fw-bold font-14" id="inv_item_amount">₹0.00</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Financial Summary -->
                        <div class="row justify-content-end mb-4">
                            <div class="col-md-5">
                                <div class="p-3 bg-light rounded border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Billed:</span>
                                        <span class="fw-bold" id="inv_summary_total">₹0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>Amount Paid:</span>
                                        <span class="fw-bold" id="inv_summary_paid">₹0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between pt-2 border-top text-danger">
                                        <span class="fw-bold">Balance Due:</span>
                                        <span class="fw-bold font-16" id="inv_summary_due">₹0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment History for this Invoice -->
                        <div class="mb-3" id="inv_payments_history_section">
                            <h6 class="fw-bold text-dark mb-2"><i class="fas fa-history me-1"></i> Payment Transaction History</h6>
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Transaction Ref</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="inv_payments_list">
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer notes -->
                        <div class="border-top pt-3 text-muted small text-center">
                            Thank you for staying at SRM Residence! For any billing queries, please contact the hostel admin office.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. VIEW & PRINT RECEIPT MODAL -->
    <div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-labelledby="viewReceiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="viewReceiptModalLabel">
                        <i class="fas fa-receipt me-1"></i> Payment Receipt
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="printableReceiptArea">
                    <div class="p-4 border rounded bg-white shadow-sm">
                        <div class="text-center pb-3 border-bottom mb-3">
                            <h5 class="fw-bold text-dark mb-1">SRM RESIDENCE</h5>
                            <span class="badge bg-success-subtle text-success border border-success font-12 px-2 py-1">OFFICIAL PAYMENT RECEIPT</span>
                            <div class="text-muted small mt-1 font-12" id="rcp_no">RCP-XXXX</div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Payment Date:</span>
                                <span class="fw-semibold text-dark" id="rcp_date">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Received From:</span>
                                <span class="fw-bold text-dark" id="rcp_tenant">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Room:</span>
                                <span class="fw-semibold text-dark" id="rcp_room">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Against Invoice:</span>
                                <span class="fw-semibold text-primary" id="rcp_bill">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Payment Mode:</span>
                                <span class="badge bg-secondary text-white" id="rcp_method">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="rcp_tx_row">
                                <span class="text-muted">Reference / UTR:</span>
                                <span class="text-dark font-12" id="rcp_tx">-</span>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded text-center my-3 border">
                            <div class="text-muted small text-uppercase fw-bold">Amount Received</div>
                            <h2 class="fw-bold text-success my-1" id="rcp_amount">₹0.00</h2>
                            <div class="text-muted font-11">Authorized payment acknowledgment</div>
                        </div>

                        <div class="text-center text-muted font-11 pt-2 border-top">
                            Generated by SRM Residence Management System.<br>
                            This is a computer-generated digital receipt.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success waves-effect waves-light" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. EDIT BILL MODAL (AJAX) -->
    <div class="modal fade" id="editBillModal" tabindex="-1" aria-labelledby="editBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="editBillModalLabel">
                        <i class="fas fa-edit me-1"></i> Edit Invoice
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBillForm">
                    @csrf
                    <input type="hidden" id="edit_bill_id" name="id">
                    <div class="modal-body p-4">
                        <div id="editBillErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Bill Type <span class="text-danger">*</span></label>
                            <select name="bill_type" id="edit_bill_type" class="form-select" required>
                                <option value="Monthly Rent">Monthly Rent</option>
                                <option value="Electricity">Electricity Bill</option>
                                <option value="Maintenance">Maintenance Charge</option>
                                <option value="Security Deposit">Security Deposit</option>
                                <option value="Mess / Food">Mess / Food Fee</option>
                                <option value="Water">Water Charges</option>
                                <option value="Penalty / Fine">Penalty / Late Fine</option>
                                <option value="Other">Other Charges</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Billing Month <span class="text-danger">*</span></label>
                            <input type="text" name="billing_month" id="edit_billing_month" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="edit_due_date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Total Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white waves-effect waves-light" id="btnUpdateBill">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="updateBillSpinner"></span>
                            Update Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

        <script type="text/javascript">
            $(function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // =========================================================================
                // 1. BILLS DATATABLE
                // =========================================================================
                var billsTable = $('#billsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('/admin/billing/bills-data') }}",
                        data: function(d) {
                            d.tenant_id = $('#filterBillTenant').val();
                            d.bill_type = $('#filterBillType').val();
                            d.status = $('#filterBillStatus').val();
                            d.billing_month = $('#filterBillMonth').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'bill_number_badge', name: 'bill_number' },
                        { data: 'tenant_room', name: 'tenant_id' },
                        { data: 'bill_type_badge', name: 'bill_type' },
                        { data: 'amount_display', name: 'amount' },
                        { data: 'payment_progress', name: 'paid_amount', orderable: false, searchable: false },
                        { data: 'due_date_formatted', name: 'due_date' },
                        { data: 'status_badge', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[1, 'desc']],
                    language: {
                        search: "",
                        searchPlaceholder: "Search invoices...",
                        lengthMenu: "Show _MENU_ entries",
                        zeroRecords: "No matching invoices found",
                        info: "Showing _START_ to _END_ of _TOTAL_ invoices",
                        infoEmpty: "Showing 0 to 0 of 0 invoices",
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    }
                });

                // Trigger reload on filter changes
                $('#filterBillTenant, #filterBillType, #filterBillStatus').on('change', function() {
                    billsTable.ajax.reload();
                });
                $('#filterBillMonth').on('keyup change', function() {
                    billsTable.ajax.reload();
                });
                $('#btnResetBillFilters').on('click', function() {
                    $('#filterBillTenant').val('');
                    $('#filterBillType').val('');
                    $('#filterBillStatus').val('');
                    $('#filterBillMonth').val('');
                    billsTable.ajax.reload();
                });

                // =========================================================================
                // 2. PAYMENTS DATATABLE
                // =========================================================================
                var paymentsTable = $('#paymentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('/admin/billing/payments-data') }}",
                        data: function(d) {
                            d.tenant_id = $('#filterPayTenant').val();
                            d.payment_method = $('#filterPayMethod').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'payment_number_badge', name: 'payment_number' },
                        { data: 'bill_reference', name: 'bill_id' },
                        { data: 'tenant_info', name: 'tenant_id' },
                        { data: 'amount_display', name: 'amount' },
                        { data: 'payment_date_formatted', name: 'payment_date' },
                        { data: 'payment_method_badge', name: 'payment_method' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[1, 'desc']],
                    language: {
                        search: "",
                        searchPlaceholder: "Search payment records...",
                        lengthMenu: "Show _MENU_ entries",
                        zeroRecords: "No matching payments found",
                        info: "Showing _START_ to _END_ of _TOTAL_ payments",
                        infoEmpty: "Showing 0 to 0 of 0 payments",
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    }
                });

                $('#filterPayTenant, #filterPayMethod').on('change', function() {
                    paymentsTable.ajax.reload();
                });
                $('#btnResetPayFilters').on('click', function() {
                    $('#filterPayTenant').val('');
                    $('#filterPayMethod').val('');
                    paymentsTable.ajax.reload();
                });

                // Reload active table on tab switch
                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    if (e.target.id === 'payments-tab') {
                        paymentsTable.columns.adjust().draw();
                    } else if (e.target.id === 'bills-tab') {
                        billsTable.columns.adjust().draw();
                    }
                });

                // =========================================================================
                // 3. AUTO-FILL AMOUNT ON TENANT SELECTION
                // =========================================================================
                $('#bill_tenant_id').on('change', function() {
                    var selected = $(this).find(':selected');
                    var rent = selected.data('rent');
                    var billType = $('#bill_type_select').val();

                    if (billType === 'Monthly Rent' && rent) {
                        $('#bill_amount_input').val(rent);
                        $('#immediate_pay_amt').val(rent);
                    }
                });

                $('#bill_type_select').on('change', function() {
                    if ($(this).val() === 'Monthly Rent') {
                        var rent = $('#bill_tenant_id').find(':selected').data('rent');
                        if (rent) {
                            $('#bill_amount_input').val(rent);
                            $('#immediate_pay_amt').val(rent);
                        }
                    }
                });

                // Toggle immediate payment fields
                $('#immediate_payment_check').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#immediate_payment_fields').removeClass('d-none');
                        var amt = $('#bill_amount_input').val();
                        if (amt) {
                            $('#immediate_pay_amt').val(amt);
                        }
                    } else {
                        $('#immediate_payment_fields').addClass('d-none');
                    }
                });

                // =========================================================================
                // 4. CREATE BILL AJAX SUBMISSION
                // =========================================================================
                $('#createBillForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveBill');
                    var $spinner = $('#saveBillSpinner');
                    var $errors = $('#createBillErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/billing/bills') }}",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (res.success) {
                                $('#createBillModal').modal('hide');
                                $form[0].reset();
                                $('#immediate_payment_fields').addClass('d-none');
                                billsTable.ajax.reload(null, false);
                                paymentsTable.ajax.reload(null, false);
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var msg = 'An error occurred while creating the invoice.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            $errors.removeClass('d-none').html(msg);
                        }
                    });
                });

                // =========================================================================
                // 5. QUICK AUTO-GENERATE MONTHLY RENT
                // =========================================================================
                $('#quickMonthlyForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnQuickGenerate');
                    var $spinner = $('#quickSpinner');
                    var $errors = $('#quickMonthlyErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/billing/bills/quick-monthly') }}",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (res.success) {
                                $('#quickMonthlyModal').modal('hide');
                                billsTable.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Rent Bills Generated!',
                                    text: res.message,
                                    confirmButtonColor: '#0ea1b1'
                                });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to generate rent bills.';
                            $errors.removeClass('d-none').html(msg);
                        }
                    });
                });

                // =========================================================================
                // 6. RECORD PAYMENT MODAL TRIGGER & SUBMIT
                // =========================================================================
                $(document).on('click', '.btn-record-payment', function() {
                    var billId = $(this).data('id');
                    var billNo = $(this).data('bill');
                    var tenant = $(this).data('tenant');
                    var due = $(this).data('due');

                    $('#pay_bill_id').val(billId);
                    $('#pay_bill_no').text(billNo);
                    $('#pay_tenant_name').text(tenant);
                    $('#pay_due_display').text('₹' + parseFloat(due).toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#pay_amount_input').val(due);

                    $('#recordPayErrors').addClass('d-none').html('');
                    $('#recordPaymentModal').modal('show');
                });

                $('#recordPaymentForm').on('submit', function(e) {
                    e.preventDefault();
                    var formData = new FormData(this);
                    var $btn = $('#btnSavePayment');
                    var $spinner = $('#savePaySpinner');
                    var $errors = $('#recordPayErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/billing/payments') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (res.success) {
                                $('#recordPaymentModal').modal('hide');
                                $('#recordPaymentForm')[0].reset();
                                billsTable.ajax.reload(null, false);
                                paymentsTable.ajax.reload(null, false);
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var msg = 'Failed to record payment.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            $errors.removeClass('d-none').html(msg);
                        }
                    });
                });

                // =========================================================================
                // 7. VIEW INVOICE MODAL
                // =========================================================================
                $(document).on('click', '.btn-view-invoice', function() {
                    var billId = $(this).data('id');

                    $.ajax({
                        url: "{{ url('/admin/billing/bills') }}/" + billId,
                        type: "GET",
                        success: function(res) {
                            if (res.success) {
                                var b = res.bill;
                                var f = res.formatted;

                                $('#inv_no').text(b.bill_number);
                                $('#inv_tenant_name').text(b.tenant ? b.tenant.name : 'Resident');
                                var roomText = b.room ? 'Room ' + b.room.room_number + ' (' + b.room.floor + ')' : (b.tenant ? 'Room ' + b.tenant.room_number : 'Room N/A');
                                $('#inv_tenant_room').text(roomText);
                                $('#inv_tenant_phone').text(b.tenant && b.tenant.phone ? b.tenant.phone : '-');
                                $('#inv_tenant_email').text(b.tenant && b.tenant.email ? b.tenant.email : '-');

                                $('#inv_period').text(b.billing_month);
                                $('#inv_date').text(f.bill_date);
                                $('#inv_due_date').text(f.due_date);

                                $('#inv_item_title').text(b.bill_type);
                                $('#inv_item_desc').text(b.notes ? b.notes : 'Charges for residence facilities');
                                $('#inv_item_month').text(b.billing_month);
                                $('#inv_item_amount').text('₹' + f.amount);

                                $('#inv_summary_total').text('₹' + f.amount);
                                $('#inv_summary_paid').text('₹' + f.paid_amount);
                                $('#inv_summary_due').text('₹' + f.due_amount);

                                // Status stamp
                                var stampCls = b.status === 'paid' ? 'paid' : (b.status === 'partially_paid' ? 'partial' : (b.status === 'overdue' ? 'overdue' : 'unpaid'));
                                var stampText = b.status.toUpperCase().replace('_', ' ');
                                $('#inv_status_stamp').html('<span class="invoice-status-stamp ' + stampCls + '">' + stampText + '</span>');

                                // Render payment history
                                var rows = '';
                                if (b.payments && b.payments.length > 0) {
                                    b.payments.forEach(function(p) {
                                        rows += '<tr>' +
                                            '<td class="fw-bold font-12">' + p.payment_number + '</td>' +
                                            '<td>' + (p.payment_date ? p.payment_date.substring(0, 10) : '-') + '</td>' +
                                            '<td>' + p.payment_method + '</td>' +
                                            '<td>' + (p.transaction_id ? p.transaction_id : '-') + '</td>' +
                                            '<td class="text-end fw-bold text-success">+ ₹' + parseFloat(p.amount).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '</td>' +
                                        '</tr>';
                                    });
                                    $('#inv_payments_list').html(rows);
                                    $('#inv_payments_history_section').removeClass('d-none');
                                } else {
                                    $('#inv_payments_list').html('<tr><td colspan="5" class="text-center text-muted py-2">No payments recorded for this invoice yet.</td></tr>');
                                    $('#inv_payments_history_section').removeClass('d-none');
                                }

                                $('#viewInvoiceModal').modal('show');
                            }
                        }
                    });
                });

                // =========================================================================
                // 8. VIEW RECEIPT MODAL
                // =========================================================================
                $(document).on('click', '.btn-view-receipt', function() {
                    var payId = $(this).data('id');

                    $.ajax({
                        url: "{{ url('/admin/billing/payments') }}/" + payId,
                        type: "GET",
                        success: function(res) {
                            if (res.success) {
                                var p = res.payment;
                                var f = res.formatted;

                                $('#rcp_no').text(p.payment_number);
                                $('#rcp_date').text(f.payment_date);
                                $('#rcp_tenant').text(p.tenant ? p.tenant.name : 'Resident');
                                var room = p.bill && p.bill.room ? 'Room ' + p.bill.room.room_number : (p.tenant ? 'Room ' + p.tenant.room_number : '-');
                                $('#rcp_room').text(room);
                                $('#rcp_bill').text(p.bill ? p.bill.bill_number + ' (' + p.bill.bill_type + ')' : '-');
                                $('#rcp_method').text(p.payment_method);
                                $('#rcp_amount').text('₹' + f.amount);

                                if (p.transaction_id) {
                                    $('#rcp_tx').text(p.transaction_id);
                                    $('#rcp_tx_row').removeClass('d-none');
                                } else {
                                    $('#rcp_tx_row').addClass('d-none');
                                }

                                $('#viewReceiptModal').modal('show');
                            }
                        }
                    });
                });

                // =========================================================================
                // 9. EDIT BILL MODAL TRIGGER & SUBMIT
                // =========================================================================
                $(document).on('click', '.btn-edit-bill', function() {
                    var billId = $(this).data('id');

                    $.ajax({
                        url: "{{ url('/admin/billing/bills') }}/" + billId,
                        type: "GET",
                        success: function(res) {
                            if (res.success) {
                                var b = res.bill;
                                $('#edit_bill_id').val(b.id);
                                $('#edit_bill_type').val(b.bill_type);
                                $('#edit_billing_month').val(b.billing_month);
                                $('#edit_due_date').val(b.due_date ? b.due_date.substring(0, 10) : '');
                                $('#edit_amount').val(b.amount);
                                $('#edit_notes').val(b.notes);

                                $('#editBillErrors').addClass('d-none').html('');
                                $('#editBillModal').modal('show');
                            }
                        }
                    });
                });

                $('#editBillForm').on('submit', function(e) {
                    e.preventDefault();
                    var billId = $('#edit_bill_id').val();
                    var $form = $(this);
                    var $btn = $('#btnUpdateBill');
                    var $spinner = $('#updateBillSpinner');
                    var $errors = $('#editBillErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/billing/bills') }}/" + billId + "/update",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (res.success) {
                                $('#editBillModal').modal('hide');
                                billsTable.ajax.reload(null, false);
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var msg = 'Failed to update invoice.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            $errors.removeClass('d-none').html(msg);
                        }
                    });
                });

                // =========================================================================
                // 10. DELETE BILL
                // =========================================================================
                $(document).on('click', '.btn-delete-bill', function() {
                    var billId = $(this).data('id');
                    var billNo = $(this).data('bill');

                    Swal.fire({
                        title: 'Delete Invoice?',
                        text: 'Are you sure you want to delete invoice ' + billNo + '? All payment records for this invoice will also be deleted.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Delete Invoice'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/billing/bills') }}/" + billId,
                                type: "DELETE",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.success) {
                                        billsTable.ajax.reload(null, false);
                                        paymentsTable.ajax.reload(null, false);
                                        Toast.fire({ icon: 'success', title: res.message });
                                    }
                                }
                            });
                        }
                    });
                });

                // =========================================================================
                // 11. DELETE PAYMENT
                // =========================================================================
                $(document).on('click', '.btn-delete-payment', function() {
                    var payId = $(this).data('id');
                    var rcpNo = $(this).data('receipt');

                    Swal.fire({
                        title: 'Delete Payment?',
                        text: 'Are you sure you want to delete payment receipt ' + rcpNo + '? The associated invoice balance will be restored.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Delete Payment'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/billing/payments') }}/" + payId,
                                type: "DELETE",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.success) {
                                        billsTable.ajax.reload(null, false);
                                        paymentsTable.ajax.reload(null, false);
                                        Toast.fire({ icon: 'success', title: res.message });
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
