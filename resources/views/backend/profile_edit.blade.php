@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush
    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">Edit Profile</h4>
            </div>
        </div><!-- end col-->
    </div>
    <div class="row">
        <div class="col-lg-4">
        @if (session('status'))
            <div class="alert alert-success bg-success h5 text-light p-2">
                {{ session('status') }}
            </div>
        @endif
        <div class="card save-card card-shadow">
            <div class="card-body">
                <form action="{{ auth()->user()->type == \App\Enums\UserType::SUPERADMIN ? url('/admin/profile') : url('/shop-admin/profile') }}" method="post" class="form-horizontal"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-3">
                        <label class="form-label text-dark">Username</label>
                        <div class="">
                            <input type="text" name="name"
                                class="form-control input-background input-form @error('name') is-invalid @enderror"
                                placeholder="Enter user name" value="{{ $data->name }}">
                            @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-dark">Email Id</label>
                        <div class="">
                            <input type="email" name="email"
                                class="form-control input-background input-form @error('email') is-invalid @enderror"
                                placeholder="Enter email id" value="{{ $data->email }}" oninput="this.value = this.value.toLowerCase()">
                            @error('email')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-dark">Phone number</label>
                        <div class="">
                            <input type="text" name="phone"
                                class="form-control input-background input-form @error('phone') is-invalid @enderror"
                                placeholder="Enter phone number" value="{{ $data->phone }}">
                            @error('phone')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit"
                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Update</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"
            integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#out-datepicker").flatpickr({
                    dateFormat: "d-m-Y"
                })
            });
            $(document).ready(function() {
                $("#in-datepicker").flatpickr({
                    dateFormat: "d-m-Y"
                })
            });
        </script>
        <script src="{{ url('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    @endpush
@endsection
