{{--<toc-list class="{{$list_class}}">--}}
<c-toc-list>
    @foreach($items as $item)
        <c-toc-list-item href="#{{ $item->getValue()->getSlug()  }}" title="{{ $item->getValue()->getText() }}">
{{--            <a >{{ $item->getValue()->getText() }}</a>--}}
            @if($item->hasChildren())
                @include($view, [ 'items' => $item->getChildren(), 'view' => $view ])
            @endif
        </c-toc-list-item>
    @endforeach
</c-toc-list>
