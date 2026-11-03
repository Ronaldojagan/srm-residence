@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ url('assets/libs/multiselect/css/multi-select.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ url('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ url('assets/libs/selectize/css/selectize.bootstrap3.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ url('assets/libs/mohithg-switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.css">
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @endpush
    <div class="row align-items-center justify-content-between">
        <div class="col-md-6 mt-3">
            <h4 class="page-title-main page mb-0">Orders</h4>
        </div>

        <div class="col-md-6 mt-3 d-flex flex-wrap justify-content-md-end justify-content-start align-items-center gap-2">

            <!-- Bulk Actions Dropdown -->
            <div class="dropdown">
                <button
                    class="btn btn-theme-second-success shadow-sm rounded-pill px-3 dropdown-toggle d-flex align-items-center"
                    type="button" id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                    <i class="fas fa-tasks me-2"></i> Bulk Actions
                </button>
                <ul class="dropdown-menu shadow-sm" aria-labelledby="bulkActionsDropdown">
                    {{-- <li>
                        <a class="dropdown-item bulk-action-btn d-flex align-items-center gap-2" href="#"
                            data-action="cancel">
                            <i class="fas fa-ban text-danger"></i> Cancel Orders
                        </a>
                    </li> --}}
                    <li>
                        <a class="dropdown-item bulk-action-btn d-flex align-items-center gap-2" href="#"
                            data-action="export">
                            <i class="fas fa-file-export text-success"></i> Export Selected
                        </a>
                    </li>
                </ul>
            </div>

            <button class="btn btn-theme-success shadow-sm rounded-pill px-3" id="exportAllBtn">
                <i class="fas fa-file-export me-2"></i> Export All
            </button>
        </div>

    </div>


    <div class="row mt-2">
        <div class="col-lg-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table">
                            <thead class="theme-menu-color">
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Date</th>
                                    <th>Order ID</th>
                                    <th>Total</th>
                                    <th>User</th>
                                    <th>User Ph</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Invoice</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="cancelOrderForm">
                        @csrf
                        <input type="hidden" name="order_id" id="cancelOrderId">
                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">Reason for Cancellation</label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Refund Amount</label>
                            <input type="text" class="form-control" id="refundAmountDisplay" readonly>
                            <input type="hidden" name="refund_amount" id="refundAmount">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmCancel" required>
                                <label class="form-check-label" for="confirmCancel">
                                    I confirm that I want to cancel this order
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">Cancel Order</button>
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
        <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
        <script src="{{ url('assets/libs/selectize/js/standalone/selectize.min.js') }}"></script>
        <script src="{{ url('assets/libs/mohithg-switchery/switchery.min.js') }}"></script>
        <script src="{{ url('assets/libs/multiselect/js/jquery.multi-select.js') }}"></script>
        <script src="{{ url('assets/libs/select2/js/select2.min.js') }}"></script>
        <script src="{{ url('assets/js/pages/form-advanced.init.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.min.js"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="{{ url('assets/js/pages/sweet-alerts.init.js') }}"></script>

        <script type="text/javascript">
            $(function() {
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('/admin/orders') }}",
                        data: function(d) {
                            const urlParams = new URLSearchParams(window.location.search);
                            if (urlParams.has('start_date')) d.start_date = urlParams.get('start_date');
                            if (urlParams.has('end_date')) d.end_date = urlParams.get('end_date');
                            if (urlParams.has('status')) d.status = urlParams.get('status');
                            if (window.activeFilterRules && window.activeFilterRules.length > 0) {
                                d.filters = window.activeFilterRules;
                            }
                        }
                    },
                    columns: [{
                            data: 'checkbox',
                            name: 'checkbox',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'date',
                            name: 'date',
                        },
                        {
                            data: 'order_no',
                            name: 'order_no'
                        },
                        {
                            data: 'total_amount',
                            name: 'total_amount'
                        },
                        {
                            data: 'user_name',
                            name: 'user_name'
                        },
                        {
                            data: 'user_phone',
                            name: 'user_phone'
                        },
                        {
                            data: 'order_status',
                            name: 'order_status'
                        },
                        {
                            data: 'payment_type',
                            name: 'payment_type'
                        },
                        {
                            data: 'invoice_download',
                            name: 'invoice_download'
                        },
                        {
                            data: 'action',
                            name: 'action'
                        }
                    ],
                    initComplete: function() {
                        $('.dataTables_filter').html(`
                            <div class="input-group small-bar">
                                <input type="search" class="form-control form-control-sm" placeholder="Search..." id="datatableSearch" style="border-radius: 10px;">
                                <button class="btn btn-sm btn-outline-secondary" style="display:none" type="button" id="searchButton">
                                </button>
                                <button type="button" class="btn btn-theme-second-success filter-btn ms-2 data-table-filter-btn" style="border-radius: 10px;">
                                    <i class="fas fa-filter"></i> Filters
                                    <span class="badge bg-danger filter-badge" style="display: none;">0</span>
                                </button>
                            </div>
                        `);

                        $('#datatableSearch').on('keyup', function() {
                            table.search(this.value).draw();
                        });

                        initFilterPopup(this.api());
                    }
                });
                // $('.data-table').DataTable().order([1, 'desc']).draw();

                // Handle select all checkbox
                $('#selectAll').click(function() {
                    $('.order-checkbox').prop('checked', this.checked);
                    toggleBulkActions();
                });

                // Handle individual checkbox clicks
                $(document).on('change', '.order-checkbox', function() {
                    if ($('.order-checkbox:checked').length === $('.order-checkbox').length) {
                        $('#selectAll').prop('checked', true);
                    } else {
                        $('#selectAll').prop('checked', false);
                    }
                    toggleBulkActions();
                });

                // Toggle bulk actions dropdown based on selected orders
                function toggleBulkActions() {
                    const checkedCount = $('.order-checkbox:checked').length;
                    const $bulkActionsDropdown = $('#bulkActionsDropdown');

                    if (checkedCount > 0) {
                        $bulkActionsDropdown.prop('disabled', false);
                        $bulkActionsDropdown.text(`Bulk Actions (${checkedCount})`);
                    } else {
                        $bulkActionsDropdown.prop('disabled', true);
                        $bulkActionsDropdown.text('Bulk Actions');
                    }
                }

                // Bulk action handlers
                $(document).on('click', '.bulk-action-btn', function(e) {
                    e.preventDefault();
                    const action = $(this).data('action');
                    const selectedIds = $('.order-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (selectedIds.length === 0) {
                        Swal.fire('Error', 'Please select at least one order', 'error');
                        return;
                    }

                    if (action === 'cancel') {
                        handleBulkCancel(selectedIds);
                    } else if (action === 'export') {
                        exportOrders(selectedIds);
                    }
                });

                // Export all orders
                $('#exportAllBtn').click(function() {
                    exportOrders('all');
                });

                // Export function
                function exportOrders(orderIds) {
                    let url = "{{ url('admin/orders-export') }}";
                    if (orderIds !== 'all') {
                        url += '?order_ids=' + orderIds.join(',');
                    }

                    window.location.href = url;
                }

                // Bulk cancel handler
                function handleBulkCancel(orderIds) {
                    Swal.fire({
                        title: 'Cancel Selected Orders?',
                        text: `You are about to cancel ${orderIds.length} order(s). This action cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Cancel Orders',
                        cancelButtonText: 'Go Back'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('admin/orders/bulk-cancel') }}",
                                type: 'POST',
                                data: {
                                    order_ids: orderIds,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Success', response.message, 'success');
                                        table.draw();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'An error occurred while processing your request',
                                        'error');
                                }
                            });
                        }
                    });
                }

                // Single order cancel handler
                $(document).on('click', '.cancel-order-btn', function() {
                    const orderId = $(this).data('id');
                    const orderAmount = $(this).data('amount');
                    const paymentType = $(this).data('payment-type');

                    $('#cancelOrderId').val(orderId);
                    $('#refundAmount').val(orderAmount);
                    $('#refundAmountDisplay').val('₹' + orderAmount);

                    const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
                    modal.show();
                });

                // Confirm cancel button
                $('#confirmCancelBtn').click(function() {
                    if (!$('#confirmCancel').is(':checked')) {
                        Swal.fire('Error', 'Please confirm cancellation', 'error');
                        return;
                    }

                    const formData = $('#cancelOrderForm').serialize();

                    $.ajax({
                        url: "{{ url('admin/orders/cancel') }}",
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message, 'success');
                                $('#cancelOrderModal').modal('hide');
                                table.draw();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'An error occurred while processing your request',
                                'error');
                        }
                    });
                });

                // Rest of your existing code for filters...
                // ... [keep all your existing filter code here] ...
                function initFilterPopup(tableApi) {
                    var popupHTML = `
                        <div class="filter-popup" id="user-filter-popup" style="display: none;">
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
                                        <button type="button" class="btn btn-sm btn-outline-secondary add-rule-btn">
                                            <i class="fas fa-plus"></i> Add Filter Rule
                                        </button>
                                        <div class="filter-logic-toggle btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary active" data-logic="and">AND (All conditions must be met)</button>
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
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-2 save-filter-btn">
                                            <i class="fas fa-save"></i> Save Filter
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary apply-filter-btn">
                                            <i class="fas fa-check"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('body').append(popupHTML);
                    var $popup = $('#user-filter-popup');
                    var currentEditingFilterId = null;

                    function positionPopup() {
                        var $btn = $('.filter-btn');
                        var offset = $btn.offset();

                    }

                    $('.filter-btn').click(function(e) {
                        e.stopPropagation();
                        positionPopup();
                        $popup.toggle();

                        // Load saved filters when opening
                        if ($popup.is(':visible')) {
                            loadSavedFilters();
                        }
                    });

                    // $(document).click(function(e) {
                    //     if (!$(e.target).closest('.filter-popup').length && !$(e.target).closest('.filter-btn')
                    //         .length) {
                    //         $popup.hide();
                    //     }
                    // });

                    $popup.find('.close').click(function() {
                        $popup.hide();
                        currentEditingFilterId = null;
                    });

                    $popup.find('.add-rule-btn').click(function() {
                        addFilterRule(tableApi);
                    });

                    $popup.find('.filter-logic-toggle button').click(function() {
                        $(this).addClass('active').siblings().removeClass('active');
                        updateRuleConnectors();
                    });

                    $popup.find('.clear-filters-btn').click(function() {
                        $popup.find('.filter-rules-container').empty();
                        window.activeFilterRules = [];
                        tableApi.draw();
                        updateFilterBadge();
                        $popup.hide();
                        currentEditingFilterId = null;
                    });

                    $popup.find('.apply-filter-btn').click(function() {
                        var rules = collectFilterRules();
                        var logic = $popup.find('.filter-logic-toggle .active').data('logic');

                        window.activeFilterRules = rules.map(rule => ({
                            ...rule,
                            logic
                        }));
                        tableApi.draw();
                        updateFilterBadge();
                        $popup.hide();
                    });

                    $popup.find('.save-filter-btn').click(function() {
                        var rules = collectFilterRules();
                        var logic = $popup.find('.filter-logic-toggle .active').data('logic');
                        var isEditing = currentEditingFilterId !== null;

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
                                var url = isEditing ?
                                    '/admin/filters/' + currentEditingFilterId :
                                    '/admin/filters';
                                var method = isEditing ? 'PUT' : 'POST';

                                $.ajax({
                                    url: url,
                                    type: method,
                                    data: {
                                        name: result.value,
                                        page: 'users',
                                        table_type: 'user',
                                        logic: logic,
                                        rules: rules,
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function() {
                                        Swal.fire(
                                            isEditing ? 'Updated!' : 'Saved!',
                                            `Your filter has been ${isEditing ? 'updated' : 'saved'}.`,
                                            'success'
                                        );
                                        loadSavedFilters();
                                        currentEditingFilterId = null;
                                    }
                                });
                            }
                        });
                    });

                    $popup.find('.saved-filters-dropdown').change(function() {
                        var filterId = $(this).val();
                        if (filterId) {
                            $.get('/admin/filters/' + filterId, function(filter) {
                                $popup.find('.filter-rules-container').empty();

                                $popup.find(
                                    `.filter-logic-toggle button[data-logic="${filter.filters.logic}"]`
                                ).click();

                                filter.filters.rules.forEach(rule => {
                                    addFilterRule(tableApi);
                                    var $lastRule = $popup.find('.filter-rule').last();

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

                                            if (rule.column === 'payment_type' && rule
                                                .value) {
                                                setTimeout(() => {
                                                    $lastRule.find(
                                                        '.rule-value').val(
                                                        rule.value).trigger(
                                                        'change');
                                                }, 200);
                                            }
                                        }
                                    }, 100);
                                });

                                currentEditingFilterId = filterId;
                                $popup.find('.save-filter-btn').html(
                                    '<i class="fas fa-save"></i> Update Filter');

                                var rules = collectFilterRules();
                                window.activeFilterRules = rules.map(r => ({
                                    ...r,
                                    logic: filter.filters.logic
                                }));
                                tableApi.draw();
                                updateFilterBadge();
                            });
                        }
                    });

                    $popup.find('.edit-saved-filter-btn').click(function() {
                        var filterId = $popup.find('.saved-filters-dropdown').val();
                        if (filterId) {
                            currentEditingFilterId = filterId;
                            $popup.find('.save-filter-btn').html('<i class="fas fa-save"></i> Update Filter');

                            $.get('/admin/filters/' + filterId, function(filter) {
                                $popup.find('.filter-rules-container').empty();
                                $popup.find(
                                    `.filter-logic-toggle button[data-logic="${filter.filters.logic}"]`
                                ).click();

                                filter.filters.rules.forEach(rule => {
                                    addFilterRule(tableApi);
                                    var $lastRule = $popup.find('.filter-rule').last();

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

                    $popup.find('.delete-saved-filter-btn').click(function() {
                        var filterId = $popup.find('.saved-filters-dropdown').val();
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
                                        url: '/admin/filters/' + filterId,
                                        type: 'DELETE',
                                        data: {
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function() {
                                            Swal.fire('Deleted!',
                                                'Your filter has been deleted.',
                                                'success');
                                            loadSavedFilters();
                                            currentEditingFilterId = null;
                                        }
                                    });
                                }
                            });
                        } else {
                            Swal.fire('Error', 'No filter selected', 'error');
                        }
                    });

                    function loadSavedFilters() {
                        $.get('/admin/filters', {
                            page: 'users',
                            table_type: 'user'
                        }, function(filters) {
                            var $dropdown = $popup.find('.saved-filters-dropdown');
                            $dropdown.empty();
                            $dropdown.append('<option value="">Load Saved Filter...</option>');

                            filters.forEach(filter => {
                                $dropdown.append(
                                    `<option value="${filter.id}">${filter.name}</option>`);
                            });
                        });
                    }

                    function collectFilterRules() {
                        var rules = [];
                        $popup.find('.filter-rule').each(function() {
                            var $rule = $(this);
                            var column = $rule.find('.rule-column').val();
                            var operator = $rule.find('.rule-operator').val();

                            if (!column || !operator) return;

                            var value;
                            if (operator === 'between') {
                                value = {
                                    start: $rule.find('.rule-value-start').val(),
                                    end: $rule.find('.rule-value-end').val()
                                };
                            } else if ($rule.find('.rule-value').is('select[multiple]')) {
                                value = $rule.find('.rule-value').val() || [];
                            } else {
                                value = $rule.find('.rule-value').val();
                            }

                            rules.push({
                                column: column,
                                operator: operator,
                                value: value
                            });
                        });
                        return rules;
                    }

                    function updateFilterBadge() {
                        var count = window.activeFilterRules ? window.activeFilterRules.length : 0;
                        var $badge = $('.filter-badge');
                        if (count > 0) {
                            $badge.text(count).show();
                        } else {
                            $badge.hide();
                        }
                    }

                    function updateRuleConnectors() {
                        var logic = $popup.find('.filter-logic-toggle .active').data('logic');
                        $popup.find('.rule-connector').text(logic);
                    }

                    addFilterRule(tableApi);
                }

                function addFilterRule(tableApi) {
                    var $popup = $('#user-filter-popup');
                    var columns = tableApi.columns().header().toArray();
                    var columnOptions = columns.map(function(col, index) {
                        var colData = tableApi.column(index).dataSrc();
                        var colTitle = $(col).text().trim();
                        if (colData !== 'action' && colData !== 'invoice_download' && colData !== 'checkbox') {
                            return `<option value="${colData}">${colTitle}</option>`;
                        }
                        return '';
                    }).join('');

                    var logic = $popup.find('.filter-logic-toggle .active').data('logic') || 'and';
                    var ruleCount = $popup.find('.filter-rule').length;
                    var showConnector = ruleCount > 0;

                    var ruleHTML = `
                        <div class="filter-rule mb-2 p-2" style="border: 1px solid #eee; border-radius: 4px; background: #f9f9f9;">
                            <div class="row align-items-center">
                                ${showConnector ? `<div class="col-auto">
                                                                                                    <span class="rule-connector" style="font-weight: bold; color: #666;">${logic}</span>
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
                        </div>
                    `;

                    $popup.find('.filter-rules-container').append(ruleHTML);

                    var $columnSelect = $popup.find('.rule-column').last();
                    $columnSelect.change(function() {
                        updateOperatorOptions($(this), tableApi);
                    });

                    updateOperatorOptions($columnSelect, tableApi);

                    $popup.find('.remove-rule-btn').last().click(function() {
                        $(this).closest('.filter-rule').remove();
                        if ($popup.find('.filter-rule').length === 0) {
                            addFilterRule(tableApi);
                        }
                    });
                }

                function updateOperatorOptions($columnSelect, tableApi) {
                    var columnName = $columnSelect.val();
                    var $rule = $columnSelect.closest('.filter-rule');
                    var $operatorSelect = $rule.find('.rule-operator');
                    var $valueContainer = $rule.find('.rule-value-container');

                    $operatorSelect.empty();
                    $valueContainer.empty();

                    $operatorSelect.append('<option value="">Select Operator</option>');

                    var baseOperators = [{
                            value: 'equals',
                            text: 'equals'
                        },
                        {
                            value: 'not_equals',
                            text: 'not equals'
                        }
                    ];

                    if (columnName === 'date') {
                        baseOperators.push({
                            value: 'greater_than',
                            text: 'after'
                        }, {
                            value: 'less_than',
                            text: 'before'
                        }, {
                            value: 'between',
                            text: 'between'
                        });

                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        var $valueInput = $(
                            '<input type="text" class="form-control form-control-sm rule-value datepicker" placeholder="Select date">'
                        );
                        $valueContainer.append($valueInput);
                        flatpickr($valueInput[0], {
                            enableTime: true,
                            dateFormat: 'Y-m-d H:i:S',
                            time_24hr: true,
                            plugins: [
                                new confirmDatePlugin({
                                    confirmText: "Confirm",
                                    showAlways: false,
                                    theme: "light"
                                })
                            ]
                        });


                        $operatorSelect.change(function() {
                            if ($(this).val() === 'between') {
                                $valueContainer.empty();
                                $valueContainer.append(`
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
                                    dateFormat: 'Y-m-d H:i:S',
                                    time_24hr: true,
                                    plugins: [
                                        new confirmDatePlugin({
                                            confirmText: "Confirm",
                                            showAlways: false,
                                            theme: "light"
                                        })
                                    ]
                                });

                            }
                        });
                    } else if (columnName === 'dob') {
                        baseOperators.push({
                            value: 'greater_than',
                            text: 'after'
                        }, {
                            value: 'less_than',
                            text: 'before'
                        }, {
                            value: 'between',
                            text: 'between'
                        });

                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        var $valueInput = $(
                            '<input type="text" class="form-control form-control-sm rule-value datepicker" placeholder="Select date">'
                        );
                        $valueContainer.append($valueInput);
                        $valueInput.datepicker({
                            dateFormat: 'yy-mm-dd',
                            changeMonth: true,
                            changeYear: true
                        });

                        $operatorSelect.change(function() {
                            if ($(this).val() === 'between') {
                                $valueContainer.empty();
                                $valueContainer.append(`
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
                                $('.rule-value-start, .rule-value-end').datepicker({
                                    dateFormat: 'yy-mm-dd',
                                    changeMonth: true,
                                    changeYear: true
                                });
                            }
                        });
                    } else if (columnName === 'kyc_verified') {
                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        const $select = $(`
                    <select class="form-control form-control-sm rule-value" data-toggle="select2" style="width: 100%;">
                        <option value="">Select</option>
                        <option value="1">Verified</option>
                        <option value="0">Not Verified</option>
                    </select>
                `);
                        $valueContainer.append($select);
                        $select.select2({
                            placeholder: "Select verification status",
                            width: '100%',
                            minimumResultsForSearch: Infinity
                        });
                    } else if (columnName === 'order_status') {
                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        var $select = $(`
                    <select class="form-control form-control-sm rule-value" data-toggle="select2" data-width="100%" multiple="multiple" style="width: 100%;">
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Completed">Completed</option>
                    </select>
                `);
                        $valueContainer.append($select);
                        $select.select2({
                            placeholder: "Select statuses",
                            width: '100%',
                            closeOnSelect: false
                        });
                    } else if (columnName === 'payment_type') {
                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        var $select = $(`
                            <select class="form-control form-control-sm rule-value" data-toggle="select2" data-width="100%" style="width: 100%;">
                                <option value="">Select Payment Type</option>
                                <option value="Cash On Delivery">Cash On Delivery</option>
                                <option value="Razorpay">Razorpay</option>
                                <option value="Wallet">Wallet</option>
                            </select>
                        `);

                        $valueContainer.append($select);

                        $select.select2({
                            placeholder: "Select payment type",
                            width: '100%'
                        });
                    } else if (columnName === 'total_earning') {
                        baseOperators.push({
                            value: 'greater_than',
                            text: 'greater than'
                        }, {
                            value: 'less_than',
                            text: 'less than'
                        }, {
                            value: 'between',
                            text: 'between'
                        });

                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        $valueContainer.append(
                            '<input type="number" class="form-control form-control-sm rule-value" placeholder="Enter total earning">'
                        );

                        $operatorSelect.change(function() {
                            if ($(this).val() === 'between') {
                                $valueContainer.empty();
                                $valueContainer.append(`
                        <div class="row g-2">
                            <div class="col">
                                <input type="number" class="form-control form-control-sm rule-value-start" placeholder="Min earning">
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <span>and</span>
                            </div>
                            <div class="col">
                                <input type="number" class="form-control form-control-sm rule-value-end" placeholder="Max earning">
                            </div>
                        </div>
                    `);
                            } else {
                                $valueContainer.empty();
                                $valueContainer.append(
                                    '<input type="number" class="form-control form-control-sm rule-value" placeholder="Enter total earning">'
                                );
                            }
                        });
                    } else if (columnName === 'state' || columnName === 'name') {
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
                            '<input type="text" class="form-control form-control-sm rule-value" placeholder="Enter name">'
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
                    } else if (columnName === 'phone') {
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
                            '<input type="text" class="form-control form-control-sm rule-value" placeholder="Enter phone">'
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
                    } else if (columnName === 'customer_no') {
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
                            '<input type="text" class="form-control form-control-sm rule-value" placeholder="Enter customer id">'
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
                        baseOperators.push({
                            value: 'contains',
                            text: 'contains'
                        }, {
                            value: 'starts_with',
                            text: 'starts with'
                        }, {
                            value: 'ends_with',
                            text: 'ends with'
                        }, {
                            value: 'is_empty',
                            text: 'is empty'
                        }, {
                            value: 'is_not_empty',
                            text: 'is not empty'
                        });

                        if (columnName === 'customer_no') {
                            baseOperators.push({
                                value: 'greater_than',
                                text: 'greater than'
                            }, {
                                value: 'less_than',
                                text: 'less than'
                            });
                        }

                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        $valueContainer.append(
                            '<input type="text" class="form-control form-control-sm rule-value" placeholder="Enter value">'
                        );
                    }
                }
            });

            // Delete handler remains the same
            $(document).ready(function() {
                $(document).on('click', '.delete', function(event) {
                    event.preventDefault();
                    var i = $(this).data('id');
                    var id = $.trim(i);
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: !0,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#f34e4e",
                        confirmButtonText: "Yes, delete it!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/orders/') }}/" + id,
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                    "id": id
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(data) {
                                    console.log(data);
                                    if (data.status == 1) {
                                        location.reload(true);
                                        Swal.fire({
                                            title: "Deleted!",
                                            text: "Your file has been deleted.",
                                            icon: "success"
                                        })
                                    } else {
                                        location.reload(true);
                                        Swal.fire({
                                            title: "Cancelled",
                                            text: "Delete operation cancelled!",
                                            type: "error"
                                        })
                                    }
                                },
                            });
                        }
                    })
                });
            });

            // Success/error messages
            $(function() {
                @if (Session::has('success'))
                    Swal.fire({
                        title: "Good job!",
                        text: "{{ Session::get('success') }}",
                        icon: "success"
                    })
                @elseif (Session::has('error'))
                    Swal.fire({
                        title: "Sorry!",
                        text: "{{ Session::get('error') }}",
                        icon: "error"
                    })
                @endif
            });
        </script>
    @endpush
@endsection
