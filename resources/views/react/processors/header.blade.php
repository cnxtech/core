@php
    /** @var \Codex\Contracts\Documents\Document $document */
@endphp

<header>
    @if(isset($document['subtitle']) && strlen($document['subtitle']) > 0)
        <small>{{ $document['subtitle'] }}</small>
    @endif
    <h1>{{ $document['title'] }}</h1>
</header>
