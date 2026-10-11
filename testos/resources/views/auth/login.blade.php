<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SIM SMKN 4 Tanjungpinang</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo & Brand Card Sesuai Logo Terbaru SKANPAT -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-white shadow-xl shadow-blue-900/10 p-2.5 mb-3 border border-slate-200">
                <img src="{{ asset('images/skanpat.jpg') }}" alt="Logo SMKN 4 Tanjungpinang" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">SIM SEKOLAH</h1>
            <p class="text-xs font-semibold tracking-wider text-blue-700 uppercase">SMKN 4 TANJUNGPINANG</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Sistem Informasi Manajemen Terpadu</p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8">
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Masuk ke Akun Anda</h2>
            <p class="text-xs text-slate-500 mb-6">Gunakan email sekolah dan kata sandi yang telah terdaftar.</p>

            @if (session('error'))
                <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="nama@smkn4tanjungpinang.sch.id">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#0A4DBA] hover:bg-blue-700 text-white text-sm font-semibold shadow-lg shadow-blue-600/30 transition-colors mt-2">
                    Masuk Sekarang
                </button>
            </form>

            <!-- Quick Demo Credentials helper -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Akun Demo (Password: password123):</p>
                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600">
                    <button type="button" onclick="fillCreds('kepsek@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">Kepsek:</span>
                        kepsek@...
                    </button>
                    <button type="button" onclick="fillCreds('tu@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">TU:</span>
                        tu@...
                    </button>
                    <button type="button" onclick="fillCreds('kurikulum@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">Waka Kurikulum:</span>
                        kurikulum@...
                    </button>
                    <button type="button" onclick="fillCreds('wakakesiswaan@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">Waka Kesiswaan:</span>
                        wakakesiswaan@...
                    </button>
                    <button type="button" onclick="fillCreds('wakasarpras@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">Waka Sarpras:</span>
                        wakasarpras@...
                    </button>
                    <button type="button" onclick="fillCreds('wakahumas@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">Waka Humas:</span>
                        wakahumas@...
                    </button>
                    <button type="button" onclick="fillCreds('pembina.osis@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">Pembina OSIS:</span>
                        pembina.osis@...
                    </button>
                    <button type="button" onclick="fillCreds('bendahara.bos@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">Bendahara BOS:</span>
                        bendahara.bos@...
                    </button>
                    <button type="button" onclick="fillCreds('bendahara.spp@smkn4tanjungpinang.sch.id')" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-left hover:bg-blue-50 hover:border-blue-300 transition">
                        <span class="font-semibold block text-slate-800">Bendahara SPP:</span>
                        bendahara.spp@...
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; {{ date('Y') }} SMKN 4 Tanjungpinang. All rights reserved.
        </p>
    </div>

    <script>
        function fillCreds(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password123';
        }
    </script>
</body>
</html>
