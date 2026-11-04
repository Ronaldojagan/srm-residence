@extends('backend.layout.main')
@section('content')
    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-6">
            <h4 class="page-title-main page">Change Password</h4>
            <p class="text-muted small mb-0">Ensure your account is using a strong, secure administrative password.</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ url('admin/profile') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Profile
            </a>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-6 col-md-8 col-sm-12">
            @if (session('status'))
                <div class="alert alert-success bg-success text-light py-2 px-3 rounded-3 font-13 mb-3 d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                </div>
            @endif

            <div class="card card-shadow border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark font-15"><i class="fas fa-shield-alt text-primary me-2"></i> Security Credentials</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ auth()->user()->type == \App\Enums\UserType::SUPERADMIN ? url('/admin/password') : url('/shop-admin/password') }}" method="post">
                        @csrf
                        <!-- Old Password -->
                        <div class="mb-3 position-relative">
                            <label class="form-label text-dark">Current Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" id="old_password" name="old_password"
                                    class="form-control pe-5 @error('old_password') is-invalid @enderror"
                                    placeholder="Enter current password" value="{{ old('old_password') }}" required>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted pe-3 text-decoration-none" onclick="togglePassword('old_password')">
                                    <i class="fas fa-eye" id="icon_old_password"></i>
                                </button>
                            </div>
                            @error('old_password')
                                <span class="invalid-feedback d-block mt-1">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-3 position-relative">
                            <label class="form-label text-dark">New Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" id="new_password" name="new_password"
                                    class="form-control pe-5 @error('new_password') is-invalid @enderror"
                                    placeholder="Enter new strong password" value="{{ old('new_password') }}" required>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted pe-3 text-decoration-none" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye" id="icon_new_password"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <span class="invalid-feedback d-block mt-1">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4 position-relative">
                            <label class="form-label text-dark">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" id="confirm_password" name="confirm_password"
                                    class="form-control pe-5 @error('confirm_password') is-invalid @enderror"
                                    placeholder="Confirm new password" value="{{ old('confirm_password') }}" required>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted pe-3 text-decoration-none" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="icon_confirm_password"></i>
                                </button>
                            </div>
                            @error('confirm_password')
                                <span class="invalid-feedback d-block mt-1">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ url('admin/profile') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-key me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script>
            function togglePassword(id) {
                var input = document.getElementById(id);
                var icon = document.getElementById('icon_' + id);

                if (input && icon) {
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
            }
        </script>
    @endpush
@endsection
