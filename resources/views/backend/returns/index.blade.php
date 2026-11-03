@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <style>
            .request-status-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }

            .verification-flow-card {
                border-left: 4px solid #0d6efd;
            }

            .verification-step {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
            }

            .verification-step-icon {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
            }

            .verification-step.pending .verification-step-icon {
                background-color: #ffc107;
                color: #000;
            }

            .verification-step.completed .verification-step-icon {
                background-color: #198754;
                color: #fff;
            }

            .verification-step.rejected .verification-step-icon {
                background-color: #dc3545;
                color: #fff;
            }
        </style>
    @endpush

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0 fw-semibold text-dark">
                            <i class="mdi mdi-rotate-left me-2"></i>Return Requests Management
                        </h4>
                        <p class="text-muted mt-1">Manage and process customer return requests</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="mdi mdi-filter me-1"></i> Filter by Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item filter-status" data-status="all" href="#">All Requests</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item filter-status" data-status="pending_admin" href="#">Pending Admin</a></li>
                                <li><a class="dropdown-item filter-status" data-status="pending_shop" href="#">Pending Shop</a></li>
                                <li><a class="dropdown-item filter-status" data-status="pending_final" href="#">Pending Final</a></li>
                                <li><a class="dropdown-item filter-status" data-status="approved" href="#">Approved</a></li>
                                <li><a class="dropdown-item filter-status" data-status="rejected" href="#">Rejected</a></li>
                            </ul>
                        </div>

                        {{-- <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Orders
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Total Pending</p>
                                <h2 class="mt-2 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{ $stats['total_pending'] ?? 0 }}">0</span>
                                </h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle rounded-circle fs-2">
                                    <i class="mdi mdi-clock-alert text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Pending Admin</p>
                                <h2 class="mt-2 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{ $stats['pending_admin'] ?? 0 }}">0</span>
                                </h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                    <i class="mdi mdi-account-cog text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Pending Shop</p>
                                <h2 class="mt-2 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{ $stats['pending_shop'] ?? 0 }}">0</span>
                                </h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded-circle fs-2">
                                    <i class="mdi mdi-store text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Completed</p>
                                <h2 class="mt-2 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{ $stats['completed'] ?? 0 }}">0</span>
                                </h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                    <i class="mdi mdi-check-circle text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-format-list-bulleted me-2"></i>Return Requests
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="returns-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Order</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Reason</th>
                                        <th>Verification Status</th>
                                        <th>Refund Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Details Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verificationModalLabel">Return Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="verificationContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div id="actionButtons" style="display: none;">
                        <!-- Action buttons loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTable
                const returnsTable = $('#returns-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('returns.datatable') }}",
                        data: function(d) {
                            d.status_filter = $('#statusFilter').val() || 'all';
                        }
                    },
                    columns: [
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function(data) {
                                return new Date(data).toLocaleDateString('en-IN', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                            }
                        },
                        {
                            data: 'order',
                            name: 'order.order_no',
                            render: function(data) {
                                return data ?
                                    `<a href="${data.view_url}" target="_blank" class="text-primary">${data.order_no}</a>` :
                                    'N/A';
                            }
                        },
                        {
                            data: 'product',
                            name: 'product_name',
                            render: function(data) {
                                return data ?
                                    `<div class="d-flex align-items-center">
                                        <img src="${data.image_url}" alt="${data.name}"
                                             class="rounded me-2" width="40" height="40">
                                        <div>
                                            <strong>${data.name}</strong><br>
                                            <small class="text-muted">Qty: ${data.quantity}</small>
                                        </div>
                                    </div>` :
                                    'N/A';
                            }
                        },
                        {
                            data: 'customer',
                            name: 'customer.name',
                            render: function(data) {
                                return data ?
                                    `<div>
                                        <strong>${data.name}</strong><br>
                                        <small class="text-muted">${data.phone || ''}</small>
                                    </div>` :
                                    'N/A';
                            }
                        },
                        {
                            data: 'return_reason',
                            name: 'return_reason',
                            render: function(data) {
                                return data ?
                                    `<span class="text-truncate" style="max-width: 150px;"
                                          title="${data}">${data}</span>` :
                                    'N/A';
                            }
                        },
                        {
                            data: 'verification_status',
                            name: 'verification_status',
                            render: function(data) {
                                const statusColors = {
                                    'pending_admin': 'warning',
                                    'pending_shop': 'info',
                                    'pending_final': 'primary',
                                    'approved': 'success',
                                    'rejected': 'danger',
                                    'processing_refund': 'warning'
                                };

                                const statusText = {
                                    'pending_admin': 'Pending Admin',
                                    'pending_shop': 'Pending Shop',
                                    'pending_final': 'Pending Final',
                                    'approved': 'Approved',
                                    'rejected': 'Rejected',
                                    'processing_refund': 'Processing Refund'
                                };

                                return `<span class="badge bg-${statusColors[data] || 'secondary'}">
                                    ${statusText[data] || data}
                                </span>`;
                            }
                        },
                        {
                            data: 'refund_amount',
                            name: 'refund_amount',
                            render: function(data) {
                                return data ?
                                    `<span class="fw-bold text-success">₹${parseFloat(data).toFixed(2)}</span>` :
                                    '<span class="text-muted">-</span>';
                            }
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            render: function(data) {
                                return `
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-info view-details-btn"
                                                data-id="${data.id}" title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                        ${data.can_approve ?
                                            `<button type="button" class="btn btn-outline-success approve-btn"
                                                    data-id="${data.id}" title="Approve">
                                                <i class="mdi mdi-check"></i>
                                            </button>` : ''
                                        }
                                        ${data.can_reject ?
                                            `<button type="button" class="btn btn-outline-danger reject-btn"
                                                    data-id="${data.id}" title="Reject">
                                                <i class="mdi mdi-close"></i>
                                            </button>` : ''
                                        }
                                    </div>
                                `;
                            }
                        }
                    ],
                    order: [[0, 'desc']],
                    language: {
                        processing: '<div class="spinner-border text-primary" role="status"></div>'
                    },
                    drawCallback: function(settings) {
                        // Initialize tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                });

                // Filter by status
                $('.filter-status').on('click', function(e) {
                    e.preventDefault();
                    const status = $(this).data('status');
                    $('#statusFilter').val(status);
                    returnsTable.ajax.reload();
                });

                // View details
                $(document).on('click', '.view-details-btn', function() {
                    const itemId = $(this).data('id');
                    loadVerificationDetails(itemId);
                });

                function loadVerificationDetails(itemId) {
                    $('#verificationContent').html(`
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading details...</p>
                        </div>
                    `);

                    $.ajax({
                        url: "{{ route('returns.details', ':id') }}".replace(':id', itemId),
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                renderVerificationDetails(response.data);
                            } else {
                                $('#verificationContent').html(`
                                    <div class="alert alert-danger">
                                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                                        ${response.message || 'Failed to load details'}
                                    </div>
                                `);
                            }
                        },
                        error: function() {
                            $('#verificationContent').html(`
                                <div class="alert alert-danger">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    Error loading details
                                </div>
                            `);
                        }
                    });
                }

                function renderVerificationDetails(data) {
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Order Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <strong>Order No:</strong>
                                            <a href="${data.order.view_url}" target="_blank" class="ms-2">
                                                ${data.order.order_no}
                                            </a>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Order Date:</strong>
                                            <span class="ms-2">${data.order.date}</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Customer:</strong>
                                            <span class="ms-2">${data.customer.name} (${data.customer.phone})</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Product Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <img src="${data.product.image_url}" alt="${data.product.name}"
                                                 class="rounded me-3" width="60" height="60">
                                            <div>
                                                <h6 class="mb-1">${data.product.name}</h6>
                                                <p class="mb-1"><small>Qty: ${data.product.quantity}</small></p>
                                                <p class="mb-0"><small>Amount: ₹${data.product.amount}</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Return Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Return Reason:</strong>
                                    <p class="mt-1">${data.return_reason}</p>
                                </div>

                                ${data.return_reason_details ? `
                                    <div class="mb-3">
                                        <strong>Additional Details:</strong>
                                        <p class="mt-1">${data.return_reason_details}</p>
                                    </div>
                                ` : ''}

                                ${data.return_image_url ? `
                                    <div class="mb-3">
                                        <strong>Return Image:</strong>
                                        <div class="mt-2">
                                            <img src="${data.return_image_url}" alt="Return Image"
                                                 class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    </div>
                                ` : ''}

                                <div class="mb-3">
                                    <strong>Requested At:</strong>
                                    <p class="mt-1">${data.requested_at}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card verification-flow-card">
                            <div class="card-header">
                                <h6 class="mb-0">Verification Process</h6>
                            </div>
                            <div class="card-body">
                                <!-- Admin Verification Step -->
                                <div class="verification-step ${data.admin_verification.status === 'completed' ? 'completed' :
                                                               data.admin_verification.status === 'rejected' ? 'rejected' : 'pending'}">
                                    <div class="verification-step-icon">
                                        <i class="mdi ${data.admin_verification.status === 'completed' ? 'mdi-check' :
                                                       data.admin_verification.status === 'rejected' ? 'mdi-close' : 'mdi-clock'}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>Admin Verification</strong>
                                        <div class="text-muted">
                                            ${data.admin_verification.status === 'completed' ?
                                                `Verified by ${data.admin_verification.verifier} on ${data.admin_verification.date}` :
                                              data.admin_verification.status === 'rejected' ?
                                                `Rejected by ${data.admin_verification.verifier} on ${data.admin_verification.date}` :
                                              'Pending admin verification'}
                                        </div>
                                        ${data.admin_verification.notes ?
                                            `<small class="text-muted">Notes: ${data.admin_verification.notes}</small>` : ''}
                                    </div>
                                </div>

                                <!-- Shop Verification Step (if admin approved) -->
                                ${data.admin_verification.status === 'completed' ? `
                                    <div class="verification-step ${data.shop_verification.status === 'completed' ? 'completed' :
                                                                   data.shop_verification.status === 'rejected' ? 'rejected' : 'pending'}">
                                        <div class="verification-step-icon">
                                            <i class="mdi ${data.shop_verification.status === 'completed' ? 'mdi-check' :
                                                           data.shop_verification.status === 'rejected' ? 'mdi-close' : 'mdi-clock'}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>Shop Verification</strong>
                                            <div class="text-muted">
                                                ${data.shop_verification.status === 'completed' ?
                                                    `Verified by shop on ${data.shop_verification.date}` :
                                                  data.shop_verification.status === 'rejected' ?
                                                    `Rejected by shop on ${data.shop_verification.date}` :
                                                  'Pending shop verification'}
                                            </div>
                                            ${data.shop_verification.notes ?
                                                `<small class="text-muted">Notes: ${data.shop_verification.notes}</small>` : ''}
                                            ${data.shop_verification.product_condition ?
                                                `<small class="text-muted d-block">Condition: ${data.shop_verification.product_condition}</small>` : ''}
                                        </div>
                                    </div>
                                ` : ''}

                                <!-- Final Approval Step (if shop approved) -->
                                ${data.shop_verification.status === 'completed' ? `
                                    <div class="verification-step ${data.final_approval.status === 'completed' ? 'completed' :
                                                                   data.final_approval.status === 'rejected' ? 'rejected' : 'pending'}">
                                        <div class="verification-step-icon">
                                            <i class="mdi ${data.final_approval.status === 'completed' ? 'mdi-check' :
                                                           data.final_approval.status === 'rejected' ? 'mdi-close' : 'mdi-clock'}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>Final Approval</strong>
                                            <div class="text-muted">
                                                ${data.final_approval.status === 'completed' ?
                                                    `Approved by ${data.final_approval.approver} on ${data.final_approval.date}` :
                                                  data.final_approval.status === 'rejected' ?
                                                    `Rejected by ${data.final_approval.approver} on ${data.final_approval.date}` :
                                                  'Pending final approval'}
                                            </div>
                                            ${data.final_approval.notes ?
                                                `<small class="text-muted">Notes: ${data.final_approval.notes}</small>` : ''}
                                        </div>
                                    </div>
                                ` : ''}

                                <!-- Refund Status (if approved) -->
                                ${data.final_approval.status === 'completed' ? `
                                    <div class="verification-step ${data.refund_status === 'processed' ? 'completed' :
                                                                   data.refund_status === 'failed' ? 'rejected' : 'pending'}">
                                        <div class="verification-step-icon">
                                            <i class="mdi ${data.refund_status === 'processed' ? 'mdi-check' :
                                                           data.refund_status === 'failed' ? 'mdi-close' : 'mdi-cash'}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>Refund Status</strong>
                                            <div class="text-muted">
                                                ${data.refund_status === 'processed' ?
                                                    `Refund processed on ${data.refund_date}` :
                                                  data.refund_status === 'failed' ?
                                                    'Refund failed' :
                                                  'Pending refund processing'}
                                            </div>
                                            ${data.refund_amount ?
                                                `<small class="text-success fw-bold">Amount: ₹${data.refund_amount}</small>` : ''}
                                            ${data.refund_id ?
                                                `<small class="text-muted d-block">Refund ID: ${data.refund_id}</small>` : ''}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;

                    $('#verificationContent').html(html);

                    // Show action buttons based on current status and user permissions
                    let actionButtons = '';

                    if (data.can_admin_verify && data.admin_verification.status === 'pending') {
                        actionButtons = `
                            <button type="button" class="btn btn-success admin-verify-btn"
                                    data-id="${data.id}">
                                <i class="mdi mdi-check me-1"></i> Verify
                            </button>
                            <button type="button" class="btn btn-danger admin-reject-btn"
                                    data-id="${data.id}">
                                <i class="mdi mdi-close me-1"></i> Reject
                            </button>
                        `;
                    } else if (data.can_shop_verify && data.shop_verification.status === 'pending') {
                        actionButtons = `
                            <button type="button" class="btn btn-success shop-verify-btn"
                                    data-id="${data.id}">
                                <i class="mdi mdi-store-check me-1"></i> Shop Verify
                            </button>
                            <button type="button" class="btn btn-danger shop-reject-btn"
                                    data-id="${data.id}">
                                <i class="mdi mdi-store-remove me-1"></i> Shop Reject
                            </button>
                        `;
                    } else if (data.can_final_approve && data.final_approval.status === 'pending') {
                        actionButtons = `
                            <button type="button" class="btn btn-success final-approve-btn"
                                    data-id="${data.id}">
                                <i class="mdi mdi-check-all me-1"></i> Final Approve
                            </button>
                            <button type="button" class="btn btn-danger final-reject-btn"
                                    data-id="${data.id}">
                                <i class="mdi mdi-close-circle me-1"></i> Final Reject
                            </button>
                        `;
                    } else if (data.can_process_refund && data.refund_status === 'pending') {
                        actionButtons = `
                            <button type="button" class="btn btn-warning process-refund-btn"
                                    data-id="${data.id}">
                                <i class="mdi mdi-cash-refund me-1"></i> Process Refund
                            </button>
                        `;
                    }

                    if (actionButtons) {
                        $('#actionButtons').html(actionButtons).show();
                    } else {
                        $('#actionButtons').hide();
                    }

                    const modal = new bootstrap.Modal(document.getElementById('verificationModal'));
                    modal.show();
                }

                // Admin Verify
                $(document).on('click', '.admin-verify-btn', function() {
                    const itemId = $(this).data('id');

                    Swal.fire({
                        title: 'Admin Verify Return',
                        html: `
                            <div class="text-start">
                                <label for="admin_refund_amount" class="form-label">Refund Amount (₹)</label>
                                <input type="number" class="form-control" id="admin_refund_amount"
                                       placeholder="Enter refund amount" step="0.01" min="0" required>
                                <label for="admin_verification_notes" class="form-label mt-2">Verification Notes (Optional)</label>
                                <textarea class="form-control" id="admin_verification_notes"
                                          rows="3" placeholder="Add verification notes..."></textarea>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Verify Request',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const amount = $('#admin_refund_amount').val();
                            if (!amount || amount <= 0) {
                                Swal.showValidationMessage('Please enter a valid refund amount');
                                return false;
                            }
                            return {
                                amount: amount,
                                notes: $('#admin_verification_notes').val()
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('returns.admin-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'approve',
                                    notes: result.value.notes,
                                    refund_amount: result.value.amount
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#verificationModal').modal('hide');
                                        returnsTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to verify return request', 'error');
                                }
                            });
                        }
                    });
                });

                // Admin Reject
                $(document).on('click', '.admin-reject-btn', function() {
                    const itemId = $(this).data('id');

                    Swal.fire({
                        title: 'Reject Return Request',
                        html: `
                            <label for="rejection_reason" class="form-label">Rejection Reason</label>
                            <textarea class="form-control" id="rejection_reason"
                                      rows="3" placeholder="Enter reason for rejection..." required></textarea>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Reject Request',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const reason = $('#rejection_reason').val();
                            if (!reason) {
                                Swal.showValidationMessage('Please enter rejection reason');
                                return false;
                            }
                            return reason;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('returns.admin-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'reject',
                                    notes: result.value
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#verificationModal').modal('hide');
                                        returnsTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to reject return request', 'error');
                                }
                            });
                        }
                    });
                });

                // Shop Verify (similar to admin verify but for shop)
                $(document).on('click', '.shop-verify-btn', function() {
                    const itemId = $(this).data('id');

                    Swal.fire({
                        title: 'Shop Verify Product',
                        html: `
                            <div class="text-start">
                                <label for="product_condition" class="form-label">Product Condition *</label>
                                <select class="form-select" id="product_condition" required>
                                    <option value="">Select Condition</option>
                                    <option value="Like New">Like New</option>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Damaged">Damaged</option>
                                </select>
                                <label for="shop_verification_notes" class="form-label mt-2">Shop Notes (Optional)</label>
                                <textarea class="form-control" id="shop_verification_notes"
                                          rows="3" placeholder="Add verification notes..."></textarea>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Verify Product',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const condition = $('#product_condition').val();
                            if (!condition) {
                                Swal.showValidationMessage('Please select product condition');
                                return false;
                            }
                            return {
                                condition: condition,
                                notes: $('#shop_verification_notes').val()
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('returns.shop-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'approve',
                                    product_condition: result.value.condition,
                                    notes: result.value.notes
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#verificationModal').modal('hide');
                                        returnsTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to verify product', 'error');
                                }
                            });
                        }
                    });
                });

                // Final Approve
                $(document).on('click', '.final-approve-btn', function() {
                    const itemId = $(this).data('id');

                    Swal.fire({
                        title: 'Final Approval',
                        html: `
                            <label for="final_approval_notes" class="form-label">Approval Notes (Optional)</label>
                            <textarea class="form-control" id="final_approval_notes"
                                      rows="3" placeholder="Add approval notes..."></textarea>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Approve Return',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('returns.final-approve') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'approve',
                                    notes: $('#final_approval_notes').val()
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#verificationModal').modal('hide');
                                        returnsTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to approve return', 'error');
                                }
                            });
                        }
                    });
                });

                // Process Refund
                $(document).on('click', '.process-refund-btn', function() {
                    const itemId = $(this).data('id');

                    Swal.fire({
                        title: 'Process Refund',
                        text: 'This will process the refund for the approved return',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Process Refund'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('returns.process-refund') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#verificationModal').modal('hide');
                                        returnsTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to process refund', 'error');
                                }
                            });
                        }
                    });
                });

                // Quick approve from table
                $(document).on('click', '.approve-btn', function(e) {
                    e.stopPropagation();
                    const itemId = $(this).data('id');
                    const row = $(this).closest('tr');

                    Swal.fire({
                        title: 'Quick Approve?',
                        text: 'This will approve the return request',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Approve'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('returns.quick-approve') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        returnsTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to approve request', 'error');
                                }
                            });
                        }
                    });
                });

                // Quick reject from table
                $(document).on('click', '.reject-btn', function(e) {
                    e.stopPropagation();
                    const itemId = $(this).data('id');

                    Swal.fire({
                        title: 'Quick Reject?',
                        text: 'This will reject the return request',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Reject',
                        input: 'textarea',
                        inputLabel: 'Rejection Reason',
                        inputPlaceholder: 'Enter reason for rejection...',
                        inputAttributes: {
                            'aria-label': 'Enter reason for rejection'
                        },
                        showCancelButton: true,
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Please enter a rejection reason';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('returns.quick-reject') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    reason: result.value
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        returnsTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to reject request', 'error');
                                }
                            });
                        }
                    });
                });

                // Refresh table every 30 seconds
                setInterval(function() {
                    returnsTable.ajax.reload(null, false);
                }, 30000);
            });
        </script>
    @endpush
@endsection
