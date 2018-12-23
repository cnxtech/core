{{--<toc-list class="{{$list_class}}">--}}
<ul>
    @foreach($items as $item)
        <li>
            <a href="#{{ $item->getValue()->getSlug()  }}" title="{{ $item->getValue()->getText() }}">{{ $item->getValue()->getText() }}</a>
            @if($item->hasChildren())
                @include($view, [ 'items' => $item->getChildren(), 'view' => $view ])
            @endif
        </li>
    @endforeach
</ul>
