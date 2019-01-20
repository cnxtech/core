<!DOCTYPE html>
<html lang="{{ config('app.locale', config('app.fallback_locale', 'en')) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $codex['display_name'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcomponentsjs/1.2.0/custom-elements-es5-adapter.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcomponentsjs/1.2.0/webcomponents-loader.js"></script>

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
    <script src="/backend_data.js"></script>
    <script>
        if ( window.localStorage.getItem('test-polys') ) {
            delete window.fetch;
            delete Object.assign;
            delete String.prototype.startsWith;
        }
    </script>

    <link href="{{ asset('vendor/codex_core/css/vendor~core.chunk.css?71e2db1ca80b87318ba1') }}" rel="stylesheet">
    <link href="{{ asset('vendor/codex_core/css/core.css?35f1169523e0046d9ad3" rel="stylesheet') }} ">
    <script type="text/javascript" src="{{ asset('vendor/codex_site/js/chunk.vendor~core~phpdoc.page~polyfill.resize.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_phpdoc/js/chunk.vendor~core~phpdoc.page.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_phpdoc/js/chunk.vendor~core~phpdoc.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_site/js/chunk.vendor~core~polyfill.resize.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_core/js/chunk.vendor~core.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_phpdoc/js/chunk.core~phpdoc.page.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_core/js/core.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_phpdoc/js/phpdoc.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_site/js/chunk.vendor~site.js') }} "></script>
    <script type="text/javascript" src="{{ asset('vendor/codex_site/js/site.js') }} "></script>
</head>
<body>

<div id="root"></div>
<script>
    (function () {
        var codex = window['codex'];
        codex.site.loadPolyfills()
            .then(function () {
                return codex.site.loadApp();
            })
            .then(function (site) {
                // codex.phpdoc.install(site.app);

                site.app.register({
                    debug : true,
                    rootID: 'root',
                    api   : {
                        url: '/api' // http://codex.local
                    }
                });
                site.app.boot(site.App);
            });
    }).call();
</script>

</body>
</html>
