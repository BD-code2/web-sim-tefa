<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\KerjaSamaIndustriController;
use App\Http\Controllers\LaporanSuratController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\SuratDinasController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Landing Page SMKN 4 Tanjungpinang
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authenticated Routes - Common for all roles
Route::middleware(['auth', 'role:kepala_sekolah,kepala_tu,tu,humas,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,pembina_osis,bendahara_bos,bendahara_spp'])->group(function () {
    // Beranda & Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil User
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifikasi Web
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');

});

// Disposisi - ALL ROLES (but different views per role in controller)
Route::middleware(['auth', 'role:kepala_sekolah,tu,humas,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,pembina_osis,bendahara_bos,bendahara_spp'])->group(function () {
    Route::get('/disposisi/antrian', [DisposisiController::class, 'antrian'])->name('disposisi.antrian');
    Route::get('/disposisi/arsip', [DisposisiController::class, 'arsip'])->name('disposisi.arsip');
    Route::get('/disposisi/{disposisi}/unduh-surat', [DisposisiController::class, 'unduhSuratMasuk'])->name('disposisi.unduh-surat');
    Route::get('/disposisi/{disposisi}/unduh-lembar', [DisposisiController::class, 'unduhLembarDisposisi'])->name('disposisi.unduh-lembar');
    Route::patch('/disposisi/{disposisi}/status', [DisposisiController::class, 'updateStatus'])->name('disposisi.status');
    Route::post('/disposisi/{disposisi}/persetujuan', [DisposisiController::class, 'persetujuan'])->name('disposisi.persetujuan');
    Route::delete('/disposisi/{disposisi}', [DisposisiController::class, 'destroy'])->name('disposisi.destroy');
});

// Persetujuan Surat Keluar hanya dapat dilakukan Kepala Sekolah (admin melewati role middleware).
Route::middleware(['auth', 'role:admin,kepala_sekolah'])->group(function () {
    Route::post('/disposisi/surat-keluar/{suratKeluar}/setujui', [DisposisiController::class, 'setujuiSuratKeluar'])->name('disposisi.surat-keluar.setujui');
    Route::post('/disposisi/surat-keluar/{suratKeluar}/revisi', [DisposisiController::class, 'revisiSuratKeluar'])->name('disposisi.surat-keluar.revisi');
});

// Pengisian dan penerbitan lembar disposisi hanya oleh Kepala Sekolah.
Route::middleware(['auth', 'role:kepala_sekolah'])->group(function () {
    Route::get('/disposisi/{disposisi}/isi-lembar', [DisposisiController::class, 'isiLembarDisposisi'])->name('disposisi.isi-lembar');
    Route::post('/disposisi/{disposisi}/simpan-lembar', [DisposisiController::class, 'simpanLembarDisposisi'])->name('disposisi.lembar.simpan');
    Route::get('/disposisi/{disposisi}/cetak', [DisposisiController::class, 'cetakLembarDisposisi'])->name('disposisi.cetak');
});

// Route untuk melihat detail Surat Masuk & Riwayat Disposisi
Route::middleware(['auth', 'role:tu,kepala_sekolah,humas,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,pembina_osis,bendahara_bos,bendahara_spp'])->group(function () {
    Route::get('/disposisi/{disposisi}', [DisposisiController::class, 'show'])->name('disposisi.show');
});

// Surat Masuk - ADMIN, TU only (create, edit, update, destroy, index)
Route::middleware(['auth', 'role:tu'])->group(function () {
    Route::get('/laporan-surat', [LaporanSuratController::class, 'index'])->name('laporan-surat.index');
    Route::get('/laporan-surat/cetak', [LaporanSuratController::class, 'cetak'])->name('laporan-surat.cetak');

    Route::post('/surat-masuk/{suratMasuk}/teruskan-kepala-sekolah', [SuratMasukController::class, 'teruskanKeKepalaSekolah'])->name('surat-masuk.teruskan-kepala-sekolah');
    Route::resource('surat-masuk', SuratMasukController::class)->except(['show']);
});

Route::middleware(['auth', 'role:kepala_sekolah'])->group(function () {
    Route::get('/surat-masuk/{suratMasuk}/disposisi/create', [DisposisiController::class, 'createLembar'])->name('disposisi.lembar.create');
    Route::post('/surat-masuk/{suratMasuk}/disposisi', [DisposisiController::class, 'store'])->name('disposisi.store');
    Route::post('/surat-masuk/{suratMasuk}/disposisi/tolak', [DisposisiController::class, 'tolakSuratMasuk'])->name('disposisi.surat-masuk.tolak');
});

// Definisikan route statis /create sebelum route dinamis /{surat_masuk}.
Route::middleware(['auth', 'role:tu,kepala_sekolah,humas,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,pembina_osis,bendahara_bos,bendahara_spp'])->group(function () {
    Route::get('/surat-masuk/{suratMasuk}/unduh', [SuratMasukController::class, 'unduhLampiran'])->name('surat-masuk.unduh');
    Route::get('/surat-masuk/{surat_masuk}', [SuratMasukController::class, 'show'])->name('surat-masuk.show');
});

// Surat Keluar (Surat Undangan / Surat Umum) - ADMIN, TU only
Route::middleware(['auth', 'role:tu'])->group(function () {
    // {kategori?} opsional: link lama SK/SPT dialihkan ke modul Surat Dinas
    Route::get('/surat-keluar/create/{kategori?}', [SuratKeluarController::class, 'create'])->name('surat-keluar.create');

    Route::get('/surat-keluar/{suratKeluar}/pdf', [SuratKeluarController::class, 'cetakPdf'])->name('surat-keluar.pdf');
    Route::patch('/surat-keluar/{suratKeluar}/status', [SuratKeluarController::class, 'updateStatus'])->name('surat-keluar.status');
    Route::resource('surat-keluar', SuratKeluarController::class)->except(['create', 'show']);
});

Route::middleware(['auth', 'role:kepala_tu'])->get('/surat-keluar/pemeriksaan-kepala-tu', [SuratKeluarController::class, 'antrianKepalaTU'])->name('surat-keluar.pemeriksaan-kepala-tu');
Route::middleware(['auth', 'role:kepala_tu'])->post('/surat-keluar/{suratKeluar}/pemeriksaan-kepala-tu', [SuratKeluarController::class, 'periksaKepalaTU'])->name('surat-keluar.periksa-kepala-tu');
Route::middleware(['auth', 'role:kepala_tu'])->post('/surat-dinas/{suratDinas}/pemeriksaan-kepala-tu', [SuratDinasController::class, 'periksaKepalaTU'])->name('surat-dinas.periksa-kepala-tu');
Route::middleware(['auth', 'role:tu'])->post('/surat-keluar/{suratKeluar}/teruskan-kepala-sekolah', [SuratKeluarController::class, 'teruskanKeKepalaSekolah'])->name('surat-keluar.teruskan-kepala-sekolah');
Route::middleware(['auth', 'role:tu'])->post('/surat-dinas/{suratDinas}/teruskan-kepala-sekolah', [SuratDinasController::class, 'teruskanKeKepalaSekolah'])->name('surat-dinas.teruskan-kepala-sekolah');

// Kepala Sekolah membaca rincian pengajuan sebelum memberikan keputusan.
Route::middleware(['auth', 'role:tu,kepala_tu,kepala_sekolah'])->get('/surat-keluar/{suratKeluar}', [SuratKeluarController::class, 'show'])->name('surat-keluar.show');

// Jalur persetujuan lama tetap tersedia, tetapi hanya untuk Kepala Sekolah/admin.
Route::middleware(['auth', 'role:admin,kepala_sekolah'])->post('/surat-keluar/{suratKeluar}/approve', [SuratKeluarController::class, 'approve'])->name('surat-keluar.approve');

// Modul Surat Dinas untuk Surat Keputusan dan Surat Perintah Tugas hanya dikelola TU.
Route::middleware(['auth', 'role:tu'])->prefix('surat-dinas')->name('surat-dinas.')->group(function () {
    Route::get('/', [SuratDinasController::class, 'index'])->name('index');
    Route::get('/create/sk', [SuratDinasController::class, 'createSk'])->name('create-sk');
    Route::get('/create/spt', [SuratDinasController::class, 'createSpt'])->name('create-spt');
    Route::post('/', [SuratDinasController::class, 'store'])->name('store');
    Route::get('/{suratDinas}/edit', [SuratDinasController::class, 'edit'])->name('edit');
    Route::put('/{suratDinas}', [SuratDinasController::class, 'update'])->name('update');
    Route::get('/{suratDinas}', [SuratDinasController::class, 'show'])->name('show');
    Route::get('/{id}/pdf', [SuratDinasController::class, 'cetakPdf'])->name('pdf');
});

// Persetujuan Surat Dinas hanya dapat dilakukan Kepala Sekolah (admin melewati role middleware).
Route::middleware(['auth', 'role:admin,kepala_sekolah'])->group(function () {
    Route::get('/disposisi/surat-dinas/{suratDinas}', [SuratDinasController::class, 'show'])->name('disposisi.surat-dinas.show');
    Route::post('/surat-dinas/{suratDinas}/setujui', [SuratDinasController::class, 'setujui'])->name('surat-dinas.setujui');
    Route::post('/surat-dinas/{suratDinas}/revisi', [SuratDinasController::class, 'revisi'])->name('surat-dinas.revisi');
});

Route::middleware(['auth', 'role:kepala_tu'])->get('/pemeriksaan-kepala-tu/surat-dinas/{suratDinas}', [SuratDinasController::class, 'show'])->name('surat-dinas.pemeriksaan.show');

// Kerja Sama Industri - ADMIN, HUMAS only
Route::middleware(['auth', 'role:tu,humas,waka_humas'])->group(function () {
    Route::post('/kerja-sama/{kerjaSama}/approve', [KerjaSamaIndustriController::class, 'approve'])->name('kerja-sama.approve');
    Route::post('/kerja-sama/{kerjaSama}/agenda', [KerjaSamaIndustriController::class, 'storeAgenda'])->name('kerja-sama.agenda.store');
    Route::get('/kerja-sama/{kerjaSama}/agenda/{agenda}/edit', [KerjaSamaIndustriController::class, 'editAgenda'])->name('kerja-sama.agenda.edit');
    Route::patch('/kerja-sama/{kerjaSama}/agenda/{agenda}', [KerjaSamaIndustriController::class, 'updateAgenda'])->name('kerja-sama.agenda.update');
    Route::get('/kerja-sama/{kerjaSama}/agenda/{agenda}', [KerjaSamaIndustriController::class, 'showAgenda'])->name('kerja-sama.agenda.show');
    Route::delete('/kerja-sama/{kerjaSama}/agenda/{agenda}', [KerjaSamaIndustriController::class, 'destroyAgenda'])->name('kerja-sama.agenda.destroy');
    Route::resource('kerja-sama', KerjaSamaIndustriController::class)->parameters([
        'kerja-sama' => 'kerjaSama',
    ]);
});

// Sertifikat PKL - ADMIN, HUMAS only
Route::middleware(['auth', 'role:tu,humas,waka_humas'])->group(function () {
    Route::get('/sertifikat/{sertifikat}/pdf', [SertifikatController::class, 'cetakPdf'])->name('sertifikat.pdf');
    Route::patch('/sertifikat/{sertifikat}/status', [SertifikatController::class, 'updateStatus'])->name('sertifikat.status');
    Route::patch('/sertifikat/{sertifikat}/nilai', [SertifikatController::class, 'updateNilai'])->name('sertifikat.nilai');
    Route::resource('sertifikat', SertifikatController::class)->except(['create', 'store']);
});

// Data Siswa & Generate Sertifikat Massal - ADMIN, TU, HUMAS only
Route::middleware(['auth', 'role:tu,humas,waka_humas'])->prefix('data-siswa')->name('data-siswa.')->group(function () {
    Route::get('/', [DataSiswaController::class, 'index'])->name('index');
    Route::get('/agenda/{periode}', [DataSiswaController::class, 'showAgenda'])->name('agenda.show');
    Route::get('/periode/create', [DataSiswaController::class, 'createPeriode'])->name('periode.create');
    Route::post('/periode', [DataSiswaController::class, 'storePeriode'])->name('periode.store');
    Route::get('/periode/{periode}/edit', [DataSiswaController::class, 'editPeriode'])->name('periode.edit');
    Route::patch('/periode/{periode}', [DataSiswaController::class, 'updatePeriode'])->name('periode.update');
    Route::delete('/periode/{periode}', [DataSiswaController::class, 'destroyPeriode'])->name('periode.destroy');
    Route::get('/import', [DataSiswaController::class, 'importForm'])->name('import');
    Route::post('/import', [DataSiswaController::class, 'import'])->name('import.store');
    Route::post('/{dataSiswa}/proses-pkl', [DataSiswaController::class, 'prosesSiswa'])->name('proses-siswa');
    Route::post('/{dataSiswa}/selesaikan-pkl', [DataSiswaController::class, 'selesaikanSiswa'])->name('selesaikan-siswa');
    Route::post('/proses-kelas', [DataSiswaController::class, 'prosesKelas'])->name('proses-kelas');
    Route::post('/selesaikan-kelas', [DataSiswaController::class, 'selesaikanKelas'])->name('selesaikan-kelas');
    Route::post('/terbitkan-kelas', [DataSiswaController::class, 'terbitkanKelas'])->name('terbitkan-kelas');
    Route::get('/pdf-kelas', [DataSiswaController::class, 'pdfKelas'])->name('pdf-kelas');
    Route::delete('/{dataSiswa}', [DataSiswaController::class, 'destroy'])->name('destroy');
});

// Pengaturan & Kelola User - dikelola oleh Tata Usaha
Route::middleware(['auth', 'role:tu'])->group(function () {
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/auth.php';
