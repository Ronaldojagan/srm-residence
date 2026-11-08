<div class="left-side-menu">
    <div class="h-100" data-simplebar>
        <!-- Mobile Sidebar Header (Brand + Close Button) -->
        <div class="d-flex d-lg-none justify-content-between align-items-center px-3 py-3 border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="res-logo-icon" style="width: 32px; height: 32px; font-size: 15px;">
                    <i class="fas fa-hotel"></i>
                </div>
                <div>
                    <div class="text-white font-13 fw-bold line-height-1">SRM RESIDENCE</div>
                    <div class="text-white-50 font-10">Mobile Portal</div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-light rounded-circle mobile-menu-close p-0" style="width: 30px; height: 30px; line-height: 28px; text-align: center;" aria-label="Close navigation">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="sidebar-menu">
            <ul id="side-menu">
                @if(auth()->user()->type == \App\Enums\UserType::SUPERADMIN)

                <!-- Section: Overview -->
                <li class="menu-title">Overview</li>
                <li class="{{ request()->is('admin/dashboard*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/dashboard') }}" class="{{ request()->is('admin/dashboard*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-dashboard"><i class="fas fa-chart-pie"></i></span>
                        <span class="ms-1"> Dashboard </span>
                    </a>
                </li>

                <!-- Section: Residence Management -->
                <li class="menu-title">Management</li>
                <li class="{{ request()->is('admin/users*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/users') }}" class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-users"><i class="fas fa-users"></i></span>
                        <span class="ms-1"> Users </span>
                    </a>
                </li>
                <li class="{{ request()->is('admin/tenants*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/tenants') }}" class="{{ request()->is('admin/tenants*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-tenants"><i class="fas fa-user-check"></i></span>
                        <span class="ms-1"> Tenants </span>
                    </a>
                </li>
                <li class="{{ request()->is('admin/rooms*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/rooms') }}" class="{{ request()->is('admin/rooms*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-rooms"><i class="fas fa-door-open"></i></span>
                        <span class="ms-1"> Rooms </span>
                        <span class="badge bg-primary-subtle text-primary ms-auto font-11">{{ \App\Models\Room::count() }}</span>
                    </a>
                </li>
                <li class="{{ request()->is('admin/billing*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/billing') }}" class="{{ request()->is('admin/billing*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-billing"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span class="ms-1"> Bills & Payments </span>
                    </a>
                </li>
                <li class="{{ request()->is('admin/expenses*') ? 'menuitem-active' : '' }}">
                    <a href="#sidebarExpenses" data-bs-toggle="collapse" class="{{ request()->is('admin/expenses*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->is('admin/expenses*') ? 'true' : 'false' }}">
                        <span class="res-nav-badge nav-badge-expenses"><i class="fas fa-wallet"></i></span>
                        <span class="ms-1"> Expenses </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->is('admin/expenses*') ? 'show' : '' }}" id="sidebarExpenses">
                        <ul class="nav-second-level">
                            <li class="{{ request()->is('admin/expenses/rooms*') ? 'menuitem-active' : '' }}">
                                <a href="{{ url('/admin/expenses/rooms') }}" class="{{ request()->is('admin/expenses/rooms*') ? 'active' : '' }}">
                                    <i class="fas fa-bed me-2 font-11"></i> Room Expenses
                                </a>
                            </li>
                            <li class="{{ request()->is('admin/expenses/personal*') ? 'menuitem-active' : '' }}">
                                <a href="{{ url('/admin/expenses/personal') }}" class="{{ request()->is('admin/expenses/personal*') ? 'active' : '' }}">
                                    <i class="fas fa-user-tag me-2 font-11"></i> Personal Expenses
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="{{ request()->is('admin/loans*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/loans') }}" class="{{ request()->is('admin/loans*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-loans"><i class="fas fa-hand-holding-usd"></i></span>
                        <span class="ms-1"> Loans & EMI </span>
                        @php
                            try {
                                $activeLoansBadge = \App\Models\Loan::where('status', 'active')->count();
                            } catch (\Throwable $e) {
                                $activeLoansBadge = 0;
                            }
                        @endphp
                        @if($activeLoansBadge > 0)
                            <span class="badge bg-warning-subtle text-warning border border-warning ms-auto font-11">{{ $activeLoansBadge }}</span>
                        @endif
                    </a>
                </li>
                <li class="{{ request()->is('admin/salary*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/salary') }}" class="{{ request()->is('admin/salary*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-salary"><i class="fas fa-wallet"></i></span>
                        <span class="ms-1"> Salary </span>
                    </a>
                </li>

                <!-- Section: System & Insights -->
                <li class="menu-title">Insights & Config</li>
                <li class="{{ request()->is('admin/reports*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/reports') }}" class="{{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-reports"><i class="fas fa-chart-line"></i></span>
                        <span class="ms-1"> Reports </span>
                    </a>
                </li>
                <li class="{{ request()->is('admin/settings*') ? 'menuitem-active' : '' }}">
                    <a href="{{ url('/admin/settings') }}" class="{{ request()->is('admin/settings*') ? 'active' : '' }}">
                        <span class="res-nav-badge nav-badge-settings"><i class="fas fa-sliders"></i></span>
                        <span class="ms-1"> Settings </span>
                    </a>
                </li>
                @endif
            </ul>

            <!-- Modern Sidebar Status Card -->
            <div class="res-sidebar-status-card">
                <div class="d-flex align-items-center mb-1">
                    <span class="res-pulsing-dot me-2"></span>
                    <span class="text-white-50 font-11 text-uppercase fw-bold letter-spacing-1">Hostel System</span>
                </div>
                <div class="text-white font-13 fw-bold mb-1">SRM RESIDENCE</div>
                <div class="text-white-50 font-11 d-flex justify-content-between align-items-center">
                    <span>Active Campus</span>
                    <span class="badge bg-success text-white font-10">v2.4 Live</span>
                </div>
            </div>

            <!-- Download Android APK Button in Sidebar -->
            <div class="px-3 pb-3">
                <a href="{{ url('download/srm-residence.apk') }}" download class="btn btn-sm w-100 btn-outline-success font-12 d-flex align-items-center justify-content-center gap-2 py-2" style="border-radius: 10px; border-color: rgba(16, 185, 129, 0.4); background: rgba(16, 185, 129, 0.08);">
                    <i class="fab fa-android font-15 text-success"></i>
                    <span class="fw-semibold text-white">Download Android App</span>
                </a>
            </div>

        </div>
        <div class="clearfix"></div>
    </div>
</div>
