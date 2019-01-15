{{--<toc-list class="{{$list_class}}">--}}
<c-toc>
    @include('codex::react.processors.toc-list', [ 'items' => $items, 'view' => 'codex::react.processors.toc-list' ])
</c-toc>
