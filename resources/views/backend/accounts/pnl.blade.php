@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .pnl-stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
                border: 1px solid #e2e8f0;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .pnl-stat-card:hover {
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
            .table-pnl th {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #475569;
                background: #f8fafc;
                font-weight: 700;
                padding: 10px 18px;
                border-bottom: 2px solid #e2e8f0;
            }
            .table-pnl td {
                padding: 12px 18px;
                vertical-align: middle;
            }
            .section-header-row td {
                background: #f1f5f9;
                font-weight: 800;
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.8px;
                color: #334155;
                border-top: 2px solid #cbd5e1;
            }
            .subtotal-row td {
                background: #f8fafc;
                font-weight: 700;
                border-top: 1px solid #cbd5e1;
                border-bottom: 2px solid #cbd5e1;
            }
            .grand-total-row td {
                background: #f0fdf4;
                font-weight: 800;
                font-size: 15px;
                border-top: 2px solid #22c55e;
                border-bottom: 2px solid #22c55e;
            }
            .grand-total-row.loss td {
                background: #fef2f2;
                border-top: 2px solid #ef4444;
                border-bottom: 2px solid #ef4444;
            }
            .filter-control {
                border-radius: 8px;
                border: 1px solid #cbd5e1;
                padding: 0.4rem 0.75rem;
                font-size: 0.85rem;
            }
            @media print {
                body * { visibility: hidden; }
                #printablePnlArea, #printablePnlArea * { visibility: visible; }
                #printablePnlArea {
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
                <i class="fas fa-chart-pie text-primary me-2"></i>Profit & Loss (P&L) Statement
            </h4>
            <p class="text-muted font-13 mb-0">
                Income statement calculating all revenues, operating costs, staff/owner salaries, and net profit.
            </p>
        </div>
        <div class="col-md-5 text-md-end d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
            <a href="{{ route('accounts.pnl.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" id="btnExportPnl" class="btn btn-outline-success btn-sm px-3 d-flex align-items-center gap-1">
                <i class="fas fa-file-excel"></i>
                <span>Export Statement</span>
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-1" onclick="window.print()">
                <i class="fas fa-print"></i>
                <span>Print P&L</span>
            </button>
        </div>
    </div>

    <!-- Submodule Navigation Tabs -->
    <div class="d-flex flex-wrap gap-2 my-3 p-2 bg-white rounded-3 shadow-sm border border-light no-print">
        <a href="{{ route('accounts.journal') }}" class="accounts-nav-pill">
            <i class="fas fa-receipt"></i> Journal & Day Book
        </a>
        <a href="{{ route('accounts.pnl') }}" class="accounts-nav-pill active">
            <i class="fas fa-chart-pie"></i> Profit & Loss (P&L)
        </a>
        <a href="{{ route('accounts.balance_sheet') }}" class="accounts-nav-pill">
            <i class="fas fa-balance-scale"></i> Balance Sheet
        </a>
    </div>

    <!-- Date Range & Preset Filter Bar -->
    <div class="card shadow-sm border-0 mb-4 rounded-3 no-print">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-auto">
                    <label class="form-label font-11 fw-bold text-muted mb-1 d-block">Period Preset</label>
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-pnl-preset {{ $preset === 'this_month' ? 'active' : '' }}" data-preset="this_month">This Month</button>
                        <button type="button" class="btn btn-outline-secondary btn-pnl-preset {{ $preset === 'last_month' ? 'active' : '' }}" data-preset="last_month">Last Month</button>
                        <button type="button" class="btn btn-outline-secondary btn-pnl-preset {{ $preset === 'this_quarter' ? 'active' : '' }}" data-preset="this_quarter">This Quarter</button>
                        <button type="button" class="btn btn-outline-secondary btn-pnl-preset {{ $preset === 'this_year' ? 'active' : '' }}" data-preset="this_year">This FY</button>
                        <button type="button" class="btn btn-outline-secondary btn-pnl-preset {{ $preset === 'all' ? 'active' : '' }}" data-preset="all">All Time</button>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label font-11 fw-bold text-muted mb-1">From Date</label>
                    <input type="date" id="pnl-start-date" class="form-control filter-control" value="{{ $startDate }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label font-11 fw-bold text-muted mb-1">To Date</label>
                    <input type="date" id="pnl-end-date" class="form-control filter-control" value="{{ $endDate }}">
                </div>
                <div class="col-12 col-md-auto align-self-end">
                    <button type="button" id="btn-apply-pnl-range" class="btn btn-primary btn-sm filter-control px-4">
                        <i class="fas fa-filter me-1"></i> Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Printable & Screen P&L Statement Area -->
    <div id="printablePnlArea">
        <!-- Top KPI Cards -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Revenue -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="pnl-stat-card border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Total Revenue (Income)</span>
                        <div class="text-success"><i class="fas fa-hand-holding-dollar font-18"></i></div>
                    </div>
                    <h3 class="mb-1 fw-bold text-success" id="pnl-card-revenue">{{ $pnl['totalRevenueFormatted'] }}</h3>
                    <div class="text-muted font-11 pt-1 border-top mt-2">
                        <span>Room rent, EB & family salary</span>
                    </div>
                </div>
            </div>

            <!-- 2. Operating Expenses -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="pnl-stat-card border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Operating Expenses</span>
                        <div class="text-warning"><i class="fas fa-wrench font-18"></i></div>
                    </div>
                    <h3 class="mb-1 fw-bold text-dark" id="pnl-card-operating">{{ $pnl['totalOperatingExpensesFormatted'] }}</h3>
                    <div class="text-muted font-11 pt-1 border-top mt-2">
                        <span>Hostel maintenance & utility costs</span>
                    </div>
                </div>
            </div>

            <!-- 3. Overheads & Indirect -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="pnl-stat-card border-start border-4 border-info">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Personal & Financing Overheads</span>
                        <div class="text-info"><i class="fas fa-hand-holding-usd font-18"></i></div>
                    </div>
                    <h3 class="mb-1 fw-bold text-dark" id="pnl-card-indirect">{{ $pnl['totalIndirectExpensesFormatted'] }}</h3>
                    <div class="text-muted font-11 pt-1 border-top mt-2">
                        <span>Personal expenses & loan EMI payments</span>
                    </div>
                </div>
            </div>

            <!-- 4. Net Profit / (Loss) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="pnl-stat-card border-start border-4 {{ $pnl['isProfitable'] ? 'border-success' : 'border-danger' }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted font-11 fw-bold letter-spacing-1">Net Profit / Margin</span>
                        <div class="{{ $pnl['isProfitable'] ? 'text-success' : 'text-danger' }}">
                            <i class="fas fa-trophy font-18"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold {{ $pnl['isProfitable'] ? 'text-success' : 'text-danger' }}" id="pnl-card-net">{{ $pnl['netProfitFormatted'] }}</h3>
                    <div class="d-flex justify-content-between text-muted font-11 pt-1 border-top mt-2">
                        <span>Margin: <strong id="pnl-card-margin" class="{{ $pnl['isProfitable'] ? 'text-success' : 'text-danger' }}">{{ $pnl['netMarginPct'] }}%</strong></span>
                        <span class="badge {{ $pnl['isProfitable'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}" id="pnl-card-status">
                            {{ $pnl['isProfitable'] ? 'Profitable' : 'Deficit' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Official Statement Card -->
        <div class="statement-card mb-4">
            <!-- Header for Printable Statement -->
            <div class="statement-header-section d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold text-white mb-1">SRM RESIDENCE</h3>
                    <div class="text-white-50 font-13">Official Income & Expenditure Statement (Profit & Loss)</div>
                </div>
                <div class="text-md-end text-white">
                    <div class="fw-bold font-14"><i class="fas fa-calendar-alt me-1 text-warning"></i> Period Covered</div>
                    <div class="font-13 text-white-50" id="statement-period-text">
                        {{ $pnl['period']['start_formatted'] }} &nbsp;&mdash;&nbsp; {{ $pnl['period']['end_formatted'] }}
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-pnl mb-0">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Account Head / Particulars</th>
                            <th style="width: 30%;">Operational Details</th>
                            <th class="text-end" style="width: 15%;">Amount (₹)</th>
                            <th class="text-end" style="width: 10%;">Share %</th>
                        </tr>
                    </thead>
                    <tbody id="pnl-table-body">
                        <!-- SECTION 1: REVENUE -->
                        <tr class="section-header-row">
                            <td colspan="4"><i class="fas fa-arrow-circle-down text-success me-2"></i> Section A: Revenues & Operating Income</td>
                        </tr>
                        @foreach($pnl['incomeBreakdown'] as $inc)
                            <tr>
                                <td class="fw-semibold text-dark">
                                    <i class="{{ $inc['icon'] }} me-2"></i> {{ $inc['category'] }}
                                </td>
                                <td class="text-muted font-12">{{ $inc['description'] }}</td>
                                <td class="text-end fw-bold text-success font-13">₹{{ number_format($inc['amount'], 2) }}</td>
                                <td class="text-end text-muted font-12">{{ $inc['share_pct'] }}%</td>
                            </tr>
                        @endforeach
                        <tr class="subtotal-row">
                            <td colspan="2" class="text-uppercase text-dark">Total Gross Revenue (A)</td>
                            <td class="text-end text-success font-14 fw-bold">₹{{ number_format($pnl['totalRevenue'], 2) }}</td>
                            <td class="text-end fw-bold font-12">100.0%</td>
                        </tr>

                        <!-- SECTION 2: DIRECT OPERATING EXPENSES -->
                        <tr class="section-header-row">
                            <td colspan="4"><i class="fas fa-wrench text-warning me-2"></i> Section B: Direct Operating Costs (Hostel Operations)</td>
                        </tr>
                        @forelse($pnl['operatingExpenseBreakdown'] as $op)
                            <tr>
                                <td class="fw-semibold text-dark">
                                    <i class="{{ $op['icon'] }} me-2"></i> {{ $op['category'] }}
                                </td>
                                <td class="text-muted font-12">Direct room maintenance & facility expenditure</td>
                                <td class="text-end fw-semibold text-danger font-13">₹{{ number_format($op['amount'], 2) }}</td>
                                <td class="text-end text-muted font-12">{{ $op['share_pct'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No direct room operating expenses logged in this period.</td>
                            </tr>
                        @endforelse
                        <tr class="subtotal-row">
                            <td colspan="2" class="text-uppercase text-dark">Total Direct Operating Expenses (B)</td>
                            <td class="text-end text-danger font-14 fw-bold">₹{{ number_format($pnl['totalOperatingExpenses'], 2) }}</td>
                            <td class="text-end text-muted font-12">
                                {{ $pnl['totalRevenue'] > 0 ? round(($pnl['totalOperatingExpenses'] / $pnl['totalRevenue']) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <td colspan="2" class="fw-bold text-primary text-uppercase">
                                <i class="fas fa-calculator me-1"></i> Operating Profit / EBITDA (A &minus; B)
                            </td>
                            <td class="text-end fw-bold text-primary font-14">₹{{ number_format($pnl['operatingProfit'], 2) }}</td>
                            <td class="text-end fw-bold text-primary font-12">{{ $pnl['operatingMarginPct'] }}%</td>
                        </tr>

                        <!-- SECTION 3: INDIRECT EXPENSES -->
                        <tr class="section-header-row">
                            <td colspan="4"><i class="fas fa-briefcase text-info me-2"></i> Section C: Indirect Expenses, Personal & Financing</td>
                        </tr>
                        @foreach($pnl['indirectExpenseBreakdown'] as $ind)
                            <tr>
                                <td class="fw-semibold text-dark">
                                    <i class="{{ $ind['icon'] }} me-2"></i> {{ $ind['category'] }}
                                </td>
                                <td class="text-muted font-12">{{ $ind['description'] }}</td>
                                <td class="text-end fw-semibold text-danger font-13">₹{{ number_format($ind['amount'], 2) }}</td>
                                <td class="text-end text-muted font-12">
                                    {{ $pnl['totalRevenue'] > 0 ? round(($ind['amount'] / $pnl['totalRevenue']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                        <tr class="subtotal-row">
                            <td colspan="2" class="text-uppercase text-dark">Total Indirect Overheads & Salaries (C)</td>
                            <td class="text-end text-danger font-14 fw-bold">₹{{ number_format($pnl['totalIndirectExpenses'], 2) }}</td>
                            <td class="text-end text-muted font-12">
                                {{ $pnl['totalRevenue'] > 0 ? round(($pnl['totalIndirectExpenses'] / $pnl['totalRevenue']) * 100, 1) : 0 }}%
                            </td>
                        </tr>

                        <!-- SECTION 4: GRAND TOTALS -->
                        <tr class="subtotal-row" style="background: #e2e8f0;">
                            <td colspan="2" class="text-uppercase fw-bold text-dark font-14">Total Expenditure (B + C)</td>
                            <td class="text-end text-danger font-14 fw-bold">₹{{ number_format($pnl['totalExpenses'], 2) }}</td>
                            <td class="text-end text-muted font-12">
                                {{ $pnl['totalRevenue'] > 0 ? round(($pnl['totalExpenses'] / $pnl['totalRevenue']) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        <tr class="grand-total-row {{ $pnl['isProfitable'] ? '' : 'loss' }}">
                            <td colspan="2">
                                <div class="fw-bold font-15 {{ $pnl['isProfitable'] ? 'text-success' : 'text-danger' }}">
                                    <i class="fas {{ $pnl['isProfitable'] ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} me-2"></i>
                                    NET {{ $pnl['isProfitable'] ? 'PROFIT' : 'LOSS' }} (Total Revenue &minus; Total Expenditure)
                                </div>
                            </td>
                            <td class="text-end fw-bold font-16 {{ $pnl['isProfitable'] ? 'text-success' : 'text-danger' }}">
                                {{ $pnl['netProfitFormatted'] }}
                            </td>
                            <td class="text-end fw-bold font-13 {{ $pnl['isProfitable'] ? 'text-success' : 'text-danger' }}">
                                {{ $pnl['netMarginPct'] }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Note -->
            <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center flex-wrap gap-2 font-11 text-muted">
                <div><i class="fas fa-info-circle me-1"></i> Generated automatically from verified SRM Residence transaction records.</div>
                <div>As on {{ date('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- 6-Month Comparative Performance Trend Table -->
        <div class="card shadow-sm border-0 rounded-3 mb-4 no-print">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title font-14 fw-bold mb-0 text-dark">
                    <i class="fas fa-chart-column text-primary me-2"></i>Last 6 Months Comparative Trend
                </h5>
                <span class="badge bg-light text-muted font-11 border">Monthly Inflow vs Outflow</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-13">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Revenue Inflow (₹)</th>
                                <th class="text-end">Total Outflow (₹)</th>
                                <th class="text-end">Net Position (₹)</th>
                                <th style="width: 250px;">Net Ratio Visual</th>
                            </tr>
                        </thead>
                        <tbody id="pnl-trend-body">
                            @foreach($pnl['monthlyTrend'] as $mt)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $mt['month'] }}</td>
                                    <td class="text-end text-success fw-semibold">₹{{ number_format($mt['income'], 2) }}</td>
                                    <td class="text-end text-danger fw-semibold">₹{{ number_format($mt['expense'], 2) }}</td>
                                    <td class="text-end fw-bold {{ $mt['is_profit'] ? 'text-success' : 'text-danger' }}">
                                        {{ ($mt['is_profit'] ? '+ ₹' : '- ₹') . number_format(abs($mt['net']), 2) }}
                                    </td>
                                    <td>
                                        @php
                                            $total = $mt['income'] + $mt['expense'];
                                            $inPct = $total > 0 ? round(($mt['income'] / $total) * 100) : 50;
                                            $expPct = 100 - $inPct;
                                        @endphp
                                        <div class="progress" style="height: 10px; border-radius: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $inPct }}%;" title="Inflow: {{ $inPct }}%"></div>
                                            <div class="progress-bar bg-danger" style="width: {{ $expPct }}%;" title="Outflow: {{ $expPct }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Apply Period Preset
            $('.btn-pnl-preset').on('click', function () {
                $('.btn-pnl-preset').removeClass('active');
                $(this).addClass('active');

                const preset = $(this).data('preset');
                const now = new Date();

                if (preset === 'this_month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    $('#pnl-start-date').val(firstDay.toISOString().split('T')[0]);
                    $('#pnl-end-date').val(lastDay.toISOString().split('T')[0]);
                } else if (preset === 'last_month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
                    $('#pnl-start-date').val(firstDay.toISOString().split('T')[0]);
                    $('#pnl-end-date').val(lastDay.toISOString().split('T')[0]);
                } else if (preset === 'this_quarter') {
                    const currentQuarter = Math.floor(now.getMonth() / 3);
                    const firstDay = new Date(now.getFullYear(), currentQuarter * 3, 1);
                    const lastDay = new Date(now.getFullYear(), (currentQuarter + 1) * 3, 0);
                    $('#pnl-start-date').val(firstDay.toISOString().split('T')[0]);
                    $('#pnl-end-date').val(lastDay.toISOString().split('T')[0]);
                } else if (preset === 'this_year') {
                    const year = now.getMonth() >= 3 ? now.getFullYear() : now.getFullYear() - 1;
                    const startFY = new Date(year, 3, 1);
                    const endFY = new Date(year + 1, 2, 31);
                    $('#pnl-start-date').val(startFY.toISOString().split('T')[0]);
                    $('#pnl-end-date').val(endFY.toISOString().split('T')[0]);
                } else if (preset === 'all') {
                    $('#pnl-start-date').val('2020-01-01');
                    $('#pnl-end-date').val(now.toISOString().split('T')[0]);
                }

                loadPnlData();
            });

            // Apply custom range button
            $('#btn-apply-pnl-range').on('click', function () {
                $('.btn-pnl-preset').removeClass('active');
                loadPnlData();
            });

            // AJAX loader for PnL
            function loadPnlData() {
                const start = $('#pnl-start-date').val();
                const end = $('#pnl-end-date').val();

                // Update Export URL
                const exportUrl = new URL("{{ route('accounts.pnl.export') }}", window.location.origin);
                exportUrl.searchParams.set('start_date', start);
                exportUrl.searchParams.set('end_date', end);
                $('#btnExportPnl').attr('href', exportUrl.toString());

                // Fetch updated P&L
                $.get("{{ route('accounts.pnl.data') }}", { start_date: start, end_date: end }, function (res) {
                    if (res.success && res.pnl) {
                        const p = res.pnl;

                        // Update Top KPI Cards
                        $('#pnl-card-revenue').text(p.totalRevenueFormatted);
                        $('#pnl-card-operating').text(p.totalOperatingExpensesFormatted);
                        $('#pnl-card-indirect').text(p.totalIndirectExpensesFormatted);
                        $('#pnl-card-net').text(p.netProfitFormatted);
                        $('#pnl-card-margin').text(p.netMarginPct + '%');
                        $('#statement-period-text').text(p.period.start_formatted + ' — ' + p.period.end_formatted);

                        if (p.isProfitable) {
                            $('#pnl-card-net').removeClass('text-danger').addClass('text-success');
                            $('#pnl-card-margin').removeClass('text-danger').addClass('text-success');
                            $('#pnl-card-status').removeClass('bg-danger-subtle text-danger').addClass('bg-success-subtle text-success').text('Profitable');
                        } else {
                            $('#pnl-card-net').removeClass('text-success').addClass('text-danger');
                            $('#pnl-card-margin').removeClass('text-success').addClass('text-danger');
                            $('#pnl-card-status').removeClass('bg-success-subtle text-success').addClass('bg-danger-subtle text-danger').text('Deficit');
                        }

                        // Rebuild P&L Table Body
                        let rows = '';

                        // Section A: Revenues
                        rows += '<tr class="section-header-row"><td colspan="4"><i class="fas fa-arrow-circle-down text-success me-2"></i> Section A: Revenues & Operating Income</td></tr>';
                        p.incomeBreakdown.forEach(function (inc) {
                            rows += '<tr>' +
                                '<td class="fw-semibold text-dark"><i class="' + inc.icon + ' me-2"></i> ' + inc.category + '</td>' +
                                '<td class="text-muted font-12">' + inc.description + '</td>' +
                                '<td class="text-end fw-bold text-success font-13">₹' + Number(inc.amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                                '<td class="text-end text-muted font-12">' + inc.share_pct + '%</td>' +
                            '</tr>';
                        });
                        rows += '<tr class="subtotal-row">' +
                            '<td colspan="2" class="text-uppercase text-dark">Total Gross Revenue (A)</td>' +
                            '<td class="text-end text-success font-14 fw-bold">₹' + Number(p.totalRevenue).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end fw-bold font-12">100.0%</td>' +
                        '</tr>';

                        // Section B: Operating Expenses
                        rows += '<tr class="section-header-row"><td colspan="4"><i class="fas fa-wrench text-warning me-2"></i> Section B: Direct Operating Costs (Hostel Operations)</td></tr>';
                        if (p.operatingExpenseBreakdown.length > 0) {
                            p.operatingExpenseBreakdown.forEach(function (op) {
                                rows += '<tr>' +
                                    '<td class="fw-semibold text-dark"><i class="' + op.icon + ' me-2"></i> ' + op.category + '</td>' +
                                    '<td class="text-muted font-12">Direct room maintenance & facility expenditure</td>' +
                                    '<td class="text-end fw-semibold text-danger font-13">₹' + Number(op.amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                                    '<td class="text-end text-muted font-12">' + op.share_pct + '%</td>' +
                                '</tr>';
                            });
                        } else {
                            rows += '<tr><td colspan="4" class="text-center text-muted py-3">No direct room operating expenses logged in this period.</td></tr>';
                        }
                        const opRatio = p.totalRevenue > 0 ? ((p.totalOperatingExpenses / p.totalRevenue) * 100).toFixed(1) : '0';
                        rows += '<tr class="subtotal-row">' +
                            '<td colspan="2" class="text-uppercase text-dark">Total Direct Operating Expenses (B)</td>' +
                            '<td class="text-end text-danger font-14 fw-bold">₹' + Number(p.totalOperatingExpenses).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end text-muted font-12">' + opRatio + '%</td>' +
                        '</tr>';
                        rows += '<tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">' +
                            '<td colspan="2" class="fw-bold text-primary text-uppercase"><i class="fas fa-calculator me-1"></i> Operating Profit / EBITDA (A &minus; B)</td>' +
                            '<td class="text-end fw-bold text-primary font-14">₹' + Number(p.operatingProfit).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end fw-bold text-primary font-12">' + p.operatingMarginPct + '%</td>' +
                        '</tr>';

                        // Section C: Indirect Expenses
                        rows += '<tr class="section-header-row"><td colspan="4"><i class="fas fa-briefcase text-info me-2"></i> Section C: Indirect Expenses, Personal & Financing</td></tr>';
                        p.indirectExpenseBreakdown.forEach(function (ind) {
                            const indRatio = p.totalRevenue > 0 ? ((ind.amount / p.totalRevenue) * 100).toFixed(1) : '0';
                            rows += '<tr>' +
                                '<td class="fw-semibold text-dark"><i class="' + ind.icon + ' me-2"></i> ' + ind.category + '</td>' +
                                '<td class="text-muted font-12">' + ind.description + '</td>' +
                                '<td class="text-end fw-semibold text-danger font-13">₹' + Number(ind.amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                                '<td class="text-end text-muted font-12">' + indRatio + '%</td>' +
                            '</tr>';
                        });
                        const indTotalRatio = p.totalRevenue > 0 ? ((p.totalIndirectExpenses / p.totalRevenue) * 100).toFixed(1) : '0';
                        rows += '<tr class="subtotal-row">' +
                            '<td colspan="2" class="text-uppercase text-dark">Total Indirect Overheads & Financing (C)</td>' +
                            '<td class="text-end text-danger font-14 fw-bold">₹' + Number(p.totalIndirectExpenses).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end text-muted font-12">' + indTotalRatio + '%</td>' +
                        '</tr>';

                        // Section 4: Grand Totals
                        const totalExpRatio = p.totalRevenue > 0 ? ((p.totalExpenses / p.totalRevenue) * 100).toFixed(1) : '0';
                        rows += '<tr class="subtotal-row" style="background: #e2e8f0;">' +
                            '<td colspan="2" class="text-uppercase fw-bold text-dark font-14">Total Expenditure (B + C)</td>' +
                            '<td class="text-end text-danger font-14 fw-bold">₹' + Number(p.totalExpenses).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end text-muted font-12">' + totalExpRatio + '%</td>' +
                        '</tr>';

                        const grandClass = p.isProfitable ? '' : 'loss';
                        const textClass = p.isProfitable ? 'text-success' : 'text-danger';
                        const trendIcon = p.isProfitable ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                        const profitLabel = p.isProfitable ? 'PROFIT' : 'LOSS';

                        rows += '<tr class="grand-total-row ' + grandClass + '">' +
                            '<td colspan="2"><div class="fw-bold font-15 ' + textClass + '"><i class="fas ' + trendIcon + ' me-2"></i> NET ' + profitLabel + ' (Total Revenue &minus; Total Expenditure)</div></td>' +
                            '<td class="text-end fw-bold font-16 ' + textClass + '">' + p.netProfitFormatted + '</td>' +
                            '<td class="text-end fw-bold font-13 ' + textClass + '">' + p.netMarginPct + '%</td>' +
                        '</tr>';

                        $('#pnl-table-body').html(rows);

                        // Rebuild Monthly Trend
                        if (p.monthlyTrend && p.monthlyTrend.length > 0) {
                            let trendRows = '';
                            p.monthlyTrend.forEach(function (mt) {
                                const total = mt.income + mt.expense;
                                const inPct = total > 0 ? Math.round((mt.income / total) * 100) : 50;
                                const expPct = 100 - inPct;
                                const netClass = mt.is_profit ? 'text-success' : 'text-danger';
                                const netSign = mt.is_profit ? '+ ₹' : '- ₹';

                                trendRows += '<tr>' +
                                    '<td class="fw-bold text-dark">' + mt.month + '</td>' +
                                    '<td class="text-end text-success fw-semibold">₹' + Number(mt.income).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                                    '<td class="text-end text-danger fw-semibold">₹' + Number(mt.expense).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                                    '<td class="text-end fw-bold ' + netClass + '">' + netSign + Number(Math.abs(mt.net)).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                                    '<td><div class="progress" style="height: 10px; border-radius: 6px;">' +
                                        '<div class="progress-bar bg-success" style="width: ' + inPct + '%;"></div>' +
                                        '<div class="progress-bar bg-danger" style="width: ' + expPct + '%;"></div>' +
                                    '</div></td>' +
                                '</tr>';
                            });
                            $('#pnl-trend-body').html(trendRows);
                        }
                    }
                });
            }
        });
    </script>
@endpush
