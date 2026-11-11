@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .accounts-stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
                border: 1px solid #e2e8f0;
                transition: transform 0.2s, box-shadow 0.2s;
                position: relative;
                overflow: hidden;
            }
            .accounts-stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            }
            .stat-icon-bubble {
                width: 44px;
                height: 44px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
            }
            .filter-control {
                border-radius: 8px;
                border: 1px solid #cbd5e1;
                padding: 0.4rem 0.75rem;
                font-size: 0.85rem;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #0a5858 !important;
                color: #fff !important;
                border-color: #0a5858 !important;
                border-radius: 6px;
            }
            .accounts-nav-pill {
                padding: 0.45rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                color: #475569;
                text-decoration: none;
                background: #f1f5f9;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .accounts-nav-pill:hover {
                background: #e2e8f0;
                color: #0f172a;
            }
            .accounts-nav-pill.active {
                background: #0a5858;
                color: #ffffff;
                box-shadow: 0 2px 8px rgba(10, 88, 88, 0.3);
            }
            .font-11 { font-size: 11px; }
            .font-12 { font-size: 12px; }
            .font-13 { font-size: 13px; }
            .font-14 { font-size: 14px; }
            .table-accounts th {
                background-color: #f8fafc;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #475569;
                font-weight: 700;
                padding: 12px 14px;
                border-bottom: 2px solid #e2e8f0;
            }
            .table-accounts td {
                padding: 12px 14px;
                vertical-align: middle;
            }
        </style>
    @endpush

    <!-- Header & Navigation Pills -->
    <div class="row justify-content-between align-items-center mt-3 g-2">
        <div class="col-md-7">
            <h4 class="page-title-main page mb-1">
                <i class="fas fa-receipt text-primary me-2"></i>Accounts Journal & Cash Book
            </h4>
            <p class="text-muted font-13 mb-0">
                Unified double-entry transaction ledger tracking all inflows, outflows, and net cash reserves.
            </p>
        </div>
        <div class="col-md-5 text-md-end d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addJournalModal">
                <i class="fas fa-plus-circle"></i>
                <span>Record Voucher</span>
            </button>
            <a href="{{ route('accounts.journal.export') }}" id="btnExportJournal" class="btn btn-outline-success btn-sm px-3 d-flex align-items-center gap-1">
                <i class="fas fa-file-excel"></i>
                <span>Export CSV</span>
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="window.print()">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>

    <!-- Submodule Navigation Tabs -->
    <div class="d-flex flex-wrap gap-2 my-3 p-2 bg-white rounded-3 shadow-sm border border-light">
        <a href="{{ route('accounts.journal') }}" class="accounts-nav-pill active">
            <i class="fas fa-receipt"></i> Journal & Day Book
        </a>
        <a href="{{ route('accounts.pnl') }}" class="accounts-nav-pill">
            <i class="fas fa-chart-pie"></i> Profit & Loss (P&L)
        </a>
        <a href="{{ route('accounts.balance_sheet') }}" class="accounts-nav-pill">
            <i class="fas fa-balance-scale"></i> Balance Sheet
        </a>
    </div>

    <!-- Real-Time Metrics Cards ("Show the balance amount we have") -->
    <div class="row g-3 mb-4">
        <!-- 1. Real-Time Balance Amount We Have -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="accounts-stat-card border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Balance We Have</span>
                    <div class="stat-icon-bubble bg-primary-subtle text-primary">
                        <i class="fas fa-vault"></i>
                    </div>
                </div>
                <h3 class="mb-1 fw-bold text-dark" id="stat-current-balance">{{ $stats['currentBalanceFormatted'] }}</h3>
                <div class="d-flex justify-content-between text-muted font-11 pt-1 border-top mt-2">
                    <span><i class="fas fa-wallet text-secondary me-1"></i>Cash: <strong id="stat-cash-hand">{{ $stats['cashInHandFormatted'] }}</strong></span>
                    <span><i class="fas fa-university text-info me-1"></i>Bank/UPI: <strong id="stat-bank-balance">{{ $stats['bankBalanceFormatted'] }}</strong></span>
                </div>
            </div>
        </div>

        <!-- 2. Total Inflows (Income / Credits) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="accounts-stat-card border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Period Inflow (Cr)</span>
                    <div class="stat-icon-bubble bg-success-subtle text-success">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <h3 class="mb-1 fw-bold text-success" id="stat-filtered-inflow">{{ $stats['filteredInflowFormatted'] }}</h3>
                <div class="text-muted font-11 pt-1 border-top mt-2">
                    <span>Rent, family salary & operating receipts</span>
                </div>
            </div>
        </div>

        <!-- 3. Total Outflows (Expenses / Debits) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="accounts-stat-card border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Period Outflow (Dr)</span>
                    <div class="stat-icon-bubble bg-danger-subtle text-danger">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <h3 class="mb-1 fw-bold text-danger" id="stat-filtered-outflow">{{ $stats['filteredOutflowFormatted'] }}</h3>
                <div class="text-muted font-11 pt-1 border-top mt-2">
                    <span>Room & personal expenses, EMI payments</span>
                </div>
            </div>
        </div>

        <!-- 4. Period Net Cash Flow -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="accounts-stat-card border-start border-4 {{ $stats['filteredNetIsPositive'] ? 'border-success' : 'border-danger' }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Period Net Flow</span>
                    <div class="stat-icon-bubble {{ $stats['filteredNetIsPositive'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <h3 class="mb-1 fw-bold {{ $stats['filteredNetIsPositive'] ? 'text-success' : 'text-danger' }}" id="stat-filtered-net">{{ $stats['filteredNetFormatted'] }}</h3>
                <div class="d-flex justify-content-between text-muted font-11 pt-1 border-top mt-2">
                    <span>Status: <strong class="{{ $stats['filteredNetIsPositive'] ? 'text-success' : 'text-danger' }}">{{ $stats['filteredNetIsPositive'] ? 'Cash Surplus' : 'Cash Deficit' }}</strong></span>
                    <span>Txns: <strong id="stat-txn-count">{{ $stats['transactionCount'] }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Date Presets -->
                <div class="col-12 col-md-auto">
                    <label class="form-label font-11 fw-bold text-muted mb-1 d-block">Quick Range</label>
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-date-preset active" data-preset="this_month">This Month</button>
                        <button type="button" class="btn btn-outline-secondary btn-date-preset" data-preset="last_month">Last Month</button>
                        <button type="button" class="btn btn-outline-secondary btn-date-preset" data-preset="this_year">This FY</button>
                        <button type="button" class="btn btn-outline-secondary btn-date-preset" data-preset="all">All Time</button>
                    </div>
                </div>

                <!-- Custom Start & End Dates -->
                <div class="col-6 col-md-2">
                    <label class="form-label font-11 fw-bold text-muted mb-1">From Date</label>
                    <input type="date" id="filter-start-date" class="form-control filter-control" value="{{ $startOfMonth }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label font-11 fw-bold text-muted mb-1">To Date</label>
                    <input type="date" id="filter-end-date" class="form-control filter-control" value="{{ $endOfMonth }}">
                </div>

                <!-- Flow Type -->
                <div class="col-6 col-md-2">
                    <label class="form-label font-11 fw-bold text-muted mb-1">Flow Direction</label>
                    <select id="filter-flow-type" class="form-select filter-control">
                        <option value="all">All Flows (Cr & Dr)</option>
                        <option value="income">Inflow (Credits Only)</option>
                        <option value="expense">Outflow (Debits Only)</option>
                    </select>
                </div>

                <!-- Source Module -->
                <div class="col-6 col-md-2">
                    <label class="form-label font-11 fw-bold text-muted mb-1">Source Module</label>
                    <select id="filter-source-type" class="form-select filter-control">
                        <option value="all">All Modules</option>
                        <option value="bill_payment">Rent & Collections</option>
                        <option value="expense">Expenses (Room & Personal)</option>
                        <option value="salary">Family Salary (Income)</option>
                        <option value="loan_emi">Loan EMIs</option>
                        <option value="journal">Manual Journal Vouchers</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-12 col-md-auto align-self-end">
                    <button type="button" id="btn-reset-filters" class="btn btn-light btn-sm filter-control w-100" title="Reset Filters">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Bulk Action Bar for Journal -->
    <div id="bulkActionBarJournal" class="d-none align-items-center justify-content-between p-2 px-3 mb-2 rounded-3" style="background: linear-gradient(135deg, #0a5858 0%, #0d7373 100%); color: #fff; box-shadow: 0 4px 12px rgba(10, 88, 88, 0.25);">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-dark fw-bold px-2 py-1 font-12" id="bulkSelectedCountJournal">0 Selected</span>
            <span class="font-12 text-white-50 d-none d-sm-inline">Actions for selected transactions:</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-warning btn-sm fw-semibold btn-bulk-edit-journal px-3 py-1 font-12">
                <i class="fas fa-edit me-1"></i> Bulk Edit
            </button>
            <button type="button" class="btn btn-danger btn-sm fw-semibold btn-bulk-delete-journal px-3 py-1 font-12">
                <i class="fas fa-trash-alt me-1"></i> Bulk Delete
            </button>
            <button type="button" class="btn btn-sm btn-outline-light btn-bulk-deselect-journal px-2 py-1 font-11" title="Deselect All">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Journal DataTables Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table id="journal-datatable" class="table table-hover table-accounts w-100 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input bulk-select-all" id="bulkSelectAllJournal">
                            </th>
                            <th style="width: 110px;">Date</th>
                            <th style="width: 140px;">Voucher #</th>
                            <th style="width: 120px;">Flow</th>
                            <th>Module & Category</th>
                            <th>Party / Description</th>
                            <th style="width: 110px;">Payment Mode</th>
                            <th class="text-end" style="width: 120px;">Credit (In ₹)</th>
                            <th class="text-end" style="width: 120px;">Debit (Out ₹)</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: RECORD JOURNAL VOUCHER (ADD)       -->
    <!-- ========================================== -->
    <div class="modal fade" id="addJournalModal" tabindex="-1" aria-labelledby="addJournalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-16 fw-bold" id="addJournalModalLabel">
                        <i class="fas fa-file-invoice me-2"></i>Record New Journal Voucher
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addJournalForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Voucher Date -->
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Voucher Date <span class="text-danger">*</span></label>
                                <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Entry Type -->
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Entry Nature / Type <span class="text-danger">*</span></label>
                                <select name="entry_type" id="modal-entry-type" class="form-select" required>
                                    <option value="income">Direct Income (Inflow / Cr)</option>
                                    <option value="expense">Direct Expense (Outflow / Dr)</option>
                                    <option value="capital">Capital Introduced (Owner Inflow / Cr)</option>
                                    <option value="drawing">Owner Drawing (Personal Outflow / Dr)</option>
                                    <option value="transfer">Internal Transfer / Contra</option>
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Account Category <span class="text-danger">*</span></label>
                                <input type="text" name="category" list="categoryOptions" class="form-control" placeholder="Select or type category..." required>
                                <datalist id="categoryOptions">
                                    @foreach($defaultCategories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </div>

                            <!-- Particulars / Title -->
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Particulars / Description <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Scrap carton sales, Capital deposit by owner" required>
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">₹</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="Cash" selected>Cash</option>
                                    <option value="UPI">UPI (GPay / PhonePe / Paytm)</option>
                                    <option value="Bank Transfer">Bank Transfer (NEFT / IMPS)</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Card">Card</option>
                                </select>
                            </div>

                            <!-- Account / Bank Name -->
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Account / Cash Book Name</label>
                                <input type="text" name="account_name" class="form-control" placeholder="e.g. Cash in Hand, SBI Current A/C">
                            </div>

                            <!-- Reference / Cheque / UTR # -->
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Transaction Reference / UTR #</label>
                                <input type="text" name="reference_no" class="form-control" placeholder="Cheque #, UTR #, or receipt code">
                            </div>

                            <!-- Receipt Attachment -->
                            <div class="col-md-12">
                                <label class="form-label font-13 fw-semibold">Voucher Document / Receipt Slip (Optional)</label>
                                <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted font-11">Accepted formats: JPG, PNG, PDF. Max size: 5MB.</small>
                            </div>

                            <!-- Narration / Notes -->
                            <div class="col-md-12">
                                <label class="form-label font-13 fw-semibold">Narration / Detailed Notes</label>
                                <textarea name="notes" rows="2" class="form-control" placeholder="Detailed description of the transaction..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnSaveJournal" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-check-circle me-1"></i> Save Voucher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: EDIT JOURNAL VOUCHER               -->
    <!-- ========================================== -->
    <div class="modal fade" id="editJournalModal" tabindex="-1" aria-labelledby="editJournalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title font-16 fw-bold" id="editJournalModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Journal Voucher: <span id="edit-voucher-num" class="font-monospace text-warning"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editJournalForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-entry-id" name="entry_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Voucher Date <span class="text-danger">*</span></label>
                                <input type="date" name="entry_date" id="edit-entry-date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Entry Nature / Type <span class="text-danger">*</span></label>
                                <select name="entry_type" id="edit-entry-type" class="form-select" required>
                                    <option value="income">Direct Income (Inflow / Cr)</option>
                                    <option value="expense">Direct Expense (Outflow / Dr)</option>
                                    <option value="capital">Capital Introduced (Owner Inflow / Cr)</option>
                                    <option value="drawing">Owner Drawing (Personal Outflow / Dr)</option>
                                    <option value="transfer">Internal Transfer / Contra</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Account Category <span class="text-danger">*</span></label>
                                <input type="text" name="category" id="edit-category" list="categoryOptions" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Particulars / Description <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="edit-title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">₹</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="edit-amount" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_method" id="edit-payment-method" class="form-select" required>
                                    <option value="Cash">Cash</option>
                                    <option value="UPI">UPI (GPay / PhonePe / Paytm)</option>
                                    <option value="Bank Transfer">Bank Transfer (NEFT / IMPS)</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Card">Card</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Account / Cash Book Name</label>
                                <input type="text" name="account_name" id="edit-account-name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Transaction Reference / UTR #</label>
                                <input type="text" name="reference_no" id="edit-reference-no" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label font-13 fw-semibold">Replace Attachment Document</label>
                                <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                <div id="edit-current-attachment" class="mt-1 font-12"></div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label font-13 fw-semibold">Narration / Notes</label>
                                <textarea name="notes" id="edit-notes" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnUpdateJournal" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-save me-1"></i> Update Voucher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BULK EDIT JOURNAL MODAL -->
    <div class="modal fade" id="bulkEditJournalModal" tabindex="-1" aria-labelledby="bulkEditJournalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <div>
                        <h5 class="modal-title text-white" id="bulkEditJournalModalLabel">
                            <i class="fas fa-edit me-1"></i> Bulk Edit Journal Vouchers
                        </h5>
                        <small class="text-white-50">Updating <span id="bulkEditCountBadgeJournal" class="badge bg-warning text-dark fw-bold"></span></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bulkEditJournalForm">
                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 px-3 font-12 mb-3">
                            <i class="fas fa-info-circle me-1"></i> <strong>Note:</strong> Bulk edit applies to manual journal vouchers. Only filled fields will be updated across all selected records.
                        </div>
                        <div id="bulkEditJournalErrors" class="alert alert-danger d-none py-2 px-3 font-12"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Entry Nature / Flow</label>
                                <select name="entry_type" class="form-select">
                                    <option value="">-- Keep Existing --</option>
                                    <option value="income">Direct Income (Inflow / Cr)</option>
                                    <option value="expense">Direct Expense (Outflow / Dr)</option>
                                    <option value="capital">Capital Introduced (Owner Inflow / Cr)</option>
                                    <option value="drawing">Owner Drawing (Personal Outflow / Dr)</option>
                                    <option value="transfer">Internal Transfer / Contra</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Voucher Date</label>
                                <input type="date" name="entry_date" class="form-control" placeholder="Keep Existing">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Account Category</label>
                                <input type="text" name="category" list="bulkCategoryOptions" class="form-control" placeholder="Select or type category...">
                                <datalist id="bulkCategoryOptions">
                                    @foreach($defaultCategories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Payment Mode</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">-- Keep Existing --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="UPI">UPI / GPay / PhonePe</option>
                                    <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold font-13">Bank / Account Name</label>
                                <input type="text" name="account_name" class="form-control" placeholder="e.g. SRM Cash Register, HDFC Current A/C">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold font-13">General Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Leave empty to keep existing notes"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white" id="btnApplyBulkEditJournal">
                            <i class="fas fa-check-double me-1"></i> Apply Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ url('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            // Setup CSRF header
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Initialize Journal DataTables
            const journalTable = $('#journal-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('accounts.journal.data') }}",
                    data: function (d) {
                        d.start_date = $('#filter-start-date').val();
                        d.end_date = $('#filter-end-date').val();
                        d.flow_type = $('#filter-flow-type').val();
                        d.source_type = $('#filter-source-type').val();
                    }
                },
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'trans_date', name: 'trans_date' },
                    { data: 'voucher_display', name: 'voucher_no' },
                    { data: 'flow_badge', name: 'flow_type' },
                    { data: 'category_display', name: 'sub_category' },
                    { data: 'party_display', name: 'party_name' },
                    { data: 'payment_method_badge', name: 'payment_method' },
                    { data: 'inflow_amount', name: 'amount', className: 'text-end' },
                    { data: 'outflow_amount', name: 'amount', className: 'text-end' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                language: {
                    processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div> Loading ledger...',
                    search: "Quick Search:",
                    lengthMenu: "_MENU_ entries / page",
                    zeroRecords: "No financial transactions found for selected filters."
                }
            });

            // Refresh Stats Cards Function
            function reloadStats() {
                const startDate = $('#filter-start-date').val();
                const endDate = $('#filter-end-date').val();
                const flowType = $('#filter-flow-type').val();
                const sourceType = $('#filter-source-type').val();

                $.ajax({
                    url: "{{ route('accounts.journal.stats') }}",
                    type: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate,
                        flow_type: flowType,
                        source_type: sourceType
                    },
                    success: function (res) {
                        if (res.success && res.stats) {
                            const s = res.stats;
                            $('#stat-current-balance').text(s.currentBalanceFormatted);
                            $('#stat-cash-hand').text(s.cashInHandFormatted);
                            $('#stat-bank-balance').text(s.bankBalanceFormatted);
                            $('#stat-filtered-inflow').text(s.filteredInflowFormatted);
                            $('#stat-filtered-outflow').text(s.filteredOutflowFormatted);
                            $('#stat-filtered-net').text(s.filteredNetFormatted);
                            $('#stat-txn-count').text(s.transactionCount);

                            // Update Net flow card styling
                            if (s.filteredNetIsPositive) {
                                $('#stat-filtered-net').removeClass('text-danger').addClass('text-success');
                            } else {
                                $('#stat-filtered-net').removeClass('text-success').addClass('text-danger');
                            }
                        }
                    }
                });

                // Update Export URL params
                const exportUrl = new URL("{{ route('accounts.journal.export') }}", window.location.origin);
                if (startDate) exportUrl.searchParams.set('start_date', startDate);
                if (endDate) exportUrl.searchParams.set('end_date', endDate);
                if (flowType !== 'all') exportUrl.searchParams.set('flow_type', flowType);
                if (sourceType !== 'all') exportUrl.searchParams.set('source_type', sourceType);
                $('#btnExportJournal').attr('href', exportUrl.toString());
            }

            // Quick Range Presets
            $('.btn-date-preset').on('click', function () {
                $('.btn-date-preset').removeClass('active');
                $(this).addClass('active');

                const preset = $(this).data('preset');
                const now = new Date();

                if (preset === 'this_month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    $('#filter-start-date').val(firstDay.toISOString().split('T')[0]);
                    $('#filter-end-date').val(lastDay.toISOString().split('T')[0]);
                } else if (preset === 'last_month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
                    $('#filter-start-date').val(firstDay.toISOString().split('T')[0]);
                    $('#filter-end-date').val(lastDay.toISOString().split('T')[0]);
                } else if (preset === 'this_year') {
                    // Indian FY: Apr 1 to Mar 31
                    const year = now.getMonth() >= 3 ? now.getFullYear() : now.getFullYear() - 1;
                    const startFY = new Date(year, 3, 1);
                    const endFY = new Date(year + 1, 2, 31);
                    $('#filter-start-date').val(startFY.toISOString().split('T')[0]);
                    $('#filter-end-date').val(endFY.toISOString().split('T')[0]);
                } else if (preset === 'all') {
                    $('#filter-start-date').val('');
                    $('#filter-end-date').val('');
                }

                journalTable.draw();
                reloadStats();
            });

            // Filter Change Listeners
            $('#filter-start-date, #filter-end-date, #filter-flow-type, #filter-source-type').on('change', function () {
                $('.btn-date-preset').removeClass('active');
                journalTable.draw();
                reloadStats();
            });

            // Reset Filters
            $('#btn-reset-filters').on('click', function () {
                $('.btn-date-preset').removeClass('active');
                $('.btn-date-preset[data-preset="this_month"]').addClass('active');
                $('#filter-start-date').val("{{ $startOfMonth }}");
                $('#filter-end-date').val("{{ $endOfMonth }}");
                $('#filter-flow-type').val('all');
                $('#filter-source-type').val('all');
                journalTable.draw();
                reloadStats();
            });

            // Save Journal Entry (AJAX)
            $('#addJournalForm').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = $('#btnSaveJournal');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

                $.ajax({
                    url: "{{ route('accounts.journal.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Save Voucher');
                        if (res.success) {
                            $('#addJournalModal').modal('hide');
                            $('#addJournalForm')[0].reset();
                            Swal.fire({
                                icon: 'success',
                                title: 'Voucher Saved!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            journalTable.draw();
                            reloadStats();
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Save Voucher');
                        let msg = 'Failed to save voucher. Please check inputs.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({ icon: 'error', title: 'Validation Error', html: msg });
                    }
                });
            });

            // Open Edit Modal
            $(document).on('click', '.btn-edit-journal', function () {
                const id = $(this).data('id');
                const url = "{{ url('/admin/accounts/journal') }}/" + id + "/data";

                $.get(url, function (res) {
                    if (res.success && res.entry) {
                        const e = res.entry;
                        $('#edit-entry-id').val(e.id);
                        $('#edit-voucher-num').text(e.entry_number);
                        $('#edit-entry-date').val(e.entry_date);
                        $('#edit-entry-type').val(e.entry_type);
                        $('#edit-category').val(e.category);
                        $('#edit-title').val(e.title);
                        $('#edit-amount').val(e.amount);
                        $('#edit-payment-method').val(e.payment_method);
                        $('#edit-account-name').val(e.account_name);
                        $('#edit-reference-no').val(e.reference_no);
                        $('#edit-notes').val(e.notes);

                        if (e.attachment_url) {
                            $('#edit-current-attachment').html('<a href="' + e.attachment_url + '" target="_blank" class="text-primary"><i class="fas fa-paperclip me-1"></i>View Current Attachment</a>');
                        } else {
                            $('#edit-current-attachment').html('<span class="text-muted">No attachment uploaded</span>');
                        }

                        $('#editJournalModal').modal('show');
                    }
                }).fail(function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to fetch voucher data.' });
                });
            });

            // Update Journal Entry (AJAX)
            $('#editJournalForm').on('submit', function (e) {
                e.preventDefault();
                const id = $('#edit-entry-id').val();
                const formData = new FormData(this);
                const btn = $('#btnUpdateJournal');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');

                $.ajax({
                    url: "{{ url('/admin/accounts/journal') }}/" + id,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Voucher');
                        if (res.success) {
                            $('#editJournalModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            journalTable.draw();
                            reloadStats();
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Voucher');
                        let msg = 'Failed to update voucher.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({ icon: 'error', title: 'Update Error', html: msg });
                    }
                });
            });

            // Delete Journal Entry
            $(document).on('click', '.btn-delete-journal', function () {
                const id = $(this).data('id');
                const voucher = $(this).data('voucher');

                Swal.fire({
                    title: 'Delete Voucher ' + voucher + '?',
                    text: "This manual transaction will be removed from the journal.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('/admin/accounts/journal') }}/" + id,
                            type: "DELETE",
                            success: function (res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                journalTable.draw();
                                reloadStats();
                            },
                            error: function () {
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete voucher.' });
                            }
                        });
                    }
                });
            });

            // =========================================================================
            // BULK SELECTION, BULK EDIT & BULK DELETE FOR JOURNAL
            // =========================================================================
            function updateBulkBarJournal() {
                var checkedBoxes = $('#journal-datatable tbody .bulk-select-row:checked');
                var count = checkedBoxes.length;
                var totalBoxes = $('#journal-datatable tbody .bulk-select-row').length;

                if (count > 0) {
                    $('#bulkSelectedCountJournal').text(count + ' Selected');
                    $('#bulkActionBarJournal').removeClass('d-none').addClass('d-flex');
                } else {
                    $('#bulkActionBarJournal').removeClass('d-flex').addClass('d-none');
                }

                if (totalBoxes > 0 && count === totalBoxes) {
                    $('#bulkSelectAllJournal').prop('checked', true).prop('indeterminate', false);
                } else if (count > 0) {
                    $('#bulkSelectAllJournal').prop('checked', false).prop('indeterminate', true);
                } else {
                    $('#bulkSelectAllJournal').prop('checked', false).prop('indeterminate', false);
                }
            }

            $(document).on('change', '#bulkSelectAllJournal', function () {
                var isChecked = $(this).is(':checked');
                $('#journal-datatable tbody .bulk-select-row').prop('checked', isChecked);
                updateBulkBarJournal();
            });

            $(document).on('change', '#journal-datatable tbody .bulk-select-row', function () {
                updateBulkBarJournal();
            });

            journalTable.on('draw', function () {
                updateBulkBarJournal();
            });

            $(document).on('click', '.btn-bulk-deselect-journal', function () {
                $('#journal-datatable tbody .bulk-select-row').prop('checked', false);
                $('#bulkSelectAllJournal').prop('checked', false).prop('indeterminate', false);
                updateBulkBarJournal();
            });

            // Bulk Delete Journal Entries
            $(document).on('click', '.btn-bulk-delete-journal', function () {
                var items = [];
                $('#journal-datatable tbody .bulk-select-row:checked').each(function () {
                    items.push({
                        id: $(this).val(),
                        source_type: $(this).data('source')
                    });
                });

                if (items.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Selection Empty', text: 'No transactions selected.' });
                    return;
                }

                Swal.fire({
                    title: "Delete " + items.length + " Transaction(s)?",
                    text: "Are you sure you want to delete these " + items.length + " selected records from the ledger? This action cannot be undone.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#f34e4e",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete " + items.length + " records"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('accounts.journal.bulk_delete') }}",
                            data: {
                                items: items,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: res.message,
                                        timer: 1800,
                                        showConfirmButton: false
                                    });
                                    $('#bulkSelectAllJournal').prop('checked', false);
                                    journalTable.draw();
                                    reloadStats();
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Failed', text: res.message || 'Bulk delete failed.' });
                                }
                            },
                            error: function (xhr) {
                                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error performing bulk delete.';
                                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                            }
                        });
                    }
                });
            });

            // Bulk Edit Journal Entries
            $(document).on('click', '.btn-bulk-edit-journal', function () {
                var journalIds = [];
                $('#journal-datatable tbody .bulk-select-row:checked').each(function () {
                    if ($(this).data('source') === 'journal') {
                        journalIds.push($(this).val());
                    }
                });

                var totalSelected = $('#journal-datatable tbody .bulk-select-row:checked').length;
                if (totalSelected === 0) {
                    Swal.fire({ icon: 'warning', title: 'Selection Empty', text: 'No transactions selected.' });
                    return;
                }

                if (journalIds.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Notice',
                        text: 'Bulk edit is available for manual journal vouchers. The selected entries are from other modules (Expenses / Salary / Bill Payments); please edit them in their respective modules.'
                    });
                    return;
                }

                $('#bulkEditCountBadgeJournal').text(journalIds.length + ' journal voucher(s)');
                $('#bulkEditJournalForm')[0].reset();
                $('#bulkEditJournalErrors').addClass('d-none').html('');
                $('#bulkEditJournalModal').modal('show');
            });

            $('#bulkEditJournalForm').on('submit', function (e) {
                e.preventDefault();
                var journalIds = [];
                $('#journal-datatable tbody .bulk-select-row:checked').each(function () {
                    if ($(this).data('source') === 'journal') {
                        journalIds.push($(this).val());
                    }
                });

                if (journalIds.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Selection Empty', text: 'No journal vouchers selected.' });
                    return;
                }

                var formData = $(this).serializeArray();
                formData.push({ name: '_token', value: "{{ csrf_token() }}" });
                journalIds.forEach(function (id) {
                    formData.push({ name: 'ids[]', value: id });
                });

                var $btn = $('#btnApplyBulkEditJournal');
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

                $.ajax({
                    type: "POST",
                    url: "{{ route('accounts.journal.bulk_update') }}",
                    data: formData,
                    success: function (res) {
                        $btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Apply Changes');
                        if (res.success) {
                            $('#bulkEditJournalModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: res.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                            $('#bulkSelectAllJournal').prop('checked', false);
                            journalTable.draw();
                            reloadStats();
                        } else {
                            $('#bulkEditJournalErrors').removeClass('d-none').html(res.message || 'Bulk update failed.');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Apply Changes');
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update selected journal vouchers.';
                        $('#bulkEditJournalErrors').removeClass('d-none').html(msg);
                    }
                });
            });
        });
    </script>
@endpush
