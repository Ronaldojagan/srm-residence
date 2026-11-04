@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    <!-- Page Title -->
    <div class="row align-items-center mt-3">
        <div class="col-12">
            <h4 class="page-title-main page">My Profile</h4>
            <p class="text-muted small mb-0">View and manage your administrator account information.</p>
        </div>
    </div>

    <!-- Profile Details Card -->
    @php
        $profileUser = $data ?? auth()->user();
    @endphp
    <div class="row mt-3">
        <div class="col-lg-5 col-md-8 col-sm-12">
            <div class="card card-shadow border-0">
                <div class="card-body p-4 text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ url('img/avatar.jpg') }}" class="rounded-circle avatar-xl border border-3 border-light shadow-sm" 
                             style="width: 100px; height: 100px; object-fit: cover;" alt="profile-image">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-2" title="Online"></span>
                    </div>

                    <h4 class="fw-bold text-dark mb-1 font-20">{{ $profileUser->name }}</h4>
                    <span class="badge bg-primary-subtle text-primary mb-3 font-12 px-3 py-1">Administrator</span>

                    <div class="bg-light rounded-3 p-3 text-start mb-4 border border-1 border-light-subtle">
                        <div class="d-flex align-items-center mb-3">
                            <div class="res-nav-badge nav-badge-dashboard me-3" style="width: 36px; height: 36px;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block font-11 text-uppercase fw-bold">Email Address</small>
                                <span class="fw-semibold text-dark font-13">{{ $profileUser->email }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="res-nav-badge nav-badge-tenants me-3" style="width: 36px; height: 36px;">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block font-11 text-uppercase fw-bold">Phone Number</small>
                                <span class="fw-semibold text-dark font-13">{{ $profileUser->phone ?? 'Not provided' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ auth()->user()->type == \App\Enums\UserType::SUPERADMIN ? url('admin/profile-edit') : url('shop-admin/profile-edit') }}"
                            class="btn btn-primary flex-fill py-2">
                            <i class="fas fa-pen-to-square me-2"></i> Edit Profile
                        </a>
                        <a href="{{ auth()->user()->type == \App\Enums\UserType::SUPERADMIN ? url('admin/password') : url('shop-admin/password') }}"
                            class="btn btn-outline-secondary flex-fill py-2">
                            <i class="fas fa-lock me-2"></i> Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
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
    @endpush
@endsection
