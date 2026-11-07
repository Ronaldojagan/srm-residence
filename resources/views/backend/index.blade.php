@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <style>
            :root {
                --res-primary: #0ea1b1;
                --res-primary-rgb: 14, 161, 177;
                --res-primary-dark: #097380;
                --res-success: #10b981;
                --res-warning: #f59e0b;
                --res-danger: #ef4444;
                --res-info: #3b82f6;
                --res-purple: #8b5cf6;
                --res-dark: #1e293b;
                --res-card-bg: #ffffff;
                --res-border: #e2e8f0;
            }

            .res-dashboard {
                padding-bottom: 2.5rem;
                color: #334155;
            }

            /* Welcome Header */
            .res-hero-banner {
                background: linear-gradient(135deg, #0ea1b1 0%, #086d77 100%);
                border-radius: 20px;
                padding: 1.75rem 2rem;
                color: #ffffff;
                box-shadow: 0 10px 25px -5px rgba(14, 161, 177, 0.25);
                margin-bottom: 1.75rem;
                position: relative;
                overflow: hidden;
            }
            .res-hero-banner::after {
                content: '';
                position: absolute;
                top: -40px;
                right: -40px;
                width: 200px;
                height: 200px;
                background: rgba(255, 255, 255, 0.08);
                border-radius: 50%;
                pointer-events: none;
            }

            /* Modern KPI Cards */
            .res-kpi-card {
                background: #ffffff;
                border-radius: 16px;
                border: 1px solid rgba(226, 232, 240, 0.8);
                padding: 1.4rem;
                transition: all 0.25s ease;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
                height: 100%;
                position: relative;
            }
            .res-kpi-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
                border-color: rgba(14, 161, 177, 0.4);
            }
            .res-icon-box {
                width: 52px;
                height: 52px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.4rem;
                color: #ffffff;
                margin-bottom: 1rem;
            }
            .res-icon-users { background: linear-gradient(135deg, #8b5cf6, #6366f1); box-shadow: 0 6px 14px rgba(99, 102, 241, 0.3); }
            .res-icon-tenants { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 14px rgba(16, 185, 129, 0.3); }
            .res-icon-rooms { background: linear-gradient(135deg, #0ea1b1, #0284c7); box-shadow: 0 6px 14px rgba(14, 161, 177, 0.3); }
            .res-icon-turnover { background: linear-gradient(135deg, #3b82f6, #1d4ed8); box-shadow: 0 6px 14px rgba(59, 130, 246, 0.3); }
            .res-icon-expenses { background: linear-gradient(135deg, #f43f5e, #e11d48); box-shadow: 0 6px 14px rgba(244, 63, 94, 0.3); }
            .res-icon-profit { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 6px 14px rgba(245, 158, 11, 0.3); }

            .res-kpi-title {
                font-size: 0.85rem;
                font-weight: 600;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                margin-bottom: 0.35rem;
            }
            .res-kpi-value {
                font-size: 1.65rem;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 0.25rem;
                line-height: 1.2;
            }
            .res-badge-pill {
                font-size: 0.75rem;
                padding: 0.25rem 0.6rem;
                border-radius: 999px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }

            /* Pipeline Card */
            .res-card {
                background: #ffffff;
                border-radius: 18px;
                border: 1px solid rgba(226, 232, 240, 0.9);
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
            }
            .res-card-title {
                font-size: 1.05rem;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            /* Visual Pipeline */
            .res-pipeline-track {
                height: 14px;
                border-radius: 999px;
                overflow: hidden;
                display: flex;
                background: #f1f5f9;
                margin-bottom: 1rem;
            }
            .res-pipeline-seg {
                height: 100%;
                transition: width 0.6s ease;
                position: relative;
            }
            .res-pipeline-seg-occupied { background: #10b981; }
            .res-pipeline-seg-available { background: #0ea1b1; }
            .res-pipeline-seg-maintenance { background: #f59e0b; }

            .res-pipeline-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
                gap: 1rem;
            }
            .res-pipe-box {
                padding: 0.9rem;
                border-radius: 12px;
                border: 1px solid #f1f5f9;
                background: #f8fafc;
            }
            .res-pipe-box.active-green { border-left: 4px solid #10b981; }
            .res-pipe-box.active-teal { border-left: 4px solid #0ea1b1; }
            .res-pipe-box.active-amber { border-left: 4px solid #f59e0b; }

            /* Action Buttons */
            .res-btn-action {
                border-radius: 10px;
                font-weight: 600;
                font-size: 0.85rem;
                padding: 0.55rem 1rem;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s ease;
            }

            /* Custom Tables */
            .res-table {
                margin-bottom: 0;
            }
            .res-table thead th {
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
                border-bottom: 1px solid #e2e8f0;
                padding: 0.85rem 0.75rem;
            }
            .res-table tbody td {
                padding: 0.95rem 0.75rem;
                vertical-align: middle;
                border-bottom: 1px solid #f1f5f9;
                font-size: 0.9rem;
            }
            .res-table tbody tr:hover {
                background-color: #f8fafc;
            }

            .res-avatar {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                background: linear-gradient(135deg, #0ea1b1, #3b82f6);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.85rem;
            }
        </style>
    @endpush

    <div class="res-dashboard">

        <!-- Welcome Banner & Quick Actions -->
        <div class="res-hero-banner d-flex flex-wrap justify-content-between align-items-center mt-1">
            <div>
                <span class="badge bg-white text-dark px-3 py-1 rounded-pill mb-2 fw-semibold">
                    <i class="fas fa-building text-info me-1"></i> SRM RESIDENCE ADMIN
                </span>
                <h3 class="fw-bold mb-1 text-white">Residence Operations & Financial Dashboard</h3>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;">
                    Live overview of tenants, room allocations, monthly turnover, and operational expenses.
                </p>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                <a href="{{ url('admin/tenants') }}" class="btn btn-light res-btn-action text-dark">
                    <i class="fas fa-user-plus text-primary"></i> Create Tenant
                </a>
                <a href="{{ url('admin/rooms') }}" class="btn btn-light res-btn-action text-dark">
                    <i class="fas fa-door-open text-info"></i> View Rooms
                </a>
                <a href="{{ url('admin/billing') }}" class="btn btn-warning res-btn-action text-dark fw-bold">
                    <i class="fas fa-file-invoice-dollar"></i> Bills & Payments
                </a>
            </div>
        </div>

        <!-- 6 KPI CARDS (Users, Tenants, Rooms, Monthly Turnover, Expenses, Net Profit) -->
        <div class="row g-3 mb-4">
            <!-- 1. Users & Staff -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="res-kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="res-icon-box res-icon-users">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <span class="res-badge-pill bg-light text-primary">
                            <i class="fas fa-shield-alt"></i> {{ $residenceData['staffCount'] }} Staff
                        </span>
                    </div>
                    <div class="res-kpi-title">Total Users</div>
                    <div class="res-kpi-value">{{ $residenceData['totalUsers'] }}</div>
                    <small class="text-muted"><i class="fas fa-check-circle text-success me-1"></i> Active Accounts</small>
                </div>
            </div>

            <!-- 2. Active Tenants -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="res-kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="res-icon-box res-icon-tenants">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <span class="res-badge-pill bg-success-subtle text-success">
                            <i class="fas fa-user-check"></i> {{ $residenceData['tenantCount'] }} Active
                        </span>
                    </div>
                    <div class="res-kpi-title">Active Tenants</div>
                    <div class="res-kpi-value">{{ $residenceData['tenantCount'] }}</div>
                    <small class="text-muted"><i class="fas fa-bed text-success me-1"></i> Residing currently</small>
                </div>
            </div>

            <!-- 3. Rooms & Occupancy -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="res-kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="res-icon-box res-icon-rooms">
                            <i class="fas fa-door-closed"></i>
                        </div>
                        <span class="res-badge-pill bg-info-subtle text-info">
                            {{ $residenceData['occupancyRate'] }}% Full
                        </span>
                    </div>
                    <div class="res-kpi-title">Total Rooms</div>
                    <div class="res-kpi-value">{{ $residenceData['totalRooms'] }}</div>
                    <small class="text-muted">
                        <span class="text-success fw-bold">{{ $residenceData['availableRooms'] }}</span> Vacant &bull; 
                        <span class="text-warning fw-bold">{{ $residenceData['maintenanceRooms'] }}</span> Maint.
                    </small>
                </div>
            </div>

            <!-- 4. Monthly Turnover -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="res-kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="res-icon-box res-icon-turnover">
                            <i class="fas fa-indian-rupee-sign"></i>
                        </div>
                        <span class="res-badge-pill bg-success-subtle text-success">
                            <i class="fas fa-arrow-up"></i> {{ $residenceData['monthlyTurnoverGrowth'] ?? '+12.5%' }}
                        </span>
                    </div>
                    <div class="res-kpi-title">Monthly Turnover</div>
                    <div class="res-kpi-value">₹{{ number_format($residenceData['monthlyTurnover'] ?? 0) }}</div>
                    <small class="text-muted"><i class="fas fa-calendar-check text-primary me-1"></i> This Month</small>
                </div>
            </div>

            <!-- 5. Monthly Expenses -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="res-kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="res-icon-box res-icon-expenses">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <span class="res-badge-pill bg-danger-subtle text-danger">
                            <i class="fas fa-arrow-down"></i> {{ $residenceData['monthlyExpenseChange'] ?? '-3.2%' }}
                        </span>
                    </div>
                    <div class="res-kpi-title">Monthly Expenses</div>
                    <div class="res-kpi-value">₹{{ number_format($residenceData['monthlyExpenses'] ?? 0) }}</div>
                    <small class="text-muted"><i class="fas fa-bolt text-warning me-1"></i> Power, Staff, Mess</small>
                </div>
            </div>

            <!-- 6. Net Profit -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="res-kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="res-icon-box res-icon-profit">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="res-badge-pill bg-warning-subtle text-dark">
                            {{ $residenceData['profitMargin'] ?? '66.5%' }}
                        </span>
                    </div>
                    <div class="res-kpi-title">Net Cashflow</div>
                    <div class="res-kpi-value">₹{{ number_format($residenceData['netProfit'] ?? 0) }}</div>
                    <small class="text-muted"><i class="fas fa-wallet text-success me-1"></i> Net Operating Margin</small>
                </div>
            </div>
        </div>

        <!-- HIGHLIGHT BAR: Security Deposits Held & Scheduled Monthly Rent Roll -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="res-card p-3 mb-0 border-start border-4 border-info d-flex align-items-center justify-content-between shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <div class="res-icon-box mb-0" style="background: linear-gradient(135deg, #0ea1b1, #0284c7); box-shadow: 0 6px 14px rgba(14, 161, 177, 0.3); width: 48px; height: 48px;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <span class="res-kpi-title mb-0 d-block">Scheduled Monthly Rent Roll</span>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="res-kpi-value mb-0 text-dark">₹{{ number_format($residenceData['totalActiveMonthlyDue'] ?? $residenceData['totalActiveRent'] ?? 0) }}</span>
                                <span class="text-muted small">/ month</span>
                            </div>
                            <small class="text-muted font-12">
                                Base: <span class="fw-bold text-dark">₹{{ number_format($residenceData['totalActiveRent'] ?? 0) }}</span> &bull; 
                                Total Room Capacity: <span class="fw-bold text-dark">₹{{ number_format($residenceData['totalRoomRentCapacity'] ?? 0) }}</span>
                            </small>
                        </div>
                    </div>
                    <a href="{{ url('admin/tenants') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                        View Leases <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="res-card p-3 mb-0 border-start border-4 border-purple d-flex align-items-center justify-content-between shadow-sm" style="border-left-color: #8b5cf6 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="res-icon-box mb-0" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); box-shadow: 0 6px 14px rgba(139, 92, 246, 0.3); width: 48px; height: 48px;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <span class="res-kpi-title mb-0 d-block">Total Security Deposits Held</span>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="res-kpi-value mb-0 text-dark" style="color: #6d28d9 !important;">₹{{ number_format($residenceData['totalSecurityDeposits'] ?? 0) }}</span>
                                <span class="badge" style="background-color: #f3e8ff; color: #7c3aed;">Refundable Trust</span>
                            </div>
                            <small class="text-muted font-12">
                                Active Leases: <span class="fw-bold text-dark">{{ $residenceData['tenantCount'] ?? 0 }} Tenants</span> &bull; 
                                Deposit Potential: <span class="fw-bold text-dark">₹{{ number_format($residenceData['totalRoomDepositCapacity'] ?? 0) }}</span>
                            </small>
                        </div>
                    </div>
                    <a href="{{ url('admin/billing') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        Ledger <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        @php
            $totRooms = $residenceData['totalRooms'] ?? 23;
            $availRooms = $residenceData['availableRooms'] ?? 0;
            $maintRooms = $residenceData['maintenanceRooms'] ?? 0;
            $availRate = $totRooms > 0 ? round(($availRooms / $totRooms) * 100, 1) : 0;
            $maintRate = $totRooms > 0 ? round(($maintRooms / $totRooms) * 100, 1) : 0;
        @endphp

        <!-- PIPELINES ROW: Room Occupancy & Rent Collection -->
        <div class="row g-3 mb-4">
            <!-- Room Occupancy Pipeline -->
            <div class="col-lg-7">
                <div class="res-card h-100">
                    <div class="res-card-title">
                        <span><i class="fas fa-stream text-primary me-2"></i> Room Occupancy & Allocation Pipeline</span>
                        <span class="badge bg-primary-subtle text-primary fw-semibold">Capacity: {{ $totRooms }} Units</span>
                    </div>

                    <!-- Visual Segmented Bar -->
                    <div class="res-pipeline-track">
                        <div class="res-pipeline-seg res-pipeline-seg-occupied" style="width: {{ $residenceData['occupancyRate'] ?? 0 }}%;" title="Occupied ({{ $residenceData['occupancyRate'] ?? 0 }}%)"></div>
                        <div class="res-pipeline-seg res-pipeline-seg-available" style="width: {{ $availRate }}%;" title="Available ({{ $availRate }}%)"></div>
                        <div class="res-pipeline-seg res-pipeline-seg-maintenance" style="width: {{ $maintRate }}%;" title="Under Maintenance"></div>
                    </div>

                    <!-- Pipeline Stage Boxes -->
                    <div class="res-pipeline-stats">
                        <div class="res-pipe-box active-green">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small fw-bold">Occupied Rooms</span>
                                <span class="badge bg-success-subtle text-success">{{ $residenceData['occupancyRate'] ?? 0 }}%</span>
                            </div>
                            <h4 class="fw-bold my-1 text-success">{{ $residenceData['occupiedRooms'] ?? 0 }}</h4>
                            <small class="text-muted">Active Tenant Leases</small>
                        </div>

                        <div class="res-pipe-box active-teal">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small fw-bold">Available / Vacant</span>
                                <span class="badge bg-info-subtle text-info">{{ $availRate }}%</span>
                            </div>
                            <h4 class="fw-bold my-1 text-primary">{{ $availRooms }}</h4>
                            <small class="text-muted">Ready for Immediate Move-in</small>
                        </div>

                        <div class="res-pipe-box active-amber">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small fw-bold">Under Maintenance</span>
                                <span class="badge bg-warning-subtle text-warning">{{ $maintRate }}%</span>
                            </div>
                            <h4 class="fw-bold my-1 text-warning">{{ $maintRooms }}</h4>
                            <small class="text-muted">Cleaning / Deep Servicing</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rent Collection Pipeline -->
            <div class="col-lg-5">
                <div class="res-card h-100">
                    <div class="res-card-title">
                        <span><i class="fas fa-hand-holding-dollar text-success me-2"></i> Rent Collection Pipeline</span>
                        <span class="text-danger small fw-bold"><i class="fas fa-clock me-1"></i> Due: ₹{{ number_format($residenceData['pendingDues'] ?? 0) }}</span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach($residenceData['rentPipeline'] as $pipe)
                            <div class="p-2 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold small text-dark">
                                        <i class="fas fa-{{ $pipe['icon'] }} text-{{ $pipe['color'] }} me-1"></i> {{ $pipe['label'] }} ({{ $pipe['count'] }} {{ \Illuminate\Support\Str::plural('tenant', $pipe['count']) }})
                                    </span>
                                    <span class="fw-bold text-{{ $pipe['color'] }}">₹{{ number_format($pipe['amount']) }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $pipe['color'] }}" style="width: {{ $pipe['pct'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- CHARTS SECTION: Turnover vs Expenses & Room Distribution -->
        <div class="row g-3 mb-4">
            <!-- Financial Trend Chart: Monthly Turnover vs Expenses -->
            <div class="col-lg-8">
                <div class="res-card h-100">
                    <div class="res-card-title">
                        <span><i class="fas fa-chart-area text-primary me-2"></i> Monthly Turnover vs. Expenses (Past 6 Months)</span>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-primary border"><i class="fas fa-circle text-primary me-1" style="font-size: 8px;"></i> Turnover</span>
                            <span class="badge bg-light text-danger border"><i class="fas fa-circle text-danger me-1" style="font-size: 8px;"></i> Expenses</span>
                            <span class="badge bg-light text-success border"><i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i> Net Profit</span>
                        </div>
                    </div>
                    <div style="height: 320px; position: relative;">
                        <canvas id="financialChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Expense Category Breakdown Donut Chart -->
            <div class="col-lg-4">
                <div class="res-card h-100">
                    <div class="res-card-title">
                        <span><i class="fas fa-pie-chart text-danger me-2"></i> Monthly Expense Breakdown</span>
                    </div>
                    <div style="height: 250px; position: relative;" class="d-flex justify-content-center align-items-center">
                        <canvas id="expenseChart"></canvas>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        @if(!empty($residenceData['expenseCategories']['top']))
                            @foreach($residenceData['expenseCategories']['top'] as $topExp)
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>{{ $topExp['category'] }}</span>
                                    <span class="fw-bold text-dark">{{ $topExp['pct'] }} (₹{{ number_format($topExp['amount']) }})</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted small py-1">
                                <i class="fas fa-info-circle me-1"></i> No operational expenses logged this month.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- DETAILS ROW: Recent Tenants & Maintenance Complaints -->
        <div class="row g-3">
            <!-- Recent Tenant Check-ins -->
            <div class="col-lg-8">
                <div class="res-card h-100">
                    <div class="res-card-title">
                        <span><i class="fas fa-user-check text-success me-2"></i> Recent Tenant Check-Ins & Allocations</span>
                        <a href="{{ url('admin/tenants') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fas fa-users-cog me-1"></i> View All Tenants
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table res-table">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Room & Type</th>
                                    <th>Commence Date</th>
                                    <th>Monthly Rent</th>
                                    <th>Rent Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($residenceData['recentTenants'] as $tenant)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="res-avatar">
                                                {{ strtoupper(substr($tenant['name'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $tenant['name'] }}</div>
                                                <small class="text-muted">{{ $tenant['phone'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info fw-bold">{{ $tenant['room'] }}</span>
                                        <div class="small text-muted">{{ $tenant['type'] }}</div>
                                    </td>
                                    <td>
                                        <span class="text-secondary small"><i class="far fa-calendar-alt me-1"></i> {{ $tenant['date'] }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">₹{{ number_format($tenant['rent']) }}</span>
                                    </td>
                                    <td>
                                        @if($tenant['status'] == 'Paid')
                                            <span class="badge bg-success-subtle text-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> Paid</span>
                                        @elseif($tenant['status'] == 'Due')
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1"><i class="fas fa-clock me-1"></i> Due</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 btn-view-tenant-modal" 
                                                title="View Tenant Quick Summary"
                                                data-tenant='@json($tenant)'>
                                                <i class="fas fa-eye me-1"></i> Quick View
                                            </button>
                                            <a href="{{ url('admin/tenants/' . $tenant['id'] . '/view') }}" 
                                                class="btn btn-sm btn-light border rounded-circle ms-1" 
                                                title="Open Resident Profile Page">
                                                <i class="fas fa-external-link-alt text-muted font-11"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Maintenance & Complaints -->
            <div class="col-lg-4">
                <div class="res-card h-100">
                    <div class="res-card-title">
                        <span><i class="fas fa-tools text-warning me-2"></i> Maintenance & Complaints</span>
                        <span class="badge bg-warning-subtle text-warning">{{ count($residenceData['maintenanceRequests']) }} Active</span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse($residenceData['maintenanceRequests'] as $req)
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">{{ $req['room'] }}</span>
                                @if($req['priority'] == 'High')
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1">High Priority</span>
                                @elseif($req['priority'] == 'Medium')
                                    <span class="badge bg-warning-subtle text-warning px-2 py-1">Medium</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1">Low</span>
                                @endif
                            </div>
                            <p class="mb-1 text-secondary small fw-medium">{{ $req['issue'] }}</p>
                            <div class="d-flex justify-content-between align-items-center small text-muted">
                                <span><i class="fas fa-tag me-1"></i> {{ $req['category'] }}</span>
                                <span><i class="far fa-clock me-1"></i> {{ $req['time'] }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-4 border rounded-3 bg-light text-center my-auto">
                            <div class="avatar-sm mx-auto mb-2 text-success">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">All Facilities Operational</h6>
                            <p class="text-muted small mb-0">No active maintenance complaints or room servicing tickets.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- TENANT QUICK VIEW MODAL -->
    <div class="modal fade" id="tenantDetailsModal" tabindex="-1" aria-labelledby="tenantDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="tenantDetailsModalLabel">
                        <i class="fas fa-user-circle me-1"></i> <span id="tv_title_name">Resident Details</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Left: Profile & Room Card -->
                        <div class="col-md-5">
                            <div class="p-3 bg-light rounded border h-100 text-center">
                                <div class="res-avatar mx-auto mb-2" style="width: 60px; height: 60px; font-size: 24px; line-height: 60px;" id="tv_avatar">T</div>
                                <h5 class="fw-bold text-dark mb-1" id="tv_card_name">Tenant Name</h5>
                                <span class="badge bg-primary text-white px-2 py-1 mb-1" id="tv_room_badge">Room</span>
                                <div class="small text-muted mb-3" id="tv_room_type">Room Type</div>
                                
                                <hr class="my-2">
                                <div class="text-start small">
                                    <div class="mb-2 d-flex justify-content-between">
                                        <strong class="text-muted">Commenced:</strong> 
                                        <span class="text-dark fw-semibold" id="tv_date">-</span>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <strong class="text-muted">Rent Status:</strong> 
                                        <span id="tv_status_badge">-</span>
                                    </div>
                                    <div class="mb-0 d-flex justify-content-between">
                                        <strong class="text-muted">Deposit:</strong> 
                                        <span class="text-dark fw-semibold" id="tv_deposit">₹0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Breakdown & Contact Details -->
                        <div class="col-md-7">
                            <h6 class="fw-bold text-primary mb-2"><i class="fas fa-address-card me-1"></i> Contact Information</h6>
                            <div class="p-2 px-3 bg-white rounded border mb-3 small">
                                <div class="mb-1"><strong>Phone:</strong> <a href="#" id="tv_phone_link" class="text-primary ms-1 fw-semibold"><span id="tv_phone">-</span></a></div>
                                <div class="mb-1"><strong>Email:</strong> <span class="text-dark ms-1" id="tv_email">-</span></div>
                                <div class="mb-1"><strong>Emergency:</strong> <span class="text-dark ms-1" id="tv_emergency">-</span></div>
                                <div class="mb-0"><strong>Address:</strong> <span class="text-muted ms-1" id="tv_address">-</span></div>
                            </div>

                            <h6 class="fw-bold text-success mb-2"><i class="fas fa-file-invoice-dollar me-1"></i> Monthly Package Breakdown</h6>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-striped mb-0 font-12">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted">Base Room Rent:</td>
                                            <td class="text-end fw-bold" id="tv_base_rent">₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Maintenance Charges:</td>
                                            <td class="text-end fw-bold" id="tv_maintenance">₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Other Charges:</td>
                                            <td class="text-end fw-bold" id="tv_other">₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">EB Electricity Rate:</td>
                                            <td class="text-end text-muted font-11" id="tv_eb_rate">₹10.00 / unit</td>
                                        </tr>
                                        <tr class="table-success">
                                            <td class="fw-bold text-dark">Total Monthly Package:</td>
                                            <td class="text-end fw-bold text-success font-14" id="tv_total_rent">₹0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ url('admin/tenants') }}" id="tv_manage_btn" class="btn button-color text-white">
                        <i class="fas fa-user-circle me-1"></i> Open Full Profile Page
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        @php
            $chartMonths = $residenceData['financialTrends']['months'] ?? ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'];
            $chartTurnover = $residenceData['financialTrends']['turnover'] ?? [0, 0, 0, 0, 0, 0];
            $chartExpenses = $residenceData['financialTrends']['expenses'] ?? [0, 0, 0, 0, 0, 0];
            $chartProfit = $residenceData['financialTrends']['profit'] ?? [0, 0, 0, 0, 0, 0];
            $expenseLabels = $residenceData['expenseCategories']['labels'] ?? ['No Expenses Recorded'];
            $expenseAmounts = $residenceData['expenseCategories']['amounts'] ?? [0];
        @endphp
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Financial Trend Chart
                const financialCtx = document.getElementById('financialChart');
                if (financialCtx) {
                    const months = @json($chartMonths);
                    const turnover = @json($chartTurnover);
                    const expenses = @json($chartExpenses);
                    const profit = @json($chartProfit);

                    new Chart(financialCtx, {
                        type: 'line',
                        data: {
                            labels: months,
                            datasets: [
                                {
                                    label: 'Turnover (₹)',
                                    data: turnover,
                                    borderColor: '#0ea1b1',
                                    backgroundColor: 'rgba(14, 161, 177, 0.1)',
                                    tension: 0.35,
                                    fill: true,
                                    borderWidth: 3,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#0ea1b1'
                                },
                                {
                                    label: 'Expenses (₹)',
                                    data: expenses,
                                    borderColor: '#ef4444',
                                    backgroundColor: 'transparent',
                                    borderDash: [5, 5],
                                    tension: 0.35,
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#ef4444'
                                },
                                {
                                    label: 'Net Profit (₹)',
                                    data: profit,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                                    tension: 0.35,
                                    fill: true,
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#10b981'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN');
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return '₹' + Number(value).toLocaleString('en-IN');
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                // Expense Breakdown Donut Chart
                const expenseCtx = document.getElementById('expenseChart');
                if (expenseCtx) {
                    const rawLabels = @json($expenseLabels);
                    const rawAmounts = @json($expenseAmounts);
                    const totalAmount = rawAmounts.reduce((a, b) => a + b, 0);

                    if (totalAmount === 0) {
                        new Chart(expenseCtx, {
                            type: 'doughnut',
                            data: {
                                labels: ['No Expenses Recorded'],
                                datasets: [{
                                    data: [1],
                                    backgroundColor: ['#e2e8f0'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 12,
                                            font: { size: 11 }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function() { return 'No expenses logged yet'; }
                                        }
                                    }
                                },
                                cutout: '68%'
                            }
                        });
                    } else {
                        new Chart(expenseCtx, {
                            type: 'doughnut',
                            data: {
                                labels: rawLabels,
                                datasets: [{
                                    data: rawAmounts,
                                    backgroundColor: [
                                        '#0ea1b1',
                                        '#8b5cf6',
                                        '#f59e0b',
                                        '#3b82f6',
                                        '#10b981',
                                        '#ec4899',
                                        '#64748b'
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#ffffff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 12,
                                            font: {
                                                size: 11
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.label + ': ₹' + Number(context.raw).toLocaleString('en-IN');
                                            }
                                        }
                                    }
                                },
                                cutout: '68%'
                            }
                        });
                    }
                }

                // Tenant Quick View Modal Handler
                $(document).on('click', '.btn-view-tenant-modal', function() {
                    var t = $(this).data('tenant');
                    if (!t) return;

                    $('#tv_title_name').text(t.name + ' - Resident Details');
                    $('#tv_card_name').text(t.name);
                    $('#tv_avatar').text(t.name.charAt(0).toUpperCase());
                    $('#tv_room_badge').text(t.room);
                    $('#tv_room_type').text(t.type + (t.floor && t.floor !== '-' ? ' (' + t.floor + ')' : ''));
                    $('#tv_date').text(t.date);

                    var statusHtml = '';
                    if (t.status === 'Paid') {
                        statusHtml = '<span class="badge bg-success-subtle text-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> Paid</span>';
                    } else if (t.status === 'Due') {
                        statusHtml = '<span class="badge bg-warning-subtle text-warning px-2 py-1"><i class="fas fa-clock me-1"></i> Due</span>';
                    } else {
                        statusHtml = '<span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>';
                    }
                    $('#tv_status_badge').html(statusHtml);

                    $('#tv_deposit').text('₹' + Number(t.deposit || 0).toLocaleString('en-IN'));
                    $('#tv_phone').text(t.phone);
                    $('#tv_phone_link').attr('href', t.phone !== '-' ? 'tel:' + t.phone : '#');
                    $('#tv_email').text(t.email);
                    $('#tv_emergency').text(t.emergency_contact);
                    $('#tv_address').text(t.address);

                    $('#tv_base_rent').text('₹' + Number(t.base_rent || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                    $('#tv_maintenance').text('₹' + Number(t.maintenance || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                    $('#tv_other').text('₹' + Number(t.other_charges || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                    $('#tv_eb_rate').text('₹' + Number(t.eb_rate || 0).toFixed(2) + ' / unit (Start: ' + Number(t.eb_start_reading || 0) + ' kWh)');
                    $('#tv_total_rent').text('₹' + Number(t.rent || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                    $('#tv_manage_btn').attr('href', "{{ url('admin/tenants') }}/" + t.id + "/view");

                    $('#tenantDetailsModal').modal('show');
                });
            });
        </script>
    @endpush
@endsection
