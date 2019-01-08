<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Welcome') - Laravel Shop</title>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    </head>
    <body>
        <div class="{{ route_class() }}-page" id="app">
            @include('layouts._header')
            <div class="container">
                @yield('content')
            </div>
            @include('layouts._footer')
        </div>
        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}"></script>
        @yield('scripts')
    </body>
</html>
