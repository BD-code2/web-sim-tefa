@props(['label'])

<details class="sim-sidebar-group">
    <summary class="sim-sidebar-category">
        <span>{{ $label }}</span>
        <svg class="sim-sidebar-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m9 5 7 7-7 7" />
        </svg>
    </summary>
    <div class="sim-sidebar-group-items">
        {{ $slot }}
    </div>
</details>
