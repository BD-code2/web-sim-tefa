<div class="border-b border-slate-200 bg-slate-50/60 px-5 py-6 sm:px-6">
    <div class="flex items-start justify-center gap-3 sm:items-center sm:gap-6">
        <img src="{{ asset('images/logo-kepri.png') }}" alt="Logo Pemerintah Provinsi Kepulauan Riau" class="h-12 w-12 shrink-0 object-contain sm:h-16 sm:w-16">
        <div class="min-w-0 text-center">
            <p class="text-xs text-slate-600">PEMERINTAH PROVINSI KEPULAUAN RIAU</p>
            <p class="text-xs font-semibold text-slate-700">DINAS PENDIDIKAN</p>
            <p class="mt-1 text-base font-bold text-slate-900">SMKN 4 TANJUNGPINANG</p>
            <p class="mt-1 text-xs leading-5 text-slate-500">Jalan Nusantara Km. 14, Batu IX, Tanjungpinang, Kepulauan Riau 29125</p>
            <p class="text-xs leading-5 text-slate-500">Telepon (0771) 4440844 &middot; NPSN 11002495 &middot; NSS 401316102010</p>
            <p class="text-xs leading-5 text-slate-500">smkn4-tanjungpinang.sch.id &middot; smknpt4@gmail.com</p>
        </div>
        <img src="{{ asset('images/logo-smkn4.png') }}" alt="Logo SMKN 4 Tanjungpinang" class="h-12 w-12 shrink-0 object-contain sm:h-16 sm:w-16">
    </div>
    <h2 class="mt-5 text-center font-semibold tracking-wide text-slate-900">LEMBAR DISPOSISI</h2>
</div>
<dl class="page-facts page-section sm:grid-cols-2">
    <div><dt>Surat dari</dt><dd class="font-medium">{{ $suratMasuk?->pengirim ?? '-' }}</dd></div>
    <div><dt>Diterima Tgl.</dt><dd>{{ $suratMasuk?->tanggal_diterima?->format('d/m/Y') ?? '-' }}</dd></div>
    <div><dt>No. Surat</dt><dd>{{ $suratMasuk?->no_surat ?? '-' }}</dd></div>
    <div><dt>No. Agenda</dt><dd>{{ $suratMasuk?->no_agenda ?? '-' }}</dd></div>
    <div><dt>Tgl. Surat</dt><dd>{{ $suratMasuk?->tanggal_surat?->format('d/m/Y') ?? '-' }}</dd></div>
    <div><dt>Hal</dt><dd class="font-medium">{{ $suratMasuk?->perihal ?? '-' }}</dd></div>
</dl>
