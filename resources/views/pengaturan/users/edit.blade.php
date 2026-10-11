@extends('layouts.sim')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Data Pengguna')

@section('content')
<div class="sim-page max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">Edit Akun: {{ $user->role_label }}</h2>
            <a href="{{ route('pengaturan.users.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 ui-action"><x-ui.icon name="close" /> Batal</a>
        </div>

        <form action="{{ route('pengaturan.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap & Gelar *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ubah Kata Sandi (Kosongkan jika tetap)</label>
                    <input type="password" name="password"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                           placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Sandi Baru</label>
                    <input type="password" name="password_confirmation"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                           placeholder="••••••••">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Peran / Hak Akses *</label>
                <select name="role" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach($roles as $role => $label)
                        <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>Akun Aktif (Dapat Login ke Sistem)</span>
                </label>
            </div>

            <div class="page-form-footer pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('pengaturan.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition ui-action"><x-ui.icon name="close" /> Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-sm transition ui-action"><x-ui.icon name="save" /> Perbarui Pengguna</button>
            </div>
        </form>
    </div>
</div>
@endsection
