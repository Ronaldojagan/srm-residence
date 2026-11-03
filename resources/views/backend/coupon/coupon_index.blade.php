@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.min.css" rel="stylesheet"> --}}
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @endpush
    <div class="row">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">All Coupons</h4>
            </div>
        </div>
        <div class="col-md-6 mt-3 align-items-center justify-content-sm-end d-flex">
            <a href="{{ url('/admin/coupons/create') }}" type="button" class="btn add-btn float-end waves-effect waves-light button-color text-white"><i
                class="mdi mdi-plus-circle me-1"></i> Create Coupon</a>
        </div>
    </div>


    <div class="row mt-2">
        <div class="col-lg-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <div class="table-responsive table-border">
                        <table class="table table-striped data-table job-table">
                            <thead class="table-theme text-white">
                                <tr>
                                    <th>Date</th>
                                    <th>Code</th>
                                    <th>Amount</th>
                                    <th>From Date</th>
                                    <th>Expire Date</th>
                                    <th>Status</th>
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
    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"
            integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>

        <script type="text/javascript">
            $(function() {
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/admin/coupons') }}",
                    order: [
                        [0, 'desc']
                    ],
                    columnDefs: [{
                        'visible': false,
                        'targets': [0]
                    }],
                    columns: [{
                            data: 'date',
                            name: 'date',
                        },
                        {
                            data: 'code',
                            name: 'code'
                        },
                        {
                            data: 'amount',
                            name: 'amount'
                        },
                        {
                            data: 'start_date',
                            name: 'start_date'
                        },
                        {
                            data: 'expire_date',
                            name: 'expire_date'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'action',
                            name: 'action'
                        },
                    ],
                });

            });
        </script>
         <script type="text/javascript">
            $(document).ready(function() {
                $(document).on('click', '.delete', function(event) {
                    event.preventDefault();
                    var i = $(this).data('id');
                    var id = $.trim(i);
                    Swal.fire({
                        title:"Are you sure?",
                        text:"You won't be able to revert this!",
                        icon:"warning",
                        showCancelButton:!0,
                        confirmButtonColor:"#28bb4b",
                        cancelButtonColor:"#f34e4e",
                        confirmButtonText:"Yes, delete it!"
                    }).then(function(result) {
                        if (result.value) {
                            $.ajax({
                                type: "DELETE",
                                url: "{{ url('/admin/coupons/') }}/"+id,
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
                                        Swal.fire({title:"Deleted!",text:"Your file has been deleted.",icon:"success"})
                                    }else {
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
                $(document).on('change', '.status-toggle', function() {
                    var $this = $(this);
                    var id = $this.data('id');
                    var status = $this.is(':checked') ? '1' : '0';

                    $.ajax({
                        url: "{{ url('/admin/coupon-status') }}",
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
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

        <script src="{{ url('assets/js/pages/sweet-alerts.init.js') }}"></script>
    @endpush
@endsection
