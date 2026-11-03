@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush
    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">Change Password</h4>
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
                <form action="{{ auth()->user()->type == \App\Enums\UserType::SUPERADMIN ? url('/admin/password') : url('/shop-admin/password') }}" method="post">
                    @csrf
                    <div class="col-12 mb-3 change-position-relative">
                        <label class="form-label text-dark">Old Password</label>
                        <div class="">
                            <input type="password" id="old_password" name="old_password"
                                class="form-control input-background input-form @error('old_password') is-invalid @enderror"
                                placeholder="Enter old password" value="{{ old('old_password') }}">
                            <i class="fas fa-eye change-password-toggle" onclick="togglePassword('old_password')"></i>
                            @error('old_password')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 mb-3 change-position-relative">
                        <label class="form-label text-dark">New Password</label>
                        <div class="">
                            <input type="password" id="new_password" name="new_password"
                                class="form-control input-background input-form @error('new_password') is-invalid @enderror"
                                placeholder="Enter new password" value="{{ old('new_password') }}">
                            <i class="fas fa-eye change-password-toggle" onclick="togglePassword('new_password')"></i>
                            @error('new_password')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 mb-3 change-position-relative">
                        <label class="form-label text-dark">Confirm Password</label>
                        <div class="">
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="form-control input-background input-form @error('confirm_password') is-invalid @enderror"
                                placeholder="Enter confirm password" value="{{ old('confirm_password') }}">
                            <i class="fas fa-eye change-password-toggle" onclick="togglePassword('confirm_password')"></i>
                            @error('confirm_password')
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
        <script>
            function togglePassword(id) {
                var input = document.getElementById(id);
                var icon = document.querySelector(`#${id} + .change-password-toggle`);

                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                } else {
                    input.type = "password";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                }
            }
        </script>

    @endpush
@endsection
