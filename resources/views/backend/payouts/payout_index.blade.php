@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @endpush

    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">Payout</h4>
            </div>
        </div><!-- end col-->
        <div class="col-md-6 mt-3">
            <div class="d-flex justify-content-end gap-2">
                <div class="date-range-container">
                    <div class="input-form" id="reportrange"
                        style="cursor: pointer; padding: 10px; border: 1px solid #0ea1b1; width: 100%; border-radius: 30px; color:#0ea1b1; text-align: center; min-width: 250px;">
                        <i class="mdi mdi-calendar"></i>&nbsp;
                        <span></span> <i class="mdi mdi-chevron-down"></i>
                    </div>
                    <input type="hidden" id="start_date_range" name="start_date">
                    <input type="hidden" id="end_date_range" name="end_date">
                </div>

                <button type="button" class="btn btn-theme-second-success" id="exportDateRangeBtn"
                    style="border-radius: 30px; padding: 10px 20px;" disabled>
                    <i class="mdi mdi-download me-1"></i> Export
                </button>

                <button class="btn add-btn waves-effect waves-light button-color text-white" data-bs-toggle="modal"
                    data-bs-target="#generatePayoutModal">
                    <i class="mdi mdi-plus-circle me-2"></i>Generate Payout
                </button>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3 payout-index-animate-fade-in payout-index-delay-1">
            <div class="payout-index-stats-card payout-index-stats-card1 payout-index-stats-card-total">
                <div class="payout-index-stats-icon">
                    <i class="mdi mdi-bank-transfer"></i>
                </div>
                <div>
                    <div class="payout-stats-number" data-original="{{ $stats['total_payouts'] }}">
                        {{ number_format($stats['total_payouts']) }}
                    </div>
                    <div class="payout-index-stats-label">Total Payouts</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 payout-index-animate-fade-in payout-index-delay-1">
            <div class="payout-index-stats-card payout-index-stats-card2 payout-index-stats-card-amount">
                <div class="payout-index-stats-icon">
                    <i class="mdi mdi-currency-inr"></i>
                </div>
                <div>
                    <div class="payout-stats-number" data-original="{{ $stats['total_amount'] }}">
                        ₹{{ number_format($stats['total_amount'], 2) }}
                    </div>
                    <div class="payout-index-stats-label">Total Amount</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 payout-index-animate-fade-in payout-index-delay-2">
            <div class="payout-index-stats-card payout-index-stats-card3 payout-index-stats-card-paid">
                <div class="payout-index-stats-icon">
                    <i class="mdi mdi-check-circle-outline"></i>
                </div>
                <div>
                    <div class="payout-stats-number" data-original="{{ $stats['paid_payouts'] }}">
                        {{ number_format($stats['paid_payouts']) }}
                    </div>
                    <div class="payout-index-stats-label">Paid Payouts</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 payout-index-animate-fade-in payout-index-delay-2">
            <div class="payout-index-stats-card payout-index-stats-card4 payout-index-stats-card-pending">
                <div class="payout-index-stats-icon">
                    <i class="mdi mdi-timer-sand"></i>
                </div>
                <div>
                    <div class="payout-stats-number" data-original="{{ $stats['pending_payouts'] }}">
                        {{ number_format($stats['pending_payouts']) }}
                    </div>
                    <div class="payout-index-stats-label">Pending Payouts</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card payout-card payout-index-animate-fade-in delay-3 mt-3">
        <div class="card-header payout-index-card-header d-flex justify-content-between align-items-center">
            <h5 class="payout-index-card-title">
                Payout Records
            </h5>
            <div>
                <small class="text-muted" id="tableSummary">Showing all payouts</small>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive table-border">
                <table id="payoutsTable" class="table table-striped data-table">
                    <thead class="theme-menu-color">
                        <tr>
                            <th>Date Range</th>
                            <th>Shops</th>
                            <th>Amount</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Generated On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Generate Payout Modal -->
    <div class="modal fade" id="generatePayoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ url('admin/payouts/generate') }}" method="POST" id="generatePayoutForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Generate New Payout
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label fw-bold">
                                    <i class="mdi mdi-calendar-start me-1 text-primary"></i>Start Date
                                </label>
                                <div class="date-picker-group">
                                    <i class="mdi mdi-calendar input-icon"></i>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required
                                        max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                                        @if ($lastPayoutEndDate) min="{{ $lastPayoutEndDate }}" @endif>
                                </div>
                                <small class="text-muted">Select the start date for payout period</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label fw-bold">
                                    <i class="mdi mdi-calendar-end me-1 text-primary"></i>End Date
                                </label>
                                <div class="date-picker-group">
                                    <i class="mdi mdi-calendar input-icon"></i>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required
                                        max="{{ date('Y-m-d', strtotime('-1 day')) }}" disabled>
                                </div>
                                <small class="text-muted">Select the end date for payout period</small>
                            </div>
                        </div>

                        <div class="alert alert-info d-flex align-items-center mt-3 border-radius">
                            <i class="mdi mdi-information-outline me-3 fs-4"></i>
                            <div>
                                <strong>Note:</strong> Payout will include all completed orders between these dates.
                                Today's date cannot be selected as payouts are generated for previous days only.
                            </div>
                        </div>

                        <div class="alert alert-warning d-flex align-items-center mt-3 d-none" id="dateRangeWarning">
                            <i class="mdi mdi-alert-circle-outline me-3 fs-4"></i>
                            <div>
                                <strong>Warning:</strong> <span id="warningMessage"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="submit" class="btn add-btn text-white" id="generateBtn">
                            <i class="mdi mdi-check-circle me-1"></i>Generate Payout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>Confirmation Required
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage"></p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Cancel
                    </button>
                    <a href="#" id="confirmActionBtn" class="btn btn-primary">
                        <i class="mdi mdi-check-circle me-1"></i>Confirm
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"
            integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

        <script>
            $(document).ready(function() {
                const table = $('#payoutsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('admin/payouts/datatable') }}",
                        data: function(d) {
                            d.start_date = $('#start_date_range').val();
                            d.end_date = $('#end_date_range').val();
                        }
                    },
                    columns: [{
                            data: 'date_range',
                            name: 'start_date',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'shops_count',
                            name: 'payout_shops_count',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'amount',
                            name: 'total_amount',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'commission',
                            name: 'total_commission',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'status_badge',
                            name: 'status',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'created',
                            name: 'created_at',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [5, 'desc']
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search payouts...",
                        lengthMenu: "Show _MENU_ entries",
                        infoEmpty: "No payouts found",
                        infoFiltered: "(filtered from _MAX_ total payouts)"
                    },
                    drawCallback: function() {
                        $('[data-bs-toggle="tooltip"]').tooltip();
                        updateTableSummary();
                    }
                });

                const start = moment().subtract(29, 'days');
                const end = moment();

                function cb(start, end, label) {
                    if (label === 'All Dates') {
                        $('#reportrange span').html('All Dates');
                        $('#start_date_range').val('');
                        $('#end_date_range').val('');
                        $('#tableSummary').text('Showing all payouts');
                        $('#exportDateRangeBtn').prop('disabled', true);

                        resetStatsCards();
                    } else {
                        const displayFormat = 'D MMM, YYYY';
                        $('#reportrange span').html(start.format(displayFormat) + ' - ' + end.format(displayFormat));
                        $('#start_date_range').val(start.format('YYYY-MM-DD'));
                        $('#end_date_range').val(end.format('YYYY-MM-DD'));

                        $('#exportDateRangeBtn').prop('disabled', false);

                        const rangeLabel = label || (start.format(displayFormat) + ' to ' + end.format(displayFormat));
                        $('#selectedRangeLabel').text(rangeLabel);
                        $('#tableSummary').text('Showing payouts from ' + start.format('D MMM YYYY') + ' to ' + end
                            .format('D MMM YYYY'));

                        loadDateRangeStats(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
                    }

                    table.draw();

                    if (label !== 'All Dates') {
                        loadDateRangeStats(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
                    }
                }

                $('#reportrange').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'All Dates': [moment().subtract(10, 'years'), moment().add(10, 'years')],
                        'Today': [moment().startOf('day'), moment().endOf('day')],
                        'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days')
                            .endOf(
                                'day')
                        ],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                                'month')
                            .endOf('month')
                        ]
                    }
                }, cb);

                cb(start, end, 'Last 30 Days');

                function loadDateRangeStats(startDate, endDate) {
                    $.ajax({
                        url: "{{ url('admin/payouts/range-stats') }}",
                        type: "GET",
                        data: {
                            start_date: startDate,
                            end_date: endDate
                        },
                        success: function(response) {
                            if (response.success) {
                                updateStatsCards(response.data);

                                $('#rangePayoutsCount').text(response.data.payouts_count);
                                $('#rangeTotalAmount').text('₹' + response.data.total_amount.toLocaleString(
                                    'en-IN', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }));
                                $('#rangeTotalCommission').text('₹' + response.data.total_commission
                                    .toLocaleString('en-IN', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }));

                                $('#selectedRangeLabel').text(moment(startDate).format('D MMM YYYY') +
                                    ' to ' + moment(endDate).format('D MMM YYYY'));
                            }
                        },
                        error: function() {
                            console.log('Failed to load date range stats');
                        }
                    });
                }

                function updateStatsCards(statsData) {
                    $('.payout-index-stats-card-total .payout-stats-number').text(statsData.payouts_count);

                    $('.payout-index-stats-card-amount .payout-stats-number').text('₹' + statsData.total_amount
                        .toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));

                    $('.payout-index-stats-card-paid .payout-stats-number').text(statsData.paid_payouts);

                    $('.payout-index-stats-card-pending .payout-stats-number').text(statsData.pending_payouts);
                }

                function resetStatsCards() {
                    const originalStats = {
                        total_payouts: parseInt($('.payout-index-stats-card-total .payout-stats-number').data(
                            'original')),
                        total_amount: parseFloat($('.payout-index-stats-card-amount .payout-stats-number').data(
                            'original')),
                        paid_payouts: parseInt($('.payout-index-stats-card-paid .payout-stats-number').data(
                            'original')),
                        pending_payouts: parseInt($('.payout-index-stats-card-pending .payout-stats-number').data(
                            'original'))
                    };

                    $('.payout-index-stats-card-total .payout-stats-number').text(originalStats.total_payouts);

                    $('.payout-index-stats-card-amount .payout-stats-number').text('₹' + originalStats.total_amount
                        .toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));

                    $('.payout-index-stats-card-paid .payout-stats-number').text(originalStats.paid_payouts);

                    $('.payout-index-stats-card-pending .payout-stats-number').text(originalStats.pending_payouts);
                }

                $('#exportDateRangeBtn').click(function() {
                    const startDate = $('#start_date_range').val();
                    const endDate = $('#end_date_range').val();

                    if (!startDate || !endDate) {
                        showToast('Please select a date range first', 'warning');
                        return;
                    }

                    $(this).html('<span class="spinner-border spinner-border-sm me-1"></span> Exporting...');
                    $(this).prop('disabled', true);

                    const url = "{{ url('admin/payouts/export-range') }}" +
                        "?start_date=" + encodeURIComponent(startDate) +
                        "&end_date=" + encodeURIComponent(endDate);

                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = url;
                    document.body.appendChild(iframe);

                    setTimeout(() => {
                        $('#exportDateRangeBtn').html(
                            '<i class="mdi mdi-download me-1"></i> Export Selected Range');
                        $('#exportDateRangeBtn').prop('disabled', false);
                    }, 2000);
                });

                function updateTableSummary() {
                    const startDate = $('#start_date_range').val();
                    const endDate = $('#end_date_range').val();

                    if (startDate && endDate) {
                        const start = moment(startDate).format('D MMM YYYY');
                        const end = moment(endDate).format('D MMM YYYY');
                        $('#tableSummary').text('Showing payouts from ' + start + ' to ' + end);
                    } else {
                        $('#tableSummary').text('Showing all payouts');
                    }
                }

                const existingPayouts = @json($existingPayouts);
                const lastPayoutEndDate = @json($lastPayoutEndDate);

                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');
                const warningDiv = document.getElementById('dateRangeWarning');
                const warningMessage = document.getElementById('warningMessage');
                const generateBtn = document.getElementById('generateBtn');
                const generateForm = document.getElementById('generatePayoutForm');

                const yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1);
                const maxDate = yesterday.toISOString().split('T')[0];

                startDateInput.max = maxDate;
                endDateInput.max = maxDate;

                function checkDateRangeOverlap(startDate, endDate) {
                    for (let payout of existingPayouts) {
                        const existingStart = new Date(payout.start);
                        const existingEnd = new Date(payout.end);
                        const newStart = new Date(startDate);
                        const newEnd = new Date(endDate);

                        if (newStart <= existingEnd && newEnd >= existingStart) {
                            return {
                                overlaps: true,
                                range: payout.range
                            };
                        }
                    }
                    return {
                        overlaps: false
                    };
                }

                function formatDateForDisplay(dateString) {
                    const options = {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    };
                    return new Date(dateString).toLocaleDateString('en-US', options);
                }

                startDateInput.addEventListener('change', function() {
                    const selectedStartDate = this.value;

                    if (selectedStartDate) {
                        endDateInput.disabled = false;
                        endDateInput.min = selectedStartDate;

                        endDateInput.value = '';

                        warningDiv.classList.add('d-none');
                        generateBtn.disabled = false;
                    } else {
                        endDateInput.disabled = true;
                        endDateInput.value = '';
                    }
                });

                endDateInput.addEventListener('change', function() {
                    const startDate = startDateInput.value;
                    const endDate = this.value;

                    if (startDate && endDate) {
                        const overlapResult = checkDateRangeOverlap(startDate, endDate);

                        if (overlapResult.overlaps) {
                            warningMessage.textContent =
                                `Selected date range (${formatDateForDisplay(startDate)} to ${formatDateForDisplay(endDate)}) overlaps with existing payout: ${overlapResult.range}`;
                            warningDiv.classList.remove('d-none');
                            generateBtn.disabled = true;
                        } else {
                            warningDiv.classList.add('d-none');
                            generateBtn.disabled = false;
                        }
                    }
                });

                generateForm.addEventListener('submit', function(e) {
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;

                    if (!startDate || !endDate) {
                        e.preventDefault();
                        showToast('Please select both start and end dates', 'danger');
                        return;
                    }

                    if (new Date(startDate) >= new Date()) {
                        e.preventDefault();
                        showToast('Start date must be before today', 'danger');
                        return;
                    }

                    if (new Date(endDate) >= new Date()) {
                        e.preventDefault();
                        showToast('End date must be before today', 'danger');
                        return;
                    }

                    if (new Date(startDate) > new Date(endDate)) {
                        e.preventDefault();
                        showToast('End date must be after or equal to start date', 'danger');
                        return;
                    }

                    const overlapResult = checkDateRangeOverlap(startDate, endDate);
                    if (overlapResult.overlaps) {
                        e.preventDefault();
                        showToast(`Selected date range overlaps with existing payout: ${overlapResult.range}`,
                            'danger');
                        return;
                    }

                    generateBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Generating...';
                    generateBtn.disabled = true;
                });

                $(document).on('click', '.confirm-action', function(e) {
                    e.preventDefault();
                    const href = $(this).data('href');
                    const message = $(this).data('message');

                    $('#confirmationMessage').text(message);
                    $('#confirmActionBtn').attr('href', href);
                    $('#confirmationModal').modal('show');
                });

                $('#confirmActionBtn').click(function(e) {
                    e.preventDefault();
                    const href = $(this).attr('href');

                    $.ajax({
                        url: href,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            $('#confirmationModal').modal('hide');
                            showToast('Processing your request...', 'info');
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast(response.message, 'success');
                                table.ajax.reload(null, false);
                            } else {
                                showToast(response.message || 'An error occurred', 'danger');
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON?.message || 'An error occurred', 'danger');
                        }
                    });
                });

                function showToast(message, type) {
                    const toast = `
                        <div class="toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1100;">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="mdi mdi-${type === 'danger' ? 'alert-circle' : 'check-circle'} me-2"></i>
                                    ${message}
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    `;

                    $(toast).appendTo('body').toast({
                        autohide: true,
                        delay: 5000
                    }).toast('show');

                    // Remove toast after it hides
                    $('.toast').on('hidden.bs.toast', function() {
                        $(this).remove();
                    });
                }

                $('#generatePayoutModal').on('hidden.bs.modal', function() {
                    generateForm.reset();
                    endDateInput.disabled = true;
                    warningDiv.classList.add('d-none');
                    generateBtn.innerHTML = '<i class="mdi mdi-check-circle me-1"></i>Generate Payout';
                    generateBtn.disabled = false;
                });

                $(function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                });

                $('#searchInput').on('keyup', function() {
                    table.search(this.value).draw();
                });

                $('#resetDateRange').click(function() {
                    $('#reportrange').data('daterangepicker').setStartDate(moment().subtract(29, 'days'));
                    $('#reportrange').data('daterangepicker').setEndDate(moment());
                    cb(moment().subtract(29, 'days'), moment(), 'Last 30 Days');
                });

                @if (session('success'))
                    showToast('{{ session('success') }}', 'success');
                @endif

                @if (session('error'))
                    showToast('{{ session('error') }}', 'danger');
                @endif

                @if (session('warning'))
                    showToast('{{ session('warning') }}', 'warning');
                @endif

                @if (session('info'))
                    showToast('{{ session('info') }}', 'info');
                @endif
            });
        </script>
    @endpush
@endsection
