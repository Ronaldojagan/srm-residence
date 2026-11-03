@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <link rel="stylesheet" href="{{ asset('assets/libs/chartist/chartist.min.css') }}">
        <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .report-card {
                border-radius: 10px;
                /* box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); */
                transition: transform 0.3s ease;
            }

            .report-card:hover {
                transform: translateY(-5px);
            }

            .report-card i {
                font-size: 24px;
                margin-bottom: 15px;
            }

            .chart-container {
                height: 300px;
                position: relative;
            }

            .report-tabs .nav-link {
                font-weight: 600;
                color: #6c757d;
            }

            .report-tabs .nav-link.active {
                color: #4e73df;
                background-color: rgba(78, 115, 223, 0.1);
            }

            .filter-popup {
                position: absolute;
                width: 800px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 5px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                display: none;
                z-index: 1050;
            }

            .filter-popup-content {
                padding: 0;
            }

            .filter-popup-header,
            .filter-popup-footer {
                padding: 10px 15px;
                background: #f8f9fa;
            }

            .filter-popup-body {
                padding: 15px;
                max-height: 500px;
                overflow-y: auto;
            }

            .filter-rule {
                margin-bottom: 10px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 4px;
                border: 1px solid #dee2e6;
            }

            .rule-connector {
                font-weight: bold;
                color: #666;
            }

            .filter-logic-toggle .btn {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }

            .no-data-chart {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100%;
                color: #6c757d;
                font-weight: bold;
            }

            .card-header-actions {
                display: flex;
                align-items: center;
            }

            .export-btn-group {
                margin-left: auto;
            }

            .select2-container--default .select2-selection--multiple {
                border: 1px solid #ced4da;
                min-height: 38px;
            }
        </style>
    @endpush

    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h4 class="page-title-main">Reports Dashboard</h4>
        </div>
        <div class="col-md-6 mt-3 text-end">
            <div class="input-group" id="reportrange" style="cursor: pointer; max-width: 300px; margin-left: auto;">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                <input type="text" class="form-control text-center" id="date-range" readonly>
                <input type="hidden" id="start-date">
                <input type="hidden" id="end-date">
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs report-tabs" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="order-tab" data-bs-toggle="tab" data-bs-target="#order-tab-pane"
                        type="button" role="tab" data-type="order">
                        <i class="fas fa-shopping-cart me-1"></i> Orders
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-tab-pane"
                        type="button" role="tab" data-type="user">
                        <i class="fas fa-users me-1"></i> Users
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="deliverymans-tab" data-bs-toggle="tab"
                        data-bs-target="#deliverymans-tab-pane" type="button" role="tab" data-type="deliverymans">
                        <i class="fas fa-users me-1"></i> Delivery Mans
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shop-tab" data-bs-toggle="tab" data-bs-target="#shop-tab-pane"
                        type="button" role="tab" data-type="shop">
                        <i class="fas fa-store me-1"></i> Delivery Points
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-tab-pane"
                        type="button" role="tab" data-type="payments">
                        <i class="fas fa-box-open me-1"></i> Payments
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shop-tab" data-bs-toggle="tab" data-bs-target="#shop-tab-pane"
                        type="button" role="tab" data-type="shop">
                        <i class="fas fa-store me-1"></i> Missing Bottles
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="product-tab" data-bs-toggle="tab" data-bs-target="#product-tab-pane"
                        type="button" role="tab" data-type="product">
                        <i class="fas fa-box-open me-1"></i> Packages
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="reportTabsContent">

        {{-- Users tab --}}
        <div class="tab-pane fade show active" id="user-tab-pane" role="tabpanel" aria-labelledby="user-tab">
            <div class="row mb-4" id="user-cards">
                <!-- Cards will be loaded here -->
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Users Data</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-table" data-table-id="user-table">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-theme-success filter-btn ms-2" data-table-id="user-table">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table user-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order tab --}}
        <div class="tab-pane fade" id="order-tab-pane" role="tabpanel" aria-labelledby="order-tab">
            <div class="row mb-4" id="order-cards">
                <!-- Cards will be loaded here -->
            </div>

            {{-- <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Order Status Distribution</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-chart"
                                    data-chart-id="orderStatusChart">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="orderStatusChart"></canvas>
                                <div class="no-data-chart" style="display: none;">No data available for this chart</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Payment Methods</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-chart"
                                    data-chart-id="paymentMethodChart">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="paymentMethodChart"></canvas>
                                <div class="no-data-chart" style="display: none;">No data available for this chart</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Orders Data</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-table" data-table-id="order-table">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-theme-success filter-btn ms-2" data-table-id="order-table">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table order-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delivery men tab --}}
        <div class="tab-pane fade show active" id="deliverymans-tab-pane" role="tabpanel"
            aria-labelledby="deliverymans-tab">
            <div class="row mb-4" id="deliverymans-cards">
                <!-- Cards will be loaded here -->
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Delivery Mans</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-table"
                                    data-table-id="deliverymans-table">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-theme-success filter-btn ms-2" data-table-id="deliverymans-table">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table deliverymans-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Joined Date</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Delivery Point</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delivery Points tab --}}
        <div class="tab-pane fade show active" id="deliverypoints-tab-pane" role="tabpanel"
            aria-labelledby="deliverypoints-tab">
            <div class="row mb-4" id="deliverypoints-cards">
                <!-- Cards will be loaded here -->
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Delivery Mans</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-table"
                                    data-table-id="deliverypoints-table">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-theme-success filter-btn ms-2"
                                    data-table-id="deliverypoints-table">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table deliverypoints-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Joined Date</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Delivery Point</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="payments-tab-pane" role="tabpanel" aria-labelledby="payments-tab">
            <div class="row mb-4" id="payments-cards">
                <!-- Cards will be loaded here -->
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Payments Data</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-table" data-table-id="payments-table">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-theme-success filter-btn ms-2" data-table-id="payments-table">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table payments-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Order ID</th>
                                            <th>User</th>
                                            <th>Trans ID</th>
                                            <th>Method</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="product-tab-pane" role="tabpanel" aria-labelledby="product-tab">
            <div class="row mb-4" id="product-cards">
                <!-- Cards will be loaded here -->
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Top Selling Products</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-chart"
                                    data-chart-id="topProductsChart">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="topProductsChart"></canvas>
                                <div class="no-data-chart" style="display: none;">No data available for this chart</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Stock Status</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-chart"
                                    data-chart-id="stockStatusChart">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="stockStatusChart"></canvas>
                                <div class="no-data-chart" style="display: none;">No data available for this chart</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Products Data</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-table" data-table-id="product-table">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-theme-success filter-btn ms-2" data-table-id="product-table">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table product-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="shop-tab-pane" role="tabpanel" aria-labelledby="shop-tab">
            <div class="row mb-4" id="shop-cards">
                <!-- Cards will be loaded here -->
            </div>

            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Top Performing Shops</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-chart" data-chart-id="topShopsChart">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="topShopsChart"></canvas>
                                <div class="no-data-chart" style="display: none;">No data available for this chart</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Shops Data</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-table" data-table-id="shop-table">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-theme-success filter-btn ms-2" data-table-id="shop-table">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table shop-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Shop Name</th>
                                            <th>Owner</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-2 d-flex align-items-center">
                            <h5>Shop Payout Data</h5>
                            <div class="export-btn-group ms-auto">
                                <button class="btn btn-theme-second-success export-table"
                                    data-table-id="shoppayout-table">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-theme-success filter-btn ms-2" data-table-id="shoppayout-table">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table shoppayout-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Shop Name</th>
                                            <th>Owner</th>
                                            <th>Total Payouts</th>
                                            <th>Total Amount</th>
                                            <th>Commission Rate</th>
                                            <th>Commission Amount</th>
                                            <th>Payable Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize date range picker
                const start = moment().subtract(29, 'days');
                const end = moment();

                $('#reportrange').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')]
                    }
                }, function(start, end) {
                    $('#date-range').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                    $('#start-date').val(start.format('YYYY-MM-DD'));
                    $('#end-date').val(end.format('YYYY-MM-DD'));

                    // Reload report data when date changes
                    loadReportData($('#reportTabs .nav-link.active').data('type'));
                    reloadDataTables();
                });

                // Set initial date range display
                $('#date-range').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                $('#start-date').val(start.format('YYYY-MM-DD'));
                $('#end-date').val(end.format('YYYY-MM-DD'));

                const filterPopups = {
                    user: {},
                    order: {},
                    product: {},
                    shop: {},
                    shoppayout: {} // Changed from shoppayout (consistent with type)
                };

                // Load initial data for the active tab
                loadReportData('user');
                initializeDataTables();

                // Handle tab changes
                $('#reportTabs .nav-link').on('click', function() {
                    const reportType = $(this).data('type');
                    loadReportData(reportType);
                });

                // Function to load report data
                function loadReportData(type) {
                    const startDate = $('#start-date').val();
                    const endDate = $('#end-date').val();

                    $.ajax({
                        url: "{{ url('admin/reports/data') }}",
                        type: 'GET',
                        data: {
                            type: type,
                            start_date: startDate,
                            end_date: endDate
                        },
                        success: function(response) {
                            renderReportCards(type, response.cards);
                            renderCharts(type, response.charts);
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Failed to load report data', 'error');
                        }
                    });
                }

                // Function to render report cards
                function renderReportCards(type, cards) {
                    const $container = $(`#${type}-cards`);
                    $container.empty();

                    cards.forEach(card => {
                        const cardStyle = `style="background-image: url('${card.image}');"`;

                        $container.append(`
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 mb-4 custom-report-card" ${cardStyle}>
            <div class="card shadow h-100 py-2 report-card text-white bg-none">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2 text-center">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                ${card.title}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">
                                ${card.value}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-${card.icon} fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `);
                    });
                }
                // Function to render charts
                function renderCharts(type, charts) {
                    // Destroy existing charts if they exist
                    if (window.charts) {
                        Object.values(window.charts).forEach(chart => {
                            if (chart) chart.destroy();
                        });
                    }

                    window.charts = {};

                    // Render each chart based on type
                    if (type === 'user') {
                        renderChartWithNoDataCheck('userOrderStatusChart', charts.order_status, {
                            type: 'doughnut',
                            options: {
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                    }
                                },
                                cutout: '70%',
                            }
                        });

                        renderChartWithNoDataCheck('userMonthlyOrdersChart', charts.monthly_orders, {
                            type: 'bar',
                            options: {
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                    } else if (type === 'order') {
                        renderChartWithNoDataCheck('orderStatusChart', charts.order_status, {
                            type: 'pie',
                            options: {
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                    }
                                },
                            }
                        });

                        renderChartWithNoDataCheck('paymentMethodChart', charts.payment_methods, {
                            type: 'polarArea',
                            options: {
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                    }
                                },
                            }
                        });
                    } else if (type === 'product') {
                        renderChartWithNoDataCheck('topProductsChart', charts.top_products, {
                            type: 'bar',
                            options: {
                                indexAxis: 'y',
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });

                        renderChartWithNoDataCheck('stockStatusChart', charts.stock_status, {
                            type: 'doughnut',
                            options: {
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                    }
                                },
                                cutout: '70%',
                            }
                        });
                    } else if (type === 'shop') {
                        renderChartWithNoDataCheck('topShopsChart', charts.top_shops, {
                            type: 'bar',
                            options: {
                                indexAxis: 'y',
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return '₹' + value;
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return '₹' + context.raw.toLocaleString();
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                // Helper function to render chart with no data check
                function renderChartWithNoDataCheck(chartId, chartData, chartConfig) {
                    const $chartContainer = $(`#${chartId}`).parent();
                    const $noDataMessage = $chartContainer.find('.no-data-chart');

                    if (!chartData || chartData.data.length === 0 || chartData.data.every(val => val === 0)) {
                        $chartContainer.find('canvas').hide();
                        $noDataMessage.show();
                        return;
                    }

                    $noDataMessage.hide();
                    $chartContainer.find('canvas').show();

                    window.charts[chartId] = new Chart(
                        document.getElementById(chartId), {
                            type: chartConfig.type,
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    data: chartData.data,
                                    backgroundColor: chartData.colors,
                                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                                }],
                            },
                            options: chartConfig.options
                        }
                    );
                }

                // Initialize DataTables
                function initializeDataTables() {
                    const startDate = $('#start-date').val();
                    const endDate = $('#end-date').val();

                    // User Table
                    $('.user-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('admin/reports/table') }}",
                            data: function(d) {
                                d.type = 'user';
                                d.start_date = $('#start-date').val();
                                d.end_date = $('#end-date').val();

                                // Ensure filters are included
                                if (filterPopups.user.activeFilterRules) {
                                    d.filters = filterPopups.user.activeFilterRules;
                                    d.filter_logic = filterPopups.user.filterLogic;
                                }

                            }
                        },
                        columns: [{
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'email',
                                name: 'email'
                            },
                            {
                                data: 'phone',
                                name: 'phone'
                            },
                            {
                                data: 'status',
                                name: 'status',
                                orderable: false,
                                render: function(data, type, row) {
                                    return data ? '<span class="badge bg-success">Active</span>' :
                                        '<span class="badge bg-danger">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Order Table
                    $('.order-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('admin/reports/table') }}",
                            data: function(d) {
                                d.type = 'order';
                                d.start_date = $('#start-date').val();
                                d.end_date = $('#end-date').val();

                                // Ensure filters are included
                                if (filterPopups.order.activeFilterRules) {
                                    d.filters = filterPopups.order.activeFilterRules;
                                    d.filter_logic = filterPopups.order.filterLogic;
                                }

                            }
                        },
                        columns: [{
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'order_id',
                                name: 'order_id',
                                render: function(data) {
                                    return '#' + data;
                                }
                            },
                            {
                                data: 'customer',
                                name: 'customer',
                                orderable: false
                            },
                            {
                                data: 'total_amount',
                                name: 'total_amount',
                                render: function(data) {
                                    return '₹' + parseFloat(data).toFixed(2);
                                }
                            },
                            {
                                data: 'order_status',
                                name: 'status',
                                orderable: false,
                                render: function(data) {
                                    const status = data.replace('_', ' ');
                                    const color = getStatusColor(data);
                                    return '<span class="badge" style="background-color: ' + color +
                                        '">' + status.charAt(0).toUpperCase() + status.slice(1) +
                                        '</span>';
                                }
                            }
                        ]
                    });

                    // DeliveryMen Table
                    $('.deliverymans-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('admin/reports/table') }}",
                            data: function(d) {
                                d.type = 'deliverymans';
                                d.start_date = $('#start-date').val();
                                d.end_date = $('#end-date').val();

                                // Ensure filters are included
                                if (filterPopups.deliverymans.activeFilterRules) {
                                    d.filters = filterPopups.user.activeFilterRules;
                                    d.filter_logic = filterPopups.user.filterLogic;
                                }

                            }
                        },
                        columns: [{
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'email',
                                name: 'email'
                            },
                            {
                                data: 'phone',
                                name: 'phone'
                            },
                            {
                                data: 'delivery_location',
                                name: 'delivery_location',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'status',
                                name: 'status',
                                orderable: false,
                                render: function(data, type, row) {
                                    return data ? '<span class="badge bg-success">Active</span>' :
                                        '<span class="badge bg-danger">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // DeliveryMen Table
                    $('.deliverypoints-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('admin/reports/table') }}",
                            data: function(d) {
                                d.type = 'deliverypoints';
                                d.start_date = $('#start-date').val();
                                d.end_date = $('#end-date').val();

                                // Ensure filters are included
                                if (filterPopups.deliverypoints.activeFilterRules) {
                                    d.filters = filterPopups.user.activeFilterRules;
                                    d.filter_logic = filterPopups.user.filterLogic;
                                }

                            }
                        },
                        columns: [{
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'location',
                                name: 'location'
                            },
                            {
                                data: 'sub_location',
                                name: 'sub_location'
                            },
                            {
                                data: 'status',
                                name: 'status',
                                orderable: false,
                                render: function(data, type, row) {
                                    return data ? '<span class="badge bg-success">Active</span>' :
                                        '<span class="badge bg-danger">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Payments Table
                    $('.payments-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('admin/reports/table') }}",
                            data: function(d) {
                                d.type = 'payments';
                                d.start_date = $('#start-date').val();
                                d.end_date = $('#end-date').val();

                                // Ensure filters are included
                                if (filterPopups.payments.activeFilterRules) {
                                    d.filters = filterPopups.payments.activeFilterRules;
                                    d.filter_logic = filterPopups.payments.filterLogic;
                                }

                            }
                        },
                        columns: [{
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'product_name',
                                name: 'name'
                            },
                            {
                                data: 'category',
                                name: 'category',
                                orderable: false
                            },
                            {
                                data: 'price',
                                name: 'price',
                                render: function(data) {
                                    return '₹' + parseFloat(data).toFixed(2);
                                }
                            },
                            {
                                data: 'product_stock',
                                name: 'product_stock'
                            },
                            {
                                data: 'status',
                                name: 'status',
                                orderable: false,
                                render: function(data) {
                                    return data ? '<span class="badge bg-success">Active</span>' :
                                        '<span class="badge bg-danger">Inactive</span>';
                                }
                            }
                        ]
                    });


                    // Product Table
                    $('.product-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('admin/reports/table') }}",
                            data: function(d) {
                                d.type = 'product';
                                d.start_date = $('#start-date').val();
                                d.end_date = $('#end-date').val();

                                // Ensure filters are included
                                if (filterPopups.product.activeFilterRules) {
                                    d.filters = filterPopups.product.activeFilterRules;
                                    d.filter_logic = filterPopups.product.filterLogic;
                                }

                            }
                        },
                        columns: [{
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'product_name',
                                name: 'name'
                            },
                            {
                                data: 'category',
                                name: 'category',
                                orderable: false
                            },
                            {
                                data: 'price',
                                name: 'price',
                                render: function(data) {
                                    return '₹' + parseFloat(data).toFixed(2);
                                }
                            },
                            {
                                data: 'product_stock',
                                name: 'product_stock'
                            },
                            {
                                data: 'status',
                                name: 'status',
                                orderable: false,
                                render: function(data) {
                                    return data ? '<span class="badge bg-success">Active</span>' :
                                        '<span class="badge bg-danger">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Shop Table
                    $('.shop-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('admin/reports/table') }}",
                            data: function(d) {
                                d.type = 'shop';
                                d.start_date = $('#start-date').val();
                                d.end_date = $('#end-date').val();

                                // Ensure filters are included
                                if (filterPopups.shop.activeFilterRules) {
                                    d.filters = filterPopups.shop.activeFilterRules;
                                    d.filter_logic = filterPopups.shop.filterLogic;
                                }

                            }
                        },
                        columns: [{
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'shop_name',
                                name: 'name'
                            },
                            {
                                data: 'owner',
                                name: 'owner',
                                orderable: false
                            },
                            {
                                data: 'location',
                                name: 'address'
                            },
                            {
                                data: 'status',
                                name: 'status',
                                orderable: false,
                                render: function(data) {
                                    return data ? '<span class="badge bg-success">Active</span>' :
                                        '<span class="badge bg-danger">Inactive</span>';
                                }
                            }
                        ]
                    });

                    $('.shoppayout-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('admin/reports/table') }}",
                            data: function(d) {
                                d.type = 'shoppayout'; // Changed from 'shoppayout' to match backend
                                d.start_date = $('#start-date').val();
                                d.end_date = $('#end-date').val();

                                // Ensure filters are included - FIXED
                                if (filterPopups.shoppayout && filterPopups.shoppayout.activeFilterRules) {
                                    d.filters = filterPopups.shoppayout.activeFilterRules;
                                    d.filter_logic = filterPopups.shoppayout.filterLogic;
                                }
                            }
                        },
                        columns: [{
                                data: 'payout_date',
                                name: 'payout_date'
                            },
                            {
                                data: 'shop_name',
                                name: 'shop_name',
                                orderable: false
                            },
                            {
                                data: 'owner_name',
                                name: 'owner_name',
                                orderable: false
                            },
                            {
                                data: 'total_payouts',
                                name: 'total_payouts',
                                render: function(data) {
                                    return '<span class="badge bg-primary">' + data + '</span>';
                                }
                            },
                            {
                                data: 'total_amount',
                                name: 'total_amount',
                                render: function(data) {
                                    return '₹' + parseFloat(data).toFixed(2);
                                }
                            },
                            {
                                data: 'commission_rate',
                                name: 'commission_rate',
                                render: function(data) {
                                    return data + '%';
                                }
                            },
                            {
                                data: 'commission_amount',
                                name: 'commission_amount',
                                render: function(data) {
                                    return '₹' + parseFloat(data).toFixed(2);
                                }
                            },
                            {
                                data: 'payable_amount',
                                name: 'payable_amount',
                                render: function(data) {
                                    return '<span class="fw-bold text-success">₹' + parseFloat(data)
                                        .toFixed(2) + '</span>';
                                }
                            },
                            {
                                data: 'payout_status',
                                name: 'payout_status',
                                orderable: false,
                                render: function(data) {
                                    const statusColors = {
                                        'generated': '#ffc107',
                                        'processing': '#17a2b8',
                                        'paid': '#28a745',
                                        'failed': '#dc3545'
                                    };
                                    const color = statusColors[data] || '#6c757d';
                                    return '<span class="badge" style="background-color: ' + color +
                                        '">' +
                                        data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                                }
                            }
                        ],
                        order: [
                            [0, 'desc']
                        ]
                    });

                    // Initialize filter buttons for each table
                    $('.filter-btn').each(function() {
                        const tableId = $(this).data('table-id');
                        initFilterPopup(tableId.replace('-table', ''), $(`.${tableId}`).DataTable());
                    });
                }

                // Reload DataTables when date range changes
                function reloadDataTables() {
                    const startDate = $('#start-date').val();
                    const endDate = $('#end-date').val();

                    $('.order-table').DataTable().ajax.reload();
                    $('.user-table').DataTable().ajax.reload();
                    $('.product-table').DataTable().ajax.reload();
                    $('.shop-table').DataTable().ajax.reload();
                    $('.shoppayout-table').DataTable().ajax.reload();
                }

                // Initialize filter popup for a table
                function initFilterPopup(type, tableApi) {
                    const popupId = `${type}-filter-popup`;
                    const $filterBtn = $(`.filter-btn[data-table-id="${type}-table"]`);

                    // Remove existing popup if any
                    $(`#${popupId}`).remove();

                    const popupHTML = `
                        <div id="${popupId}" class="filter-popup">
                            <div class="filter-popup-content">
                                <div class="filter-popup-header" style="border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                    <h5 style="margin: 0; font-size: 1.1rem;">Advanced Filters</h5>
                                    <button type="button" class="close btn-close btn-close-white"></button>
                                </div>
                                <div class="filter-popup-body p-3">
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <select class="form-control form-control-sm saved-filters-dropdown">
                                                <option value="">Load Saved Filter...</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-secondary edit-saved-filter-btn" type="button" title="Edit Filter">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-saved-filter-btn" type="button" title="Delete Filter">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="filter-rules-container">
                                        <!-- Rules will be added here -->
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-theme-second-success add-rule-btn">
                                            <i class="fas fa-plus"></i> Add Filter Rule
                                        </button>
                                        <div class="filter-logic-toggle btn-group btn-group-sm">
                                            <button type="button" class="btn btn-theme-success active" data-logic="and">AND (All conditions must be met)</button>
                                            <button type="button" class="btn btn-outline-primary" data-logic="or">OR (Any condition can be met)</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="filter-popup-footer p-3" style="border-top: 1px solid #eee; display: flex; justify-content: space-between;">
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-danger clear-filters-btn">
                                            <i class="fas fa-trash"></i> Clear All
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-theme-second-success me-2 save-filter-btn">
                                            <i class="fas fa-save"></i> Save Filter
                                        </button>
                                        <button type="button" class="btn add-btn text-white apply-filter-btn">
                                            <i class="fas fa-check"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                    $('body').append(popupHTML);

                    const $popup = $(`#${popupId}`);

                    // Store filter state
                    filterPopups[type] = {
                        activeFilterRules: [],
                        filterLogic: 'and',
                        $popup: $popup,
                        currentEditingFilterId: null
                    };

                    // Position and show/hide popup
                    function positionPopup() {
                        const offset = $filterBtn.offset();
                        const popupWidth = 800;
                        const windowWidth = $(window).width();

                        let leftPos = offset.left;

                        if (leftPos + popupWidth > windowWidth) {
                            leftPos = windowWidth - popupWidth - 20;
                        }

                        $popup.css({
                            'top': offset.top + $filterBtn.outerHeight() + 5 + 'px',
                            'left': leftPos + 'px'
                        });
                    }

                    $filterBtn.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        $('.filter-popup').not($popup).hide();

                        positionPopup();
                        $popup.toggle();

                        filterPopups[type].currentEditingFilterId = null;
                        $popup.find('.save-filter-btn').html('<i class="fas fa-save"></i> Save Filter');

                        if ($popup.find('.filter-rule').length === 0) {
                            addFilterRule(type, tableApi);
                        }

                        loadSavedFilters(type);
                    });

                    $popup.find('.close').on('click', function() {
                        $popup.hide();
                        filterPopups[type].currentEditingFilterId = null;
                    });

                    $popup.find('.add-rule-btn').on('click', function() {
                        addFilterRule(type, tableApi);
                    });

                    $popup.find('.filter-logic-toggle button').on('click', function() {
                        $(this).addClass('active').siblings().removeClass('active');
                        filterPopups[type].filterLogic = $(this).data('logic');
                        updateRuleConnectors(type);
                    });

                    $popup.find('.clear-filters-btn').on('click', function() {
                        $popup.find('.filter-rules-container').empty();
                        filterPopups[type].activeFilterRules = [];
                        filterPopups[type].filterLogic = 'and';
                        tableApi.ajax.reload();
                        updateFilterBadge(type);
                        $popup.hide();
                        filterPopups[type].currentEditingFilterId = null;
                    });

                    $popup.find('.apply-filter-btn').on('click', function() {
                        const rules = collectFilterRules(type);

                        const logic = $popup.find('.filter-logic-toggle .active').data('logic') || 'and';

                        if (rules.length === 0) {
                            Swal.fire('Warning', 'Please add at least one filter rule', 'warning');
                            return;
                        }

                        filterPopups[type].activeFilterRules = rules;
                        filterPopups[type].filterLogic = logic;

                        tableApi.ajax.reload();
                        updateFilterBadge(type);
                        $popup.hide();
                    });

                    $popup.find('.save-filter-btn').on('click', function() {
                        const rules = collectFilterRules(type);
                        const logic = $popup.find('.filter-logic-toggle .active').data('logic') || 'and';
                        const filterId = filterPopups[type].currentEditingFilterId;
                        const isEditing = !!filterId;

                        Swal.fire({
                            title: isEditing ? 'Update Filter' : 'Save Filter',
                            input: 'text',
                            inputLabel: 'Filter Name',
                            inputValue: isEditing ? $popup.find(
                                '.saved-filters-dropdown option:selected').text() : '',
                            showCancelButton: true,
                            confirmButtonText: isEditing ? 'Update' : 'Save',
                            preConfirm: (name) => {
                                if (!name) {
                                    Swal.showValidationMessage('Filter name is required');
                                }
                                return name;
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const url = isEditing ? `/admin/filters/${filterId}` : '/admin/filters';
                                const method = isEditing ? 'PUT' : 'POST';

                                $.ajax({
                                    url: url,
                                    type: method,
                                    data: {
                                        name: result.value,
                                        page: 'reports',
                                        table_type: type,
                                        logic: logic,
                                        rules: rules,
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function() {
                                        Swal.fire(
                                            isEditing ? 'Updated!' : 'Saved!',
                                            `Your filter has been ${isEditing ? 'updated' : 'saved'}.`,
                                            'success'
                                        );
                                        loadSavedFilters(type);
                                        filterPopups[type].currentEditingFilterId = null;
                                        $popup.find('.save-filter-btn').html(
                                            '<i class="fas fa-save"></i> Save Filter');
                                    },
                                    error: function(xhr) {
                                        Swal.fire('Error', xhr.responseJSON?.message ||
                                            'Failed to save filter', 'error');
                                    }
                                });
                            }
                        });
                    });

                    $popup.find('.edit-saved-filter-btn').on('click', function() {
                        const filterId = $popup.find('.saved-filters-dropdown').val();
                        if (filterId) {
                            filterPopups[type].currentEditingFilterId = filterId;
                            $popup.find('.save-filter-btn').html('<i class="fas fa-save"></i> Update Filter');

                            $.get(`/admin/filters/${filterId}`, function(filter) {
                                $popup.find('.filter-rules-container').empty();

                                $popup.find(
                                    `.filter-logic-toggle button[data-logic="${filter.filters.logic}"]`
                                ).click();

                                filter.filters.rules.forEach(rule => {
                                    addFilterRule(type, tableApi);
                                    const $lastRule = $popup.find('.filter-rule').last();

                                    $lastRule.find('.rule-column').val(rule.column).change();

                                    setTimeout(() => {
                                        $lastRule.find('.rule-operator').val(rule
                                            .operator);

                                        if (rule.operator === 'between') {
                                            $lastRule.find('.rule-value-start').val(rule
                                                .value.start);
                                            $lastRule.find('.rule-value-end').val(rule
                                                .value.end);
                                        } else if (Array.isArray(rule.value)) {
                                            $lastRule.find('.rule-value').val(rule
                                                .value).trigger('change');
                                        } else {
                                            $lastRule.find('.rule-value').val(rule
                                                .value);
                                        }
                                    }, 100);
                                });
                            });
                        } else {
                            Swal.fire('Error', 'No filter selected', 'error');
                        }
                    });

                    $popup.find('.saved-filters-dropdown').on('change', function() {
                        const filterId = $(this).val();
                        if (filterId) {
                            $.get(`/admin/filters/${filterId}`, function(filter) {
                                $popup.find('.filter-rules-container').empty();

                                $popup.find(
                                    `.filter-logic-toggle button[data-logic="${filter.filters.logic}"]`
                                ).click();
                                filterPopups[type].filterLogic = filter.filters.logic;

                                filter.filters.rules.forEach(rule => {
                                    addFilterRule(type, tableApi);
                                    const $lastRule = $popup.find('.filter-rule').last();

                                    $lastRule.find('.rule-column').val(rule.column).change();

                                    setTimeout(() => {
                                        $lastRule.find('.rule-operator').val(rule
                                            .operator);

                                        if (rule.operator === 'between') {
                                            $lastRule.find('.rule-value-start').val(rule
                                                .value.start);
                                            $lastRule.find('.rule-value-end').val(rule
                                                .value.end);
                                        } else if (Array.isArray(rule.value)) {
                                            $lastRule.find('.rule-value').val(rule
                                                .value).trigger('change');
                                        } else {
                                            $lastRule.find('.rule-value').val(rule
                                                .value);
                                        }
                                    }, 100);
                                });

                                filterPopups[type].currentEditingFilterId = filterId;
                                $popup.find('.save-filter-btn').html(
                                    '<i class="fas fa-save"></i> Update Filter');

                                filterPopups[type].activeFilterRules = filter.filters.rules;
                                tableApi.ajax.reload();
                                updateFilterBadge(type);
                            }).fail(function(xhr) {
                                Swal.fire('Error', 'Failed to load filter', 'error');
                            });
                        }
                    });

                    $popup.find('.delete-saved-filter-btn').on('click', function() {
                        const filterId = $popup.find('.saved-filters-dropdown').val();
                        if (filterId) {
                            Swal.fire({
                                title: 'Delete Filter?',
                                text: "You won't be able to revert this!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                confirmButtonText: 'Delete'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: `/admin/filters/${filterId}`,
                                        type: 'DELETE',
                                        data: {
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function() {
                                            Swal.fire('Deleted!',
                                                'Your filter has been deleted.',
                                                'success');
                                            loadSavedFilters(type);
                                            filterPopups[type].currentEditingFilterId =
                                                null;
                                        },
                                        error: function(xhr) {
                                            Swal.fire('Error', xhr.responseJSON?.message ||
                                                'Failed to delete filter', 'error');
                                        }
                                    });
                                }
                            });
                        } else {
                            Swal.fire('Error', 'No filter selected', 'error');
                        }
                    });

                    function loadSavedFilters(type) {
                        $.get('/admin/filters', {
                            page: 'reports',
                            table_type: type
                        }, function(filters) {
                            const $dropdown = $popup.find('.saved-filters-dropdown');
                            $dropdown.empty();
                            $dropdown.append('<option value="">Load Saved Filter...</option>');

                            filters.forEach(filter => {
                                $dropdown.append(
                                    `<option value="${filter.id}">${filter.name}</option>`);
                            });
                        }).fail(function(xhr) {});
                    }

                    function collectFilterRules(type) {
                        const rules = [];
                        const $popup = filterPopups[type].$popup;

                        $popup.find('.filter-rule').each(function() {
                            const $rule = $(this);
                            const column = $rule.find('.rule-column').val();
                            const operator = $rule.find('.rule-operator').val();

                            if (!column || !operator) {
                                return;
                            }

                            let value;
                            if (operator === 'between') {
                                const startVal = $rule.find('.rule-value-start').val();
                                const endVal = $rule.find('.rule-value-end').val();
                                if (!startVal || !endVal) {
                                    return;
                                }
                                value = {
                                    start: startVal,
                                    end: endVal
                                };
                            } else if ($rule.find('.rule-value').is('select[multiple]')) {
                                value = $rule.find('.rule-value').val() || [];
                                if (value.length === 0) {
                                    return;
                                }
                            } else {
                                value = $rule.find('.rule-value').val();
                                if (!value && value !== 0 && operator !== 'is_empty' && operator !==
                                    'is_not_empty') {
                                    return;
                                }
                            }

                            rules.push({
                                column: column,
                                operator: operator,
                                value: value
                            });
                        });

                        return rules;
                    }
                }

                function addFilterRule(type, tableApi) {
                    const $popup = filterPopups[type].$popup;
                    const columns = tableApi.columns().header().toArray();

                    const columnOptions = columns.map(function(col, index) {
                        const colData = tableApi.column(index).dataSrc();
                        const colTitle = $(col).text().trim();
                        if (colData && colData !== 'action' && colData !== 'kyc_details' && colData !==
                            'bank_details') {
                            return `<option value="${colData}">${colTitle}</option>`;
                        }
                        return '';
                    }).filter(option => option !== '').join('');

                    const ruleCount = $popup.find('.filter-rule').length;
                    const showConnector = ruleCount > 0;

                    const ruleHTML = `
                        <div class="filter-rule mb-2 p-2" style="border: 1px solid #eee; border-radius: 4px; background: #f9f9f9;">
                            <div class="row align-items-center">
                                ${showConnector ? `<div class="col-auto">
                                                                            <span class="rule-connector" style="font-weight: bold; color: #666;">${filterPopups[type].filterLogic}</span>
                                                                        </div>` : ''}
                                <div class="col">
                                    <select class="form-control form-control-sm rule-column">
                                        <option value="">Select Column</option>
                                        ${columnOptions}
                                    </select>
                                </div>
                                <div class="col">
                                    <select class="form-control form-control-sm rule-operator">
                                        <option value="">Select Operator</option>
                                    </select>
                                </div>
                                <div class="col rule-value-container">
                                    <input type="text" class="form-control form-control-sm rule-value" placeholder="Value">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-rule-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;

                    $popup.find('.filter-rules-container').append(ruleHTML);

                    const $newRule = $popup.find('.filter-rule').last();
                    const $columnSelect = $newRule.find('.rule-column');

                    $columnSelect.on('change', function() {
                        updateOperatorOptions(type, $(this), tableApi);
                    });

                    $newRule.find('.remove-rule-btn').on('click', function() {
                        $(this).closest('.filter-rule').remove();
                        updateRuleConnectors(type);

                        // Add a new rule if none exist
                        if ($popup.find('.filter-rule').length === 0) {
                            addFilterRule(type, tableApi);
                        }
                    });
                }

                function updateOperatorOptions(type, $columnSelect, tableApi) {
                    const columnName = $columnSelect.val();
                    const $rule = $columnSelect.closest('.filter-rule');
                    const $operatorSelect = $rule.find('.rule-operator');
                    const $valueContainer = $rule.find('.rule-value-container');

                    $operatorSelect.empty().append('<option value="">Select Operator</option>');
                    $valueContainer.empty();

                    if (!columnName) {
                        return;
                    }

                    const baseOperators = [{
                            value: 'equals',
                            text: 'equals'
                        },
                        {
                            value: 'not_equals',
                            text: 'not equals'
                        }
                    ];

                    if (columnName === 'status') {
                        baseOperators.forEach(op => $operatorSelect.append(
                            `<option value="${op.value}">${op.text}</option>`));

                        const $select = $(`
                            <select class="form-control form-control-sm rule-value">
                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        `);
                        $valueContainer.append($select);
                    } else if (columnName === 'payment_status') {
                        baseOperators.forEach(op => $operatorSelect.append(
                            `<option value="${op.value}">${op.text}</option>`));

                        const $select = $(`
                            <select class="form-control form-control-sm rule-value">
                                <option value="">Select Status</option>
                                <option value="Razorpay">Razorpay</option>
                                <option value="COD">Cash on delivery</option>
                            </select>
                        `);
                        $valueContainer.append($select);
                    } else if (columnName === 'payout_status') {
                        baseOperators.forEach(op => $operatorSelect.append(
                            `<option value="${op.value}">${op.text}</option>`));

                        const $select = $(`
                            <select class="form-control form-control-sm rule-value">
                                <option value="">Select Status</option>
                                <option value="Generated">Generated</option>
                                <option value="Paid">Paid</option>
                                <option value="Failed">Failed</option>
                            </select>
                        `);
                        $valueContainer.append($select);
                    } else if (columnName === 'suspension') {
                        baseOperators.forEach(op => $operatorSelect.append(
                            `<option value="${op.value}">${op.text}</option>`));

                        const $select = $(`
                            <select class="form-control form-control-sm rule-value">
                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">Suspended</option>
                            </select>
                        `);
                        $valueContainer.append($select);
                    } else if (columnName === 'order_status') {
                        baseOperators.forEach(op => $operatorSelect.append(
                            `<option value="${op.value}">${op.text}</option>`));

                        const $select = $(`
                            <select class="form-control form-control-sm rule-value">
                                <option value="">Select Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Inprogress">Inprogress</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        `);
                        $valueContainer.append($select);

                    } else if (columnName === 'date' || columnName === 'created_at') {
                        const dateOperators = [
                            ...baseOperators,
                            {
                                value: 'greater_than',
                                text: 'after'
                            },
                            {
                                value: 'less_than',
                                text: 'before'
                            },
                            {
                                value: 'between',
                                text: 'between'
                            }
                        ];

                        dateOperators.forEach(op => $operatorSelect.append(
                            `<option value="${op.value}">${op.text}</option>`
                        ));

                        const $valueInput = $(
                            '<input type="text" class="form-control form-control-sm rule-value datepicker" placeholder="Select date">'
                        );
                        $valueContainer.append($valueInput);

                        flatpickr($valueInput[0], {
                            enableTime: true,
                            dateFormat: "Y-m-d H:i:S",
                            time_24hr: true
                        });

                        $operatorSelect.on('change', function() {
                            if ($(this).val() === 'between') {
                                $valueContainer.empty().append(`
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="text" class="form-control form-control-sm rule-value-start datepicker" placeholder="Start date">
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <span>and</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control form-control-sm rule-value-end datepicker" placeholder="End date">
                                        </div>
                                    </div>
                                `);

                                flatpickr('.rule-value-start, .rule-value-end', {
                                    enableTime: true,
                                    dateFormat: "Y-m-d H:i:S",
                                    time_24hr: true
                                });
                            }
                        });
                    } else if (columnName === 'total_amount' || columnName === 'price' || columnName ===
                        'product_stock') {
                        const numericOperators = [
                            ...baseOperators,
                            {
                                value: 'greater_than',
                                text: 'greater than'
                            },
                            {
                                value: 'less_than',
                                text: 'less than'
                            },
                            {
                                value: 'between',
                                text: 'between'
                            }
                        ];

                        numericOperators.forEach(op => $operatorSelect.append(
                            `<option value="${op.value}">${op.text}</option>`));

                        $valueContainer.append(
                            '<input type="number" class="form-control form-control-sm rule-value" placeholder="Enter value" step="0.01">'
                        );

                        $operatorSelect.on('change', function() {
                            if ($(this).val() === 'between') {
                                $valueContainer.empty().append(`
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="number" class="form-control form-control-sm rule-value-start" placeholder="Min" step="0.01">
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <span>and</span>
                                        </div>
                                        <div class="col">
                                            <input type="number" class="form-control form-control-sm rule-value-end" placeholder="Max" step="0.01">
                                        </div>
                                    </div>
                                `);
                            } else {
                                $valueContainer.empty().append(
                                    '<input type="number" class="form-control form-control-sm rule-value" placeholder="Enter value" step="0.01">'
                                );
                            }
                        });
                    } else if (columnName === 'total_orders') {
                        baseOperators.push({
                            value: 'contains',
                            text: 'contains'
                        }, {
                            value: 'starts_with',
                            text: 'starts with'
                        }, {
                            value: 'ends_with',
                            text: 'ends with'
                        });

                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        var $input = $(
                            '<input type="text" class="form-control form-control-sm rule-value" placeholder="Enter value">'
                        );
                        $valueContainer.append($input);

                        $.get(tableApi.ajax.url(), {
                            get_states: true
                        }, function(states) {
                            var $datalist = $('<datalist id="states-list">');
                            states.forEach(function(state) {
                                $datalist.append($('<option>').val(state));
                            });
                            $input.attr('list', 'states-list');
                            $valueContainer.append($datalist);
                        });
                    } else {
                        const textOperators = [
                            ...baseOperators,
                            {
                                value: 'contains',
                                text: 'contains'
                            },
                            {
                                value: 'starts_with',
                                text: 'starts with'
                            },
                            {
                                value: 'ends_with',
                                text: 'ends with'
                            },
                            {
                                value: 'is_empty',
                                text: 'is empty'
                            },
                            {
                                value: 'is_not_empty',
                                text: 'is not empty'
                            }
                        ];

                        textOperators.forEach(op => $operatorSelect.append(
                            `<option value="${op.value}">${op.text}</option>`));
                        $valueContainer.append(
                            '<input type="text" class="form-control form-control-sm rule-value" placeholder="Enter value">'
                        );

                        $operatorSelect.on('change', function() {
                            const operator = $(this).val();
                            if (operator === 'is_empty' || operator === 'is_not_empty') {
                                $valueContainer.find('.rule-value').prop('disabled', true).val('');
                            } else {
                                $valueContainer.find('.rule-value').prop('disabled', false);
                            }
                        });
                    }
                }

                function updateRuleConnectors(type) {
                    const $popup = filterPopups[type].$popup;
                    const logic = filterPopups[type].filterLogic || 'and';
                    $popup.find('.rule-connector').text(logic);
                }

                function updateFilterBadge(type) {
                    const count = filterPopups[type] && filterPopups[type].activeFilterRules ? filterPopups[type]
                        .activeFilterRules.length : 0;
                    const $badge = $(`.filter-btn[data-table-id="${type}-table"] .filter-badge`);

                    if (count > 0) {
                        $badge.show().text(count);
                    } else {
                        $badge.hide();
                    }
                }

                function getStatusColor(status) {
                    const colors = {
                        'pending': '#f6c23e',
                        'inprogress': '#36b9cc',
                        'completed': '#1cc88a',
                        'cancelled': '#e74a3b'
                    };

                    return colors[status.toLowerCase()] || '#858796';
                }

                // Export table to Excel
                $(document).on('click', '.export-table', function() {
                    const tableId = $(this).data('table-id');
                    const $table = $(`.${tableId}`);

                    // Get the DataTable instance
                    const table = $table.DataTable();

                    // Get the filtered data
                    const data = table.rows({
                        search: 'applied'
                    }).data().toArray();

                    // Prepare the Excel data
                    const excelData = [
                        // Headers
                        $table.find('thead tr').first().find('th').map(function() {
                            return $(this).text();
                        }).get(),

                        // Data rows
                        ...data.map(row => {
                            return $table.find('thead tr').first().find('th').map(function(i, el) {
                                const colName = table.column(i).dataSrc();
                                // Handle HTML content in cells
                                if (typeof row[colName] === 'string' && row[colName].startsWith(
                                        '<')) {
                                    const temp = $('<div>').html(row[colName]);
                                    return temp.text();
                                }
                                return row[colName] || '';
                            }).get();
                        })
                    ];

                    // Create workbook
                    const wb = XLSX.utils.book_new();
                    const ws = XLSX.utils.aoa_to_sheet(excelData);
                    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

                    // Generate file and download
                    const fileName =
                        `${tableId.replace('-table', '')}_report_${moment().format('YYYY-MM-DD')}.xlsx`;
                    XLSX.writeFile(wb, fileName);
                });

                // Export chart to image
                $(document).on('click', '.export-chart', function() {
                    const chartId = $(this).data('chart-id');
                    const chart = window.charts[chartId];

                    if (!chart) {
                        Swal.fire('Error', 'No chart data to export', 'error');
                        return;
                    }

                    // Create a temporary canvas to ensure high quality export
                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = chart.canvas.width;
                    tempCanvas.height = chart.canvas.height;
                    const tempCtx = tempCanvas.getContext('2d');
                    tempCtx.fillStyle = 'white';
                    tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                    tempCtx.drawImage(chart.canvas, 0, 0);

                    // Convert to blob and download
                    tempCanvas.toBlob(function(blob) {
                        const a = document.createElement('a');
                        a.href = URL.createObjectURL(blob);
                        a.download = `${chartId}_${moment().format('YYYY-MM-DD')}.png`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    }, 'image/png');
                });

                // Close popups when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.filter-popup').length && !$(e.target).closest('.filter-btn')
                        .length) {
                        $('.filter-popup').hide();
                    }
                });
            });
        </script>
    @endpush
@endsection
