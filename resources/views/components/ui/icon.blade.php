@props(['name', 'size' => 'sm'])

@php
    // Satu gaya ikon untuk navigasi dan aksi pada seluruh halaman role.
    $paths = [
        'dashboard' => ['M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z'],
        'inbox' => ['M4 13V4h16v9M3 13h5l2 3h4l2-3h5v7H3z', 'M12 5v6m-3-3 3 3 3-3'],
        'outbox' => ['M4 13V4h16v9M3 13h5l2 3h4l2-3h5v7H3z', 'M12 11V5m-3 3 3-3 3 3'],
        'document' => ['M14 3H5v18h14V8zM14 3v5h5M8 12h8M8 16h6'],
        'clipboard' => ['M9 5H5v16h14V5h-4M9 3h6v4H9zM8 12h8M8 16h6'],
        'archive' => ['M3 3h18v5H3zM5 8v13h14V8M9 12h6'],
        'building' => ['M4 21V3h12v18M16 9h4v12M2 21h20M8 7h4M8 11h4M8 15h4M9 21v-3h2v3'],
        'certificate' => ['M14 3H5v18h7M14 3v5h5v4M8 8h2M8 12h3', 'M20 16a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM15 18.5V22l2-1 2 1v-3.5'],
        'users' => ['M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6 21v-2a6 6 0 0 1 12 0v2M18 4a3 3 0 0 1 0 6M20 14a5 5 0 0 1 2 4v3M6 4a3 3 0 0 0 0 6M4 14a5 5 0 0 0-2 4v3'],
        'user' => ['M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM4 21v-2a8 8 0 0 1 16 0v2'],
        'bell' => ['M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4'],
        'menu' => ['M4 6h16M4 12h16M4 18h16'],
        'logout' => ['M9 4H4v16h5M14 8l4 4-4 4M8 12h12'],
        'plus' => ['M12 5v14M5 12h14'],
        'eye' => ['M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z', 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z'],
        'pencil' => ['m16 3 5 5L8 21H3v-5zM13 6l5 5'],
        'trash' => ['M3 6h18M5 6l1 15h12l1-15M9 6V3h6v3M10 10v7M14 10v7'],
        'download' => ['M12 3v12m-4-4 4 4 4-4M4 16v5h16v-5'],
        'printer' => ['M6 9V3h12v6M6 17H3V9h18v8h-3M6 14h12v7H6zM17 12h.01'],
        'check' => ['m5 12 4 4L19 6'],
        'close' => ['m6 6 12 12M6 18 18 6'],
        'revision' => ['M9 4H4v5M4 4l5 5M4 12a8 8 0 1 0 8-8'],
        'send' => ['m22 2-7 20-4-9-9-4zM22 2 11 13'],
        'save' => ['M19 21H3V3h14l4 4v14zM7 3v6h10V3M7 21v-8h10v8'],
        'search' => ['M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0zM15 15l6 6'],
        'filter' => ['M3 4h18l-7 8v7l-4 2v-9z'],
        'reset' => ['M3 10V4m0 6h6M3 10a9 9 0 1 1 2 8'],
        'back' => ['m12 5-7 7 7 7M5 12h15'],
        'upload' => ['M12 16V4m-4 4 4-4 4 4M4 16v5h16v-5'],
        'process' => ['m9 5 11 7-11 7zM4 5v14'],
        'check-circle' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0zM8 12l3 3 5-6'],
        'alert-circle' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0zM12 8v5M12 16h.01'],
        'warning' => ['m12 3 10 18H2zM12 9v4M12 17h.01'],
        'reply' => ['m9 4-7 7 7 7M2 11h10a8 8 0 0 1 8 8'],
        'folder-open' => ['M3 8V4h6l2 3h10v5M3 8v12h16l3-8H7l-4 8'],
    ];
    $sizeClass = match ($size) {
        'md' => 'h-5 w-5',
        'lg' => 'h-6 w-6',
        default => 'h-4 w-4',
    };
@endphp

<svg {{ $attributes->class(['ui-icon shrink-0', $sizeClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" data-icon="{{ $name }}">
    @foreach($paths[$name] ?? $paths['document'] as $path)
        <path d="{{ $path }}" />
    @endforeach
</svg>
