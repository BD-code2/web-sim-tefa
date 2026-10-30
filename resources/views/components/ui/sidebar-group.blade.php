@props(['label'])

<div class="sim-sidebar-group">
    <h2 class="sim-sidebar-category">{{ $label }}</h2>
    <div class="sim-sidebar-group-items">
        {{ $slot }}
    </div>
</div>
