@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap" rel="stylesheet">
        <style>
            /* Remove browser number spin buttons */
            input[type=number]::-webkit-outer-spin-button,
            input[type=number]::-webkit-inner-spin-button {
                -webkit-appearance: none !important;
                margin: 0 !important;
            }
            input[type=number] {
                -moz-appearance: textfield !important;
                appearance: textfield !important;
            }
            .readonly-lock-field {
                background-color: #f8fafc !important;
                color: #334155 !important;
                cursor: not-allowed;
            }
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
            .table-responsive .dropdown-menu {
                z-index: 1065 !important;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
                border-radius: 8px;
            }
            .table-responsive .dropdown-item:hover {
                background-color: #f1f5f9;
            }
            /* Printable Invoice & Receipt Styling */
            @media print {
                body * {
                    visibility: hidden !important;
                }
                #printableInvoiceArea, #printableInvoiceArea *,
                #printableReceiptArea, #printableReceiptArea * {
                    visibility: visible !important;
                }
                #printableInvoiceArea,
                #printableReceiptArea {
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

    <!-- Overview Metric Cards (Rent Amounts & Security Deposits Included) -->
    <div class="row g-3 mt-1">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-pill-card border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Scheduled Rent</span>
                    <span class="badge bg-info-subtle text-info font-12"><i class="fas fa-calendar-check"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-info" id="statScheduledRent">₹{{ number_format($stats['scheduledRent'] ?? 0) }}</h3>
                <small class="text-muted">Active Leases / Mo</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-pill-card border-start border-4 border-purple" style="border-left-color: #8b5cf6 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Security Deposits</span>
                    <span class="badge font-12" style="background-color: #f3e8ff; color: #8b5cf6;"><i class="fas fa-shield-alt"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-purple" style="color: #8b5cf6;" id="statTotalSecurityDeposits">₹{{ number_format($stats['totalSecurityDeposits'] ?? 0) }}</h3>
                <small class="text-muted">Held in Trust</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-pill-card border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Total Invoiced</span>
                    <span class="badge bg-primary-subtle text-primary font-12"><i class="fas fa-file-invoice"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-dark" id="statTotalBilled">₹{{ number_format($stats['totalBilled']) }}</h3>
                <small class="text-muted">This Mo: <span id="statThisMonthBilled">₹{{ number_format($stats['thisMonthBilled']) }}</span></small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-pill-card border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Total Collected</span>
                    <span class="badge bg-success-subtle text-success font-12"><i class="fas fa-check-double"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-success" id="statTotalCollected">₹{{ number_format($stats['totalCollected']) }}</h3>
                <small class="text-muted">Rate: <span class="fw-bold text-success" id="statCollectionRate">{{ $stats['collectionRate'] }}%</span></small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-pill-card border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Outstanding Dues</span>
                    <span class="badge bg-warning-subtle text-warning font-12"><i class="fas fa-clock"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-warning" id="statTotalDue">₹{{ number_format($stats['totalDue']) }}</h3>
                <small class="text-muted">Pending balance</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-pill-card border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Overdue Invoices</span>
                    <span class="badge bg-danger-subtle text-danger font-12"><i class="fas fa-exclamation-circle"></i></span>
                </div>
                <h3 class="fw-bold my-1 text-danger" id="statOverdueCount">{{ $stats['overdueCount'] }}</h3>
                <small class="text-muted">Past due date</small>
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
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Filter by Tenant</label>
                                    <select id="filterBillTenant" class="form-select filter-select">
                                        <option value="">All Tenants</option>
                                        @foreach($activeTenants as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} (Room {{ $t->room_numbers_display }})</option>
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
                                <div class="col-md-2 text-end">
                                    <button type="button" id="btnResetBillFilters" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                    <button type="button" id="btnExportBills" class="btn btn-outline-success btn-sm ms-2">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                </div>
                            </div>

                            <!-- Modern Bulk Action Bar for Bills -->
                            <div id="bulkActionBarBills" class="d-none align-items-center justify-content-between p-2 px-3 mb-2 rounded-3" style="background: linear-gradient(135deg, #0a5858 0%, #0d7373 100%); color: #fff; box-shadow: 0 4px 12px rgba(10, 88, 88, 0.25);">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-white text-dark fw-bold px-2 py-1 font-12" id="bulkSelectedCountBills">0 Selected</span>
                                    <span class="font-12 text-white-50 d-none d-sm-inline">Actions for selected invoices:</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-warning btn-sm fw-semibold btn-bulk-edit-bills px-3 py-1 font-12">
                                        <i class="fas fa-edit me-1"></i> Bulk Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm fw-semibold btn-bulk-delete-bills px-3 py-1 font-12">
                                        <i class="fas fa-trash-alt me-1"></i> Bulk Delete
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light btn-bulk-deselect-bills px-2 py-1 font-11" title="Deselect All">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Bills DataTable -->
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table job-table w-100" id="billsTable">
                                    <thead class="table-theme text-white text-nowrap">
                                        <tr>
                                            <th style="width: 40px;" class="text-center">
                                                <input type="checkbox" class="form-check-input bulk-select-all" id="bulkSelectAllBills">
                                            </th>
                                            <th style="width: 4%;">#</th>
                                            <th style="min-width: 140px;" class="text-nowrap">Invoice No</th>
                                            <th style="min-width: 180px;">Tenant & Room</th>
                                            <th style="min-width: 150px;">Type & Month</th>
                                            <th style="min-width: 130px;" class="text-nowrap">Amount</th>
                                            <th style="min-width: 170px;" class="text-nowrap">Paid / Due Progress</th>
                                            <th style="min-width: 110px;" class="text-nowrap">Due Date</th>
                                            <th style="min-width: 110px;" class="text-nowrap">Status</th>
                                            <th style="min-width: 150px;" class="text-center text-nowrap">Action</th>
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
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Filter by Tenant</label>
                                    <select id="filterPayTenant" class="form-select filter-select">
                                        <option value="">All Tenants</option>
                                        @foreach($activeTenants as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} (Room {{ $t->room_numbers_display }})</option>
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
                                <div class="col-md-5 text-end">
                                    <button type="button" id="btnResetPayFilters" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-undo me-1"></i> Reset Filters
                                    </button>
                                    <button type="button" id="btnExportPayments" class="btn btn-outline-success btn-sm ms-2">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                </div>
                            </div>

                            <!-- Modern Bulk Action Bar for Payments -->
                            <div id="bulkActionBarPayments" class="d-none align-items-center justify-content-between p-2 px-3 mb-2 rounded-3" style="background: linear-gradient(135deg, #0a5858 0%, #0d7373 100%); color: #fff; box-shadow: 0 4px 12px rgba(10, 88, 88, 0.25);">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-white text-dark fw-bold px-2 py-1 font-12" id="bulkSelectedCountPayments">0 Selected</span>
                                    <span class="font-12 text-white-50 d-none d-sm-inline">Actions for selected receipts:</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-danger btn-sm fw-semibold btn-bulk-delete-payments px-3 py-1 font-12">
                                        <i class="fas fa-trash-alt me-1"></i> Bulk Delete
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light btn-bulk-deselect-payments px-2 py-1 font-11" title="Deselect All">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Payments DataTable -->
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table job-table w-100" id="paymentsTable">
                                    <thead class="table-theme text-white text-nowrap">
                                        <tr>
                                            <th style="width: 40px;" class="text-center">
                                                <input type="checkbox" class="form-check-input bulk-select-all" id="bulkSelectAllPayments">
                                            </th>
                                            <th style="width: 4%;">#</th>
                                            <th style="min-width: 150px;" class="text-nowrap">Receipt No</th>
                                            <th style="min-width: 150px;" class="text-nowrap">Invoice Reference</th>
                                            <th style="min-width: 180px;">Tenant & Room</th>
                                            <th style="min-width: 130px;" class="text-nowrap">Amount Paid</th>
                                            <th style="min-width: 120px;" class="text-nowrap">Payment Date</th>
                                            <th style="min-width: 130px;" class="text-nowrap">Method</th>
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
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Select Resident / Tenant <span class="text-danger">*</span></label>
                                <select name="tenant_id" id="bill_tenant_id" class="form-select" required>
                                    <option value="" disabled selected>Select resident...</option>
                                    @foreach($activeTenants as $t)
                                        <option value="{{ $t->id }}" 
                                            data-rent="{{ $t->monthly_rent }}" 
                                            data-total="{{ $t->total_monthly_due }}"
                                            data-eb-rate="{{ $t->eb_unit_rate ?? 10.00 }}"
                                            data-eb-prev="{{ $t->last_eb_reading }}"
                                            data-eb-start="{{ $t->eb_start_reading ?? 0.00 }}"
                                            data-maint="{{ $t->maintenance_charges }}"
                                            data-other="{{ $t->other_charges }}"
                                            data-room="{{ $t->room_numbers_display }}">
                                            {{ $t->name }} (Room {{ $t->room_numbers_display }}) - Rent ₹{{ number_format($t->monthly_rent) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Billing Month <span class="text-danger">*</span></label>
                                <input type="text" name="billing_month" class="form-control" value="{{ $currentMonth }}" placeholder="e.g. November 2026" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                                <select name="bill_type" id="bill_type_select" class="form-select" required>
                                    <option value="Monthly Rent & Utilities" selected>Monthly Rent & Utilities</option>
                                    <option value="Monthly Rent">Monthly Rent Only</option>
                                    <option value="Electricity">Electricity Bill Only</option>
                                    <option value="Maintenance">Maintenance Charge</option>
                                    <option value="Security Deposit">Security Deposit</option>
                                    <option value="Mess / Food">Mess / Food Fee</option>
                                    <option value="Other">Other Charges</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+5 days')) }}" required>
                            </div>

                            <!-- Electricity (EB) Meter Reading & Calculation Section -->
                            <div class="col-12" id="eb_meter_section">
                                <div class="card border border-warning-subtle shadow-none mb-0" style="background: #fffdf5; border-radius: 10px;">
                                    <div class="card-header bg-warning-subtle py-2 d-flex justify-content-between align-items-center">
                                        <div class="fw-bold text-dark font-13">
                                            <i class="fas fa-bolt text-warning me-1"></i> Electricity (EB) Meter Reading & Units Calculation
                                        </div>
                                        <div class="font-12 text-muted" id="eb_rate_badge">
                                            Active Rate: <span class="fw-bold text-dark" id="display_eb_rate">₹10.00</span> / Unit
                                        </div>
                                    </div>
                                    <div class="card-body py-3 px-3">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold mb-1"><i class="fas fa-lock text-muted font-10 me-1"></i> Previous (Units)</label>
                                                <input type="number" step="0.01" min="0" readonly name="eb_previous_reading" id="bill_eb_prev_reading" class="form-control form-control-sm readonly-lock-field text-center font-12 fw-semibold" placeholder="0.00">
                                                <small class="text-muted font-10">Start / last bill</small>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold mb-1">Current Reading (Units) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" min="0" name="eb_current_reading" id="bill_eb_curr_reading" class="form-control form-control-sm border-primary" placeholder="Enter meter units">
                                                <small class="text-muted font-10">Current meter reading</small>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold mb-1">Consumed Units</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" readonly name="eb_consumed_units" id="bill_eb_consumed_units" class="form-control form-control-sm readonly-lock-field fw-bold text-primary text-center font-13" placeholder="0" value="0">
                                                    <span class="input-group-text font-11 px-2">Units</span>
                                                </div>
                                                <small class="text-muted font-10" id="eb_diff_hint">Current - Previous</small>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold mb-1"><i class="fas fa-lock text-muted font-10 me-1"></i> Rate (₹/Unit)</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text font-11">₹</span>
                                                    <input type="number" step="0.01" min="0" readonly name="eb_unit_rate" id="bill_eb_unit_rate" class="form-control form-control-sm readonly-lock-field fw-bold text-center" placeholder="10.00" value="10.00">
                                                </div>
                                                <small class="text-muted font-10">Agreed unit rate</small>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold mb-1">EB Charge Amount</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text font-11">₹</span>
                                                    <input type="number" step="0.01" readonly name="eb_amount" id="bill_eb_amount" class="form-control form-control-sm readonly-lock-field fw-bold text-success font-13" placeholder="0.00" value="0.00">
                                                </div>
                                                <small class="text-muted font-10">Units × Rate</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Invoice Line Items Table -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-semibold mb-0">
                                        <i class="fas fa-list-ol text-primary me-1"></i> Invoice Line Items
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-12" id="btnAddCustomItem">
                                        <i class="fas fa-plus me-1"></i> Add Custom Item
                                    </button>
                                </div>
                                <div class="table-responsive border rounded bg-white">
                                    <table class="table table-sm table-hover align-middle mb-0" id="billItemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 45%;">Item Description</th>
                                                <th style="width: 20%;">Category</th>
                                                <th style="width: 25%;" class="text-end">Amount (₹)</th>
                                                <th style="width: 10%;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="billItemsBody">
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">
                                                    <i class="fas fa-arrow-up me-1"></i> Select a resident above to auto-populate invoice line items.
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-light border-top">
                                            <tr>
                                                <th colspan="2" class="text-end fw-bold">Total Invoice Amount:</th>
                                                <th class="text-end fw-bold font-16 text-success" id="displayInvoiceTotal">₹0.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <input type="hidden" name="amount" id="bill_amount_input" value="0.00">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Notes / Description (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. November monthly room rent and utilities breakdown"></textarea>
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

                        <!-- Electricity (EB) Details Badge if applicable -->
                        <div id="inv_eb_details_badge" class="mb-3 d-none"></div>

                        <!-- Invoice Line Items Table -->
                        <table class="table table-bordered mb-4">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 50%;">Description / Line Item</th>
                                    <th style="width: 25%;">Billing Month</th>
                                    <th style="width: 20%;" class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody id="inv_items_tbody">
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <strong id="inv_item_title">Monthly Rent</strong>
                                        <div class="text-muted small" id="inv_item_desc">Room accommodation and resident amenities</div>
                                    </td>
                                    <td id="inv_item_month">-</td>
                                    <td class="text-end fw-bold font-14" id="inv_item_amount">₹0.00</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Bottom Grid: Direct UPI / QR Pay & Financial Summary -->
                        <div class="row mb-4 g-3 align-items-stretch">
                            <!-- Left: Instant UPI / QR Payment -->
                            <div class="col-md-7">
                                <div class="p-3 bg-light rounded border h-100">
                                    <div class="fw-bold text-dark mb-2 font-13">
                                        <i class="fas fa-qrcode text-primary me-1"></i> Instant UPI &amp; Direct Bank Transfer
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="background: #ffffff; padding: 6px; border-radius: 8px; border: 1px solid #cbd5e1; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                                            <img src="@if(!empty($settings['upi_qr_code']) && file_exists(public_path('storage/' . $settings['upi_qr_code']))){{ asset('storage/' . $settings['upi_qr_code']) }}@else @include('backend.partials.upi_qr_base64') @endif" alt="UPI QR Code" style="width: 84px; height: 84px; object-fit: contain; display: block;">
                                        </div>
                                        <div class="font-12">
                                            <div><strong>UPI VPA ID:</strong> <span class="badge bg-primary-subtle text-primary font-12">{{ $settings['upi_id'] ?? 'srmresidence@okhdfcbank' }}</span></div>
                                            <div class="text-muted mt-1"><strong>Payee Name:</strong> {{ $settings['upi_merchant_name'] ?? 'SRM RESIDENCE' }}</div>
                                            @if(!empty($settings['bank_account_number']))
                                                <div class="text-muted mt-1 font-11">
                                                    <strong>Bank:</strong> {{ $settings['bank_name'] ?? 'Bank Transfer' }} | 
                                                    <strong>A/C:</strong> {{ $settings['bank_account_number'] }} | 
                                                    <strong>IFSC:</strong> {{ $settings['bank_ifsc_code'] ?? '-' }}
                                                </div>
                                            @endif
                                            <div class="text-muted font-11 mt-1"><i class="fas fa-mobile-alt me-1 text-success"></i> Scan with GPay, PhonePe, Paytm or BHIM UPI</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Financial Summary -->
                            <div class="col-md-5">
                                <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-center">
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

                        <!-- Authorized Signatory & Official Residence Stamp -->
                        <div class="d-flex justify-content-between align-items-end mt-4 pt-3 border-top">
                            <div class="text-muted small">
                                <strong>{{ $settings['residence_name'] ?? 'SRM Residence' }}</strong><br>
                                Computer generated billing record & official proof.
                            </div>
                            <div class="text-center" style="width: 170px; position: relative;">
                                <div style="position: relative; height: 65px; display: flex; align-items: flex-end; justify-content: center;">
                                    <div style="position: absolute; top: -18px; left: 50%; transform: translateX(-50%); pointer-events: none; opacity: 0.88; z-index: 1;">
                                        @include('backend.partials.residence_stamp', ['width' => 90, 'height' => 90, 'rotation' => '-6deg'])
                                    </div>
                                    <div style="font-family: 'Caveat', cursive, 'Brush Script MT', sans-serif; font-size: 22px; font-weight: 700; color: #0f172a; position: relative; z-index: 2; transform: rotate(-3deg); line-height: 1;">
                                        {{ $settings['residence_name'] ?? 'SRM Residence' }}
                                    </div>
                                </div>
                                <div style="border-bottom: 1.5px solid #475569; margin-bottom: 4px; position: relative; z-index: 2;"></div>
                                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #0f172a; position: relative; z-index: 2;">Authorized Signatory</div>
                                <div style="font-size: 9px; color: #64748b; position: relative; z-index: 2;">{{ $settings['residence_name'] ?? 'SRM Residence' }}</div>
                            </div>
                        </div>

                        <!-- Footer notes -->
                        <div class="pt-3 text-muted small text-center font-11">
                            Thank you for staying at {{ $settings['residence_name'] ?? 'SRM Residence' }}! For any billing queries, please contact the hostel office.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success text-white waves-effect waves-light" id="btn_modal_share_invoice_pdf" title="Share Invoice as PDF">
                        <i class="fab fa-whatsapp me-1"></i><i class="fas fa-file-pdf me-1"></i> Share PDF
                    </button>
                    <button type="button" class="btn btn-primary text-white waves-effect waves-light" id="btn_modal_share_invoice_image" title="Share Invoice as Image">
                        <i class="fab fa-whatsapp me-1"></i><i class="fas fa-image me-1"></i> Share Image
                    </button>
                    <button type="button" class="btn btn-info text-white waves-effect waves-light" id="btn_send_modal_invoice_email">
                        <i class="fas fa-paper-plane me-1"></i> Send via Email
                    </button>
                    <a href="javascript:void(0)" class="btn button-color text-white waves-effect waves-light" id="btn_open_print_invoice" target="_blank">
                        <i class="fas fa-print me-1"></i> Print Invoice (A4)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4.1. UNIFIED SHARE INVOICE HUB MODAL -->
    <div class="modal fade" id="shareInvoiceHubModal" tabindex="-1" aria-labelledby="shareInvoiceHubModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header button-color text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-white text-dark p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas fa-share-alt font-16" style="color: #0a5858;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title text-white mb-0 font-16 fw-bold" id="shareInvoiceHubModalLabel">Share Invoice</h5>
                            <small class="text-white-50 font-12" id="hub_invoice_subtitle">SRM Residence</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Invoice Summary Card -->
                    <div class="p-3 rounded-3 mb-3 border bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark font-14" id="hub_tenant_name">Tenant Name</span>
                            <span class="badge bg-success-subtle text-success border border-success font-11 px-2 py-1" id="hub_bill_type">Monthly Rent</span>
                        </div>
                        <div class="text-muted font-12 d-flex justify-content-between">
                            <span><i class="fas fa-door-open me-1"></i>Room: <strong class="text-dark" id="hub_room_no">-</strong></span>
                            <span>Month: <strong class="text-dark" id="hub_month">-</strong></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
                            <span class="text-muted font-12">Total: <strong class="text-dark" id="hub_total_amt">₹0.00</strong></span>
                            <span class="font-13 fw-bold text-danger">Balance Due: <span id="hub_due_amt">₹0.00</span></span>
                        </div>
                    </div>

                    <div class="font-11 text-uppercase text-muted fw-bold mb-2" style="letter-spacing: 0.5px;">Choose Sharing Method:</div>

                    <!-- 4 Sharing Action Options -->
                    <div class="d-grid gap-2">
                        <!-- 1. WhatsApp -->
                        <button type="button" class="btn btn-outline-success text-start p-3 d-flex align-items-center gap-3 rounded-3 border-2" id="btnHubWhatsApp" style="transition: all 0.2s;">
                            <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="fab fa-whatsapp fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark font-14">Share via WhatsApp</div>
                                <small class="text-muted font-11 d-block" id="hub_whatsapp_desc">Send pre-filled invoice message & link to tenant</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted font-12"></i>
                        </button>

                        <!-- 2. Share with Apps / Native Share -->
                        <button type="button" class="btn btn-outline-primary text-start p-3 d-flex align-items-center gap-3 rounded-3 border-2" id="btnHubNativeShare" style="transition: all 0.2s;">
                            <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="fas fa-share-nodes fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark font-14">Share via Apps / Copy Link</div>
                                <small class="text-muted font-11 d-block">Open phone share drawer (Telegram, Drive, etc.) or copy URL</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted font-12"></i>
                        </button>

                        <!-- 3. Print Invoice (A4) -->
                        <button type="button" class="btn btn-outline-dark text-start p-3 d-flex align-items-center gap-3 rounded-3 border-2" id="btnHubPrintA4" style="transition: all 0.2s;">
                            <div class="rounded-circle bg-dark text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="fas fa-print fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark font-14">Print Official Invoice (A4)</div>
                                <small class="text-muted font-11 d-block">Official printable tax invoice layout with residence seal & signature</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted font-12"></i>
                        </button>

                        <!-- 4. Send via Email -->
                        <button type="button" class="btn btn-outline-info text-start p-3 d-flex align-items-center gap-3 rounded-3 border-2" id="btnHubEmail" style="transition: all 0.2s;">
                            <div class="rounded-circle bg-info text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="fas fa-paper-plane fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark font-14">Send Invoice via Email</div>
                                <small class="text-muted font-11 d-block" id="hub_email_desc">Email computerized invoice statement to tenant</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted font-12"></i>
                        </button>
                    </div>

                    <!-- Direct Copyable Link Row -->
                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label font-11 text-muted mb-1 fw-bold">Online Invoice URL:</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control font-12 bg-white" id="hub_invoice_url_input" readonly>
                            <button class="btn btn-secondary font-12" type="button" id="btnHubCopyUrl">
                                <i class="fas fa-copy me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
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

                        <!-- Authorized Signature & Official Stamp in Receipt Modal -->
                        <div class="d-flex justify-content-between align-items-end pt-3 border-top">
                            <div class="text-muted font-11">
                                <strong>{{ $settings['residence_name'] ?? 'SRM Residence' }}</strong><br>
                                Official payment acknowledgment
                            </div>
                            <div class="text-center" style="width: 150px; position: relative;">
                                <div style="position: relative; height: 55px; display: flex; align-items: flex-end; justify-content: center;">
                                    <div style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); pointer-events: none; opacity: 0.88; z-index: 1;">
                                        @include('backend.partials.residence_stamp', ['width' => 78, 'height' => 78, 'rotation' => '-6deg'])
                                    </div>
                                    <div style="font-family: 'Caveat', cursive, 'Brush Script MT', sans-serif; font-size: 19px; font-weight: 700; color: #0f172a; position: relative; z-index: 2; transform: rotate(-3deg); line-height: 1;">
                                        {{ $settings['residence_name'] ?? 'SRM Residence' }}
                                    </div>
                                </div>
                                <div style="border-bottom: 1.5px solid #475569; margin-bottom: 4px; position: relative; z-index: 2;"></div>
                                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #0f172a; position: relative; z-index: 2;">Authorized Signature</div>
                            </div>
                        </div>

                        <div class="text-center text-muted font-11 pt-2">
                            Generated by {{ $settings['residence_name'] ?? 'SRM Residence' }} Management System.<br>
                            This is an official computer-generated digital receipt.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success text-white waves-effect waves-light" id="btn_modal_share_receipt_pdf" title="Share Receipt as PDF">
                        <i class="fab fa-whatsapp me-1"></i><i class="fas fa-file-pdf me-1"></i> Share PDF
                    </button>
                    <button type="button" class="btn btn-primary text-white waves-effect waves-light" id="btn_modal_share_receipt_image" title="Share Receipt as Image">
                        <i class="fab fa-whatsapp me-1"></i><i class="fas fa-image me-1"></i> Share Image
                    </button>
                    <a href="javascript:void(0)" class="btn btn-dark waves-effect waves-light" id="btn_open_print_receipt" target="_blank">
                        <i class="fas fa-print me-1"></i> Print Receipt
                    </a>
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

    <!-- BULK EDIT BILLS MODAL -->
    <div class="modal fade" id="bulkEditBillModal" tabindex="-1" aria-labelledby="bulkEditBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <div>
                        <h5 class="modal-title text-white" id="bulkEditBillModalLabel">
                            <i class="fas fa-edit me-1"></i> Bulk Edit Invoices
                        </h5>
                        <small class="text-white-50">Updating <span id="bulkEditCountBadgeBills" class="badge bg-warning text-dark fw-bold"></span></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bulkEditBillForm">
                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 px-3 font-12 mb-3">
                            <i class="fas fa-info-circle me-1"></i> <strong>Note:</strong> Only filled fields will be updated across all selected invoices. Leave fields empty to keep their current values.
                        </div>
                        <div id="bulkEditBillErrors" class="alert alert-danger d-none py-2 px-3 font-12"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Invoice / Bill Type</label>
                                <select name="bill_type" class="form-select">
                                    <option value="">-- Keep Existing --</option>
                                    <option value="Monthly Rent">Monthly Rent</option>
                                    <option value="Electricity">Electricity</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Security Deposit">Security Deposit</option>
                                    <option value="Mess / Food">Mess / Food</option>
                                    <option value="Water">Water</option>
                                    <option value="Penalty / Fine">Penalty / Fine</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Billing Month</label>
                                <input type="text" name="billing_month" class="form-control" placeholder="e.g. November 2026">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold font-13">Payment Due Date</label>
                                <input type="date" name="due_date" class="form-control" placeholder="Keep Existing">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold font-13">General Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Leave empty to keep existing notes"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white" id="btnApplyBulkEditBills">
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
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="{{ asset('assets/libs/html2canvas.min.js') }}"></script>
        <script src="{{ asset('assets/libs/jspdf.umd.min.js') }}"></script>

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
                // 0. LIVE STATISTIC CARDS UPDATE
                // =========================================================================
                function updateStatsCards(stats) {
                    if (!stats) return;
                    if (stats.scheduledRentFormatted !== undefined) {
                        $('#statScheduledRent').text(stats.scheduledRentFormatted);
                    } else if (stats.scheduledRent !== undefined) {
                        $('#statScheduledRent').text('₹' + Math.round(stats.scheduledRent).toLocaleString('en-IN'));
                    }

                    if (stats.totalSecurityDepositsFormatted !== undefined) {
                        $('#statTotalSecurityDeposits').text(stats.totalSecurityDepositsFormatted);
                    } else if (stats.totalSecurityDeposits !== undefined) {
                        $('#statTotalSecurityDeposits').text('₹' + Math.round(stats.totalSecurityDeposits).toLocaleString('en-IN'));
                    }

                    if (stats.totalBilledFormatted !== undefined) {
                        $('#statTotalBilled').text(stats.totalBilledFormatted);
                    } else if (stats.totalBilled !== undefined) {
                        $('#statTotalBilled').text('₹' + Math.round(stats.totalBilled).toLocaleString('en-IN'));
                    }

                    if (stats.thisMonthBilledFormatted !== undefined) {
                        $('#statThisMonthBilled').text(stats.thisMonthBilledFormatted);
                    } else if (stats.thisMonthBilled !== undefined) {
                        $('#statThisMonthBilled').text('₹' + Math.round(stats.thisMonthBilled).toLocaleString('en-IN'));
                    }

                    if (stats.totalCollectedFormatted !== undefined) {
                        $('#statTotalCollected').text(stats.totalCollectedFormatted);
                    } else if (stats.totalCollected !== undefined) {
                        $('#statTotalCollected').text('₹' + Math.round(stats.totalCollected).toLocaleString('en-IN'));
                    }

                    if (stats.collectionRate !== undefined) {
                        $('#statCollectionRate').text(stats.collectionRate + '%');
                    }

                    if (stats.totalDueFormatted !== undefined) {
                        $('#statTotalDue').text(stats.totalDueFormatted);
                    } else if (stats.totalDue !== undefined) {
                        $('#statTotalDue').text('₹' + Math.round(stats.totalDue).toLocaleString('en-IN'));
                    }

                    if (stats.overdueCount !== undefined) {
                        $('#statOverdueCount').text(stats.overdueCount);
                    }
                }

                function reloadStats() {
                    $.ajax({
                        url: "{{ url('/admin/billing/stats') }}",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.stats) {
                                updateStatsCards(res.stats);
                            }
                        }
                    });
                }

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
                        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
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
                    order: [[2, 'desc']],
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
                    billsTable.search('').draw();
                });

                $('#btnExportBills').on('click', function() {
                    var params = {
                        tenant_id: $('#filterBillTenant').val(),
                        bill_type: $('#filterBillType').val(),
                        status: $('#filterBillStatus').val(),
                        billing_month: $('#filterBillMonth').val(),
                        search: billsTable.search()
                    };
                    window.location.href = "{{ route('billing.bills.export') }}?" + $.param(params);
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
                        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'payment_number_badge', name: 'payment_number' },
                        { data: 'bill_reference', name: 'bill_id' },
                        { data: 'tenant_info', name: 'tenant_id' },
                        { data: 'amount_display', name: 'amount' },
                        { data: 'payment_date_formatted', name: 'payment_date' },
                        { data: 'payment_method_badge', name: 'payment_method' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[2, 'desc']],
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
                    paymentsTable.search('').draw();
                });

                $('#btnExportPayments').on('click', function() {
                    var params = {
                        tenant_id: $('#filterPayTenant').val(),
                        payment_method: $('#filterPayMethod').val(),
                        search: paymentsTable.search()
                    };
                    window.location.href = "{{ route('billing.payments.export') }}?" + $.param(params);
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
                // 3. DYNAMIC INVOICE ITEMS & EB METER CONSUMPTION CALCULATION
                // =========================================================================
                var customItemCounter = 10;

                function calculateInvoiceTotal() {
                    var total = 0;
                    $('#billItemsBody tr').each(function() {
                        var amt = parseFloat($(this).find('.item-amt-input').val()) || 0;
                        total += amt;
                    });

                    $('#displayInvoiceTotal').text('₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#bill_amount_input').val(total.toFixed(2));

                    if ($('#immediate_payment_check').is(':checked')) {
                        $('#immediate_pay_amt').val(total.toFixed(2));
                    }
                }

                function calculateEbConsumption() {
                    var prev = parseFloat($('#bill_eb_prev_reading').val()) || 0;
                    var currRaw = $('#bill_eb_curr_reading').val();
                    var rate = parseFloat($('#bill_eb_unit_rate').val()) || 10.00;

                    // Update live rate display badge
                    $('#display_eb_rate').text('₹' + rate.toFixed(2));

                    if (currRaw === '' || isNaN(parseFloat(currRaw))) {
                        $('#bill_eb_consumed_units').val('0');
                        $('#bill_eb_amount').val('0.00');
                        $('#eb_diff_hint').text('Current - Previous').removeClass('text-danger');
                        updateEbLineItem(0, rate, prev, 0, 0);
                        return;
                    }

                    var curr = parseFloat(currRaw);
                    if (curr < prev) {
                        $('#eb_diff_hint').html('<span class="text-danger fw-semibold">Current cannot be less than previous (' + prev + ')</span>');
                        $('#bill_eb_consumed_units').val('0');
                        $('#bill_eb_amount').val('0.00');
                        updateEbLineItem(0, rate, prev, curr, 0);
                    } else {
                        var units = curr - prev;
                        var cost = units * rate;
                        var unitsDisplay = (units % 1 === 0) ? units.toFixed(0) : units.toFixed(2);
                        $('#eb_diff_hint').html('<span class="text-success fw-semibold">' + unitsDisplay + ' units consumed</span>');
                        $('#bill_eb_consumed_units').val(unitsDisplay);
                        $('#bill_eb_amount').val(cost.toFixed(2));
                        updateEbLineItem(cost, rate, prev, curr, units);
                    }
                }

                function updateEbLineItem(cost, rate, prev, curr, units) {
                    var $ebRow = $('#billItemsBody tr.eb-item-row');
                    if ($ebRow.length > 0) {
                        $ebRow.find('.item-amt-input').val(cost > 0 ? cost.toFixed(2) : '0.00');
                        if (units && units > 0) {
                            var unitsText = (units % 1 === 0) ? units.toFixed(0) : units.toFixed(2);
                            var prevText = (prev % 1 === 0) ? prev.toFixed(0) : prev.toFixed(2);
                            var currText = (curr % 1 === 0) ? curr.toFixed(0) : curr.toFixed(2);
                            var desc = unitsText + ' units @ ₹' + rate.toFixed(2) + '/unit (Meter: ' + prevText + ' ➔ ' + currText + ')';
                            $ebRow.find('.eb-item-desc').html('<i class="fas fa-bolt text-warning me-1"></i>' + desc);
                            $ebRow.find('input[name*="[details]"]').val(desc);
                        } else {
                            $ebRow.find('.eb-item-desc').html('<i class="fas fa-bolt text-warning me-1"></i>Billed on meter units @ ₹' + rate.toFixed(2) + '/unit');
                            $ebRow.find('input[name*="[details]"]').val('');
                        }
                    }
                    calculateInvoiceTotal();
                }

                function renderDefaultTenantItems(rent, maint, other, ebRate) {
                    var rows = '';

                    // 1. Rent (Locked - Readonly)
                    rows += '<tr class="standard-item-row" data-type="Rent">' +
                        '<td>' +
                            '<input type="text" readonly name="items[0][title]" class="form-control form-control-sm fw-semibold readonly-lock-field" value="Room Monthly Rent">' +
                            '<input type="hidden" name="items[0][type]" value="Rent">' +
                            '<small class="text-muted font-11"><i class="fas fa-lock me-1 font-10 text-muted"></i>Fixed base room accommodation</small>' +
                        '</td>' +
                        '<td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Rent</span></td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text bg-light">₹</span>' +
                                '<input type="number" step="0.01" readonly name="items[0][amount]" class="form-control form-control-sm text-end item-amt-input readonly-lock-field fw-bold" value="' + rent.toFixed(2) + '">' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<span class="badge bg-light text-muted border px-2 py-1 font-10" title="Fixed registered amount (cannot be edited)"><i class="fas fa-lock me-1"></i>Fixed</span>' +
                        '</td>' +
                    '</tr>';

                    // 2. Maintenance (Locked - Readonly)
                    rows += '<tr class="standard-item-row" data-type="Maintenance">' +
                        '<td>' +
                            '<input type="text" readonly name="items[1][title]" class="form-control form-control-sm fw-semibold readonly-lock-field" value="Maintenance Charges">' +
                            '<input type="hidden" name="items[1][type]" value="Maintenance">' +
                            '<small class="text-muted font-11"><i class="fas fa-lock me-1 font-10 text-muted"></i>Building maintenance & cleaning</small>' +
                        '</td>' +
                        '<td><span class="badge bg-info-subtle text-info border border-info-subtle">Maintenance</span></td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text bg-light">₹</span>' +
                                '<input type="number" step="0.01" readonly name="items[1][amount]" class="form-control form-control-sm text-end item-amt-input readonly-lock-field fw-bold" value="' + maint.toFixed(2) + '">' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<span class="badge bg-light text-muted border px-2 py-1 font-10" title="Fixed registered amount (cannot be edited)"><i class="fas fa-lock me-1"></i>Fixed</span>' +
                        '</td>' +
                    '</tr>';

                    // 3. Other Charges (Locked - Readonly)
                    rows += '<tr class="standard-item-row" data-type="Other">' +
                        '<td>' +
                            '<input type="text" readonly name="items[2][title]" class="form-control form-control-sm fw-semibold readonly-lock-field" value="Other Charges">' +
                            '<input type="hidden" name="items[2][type]" value="Other">' +
                            '<small class="text-muted font-11"><i class="fas fa-lock me-1 font-10 text-muted"></i>Wi-Fi, water & campus amenities</small>' +
                        '</td>' +
                        '<td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Other</span></td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text bg-light">₹</span>' +
                                '<input type="number" step="0.01" readonly name="items[2][amount]" class="form-control form-control-sm text-end item-amt-input readonly-lock-field fw-bold" value="' + other.toFixed(2) + '">' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<span class="badge bg-light text-muted border px-2 py-1 font-10" title="Fixed registered amount (cannot be edited)"><i class="fas fa-lock me-1"></i>Fixed</span>' +
                        '</td>' +
                    '</tr>';

                    // 4. Electricity (EB) (Locked - Computed via Meter Readings)
                    rows += '<tr class="eb-item-row" data-type="Electricity">' +
                        '<td>' +
                            '<input type="text" readonly name="items[3][title]" class="form-control form-control-sm fw-semibold readonly-lock-field" value="Electricity Charges (EB)">' +
                            '<input type="hidden" name="items[3][type]" value="Electricity">' +
                            '<input type="hidden" name="items[3][details]" value="">' +
                            '<small class="text-muted font-11 eb-item-desc"><i class="fas fa-bolt text-warning me-1"></i>Billed on meter units @ ₹' + ebRate.toFixed(2) + '/unit</small>' +
                        '</td>' +
                        '<td><span class="badge bg-warning-subtle text-warning border border-warning-subtle">Electricity</span></td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text bg-light">₹</span>' +
                                '<input type="number" step="0.01" readonly name="items[3][amount]" class="form-control form-control-sm text-end item-amt-input readonly-lock-field fw-bold text-success" value="0.00">' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 font-10" title="Calculated from meter consumption"><i class="fas fa-bolt me-1"></i>Meter</span>' +
                        '</td>' +
                    '</tr>';

                    $('#billItemsBody').html(rows);
                    calculateInvoiceTotal();
                }

                function populateTenantItems() {
                    var $opt = $('#bill_tenant_id').find(':selected');
                    var tenantId = $opt.val();
                    if (!tenantId) {
                        $('#billItemsBody').html('<tr><td colspan="4" class="text-center text-muted py-3"><i class="fas fa-arrow-up me-1"></i> Select a resident above to auto-populate invoice line items.</td></tr>');
                        calculateInvoiceTotal();
                        return;
                    }

                    var rent = parseFloat($opt.data('rent')) || 0;
                    var maint = parseFloat($opt.data('maint')) || 0;
                    var other = parseFloat($opt.data('other')) || 0;
                    var ebRate = parseFloat($opt.data('eb-rate')) || 10.00;
                    var ebPrev = parseFloat($opt.data('eb-prev')) || 0.00;

                    // Set EB Meter Section fields immediately
                    var prevDisplay = (ebPrev % 1 === 0) ? ebPrev.toFixed(0) : ebPrev.toFixed(2);
                    $('#bill_eb_prev_reading').val(prevDisplay);
                    $('#bill_eb_curr_reading').val('');
                    $('#bill_eb_consumed_units').val('0');
                    $('#bill_eb_amount').val('0.00');
                    $('#bill_eb_unit_rate').val(ebRate.toFixed(2));
                    $('#display_eb_rate').text('₹' + ebRate.toFixed(2));
                    $('#eb_diff_hint').text('Current - Previous').removeClass('text-danger');

                    // Pre-populate line items (1. Rent, 2. Maintenance, 3. Other, 4. EB)
                    renderDefaultTenantItems(rent, maint, other, ebRate);

                    // Also fetch live billing info via AJAX to guarantee 100% sync
                    $.ajax({
                        url: "{{ url('/admin/billing/tenants') }}/" + tenantId + "/billing-info",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.tenant) {
                                var t = res.tenant;
                                if (t.eb_previous_reading !== undefined && t.eb_previous_reading !== null) {
                                    var pt = parseFloat(t.eb_previous_reading);
                                    $('#bill_eb_prev_reading').val((pt % 1 === 0) ? pt.toFixed(0) : pt.toFixed(2));
                                }
                                if (t.eb_unit_rate !== undefined && t.eb_unit_rate !== null) {
                                    $('#bill_eb_unit_rate').val(parseFloat(t.eb_unit_rate).toFixed(2));
                                    $('#display_eb_rate').text('₹' + parseFloat(t.eb_unit_rate).toFixed(2));
                                }
                                calculateEbConsumption();
                            }
                        }
                    });
                }

                // Event bindings for dynamic items & meter calculation
                $(document).on('change', '#bill_tenant_id', populateTenantItems);
                $(document).on('keyup change input', '#bill_eb_curr_reading, #bill_eb_prev_reading, #bill_eb_unit_rate', calculateEbConsumption);
                $(document).on('keyup change input', '.item-amt-input', calculateInvoiceTotal);

                // Remove line item
                $(document).on('click', '.btn-remove-item', function() {
                    $(this).closest('tr').remove();
                    if ($('#billItemsBody tr').length === 0) {
                        $('#billItemsBody').html('<tr><td colspan="4" class="text-center text-muted py-3"><i class="fas fa-arrow-up me-1"></i> No items added. Click "+ Add Custom Item" above.</td></tr>');
                    }
                    calculateInvoiceTotal();
                });

                // Add Custom Line Item
                $(document).on('click', '#btnAddCustomItem', function(e) {
                    e.preventDefault();
                    if ($('#billItemsBody tr td[colspan]').length > 0) {
                        $('#billItemsBody').empty();
                    }
                    customItemCounter++;
                    var newRow = '<tr class="custom-item-row">' +
                        '<td>' +
                            '<input type="text" name="items[' + customItemCounter + '][title]" class="form-control form-control-sm fw-semibold" placeholder="e.g. Mess Fee, Laundry, Parking" required>' +
                            '<input type="hidden" name="items[' + customItemCounter + '][type]" value="Custom">' +
                            '<small class="text-muted font-11">Custom resident charge</small>' +
                        '</td>' +
                        '<td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Custom</span></td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text">₹</span>' +
                                '<input type="number" step="0.01" min="0" name="items[' + customItemCounter + '][amount]" class="form-control form-control-sm text-end item-amt-input" placeholder="0.00" value="0.00">' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<button type="button" class="btn btn-xs btn-outline-danger btn-remove-item" title="Remove Item"><i class="fas fa-trash-alt"></i></button>' +
                        '</td>' +
                    '</tr>';
                    $('#billItemsBody').append(newRow);
                    calculateInvoiceTotal();
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
                // 4. CREATE BILL VIA AJAX
                // =========================================================================
                $('#createBillForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnSaveBill');
                    var $spinner = $('#saveBillSpinner');
                    var $errors = $('#createBillErrors');

                    $errors.addClass('d-none').html('');
                    $form.find('.is-invalid').removeClass('is-invalid');

                    var tenantId = $form.find('#bill_tenant_id').val();
                    if (!tenantId) {
                        $errors.removeClass('d-none').html('Please select a resident to bill.');
                        $form.find('#bill_tenant_id').addClass('is-invalid').focus();
                        return false;
                    }

                    var billDate = $form.find('input[name="bill_date"]').val();
                    var dueDate = $form.find('input[name="due_date"]').val();
                    if (billDate && dueDate && dueDate < billDate) {
                        $errors.removeClass('d-none').html('Due date must be on or after the bill issue date.');
                        $form.find('input[name="due_date"]').addClass('is-invalid').focus();
                        return false;
                    }

                    var prevEb = parseFloat($('#bill_eb_prev_reading').val() || 0);
                    var currEbVal = $('#bill_eb_curr_reading').val();
                    if (currEbVal !== '' && !isNaN(parseFloat(currEbVal))) {
                        var currEb = parseFloat(currEbVal);
                        if (currEb < prevEb) {
                            $errors.removeClass('d-none').html('Current meter reading (' + currEb + ') cannot be less than previous reading (' + prevEb + ').');
                            $('#bill_eb_curr_reading').addClass('is-invalid').focus();
                            return false;
                        }
                    }

                    var totalAmt = parseFloat($('#bill_amount_input').val());
                    if (isNaN(totalAmt) || totalAmt < 1) {
                        $errors.removeClass('d-none').html('Invoice total amount must be at least ₹1.00.');
                        return false;
                    }

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
                                $form.find('.is-invalid').removeClass('is-invalid');
                                $('#billItemsBody').html('<tr><td colspan="4" class="text-center text-muted py-3"><i class="fas fa-arrow-up me-1"></i> Select a resident above to auto-populate invoice line items.</td></tr>');
                                $('#displayInvoiceTotal').text('₹0.00');
                                $('#bill_amount_input').val('0.00');
                                $('#immediate_payment_fields').addClass('d-none');
                                billsTable.ajax.reload(null, false);
                                paymentsTable.ajax.reload(null, false);
                                if (res.stats) {
                                    updateStatsCards(res.stats);
                                } else {
                                    reloadStats();
                                }
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var msg = 'An error occurred while creating the invoice.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                $.each(xhr.responseJSON.errors, function(field, messages) {
                                    $form.find('[name="' + field + '"]').addClass('is-invalid');
                                });
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
                                if (res.stats) {
                                    updateStatsCards(res.stats);
                                } else {
                                    reloadStats();
                                }
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
                    $('#pay_amount_input').val(due).data('max-due', due).attr('max', due).removeClass('is-invalid');

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
                    $('#recordPaymentForm').find('.is-invalid').removeClass('is-invalid');

                    var amount = parseFloat($('#pay_amount_input').val());
                    var maxDue = parseFloat($('#pay_amount_input').data('max-due') || 0);

                    if (isNaN(amount) || amount < 1) {
                        $errors.removeClass('d-none').html('Payment amount must be at least ₹1.00.');
                        $('#pay_amount_input').addClass('is-invalid').focus();
                        return false;
                    }

                    if (maxDue > 0 && amount > (maxDue + 0.01)) {
                        $errors.removeClass('d-none').html('Payment amount (₹' + amount.toFixed(2) + ') cannot exceed the pending due of ₹' + maxDue.toFixed(2) + '.');
                        $('#pay_amount_input').addClass('is-invalid').focus();
                        return false;
                    }

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
                                $('#recordPaymentForm').find('.is-invalid').removeClass('is-invalid');
                                billsTable.ajax.reload(null, false);
                                paymentsTable.ajax.reload(null, false);
                                if (res.stats) {
                                    updateStatsCards(res.stats);
                                } else {
                                    reloadStats();
                                }
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var msg = 'Failed to record payment.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                $.each(xhr.responseJSON.errors, function(field, messages) {
                                    $('#recordPaymentForm').find('[name="' + field + '"]').addClass('is-invalid');
                                });
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

                                if (b.items && Array.isArray(b.items) && b.items.length > 0) {
                                    var itemRows = '';
                                    b.items.forEach(function(item, idx) {
                                        var detailsHtml = item.details ? '<div class="text-muted small font-11">' + item.details + '</div>' : '';
                                        itemRows += '<tr>' +
                                            '<td class="text-muted">' + (idx + 1) + '</td>' +
                                            '<td><strong>' + (item.title || item.name || 'Item') + '</strong>' + detailsHtml + '</td>' +
                                            '<td>' + b.billing_month + '</td>' +
                                            '<td class="text-end fw-bold font-13">₹' + parseFloat(item.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '</td>' +
                                        '</tr>';
                                    });
                                    $('#inv_items_tbody').html(itemRows);
                                } else {
                                    var fallbackRow = '<tr>' +
                                        '<td class="text-muted">1</td>' +
                                        '<td><strong>' + b.bill_type + '</strong><div class="text-muted small font-11">' + (b.notes ? b.notes : 'Charges for residence facilities') + '</div></td>' +
                                        '<td>' + b.billing_month + '</td>' +
                                        '<td class="text-end fw-bold font-13">₹' + f.amount + '</td>' +
                                    '</tr>';
                                    $('#inv_items_tbody').html(fallbackRow);
                                }

                                if (b.eb_consumed_units !== null && parseFloat(b.eb_consumed_units) > 0) {
                                    $('#inv_eb_details_badge').html(
                                        '<div class="p-2 mb-3 bg-warning-subtle border border-warning rounded d-flex justify-content-between align-items-center font-12">' +
                                            '<div><i class="fas fa-bolt text-warning me-1"></i> <strong>Electricity Meter Reading:</strong> Previous: ' + (f.eb_previous_reading || '0.00') + ' | Current: ' + (f.eb_current_reading || '0.00') + '</div>' +
                                            '<div><strong>Consumed:</strong> ' + (f.eb_consumed_units || '0.00') + ' units @ ₹' + (f.eb_unit_rate || '10.00') + '/unit = <span class="text-success fw-bold">₹' + (f.eb_amount || '0.00') + '</span></div>' +
                                        '</div>'
                                    ).removeClass('d-none');
                                } else {
                                    $('#inv_eb_details_badge').addClass('d-none').html('');
                                }

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

                                window.currentActiveInvoice = {
                                    id: b.id,
                                    billNumber: b.bill_number,
                                    tenantName: b.tenant ? b.tenant.name : 'Resident',
                                    phone: b.tenant && b.tenant.phone ? b.tenant.phone : '',
                                    room: b.room ? 'Room ' + b.room.room_number : (b.tenant ? 'Room ' + b.tenant.room_number : '-'),
                                    month: b.billing_month,
                                    billType: b.bill_type,
                                    amount: '₹' + f.amount,
                                    balance: '₹' + f.due_amount,
                                    dueDate: f.due_date,
                                    status: b.status.toUpperCase(),
                                    upiId: '{{ $settings["upi_id"] ?? "srmresidence@okhdfcbank" }}',
                                    url: "{{ url('/admin/billing/bills') }}/" + b.id + "/print"
                                };

                                $('#btn_open_print_invoice').attr('href', window.currentActiveInvoice.url);
                                $('#btn_send_modal_invoice_email').data('id', b.id).data('bill', b.bill_number).data('email', b.tenant && b.tenant.email ? b.tenant.email : '');
                                $('#viewInvoiceModal').modal('show');
                            }
                        }
                    });
                });

                // =========================================================================
                // 7.1 SEND INVOICE VIA EMAIL HANDLER
                // =========================================================================
                function triggerSendInvoiceEmail(billId, billNumber, defaultEmail) {
                    Swal.fire({
                        title: 'Send Invoice via Email',
                        html: 'Send Rent Invoice <strong>#' + billNumber + '</strong> directly to resident email:<br><br>' +
                              '<input id="swal_invoice_email" class="swal2-input font-14" placeholder="Resident Email Address" value="' + (defaultEmail || '') + '">',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0a5858',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Send Email',
                        preConfirm: () => {
                            const email = document.getElementById('swal_invoice_email').value;
                            if (!email || !email.includes('@')) {
                                Swal.showValidationMessage('Please enter a valid email address');
                                return false;
                            }
                            return email;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const targetEmail = result.value;
                            Swal.fire({
                                title: 'Sending Invoice...',
                                html: 'Delivering invoice email to <strong>' + targetEmail + '</strong> via SMTP...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: "{{ url('/admin/billing/bills') }}/" + billId + "/send-email",
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    email: targetEmail
                                },
                                success: function(res) {
                                    if (res.success) {
                                        Swal.fire({
                                            title: 'Email Sent!',
                                            text: res.message,
                                            icon: 'success',
                                            confirmButtonColor: '#0a5858'
                                        });
                                    } else {
                                        Swal.fire('Failed', res.message || 'Could not send invoice email.', 'error');
                                    }
                                },
                                error: function(xhr) {
                                    var errMsg = 'Failed to send invoice email.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errMsg = xhr.responseJSON.message;
                                    }
                                    Swal.fire('Error', errMsg, 'error');
                                }
                            });
                        }
                    });
                }

                $(document).on('click', '.btn-send-invoice-mail', function() {
                    var billId = $(this).data('id');
                    var billNumber = $(this).data('bill');
                    var email = $(this).data('email');
                    triggerSendInvoiceEmail(billId, billNumber, email);
                });

                $(document).on('click', '#btn_send_modal_invoice_email', function() {
                    var billId = $(this).data('id');
                    var billNumber = $(this).data('bill');
                    var email = $(this).data('email');
                    triggerSendInvoiceEmail(billId, billNumber, email);
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

                                window.currentActiveReceipt = {
                                    id: p.id,
                                    receiptNumber: p.payment_number,
                                    tenantName: p.tenant ? p.tenant.name : 'Resident',
                                    phone: p.tenant && p.tenant.phone ? p.tenant.phone : '',
                                    room: room,
                                    billNumber: p.bill ? p.bill.bill_number : '-',
                                    billType: p.bill ? p.bill.bill_type : 'Rent/Utility',
                                    amount: '₹' + f.amount,
                                    method: p.payment_method,
                                    txId: p.transaction_id || '-',
                                    date: f.payment_date,
                                    url: "{{ url('/admin/billing/payments') }}/" + p.id + "/print"
                                };

                                $('#btn_open_print_receipt').attr('href', window.currentActiveReceipt.url);
                                $('#viewReceiptModal').modal('show');
                            }
                        }
                    });
                });

                // =========================================================================
                // 8.1 SHARE INVOICE & RECEIPT AS IMAGE OR PDF (NO LINK, ATTACHED FILE)
                // =========================================================================
                function formatInvoiceShareMsg(data, type) {
                    const ext = (type || 'PDF').toUpperCase();
                    return `🏠 *{{ addslashes($settings['residence_name'] ?? 'SRM RESIDENCE') }} - OFFICIAL RENT INVOICE*\n` +
                           `━━━━━━━━━━━━━━━━━━━━━━━\n` +
                           `📄 *Invoice No:* ${data.billNumber}\n` +
                           `👤 *Resident:* ${data.tenantName} (${data.room})\n` +
                           `📅 *Billing Month:* ${data.month}\n` +
                           `📌 *Type:* ${data.billType}\n` +
                           `💰 *Total Amount:* ${data.amount}\n` +
                           `⏳ *Net Due Balance:* ${data.balance}\n` +
                           `⏰ *Due Date:* ${data.dueDate}\n` +
                           `💳 *Instant Pay UPI ID:* ${data.upiId || '{{ $settings["upi_id"] ?? "srmresidence@okhdfcbank" }}'}\n` +
                           `━━━━━━━━━━━━━━━━━━━━━━━\n` +
                           `📑 *Attached: Official Rent Invoice (${ext})*`;
                }

                function formatReceiptShareMsg(data, type) {
                    const ext = (type || 'PDF').toUpperCase();
                    return `🧾 *{{ addslashes($settings['residence_name'] ?? 'SRM RESIDENCE') }} - PAYMENT RECEIPT*\n` +
                           `━━━━━━━━━━━━━━━━━━━━━━━\n` +
                           `✅ *Payment Confirmed & Credited*\n` +
                           `📄 *Receipt No:* ${data.receiptNumber}\n` +
                           `👤 *Resident:* ${data.tenantName} (${data.room})\n` +
                           `📄 *For Invoice:* ${data.billNumber}\n` +
                           `💵 *Amount Received:* ${data.amount}\n` +
                           `💳 *Payment Mode:* ${data.method}\n` +
                           `🔖 *Ref / Tx ID:* ${data.txId}\n` +
                           `📅 *Payment Date:* ${data.date}\n` +
                           `━━━━━━━━━━━━━━━━━━━━━━━\n` +
                           `📑 *Attached: Official Payment Receipt (${ext})*`;
                }

                async function shareElementAsFile(el, type, shareData, baseFileName, $btn) {
                    if (!el) return;
                    var originalHtml = $btn ? $btn.html() : '';
                    if ($btn) {
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Generating ' + type.toUpperCase() + '...');
                    }

                    try {
                        var canvas = await html2canvas(el, {
                            scale: 2,
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: '#ffffff'
                        });

                        var file = null;
                        var fileName = '';

                        if (type === 'pdf') {
                            fileName = baseFileName + '.pdf';
                            var { jsPDF } = window.jspdf;
                            var pdf = new jsPDF('p', 'mm', 'a4');
                            var pageWidth = pdf.internal.pageSize.getWidth();
                            var pageHeight = (canvas.height * pageWidth) / canvas.width;
                            var imgData = canvas.toDataURL('image/jpeg', 0.95);
                            pdf.addImage(imgData, 'JPEG', 0, 0, pageWidth, pageHeight, undefined, 'FAST');
                            var blob = pdf.output('blob');
                            file = new File([blob], fileName, { type: 'application/pdf' });
                        } else {
                            fileName = baseFileName + '.png';
                            var blob = await new Promise(function(resolve) { canvas.toBlob(resolve, 'image/png'); });
                            file = new File([blob], fileName, { type: 'image/png' });
                        }

                        if (navigator.canShare && navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: shareData.title,
                                text: shareData.text,
                                files: [file]
                            });
                        } else {
                            // Fallback for Desktop / unsupported browsers:
                            var downloadLink = document.createElement('a');
                            downloadLink.href = URL.createObjectURL(file);
                            downloadLink.download = fileName;
                            document.body.appendChild(downloadLink);
                            downloadLink.click();
                            document.body.removeChild(downloadLink);

                            var cleanPhone = (shareData.phone || '').replace(/[^0-9]/g, '');
                            if (cleanPhone.length >= 10) {
                                cleanPhone = cleanPhone.length === 10 ? '91' + cleanPhone : cleanPhone;
                                var waUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(shareData.text)}`;
                            } else {
                                var waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareData.text)}`;
                            }
                            window.open(waUrl, '_blank');

                            Swal.fire({
                                icon: 'success',
                                title: 'Invoice ' + type.toUpperCase() + ' Downloaded!',
                                html: 'File saved to your Downloads.<br>Opening WhatsApp... Please attach the downloaded file in the chat.',
                                confirmButtonColor: '#0a5858'
                            });
                        }
                    } catch (err) {
                        if (err.name !== 'AbortError') {
                            console.error('File share error:', err);
                            toastr.error('Could not share file: ' + (err.message || err));
                        }
                    } finally {
                        if ($btn) {
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    }
                }

                // Modal Share Handlers
                $(document).on('click', '#btn_modal_share_invoice_pdf', function() {
                    if (!window.currentActiveInvoice) return;
                    var inv = window.currentActiveInvoice;
                    var shareData = {
                        title: 'Rent Invoice ' + inv.billNumber,
                        text: formatInvoiceShareMsg(inv, 'pdf'),
                        phone: inv.phone
                    };
                    shareElementAsFile(document.getElementById('printableInvoiceArea'), 'pdf', shareData, 'Invoice_' + inv.billNumber, $(this));
                });

                $(document).on('click', '#btn_modal_share_invoice_image', function() {
                    if (!window.currentActiveInvoice) return;
                    var inv = window.currentActiveInvoice;
                    var shareData = {
                        title: 'Rent Invoice ' + inv.billNumber,
                        text: formatInvoiceShareMsg(inv, 'image'),
                        phone: inv.phone
                    };
                    shareElementAsFile(document.getElementById('printableInvoiceArea'), 'image', shareData, 'Invoice_' + inv.billNumber, $(this));
                });

                $(document).on('click', '#btn_modal_share_receipt_pdf', function() {
                    if (!window.currentActiveReceipt) return;
                    var rcp = window.currentActiveReceipt;
                    var shareData = {
                        title: 'Payment Receipt ' + rcp.receiptNumber,
                        text: formatReceiptShareMsg(rcp, 'pdf'),
                        phone: rcp.phone
                    };
                    shareElementAsFile(document.getElementById('printableReceiptArea'), 'pdf', shareData, 'Receipt_' + rcp.receiptNumber, $(this));
                });

                $(document).on('click', '#btn_modal_share_receipt_image', function() {
                    if (!window.currentActiveReceipt) return;
                    var rcp = window.currentActiveReceipt;
                    var shareData = {
                        title: 'Payment Receipt ' + rcp.receiptNumber,
                        text: formatReceiptShareMsg(rcp, 'image'),
                        phone: rcp.phone
                    };
                    shareElementAsFile(document.getElementById('printableReceiptArea'), 'image', shareData, 'Receipt_' + rcp.receiptNumber, $(this));
                });

                // Helper to copy URL to clipboard
                function copyUrlToClipboard(url) {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(function() {
                            Toast.fire({ icon: 'success', title: 'Invoice link copied to clipboard!' });
                        }).catch(function() {
                            fallbackCopy(url);
                        });
                    } else {
                        fallbackCopy(url);
                    }
                }

                function fallbackCopy(text) {
                    var tempInput = document.createElement('input');
                    tempInput.value = text;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    Toast.fire({ icon: 'success', title: 'Invoice link copied to clipboard!' });
                }

                // =========================================================================
                // UNIFIED SHARE INVOICE HUB LOGIC
                // =========================================================================
                var activeHubBill = null;

                $(document).on('click', '.btn-open-share-hub', function() {
                    var $el = $(this);
                    activeHubBill = {
                        id: $el.data('id'),
                        billNumber: $el.data('bill') || 'Invoice',
                        tenant: $el.data('tenant') || 'Tenant',
                        phone: ($el.data('phone') || '').toString().replace(/[^0-9]/g, ''),
                        email: $el.data('email') || '',
                        room: $el.data('room') || '-',
                        month: $el.data('month') || '',
                        type: $el.data('type') || 'Monthly Rent',
                        amount: $el.data('amount') || '0.00',
                        due: $el.data('due') || '0.00',
                        dueDate: $el.data('duedate') || '-',
                        url: $el.data('url') || window.location.href
                    };

                    $('#hub_invoice_subtitle').text(activeHubBill.billNumber + ' • ' + activeHubBill.tenant);
                    $('#hub_tenant_name').text(activeHubBill.tenant);
                    $('#hub_bill_type').text(activeHubBill.type);
                    $('#hub_room_no').text(activeHubBill.room);
                    $('#hub_month').text(activeHubBill.month);
                    $('#hub_total_amt').text('₹' + activeHubBill.amount);
                    $('#hub_due_amt').text('₹' + activeHubBill.due);
                    $('#hub_invoice_url_input').val(activeHubBill.url);

                    if (activeHubBill.phone && activeHubBill.phone.length >= 10) {
                        $('#hub_whatsapp_desc').text('Send to ' + activeHubBill.tenant + ' (' + activeHubBill.phone + ')');
                    } else {
                        $('#hub_whatsapp_desc').text('Send formatted invoice summary & link to tenant WhatsApp');
                    }

                    if (activeHubBill.email) {
                        $('#hub_email_desc').text('Send to ' + activeHubBill.email);
                    } else {
                        $('#hub_email_desc').text('Email computerized invoice statement to tenant');
                    }

                    $('#shareInvoiceHubModal').modal('show');
                });

                // 1. WhatsApp Button Click in Hub
                $(document).on('click', '#btnHubWhatsApp', function() {
                    if (!activeHubBill) return;
                    var b = activeHubBill;
                    var msg = `*SRM RESIDENCE - INVOICE*\n` +
                              `Dear ${b.tenant},\n\n` +
                              `Your invoice for *${b.month}* has been issued.\n\n` +
                              `📄 *Invoice No:* ${b.billNumber}\n` +
                              `🏠 *Room / Bed:* ${b.room}\n` +
                              `📋 *Bill Type:* ${b.type}\n` +
                              `💰 *Total Amount:* ₹${b.amount}\n` +
                              `⚠️ *Balance Due:* ₹${b.due}\n` +
                              `📅 *Due Date:* ${b.dueDate}\n\n` +
                              `🔗 *View & Download Invoice:* \n${b.url}\n\n` +
                              `Thank you,\n*SRM Residence*`;

                    var waUrl;
                    if (b.phone && b.phone.length >= 10) {
                        var cleanPhone = b.phone.length === 10 ? '91' + b.phone : b.phone;
                        waUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(msg)}`;
                    } else {
                        waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
                    }
                    window.open(waUrl, '_blank');
                });

                // 2. Native Share / Copy Link in Hub
                $(document).on('click', '#btnHubNativeShare', function() {
                    if (!activeHubBill) return;
                    var b = activeHubBill;
                    var shareText = `SRM Residence Invoice #${b.billNumber} for ${b.tenant} (${b.month}). Total: ₹${b.amount}, Due: ₹${b.due}.\n${b.url}`;

                    if (navigator.share) {
                        navigator.share({
                            title: 'SRM Residence Invoice - ' + b.billNumber,
                            text: shareText,
                            url: b.url
                        }).catch(function(err) {
                            if (err.name !== 'AbortError') {
                                copyUrlToClipboard(b.url);
                            }
                        });
                    } else {
                        copyUrlToClipboard(b.url);
                    }
                });

                // 3. Print A4 in Hub
                $(document).on('click', '#btnHubPrintA4', function() {
                    if (!activeHubBill) return;
                    window.open(activeHubBill.url, '_blank');
                });

                // 4. Send Email in Hub
                $(document).on('click', '#btnHubEmail', function() {
                    if (!activeHubBill) return;
                    var b = activeHubBill;
                    $('#shareInvoiceHubModal').modal('hide');
                    triggerSendInvoiceEmail(b.id, b.billNumber, b.email);
                });

                // 5. Copy URL in Hub
                $(document).on('click', '#btnHubCopyUrl', function() {
                    if (!activeHubBill) return;
                    copyUrlToClipboard(activeHubBill.url);
                });

                // Backward-compatibility DataTable Row Quick Share Handlers
                $(document).on('click', '.btn-table-share-invoice', function() {
                    var billId = $(this).data('id');
                    $('.btn-open-share-hub[data-id="' + billId + '"]').trigger('click');
                });

                $(document).on('click', '.btn-table-share-receipt', function() {
                    var payId = $(this).data('id');
                    // Trigger receipt modal where PDF & Image share buttons are available
                    $('.btn-view-receipt[data-id="' + payId + '"]').trigger('click');
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
                                if (res.stats) {
                                    updateStatsCards(res.stats);
                                } else {
                                    reloadStats();
                                }
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
                                        if (res.stats) {
                                            updateStatsCards(res.stats);
                                        } else {
                                            reloadStats();
                                        }
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
                                        if (res.stats) {
                                            updateStatsCards(res.stats);
                                        } else {
                                            reloadStats();
                                        }
                                        Toast.fire({ icon: 'success', title: res.message });
                                    }
                                }
                            });
                        }
                    });
                });

                // =========================================================================
                // BULK SELECTION, BULK EDIT & BULK DELETE FOR INVOICES (BILLS)
                // =========================================================================
                function updateBulkBarBills() {
                    var checkedBoxes = $('#billsTable tbody .bulk-select-row:checked');
                    var count = checkedBoxes.length;
                    var totalBoxes = $('#billsTable tbody .bulk-select-row').length;

                    if (count > 0) {
                        $('#bulkSelectedCountBills').text(count + ' Selected');
                        $('#bulkActionBarBills').removeClass('d-none').addClass('d-flex');
                    } else {
                        $('#bulkActionBarBills').removeClass('d-flex').addClass('d-none');
                    }

                    if (totalBoxes > 0 && count === totalBoxes) {
                        $('#bulkSelectAllBills').prop('checked', true).prop('indeterminate', false);
                    } else if (count > 0) {
                        $('#bulkSelectAllBills').prop('checked', false).prop('indeterminate', true);
                    } else {
                        $('#bulkSelectAllBills').prop('checked', false).prop('indeterminate', false);
                    }
                }

                $(document).on('change', '#bulkSelectAllBills', function() {
                    var isChecked = $(this).is(':checked');
                    $('#billsTable tbody .bulk-select-row').prop('checked', isChecked);
                    updateBulkBarBills();
                });

                $(document).on('change', '#billsTable tbody .bulk-select-row', function() {
                    updateBulkBarBills();
                });

                billsTable.on('draw', function() {
                    updateBulkBarBills();
                });

                $(document).on('click', '.btn-bulk-deselect-bills', function() {
                    $('#billsTable tbody .bulk-select-row').prop('checked', false);
                    $('#bulkSelectAllBills').prop('checked', false).prop('indeterminate', false);
                    updateBulkBarBills();
                });

                // Bulk Delete Bills
                $(document).on('click', '.btn-bulk-delete-bills', function() {
                    var ids = [];
                    $('#billsTable tbody .bulk-select-row:checked').each(function() {
                        ids.push($(this).val());
                    });

                    if (ids.length === 0) {
                        Toast.fire({ icon: 'warning', title: 'No invoices selected.' });
                        return;
                    }

                    Swal.fire({
                        title: "Delete " + ids.length + " Invoice(s)?",
                        text: "Are you sure you want to delete these " + ids.length + " selected invoice(s) and any recorded payments? This action cannot be undone.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f34e4e",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete " + ids.length + " invoices"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "POST",
                                url: "{{ route('billing.bills.bulk_delete') }}",
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
                                        $('#bulkSelectAllBills').prop('checked', false);
                                        billsTable.ajax.reload(null, false);
                                        paymentsTable.ajax.reload(null, false);
                                        if (res.stats) updateStatsCards(res.stats);
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

                // Bulk Edit Bills
                $(document).on('click', '.btn-bulk-edit-bills', function() {
                    var count = $('#billsTable tbody .bulk-select-row:checked').length;
                    if (count === 0) {
                        Toast.fire({ icon: 'warning', title: 'No invoices selected.' });
                        return;
                    }
                    $('#bulkEditCountBadgeBills').text(count + ' selected');
                    $('#bulkEditBillForm')[0].reset();
                    $('#bulkEditBillErrors').addClass('d-none').html('');
                    $('#bulkEditBillModal').modal('show');
                });

                $('#bulkEditBillForm').on('submit', function(e) {
                    e.preventDefault();
                    var ids = [];
                    $('#billsTable tbody .bulk-select-row:checked').each(function() {
                        ids.push($(this).val());
                    });

                    if (ids.length === 0) {
                        Toast.fire({ icon: 'warning', title: 'No invoices selected.' });
                        return;
                    }

                    var formData = $(this).serializeArray();
                    formData.push({ name: '_token', value: "{{ csrf_token() }}" });
                    ids.forEach(function(id) {
                        formData.push({ name: 'ids[]', value: id });
                    });

                    var $btn = $('#btnApplyBulkEditBills');
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

                    $.ajax({
                        type: "POST",
                        url: "{{ route('billing.bills.bulk_update') }}",
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Apply Changes');
                            if (res.success) {
                                $('#bulkEditBillModal').modal('hide');
                                Toast.fire({ icon: 'success', title: res.message });
                                $('#bulkSelectAllBills').prop('checked', false);
                                billsTable.ajax.reload(null, false);
                                if (res.stats) updateStatsCards(res.stats);
                            } else {
                                $('#bulkEditBillErrors').removeClass('d-none').html(res.message || 'Bulk update failed.');
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Apply Changes');
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update selected invoices.';
                            $('#bulkEditBillErrors').removeClass('d-none').html(msg);
                        }
                    });
                });

                // =========================================================================
                // BULK SELECTION & BULK DELETE FOR PAYMENT RECEIPTS
                // =========================================================================
                function updateBulkBarPayments() {
                    var checkedBoxes = $('#paymentsTable tbody .bulk-select-row:checked');
                    var count = checkedBoxes.length;
                    var totalBoxes = $('#paymentsTable tbody .bulk-select-row').length;

                    if (count > 0) {
                        $('#bulkSelectedCountPayments').text(count + ' Selected');
                        $('#bulkActionBarPayments').removeClass('d-none').addClass('d-flex');
                    } else {
                        $('#bulkActionBarPayments').removeClass('d-flex').addClass('d-none');
                    }

                    if (totalBoxes > 0 && count === totalBoxes) {
                        $('#bulkSelectAllPayments').prop('checked', true).prop('indeterminate', false);
                    } else if (count > 0) {
                        $('#bulkSelectAllPayments').prop('checked', false).prop('indeterminate', true);
                    } else {
                        $('#bulkSelectAllPayments').prop('checked', false).prop('indeterminate', false);
                    }
                }

                $(document).on('change', '#bulkSelectAllPayments', function() {
                    var isChecked = $(this).is(':checked');
                    $('#paymentsTable tbody .bulk-select-row').prop('checked', isChecked);
                    updateBulkBarPayments();
                });

                $(document).on('change', '#paymentsTable tbody .bulk-select-row', function() {
                    updateBulkBarPayments();
                });

                paymentsTable.on('draw', function() {
                    updateBulkBarPayments();
                });

                $(document).on('click', '.btn-bulk-deselect-payments', function() {
                    $('#paymentsTable tbody .bulk-select-row').prop('checked', false);
                    $('#bulkSelectAllPayments').prop('checked', false).prop('indeterminate', false);
                    updateBulkBarPayments();
                });

                // Bulk Delete Payments
                $(document).on('click', '.btn-bulk-delete-payments', function() {
                    var ids = [];
                    $('#paymentsTable tbody .bulk-select-row:checked').each(function() {
                        ids.push($(this).val());
                    });

                    if (ids.length === 0) {
                        Toast.fire({ icon: 'warning', title: 'No payment receipts selected.' });
                        return;
                    }

                    Swal.fire({
                        title: "Delete " + ids.length + " Payment Receipt(s)?",
                        text: "Are you sure you want to delete these " + ids.length + " selected payment receipt(s)? The related invoices will have their paid amounts and balances recalculated.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f34e4e",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete " + ids.length + " receipts"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "POST",
                                url: "{{ route('billing.payments.bulk_delete') }}",
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
                                        $('#bulkSelectAllPayments').prop('checked', false);
                                        paymentsTable.ajax.reload(null, false);
                                        billsTable.ajax.reload(null, false);
                                        if (res.stats) updateStatsCards(res.stats);
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

            });
        </script>
    @endpush
@endsection

