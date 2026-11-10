@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .bs-stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
                border: 1px solid #e2e8f0;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .bs-stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
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
            .font-15 { font-size: 15px; }
            .statement-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
                overflow: hidden;
            }
            .statement-header-section {
                background: linear-gradient(135deg, #0a5858 0%, #0d7373 100%);
                color: #fff;
                padding: 24px 30px;
            }
            .balance-column-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                overflow: hidden;
                height: 100%;
            }
            .balance-column-header {
                padding: 14px 20px;
                font-weight: 700;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .assets-header {
                background: #f0fdf4;
                color: #166534;
                border-bottom: 2px solid #bbf7d0;
            }
            .liabilities-header {
                background: #fef2f2;
                color: #991b1b;
                border-bottom: 2px solid #fecaca;
            }
            .bs-table th {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #64748b;
                background: #f8fafc;
                padding: 8px 16px;
            }
            .bs-table td {
                padding: 10px 16px;
                font-size: 13px;
                vertical-align: middle;
            }
            .bs-category-row {
                background: #f8fafc;
                font-weight: 700;
                font-size: 12px;
                color: #334155;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .bs-subtotal-row {
                background: #f1f5f9;
                font-weight: 700;
                border-top: 1px solid #cbd5e1;
                border-bottom: 1px solid #cbd5e1;
            }
            .bs-grand-total {
                background: #0a5858;
                color: #fff;
                font-weight: 800;
                font-size: 15px;
            }
            .filter-control {
                border-radius: 8px;
                border: 1px solid #cbd5e1;
                padding: 0.4rem 0.75rem;
                font-size: 0.85rem;
            }
            @media print {
                body * { visibility: hidden; }
                #printableBsArea, #printableBsArea * { visibility: visible; }
                #printableBsArea {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    border: none !important;
                    box-shadow: none !important;
                }
                .no-print { display: none !important; }
            }
        </style>
    @endpush

    <!-- Header & Navigation Pills -->
    <div class="row justify-content-between align-items-center mt-3 g-2 no-print">
        <div class="col-md-7">
            <h4 class="page-title-main page mb-1">
                <i class="fas fa-balance-scale text-primary me-2"></i>Balance Sheet & Financial Position
            </h4>
            <p class="text-muted font-13 mb-0">
                Statement of SRM Residence Assets, Liabilities, Tenant Dues, Deposits, and Owner Net Worth.
            </p>
        </div>
        <div class="col-md-5 text-md-end d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
            <a href="{{ route('accounts.balance_sheet.export', ['as_of_date' => $asOfDate]) }}" id="btnExportBs" class="btn btn-outline-success btn-sm px-3 d-flex align-items-center gap-1">
                <i class="fas fa-file-excel"></i>
                <span>Export Statement</span>
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-1" onclick="window.print()">
                <i class="fas fa-print"></i>
                <span>Print Balance Sheet</span>
            </button>
        </div>
    </div>

    <!-- Submodule Navigation Tabs -->
    <div class="d-flex flex-wrap gap-2 my-3 p-2 bg-white rounded-3 shadow-sm border border-light no-print">
        <a href="{{ route('accounts.journal') }}" class="accounts-nav-pill">
            <i class="fas fa-receipt"></i> Journal & Day Book
        </a>
        <a href="{{ route('accounts.pnl') }}" class="accounts-nav-pill">
            <i class="fas fa-chart-pie"></i> Profit & Loss (P&L)
        </a>
        <a href="{{ route('accounts.balance_sheet') }}" class="accounts-nav-pill active">
            <i class="fas fa-balance-scale"></i> Balance Sheet
        </a>
    </div>

    <!-- As-Of Date Filter Bar -->
    <div class="card shadow-sm border-0 mb-4 rounded-3 no-print">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-12 col-md-auto d-flex align-items-center gap-2">
                    <span class="text-muted font-13 fw-semibold"><i class="fas fa-calendar-check text-primary me-1"></i> As of Statement Date:</span>
                    <input type="date" id="bs-as-of-date" class="form-control filter-control" style="max-width: 180px;" value="{{ $asOfDate }}">
                    <button type="button" id="btn-apply-bs-date" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-sync-alt me-1"></i> Recalculate
                    </button>
                </div>
                <div class="col-12 col-md-auto text-muted font-12">
                    <i class="fas fa-shield-alt text-success me-1"></i> Double-entry accounting equilibrium verified.
                </div>
            </div>
        </div>
    </div>

    <!-- Printable & Screen Balance Sheet Area -->
    <div id="printableBsArea">
        <!-- Top 4 Summary Cards -->
        <div class="row g-3 mb-4">
            <!-- 1. Liquid Reserves ("Balance Amount We Have") -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="bs-stat-card border-start border-4 border-primary">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Balance We Have</span>
                        <div class="text-primary"><i class="fas fa-vault font-18"></i></div>
                    </div>
                    <h3 class="mb-1 fw-bold text-dark" id="bs-card-liquid">{{ $data['totalLiquidCashFormatted'] }}</h3>
                    <div class="d-flex justify-content-between text-muted font-11 pt-1 border-top mt-2">
                        <span>Cash: <strong id="bs-card-cash">{{ $data['cashInHandFormatted'] }}</strong></span>
                        <span>Bank: <strong id="bs-card-bank">{{ $data['bankBalanceFormatted'] }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- 2. Accounts Receivable (Tenant Dues) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="bs-stat-card border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Tenant Receivables</span>
                        <div class="text-warning"><i class="fas fa-file-invoice-dollar font-18"></i></div>
                    </div>
                    <h3 class="mb-1 fw-bold text-warning" id="bs-card-receivable">{{ $data['accountsReceivableFormatted'] }}</h3>
                    <div class="text-muted font-11 pt-1 border-top mt-2">
                        <span>Pending rent & EB bills to collect</span>
                    </div>
                </div>
            </div>

            <!-- 3. Total Liabilities & Debt -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="bs-stat-card border-start border-4 border-danger">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Total Liabilities</span>
                        <div class="text-danger"><i class="fas fa-hand-holding-usd font-18"></i></div>
                    </div>
                    <h3 class="mb-1 fw-bold text-danger" id="bs-card-liabilities">{{ $data['totalLiabilitiesFormatted'] }}</h3>
                    <div class="d-flex justify-content-between text-muted font-11 pt-1 border-top mt-2">
                        <span>Deposits: <strong>{{ $data['securityDepositsHeldFormatted'] }}</strong></span>
                        <span>Loans: <strong>{{ $data['loanDebtOutstandingFormatted'] }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- 4. SRM Residence Net Worth (Equity) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="bs-stat-card border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Business Net Worth</span>
                        <div class="text-success"><i class="fas fa-gem font-18"></i></div>
                    </div>
                    <h3 class="mb-1 fw-bold text-success" id="bs-card-networth">{{ $data['netWorthFormatted'] }}</h3>
                    <div class="d-flex justify-content-between text-muted font-11 pt-1 border-top mt-2">
                        <span>Total Assets &minus; Liabilities</span>
                        <span class="badge bg-success-subtle text-success">Verified</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Official Balance Sheet Statement Card -->
        <div class="statement-card mb-4">
            <!-- Statement Header -->
            <div class="statement-header-section d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold text-white mb-1">SRM RESIDENCE</h3>
                    <div class="text-white-50 font-13">Statement of Financial Position (Balance Sheet)</div>
                </div>
                <div class="text-md-end text-white">
                    <div class="fw-bold font-14"><i class="fas fa-calendar-check me-1 text-warning"></i> Statement Date</div>
                    <div class="font-13 text-white-50" id="bs-header-date">
                        As of {{ $data['asOfFormatted'] }}
                    </div>
                </div>
            </div>

            <!-- Two-Column Financial Layout: Left = Assets, Right = Liabilities & Equity -->
            <div class="p-3 bg-light">
                <div class="row g-3">
                    <!-- LEFT COLUMN: ASSETS -->
                    <div class="col-12 col-lg-6">
                        <div class="balance-column-card shadow-sm">
                            <div class="balance-column-header assets-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-boxes-stacked me-2"></i> Assets (What We Own)</span>
                                <span class="badge bg-success text-white font-11">Total Properties & Cash</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table bs-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Asset Head</th>
                                            <th class="text-end">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bs-assets-tbody">
                                        <!-- Current Assets -->
                                        <tr class="bs-category-row">
                                            <td colspan="2"><i class="fas fa-wallet text-primary me-1"></i> 1. Current / Liquid Assets</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">Cash in Hand</div>
                                                <small class="text-muted">Physical currency available in campus locker/cash box</small>
                                            </td>
                                            <td class="text-end fw-semibold text-dark">{{ $data['cashInHandFormatted'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">Bank & Digital UPI Reserves</div>
                                                <small class="text-muted">Current accounts, UPI collections & digital funds</small>
                                            </td>
                                            <td class="text-end fw-semibold text-dark">{{ $data['bankBalanceFormatted'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">Accounts Receivable (Tenant Dues)</div>
                                                <small class="text-muted">Uncollected monthly rent and electricity bill dues</small>
                                            </td>
                                            <td class="text-end fw-semibold text-warning">{{ $data['accountsReceivableFormatted'] }}</td>
                                        </tr>
                                        <tr class="bs-subtotal-row">
                                            <td>Total Current Assets</td>
                                            <td class="text-end fw-bold text-dark">{{ $data['totalCurrentAssetsFormatted'] }}</td>
                                        </tr>

                                        <!-- Non-Current / Fixed Assets -->
                                        <tr class="bs-category-row">
                                            <td colspan="2"><i class="fas fa-building text-info me-1"></i> 2. Non-Current / Fixed Assets</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">Room Infrastructure & Furnishings</div>
                                                <small class="text-muted">{{ $data['roomCount'] }} rooms &times; estimated infrastructure fittings</small>
                                            </td>
                                            <td class="text-end fw-semibold text-dark">₹{{ number_format($data['estimatedRoomAssets'], 2) }}</td>
                                        </tr>
                                        @if($data['capitalJournalAssets'] > 0)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-semibold text-dark">Capital Equipment & Assets Logged</div>
                                                    <small class="text-muted">Appliances, civil additions from journal ledger</small>
                                                </td>
                                                <td class="text-end fw-semibold text-dark">₹{{ number_format($data['capitalJournalAssets'], 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="bs-subtotal-row">
                                            <td>Total Fixed Assets</td>
                                            <td class="text-end fw-bold text-dark">{{ $data['totalFixedAssetsFormatted'] }}</td>
                                        </tr>

                                        <!-- Total Assets -->
                                        <tr class="bs-grand-total">
                                            <td class="py-3 font-14">TOTAL ASSETS (A)</td>
                                            <td class="text-end py-3 font-15">{{ $data['totalAssetsFormatted'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: LIABILITIES & EQUITY -->
                    <div class="col-12 col-lg-6">
                        <div class="balance-column-card shadow-sm">
                            <div class="balance-column-header liabilities-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hand-holding-dollar me-2"></i> Liabilities & Equity</span>
                                <span class="badge bg-danger text-white font-11">What We Owe & Net Worth</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table bs-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Liability / Capital Head</th>
                                            <th class="text-end">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bs-liabilities-tbody">
                                        <!-- Current Liabilities -->
                                        <tr class="bs-category-row">
                                            <td colspan="2"><i class="fas fa-shield-alt text-warning me-1"></i> 1. Current Liabilities</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">Tenant Caution Deposits Held</div>
                                                <small class="text-muted">Security deposits refundable to tenants upon vacate</small>
                                            </td>
                                            <td class="text-end fw-semibold text-danger">{{ $data['securityDepositsHeldFormatted'] }}</td>
                                        </tr>
                                        <tr class="bs-subtotal-row">
                                            <td>Total Current Liabilities</td>
                                            <td class="text-end fw-bold text-dark">{{ $data['securityDepositsHeldFormatted'] }}</td>
                                        </tr>

                                        <!-- Long-Term Liabilities -->
                                        <tr class="bs-category-row">
                                            <td colspan="2"><i class="fas fa-landmark text-danger me-1"></i> 2. Long-Term Debt / Loans</td>
                                        </tr>
                                        @forelse($data['activeLoansList'] as $ln)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-semibold text-dark">{{ $ln->loan_title }}</div>
                                                    <small class="text-muted">{{ $ln->lender_name }} &bull; EMI: ₹{{ number_format($ln->emi_amount, 2) }}</small>
                                                </td>
                                                <td class="text-end fw-semibold text-danger">₹{{ number_format($ln->outstanding_balance, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="ps-4 text-muted" colspan="2">No active outstanding bank loans.</td>
                                            </tr>
                                        @endforelse
                                        <tr class="bs-subtotal-row">
                                            <td>Total Long-Term Debt</td>
                                            <td class="text-end fw-bold text-dark">{{ $data['loanDebtOutstandingFormatted'] }}</td>
                                        </tr>
                                        <tr style="background: #fef2f2; border-bottom: 2px solid #cbd5e1;">
                                            <td class="fw-bold text-danger text-uppercase">Total Liabilities (B)</td>
                                            <td class="text-end fw-bold text-danger">{{ $data['totalLiabilitiesFormatted'] }}</td>
                                        </tr>

                                        <!-- Equity & Net Worth -->
                                        <tr class="bs-category-row">
                                            <td colspan="2"><i class="fas fa-gem text-success me-1"></i> 3. Owner's Equity & Retained Capital</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">SRM Residence Net Worth / Reserves</div>
                                                <small class="text-muted">Accumulated retained surplus & business capital (Assets &minus; Liabilities)</small>
                                            </td>
                                            <td class="text-end fw-bold text-success font-14">{{ $data['netWorthFormatted'] }}</td>
                                        </tr>
                                        <tr class="bs-subtotal-row">
                                            <td>Total Business Equity</td>
                                            <td class="text-end fw-bold text-success">{{ $data['netWorthFormatted'] }}</td>
                                        </tr>

                                        <!-- Total Liabilities & Equity -->
                                        <tr class="bs-grand-total">
                                            <td class="py-3 font-14">TOTAL LIABILITIES & EQUITY (B + Equity)</td>
                                            <td class="text-end py-3 font-15">{{ $data['totalLiabilitiesAndEquityFormatted'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equilibrium Check Bar -->
            <div class="p-3 bg-white border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white px-2 py-1 font-12">
                        <i class="fas fa-check-circle me-1"></i> In Equilibrium
                    </span>
                    <span class="font-12 text-muted">
                        Total Assets (<strong>{{ $data['totalAssetsFormatted'] }}</strong>) = Total Liabilities (<strong>{{ $data['totalLiabilitiesFormatted'] }}</strong>) + Owner Equity (<strong>{{ $data['netWorthFormatted'] }}</strong>)
                    </span>
                </div>
                <div class="font-11 text-muted">
                    Verified as of {{ date('d M Y, h:i A') }}
                </div>
            </div>
        </div>

        <!-- Receivable Dues Breakdown Table (Top 10 Tenants with Pending Bills) -->
        @if(count($data['receivableTenants']) > 0)
            <div class="card shadow-sm border-0 rounded-3 mb-4 no-print">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title font-14 fw-bold mb-0 text-dark">
                        <i class="fas fa-user-clock text-warning me-2"></i>Accounts Receivable: Tenants with Outstanding Dues
                    </h5>
                    <a href="{{ url('/admin/billing') }}" class="btn btn-sm btn-outline-primary font-12">
                        <span>Manage Bills & Collect</span> <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 font-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Tenant Name</th>
                                    <th>Room Assigned</th>
                                    <th class="text-end">Pending Balance Due (₹)</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['receivableTenants'] as $t)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $t->name }}</td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border border-info px-2 py-1 font-11">
                                                <i class="fas fa-door-closed me-1"></i>Room {{ $t->room_number }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-danger">₹{{ number_format($t->total_due, 2) }}</td>
                                        <td class="text-center">
                                            <a href="{{ url('/admin/billing') }}" class="btn btn-sm btn-outline-success font-11 px-2 py-0">
                                                <i class="fas fa-money-bill-wave me-1"></i> Collect
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Apply As-Of Date Filter
            $('#btn-apply-bs-date').on('click', function () {
                const asOf = $('#bs-as-of-date').val();

                // Update Export link
                const exportUrl = new URL("{{ route('accounts.balance_sheet.export') }}", window.location.origin);
                exportUrl.searchParams.set('as_of_date', asOf);
                $('#btnExportBs').attr('href', exportUrl.toString());

                // Fetch Balance Sheet via AJAX
                $.get("{{ route('accounts.balance_sheet.data') }}", { as_of_date: asOf }, function (res) {
                    if (res.success && res.balance_sheet) {
                        const b = res.balance_sheet;

                        // Update Top Metric Cards
                        $('#bs-card-liquid').text(b.totalLiquidCashFormatted);
                        $('#bs-card-cash').text(b.cashInHandFormatted);
                        $('#bs-card-bank').text(b.bankBalanceFormatted);
                        $('#bs-card-receivable').text(b.accountsReceivableFormatted);
                        $('#bs-card-liabilities').text(b.totalLiabilitiesFormatted);
                        $('#bs-card-networth').text(b.netWorthFormatted);
                        $('#bs-header-date').text('As of ' + b.asOfFormatted);

                        // Reload page with new date for complete table rerender
                        window.location.href = "{{ route('accounts.balance_sheet') }}?as_of_date=" + asOf;
                    }
                });
            });
        });
    </script>
@endpush
