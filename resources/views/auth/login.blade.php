<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SIM SMKN 4 Tanjungpinang</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sim-auth min-h-screen bg-blue-950 font-sans antialiased text-slate-800">
    <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-cover bg-center bg-no-repeat"
             style="background-image: url('{{ asset('images/login-wallpaper-3d.png') }}');"></div>

        <section class="relative w-full max-w-md">
            <div aria-hidden="true" class="absolute -inset-3 rounded-[2rem] border border-white/60 bg-gradient-to-br from-white/80 via-blue-50/60 to-blue-100/50 shadow-[0_30px_80px_-42px_rgba(30,64,175,0.3)]"></div>
            <div aria-hidden="true" class="absolute -inset-1.5 rounded-[1.7rem] border border-white/80"></div>

            <div class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_24px_70px_-30px_rgba(15,23,42,0.3)]">
                <div class="h-1.5 bg-gradient-to-r from-blue-950 via-blue-600 to-cyan-400"></div>
                <div class="px-6 py-8 sm:px-10 sm:py-10">
                    <div class="mb-7 text-center">
                        <div class="mx-auto flex h-[78px] w-[78px] items-center justify-center rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-2 shadow-sm shadow-blue-900/10">
                            <img src="{{ asset('images/logo-smkn4.png') }}" alt="Logo SMKN 4 Tanjungpinang" class="h-full w-full object-contain">
                        </div>
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.2em] text-blue-700">SMKN 4 TANJUNGPINANG</p>
                        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Selamat Datang</h1>
                        <p class="mt-2 text-sm text-slate-500">Masuk ke Sistem Informasi Manajemen Sekolah</p>
                    </div>

                    @if (session('error'))
                        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-700">
                            <ul class="list-disc space-y-1 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-700">Alamat Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                   placeholder="nama@smkn4.com">
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-xs font-semibold text-slate-700">Kata Sandi</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required autocomplete="current-password"
                                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-12 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                       placeholder="Masukkan kata sandi">
                                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-blue-700" aria-label="Tampilkan kata sandi" aria-pressed="false">
                                    <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.25 9.75-6.25S21.75 12 21.75 12 18.25 18.25 12 18.25 2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                                    <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.59a2 2 0 0 0 2.83 2.83M9.88 5.1A10.8 10.8 0 0 1 12 4.9c6.25 0 9.75 7.1 9.75 7.1a16.8 16.8 0 0 1-3.1 3.85M6.23 6.23C3.7 7.9 2.25 12 2.25 12s3.5 7.1 9.75 7.1c1.2 0 2.28-.25 3.25-.66"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1 text-xs">
                            <label class="flex cursor-pointer items-center gap-2 text-slate-600">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                                <span>Ingat saya</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="font-medium text-blue-700 transition hover:text-blue-900">Lupa password?</a>
                            @endif
                        </div>

                        <button type="submit" class="mt-2 w-full rounded-xl bg-gradient-to-r from-[#0A4DBA] to-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:-translate-y-0.5 hover:from-blue-800 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                            Masuk Sekarang
                        </button>
                    </form>

                    <p class="mt-7 border-t border-slate-100 pt-5 text-center text-[10px] text-slate-400">&copy; {{ date('Y') }} SMKN 4 Tanjungpinang</p>
                </div>
            </div>

            <p class="relative mt-7 text-center text-[10px] font-medium uppercase tracking-[0.2em] text-blue-950">Sistem Informasi Manajemen Terpadu</p>
        </section>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('toggle-password');
        const eyeIcon = document.getElementById('icon-eye');
        const eyeOffIcon = document.getElementById('icon-eye-off');

        togglePassword.addEventListener('click', () => {
            const isVisible = passwordInput.type === 'text';
            passwordInput.type = isVisible ? 'password' : 'text';
            togglePassword.setAttribute('aria-pressed', String(!isVisible));
            togglePassword.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
            eyeIcon.classList.toggle('hidden', !isVisible);
            eyeOffIcon.classList.toggle('hidden', isVisible);
        });
    </script>
</body>
</html>
