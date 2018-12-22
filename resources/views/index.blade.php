<!DOCTYPE html>
<html lang="{{ config('app.locale', config('app.fallback_locale', 'en')) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $codex['display_name'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link href="https://bootswatch.com/4/{{ data_get($__env,'theme', 'superhero')  }}/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="https://unpkg.com/metismenu/dist/metisMenu.min.css" rel="stylesheet"/>
    <link href="https://mm.onokumus.com/assets/css/mm-horizontal.css" rel="stylesheet"/>
    <style type="text/css">
        #header .navbar-brand {
            float: left;
            margin: 5px 20px;
        }
    </style>
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

<div class="container">

    {!! $content !!}

</div>

<script src="https://bootswatch.com/_vendor/jquery/dist/jquery.min.js"></script>
<script src="https://bootswatch.com/_vendor/popper.js/dist/umd/popper.min.js"></script>
<script src="https://bootswatch.com/_vendor/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="https://unpkg.com/metismenu"></script>

<script>
    $(function () {
        $('#header-menu').metisMenu();
        $(window).scroll(function () {
            var top = $(document).scrollTop();
            if ( top > 50 )
                $('#home > .navbar').removeClass('navbar-transparent');
            else
                $('#home > .navbar').addClass('navbar-transparent');
        });

        $('a[href=\'#\']').click(function (e) {
            e.preventDefault();
        });

        var $button = $('<div id=\'source-button\' class=\'btn btn-primary btn-xs\'>&lt; &gt;</div>').click(function () {
            var html = $(this).parent().html();
            html = cleanSource(html);
            $('#source-modal pre').text(html);
            $('#source-modal').modal();
        });

        $('.bs-component [data-toggle="popover"]').popover();
        $('.bs-component [data-toggle="tooltip"]').tooltip();

        $('.bs-component').hover(function () {
            $(this).append($button);
            $button.show();
        }, function () {
            $button.hide();
        });

        function cleanSource(html) {
            html = html.replace(/×/g, '&times;')
                .replace(/«/g, '&laquo;')
                .replace(/»/g, '&raquo;')
                .replace(/←/g, '&larr;')
                .replace(/→/g, '&rarr;');

            var lines = html.split(/\n/);

            lines.shift();
            lines.splice(- 1, 1);

            var indentSize = lines[0].length - lines[0].trim().length,
                re         = new RegExp(' {' + indentSize + '}');

            lines = lines.map(function (line) {
                if ( line.match(re) ) {
                    line = line.substring(indentSize);
                }

                return line;
            });

            lines = lines.join('\n');

            return lines;
        }
    });
</script>
</body>
</html>
