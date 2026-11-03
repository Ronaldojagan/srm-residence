@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    @endpush
    <div class="row justify-content-between">
        <div class="col-md-6 mt-3 mb-3">
            <h4 class="page-title">
                Product Alerts
            </h4>
        </div>
        <div class="col-md-6 mt-3 flex-wrap align-items-center justify-content-sm-end">
            <a href="{{url('shop-admin/dashboard')}}" type="button"
                class="btn add-btn float-end waves-effect waves-light button-color text-white"><i
                    class="mdi mdi-arrow-left-circle me-1"></i> Back</a>
        </div>
    </div>

    <div class="row justify-content-between">
        <div class="col-md-6 mt-3"></div>
        <div class="col-md-6 text-end">
            <button id="markAllRead" class="btn btn-sm btn-primary">
                <i class="mdi mdi-check-all"></i> Mark All as Read
            </button>
            <div class="btn-group ms-2">
                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    Filter
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item filter-item" href="#" data-filter="all">All Alerts</a></li>
                    <li><a class="dropdown-item filter-item" href="#" data-filter="unread">Unread Only</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item filter-item" href="#" data-filter="critical">Critical Alerts</a></li>
                    <li><a class="dropdown-item filter-item" href="#" data-filter="low">Low Stock Alerts</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive table-border">
                        <table id="alertsTable" class="table table-striped data-table">
                            <thead class="theme-menu-color">
                                <tr>
                                    <th width="40px"></th>
                                    <th>Product</th>
                                    <th>Alert Type</th>
                                    <th>Message</th>
                                    <th>Triggered By</th>
                                    <th>Date</th>
                                    <th>Status</th>
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
@endsection

@push('footer_content')
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"
        integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#alertsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('shop-admin/alerts') }}",
                    data: function(d) {
                        d.filter = $('#filter').val();
                    }
                },
                columns: [{
                        data: 'icon',
                        name: 'icon',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'product_name',
                        name: 'product.name'
                    },
                    {
                        data: 'type_badge',
                        name: 'type'
                    },
                    {
                        data: 'message',
                        name: 'message'
                    },
                    {
                        data: 'triggered_by',
                        name: 'triggered_by'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'status_badge',
                        name: 'is_read'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [5, 'desc']
                ]
            });

            $('.filter-item').click(function(e) {
                e.preventDefault();
                var filter = $(this).data('filter');
                table.ajax.url("{{ url('shop-admin/alerts') }}?filter=" + filter).load();
            });

            $('#markAllRead').click(function() {
                $.ajax({
                    url: "{{ url('shop-admin/alerts-mark-all-read') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        toastr.success(response.success);
                        table.ajax.reload();
                    }
                });
            });

            $(document).on('submit', 'form[action*="/read"]', function(e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        toastr.success(response.success);
                        table.ajax.reload();
                    }
                });
            });
        });
    </script>
@endpush
