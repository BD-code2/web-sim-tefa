<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\KerjaSamaIndustri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KerjaSamaAgendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_dapat_ditambahkan_dan_dihapus_dari_detail_industri(): void
    {
        $humas = User::factory()->create(['role' => 'humas']);
        $industri = KerjaSamaIndustri::create([
            'nama_industri' => 'PT Mitra Sekolah',
            'alamat' => 'Tanjungpinang',
            'program_keahlian' => '-',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2027-01-01',
            'status' => 'aktif',
            'created_by' => $humas->id,
        ]);

        $this->actingAs($humas)->post(route('kerja-sama.agenda.store', $industri), [
            'judul' => 'Evaluasi PKL Bersama',
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-11',
            'waktu' => '09:00',
            'lokasi' => 'Ruang Rapat Industri',
            'tipe' => 'rapat',
            'is_penting' => '1',
            'deskripsi' => 'Evaluasi pelaksanaan kerja sama PKL.',
        ])->assertSessionHasNoErrors()->assertRedirect(route('kerja-sama.show', $industri));

        $agenda = Agenda::sole();
        $this->assertSame($industri->id, $agenda->kerja_sama_industri_id);
        $this->assertTrue($agenda->is_penting);

        $this->get(route('kerja-sama.show', $industri))
            ->assertOk()
            ->assertSee('Evaluasi PKL Bersama')
            ->assertSee('Tambah Agenda')
            ->assertSee('Lihat Detail Agenda');

        $this->get(route('kerja-sama.agenda.show', [$industri, $agenda]))
            ->assertOk()
            ->assertSee('Evaluasi PKL Bersama')
            ->assertSee('Ruang Rapat Industri');

        $this->get(route('kerja-sama.agenda.edit', [$industri, $agenda]))
            ->assertOk()
            ->assertSee('value="Evaluasi PKL Bersama"', false);

        $this->patch(route('kerja-sama.agenda.update', [$industri, $agenda]), [
            'judul' => 'Evaluasi PKL Diperbarui',
            'tanggal_mulai' => '2026-10-12',
            'waktu' => '10:30',
            'lokasi' => 'Aula Mitra',
            'tipe' => 'kegiatan',
            'deskripsi' => 'Agenda yang telah diperbarui.',
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('kerja-sama.agenda.show', [$industri, $agenda]));

        $this->assertDatabaseHas('agendas', [
            'id' => $agenda->id,
            'judul' => 'Evaluasi PKL Diperbarui',
            'lokasi' => 'Aula Mitra',
            'is_penting' => false,
        ]);

        $this->delete(route('kerja-sama.agenda.destroy', [$industri, $agenda]))
            ->assertRedirect(route('kerja-sama.show', $industri));
        $this->assertDatabaseMissing('agendas', ['id' => $agenda->id]);
    }
}
