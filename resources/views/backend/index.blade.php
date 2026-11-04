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

    <div class="res-dashboard mt-3">

        <!-- Welcome Banner & Quick Actions -->
        <div class="res-hero-banner d-flex flex-wrap justify-content-between align-items-center">
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
                <button type="button" class="btn btn-light res-btn-action text-dark" onclick="alert('Add Tenant form ready')">
                    <i class="fas fa-user-plus text-primary"></i> Add Tenant
                </button>
                <button type="button" class="btn btn-light res-btn-action text-dark" onclick="alert('Room Allocation dialog')">
                    <i class="fas fa-door-open text-info"></i> Allocate Room
                </button>
                <button type="button" class="btn btn-warning res-btn-action text-dark fw-bold" onclick="alert('Record Expense dialog')">
                    <i class="fas fa-receipt"></i> Log Expense
                </button>
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
                            <i class="fas fa-arrow-up"></i> +8 New
                        </span>
                    </div>
                    <div class="res-kpi-title">Active Tenants</div>
                    <div class="res-kpi-value">{{ $residenceData['occupiedRooms'] }}</div>
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
                            <i class="fas fa-arrow-up"></i> {{ $residenceData['monthlyTurnoverGrowth'] }}
                        </span>
                    </div>
                    <div class="res-kpi-title">Monthly Turnover</div>
                    <div class="res-kpi-value">₹{{ number_format($residenceData['monthlyTurnover']) }}</div>
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
                            <i class="fas fa-arrow-down"></i> {{ $residenceData['monthlyExpenseChange'] }}
                        </span>
                    </div>
                    <div class="res-kpi-title">Monthly Expenses</div>
                    <div class="res-kpi-value">₹{{ number_format($residenceData['monthlyExpenses']) }}</div>
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
                            {{ $residenceData['profitMargin'] }}
                        </span>
                    </div>
                    <div class="res-kpi-title">Net Cashflow</div>
                    <div class="res-kpi-value">₹{{ number_format($residenceData['netProfit']) }}</div>
                    <small class="text-muted"><i class="fas fa-wallet text-success me-1"></i> Net Operating Margin</small>
                </div>
            </div>
        </div>

        <!-- PIPELINES ROW: Room Occupancy & Rent Collection -->
        <div class="row g-3 mb-4">
            <!-- Room Occupancy Pipeline -->
            <div class="col-lg-7">
                <div class="res-card h-100">
                    <div class="res-card-title">
                        <span><i class="fas fa-stream text-primary me-2"></i> Room Occupancy & Allocation Pipeline</span>
                        <span class="badge bg-primary-subtle text-primary fw-semibold">Capacity: {{ $residenceData['totalRooms'] }} Units</span>
                    </div>

                    <!-- Visual Segmented Bar -->
                    <div class="res-pipeline-track">
                        <div class="res-pipeline-seg res-pipeline-seg-occupied" style="width: {{ $residenceData['occupancyRate'] }}%;" title="Occupied ({{ $residenceData['occupancyRate'] }}%)"></div>
                        <div class="res-pipeline-seg res-pipeline-seg-available" style="width: {{ round(($residenceData['availableRooms'] / $residenceData['totalRooms']) * 100, 1) }}%;" title="Available ({{ round(($residenceData['availableRooms'] / $residenceData['totalRooms']) * 100, 1) }}%)"></div>
                        <div class="res-pipeline-seg res-pipeline-seg-maintenance" style="width: {{ round(($residenceData['maintenanceRooms'] / $residenceData['totalRooms']) * 100, 1) }}%;" title="Under Maintenance"></div>
                    </div>

                    <!-- Pipeline Stage Boxes -->
                    <div class="res-pipeline-stats">
                        <div class="res-pipe-box active-green">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small fw-bold">Occupied Rooms</span>
                                <span class="badge bg-success-subtle text-success">{{ $residenceData['occupancyRate'] }}%</span>
                            </div>
                            <h4 class="fw-bold my-1 text-success">{{ $residenceData['occupiedRooms'] }}</h4>
                            <small class="text-muted">Active Tenant Leases</small>
                        </div>

                        <div class="res-pipe-box active-teal">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small fw-bold">Available / Vacant</span>
                                <span class="badge bg-info-subtle text-info">{{ round(($residenceData['availableRooms'] / $residenceData['totalRooms']) * 100, 1) }}%</span>
                            </div>
                            <h4 class="fw-bold my-1 text-primary">{{ $residenceData['availableRooms'] }}</h4>
                            <small class="text-muted">Ready for Immediate Move-in</small>
                        </div>

                        <div class="res-pipe-box active-amber">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small fw-bold">Under Maintenance</span>
                                <span class="badge bg-warning-subtle text-warning">{{ round(($residenceData['maintenanceRooms'] / $residenceData['totalRooms']) * 100, 1) }}%</span>
                            </div>
                            <h4 class="fw-bold my-1 text-warning">{{ $residenceData['maintenanceRooms'] }}</h4>
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
                        <span class="text-danger small fw-bold"><i class="fas fa-clock me-1"></i> Due: ₹{{ number_format($residenceData['pendingDues']) }}</span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach([
                            ['label' => 'Collected On-Time', 'amount' => 358900, 'count' => 74, 'pct' => 74, 'color' => 'success', 'icon' => 'check-circle'],
                            ['label' => 'Grace Period / Due', 'amount' => 87300, 'count' => 16, 'pct' => 18, 'color' => 'warning', 'icon' => 'hourglass-half'],
                            ['label' => 'Overdue (> 7 Days)', 'amount' => 38800, 'count' => 4, 'pct' => 8, 'color' => 'danger', 'icon' => 'exclamation-circle']
                        ] as $pipe)
                            <div class="p-2 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold small text-dark">
                                        <i class="fas fa-{{ $pipe['icon'] }} text-{{ $pipe['color'] }} me-1"></i> {{ $pipe['label'] }} ({{ $pipe['count'] }} tenants)
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
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Top Expense: Electricity & Power</span>
                            <span class="fw-bold text-dark">32% (₹52,000)</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Staff Salaries & Security</span>
                            <span class="fw-bold text-dark">28% (₹45,000)</span>
                        </div>
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
                        <a href="{{ url('admin/users') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All Tenants</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table res-table">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Room & Type</th>
                                    <th>Check-in Date</th>
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
                                        <button class="btn btn-sm btn-light border rounded-circle" title="View Tenant"><i class="fas fa-chevron-right text-muted"></i></button>
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
                        <span class="badge bg-warning-subtle text-warning">4 Active</span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach($residenceData['maintenanceRequests'] as $req)
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
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('footer_content')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Financial Trend Chart
                const financialCtx = document.getElementById('financialChart');
                if (financialCtx) {
                    const months = @json($residenceData['financialTrends']['months']);
                    const turnover = @json($residenceData['financialTrends']['turnover']);
                    const expenses = @json($residenceData['financialTrends']['expenses']);
                    const profit = @json($residenceData['financialTrends']['profit']);

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
                                            return '₹' + (value / 1000) + 'k';
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
                    const expenseLabels = @json($residenceData['expenseCategories']['labels']);
                    const expenseAmounts = @json($residenceData['expenseCategories']['amounts']);

                    new Chart(expenseCtx, {
                        type: 'doughnut',
                        data: {
                            labels: expenseLabels,
                            datasets: [{
                                data: expenseAmounts,
                                backgroundColor: [
                                    '#0ea1b1',
                                    '#8b5cf6',
                                    '#f59e0b',
                                    '#3b82f6',
                                    '#10b981'
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
            });
        </script>
    @endpush
@endsection
