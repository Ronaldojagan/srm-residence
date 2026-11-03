@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <link rel="stylesheet" href="{{ url('assets/libs/chartist/chartist.min.css') }}">
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush

    <div class="row justify-content-between align-items-center">
        <div class="col-md-4 mt-3">
            <h4 class="page-title-main page">Dashboard</h4>
        </div>
        <div class="col-md-8 mt-3">
            <div class="d-flex justify-content-end">
                <div style="min-width: 250px;">
                    <div class="input-form" id="reportrange"
                        style="cursor: pointer; padding: 10px; border: 1px solid #0ea1b1; width: 100%; border-radius: 30px; color:#0ea1b1; text-align: center;">
                        <i class="fa fa-calendar"></i>&nbsp;
                        <span></span> <i class="fa fa-caret-down"></i>
                    </div>
                    <input type="hidden" class="start-date" name="start_date">
                    <input type="hidden" class="end-date" name="end_date">
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="row dashboard-card mb-3 d-flex flex-wrap justify-content-between">
        @php
            $cards = [
                [
                    'url' => 'admin/orders',
                    'label' => 'Orders',
                    'count' => $orderCount,
                    'id' => 'total-orders',
                    'icon' => 'shopping-cart',
                ],
                [
                    'url' => 'admin/products',
                    'label' => 'Packages',
                    'count' => $packageCount,
                    'id' => 'total-products',
                    'icon' => 'box',
                ],
                [
                    'url' => 'admin/users',
                    'label' => 'Users',
                    'count' => $userCount,
                    'id' => 'total-users',
                    'icon' => 'users',
                ],
                [
                    'url' => 'admin/shops',
                    'label' => 'Delivery Mans',
                    'count' => $shopCount,
                    'id' => 'total-shops',
                    'icon' => 'store',
                ],
            ];
        @endphp

        @php
            $cardClasses = ['dash-card1', 'dash-card2', 'dash-card1', 'dash-card2'];
            $cardIcons = ['dash-icon1.png', 'dash-icon2.png', 'dash-icon3.png', 'dash-icon4.png'];
        @endphp

        @foreach ($cards as $card)
            <div class="dash-card-container">
                <a href="{{ url($card['url']) }}">
                    <div class="{{ $cardClasses[$loop->index % count($cardClasses)] }}">
                        <div class="row mt-1 p-3">
                            <div class="col-4">
                                <img class="mt-1 img-fluid w-26"
                                    src="{{ asset('img/' . $cardIcons[$loop->index % count($cardIcons)]) }}" alt="Icon">
                            </div>
                            <div class="col-8 text-white mb-0 fs-5">
                                <p class="text-white mt-1 fs-4 mb-1">{{ $card['label'] }}</p>
                                <p class="text-white fs-5 mt-0" id="{{ $card['id'] }}">
                                    {{ $card['count'] }} {{ $card['label'] }}
                                </p>
                            </div>
                        </div>
                        <a class="ps-3 mt-0 fw-medium text-white" href="{{ url($card['url']) }}">
                            View {{ $card['label'] }} <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Analytics Summary Cards -->
    {{-- <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-index-order-stats-card order-card1">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shopping-bag fa-2x me-3"></i>
                    <div>
                        <h5 class="text-white">Orders in Range</h5>
                        <h2 class="text-white" id="rangeOrders">{{ $totalOrdersInRange }}</h2>
                        <small>Selected period</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-index-order-stats-card order-card2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-rupee-sign fa-2x me-3"></i>
                    <div>
                        <h5 class="text-white">Total Revenue</h5>
                        <h2 class="text-white" id="totalRevenue">₹{{ number_format($totalRevenue, 2) }}</h2>
                        <small>Selected period</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-index-order-stats-card order-card3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-percentage fa-2x me-3"></i>
                    <div>
                        <h5 class="text-white">Commission</h5>
                        <h2 class="text-white" id="totalCommission">₹{{ number_format($commission, 2) }}</h2>
                        <small>Admin earnings</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-index-order-stats-card order-card4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-line fa-2x me-3"></i>
                    <div>
                        <h5 class="text-white">Avg Order Value</h5>
                        <h2 class="text-white" id="avgOrderValue">
                            ₹{{ number_format($totalOrdersInRange > 0 ? $totalRevenue / $totalOrdersInRange : 0, 2) }}</h2>
                        <small>Per order</small>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Charts Row -->
    {{-- <div class="row mb-4">
        <div class="col-md-6">
            <div class="dashboard-index-chart-card">
                <h5 class="mb-3">Order Status Distribution</h5>
                <div class="dashboard-index-chart-container">
                    <canvas id="orderStatusChart"></canvas>
                    <div id="orderStatusEmpty" class="dashboard-card-empty-chart" style="display: none;">
                        <div class="text-center">
                            <i class="fa fa-chart-pie fa-3x mb-3" style="opacity: 0.3;"></i>
                            <p>No order data available for selected period</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-index-chart-card">
                <h5 class="mb-3">Daily Orders Trend</h5>
                <div class="dashboard-index-chart-container">
                    <canvas id="dailyOrdersChart"></canvas>
                    <div id="dailyOrdersEmpty" class="dashboard-card-empty-chart" style="display: none;">
                        <div class="text-center">
                            <i class="fa fa-chart-line fa-3x mb-3" style="opacity: 0.3;"></i>
                            <p>No daily order data available for selected period</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="dashboard-index-chart-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Recent Orders</h5>
                    <div class="bulk-actions" id="bulkActionsPanel">
                        <a href="{{ url('admin/orders') }}"
                            class="fw-medium text-dark d-inline-flex align-items-center gap-1">
                            View All
                            <img src="{{ url('img/view-icon.png') }}" style="width:16px;" alt="View icon">
                        </a>
                    </div>
                </div>
                <div class="table-responsive table-border">
                    <table id="orders-table" class="table" style="width:100%">
                        <thead class="theme-menu-color">
                            <tr>
                                <th>S.no</th>
                                <th>Order No</th>
                                <th>Name</th>
                                <th>Package</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Order rows here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4" style="max-width: 340px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-semibold">Recent Users</h5>
                    </div>
                    <hr class="mt-0">
                    <ul class="list-unstyled mb-0">
                        @foreach ($recentUsers as $user)
                            <li class="d-flex align-items-center justify-content-between py-2">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ url('img/avatar.jpg') }}" alt={{ $user->name }} class="rounded-circle"
                                        width="45" height="45" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">{{ $user->name }}</div>
                                        <div class="text-muted" style="font-size: 12px;">{{ $user->phone }}</div>
                                    </div>
                                </div>
                                <span class="badge rounded-pill px-1 py-1"
                                    style="background-color: #28a745; font-size: 10px;">Active</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="{{ url('assets/libs/jquery-knob/jquery.knob.min.js') }}"></script>

        <script>
            let orderStatusChart, dailyOrdersChart, topProductsChart, revenueCommissionChart;
            let dataTables = {};
            let analyticsData = @json($analyticsData);

            $(document).ready(function() {
                initDateRangePicker();
                initCharts();
                initDataTables();
                initTodayActivity();
                updateActivityFeed();

                // Update activity feed every 30 seconds
                setInterval(updateActivityFeed, 30000);
            });

            function initDateRangePicker() {
                const start = moment().subtract(29, 'days');
                const end = moment();

                function cb(start, end, label) {
                    if (label === 'All Dates') {
                        $('#reportrange span').html('All Dates');
                        $('.start-date').val('');
                        $('.end-date').val('');
                    } else {
                        $('#reportrange span').html(start.format('D MMM, YYYY') + ' - ' + end.format('D MMM, YYYY'));
                        $('.start-date').val(start.format('YYYY-MM-DD'));
                        $('.end-date').val(end.format('YYYY-MM-DD'));
                    }

                    updateDashboardData(start, end, label);
                }

                $('#reportrange').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'All Dates': [moment().subtract(10, 'years'), moment().add(10, 'years')],
                        'Today': [moment().startOf('day'), moment().endOf('day')],
                        'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf(
                            'day')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                            .endOf('month')
                        ]
                    }
                }, cb);

                cb(start, end);
            }

            // function updateDashboardData(start, end, label) {
            //     const startDate = label === 'All Dates' ? '' : start.format('YYYY-MM-DD');
            //     const endDate = label === 'All Dates' ? '' : end.format('YYYY-MM-DD');

            //     showLoadingState();

            //     $.ajax({
            //         url: '{{ url('admin/analytics-data') }}',
            //         method: 'GET',
            //         data: {
            //             start_date: startDate,
            //             end_date: endDate
            //         },
            //         success: function(response) {
            //             updateSummaryCards(response);
            //             updateAnalyticsCards(response);
            //             updateCharts(response);
            //             updateTodayActivity(response);
            //             refreshDataTables();
            //             hideLoadingState();
            //         },
            //         error: function(xhr) {
            //             console.error('Error updating dashboard data:', xhr);
            //             hideLoadingState();
            //             alert('Error loading dashboard data. Please try again.');
            //         }
            //     });
            // }

            function updateSummaryCards(response) {
                if (response.summary) {
                    $('#total-orders').text(response.summary.totalOrders || 0);
                    $('#total-products').text(response.summary.totalProducts || 0);
                    $('#total-users').text(response.summary.totalUsers || 0);
                    $('#total-shops').text(response.summary.totalShops || 0);
                    $('#total-delivery').text(response.summary.totalDelivery || 0);
                }
            }

            function updateAnalyticsCards(response) {
                if (response.summary) {
                    $('#rangeOrders').text(response.summary.totalOrders || 0);
                    $('#totalRevenue').text('₹' + parseFloat(response.summary.totalRevenue || 0).toFixed(2));
                    $('#totalCommission').text('₹' + parseFloat(response.summary.commission || 0).toFixed(2));

                    const avgOrderValue = response.summary.totalOrders > 0 ?
                        response.summary.totalRevenue / response.summary.totalOrders : 0;
                    $('#avgOrderValue').text('₹' + avgOrderValue.toFixed(2));
                }
            }

            function updateTodayActivity(response) {
                if (response.todayActivity) {
                    $('#todayOrders').text(response.todayActivity.newOrders || 0);
                    $('#todayUsers').text(response.todayActivity.newUsers || 0);
                }
            }

            function updateActivityFeed() {
                $.ajax({
                    url: '{{ url('admin/today-activity-data') }}',
                    method: 'GET',
                    success: function(response) {
                        const activityList = $('#activity-feed-list');
                        activityList.empty();

                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(activity) {
                                const item = `
                                    <div class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <div class="dashborad-card-activity-icon">
                                                <i class="fas fa-${activity.icon}"></i>
                                            </div>
                                            <div class="dashboard-index-activity-content">
                                                <h6 class="mb-1">${activity.title}</h6>
                                                <p class="mb-0 text-muted">${activity.description}</p>
                                            </div>
                                            <div class="dashboard-index-activity-time">
                                                ${activity.time}
                                            </div>
                                        </div>
                                    </div>
                                `;
                                activityList.append(item);
                            });
                        } else {
                            activityList.append(`
                                <div class="text-center py-3 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                    <p>No recent activities found</p>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error updating activity feed:', xhr);
                    }
                });
            }

            function refreshDataTables() {
                Object.keys(dataTables).forEach(function(key) {
                    if (dataTables[key]) {
                        dataTables[key].ajax.reload();
                    }
                });
            }

            function showLoadingState() {
                $('.dashboard-index-chart-container').each(function() {
                    if (!$(this).find('.dashboard-index-loading-overlay').length) {
                        $(this).append(
                            '<div class="dashboard-index-loading-overlay"><i class="fa fa-spinner fa-spin fa-2x"></i></div>'
                        );
                    }
                });
            }

            function hideLoadingState() {
                $('.dashboard-index-loading-overlay').remove();
            }

            function initCharts() {
                // Order Status Chart
                const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
                orderStatusChart = new Chart(orderStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                '#FF6384',
                                '#36A2EB',
                                '#FFCE56',
                                '#4BC0C0',
                                '#9966FF',
                                '#FF9F40'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });

                // Daily Orders Chart
                const dailyOrdersCtx = document.getElementById('dailyOrdersChart').getContext('2d');
                dailyOrdersChart = new Chart(dailyOrdersCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Orders',
                            data: [],
                            borderColor: '#36A2EB',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            borderWidth: 2,
                            pointBackgroundColor: '#36A2EB',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.4,
                            fill: true
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
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false
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

                // Top Products Chart
                // const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
                // topProductsChart = new Chart(topProductsCtx, {
                //     type: 'bar',
                //     data: {
                //         labels: [],
                //         datasets: [{
                //             label: 'Quantity Sold',
                //             data: [],
                //             backgroundColor: '#4BC0C0',
                //             borderColor: '#4BC0C0',
                //             borderWidth: 1,
                //             borderRadius: 4
                //         }]
                //     },
                //     options: {
                //         responsive: true,
                //         maintainAspectRatio: false,
                //         plugins: {
                //             legend: {
                //                 display: false
                //             }
                //         },
                //         scales: {
                //             y: {
                //                 beginAtZero: true,
                //                 grid: {
                //                     drawBorder: false
                //                 }
                //             },
                //             x: {
                //                 grid: {
                //                     display: false
                //                 }
                //             }
                //         }
                //     }
                // });

                // // Revenue vs Commission Chart
                // const revenueCommissionCtx = document.getElementById('revenueCommissionChart').getContext('2d');
                // revenueCommissionChart = new Chart(revenueCommissionCtx, {
                //     type: 'bar',
                //     data: {
                //         labels: [],
                //         datasets: [{
                //                 label: 'Revenue',
                //                 data: [],
                //                 backgroundColor: '#36A2EB',
                //                 borderColor: '#36A2EB',
                //                 borderWidth: 1,
                //                 borderRadius: 4
                //             },
                //             {
                //                 label: 'Commission',
                //                 data: [],
                //                 backgroundColor: '#FFCE56',
                //                 borderColor: '#FFCE56',
                //                 borderWidth: 1,
                //                 borderRadius: 4
                //             }
                //         ]
                //     },
                //     options: {
                //         responsive: true,
                //         maintainAspectRatio: false,
                //         plugins: {
                //             legend: {
                //                 position: 'bottom',
                //                 labels: {
                //                     padding: 20,
                //                     usePointStyle: true
                //                 }
                //             },
                //             tooltip: {
                //                 callbacks: {
                //                     label: function(context) {
                //                         let label = context.dataset.label || '';
                //                         if (label) {
                //                             label += ': ';
                //                         }
                //                         if (context.parsed.y !== null) {
                //                             label += '₹' + context.parsed.y.toFixed(2);
                //                         }
                //                         return label;
                //                     }
                //                 }
                //             }
                //         },
                //         scales: {
                //             y: {
                //                 beginAtZero: true,
                //                 grid: {
                //                     drawBorder: false
                //                 },
                //                 ticks: {
                //                     callback: function(value) {
                //                         return '₹' + value;
                //                     }
                //                 }
                //             },
                //             x: {
                //                 grid: {
                //                     display: false
                //                 }
                //             }
                //         }
                //     }
                // });

                if (analyticsData) {
                    updateChartsData(analyticsData);
                }
            }

            function updateCharts(data) {
                updateChartsData(data);
            }

            function updateChartsData(data) {
                // Order Status Chart
                if (data.orderStatus && data.orderStatus.length > 0) {
                    orderStatusChart.data.labels = data.orderStatus.map(item => item.status);
                    orderStatusChart.data.datasets[0].data = data.orderStatus.map(item => item.count);
                    orderStatusChart.update();
                    $('#orderStatusChart').show();
                    $('#orderStatusEmpty').hide();
                } else {
                    $('#orderStatusChart').hide();
                    $('#orderStatusEmpty').show();
                }

                // Daily Orders Chart
                if (data.dailyOrders && data.dailyOrders.length > 0) {
                    dailyOrdersChart.data.labels = data.dailyOrders.map(item => item.date);
                    dailyOrdersChart.data.datasets[0].data = data.dailyOrders.map(item => item.count);
                    dailyOrdersChart.update();
                    $('#dailyOrdersChart').show();
                    $('#dailyOrdersEmpty').hide();
                } else {
                    $('#dailyOrdersChart').hide();
                    $('#dailyOrdersEmpty').show();
                }

                // Top Products Chart
                if (data.topProducts && data.topProducts.length > 0) {
                    topProductsChart.data.labels = data.topProducts.map(item => item.name);
                    topProductsChart.data.datasets[0].data = data.topProducts.map(item => item.quantity);
                    topProductsChart.update();
                    $('#topProductsChart').show();
                    $('#topProductsEmpty').hide();
                } else {
                    $('#topProductsChart').hide();
                    $('#topProductsEmpty').show();
                }

                // Revenue vs Commission Chart
                if (data.monthlyRevenue && data.monthlyRevenue.length > 0) {
                    revenueCommissionChart.data.labels = data.monthlyRevenue.map(item => item.month);
                    revenueCommissionChart.data.datasets[0].data = data.monthlyRevenue.map(item => item.revenue);
                    revenueCommissionChart.data.datasets[1].data = data.monthlyRevenue.map(item => item.commission);
                    revenueCommissionChart.update();
                    console.log(revenueCommissionChart.data.datasets[0].data = data.monthlyRevenue.map(item => item.revenue));
                    console.log(revenueCommissionChart.data.datasets[1].data = data.monthlyRevenue.map(item => item
                        .commission));
                    $('#revenueCommissionChart').show();
                    $('#revenueCommissionEmpty').hide();
                } else {
                    $('#revenueCommissionChart').hide();
                    $('#revenueCommissionEmpty').show();
                }
            }

            $(function() {
                var table = $('#orders-table').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    lengthChange: false,
                    ajax: {
                        url: "{{ url('/admin/orders-data') }}",
                    },
                    columns: [{
                            data: 'order_no',
                            name: 'order_no'
                        },
                        {
                            data: 'user_name',
                            name: 'user_name'
                        },
                        {
                            data: 'package',
                            name: 'package'
                        },
                        {
                            data: 'amount',
                            name: 'amount'
                        },
                        {
                            data: 'action',
                            name: 'action'
                        },
                    ],
                    pageLength: 10,
                    order: [
                        [0, 'desc']
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search orders...",
                        lengthMenu: "Show _MENU_ orders per page",
                        zeroRecords: "No orders found",
                        info: "Showing _START_ to _END_ of _TOTAL_ orders",
                        infoEmpty: "No orders available",
                        infoFiltered: "(filtered from _MAX_ total orders)"
                    }
                });
                dataTables.users = $('#purchases-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ url('admin/user-data') }}',
                        data: function(d) {
                            d.start_date = $('.start-date').val();
                            d.end_date = $('.end-date').val();
                        }
                    },
                    columns: [{
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'phone',
                            name: 'phone'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                    ],
                    pageLength: 10,
                    order: [
                        [0, 'desc']
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search users...",
                        lengthMenu: "Show _MENU_ users per page",
                        zeroRecords: "No users found",
                        info: "Showing _START_ to _END_ of _TOTAL_ users",
                        infoEmpty: "No users available",
                        infoFiltered: "(filtered from _MAX_ total users)"
                    }
                });
            });
        </script>
    @endpush
@endsection
