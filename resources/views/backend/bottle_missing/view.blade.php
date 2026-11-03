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
                Missing Bottle Details
            </h4>
        </div>
        <div class="col-md-6 mt-3 mb-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/bottle-missing') }}"
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
                    Order ID : {{ $order->order_no ?? '-' }}
                </h5>
                {{-- <span class="badge text-white">
                    <i class="mdi mdi-clock-outline me-1"></i>
                    Date: {{ $missingBottle->created_at->format('d M Y') ?? '-' }}
                </span> --}}
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
                                    <td width="30%" class="text-muted">Name:</td>
                                    <td><strong>{{ $order->user->name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td width="30%" class="text-muted">Phone:</td>
                                    <td><strong>{{ $order->user->phone ?? '-' }}</strong></td>
                                </tr>

                                {{-- <tr>
                                    <td class="text-muted">Status:</td>
                                    <td>
                                        <span class="badge {{ $missingBottle->status ? 'bg-success' : 'bg-danger' }}">
                                            {{ $missingBottle->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr> --}}
                                {{-- <tr>
                                    <td class="text-muted">Created Date:</td>
                                    <td>{{ $missingBottle->created_at->format('d M Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Last Updated:</td>
                                    <td>{{ $missingBottle->updated_at->format('d M Y h:i A') }}</td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column - Image -->
                <div class="col-lg-4">
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tbody>
                                <td width="30%" class="text-muted">Location:</td>
                                <tr>
                                    <td>
                                        <strong>
                                            {{ $shippingAddress->flat_no ?? '-' }},
                                            {{ $shippingAddress->area_name ?? '-' }},
                                            {{ $shippingAddress->street ?? '-' }},
                                            {{ $shippingAddress->city ?? '-' }}
                                            <br>
                                            {{ $shippingAddress->district ?? '-' }}
                                            - {{ $shippingAddress->pincode ?? '-' }}
                                        </strong>
                                    </td>
                                </tr>
                                {{-- <tr>
                                    <td width="30%" class="text-muted">Phone:</td>
                                    <td><strong>{{ $shippingAddress->area_name ?? "-" }}</strong></td>
                                </tr> --}}

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header custom-header-secondary-color text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-white">
                    Missing Bottle Details
                </h5>
                {{-- <span class="badge text-white">
                    <i class="mdi mdi-clock-outline me-1"></i>
                    Date: {{ $missingBottle->created_at->format('d M Y') ?? '-' }}
                </span> --}}
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Left Column - Details -->
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table id="orders-table" class="table table-striped data-table">
                            <thead class="theme-menu-color">
                                <tr>
                                    <th>Missed Date</th>
                                    <th>Returned Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
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
                    ajax: "{{ url('/admin/bottle-missing/list/' . Crypt::encrypt($order->id)) }}",

                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search...",
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            title: 'S.No',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'missed_date',
                            name: 'missed_date',
                        },
                        {
                            data: 'returned_date',
                            name: 'returned_date',
                        },
                        {
                            data: 'status',
                            name: 'status'
                        }
                    ],

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

            });
        </script>
    @endpush
@endsection
