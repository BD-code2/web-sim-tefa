@props(['label'])

<div class="sim-sidebar-group" x-data="{ expanded: false }" x-id="['sidebar-group']">
    <button type="button" class="sim-sidebar-category" @click="expanded = !expanded" :aria-expanded="expanded" :aria-controls="$id('sidebar-group')">
        <span>{{ $label }}</span>
        <span class="sim-sidebar-chevron-badge" aria-hidden="true">
            <svg class="sim-sidebar-chevron" :class="{ 'is-expanded': expanded }" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 5 7 7-7 7" />
            </svg>
        </span>
    </button>
    <div class="sim-sidebar-group-items" :id="$id('sidebar-group')" x-cloak x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1">
        {{ $slot }}
    </div>
</div>
