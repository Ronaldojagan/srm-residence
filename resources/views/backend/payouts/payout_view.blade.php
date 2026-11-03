@extends('backend.layout.main')

@push('head_content')
    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .stats-card {
            background: #0EA1B1;
            border-radius: 14px;
            padding: 1.75rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            height: 100%;
            border: 1px solid rgba(76, 175, 80, 0.15);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(76, 175, 80, 0.15);
        }

        .stats-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .stats-icon {
            font-size: 1.75rem;
            color: #fff;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .stats-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #fff;
            margin: 0;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .stat-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(76, 175, 80, 0.1);
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.15);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1b5e20;
            margin-bottom: 0.25rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* Progress Bar */
        .stats-progress {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(76, 175, 80, 0.1);
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-left: 4px solid #4CAF50;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #CFD8DC;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0 !important;
        }

        .card-title {
            font-weight: 600;
            color: #263238;
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 0.5rem;
            color: #4CAF50;
        }

        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #388E3C;
            border-color: #388E3C;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .btn-success {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .btn-danger {
            background-color: #F44336;
            border-color: #F44336;
        }

        .btn-secondary {
            background-color: #607D8B;
            border-color: #607D8B;
        }

        .shop-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #0EA1B1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-right: 12px;
            transition: all 0.3s ease;
        }

        .shop-avatar:hover {
            transform: scale(1.1);
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
        }

        .status-badge i {
            margin-right: 0.375rem;
            font-size: 0.75rem;
        }

        .badge-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }

        .badge-warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #FFC107;
        }

        .badge-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }

        .badge-info {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .action-btn-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
            color: white;
        }

        .action-btn-sm:hover {
            transform: scale(1.1);
        }

        .btn-generated {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
        }

        .btn-processing {
            background: rgba(33, 150, 243, 0.2);
            color: #2196F3;
        }

        .btn-paid {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .btn-failed {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
        }

        .amount-display {
            font-family: 'Monaco', 'Consolas', monospace;
            font-weight: 600;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .progress-label {
            font-size: 0.85rem;
            color: #0EA1B1;
            font-weight: 500;
        }

        .progress-percent {
            font-size: 0.85rem;
            font-weight: 600;
            color: #0EA1B1;
        }

        .progress-track {
            height: 8px;
            background: #e8f5e9;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #0EA1B1 0%, #217982 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: width 0.6s ease;
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, 0.1) 0%,
                    rgba(255, 255, 255, 0.3) 50%,
                    rgba(255, 255, 255, 0.1) 100%);
            animation: progressShine 2s infinite;
        }

        @keyframes progressShine {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }


        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 1rem;
                text-align: center;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .action-btn-sm {
                width: 30px;
                height: 30px;
                font-size: 0.875rem;
            }
        }
    </style>
@endpush

@section('content')
    <!-- Page Header -->
    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <h4 class="page-title-main page">
                Payout Details
            </h4>
        </div>
        <div class="col-md-6 mt-3 mb-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/payouts') }}"
                class="btn add-btn float-end waves-effect waves-light button-color text-white">
                <i class="mdi mdi-keyboard-backspace me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card summary-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="mdi mdi-information-outline me-2 text-primary"></i>Payout Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">
                                    <i class="mdi mdi-calendar-range text-primary me-2"></i>Date Range:
                                </span>
                                <span class="fw-bold">
                                    {{ $payout->start_date->format('d M Y') }} - {{ $payout->end_date->format('d M Y') }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">
                                    <i class="mdi mdi-flag text-primary me-2"></i>Status:
                                </span>
                                <span>{!! $payout->status_badge !!}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">
                                    <i class="mdi mdi-currency-inr text-primary me-2"></i>Total Amount:
                                </span>
                                <span class="amount-display text-success fw-bold">
                                    ₹{{ number_format($payout->total_amount, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">
                                    <i class="mdi mdi-percent text-primary me-2"></i>Total Commission:
                                </span>
                                <span class="amount-display text-warning fw-bold">
                                    ₹{{ number_format($payout->total_commission, 2) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">
                                    <i class="mdi mdi-cash text-primary me-2"></i>Total Payable:
                                </span>
                                <span class="amount-display text-primary fw-bold">
                                    ₹{{ number_format($payout->total_payable_amount, 2) }}
                                </span>
                            </div>
                            @if ($payout->isPaid())
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="mdi mdi-calendar-check text-primary me-2"></i>Paid Date:
                                    </span>
                                    <span>
                                        {{ $payout->paid_date->format('d M Y H:i') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($payout->isGenerated())
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" onclick="confirmMarkAsPaid()" class="btn add-btn text-white">
                                    <i class="mdi mdi-check-circle me-1"></i>Mark as Paid
                                </button>
                                {{-- <button class="btn btn-theme-second-background add-btn text-white" data-bs-toggle="modal" data-bs-target="#markAsFailedModal">
                                    <i class="mdi mdi-alert-circle me-1"></i>Mark as Failed
                                </button> --}}
                                <a href="{{ url('admin/payouts/' . Crypt::encrypt($payout->id) . '/export') }}"
                                    class="btn btn-theme-second-success">
                                    <i class="mdi mdi-download me-1"></i>Export CSV
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 pt-3 border-top">
                            <a href="{{ url('admin/payouts/' . Crypt::encrypt($payout->id) . '/export') }}"
                                class="btn btn-theme-second-success">
                                <i class="mdi mdi-download me-1"></i>Export CSV
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @if ($payout->notes)
                <div class="card mt-3 border-warning">
                    <div class="card-body">
                        <h6 class="card-title text-warning">
                            <i class="mdi mdi-note-text me-2"></i>Notes
                        </h6>
                        <p class="mb-0">{{ $payout->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="stats-card">
                <div class="stats-header">
                    <i class="mdi mdi-chart-pie stats-icon"></i>
                    <h5 class="stats-title">Payout Statistics</h5>
                </div>

                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['total_shops'] }}</div>
                        <div class="stat-label">Total Shops</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value">₹{{ number_format($stats['total_payable'], 0) }}</div>
                        <div class="stat-label">Payable Amount</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['paid_shops'] }}</div>
                        <div class="stat-label">Shops Paid</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['pending_shops'] }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="stats-progress">
                    <div class="progress-header">
                        <span class="progress-label">Completion Progress</span>
                        <span class="progress-percent">{{ number_format($stats['progress_percent'], 1) }}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ $stats['progress_percent'] }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop Payouts Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="mdi mdi-store-24-hour me-2 text-primary"></i>Shop Payout Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-border">
                        <table id="shopsTable" class="table table-striped data-table">
                            <thead class="theme-menu-color">
                                <tr>
                                    <th>Shop</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Commission</th>
                                    <th class="text-end">Payable</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark as Failed Modal -->
    <div class="modal fade" id="markAsFailedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ url('admin/payouts/' . Crypt::encrypt($payout->id) . '/mark-as-failed') }}"
                    method="POST" id="failPayoutForm">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-alert-circle me-2"></i>Mark Payout as Failed
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-bold">
                                <i class="mdi mdi-note-text me-2 text-danger"></i>Reason for Failure
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" required
                                placeholder="Please provide a detailed reason for marking this payout as failed..."></textarea>
                        </div>
                        <div class="alert alert-warning d-flex align-items-start">
                            <i class="mdi mdi-alert-triangle me-2 mt-1"></i>
                            <div>
                                <strong>Warning:</strong> This action will mark all shop payouts in this batch as failed.
                                This action cannot be undone easily.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="mdi mdi-check me-1"></i>Confirm Failure
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('footer_content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"
        integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#shopsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('admin/payouts/shops-datatable/' . Crypt::encrypt($payout->id)) }}",
                columns: [{
                        data: 'shop_name',
                        name: 'shop.name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        orderable: true,
                        searchable: false,
                        className: 'text-end'
                    },
                    {
                        data: 'commission',
                        name: 'commission',
                        orderable: true,
                        searchable: false,
                        className: 'text-end'
                    },
                    {
                        data: 'payable',
                        name: 'payable_amount',
                        orderable: true,
                        searchable: false,
                        className: 'text-end'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search shops...",
                    lengthMenu: "Show _MENU_ entries",
                    infoEmpty: "No shops found",
                    infoFiltered: "(filtered from _MAX_ total shops)",
                },
                drawCallback: function() {}
            });

            $('#searchInput').keyup(function() {
                table.search($(this).val()).draw();
            });

            $('#failPayoutForm').submit(function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will mark all shop payouts in this batch as failed!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, mark as failed!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        function confirmMarkAsPaid() {
            Swal.fire({
                title: 'Mark Payout as Paid?',
                text: "Are you sure you want to mark this payout as paid?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark as paid!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/payouts/' . Crypt::encrypt($payout->id) . '/mark-as-paid') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire('Success!', 'Payout marked as paid.', 'success')
                                .then(() => location.reload());
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        }

        $(document).on('click', '.update-status-btn', function() {
            const encryptedShopId = $(this).data('id');
            const status = $(this).data('status');

            updateStatus(encryptedShopId, status);
        });

        function updateStatus(shopId, status) {
            console.log(shopId);
            const statusText = status.charAt(0).toUpperCase() + status.slice(1);
            const url = `{{ url('admin/payouts') }}/${shopId}/update-status`;

            Swal.fire({
                title: `Mark as ${statusText}?`,
                text: `Are you sure you want to mark this shop payout as ${status}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, mark as ${statusText}`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            status: status
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Processing',
                                text: 'Please wait...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message || 'Status updated successfully',
                                icon: 'success',
                                confirmButtonColor: '#4CAF50'
                            }).then(() => {
                                $('#shopsTable').DataTable().ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'An error occurred',
                                icon: 'error',
                                confirmButtonColor: '#F44336'
                            });
                        }
                    });
                }
            });
        }

        @if (session('success'))
            Swal.fire({
                title: 'Success!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonColor: '#4CAF50'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonColor: '#F44336'
            });
        @endif
    </script>
@endpush
