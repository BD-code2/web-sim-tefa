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
<body class="font-sans antialiased bg-slate-950 text-slate-100" style="background-image: linear-gradient(to bottom, rgb(2 6 23 / 90%), rgb(15 23 42 / 85%), rgb(2 6 23 / 95%)), url('{{ asset('images/smkn4-gerbang.png') }}'); background-size: cover; background-position: center; background-attachment: fixed;">
    <section class="flex min-h-[68vh] flex-col">
        <header class="mx-auto flex w-full max-w-7xl items-center gap-4 px-6 py-6 md:px-10">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/10 p-1.5 backdrop-blur">
                <img src="{{ asset('images/logo-smkn4.png') }}" alt="Logo SMKN 4" class="h-full w-full object-contain">
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-200">Pemerintah Provinsi Kepulauan Riau</span>
                <span class="text-base font-bold tracking-wide text-white md:text-lg">SMKN 4 TANJUNGPINANG</span>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-6xl flex-1 flex-col justify-center px-6 pb-14 pt-8 text-center md:px-10 md:pb-16">
            <div class="mx-auto inline-flex items-center gap-2 rounded-full border border-blue-300/25 bg-blue-400/10 px-4 py-2 text-xs font-semibold text-blue-100 backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-blue-300"></span>
                Portal SIM Sekolah Terpadu
            </div>
            <h1 class="mx-auto mt-7 max-w-4xl text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl md:text-6xl">
                Administrasi sekolah dalam satu sistem
            </h1>
            <p class="mx-auto mt-6 max-w-3xl text-base leading-7 text-slate-200 md:text-lg">
                Kelola persuratan, disposisi, agenda, kemitraan industri, data siswa, dan sertifikat PKL secara terintegrasi di SMKN 4 Tanjungpinang.
            </p>
            <div class="mt-9">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-500">
                        Buka Dashboard <span aria-hidden="true">→</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-500">
                        Login Pengguna <span aria-hidden="true">→</span>
                    </a>
                @endauth
            </div>
        </main>
    </section>

    <section class="px-6 pb-20 pt-8 md:px-10 md:pb-24 md:pt-12">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto mb-10 max-w-2xl text-center md:mb-12">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">Fitur Sistem</p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white md:text-4xl">Layanan administrasi sekolah</h2>
                <p class="mt-3 text-sm leading-6 text-slate-300 md:text-base">Berbagai modul untuk mendukung pengelolaan administrasi dan kegiatan sekolah.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-6">
                <article class="rounded-2xl border border-white/15 bg-white/10 p-6 shadow-sm backdrop-blur-md transition hover:-translate-y-1 hover:bg-white/15 hover:shadow-md lg:col-span-2">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-400/15 text-lg font-bold text-emerald-300">01</div>
                    <h3 class="mt-5 text-base font-bold text-white">Surat Masuk & Keluar</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Pencatatan, nomor agenda otomatis, alur persetujuan, dan arsip surat sekolah.</p>
                </article>
                <article class="rounded-2xl border border-white/15 bg-white/10 p-6 shadow-sm backdrop-blur-md transition hover:-translate-y-1 hover:bg-white/15 hover:shadow-md lg:col-span-2">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-400/15 text-lg font-bold text-violet-300">02</div>
                    <h3 class="mt-5 text-base font-bold text-white">Surat Dinas</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Pembuatan Surat Keputusan (SK) dan Surat Perintah Tugas (SPT) dengan alur persetujuan.</p>
                </article>
                <article class="rounded-2xl border border-white/15 bg-white/10 p-6 shadow-sm backdrop-blur-md transition hover:-translate-y-1 hover:bg-white/15 hover:shadow-md lg:col-span-2">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-400/15 text-lg font-bold text-amber-300">03</div>
                    <h3 class="mt-5 text-base font-bold text-white">Disposisi</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Teruskan surat, berikan instruksi, dan pantau tindak lanjut oleh penerima.</p>
                </article>
                <article class="rounded-2xl border border-white/15 bg-white/10 p-6 shadow-sm backdrop-blur-md transition hover:-translate-y-1 hover:bg-white/15 hover:shadow-md lg:col-span-2 lg:col-start-2">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/15 text-lg font-bold text-cyan-300">04</div>
                    <h3 class="mt-5 text-base font-bold text-white">Kerja Sama Industri</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Kelola data mitra industri, periode kemitraan, dan agenda kerja sama sekolah.</p>
                </article>
                <article class="rounded-2xl border border-white/15 bg-white/10 p-6 shadow-sm backdrop-blur-md transition hover:-translate-y-1 hover:bg-white/15 hover:shadow-md lg:col-span-2 lg:col-start-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-400/15 text-lg font-bold text-indigo-300">05</div>
                    <h3 class="mt-5 text-base font-bold text-white">PKL & Sertifikat</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Kelola data siswa PKL, pantau progres, dan terbitkan sertifikat.</p>
                </article>
            </div>
        </div>
    </section>

    <footer class="border-t border-white/10 px-6 py-6 text-center text-xs text-slate-400 md:px-10">
        &copy; {{ date('Y') }} SMKN 4 Tanjungpinang. Dinas Pendidikan Pemerintah Provinsi Kepulauan Riau.
    </footer>
</body>
</html>
