@props(['icon', 'label', 'href' => null, 'tone' => 'neutral', 'type' => 'button'])

@php
    $toneClass = match ($tone) {
        'info' => 'ui-action-info',
        'success' => 'ui-action-success',
        'warning' => 'ui-action-warning',
        'danger' => 'ui-action-danger',
        default => 'ui-action-neutral',
    };
    $defaults = ['title' => $label, 'aria-label' => $label];
    if ($attributes->get('target') === '_blank') {
        $defaults['rel'] = 'noopener';
    }
@endphp

@if($href)
    <a {{ $attributes->class(['ui-action ui-action-icon', $toneClass])->merge($defaults + ['href' => $href]) }}>
        <x-ui.icon :name="$icon" size="md" />
    </a>
@else
    <button {{ $attributes->class(['ui-action ui-action-icon', $toneClass])->merge($defaults + ['type' => $type]) }}>
        <x-ui.icon :name="$icon" size="md" />
    </button>
@endif
