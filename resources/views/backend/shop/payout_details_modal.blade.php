@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <link rel="stylesheet" href="{{ url('assets/libs/chartist/chartist.min.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <style>
            /* Shop Header Section */
            .shop-header {
                background: linear-gradient(135deg, #4AB14C 0%, #1F4B20 100%);
                border-radius: 0.375rem;
                overflow: hidden;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                position: relative;
                color: white;
                margin-bottom: 1.5rem;
                background-size: 200% 200%;
                animation: gradientBG 15s ease infinite;
            }

            @keyframes gradientBG {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }

            .shop-header-content {
                padding: 2rem;
                position: relative;
                z-index: 2;
            }

            .shop-header-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.1);
                z-index: 1;
            }

            .shop-avatar {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                border: 4px solid rgba(255, 255, 255, 0.2);
                object-fit: cover;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
                background-color: rgba(255, 255, 255, 0.1);
            }

            .shop-avatar:hover {
                transform: scale(1.05);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            }

            .shop-title {
                font-weight: 600;
                margin-bottom: 0.5rem;
                font-size: 1.75rem;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .shop-subtitle {
                opacity: 0.9;
                margin-bottom: 1rem;
                font-size: 1rem;
            }

            .shop-status-badge {
                font-size: 0.75rem;
                padding: 0.35rem 0.75rem;
                border-radius: 50px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            /* Stats Cards */
            .stats-card {
                border-radius: 0.375rem;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                position: relative;
                overflow: hidden;
                color: white;
                transition: all 0.3s ease;
                border: none;
                background-size: 200% 200%;
                animation: gradientBG 15s ease infinite;
            }

            .stats-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
            }

            .stats-card .card-icon {
                font-size: 3.5rem;
                position: absolute;
                right: 1.5rem;
                top: 1.5rem;
                opacity: 0.2;
                transition: all 0.3s ease;
            }

            .stats-card:hover .card-icon {
                opacity: 0.4;
                transform: scale(1.1);
            }

            .stats-card .card-title {
                font-size: 0.875rem;
                margin-bottom: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                opacity: 0.9;
            }

            .stats-card .card-value {
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .stats-card .card-change {
                font-size: 0.75rem;
                opacity: 0.8;
                display: flex;
                align-items: center;
            }

            .stats-card-shops {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .stats-card-categories {
                background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
            }

            .stats-card-products {
                background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            }

            .stats-card-revenue {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            }

            /* Info Cards */
            .info-card {
                border-radius: 0.375rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                margin-bottom: 1.5rem;
                border: none;
                transition: all 0.3s ease;
                background-color: white;
            }

            .info-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1);
            }

            .info-card .card-header {
                background-color: #f7fafc;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                font-weight: 600;
                padding: 1rem 1.5rem;
                border-radius: 0.375rem 0.375rem 0 0 !important;
            }

            .info-card .card-body {
                padding: 1.5rem;
            }

            .info-table {
                width: 100%;
            }

            .info-table tr:not(:last-child) {
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }

            .info-table td {
                padding: 0.75rem 0;
                vertical-align: top;
            }

            .info-table .label {
                color: #6c757d;
                font-weight: 500;
                width: 40%;
            }

            /* Mini Map */
            .mini-map-container {
                height: 200px;
                width: 100%;
                border-radius: 0.375rem;
                overflow: hidden;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                margin-top: 1rem;
                position: relative;
            }

            .map-placeholder {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #f8f9fa;
                color: #6c757d;
                z-index: 100;
            }

            /* Revenue Cards */
            .revenue-card {
                border-radius: 0.375rem;
                padding: 1.25rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                border: none;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .revenue-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1);
            }

            .revenue-card .card-title {
                font-size: 0.875rem;
                color: #6c757d;
                margin-bottom: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 600;
            }

            .revenue-card .card-value {
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 0.25rem;
            }

            .revenue-card .card-icon {
                font-size: 2.5rem;
                opacity: 0.2;
                position: absolute;
                right: 1.25rem;
                top: 1.25rem;
                transition: all 0.3s ease;
            }

            .revenue-card:hover .card-icon {
                opacity: 0.3;
                transform: scale(1.1);
            }

            .revenue-card-total {
                background-color: #e3f2fd;
                color: #5e72e4;
            }

            .revenue-card-commission {
                background-color: #e8f5e9;
                color: #2dce89;
            }

            .revenue-card-shop {
                background-color: #fff8e1;
                color: #fb6340;
            }

            /* Chart Section */
            .chart-container {
                background: white;
                border-radius: 0.375rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                position: relative;
            }

            .chart-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
            }

            .chart-title {
                font-weight: 600;
                margin-bottom: 0;
            }

            .date-range-picker {
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                padding: 0.5rem 1rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
            }

            .date-range-picker:hover {
                border-color: #5e72e4;
            }

            .ct-legend {
                text-align: center;
                margin-top: 1rem;
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
            }

            .ct-legend li {
                display: inline-flex;
                padding: 0 1rem;
                list-style: none;
                position: relative;
                padding-left: 1.5rem;
                margin: 0.25rem;
                align-items: center;
            }

            .ct-legend li:before {
                width: 1rem;
                height: 1rem;
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                content: '';
                border-radius: 2px;
            }

            .ct-series-0:before {
                background-color: #5e72e4;
            }

            .ct-series-1:before {
                background-color: #2dce89;
            }

            .ct-series-2:before {
                background-color: #fb6340;
            }

            /* No Data State */
            .no-data-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                text-align: center;
                color: #6c757d;
            }

            .no-data-icon {
                font-size: 3rem;
                margin-bottom: 1rem;
                opacity: 0.5;
            }

            .no-data-text {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            /* Payouts Section */
            .payout-card {
                border-radius: 0.375rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                border: none;
            }

            .payout-card .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 1.5rem;
                background-color: #f7fafc;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                font-weight: 600;
                border-radius: 0.375rem 0.375rem 0 0 !important;
            }

            .payout-badge {
                font-size: 0.75rem;
                padding: 0.35rem 0.75rem;
                border-radius: 50px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .badge-paid {
                background-color: rgba(45, 206, 137, 0.1);
                color: #2dce89;
            }

            .badge-pending {
                background-color: rgba(251, 99, 64, 0.1);
                color: #fb6340;
            }

            .badge-failed {
                background-color: rgba(245, 54, 92, 0.1);
                color: #f5365c;
            }

            .badge-processing {
                background-color: rgba(17, 205, 239, 0.1);
                color: #11cdef;
            }

            /* Status Section */
            .status-card {
                border-radius: 0.375rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                border: none;
                margin-bottom: 1.5rem;
            }

            .status-card.active .card-header {
                background-color: rgba(45, 206, 137, 0.1);
                border-bottom: 1px solid rgba(45, 206, 137, 0.2);
                color: #2dce89;
            }

            .status-card.inactive .card-header {
                background-color: rgba(245, 54, 92, 0.1);
                border-bottom: 1px solid rgba(245, 54, 92, 0.2);
                color: #f5365c;
            }

            .status-card .card-body {
                padding: 1.5rem;
            }

            .status-alert {
                border-radius: 0.375rem;
                padding: 1rem 1.5rem;
                display: flex;
                align-items: center;
            }

            .status-alert i {
                font-size: 1.5rem;
                margin-right: 1rem;
            }

            .status-alert-content {
                flex: 1;
            }

            .status-history-btn {
                white-space: nowrap;
                margin-left: 1rem;
            }

            /* Action Buttons */
            .action-btn {
                width: 32px;
                height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.3s ease;
                margin: 0 2px;
            }

            .action-btn:hover {
                transform: scale(1.1);
            }

            .btn-view {
                background-color: rgba(17, 205, 239, 0.1);
                color: #11cdef;
            }

            .btn-view:hover {
                background-color: rgba(17, 205, 239, 0.2);
            }

            .btn-edit {
                background-color: rgba(94, 114, 228, 0.1);
                color: #5e72e4;
            }

            .btn-edit:hover {
                background-color: rgba(94, 114, 228, 0.2);
            }

            .btn-delete {
                background-color: rgba(245, 54, 92, 0.1);
                color: #f5365c;
            }

            .btn-delete:hover {
                background-color: rgba(245, 54, 92, 0.2);
            }

            /* Loading States */
            .loading-shimmer {
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
                animation: shimmer 1.5s infinite linear;
                border-radius: 0.375rem;
                color: transparent !important;
                user-select: none;
            }

            .chart-loading {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                z-index: 10;
                border-radius: 0.375rem;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
            }

            .chart-loading-spinner {
                width: 3rem;
                height: 3rem;
                border: 0.25em solid rgba(94, 114, 228, 0.2);
                border-top-color: #5e72e4;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 1rem;
            }

            .chart-loading-text {
                color: #5e72e4;
                font-weight: 500;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            @keyframes shimmer {
                0% {
                    background-position: 200% 0;
                }

                100% {
                    background-position: -200% 0;
                }
            }

            /* Responsive Adjustments */
            @media (max-width: 767.98px) {
                .shop-header-content {
                    flex-direction: column;
                    text-align: center;
                }

                .shop-avatar {
                    margin-bottom: 1rem;
                }

                .stats-card .card-value {
                    font-size: 1.5rem;
                }

                .info-table .label {
                    width: 50%;
                }

                .chart-header {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .date-range-picker {
                    margin-top: 1rem;
                }
            }

            /* Animations */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in {
                animation: fadeIn 0.5s ease-out forwards;
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }
        </style>
    @endpush

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">
                        <i class="mdi mdi-store me-2"></i> Shop Management
                    </h4>
                    <div class="page-title-right">
                        <a href="{{ url('/admin/shops') }}" class="btn add-btn text-white">
                            <i class="mdi mdi-keyboard-backspace me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Alert -->
        @if (session('status'))
            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show mb-4" role="alert">
                <i
                    class="mdi mdi-{{ session('status')['type'] == 'success' ? 'check-circle-outline' : 'alert-circle-outline' }} me-2"></i>
                {{ session('status')['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Shop Header Section -->
        <div class="shop-header">
            <div class="shop-header-overlay"></div>
            <div class="d-flex flex-wrap align-items-center shop-header-content">
                <div class="flex-shrink-0 me-4">
                    <img src="{{ $shop->image ? url('storage/shop/' . $shop->image) : url('assets/images/default-shop.png') }}"
                        class="shop-avatar" alt="Shop Image"
                        onerror="this.src='{{ url('assets/images/default-shop.png') }}'">
                </div>
                <div class="flex-grow-1">
                    <h1 class="shop-title">{{ $shop->name }}</h1>
                    <p class="shop-subtitle">
                        <i class="mdi mdi-map-marker-outline me-1"></i>
                        {{ $shop->address ?? 'Address not provided' }}
                    </p>
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="shop-status-badge bg-white text-{{ $shop->status ? 'success' : 'danger' }} me-3">
                            <i class="mdi mdi-{{ $shop->status ? 'check-circle' : 'close-circle' }} me-1"></i>
                            {{ $shop->status ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="text-white me-3">
                            <i class="mdi mdi-calendar-clock me-1"></i>
                            Joined {{ $shop->created_at->format('d M Y') }}
                        </span>
                        <span class="text-white">
                            <i class="mdi mdi-identifier me-1"></i>
                            ID: {{ $shop->id }}
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0 mt-3 mt-md-0">
                    <div class="d-flex">
                        <form id="statusForm" action="{{ url('admin/shops-update-status/' . Crypt::encrypt($shop->id)) }}"
                            method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $shop->status ? 'danger' : 'success' }}">
                                <i class="mdi mdi-{{ $shop->status ? 'store-remove' : 'store-check' }} me-1"></i>
                                {{ $shop->status ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <!-- Total Categories -->
            <div class="col-md-6 col-lg-3">
                <div class="stats-card stats-card-categories animate-fade-in">
                    <div class="card-title">Total Categories</div>
                    <div class="card-value">{{ $productCategoryCounts->count() }}</div>
                    <i class="mdi mdi-shape card-icon"></i>
                </div>
            </div>

            <!-- Total Sub Categories -->
            <div class="col-md-6 col-lg-3">
                <div class="stats-card stats-card-shops animate-fade-in" style="animation-delay: 0.1s">
                    <div class="card-title">Total Sub Categories</div>
                    <div class="card-value">{{ $productSubCategoryCounts->count() }}</div>
                    <i class="mdi mdi-tag-multiple card-icon"></i>
                </div>
            </div>

            <!-- Total Products -->
            <div class="col-md-6 col-lg-3">
                <div class="stats-card stats-card-products animate-fade-in" style="animation-delay: 0.2s">
                    <div class="card-title">Total Products</div>
                    <div class="card-value">{{ $productCount }}</div>
                    <i class="mdi mdi-package-variant-closed card-icon"></i>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="col-md-6 col-lg-3">
                <div class="stats-card stats-card-revenue animate-fade-in" style="animation-delay: 0.3s">
                    <div class="card-title">Total Orders</div>
                    <div class="card-value">{{ $totalOrders ?? 0 }}</div>
                    <i class="mdi mdi-cart-outline card-icon"></i>
                </div>
            </div>
        </div>

        <!-- Revenue Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="revenue-card revenue-card-total">
                    <h5 class="card-title">Total Revenue</h5>
                    <div class="card-value" id="total-revenue">₹0.00</div>
                    <small class="text-muted">All time revenue</small>
                    <i class="mdi mdi-currency-inr card-icon"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="revenue-card revenue-card-commission">
                    <h5 class="card-title">Admin Commission</h5>
                    <div class="card-value" id="admin-commission">₹0.00</div>
                    <small class="text-muted">{{ $shop->commission_percentage ?? 0 }}% commission rate</small>
                    <i class="mdi mdi-account-supervisor card-icon"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="revenue-card revenue-card-shop">
                    <h5 class="card-title">Shop Revenue</h5>
                    <div class="card-value" id="shop-revenue">₹0.00</div>
                    <small class="text-muted">After commission deduction</small>
                    <i class="mdi mdi-store card-icon"></i>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Left Column - Shop Details -->
            <div class="col-lg-6">
                <!-- Shop Information Card -->
                <div class="info-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="mdi mdi-information-outline me-2"></i> Shop Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="info-table">
                            <tbody>
                                <tr>
                                    <td class="label">Phone</td>
                                    <td>
                                        <a href="tel:{{ $shop->phone }}" class="text-dark">
                                            {{ $shop->phone ?? 'N/A' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Email</td>
                                    <td>
                                        @if ($shop->email)
                                            <a href="mailto:{{ $shop->email }}" class="text-dark">
                                                {{ $shop->email }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Commission Percentage</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $shop->commission_percentage ?? 0 }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Location</td>
                                    <td>
                                        @if ($shop->latitude && $shop->longitude)
                                            <a href="https://www.google.com/maps?q={{ $shop->latitude }},{{ $shop->longitude }}"
                                                target="_blank" class="text-primary">
                                                <i class="mdi mdi-map-marker-outline me-1"></i> View on Map
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Coordinates</td>
                                    <td>
                                        @if ($shop->latitude && $shop->longitude)
                                            <code>{{ $shop->latitude }}, {{ $shop->longitude }}</code>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        @if ($shop->latitude && $shop->longitude)
                            <div class="mini-map-container" id="miniMap">
                                <div class="map-placeholder">
                                    <i class="mdi mdi-loading mdi-spin"></i> Loading map...
                                </div>
                            </div>
                        @else
                            <div class="no-data-container mt-3">
                                <i class="mdi mdi-map-marker-off no-data-icon"></i>
                                <div class="no-data-text">Location data not available</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Owner Information Card -->
                <div class="info-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="mdi mdi-account-tie me-2"></i> Owner Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="info-table">
                            <tbody>
                                <tr>
                                    <td class="label">Owner Name</td>
                                    <td>{{ $shop->owner_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Contact</td>
                                    <td>{{ $shop->owner_phone ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column - Bank Details and Status -->
            <div class="col-lg-6">
                <!-- Bank Details Card -->
                <div class="info-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="mdi mdi-bank me-2"></i> Bank Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="info-table">
                            <tbody>
                                <tr>
                                    <td class="label">Bank Name</td>
                                    <td>{{ $bank->bank_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Account Number</td>
                                    <td>{{ $bank->account_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">IFSC Code</td>
                                    <td>{{ $bank->ifsc_code ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Account Holder</td>
                                    <td>{{ $bank->holder_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Bank Branch</td>
                                    <td>{{ $bank->branch ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Account Status Section -->
                <div class="status-card {{ $shop->deactivation_status ? 'inactive' : 'active' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
        <i class="mdi mdi-store-remove-outline me-2"></i> Shop Status
    </h5>
    @if ($shop->deactivation_status)
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reactivateModal">
            <i class="mdi mdi-store-check me-1"></i> Reactivate Shop
        </button>
    @else
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deactivateModal">
            <i class="mdi mdi-store-remove me-1"></i> Deactivate Shop
        </button>
    @endif
</div>

                    <div class="card-body">
                        @if ($shop->deactivation_status)
                            <div class="alert alert-danger status-alert">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                <div class="status-alert-content">
                                    <strong>This shop is currently deactivated.</strong>
                                    <div class="mt-2">
                                        <strong>Reason:</strong> {{ $shop->latestDeactivation->reason ?? 'N/A' }}
                                    </div>
                                    <div class="mt-1">
                                        <strong>Deactivated On:</strong>
                                        {{ optional($shop->latestDeactivation->created_at)->format('d M Y h:i A') }}
                                    </div>
                                    <div class="mt-1">
                                        <strong>Deactivated By:</strong> {{ $shop->latestDeactivation->actionBy->name ?? 'System' }}
                                    </div>
                                </div>
                                <a href="#"
   class="btn btn-outline-dark btn-sm status-history-btn"
   data-shop-id="{{ Crypt::encrypt($shop->id) }}"
   data-bs-toggle="modal"
   data-bs-target="#deactivationHistoryModal"
   onclick="loadDeactivationHistory(this)">
    <i class="mdi mdi-history me-1"></i> View History
</a>
                            </div>
                        @else
                            <div class="alert alert-success status-alert">
                                <i class="mdi mdi-check-circle-outline"></i>
                                <div class="status-alert-content">
                                    <strong>This shop is currently active and in good standing.</strong>
                                    <div class="mt-2">
                                        <strong>Last Active:</strong> {{ $shop->updated_at->format('d M Y h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Analytics Section -->
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <div class="chart-header">
                        <h5 class="chart-title">
                            <i class="mdi mdi-chart-line me-2"></i> Revenue Analytics
                        </h5>
                        <div id="reportrange" class="date-range-picker">
                            <i class="mdi mdi-calendar me-1"></i>&nbsp;
                            <span></span> <i class="mdi mdi-menu-down"></i>
                        </div>
                        <input type="hidden" id="start-date">
                        <input type="hidden" id="end-date">
                    </div>
                    <div id="revenue-chart" class="ct-chart ct-golden-section"></div>
                    <div id="revenue-chart-no-data" class="no-data-container" style="display: none;">
                        <i class="mdi mdi-chart-line no-data-icon"></i>
                        <div class="no-data-text">No revenue data available for the selected period</div>
                        <button class="btn btn-sm btn-primary mt-2" id="refresh-revenue-btn">
                            <i class="mdi mdi-refresh me-1"></i> Refresh Data
                        </button>
                    </div>
                    <div class="ct-legend">
                        <ul>
                            <li class="ct-series-0">Total Revenue</li>
                            <li class="ct-series-1">Admin Commission</li>
                            <li class="ct-series-2">Shop Revenue</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payouts Section -->
        <div class="row">
            <div class="col-12">
                <div class="payout-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="mdi mdi-wallet-outline me-2"></i> Shop Payouts
                        </h5>
                        <div id="payout-range" class="date-range-picker">
                            <i class="mdi mdi-calendar me-1"></i>&nbsp;
                            <span></span> <i class="mdi mdi-menu-down"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stats-card stats-card-shops">
                                    <div class="card-title">Total Payouts</div>
                                    <div class="card-value" id="total-payouts">{{ $totalPayouts }}</div>
                                    <i class="mdi mdi-wallet card-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card stats-card-products">
                                    <div class="card-title">Paid Payouts</div>
                                    <div class="card-value" id="paid-payouts">{{ $paidPayouts }}</div>
                                    <i class="mdi mdi-check-circle card-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card stats-card-revenue">
                                    <div class="card-title">Pending Payouts</div>
                                    <div class="card-value" id="pending-payouts">{{ $pendingPayouts }}</div>
                                    <i class="mdi mdi-clock-outline card-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card stats-card-categories">
                                    <div class="card-title">Failed Payouts</div>
                                    <div class="card-value" id="failed-payouts">{{ $failedPayouts }}</div>
                                    <i class="mdi mdi-alert-circle card-icon"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="revenue-card revenue-card-total">
                                    <h5 class="card-title">Total Amount</h5>
                                    <div class="card-value" id="total-amount">₹{{ number_format($totalAmount, 2) }}</div>
                                    <i class="mdi mdi-cash-multiple card-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="revenue-card revenue-card-commission">
                                    <h5 class="card-title">Total Commission</h5>
                                    <div class="card-value" id="total-commission">
                                        ₹{{ number_format($totalCommission, 2) }}</div>
                                    <i class="mdi mdi-account-supervisor card-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="revenue-card revenue-card-shop">
                                    <h5 class="card-title">Total Payable</h5>
                                    <div class="card-value" id="total-payable">₹{{ number_format($totalPayable, 2) }}
                                    </div>
                                    <i class="mdi mdi-bank-transfer card-icon"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Payouts Chart -->
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5 class="chart-title">
                                    <i class="mdi mdi-chart-bar me-2"></i> Payouts Overview
                                </h5>
                            </div>
                            <div id="payouts-chart" class="ct-chart ct-golden-section"></div>
                            <div id="payouts-chart-no-data" class="no-data-container" style="display: none;">
                                <i class="mdi mdi-chart-bar no-data-icon"></i>
                                <div class="no-data-text">No payout data available for the selected period</div>
                                <button class="btn btn-sm btn-primary mt-2" id="refresh-payouts-btn">
                                    <i class="mdi mdi-refresh me-1"></i> Refresh Data
                                </button>
                            </div>
                            <div class="ct-legend">
                                <ul>
                                    <li class="ct-series-0">Total Amount</li>
                                    <li class="ct-series-1">Commission</li>
                                    <li class="ct-series-2">Payable Amount</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Payouts Table -->
                        <div class="table-responsive table-border">
                            <table id="payoutsTable" class="table table-striped data-table job-table">
                                <thead class="table-theme text-white">
                                    <tr>
                                        <th>Payout ID</th>
                                        <th>Date Range</th>
                                        <th>Amount</th>
                                        <th>Commission</th>
                                        <th>Payable</th>
                                        <th>Status</th>
                                        <th>Generated On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payouts as $payout)
                                        <tr>
                                            <td>#{{ $payout->id }}</td>
                                            <td>
                                                {{ $payout->payout->start_date->format('d M Y') }} -
                                                {{ $payout->payout->end_date->format('d M Y') }}
                                            </td>
                                            <td>₹{{ number_format($payout->amount, 2) }}</td>
                                            <td>₹{{ number_format($payout->commission, 2) }}</td>
                                            <td>₹{{ number_format($payout->payable_amount, 2) }}</td>
                                            <td>
                                                @if ($payout->status == 'paid')
                                                    <span class="payout-badge badge-paid">
                                                        <i class="mdi mdi-check-circle me-1"></i> Paid
                                                    </span>
                                                @elseif($payout->status == 'failed')
                                                    <span class="payout-badge badge-failed">
                                                        <i class="mdi mdi-alert-circle me-1"></i> Failed
                                                    </span>
                                                @elseif($payout->status == 'processing')
                                                    <span class="payout-badge badge-processing">
                                                        <i class="mdi mdi-clock-outline me-1"></i> Processing
                                                    </span>
                                                @else
                                                    <span class="payout-badge badge-pending">
                                                        <i class="mdi mdi-file-outline me-1"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $payout->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                <button class="action-btn btn-view view-payout-details"
                                                    data-id="{{ Crypt::encrypt($payout->id) }}" title="View Details">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                @if ($payout->status == 'pending')
                                                    <button class="action-btn btn-edit process-payout"
                                                        data-id="{{ Crypt::encrypt($payout->id) }}" title="Process Payout">
                                                        <i class="mdi mdi-cash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Sections -->
        <div class="row mt-2">
            <!-- Recent Orders -->
            <div class="col-lg-12">
                <div class="info-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="mdi mdi-cart-outline me-2"></i> Recent Orders
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-border">
                            <table id="recentOrdersTable" class="table table-striped data-table job-table">
                                <thead class="table-theme text-white">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shop Products -->
            <div class="col-lg-12">
                <div class="info-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="mdi mdi-package-variant-closed me-2"></i> Shop Products
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-border">
                            <table id="shopProductsTable" class="table table-striped data-table job-table">
                                <thead class="table-theme text-white">
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactivate Modal -->
    <div class="modal fade" id="deactivateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white">
                        <i class="mdi mdi-store-remove me-1"></i> Deactivate Shop
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ url('admin/shops/' . Crypt::encrypt($shop->id) . '/deactivate') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="deactivation_reason" class="form-label">Reason for Deactivation</label>
                            <textarea class="form-control" id="deactivation_reason" name="deactivation_reason" rows="3" required
                                placeholder="Enter the reason for deactivating this shop"></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            Deactivating this shop will:
                            <ul class="mt-2 mb-0">
                                <li>Hide the shop from customers</li>
                                <li>Prevent new orders from being placed</li>
                                <li>Allow completion of existing orders</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="mdi mdi-store-remove me-1"></i> Confirm Deactivation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reactivate Modal -->
    <div class="modal fade" id="reactivateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white">
                        <i class="mdi mdi-store-check me-1"></i> Reactivate Shop
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ url('admin/shops/' . Crypt::encrypt($shop->id) . '/reactivate') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reactivation_reason" class="form-label">Reason for Reactivation</label>
                            <textarea class="form-control" id="reactivation_reason" name="reactivation_reason" rows="3" required
                                placeholder="Enter the reason for reactivating this shop"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Reactivating this shop will:
                            <ul class="mt-2 mb-0">
                                <li>Make the shop visible to customers</li>
                                <li>Allow new orders to be placed</li>
                                <li>Restore all shop functionality</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-store-check me-1"></i> Confirm Reactivation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Items Modal -->
    <div class="modal fade" id="orderItemsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">
                        <i class="mdi mdi-cart-outline me-1"></i> Order Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderItemsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" role="dialog"
        aria-labelledby="productDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="productDetailsModalLabel">Product Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="productDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Details Modal -->
    <div class="modal fade" id="payoutDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">
                        <i class="mdi mdi-wallet-outline me-1"></i> Payout Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="payoutDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add this modal at the bottom of your Blade template -->
<div class="modal fade" id="deactivationHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="mdi mdi-history me-1"></i> Deactivation History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deactivationHistoryContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-dark" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading history...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('footer_content')
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"
        integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ url('assets/libs/chartist/chartist.min.js') }}"></script>
    <script src="{{ url('assets/libs/chartist-plugin-tooltips/chartist-plugin-tooltip.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ url('assets/js/pages/chartist.init.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#recentOrdersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('admin/shop-orders/' . Crypt::encrypt($shop->id)) }}",
                columns: [{
                        data: 'order_no',
                        name: 'order_no'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'desc']
                ]
            });

            $('#shopProductsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('admin/shop-products/' . Crypt::encrypt($shop->id)) }}",
                columns: [{
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex align-items-center">
                                    <img src="${row.image_url}" class="rounded me-3" width="40" height="40" alt="${data}" onerror="this.src='{{ url('assets/images/default-product.png') }}'">
                                    <div>
                                        <h6 class="mb-0">${data}</h6>
                                        <small class="text-muted"> ${row.product_no || 'N/A'}</small>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'product_stock',
                        name: 'product_stock'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ]
            });

            $('#payoutsTable').DataTable({
                order: [
                    [6, 'desc']
                ],
                responsive: true,
                columnDefs: [{
                    orderable: false,
                    targets: [7]
                }]
            });

            // Initialize Mini Map
            if (document.getElementById('miniMap')) {
                var latitude = {{ $shop->latitude ?? 0 }};
                var longitude = {{ $shop->longitude ?? 0 }};

                if (latitude && longitude) {
                    var radiusKm = {{ App\Models\Application::where('name', 'shop_radius')->value('value') ?? 1 }};
                    var radiusMeters = radiusKm * 1000;

                    var map = L.map('miniMap').setView([latitude, longitude], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    // Add marker
                    var marker = L.marker([latitude, longitude]).addTo(map)
                        .bindPopup('<b>' + '{{ $shop->name }}' + '</b><br>' + '{{ $shop->address }}')
                        .openPopup();

                    // Add circle
                    L.circle([latitude, longitude], {
                        color: '#5e72e4',
                        fillColor: '#5e72e4',
                        fillOpacity: 0.2,
                        radius: radiusMeters
                    }).addTo(map);

                    // Remove loading placeholder
                    $('.mini-map-container .map-placeholder').remove();
                } else {
                    $('.mini-map-container .map-placeholder').html(`
                        <i class="mdi mdi-map-marker-off"></i>
                        <div>Location data not available</div>
                    `);
                }
            }

            // Status change confirmation
            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Confirm Status Change',
                    text: 'Are you sure you want to change the status of this shop?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });

            // View Order Items
            $(document).on('click', '.view-order-items', function() {
                var orderId = $(this).data('id');
                var modal = $('#orderItemsModal');

                $('#orderItemsContent').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading order details...</p>
                    </div>
                `);

                modal.modal('show');

                $.ajax({
                    url: "{{ url('admin/shops-order-items') }}/" + orderId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#orderItemsContent').html(response.html);
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message ||
                            'Failed to load order details';
                        showError(errorMessage);
                    }
                });
            });

            // View Product Details
            $(document).on('click', '.view-product-details', function() {
                var productId = $(this).data('id');
                var modal = $('#productDetailsModal');

                $('#productDetailsContent').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading product details...</p>
                    </div>
                `);

                modal.modal('show');

                $.ajax({
                    url: "{{ url('admin/shops-product-details') }}/" + productId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#productDetailsContent').html(response.html);
                        } else {
                            showProductError(response.message);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message ||
                            'Failed to load product details';
                        showProductError(errorMessage);
                    }
                });
            });

            // View Payout Details
            $(document).on('click', '.view-payout-details', function() {
                var payoutId = $(this).data('id');
                var modal = $('#payoutDetailsModal');

                $('#payoutDetailsContent').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading payout details...</p>
                    </div>
                `);

                modal.modal('show');

                $.ajax({
                    url: "{{ url('admin/shops-payout-details') }}/" + payoutId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#payoutDetailsContent').html(response.html);
                        } else {
                            showPayoutError(response.message);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message ||
                            'Failed to load payout details';
                        showPayoutError(errorMessage);
                    }
                });
            });

            // Process Payout
            $(document).on('click', '.process-payout', function() {
                var payoutId = $(this).data('id');

                Swal.fire({
                    title: 'Process Payout?',
                    text: 'Are you sure you want to process this payout?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, process it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/shops-process-payout') }}/" + payoutId,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Success!', response.message, 'success');
                                    $('#payoutsTable').DataTable().ajax.reload(null,
                                        false);
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Error processing payout', 'error');
                            }
                        });
                    }
                });
            });

            // Approve/Reject Product
            $(document).on('click', '.approve-product, .reject-product', function(e) {
                e.preventDefault();
                var productId = $(this).data('id');
                var status = $(this).hasClass('approve-product') ? 1 : -1;
                var action = status == 1 ? 'approve' : 'reject';

                Swal.fire({
                    title: `${action.charAt(0).toUpperCase() + action.slice(1)} Product?`,
                    text: `Are you sure you want to ${action} this product?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Yes, ${action} it!`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/shop-products-update-status') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                product_id: productId,
                                status: status
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Success!', response.message, 'success');
                                    $('#shopProductsTable').DataTable().ajax.reload(
                                        null, false);
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Error updating product status', 'error');
                            }
                        });
                    }
                });
            });

            function showError(message) {
                $('#orderItemsContent').html(`
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        ${message}
                    </div>
                `);
            }

            function showProductError(message) {
                $('#productDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        ${message}
                    </div>
                `);
            }

            function showPayoutError(message) {
                $('#payoutDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        ${message}
                    </div>
                `);
            }

            // Revenue Analytics Chart
            const start = moment().subtract(29, 'days');
            const end = moment();
            let revenueChart = null;
            let payoutsChart = null;

            function showLoadingState(selector) {
                $(selector).prepend(`
                    <div class="chart-loading">
                        <div class="chart-loading-spinner"></div>
                        <div class="chart-loading-text">Loading data...</div>
                    </div>
                `);
            }

            function hideLoadingState(selector) {
                $(selector).find('.chart-loading').remove();
            }

            function showNoDataState(selector) {
                $(selector).hide();
                $(selector + '-no-data').show();
            }

            function hideNoDataState(selector) {
                $(selector).show();
                $(selector + '-no-data').hide();
            }

            function formatCurrency(value) {
                const numValue = typeof value === 'number' ? value : parseFloat(value) || 0;
                return '₹' + numValue.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Revenue Date Range Picker
            function cb(start, end, label) {
                $('#reportrange span').html(start.format('D MMM, YYYY') + ' - ' + end.format('D MMM, YYYY'));
                $('#start-date').val(start.format('YYYY-MM-DD'));
                $('#end-date').val(end.format('YYYY-MM-DD'));

                fetchShopStats(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                maxDate: moment(),
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days')
                        .endOf('day')
                    ],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'Last 3 Months': [moment().subtract(3, 'months').startOf('month'), moment().endOf(
                        'month')],
                    'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf(
                        'month')]
                },
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Apply',
                    cancelLabel: 'Cancel',
                    fromLabel: 'From',
                    toLabel: 'To',
                    customRangeLabel: 'Custom',
                    weekLabel: 'W',
                    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ],
                    firstDay: 1
                }
            }, cb);

            // Payouts Date Range Picker
            function payoutCb(start, end, label) {
                $('#payout-range span').html(start.format('D MMM, YYYY') + ' - ' + end.format('D MMM, YYYY'));
                fetchPayoutStats(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            }

            $('#payout-range').daterangepicker({
                startDate: start,
                endDate: end,
                maxDate: moment(),
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days')
                        .endOf('day')
                    ],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'Last 3 Months': [moment().subtract(3, 'months').startOf('month'), moment().endOf(
                        'month')],
                    'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf(
                        'month')]
                },
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Apply',
                    cancelLabel: 'Cancel',
                    fromLabel: 'From',
                    toLabel: 'To',
                    customRangeLabel: 'Custom',
                    weekLabel: 'W',
                    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ],
                    firstDay: 1
                }
            }, payoutCb);

            function initializeRevenueChart() {
                if (revenueChart) {
                    revenueChart.detach();
                }

                revenueChart = new Chartist.Line('#revenue-chart', {
                    labels: [],
                    series: [
                        [],
                        [],
                        []
                    ]
                }, {
                    fullWidth: true,
                    low: 0,
                    showArea: false,
                    showPoint: true,
                    lineSmooth: Chartist.Interpolation.cardinal({
                        tension: 0.1
                    }),
                    chartPadding: {
                        right: 40,
                        left: 20,
                        top: 20,
                        bottom: 20
                    },
                    plugins: [
                        Chartist.plugins.tooltip({
                            currency: '₹',
                            appendToBody: true,
                            transformTooltipTextFnc: function(value) {
                                return formatCurrency(value);
                            }
                        })
                    ],
                    axisY: {
                        labelInterpolationFnc: function(value) {
                            if (value >= 100000) {
                                return '₹' + (value / 100000).toFixed(1) + 'L';
                            } else if (value >= 1000) {
                                return '₹' + (value / 1000).toFixed(1) + 'K';
                            }
                            return '₹' + Math.round(value);
                        }
                    },
                    axisX: {
                        labelInterpolationFnc: function(value, index) {
                            const totalLabels = revenueChart?.data?.labels?.length || 0;

                            if (totalLabels <= 7) {
                                return value;
                            } else if (totalLabels <= 31) {
                                return index % 2 === 0 ? value :
                                    null;
                            } else {
                                return index % 7 === 0 ? value :
                                    null;
                            }
                        }
                    }
                });

                revenueChart.on('draw', function(data) {
                    if (data.type === 'line') {
                        data.element.animate({
                            d: {
                                begin: 200 * data.index,
                                dur: 1000,
                                from: data.path.clone().scale(1, 0).translate(0, data.chartRect
                                    .height()).stringify(),
                                to: data.path.clone().stringify(),
                                easing: Chartist.Svg.Easing.easeOutQuint
                            }
                        });
                    }

                    if (data.type === 'point') {
                        data.element.animate({
                            opacity: {
                                begin: 200 * data.index,
                                dur: 500,
                                from: 0,
                                to: 1,
                                easing: 'ease'
                            }
                        });
                    }
                });
            }

            function initializePayoutsChart() {
                if (payoutsChart) {
                    payoutsChart.detach();
                }

                payoutsChart = new Chartist.Bar('#payouts-chart', {
                    labels: [],
                    series: [
                        [],
                        [],
                        []
                    ]
                }, {
                    stackBars: true,
                    fullWidth: true,
                    chartPadding: {
                        right: 40,
                        left: 20,
                        top: 20,
                        bottom: 20
                    },
                    plugins: [
                        Chartist.plugins.tooltip({
                            currency: '₹',
                            appendToBody: true,
                            transformTooltipTextFnc: function(value) {
                                return formatCurrency(value);
                            }
                        })
                    ],
                    axisY: {
                        labelInterpolationFnc: function(value) {
                            if (value >= 100000) {
                                return '₹' + (value / 100000).toFixed(1) + 'L';
                            } else if (value >= 1000) {
                                return '₹' + (value / 1000).toFixed(1) + 'K';
                            }
                            return '₹' + Math.round(value);
                        }
                    },
                    axisX: {
                        labelInterpolationFnc: function(value, index) {
                            const totalLabels = payoutsChart?.data?.labels?.length || 0;

                            if (totalLabels <= 7) {
                                return value;
                            } else if (totalLabels <= 31) {
                                return index % 2 === 0 ? value :
                                    null;
                            } else {
                                return index % 7 === 0 ? value :
                                    null;
                            }
                        }
                    }
                });

                payoutsChart.on('draw', function(data) {
                    if (data.type === 'bar') {
                        data.element.animate({
                            y2: {
                                begin: 200 * data.index,
                                dur: 1000,
                                from: data.y1,
                                to: data.y2,
                                easing: Chartist.Svg.Easing.easeOutQuint
                            }
                        });
                    }
                });
            }

            function fetchShopStats(startDate, endDate) {
                showLoadingState('#revenue-chart');
                hideNoDataState('#revenue-chart');

                $.ajax({
                    url: "{{ url('admin/shops/' . Crypt::encrypt($shop->id) . '/stats') }}",
                    type: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate
                    },
                    timeout: 30000,
                    success: function(response) {
                        hideLoadingState('#revenue-chart');

                        if (response.success) {
                            updateStatsCards(response);

                            const allDatesData = generateAllDatesInRange(startDate, endDate, response
                                .chart_data);

                            if (allDatesData.total_revenue.some(val => val > 0)) {
                                updateRevenueChart(allDatesData);
                            } else {
                                showNoDataState('#revenue-chart');
                            }

                            updateSummaryStats(response);
                        } else {
                            showNoDataState('#revenue-chart');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoadingState('#revenue-chart');
                        showNoDataState('#revenue-chart');
                        console.error('Error fetching shop stats:', error);
                    }
                });
            }

            function fetchPayoutStats(startDate, endDate) {
                showLoadingState('#payouts-chart');
                hideNoDataState('#payouts-chart');

                $.ajax({
                    url: "{{ url('admin/shops/' . Crypt::encrypt($shop->id) . '/payout-stats') }}",
                    type: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        hideLoadingState('#payouts-chart');

                        if (response.success) {
                            updatePayoutStats(response);

                            if (response.chart_data && response.chart_data.total_amount.some(val =>
                                    val > 0)) {
                                updatePayoutsChart(response.chart_data);
                            } else {
                                showNoDataState('#payouts-chart');
                            }
                        } else {
                            showNoDataState('#payouts-chart');
                        }
                    },
                    error: function(xhr) {
                        hideLoadingState('#payouts-chart');
                        showNoDataState('#payouts-chart');
                        console.error('Error fetching payout stats:', xhr.responseJSON?.message ||
                            'Error loading data');
                    }
                });
            }

            function generateAllDatesInRange(startDate, endDate, chartData) {
                const start = moment(startDate);
                const end = moment(endDate);
                const dateRange = end.diff(start, 'days') + 1;

                const allDates = [];
                const allLabels = [];
                let current = moment(startDate);

                while (current.isSameOrBefore(end, 'day')) {
                    allDates.push(current.format('YYYY-MM-DD'));
                    allLabels.push(current.format('D MMM'));
                    current.add(1, 'day');
                }

                const dataMap = {};
                if (chartData && chartData.labels && chartData.labels.length > 0) {
                    chartData.labels.forEach((label, index) => {
                        const labelDate = moment(label, ['D MMM', 'DD MMM', 'D MMM YYYY', 'DD MMM YYYY',
                            'YYYY-MM-DD'
                        ], true);
                        if (labelDate.isValid()) {
                            const dateKey = labelDate.format('YYYY-MM-DD');
                            dataMap[dateKey] = {
                                total: parseFloat(chartData.total_revenue[index]) || 0,
                                admin: parseFloat(chartData.admin_commission[index]) || 0,
                                shop: parseFloat(chartData.shop_revenue[index]) || 0
                            };
                        }
                    });
                }

                const totalRevenue = [];
                const adminCommission = [];
                const shopRevenue = [];

                allDates.forEach(date => {
                    if (dataMap[date]) {
                        totalRevenue.push(dataMap[date].total);
                        adminCommission.push(dataMap[date].admin);
                        shopRevenue.push(dataMap[date].shop);
                    } else {
                        totalRevenue.push(0);
                        adminCommission.push(0);
                        shopRevenue.push(0);
                    }
                });

                const result = {
                    labels: allLabels,
                    total_revenue: totalRevenue,
                    admin_commission: adminCommission,
                    shop_revenue: shopRevenue
                };

                return result;
            }

            function updateStatsCards(response) {
                const cards = [{
                        id: '#total-revenue',
                        value: response.total_revenue,
                        prevValue: parseFloat($('#total-revenue').text().replace(/[₹,]/g, '')) || 0
                    },
                    {
                        id: '#admin-commission',
                        value: response.admin_commission,
                        prevValue: parseFloat($('#admin-commission').text().replace(/[₹,]/g, '')) || 0
                    },
                    {
                        id: '#shop-revenue',
                        value: response.shop_revenue,
                        prevValue: parseFloat($('#shop-revenue').text().replace(/[₹,]/g, '')) || 0
                    }
                ];

                cards.forEach(card => {
                    const $element = $(card.id);
                    const currentValue = parseFloat(card.value) || 0;

                    $({
                        animatedValue: card.prevValue
                    }).animate({
                        animatedValue: currentValue
                    }, {
                        duration: 1000,
                        step: function() {
                            $element.text(formatCurrency(this.animatedValue));
                        },
                        complete: function() {
                            $element.text(formatCurrency(currentValue));
                        }
                    });
                });
            }

            function updatePayoutStats(response) {
                const stats = {
                    'total-payouts': response.total_payouts,
                    'paid-payouts': response.paid_payouts,
                    'pending-payouts': response.pending_payouts,
                    'failed-payouts': response.failed_payouts,
                    'total-amount': response.total_amount,
                    'total-commission': response.total_commission,
                    'total-payable': response.total_payable
                };

                Object.entries(stats).forEach(([id, value]) => {
                    const $element = $('#' + id);
                    const isCurrency = id.includes('amount') || id.includes('commission') || id.includes(
                        'payable');
                    const currentValue = isCurrency ? parseFloat(value) || 0 : parseInt(value) || 0;
                    const prevValue = isCurrency ?
                        parseFloat($element.text().replace(/[₹,]/g, '')) || 0 :
                        parseInt($element.text()) || 0;

                    $({
                        animatedValue: prevValue
                    }).animate({
                        animatedValue: currentValue
                    }, {
                        duration: 1000,
                        step: function() {
                            if (isCurrency) {
                                $element.text(formatCurrency(this.animatedValue));
                            } else {
                                $element.text(Math.round(this.animatedValue));
                            }
                        },
                        complete: function() {
                            if (isCurrency) {
                                $element.text(formatCurrency(currentValue));
                            } else {
                                $element.text(currentValue);
                            }
                        }
                    });
                });
            }

            function updateRevenueChart(processedData) {
                if (!revenueChart) {
                    initializeRevenueChart();
                }

                const seriesWithMeta = processedData.total_revenue.map((value, index) => ({
                    value: value,
                    meta: processedData.labels[index]
                }));

                const adminSeriesWithMeta = processedData.admin_commission.map((value, index) => ({
                    value: value,
                    meta: processedData.labels[index]
                }));

                const shopSeriesWithMeta = processedData.shop_revenue.map((value, index) => ({
                    value: value,
                    meta: processedData.labels[index]
                }));

                revenueChart.update({
                    labels: processedData.labels,
                    series: [
                        seriesWithMeta,
                        adminSeriesWithMeta,
                        shopSeriesWithMeta
                    ]
                });
            }

            function updatePayoutsChart(chartData) {
                if (!payoutsChart) {
                    initializePayoutsChart();
                }

                const labels = chartData.labels || [];
                const totalAmount = chartData.total_amount || [];
                const commission = chartData.commission || [];
                const payable = chartData.payable || [];

                payoutsChart.update({
                    labels: labels,
                    series: [
                        totalAmount,
                        commission,
                        payable
                    ]
                });
            }

            function updateSummaryStats(response) {
                const totalRevenue = parseFloat(response.total_revenue) || 0;
                const adminCommission = parseFloat(response.admin_commission) || 0;
                const shopRevenue = parseFloat(response.shop_revenue) || 0;

                const adminPercentage = totalRevenue > 0 ? ((adminCommission / totalRevenue) * 100).toFixed(1) : 0;
                const shopPercentage = totalRevenue > 0 ? ((shopRevenue / totalRevenue) * 100).toFixed(1) : 0;

                $('.admin-commission-percentage').text(adminPercentage + '%');
                $('.shop-revenue-percentage').text(shopPercentage + '%');

                if (response.summary && response.summary.total_orders) {
                    $('#total-orders').text(response.summary.total_orders);
                    $('#average-order-value').text(formatCurrency(response.summary.average_order_value));
                }
            }

            // Initialize charts and load initial data
            initializeRevenueChart();
            initializePayoutsChart();
            cb(start, end);
            payoutCb(start, end);

            // Refresh buttons
            $('#refresh-revenue-btn').on('click', function() {
                const startDate = $('#start-date').val();
                const endDate = $('#end-date').val();
                if (startDate && endDate) {
                    fetchShopStats(startDate, endDate);
                }
            });

            $('#refresh-payouts-btn').on('click', function() {
                const startDate = $('#payout-range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                const endDate = $('#payout-range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                if (startDate && endDate) {
                    fetchPayoutStats(startDate, endDate);
                }
            });

            // Handle window resize
            $(window).on('resize', function() {
                if (revenueChart) {
                    setTimeout(function() {
                        revenueChart.update();
                    }, 100);
                }
                if (payoutsChart) {
                    setTimeout(function() {
                        payoutsChart.update();
                    }, 100);
                }
            });
        });

        @if (Session::has('success'))
            Swal.fire({
                title: "Success!",
                text: "{{ Session::get('success') }}",
                icon: "success",
                timer: 3000
            });
        @elseif (Session::has('error'))
            Swal.fire({
                title: "Error!",
                text: "{{ Session::get('error') }}",
                icon: "error"
            });
        @endif
    </script>
    <script>
        function loadDeactivationHistory(button) {
    const shopId = button.dataset.shopId;
    const modalContent = document.getElementById('deactivationHistoryContent');

    // Show loading state
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-dark" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading history...</p>
        </div>
    `;

    // Fetch the history data
    fetch(`/admin/shops/${shopId}/deactivation-history`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            modalContent.innerHTML = html;
        })
        .catch(error => {
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                    Failed to load deactivation history. Please try again.
                </div>
            `;
            console.error('Error loading deactivation history:', error);
        });
}
    </script>
@endpush
