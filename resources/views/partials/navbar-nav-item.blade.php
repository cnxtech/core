@if(isset($item['children']) && count($item['children']) > 0)
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" id="navbar-nav-item-{{ $item['id'] }}">{{ $item['label'] }} <span class="caret"></span></a>
        <div class="dropdown-menu" aria-labelledby="navbar-nav-item-{{ $item['id'] }}">
            @foreach($item['children'] as $child)
                @include('codex::partials.navbar-nav-item', ['item' => $child, 'class' => 'dropdown_item'])
            @endforeach
        </div>
    </li>
@else
    <li class="nav-item">
        @if($item['document'])
            <a class="{{ isset($class) ? $class : 'nav-link' }}" href={{ $revision->url($item['document']) }}>{{ $item['label'] }}</a>
        @elseif($item['href'])
            <a class="{{ isset($class) ? $class : 'nav-link' }}" href={{ $item['href'] }}>{{ $item['label'] }}</a>
        @endif
    </li>

@endif
