<!DOCTYPE html>
<html lang="{{ config('app.locale', config('app.fallback_locale', 'en')) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $codex['display_name'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <style>
        html,
        body {
            margin                 : 0;
            padding                : 0;
            image-rendering        : optimizeSpeed; /* Older versions of FF */
            image-rendering        : -moz-crisp-edges; /* FF 6.0+ */
            image-rendering        : -webkit-optimize-contrast; /* Webkit (non standard naming) */
            image-rendering        : -o-crisp-edges; /* OS X & Windows Opera (12.02+) */
            image-rendering        : crisp-edges; /* Possible future browsers. */
            -ms-interpolation-mode : nearest-neighbor; /* IE (non standard naming) */
            image-rendering        : pixelated; /* Chrome 41 */
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcomponentsjs/2.2.1/custom-elements-es5-adapter.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcomponentsjs/2.2.1/webcomponents-loader.js"></script>
    <script src="{{ asset('backend_data.js') }}"></script>

    <link rel="shortcut icon" href="{{ asset('vendor/codex_core/img/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vendor/codex_core/img/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vendor/codex_core/img/favicon-32x32.png') }}">
    <link rel="manifest" href="{{ asset('vendor/codex_core/img/manifest.json') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#fff">
    <meta name="application-name" content="@codex/app">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('vendor/codex_core/img/apple-touch-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('vendor/codex_core/img/apple-touch-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('vendor/codex_core/img/apple-touch-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('vendor/codex_core/img/apple-touch-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('vendor/codex_core/img/apple-touch-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('vendor/codex_core/img/apple-touch-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('vendor/codex_core/img/apple-touch-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('vendor/codex_core/img/apple-touch-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="167x167" href="{{ asset('vendor/codex_core/img/apple-touch-icon-167x167.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vendor/codex_core/img/apple-touch-icon-180x180.png') }}">
    <link rel="apple-touch-icon" sizes="1024x1024" href="{{ asset('vendor/codex_core/img/apple-touch-icon-1024x1024.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="@codex/app">
    <link rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 1)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-320x460.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-640x920.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-640x1096.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-750x1294.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 736px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 3)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-1182x2208.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 736px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 3)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-1242x2148.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 1)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-748x1024.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 1)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-768x1004.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 2)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-1496x2048.png') }}">
    <link rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 2)" href="{{ asset('vendor/codex_core/img/apple-touch-startup-image-1536x2008.png') }}">
    <link rel="icon" type="image/png" sizes="228x228" href="{{ asset('vendor/codex_core/img/coast-228x228.png') }}">
    <meta name="msapplication-TileColor" content="#fff">
    <meta name="msapplication-TileImage" content="{{ asset('vendor/codex_core/img/mstile-144x144.png') }}">
    <meta name="msapplication-config" content="{{ asset('vendor/codex_core/img/browserconfig.xml') }}">
    <link rel="yandex-tableau-widget" href="{{ asset('vendor/codex_core/img/yandex-browser-manifest.json') }}">
    <link href="{{ asset('vendor/codex_core/css/core.css?571103a3e8466ae941a7') }}" rel="stylesheet">
    <script type="text/javascript" src="{{ asset('vendor/codex_core/js/core.js') }}"></script>

    {{--<link href="{{ asset('vendor/codex_phpdoc/css/phpdoc.css?571103a3e8466ae941a7') }}" rel="stylesheet">--}}
    <script type="text/javascript" src="{{ asset('vendor/codex_phpdoc/js/phpdoc.js') }}"></script>


    {{--<script type="text/javascript" src="{{ asset('vendor/codex_auth/js/auth.js') }}"></script>--}}
</head>
<body>

<div id="root"></div>


<script>
    (function () {
        var codex = window['codex'];
        codex.core.loadPolyfills().then(function () {
            var app = codex.core.app;
            app.plugin(new codex.phpdoc.default());
            return app.register({
                debug : app.store.config.debug,
                cache : app.store.codex.cache.enabled,
                rootID: 'root',
                api   : {
                    url: app.url.api()
                }
            });
        }).then(function (app) {
            return app.boot();
        }).then(function (app) {
            console.log('booted');
        });
    }).call();
</script>

</body>
</html>
