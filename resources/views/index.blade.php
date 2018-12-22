<!DOCTYPE html>
<html lang="{{ config('app.locale', config('app.fallback_locale', 'en')) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $codex['display_name'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcomponentsjs/1.2.0/custom-elements-es5-adapter.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcomponentsjs/1.2.0/webcomponents-loader.js"></script>

</head>
<body>

<div class="fixed-top bg-dark" id="header">
    <div class="container">
        <a href="../" class="navbar-brand">{{ $codex['display_name'] }}</a>
        <nav class="topbar-nav">
            <ul class="metismenu" id="header-menu">
                @each('codex::partials.metismenu-item', $document['layout.left.menu'], 'item')
            </ul>
        </nav>
    </div>
</div>

<div class="container" id="content">

    {!! $content !!}

</div>

<script src="{{ asset('vendor/codex/js/runtime.js') }}"></script>
<script src="{{ asset('vendor/codex/js/chunk.main.js') }}"></script>

<script>
    app.start();
</script>
</body>
</html>
