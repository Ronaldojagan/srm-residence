@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    @endpush

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <span class="text-dark page-title-main">Resident & User Profile</span>
                    </h4>
                    <div class="page-title-right">
                        <a href="{{ url('/admin/users') }}" class="btn add-btn text-white">
                            <i class="mdi mdi-keyboard-backspace me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="user-view-stats-card1">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="user-view-stat-icon-container">
                                <i class="mdi mdi-cart-outline"></i>
                            </div>
                            <div>
                                <div class="user-view-stat-value" id="totalOrders">{{ $stats['total_orders'] ?? 0 }}</div>
                                <div class="user-view-stat-label">Total Orders</div>
                                <div class="user-view-stat-info">
                                    <i class="mdi mdi-information-outline me-1"></i> All completed orders
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="user-view-stats-card2">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="user-view-stat-icon-container">
                                <i class="mdi mdi-cash-multiple"></i>
                            </div>
                            <div>
                                <div class="user-view-stat-value" id="totalPayments">
                                    ₹{{ number_format($stats['total_payments'] ?? 0, 2) }}</div>
                                <div class="user-view-stat-label">Total Payments</div>
                                <div class="user-view-stat-info">
                                    <i class="mdi mdi-information-outline me-1"></i> Sum of all orders
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="user-view-stats-card1">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="user-view-stat-icon-container">
                                <i class="mdi mdi-map-marker-outline"></i>
                            </div>
                            <div>
                                <div class="user-view-stat-value" id="totalAddresses">{{ $stats['total_addresses'] ?? 0 }}
                                </div>
                                <div class="user-view-stat-label">Saved Addresses</div>
                                <div class="user-view-stat-info">
                                    <i class="mdi mdi-information-outline me-1"></i> Delivery locations
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="user-view-stats-card2">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="user-view-stat-icon-container">
                                <i class="mdi mdi-account-clock-outline"></i>
                            </div>
                            <div>
                                <div class="user-view-stat-value">{{ $user->created_at->diffForHumans() }}</div>
                                <div class="user-view-stat-label">Member Since</div>
                                <div class="user-view-stat-info">
                                    <i class="mdi mdi-calendar-month me-1"></i> {{ $user->created_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Profile Section -->
            <div class="col-lg-4">
                <div class="card user-view-profile-card">
                    <div class="card-body text-center">
                        <div class="user-view-profile-img-container">
                            @if ($user->image == 'avatar.jpg')
                                <img src="{{ url('img/avatar.jpg') }}" alt="User Image" class="rounded-circle img-thumbnail"
                                    width="150">
                            @else
                                <img src="{{ url('storage/' . $user->image) }}" alt="User Image"
                                    class="rounded-circle img-thumbnail" width="150">
                            @endif
                            <div class="user-view-status-badge bg-{{ $user->status ? 'success' : 'danger' }}">
                                <i class="mdi mdi-{{ $user->status ? 'check' : 'close' }} text-white"></i>
                            </div>
                        </div>

                        <h3 class="mb-1">{{ $user->name }}</h3>
                        <p class="text-muted mb-3">{{ $user->email }}</p>

                        <div class="d-flex justify-content-center mb-3">
                            <button class="btn add-btn text-white me-2" data-bs-toggle="modal"
                                data-bs-target="#updateStatusModal">
                                <i class="mdi mdi-account-edit me-1"></i> Update Status
                            </button>
                        </div>

                        <hr>

                        <div class="text-start">
                            <h5 class="mb-3"><i class="mdi mdi-information-outline me-1 text-primary"></i> Basic
                                Information</h5>
                            <div class="mb-2">
                                <p class="mb-2"><i class="mdi mdi-phone me-1 text-muted"></i> <strong>Phone:</strong>
                                    {{ $user->phone ?? 'N/A' }}</p>
                                <p class="mb-2"><i class="mdi mdi-calendar me-1 text-muted"></i>
                                    <strong>Registered:</strong> {{ $user->created_at->format('d M Y') }}</p>
                                {{-- <p class="mb-2"><i class="mdi mdi-clock-outline me-1 text-muted"></i> <strong>Last
                                        Login:</strong>
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</p> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-8">
                <!-- Date Range Picker -->
                <div class="card user-view-profile-card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label text-dark mb-1">Filter by Date Range</label>
                                <div class="input-form" id="reportrange"
                                    style="cursor: pointer; padding: 10px; border: 1px solid #ced4da; border-radius: 30px; width: 100%; text-align:center;">
                                    <i class="mdi mdi-calendar-range me-1"></i>
                                    <span></span> <i class="mdi mdi-menu-down float-end"></i>
                                </div>
                                <input type="hidden" id="startDate" name="start_date">
                                <input type="hidden" id="endDate" name="end_date">
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-theme-second-success me-2" id="resetDateRange">
                                        <i class="mdi mdi-refresh me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="card user-view-profile-card h-100">
                            <div class="card-header bg-transparent border-bottom">
                                <h5 class="mb-0"><i class="mdi mdi-chart-line me-1 text-primary"></i> Orders Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="user-view-chart-container">
                                    @if (!empty($orderChartData['labels']) && count(array_filter($orderChartData['data'])) > 0)
                                        <canvas id="ordersChart"></canvas>
                                    @else
                                        <div class="user-view-no-data-chart">
                                            <i class="mdi mdi-chart-line"></i>
                                            <h5>No Order Data</h5>
                                            <p>There is no order data available for the selected time period</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card user-view-profile-card h-100">
                            <div class="card-header bg-transparent border-bottom">
                                <h5 class="mb-0"><i class="mdi mdi-chart-pie me-1 text-primary"></i> Order Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="user-view-chart-container">
                                    @if (!empty($statusChartData['labels']) && count(array_filter($statusChartData['data'])) > 0)
                                        <canvas id="statusChart"></canvas>
                                    @else
                                        <div class="user-view-no-data-chart">
                                            <i class="mdi mdi-chart-pie"></i>
                                            <h5>No Status Data</h5>
                                            <p>There is no status data available for the selected time period</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <div class="row">
            <!-- Addresses Section -->
            <div class="card user-view-profile-card mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0"><i class="mdi mdi-map-marker-multiple me-1 text-primary"></i> Saved Addresses</h5>
                </div>
                <div class="card-body">
                    @if ($addresses->count() > 0)
                        <div class="row">
                            @php $addressCounter = 1; @endphp
                            @foreach ($addresses as $address)
                                <div class="col-md-4 mb-4">
                                    <div class="user-view-address-card">
                                        <div class="card-body position-relative">
                                            <span class="user-view-address-type-badge"
                                                style="--card-color: {{ $address->is_default ? '#EE7926' : '#0EA1B1' }}">
                                                @if ($address->is_default)
                                                    Default
                                                @else
                                                    Address {{ $addressCounter++ }}
                                                @endif
                                            </span>

                                            <h6 class="card-title mb-3">{{ $address->name }}</h6>

                                            <div class="mb-3">
                                                <p class="mb-1"><i class="mdi mdi-home-outline me-1 text-muted"></i>
                                                    {{ $address->flat_no }}, {{ $address->area_name }}</p>
                                                @if($address->landmark)
                                                    <p class="mb-1"><i
                                                            class="mdi mdi-map-marker-radius me-1 text-muted"></i>
                                                        {{ $address->landmark }}</p>
                                                @endif
                                                <p class="mb-1"><i class="mdi mdi-city me-1 text-muted"></i>
                                                    {{ $address->city }}, {{ $address->district }} -
                                                    {{ $address->pincode }}</p>
                                                <p class="mb-0"><i class="mdi mdi-phone me-1 text-muted"></i>
                                                    {{ $address->phone }}</p>
                                            </div>

                                            @if ($address->lat && $address->long)
                                                <div class="user-view-map-preview"
                                                    onclick="openMap({{ $address->lat }}, {{ $address->long }})">
                                                    <div id="map-{{ $address->id }}" style="width:100%; height:100%;">
                                                    </div>
                                                    <div class="user-view-map-overlay">
                                                        <i class="mdi mdi-map-marker me-1"></i> Click to view full map
                                                    </div>
                                                </div>
                                            @else
                                                <div class="user-view-map-preview bg-light text-center">
                                                    <i class="mdi mdi-map-marker-off"
                                                        style="font-size: 48px; color: #adb5bd;"></i>
                                                    <p class="text-muted m-0">No location data</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-map-marker-off text-muted" style="font-size: 48px;"></i>
                            <h5 class="mt-2 text-muted">No Addresses Found</h5>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order History Section -->
            <div class="card user-view-profile-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="mdi mdi-history me-1 text-primary"></i> Order History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-border">
                        <table id="orders-table" class="table table-striped data-table">
                            <thead class="theme-menu-color">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Status Update Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="mdi mdi-account-edit me-1"></i> Update User Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('admin/users/' . Crypt::encrypt($user->id)) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $user->status == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $user->status == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items Modal -->
    <div class="modal fade" id="orderItemsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">
                        <i class="mdi mdi-cart-outline me-1"></i> Order Items
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

    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

        <script>
            // Initialize mini maps for addresses
            function initMiniMaps() {
                @foreach ($addresses as $address)
                    @if ($address->lat && $address->long)
                        initMap({{ $address->id }}, {{ $address->lat }}, {{ $address->long }});
                    @endif
                @endforeach
            }

            function initMap(id, lat, lng) {
                const mapElement = document.getElementById(`map-${id}`);
                if (!mapElement) return;

                const map = L.map(mapElement).setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
                }).addTo(map);

                L.marker([lat, lng]).addTo(map);
            }


            function openMap(lat, lng) {
                const url = `https://www.google.com/maps?q=${lat},${lng}`;
                window.open(url, '_blank');
            }

            $(document).ready(function() {
                // Initialize mini maps
                initMiniMaps();

                // Initialize date range picker
                const start = moment().subtract(29, 'days');
                const end = moment();

                function updateChartsAndData(startDate, endDate) {
                    // Update stats cards via AJAX
                    $.ajax({
                        url: "{{ url('admin/users/stats/' . Crypt::encrypt($user->id)) }}",
                        type: "GET",
                        data: {
                            start_date: startDate.format('YYYY-MM-DD'),
                            end_date: endDate.format('YYYY-MM-DD')
                        },
                        success: function(response) {
                            $('#totalOrders').text(response.total_orders);
                            $('#totalPayments').text('₹' + response.total_payments.toLocaleString('en-IN'));
                            $('#totalAddresses').text(response.total_addresses);

                            // Update charts if they exist
                            if (window.ordersChart && response.order_chart.labels.length > 0) {
                                window.ordersChart.data.labels = response.order_chart.labels;
                                window.ordersChart.data.datasets[0].data = response.order_chart.data;
                                window.ordersChart.update();
                            }

                            if (window.statusChart && response.status_chart.labels.length > 0) {
                                window.statusChart.data.labels = response.status_chart.labels;
                                window.statusChart.data.datasets[0].data = response.status_chart.data;
                                window.statusChart.update();
                            }

                            // Reload DataTable
                            if ($.fn.DataTable.isDataTable('#orders-table')) {
                                $('#orders-table').DataTable().ajax.reload();
                            }
                        }
                    });
                }

                $('#reportrange').daterangepicker({
                    startDate: start,
                    endDate: end,
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
                        'All Time': [moment().subtract(10, 'years'), moment().add(10, 'years')]
                    },
                    opens: 'left',
                    alwaysShowCalendars: true,
                    autoUpdateInput: true,
                    locale: {
                        format: 'D MMM, YYYY'
                    }
                }, function(start, end, label) {
                    $('#reportrange span').html(start.format('D MMM, YYYY') + ' - ' + end.format(
                    'D MMM, YYYY'));
                    $('#startDate').val(start.format('YYYY-MM-DD'));
                    $('#endDate').val(end.format('YYYY-MM-DD'));

                    updateChartsAndData(start, end);
                });

                $('#reportrange span').html(start.format('D MMM, YYYY') + ' - ' + end.format('D MMM, YYYY'));
                $('#startDate').val(start.format('YYYY-MM-DD'));
                $('#endDate').val(end.format('YYYY-MM-DD'));

                // Reset date range
                $('#resetDateRange').click(function() {
                    $('#reportrange').data('daterangepicker').setStartDate(moment().subtract(29, 'days'));
                    $('#reportrange').data('daterangepicker').setEndDate(moment());
                    $('#reportrange span').html(
                        moment().subtract(29, 'days').format('D MMM, YYYY') + ' - ' +
                        moment().format('D MMM, YYYY')
                    );
                    $('#startDate').val(moment().subtract(29, 'days').format('YYYY-MM-DD'));
                    $('#endDate').val(moment().format('YYYY-MM-DD'));

                    updateChartsAndData(moment().subtract(29, 'days'), moment());
                });

                // Initialize DataTable
                $('#orders-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('admin/users/orders/' . Crypt::encrypt($user->id)) }}",
                        data: function(d) {
                            d.start_date = $('#startDate').val();
                            d.end_date = $('#endDate').val();
                        }
                    },
                    columns: [{
                            data: 'order_no',
                            name: 'order_no'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'items_count',
                            name: 'items_count',
                            searchable: false,
                            orderable: false,
                            className: 'text-center'
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
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                    ],
                    order: [
                        [1, 'desc']
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search orders...",
                        lengthMenu: "Show _MENU_ orders",
                        zeroRecords: "No orders found",
                        info: "Showing _START_ to _END_ of _TOTAL_ orders",
                        infoEmpty: "No orders available",
                        infoFiltered: "(filtered from _MAX_ total orders)"
                    }
                });

                // Initialize Charts
                @if (!empty($orderChartData['labels']) && count(array_filter($orderChartData['data'])) > 0)
                    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
                    window.ordersChart = new Chart(ordersCtx, {
                        type: 'line',
                        data: {
                            labels: @json($orderChartData['labels']),
                            datasets: [{
                                label: 'Orders',
                                data: @json($orderChartData['data']),
                                backgroundColor: 'rgba(74, 108, 247, 0.1)',
                                borderColor: '#4a6cf7',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#4a6cf7',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#4a6cf7',
                                    titleFont: {
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 14
                                    },
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        drawBorder: false,
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    ticks: {
                                        padding: 10
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false,
                                        drawBorder: false
                                    },
                                    ticks: {
                                        padding: 10
                                    }
                                }
                            }
                        }
                    });
                @endif

                @if (!empty($statusChartData['labels']) && count(array_filter($statusChartData['data'])) > 0)
                    const statusCtx = document.getElementById('statusChart').getContext('2d');
                    window.statusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: @json($statusChartData['labels']),
                            datasets: [{
                                data: @json($statusChartData['data']),
                                backgroundColor: [
                                    '#4a6cf7',
                                    '#28a745',
                                    '#ffc107',
                                    '#dc3545',
                                    '#6c757d'
                                ],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        pointStyle: 'circle'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#4a6cf7',
                                    bodyFont: {
                                        size: 14
                                    },
                                    padding: 12,
                                    cornerRadius: 8
                                }
                            },
                            cutout: '70%'
                        }
                    });
                @endif

                // Order items modal
                $(document).on('click', '.view-order-items', function() {
                    var orderId = $(this).data('id');
                    var modal = $('#orderItemsModal');

                    $('#orderItemsContent').html(`
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading order items...</p>
                        </div>
                    `);

                    modal.modal('show');

                    $.ajax({
                        url: "{{ url('admin/shops/order-items') }}/" + orderId,
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
                                'Failed to load order items';
                            showError(errorMessage);
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

                @if (session('status'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '{{ session('status')['message'] }}',
                        confirmButtonColor: '#4a6cf7',
                    });
                @endif
            });
        </script>
    @endpush
@endsection
