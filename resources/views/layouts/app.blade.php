<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.laravel = { csrfToken: '{{ csrf_token() }}' }</script>
        
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    {{-- TEMPOLARY : TODO - FIX Dropdown Logout does not schow --}}
    @if(Request::url() !== env('APP_URL').'users')
        <script src="{{ asset('js/app.js') }}" defer></script>
    @endif
    {{-- <script src="{{ asset('js/app.js') }}" defer></script> --}}
    
    {{-- show error in browser dev tool --}}
    {{-- <script src="{{ asset('js/bootstrap.js') }}" defer></script> --}}
    {{-- <script src="{{ asset('js/jquery.js') }}" defer></script> --}}

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/toggle-switch.css') }}" rel='stylesheet' type='text/css'>
    <link href="{{ asset('css/checkbox.css') }}" rel='stylesheet' type='text/css'>
    <link href="{{ asset('css/custom.css') }}" rel='stylesheet' type='text/css'>

    {{-- TODO : USE cdn.datatables.net --}}
    <!-- jQuery -->
    {{-- <script src="//code.jquery.com/jquery.js"></script> --}}
    {{-- <script src="{{asset('js/jquery.js')}}"></script> --}}
    <!-- DataTables -->
    {{-- <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script> --}}
    <!-- Bootstrap JavaScript -->
    {{-- <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script> --}}
    <!-- App scripts -->
    
    @stack('scripts')
</head>
<body>
    <div id="app">
        @include("inc.navbar")
        <div class="container">
            @include('inc.messages')
            @yield('content')
        </div>
    </div>

    {{-- <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script> --}}
    {{-- <script src="{{ asset('js/jquery.js') }}" defer></script> --}}
    
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        // CKEDITOR.replace( 'article-ckeditor' );     //working with pre-tag problem when save html to db 
    </script>
</body>
</html>
