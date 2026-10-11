@extends('layouts.sim')

@section('title', 'Profil Saya')
@section('page-title', 'Profil & Keamanan Akun')

@section('content')
<div class="sim-page max-w-3xl space-y-6">
    <p class="page-intro text-slate-500">Perbarui informasi akun dan alamat email, atau ganti kata sandi untuk masuk ke sistem. Pengaturan role dan hak akses dikelola oleh TU.</p>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-800 mb-4">Informasi Profil</h2>
        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-800 mb-4">Ubah Kata Sandi</h2>
        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-rose-200/80 shadow-sm p-6">
        <h2 class="text-base font-bold text-rose-600 mb-4">Hapus Akun</h2>
        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
