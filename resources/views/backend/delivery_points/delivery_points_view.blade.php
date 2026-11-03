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
            <h4 class="page-title-main page">
                Delivery Points Details
            </h4>
        </div>
        <div class="col-md-6 mt-3 mb-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/package') }}"
                class="btn add-btn float-end waves-effect waves-light button-color text-white">
                <i class="mdi mdi-keyboard-backspace me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header custom-header-color text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-white">
                    Delivery Points Details
                </h5>
                <span class="badge text-white">
                    <i class="mdi mdi-clock-outline me-1"></i>
                    Created: {{ $deliveryPoint->created_at->format('d M Y') ?? '-' }}
                </span>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Left Column - Details -->
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tbody>
                                <tr>
                                    <td width="30%" class="text-muted">Location:</td>
                                    <td><strong>{{ $deliveryPoint->location }}</strong></td>
                                </tr>
                                <tr>
                                    <td width="30%" class="text-muted">Sub Location:</td>
                                    <td><strong>{{ $deliveryPoint->sub_location }}</strong></td>
                                </tr>

                                <tr>
                                    <td class="text-muted">Status:</td>
                                    <td>
                                        <span class="badge {{ $deliveryPoint->status ? 'bg-success' : 'bg-danger' }}">
                                            {{ $deliveryPoint->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Created Date:</td>
                                    <td>{{ $deliveryPoint->created_at->format('d M Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Last Updated:</td>
                                    <td>{{ $deliveryPoint->updated_at->format('d M Y h:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- <!-- Right Column - Image -->
                <div class="col-lg-4">
                    @if ($deliveryPoint->image)
                        <div class="text-center">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
                                <img src="{{ url('storage/category/' . $deliveryPoint->image) }}"
                                    alt="{{ $deliveryPoint->name }}" class="img-thumbnail cursor-zoom"
                                    style="max-height: 200px;">
                            </a>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#imageModal">
                                    <i class="mdi mdi-magnify me-1"></i> View Full Size
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="mdi mdi-image-off-outline display-5 opacity-50"></i>
                            <p class="mt-2 mb-0">No image available</p>
                        </div>
                    @endif
                </div> --}}
            </div>
        </div>
    </div>

    {{-- <div class="row">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">All Subcategory</h4>
            </div>
        </div>
        <div class="col-md-6 mt-3 align-items-center justify-content-sm-end d-flex">
            <a href="{{ url('/admin/sub-category/create/' . Crypt::encrypt($deliveryPoint->id)) }}" type="button"
                class="btn add-btn float-end waves-effect waves-light button-color text-white">
                <i class="mdi mdi-plus-circle me-1"></i> Create Subcategory
            </a>
        </div>
    </div>

    <!-- Sub Categories Section -->
    <div class="card border-0 shadow-sm">
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
                            <th>Sort Order</th>
                            <th>Date</th>
                            <th>Category Name</th>
                            <th>Sub Category Name</th>
                            <th>Image</th>
                            <th>Product Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="sortable">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header custom-header-color">
                    <h5 class="modal-title text-white">
                        {{ $deliveryPoint->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img src="{{ url('storage/category/' . $deliveryPoint->image) }}" class="img-fluid rounded"
                        alt="Full Category Image" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div> --}}

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
                    ajax: "{{ url('/admin/sub-category/' . Crypt::encrypt($deliveryPoint->id)) }}",
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search subcategories...",
                    },
                    columns: [{
                            data: 'checkbox',
                            name: 'checkbox',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'sort_order',
                            name: 'sort_order',
                            className: 'sort-order-cell'
                        },
                        {
                            data: 'date',
                            name: 'date',
                        },
                        {
                            data: 'category_name',
                            name: 'category_name'
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
                            data: 'product_count',
                            name: 'product_count'
                        },
                        {
                            data: 'status',
                            name: 'status'
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
                            url: "{{ url('/admin/sub-category/update-order') }}",
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
                    var checkedCheckboxes = $('.data-table tbody input[type="checkbox"].select-checkbox:checked').length;

                    $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
                    $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);

                    toggleBulkActions();
                });

                // Toggle bulk actions visibility
                function toggleBulkActions() {
                    var count = $('.data-table tbody input[type="checkbox"].select-checkbox:checked').length;
                    if (count > 0) {
                        $('#bulkActionsPanel').show();
                        $('#bulkAction').prop('disabled', false);
                    } else {
                        $('#bulkActionsPanel').hide();
                        $('#bulkAction').prop('disabled', true).val('');
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
                            text: "No subcategories selected.",
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
                        text: "You are about to " + actionText + " " + ids.length + " subcategories.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28bb4b",
                        cancelButtonColor: "#f34e4e",
                        confirmButtonText: "Yes, " + actionText + " them!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/sub-category/bulk-status') }}",
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
                        } else {
                            // Reset the select if cancelled
                            $('#bulkAction').val('');
                        }
                    });
                }

                // Confirm bulk delete
                function confirmBulkDelete(ids) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this! This will delete " + ids.length + " subcategories.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f34e4e",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete them!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performBulkDelete(ids);
                        } else {
                            // Reset the select if cancelled
                            $('#bulkAction').val('');
                        }
                    });
                }

                // Perform bulk delete
                function performBulkDelete(ids) {
                    $.ajax({
                        url: "{{ url('/admin/sub-category/bulk-delete') }}",
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
                                text: "Failed to delete subcategories",
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
                                url: "{{ url('/admin/sub-category/') }}/" + id,
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
                                        text: "Failed to delete subcategory. Please try again later.",
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
                        url: "{{ url('/admin/sub-category/status') }}",
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
