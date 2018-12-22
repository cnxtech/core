import { EventEmitter } from 'eventemitter3';
import { IPlatform } from '../interfaces';
import Platform from '../utils/platform';


export class Application extends EventEmitter {
    platform: IPlatform = Platform

    constructor() {
        super();
    }

    start() {
        let self  = this,
            $win  = $(window),
            $doc  = $(document),
            $body = $(document.body);

        $(() => {
            let $header     = $('#header'),
                $headerMenu = $('#header-menu'),
                $navbar     = $('#home > .navbar');

            $headerMenu.metisMenu();
            $('#content table').addClass('table table-sm table-bordered table-hover');
            $win.on('scroll', () => $navbar[ $doc.scrollTop() > 50 ? 'removeClass' : 'addClass' ]('navbar-transparent'));
            $('a[href=\'#\']').on('click', e => e.preventDefault());

            let $button = $('<div id=\'source-button\' class=\'btn btn-primary btn-xs\'>&lt; &gt;</div>').click(function () {
                $('#source-modal pre').text(self.cleanSource($(this).parent().html()));
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
        })
    }

    protected cleanSource(html) {
        html = html.replace(/×/g, '&times;')
            .replace(/«/g, '&laquo;')
            .replace(/»/g, '&raquo;')
            .replace(/←/g, '&larr;')
            .replace(/→/g, '&rarr;');

        let lines = html.split(/\n/);
        lines.shift();
        lines.splice(- 1, 1);

        const indentSize = lines[ 0 ].length - lines[ 0 ].trim().length,
              re         = new RegExp(' {' + indentSize + '}');

        return lines.map(line => line.match(re) ? line.substring(indentSize) : line).join('\n');
    }
}

export const app: Application = new Application();
