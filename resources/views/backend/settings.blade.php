@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Tab Container Styles */
            .settings-container {
                display: flex;
                gap: 20px;
                margin-top: 20px;
            }

            .settings-sidebar {
                flex: 0 0 250px;
                background: #fff;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .settings-content {
                flex: 1;
                min-width: 0;
            }

            /* Tab Navigation Styles */
            .settings-tabs {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .settings-tabs li {
                border-bottom: 1px solid #f0f0f0;
            }

            .settings-tabs li:last-child {
                border-bottom: none;
            }

            .tab-link {
                display: block;
                padding: 12px 20px;
                color: #495057;
                text-decoration: none;
                background: transparent;
                border: none;
                width: 100%;
                text-align: left;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .tab-link:hover {
                background-color: #f8f9fa;
                color: #0d6efd;
            }

            .tab-link.active {
                background-color: #f0f7ff;
                color: #0d6efd;
                border-left: 3px solid #0d6efd;
                font-weight: 500;
            }

            .tab-link i {
                margin-right: 10px;
                width: 20px;
                text-align: center;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            .settings-header {
                padding: 15px 20px;
                background: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
                font-weight: 600;
                color: #495057;
            }

            @media (max-width: 768px) {
                .settings-container {
                    flex-direction: column;
                }

                .settings-sidebar {
                    flex: none;
                    width: 100%;
                }
            }
        </style>
    @endpush

    <div class="row justify-content-between">
        <div class="col-md-6 mt-1">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page login-label">Settings</h4>
            </div>
        </div>
    </div>

    <div class="settings-container">
        <!-- Left Sidebar Tabs -->
        <div class="settings-sidebar">
            <div class="settings-header">Settings Menu</div>
            <ul class="settings-tabs">
                <li>
                    <button class="tab-link active" data-tab="banners">
                        <i class="mdi mdi-image-multiple"></i> Banners
                    </button>
                </li>
                <li>
                    <button class="tab-link" data-tab="terms">
                        <i class="mdi mdi-file-document"></i> Terms & Conditions
                    </button>
                </li>

                <li>
                    <button class="tab-link" data-tab="contact">
                        <i class="mdi mdi-contacts"></i> Contact Details
                    </button>
                </li>
                <li>
                    <button class="tab-link" data-tab="homepage">
                        <i class="mdi mdi-currency-usd"></i> Home Page Contents
                    </button>
                </li>
            </ul>
        </div>

        <!-- Right Content Area -->
        <div class="settings-content">
            <!-- Banners Tab -->
            <div class="tab-content active" id="banners-tab">
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="card card-shadow">
                            <div class="card-header theme-text-color">
                                <div class="row justify-content-between">
                                    <div class="col-md-6 mt-1 theme-text-color">
                                        All Banner
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center justify-content-end">
                                        <a href="javascript:void(0)" type="button"
                                            class="btn add-btn float-end waves-effect waves-light button-color text-white add-banner">
                                            <i class="mdi mdi-plus-circle me-1"></i> Create Banner
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-border">
                                    <table class="table table-striped data-table">
                                        <thead class="button-color text-white">
                                            <tr>
                                                <th>Date</th>
                                                <th>Title</th>
                                                <th>Image</th>
                                                <th>Status</th>
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

            <div class="tab-content" id="return-replace-days-tab">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-shadow">
                            <div class="card-header theme-text-color">
                                Return & Replace Days Settings
                            </div>
                            <div class="card-body">
                                <form id="return-replace-days-form" action="{{ url('/admin/return-replace-days-settings') }}" method="post">
                                    @csrf
                                    <div class="row mt-3">
                                        <div class="col-12 col-md-6 mb-3">
                                            <label class="form-label text-dark">Maximum Return Days</label>
                                            <div class="">
                                                <input type="number" name="max_return_days"
                                                    class="form-control form-field-style input-form @error('max_return_days') is-invalid @enderror"
                                                    placeholder="Enter maximum days for returns"
                                                    value="{{ old('max_return_days', $settings['max_return_days'] ?? '') }}"
                                                    min="1" max="365">
                                                <small class="text-muted">Number of days customers can return products after delivery</small>
                                                @error('max_return_days')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 mb-3">
                                            <label class="form-label text-dark">Maximum Replace Days</label>
                                            <div class="">
                                                <input type="number" name="max_replace_days"
                                                    class="form-control form-field-style input-form @error('max_replace_days') is-invalid @enderror"
                                                    placeholder="Enter maximum days for replacements"
                                                    value="{{ old('max_replace_days', $settings['max_replace_days'] ?? '') }}"
                                                    min="1" max="365">
                                                <small class="text-muted">Number of days customers can request replacements after delivery</small>
                                                @error('max_replace_days')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="submit_btn text-end mt-3">
                                        <button type="submit"
                                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="user-account-reasons-tab">
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="card card-shadow">
                            <div class="card-header theme-text-color">
                                <div class="row justify-content-between">
                                    <div class="col-md-6 mt-1 theme-text-color">
                                        All User Account Reasons
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center justify-content-end">
                                        <a href="javascript:void(0)" type="button"
                                            class="btn add-btn float-end waves-effect waves-light button-color text-white add-user-account-reason">
                                            <i class="mdi mdi-plus-circle me-1"></i> Add User Account Reason
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-border">
                                    <table class="table table-striped user-account-reasons-data-table">
                                        <thead class="button-color text-white">
                                            <tr>
                                                <th>Date</th>
                                                <th>Reason</th>
                                                <th>Show</th>
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

            <!-- Return Order Reasons Tab -->
            <div class="tab-content" id="return-order-reasons-tab">
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="card card-shadow">
                            <div class="card-header theme-text-color">
                                <div class="row justify-content-between">
                                    <div class="col-md-6 mt-1 theme-text-color">
                                        All Return Order Reasons
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center justify-content-end">
                                        <a href="javascript:void(0)" type="button"
                                            class="btn add-btn float-end waves-effect waves-light button-color text-white add-return-order-reason">
                                            <i class="mdi mdi-plus-circle me-1"></i> Add Return Order Reason
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-border">
                                    <table class="table table-striped return-order-reasons-data-table">
                                        <thead class="button-color text-white">
                                            <tr>
                                                <th>Date</th>
                                                <th>Reason</th>
                                                <th>Show</th>
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

            <!-- Replace Order Reasons Tab -->
            <div class="tab-content" id="replace-order-reasons-tab">
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="card card-shadow">
                            <div class="card-header theme-text-color">
                                <div class="row justify-content-between">
                                    <div class="col-md-6 mt-1 theme-text-color">
                                        All Replace Order Reasons
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center justify-content-end">
                                        <a href="javascript:void(0)" type="button"
                                            class="btn add-btn float-end waves-effect waves-light button-color text-white add-replace-order-reason">
                                            <i class="mdi mdi-plus-circle me-1"></i> Add Replace Order Reason
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-border">
                                    <table class="table table-striped replace-order-reasons-data-table">
                                        <thead class="button-color text-white">
                                            <tr>
                                                <th>Date</th>
                                                <th>Reason</th>
                                                <th>Show</th>
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

            <!-- Terms and Conditions Tab -->
            <div class="tab-content" id="terms-tab">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-shadow">
                            <div class="card-header theme-text-color">
                                Terms and Conditions
                            </div>
                            <div class="card-body">
                                <form id="terms-form" action="{{ url('/admin/terms-conditions') }}" method="post">
                                    @csrf
                                    <div class="mt-3">
                                        <textarea id="terms" class="form-control ckeditor @error('terms_and_condition') is-invalid @enderror"
                                            rows="7" name="terms_and_condition">{{ isset($settings['terms_and_condition']) ? old('terms_and_condition', $settings['terms_and_condition']) : '' }}</textarea>
                                        @error('terms_and_condition')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="submit_btn text-end mt-3">
                                        <button type="submit"
                                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="homepage-tab">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-shadow">
                            <div class="card-header theme-text-color">
                                Return & Refund Policy
                            </div>
                            <div class="card-body">
                                <form id="refund-form" action="{{ url('/admin/refund-policy') }}" method="post">
                                    @csrf
                                    <div class="mt-3">
                                        <textarea id="refund_policy" class="form-control ckeditor @error('refund_policy') is-invalid @enderror"
                                            rows="7" name="refund_policy">{{ isset($settings['refund_policy']) ? old('refund_policy', $settings['refund_policy']) : '' }}</textarea>
                                        @error('refund_policy')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="submit_btn text-end mt-3">
                                        <button type="submit"
                                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modals (keep all your existing modals as they are) -->
    <div class="modal" id="add-banner">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Add Banner</h5>
                    <button type="button" class="btn-close custom-modal-close modal-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <form action="{{ url('/admin/banner/') }}" method="post" class="form-horizontal"
                        enctype="multipart/form-data" id="add-banner-form">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Name</label>
                                <div class="">
                                    <input type="text" name="name"
                                        class="form-control form-field-style input-form @error('name') is-invalid @enderror"
                                        placeholder="Enter Name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Image</label>
                                <div class="">
                                    <input type="file" name="image"
                                        class="form-control form-field-style input-form @error('image') is-invalid @enderror"
                                        placeholder="Enter image" value="{{ old('image') }}" required>
                                    @error('image')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Status</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="active" name="status" class="form-check-input"
                                                value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="inactive" name="status" class="form-check-input"
                                                value="0" {{ old('status') == '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="submit_btn text-end">
                            <button type="submit"
                                class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Banner Modal -->
    <div class="modal" id="edit-banner">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white">
                    <h5 class="modal-title">Edit Banner</h5>
                    <button type="button" class="btn-close custom-modal-close modal-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <form method="post" class="form-horizontal edit-banner-form" enctype="multipart/form-data"
                        id="edit-banner-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Name</label>
                                <input type="text" name="name"
                                    class="form-control banner-name @error('name') is-invalid @enderror"
                                    placeholder="Enter Name" required>
                                @error('name')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>


                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Image</label>
                                <input type="file" name="image"
                                    class="form-control banner-image @error('image') is-invalid @enderror">
                                <a href="javascript:void(0)" class="image-view-link mt-2" style="display: none;"></a>
                                @error('image')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Status</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="banner-active" name="status"
                                                class="form-check-input" value="1">
                                            <label class="form-check-label" for="banner-active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="banner-inactive" name="status"
                                                class="form-check-input" value="0">
                                            <label class="form-check-label" for="banner-inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="banner_id" class="banner-id" value="">
                        <div class="submit_btn text-end">
                            <button type="submit" class="btn add-btn text-white">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="add-user-account-reason">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Add User Account Reason</h5>
                    <button type="button" class="btn-close custom-modal-close modal-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <form action="{{ url('/admin/user-account-reason/') }}" method="post" class="form-horizontal"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-4">
                                <label class="form-label text-dark">Reason</label>
                                <div class="">
                                    <input type="text" name="reason"
                                        class="form-control form-field-style input-form @error('reason') is-invalid @enderror"
                                        placeholder="Enter Reason" value="{{ old('reason') }}" required>
                                    @error('reason')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <label class="form-label text-dark">Show</label>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" id="user-account-active" name="is_show"
                                            class="form-check-input" value="1"
                                            {{ old('is_show', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user-account-active">Yes</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" id="user-account-inactive" name="is_show"
                                            class="form-check-input" value="0"
                                            {{ old('is_show') == '0' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user-account-inactive">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="submit_btn text-end">
                            <button type="submit"
                                class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    @push('footer_content')
        <script src="//cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"
            integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            // Initialize CKEditor
            CKEDITOR.replace('terms', {
                versionCheck: false,
            });

            // Local Storage Key for active tab
            const ACTIVE_TAB_KEY = 'settings_active_tab';

            // Default tab (will be used if no stored value exists)
            const DEFAULT_TAB = 'banners';

            $(document).ready(function() {

                // Tab Management Functions
                function getStoredTab() {
                    try {
                        return localStorage.getItem(ACTIVE_TAB_KEY) || DEFAULT_TAB;
                    } catch (e) {
                        console.error('Error reading from localStorage:', e);
                        return DEFAULT_TAB;
                    }
                }

                function setStoredTab(tab) {
                    try {
                        localStorage.setItem(ACTIVE_TAB_KEY, tab);
                    } catch (e) {
                        console.error('Error saving to localStorage:', e);
                    }
                }

                function activateTab(tabId) {
                    // Remove active class from all tabs and panes
                    $('.tab-link').removeClass('active');
                    $('.tab-content').removeClass('active');

                    // Add active class to selected tab and pane
                    $(`.tab-link[data-tab="${tabId}"]`).addClass('active');
                    $(`#${tabId}-tab`).addClass('active');

                    // Store active tab
                    setStoredTab(tabId);

                    // Initialize DataTables for the active tab if needed
                    initializeTabContent(tabId);
                }

                function initializeTabContent(tabId) {
                    switch (tabId) {
                        case 'banners':
                            if (!$.fn.DataTable.isDataTable('.data-table')) {
                                initializeBannerTable();
                            }
                            break;

                    }
                }

                $(document).on('click', '.tab-link', function(e) {
                    e.preventDefault();
                    const tabId = $(this).data('tab');
                    activateTab(tabId);
                });

                // Initialize DataTables
                function initializeBannerTable() {
                    $('.data-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ url('/admin/banner') }}",
                        order: [
                            [0, 'desc']
                        ],
                        columnDefs: [{
                            'visible': false,
                            'targets': [0]
                        }],
                        columns: [{
                                data: 'date',
                                name: 'date'
                            },
                            {
                                data: 'title',
                                name: 'title'
                            },
                            {
                                data: 'image',
                                name: 'image',
                                orderable: false
                            },
                            {
                                data: 'status',
                                name: 'status',
                                orderable: false
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });
                }



                const initialTab = getStoredTab();
                activateTab(initialTab);


                // Modal handlers (keep your existing modal code)
                $(document).on('click', '.add-banner', function(event) {
                    // Reset form
                    $('#add-banner-form')[0].reset();
                    $('#add-banner').modal('show');
                });

                // Edit banner handler
                $(document).on('click', '.edit-banner', function() {
                    var id = $(this).data('id');

                    $.ajax({
                        type: 'GET',
                        url: "{{ url('admin/banner') }}/" + id + "/edit",
                        success: function(data) {
                            // Fill form fields
                            $('.banner-id').val(data.id);
                            $('.banner-name').val(data.name);
                            // Set status
                            if (data.status == 1) {
                                $('#banner-active').prop('checked', true);
                            } else {
                                $('#banner-inactive').prop('checked', true);
                            }

                            // Set image preview
                            if (data.image) {
                                let imageUrl = "{{ url('storage/banner') }}/" + data.image;
                                $('.image-view-link')
                                    .attr('href', imageUrl)
                                    .text(data.image)
                                    .show();
                            } else {
                                $('.image-view-link').hide();
                            }

                            // Set form action
                            let updateUrl = "{{ url('admin/banner') }}/" + data.encrypted_id;
                            $('.edit-banner-form').attr('action', updateUrl);

                            $('#edit-banner').modal('show');
                        },
                        error: function(xhr) {
                            console.error("Error fetching banner data:", xhr.responseText);
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to load banner data",
                                icon: "error"
                            });
                        }
                    });
                });

                $('#add-banner-form').validate({
                    rules: {
                        name: {
                            required: true,
                            minlength: 3
                        },
                        image: {
                            required: true,
                            accept: "image/*"
                        }
                    },
                    messages: {
                        name: {
                            required: "Please enter banner name",
                            minlength: "Banner name must be at least 3 characters"
                        },
                        image: {
                            required: "Please upload an image",
                            accept: "Please upload a valid image file"
                        }
                    },
                    submitHandler: function(form) {
                        form.submit();
                    }
                });

                // Delete handler for banners
                $(document).on('click', '.delete', function(event) {
                    event.preventDefault();
                    var id = $(this).data('id');

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#f34e4e",
                        confirmButtonText: "Yes, delete it!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/banner/') }}/" + id,
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                    "id": id
                                },
                                success: function(data) {
                                    if (data.status == 1) {
                                        bannerTable.ajax.reload();
                                        Swal.fire({
                                            title: "Deleted!",
                                            text: "Banner deleted successfully.",
                                            icon: "success"
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: data.message ||
                                                "Failed to delete banner",
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Failed to delete banner",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });

                // Banner status toggle
                $(document).on('change', '.banner-status-toggle', function() {
                    var $this = $(this);
                    var id = $this.data('id');
                    var status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/banner/status') }}",
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
                                    text: response.message,
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



                // Initialize form validation
                initializeFormValidation();

                // Success message handler
                @if (Session::has('success'))
                    Swal.fire({
                        title: "Good job!",
                        text: "{{ Session::get('success') }}",
                        icon: "success"
                    });
                @endif
            });

            // Form validation function
            function initializeFormValidation() {
                $.validator.addMethod("phoneNumber", function(value, element) {
                    return this.optional(element) || /^[0-9+\s()-]{10,15}$/.test(value);
                }, "Please enter a valid phone number");

                $.validator.addMethod("emailValidation", function(value, element) {
                    return this.optional(element) || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
                }, "Please enter a valid email address");

                // Terms form validation
                $("#terms-form").validate({
                    errorClass: "is-invalid",
                    errorElement: "span",
                    highlight: function(element) {
                        $(element).addClass("is-invalid").removeClass("is-valid");
                    },
                    unhighlight: function(element) {
                        $(element).addClass("is-valid").removeClass("is-invalid");
                    },
                    rules: {
                        terms_and_condition: {
                            required: true,
                            minlength: 50
                        }
                    },
                    messages: {
                        terms_and_condition: {
                            required: "Please enter terms and conditions",
                            minlength: "Terms and conditions must be at least 50 characters long"
                        }
                    },
                    submitHandler: function(form) {
                        var termsContent = CKEDITOR.instances.terms.getData().trim();
                        if (termsContent.length < 50) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: 'Terms and conditions must be at least 50 characters long'
                            });
                            return false;
                        }
                        form.submit();
                    }
                });

                // Delivery charge form validation
                $("#delivery-charge-form").validate({
                    errorClass: "is-invalid",
                    errorElement: "span",
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        error.insertAfter(element);
                    },
                    highlight: function(element) {
                        $(element).addClass("is-invalid").removeClass("is-valid");
                    },
                    unhighlight: function(element) {
                        $(element).removeClass("is-invalid").addClass("is-valid");
                    },
                    rules: {
                        delivery_charge: {
                            required: true,
                            number: true,
                            min: 1
                        }
                    },
                    messages: {
                        delivery_charge: {
                            required: "Please enter delivery charge",
                            number: "Please enter a valid number",
                            min: "Delivery charge must be at least 1"
                        }
                    },
                    submitHandler: function(form) {
                        form.submit();
                    }
                });



                // Contact form validation
                $("#contact-form").validate({
                    errorClass: "is-invalid",
                    errorElement: "span",
                    highlight: function(element) {
                        $(element).addClass("is-invalid").removeClass("is-valid");
                    },
                    unhighlight: function(element) {
                        $(element).addClass("is-valid").removeClass("is-invalid");
                    },
                    rules: {
                        phone: {
                            required: true,
                            phoneNumber: true
                        },
                        whatsapp: {
                            required: true,
                            phoneNumber: true
                        },
                        email: {
                            required: true,
                            emailValidation: true
                        },
                        address: {
                            required: true
                        }
                    },
                    messages: {
                        phone: {
                            required: "Please enter a phone number",
                            phoneNumber: "Please enter 10 digit phone number"
                        },
                        whatsapp: {
                            required: "Please enter a WhatsApp number",
                            phoneNumber: "Please enter 10 digit WhatsApp number"
                        },
                        email: {
                            required: "Please enter an email address",
                            emailValidation: "Please enter a valid email address"
                        },
                        address: {
                            required: "Please enter a address"
                        }
                    },
                    submitHandler: function(form) {
                        form.submit();
                    }
                });
            }
        </script>

        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="{{ url('assets/js/pages/sweet-alerts.init.js') }}"></script>
    @endpush
@endsection
