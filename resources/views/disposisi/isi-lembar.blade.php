@extends('layouts.sim')
@section('title', 'Sunting Lembar Disposisi')
@section('page-title', 'Sunting Lembar Disposisi')

@section('content')
@php($suratMasuk = $disposisi->suratMasuk)
<div class="sim-page mx-auto max-w-5xl">
    <div class="page-toolbar"><p class="text-slate-600">Perbarui isian pada lembar disposisi yang sudah diterbitkan.</p><a href="{{ route('disposisi.antrian') }}" class="page-button ui-action"><x-ui.icon name="back" /> Kembali</a></div>
    <section class="page-panel">@include('disposisi.partials.letter-summary')</section>
    <form action="{{ route('disposisi.lembar.simpan', $disposisi) }}" method="POST" class="space-y-6">
        @csrf
        <x-ui.form-errors />
        <section class="page-panel">
            <div class="page-panel-heading"><h2 class="page-title">Sifat surat</h2></div>
            <fieldset class="page-section"><legend class="sr-only">Sifat Surat</legend><div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(['sangat_segera' => 'Sangat segera', 'segera' => 'Segera', 'rahasia' => 'Rahasia', 'biasa' => 'Biasa'] as $value => $label)
                    <label class="page-choice"><input type="checkbox" name="sifat_surat[]" value="{{ $value }}" @checked(in_array($value, old('sifat_surat', $disposisi->sifat_surat ?? []))) class="rounded"><span>{{ $label }}</span></label>
                @endforeach
            </div></fieldset>
        </section>
        <section class="page-panel">
            <div class="page-panel-heading"><div><h2 class="page-title">Penerima dan instruksi pada lembar</h2><p class="page-description">Isian ini memperbarui dokumen disposisi yang telah diterbitkan.</p></div></div>
            <div class="page-section grid gap-8 lg:grid-cols-2">
                <fieldset><legend class="mb-4">Diteruskan kepada Sdr.</legend><div class="space-y-2">
                    @foreach(['Waka Kurikulum', 'Waka Kesiswaan', 'Waka Sarpras', 'Waka Humas', 'Pembina OSIS', 'Koordinator TU', 'Bendahara Sekolah/BOS', 'Bendahara SPP'] as $label)
                        <label class="page-choice"><input type="checkbox" name="tujuan_disposisi[]" value="{{ $label }}" @checked(in_array($label, old('tujuan_disposisi', $disposisi->tujuan_disposisi ?? []))) class="rounded"><span>{{ $label }}</span></label>
                    @endforeach
                    <div class="space-y-3 border-t border-slate-200 pt-4" x-data="{ other: {{ old('penerima_lain', $disposisi->penerima_lain) ? 'true' : 'false' }} }">
                        <label class="page-choice"><input type="checkbox" name="tujuan_disposisi[]" value="penerima_lain" x-model="other" @checked(old('penerima_lain', $disposisi->penerima_lain)) class="rounded"><span>Sdr. Lainnya</span></label>
                        <div x-show="other" x-cloak class="page-field"><label for="penerima_lain" class="block">Nama penerima lainnya</label><input id="penerima_lain" type="text" name="penerima_lain" value="{{ old('penerima_lain', $disposisi->penerima_lain) }}" maxlength="255" class="block w-full" placeholder="Nama penerima lainnya"></div>
                    </div>
                </div></fieldset>
                <fieldset><legend class="mb-4">Dengan hormat harap:</legend>
                    <div class="space-y-2">@foreach(['Tanggapan dan saran', 'Proses lebih lanjut', 'Koordinasi/konfirmasi'] as $item)<label class="page-choice"><input type="checkbox" name="harapan_instruksi[]" value="{{ $item }}" @checked(in_array($item, old('harapan_instruksi', $disposisi->harapan_instruksi ?? []))) class="rounded"><span>{{ $item }}</span></label>@endforeach</div>
                    <div class="page-field mt-5"><label for="instruksi_tambahan" class="block">Instruksi tambahan <span class="font-normal text-slate-500">(opsional)</span></label><textarea id="instruksi_tambahan" name="instruksi_tambahan" rows="5" maxlength="2000" class="block w-full">{{ old('instruksi_tambahan', $disposisi->instruksi_tambahan) }}</textarea></div>
                </fieldset>
            </div>
        </section>
        <section class="page-panel">
            <div class="page-panel-heading"><h2 class="page-title">Catatan disposisi</h2></div>
            <div class="page-section"><label for="catatan" class="mb-2 block">Catatan <span class="font-normal text-slate-500">(opsional)</span></label><textarea id="catatan" name="catatan" rows="3" class="block w-full">{{ old('catatan', $disposisi->catatan) }}</textarea></div>
        </section>
        <div class="page-form-footer">
            <a href="{{ route('disposisi.antrian') }}" class="page-button ui-action"><x-ui.icon name="close" /> Batal</a>
            <a href="{{ route('disposisi.cetak', $disposisi) }}" target="_blank" rel="noopener" class="page-button ui-action"><x-ui.icon name="eye" /> Lihat PDF saat ini</a>
            <button type="submit" class="page-button page-button-primary ui-action"><x-ui.icon name="save" /> Simpan &amp; buka PDF</button>
        </div>
    </form>
</div>
@endsection
