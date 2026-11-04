<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login &bull; {{ config('app.name', 'SRM RESIDENCE') }}</title>
    <link rel="shortcut icon" href="{{ url('img/fev.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="{{ url('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <link href="{{ url('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href="{{ url('css/custom.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('css/modern-overlay.css') }}" rel="stylesheet" type="text/css" />

    <style>
        body, html {
            height: 100%;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            background: radial-gradient(circle at 10% 20%, rgba(14, 161, 177, 0.22) 0%, transparent 45%),
                        radial-gradient(circle at 90% 80%, rgba(99, 102, 241, 0.18) 0%, transparent 50%),
                        #0b1329 !important;
            overflow-x: hidden;
        }

        .res-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .res-login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 1020px;
        }

        .res-login-hero {
            background: linear-gradient(135deg, #0b1329 0%, #0d2238 60%, #086d77 100%);
            padding: 3.5rem 3rem;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .res-login-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(14, 161, 177, 0.15);
            pointer-events: none;
        }

        .res-login-form-side {
            padding: 3.5rem 3rem;
            background: #ffffff;
        }

        .res-feature-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }

        .res-feature-pill i {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(14, 161, 177, 0.25);
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        .res-input-group {
            position: relative;
        }

        .res-input-group .form-control {
            height: 48px;
            padding-left: 42px;
            padding-right: 42px;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0;
            font-size: 14px;
            font-weight: 500;
        }

        .res-input-group .form-control:focus {
            border-color: #0ea1b1;
            box-shadow: 0 0 0 4px rgba(14, 161, 177, 0.15);
        }

        .res-input-group .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
            z-index: 4;
        }

        .res-input-group .toggle-password-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            z-index: 4;
            transition: color 0.2s ease;
        }

        .res-input-group .toggle-password-btn:hover {
            color: #0ea1b1;
        }

        .res-btn-submit {
            height: 48px;
            border-radius: 12px !important;
            background: linear-gradient(135deg, #0ea1b1 0%, #086d77 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 14.5px;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 18px rgba(14, 161, 177, 0.35);
            transition: all 0.2s ease;
        }

        .res-btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(14, 161, 177, 0.45);
            color: #ffffff;
        }

        @media (max-width: 991.98px) {
            .res-login-hero {
                display: none;
            }
            .res-login-form-side {
                padding: 2.5rem 1.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="res-login-container">
        <div class="res-login-card">
            <div class="row g-0">
                <!-- Left Hero Panel (Desktop) -->
                <div class="col-lg-6 res-login-hero">
                    <div>
                        <div class="d-flex align-items-center mb-4">
                            <div class="res-logo-icon me-3">
                                <i class="fas fa-hotel"></i>
                            </div>
                            <div>
                                <h4 class="text-white mb-0 fw-bold font-20">SRM RESIDENCE</h4>
                                <span class="text-white-50 font-12 fw-semibold text-uppercase letter-spacing-1">Student & Living Hub</span>
                            </div>
                        </div>

                        <h2 class="text-white fw-bold mb-3 font-26" style="line-height: 1.3;">
                            Smart Residence &amp; Hostel Management System
                        </h2>
                        <p class="text-white-50 font-14 mb-4">
                            Seamlessly control room occupancy, tenant records, staff workflows, and financial overhead in one unified modern portal.
                        </p>

                        <div class="res-feature-pill">
                            <i class="fas fa-door-open"></i>
                            <div>
                                <div class="text-white fw-bold font-13">Dynamic Room Allocations</div>
                                <div class="text-white-50 font-11">23 Live rooms with real-time occupancy status</div>
                            </div>
                        </div>

                        <div class="res-feature-pill">
                            <i class="fas fa-user-check"></i>
                            <div>
                                <div class="text-white fw-bold font-13">Tenant CRM &amp; History</div>
                                <div class="text-white-50 font-11">Commencement dates, room linking &amp; KYC records</div>
                            </div>
                        </div>

                        <div class="res-feature-pill mb-0">
                            <i class="fas fa-chart-pie"></i>
                            <div>
                                <div class="text-white fw-bold font-13">Room &amp; Personal Expenses</div>
                                <div class="text-white-50 font-11">Categorized tracking with instant financial breakdowns</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top border-white-10 d-flex justify-content-between align-items-center">
                        <span class="text-white-50 font-11">&copy; {{ date('Y') }} SRM RESIDENCE. All rights reserved.</span>
                        <span class="badge bg-success-subtle text-success font-11"><i class="fas fa-shield-halved me-1"></i> Secure Portal</span>
                    </div>
                </div>

                <!-- Right Login Form -->
                <div class="col-lg-6 res-login-form-side">
                    <!-- Mobile Brand Header -->
                    <div class="d-flex d-lg-none align-items-center mb-4 pb-3 border-bottom">
                        <div class="res-logo-icon me-2" style="width: 38px; height: 38px; min-width: 38px; font-size: 16px;">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark font-16">SRM RESIDENCE</div>
                            <div class="text-muted font-10 fw-semibold text-uppercase letter-spacing-1">Student & Living Hub</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-inline-flex p-2 rounded-3 bg-primary-subtle text-primary mb-3">
                            <i class="fas fa-lock-open font-18"></i>
                        </div>
                        <h3 class="fw-bold text-dark font-22 mb-1">Sign In to Account</h3>
                        <p class="text-muted font-13">Enter your administrative credentials to access portal</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger bg-danger text-light py-2 px-3 rounded-3 font-13 mb-3 d-flex align-items-center">
                            <i class="fas fa-circle-exclamation me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Username / Email -->
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label">Email Address</label>
                            <div class="res-input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input name="email" class="form-control @error('email') is-invalid @enderror" 
                                       type="email" id="emailaddress" value="{{ old('email') }}" required 
                                       placeholder="admin@srmresidence.in" autocomplete="email" autofocus>
                            </div>
                            @error('email')
                                <span class="text-danger small mt-1 d-block font-12 fw-medium">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="font-12 text-primary text-decoration-none fw-semibold">Forgot password?</a>
                                @endif
                            </div>
                            <div class="res-input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input name="password" type="password" id="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required autocomplete="current-password">
                                <button type="button" class="toggle-password-btn" id="togglePasswordBtn" aria-label="Toggle password visibility">
                                    <i class="far fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-danger small mt-1 d-block font-12 fw-medium">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3 d-flex align-items-center justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label font-13 text-muted" for="remember">
                                    Remember my session
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mt-4">
                            <button class="btn res-btn-submit" type="submit">
                                <i class="fas fa-arrow-right-to-bracket me-2"></i> Access Dashboard
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top text-muted font-12">
                        <i class="fas fa-shield-halved me-1 text-primary"></i> 256-bit SSL encrypted connection &bull; SRM Residence Portal
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ url('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ url('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (toggleBtn && passwordInput && toggleIcon) {
                toggleBtn.addEventListener('click', function() {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    toggleIcon.classList.toggle('fa-eye', !isPassword);
                    toggleIcon.classList.toggle('fa-eye-slash', isPassword);
                });
            }
        });
    </script>
</body>
</html>
