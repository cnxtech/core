/*
 * Copyright (c) 2018. Codex Project
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author Robin Radic
 * @license https://codex-project.mit-license.org MIT License
 */

import * as Prism from 'prismjs'
// import Clipboard from 'clipboard'
import 'prismjs/plugins/autoloader/prism-autoloader.js'
import 'prismjs/plugins/toolbar/prism-toolbar.js'
import 'prismjs/plugins/line-numbers/prism-line-numbers.js'
import 'prismjs/plugins/show-language/prism-show-language.js'
import 'prismjs/plugins/wpd/prism-wpd.js'
// import 'prismjs/plugins/command-line/prism-command-line.js'
import 'prismjs/plugins/autolinker/prism-autolinker.js'
import 'prismjs/plugins/normalize-whitespace/prism-normalize-whitespace.js'
import 'prismjs/plugins/line-highlight/prism-line-highlight.js'
import 'prismjs/components/prism-markup'
import 'prismjs/components/prism-markup-templating'

// if ( typeof window !== 'undefined' ) {
//     window[ 'Prism' ]     = window[ 'Prism' ] ? window[ 'Prism' ] : Prism;
//     window[ 'Clipboard' ] = window[ 'Clipboard' ] ? window[ 'Clipboard' ] : Clipboard;
// }

Prism.plugins.autoloader.languages_path = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.15.0/components/'
Prism.plugins.autoloader.use_minified = PROD
Prism.plugins.NormalizeWhitespace.setDefaults({
    'remove-trailing': true,
    'remove-indent'  : true,
    'left-trim'      : true,
    'right-trim'     : true
    /*'break-lines': 80,
    'indent': 2,
    'remove-initial-line-feed': false,
    'tabs-to-spaces': 4,
    'spaces-to-tabs': 4*/
});
if ( typeof window !== 'undefined' ) {
    // require('prismjs/plugins/copy-to-clipboard/prism-copy-to-clipboard.js');
}
export default Prism

/*
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/prism.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/autoloader/prism-autoloader.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/toolbar/prism-toolbar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/line-numbers/prism-line-numbers.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/show-language/prism-show-language.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/wpd/prism-wpd.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/command-line/prism-command-line.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/autolinker/prism-autolinker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/normalize-whitespace/prism-normalize-whitespace.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/line-highlight/prism-line-highlight.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/plugins/copy-to-clipboard/prism-copy-to-clipboard.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/themes/prism.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/themes/prism-solarizedlight.css"/>

<script>
Prism.plugins.autoloader.languages_path = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/components/';
Prism.plugins.NormalizeWhitespace.setDefaults({
    'remove-trailing': true,
    'remove-indent': true,
    'left-trim': true,
    'right-trim': true,
});
</script>
<style type="text/css">
code[class*="language-"],pre[class*="language-"]{-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-hyphens:none;-moz-hyphens:none;-ms-hyphens:none;hyphens:none;white-space:pre;white-space:pre-wrap;word-break:break-all;white-space:pre !important;line-height:17px;color:#ed7375;text-shadow:none;background:0;text-align:left;word-spacing:normal;overflow:auto;background:#f5f2f0}code[class*="language-"]::-moz-selection,pre[class*="language-"]::-moz-selection{text-shadow:none;text-shadow:none;background:#b3d4fc;background:#b3d4fc}code[class*="language-"]::selection,pre[class*="language-"]::selection{text-shadow:none;text-shadow:none;background:#b3d4fc;background:#b3d4fc}:not(pre)>code{color:#ed7375;font-size:12px;padding:1px 5px;border:1px solid #c5d0dc;background:#f5f2f0}pre[class*="language-"]{padding:6px;margin:.5em 0 1rem 0}pre[class*="language-"]:not(.no-border){border:1px solid #c5d0dc}table :not(pre)>code{padding:2px 5px;display:block}:not(pre)>code[class*="language-"]{background:#f5f2f0;padding:.1em;white-space:normal}.token.class-name{color:#ed7375 !important}.token.comment{color:#77b767 !important}.token.delimiter{color:#ed7375 !important}.token.prolog{color:#77b767 !important}.token.doctype{color:#77b767 !important}.token.cdata{color:#77b767 !important}.token.punctuation{color:#546e7a !important}.namespace{opacity:.7}.token.property{color:#90a4ae !important}.token.package{color:#6b4d4d !important}.token.tag{color:#77b767 !important}.token.tag-id{color:#77b767 !important}.token.boolean{color:#90a4ae !important}.token.number{color:#138cec !important}.token.constant{color:#90a4ae !important}.token.symbol{color:#90a4ae !important}.token.deleted{color:#90a4ae !important}.token.selector{color:#690 !important}.token.attr-name{color:#690 !important}.token.string{color:#690 !important}.token.char{color:#690 !important}.token.builtin{color:#690 !important}.token.inserted{color:#690 !important}.token.operator{color:#a67f59 !important}.token.entity{color:#a67f59 !important;background:rgba(255,255,255,0.5);cursor:help}.token.url{color:#a67f59 !important;background:rgba(255,255,255,0.5)}.language-css .token.string{color:#a67f59 !important;background:rgba(255,255,255,0.5)}.style .token.string{color:#a67f59;background:rgba(255,255,255,0.5)}.token.atrule{color:#07a !important}.token.attr-value{color:#07a !important}.token.keyword{color:#093246 !important}.token.function{color:#195471 !important}.token.regex{color:#e90 !important}.token.important{color:#e90;font-weight:bold}.token.variable{color:#78909c}.token.bold{font-weight:bold}.token.italic{font-style:italic}@media print{code[class*="language-"]{text-shadow:none}pre[class*="language-"]{text-shadow:none}}.token a{color:inherit}.command-line-prompt{border-right:1px solid #999;display:block;float:left;font-size:100%;letter-spacing:-1px;margin-right:1em;pointer-events:none;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.command-line-prompt>span:before{color:#999;content:' ';display:block;padding-right:.8em}.command-line-prompt>span[data-user]:before{content:"[" attr(data-user) "@" attr(data-host) "] $"}.command-line-prompt>span[data-user="root"]:before{content:"[" attr(data-user) "@" attr(data-host) "] #"}.command-line-prompt>span[data-prompt]:before{content:attr(data-prompt)}pre.line-numbers{position:relative;padding-left:3.8em;counter-reset:linenumber}pre.line-numbers>code{position:relative}.line-numbers .line-numbers-rows{position:absolute;pointer-events:none;top:0;font-size:100%;left:-3.8em;width:3em;letter-spacing:-1px;border-right:1px solid #999;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.line-numbers-rows>span{pointer-events:none;display:block;counter-increment:linenumber}.line-numbers-rows>span:before{content:counter(linenumber);color:#999;display:block;padding-right:.8em;text-align:right}div.prism-show-language{position:relative}div.prism-show-language>div.prism-show-language-label{color:black;background-color:#cfcfcf;display:inline-block;position:absolute;bottom:auto;left:auto;top:0;right:0;width:auto;height:auto;font-size:.9em;border-radius:0 0 0 5px;padding:0 .5em;text-shadow:none;z-index:1;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;-webkit-transform:none;-moz-transform:none;-ms-transform:none;-o-transform:none;transform:none}div.code-toolbar{position:relative}div.code-toolbar>.toolbar{position:absolute;top:.3em;right:.2em;transition:opacity .3s ease-in-out;opacity:0}div.code-toolbar:hover>.toolbar{opacity:1}div.code-toolbar>.toolbar .toolbar-item{display:inline-block}div.code-toolbar>.toolbar a{cursor:pointer}div.code-toolbar>.toolbar button{background:0;border:0;color:inherit;font:inherit;line-height:normal;overflow:visible;padding:0;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none}div.code-toolbar>.toolbar a,div.code-toolbar>.toolbar button,div.code-toolbar>.toolbar span{color:#bbb;font-size:.8em;padding:0 .5em;background:#f5f2f0;background:rgba(224,224,224,0.2);box-shadow:0 2px 0 0 rgba(0,0,0,0.2);border-radius:.5em}div.code-toolbar>.toolbar a:hover,div.code-toolbar>.toolbar a:focus,div.code-toolbar>.toolbar button:hover,div.code-toolbar>.toolbar button:focus,div.code-toolbar>.toolbar span:hover,div.code-toolbar>.toolbar span:focus{color:inherit;text-decoration:none}code[class*="language-"] a[href],pre[class*="language-"] a[href]{cursor:help;text-decoration:none}code[class*="language-"] a[href]:hover,pre[class*="language-"] a[href]:hover{cursor:help;text-decoration:underline}div.prism-show-language{background-color:transparent;position:relative;text-align:right;width:20%;left:80%}div.prism-show-language div.prism-show-language-label{color:#b0bec5;font-size:12px;padding:6px 10px 0 0;background-color:transparent}.prism-show-language+pre[class*="language-"]{margin-top:0}.line-numbers .line-numbers-rows>span:before{color:#ed7375;text-align:left;padding-left:.8em;white-space:nowrap}.line-numbers .line-numbers-rows>span.highlight{overflow:hidden}.line-numbers .line-numbers-rows>span.highlight:before{width:9000px;background:rgba(204,189,179,0.5)}div.code-toolbar>.toolbar{opacity:1}div.code-toolbar>.toolbar>.toolbar-item:first-child{border-left:0}div.code-toolbar>.toolbar a,div.code-toolbar>.toolbar button,div.code-toolbar>.toolbar span{color:#bbb;font-size:.8rem;padding:.2rem .7rem;background:white;box-shadow:0 2px 0 0 rgba(0,0,0,0.2);border-radius:0}div.code-toolbar>.toolbar a,div.code-toolbar>.toolbar button{transition:opacity .3s ease-in-out;opacity:0;border:1px solid rgba(0,0,0,0.2)}div.code-toolbar>.toolbar span{box-shadow:none}div.code-toolbar:hover>.toolbar{opacity:1}div.code-toolbar:hover>.toolbar a,div.code-toolbar:hover>.toolbar button{opacity:1}code .token,pre .token{background:0;border-radius:0;padding:0;font-size:100%}
</style>

*/
