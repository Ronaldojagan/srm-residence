@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .changes-diff {
                max-height: 300px;
                overflow-y: auto;
            }
            .change-item {
                border-left: 3px solid #007bff;
                padding-left: 10px;
                margin-bottom: 15px;
            }
            .change-item.removed {
                border-left-color: #dc3545;
            }
            .change-item.added {
                border-left-color: #28a745;
            }
            .old-value {
                background-color: #ffeaea;
                padding: 5px;
                border-radius: 3px;
                text-decoration: line-through;
            }
            .new-value {
                background-color: #eaffea;
                padding: 5px;
                border-radius: 3px;
            }
            .field-name {
                font-weight: 600;
                color: #495057;
            }
            .request-details-modal .modal-lg {
                max-width: 900px;
            }
            .filter-tag {
                display: inline-block;
                background: #e9ecef;
                padding: 3px 8px;
                border-radius: 4px;
                margin-right: 5px;
                margin-bottom: 5px;
                font-size: 12px;
            }
            .remove-filter {
                cursor: pointer;
                margin-left: 5px;
                color: #6c757d;
            }
            .remove-filter:hover {
                color: #dc3545;
            }
            .stock-low {
                color: #dc3545;
                font-weight: bold;
            }
            .stock-ok {
                color: #28a745;
            }
            .table-actions {
                min-width: 150px;
            }
        </style>
    @endpush

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body reject-modal">
                    <input type="hidden" id="reject_product_id">
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" id="reject_reason" placeholder="Please provide a reason for rejection..." required></textarea>
                        <div class="form-text">This reason will be visible to the shop owner.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmReject">Reject Product</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Details Modal -->
    <div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestDetailsModalLabel">Product Update Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body request-details-modal">
                    <div id="requestDetailsContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="approveFromModal">Approve</button>
                    <button type="button" class="btn btn-danger" id="rejectFromModal">Reject</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Request Modal -->
    <div class="modal fade" id="rejectRequestModal" tabindex="-1" aria-labelledby="rejectRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectRequestModalLabel">Reject Update Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reject_request_id">
                    <div class="mb-3">
                        <label for="reject_request_reason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" id="reject_request_reason"
                                  placeholder="Please provide a reason for rejection..."
                                  rows="4" required></textarea>
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

    <!-- State Management Modal -->
    <div class="modal fade" id="stateManagementModal" tabindex="-1" aria-labelledby="stateManagementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stateManagementModalLabel">Saved States</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Tab</th>
                                    <th>Filters</th>
                                    <th>Date Saved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="savedStatesList">
                                <!-- States will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        Saved states are stored in your browser's localStorage. They will not be available on other devices
                        or browsers.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">Products</h4>
                <small class="text-muted" id="currentStateInfo"></small>
            </div>
        </div>
        <div class="col-md-6 mt-3 flex-wrap align-items-center justify-content-sm-end">
            <div class="d-flex gap-2 float-end">
                <div class="btn-group d-none" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="saveCurrentState"
                        title="Save current state">
                        <i class="mdi mdi-content-save me-1"></i> Save
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="manageStates" title="Manage saved states">
                        <i class="mdi mdi-cog"></i>
                    </button>
                </div>
                {{-- <a href="{{ url('/admin/products/create') }}" type="button"
                    class="btn add-btn waves-effect waves-light button-color text-white">
                    <i class="mdi mdi-plus-circle me-1"></i> Create Product
                </a> --}}
            </div>
        </div>
    </div>

    <!-- Status Tabs and Counters -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Product Status</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary d-none" id="resetToDefault">
                            <i class="mdi mdi-refresh me-1"></i> Reset to Default
                        </button>
                    </div>

                    <ul class="nav nav-tabs nav-bordered" role="tablist" id="statusTabs">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active has-state" data-bs-toggle="tab" href="#all" role="tab"
                                data-tab="all">
                                All Products
                                <span class="badge bg-secondary ms-1" id="all-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link has-state" data-bs-toggle="tab" href="#approved" role="tab"
                                data-tab="approved">
                                Approved
                                <span class="badge bg-success ms-1" id="approved-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link has-state" data-bs-toggle="tab" href="#pending" role="tab"
                                data-tab="pending">
                                Pending Review
                                <span class="badge bg-warning ms-1" id="pending-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link has-state" data-bs-toggle="tab" href="#rejected" role="tab"
                                data-tab="rejected">
                                Rejected
                                <span class="badge bg-danger ms-1" id="rejected-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link has-state" data-bs-toggle="tab" href="#deactive" role="tab"
                                data-tab="deactive">
                                Deactivated
                                <span class="badge bg-secondary ms-1" id="deactive-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link has-state" data-bs-toggle="tab" href="#update_requests" role="tab"
                                data-tab="update_requests">
                                Update Requests
                                <span class="badge bg-warning ms-1" id="update_requests-count">0</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Active Filters Display -->
                    <div class="active-filters mt-3 p-2 bg-light border rounded d-none" id="activeFiltersContainer">
                        <div class="d-flex align-items-center mb-1">
                            <small class="text-muted me-2"><i class="mdi mdi-filter me-1"></i>Active Filters:</small>
                            <div id="activeFilters"></div>
                            <div class="ms-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger" id="clearAllFilters">
                                    <i class="mdi mdi-close-circle me-1"></i> Clear All
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3 g-2">
                        <div class="col-md-3">
                            <div class="card product-index-card1">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-dark mb-0">Total</h6>
                                            <h4 class="mb-0" id="total-products-count">0</h4>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title product-index-card-icon1 rounded-circle">
                                                <i class="mdi mdi-package-variant"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card product-index-card2">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-dark mb-0">Approved</h6>
                                            <h4 class="mb-0 text-dark" id="approved-total-count">0</h4>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title product-index-card-icon2 rounded-circle">
                                                <i class="mdi mdi-check-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card product-index-card3">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-dark mb-0">Pending</h6>
                                            <h4 class="mb-0 text-dark" id="pending-total-count">0</h4>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title product-index-card-icon3 rounded-circle">
                                                <i class="mdi mdi-clock"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card product-index-card4">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-dark mb-0">Rejected</h6>
                                            <h4 class="mb-0 text-dark" id="rejected-total-count">0</h4>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title product-index-card-icon4 rounded-circle">
                                                <i class="mdi mdi-close-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Table -->
    <div class="row mt-2">
        <div class="col-lg-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3 g-2">
                        <div class="col-md-3">
                            <label for="shop_filter" class="form-label">Shop</label>
                            <select id="shop_filter" class="form-select select2" data-filter="shop">
                                <option value="">All Shops</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="category_filter" class="form-label">Category</label>
                            <select id="category_filter" class="form-select select2" data-filter="category">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" id="date_from" class="form-control" data-filter="date_from">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" id="date_to" class="form-control" data-filter="date_to">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="button" id="reset_filters" class="btn btn-outline-secondary">
                                    <i class="mdi mdi-refresh me-1"></i> Reset
                                </button>
                                <button type="button" id="saveFilterSet" class="btn btn-outline-primary d-none"
                                    title="Save filter set">
                                    <i class="mdi mdi-bookmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="all" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="all-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Product ID</th>
                                            <th>Name</th>
                                            <th>Shop</th>
                                            <th>Category</th>
                                            <th>Image</th>
                                            <th>Stock</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th class="table-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="approved" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="approved-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Product ID</th>
                                            <th>Name</th>
                                            <th>Shop</th>
                                            <th>Category</th>
                                            <th>Image</th>
                                            <th>Stock</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th class="table-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="pending" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="pending-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Product ID</th>
                                            <th>Name</th>
                                            <th>Shop</th>
                                            <th>Category</th>
                                            <th>Image</th>
                                            <th>Stock</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th class="table-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="rejected" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="rejected-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Product ID</th>
                                            <th>Name</th>
                                            <th>Shop</th>
                                            <th>Category</th>
                                            <th>Image</th>
                                            <th>Stock</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th class="table-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="deactive" role="tabpanel">
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="deactive-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Product ID</th>
                                            <th>Name</th>
                                            <th>Shop</th>
                                            <th>Category</th>
                                            <th>Image</th>
                                            <th>Stock</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th class="table-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="update_requests" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="mdi mdi-information me-2"></i>
                                This tab shows product update requests from shops. Review and approve/reject changes.
                            </div>
                            <div class="table-responsive table-border">
                                <table class="table table-striped data-table" id="update_requests-table">
                                    <thead class="theme-menu-color">
                                        <tr>
                                            <th>Date</th>
                                            <th>Product</th>
                                            <th>Shop</th>
                                            <th>Type</th>
                                            <th>Requester</th>
                                            <th>Changes</th>
                                            <th>Actions</th>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                // State management object
                const StateManager = {
                    storageKey: 'product_management_state',
                    currentState: null,

                    // Initialize state from localStorage
                    init: function() {
                        this.loadState();
                        this.setupEventListeners();
                    },

                    // Load state from localStorage
                    loadState: function() {
                        const savedState = localStorage.getItem(this.storageKey);
                        if (savedState) {
                            try {
                                this.currentState = JSON.parse(savedState);
                                this.applyState();
                            } catch (e) {
                                console.error('Error loading state:', e);
                                this.resetToDefault();
                            }
                        } else {
                            this.resetToDefault();
                        }
                    },

                    // Save state to localStorage
                    saveState: function(name = null) {
                        const state = this.getCurrentState();

                        if (name) {
                            state.name = name;
                        }

                        this.currentState = state;
                        localStorage.setItem(this.storageKey, JSON.stringify(state));
                        this.showToast('State saved successfully!', 'success');

                        // Update save button
                        $('#saveCurrentState').addClass('saved');
                        setTimeout(() => {
                            $('#saveCurrentState').removeClass('saved');
                        }, 2000);
                    },

                    // Get current state from UI
                    getCurrentState: function() {
                        return {
                            tab: this.getActiveTab(),
                            filters: this.getFilters(),
                            timestamp: new Date().toISOString(),
                            name: this.currentState?.name || 'Current State'
                        };
                    },

                    // Apply state to UI
                    applyState: function() {
                        if (!this.currentState) return;

                        // Apply tab
                        this.setActiveTab(this.currentState.tab);

                        // Apply filters
                        this.setFilters(this.currentState.filters);

                        // Update UI
                        this.updateActiveFilters();
                    },

                    // Reset to default state
                    resetToDefault: function() {
                        this.currentState = {
                            tab: 'all',
                            filters: {},
                            timestamp: new Date().toISOString(),
                            name: 'Default State'
                        };
                        localStorage.removeItem(this.storageKey);
                        this.applyState();
                        this.showToast('Reset to default state', 'info');
                    },

                    // Get active tab
                    getActiveTab: function() {
                        const activeTab = $('.nav-link.active[data-tab]');
                        return activeTab.length ? activeTab.data('tab') : 'all';
                    },

                    // Set active tab
                    setActiveTab: function(tab) {
                        $(`.nav-link[data-tab="${tab}"]`).tab('show');
                    },

                    // Get current filters
                    getFilters: function() {
                        const filters = {};

                        // Shop filter
                        const shopId = $('#shop_filter').val();
                        if (shopId) {
                            filters.shop_id = shopId;
                            filters.shop_name = $('#shop_filter option:selected').text();
                        }

                        // Category filter
                        const categoryId = $('#category_filter').val();
                        if (categoryId) {
                            filters.category_id = categoryId;
                            filters.category_name = $('#category_filter option:selected').text();
                        }

                        // Date filters
                        const dateFrom = $('#date_from').val();
                        const dateTo = $('#date_to').val();
                        if (dateFrom) filters.date_from = dateFrom;
                        if (dateTo) filters.date_to = dateTo;

                        return filters;
                    },

                    // Set filters
                    setFilters: function(filters) {
                        // Shop filter
                        if (filters.shop_id) {
                            $('#shop_filter').val(filters.shop_id).trigger('change');
                        }

                        // Category filter
                        if (filters.category_id) {
                            $('#category_filter').val(filters.category_id).trigger('change');
                        }

                        // Date filters
                        if (filters.date_from) {
                            $('#date_from').val(filters.date_from);
                        }
                        if (filters.date_to) {
                            $('#date_to').val(filters.date_to);
                        }
                    },

                    // Update active filters display
                    updateActiveFilters: function() {
                        const filters = this.getFilters();
                        const container = $('#activeFiltersContainer');
                        const display = $('#activeFilters');

                        display.empty();

                        let hasFilters = false;

                        if (filters.shop_id) {
                            hasFilters = true;
                            display.append(`
                                <span class="filter-tag">
                                    Shop: ${filters.shop_name}
                                    <span class="remove-filter" data-filter="shop">
                                        <i class="mdi mdi-close"></i>
                                    </span>
                                </span>
                            `);
                        }

                        if (filters.category_id) {
                            hasFilters = true;
                            display.append(`
                                <span class="filter-tag">
                                    Category: ${filters.category_name}
                                    <span class="remove-filter" data-filter="category">
                                        <i class="mdi mdi-close"></i>
                                    </span>
                                </span>
                            `);
                        }

                        if (filters.date_from) {
                            hasFilters = true;
                            display.append(`
                                <span class="filter-tag">
                                    From: ${filters.date_from}
                                    <span class="remove-filter" data-filter="date_from">
                                        <i class="mdi mdi-close"></i>
                                    </span>
                                </span>
                            `);
                        }

                        if (filters.date_to) {
                            hasFilters = true;
                            display.append(`
                                <span class="filter-tag">
                                    To: ${filters.date_to}
                                    <span class="remove-filter" data-filter="date_to">
                                        <i class="mdi mdi-close"></i>
                                    </span>
                                </span>
                            `);
                        }

                        if (hasFilters) {
                            container.removeClass('d-none');
                        } else {
                            container.addClass('d-none');
                        }
                    },

                    // Clear specific filter
                    clearFilter: function(filterName) {
                        switch (filterName) {
                            case 'shop':
                                $('#shop_filter').val('').trigger('change');
                                break;
                            case 'category':
                                $('#category_filter').val('').trigger('change');
                                break;
                            case 'date_from':
                                $('#date_from').val('');
                                break;
                            case 'date_to':
                                $('#date_to').val('');
                                break;
                        }
                        this.saveState();
                    },

                    // Clear all filters
                    clearAllFilters: function() {
                        $('#shop_filter').val('').trigger('change');
                        $('#category_filter').val('').trigger('change');
                        $('#date_from').val('');
                        $('#date_to').val('');
                        this.saveState();
                    },

                    showToast: function(message, type = 'info') {
                        const toast = $(`
                            <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
                                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="d-flex">
                                        <div class="toast-body">
                                            ${message}
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                </div>
                            </div>
                        `);

                        $('body').append(toast);
                        const toastElement = toast.find('.toast');
                        const bsToast = new bootstrap.Toast(toastElement);
                        bsToast.show();

                        toastElement.on('hidden.bs.toast', function() {
                            toast.remove();
                        });
                    },

                    setupEventListeners: function() {
                        const self = this;

                        // Save current state
                        $('#saveCurrentState').on('click', function() {
                            Swal.fire({
                                title: 'Save Current State',
                                input: 'text',
                                inputLabel: 'State Name (optional)',
                                inputPlaceholder: 'Enter a name for this state...',
                                showCancelButton: true,
                                confirmButtonText: 'Save',
                                cancelButtonText: 'Cancel',
                                inputValidator: (value) => {
                                    if (value && value.length > 50) {
                                        return 'State name must be less than 50 characters';
                                    }
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    self.saveState(result.value || null);
                                }
                            });
                        });

                        $('#manageStates').on('click', function() {
                            self.showStateManagementModal();
                        });

                        $('#resetToDefault').on('click', function() {
                            Swal.fire({
                                title: 'Reset to Default?',
                                text: 'This will clear all saved filters and reset to default view.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes, reset!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    self.resetToDefault();
                                }
                            });
                        });

                        $('#clearAllFilters').on('click', function() {
                            self.clearAllFilters();
                        });

                        $(document).on('click', '.remove-filter', function() {
                            const filterName = $(this).data('filter');
                            self.clearFilter(filterName);
                        });

                        $('#saveFilterSet').on('click', function() {
                            const filters = self.getFilters();
                            if (Object.keys(filters).length === 0) {
                                Swal.fire({
                                    title: 'No Filters',
                                    text: 'There are no filters to save.',
                                    icon: 'info'
                                });
                                return;
                            }

                            Swal.fire({
                                title: 'Save Filter Set',
                                input: 'text',
                                inputLabel: 'Filter Set Name',
                                inputPlaceholder: 'Enter a name for this filter set...',
                                showCancelButton: true,
                                confirmButtonText: 'Save',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed && result.value) {
                                    self.saveFilterSet(result.value, filters);
                                }
                            });
                        });

                        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                            const tab = $(e.target).data('tab');
                            if (tab) {
                                self.currentState.tab = tab;
                                self.saveState();
                            }
                        });

                        $('#shop_filter, #category_filter, #date_from, #date_to').on('change', function() {
                            self.updateActiveFilters();
                            self.saveState();
                        });

                        $(window).on('beforeunload', function() {
                            self.saveState();
                        });

                        setInterval(() => {
                            self.saveState();
                        }, 30000);
                    },

                    saveFilterSet: function(name, filters) {
                        const filterSets = JSON.parse(localStorage.getItem('product_filter_sets') || '[]');

                        const existingIndex = filterSets.findIndex(set => set.name === name);

                        const filterSet = {
                            name: name,
                            filters: filters,
                            timestamp: new Date().toISOString()
                        };

                        if (existingIndex > -1) {
                            filterSets[existingIndex] = filterSet;
                        } else {
                            filterSets.push(filterSet);
                        }

                        localStorage.setItem('product_filter_sets', JSON.stringify(filterSets));

                        Swal.fire({
                            title: 'Success!',
                            text: `Filter set "${name}" saved successfully.`,
                            icon: 'success'
                        });
                    },

                    loadFilterSets: function() {
                        return JSON.parse(localStorage.getItem('product_filter_sets') || '[]');
                    },

                    applyFilterSet: function(filterSet) {
                        this.setFilters(filterSet.filters);
                        this.saveState();

                        Swal.fire({
                            title: 'Filters Applied!',
                            text: `Filter set "${filterSet.name}" applied successfully.`,
                            icon: 'success'
                        });
                    },

                    deleteFilterSet: function(name) {
                        const filterSets = this.loadFilterSets();
                        const newFilterSets = filterSets.filter(set => set.name !== name);
                        localStorage.setItem('product_filter_sets', JSON.stringify(newFilterSets));

                        Swal.fire({
                            title: 'Deleted!',
                            text: `Filter set "${name}" deleted successfully.`,
                            icon: 'success'
                        });
                    },

                    showStateManagementModal: function() {
                        const filterSets = this.loadFilterSets();
                        const list = $('#savedStatesList');

                        list.empty();

                        if (filterSets.length === 0) {
                            list.append(`
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <i class="mdi mdi-information-outline me-2"></i>
                                        No saved filter sets found.
                                    </td>
                                </tr>
                            `);
                        } else {
                            filterSets.forEach((set, index) => {
                                const date = new Date(set.timestamp);
                                const filtersText = Object.entries(set.filters)
                                    .filter(([key]) => key.includes('_name') || key.includes('date'))
                                    .map(([key, value]) => {
                                        if (key === 'shop_name') return `Shop: ${value}`;
                                        if (key === 'category_name') return `Category: ${value}`;
                                        if (key === 'date_from') return `From: ${value}`;
                                        if (key === 'date_to') return `To: ${value}`;
                                        return `${key}: ${value}`;
                                    })
                                    .join(', ');

                                list.append(`
                                    <tr>
                                        <td>${set.name}</td>
                                        <td>${set.filters.tab || 'Current'}</td>
                                        <td><small class="text-muted">${filtersText || 'No filters'}</small></td>
                                        <td><small>${date.toLocaleDateString()}</small></td>
                                        <td>
                                            <button class="btn btn-sm btn-success apply-filter-set" data-index="${index}">
                                                <i class="mdi mdi-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-filter-set" data-name="${set.name}">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `);
                            });
                        }

                        list.prepend(`
                            <tr class="table-primary">
                                <td><strong>${this.currentState.name}</strong></td>
                                <td>${this.currentState.tab}</td>
                                <td><small class="text-muted">Current state</small></td>
                                <td><small>${new Date(this.currentState.timestamp).toLocaleDateString()}</small></td>
                                <td>
                                    <span class="badge bg-info">Current</span>
                                </td>
                            </tr>
                        `);

                        $('#stateManagementModal').modal('show');
                    }
                };

                $('.select2').select2({
                    placeholder: "Select option",
                    allowClear: true
                });

                var dataTables = {};
                var currentTab = 'all';

                StateManager.init();

                const tables = ['all', 'approved', 'pending', 'rejected', 'deactive', 'update_requests'];

                // Initialize all tables
                tables.forEach(tableId => {
                    initDataTable(tableId);
                });

                loadCounts();

                $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    currentTab = $(e.target).attr('href').replace('#', '');
                    if (dataTables[currentTab]) {
                        dataTables[currentTab].draw();
                    }
                });

                $('#shop_filter, #category_filter, #date_from, #date_to').on('change', function() {
                    tables.forEach(tableId => {
                        if (dataTables[tableId]) {
                            dataTables[tableId].draw();
                        }
                    });
                    loadCounts();
                });

                $('#reset_filters').on('click', function() {
                    StateManager.clearAllFilters();
                });

                function initDataTable(tableId) {
                    const statusMap = {
                        'all': null,
                        'approved': 1,
                        'pending': -1,
                        'rejected': 2,
                        'deactive': 0
                    };

                    // Special case for update_requests table
                    if (tableId === 'update_requests') {
                        dataTables[tableId] = $('#' + tableId + '-table').DataTable({
                            processing: true,
                            serverSide: true,
                            searching: true,
                            ajax: {
                                url: "{{ url('/admin/products/pending-requests') }}",
                                data: function(d) {
                                    d.shop_id = $('#shop_filter').val();
                                    d.category_id = $('#category_filter').val();
                                    d.date_from = $('#date_from').val();
                                    d.date_to = $('#date_to').val();
                                    d.search = d.search.value;
                                }
                            },
                            columns: [
                                { data: 'date', name: 'created_at' },
                                { data: 'product_info', name: 'product.name', orderable: false },
                                { data: 'shop', name: 'product.shop.name', orderable: false },
                                { data: 'request_type', name: 'type' },
                                { data: 'requester', name: 'requester.name', orderable: false },
                                { data: 'changes_summary', name: 'changes_summary', orderable: false, searchable: false },
                                { data: 'action', name: 'action', orderable: false, searchable: false }
                            ],
                            language: {
                                search: "_INPUT_",
                                searchPlaceholder: "Search products..."
                            },
                            order: [[0, 'desc']],
                            drawCallback: function(settings) {
                                const count = settings.json?.recordsTotal || 0;
                                $('#' + tableId + '-count').text(count);
                            }
                        });
                        return;
                    }

                    // For regular product tables
                    dataTables[tableId] = $('#' + tableId + '-table').DataTable({
                        processing: true,
                        serverSide: true,
                        searching: true,
                        ajax: {
                            url: "{{ url('/admin/products') }}",
                            data: function(d) {
                                d.status = statusMap[tableId];
                                d.shop_id = $('#shop_filter').val();
                                d.category_id = $('#category_filter').val();
                                d.date_from = $('#date_from').val();
                                d.date_to = $('#date_to').val();
                                d.tab = tableId;
                                d.search = d.search.value;
                            }
                        },
                        language: {
                            search: "_INPUT_",
                            searchPlaceholder: "Search products..."
                        },
                        order: [[0, 'desc']],
                        columns: [
                            { data: 'date', name: 'created_at' },
                            { data: 'product_no', name: 'product_no', className: 'text-nowrap' },
                            { data: 'name', name: 'name' },
                            { data: 'shop', name: 'shop' },
                            { data: 'category', name: 'category' },
                            { data: 'image', name: 'image', orderable: false, searchable: false },
                            { data: 'stock', name: 'stock', className: 'text-center' },
                            { data: 'amount', name: 'amount', className: 'text-nowrap' },
                            { data: 'status', name: 'status', className: 'text-center' },
                            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'table-actions' }
                        ],
                        drawCallback: function(settings) {
                            const count = settings.json?.recordsTotal || 0;
                            $('#' + tableId + '-count').text(count);
                        }
                    });
                }

                function loadCounts() {
                    $.ajax({
                        url: "{{ url('admin/products/counts') }}",
                        type: "GET",
                        data: {
                            shop_id: $('#shop_filter').val(),
                            category_id: $('#category_filter').val(),
                            date_from: $('#date_from').val(),
                            date_to: $('#date_to').val()
                        },
                        success: function(response) {
                            $('#total-products-count').text(response.total);
                            $('#approved-total-count').text(response.approved);
                            $('#pending-total-count').text(response.pending);
                            $('#rejected-total-count').text(response.rejected);
                            if (response.deactive !== undefined) {
                                $('#deactive-total-count').text(response.deactive);
                            }
                            if (response.update_requests !== undefined) {
                                $('#update_requests-count').text(response.update_requests);
                            }
                        }
                    });
                }

                // View request details
                $(document).on('click', '.view-request-btn', function() {
    var requestId = $(this).data('id');

    $.ajax({
        url: "{{ url('admin/products/update-requests') }}/" + requestId,
        type: "GET",
        success: function(response) {
            if (response.success) {
                showRequestDetails(response.data, response.change_summary, response.image_previews, requestId);
            } else {
                Swal.fire('Error', 'Failed to load request details', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load request details', 'error');
        }
    });
});

// Show request details in modal with image previews
function showRequestDetails(request, changeSummary, imagePreviews, requestId) {
    var html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Request Information</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Request ID:</th>
                        <td>#${request.id}</td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td><span class="badge bg-${request.type === 'description' ? 'info' : 'warning'}">${request.type.charAt(0).toUpperCase() + request.type.slice(1)} Update</span></td>
                    </tr>
                    <tr>
                        <th>Submitted:</th>
                        <td>${new Date(request.created_at).toLocaleString()}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><span class="badge bg-warning">Pending Review</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Product & Requester</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Product:</th>
                        <td>${request.product.name} (${request.product.sku || 'N/A'})</td>
                    </tr>
                    <tr>
                        <th>Shop:</th>
                        <td>${request.product.shop ? request.product.shop.name : 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Requester:</th>
                        <td>${request.requester.name} <br><small>${request.requester.email}</small></td>
                    </tr>
                </table>
            </div>
        </div>

        <hr>

        <h6>Changes Summary</h6>
        <div class="changes-diff">`;

    // Show changes
    var summary = changeSummary || {};
    if (Object.keys(summary).length === 0) {
        html += '<p class="text-muted">No changes detected</p>';
    } else {
        html += '<div class="row">';
        Object.keys(summary).forEach(function(field) {
            var change = summary[field];
            var fieldHtml = '';

            if (change.type === 'image' && imagePreviews && imagePreviews.main_image) {
                // Show image preview for main image
                fieldHtml = `
                    <div class="col-md-12 mb-3">
                        <div class="change-item">
                            <div class="field-name mb-2">${change.field_name}:</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="old-value p-2">
                                        <small class="text-muted d-block mb-1">Old:</small>
                                        ${change.old === '(no image)' ?
                                            '<div class="text-muted">No image</div>' :
                                            `<div class="mb-1">${change.old}</div>
                                             <img src="${imagePreviews.main_image.old || '{{ url("img/avatar.jpg") }}'}"
                                                  class="img-thumbnail"
                                                  style="max-width: 150px; max-height: 150px;"
                                                  onerror="this.src='{{ url("img/avatar.jpg") }}'">`}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="new-value p-2">
                                        <small class="text-muted d-block mb-1">New:</small>
                                        <div class="mb-1">${change.new}</div>
                                        <img src="${imagePreviews.main_image.new || '{{ url("img/avatar.jpg") }}'}"
                                             class="img-thumbnail"
                                             style="max-width: 150px; max-height: 150px;"
                                             onerror="this.src='{{ url("img/avatar.jpg") }}'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
            } else if (change.type === 'image_gallery' && imagePreviews && imagePreviews.additional_images) {
                // Show image gallery for additional images
                var oldImagesHtml = '<div class="text-muted">No images</div>';
                var newImagesHtml = '<div class="text-muted">No images</div>';

                if (imagePreviews.additional_images.old && imagePreviews.additional_images.old.length > 0) {
                    oldImagesHtml = '<div class="d-flex flex-wrap gap-2">';
                    imagePreviews.additional_images.old.forEach(function(img) {
                        oldImagesHtml += `
                            <div class="text-center">
                                <img src="${img.url || '{{ url("img/avatar.jpg") }}'}"
                                     class="img-thumbnail"
                                     style="width: 80px; height: 80px; object-fit: cover;"
                                     title="${img.name}"
                                     onerror="this.src='{{ url("img/avatar.jpg") }}'">
                                <div class="text-truncate" style="max-width: 80px; font-size: 10px;">${img.name}</div>
                            </div>`;
                    });
                    oldImagesHtml += '</div>';
                }

                if (imagePreviews.additional_images.new && imagePreviews.additional_images.new.length > 0) {
                    newImagesHtml = '<div class="d-flex flex-wrap gap-2">';
                    imagePreviews.additional_images.new.forEach(function(img) {
                        newImagesHtml += `
                            <div class="text-center">
                                <img src="${img.url || '{{ url("img/avatar.jpg") }}'}"
                                     class="img-thumbnail"
                                     style="width: 80px; height: 80px; object-fit: cover;"
                                     title="${img.name}"
                                     onerror="this.src='{{ url("img/avatar.jpg") }}'">
                                <div class="text-truncate" style="max-width: 80px; font-size: 10px;">${img.name}</div>
                            </div>`;
                    });
                    newImagesHtml += '</div>';
                }

                fieldHtml = `
                    <div class="col-md-12 mb-3">
                        <div class="change-item">
                            <div class="field-name mb-2">${change.field_name}:</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="old-value p-2">
                                        <small class="text-muted d-block mb-1">Old (${change.old}):</small>
                                        ${oldImagesHtml}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="new-value p-2">
                                        <small class="text-muted d-block mb-1">New (${change.new}):</small>
                                        ${newImagesHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
            } else {
                // Show regular text/number field
                var valueClass = '';
                if (change.type === 'number') {
                    valueClass = 'font-monospace';
                } else if (change.type === 'textarea') {
                    valueClass = 'preformatted-text';
                }

                fieldHtml = `
                    <div class="col-md-6 mb-3">
                        <div class="change-item">
                            <div class="field-name mb-1">${change.field_name}:</div>
                            <div class="old-value mb-1">
                                <small>Old: <span class="${valueClass}">${change.old}</span></small>
                            </div>
                            <div class="new-value">
                                <small>New: <span class="${valueClass}">${change.new}</span></small>
                            </div>
                        </div>
                    </div>`;
            }

            html += fieldHtml;
        });
        html += '</div>';
    }

    html += `</div>`;

    $('#requestDetailsContent').html(html);

    // Set current request ID for approve/reject actions
    $('#requestDetailsModal').data('request-id', requestId);
    $('#requestDetailsModal').modal('show');
}

                // Approve request from modal
                $('#approveFromModal').on('click', function() {
                    var requestId = $('#requestDetailsModal').data('request-id');
                    if (!requestId) return;

                    Swal.fire({
                        title: 'Approve Update Request?',
                        text: 'Are you sure you want to approve these changes?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, approve it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            approveRequest(requestId);
                        }
                    });
                });

                // Approve request directly
                $(document).on('click', '.approve-request-btn', function(e) {
                    e.stopPropagation();
                    var requestId = $(this).data('id');

                    Swal.fire({
                        title: 'Approve Update Request?',
                        text: 'Are you sure you want to approve these changes?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, approve it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            approveRequest(requestId);
                        }
                    });
                });

                // Reject request from modal
                $('#rejectFromModal').on('click', function() {
                    var requestId = $('#requestDetailsModal').data('request-id');
                    if (!requestId) return;

                    $('#reject_request_id').val(requestId);
                    $('#reject_request_reason').val('');
                    $('#requestDetailsModal').modal('hide');
                    $('#rejectRequestModal').modal('show');
                });

                // Reject request directly
                $(document).on('click', '.reject-request-btn', function(e) {
                    e.stopPropagation();
                    var requestId = $(this).data('id');

                    $('#reject_request_id').val(requestId);
                    $('#reject_request_reason').val('');
                    $('#rejectRequestModal').modal('show');
                });

                // Confirm reject request
                $('#confirmRejectRequest').on('click', function() {
                    var requestId = $('#reject_request_id').val();
                    var reason = $('#reject_request_reason').val().trim();

                    if (!reason) {
                        Swal.fire('Warning', 'Please provide a rejection reason', 'warning');
                        return;
                    }

                    if (reason.length < 10) {
                        Swal.fire('Warning', 'Rejection reason must be at least 10 characters', 'warning');
                        return;
                    }

                    rejectRequest(requestId, reason);
                });

                // Approve request function
                function approveRequest(requestId) {
                    $.ajax({
                        url: "{{ url('admin/products/update-requests') }}/" + requestId + "/approve",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Approved!', response.message, 'success');
                                dataTables['update_requests'].draw();
                                loadCounts();
                                $('#requestDetailsModal').modal('hide');
                                $('#rejectRequestModal').modal('hide');
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to approve request', 'error');
                        }
                    });
                }

                // Reject request function
                function rejectRequest(requestId, reason) {
                    $.ajax({
                        url: "{{ url('admin/products/update-requests') }}/" + requestId + "/reject",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            rejection_reason: reason
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Rejected!', response.message, 'success');
                                dataTables['update_requests'].draw();
                                loadCounts();
                                $('#rejectRequestModal').modal('hide');
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to reject request', 'error');
                        }
                    });
                }

                $(document).on('change', '.status-toggle', function() {
                    var $this = $(this);
                    var id = $this.data('id');
                    var status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/products/status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        },
                        success: function(response) {
                            if (response.success) {
                                tables.forEach(tableId => {
                                    if (dataTables[tableId]) {
                                        dataTables[tableId].draw();
                                    }
                                });
                                loadCounts();

                                Swal.fire({
                                    title: "Success!",
                                    text: response.message,
                                    icon: "success"
                                });
                            } else {
                                $this.prop('checked', !$this.prop('checked'));
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message,
                                    icon: "error"
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            $this.prop('checked', !$this.prop('checked'));
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to update status",
                                icon: "error"
                            });
                        }
                    });
                });

                $(document).on('click', '.approve-btn', function() {
                    var productId = $(this).data('id');

                    Swal.fire({
                        title: "Approve Product?",
                        text: "Are you sure you want to approve this product?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, approve it!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                url: "{{ url('/admin/products/approve') }}",
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    id: productId
                                },
                                success: function(response) {
                                    if (response.success) {
                                        tables.forEach(tableId => {
                                            if (dataTables[tableId]) {
                                                dataTables[tableId].draw();
                                            }
                                        });
                                        loadCounts();

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
                                        text: "Failed to approve product",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });

                $(document).on('click', '.reject-btn', function() {
                    var productId = $(this).data('id');
                    $('#reject_product_id').val(productId);
                    $('#reject_reason').val('');
                    $('#rejectModal').modal('show');
                });

                $('#confirmReject').on('click', function() {
                    var productId = $('#reject_product_id').val();
                    var reason = $('#reject_reason').val().trim();

                    if (!reason) {
                        Swal.fire({
                            title: "Warning!",
                            text: "Please provide a rejection reason",
                            icon: "warning"
                        });
                        return;
                    }

                    if (reason.length < 10) {
                        Swal.fire({
                            title: "Warning!",
                            text: "Rejection reason must be at least 10 characters long",
                            icon: "warning"
                        });
                        return;
                    }

                    $.ajax({
                        url: "{{ url('/admin/products/reject') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: productId,
                            reason: reason
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#rejectModal').modal('hide');

                                tables.forEach(tableId => {
                                    if (dataTables[tableId]) {
                                        dataTables[tableId].draw();
                                    }
                                });
                                loadCounts();

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
                                text: "Failed to reject product",
                                icon: "error"
                            });
                        }
                    });
                });

                $(document).on('click', '.delete-btn', function(event) {
                    event.preventDefault();
                    var productId = $(this).data('id');

                    Swal.fire({
                        title: "Delete Product?",
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
                                url: "{{ url('/admin/products/') }}/" + productId,
                                data: {
                                    "_token": "{{ csrf_token() }}"
                                },
                                success: function(data) {
                                    if (data.success) {
                                        tables.forEach(tableId => {
                                            if (dataTables[tableId]) {
                                                dataTables[tableId].draw();
                                            }
                                        });
                                        loadCounts();

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
                                        text: "Something went wrong",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });
            });

            $(function() {
                @if (Session::has('success'))
                    Swal.fire({
                        title: "Success!",
                        text: "{{ Session::get('success') }}",
                        icon: "success"
                    });
                @elseif (Session::has('error'))
                    Swal.fire({
                        title: "Error!",
                        text: "{{ Session::get('error') }}",
                        icon: "error"
                    });
                @endif
            });
        </script>

        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="{{ url('assets/js/pages/sweet-alerts.init.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    @endpush
@endsection
