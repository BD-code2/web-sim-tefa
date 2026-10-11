<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIM SMKN 4 Tanjungpinang - Sistem Informasi Manajemen Sekolah</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-900 text-slate-100 min-h-screen">
    <!-- Hero Banner Full Sesuai Foto Gerbang SMKN 4 Tanjungpinang -->
    <div class="relative min-h-screen flex flex-col justify-between overflow-hidden">
        <!-- Background Image Gerbang SMKN 4 -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/smkn4-gerbang.png') }}" alt="Gerbang SMKN 4 Tanjungpinang" class="w-full h-full object-cover object-center brightness-[0.45] scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/60 to-blue-950/80"></div>
        </div>

        <!-- Top Navigation -->
        <header class="relative z-10 max-w-7xl mx-auto w-full px-6 py-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md p-1.5 border border-white/20 flex items-center justify-center">
                    <img src="{{ asset('images/logo-smkn4.png') }}" alt="Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-widest text-blue-300 font-semibold">Pemerintah Provinsi Kepulauan Riau</span>
                    <span class="font-bold text-lg md:text-xl text-white">SMKN 4 TANJUNGPINANG</span>
                </div>
            </div>

            <div>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition-all shadow-lg shadow-blue-600/30">
                        Buka Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition-all shadow-lg shadow-blue-600/30">
                        Masuk Sistem
                    </a>
                @endauth
            </div>
        </header>

        <!-- Hero Content Center -->
        <main class="relative z-10 max-w-5xl mx-auto px-6 py-16 text-center my-auto">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/20 border border-blue-400/30 backdrop-blur-sm text-blue-200 text-xs font-semibold mb-6">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                Portal SIM Sekolah Terpadu
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-white tracking-tight leading-tight mb-6">
                Sistem Informasi Manajemen Sekolah
            </h1>

            <p class="text-lg md:text-xl text-slate-200 max-w-3xl mx-auto mb-10 font-normal leading-relaxed">
                Platform terpadu untuk tata kelola persuratan digital, disposisi cepat Kepala Sekolah, manajemen kemitraan industri (MoU), penerbitan sertifikat PKL, dan agenda terintegrasi SMKN 4 Tanjungpinang.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold shadow-xl shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                        <span>Masuk ke Dashboard</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold shadow-xl shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                        <span>Login Pengguna</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                @endauth
            </div>

            <!-- 5 Modul Cards Preview -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-16 text-left">

                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="font-semibold text-white text-sm">Surat Masuk & Keluar</h3>
                    <p class="text-xs text-slate-300 mt-1">Nomor agenda otomatis & arsip digital.</p>
                </div>

                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="font-semibold text-white text-sm">Disposisi & Approval</h3>
                    <p class="text-xs text-slate-300 mt-1">Kepala Sekolah menginstruksikan via sistem.</p>
                </div>

                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h3 class="font-semibold text-white text-sm">Kerja Sama & Sertifikat</h3>
                    <p class="text-xs text-slate-300 mt-1">Manajemen mitra DUDI & sertifikasi PKL.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 max-w-7xl mx-auto w-full px-6 py-6 border-t border-white/10 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} SMKN 4 Tanjungpinang. Dinas Pendidikan Pemerintah Provinsi Kepulauan Riau.
        </footer>
    </div>
</body>
</html>
