@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        {{-- <button id="mark-all-read" class="btn btn-primary">
                            <i class="mdi mdi-check-all"></i> Mark All as Read
                        </button> --}}
                    </div>
                    <h4 class="page-title">Product Approval Requests</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive table-border">
                            <table id="product-requests-table" class="table table-stripe">
                                <thead class="theme-menu-color">
                                    <tr>
                                        <th>Product</th>
                                        <th>Shop</th>
                                        <th>Category</th>
                                        <th>Subcategory</th>
                                        <th>Price</th>
                                        <th>Requested At</th>
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

    <div class="modal fade" id="productDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Product Details</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="productDetailsContent">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger reject-product">
                        <i class="mdi mdi-close"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success approve-product">
                        <i class="mdi mdi-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Rejection Reason</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectionForm">
                        <input type="hidden" id="productId">
                        <div class="mb-3">
                            <label for="rejectionReason" class="form-label">Please specify the reason for rejection</label>
                            <textarea class="form-control" id="rejectionReason" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmReject">Submit Rejection</button>
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            $(document).ready(function() {
                var table = $('#product-requests-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('admin/request-product') }}",
                    columns: [{
                            data: 'product_info',
                            name: 'name'
                        },
                        {
                            data: 'shop_info',
                            name: 'shop.name'
                        },
                        {
                            data: 'category',
                            name: 'category'
                        },
                        {
                            data: 'sub_category',
                            name: 'sub_category'
                        },
                        {
                            data: 'amount',
                            name: 'amount',
                            render: function(data) {
                                return '₹ ' + parseFloat(data).toFixed(2);
                            }
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function(data) {
                                return new Date(data).toLocaleString();
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [5, 'desc']
                    ]
                });

                $(document).on('click', '.view-product', function() {
                    var productId = $(this).data('id');
                    var modal = $('#productDetailsModal');

                    $('#productDetailsContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading product details...</p>
            </div>
        `);

                    modal.find('.approve-product, .reject-product').data('id', productId);
                    modal.modal('show');

                    $.ajax({
                        url: "{{ url('admin/products-request-details') }}/" + productId,
                        type: "GET",
                        data: {
                            id: productId
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#productDetailsContent').html(response.html);
                            } else {
                                $('#productDetailsContent').html(`
                        <div class="alert alert-danger">
                            ${response.message}
                        </div>
                    `);
                            }
                        }
                    });
                });

                // Approve product
                $(document).on('click', '.approve-product', function() {
                    var productId = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to approve this product?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, approve it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('admin/products-request-update-status') }}/" +
                                    productId,
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    id: productId,
                                    status: 1
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire(
                                            'Approved!',
                                            'Product approved successfully.',
                                            'success'
                                        );
                                        table.ajax.reload();
                                        $('#productDetailsModal').modal('hide');
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                }
                            });
                        }
                    });
                });


                var currentRejectProductId = null;

                $(document).on('click', '.reject-product', function() {
                    currentRejectProductId = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to reject this product?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, reject it!',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#aaa',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('admin/products-request-update-status') }}/" +
                                    currentRejectProductId,
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    id: currentRejectProductId,
                                    status: -1
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Rejected!', 'Product has been rejected.',
                                            'success');
                                        table.ajax.reload();
                                        $('#productDetailsModal').modal('hide');
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                }
                            });
                        }
                    });
                });


                $('#mark-all-read').click(function() {
                    $.ajax({
                        url: "{{ url('admin.products.mark-all-read') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success('All notifications marked as read');
                                $('.noti-icon-badge').remove();
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
