<div class="navbar-custom">
    <!-- Left Logo Brand Box (Starts at left: 0 with zero gap) -->
    <div class="logo-box">
        <a href="{{ url('/admin/dashboard') }}" class="res-logo-brand">
            <div class="res-logo-icon">
                <i class="fas fa-hotel"></i>
            </div>
            <div class="res-logo-details d-none d-lg-flex">
                <span class="res-logo-title">SRM RESIDENCE</span>
                <span class="res-logo-subtitle">Student & Living Hub</span>
            </div>
        </a>
    </div>

    <!-- Center: Academic Year & Live Date (Centered across topbar) -->
    <div class="res-topbar-center d-none d-md-flex align-items-center">
        <span class="res-topbar-pill">
            <span class="res-pulsing-dot me-2"></span>
            <span>Academic Year 2026-27</span>
        </span>
        <span class="text-muted font-13 fw-medium">
            <i class="far fa-calendar-alt text-primary me-1"></i> {{ date('l, d M Y') }}
        </span>
    </div>

    <!-- Right Controls: Profile & Quick Links (Floated Right in UBold architecture) -->
    <ul class="list-unstyled topnav-menu float-end mb-0 d-flex align-items-center">
        <!-- Quick Action Shortcut: View Rooms -->
        <li class="d-none d-sm-inline-block me-2">
            <a href="{{ url('/admin/rooms') }}" class="btn btn-sm btn-outline-primary py-1 px-3 font-12" style="border-radius: 20px;">
                <i class="fas fa-door-open me-1"></i> 23 Rooms
            </a>
        </li>

        <!-- User Profile Dropdown -->
        <li class="dropdown notification-list topbar-dropdown">
            <a class="nav-link dropdown-toggle nav-user me-0 waves-effect waves-light"
                data-bs-toggle="dropdown" data-bs-display="static" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <div class="res-avatar-wrapper">
                    <img src="{{ url('img/avatar.jpg') }}" alt="user-avatar" class="rounded-circle">
                    <span class="res-avatar-online"></span>
                </div>
                <div class="res-user-meta d-none d-md-flex">
                    <span class="res-user-name">{{ auth()->user()->name }}</span>
                    <span class="res-user-role">Administrator</span>
                </div>
                <i class="mdi mdi-chevron-down ms-1 text-muted"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                <div class="dropdown-header noti-title px-3 py-2 border-bottom mb-1">
                    <div class="font-13 fw-bold text-dark">{{ auth()->user()->name }}</div>
                    <div class="font-11 text-muted">{{ auth()->user()->email ?? 'admin@srmresidence.in' }}</div>
                </div>

                @php
                    $profileUrl = auth()->user()->type == \App\Enums\UserType::SUPERADMIN
                        ? url('/admin/profile')
                        : url('/shop-admin/profile');

                    $passwordUrl = auth()->user()->type == \App\Enums\UserType::SUPERADMIN
                        ? url('/admin/password')
                        : url('/shop-admin/password');
                @endphp

                <a href="{{ $profileUrl }}" class="dropdown-item notify-item">
                    <i class="fe-user"></i>
                    <span>My Profile</span>
                </a>
                <a href="{{ $passwordUrl }}" class="dropdown-item notify-item">
                    <i class="fe-lock"></i>
                    <span>Change Password</span>
                </a>
                <a href="{{ url('/admin/settings') }}" class="dropdown-item notify-item">
                    <i class="fe-settings"></i>
                    <span>System Settings</span>
                </a>

                <div class="dropdown-divider my-1"></div>

                <a class="dropdown-item notify-item text-danger" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fe-log-out text-danger"></i> <span>Logout</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>

    <div class="clearfix"></div>
</div>
