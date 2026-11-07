<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Set New Password &bull; {{ config('app.name', 'SRM RESIDENCE') }}</title>
    <link rel="shortcut icon" href="{{ url('img/fev.png') }}">
    <link rel="apple-touch-icon" href="{{ url('img/icon-192.png') }}">
    <link rel="manifest" href="{{ url('manifest.json') }}">
    <meta name="theme-color" content="#0ea1b1">

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
            max-width: 900px;
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

        .res-login-hero::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 161, 177, 0.3) 0%, transparent 70%);
            pointer-events: none;
        }

        .res-login-form-side {
            padding: 3.5rem 3rem;
        }

        .res-input-group {
            position: relative;
        }

        .res-input-group .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
            z-index: 4;
            transition: color 0.2s ease;
        }

        .res-input-group .form-control {
            padding-left: 44px;
            height: 48px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .res-input-group .form-control:focus {
            border-color: #0ea1b1;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(14, 161, 177, 0.12);
        }

        .res-btn-submit {
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0ea1b1 0%, #086d77 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
            box-shadow: 0 8px 20px -4px rgba(14, 161, 177, 0.45);
            transition: all 0.2s ease;
        }

        .res-btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -4px rgba(14, 161, 177, 0.55);
            color: #ffffff;
        }

        @media (max-width: 991.98px) {
            .res-login-hero {
                display: none;
            }
            .res-login-form-side {
                padding: 2.25rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="res-login-container">
        <div class="res-login-card">
            <div class="row g-0">
                <!-- Left Hero Panel -->
                <div class="col-lg-5 res-login-hero d-none d-lg-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-4">
                            <div class="res-logo-icon me-2" style="width: 44px; height: 44px; font-size: 20px;">
                                <i class="fas fa-hotel"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white font-17 line-height-1">SRM RESIDENCE</div>
                                <div class="text-white-50 font-11 text-uppercase letter-spacing-1">Student & Living Hub</div>
                            </div>
                        </div>

                        <h2 class="fw-extrabold text-white font-24 mb-3">Set New Password</h2>
                        <p class="text-white-50 font-14 mb-4">
                            Choose a secure password for your administrative portal account.
                        </p>
                    </div>

                    <div class="border-top border-secondary border-opacity-25 pt-3">
                        <div class="d-flex align-items-center gap-2 text-white-50 font-12">
                            <i class="fas fa-shield-halved text-success"></i>
                            <span>Protected by 256-bit encryption</span>
                        </div>
                    </div>
                </div>

                <!-- Right Form Panel -->
                <div class="col-lg-7 res-login-form-side">
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
                        <h3 class="fw-bold text-dark font-22 mb-1">Create New Password</h3>
                        <p class="text-muted font-13">Enter and confirm your new password below</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger bg-danger text-light py-2 px-3 rounded-3 font-13 mb-3 d-flex align-items-center">
                            <i class="fas fa-circle-exclamation me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label font-13 fw-semibold text-dark">Email Address</label>
                            <div class="res-input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input name="email" class="form-control @error('email') is-invalid @enderror" 
                                       type="email" id="emailaddress" value="{{ $email ?? old('email') }}" readonly required 
                                       placeholder="admin@srmresidence.in" autocomplete="email">
                            </div>
                            @error('email')
                                <span class="text-danger small mt-1 d-block font-12 fw-medium">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label font-13 fw-semibold text-dark">New Password</label>
                            <div class="res-input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input name="password" type="password" id="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Min 8 characters" required autocomplete="new-password">
                            </div>
                            @error('password')
                                <span class="text-danger small mt-1 d-block font-12 fw-medium">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label font-13 fw-semibold text-dark">Confirm Password</label>
                            <div class="res-input-group">
                                <i class="fas fa-lock-check input-icon"></i>
                                <input name="password_confirmation" type="password" id="password_confirmation" 
                                       class="form-control" placeholder="Re-enter new password" required autocomplete="new-password">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mt-4">
                            <button class="btn res-btn-submit" type="submit">
                                <i class="fas fa-key me-2"></i> Update Password
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top text-muted font-12">
                        <i class="fas fa-shield-halved me-1 text-primary"></i> 256-bit SSL encrypted &bull; SRM Residence Portal
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
