@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <link href="{{ url('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush
    <div class="row">
        <div class="col-8 mt-3">
            <h4 class="page-title-main page">My Profile</h4>
        </div>

    </div>
    {{-- <div class="row">
        <div class="col-lg-4 col-xl-4 col-sm-12">
            <div class="card card-shadow edit-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 text-center">
                            <img src="{{ url('img/avatar.jpg') }}" class="mt-4 rounded-circle avatar-xl img-thumbnail mb-2"
                                alt="profile-image">
                        </div>

                        <div class="text-start col-8">
                            <h2 class="font-13 text-color mt-4"><span class="ms-2 name-font">{{ $data->name }}</span>
                            </h2>

                            <p class="font-13 mt-3"><span class="ms-2">{{ $data->email }}</span></p>

                            <p class="font-13 mt-3"><span class="ms-2">{{ $data->phone }}</span></p>

                        </div>
                    </div>
                    <div class="submit_btn mt-2">
                        <a href="{{ url('admin/profile-edit') }}"
                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save"><img
                                src="{{ url('img/edit.png') }}" class="me-2 back-button" alt="edit-image">Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-lg-4 col-xl-4 col-sm-12">
            <div class="card card-shadow edit-card">
                <div class="card-body">
                        <div class="col-12 text-center">
                            <img src="{{ url('img/avatar.jpg') }}" class="mt-4 rounded-circle avatar-xl img-thumbnail mb-2"
                                alt="profile-image">
                        </div>

                        <div class="text-center col-12">
                            <h2 class="font-13 text-color mt-4"><span>{{ $data->name }}</span>
                            </h2>

                            <p class="font-13 mt-3"><span>{{ $data->email }}</span></p>

                            <p class="font-13 mt-3"><span>{{ $data->phone }}</span></p>

                        </div>
                    <div class="text-center mt-2">
                        <a href="{{ auth()->user()->type == \App\Enums\UserType::SUPERADMIN ? url('admin/profile-edit') : url('shop-admin/profile-edit') }}"
                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save"><img
                                src="{{ url('img/edit.png') }}" class="me-2 back-button" alt="edit-image">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('footer_content')
        <script src="{{ url('assets/js/app.min.js') }}"></script>
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
