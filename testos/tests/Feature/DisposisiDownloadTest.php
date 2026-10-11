<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposisiDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_arsip_disposisi_dapat_dicari_dan_tetap_dibatasi_ke_penerima(): void
    {
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
        $penerima = User::factory()->create(['role' => 'waka_kurikulum']);
        $penerimaLain = User::factory()->create(['role' => 'waka_sarpras']);
        $tu = User::factory()->create(['role' => 'tu']);
        $suratDitemukan = SuratMasuk::create([
            'no_surat' => 'ARSIP/001', 'tanggal_surat' => '2026-09-30', 'tanggal_diterima' => '2026-09-30',
            'pengirim' => 'Dinas Pendidikan', 'perihal' => 'Rapat Kurikulum Khusus', 'created_by' => $tu->id,
        ]);
        $suratLain = SuratMasuk::create([
            'no_surat' => 'ARSIP/002', 'tanggal_surat' => '2026-09-30', 'tanggal_diterima' => '2026-09-30',
            'pengirim' => 'Mitra Industri', 'perihal' => 'Data Rahasia Sarpras', 'created_by' => $tu->id,
        ]);
        Disposisi::create([
            'surat_masuk_id' => $suratDitemukan->id, 'dari_user_id' => $kepsek->id,
            'kepada_user_id' => $penerima->id, 'instruksi' => 'Tindak lanjuti kurikulum.',
            'status' => 'selesai', 'status_persetujuan' => 'setuju', 'selesai_at' => now(),
        ]);
        Disposisi::create([
            'surat_masuk_id' => $suratLain->id, 'dari_user_id' => $kepsek->id,
            'kepada_user_id' => $penerimaLain->id, 'instruksi' => 'Tindak lanjuti sarpras.',
            'status' => 'selesai', 'status_persetujuan' => 'setuju', 'selesai_at' => now(),
        ]);

        $this->actingAs($penerima)
            ->get(route('disposisi.arsip', ['search' => 'Kurikulum Khusus']))
            ->assertOk()
            ->assertSee('Rapat Kurikulum Khusus')
            ->assertDontSee('Data Rahasia Sarpras')
            ->assertSee('value="Kurikulum Khusus"', false);

        $this->get(route('disposisi.arsip', ['search' => 'Data Rahasia']))
            ->assertOk()
            ->assertSee('Arsip disposisi yang sesuai filter tidak ditemukan.');

        $hariIni = now()->toDateString();
        $this->actingAs($tu)
            ->get(route('disposisi.arsip', [
                'penerima' => 'waka_kurikulum',
                'persetujuan' => 'setuju',
                'tanggal_mulai' => $hariIni,
                'tanggal_selesai' => $hariIni,
            ]))
            ->assertOk()
            ->assertSee('Rapat Kurikulum Khusus')
            ->assertDontSee('Data Rahasia Sarpras')
            ->assertSee('value="waka_kurikulum" selected', false)
            ->assertSee('Terapkan filter');
    }

    public function test_semua_penerima_langsung_bisa_mengunduh_perintah_setelah_dikirim_kepsek(): void
    {
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'name' => 'Kepala Sekolah Pengirim']);
        $penerimas = collect(User::DISPOSISI_RECIPIENT_ROLES)
            ->map(fn ($role) => User::factory()->create(['role' => $role]));
        // Seluruh akun aktif pada role yang dipilih harus menerima lembar.
        $penerimas->push(User::factory()->create(['role' => 'waka_kurikulum']));
        $tu = $penerimas->firstWhere('role', 'tu');
        $surat = SuratMasuk::create([
            'no_surat' => 'TEST/DISPOSISI/001',
            'tanggal_surat' => '2026-09-30',
            'tanggal_diterima' => '2026-09-30',
            'pengirim' => 'Dinas Pendidikan',
            'perihal' => 'Koordinasi kegiatan sekolah',
            'created_by' => $tu->id,
            'diteruskan_ke_kepala_sekolah_at' => now(),
        ]);

        $this->actingAs($kepsek)->post(route('disposisi.store', $surat), [
            'tujuan_disposisi' => User::DISPOSISI_RECIPIENT_ROLES,
            'sifat_surat' => ['segera'],
            'harapan_instruksi' => ['Proses lebih lanjut'],
            'instruksi_tambahan' => 'Siapkan laporan kegiatan sekolah.',
            'catatan' => 'Sampaikan hasil paling lambat besok.',
        ])->assertSessionHasNoErrors()->assertRedirect(route('disposisi.antrian'));

        $this->assertSame($penerimas->count(), Disposisi::count());

        foreach ($penerimas as $penerima) {
            $disposisi = Disposisi::where('kepada_user_id', $penerima->id)->sole();
            $this->assertSame('pending', $disposisi->status_persetujuan);
            $this->assertSame('menunggu', $disposisi->status);
            $url = route('disposisi.unduh-lembar', $disposisi);

            $this->actingAs($penerima)->get(route('disposisi.antrian'))
                ->assertOk()->assertSee($url, false);
            $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf')
                ->assertDownload('lembar-disposisi-'.str_replace('/', '-', $surat->no_agenda).'.pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $this->assertGreaterThanOrEqual(2, preg_match_all('/\/Subtype\s*\/Image\b/', $response->getContent()), 'Kedua logo harus tertanam sebagai gambar dalam PDF.');
            preg_match('/\/Type\s*\/Pages\b(?:(?!endobj).)*?\/Count\s+(\d+)/s', $response->getContent(), $pages);
            $this->assertSame(1, (int) ($pages[1] ?? 0), 'Lembar disposisi harus muat satu halaman.');
        }

        $lembar = Disposisi::where('kepada_user_id', $penerimas->first()->id)->sole();
        $html = view('disposisi.pdf', [
            'disposisi' => $lembar,
            'kepsekData' => ['nama' => $kepsek->name, 'nip' => '123456', 'pangkat' => 'Pembina'],
        ])->render();
        foreach (['LEMBAR DISPOSISI', 'Diteruskan kepada Sdr.', 'Dengan hormat harap:', 'Siapkan laporan kegiatan sekolah.', 'Sampaikan hasil paling lambat besok.', $kepsek->name] as $text) {
            $this->assertStringContainsString($text, $html);
        }

        $bukanPenerima = User::factory()->create(['role' => 'waka_kurikulum']);
        $this->actingAs($bukanPenerima)->get(route('disposisi.unduh-lembar', $lembar))->assertForbidden();

        $lembar->update(['status' => 'selesai', 'selesai_at' => now()]);
        $this->actingAs($penerimas->first())->get(route('disposisi.arsip'))
            ->assertOk()->assertSee(route('disposisi.unduh-lembar', $lembar), false);
    }
}
