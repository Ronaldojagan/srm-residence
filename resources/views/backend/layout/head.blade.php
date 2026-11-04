<meta charset="utf-8" />
<title>{{ config('app.name', 'SRM RESIDENCE') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<link rel="shortcut icon" href="{{url('img/fev.png')}}">
@stack('head_content')
<link href="{{url('assets/css/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />
<link href="{{url('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{url('css/custom.css')}}" rel="stylesheet" type="text/css" />
