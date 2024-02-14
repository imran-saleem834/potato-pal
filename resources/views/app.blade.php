@php($page['url'] = App::make('request')->getRequestUri())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Potato Pal">
        {{--<meta name="apple-touch-startup-image" content="splash.png">--}}
        <meta name="apple-touch-fullscreen" content="yes">
        
        <meta name="theme-color" content="#FFFFFF">
        <link rel="icon" href="{{ asset('images/favicon.ico') }}" sizes="48x48">
        
        {{--<link rel="icon" href="/favicon.svg" sizes="any" type="image/svg+xml">--}}
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon-180x180.png') }}">
        
        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}" />
        <link rel="prefetch" href="{{ asset('build/manifest.webmanifest') }}" />
        <script src="{{ asset('build/registerSW.js') }}"></script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>

    <script type="text/javascript" src="https://cdn.pubnub.com/sdk/javascript/pubnub.7.4.5.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places" defer></script>
</html>
