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
                <h4 class="page-title-main page">Packages</h4>
            </div>
        </div>
        <div class="col-md-6 mt-3 flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/package/create') }}" type="button"
                class="btn add-btn float-end waves-effect waves-light button-color text-white">
                <i class="mdi mdi-plus-circle me-1"></i> Create Package
            </a>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <div class="bulk-actions mb-3 d-flex justify-content-end" id="bulkActionsPanel">
                        <div class="bulk-select-container">
                            <select class="form-select btn-theme-dropdown" id="bulkAction">
                                <option value="">Bulk Actions</option>
                                <option value="1">Set as Active</option>
                                <option value="0">Set as Inactive</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table">
                            <thead class="theme-menu-color">
                                <tr>
                                    <th width="20px">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>S.No</th>
                                    <th>Package Name</th>
                                    <th>Total Members Joined</th>
                                    <th>Amount</th>
                                    <th>Days</th>
                                    <th>Status</th>
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
                    ajax: "{{ url('/admin/package') }}",
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search packages...",
                    },
                    columns: [
                        {
                            data: 'checkbox',
                            name: 'checkbox',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'package_name',
                            name: 'package_name',
                        },
                        {
                            data: 'totalmembers',
                            name: 'totalmembers',
                        },
                        {
                            data: 'amount',
                            name: 'amount',
                        },
                        {
                            data: 'days',
                            name: 'days',
                        },
                        {
                            data: 'status',
                            name: 'status',
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
                        $('.sortable').sortable({
                            items: 'tr',
                            cursor: 'move',
                            opacity: 0.7,
                            handle: '.sort-order-handle',
                            helper: 'clone',
                            update: function() {
                                updateSortOrder();
                            }
                        }).disableSelection();
                    }
                });
                // $('.data-table').DataTable().order([1, 'desc']).draw();

                // Update sort order after drag and drop
                function updateSortOrder() {
                    var order = [];
                    $('.sortable tr').each(function(index) {
                        var dataId = $(this).find('.sort-order-handle').data('id');
                        if (dataId) {
                            order.push({
                                id: dataId,
                                position: index + 1
                            });
                        }
                    });

                    if (order.length > 0) {
                        $.ajax({
                            type: "POST",
                            url: "{{ url('/admin/package/update-order') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                order: order
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "Success!",
                                        text: "Sort order updated successfully.",
                                        icon: "success",
                                        timer: 1000,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload(null, false);
                                } else {
                                    Swal.fire({
                                        title: "Error!",
                                        text: response.message,
                                        icon: "error"
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: "Error!",
                                    text: "Failed to update sort order",
                                    icon: "error"
                                });
                            }
                        });
                    }
                }

                // Select all checkbox
                $('#selectAll').on('click', function() {
                    var isChecked = this.checked;
                    $('.data-table tbody input[type="checkbox"].select-checkbox').prop('checked', isChecked);
                    toggleBulkActions();
                });

                // Update when individual checkbox is clicked
                $('.data-table tbody').on('change', 'input[type="checkbox"].select-checkbox', function() {
                    var totalCheckboxes = $('.data-table tbody input[type="checkbox"].select-checkbox').length;
                    var checkedCheckboxes = $(
                        '.data-table tbody input[type="checkbox"].select-checkbox:checked').length;

                    $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
                    $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes <
                        totalCheckboxes);

                    toggleBulkActions();
                });

                // Toggle bulk actions visibility
                function toggleBulkActions() {
                    var count = $('.data-table tbody input[type="checkbox"].select-checkbox:checked').length;
                    if (count > 0) {
                        $('#bulkActionsPanel').show();
                    } else {
                        $('#bulkActionsPanel').hide();
                        $('#bulkAction').val('');
                    }
                }

                // Handle bulk action selection
                $('#bulkAction').on('change', function() {
                    var action = $(this).val();
                    if (!action) return;

                    var selected = [];
                    $('.data-table tbody input[type="checkbox"].select-checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });

                    if (selected.length === 0) {
                        Swal.fire({
                            title: "Error!",
                            text: "No categories selected.",
                            icon: "error"
                        });
                        return;
                    }

                    if (action === 'delete') {
                        confirmBulkDelete(selected);
                    } else {
                        updateBulkStatus(selected, action);
                    }

                    // Reset the dropdown
                    $(this).val('');
                });

                // Update status for bulk selection
                function updateBulkStatus(ids, status) {
                    var actionText = status == '1' ? 'activate' : 'deactivate';

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You are about to " + actionText + " " + ids.length + " categories.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#f34e4e",
                        confirmButtonText: "Yes, " + actionText + " them!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/package/bulk-status') }}",
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    ids: ids,
                                    status: status
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: "Success!",
                                            text: response.message,
                                            icon: "success"
                                        });
                                        table.ajax.reload(null, false);
                                        toggleBulkActions();
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Failed to update status",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                }

                // Confirm bulk delete
                function confirmBulkDelete(ids) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this! This will delete " + ids.length +
                            " packages.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f34e4e",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete them!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performBulkDelete(ids);
                        }
                    });
                }

                // Perform bulk delete
                function performBulkDelete(ids) {
                    $.ajax({
                        url: "{{ url('/admin/package/bulk-delete') }}",
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}",
                            ids: ids
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success"
                                });
                                table.ajax.reload(null, false);
                                toggleBulkActions();
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message,
                                    icon: "error"
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to delete packages",
                                icon: "error"
                            });
                        }
                    });
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
                                url: "{{ url('/admin/package/') }}/" + id,
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
                                        text: "Failed to delete package. Please try again later.",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });

                // Quick status toggle
                $(document).on('change', '.status-toggle', function() {
                    var $this = $(this);
                    var id = $this.data('id');
                    var status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/package/status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        },
                        success: function(response) {
                            if (!response.success) {
                                // Revert if failed
                                $this.prop('checked', !$this.prop('checked'));
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message,
                                    icon: "error"
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Revert if failed
                            $this.prop('checked', !$this.prop('checked'));
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to update status",
                                icon: "error"
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
