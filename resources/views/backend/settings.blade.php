@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Settings Container Layout */
            .settings-container {
                display: flex;
                gap: 24px;
                margin-top: 15px;
            }

            .settings-sidebar {
                flex: 0 0 280px;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
                height: fit-content;
            }

            .settings-content {
                flex: 1;
                min-width: 0;
            }

            .settings-header {
                padding: 16px 20px;
                background: linear-gradient(135deg, #0a5858 0%, #064040 100%);
                color: #fff;
                font-weight: 700;
                font-size: 14px;
                letter-spacing: 0.5px;
                text-transform: uppercase;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .settings-tabs {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .settings-tabs li {
                border-bottom: 1px solid #f1f5f9;
            }

            .settings-tabs li:last-child {
                border-bottom: none;
            }

            .tab-link {
                display: flex;
                align-items: center;
                gap: 14px;
                padding: 14px 18px;
                color: #475569;
                text-decoration: none;
                background: transparent;
                border: none;
                width: 100%;
                text-align: left;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .tab-link:hover {
                background-color: #f8fafc;
                color: #0a5858;
            }

            .tab-link.active {
                background: #f0fdfa;
                color: #0a5858;
                border-left: 4px solid #0a5858;
                font-weight: 600;
            }

            .tab-link i {
                font-size: 18px;
                width: 24px;
                text-align: center;
                color: #64748b;
                transition: color 0.2s ease;
            }

            .tab-link.active i {
                color: #0a5858;
            }

            .tab-link .tab-info {
                display: flex;
                flex-direction: column;
                line-height: 1.3;
            }

            .tab-link .tab-title {
                font-size: 14px;
                font-weight: 600;
            }

            .tab-link .tab-subtitle {
                font-size: 11px;
                color: #94a3b8;
                font-weight: 400;
            }

            .tab-link.active .tab-subtitle {
                color: #0d9488;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
                animation: fadeIn 0.25s ease;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(4px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Integration Sub-Pills */
            .integration-nav {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-bottom: 20px;
                background: #fff;
                padding: 8px;
                border-radius: 10px;
                border: 1px solid #e2e8f0;
            }

            .integration-pill {
                flex: 1;
                min-width: 140px;
                padding: 10px 14px;
                border: 1px solid #e2e8f0;
                background: #f8fafc;
                border-radius: 8px;
                color: #475569;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s ease;
            }

            .integration-pill:hover {
                background: #e2e8f0;
                color: #0f172a;
            }

            .integration-pill.active {
                background: #0a5858;
                color: #fff;
                border-color: #0a5858;
                box-shadow: 0 2px 6px rgba(10, 88, 88, 0.25);
            }

            .integration-card {
                display: none;
            }

            .integration-card.active {
                display: block;
            }

            .form-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.03);
                margin-bottom: 20px;
                overflow: hidden;
            }

            .form-card-header {
                padding: 14px 20px;
                background: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .form-card-title {
                font-size: 15px;
                font-weight: 700;
                color: #1e293b;
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 0;
            }

            .form-card-body {
                padding: 20px;
            }

            .form-card-footer {
                padding: 14px 20px;
                background: #f8fafc;
                border-top: 1px solid #e2e8f0;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }

            .form-label {
                font-size: 13px;
                font-weight: 600;
                color: #334155;
                margin-bottom: 6px;
            }

            .form-text {
                font-size: 11px;
                color: #64748b;
                margin-top: 4px;
            }

            .input-group-text {
                background: #f1f5f9;
                border-color: #cbd5e1;
                color: #475569;
                font-weight: 600;
            }

            .toggle-password-btn {
                cursor: pointer;
            }

            .qr-preview-box {
                max-width: 160px;
                max-height: 160px;
                border: 2px dashed #cbd5e1;
                border-radius: 8px;
                padding: 6px;
                display: inline-block;
                background: #fff;
            }

            .qr-preview-box img {
                width: 100%;
                height: auto;
                object-fit: contain;
                border-radius: 4px;
            }

            @media (max-width: 860px) {
                .settings-container {
                    flex-direction: column;
                }
                .settings-sidebar {
                    width: 100%;
                    flex: none;
                }
            }
        </style>
    @endpush

    <!-- Page Header -->
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h4 class="page-title-main page login-label mb-1">
                <i class="fas fa-sliders-h me-2 text-teal"></i>System Settings
            </h4>
            <p class="text-muted font-12 mb-0">Manage residence profile, billing configurations, third-party integrations & policies</p>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <span class="badge bg-light text-dark border px-3 py-2 font-12">
                <i class="fas fa-shield-alt text-success me-1"></i> SRM Residence v2.0
            </span>
        </div>
    </div>

    <div class="settings-container">
        <!-- Left Sidebar Navigation -->
        <div class="settings-sidebar">
            <div class="settings-header">
                <i class="fas fa-cog"></i> Configuration Menu
            </div>
            <ul class="settings-tabs">
                <li>
                    <button class="tab-link active" data-tab="general">
                        <i class="fas fa-building"></i>
                        <div class="tab-info">
                            <span class="tab-title">General Settings</span>
                            <span class="tab-subtitle">Property & Contact Info</span>
                        </div>
                    </button>
                </li>
                <li>
                    <button class="tab-link" data-tab="billing">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <div class="tab-info">
                            <span class="tab-title">Billing & Rent</span>
                            <span class="tab-subtitle">EB rates, deposits & dues</span>
                        </div>
                    </button>
                </li>
                <li>
                    <button class="tab-link" data-tab="integrations">
                        <i class="fas fa-plug text-primary"></i>
                        <div class="tab-info">
                            <span class="tab-title">Integrations <span class="badge bg-primary text-white font-10 ms-1">Setup</span></span>
                            <span class="tab-subtitle">Razorpay, UPI, SMS & Mail</span>
                        </div>
                    </button>
                </li>
                <li>
                    <button class="tab-link" data-tab="notifications">
                        <i class="fas fa-bell"></i>
                        <div class="tab-info">
                            <span class="tab-title">Notifications</span>
                            <span class="tab-subtitle">Alert templates & triggers</span>
                        </div>
                    </button>
                </li>
                <li>
                    <button class="tab-link" data-tab="policies">
                        <i class="fas fa-file-contract"></i>
                        <div class="tab-info">
                            <span class="tab-title">Rules & Policies</span>
                            <span class="tab-subtitle">Agreements & refunds</span>
                        </div>
                    </button>
                </li>
                <li>
                    <button class="tab-link" data-tab="banners">
                        <i class="fas fa-images"></i>
                        <div class="tab-info">
                            <span class="tab-title">Banners & Media</span>
                            <span class="tab-subtitle">App promotional banners</span>
                        </div>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Right Content Area -->
        <div class="settings-content">

            <!-- ========================================== -->
            <!-- 1. GENERAL SETTINGS TAB                   -->
            <!-- ========================================== -->
            <div class="tab-content active" id="general-tab">
                <form class="settings-form" action="{{ route('settings.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="general">

                    <div class="form-card">
                        <div class="form-card-header">
                            <h5 class="form-card-title">
                                <i class="fas fa-hotel text-primary"></i> Residence Identity
                            </h5>
                            <span class="badge bg-light text-muted border font-11">Basic Details</span>
                        </div>
                        <div class="form-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Residence / Property Name <span class="text-danger">*</span></label>
                                    <input type="text" name="residence_name" class="form-control" 
                                           value="{{ $settings['residence_name'] ?? 'SRM Residence' }}" required>
                                    <div class="form-text">Displayed on invoices, rent receipts, and reports</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tagline / Subtitle</label>
                                    <input type="text" name="residence_tagline" class="form-control" 
                                           value="{{ $settings['residence_tagline'] ?? 'Luxury Co-Living & Executive PG' }}">
                                    <div class="form-text">e.g. Co-living, Executive PG, Student Hostel</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Property Manager / Warden</label>
                                    <input type="text" name="contact_person" class="form-control" 
                                           value="{{ $settings['contact_person'] ?? 'Property Manager' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Designation / Role</label>
                                    <input type="text" name="contact_designation" class="form-control" 
                                           value="{{ $settings['contact_designation'] ?? 'Resident Warden' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-card-header">
                            <h5 class="form-card-title">
                                <i class="fas fa-address-book text-success"></i> Official Contact Information
                            </h5>
                        </div>
                        <div class="form-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Primary Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" name="contact_phone" class="form-control" 
                                               value="{{ $settings['contact_phone'] ?? '+91 98765 43210' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">WhatsApp Contact Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                                        <input type="text" name="contact_whatsapp" class="form-control" 
                                               value="{{ $settings['contact_whatsapp'] ?? '+91 98765 43210' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Official Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="contact_email" class="form-control" 
                                               value="{{ $settings['contact_email'] ?? 'contact@srmresidence.com' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">24/7 Emergency Support Contact</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone-alt text-danger"></i></span>
                                        <input type="text" name="emergency_phone" class="form-control" 
                                               value="{{ $settings['emergency_phone'] ?? '+91 98765 00000' }}">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Physical Address</label>
                                    <textarea name="contact_address" class="form-control" rows="2">{{ $settings['contact_address'] ?? 'SRM Residence, Main Road, Potheri, Kattankulathur, Tamil Nadu 603203' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gate Closing Time</label>
                                    <input type="time" name="gate_closing_time" class="form-control" 
                                           value="{{ $settings['gate_closing_time'] ?? '22:30' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Office / Helpdesk Hours</label>
                                    <input type="text" name="office_hours" class="form-control" 
                                           value="{{ $settings['office_hours'] ?? '09:00 AM - 08:00 PM' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-card-footer">
                            <button type="submit" class="btn button-color text-white btn-save-settings">
                                <span class="spinner-border spinner-border-sm d-none me-1"></span>
                                <i class="fas fa-save me-1"></i> Save General Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- 2. BILLING & RENT DEFAULTS TAB            -->
            <!-- ========================================== -->
            <div class="tab-content" id="billing-tab">
                <form class="settings-form" action="{{ route('settings.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="billing">

                    <div class="form-card">
                        <div class="form-card-header">
                            <h5 class="form-card-title">
                                <i class="fas fa-bolt text-warning"></i> Default Room & Utility Rates
                            </h5>
                            <span class="badge bg-light text-muted border font-11">Auto-applied to new units</span>
                        </div>
                        <div class="form-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Default EB Rate (₹/Unit)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="default_eb_rate" class="form-control" 
                                               value="{{ $settings['default_eb_rate'] ?? '10.00' }}" min="0">
                                    </div>
                                    <div class="form-text">Per unit electricity charge</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Default Maintenance (₹/month)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="default_maintenance" class="form-control" 
                                               value="{{ $settings['default_maintenance'] ?? '0.00' }}" min="0">
                                    </div>
                                    <div class="form-text">Upkeep & housekeeping</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Default Other Charges (₹/month)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="default_other_charges" class="form-control" 
                                               value="{{ $settings['default_other_charges'] ?? '0.00' }}" min="0">
                                    </div>
                                    <div class="form-text">Wi-Fi, water, or miscellaneous</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Default Security Deposit (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="default_security_deposit" class="form-control" 
                                               value="{{ $settings['default_security_deposit'] ?? '10000.00' }}" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Default Rent Structure</label>
                                    <select name="default_rent_type" class="form-select">
                                        <option value="custom" {{ ($settings['default_rent_type'] ?? 'custom') === 'custom' ? 'selected' : '' }}>Custom Charges (Rent + EB + Maintenance)</option>
                                        <option value="fixed" {{ ($settings['default_rent_type'] ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Rent (Zero Maintenance & Other charges)</option>
                                    </select>
                                    <div class="form-text">Pre-selected option when creating tenants</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-card-header">
                            <h5 class="form-card-title">
                                <i class="fas fa-calendar-alt text-info"></i> Invoicing Cycle & Late Dues
                            </h5>
                        </div>
                        <div class="form-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bill Generation Day</label>
                                    <div class="input-group">
                                        <input type="number" name="bill_generation_day" class="form-control" 
                                               value="{{ $settings['bill_generation_day'] ?? '1' }}" min="1" max="28">
                                        <span class="input-group-text">of every month</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Rent Due Day</label>
                                    <div class="input-group">
                                        <input type="number" name="bill_due_day" class="form-control" 
                                               value="{{ $settings['bill_due_day'] ?? '5' }}" min="1" max="28">
                                        <span class="input-group-text">of every month</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Grace Period (Days)</label>
                                    <div class="input-group">
                                        <input type="number" name="grace_period_days" class="form-control" 
                                               value="{{ $settings['grace_period_days'] ?? '3' }}" min="0">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Late Fee Penalty Type</label>
                                    <select name="late_fee_type" class="form-select">
                                        <option value="fixed" {{ ($settings['late_fee_type'] ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Amount per Overdue Bill</option>
                                        <option value="per_day" {{ ($settings['late_fee_type'] ?? '') === 'per_day' ? 'selected' : '' }}>Daily Late Fee (Per Day Overdue)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Late Fee Amount (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="late_fee_amount" class="form-control" 
                                               value="{{ $settings['late_fee_amount'] ?? '100.00' }}" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Currency Symbol</label>
                                    <input type="text" name="currency_symbol" class="form-control" 
                                           value="{{ $settings['currency_symbol'] ?? '₹' }}" style="max-width: 100px;">
                                </div>
                            </div>
                        </div>
                        <div class="form-card-footer">
                            <button type="submit" class="btn button-color text-white btn-save-settings">
                                <span class="spinner-border spinner-border-sm d-none me-1"></span>
                                <i class="fas fa-save me-1"></i> Save Billing Defaults
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- 3. INTEGRATIONS TAB (SPLIT SECTIONS)      -->
            <!-- ========================================== -->
            <div class="tab-content" id="integrations-tab">

                <!-- Integrations Sub-Navigation Pills -->
                <div class="integration-nav">
                    <button type="button" class="integration-pill active" data-target="#integ-razorpay">
                        <i class="fas fa-credit-card text-primary"></i> Razorpay Gateway
                    </button>
                    <button type="button" class="integration-pill" data-target="#integ-upi">
                        <i class="fas fa-qrcode text-success"></i> Direct UPI & QR
                    </button>
                    <button type="button" class="integration-pill" data-target="#integ-messaging">
                        <i class="fab fa-whatsapp text-teal"></i> WhatsApp & SMS
                    </button>
                    <button type="button" class="integration-pill" data-target="#integ-smtp">
                        <i class="fas fa-envelope text-info"></i> SMTP / Email
                    </button>
                </div>

                <form class="settings-form" action="{{ route('settings.save') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="integrations">

                    <!-- Section A: Razorpay -->
                    <div class="integration-card active" id="integ-razorpay">
                        <div class="form-card">
                            <div class="form-card-header">
                                <h5 class="form-card-title">
                                    <i class="fas fa-credit-card text-primary"></i> Razorpay Payment Gateway
                                </h5>
                                <div>
                                    @if(!empty($settings['razorpay_enabled']) && $settings['razorpay_enabled'] == '1')
                                        <span class="badge bg-success font-11"><i class="fas fa-check-circle me-1"></i> Enabled</span>
                                    @else
                                        <span class="badge bg-secondary font-11">Disabled</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-card-body">
                                <div class="alert alert-info py-2 px-3 font-12 mb-3">
                                    <i class="fas fa-info-circle me-1"></i> Razorpay allows residents to pay rent, electricity & maintenance bills online via UPI, Credit/Debit Cards, NetBanking, and Wallets.
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" name="razorpay_enabled" value="1" id="razorpay_enabled"
                                                   {{ !empty($settings['razorpay_enabled']) && $settings['razorpay_enabled'] == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-6 fw-bold" for="razorpay_enabled">Enable Online Payments via Razorpay</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Gateway Environment</label>
                                        <div class="d-flex gap-3 mt-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="razorpay_mode" id="rzp_mode_test" value="test"
                                                       {{ ($settings['razorpay_mode'] ?? 'test') === 'test' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="rzp_mode_test">Test / Sandbox Mode</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="razorpay_mode" id="rzp_mode_live" value="live"
                                                       {{ ($settings['razorpay_mode'] ?? '') === 'live' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold text-danger" for="rzp_mode_live">Live Production Mode</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Auto-Reconcile Invoices</label>
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="razorpay_auto_reconcile" value="1" id="razorpay_auto_reconcile"
                                                   {{ !empty($settings['razorpay_auto_reconcile']) && $settings['razorpay_auto_reconcile'] == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label font-12 text-muted" for="razorpay_auto_reconcile">Mark monthly bills as "Paid" automatically upon successful transaction</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Razorpay Key ID</label>
                                        <input type="text" name="razorpay_key_id" class="form-control font-monospace" 
                                               placeholder="rzp_test_..." value="{{ $settings['razorpay_key_id'] ?? '' }}">
                                        <div class="form-text">Obtain from Razorpay Dashboard > Settings > API Keys</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Razorpay Key Secret</label>
                                        <div class="input-group">
                                            <input type="password" name="razorpay_key_secret" id="rzp_secret" class="form-control font-monospace" 
                                                   placeholder="••••••••••••••••" value="{{ $settings['razorpay_key_secret'] ?? '' }}">
                                            <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#rzp_secret">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Keep your API secret strictly confidential</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Webhook Secret</label>
                                        <input type="password" name="razorpay_webhook_secret" id="rzp_webhook" class="form-control font-monospace" 
                                               placeholder="Webhook secret key" value="{{ $settings['razorpay_webhook_secret'] ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Webhook Callback URL</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control font-monospace bg-light" readonly value="{{ url('/api/razorpay/webhook') }}" id="rzpWebhookUrl">
                                            <button class="btn btn-outline-primary btn-copy-text" type="button" data-copy="#rzpWebhookUrl">
                                                <i class="fas fa-copy me-1"></i> Copy
                                            </button>
                                        </div>
                                        <div class="form-text">Add this URL in Razorpay Dashboard > Webhooks</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section B: Direct UPI & QR -->
                    <div class="integration-card" id="integ-upi">
                        <div class="form-card">
                            <div class="form-card-header">
                                <h5 class="form-card-title">
                                    <i class="fas fa-qrcode text-success"></i> Direct UPI & Bank Transfer Settings
                                </h5>
                                <div>
                                    @if(!empty($settings['upi_enabled']) && $settings['upi_enabled'] == '1')
                                        <span class="badge bg-success font-11"><i class="fas fa-check-circle me-1"></i> Active</span>
                                    @else
                                        <span class="badge bg-secondary font-11">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-card-body">
                                <div class="alert alert-light border py-2 px-3 font-12 mb-3">
                                    <i class="fas fa-lightbulb text-warning me-1"></i> The UPI ID and QR code configured here will be dynamically printed on monthly PDF invoices and shown to residents for zero-fee bank transfers.
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" name="upi_enabled" value="1" id="upi_enabled"
                                                   {{ !empty($settings['upi_enabled']) && $settings['upi_enabled'] == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-6 fw-bold" for="upi_enabled">Display UPI & QR Code on Rent Invoices</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Primary UPI ID / VPA <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            <input type="text" name="upi_id" class="form-control fw-bold text-primary" 
                                                   placeholder="e.g. srmresidence@okhdfcbank" value="{{ $settings['upi_id'] ?? 'srmresidence@okhdfcbank' }}">
                                        </div>
                                        <div class="form-text">e.g. your_name@okaxis, 9876543210@upi</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Payee / Business Display Name</label>
                                        <input type="text" name="upi_merchant_name" class="form-control" 
                                               placeholder="SRM RESIDENCE" value="{{ $settings['upi_merchant_name'] ?? 'SRM RESIDENCE' }}">
                                        <div class="form-text">Name shown on Google Pay, PhonePe, Paytm scanner</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Upload Custom UPI QR Code Image</label>
                                        <input type="file" name="upi_qr_code" class="form-control" accept="image/*" id="upi_qr_input">
                                        <div class="form-text">Supported: PNG, JPG, WebP. Recommended 500x500 px.</div>

                                        @if(!empty($settings['upi_qr_code']))
                                            <div class="mt-3">
                                                <div class="qr-preview-box">
                                                    <img src="{{ asset('storage/' . $settings['upi_qr_code']) }}" alt="UPI QR Code">
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="remove_upi_qr_code" value="1" id="remove_qr">
                                                    <label class="form-check-label font-12 text-danger" for="remove_qr">Remove current QR Code</label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded border">
                                            <h6 class="fw-bold font-13 mb-2 text-dark"><i class="fas fa-university me-1 text-primary"></i> Bank Account Details (NEFT/IMPS)</h6>
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <label class="form-label font-11 mb-1">Bank Name</label>
                                                    <input type="text" name="bank_name" class="form-control form-control-sm" 
                                                           placeholder="e.g. HDFC Bank" value="{{ $settings['bank_name'] ?? 'HDFC Bank' }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label font-11 mb-1">Account Holder</label>
                                                    <input type="text" name="bank_account_name" class="form-control form-control-sm" 
                                                           placeholder="Account Name" value="{{ $settings['bank_account_name'] ?? 'SRM RESIDENCE' }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label font-11 mb-1">Account Number</label>
                                                    <input type="text" name="bank_account_number" class="form-control form-control-sm" 
                                                           placeholder="Account Number" value="{{ $settings['bank_account_number'] ?? '' }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label font-11 mb-1">IFSC Code</label>
                                                    <input type="text" name="bank_ifsc_code" class="form-control form-control-sm font-monospace text-uppercase" 
                                                           placeholder="HDFC0001234" value="{{ $settings['bank_ifsc_code'] ?? '' }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label font-11 mb-1">Branch Name</label>
                                                    <input type="text" name="bank_branch" class="form-control form-control-sm" 
                                                           placeholder="Branch" value="{{ $settings['bank_branch'] ?? 'Potheri Branch' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section C: WhatsApp & SMS Messaging -->
                    <div class="integration-card" id="integ-messaging">
                        <div class="form-card">
                            <div class="form-card-header">
                                <h5 class="form-card-title">
                                    <i class="fab fa-whatsapp text-teal"></i> SMS & WhatsApp Messaging Gateway
                                </h5>
                                <div>
                                    @if(!empty($settings['messaging_enabled']) && $settings['messaging_enabled'] == '1')
                                        <span class="badge bg-success font-11"><i class="fas fa-check-circle me-1"></i> Enabled</span>
                                    @else
                                        <span class="badge bg-secondary font-11">Disabled</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" name="messaging_enabled" value="1" id="messaging_enabled"
                                                   {{ !empty($settings['messaging_enabled']) && $settings['messaging_enabled'] == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-6 fw-bold" for="messaging_enabled">Enable Automated SMS / WhatsApp Alerts</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Messaging Provider</label>
                                        <select name="messaging_provider" class="form-select">
                                            <option value="whatsapp_cloud" {{ ($settings['messaging_provider'] ?? 'whatsapp_cloud') === 'whatsapp_cloud' ? 'selected' : '' }}>Meta WhatsApp Cloud API (Official)</option>
                                            <option value="msg91" {{ ($settings['messaging_provider'] ?? '') === 'msg91' ? 'selected' : '' }}>MSG91 (India SMS & WhatsApp)</option>
                                            <option value="twilio" {{ ($settings['messaging_provider'] ?? '') === 'twilio' ? 'selected' : '' }}>Twilio (Global SMS & WhatsApp)</option>
                                            <option value="custom" {{ ($settings['messaging_provider'] ?? '') === 'custom' ? 'selected' : '' }}>Custom Webhook / HTTP API</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Sender ID / From Number</label>
                                        <input type="text" name="messaging_sender_id" class="form-control" 
                                               placeholder="SRMRES or +919876543210" value="{{ $settings['messaging_sender_id'] ?? 'SRMRES' }}">
                                        <div class="form-text">Registered SMS Header (DLT) or WhatsApp sender phone</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">API Key / Access Token</label>
                                        <div class="input-group">
                                            <input type="password" name="messaging_api_key" id="msg_api_key" class="form-control font-monospace" 
                                                   placeholder="API Token or Secret Key" value="{{ $settings['messaging_api_key'] ?? '' }}">
                                            <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#msg_api_key">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">WhatsApp Phone Number ID / Account ID</label>
                                        <input type="text" name="whatsapp_phone_number_id" class="form-control font-monospace" 
                                               placeholder="e.g. 1029384756..." value="{{ $settings['whatsapp_phone_number_id'] ?? '' }}">
                                    </div>

                                    <div class="col-12 mt-3">
                                        <h6 class="fw-bold font-13 text-dark mb-2">Automated Notification Triggers:</h6>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="notify_bill_generated" value="1" id="trig_bill"
                                                           {{ !empty($settings['notify_bill_generated']) && $settings['notify_bill_generated'] == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label font-13" for="trig_bill">
                                                        <i class="fas fa-file-invoice text-primary me-1"></i> Send alert when monthly rent bill is generated
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="notify_payment_received" value="1" id="trig_pay"
                                                           {{ !empty($settings['notify_payment_received']) && $settings['notify_payment_received'] == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label font-13" for="trig_pay">
                                                        <i class="fas fa-receipt text-success me-1"></i> Send instant receipt confirmation when payment is recorded
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="notify_due_reminder" value="1" id="trig_due"
                                                           {{ !empty($settings['notify_due_reminder']) && $settings['notify_due_reminder'] == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label font-13" for="trig_due">
                                                        <i class="fas fa-clock text-warning me-1"></i> Send gentle reminder 3 days before rent due date
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="notify_overdue_alert" value="1" id="trig_overdue"
                                                           {{ !empty($settings['notify_overdue_alert']) && $settings['notify_overdue_alert'] == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label font-13" for="trig_overdue">
                                                        <i class="fas fa-exclamation-triangle text-danger me-1"></i> Send urgent notice if rent bill becomes overdue
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section D: SMTP / Email -->
                    <div class="integration-card" id="integ-smtp">
                        <div class="form-card">
                            <div class="form-card-header">
                                <h5 class="form-card-title">
                                    <i class="fas fa-envelope text-info"></i> Email / SMTP Server Configuration
                                </h5>
                                <span class="badge bg-light text-muted border font-11">Transactional Emails</span>
                            </div>
                            <div class="form-card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Mail Driver</label>
                                        <select name="mail_mailer" class="form-select">
                                            <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                            <option value="log" {{ ($settings['mail_mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log (Testing only)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" name="mail_host" class="form-control font-monospace" 
                                               placeholder="smtp.gmail.com" value="{{ $settings['mail_host'] ?? 'smtp.gmail.com' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">SMTP Port</label>
                                        <input type="number" name="mail_port" class="form-control font-monospace" 
                                               placeholder="587" value="{{ $settings['mail_port'] ?? '587' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Username / Email</label>
                                        <input type="text" name="mail_username" class="form-control" 
                                               placeholder="your-email@gmail.com" value="{{ $settings['mail_username'] ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Password / App Password</label>
                                        <div class="input-group">
                                            <input type="password" name="mail_password" id="smtp_pass" class="form-control font-monospace" 
                                                   placeholder="••••••••••••••••" value="{{ $settings['mail_password'] ?? '' }}">
                                            <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#smtp_pass">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Encryption Type</label>
                                        <select name="mail_encryption" class="form-select">
                                            <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Recommended: 587)</option>
                                            <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                            <option value="none" {{ ($settings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Sender Email Address (From)</label>
                                        <input type="email" name="mail_from_address" class="form-control" 
                                               placeholder="notifications@srmresidence.com" value="{{ $settings['mail_from_address'] ?? 'notifications@srmresidence.com' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Sender Name (From Name)</label>
                                        <input type="text" name="mail_from_name" class="form-control" 
                                               placeholder="SRM Residence" value="{{ $settings['mail_from_name'] ?? 'SRM Residence' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shared Integrations Save Button Footer -->
                    <div class="form-card mb-0">
                        <div class="form-card-footer bg-light">
                            <button type="submit" class="btn button-color text-white btn-save-settings px-4">
                                <span class="spinner-border spinner-border-sm d-none me-1"></span>
                                <i class="fas fa-save me-1"></i> Save Integrations Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- 4. NOTIFICATIONS & ALERTS TAB             -->
            <!-- ========================================== -->
            <div class="tab-content" id="notifications-tab">
                <form class="settings-form" action="{{ route('settings.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="notifications">

                    <div class="form-card">
                        <div class="form-card-header">
                            <h5 class="form-card-title">
                                <i class="fas fa-bell text-primary"></i> Notification Templates & Alerts
                            </h5>
                        </div>
                        <div class="form-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Admin Alert Email</label>
                                    <input type="email" name="admin_alert_email" class="form-control" 
                                           value="{{ $settings['admin_alert_email'] ?? 'admin@srmresidence.com' }}">
                                    <div class="form-text">Receives daily summary, new resident onboarding & checkout alerts</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Send Due Date Reminder</label>
                                    <div class="input-group">
                                        <input type="number" name="reminder_days_before_due" class="form-control" 
                                               value="{{ $settings['reminder_days_before_due'] ?? '3' }}" min="1" max="10">
                                        <span class="input-group-text">days before rent due date</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Monthly Rent Bill Generated Message Template</label>
                                    <textarea name="bill_sms_template" class="form-control font-monospace font-13" rows="3">{{ $settings['bill_sms_template'] ?? 'Dear {resident_name}, your room rent bill for {month} of Rs.{amount} has been generated. Due date: {due_date}. - SRM Residence' }}</textarea>
                                    <div class="form-text">Available tags: <code>{resident_name}</code>, <code>{month}</code>, <code>{amount}</code>, <code>{due_date}</code></div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Payment Receipt Confirmation Message Template</label>
                                    <textarea name="payment_receipt_template" class="form-control font-monospace font-13" rows="3">{{ $settings['payment_receipt_template'] ?? 'Dear {resident_name}, we received your payment of Rs.{amount} on {payment_date}. Receipt #{receipt_no}. Thank you! - SRM Residence' }}</textarea>
                                    <div class="form-text">Available tags: <code>{resident_name}</code>, <code>{amount}</code>, <code>{payment_date}</code>, <code>{receipt_no}</code></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-card-footer">
                            <button type="submit" class="btn button-color text-white btn-save-settings">
                                <span class="spinner-border spinner-border-sm d-none me-1"></span>
                                <i class="fas fa-save me-1"></i> Save Notification Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- 5. RULES & POLICIES TAB                   -->
            <!-- ========================================== -->
            <div class="tab-content" id="policies-tab">
                <form class="settings-form" action="{{ route('settings.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="policies">

                    <div class="form-card">
                        <div class="form-card-header">
                            <h5 class="form-card-title">
                                <i class="fas fa-shield-alt text-teal"></i> Hostel & Residence House Rules
                            </h5>
                        </div>
                        <div class="form-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Code of Conduct & House Rules</label>
                                    <textarea name="house_rules" class="form-control" rows="5" placeholder="Enter house rules...">{{ $settings['house_rules'] ?? '' }}</textarea>
                                    <div class="form-text">Printed on admission agreements and tenant welcome packets</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Rental Terms & Conditions <span class="text-danger">*</span></label>
                                    <textarea name="terms_and_condition" id="terms" class="form-control" rows="5" required>{{ $settings['terms_and_condition'] ?? '' }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Mandatory Notice Period (Days)</label>
                                    <div class="input-group">
                                        <input type="number" name="notice_period_days" class="form-control" 
                                               value="{{ $settings['notice_period_days'] ?? '30' }}" min="0">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">Prior notice required before vacating</div>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Notice Period Policy</label>
                                    <textarea name="notice_period_policy" class="form-control" rows="2">{{ $settings['notice_period_policy'] ?? 'Residents must provide at least 30 days prior written notice before vacating their room. Vacating without notice may forfeit part of the security deposit.' }}</textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Security Deposit Refund Policy</label>
                                    <textarea name="refund_policy" id="refund_policy" class="form-control" rows="4">{{ $settings['refund_policy'] ?? 'Security deposit will be processed within 7 business days of checkout after room inspection and electricity meter reconciliation.' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-card-footer">
                            <button type="submit" class="btn button-color text-white btn-save-settings">
                                <span class="spinner-border spinner-border-sm d-none me-1"></span>
                                <i class="fas fa-save me-1"></i> Save Rules & Policies
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- 6. BANNERS & MEDIA TAB (PRESERVED FULLY)  -->
            <!-- ========================================== -->
            <div class="tab-content" id="banners-tab">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-shadow">
                            <div class="card-header theme-text-color">
                                <div class="row justify-content-between align-items-center">
                                    <div class="col-md-6 theme-text-color fw-bold">
                                        <i class="fas fa-images me-1"></i> All Promotional Banners
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <a href="javascript:void(0)" type="button"
                                            class="btn add-btn waves-effect waves-light button-color text-white add-banner">
                                            <i class="mdi mdi-plus-circle me-1"></i> Create Banner
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-border">
                                    <table class="table table-striped data-table w-100">
                                        <thead class="button-color text-white">
                                            <tr>
                                                <th>Date</th>
                                                <th>Title</th>
                                                <th>Image</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Banner Modals (Preserved) -->
    <div class="modal fade" id="add-banner" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white">Add Banner</h5>
                    <button type="button" class="btn-close custom-modal-close modal-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <form action="{{ url('/admin/banner/') }}" method="post" class="form-horizontal"
                        enctype="multipart/form-data" id="add-banner-form">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Name</label>
                                <input type="text" name="name"
                                    class="form-control form-field-style input-form"
                                    placeholder="Enter Name" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Image</label>
                                <input type="file" name="image"
                                    class="form-control form-field-style input-form"
                                    placeholder="Enter image" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Status</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="active" name="status" class="form-check-input"
                                                value="1" checked>
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="inactive" name="status" class="form-check-input"
                                                value="0">
                                            <label class="form-check-label" for="inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="submit_btn text-end">
                            <button type="submit" class="btn waves-effect waves-light button-color text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Banner Modal -->
    <div class="modal fade" id="edit-banner" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header button-color text-white">
                    <h5 class="modal-title text-white">Edit Banner</h5>
                    <button type="button" class="btn-close custom-modal-close modal-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <form method="post" class="form-horizontal edit-banner-form" enctype="multipart/form-data"
                        id="edit-banner-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Name</label>
                                <input type="text" name="name"
                                    class="form-control banner-name"
                                    placeholder="Enter Name" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Image</label>
                                <input type="file" name="image" class="form-control banner-image">
                                <a href="javascript:void(0)" class="image-view-link mt-2" style="display: none;"></a>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Status</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="banner-active" name="status"
                                                class="form-check-input" value="1">
                                            <label class="form-check-label" for="banner-active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="banner-inactive" name="status"
                                                class="form-check-input" value="0">
                                            <label class="form-check-label" for="banner-inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="banner_id" class="banner-id" value="">
                        <div class="submit_btn text-end">
                            <button type="submit" class="btn button-color text-white">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

        <script>
            $(document).ready(function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // Tab Management
                const ACTIVE_TAB_KEY = 'srm_settings_active_tab';
                const DEFAULT_TAB = 'general';

                function getActiveTab() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const urlTab = urlParams.get('tab');
                    if (urlTab && $(`#${urlTab}-tab`).length) {
                        return urlTab;
                    }
                    try {
                        const stored = localStorage.getItem(ACTIVE_TAB_KEY);
                        if (stored && $(`#${stored}-tab`).length) return stored;
                    } catch(e) {}
                    return DEFAULT_TAB;
                }

                function activateTab(tabId) {
                    $('.tab-link').removeClass('active');
                    $('.tab-content').removeClass('active');

                    $(`.tab-link[data-tab="${tabId}"]`).addClass('active');
                    $(`#${tabId}-tab`).addClass('active');

                    try {
                        localStorage.setItem(ACTIVE_TAB_KEY, tabId);
                    } catch(e) {}

                    if (tabId === 'banners') {
                        if (!$.fn.DataTable.isDataTable('.data-table')) {
                            initializeBannerTable();
                        }
                    }
                }

                $(document).on('click', '.tab-link', function(e) {
                    e.preventDefault();
                    const tabId = $(this).data('tab');
                    activateTab(tabId);
                });

                // Sub-pills for Integrations
                $(document).on('click', '.integration-pill', function(e) {
                    e.preventDefault();
                    $('.integration-pill').removeClass('active');
                    $(this).addClass('active');

                    const target = $(this).data('target');
                    $('.integration-card').removeClass('active');
                    $(target).addClass('active');
                });

                // Toggle Password / API Keys visibility
                $(document).on('click', '.toggle-password-btn', function() {
                    const target = $(this).data('target');
                    const $input = $(target);
                    const $icon = $(this).find('i');
                    if ($input.attr('type') === 'password') {
                        $input.attr('type', 'text');
                        $icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    } else {
                        $input.attr('type', 'password');
                        $icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    }
                });

                // Copy Text Helper
                $(document).on('click', '.btn-copy-text', function() {
                    const selector = $(this).data('copy');
                    const text = $(selector).val();
                    navigator.clipboard.writeText(text).then(function() {
                        Toast.fire({ icon: 'success', title: 'Copied to clipboard!' });
                    });
                });

                // AJAX Form Submission for Settings
                $('.settings-form').on('submit', function(e) {
                    e.preventDefault();
                    const $form = $(this);
                    const $btn = $form.find('.btn-save-settings');
                    const $spinner = $btn.find('.spinner-border');

                    $btn.prop('disabled', true);
                    $spinner.removeClass('d-none');

                    const formData = new FormData(this);

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');

                            if (response.success) {
                                Toast.fire({
                                    icon: 'success',
                                    title: response.message || 'Settings updated successfully!'
                                });
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            let errorMsg = 'Failed to save settings. Please check fields.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        }
                    });
                });

                // Initialize Banner DataTable
                var bannerTable = null;
                function initializeBannerTable() {
                    bannerTable = $('.data-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ url('/admin/banner') }}",
                        order: [[0, 'desc']],
                        columnDefs: [{ 'visible': false, 'targets': [0] }],
                        columns: [
                            { data: 'date', name: 'date' },
                            { data: 'title', name: 'title' },
                            { data: 'image', name: 'image', orderable: false },
                            { data: 'status', name: 'status', orderable: false },
                            { data: 'action', name: 'action', orderable: false, searchable: false }
                        ]
                    });
                }

                // Add Banner
                $(document).on('click', '.add-banner', function() {
                    $('#add-banner-form')[0].reset();
                    $('#add-banner').modal('show');
                });

                // Edit Banner
                $(document).on('click', '.edit-banner', function() {
                    const id = $(this).data('id');
                    $.ajax({
                        type: 'GET',
                        url: "{{ url('admin/banner') }}/" + id + "/edit",
                        success: function(data) {
                            $('.banner-id').val(data.id);
                            $('.banner-name').val(data.name);
                            if (data.status == 1) {
                                $('#banner-active').prop('checked', true);
                            } else {
                                $('#banner-inactive').prop('checked', true);
                            }
                            if (data.image) {
                                let imageUrl = "{{ url('storage/banner') }}/" + data.image;
                                $('.image-view-link').attr('href', imageUrl).text(data.image).show();
                            } else {
                                $('.image-view-link').hide();
                            }
                            $('.edit-banner-form').attr('action', "{{ url('admin/banner') }}/" + data.encrypted_id);
                            $('#edit-banner').modal('show');
                        },
                        error: function() {
                            Toast.fire({ icon: 'error', title: 'Failed to load banner details' });
                        }
                    });
                });

                // Delete Banner
                $(document).on('click', '.delete', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    Swal.fire({
                        title: "Are you sure?",
                        text: "This banner will be permanently deleted!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#0a5858",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/banner/') }}/" + id,
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                    "id": id
                                },
                                success: function(data) {
                                    if (data.status == 1) {
                                        if (bannerTable) bannerTable.ajax.reload();
                                        Toast.fire({ icon: 'success', title: data.message || 'Banner deleted successfully.' });
                                    } else {
                                        Swal.fire({ title: "Error!", text: data.message || "Failed to delete banner", icon: "error" });
                                    }
                                }
                            });
                        }
                    });
                });

                // Banner Status Toggle
                $(document).on('change', '.banner-status-toggle', function() {
                    const $this = $(this);
                    const id = $this.data('id');
                    const status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/banner/status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        },
                        success: function(response) {
                            if (!response.success) {
                                $this.prop('checked', !$this.prop('checked'));
                                Toast.fire({ icon: 'error', title: response.message || 'Failed to update status' });
                            } else {
                                Toast.fire({ icon: 'success', title: 'Banner status updated' });
                            }
                        },
                        error: function() {
                            $this.prop('checked', !$this.prop('checked'));
                            Toast.fire({ icon: 'error', title: 'Failed to update status' });
                        }
                    });
                });

                // Activate initial tab
                activateTab(getActiveTab());

                @if (Session::has('success'))
                    Toast.fire({ icon: 'success', title: "{{ Session::get('success') }}" });
                @endif
                @if (Session::has('error'))
                    Swal.fire({ icon: 'error', title: 'Error', text: "{{ Session::get('error') }}" });
                @endif
            });
        </script>
    @endpush
@endsection
