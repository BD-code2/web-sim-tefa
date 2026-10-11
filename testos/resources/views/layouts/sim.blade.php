<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIM SMKN 4 Tanjungpinang') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar Kiri Sesuai Mockup Biru Tua (#0B4DB7 / #0F4C81) -->
        <aside class="w-64 bg-[#0A4DBA] text-white flex flex-col fixed inset-y-0 z-30 transition-transform duration-300 md:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            
            <!-- Logo Brand Header -->
            <div class="h-20 flex items-center px-6 gap-3 border-b border-blue-600/50">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-md overflow-hidden p-1">
                    <img src="{{ asset('images/logo-smkn4.png') }}" alt="Logo SMKN 4 Tanjungpinang" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight tracking-wide">SIM SEKOLAH</h1>
                    <p class="text-xs text-blue-200">SMKN 4 Tanjungpinang</p>
                </div>
            </div>

<!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <!-- Dashboard - ALL ROLES -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white font-semibold shadow-sm' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="dashboard" size="md" />
                    Dashboard
                </a>

                <!-- Surat Masuk dikelola TU -->
                @if(auth()->user()->isTU())
                <a href="{{ route('surat-masuk.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('surat-masuk.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="inbox" size="md" />
                    Surat Masuk
                </a>
                @endif

                @if(auth()->user()->isTU())
                <!-- Akses surat keluar tersedia di menu tunggal di bawah. -->
                @endif

                <!-- Surat Keluar: undangan dan surat umum -->
                @if(auth()->user()->isTU())
                <a href="{{ route('surat-keluar.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('surat-keluar.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="outbox" size="md" />
                    Surat Keluar
                </a>
                @endif

                <!-- Surat Dinas: SK dan SPT -->
                @if(auth()->user()->isTU())
                <a href="{{ route('surat-dinas.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('surat-dinas.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="document" size="md" />
                    Surat Dinas
                </a>
                @endif

                @if(auth()->user()->isTU())
                <a href="{{ route('laporan-surat.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('laporan-surat.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="printer" size="md" />
                    Laporan Surat
                </a>
                @endif

                <!-- Pemantauan Disposisi untuk TU, Kepala Sekolah, dan penerima disposisi -->
                @if(auth()->user()->hasRole(['kepala_sekolah', 'tu', 'humas', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas', 'pembina_osis', 'bendahara_bos', 'bendahara_spp']))
                <a href="{{ route('disposisi.antrian') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('disposisi.antrian') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="clipboard" size="md" />
                    Pemantauan Disposisi
                </a>
                <a href="{{ route('disposisi.arsip') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('disposisi.arsip') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="archive" size="md" />
                    Arsip Disposisi
                </a>
                @endif

                <!-- Kerja Sama Industri dikelola TU dan Humas -->
                @if(auth()->user()->hasRole(['tu', 'humas', 'waka_humas']))
                <a href="{{ route('kerja-sama.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('kerja-sama.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="building" size="md" />
                    Kerja Sama Industri
                </a>
                @endif

                <!-- Sertifikat dan PKL -->
                @if(auth()->user()->hasRole(['tu', 'humas', 'waka_humas']))
                <a href="{{ route('data-siswa.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('data-siswa.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="certificate" size="md" />
                    Sertifikat dan PKL
                </a>
                @endif

                <!-- Pengaturan akun dan hak akses dikelola TU -->
                @if(auth()->user()->isTU())
                <div class="pt-4 pb-1">
                    <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Administrasi</p>
                </div>
                <a href="{{ route('pengaturan.users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('pengaturan.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon name="users" size="md" />
                    Kelola Pengguna
                </a>
                @endif
            </nav>

            <!-- Bottom User / Logout -->
            <div class="p-4 border-t border-blue-600/50">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-blue-100 hover:bg-rose-600/80 hover:text-white transition-colors">
                        <x-ui.icon name="logout" size="md" />
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Wrapper Konten Kanan -->
        <div class="flex-1 flex flex-col md:pl-64">
            <!-- Top Navbar -->
            <header class="h-20 bg-white border-b border-slate-200/80 sticky top-0 z-20 flex items-center justify-between px-6 lg:px-10">
                <!-- Mobile toggle & Judul Halaman -->
                <div class="flex items-center gap-4">
                    <button type="button" aria-label="Buka atau tutup menu" :aria-expanded="sidebarOpen" @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 md:hidden">
                        <x-ui.icon name="menu" size="lg" />
                    </button>
                    <h2 class="text-xl font-bold text-slate-800">@yield('page-title', 'Dashboard')</h2>
                </div>

                <!-- Profil pengguna di sisi kanan header -->
                <div class="flex items-center gap-5">
                    <!-- User Profile Dropdown Badge -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shadow-sm">
                            <x-ui.icon name="user" size="md" />
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-semibold text-slate-800 leading-snug">{{ auth()->user()->role_label }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Body -->
            <main class="flex-1 p-6 lg:p-10 max-w-7xl w-full mx-auto">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
                        <x-ui.icon name="check-circle" size="md" class="text-emerald-500" />
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3">
                        <x-ui.icon name="alert-circle" size="md" class="text-rose-500" />
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center gap-3">
                        <x-ui.icon name="warning" size="md" class="text-amber-500" />
                        <span class="text-sm font-medium">{{ session('warning') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
