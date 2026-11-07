@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <style>
            .res-report-card {
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                background: #ffffff;
                padding: 1.25rem 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                position: relative;
                overflow: hidden;
            }
            .res-report-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            }
            .res-report-card .card-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 22px;
            }
            .filter-card {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            .filter-label {
                font-size: 0.8rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #64748b;
                margin-bottom: 0.35rem;
            }
            .report-nav-pills .nav-link {
                font-weight: 600;
                color: #475569;
                border-radius: 10px;
                padding: 0.65rem 1.25rem;
                margin-right: 0.5rem;
                background: #f1f5f9;
                border: 1px solid transparent;
                transition: all 0.2s ease;
            }
            .report-nav-pills .nav-link:hover {
                background: #e2e8f0;
                color: #0f172a;
            }
            .report-nav-pills .nav-link.active {
                background: #0ea1b1;
                color: #ffffff;
                box-shadow: 0 4px 10px rgba(14, 161, 177, 0.25);
            }
            .data-table-card {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
                padding: 1.5rem;
            }
            .table-responsive {
                border-radius: 10px;
            }
            .chart-box {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
                padding: 1.25rem;
                margin-bottom: 1.5rem;
            }
            @media print {
                .navbar, .left-side-menu, .filter-card, .report-nav-pills, .btn, .dataTables_filter, .dataTables_length, .dataTables_paginate, .dataTables_info, .card-header-actions, .no-print {
                    display: none !important;
                }
                .content-page, .content, .container-fluid {
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }
                .data-table-card, .res-report-card {
                    box-shadow: none !important;
                    border: 1px solid #ddd !important;
                }
                .tab-pane {
                    display: block !important;
                    opacity: 1 !important;
                }
            }
        </style>
    @endpush

    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h4 class="page-title-main mb-1">
                <i class="fas fa-chart-line text-primary me-2"></i>Reports & Financial Ledger
            </h4>
            <p class="text-muted mb-0 font-13">
                Comprehensive multi-criteria reporting, audit records, and analytics for SRM Residence.
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0 no-print">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary" id="btnExportCsv" title="Export current filtered records to CSV">
                    <i class="fas fa-file-excel me-1"></i> Export CSV
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()" title="Print Summary Report">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button type="button" class="btn btn-outline-info" id="btnToggleCharts" title="Toggle Visual Charts">
                    <i class="fas fa-chart-pie me-1"></i> <span id="toggleChartsText">Show Charts</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Live KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Invoiced -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="res-report-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted font-12 fw-semibold text-uppercase">Total Invoiced</span>
                    <div class="card-icon bg-primary-subtle text-primary">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-dark mb-1" id="kpiTotalInvoiced">₹0</h4>
                <small class="text-muted font-11" id="kpiBillsCount">0 Invoices</small>
            </div>
        </div>

        <!-- Collected -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="res-report-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted font-12 fw-semibold text-uppercase">Total Collected</span>
                    <div class="card-icon bg-success-subtle text-success">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-success mb-1" id="kpiTotalCollected">₹0</h4>
                <small class="text-muted font-11">Rate: <span id="kpiCollectionRate" class="fw-bold text-success">0%</span></small>
            </div>
        </div>

        <!-- Outstanding Dues -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="res-report-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted font-12 fw-semibold text-uppercase">Pending Dues</span>
                    <div class="card-icon bg-warning-subtle text-warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-warning mb-1" id="kpiOutstandingDues">₹0</h4>
                <small class="text-muted font-11" id="kpiPendingBillsCount">0 Pending</small>
            </div>
        </div>

        <!-- Overdue -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="res-report-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted font-12 fw-semibold text-uppercase">Overdue Dues</span>
                    <div class="card-icon bg-danger-subtle text-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-danger mb-1" id="kpiOverdueAmount">₹0</h4>
                <small class="text-danger font-11 fw-semibold" id="kpiOverdueCount">0 Overdue Bills</small>
            </div>
        </div>

        <!-- Expenses -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="res-report-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted font-12 fw-semibold text-uppercase">Total Expenses</span>
                    <div class="card-icon bg-purple-subtle text-purple" style="background-color: #f3e8ff; color: #9333ea;">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-purple mb-1" style="color: #9333ea;" id="kpiTotalExpenses">₹0</h4>
                <small class="text-muted font-11" id="kpiExpensesCount">0 Records</small>
            </div>
        </div>

        <!-- Net Cashflow & Occupancy -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="res-report-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted font-12 fw-semibold text-uppercase">Net Balance</span>
                    <div class="card-icon bg-info-subtle text-info">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1" id="kpiNetCashflow">₹0</h4>
                <small class="text-muted font-11">Occupancy: <span id="kpiOccupancyRate" class="fw-bold text-dark">0%</span></small>
            </div>
        </div>
    </div>

    <!-- Active Portfolio Rent & Deposits Sub-bar -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted font-11 text-uppercase fw-bold d-block">Scheduled Rent Roll</span>
                    <span class="fs-6 fw-bold text-dark" id="kpiScheduledDues">₹0 <small class="text-muted fw-normal font-11">/ mo</small></span>
                </div>
                <span class="badge bg-primary-subtle text-primary p-2"><i class="fas fa-hand-holding-dollar fa-lg"></i></span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted font-11 text-uppercase fw-bold d-block">Base Monthly Rent</span>
                    <span class="fs-6 fw-bold text-success" id="kpiMonthlyRent">₹0 <small class="text-muted fw-normal font-11">/ mo</small></span>
                </div>
                <span class="badge bg-success-subtle text-success p-2"><i class="fas fa-money-bill-wave fa-lg"></i></span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted font-11 text-uppercase fw-bold d-block">Security Deposits Held</span>
                    <span class="fs-6 fw-bold text-purple" style="color: #8b5cf6;" id="kpiSecurityDeposits">₹0</span>
                </div>
                <span class="badge p-2" style="background-color: #f3e8ff; color: #8b5cf6;"><i class="fas fa-shield-alt fa-lg"></i></span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted font-11 text-uppercase fw-bold d-block">Inventory Rent Capacity</span>
                    <span class="fs-6 fw-bold text-info" id="kpiRoomRentCapacity">₹0 <small class="text-muted fw-normal font-11">/ mo</small></span>
                </div>
                <span class="badge bg-info-subtle text-info p-2"><i class="fas fa-building fa-lg"></i></span>
            </div>
        </div>
    </div>

    <!-- Visual Charts Container (Initially Hidden, Toggleable) -->
    <div id="chartsContainer" class="row mb-4 no-print" style="display: none;">
        <div class="col-lg-8">
            <div class="chart-box">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Monthly Financial Trends (Last 6 Months)</h5>
                    <span class="badge bg-light text-secondary border">Invoiced vs Collected vs Expenses</span>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-box">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-pie-chart text-info me-2"></i>Bill Status Split</h5>
                    <span class="badge bg-light text-secondary border">Distribution</span>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="billStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Filter Suite -->
    <div class="filter-card no-print">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="fas fa-sliders-h text-primary me-2"></i>Filter Records
            </h5>
            <span class="badge bg-light text-muted border px-2 py-1 font-11">Filters apply immediately across tables & KPI cards</span>
        </div>

        <form id="reportFilterForm" class="row g-2 align-items-end">
            <!-- Date Range Preset -->
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label class="filter-label"><i class="far fa-calendar-alt me-1"></i>Date Range</label>
                <select id="filterDateRange" class="form-select form-select-sm">
                    <option value="all" selected>All Time</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Financial Year</option>
                    <option value="custom">Custom Date Range</option>
                </select>
            </div>

            <!-- Custom Start Date -->
            <div class="col-lg-2 col-md-3 col-sm-6 custom-date-col" style="display: none;">
                <label class="filter-label">Start Date</label>
                <input type="text" id="filterStartDate" class="form-control form-select-sm flatpickr-input" placeholder="YYYY-MM-DD">
            </div>

            <!-- Custom End Date -->
            <div class="col-lg-2 col-md-3 col-sm-6 custom-date-col" style="display: none;">
                <label class="filter-label">End Date</label>
                <input type="text" id="filterEndDate" class="form-control form-select-sm flatpickr-input" placeholder="YYYY-MM-DD">
            </div>

            <!-- Room Selector (Numerically Sorted) -->
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label class="filter-label"><i class="fas fa-door-closed me-1"></i>Room</label>
                <select id="filterRoom" class="form-select form-select-sm">
                    <option value="all" selected>All Rooms</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}">
                            Room {{ $room->room_number }} ({{ $room->floor }}){{ $room->is_self ? ' - Self' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tenant / Resident Selector -->
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label class="filter-label"><i class="fas fa-user me-1"></i>Resident</label>
                <select id="filterTenant" class="form-select form-select-sm">
                    <option value="all" selected>All Residents</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}">
                            {{ $tenant->name }} (Room {{ $tenant->room_number ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Floor Selector -->
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label class="filter-label"><i class="fas fa-layer-group me-1"></i>Floor</label>
                <select id="filterFloor" class="form-select form-select-sm">
                    <option value="all" selected>All Floors</option>
                    @foreach ($floors as $fl)
                        <option value="{{ $fl }}">{{ $fl }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Adaptive Status Selector -->
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label class="filter-label" id="statusLabel"><i class="fas fa-tag me-1"></i>Status</label>
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="all" selected>All Statuses</option>
                    <!-- Options populated adaptively by active tab JS -->
                </select>
            </div>

            <!-- Payment Method Filter (for Payments & Expenses) -->
            <div class="col-lg-2 col-md-3 col-sm-6" id="paymentMethodCol">
                <label class="filter-label"><i class="fas fa-credit-card me-1"></i>Payment Mode</label>
                <select id="filterPaymentMethod" class="form-select form-select-sm">
                    <option value="all" selected>All Methods</option>
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI / GPay / PhonePe</option>
                    <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Card">Card</option>
                </select>
            </div>

            <!-- Expense Category Filter (for Expenses) -->
            <div class="col-lg-2 col-md-3 col-sm-6" id="expenseCategoryCol" style="display: none;">
                <label class="filter-label"><i class="fas fa-folder-open me-1"></i>Expense Category</label>
                <select id="filterExpenseCategory" class="form-select form-select-sm">
                    <option value="all" selected>All Categories</option>
                    @foreach ($expenseCategories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Action Buttons -->
            <div class="col-lg-4 col-md-6 col-sm-12 text-end ms-auto mt-2">
                <button type="button" id="btnApplyFilters" class="btn btn-sm btn-primary px-3">
                    <i class="fas fa-filter me-1"></i> Apply Filters
                </button>
                <button type="button" id="btnResetFilters" class="btn btn-sm btn-outline-secondary px-3 ms-1">
                    <i class="fas fa-undo me-1"></i> Reset All
                </button>
            </div>
        </form>
    </div>

    <!-- Module Navigation Pills -->
    <ul class="nav nav-pills report-nav-pills mb-3 no-print" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-bills" data-bs-toggle="pill" data-bs-target="#pane-bills" type="button" role="tab" data-type="bills">
                <i class="fas fa-file-invoice me-1"></i> Invoices & Bills
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-payments" data-bs-toggle="pill" data-bs-target="#pane-payments" type="button" role="tab" data-type="payments">
                <i class="fas fa-receipt me-1"></i> Collections & Receipts
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-rooms" data-bs-toggle="pill" data-bs-target="#pane-rooms" type="button" role="tab" data-type="rooms">
                <i class="fas fa-door-open me-1"></i> Room Occupancy
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tenants" data-bs-toggle="pill" data-bs-target="#pane-tenants" type="button" role="tab" data-type="tenants">
                <i class="fas fa-users me-1"></i> Residents & Leases
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-expenses" data-bs-toggle="pill" data-bs-target="#pane-expenses" type="button" role="tab" data-type="expenses">
                <i class="fas fa-money-bill-wave me-1"></i> Expenses Ledger
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-eb" data-bs-toggle="pill" data-bs-target="#pane-eb" type="button" role="tab" data-type="eb">
                <i class="fas fa-bolt me-1"></i> Electricity (EB)
            </button>
        </li>
    </ul>

    <!-- Tab Content Panes with DataTables -->
    <div class="data-table-card">
        <div class="tab-content" id="reportTabContent">
            
            <!-- 1. BILLS & INVOICES TAB -->
            <div class="tab-pane fade show active" id="pane-bills" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-file-invoice text-primary me-2"></i>Bills & Invoices Records
                    </h5>
                    <span class="badge bg-light text-muted border font-12" id="billsRecordCountBadge">Live Records</span>
                </div>
                <div class="table-responsive">
                    <table id="billsTable" class="table table-hover table-bordered align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Invoice #</th>
                                <th>Billing Month</th>
                                <th>Room</th>
                                <th>Resident</th>
                                <th>Bill Type</th>
                                <th>Total (₹)</th>
                                <th>Paid (₹)</th>
                                <th>Due (₹)</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th class="no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- 2. COLLECTIONS & PAYMENTS TAB -->
            <div class="tab-pane fade" id="pane-payments" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-receipt text-success me-2"></i>Collections & Payment Receipts
                    </h5>
                    <span class="badge bg-light text-muted border font-12">Live Records</span>
                </div>
                <div class="table-responsive">
                    <table id="paymentsTable" class="table table-hover table-bordered align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Receipt #</th>
                                <th>Invoice Ref</th>
                                <th>Payment Date</th>
                                <th>Resident</th>
                                <th>Room</th>
                                <th>Mode</th>
                                <th>Txn / Ref ID</th>
                                <th>Amount Paid</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- 3. ROOM INVENTORY & OCCUPANCY TAB -->
            <div class="tab-pane fade" id="pane-rooms" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-door-open text-info me-2"></i>Rooms & Occupancy Register
                    </h5>
                    <span class="badge bg-light text-muted border font-12">All Units</span>
                </div>
                <div class="table-responsive">
                    <table id="roomsTable" class="table table-hover table-bordered align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Room #</th>
                                <th>Floor</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Monthly Rent</th>
                                <th>EB Rate</th>
                                <th>Maintenance</th>
                                <th>Other</th>
                                <th>Total Monthly</th>
                                <th>Deposit</th>
                                <th>Status</th>
                                <th>Current Resident</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- 4. TENANTS & RESIDENTS TAB -->
            <div class="tab-pane fade" id="pane-tenants" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-users text-primary me-2"></i>Residents Directory & Ledger
                    </h5>
                    <span class="badge bg-light text-muted border font-12">Resident Accounts</span>
                </div>
                <div class="table-responsive">
                    <table id="tenantsTable" class="table table-hover table-bordered align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Resident Details</th>
                                <th>Room</th>
                                <th>Move-in Date</th>
                                <th>Monthly Rent</th>
                                <th>Security Deposit</th>
                                <th>Total Invoiced</th>
                                <th>Total Paid</th>
                                <th>Current Due</th>
                                <th>Status</th>
                                <th class="no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- 5. EXPENSES TAB -->
            <div class="tab-pane fade" id="pane-expenses" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-money-bill-wave text-danger me-2"></i>Expenditures & Maintenance Register
                    </h5>
                    <span class="badge bg-light text-muted border font-12">Expense Records</span>
                </div>
                <div class="table-responsive">
                    <table id="expensesTable" class="table table-hover table-bordered align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Expense Date</th>
                                <th>Type</th>
                                <th>Room / Area</th>
                                <th>Category</th>
                                <th>Item / Description</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Paid To</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- 6. ELECTRICITY (EB) TAB -->
            <div class="tab-pane fade" id="pane-eb" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>Electricity (EB) Meter & Units Ledger
                    </h5>
                    <span class="badge bg-light text-muted border font-12">EB Meter Readings</span>
                </div>
                <div class="table-responsive">
                    <table id="ebTable" class="table table-hover table-bordered align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Billing Month</th>
                                <th>Room</th>
                                <th>Resident</th>
                                <th>Prev Reading</th>
                                <th>Curr Reading</th>
                                <th>Consumed Units</th>
                                <th>Rate (₹/unit)</th>
                                <th>EB Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('footer_content')
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('assets/libs/chart.js/Chart.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script type="text/javascript">
        $(function () {
            // State
            let currentTabType = 'bills';
            let trendChart = null;
            let statusChart = null;
            let chartsVisible = false;

            // Initialize Flatpickr for custom dates
            const startPicker = flatpickr('#filterStartDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    endPicker.set('minDate', dateStr);
                }
            });

            const endPicker = flatpickr('#filterEndDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
            });

            // Date range preset change
            $('#filterDateRange').on('change', function () {
                if ($(this).val() === 'custom') {
                    $('.custom-date-col').show();
                } else {
                    $('.custom-date-col').hide();
                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                }
                loadReportSummary();
                reloadActiveTable();
            });

            // Update status dropdown options based on active tab
            function updateStatusDropdown(tabType) {
                const $status = $('#filterStatus');
                $status.empty();
                $status.append('<option value="all" selected>All Statuses</option>');

                if (tabType === 'bills' || tabType === 'eb') {
                    $('#statusLabel').html('<i class="fas fa-tag me-1"></i>Bill Status');
                    $status.append('<option value="paid">Paid</option>');
                    $status.append('<option value="partially_paid">Partially Paid</option>');
                    $status.append('<option value="unpaid">Unpaid</option>');
                    $status.append('<option value="overdue">Overdue</option>');
                    $('#paymentMethodCol').hide();
                    $('#expenseCategoryCol').hide();
                } else if (tabType === 'rooms') {
                    $('#statusLabel').html('<i class="fas fa-tag me-1"></i>Room Status');
                    $status.append('<option value="occupied">Occupied</option>');
                    $status.append('<option value="available">Available</option>');
                    $status.append('<option value="maintenance">Maintenance</option>');
                    $status.append('<option value="self">Self-Occupied</option>');
                    $('#paymentMethodCol').hide();
                    $('#expenseCategoryCol').hide();
                } else if (tabType === 'tenants') {
                    $('#statusLabel').html('<i class="fas fa-tag me-1"></i>Resident Status');
                    $status.append('<option value="active">Active Residents</option>');
                    $status.append('<option value="inactive">Inactive Residents</option>');
                    $('#paymentMethodCol').hide();
                    $('#expenseCategoryCol').hide();
                } else if (tabType === 'payments') {
                    $('#statusLabel').html('<i class="fas fa-tag me-1"></i>Status');
                    $status.append('<option value="all" selected>All</option>');
                    $('#paymentMethodCol').show();
                    $('#expenseCategoryCol').hide();
                } else if (tabType === 'expenses') {
                    $('#statusLabel').html('<i class="fas fa-tag me-1"></i>Expense Type');
                    $status.append('<option value="room">Room Expense</option>');
                    $status.append('<option value="personal">General / Personal</option>');
                    $('#paymentMethodCol').show();
                    $('#expenseCategoryCol').show();
                }
            }

            // Collect active filter parameters
            function getFilterParams() {
                return {
                    report_type: currentTabType,
                    date_range: $('#filterDateRange').val(),
                    start_date: $('#filterStartDate').val(),
                    end_date: $('#filterEndDate').val(),
                    room_id: $('#filterRoom').val(),
                    tenant_id: $('#filterTenant').val(),
                    floor: $('#filterFloor').val(),
                    status: $('#filterStatus').val(),
                    payment_method: $('#filterPaymentMethod').val(),
                    category: $('#filterExpenseCategory').val()
                };
            }

            // Load and update KPI cards
            function loadReportSummary() {
                const params = getFilterParams();
                $.ajax({
                    url: "{{ url('admin/reports/summary') }}",
                    type: 'GET',
                    data: params,
                    success: function (res) {
                        if (res && res.success && res.cards) {
                            const c = res.cards;
                            $('#kpiTotalInvoiced').text(c.total_invoiced_formatted);
                            $('#kpiBillsCount').text(c.bills_count + ' Invoices');
                            $('#kpiTotalCollected').text(c.total_collected_formatted);
                            $('#kpiCollectionRate').text(c.collection_rate);
                            $('#kpiOutstandingDues').text(c.outstanding_dues_formatted);
                            $('#kpiPendingBillsCount').text(c.pending_bills_count + ' Pending');
                            $('#kpiOverdueAmount').text(c.overdue_amount_formatted);
                            $('#kpiOverdueCount').text(c.overdue_count + ' Overdue Bills');
                            $('#kpiTotalExpenses').text(c.total_expenses_formatted);
                            $('#kpiExpensesCount').text(c.expenses_count + ' Records');
                            $('#kpiNetCashflow').text(c.net_cashflow_formatted);
                            $('#kpiOccupancyRate').text(c.occupancy_rate);
                            if (c.total_scheduled_dues_formatted) $('#kpiScheduledDues').html(c.total_scheduled_dues_formatted + ' <small class="text-muted fw-normal font-11">/ mo</small>');
                            if (c.total_monthly_rent_formatted) $('#kpiMonthlyRent').html(c.total_monthly_rent_formatted + ' <small class="text-muted fw-normal font-11">/ mo</small>');
                            if (c.total_security_deposits_formatted) $('#kpiSecurityDeposits').text(c.total_security_deposits_formatted);
                            if (c.total_room_rent_capacity_formatted) $('#kpiRoomRentCapacity').html(c.total_room_rent_capacity_formatted + ' <small class="text-muted fw-normal font-11">/ mo</small>');
                        }
                    }
                });
            }

            // Tables instances
            const tables = {};

            // 1. Bills DataTable
            function initBillsTable() {
                if (tables['bills']) return;
                tables['bills'] = $('#billsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('admin/reports/table') }}",
                        data: function (d) {
                            const f = getFilterParams();
                            Object.assign(d, f);
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '4%' },
                        { data: 'bill_number_badge', name: 'bill_number' },
                        { data: 'billing_month_display', name: 'billing_month' },
                        { data: 'room_badge', name: 'room.room_number' },
                        { data: 'tenant_info', name: 'tenant.name' },
                        { data: 'bill_type_display', name: 'bill_type' },
                        { data: 'amount_formatted', name: 'amount' },
                        { data: 'paid_formatted', name: 'paid_amount' },
                        { data: 'due_formatted', name: 'due_amount' },
                        { data: 'due_date_formatted', name: 'due_date' },
                        { data: 'status_badge', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    order: [[1, 'desc']],
                    pageLength: 15,
                    language: {
                        emptyTable: "No invoices found for the selected criteria."
                    }
                });
            }

            // 2. Payments DataTable
            function initPaymentsTable() {
                if (tables['payments']) return;
                tables['payments'] = $('#paymentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('admin/reports/table') }}",
                        data: function (d) {
                            const f = getFilterParams();
                            Object.assign(d, f);
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '4%' },
                        { data: 'payment_number_badge', name: 'payment_number' },
                        { data: 'bill_number_display', name: 'bill.bill_number' },
                        { data: 'payment_date_formatted', name: 'payment_date' },
                        { data: 'tenant_info', name: 'tenant.name' },
                        { data: 'room_badge', name: 'bill.room.room_number' },
                        { data: 'payment_method_badge', name: 'payment_method' },
                        { data: 'transaction_id_display', name: 'transaction_id' },
                        { data: 'amount_formatted', name: 'amount' },
                        { data: 'received_by_name', name: 'receiver.name' }
                    ],
                    order: [[3, 'desc']],
                    pageLength: 15,
                    language: {
                        emptyTable: "No payments recorded for the selected criteria."
                    }
                });
            }

            // 3. Rooms DataTable
            function initRoomsTable() {
                if (tables['rooms']) return;
                tables['rooms'] = $('#roomsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('admin/reports/table') }}",
                        data: function (d) {
                            const f = getFilterParams();
                            Object.assign(d, f);
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '4%' },
                        { data: 'room_badge', name: 'room_number' },
                        { data: 'floor_display', name: 'floor' },
                        { data: 'room_type_display', name: 'room_type' },
                        { data: 'capacity_display', name: 'capacity' },
                        { data: 'monthly_rent_formatted', name: 'monthly_rent' },
                        { data: 'eb_rate_formatted', name: 'eb_unit_rate' },
                        { data: 'maintenance_formatted', name: 'maintenance_charges' },
                        { data: 'other_formatted', name: 'other_charges' },
                        { data: 'total_monthly_formatted', name: 'id' },
                        { data: 'deposit_formatted', name: 'security_deposit' },
                        { data: 'status_badge', name: 'occupancy_status' },
                        { data: 'current_tenant_display', name: 'currentTenant.name' }
                    ],
                    pageLength: 25,
                    language: {
                        emptyTable: "No rooms match the criteria."
                    }
                });
            }

            // 4. Tenants DataTable
            function initTenantsTable() {
                if (tables['tenants']) return;
                tables['tenants'] = $('#tenantsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('admin/reports/table') }}",
                        data: function (d) {
                            const f = getFilterParams();
                            Object.assign(d, f);
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '4%' },
                        { data: 'resident_info', name: 'name' },
                        { data: 'room_badge', name: 'room_number' },
                        { data: 'commence_date_formatted', name: 'commence_date' },
                        { data: 'monthly_rent_formatted', name: 'monthly_rent' },
                        { data: 'deposit_formatted', name: 'security_deposit' },
                        { data: 'total_invoiced_formatted', name: 'id', orderable: false, searchable: false },
                        { data: 'total_paid_formatted', name: 'id', orderable: false, searchable: false },
                        { data: 'outstanding_due_formatted', name: 'id', orderable: false, searchable: false },
                        { data: 'status_badge', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    pageLength: 20,
                    language: {
                        emptyTable: "No residents found."
                    }
                });
            }

            // 5. Expenses DataTable
            function initExpensesTable() {
                if (tables['expenses']) return;
                tables['expenses'] = $('#expensesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('admin/reports/table') }}",
                        data: function (d) {
                            const f = getFilterParams();
                            Object.assign(d, f);
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '4%' },
                        { data: 'expense_date_formatted', name: 'expense_date' },
                        { data: 'type_badge', name: 'expense_type' },
                        { data: 'room_badge', name: 'room.room_number' },
                        { data: 'category_badge', name: 'category' },
                        { data: 'title_desc', name: 'title' },
                        { data: 'amount_formatted', name: 'amount' },
                        { data: 'payment_method_display', name: 'payment_method' },
                        { data: 'paid_to_display', name: 'paid_to' },
                        { data: 'recorded_by_name', name: 'creator.name' }
                    ],
                    order: [[1, 'desc']],
                    pageLength: 15,
                    language: {
                        emptyTable: "No expenses found for the selected criteria."
                    }
                });
            }

            // 6. EB DataTable
            function initEbTable() {
                if (tables['eb']) return;
                tables['eb'] = $('#ebTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('admin/reports/table') }}",
                        data: function (d) {
                            const f = getFilterParams();
                            Object.assign(d, f);
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '4%' },
                        { data: 'billing_month_display', name: 'billing_month' },
                        { data: 'room_badge', name: 'room.room_number' },
                        { data: 'tenant_name_display', name: 'tenant.name' },
                        { data: 'previous_reading_display', name: 'eb_previous_reading' },
                        { data: 'current_reading_display', name: 'eb_current_reading' },
                        { data: 'consumed_units_display', name: 'eb_consumed_units' },
                        { data: 'unit_rate_display', name: 'eb_unit_rate' },
                        { data: 'eb_amount_formatted', name: 'eb_amount' },
                        { data: 'bill_status_badge', name: 'status' }
                    ],
                    order: [[1, 'desc']],
                    pageLength: 15,
                    language: {
                        emptyTable: "No electricity readings found."
                    }
                });
            }

            // Reload currently active table
            function reloadActiveTable() {
                if (tables[currentTabType]) {
                    tables[currentTabType].ajax.reload(null, false);
                } else {
                    switch (currentTabType) {
                        case 'payments': initPaymentsTable(); break;
                        case 'rooms': initRoomsTable(); break;
                        case 'tenants': initTenantsTable(); break;
                        case 'expenses': initExpensesTable(); break;
                        case 'eb': initEbTable(); break;
                        default: initBillsTable(); break;
                    }
                }
            }

            // Tab switching
            $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
                currentTabType = $(this).data('type');
                updateStatusDropdown(currentTabType);
                reloadActiveTable();
            });

            // Filter Apply Button
            $('#btnApplyFilters').on('click', function () {
                loadReportSummary();
                reloadActiveTable();
                if (chartsVisible) loadCharts();
            });

            // Filter inputs auto-trigger on change
            $('#filterRoom, #filterTenant, #filterFloor, #filterStatus, #filterPaymentMethod, #filterExpenseCategory').on('change', function () {
                loadReportSummary();
                reloadActiveTable();
                if (chartsVisible) loadCharts();
            });

            // Reset Button
            $('#btnResetFilters').on('click', function () {
                $('#reportFilterForm')[0].reset();
                $('#filterDateRange').val('all').trigger('change');
                $('#filterRoom').val('all');
                $('#filterTenant').val('all');
                $('#filterFloor').val('all');
                $('#filterStatus').val('all');
                $('#filterPaymentMethod').val('all');
                $('#filterExpenseCategory').val('all');
                $('.custom-date-col').hide();
                updateStatusDropdown(currentTabType);
                loadReportSummary();
                reloadActiveTable();
                if (chartsVisible) loadCharts();
            });

            // Export CSV Action
            $('#btnExportCsv').on('click', function () {
                const params = getFilterParams();
                const queryStr = $.param(params);
                window.location.href = "{{ url('admin/reports/export') }}?" + queryStr;
            });

            // Toggle Charts Action
            $('#btnToggleCharts').on('click', function () {
                chartsVisible = !chartsVisible;
                if (chartsVisible) {
                    $('#chartsContainer').slideDown();
                    $('#toggleChartsText').text('Hide Charts');
                    loadCharts();
                } else {
                    $('#chartsContainer').slideUp();
                    $('#toggleChartsText').text('Show Charts');
                }
            });

            // Charts Loader
            function loadCharts() {
                $.ajax({
                    url: "{{ url('admin/reports/charts') }}",
                    type: 'GET',
                    success: function (res) {
                        if (!res) return;

                        // 1. Monthly Trends Bar Chart
                        const ctxTrend = document.getElementById('monthlyTrendChart');
                        if (ctxTrend) {
                            if (trendChart) trendChart.destroy();
                            trendChart = new Chart(ctxTrend.getContext('2d'), {
                                type: 'bar',
                                data: {
                                    labels: res.months,
                                    datasets: [
                                        {
                                            label: 'Invoiced (₹)',
                                            backgroundColor: '#3b82f6',
                                            data: res.invoiced
                                        },
                                        {
                                            label: 'Collected (₹)',
                                            backgroundColor: '#10b981',
                                            data: res.collected
                                        },
                                        {
                                            label: 'Expenses (₹)',
                                            backgroundColor: '#ef4444',
                                            data: res.expenses
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        yAxes: [{
                                            ticks: {
                                                beginAtZero: true,
                                                callback: function (val) {
                                                    return '₹' + Number(val).toLocaleString('en-IN');
                                                }
                                            }
                                        }]
                                    }
                                }
                            });
                        }

                        // 2. Bill Status Doughnut Chart
                        const ctxStatus = document.getElementById('billStatusChart');
                        if (ctxStatus) {
                            if (statusChart) statusChart.destroy();
                            statusChart = new Chart(ctxStatus.getContext('2d'), {
                                type: 'doughnut',
                                data: {
                                    labels: ['Paid', 'Partial', 'Unpaid', 'Overdue'],
                                    datasets: [{
                                        data: [
                                            res.status.paid || 0,
                                            res.status.partially_paid || 0,
                                            res.status.unpaid || 0,
                                            res.status.overdue || 0
                                        ],
                                        backgroundColor: ['#10b981', '#06b6d4', '#f59e0b', '#ef4444']
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            });
                        }
                    }
                });
            }

            // Initial setup
            updateStatusDropdown('bills');
            initBillsTable();
            loadReportSummary();
        });
    </script>
@endpush
