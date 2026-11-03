<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Nam Naadu Naturals and Beverages') }}</title>
    <link rel="shortcut icon" href="{{url('img/fev.png')}}">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="{{url('assets/css/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />
	<link href="{{url('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{url('css/custom.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div class="row loginbg">
        <div class="col-lg-7 col-xl-7 col-md-7 col-sm-7 col-xs-12">

        </div>
        <div class="col-lg-5 col-xl-5 col-sm-5 col-md-5 col-xs-12 text-end long-width">
            <div class="row justify-content-end">
                <div class="login-content p-5 col-lg-10 bg-white rounded-3">

                    <h3 class="text-center mb-4 text-white">
                        <img src="{{ url('img/logo.png') }}" alt="" class="logo-header" width="250">
                    </h3>
                    <form method="POST" action="{{ route('login') }}" class="login-page">
                        @csrf
                        <div class="mb-4 mt-4 text-start">
                            <label for="emailaddress" class="form-label text-dark text-start">Username</label>
                            <div class="input-group input-group-merge text-start">
                                <input name="email" class="form-control login-form @error('email') is-invalid @enderror" type="email" id="emailaddress" value="{{ old('email') }}" required="" placeholder="Enter your email" autocomplete="email" autofocus>
                                <div class="input-group-text" data-password="false">
                                    <img src="{{url('img/user-icon.png')}}" width="">
                                </div>
                            </div>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 text-start">
                            <label for="password" class="form-label text-dark">Password</label>
                            <div class="input-group input-group-merge text-start">
                                <input name="password" type="password" id="password" class="form-control login-form @error('password') is-invalid @enderror" placeholder="Enter password" required="" autocomplete="current-password">
                                <div class="input-group-text" data-password="false">
                                    <img src="{{url('img/password-icon.png')}}" width="">
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <p class="text-end"> <a href="{{ route('password.request') }}" class="text-dark text-decoration-none ms-1">Forgot password?</a></p>
                        <div class="mb-3 d-grid text-center login-btn">
                            <button class="btn save-button text-white fw-bold login-form" type="submit"> LOGIN </button>
                        </div>
                        @if(session('error'))
                            <div class="alert alert-danger bg-danger text-light p-2" style="text-align: center;">
                                {{ session('error') }}
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        setTimeout(function(){
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
<script src="{{url('assets/js/app.min.js')}}"></script>
</html>
