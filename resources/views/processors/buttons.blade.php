@php
    /** @var \Codex\Project $project */
    /** @var \Codex\Revision $revision */
    /** @var \Codex\Document $document */
    /** @var \Codex\Support\Theme $theme */
    /** @var \Codex\Processors\Buttons\Button[] $buttons */
@endphp
<button-group class="pull-right">
    @foreach($buttons as $button)
        {!! $button->mergeAttributes([])->render() !!}
    @endforeach
</button-group>
