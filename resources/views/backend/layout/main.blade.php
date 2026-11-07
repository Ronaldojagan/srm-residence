<!DOCTYPE html>
<html lang="en">
    <head>
        @include('backend.layout.head')
    </head>

    <body data-layout-color="light" data-layout-mode="default" data-layout-size="fluid" data-topbar-color="light" data-leftbar-position="fixed" data-leftbar-color="dark" data-leftbar-size="default" data-sidebar-user="false">

        <div id="wrapper">

            @include('backend.layout.header')

            @include('backend.layout.menu')

            <!-- Mobile Drawer Backdrop -->
            <div class="res-mobile-backdrop" id="resMobileBackdrop"></div>

            <div class="content-page">
                <div class="content">
                    <div class="container-fluid">
                        @yield('content')
                        <div class="modal modal-image" id="image-view">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"></h5>
                                        <button type="button" class="btn-close modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <p></p>
                                    </div>
                                    {{-- <div class="modal-footer">
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('backend.layout.footer')

            </div>

        </div>

        <div class="rightbar-overlay"></div>

        <script src="{{url('assets/libs/jquery/jquery.min.js')}}"></script>
        <script src="{{url('assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{url('assets/libs/simplebar/simplebar.min.js')}}"></script>
        <script src="{{url('assets/libs/node-waves/waves.min.js')}}"></script>
        <script src="{{url('assets/libs/waypoints/lib/jquery.waypoints.min.js')}}"></script>
        <script src="{{url('assets/libs/jquery.counterup/jquery.counterup.min.js')}}"></script>
        <script src="{{url('assets/libs/feather-icons/feather.min.js')}}"></script>

        @stack('footer_content')
        @stack('scripts')
        <script src="{{url('assets/js/app.min.js')}}"></script>
        <script src="{{url('assets/js/custom.js')}}"></script>
        <script>
            $(document).ready(function() {
                // Mobile Burger Toggle
                $(document).on('click', '.res-mobile-menu-btn, #resMobileMenuBtn, .button-menu-mobile', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('body').toggleClass('sidebar-enable');
                });

                // Mobile Drawer Close Button & Backdrop
                $(document).on('click', '.mobile-menu-close, .res-mobile-backdrop', function(e) {
                    e.preventDefault();
                    $('body').removeClass('sidebar-enable');
                });

                // Auto-close on link navigation for screens < 992px
                $(document).on('click', '#side-menu a:not([data-bs-toggle])', function() {
                    if ($(window).width() < 992) {
                        $('body').removeClass('sidebar-enable');
                    }
                });

                // Reset sidebar-enable on resize to desktop
                $(window).on('resize', function() {
                    if ($(window).width() >= 992) {
                        $('body').removeClass('sidebar-enable');
                    }
                });
            });
        </script>
    </body>
</html>
