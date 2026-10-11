@extends('layouts.sim')
@section('title', 'Isi Lembar Disposisi')
@section('page-title', 'Isi Lembar Disposisi')

@section('content')
<div class="sim-page mx-auto max-w-5xl">
    <div class="page-toolbar"><p class="text-slate-600">Tentukan penerima dan arahan untuk menindaklanjuti surat masuk.</p><a href="{{ route('disposisi.antrian') }}" class="page-button ui-action"><x-ui.icon name="back" /> Kembali</a></div>
    <section class="page-panel">@include('disposisi.partials.letter-summary')</section>
    <form action="{{ route('disposisi.store', $suratMasuk) }}" method="POST" class="space-y-6">
        @csrf
        <x-ui.form-errors />
        <section class="page-panel">
            <div class="page-panel-heading"><div><h2 class="page-title">1. Sifat surat</h2><p class="page-description">Pilih sifat surat yang dicantumkan pada lembar disposisi.</p></div></div>
            <fieldset class="page-section"><legend class="sr-only">Sifat Surat</legend><div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(['sangat_segera' => 'Sangat segera', 'segera' => 'Segera', 'rahasia' => 'Rahasia', 'biasa' => 'Biasa'] as $value => $label)
                    <label class="page-choice"><input type="checkbox" name="sifat_surat[]" value="{{ $value }}" @checked(in_array($value, old('sifat_surat', []))) class="rounded"><span>{{ $label }}</span></label>
                @endforeach
            </div></fieldset>
        </section>
        <section class="page-panel">
            <div class="page-panel-heading"><div><h2 class="page-title">2. Penerima dan instruksi</h2><p class="page-description">Pilih minimal satu penerima aktif. Anda dapat meneruskan surat ke beberapa penerima.</p></div></div>
            <div class="page-section grid gap-8 lg:grid-cols-2">
                <fieldset class="min-w-0"><legend class="mb-4">Diteruskan kepada Sdr. <span class="text-rose-600">*</span></legend>
                    <div class="space-y-2">
                        @foreach($roleOptions as $role => $label)
                            <label class="page-choice">
                                <input type="checkbox" name="tujuan_disposisi[]" value="{{ $role }}" @checked(in_array($role, old('tujuan_disposisi', []))) @disabled(empty($activeRecipientCounts[$role] ?? 0)) class="rounded">
                                <span>{{ $label }}@if(empty($activeRecipientCounts[$role] ?? 0))<span class="mt-1 block text-xs font-normal">Akun penerima belum aktif</span>@endif</span>
                            </label>
                        @endforeach
                        <div class="space-y-3 border-t border-slate-200 pt-4" x-data="{ other: {{ old('ada_penerima_lain') ? 'true' : 'false' }} }">
                            <label class="page-choice"><input id="ada_penerima_lain" type="checkbox" name="ada_penerima_lain" value="1" x-model="other" @checked(old('ada_penerima_lain')) class="rounded"><span>Sdr. Lainnya</span></label>
                            <div x-show="other" x-cloak class="page-field"><label for="penerima_lain" class="block">Nama penerima lainnya</label><input id="penerima_lain" type="text" name="penerima_lain" value="{{ old('penerima_lain') }}" :required="other" maxlength="255" class="block w-full" placeholder="Nama penerima lainnya"></div>
                            <p class="text-xs text-slate-500">Nama lainnya dicantumkan pada lembar. Pilih juga akun penerima aktif di atas untuk pengiriman dalam sistem.</p>
                        </div>
                    </div>
                    @error('tujuan_disposisi')<p class="mt-3 text-sm text-rose-700">{{ $message }}</p>@enderror
                </fieldset>
                <fieldset class="min-w-0"><legend class="mb-4">Dengan hormat harap:</legend>
                    <div class="space-y-2">
                        @foreach(['Tanggapan dan saran', 'Proses lebih lanjut', 'Koordinasi/konfirmasi'] as $item)
                            <label class="page-choice"><input type="checkbox" name="harapan_instruksi[]" value="{{ $item }}" @checked(in_array($item, old('harapan_instruksi', []))) class="rounded"><span>{{ $item }}</span></label>
                        @endforeach
                    </div>
                    <div class="page-field mt-5"><label for="instruksi_tambahan" class="block">Instruksi tambahan <span class="font-normal text-slate-500">(opsional)</span></label><textarea id="instruksi_tambahan" name="instruksi_tambahan" rows="5" maxlength="2000" class="block w-full" placeholder="Jelaskan tugas yang perlu dilakukan penerima.">{{ old('instruksi_tambahan') }}</textarea></div>
                </fieldset>
            </div>
        </section>
        <section class="page-panel">
            <div class="page-panel-heading"><div><h2 class="page-title">3. Catatan disposisi</h2><p class="page-description">Tambahkan keterangan pendukung bila diperlukan.</p></div></div>
            <div class="page-section"><label for="catatan" class="mb-2 block">Catatan <span class="font-normal text-slate-500">(opsional)</span></label><textarea id="catatan" name="catatan" rows="3" maxlength="2000" class="block w-full" placeholder="Catatan Kepala Sekolah untuk penerima.">{{ old('catatan') }}</textarea></div>
        </section>
        <section class="page-panel page-section space-y-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <p class="max-w-sm text-slate-500">Lembar disposisi diterbitkan dan ditandatangani Kepala Sekolah. Periksa penerima sebelum mengirim.</p>
                <div class="text-sm sm:text-right"><p class="text-slate-500">Kepala Sekolah</p><p>{{ auth()->user()->pangkat ?? 'Pembina Tk.I' }}</p><p class="text-xs text-slate-500">NIP {{ auth()->user()->nip ?? '19770421 200502 2 011' }}</p></div>
            </div>
            <div class="page-form-footer"><a href="{{ route('disposisi.antrian') }}" class="page-button ui-action"><x-ui.icon name="close" /> Batal</a><button type="submit" class="page-button page-button-primary ui-action"><x-ui.icon name="send" /> Simpan dan kirim disposisi</button></div>
        </section>
    </form>
</div>
@endsection
