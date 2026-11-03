@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/select/1.3.4/css/select.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet">
    @endpush
    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">Missing Bottles</h4>
            </div>
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
                                    <th>S.No</th>
                                    <th>Order ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="sortable">
                            </tbody>
                        </table>
                    </div>
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
        <script src="https://cdn.datatables.net/select/1.3.4/js/dataTables.select.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

        <script type="text/javascript">
            $(function() {
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/admin/bottle-missing') }}",
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search Missing Bottles...",
                    },
                    columns: [
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'order_id',
                            name: 'order_id'
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
                            data: 'location',
                            name: 'location'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    select: {
                        style: 'multi',
                        selector: 'td:first-child'
                    },
                    initComplete: function() {
                        $('.dataTables_filter').html(`
                            <div class="input-group small-bar">
                                <input type="search" class="form-control form-control-sm" placeholder="Search..." id="datatableSearch" style="border-radius: 10px;">
                                <button class="btn btn-sm btn-outline-secondary" style="display:none" type="button" id="searchButton">
                                </button>
                                <button type="button" class="btn btn-theme-second-success  filter-btn ms-2 data-table-filter-btn" style="border-radius: 10px;">
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

                function initFilterPopup(tableApi) {
                    var popupHTML = `
                    <div class="filter-popup" id="payment-filter-popup" style="display: none;">
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
                    var $popup = $('#payment-filter-popup');
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
                                        page: 'payments',
                                        table_type: 'payment',
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
                            page: 'payments',
                            table_type: 'payment'
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
                    var $popup = $('#payment-filter-popup');
                    var columns = tableApi.columns().header().toArray();
                    var columnOptions = columns.map(function(col, index) {
                        var colData = tableApi.column(index).dataSrc();
                        var colTitle = $(col).text().trim();
                        if (colData !== 'action') {
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
                    // } else if (columnName === 'status') {
                    //    baseOperators.forEach(op => $operatorSelect.append(
                    //         `<option value="${op.value}">${op.text}</option>`));

                    //     var $select = $(`
                    //         <select class="form-control form-control-sm rule-value" data-toggle="select2" data-width="100%" style="width: 100%;">
                    //             <option value="Returned">Returned</option>
                    //             <option value="Pending">Pending</option>
                    //         </select>
                    //     `);

                    //     $valueContainer.append($select);

                    //     $select.select2({
                    //         placeholder: "Select status",
                    //         width: '100%'
                    //     });
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

                        baseOperators.forEach(op => {
                            $operatorSelect.append(`<option value="${op.value}">${op.text}</option>`);
                        });

                        $valueContainer.append(
                            '<input type="text" class="form-control form-control-sm rule-value" placeholder="Enter value">'
                        );
                    }
                }

                // Individual delete
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
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/bottle-missing/') }}/" + id,
                                data: {
                                    "_token": "{{ csrf_token() }}"
                                },
                                success: function(data) {
                                    if (data.status) {
                                        Swal.fire({
                                            title: "Deleted!",
                                            text: data.message,
                                            icon: "success"
                                        });
                                        table.ajax.reload(null, false);
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: data.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Failed to delete Missing Bottle. Please try again later.",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });
            });
        </script>

        <script>
            $(function() {
                @if (Session::has('success'))
                    Swal.fire({
                        title: "Good job!",
                        text: "{{ Session::get('success') }}",
                        icon: "success"
                    });
                @elseif (Session::has('error'))
                    Swal.fire({
                        title: "Sorry!",
                        text: "{{ Session::get('error') }}",
                        icon: "error"
                    });
                @endif
            });
        </script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="{{ url('assets/js/pages/sweet-alerts.init.js') }}"></script>
    @endpush
@endsection
