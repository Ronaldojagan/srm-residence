@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .loan-stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                border: 1px solid #e2e8f0;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .loan-stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            }
            .nav-tabs-custom .nav-link {
                font-weight: 600;
                color: #64748b;
                border: none;
                border-bottom: 3px solid transparent;
                padding: 0.75rem 1.25rem;
                border-radius: 0;
            }
            .nav-tabs-custom .nav-link.active {
                color: #0a5858;
                border-bottom-color: #0a5858;
                background: transparent;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #0a5858 !important;
                color: #fff !important;
                border-color: #0a5858 !important;
                border-radius: 5px;
            }
            .filter-control {
                border-radius: 8px;
                border: 1px solid #cbd5e1;
                padding: 0.4rem 0.75rem;
                font-size: 0.875rem;
            }
            .font-11 { font-size: 11px; }
            .font-12 { font-size: 12px; }
            .font-13 { font-size: 13px; }
            .font-14 { font-size: 14px; }
            .bg-purple-subtle { background-color: #f3e8ff !important; }
            .text-purple { color: #7e22ce !important; }
            .border-purple { border-color: #c084fc !important; }
            .schedule-timeline-item {
                border-left: 2px dashed #cbd5e1;
                padding-left: 1rem;
                position: relative;
                margin-bottom: 1rem;
            }
            .schedule-timeline-item::before {
                content: '';
                position: absolute;
                left: -7px;
                top: 4px;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #0a5858;
            }
            .schedule-timeline-item.paid::before { background: #10b981; }
            .schedule-timeline-item.overdue::before { background: #ef4444; }
            .schedule-timeline-item.upcoming::before { background: #f59e0b; }
        </style>
    @endpush

    <!-- Page Header & Action Buttons -->
    <div class="row justify-content-between align-items-center mt-3 g-2">
        <div class="col-md-7">
            <h4 class="page-title-main page mb-1">
                <i class="fas fa-hand-holding-usd text-success me-2"></i>Loans & EMI Management
            </h4>
            <p class="text-muted small mb-0">
                Track active loans, financier details, interest rates, monthly EMI obligations, auto-schedules, and repayment ledger.
            </p>
        </div>
        <div class="col-md-5 text-md-end text-start">
            <button type="button" class="btn btn-outline-success waves-effect waves-light me-1" data-bs-toggle="modal" data-bs-target="#recordEmiModal">
                <i class="fas fa-credit-card me-1"></i> Record EMI
            </button>
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                <i class="fas fa-plus-circle me-1"></i> Add Loan
            </button>
        </div>
    </div>

    <!-- Quick Stat Metric Cards -->
    <div class="row mt-3 g-3">
        <div class="col-xl-3 col-md-6">
            <div class="loan-stat-card border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Active Loans</div>
                        <h3 class="fw-bold my-1 text-primary" id="statTotalActive">{{ $stats['totalActive'] ?? 0 }}</h3>
                        <small class="text-muted">Currently servicing</small>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary">
                        <i class="fas fa-file-invoice-dollar fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="loan-stat-card border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Total Borrowed</div>
                        <h3 class="fw-bold my-1 text-info" id="statTotalBorrowed">{{ $stats['totalBorrowedFormatted'] ?? '₹0.00' }}</h3>
                        <small class="text-muted">Total principal across loans</small>
                    </div>
                    <div class="rounded-circle p-3 bg-info-subtle text-info">
                        <i class="fas fa-university fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="loan-stat-card border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Total Repaid</div>
                        <h3 class="fw-bold my-1 text-success" id="statTotalRepaid">{{ $stats['totalRepaidFormatted'] ?? '₹0.00' }}</h3>
                        <small class="text-muted">Principal + Interest cleared</small>
                    </div>
                    <div class="rounded-circle p-3 bg-success-subtle text-success">
                        <i class="fas fa-check-double fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="loan-stat-card border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Monthly EMI Outflow</div>
                        <h3 class="fw-bold my-1 text-danger" id="statMonthlyOutflow">{{ $stats['monthlyEmiOutflowFormatted'] ?? '₹0.00' }}</h3>
                        <small class="text-muted">Commitment due every month</small>
                    </div>
                    <div class="rounded-circle p-3 bg-danger-subtle text-danger">
                        <i class="fas fa-calendar-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Tabs: Loans List & EMI Ledger -->
    <div class="card mt-4 border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom pt-3 pb-0">
            <ul class="nav nav-tabs nav-tabs-custom card-header-tabs" id="loanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loansTabPane" type="button" role="tab">
                        <i class="fas fa-list-ul me-2"></i>Loans & Borrowings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="emis-tab" data-bs-toggle="tab" data-bs-target="#emisTabPane" type="button" role="tab">
                        <i class="fas fa-history me-2"></i>EMI Payment Ledger
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-3">
            <div class="tab-content" id="loanTabsContent">
                <!-- TAB 1: LOANS LIST -->
                <div class="tab-pane fade show active" id="loansTabPane" role="tabpanel">
                    <!-- Filters row -->
                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-md-3 col-sm-6">
                            <label class="font-11 text-muted mb-1">Filter by Status</label>
                            <select id="filterLoanStatus" class="form-select filter-control">
                                <option value="">All Statuses (Active, Closed, Overdue)</option>
                                <option value="active" selected>Active Loans Only</option>
                                <option value="closed">Closed / Fully Paid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="font-11 text-muted mb-1">Filter by Loan Type</label>
                            <select id="filterLoanType" class="form-select filter-control">
                                <option value="">All Loan Types</option>
                                <option value="business">Business Loan</option>
                                <option value="personal">Personal Loan</option>
                                <option value="property">Property / Mortgage</option>
                                <option value="appliance">Appliance / Electronics</option>
                                <option value="vehicle">Vehicle Loan</option>
                                <option value="hand_loan">Hand Loan / Private</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-8">
                            <label class="font-11 text-muted mb-1">Search Lender / Bank</label>
                            <input type="text" id="filterLenderName" class="form-control filter-control" placeholder="e.g. HDFC, SBI, Bajaj...">
                        </div>
                        <div class="col-md-2 col-sm-4 text-end mt-auto">
                            <button type="button" id="btnResetLoanFilters" class="btn btn-sm btn-light border w-100 font-12 py-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- Loans Table -->
                    <div class="table-responsive">
                        <table id="loansTable" class="table table-hover align-middle mb-0 w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="20%">Loan / Title</th>
                                    <th width="15%">Lender & Type</th>
                                    <th width="18%">Principal & EMI</th>
                                    <th width="18%">Repayment Progress</th>
                                    <th width="12%">Next Due</th>
                                    <th width="8%">Status</th>
                                    <th width="9%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: EMI LEDGER -->
                <div class="tab-pane fade" id="emisTabPane" role="tabpanel">
                    <!-- Filters row -->
                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-md-3 col-sm-6">
                            <label class="font-11 text-muted mb-1">Filter by Loan Account</label>
                            <select id="filterEmiLoan" class="form-select filter-control">
                                <option value="">All Loan Accounts</option>
                                @foreach($loans as $l)
                                    <option value="{{ $l->id }}">{{ $l->loan_title }} ({{ $l->lender_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="font-11 text-muted mb-1">Payment Method</label>
                            <select id="filterEmiMethod" class="form-select filter-control">
                                <option value="">All Payment Methods</option>
                                <option value="Auto-Debit / NACH">Auto-Debit / NACH</option>
                                <option value="Bank Transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                                <option value="UPI">UPI</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="font-11 text-muted mb-1">From Date</label>
                            <input type="date" id="filterEmiStartDate" class="form-control filter-control">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="font-11 text-muted mb-1">To Date</label>
                            <input type="date" id="filterEmiEndDate" class="form-control filter-control">
                        </div>
                        <div class="col-md-2 col-sm-12 text-end mt-auto">
                            <button type="button" id="btnResetEmiFilters" class="btn btn-sm btn-light border w-100 font-12 py-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- EMIs Table -->
                    <div class="table-responsive">
                        <table id="emisTable" class="table table-hover align-middle mb-0 w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="24%">Loan / Lender</th>
                                    <th width="15%">Installment</th>
                                    <th width="14%">Payment Date</th>
                                    <th width="14%">Amount</th>
                                    <th width="14%">Method / Ref</th>
                                    <th width="8%">Receipt</th>
                                    <th width="6%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: ADD LOAN ACCOUNT -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="addLoanModal" tabindex="-1" aria-labelledby="addLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="addLoanForm">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="addLoanModalLabel">
                            <i class="fas fa-plus-circle text-primary me-2"></i>Add New Loan / EMI Account
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label font-13 fw-semibold">Loan Title / Purpose <span class="text-danger">*</span></label>
                                <input type="text" name="loan_title" class="form-control" placeholder="e.g. SRM Residence Building Refurbishment, Solar Setup" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label font-13 fw-semibold">Loan Category / Type <span class="text-danger">*</span></label>
                                <select name="loan_type" class="form-select" required>
                                    <option value="business">Business / Commercial</option>
                                    <option value="personal">Personal Loan</option>
                                    <option value="property">Property / Mortgage</option>
                                    <option value="appliance">Appliance / Electronics</option>
                                    <option value="vehicle">Vehicle Loan</option>
                                    <option value="hand_loan">Hand Loan / Private Borrowing</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Lender / Financier Name <span class="text-danger">*</span></label>
                                <input type="text" name="lender_name" class="form-control" placeholder="e.g. HDFC Bank, SBI, Bajaj Finserv, Private Lender" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Loan Account # / LAN <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="account_number" class="form-control" placeholder="e.g. LAN-894729104">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Principal Borrowed (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="principal_amount" id="add_principal_amount" class="form-control" placeholder="500000" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Interest Rate (% p.a.)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="interest_rate" id="add_interest_rate" class="form-control" placeholder="10.5" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Tenure (Months) <span class="text-danger">*</span></label>
                                <input type="number" name="tenure_months" id="add_tenure_months" class="form-control" placeholder="24" required>
                            </div>

                            <!-- EMI Auto Calculator Helper Bar -->
                            <div class="col-12">
                                <div class="p-2 bg-light rounded border d-flex justify-content-between align-items-center">
                                    <span class="font-12 text-muted">
                                        <i class="fas fa-calculator me-1 text-primary"></i> Need to calculate monthly EMI?
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary font-11 py-1" id="btnAutoCalculateEmi">
                                        <i class="fas fa-magic me-1"></i> Auto-Calculate EMI
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Monthly EMI Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="emi_amount" id="add_emi_amount" class="form-control" placeholder="22500" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">EMI Due Day of Month <span class="text-danger">*</span></label>
                                <select name="emi_day" class="form-select" required>
                                    @for($d = 1; $d <= 31; $d++)
                                        <option value="{{ $d }}" {{ $d == 5 ? 'selected' : '' }}>{{ $d }}{{ in_array($d, [1, 21, 31]) ? 'st' : (in_array($d, [2, 22]) ? 'nd' : (in_array($d, [3, 23]) ? 'rd' : 'th')) }} of every month</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Start / First EMI Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Debited Bank Account <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="bank_account_debited" class="form-control" placeholder="e.g. SBI Current A/C ***2248">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Notes / Reference <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="notes" class="form-control" placeholder="e.g. ECS Auto-debit active on 5th">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary font-12" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success font-12 px-3" id="btnAddLoanSubmit">
                            <i class="fas fa-save me-1"></i> Save Loan Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: EDIT LOAN ACCOUNT -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="editLoanModal" tabindex="-1" aria-labelledby="editLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="editLoanForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="loan_id" id="edit_loan_id">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="editLoanModalLabel">
                            <i class="fas fa-edit text-primary me-2"></i>Edit Loan Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label font-13 fw-semibold">Loan Title / Purpose <span class="text-danger">*</span></label>
                                <input type="text" name="loan_title" id="edit_loan_title" class="form-control" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label font-13 fw-semibold">Loan Category / Type <span class="text-danger">*</span></label>
                                <select name="loan_type" id="edit_loan_type" class="form-select" required>
                                    <option value="business">Business / Commercial</option>
                                    <option value="personal">Personal Loan</option>
                                    <option value="property">Property / Mortgage</option>
                                    <option value="appliance">Appliance / Electronics</option>
                                    <option value="vehicle">Vehicle Loan</option>
                                    <option value="hand_loan">Hand Loan / Private Borrowing</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Lender / Financier Name <span class="text-danger">*</span></label>
                                <input type="text" name="lender_name" id="edit_lender_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Loan Account # / LAN</label>
                                <input type="text" name="account_number" id="edit_account_number" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Principal Borrowed (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="principal_amount" id="edit_principal_amount" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Interest Rate (% p.a.)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="interest_rate" id="edit_interest_rate" class="form-control">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Tenure (Months) <span class="text-danger">*</span></label>
                                <input type="number" name="tenure_months" id="edit_tenure_months" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Monthly EMI Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="emi_amount" id="edit_emi_amount" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">EMI Due Day of Month <span class="text-danger">*</span></label>
                                <select name="emi_day" id="edit_emi_day" class="form-select" required>
                                    @for($d = 1; $d <= 31; $d++)
                                        <option value="{{ $d }}">{{ $d }}th of month</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="edit_start_date" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Account Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="active">Active (Ongoing EMI)</option>
                                    <option value="closed">Closed (Fully Paid)</option>
                                    <option value="overdue">Overdue</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Debited Bank Account</label>
                                <input type="text" name="bank_account_debited" id="edit_bank_account_debited" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Notes / Reference</label>
                                <input type="text" name="notes" id="edit_notes" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary font-12" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary font-12 px-3" id="btnEditLoanSubmit">
                            <i class="fas fa-check-circle me-1"></i> Update Loan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: RECORD EMI PAYMENT -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="recordEmiModal" tabindex="-1" aria-labelledby="recordEmiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="recordEmiForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="recordEmiModalLabel">
                            <i class="fas fa-credit-card text-success me-2"></i>Record EMI Installment Payment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label font-13 fw-semibold">Select Loan Account <span class="text-danger">*</span></label>
                                <select name="loan_id" id="emi_loan_id" class="form-select" required>
                                    <option value="">-- Choose Loan --</option>
                                    @foreach($loans->where('status', '!=', 'closed') as $l)
                                        <option value="{{ $l->id }}" data-emi="{{ $l->emi_amount }}" data-tenure="{{ $l->tenure_months }}" data-repaid="{{ $l->total_repaid }}" data-title="{{ $l->loan_title }}">
                                            {{ $l->loan_title }} — EMI: ₹{{ number_format($l->emi_amount, 2) }} ({{ $l->lender_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Installment # <span class="text-danger">*</span></label>
                                <input type="number" name="installment_number" id="emi_installment_number" class="form-control" placeholder="e.g. 1" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" id="emi_payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Amount Paid (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="amount" id="emi_amount" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="emi_payment_method" class="form-select" required>
                                    <option value="Auto-Debit / NACH">Auto-Debit / NACH</option>
                                    <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                                    <option value="UPI" selected>UPI</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label font-13 fw-semibold">Transaction ID / UTR / Reference</label>
                                <input type="text" name="transaction_id" id="emi_transaction_id" class="form-control" placeholder="e.g. UTR-308472910482">
                            </div>

                            <div class="col-12">
                                <label class="form-label font-13 fw-semibold">Payment Receipt / Bank Slip <small class="text-muted">(Optional, Max 5MB)</small></label>
                                <input type="file" name="receipt" id="emi_receipt" class="form-control" accept="image/png,image/jpeg,application/pdf">
                            </div>

                            <div class="col-12">
                                <label class="form-label font-13 fw-semibold">Notes</label>
                                <input type="text" name="notes" id="emi_notes" class="form-control" placeholder="e.g. Month of November 2026 EMI">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary font-12" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success font-12 px-3" id="btnRecordEmiSubmit">
                            <i class="fas fa-check-circle me-1"></i> Confirm Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 4: VIEW EMI SCHEDULE & LEDGER -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="viewScheduleModal" tabindex="-1" aria-labelledby="viewScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="viewScheduleModalLabel">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>EMI Schedule & Repayment Ledger
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Loan Summary Header in Modal -->
                    <div class="card border mb-3 bg-light-subtle">
                        <div class="card-body p-3">
                            <div class="row align-items-center g-3">
                                <div class="col-md-4">
                                    <h5 class="fw-bold text-dark mb-1" id="sched_loan_title">-</h5>
                                    <div class="text-muted font-12"><i class="fas fa-landmark me-1"></i>Lender: <span id="sched_lender_name">-</span></div>
                                    <div class="mt-1"><span class="badge bg-secondary font-11" id="sched_status_badge">-</span></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between font-12 mb-1">
                                        <span class="text-muted">Principal: <strong class="text-dark" id="sched_principal">-</strong></span>
                                        <span class="text-muted">EMI: <strong class="text-danger" id="sched_emi">-</strong></span>
                                    </div>
                                    <div class="d-flex justify-content-between font-12 mb-1">
                                        <span class="text-muted">Repaid: <strong class="text-success" id="sched_repaid">-</strong></span>
                                        <span class="text-muted">Balance: <strong class="text-danger" id="sched_balance">-</strong></span>
                                    </div>
                                    <div class="progress mt-2" style="height: 7px;">
                                        <div class="progress-bar bg-success" id="sched_progress_bar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end text-start">
                                    <button type="button" class="btn btn-sm btn-success font-12 px-3 py-2" id="btnSchedPayNextEmi">
                                        <i class="fas fa-credit-card me-1"></i> Pay Next Installment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Installments Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 font-13" id="scheduleTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%" class="text-center"># Inst.</th>
                                    <th width="15%">Scheduled Due Date</th>
                                    <th width="15%">EMI Amount</th>
                                    <th width="15%">Payment Date</th>
                                    <th width="18%">Method / UTR</th>
                                    <th width="14%">Status</th>
                                    <th width="15%">Notes</th>
                                </tr>
                            </thead>
                            <tbody id="scheduleTableBody">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-12" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true
                });

                // =========================================================================
                // 1. DATA TABLE: LOANS
                // =========================================================================
                var loansTable = $('#loansTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    order: [[0, 'desc']],
                    ajax: {
                        url: "{{ route('loans.index') }}",
                        data: function(d) {
                            d.table = 'loans';
                            d.status = $('#filterLoanStatus').val();
                            d.loan_type = $('#filterLoanType').val();
                            d.lender_name = $('#filterLenderName').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'loan_title_badge', name: 'loan_title' },
                        { data: 'lender_and_type', name: 'lender_name' },
                        { data: 'principal_and_emi', name: 'principal_amount' },
                        { data: 'repayment_progress', name: 'total_repaid' },
                        { data: 'next_due_date', name: 'start_date' },
                        { data: 'status_badge', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    language: {
                        emptyTable: "No loan accounts recorded yet. Click '+ Add Loan' above to create one."
                    }
                });

                $('#filterLoanStatus, #filterLoanType').on('change', function() {
                    loansTable.draw();
                });
                $('#filterLenderName').on('keyup', function() {
                    loansTable.draw();
                });
                $('#btnResetLoanFilters').on('click', function() {
                    $('#filterLoanStatus').val('');
                    $('#filterLoanType').val('');
                    $('#filterLenderName').val('');
                    loansTable.draw();
                });

                // =========================================================================
                // 2. DATA TABLE: EMI LEDGER
                // =========================================================================
                var emisTable = $('#emisTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    order: [[0, 'desc']],
                    ajax: {
                        url: "{{ route('loans.index') }}",
                        data: function(d) {
                            d.table = 'emis';
                            d.loan_id = $('#filterEmiLoan').val();
                            d.payment_method = $('#filterEmiMethod').val();
                            d.start_date = $('#filterEmiStartDate').val();
                            d.end_date = $('#filterEmiEndDate').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'loan_info', name: 'loan.loan_title' },
                        { data: 'installment_badge', name: 'installment_number' },
                        { data: 'payment_date_formatted', name: 'payment_date' },
                        { data: 'amount_display', name: 'amount' },
                        { data: 'payment_method_badge', name: 'payment_method' },
                        { data: 'receipt_link', name: 'receipt', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    language: {
                        emptyTable: "No EMI payments recorded yet."
                    }
                });

                $('#filterEmiLoan, #filterEmiMethod, #filterEmiStartDate, #filterEmiEndDate').on('change', function() {
                    emisTable.draw();
                });
                $('#btnResetEmiFilters').on('click', function() {
                    $('#filterEmiLoan').val('');
                    $('#filterEmiMethod').val('');
                    $('#filterEmiStartDate').val('');
                    $('#filterEmiEndDate').val('');
                    emisTable.draw();
                });

                // =========================================================================
                // 3. REFRESH STATS FUNCTION
                // =========================================================================
                function reloadStats() {
                    $.ajax({
                        url: "{{ route('loans.stats') }}",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.stats) {
                                $('#statTotalActive').text(res.stats.totalActive);
                                $('#statTotalBorrowed').text(res.stats.totalBorrowedFormatted);
                                $('#statTotalRepaid').text(res.stats.totalRepaidFormatted);
                                $('#statMonthlyOutflow').text(res.stats.monthlyEmiOutflowFormatted);
                            }
                        }
                    });
                }

                // =========================================================================
                // 4. AUTO-CALCULATE EMI HELPER (Reducing Balance & Simple Interest)
                // =========================================================================
                $('#btnAutoCalculateEmi').on('click', function() {
                    var p = parseFloat($('#add_principal_amount').val());
                    var rAnnual = parseFloat($('#add_interest_rate').val()) || 0;
                    var n = parseInt($('#add_tenure_months').val());

                    if (!p || p <= 0 || !n || n <= 0) {
                        Toast.fire({ icon: 'warning', title: 'Please enter Principal Amount and Tenure Months first!' });
                        return;
                    }

                    var emi = 0;
                    if (rAnnual > 0) {
                        var rMonthly = (rAnnual / 12) / 100;
                        // EMI = [P x R x (1+R)^N]/[(1+R)^N-1]
                        var emiVal = (p * rMonthly * Math.pow(1 + rMonthly, n)) / (Math.pow(1 + rMonthly, n) - 1);
                        emi = Math.round(emiVal);
                    } else {
                        // 0% interest loan
                        emi = Math.round(p / n);
                    }

                    $('#add_emi_amount').val(emi);
                    Toast.fire({ icon: 'info', title: 'Calculated Monthly EMI: ₹' + emi.toLocaleString('en-IN') });
                });

                // =========================================================================
                // 5. ADD LOAN ACCOUNT AJAX
                // =========================================================================
                $('#addLoanForm').on('submit', function(e) {
                    e.preventDefault();
                    var $btn = $('#btnAddLoanSubmit');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

                    $.ajax({
                        url: "{{ route('loans.store') }}",
                        type: "POST",
                        data: $(this).serialize(),
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Loan Account');
                            if (res.success) {
                                $('#addLoanModal').modal('hide');
                                $('#addLoanForm')[0].reset();
                                loansTable.draw();
                                reloadStats();
                                Toast.fire({ icon: 'success', title: res.message });
                                // Also update dropdown in Record EMI modal
                                if (res.loan) {
                                    $('#emi_loan_id').append('<option value="' + res.loan.id + '" data-emi="' + res.loan.emi_amount + '" data-tenure="' + res.loan.tenure_months + '" data-title="' + res.loan.loan_title + '">' + res.loan.loan_title + ' (EMI: ₹' + res.loan.emi_amount + ')</option>');
                                    $('#filterEmiLoan').append('<option value="' + res.loan.id + '">' + res.loan.loan_title + '</option>');
                                }
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Loan Account');
                            var msg = 'Failed to create loan account.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors)[0][0];
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: msg });
                        }
                    });
                });

                // =========================================================================
                // 6. EDIT LOAN DETAILS AJAX
                // =========================================================================
                $(document).on('click', '.btn-edit-loan', function() {
                    var id = $(this).data('id');
                    $.ajax({
                        url: "{{ url('/admin/loans') }}/" + id + "/data",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.loan) {
                                var l = res.loan;
                                $('#edit_loan_id').val(l.id);
                                $('#edit_loan_title').val(l.loan_title);
                                $('#edit_loan_type').val(l.loan_type);
                                $('#edit_lender_name').val(l.lender_name);
                                $('#edit_account_number').val(l.account_number);
                                $('#edit_principal_amount').val(l.principal_amount);
                                $('#edit_interest_rate').val(l.interest_rate);
                                $('#edit_tenure_months').val(l.tenure_months);
                                $('#edit_emi_amount').val(l.emi_amount);
                                $('#edit_emi_day').val(l.emi_day || 5);
                                $('#edit_start_date').val(l.start_date_formatted || (l.start_date ? l.start_date.substring(0, 10) : ''));
                                $('#edit_status').val(l.status);
                                $('#edit_bank_account_debited').val(l.bank_account_debited);
                                $('#edit_notes').val(l.notes);

                                $('#editLoanModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to load loan details.' });
                        }
                    });
                });

                $('#editLoanForm').on('submit', function(e) {
                    e.preventDefault();
                    var id = $('#edit_loan_id').val();
                    var $btn = $('#btnEditLoanSubmit');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');

                    $.ajax({
                        url: "{{ url('/admin/loans') }}/" + id,
                        type: "POST",
                        data: $(this).serialize(),
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Update Loan');
                            if (res.success) {
                                $('#editLoanModal').modal('hide');
                                loansTable.draw();
                                reloadStats();
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Update Loan');
                            var msg = 'Failed to update loan.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors)[0][0];
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: msg });
                        }
                    });
                });

                // =========================================================================
                // 7. DELETE LOAN ACCOUNT AJAX
                // =========================================================================
                $(document).on('click', '.btn-delete-loan', function() {
                    var id = $(this).data('id');
                    var title = $(this).data('title') || 'this loan account';

                    Swal.fire({
                        title: "Delete Loan Account?",
                        text: "Are you sure you want to delete '" + title + "' and all its recorded EMI installments? This cannot be undone.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#dc3545",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/loans') }}/" + id,
                                type: "DELETE",
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                success: function(res) {
                                    if (res.success) {
                                        loansTable.draw();
                                        emisTable.draw();
                                        reloadStats();
                                        Toast.fire({ icon: 'success', title: res.message });
                                    }
                                },
                                error: function() {
                                    Toast.fire({ icon: 'error', title: 'Failed to delete loan.' });
                                }
                            });
                        }
                    });
                });

                // =========================================================================
                // 8. RECORD EMI PAYMENT MODAL & LOGIC
                // =========================================================================
                $(document).on('click', '.btn-record-loan-emi', function() {
                    var loanId = $(this).data('id');
                    var amount = $(this).data('amount');
                    $('#emi_loan_id').val(loanId).trigger('change');
                    if (amount) {
                        $('#emi_amount').val(amount);
                    }
                    $('#recordEmiModal').modal('show');
                });

                // Auto-fill EMI amount and next installment when loan is picked
                $('#emi_loan_id').on('change', function() {
                    var loanId = $(this).val();
                    if (!loanId) return;

                    var $opt = $(this).find('option:selected');
                    var emi = $opt.data('emi');
                    if (emi) {
                        $('#emi_amount').val(emi);
                    }

                    // Query schedule endpoint to fetch next installment number
                    $.ajax({
                        url: "{{ url('/admin/loans') }}/" + loanId + "/schedule",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.loan) {
                                $('#emi_installment_number').val(res.loan.next_installment);
                            }
                        }
                    });
                });

                $('#recordEmiForm').on('submit', function(e) {
                    e.preventDefault();
                    var loanId = $('#emi_loan_id').val();
                    if (!loanId) {
                        Toast.fire({ icon: 'warning', title: 'Please select a Loan Account first!' });
                        return;
                    }

                    var formData = new FormData(this);
                    var $btn = $('#btnRecordEmiSubmit');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Recording...');

                    $.ajax({
                        url: "{{ url('/admin/loans') }}/" + loanId + "/record-emi",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Confirm Payment');
                            if (res.success) {
                                $('#recordEmiModal').modal('hide');
                                $('#recordEmiForm')[0].reset();
                                loansTable.draw();
                                emisTable.draw();
                                reloadStats();
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Confirm Payment');
                            var msg = 'Failed to record EMI payment.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors)[0][0];
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: msg });
                        }
                    });
                });

                // =========================================================================
                // 9. DELETE EMI INSTALLMENT RECORD AJAX
                // =========================================================================
                $(document).on('click', '.btn-delete-loan-emi', function() {
                    var id = $(this).data('id');
                    var inst = $(this).data('inst') || '';

                    Swal.fire({
                        title: "Delete EMI Payment?",
                        text: "Are you sure you want to delete payment record for Installment #" + inst + "? The loan balance will be automatically adjusted.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#dc3545",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/loans/emis') }}/" + id,
                                type: "DELETE",
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                success: function(res) {
                                    if (res.success) {
                                        loansTable.draw();
                                        emisTable.draw();
                                        reloadStats();
                                        Toast.fire({ icon: 'success', title: res.message });
                                    }
                                },
                                error: function() {
                                    Toast.fire({ icon: 'error', title: 'Failed to delete payment record.' });
                                }
                            });
                        }
                    });
                });

                // =========================================================================
                // 10. VIEW SCHEDULE & REPAYMENT LEDGER MODAL
                // =========================================================================
                var currentScheduleLoanId = null;

                $(document).on('click', '.btn-view-loan-schedule', function() {
                    var id = $(this).data('id');
                    currentScheduleLoanId = id;
                    loadLoanSchedule(id);
                });

                function loadLoanSchedule(loanId) {
                    $('#scheduleTableBody').html('<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading EMI schedule...</td></tr>');
                    $('#viewScheduleModal').modal('show');

                    $.ajax({
                        url: "{{ url('/admin/loans') }}/" + loanId + "/schedule",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.loan) {
                                var l = res.loan;
                                $('#sched_loan_title').text(l.loan_title);
                                $('#sched_lender_name').text(l.lender_name);
                                $('#sched_principal').text(l.principal_amount);
                                $('#sched_emi').text(l.emi_amount);
                                $('#sched_repaid').text(l.total_repaid);
                                $('#sched_balance').text(l.outstanding_balance);
                                $('#sched_status_badge').text(l.status);
                                $('#sched_progress_bar').css('width', l.progress_percentage + '%');

                                if (l.status === 'CLOSED') {
                                    $('#btnSchedPayNextEmi').addClass('d-none');
                                } else {
                                    $('#btnSchedPayNextEmi').removeClass('d-none').data('id', l.id).data('next', l.next_installment);
                                }

                                var rows = '';
                                if (res.schedule && res.schedule.length > 0) {
                                    $.each(res.schedule, function(idx, item) {
                                        var badge = '';
                                        if (item.status === 'Paid') {
                                            badge = '<span class="badge bg-success font-11 px-2 py-1"><i class="fas fa-check-circle me-1"></i>Paid</span>';
                                        } else if (item.status === 'Overdue') {
                                            badge = '<span class="badge bg-danger font-11 px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</span>';
                                        } else {
                                            badge = '<span class="badge bg-warning text-dark font-11 px-2 py-1"><i class="fas fa-clock me-1"></i>Upcoming</span>';
                                        }

                                        rows += '<tr>' +
                                                    '<td class="text-center fw-bold">#' + item.installment_number + '</td>' +
                                                    '<td>' + item.due_date + '</td>' +
                                                    '<td class="fw-semibold text-danger">₹' + parseFloat(item.amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                                                    '<td>' + (item.payment_date ? '<span class="text-success fw-semibold">' + item.payment_date + '</span>' : '<span class="text-muted">-</span>') + '</td>' +
                                                    '<td>' + (item.payment_method ? '<span class="badge bg-light text-dark border">' + item.payment_method + '</span>' + (item.transaction_id ? '<small class="d-block text-muted font-11">' + item.transaction_id + '</small>' : '') : '<span class="text-muted">-</span>') + '</td>' +
                                                    '<td>' + badge + '</td>' +
                                                    '<td>' + (item.notes ? item.notes : '<span class="text-muted font-11">-</span>') + '</td>' +
                                                '</tr>';
                                    });
                                } else {
                                    rows = '<tr><td colspan="7" class="text-center text-muted py-3">No schedule available.</td></tr>';
                                }

                                $('#scheduleTableBody').html(rows);
                            }
                        },
                        error: function() {
                            $('#scheduleTableBody').html('<tr><td colspan="7" class="text-center text-danger py-3">Failed to load schedule.</td></tr>');
                        }
                    });
                }

                $('#btnSchedPayNextEmi').on('click', function() {
                    var loanId = $(this).data('id');
                    $('#viewScheduleModal').modal('hide');
                    $('#emi_loan_id').val(loanId).trigger('change');
                    $('#recordEmiModal').modal('show');
                });
            });
        </script>
    @endpush
@endsection
