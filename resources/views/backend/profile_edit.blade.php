@extends('backend.layout.main')
@section('content')
    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-6">
            <h4 class="page-title-main page">Edit Profile</h4>
            <p class="text-muted small mb-0">Update your account username, email, and phone contact.</p>
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
                    <h5 class="fw-bold mb-0 text-dark font-15"><i class="fas fa-user-edit text-primary me-2"></i> Account Details</h5>
                </div>
                <div class="card-body p-4">
                    @php
                        $profileUser = $data ?? auth()->user();
                    @endphp
                    <form action="{{ auth()->user()->type == \App\Enums\UserType::SUPERADMIN ? url('/admin/profile') : url('/shop-admin/profile') }}" 
                          method="post" class="form-horizontal">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-dark">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Enter full name" value="{{ old('name', $profileUser->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="Enter email address" value="{{ old('email', $profileUser->email) }}" 
                                oninput="this.value = this.value.toLowerCase()" required>
                            @error('email')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-dark">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                placeholder="Enter phone number" value="{{ old('phone', $profileUser->phone) }}" required>
                            @error('phone')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ url('admin/profile') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
