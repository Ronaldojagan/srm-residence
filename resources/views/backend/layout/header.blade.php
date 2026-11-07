<div class="navbar-custom">
    <!-- Left Section (Mobile: Burger + Brand | Desktop: Brand Box only) -->
    <div class="d-flex align-items-center h-100 float-start">
        <!-- Mobile & Tablet Hamburger Menu Toggle -->
        <button class="btn button-menu-mobile waves-effect waves-light res-mobile-menu-btn d-lg-none" type="button" aria-label="Toggle navigation" id="resMobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Brand Logo Box (250px on desktop, auto on mobile) -->
        <div class="logo-box">
            <a href="{{ url('/admin/dashboard') }}" class="res-logo-brand">
                <div class="res-logo-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <div class="res-logo-details d-none d-sm-flex">
                    <span class="res-logo-title">SRM RESIDENCE</span>
                    <span class="res-logo-subtitle d-none d-xl-block">Student & Living Hub</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Center: Academic Year & Live Date (Desktop Only) -->
    <div class="res-topbar-center d-none d-lg-flex align-items-center">
        <span class="res-topbar-pill">
            <span class="res-pulsing-dot me-2"></span>
            <span>Academic Year 2026-27</span>
        </span>
        <span class="text-muted font-13 fw-medium">
            <i class="far fa-calendar-alt text-primary me-1"></i> {{ date('l, d M Y') }}
        </span>
    </div>

    <!-- Right Controls: Profile & Quick Links (Floated Right) -->
    <ul class="list-unstyled topnav-menu float-end mb-0 d-flex align-items-center h-100">
        <!-- Download Android APK (Desktop & Tablet only to keep mobile header clean) -->
        <li class="d-none d-md-inline-block me-2">
            <a href="{{ url('download/srm-residence.apk') }}" download class="btn btn-sm btn-outline-success py-1 px-3 font-12" style="border-radius: 20px;" title="Download Android App">
                <i class="fab fa-android me-1"></i> <span>Android App</span>
            </a>
        </li>

        <!-- Quick Action Shortcut: View Rooms (Desktop only) -->
        <li class="d-none d-lg-inline-block me-2">
            <a href="{{ url('/admin/rooms') }}" class="btn btn-sm btn-outline-primary py-1 px-3 font-12" style="border-radius: 20px;">
                <i class="fas fa-door-open me-1"></i> {{ \App\Models\Room::count() }} Rooms
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
                <a href="{{ url('download/srm-residence.apk') }}" download class="dropdown-item notify-item text-success">
                    <i class="fab fa-android"></i>
                    <span>Download Mobile APK</span>
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
