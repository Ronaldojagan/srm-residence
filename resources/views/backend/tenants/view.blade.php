@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
        <style>
            .tenant-hero-card {
                background: linear-gradient(135deg, #0ea1b1 0%, #086d77 100%);
                color: #ffffff;
                border-radius: 16px;
                padding: 1.75rem;
                box-shadow: 0 10px 25px -5px rgba(14, 161, 177, 0.25);
            }
            .tenant-view-stat {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                padding: 1.25rem;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
                height: 100%;
                transition: transform 0.2s ease;
            }
            .tenant-view-stat:hover {
                transform: translateY(-2px);
            }
            .tenant-avatar-circle {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.2);
                border: 3px solid rgba(255, 255, 255, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 28px;
                font-weight: 700;
                color: #ffffff;
            }
            .card-section {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
                overflow: hidden;
            }
            .card-section-header {
                padding: 1rem 1.25rem;
                border-bottom: 1px solid #edf2f7;
                background: #f8fafc;
                font-weight: 700;
                font-size: 14px;
            }
            .info-label {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #64748b;
                font-weight: 600;
                margin-bottom: 2px;
            }
            .info-value {
                font-size: 14px;
                font-weight: 600;
                color: #1e293b;
            }
            .nav-tabs-custom .nav-link {
                font-weight: 600;
                color: #64748b;
                border: none;
                border-bottom: 2px solid transparent;
                padding: 0.75rem 1.25rem;
            }
            .nav-tabs-custom .nav-link.active {
                color: #0ea1b1;
                border-bottom: 2px solid #0ea1b1;
                background: transparent;
            }
        </style>
    @endpush

    <div class="container-fluid py-3">
        <!-- Top Action & Breadcrumb Bar -->
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url('/admin/tenants') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Tenants
                    </a>
                    <span class="text-muted">/</span>
                    <span class="fw-bold text-dark font-15">Resident Profile</span>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#editTenantModal">
                        <i class="fas fa-edit me-1"></i> Edit Tenant Details
                    </button>
                    <a href="{{ url('/admin/billing') }}" class="btn button-color text-white btn-sm rounded-pill px-3">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Create Bill
                    </a>
                </div>
            </div>
        </div>

        <!-- Hero Card -->
        <div class="tenant-hero-card mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="tenant-avatar-circle">
                            {{ strtoupper(substr($tenant->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h3 class="fw-bold text-white mb-0">{{ $tenant->name }}</h3>
                                @if($tenant->status)
                                    <span class="badge bg-success text-white px-2 py-1 font-11 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Active Resident
                                    </span>
                                @else
                                    <span class="badge bg-secondary text-white px-2 py-1 font-11 rounded-pill">
                                        <i class="fas fa-user-times me-1"></i> Inactive / Vacated
                                    </span>
                                @endif
                            </div>
                            <div class="text-white-50 font-13 d-flex flex-wrap gap-3">
                                <span><i class="mdi mdi-door-closed me-1"></i> Room {{ $tenant->room ? $tenant->room->room_number : $tenant->room_number }} ({{ $tenant->room ? $tenant->room->floor : 'Floor' }})</span>
                                <span><i class="mdi mdi-phone me-1"></i> {{ $tenant->phone }}</span>
                                <span><i class="mdi mdi-email-outline me-1"></i> {{ $tenant->email ?? 'No email' }}</span>
                                <span><i class="mdi mdi-calendar-check me-1"></i> Commenced: {{ $tenant->commence_date ? $tenant->commence_date->format('d M Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="p-2 px-3 bg-white bg-opacity-10 rounded-3 d-inline-block text-md-end text-start">
                        <div class="small text-white-50 text-uppercase font-11">Total Monthly Rent & Utilities</div>
                        <h2 class="fw-bold text-white mb-0">₹{{ number_format($tenant->total_monthly_due, 2) }}</h2>
                        <small class="text-white-50 font-11">+ EB electricity per unit consumption</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 KPI Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="tenant-view-stat border-start border-4 border-primary">
                    <div class="info-label">Assigned Room</div>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <div>
                            <h4 class="fw-bold text-primary mb-0">Room {{ $tenant->room ? $tenant->room->room_number : $tenant->room_number }}</h4>
                            <small class="text-muted">{{ $tenant->room_type }}</small>
                        </div>
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center p-2">
                            <i class="mdi mdi-door-closed fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="tenant-view-stat border-start border-4 border-success">
                    <div class="info-label">Total Amount Billed</div>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <div>
                            <h4 class="fw-bold text-success mb-0">₹{{ number_format($stats['totalBilled'], 2) }}</h4>
                            <small class="text-muted">{{ $stats['totalBills'] }} Total Invoices</small>
                        </div>
                        <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center p-2">
                            <i class="mdi mdi-receipt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="tenant-view-stat border-start border-4 border-info">
                    <div class="info-label">Total Collected</div>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <div>
                            <h4 class="fw-bold text-info mb-0">₹{{ number_format($stats['totalPaid'], 2) }}</h4>
                            <small class="text-muted">{{ $stats['paidBills'] }} Fully Paid</small>
                        </div>
                        <div class="avatar-sm bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center p-2">
                            <i class="mdi mdi-cash-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="tenant-view-stat border-start border-4 {{ $stats['outstandingDues'] > 0 ? 'border-danger' : 'border-success' }}">
                    <div class="info-label">Pending / Outstanding Dues</div>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <div>
                            <h4 class="fw-bold {{ $stats['outstandingDues'] > 0 ? 'text-danger' : 'text-success' }} mb-0">
                                ₹{{ number_format($stats['outstandingDues'], 2) }}
                            </h4>
                            <small class="text-muted">
                                @if($stats['outstandingDues'] > 0)
                                    <span class="text-danger fw-semibold">{{ $stats['dueBills'] }} bill(s) pending</span>
                                @else
                                    <span class="text-success fw-semibold">All dues cleared</span>
                                @endif
                            </small>
                        </div>
                        <div class="avatar-sm {{ $stats['outstandingDues'] > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} rounded-circle d-flex align-items-center justify-content-center p-2">
                            <i class="mdi mdi-alert-circle-outline fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Body: Two Columns -->
        <div class="row g-3">
            <!-- Left Column: Resident Profile & Room Info -->
            <div class="col-lg-4">
                <!-- Personal & Contact Information -->
                <div class="card-section mb-3">
                    <div class="card-section-header d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-id-card text-primary me-2"></i> Resident Information</span>
                        <span class="badge bg-light text-dark font-11">ID: #{{ $tenant->id }}</span>
                    </div>
                    <div class="p-3">
                        <div class="mb-3">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">{{ $tenant->name }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">
                                <a href="tel:{{ $tenant->phone }}" class="text-primary text-decoration-none">
                                    <i class="fas fa-phone-alt me-1 font-12"></i> {{ $tenant->phone }}
                                </a>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Email Address</div>
                            <div class="info-value">
                                @if($tenant->email)
                                    <a href="mailto:{{ $tenant->email }}" class="text-primary text-decoration-none">
                                        <i class="fas fa-envelope me-1 font-12"></i> {{ $tenant->email }}
                                    </a>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Emergency Contact (Parent / Guardian)</div>
                            <div class="info-value">
                                @if($tenant->emergency_contact)
                                    <a href="tel:{{ $tenant->emergency_contact }}" class="text-danger text-decoration-none">
                                        <i class="fas fa-phone-square-alt me-1 font-12"></i> {{ $tenant->emergency_contact }}
                                    </a>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Permanent / Home Address</div>
                            <div class="info-value text-muted font-13">
                                {{ $tenant->address ?? 'No address recorded' }}
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="info-label">Commencement Date</div>
                            <div class="info-value">
                                <i class="far fa-calendar-alt me-1 text-muted"></i>
                                {{ $tenant->commence_date ? $tenant->commence_date->format('d M Y') : 'N/A' }}
                                @if($tenant->commence_date)
                                    <small class="text-muted">({{ $tenant->commence_date->diffForHumans() }})</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Room Details -->
                <div class="card-section mb-3">
                    <div class="card-section-header d-flex align-items-center justify-content-between">
                        <span><i class="mdi mdi-door-closed text-primary me-2"></i> Allocated Room Unit</span>
                        <span class="badge bg-primary text-white">Room {{ $tenant->room ? $tenant->room->room_number : $tenant->room_number }}</span>
                    </div>
                    <div class="p-3">
                        @if($tenant->room)
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="info-label">Floor</div>
                                    <div class="info-value">{{ $tenant->room->floor }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">Capacity</div>
                                    <div class="info-value">{{ $tenant->room->capacity }} Bed(s)</div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="info-label">Room Type</div>
                                    <div class="info-value">{{ $tenant->room->room_type }}</div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="info-label">Occupancy Status</div>
                                    <div class="info-value">
                                        @if($tenant->room->occupancy_status === 'occupied')
                                            <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1"><i class="fas fa-user-lock me-1"></i> Occupied</span>
                                        @elseif($tenant->room->occupancy_status === 'available')
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> Available</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1">{{ ucfirst($tenant->room->occupancy_status) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="info-label">Room Amenities</div>
                                    <div class="small text-muted">{{ $tenant->room->amenities ?? 'Standard amenities provided' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="p-3 text-center text-muted bg-light rounded">
                                <i class="mdi mdi-door-closed-off fs-2 d-block mb-1"></i>
                                Unassigned or Room record not linked
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Charges Package, Invoices, and Payment History -->
            <div class="col-lg-8">
                <!-- Package Breakdown Card -->
                <div class="card-section mb-3">
                    <div class="card-section-header d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-layer-group text-success me-2"></i> Monthly Package & Utility Charges</span>
                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Active Rates</span>
                    </div>
                    <div class="p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Fee Component</th>
                                        <th>Description</th>
                                        <th class="text-end" width="25%">Configured Rate / Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">Base Monthly Rent</div>
                                            <small class="text-muted">Standard room accommodation charge</small>
                                        </td>
                                        <td class="text-muted small">Fixed monthly recurring</td>
                                        <td class="text-end fw-bold text-dark font-14">₹{{ number_format($tenant->monthly_rent, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">Maintenance Charges</div>
                                            <small class="text-muted">Facility upkeep, cleaning & building maintenance</small>
                                        </td>
                                        <td class="text-muted small">Monthly recurring</td>
                                        <td class="text-end fw-bold text-dark font-14">₹{{ number_format($tenant->maintenance_charges, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">Other Charges</div>
                                            <small class="text-muted">High-speed Wi-Fi, water, and miscellaneous</small>
                                        </td>
                                        <td class="text-muted small">Monthly recurring</td>
                                        <td class="text-end fw-bold text-dark font-14">₹{{ number_format($tenant->other_charges, 2) }}</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td colspan="2">
                                            <div class="fw-bold text-success font-14">Total Monthly Recurring Package:</div>
                                            <small class="text-muted">Sum of Base Rent + Maintenance + Other charges</small>
                                        </td>
                                        <td class="text-end fw-bold text-success fs-5">₹{{ number_format($tenant->total_monthly_due, 2) }}</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td>
                                            <div class="fw-bold text-dark">Electricity (EB) Rate</div>
                                            <small class="text-muted">Billing unit tariff per kWh consumed</small>
                                        </td>
                                        <td class="text-muted small">Metered billing (Current reading - Previous reading)</td>
                                        <td class="text-end fw-bold text-dark">₹{{ number_format($tenant->eb_unit_rate, 2) }} <span class="text-muted font-11">/ unit</span></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td>
                                            <div class="fw-bold text-dark">Starting EB Meter Reading</div>
                                            <small class="text-muted">Initial baseline meter units registered</small>
                                        </td>
                                        <td class="text-muted small">Registered at lease commencement</td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($tenant->eb_start_reading, 2) }} <span class="text-muted font-11">kWh</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">Security Deposit Held</div>
                                            <small class="text-muted">Refundable security deposit collected on check-in</small>
                                        </td>
                                        <td class="text-muted small">One-time advance (Refundable upon vacating)</td>
                                        <td class="text-end fw-bold text-dark font-14">₹{{ number_format($tenant->security_deposit, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabs for Bills History & Payments -->
                <div class="card-section mb-3">
                    <ul class="nav nav-tabs nav-tabs-custom px-3 pt-2 bg-light border-bottom" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tabBills" role="tab">
                                <i class="fas fa-file-invoice me-1"></i> Invoices & Bills ({{ $bills->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tabPayments" role="tab">
                                <i class="fas fa-receipt me-1"></i> Payment Receipts ({{ $payments->count() }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <!-- Tab 1: Bills -->
                        <div class="tab-pane active" id="tabBills" role="tabpanel">
                            @if($bills->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Bill Number</th>
                                                <th>Billing Month</th>
                                                <th>Issue / Due Date</th>
                                                <th>EB Consumed</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Paid</th>
                                                <th class="text-end">Due</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bills as $bill)
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold text-primary">{{ $bill->bill_number }}</span>
                                                        <small class="text-muted d-block font-11">{{ ucfirst(str_replace('_', ' ', $bill->bill_type)) }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold text-dark">{{ $bill->billing_month }}</span>
                                                    </td>
                                                    <td>
                                                        <div>{{ $bill->bill_date ? $bill->bill_date->format('d M Y') : '-' }}</div>
                                                        <small class="text-muted font-11">Due: {{ $bill->due_date ? $bill->due_date->format('d M Y') : '-' }}</small>
                                                    </td>
                                                    <td>
                                                        @if($bill->eb_consumed_units > 0)
                                                            <span class="fw-semibold">{{ number_format($bill->eb_consumed_units, 2) }} units</span>
                                                            <small class="text-muted d-block font-11">₹{{ number_format($bill->eb_amount, 2) }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end fw-bold text-dark">₹{{ number_format($bill->amount, 2) }}</td>
                                                    <td class="text-end text-success fw-semibold">₹{{ number_format($bill->paid_amount, 2) }}</td>
                                                    <td class="text-end {{ $bill->due_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                                        ₹{{ number_format($bill->due_amount, 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if($bill->status === 'paid')
                                                            <span class="badge bg-success-subtle text-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> Paid</span>
                                                        @elseif($bill->status === 'partially_paid')
                                                            <span class="badge bg-warning-subtle text-warning px-2 py-1"><i class="fas fa-hourglass-half me-1"></i> Partial</span>
                                                        @elseif($bill->status === 'overdue')
                                                            <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">Unpaid</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="mdi mdi-receipt-text-outline fs-1 d-block mb-2"></i>
                                    <h6>No Invoices Generated Yet</h6>
                                    <p class="small mb-3">No bills have been issued for this resident so far.</p>
                                    <a href="{{ url('/admin/billing') }}" class="btn btn-sm button-color text-white rounded-pill px-3">
                                        <i class="fas fa-plus me-1"></i> Create First Bill
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Tab 2: Payments -->
                        <div class="tab-pane" id="tabPayments" role="tabpanel">
                            @if($payments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Receipt #</th>
                                                <th>Payment Date</th>
                                                <th>Bill Reference</th>
                                                <th>Method</th>
                                                <th>Transaction ID</th>
                                                <th class="text-end">Amount Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payments as $pmt)
                                                <tr>
                                                    <td class="fw-bold text-dark">{{ $pmt->payment_number ?? ('RCP-' . $pmt->id) }}</td>
                                                    <td>{{ $pmt->payment_date ? $pmt->payment_date->format('d M Y') : '-' }}</td>
                                                    <td>
                                                        @if($pmt->bill)
                                                            <span class="badge bg-primary-subtle text-primary">{{ $pmt->bill->bill_number }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info-subtle text-info text-uppercase font-11">{{ $pmt->payment_method ?? 'Cash' }}</span>
                                                    </td>
                                                    <td class="text-muted small">{{ $pmt->transaction_id ?? '-' }}</td>
                                                    <td class="text-end fw-bold text-success font-14">₹{{ number_format($pmt->amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="mdi mdi-cash-multiple fs-1 d-block mb-2"></i>
                                    <h6>No Payments Recorded</h6>
                                    <p class="small mb-0">Payments recorded against bills will automatically appear here.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT TENANT MODAL (AJAX) -->
    <div class="modal fade" id="editTenantModal" tabindex="-1" aria-labelledby="editTenantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white" id="editTenantModalLabel">
                        <i class="mdi mdi-account-edit me-1"></i> Edit Tenant Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTenantForm">
                    @csrf
                    <input type="hidden" name="room_number" id="edit_tenant_room_number" value="{{ $tenant->room_number }}">
                    <div class="modal-body p-4">
                        <div id="editTenantErrors" class="alert alert-danger d-none py-2 px-3 small"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tenant Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $tenant->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" value="{{ $tenant->phone }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ $tenant->email }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Emergency Contact</label>
                                <input type="tel" name="emergency_contact" class="form-control" value="{{ $tenant->emergency_contact }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Room Type <span class="text-danger">*</span></label>
                                <input type="text" name="room_type" class="form-control" value="{{ $tenant->room_type }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Commence Date <span class="text-danger">*</span></label>
                                <input type="date" name="commence_date" class="form-control" value="{{ $tenant->commence_date ? $tenant->commence_date->format('Y-m-d') : '' }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="monthly_rent" class="form-control" value="{{ $tenant->monthly_rent }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Security Deposit (₹)</label>
                                <input type="number" step="0.01" name="security_deposit" class="form-control" value="{{ $tenant->security_deposit }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">EB Rate (₹/Unit)</label>
                                <input type="number" step="0.01" name="eb_unit_rate" class="form-control" value="{{ $tenant->eb_unit_rate ?? 10.00 }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Maintenance Charges (₹)</label>
                                <input type="number" step="0.01" name="maintenance_charges" class="form-control" value="{{ $tenant->maintenance_charges }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Other Charges (₹)</label>
                                <input type="number" step="0.01" name="other_charges" class="form-control" value="{{ $tenant->other_charges }}">
                            </div>

                            <!-- Hidden room_id keep same -->
                            <input type="hidden" name="room_id" value="{{ $tenant->room_id }}">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Permanent Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ $tenant->address }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn button-color text-white" id="btnUpdateTenant">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="updateTenantSpinner"></span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                $('#editTenantForm').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btnUpdateTenant');
                    var $spinner = $('#updateTenantSpinner');
                    var $errors = $('#editTenantErrors');

                    $errors.addClass('d-none').html('');
                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    $.ajax({
                        url: "{{ url('/admin/tenants/' . $tenant->id . '/update') }}",
                        type: "POST",
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                $('#editTenantModal').modal('hide');
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Tenant updated successfully!'
                                });
                                setTimeout(function() {
                                    window.location.reload();
                                }, 800);
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            var errorMsg = 'Please verify tenant details.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $errors.html(errorMsg).removeClass('d-none');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
