@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <style>
            .replacement-badge {
                background-color: #e3f2fd;
                color: #1976d2;
                border: 1px solid #bbdefb;
            }

            .status-indicator {
                display: inline-block;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                margin-right: 5px;
            }

            .status-pending { background-color: #ffc107; }
            .status-approved { background-color: #198754; }
            .status-rejected { background-color: #dc3545; }
            .status-processing { background-color: #0d6efd; }
        </style>
    @endpush

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0 fw-semibold text-dark">
                            <i class="mdi mdi-autorenew me-2"></i>Replace Requests Management
                        </h4>
                        <p class="text-muted mt-1">Manage and process product replacement requests</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="mdi mdi-filter me-1"></i> Filter by Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item filter-status" data-status="all" href="#">All Requests</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item filter-status" data-status="pending_admin" href="#">Pending Admin</a></li>
                                <li><a class="dropdown-item filter-status" data-status="pending_shop" href="#">Pending Shop</a></li>
                                <li><a class="dropdown-item filter-status" data-status="pending_final" href="#">Pending Final</a></li>
                                <li><a class="dropdown-item filter-status" data-status="processing" href="#">Processing</a></li>
                                <li><a class="dropdown-item filter-status" data-status="completed" href="#">Completed</a></li>
                            </ul>
                        </div>

                        <a href="{{ url('admin.orders.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Orders
                        </a>
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
                                <p class="fw-medium text-muted mb-0">Processing</p>
                                <h2 class="mt-2 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{ $stats['processing'] ?? 0 }}">0</span>
                                </h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded-circle fs-2">
                                    <i class="mdi mdi-progress-wrench text-primary"></i>
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
                            <i class="mdi mdi-autorenew me-2"></i>Replace Requests
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="replaces-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Order</th>
                                        <th>Original Product</th>
                                        <th>Replacement Product</th>
                                        <th>Customer</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Replacement Order</th>
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

    <!-- Replace Details Modal -->
    <div class="modal fade" id="replaceDetailsModal" tabindex="-1" aria-labelledby="replaceDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replaceDetailsModalLabel">Replace Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="replaceDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div id="replaceActionButtons" style="display: none;">
                        <!-- Action buttons loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Select Replacement Product Modal -->
    <div class="modal fade" id="selectReplacementModal" tabindex="-1" aria-labelledby="selectReplacementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectReplacementModalLabel">Select Replacement Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="selectReplacementContent">
                    <!-- Products list loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmReplacementBtn" disabled>
                        Select Product
                    </button>
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
                let selectedProductId = null;
                let currentItemId = null;

                // Initialize DataTable
                const replacesTable = $('#replaces-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('replaces.datatable') }}",
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
                            data: 'original_product',
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
                            data: 'replacement_product',
                            name: 'replacement_product',
                            render: function(data) {
                                if (data && data.id) {
                                    return `
                                        <div class="d-flex align-items-center">
                                            <img src="${data.image_url}" alt="${data.name}"
                                                 class="rounded me-2" width="40" height="40">
                                            <div>
                                                <strong>${data.name}</strong><br>
                                                <small class="text-muted">SKU: ${data.sku}</small>
                                            </div>
                                        </div>
                                    `;
                                } else if (data && data.same_product) {
                                    return `<span class="badge replacement-badge">Same Product</span>`;
                                }
                                return '<span class="text-muted">Not Selected</span>';
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
                            data: 'replace_reason',
                            name: 'replace_reason',
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
                                    'processing': 'primary',
                                    'completed': 'success',
                                    'rejected': 'danger'
                                };

                                const statusText = {
                                    'pending_admin': 'Pending Admin',
                                    'pending_shop': 'Pending Shop',
                                    'pending_final': 'Pending Final',
                                    'processing': 'Processing',
                                    'completed': 'Completed',
                                    'rejected': 'Rejected'
                                };

                                return `<span class="badge bg-${statusColors[data] || 'secondary'}">
                                    <span class="status-indicator status-${data}"></span>
                                    ${statusText[data] || data}
                                </span>`;
                            }
                        },
                        {
                            data: 'replacement_order',
                            name: 'replacement_order',
                            render: function(data) {
                                if (data && data.id) {
                                    return `
                                        <div>
                                            <a href="${data.view_url}" target="_blank" class="text-primary">
                                                ${data.order_no}
                                            </a>
                                            <br>
                                            <small class="text-muted">${data.status}</small>
                                        </div>
                                    `;
                                }
                                return '<span class="text-muted">-</span>';
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
                                        ${data.can_create_order ?
                                            `<button type="button" class="btn btn-outline-primary create-order-btn"
                                                    data-id="${data.id}" title="Create Order">
                                                <i class="mdi mdi-cart-plus"></i>
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
                    replacesTable.ajax.reload();
                });

                // View details
                $(document).on('click', '.view-details-btn', function() {
                    const itemId = $(this).data('id');
                    loadReplaceDetails(itemId);
                });

                function loadReplaceDetails(itemId) {
                    $('#replaceDetailsContent').html(`
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading details...</p>
                        </div>
                    `);

                    $.ajax({
                        url: "{{ route('replaces.details', ':id') }}".replace(':id', itemId),
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                renderReplaceDetails(response.data);
                            } else {
                                $('#replaceDetailsContent').html(`
                                    <div class="alert alert-danger">
                                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                                        ${response.message || 'Failed to load details'}
                                    </div>
                                `);
                            }
                        },
                        error: function() {
                            $('#replaceDetailsContent').html(`
                                <div class="alert alert-danger">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    Error loading details
                                </div>
                            `);
                        }
                    });
                }

                function renderReplaceDetails(data) {
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
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-2">Original Product:</h6>
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="${data.original_product.image_url}" alt="${data.original_product.name}"
                                                         class="rounded me-2" width="50" height="50">
                                                    <div>
                                                        <strong>${data.original_product.name}</strong>
                                                        <p class="mb-0"><small>Qty: ${data.original_product.quantity}</small></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-2">Replacement:</h6>
                                                ${data.replacement_product.id ?
                                                    `<div class="d-flex align-items-center mb-3">
                                                        <img src="${data.replacement_product.image_url}" alt="${data.replacement_product.name}"
                                                             class="rounded me-2" width="50" height="50">
                                                        <div>
                                                            <strong>${data.replacement_product.name}</strong>
                                                            <p class="mb-0"><small>SKU: ${data.replacement_product.sku}</small></p>
                                                        </div>
                                                    </div>` :
                                                    `<div class="alert alert-info">
                                                        <i class="mdi mdi-information-outline me-2"></i>
                                                        Same product will be replaced
                                                    </div>`
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Replace Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Replace Reason:</strong>
                                    <p class="mt-1">${data.replace_reason}</p>
                                </div>

                                ${data.replace_reason_details ? `
                                    <div class="mb-3">
                                        <strong>Additional Details:</strong>
                                        <p class="mt-1">${data.replace_reason_details}</p>
                                    </div>
                                ` : ''}

                                ${data.replace_image_url ? `
                                    <div class="mb-3">
                                        <strong>Replace Image:</strong>
                                        <div class="mt-2">
                                            <img src="${data.replace_image_url}" alt="Replace Image"
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

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Verification Process</h6>
                            </div>
                            <div class="card-body">
                                <!-- Admin Verification -->
                                <div class="verification-step mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="status-indicator status-${data.admin_verification.status} me-2"></span>
                                        <strong>Admin Verification</strong>
                                    </div>
                                    <div class="ps-4">
                                        ${data.admin_verification.status === 'completed' ?
                                            `<p class="mb-1">✓ Verified by ${data.admin_verification.verifier} on ${data.admin_verification.date}</p>` :
                                          data.admin_verification.status === 'rejected' ?
                                            `<p class="mb-1 text-danger">✗ Rejected by ${data.admin_verification.verifier} on ${data.admin_verification.date}</p>` :
                                            `<p class="mb-1 text-warning">⏳ Pending admin verification</p>`
                                        }
                                        ${data.admin_verification.notes ?
                                            `<small class="text-muted">Notes: ${data.admin_verification.notes}</small>` : ''}
                                    </div>
                                </div>

                                <!-- Shop Verification -->
                                ${data.admin_verification.status === 'completed' ? `
                                    <div class="verification-step mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="status-indicator status-${data.shop_verification.status} me-2"></span>
                                            <strong>Shop Verification</strong>
                                        </div>
                                        <div class="ps-4">
                                            ${data.shop_verification.status === 'completed' ?
                                                `<p class="mb-1">✓ Verified by shop on ${data.shop_verification.date}</p>` :
                                              data.shop_verification.status === 'rejected' ?
                                                `<p class="mb-1 text-danger">✗ Rejected by shop on ${data.shop_verification.date}</p>` :
                                                `<p class="mb-1 text-warning">⏳ Pending shop verification</p>`
                                            }
                                            ${data.shop_verification.notes ?
                                                `<small class="text-muted">Notes: ${data.shop_verification.notes}</small>` : ''}
                                            ${data.shop_verification.product_condition ?
                                                `<small class="text-muted d-block">Condition: ${data.shop_verification.product_condition}</small>` : ''}
                                        </div>
                                    </div>
                                ` : ''}

                                <!-- Final Approval -->
                                ${data.shop_verification.status === 'completed' ? `
                                    <div class="verification-step mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="status-indicator status-${data.final_approval.status} me-2"></span>
                                            <strong>Final Approval</strong>
                                        </div>
                                        <div class="ps-4">
                                            ${data.final_approval.status === 'completed' ?
                                                `<p class="mb-1">✓ Approved by ${data.final_approval.approver} on ${data.final_approval.date}</p>` :
                                              data.final_approval.status === 'rejected' ?
                                                `<p class="mb-1 text-danger">✗ Rejected by ${data.final_approval.approver} on ${data.final_approval.date}</p>` :
                                                `<p class="mb-1 text-warning">⏳ Pending final approval</p>`
                                            }
                                            ${data.final_approval.notes ?
                                                `<small class="text-muted">Notes: ${data.final_approval.notes}</small>` : ''}
                                        </div>
                                    </div>
                                ` : ''}

                                <!-- Replacement Order -->
                                ${data.replacement_order ? `
                                    <div class="verification-step">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="status-indicator status-completed me-2"></span>
                                            <strong>Replacement Order</strong>
                                        </div>
                                        <div class="ps-4">
                                            <p class="mb-1">
                                                ✓ Order Created:
                                                <a href="${data.replacement_order.view_url}" target="_blank" class="ms-1">
                                                    ${data.replacement_order.order_no}
                                                </a>
                                            </p>
                                            <small class="text-muted">Status: ${data.replacement_order.status}</small><br>
                                            <small class="text-muted">Created: ${data.replacement_order.created_at}</small>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;

                    $('#replaceDetailsContent').html(html);

                    // Show action buttons based on permissions
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
                    } else if (data.can_create_order && data.final_approval.status === 'completed') {
                        actionButtons = `
                            <button type="button" class="btn btn-primary create-replacement-order-btn"
                                    data-id="${data.id}">
                                <i class="mdi mdi-cart-plus me-1"></i> Create Order
                            </button>
                        `;
                    }

                    if (actionButtons) {
                        $('#replaceActionButtons').html(actionButtons).show();
                    } else {
                        $('#replaceActionButtons').hide();
                    }

                    const modal = new bootstrap.Modal(document.getElementById('replaceDetailsModal'));
                    modal.show();
                }

                $(document).on('click', '.admin-verify-btn', function() {
                    currentItemId = $(this).data('id');
                    loadAvailableProducts(currentItemId);
                });

                function loadAvailableProducts(itemId) {
                    $('#selectReplacementContent').html(`
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading available products...</p>
                        </div>
                    `);

                    $.ajax({
                        url: "{{ url('replaces.available-products', ':id') }}".replace(':id', itemId),
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                renderProductsList(response.products, response.original_product);
                            } else {
                                $('#selectReplacementContent').html(`
                                    <div class="alert alert-danger">
                                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                                        ${response.message}
                                    </div>
                                `);
                            }
                        },
                        error: function() {
                            $('#selectReplacementContent').html(`
                                <div class="alert alert-danger">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    Error loading products
                                </div>
                            `);
                        }
                    });

                    const modal = new bootstrap.Modal(document.getElementById('selectReplacementModal'));
                    modal.show();
                }

                function renderProductsList(products, originalProduct) {
                    let html = `
                        <div class="mb-4">
                            <h6>Original Product:</h6>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <img src="${originalProduct.image_url}" alt="${originalProduct.name}"
                                             class="rounded me-3" width="60" height="60">
                                        <div>
                                            <h6 class="mb-1">${originalProduct.name}</h6>
                                            <p class="mb-1"><small>SKU: ${originalProduct.sku}</small></p>
                                            <p class="mb-0"><small>Price: ₹${originalProduct.price}</small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6>Select Replacement Product:</h6>
                        <div class="list-group">
                            <!-- Option for same product -->
                            <div class="list-group-item product-option" data-product-id="0">
                                <div class="d-flex align-items-center">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="replacementProduct"
                                               id="sameProduct" value="0">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Same Product (${originalProduct.name})</h6>
                                        <p class="mb-0 text-muted">Replace with the same product</p>
                                    </div>
                                </div>
                            </div>
                    `;

                    products.forEach(product => {
                        html += `
                            <div class="list-group-item product-option" data-product-id="${product.id}">
                                <div class="d-flex align-items-center">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="replacementProduct"
                                               id="product_${product.id}" value="${product.id}">
                                    </div>
                                    <img src="${product.image_url}" alt="${product.name}"
                                         class="rounded me-3" width="50" height="50">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${product.name}</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><small>SKU: ${product.sku}</small></p>
                                                <p class="mb-0"><small>Price: ₹${product.price}</small></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><small>Stock: ${product.stock}</small></p>
                                                <p class="mb-0"><small>Category: ${product.category}</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div>`;

                    $('#selectReplacementContent').html(html);

                    // Enable/disable confirm button based on selection
                    $('.product-option').on('click', function() {
                        selectedProductId = $(this).data('product-id');
                        $(this).find('input[type="radio"]').prop('checked', true);
                        $('#confirmReplacementBtn').prop('disabled', false);
                    });

                    $('#confirmReplacementBtn').off('click').on('click', function() {
                        processAdminVerification(currentItemId, selectedProductId);
                    });
                }

                function processAdminVerification(itemId, newProductId) {
                    $('#selectReplacementModal').modal('hide');

                    Swal.fire({
                        title: 'Verify Replace Request',
                        html: `
                            <label for="replace_admin_notes" class="form-label">Verification Notes (Optional)</label>
                            <textarea class="form-control" id="replace_admin_notes"
                                      rows="3" placeholder="Add verification notes..."></textarea>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Verify Request',
                        confirmButtonColor: '#17a2b8',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('replaces.admin-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'approve',
                                    new_product_id: newProductId == 0 ? null : newProductId,
                                    notes: $('#replace_admin_notes').val()
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
                                        $('#replaceDetailsModal').modal('hide');
                                        replacesTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to verify replace request', 'error');
                                }
                            });
                        }
                    });
                }

                // Create Replacement Order
                $(document).on('click', '.create-replacement-order-btn', function() {
                    const itemId = $(this).data('id');

                    Swal.fire({
                        title: 'Create Replacement Order',
                        text: 'This will create a new order for the replacement product',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Create Order'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('replaces.create-order') }}",
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
                                            timer: 2000
                                        });
                                        $('#replaceDetailsModal').modal('hide');
                                        replacesTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to create order', 'error');
                                }
                            });
                        }
                    });
                });

                // Quick actions from table
                $(document).on('click', '.approve-btn', function(e) {
                    e.stopPropagation();
                    const itemId = $(this).data('id');
                    // Similar to return approve logic
                });

                $(document).on('click', '.reject-btn', function(e) {
                    e.stopPropagation();
                    const itemId = $(this).data('id');

                    Swal.fire({
                        title: 'Reject Replace Request',
                        input: 'textarea',
                        inputLabel: 'Rejection Reason',
                        inputPlaceholder: 'Enter reason for rejection...',
                        showCancelButton: true,
                        confirmButtonText: 'Reject',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#dc3545',
                        preConfirm: (reason) => {
                            if (!reason) {
                                Swal.showValidationMessage('Please enter a reason');
                            }
                            return reason;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('replaces.quick-reject') }}",
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
                                        replacesTable.ajax.reload();
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
                    replacesTable.ajax.reload(null, false);
                }, 30000);
            });
        </script>
    @endpush
@endsection
