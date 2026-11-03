@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <style>
            .change-request-item {
                border: 1px solid #e3e6f0;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 5px;
            }

            .change-field {
                margin-bottom: 10px;
                padding: 8px;
                background-color: #f8f9fa;
                border-radius: 4px;
            }

            .old-value {
                color: #dc3545;
                text-decoration: line-through;
            }

            .new-value {
                color: #28a745;
                font-weight: bold;
            }

            .search-suggestions {
                position: absolute;
                z-index: 1000;
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                max-height: 200px;
                overflow-y: auto;
                display: none;
                width: calc(100% - 100px);
                margin-top: 2px;
            }

            .search-suggestions.active {
                display: block;
            }

            .suggestion-item {
                padding: 8px 12px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
            }

            .suggestion-item:hover {
                background-color: #f8f9fa;
            }

            .suggestion-loading {
                padding: 12px;
                text-align: center;
                color: #6c757d;
                font-style: italic;
            }

            .form-required::after {
                content: " *";
                color: #dc3545;
            }

            .bank-info-section {
                background-color: #f8f9fa;
                border-radius: 5px;
                padding: 15px;
                margin-top: 10px;
            }
        </style>
    @endpush

    <!-- Change Request Details Modal -->
    <div class="modal fade" id="changeRequestModal" tabindex="-1" aria-labelledby="changeRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeRequestModalLabel">Change Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="requestDetailsContainer">
                    <!-- Request details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="modalRejectBtn">Reject</button>
                    <button type="button" class="btn btn-success" id="modalApproveBtn">Approve</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Request Modal -->
    <div class="modal fade" id="rejectRequestModal" tabindex="-1" aria-labelledby="rejectRequestModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectRequestModalLabel">Reject Change Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reject_request_id">
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Rejection Reason *</label>
                        <textarea class="form-control" id="reject_reason" rows="4" placeholder="Please provide a reason for rejection..."
                            required></textarea>
                        <div class="form-text">This reason will be visible to the shop owner.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectRequest">Reject Request</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Shop for User Modal -->
    <div class="modal fade" id="createShopModal" tabindex="-1" aria-labelledby="createShopModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createShopModalLabel">
                        <i class="mdi mdi-store-plus me-2"></i>Create Shop for User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="create_shop_user_id">

                    <!-- User Information Card -->
                    <div id="userInfoContainer" class="mb-4">
                        <!-- User info will be loaded here -->
                    </div>

                    <form id="createShopForm">
                        @csrf

                        <!-- Shop Basic Information -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="mdi mdi-store me-2"></i>Shop Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Shop Name</label>
                                        <input type="text" name="name" class="form-control required-field"
                                            placeholder="Enter Shop Name" required>
                                        <div class="invalid-feedback">Please enter shop name.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Owner Name</label>
                                        <input type="text" name="owner_name" class="form-control required-field"
                                            placeholder="Enter Owner Name" required readonly>
                                        <div class="invalid-feedback">Please enter owner name.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Owner Phone</label>
                                        <input type="text" name="owner_phone" class="form-control required-field"
                                            placeholder="Enter Owner Phone" required readonly>
                                        <div class="invalid-feedback">Please enter owner phone.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Commission Percentage (%)</label>
                                        <input type="number" step="0.01" name="commission_percentage"
                                            class="form-control required-field" value="10" min="0"
                                            max="100" required>
                                        <div class="invalid-feedback">Please enter commission percentage.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="Enter Email">
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label form-required">Address</label>
                                        <textarea name="address" class="form-control required-field" rows="3" placeholder="Enter Shop Address"
                                            required></textarea>
                                        <div class="invalid-feedback">Please enter address.</div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input type="radio" id="modal_status_active" name="status"
                                                    class="form-check-input" value="1" checked>
                                                <label class="form-check-label" for="modal_status_active">Active</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="modal_status_inactive" name="status"
                                                    class="form-check-input" value="0">
                                                <label class="form-check-label"
                                                    for="modal_status_inactive">Inactive</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="mdi mdi-image me-2"></i>Shop Image (Optional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Upload Shop Image</label>
                                    <div class="file-upload-wrapper">
                                        <input type="file" name="image" id="shop_image" class="form-control"
                                            accept="image/*" onchange="previewShopImage(event)">
                                        <small class="form-text text-muted">Allowed formats: JPEG, PNG, JPG, GIF. Max size:
                                            2MB</small>
                                    </div>
                                </div>

                                <!-- Image Preview -->
                                <div class="image-preview-wrapper mt-3" id="shopImagePreviewWrapper"
                                    style="display: none;">
                                    <h6>Preview:</h6>
                                    <div class="border rounded p-2 text-center">
                                        <img id="shopImagePreview" src="#" alt="Shop Image Preview"
                                            style="max-width: 100%; max-height: 200px; object-fit: contain;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="removeShopImage()">
                                                <i class="mdi mdi-delete"></i> Remove Image
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="mdi mdi-map-marker me-2"></i>Location Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label form-required">Search Location</label>
                                    <div class="input-group">
                                        <input type="text" id="modal_location_search" class="form-control"
                                            placeholder="Search for a location..." required>
                                        <button class="btn btn-primary" type="button" id="modal_search_btn">
                                            <i class="mdi mdi-magnify"></i> Search
                                        </button>
                                        <button class="btn btn-info" type="button" id="modal_current_location_btn">
                                            <i class="mdi mdi-crosshairs-gps"></i> Current
                                        </button>
                                    </div>
                                    <div class="invalid-feedback location-feedback">Please select a location.</div>
                                    <div id="modal_search_suggestions" class="search-suggestions"></div>
                                </div>

                                <!-- Map Container -->
                                <div class="mb-3">
                                    <div id="modal_map"
                                        style="height: 350px; border: 1px solid #ddd; border-radius: 4px;"></div>
                                    <div class="map-coordinates mt-2 p-2 bg-light rounded">
                                        <small>
                                            <strong>Coordinates:</strong>
                                            Lat: <span id="modal_lat_display">26.8467</span>,
                                            Lng: <span id="modal_lng_display">80.9462</span>
                                        </small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Latitude</label>
                                        <input type="text" name="latitude" id="modal_latitude"
                                            class="form-control required-field" placeholder="Latitude" value="26.8467"
                                            required readonly>
                                        <div class="invalid-feedback">Please select a location to get latitude.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Longitude</label>
                                        <input type="text" name="longitude" id="modal_longitude"
                                            class="form-control required-field" placeholder="Longitude" value="80.9462"
                                            required readonly>
                                        <div class="invalid-feedback">Please select a location to get longitude.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Information - REQUIRED -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="mdi mdi-bank me-2"></i>Bank Information <span
                                        class="text-danger">*</span></h6>
                            </div>
                            <div class="card-body bank-info-section">
                                <div class="alert alert-info mb-3">
                                    <i class="mdi mdi-information me-2"></i>
                                    All bank information fields are required for payment processing.
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control required-field"
                                            placeholder="Enter Bank Name" required>
                                        <div class="invalid-feedback">Please enter bank name.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Account Number</label>
                                        <input type="text" name="account_number" class="form-control required-field"
                                            placeholder="Enter Account Number" required>
                                        <div class="invalid-feedback">Please enter account number.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">IFSC Code</label>
                                        <input type="text" name="ifsc_code" class="form-control required-field"
                                            placeholder="Enter IFSC Code" required>
                                        <div class="invalid-feedback">Please enter IFSC code.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Account Holder Name</label>
                                        <input type="text" name="holder_name" class="form-control required-field"
                                            placeholder="Enter Holder Name" required>
                                        <div class="invalid-feedback">Please enter account holder name.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-required">Branch</label>
                                        <input type="text" name="branch" class="form-control required-field"
                                            placeholder="Enter Branch" required>
                                        <div class="invalid-feedback">Please enter branch.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn add-btn" id="confirmCreateShop">
                        <i class="mdi mdi-check me-1"></i>Create Shop
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">Shop Management</h4>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-shadow mb-4">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-bordered" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#shops_tab" role="tab"
                                data-tab="shops">
                                All Shops
                                <span class="badge bg-secondary ms-1" id="shops-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#change_requests_tab" role="tab"
                                data-tab="change-requests">
                                Pending Change Requests
                                <span class="badge bg-warning ms-1" id="change-requests-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#unapproved_shops_tab" role="tab"
                                data-tab="unapproved-shops">
                                Unapproved Shops
                                <span class="badge bg-danger ms-1" id="unapproved-shops-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#approved_shops_tab" role="tab"
                                data-tab="approved-shops">
                                Approved Shops
                                <span class="badge bg-success ms-1" id="approved-shops-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#rejected_shops_tab" role="tab"
                                data-tab="rejected-shops">
                                Rejected Shops
                                <span class="badge bg-dark ms-1" id="rejected-shops-count">0</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">
                        <!-- All Shops Tab -->
                        <div class="tab-pane active" id="shops_tab" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="shops-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Image</th>
                                            <th>Owner</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Approval</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Change Requests Tab -->
                        <div class="tab-pane" id="change_requests_tab" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="change-requests-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Shop Name</th>
                                            <th>Image</th>
                                            <th>Requested By</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Unapproved Shops Tab -->
                        <div class="tab-pane" id="unapproved_shops_tab" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="unapproved-shops-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Image</th>
                                            <th>Owner</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Rejection Reason</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Approved Shops Tab -->
                        <div class="tab-pane" id="approved_shops_tab" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="approved-shops-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Image</th>
                                            <th>Owner</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Approved At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Rejected Shops Tab -->
                        <div class="tab-pane" id="rejected_shops_tab" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="rejected-shops-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Image</th>
                                            <th>Owner</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Rejection Reason</th>
                                            <th>Rejected At</th>
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

    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                var dataTables = {};
                var currentTab = localStorage.getItem('shopManagementTab') || 'shops';
                let modalMap, modalMarker, modalAutocomplete;
                let modalSearchTimeout;
                let isLocationSelected = false;

                const tabs = ['shops', 'change-requests', 'unapproved-shops', 'approved-shops', 'rejected-shops'];

                // Initialize the correct tab
                setTimeout(() => {
                    $('.nav-link[data-tab="' + currentTab + '"]').tab('show');
                }, 100);

                // Initialize all DataTables
                tabs.forEach(tabId => {
                    initDataTable(tabId);
                });

                // Tab change handler
                $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    currentTab = $(e.target).data('tab');
                    localStorage.setItem('shopManagementTab', currentTab);

                    if (dataTables[currentTab]) {
                        dataTables[currentTab].draw();
                    }
                });

                // Initialize DataTable
                function initDataTable(tabId) {
                    const tableId = tabId.replace(/-/g, '-') + '-table';
                    dataTables[tabId] = $('#' + tableId).DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url('/admin/shops') }}",
                            data: function(d) {
                                d.tab = tabId;
                            },
                            error: function(xhr, error, thrown) {
                                console.error('DataTable error for tab ' + tabId + ':', error);
                                Swal.fire({
                                    title: "Error!",
                                    text: "Failed to load table data",
                                    icon: "error"
                                });
                            }
                        },
                        language: {
                            search: "_INPUT_",
                            searchPlaceholder: "Search..."
                        },
                        order: [
                            [0, 'desc']
                        ],
                        columns: getColumnsForTab(tabId),
                        drawCallback: function(settings) {
                            // Update count for this tab
                            const count = settings.json?.recordsTotal || 0;
                            $('#' + tabId.replace(/-/g, '-') + '-count').text(count);
                        },
                        initComplete: function() {
                            console.log('DataTable initialized for tab:', tabId);
                        }
                    });
                }

                function getColumnsForTab(tabId) {
                    switch (tabId) {
                        case 'shops':
                            return [{
                                    data: 'date',
                                    name: 'date'
                                },
                                {
                                    data: 'name',
                                    name: 'name'
                                },
                                {
                                    data: 'image',
                                    name: 'image'
                                },
                                {
                                    data: 'owner',
                                    name: 'owner'
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
                                    name: 'status'
                                },
                                {
                                    data: 'approved',
                                    name: 'approved',
                                    render: function(data, type, row) {
                                        if (row.approved == 0)
                                        return '<span class="badge bg-warning">Unapproved</span>';
                                        if (row.approved == 1)
                                        return '<span class="badge bg-success">Approved</span>';
                                        if (row.approved == -1)
                                        return '<span class="badge bg-danger">Rejected</span>';
                                        return '<span class="badge bg-secondary">Unknown</span>';
                                    }
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false
                                }
                            ];
                        case 'change-requests':
                            return [{
                                    data: 'date',
                                    name: 'date'
                                },
                                {
                                    data: 'shop',
                                    name: 'shop'
                                },
                                {
                                    data: 'image',
                                    name: 'image'
                                },
                                {
                                    data: 'user',
                                    name: 'user'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false
                                }
                            ];
                        case 'unapproved-shops':
                            return [{
                                    data: 'date',
                                    name: 'date'
                                },
                                {
                                    data: 'name',
                                    name: 'name'
                                },
                                {
                                    data: 'image',
                                    name: 'image'
                                },
                                {
                                    data: 'owner',
                                    name: 'owner'
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
                                    data: 'rejection_reason',
                                    name: 'rejection_reason'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false
                                }
                            ];
                        case 'approved-shops':
                            return [{
                                    data: 'date',
                                    name: 'date'
                                },
                                {
                                    data: 'name',
                                    name: 'name'
                                },
                                {
                                    data: 'image',
                                    name: 'image'
                                },
                                {
                                    data: 'owner',
                                    name: 'owner'
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
                                    data: 'approved_at',
                                    name: 'approved_at'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false
                                }
                            ];
                        case 'rejected-shops':
                            return [{
                                    data: 'date',
                                    name: 'date'
                                },
                                {
                                    data: 'name',
                                    name: 'name'
                                },
                                {
                                    data: 'image',
                                    name: 'image'
                                },
                                {
                                    data: 'owner',
                                    name: 'owner'
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
                                    data: 'rejection_reason',
                                    name: 'rejection_reason'
                                },
                                {
                                    data: 'rejected_at',
                                    name: 'rejected_at'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false
                                }
                            ];
                    }
                }

                // Shop status toggle (active/inactive)
                $(document).on('change', '.shop-status-toggle', function() {
                    var $this = $(this);
                    var id = $this.data('id');
                    var status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/shop-status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        },
                        success: function(response) {
                            if (!response.success) {
                                $this.prop('checked', !$this.prop('checked'));
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message || "Failed to update status",
                                    icon: "error"
                                });
                            }
                        },
                        error: function() {
                            $this.prop('checked', !$this.prop('checked'));
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to update status",
                                icon: "error"
                            });
                        }
                    });
                });

                // Review shop change request
                $(document).on('click', '.review-shop-request', function() {
                    var requestId = $(this).data('id');

                    $.ajax({
                        url: "{{ url('/admin/shops/change-request-details/') }}/" + requestId,
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                displayShopRequestDetails(response.data, response.shop_data,
                                    requestId);
                                $('#changeRequestModal').modal('show');
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message ||
                                        "Failed to load request details",
                                    icon: "error"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to load request details",
                                icon: "error"
                            });
                        }
                    });
                });

                function displayShopRequestDetails(request, shopData) {
                    const container = $('#requestDetailsContainer');
                    container.empty();

                    const changes = request.changes || {};
                    const oldData = request.old_data || {};
                    let changesHtml = '';

                    Object.entries(changes).forEach(([field, newValue]) => {
                        const oldValue = oldData[field] || 'Not set';
                        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                        // Special handling for image fields
                        if (field === 'image') {
                            // Get the old image URL from old_data
                            const oldImageUrl = oldValue && oldValue !== 'Not set' ? oldValue : null;

                            // Build new image URL - check if it's already a full URL
                            let newImageUrl = null;
                            if (newValue) {
                                if (newValue.startsWith('http')) {
                                    // It's already a full URL
                                    newImageUrl = newValue;
                                } else if (request.temp_image_url) {
                                    // Use the temp image URL provided by backend
                                    newImageUrl = request.temp_image_url;
                                } else {
                                    // Build URL from temp image name
                                    newImageUrl = `/storage/temp/shop-updates/${newValue}`;
                                }
                            }

                            changesHtml += `
                        <div class="change-field mb-3">
                            <strong>${fieldName}:</strong>
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><small>Current Image:</small></p>
                                        ${oldImageUrl ?
                                            `<div class="text-center">
                                                        <img src="${oldImageUrl}" alt="Current Shop Image"
                                                             style="max-width: 100%; max-height: 150px; object-fit: contain;"
                                                             class="img-thumbnail border-danger">
                                                        <p class="text-muted mt-1"><small>Current Image</small></p>
                                                    </div>` :
                                            '<div class="text-center p-3 bg-light rounded">' +
                                            '<i class="mdi mdi-image-off text-muted" style="font-size: 48px;"></i>' +
                                            '<p class="text-muted mt-2">No image set</p></div>'}
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><small>Proposed New Image:</small></p>
                                        ${newImageUrl ?
                                            `<div class="text-center">
                                                        <img src="${newImageUrl}" alt="Proposed New Image"
                                                             style="max-width: 100%; max-height: 150px; object-fit: contain;"
                                                             class="img-thumbnail border-success">
                                                        <p class="text-muted mt-1"><small>New Image</small></p>
                                                        <p class="text-muted"><small>Filename: ${newValue}</small></p>
                                                    </div>` :
                                            '<div class="text-center p-3 bg-light rounded">' +
                                            '<i class="mdi mdi-image-off text-muted" style="font-size: 48px;"></i>' +
                                            '<p class="text-muted mt-2">No image change</p></div>'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                        } else {
                            changesHtml += `
                        <div class="change-field mb-3">
                            <strong>${fieldName}:</strong>
                            <div class="mt-2 d-flex align-items-center">
                                <div class="old-value-container p-2 bg-light rounded flex-fill">
                                    <small class="text-muted d-block">Current:</small>
                                    <span class="text-danger">${oldValue}</span>
                                </div>
                                <i class="mdi mdi-arrow-right text-primary mx-3"></i>
                                <div class="new-value-container p-2 bg-light rounded flex-fill">
                                    <small class="text-muted d-block">Proposed:</small>
                                    <span class="text-success fw-bold">${newValue}</span>
                                </div>
                            </div>
                        </div>
                    `;
                        }
                    });

                    const html = `
                <div class="mb-4">
                    <h5>Shop Update Request Details</h5>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Shop Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="120">Shop:</th>
                                            <td>${shopData.name || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Current Owner:</th>
                                            <td>${shopData.user?.name || 'N/A'} (ID: ${shopData.user?.id || 'N/A'})</td>
                                        </tr>
                                        <tr>
                                            <th>Requested by:</th>
                                            <td>${request.user?.name || 'Unknown'} (ID: ${request.user?.id || 'N/A'})</td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td>${new Date(request.created_at).toLocaleString()}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Request Status</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="120">Status:</th>
                                            <td><span class="badge bg-warning">Pending Review</span></td>
                                        </tr>
                                        <tr>
                                            <th>Request ID:</th>
                                            <td>#${request.id}</td>
                                        </tr>
                                        <tr>
                                            <th>Changes:</th>
                                            <td>${Object.keys(changes).length} field(s)</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6>Proposed Changes:</h6>
                    <div class="card">
                        <div class="card-body">
                            ${changesHtml}
                        </div>
                    </div>
                </div>

                <input type="hidden" id="current_request_id" value="${request.id}">
            `;

                    container.html(html);
                }

                $('#modalApproveBtn').on('click', function() {
                    var requestId = $('#current_request_id').val();
                    if (!requestId) return;
                    approveRequest(requestId);
                });

                $('#modalRejectBtn').on('click', function() {
                    var requestId = $('#current_request_id').val();
                    if (!requestId) return;
                    $('#changeRequestModal').modal('hide');
                    $('#reject_request_id').val(requestId);
                    $('#reject_reason').val('');
                    $('#rejectRequestModal').modal('show');
                });

                $(document).on('click', '.approve-shop-request', function() {
                    var requestId = $(this).data('id');
                    approveRequest(requestId);
                });

                $(document).on('click', '.reject-shop-request', function() {
                    var requestId = $(this).data('id');
                    $('#reject_request_id').val(requestId);
                    $('#reject_reason').val('');
                    $('#rejectRequestModal').modal('show');
                });

                function approveRequest(requestId) {
                    Swal.fire({
                        title: "Approve Changes?",
                        text: "Are you sure you want to approve these changes?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, approve!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                url: "{{ url('/admin/shops/approve-change-request/') }}/" + requestId,
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.success) {
                                        $('#changeRequestModal').modal('hide');
                                        dataTables['change-requests'].draw();
                                        dataTables['shops'].draw();

                                        Swal.fire({
                                            title: "Approved!",
                                            text: response.message,
                                            icon: "success"
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Failed to approve request",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                }

                // Confirm reject request
                $('#confirmRejectRequest').on('click', function() {
                    var requestId = $('#reject_request_id').val();
                    var reason = $('#reject_reason').val().trim();

                    if (!reason || reason.length < 10) {
                        Swal.fire({
                            title: "Warning!",
                            text: "Please provide a valid rejection reason (minimum 10 characters)",
                            icon: "warning"
                        });
                        return;
                    }

                    $.ajax({
                        url: "{{ url('/admin/shops/reject-change-request/') }}/" + requestId,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            reason: reason
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#rejectRequestModal').modal('hide');
                                $('#changeRequestModal').modal('hide');
                                dataTables['change-requests'].draw();
                                dataTables['shops'].draw();

                                Swal.fire({
                                    title: "Rejected!",
                                    text: response.message,
                                    icon: "success"
                                });
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message,
                                    icon: "error"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to reject request",
                                icon: "error"
                            });
                        }
                    });
                });

                // Delete shop
                $(document).on('click', '.delete-shop', function() {
                    var shopId = $(this).data('id');

                    Swal.fire({
                        title: "Delete Shop?",
                        text: "This action cannot be undone!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f34e4e",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete it!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/shops/') }}/" + shopId,
                                data: {
                                    "_token": "{{ csrf_token() }}"
                                },
                                success: function(data) {
                                    if (data.success) {
                                        // Refresh all shop-related tables
                                        dataTables['shops']?.draw();
                                        dataTables['unapproved-shops']?.draw();
                                        dataTables['approved-shops']?.draw();
                                        dataTables['rejected-shops']?.draw();

                                        Swal.fire({
                                            title: "Deleted!",
                                            text: data.message,
                                            icon: "success"
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: data.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Failed to delete shop",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });

                // Approve shop (from unapproved shops tab)
                $(document).on('click', '.approve-shop', function() {
                    var shopId = $(this).data('id');

                    Swal.fire({
                        title: "Approve Shop?",
                        text: "Are you sure you want to approve this shop?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, approve!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                url: "{{ url('/admin/shops/approve/') }}/" + shopId,
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Refresh all shop-related tables
                                        dataTables['shops']?.draw();
                                        dataTables['unapproved-shops']?.draw();
                                        dataTables['approved-shops']?.draw();
                                        dataTables['rejected-shops']?.draw();

                                        Swal.fire({
                                            title: "Approved!",
                                            text: response.message,
                                            icon: "success"
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Failed to approve shop",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });

                // Reject shop (from unapproved shops tab)
                $(document).on('click', '.reject-shop', function() {
                    var shopId = $(this).data('id');

                    Swal.fire({
                        title: "Reject Shop?",
                        text: "Please provide a reason for rejection",
                        icon: "warning",
                        input: "textarea",
                        inputLabel: "Rejection Reason",
                        inputPlaceholder: "Enter reason for rejection...",
                        inputAttributes: {
                            minlength: "10"
                        },
                        showCancelButton: true,
                        confirmButtonColor: "#f34e4e",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Reject",
                        preConfirm: (reason) => {
                            if (!reason || reason.length < 10) {
                                Swal.showValidationMessage(
                                    "Please enter a reason (minimum 10 characters)");
                            }
                            return reason;
                        }
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                url: "{{ url('/admin/shops/reject/') }}/" + shopId,
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    reason: result.value
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Refresh all shop-related tables
                                        dataTables['shops']?.draw();
                                        dataTables['unapproved-shops']?.draw();
                                        dataTables['approved-shops']?.draw();
                                        dataTables['rejected-shops']?.draw();

                                        Swal.fire({
                                            title: "Rejected!",
                                            text: response.message,
                                            icon: "success"
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Failed to reject shop",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });

                // Unapprove shop (from approved shops tab)
                $(document).on('click', '.unapprove-shop', function() {
                    var shopId = $(this).data('id');

                    Swal.fire({
                        title: "Unapprove Shop?",
                        text: "Are you sure you want to unapprove this shop?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#f34e4e",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, unapprove!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                url: "{{ url('/admin/shops/unapprove/') }}/" + shopId,
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Refresh all shop-related tables
                                        dataTables['shops']?.draw();
                                        dataTables['unapproved-shops']?.draw();
                                        dataTables['approved-shops']?.draw();
                                        dataTables['rejected-shops']?.draw();

                                        Swal.fire({
                                            title: "Unapproved!",
                                            text: response.message,
                                            icon: "success"
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Failed to unapprove shop",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });

                // Create shop for user (if you still need this functionality)
                $(document).on('click', '.create-shop-for-user', function() {
                    var userId = $(this).data('id');
                    $('#create_shop_user_id').val(userId);

                    // Load user info
                    $.ajax({
                        url: "{{ url('/admin/users/details/') }}/" + userId,
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                const user = response.user;
                                const userInfo = `
                            <div class="alert alert-info border-0 shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="mdi mdi-account-circle font-size-24 me-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">Creating shop for:</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Name</small>
                                                <strong>${user.name}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Email</small>
                                                <strong>${user.email}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Phone</small>
                                                <strong>${user.phone || 'Not provided'}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                                $('#userInfoContainer').html(userInfo);

                                // Auto-fill form with user data
                                $('#createShopForm input[name="name"]').val(user.name + "'s Shop");
                                $('#createShopForm input[name="email"]').val(user.email);
                                $('#createShopForm input[name="owner_name"]').val(user.name).prop(
                                    'readonly', true);
                                $('#createShopForm input[name="owner_phone"]').val(user.phone || '')
                                    .prop('readonly', true);

                                // Reset location
                                isLocationSelected = false;
                                clearLocationError();

                                $('#createShopModal').modal('show');
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message || "Failed to load user details",
                                    icon: "error"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to load user details",
                                icon: "error"
                            });
                        }
                    });
                });

                // Initialize modal map when modal is shown
                $('#createShopModal').on('shown.bs.modal', function() {
                    if (!modalMap) {
                        setTimeout(() => {
                            initModalMap();
                        }, 100);
                    } else {
                        google.maps.event.trigger(modalMap, 'resize');
                        modalMap.setCenter(modalMarker.getPosition());
                    }
                });

                // Confirm create shop
                $('#confirmCreateShop').on('click', function() {
                    if (!validateCreateShopForm()) {
                        Swal.fire({
                            title: "Validation Error!",
                            html: "Please fill in all required fields correctly.<br><small class='text-muted'>Note: All bank information is required for payment processing.</small>",
                            icon: "warning"
                        });
                        return;
                    }

                    var userId = $('#create_shop_user_id').val();
                    var formData = $('#createShopForm').serializeArray();

                    Swal.fire({
                        title: "Create Shop?",
                        html: "Are you sure you want to create a shop for this user?<br><small class='text-muted'>This will create the shop with all the provided information including bank details.</small>",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, create!"
                    }).then(function(result) {
                        if (result.value) {
                            var data = {
                                _token: "{{ csrf_token() }}",
                                user_id: userId
                            };

                            formData.forEach(item => {
                                if (item.name !== '_token') {
                                    data[item.name] = item.value;
                                }
                            });

                            // Show loading
                            Swal.fire({
                                title: 'Creating Shop...',
                                html: 'Please wait while we create the shop.<br><small class="text-muted">This may take a few moments.</small>',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: "{{ url('/admin/shops/create-for-user') }}",
                                type: "POST",
                                data: data,
                                success: function(response) {
                                    Swal.close();
                                    if (response.success) {
                                        $('#createShopModal').modal('hide');

                                        // Refresh all shop tables
                                        dataTables['shops']?.draw();
                                        dataTables['unapproved-shops']?.draw();
                                        dataTables['approved-shops']?.draw();
                                        dataTables['rejected-shops']?.draw();

                                        Swal.fire({
                                            title: "Success!",
                                            text: response.message,
                                            icon: "success",
                                            timer: 3000,
                                            showConfirmButton: false
                                        });

                                        // Reset form
                                        $('#createShopForm')[0].reset();
                                        $('#userInfoContainer').empty();
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    Swal.close();
                                    const message = xhr.responseJSON?.message ||
                                        "Failed to create shop";
                                    Swal.fire({
                                        title: "Error!",
                                        text: message,
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });

                // Reset modal when closed
                $('#createShopModal').on('hidden.bs.modal', function() {
                    $('#createShopForm')[0].reset();
                    $('#userInfoContainer').empty();
                    $('#modal_search_suggestions').removeClass('active');
                    $('.required-field').removeClass('is-invalid');

                    // Reset readonly fields
                    $('#createShopForm input[name="owner_name"]').prop('readonly', false);
                    $('#createShopForm input[name="owner_phone"]').prop('readonly', false);

                    // Reset location
                    isLocationSelected = false;

                    // Reset map to default location
                    if (modalMap && modalMarker) {
                        const defaultLat = 26.8467;
                        const defaultLng = 80.9462;
                        updateModalLocation(defaultLat, defaultLng, '');
                        clearLocationError();
                    }
                });

                // Real-time validation
                $(document).on('blur', '.required-field', function() {
                    const $field = $(this);
                    const value = $field.val().trim();

                    // Skip validation for auto-filled fields that might be empty
                    if ($field.attr('name') === 'owner_phone' && value === '') {
                        $field.removeClass('is-invalid');
                        return;
                    }

                    if (value === '' && $field.attr('name') !== 'email') {
                        $field.addClass('is-invalid');
                    } else {
                        $field.removeClass('is-invalid');

                        // Email validation
                        if ($field.attr('name') === 'email' && value !== '') {
                            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailPattern.test(value)) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(
                                'Please enter a valid email address.');
                            }
                        }

                        // Phone validation
                        if ($field.attr('name') === 'phone' || $field.attr('name') === 'owner_phone') {
                            if (value !== '' && !/^[0-9]{10,15}$/.test(value)) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(
                                    'Please enter a valid phone number (10-15 digits).');
                            }
                        }

                        // Account number validation
                        if ($field.attr('name') === 'account_number') {
                            if (value !== '' && !/^[0-9]{9,18}$/.test(value)) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(
                                    'Please enter a valid account number (9-18 digits).');
                            }
                        }

                        // IFSC code validation
                        if ($field.attr('name') === 'ifsc_code') {
                            if (value !== '' && !/^[A-Z]{4}0[A-Z0-9]{6}$/.test(value)) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(
                                    'Please enter a valid IFSC code (e.g., SBIN0001234).');
                            }
                        }
                    }
                });

                // Modal Map Functionality
                function initModalMap() {
                    const defaultLat = 26.8467;
                    const defaultLng = 80.9462;

                    modalMap = new google.maps.Map(document.getElementById('modal_map'), {
                        center: {
                            lat: defaultLat,
                            lng: defaultLng
                        },
                        zoom: 13,
                        mapTypeControl: true,
                        streetViewControl: true,
                        fullscreenControl: false
                    });

                    modalMarker = new google.maps.Marker({
                        position: {
                            lat: defaultLat,
                            lng: defaultLng
                        },
                        map: modalMap,
                        draggable: true,
                        title: 'Shop Location'
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: 'Shop Location'
                    });

                    modalMarker.addListener('click', function() {
                        infoWindow.open(modalMap, modalMarker);
                    });

                    modalMarker.addListener('dragend', function(event) {
                        isLocationSelected = true;
                        updateModalLocation(event.latLng.lat(), event.latLng.lng(), 'Dragged Location');
                        clearLocationError();
                    });

                    modalMap.addListener('click', function(event) {
                        isLocationSelected = true;
                        updateModalLocation(event.latLng.lat(), event.latLng.lng(), 'Selected Location');
                        clearLocationError();
                    });

                    // Autocomplete
                    const searchInput = document.getElementById('modal_location_search');
                    modalAutocomplete = new google.maps.places.Autocomplete(searchInput);
                    modalAutocomplete.bindTo('bounds', modalMap);

                    modalAutocomplete.addListener('place_changed', function() {
                        const place = modalAutocomplete.getPlace();
                        if (!place.geometry || !place.geometry.location) {
                            showLocationError("No location found for: '" + place.name + "'");
                            return;
                        }
                        isLocationSelected = true;
                        updateModalLocation(
                            place.geometry.location.lat(),
                            place.geometry.location.lng(),
                            place.formatted_address || place.name
                        );
                        clearLocationError();
                    });

                    // Search suggestions
                    const suggestionsDiv = document.getElementById('modal_search_suggestions');
                    searchInput.addEventListener('input', function() {
                        const query = this.value.trim();
                        clearTimeout(modalSearchTimeout);

                        if (query.length < 3) {
                            suggestionsDiv.classList.remove('active');
                            return;
                        }

                        modalSearchTimeout = setTimeout(() => {
                            fetchModalSuggestions(query);
                        }, 300);
                    });

                    document.addEventListener('click', function(e) {
                        if (!e.target.closest('#modal_location_search') && !e.target.closest(
                                '#modal_search_suggestions')) {
                            suggestionsDiv.classList.remove('active');
                        }
                    });

                    // Search button
                    document.getElementById('modal_search_btn').addEventListener('click', function() {
                        const query = searchInput.value.trim();
                        if (query) {
                            searchModalLocation(query);
                            suggestionsDiv.classList.remove('active');
                        }
                    });

                    // Current location button
                    document.getElementById('modal_current_location_btn').addEventListener('click', function() {
                        const btn = this;
                        const originalHtml = btn.innerHTML;

                        if (navigator.geolocation) {
                            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Loading...';
                            btn.disabled = true;

                            navigator.geolocation.getCurrentPosition(
                                function(position) {
                                    isLocationSelected = true;
                                    updateModalLocation(
                                        position.coords.latitude,
                                        position.coords.longitude,
                                        'Your Current Location'
                                    );
                                    btn.innerHTML = originalHtml;
                                    btn.disabled = false;
                                    clearLocationError();
                                },
                                function(error) {
                                    showLocationError("Could not get your location: " + error.message);
                                    btn.innerHTML = originalHtml;
                                    btn.disabled = false;
                                }
                            );
                        } else {
                            showLocationError("Geolocation is not supported by your browser");
                        }
                    });
                }

                function fetchModalSuggestions(query) {
                    const suggestionsDiv = document.getElementById('modal_search_suggestions');
                    suggestionsDiv.innerHTML = '<div class="suggestion-loading">Searching...</div>';
                    suggestionsDiv.classList.add('active');

                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({
                        address: query
                    }, function(results, status) {
                        if (status === 'OK' && results.length > 0) {
                            suggestionsDiv.innerHTML = '';
                            results.slice(0, 5).forEach(result => {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                div.textContent = result.formatted_address;
                                div.addEventListener('click', function() {
                                    isLocationSelected = true;
                                    updateModalLocation(
                                        result.geometry.location.lat(),
                                        result.geometry.location.lng(),
                                        result.formatted_address
                                    );
                                    document.getElementById('modal_location_search').value =
                                        result.formatted_address;
                                    suggestionsDiv.classList.remove('active');
                                    clearLocationError();
                                });
                                suggestionsDiv.appendChild(div);
                            });
                        } else {
                            suggestionsDiv.innerHTML = '<div class="suggestion-loading">No results found</div>';
                            setTimeout(() => {
                                suggestionsDiv.classList.remove('active');
                            }, 2000);
                        }
                    });
                }

                function searchModalLocation(query) {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({
                        address: query
                    }, function(results, status) {
                        if (status === 'OK' && results.length > 0) {
                            const result = results[0];
                            isLocationSelected = true;
                            updateModalLocation(
                                result.geometry.location.lat(),
                                result.geometry.location.lng(),
                                result.formatted_address
                            );
                            clearLocationError();
                        } else {
                            showLocationError("Location not found");
                        }
                    });
                }

                function updateModalLocation(lat, lng, displayName = null) {
                    document.getElementById('modal_latitude').value = lat.toFixed(6);
                    document.getElementById('modal_longitude').value = lng.toFixed(6);
                    document.getElementById('modal_lat_display').textContent = lat.toFixed(6);
                    document.getElementById('modal_lng_display').textContent = lng.toFixed(6);

                    const position = {
                        lat: lat,
                        lng: lng
                    };
                    modalMarker.setPosition(position);
                    modalMap.setCenter(position);
                    modalMap.setZoom(15);

                    if (displayName) {
                        document.getElementById('modal_location_search').value = displayName;
                    }
                }

                function showLocationError(message) {
                    const input = document.getElementById('modal_location_search');
                    const feedback = document.querySelector('.location-feedback');
                    input.classList.add('is-invalid');
                    if (feedback) {
                        feedback.textContent = message;
                        feedback.style.display = 'block';
                    }
                }

                function clearLocationError() {
                    const input = document.getElementById('modal_location_search');
                    const feedback = document.querySelector('.location-feedback');
                    input.classList.remove('is-invalid');
                    if (feedback) {
                        feedback.style.display = 'none';
                    }
                }

                function previewShopImage(event) {
                    const input = event.target;
                    const preview = document.getElementById('shopImagePreview');
                    const wrapper = document.getElementById('shopImagePreviewWrapper');

                    if (input.files && input.files[0]) {
                        const file = input.files[0];

                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid File Type',
                                text: 'Please upload only JPEG, PNG, JPG, or GIF images.'
                            });
                            input.value = '';
                            return;
                        }

                        // Validate file size (2MB max)
                        const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                        if (file.size > maxSize) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Too Large',
                                text: `File size is ${(file.size / (1024*1024)).toFixed(2)} MB. Maximum allowed is 2 MB.`
                            });
                            input.value = '';
                            return;
                        }

                        const reader = new FileReader();

                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            wrapper.style.display = 'block';

                            // Add some styling to the preview
                            preview.onload = function() {
                                console.log('Image loaded successfully');
                            };
                        }

                        reader.onerror = function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load image. Please try another file.'
                            });
                            input.value = '';
                            wrapper.style.display = 'none';
                        }

                        reader.readAsDataURL(file);
                    }
                }

                function removeShopImage() {
                    const input = document.getElementById('shop_image');
                    const preview = document.getElementById('shopImagePreview');
                    const wrapper = document.getElementById('shopImagePreviewWrapper');

                    // Reset the input
                    input.value = '';

                    // Reset preview
                    preview.src = '#';
                    wrapper.style.display = 'none';

                    console.log('Image removed');
                }

                // Form validation
                function validateCreateShopForm() {
                    let isValid = true;
                    const form = document.getElementById('createShopForm');

                    // Clear previous validation
                    $('.required-field').removeClass('is-invalid');

                    // Check required fields (except email which is optional)
                    $('.required-field').each(function() {
                        const $field = $(this);
                        const value = $field.val().trim();

                        // Skip validation for fields that should be auto-filled but might be empty
                        if ($field.attr('name') === 'owner_phone' && value === '') {
                            // Allow empty owner_phone since it's auto-filled from user data
                            return true;
                        }

                        if (value === '' && $field.attr('name') !== 'email') { // Email is optional
                            $field.addClass('is-invalid');
                            isValid = false;
                        }

                        // Email validation if provided
                        if ($field.attr('name') === 'email' && value !== '') {
                            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailPattern.test(value)) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(
                                'Please enter a valid email address.');
                                isValid = false;
                            }
                        }

                        // Phone validation
                        if ($field.attr('name') === 'owner_phone') {
                            if (value !== '' && !/^[0-9]{10,15}$/.test(value)) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(
                                    'Please enter a valid phone number (10-15 digits).');
                                isValid = false;
                            }
                        }

                        // Account number validation
                        if ($field.attr('name') === 'account_number') {
                            if (value !== '' && !/^[0-9]{9,18}$/.test(value)) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(
                                    'Please enter a valid account number (9-18 digits).');
                                isValid = false;
                            }
                        }

                        // IFSC code validation
                        if ($field.attr('name') === 'ifsc_code') {
                            if (value !== '' && !/^[A-Z]{4}0[A-Z0-9]{6}$/.test(value)) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(
                                    'Please enter a valid IFSC code (e.g., SBIN0001234).');
                                isValid = false;
                            }
                        }

                        const imageInput = document.getElementById('shop_image');

                        if (imageInput.files.length > 0) {
                            const fileSize = imageInput.files[0].size / 1024 / 1024;

                            if (fileSize > 2) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Invalid Image',
                                    text: `Image size is ${fileSize.toFixed(2)} MB. Maximum allowed is 2 MB.`,
                                });

                                imageInput.value = '';
                                isValid = false;
                            }
                        }
                    });

                    return isValid;
                }

            });
        </script>
    @endpush
@endsection
