<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover"
        />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta
            name="apple-mobile-web-app-status-bar-style"
            content="black-translucent"
        />
        <meta name="theme-color" content="#000000" />
        <title>Stock Opname</title>
        <meta name="description" content="Finapp HTML Mobile Template" />
        <meta
            name="keywords"
            content="bootstrap, wallet, banking, fintech mobile template, cordova, phonegap, mobile, html, responsive"
        />
        <link
            rel="icon"
            type="image/png"
            href="{{ asset('assets/img/favicon.png') }}"
            sizes="32x32"
        />
        <link
            rel="apple-touch-icon"
            sizes="180x180"
            href="{{ asset('assets/img/icon/192x192.png') }}"
        />
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
        <link rel="manifest" href="__manifest.json" />
    </head>

    <body>
        <!-- App Header -->
        @include('layouts.header')
        <!-- * App Header -->

        <!-- App Capsule -->
        <div id="appCapsule">@yield('content')</div>
        <!-- * App Capsule -->
        <!-- App Bottom Menu -->
        @include('layouts.footer')

        <!-- * App Bottom Menu -->

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <!-- ========= JS Files =========  -->
        <!-- Bootstrap -->

        <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
        <!-- Ionicons -->
        <script
            type="module"
            src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"
        ></script>
        <!-- Splide -->
        <script src="{{ asset('assets/js/plugins/splide/splide.min.js') }}"></script>
        <!-- Base Js File -->
        <script src="{{ asset('assets/js/base.js') }}"></script>
        @yield('script')
    </body>
</html>
