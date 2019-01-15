{{--<toc-header title='{{$text}}' size='{{$size}}' slug='{{$slug}}' link-class='{{$header_link_class}}'>--}}
{{--<toc-header title='{{$text}}' size='{{$size}}' slug='{{$slug}}'>--}}
{{--{{$text}}--}}
{{--</toc-header>--}}


<h{{$size}} id="{{$slug}}"  class='c-toc-header'><span>{{$text}}</span>
@if (true === $header_link_show)
    <a href='#{{$slug}}' class='c-toc-header-link'>#</a>
    @else
    {{$link}}
    @endif
    </h{{$size}}>
