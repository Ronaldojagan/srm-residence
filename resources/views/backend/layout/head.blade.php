<meta charset="utf-8" />
<title>{{ config('app.name', 'SRM RESIDENCE') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<link rel="shortcut icon" href="{{url('img/fev.png')}}">
<link rel="apple-touch-icon" href="{{url('img/icon-192.png')}}">
<link rel="manifest" href="{{url('manifest.json')}}">
<meta name="theme-color" content="#0ea1b1">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="SRM Residence">

<!-- PWA Service Worker Registration -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('{{ url("sw.js") }}').catch(() => {});
        });
    }
</script>

<!-- Google Fonts: Plus Jakarta Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- FontAwesome 6 Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

<link href="{{url('assets/css/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />
<link href="{{url('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />

@stack('head_content')

<link href="{{url('css/custom.css')}}" rel="stylesheet" type="text/css" />
<!-- Modern Website Overlay -->
<link href="{{url('css/modern-overlay.css')}}" rel="stylesheet" type="text/css" />
