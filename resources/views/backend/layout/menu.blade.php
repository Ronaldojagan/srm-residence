<div class="left-side-menu">
    <div class="h-100" data-simplebar>
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
                        <span class="badge bg-primary-subtle text-primary ms-auto font-11">23</span>
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

        </div>
        <div class="clearfix"></div>
    </div>
</div>
