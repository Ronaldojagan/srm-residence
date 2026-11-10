@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .salary-stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                border: 1px solid #e2e8f0;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .salary-stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
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
            .net-calculator-box {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 12px 16px;
            }
            .voucher-printable {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 24px;
            }
            @media print {
                body * { visibility: hidden; }
                #printableVoucherArea, #printableVoucherArea * { visibility: visible; }
                #printableVoucherArea { position: absolute; left: 0; top: 0; width: 100%; border: none !important; }
            }
        </style>
    @endpush

    <!-- Page Header & Action Buttons -->
    <div class="row justify-content-between align-items-center mt-3 g-2">
        <div class="col-md-7">
            <h4 class="page-title-main page mb-1">
                <i class="fas fa-wallet text-success me-2"></i>Family Salary Income Management
            </h4>
            <p class="text-muted small mb-0">
                Track monthly salary income received by our family members (Jagan, family earners) brought into residence and personal funds.
            </p>
        </div>
        <div class="col-md-5 text-md-end text-start">
            <button type="button" class="btn add-btn waves-effect waves-light button-color text-white px-3" data-bs-toggle="modal" data-bs-target="#addSalaryModal">
                <i class="fas fa-plus-circle me-1"></i> Record Family Salary Received
            </button>
        </div>
    </div>

    <!-- Quick Stat Metric Cards -->
    <div class="row mt-3 g-3">
        <div class="col-xl-3 col-md-6">
            <div class="salary-stat-card border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Total Family Salary (Year <span id="statYearLabel">{{ $stats['year'] }}</span>)</div>
                        <h3 class="fw-bold my-1 text-success" id="statTotalThisYear">{{ $stats['totalPaidThisYearFormatted'] }}</h3>
                        <small class="text-muted">Total net salary received</small>
                    </div>
                    <div class="rounded-circle p-3 bg-success-subtle text-success">
                        <i class="fas fa-coins fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="salary-stat-card border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">This Month Family Salary</div>
                        <h3 class="fw-bold my-1 text-primary" id="statThisMonth">{{ $stats['thisMonthSalaryFormatted'] }}</h3>
                        <small class="text-muted">Current month earnings received</small>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary">
                        <i class="fas fa-calendar-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="salary-stat-card border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Average Monthly Salary</div>
                        <h3 class="fw-bold my-1 text-info" id="statAvgMonthly">{{ $stats['averageMonthlySalaryFormatted'] }}</h3>
                        <small class="text-muted">Monthly earnings pace</small>
                    </div>
                    <div class="rounded-circle p-3 bg-info-subtle text-info">
                        <i class="fas fa-chart-line fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="salary-stat-card border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Salary Records</div>
                        <h3 class="fw-bold my-1 text-warning" id="statTotalEntries">{{ $stats['totalEntries'] }}</h3>
                        <small class="text-muted">Total statements logged</small>
                    </div>
                    <div class="rounded-circle p-3 bg-warning-subtle text-warning">
                        <i class="fas fa-file-invoice-dollar fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card & DataTables -->
    <div class="card mt-4 border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-list-alt text-primary me-2"></i>Personal Salary Ledger
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1 font-12">
                    <i class="fas fa-shield-alt me-1"></i>Owner Earnings & Drawings
                </span>
            </div>
        </div>

        <div class="card-body p-3">
            <!-- Filter Bar -->
            <div class="row g-2 mb-3 align-items-center">
                <div class="col-md-2 col-sm-6">
                    <label class="font-11 text-muted mb-1">Filter by Year</label>
                    <select id="filterSalaryYear" class="form-select filter-control">
                        @for($y = (int)date('Y'); $y >= (int)date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $y == (int)date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="font-11 text-muted mb-1">Filter by Month</label>
                    <input type="month" id="filterSalaryMonth" class="form-control filter-control">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="font-11 text-muted mb-1">Payment Status</label>
                    <select id="filterSalaryStatus" class="form-select filter-control">
                        <option value="">All Statuses</option>
                        <option value="credited" selected>Credited / Received</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="font-11 text-muted mb-1">Payment Mode</label>
                    <select id="filterSalaryMethod" class="form-select filter-control">
                        <option value="">All Methods</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="UPI">UPI</option>
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Direct Deposit">Direct Deposit</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-8">
                    <label class="font-11 text-muted mb-1">Search Earner / Title</label>
                    <input type="text" id="filterRecipientName" class="form-control filter-control" placeholder="e.g. Jagan, Ragavan, Job Salary...">
                </div>
                <div class="col-md-1 col-sm-4 text-end mt-auto">
                    <button type="button" id="btnResetSalaryFilters" class="btn btn-sm btn-light border w-100 font-12 py-2" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>

            <!-- Salary Table -->
            <div class="table-responsive">
                <table id="salaryTable" class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="4%">#</th>
                            <th width="14%">Salary Month</th>
                            <th width="22%">Family Earner & Title</th>
                            <th width="18%">Breakdown</th>
                            <th width="14%">Net Salary Received</th>
                            <th width="14%">Credit Date & Mode</th>
                            <th width="8%">Status</th>
                            <th width="6%">Document</th>
                            <th width="10%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: ADD SALARY CREDIT -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="addSalaryModal" tabindex="-1" aria-labelledby="addSalaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="addSalaryForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="addSalaryModalLabel">
                            <i class="fas fa-plus-circle text-success me-2"></i>Record Family Salary Income Received
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Salary Month & Year <span class="text-danger">*</span></label>
                                <input type="month" name="salary_month" class="form-control" value="{{ $currentMonth }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Family Member / Earner Name <span class="text-danger">*</span></label>
                                <input type="text" name="recipient_name" class="form-control" value="{{ Auth::user()->name ?? 'Jagan' }}" placeholder="e.g. Jagan, Ragavan" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label font-13 fw-semibold">Salary Title / Income Source <span class="text-danger">*</span></label>
                                <input type="text" name="salary_title" class="form-control" value="Monthly Job Salary" placeholder="e.g. Monthly Job Salary, IT / Company Salary, Professional Remuneration" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Base Salary (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="base_amount" id="add_base_amount" class="form-control" placeholder="50000" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Bonus / Allowances (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">+₹</span>
                                    <input type="number" step="0.01" name="bonus_incentive" id="add_bonus_incentive" class="form-control" placeholder="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Deductions / TDS (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">-₹</span>
                                    <input type="number" step="0.01" name="deductions" id="add_deductions" class="form-control" placeholder="0" value="0">
                                </div>
                            </div>

                            <!-- Live Net Salary Calculation Helper Box -->
                            <div class="col-12">
                                <div class="net-calculator-box d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted font-12 d-block">Calculated Net Salary Payable / Credited:</span>
                                        <small class="text-secondary font-11">(Base Amount + Bonus - Deductions)</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="fw-bold text-success mb-0" id="add_net_salary_preview">₹0.00</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Date of Credit <span class="text-danger">*</span></label>
                                <input type="date" name="credit_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="Bank Transfer" selected>Bank Transfer (NEFT/RTGS/IMPS)</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Direct Deposit">Direct Deposit</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Payment Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="credited" selected>Credited / Received</option>
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Credited to Bank Account <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="credited_to_bank" class="form-control" placeholder="e.g. HDFC Bank A/C ***8940, SBI Current">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Transaction ID / UTR Ref <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="transaction_reference" class="form-control" placeholder="e.g. UTR-98374829104">
                            </div>

                            <div class="col-12">
                                <label class="form-label font-13 fw-semibold">Payslip / Bank Credit Proof <small class="text-muted">(Optional, Max 5MB)</small></label>
                                <input type="file" name="payslip_document" class="form-control" accept="image/png,image/jpeg,application/pdf">
                            </div>

                            <div class="col-12">
                                <label class="form-label font-13 fw-semibold">Notes / Remarks <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="notes" class="form-control" placeholder="e.g. Cleared through official business account">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary font-12" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success font-12 px-3" id="btnAddSalarySubmit">
                            <i class="fas fa-save me-1"></i> Save Salary Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: EDIT SALARY RECORD -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="editSalaryModal" tabindex="-1" aria-labelledby="editSalaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="editSalaryForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="salary_id" id="edit_salary_id">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="editSalaryModalLabel">
                            <i class="fas fa-edit text-primary me-2"></i>Edit Salary Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Salary Month & Year <span class="text-danger">*</span></label>
                                <input type="month" name="salary_month" id="edit_salary_month" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Family Member / Earner Name <span class="text-danger">*</span></label>
                                <input type="text" name="recipient_name" id="edit_recipient_name" class="form-control" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label font-13 fw-semibold">Salary Title / Income Source <span class="text-danger">*</span></label>
                                <input type="text" name="salary_title" id="edit_salary_title" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Base Salary (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="base_amount" id="edit_base_amount" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Bonus / Allowances (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">+₹</span>
                                    <input type="number" step="0.01" name="bonus_incentive" id="edit_bonus_incentive" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Deductions / TDS (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">-₹</span>
                                    <input type="number" step="0.01" name="deductions" id="edit_deductions" class="form-control">
                                </div>
                            </div>

                            <!-- Live Net Salary Calculation Helper Box -->
                            <div class="col-12">
                                <div class="net-calculator-box d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted font-12 d-block">Calculated Net Salary Credited:</span>
                                        <small class="text-secondary font-11">(Base Amount + Bonus - Deductions)</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="fw-bold text-success mb-0" id="edit_net_salary_preview">₹0.00</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Date of Credit <span class="text-danger">*</span></label>
                                <input type="date" name="credit_date" id="edit_credit_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="edit_payment_method" class="form-select" required>
                                    <option value="Bank Transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Direct Deposit">Direct Deposit</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-13 fw-semibold">Payment Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="credited">Credited / Received</option>
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Credited to Bank Account</label>
                                <input type="text" name="credited_to_bank" id="edit_credited_to_bank" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-13 fw-semibold">Transaction ID / UTR Ref</label>
                                <input type="text" name="transaction_reference" id="edit_transaction_reference" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label font-13 fw-semibold">Update Document / Slip <small class="text-muted">(Optional)</small></label>
                                <input type="file" name="payslip_document" class="form-control" accept="image/png,image/jpeg,application/pdf">
                                <div id="edit_doc_preview" class="mt-1 font-11 text-muted"></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label font-13 fw-semibold">Notes / Remarks</label>
                                <input type="text" name="notes" id="edit_notes" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary font-12" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary font-12 px-3" id="btnEditSalarySubmit">
                            <i class="fas fa-check-circle me-1"></i> Update Salary Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: VIEW SALARY CREDIT VOUCHER -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="viewSalaryVoucherModal" tabindex="-1" aria-labelledby="viewSalaryVoucherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="viewSalaryVoucherModalLabel">
                        <i class="fas fa-receipt text-success me-2"></i>Official Salary Credit Voucher
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="printableVoucherArea">
                    <div class="voucher-printable">
                        <!-- Top Voucher Header -->
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                            <div>
                                <h4 class="fw-bold text-dark mb-0">SRM RESIDENCE</h4>
                                <small class="text-muted">Luxury Gents Hostel & PG Accommodation</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-1 font-12 fw-bold" id="v_status">CREDITED</span>
                                <small class="text-muted d-block mt-1 font-11">Voucher #<span id="v_id">-</span></small>
                            </div>
                        </div>

                        <!-- Recipient & Period Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="text-muted font-11 text-uppercase fw-bold">Family Earner / Member</label>
                                <h5 class="fw-bold text-dark mb-0" id="v_recipient">-</h5>
                                <div class="text-secondary font-12" id="v_title">-</div>
                            </div>
                            <div class="col-6 text-end">
                                <label class="text-muted font-11 text-uppercase fw-bold">Salary Period</label>
                                <h5 class="fw-bold text-primary mb-0" id="v_month">-</h5>
                                <div class="text-secondary font-12">Credit Date: <span id="v_date" class="fw-semibold text-dark">-</span></div>
                            </div>
                        </div>

                        <!-- Itemized Financial Breakdown Table -->
                        <table class="table table-bordered mb-4 font-13 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Component / Description</th>
                                    <th width="35%" class="text-end">Amount (INR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Base Monthly Salary</td>
                                    <td class="text-end fw-semibold" id="v_base">₹0.00</td>
                                </tr>
                                <tr>
                                    <td>Special Allowances / Performance Bonus / Incentives</td>
                                    <td class="text-end text-success fw-semibold" id="v_bonus">+ ₹0.00</td>
                                </tr>
                                <tr>
                                    <td>Statutory / Tax / Advance Deductions</td>
                                    <td class="text-end text-danger fw-semibold" id="v_deductions">- ₹0.00</td>
                                </tr>
                                <tr class="table-success">
                                    <td class="fw-bold fs-6">Net Salary Credited</td>
                                    <td class="text-end fw-bold fs-6 text-success" id="v_net">₹0.00</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Payment & Bank Metadata -->
                        <div class="row g-2 p-3 bg-light rounded border mb-3 font-12">
                            <div class="col-md-4">
                                <span class="text-muted">Payment Mode:</span>
                                <strong class="d-block text-dark" id="v_method">-</strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted">Bank Credited:</span>
                                <strong class="d-block text-dark" id="v_bank">-</strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted">Transaction UTR:</span>
                                <strong class="d-block text-dark" id="v_ref">-</strong>
                            </div>
                            <div class="col-12 mt-2" id="v_notes_row">
                                <span class="text-muted">Remarks:</span>
                                <span class="text-dark" id="v_notes">-</span>
                            </div>
                        </div>

                        <!-- Footer Signatures & Stamp -->
                        <div class="d-flex justify-content-between align-items-end pt-3 mt-4 border-top">
                            <div class="font-11 text-muted">
                                <div>Generated on: <span id="v_created">-</span></div>
                                <div>Official computerized salary payment statement.</div>
                            </div>
                            <div class="text-center" style="min-width: 160px;">
                                <div style="display: inline-block;">
                                    @include('backend.partials.residence_stamp')
                                </div>
                                <div class="font-11 fw-bold text-dark mt-1">AUTHORIZED SIGNATORY</div>
                                <div class="font-10 text-muted">SRM Residence</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-12" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="v_download_slip_btn" target="_blank" class="btn btn-outline-info font-12 d-none">
                        <i class="fas fa-paperclip me-1"></i> Attached Slip
                    </a>
                    <button type="button" class="btn btn-primary font-12 px-3" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Voucher
                    </button>
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
                // 1. DATA TABLE: SALARY LEDGER
                // =========================================================================
                var salaryTable = $('#salaryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    order: [[0, 'desc']],
                    ajax: {
                        url: "{{ route('salary.index') }}",
                        data: function(d) {
                            d.salary_year = $('#filterSalaryYear').val();
                            d.salary_month = $('#filterSalaryMonth').val();
                            d.status = $('#filterSalaryStatus').val();
                            d.payment_method = $('#filterSalaryMethod').val();
                            d.recipient_name = $('#filterRecipientName').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'month_badge', name: 'salary_month' },
                        { data: 'recipient_and_title', name: 'recipient_name' },
                        { data: 'breakdown', name: 'base_amount' },
                        { data: 'net_salary_display', name: 'net_salary' },
                        { data: 'credit_details', name: 'credit_date' },
                        { data: 'status_badge', name: 'status' },
                        { data: 'payslip_link', name: 'payslip_document', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    language: {
                        emptyTable: "No salary credits recorded yet. Click '+ Record Salary Credit' to add your salary."
                    }
                });

                // Filter listeners
                $('#filterSalaryYear, #filterSalaryMonth, #filterSalaryStatus, #filterSalaryMethod').on('change', function() {
                    salaryTable.draw();
                    reloadStats();
                });
                $('#filterRecipientName').on('keyup', function() {
                    salaryTable.draw();
                });
                $('#btnResetSalaryFilters').on('click', function() {
                    $('#filterSalaryYear').val('{{ date("Y") }}');
                    $('#filterSalaryMonth').val('');
                    $('#filterSalaryStatus').val('credited');
                    $('#filterSalaryMethod').val('');
                    $('#filterRecipientName').val('');
                    salaryTable.draw();
                    reloadStats();
                });

                // =========================================================================
                // 2. LIVE RECALCULATION OF NET SALARY
                // =========================================================================
                function updateAddNetPreview() {
                    var base = parseFloat($('#add_base_amount').val()) || 0;
                    var bonus = parseFloat($('#add_bonus_incentive').val()) || 0;
                    var ded = parseFloat($('#add_deductions').val()) || 0;
                    var net = Math.max(0, base + bonus - ded);
                    $('#add_net_salary_preview').text('₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }

                $('#add_base_amount, #add_bonus_incentive, #add_deductions').on('input', updateAddNetPreview);

                function updateEditNetPreview() {
                    var base = parseFloat($('#edit_base_amount').val()) || 0;
                    var bonus = parseFloat($('#edit_bonus_incentive').val()) || 0;
                    var ded = parseFloat($('#edit_deductions').val()) || 0;
                    var net = Math.max(0, base + bonus - ded);
                    $('#edit_net_salary_preview').text('₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }

                $('#edit_base_amount, #edit_bonus_incentive, #edit_deductions').on('input', updateEditNetPreview);

                // =========================================================================
                // 3. RELOAD STATS AJAX
                // =========================================================================
                function reloadStats() {
                    var year = $('#filterSalaryYear').val() || '{{ date("Y") }}';
                    $.ajax({
                        url: "{{ route('salary.stats') }}",
                        type: "GET",
                        data: { salary_year: year },
                        success: function(res) {
                            if (res.success && res.stats) {
                                $('#statYearLabel').text(res.stats.year);
                                $('#statTotalThisYear').text(res.stats.totalPaidThisYearFormatted);
                                $('#statThisMonth').text(res.stats.thisMonthSalaryFormatted);
                                $('#statAvgMonthly').text(res.stats.averageMonthlySalaryFormatted);
                                $('#statTotalEntries').text(res.stats.totalEntries);
                            }
                        }
                    });
                }

                // =========================================================================
                // 4. ADD SALARY RECORD AJAX
                // =========================================================================
                $('#addSalaryForm').on('submit', function(e) {
                    e.preventDefault();
                    var formData = new FormData(this);
                    var $btn = $('#btnAddSalarySubmit');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

                    $.ajax({
                        url: "{{ route('salary.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Salary Record');
                            if (res.success) {
                                $('#addSalaryModal').modal('hide');
                                $('#addSalaryForm')[0].reset();
                                updateAddNetPreview();
                                salaryTable.draw();
                                reloadStats();
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Salary Record');
                            var msg = 'Failed to record salary credit.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors)[0][0];
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: msg });
                        }
                    });
                });

                // =========================================================================
                // 5. EDIT SALARY DETAILS AJAX
                // =========================================================================
                $(document).on('click', '.btn-edit-salary', function() {
                    var id = $(this).data('id');
                    $.ajax({
                        url: "{{ url('/admin/salary') }}/" + id + "/data",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.salary) {
                                var s = res.salary;
                                $('#edit_salary_id').val(s.id);
                                $('#edit_salary_month').val(s.salary_month);
                                $('#edit_recipient_name').val(s.recipient_name);
                                $('#edit_salary_title').val(s.salary_title);
                                $('#edit_base_amount').val(s.base_amount);
                                $('#edit_bonus_incentive').val(s.bonus_incentive);
                                $('#edit_deductions').val(s.deductions);
                                $('#edit_credit_date').val(s.credit_date_formatted || (s.credit_date ? s.credit_date.substring(0, 10) : ''));
                                $('#edit_payment_method').val(s.payment_method);
                                $('#edit_status').val(s.status);
                                $('#edit_credited_to_bank').val(s.credited_to_bank);
                                $('#edit_transaction_reference').val(s.transaction_reference);
                                $('#edit_notes').val(s.notes);

                                if (s.payslip_document) {
                                    $('#edit_doc_preview').html('<i class="fas fa-paperclip me-1 text-primary"></i> Current: <a href="{{ asset("storage") }}/' + s.payslip_document + '" target="_blank">View File</a>');
                                } else {
                                    $('#edit_doc_preview').html('');
                                }

                                updateEditNetPreview();
                                $('#editSalaryModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to load salary details.' });
                        }
                    });
                });

                $('#editSalaryForm').on('submit', function(e) {
                    e.preventDefault();
                    var id = $('#edit_salary_id').val();
                    var formData = new FormData(this);
                    var $btn = $('#btnEditSalarySubmit');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');

                    $.ajax({
                        url: "{{ url('/admin/salary') }}/" + id,
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Update Salary Record');
                            if (res.success) {
                                $('#editSalaryModal').modal('hide');
                                salaryTable.draw();
                                reloadStats();
                                Toast.fire({ icon: 'success', title: res.message });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Update Salary Record');
                            var msg = 'Failed to update salary record.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors)[0][0];
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: msg });
                        }
                    });
                });

                // =========================================================================
                // 6. DELETE SALARY RECORD AJAX
                // =========================================================================
                $(document).on('click', '.btn-delete-salary', function() {
                    var id = $(this).data('id');
                    var month = $(this).data('month') || 'this period';
                    var name = $(this).data('name') || '';

                    Swal.fire({
                        title: "Delete Salary Record?",
                        text: "Are you sure you want to delete salary record for " + month + (name ? " (" + name + ")" : "") + "?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#dc3545",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete"
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/salary') }}/" + id,
                                type: "DELETE",
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                success: function(res) {
                                    if (res.success) {
                                        salaryTable.draw();
                                        reloadStats();
                                        Toast.fire({ icon: 'success', title: res.message });
                                    }
                                },
                                error: function() {
                                    Toast.fire({ icon: 'error', title: 'Failed to delete salary record.' });
                                }
                            });
                        }
                    });
                });

                // =========================================================================
                // 7. VIEW SALARY CREDIT VOUCHER MODAL
                // =========================================================================
                $(document).on('click', '.btn-view-salary-voucher', function() {
                    var id = $(this).data('id');
                    $.ajax({
                        url: "{{ url('/admin/salary') }}/" + id + "/voucher",
                        type: "GET",
                        success: function(res) {
                            if (res.success && res.salary) {
                                var s = res.salary;
                                $('#v_id').text(s.id);
                                $('#v_status').text(s.status);
                                $('#v_recipient').text(s.recipient_name);
                                $('#v_title').text(s.salary_title);
                                $('#v_month').text(s.formatted_month);
                                $('#v_date').text(s.credit_date);
                                $('#v_base').text(s.base_amount);
                                $('#v_bonus').text('+ ' + s.bonus_incentive);
                                $('#v_deductions').text('- ' + s.deductions);
                                $('#v_net').text(s.net_salary);
                                $('#v_method').text(s.payment_method);
                                $('#v_bank').text(s.credited_to_bank);
                                $('#v_ref').text(s.transaction_reference);
                                $('#v_notes').text(s.notes);
                                $('#v_created').text(s.created_at);

                                if (s.slip_url) {
                                    $('#v_download_slip_btn').attr('href', s.slip_url).removeClass('d-none');
                                } else {
                                    $('#v_download_slip_btn').addClass('d-none');
                                }

                                $('#viewSalaryVoucherModal').modal('show');
                            }
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to load salary voucher.' });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
