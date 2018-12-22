<li>
    @if(isset($item['children']) && count($item['children']) > 0)
        <a class="has-arrow" href="#" aria-expanded="false">{{ $item['label'] }}</a>
        <ul>
            @each('codex::partials.metismenu-item', $item['children'], 'item')
        </ul>
    @else
        <a href={{ $item['document'] ? $revision->url($item['document']) : $item['href'] }}>{{ $item['label'] }}</a>
    @endif
</li>
