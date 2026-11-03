<div class="navbar-custom nav-bar-color">
    <ul class="list-unstyled topnav-menu float-end mb-0">
        <li class="dropdown d-inline-block d-lg-none">
            <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-bs-toggle="dropdown"
                href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i class="fe-search noti-icon"></i>
            </a>
            <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                <form class="p-3">
                    <input type="text" class="form-control" placeholder="Search ..."
                        aria-label="Recipient's username">
                </form>
            </div>
        </li>

        <!-- Add this notification dropdown before the user dropdown -->
        <li class="dropdown notification-list topbar-dropdown">
            <a class="nav-link dropdown-toggle nav-user me-0 waves-effect waves-light user-name"
                data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <img src="{{ url('img/avatar.jpg') }}" alt="user-image" class="rounded-circle">
                <span class="pro-user-name ms-1 text-dark">
                    {{ auth()->user()->name }} <i class="mdi mdi-chevron-down"></i>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                <div class="dropdown-header noti-title">
                    <h6 class="text-overflow m-0">Welcome !</h6>
                </div>

                @php
                    $profileUrl =
                        auth()->user()->type == \App\Enums\UserType::SUPERADMIN
                            ? url('/admin/profile')
                            : url('/shop-admin/profile');

                    $passwordUrl =
                        auth()->user()->type == \App\Enums\UserType::SUPERADMIN
                            ? url('/admin/password')
                            : url('/shop-admin/password');
                @endphp

                <a href="{{ $profileUrl }}" class="dropdown-item notify-item">
                    <i class="fe-user"></i>
                    <span>My Profile</span>
                </a>
                <a href="{{ $passwordUrl }}" class="dropdown-item notify-item">
                    <i class="fe-eye"></i>
                    <span>Change Password</span>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item notify-item" href="{{ route('logout') }}"onclick="event.preventDefault();
                                                                     document.getElementById('logout-form').submit();">
                    <i class="fe-log-out"></i> <span>Logout</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>

    <div class="logo-box nav-bar-color d-flex align-items-center justify-content-center">
        <a href="#" class="logo logo-dark text-center">
            <span class="logo-sm">
                <img src="{{ url('img/logo-white-small.png') }}" alt="" class="logo-header" width="50px">
            </span>
            <span class="logo-lg">
                <img src="{{ url('img/logo-white.png') }}" alt="" class="logo-header" width="130">
            </span>
        </a>
    </div>

    <ul class="list-unstyled topnav-menu topnav-menu-left mb-0">
        <li>
            <button class="button-menu-mobile disable-btn waves-effect">
                <i class="fe-menu"></i>
            </button>
        </li>
    </ul>

    <div class="clearfix"></div>
</div>

@push('footer_content')
    <script>
        $(document).ready(function() {
            $('.mark-as-read').click(function(e) {
                e.preventDefault();
                const url = $(this).attr('href');

                $.post(url, {
                    _token: "{{ csrf_token() }}"
                }, function() {
                    window.location.href = url;
                });
            });
        });
    </script>
@endpush
