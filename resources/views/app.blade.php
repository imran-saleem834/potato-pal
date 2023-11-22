<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ asset('css/font-awesome.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ asset('css/animate.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ asset('css/hover.css') }}" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>

    <script type="text/javascript" src="{{ asset('js/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/index.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/wow.js') }}"></script>
</html>
