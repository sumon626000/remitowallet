<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif />
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="icon" type="image/x-icon" href="{{asset('assets/store/img/icon/fav.png')}}"/>
	<title> @yield('page_title') | {{ basicControl()->site_title }} </title>
    @include('layouts.store.styles')
</head>

<body class="">
@include('layouts.store.nav')
@include('layouts.store.banner')
@yield('content')
@include('layouts.store.footer')


@include('layouts.store.scripts')

@yield('scripts')

<x-notify />
</body>
</html>
