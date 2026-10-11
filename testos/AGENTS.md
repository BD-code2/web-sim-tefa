# SIM Sekolah - AGENTS.md

Laravel 13 (PHP 8.3) school administration app for SMKN 4 Tanjungpinang: surat masuk, surat keluar (undangan/umum), surat dinas (SK/SPT), disposisi, agenda, kerja sama industri, sertifikat PKL. UI text and code comments are Indonesian.

## Setup

```sh
composer install
cp .env.example .env       # SQLite default; database/database.sqlite must exist
php artisan key:generate
php artisan migrate
npm install                # .npmrc sets ignore-scripts=true
npm run dev
```

One shot: `composer run setup` (install, .env, key, `migrate --force`, `npm install --ignore-scripts`, `npm run build`).

`.env` uses `SESSION_DRIVER=database` and `QUEUE_CONNECTION=database` — the dev app needs a migrated SQLite file (tests override both to `array`/`sync`).

Seeded logins (`php artisan db:seed`, idempotent `updateOrCreate`), password `password123`:
`kepsek@`, `tu@`, `humas@`, `kurikulum@`, `wakakesiswaan@`, `wakasarpras@`, `wakahumas@`, `pembina.osis@`, `bendahara.bos@`, `bendahara.spp@` — all `@smkn4tanjungpinang.sch.id`; seeded password `password123`. No admin, guru, or siswa account is seeded. Legacy admin/guru users are disabled by migration while retaining historical references.

`/register` is open and creates an active user with the database default role `siswa`; no email verification is enforced on app routes.

## Commands

| Command | Notes |
|---|---|
| `php artisan test` | `composer test` = `config:clear` + test |
| `vendor\bin\pint` | formatter, no pint.json → default preset; **`php artisan pint` is NOT a defined command** |
| `npm run dev` / `npm run build` | Vite 8 + laravel-vite-plugin |
| `php artisan dev` | serve + `queue:listen` + `npm run dev` via `concurrently` on Windows; Pail (logs) is only registered when `pcntl_fork` exists, which this Windows PHP does not have |

Single test: `php artisan test --filter=<name>` (standard PHPUnit filtering).

**Test and Pint output is JSON when run by an agent.** `laravel/pao` (require-dev) detects agent shells and replaces PHPUnit/Pint output with one line: `{"tool":"phpunit","result":"passed","tests":59,...}` — on failure read `failures` / `error_details` / `raw`. Set `PAO_DISABLE=1` for normal pretty output (`PAO_FORCE=1` forces JSON). PowerShell has no `VAR=value cmd` syntax: use `$env:PAO_DISABLE='1'; php artisan test`.

**Pint is not clean repo-wide.** `vendor\bin\pint --test` reports ~20 style-dirty files today — do not treat that as your regression, and running bare `vendor\bin\pint` rewrites all of them into a huge diff. Format only files you touched: `vendor\bin\pint --test app\Http\Controllers\Foo.php`.

## Testing

- SQLite in-memory via `phpunit.xml` (`DB_DATABASE=:memory:`, `BCRYPT_ROUNDS=4`); `RefreshDatabase` per test.
- Factory password is `password` (`UserFactory`), factory default role `siswa`. `password123` is seeder-only — tests logging in with it will fail.
- Run `php artisan test` for the current test count and status.
- On Windows this PHP build prints `Module "mysqli" is already loaded` warnings to stderr; that is noise, not failure.

## RBAC (`role` middleware + `routes/web.php`)

Roles: `kepala_sekolah`, `tu`, `humas`, `waka_kurikulum`, `waka_kesiswaan`, `waka_sarpras`, `waka_humas`, `pembina_osis`, `bendahara_bos`, `bendahara_spp`, `siswa`.
`role` alias is registered in `bootstrap/app.php` → `App\Http\Middleware\RoleMiddleware`. Inactive users are logged out before role checks. TU owns the former admin management capabilities; there are no active admin or guru accounts. The eight recipient roles from `public/preference/format table/format disposisi.jpeg` are seeded as active accounts.

Access map (enforced by route groups — verify there before assuming):

- All active roles: dashboard, profile, notifikasi, agenda resource.
- TU and Kepala Sekolah manage/send disposisi; the eight recipient roles process disposisi sent to their accounts.
- TU and Kepala Sekolah: surat keluar/dinas approvals and lembar disposisi, guarded by `manage-disposisi`.
- TU: surat masuk/keluar, surat dinas, user management, data siswa, kerja sama, and certificates.
- Kepala Sekolah and recipients can view surat masuk only when the letter is assigned through a disposisi.
- `siswa`: dashboard, profile, notifikasi, agenda only.

## Approval flows

Surat keluar punya dua jalur mendekati duplikat; keduanya hanya menulis nomor **jika `no_surat` masih kosong**, dan men-set sebelum `update()` sehingga format controller menang atas hook model `UND/...`:

1. `POST surat-keluar/{id}/approve` (tu): decision `setujui|tolak`, sets `approved_by`/`approved_at`, number `421.5/SMKN4/MM/YYYY/NNNN`.
2. `POST disposisi/surat-keluar/{id}/setujui|revisi` (tu,kepsek + Gate): same number formula; `revisi` only flips status to `minta_revisi`, no number.

Kedua jalur menghitung urut sebagai `count(semua baris yang punya no_surat) + 1` — **global, bukan per bulan**, dan dengan SoftDeletes aktif. Artinya: nomor tidak kontigu per bulan, dan setelah satu surat di-soft-delete angka berikutnya bisa bentrok dengan nomor yang masih ada (`no_surat` UNIQUE → QueryException).

3. `POST surat-dinas/{id}/setujui|revisi` (tu,kepsek + Gate) di `SuratDinasController`. TU membuat SK/SPT → status selalu awal `menunggu_persetujuan` (tombol "Simpan Draf" sudah dihapus). `setujui` → status `disetujui` + `approved_by`/`approved_at` + nomor dibuat **hanya bila kosong**: SK `421.5/SMKN4/MM/YYYY/NNNN`, SPT `421.5.1/SMKN4/MM/YYYY/NNNN` (urut per jenis, dihitung dari row ber-prefix `LIKE '{prefix}/SMKN4/MM/YYYY/%'`). `revisi` → status `ditolak` + `catatan_revisi` **wajib**. Keduanya redirect ke `disposisi.antrian?tab=persetujuan`.

Halaman `disposisi.antrian` (Kepsek) menampilkan daftar **kedua-duanya**: `$suratKeluarMenunggu` + `$suratDinasMenunggu` (kedua variabel dikirim controller; section "Surat Menunggu Persetujuan" ada di `antrian-kepsek.blade.php`). Panel approval juga ada di `surat-dinas/show` (khusus TU/Kepala Sekolah saat status `menunggu_persetujuan`).

`POST disposisi/{id}/persetujuan` has **no** Gate check — reachable by the addressed recipient, TU, or Kepala Sekolah after the new ownership check. It updates `disposisi.status_persetujuan` and cascades to `SuratMasuk.status`.

### Status trap: `minta_revisi`

`revisiSuratKeluar` sets status `minta_revisi`, but `SuratKeluarController::edit()` only allows `draft`/`ditolak`, and no view badge handles `minta_revisi` (index/show fall through to the else branch). A surat keluar sent back for revision is therefore uneditable and shows no status label. `approve(tolak)` sets `ditolak`, which *is* editable — don't assume the two rejection paths behave the same.

### Gate PDF Surat Dinas

`SuratDinasController::cetakPdf` `abort 403` kecuali `status === 'disetujui'`. Di view (`index`/`show`) tombol PDF hanya dirender sebagai link bila disetujui; selain itu ikon/link tersembunyi (span non-link). Jangan "buka" PDF sebelum approval.

## Auto-numbering (model `boot()`)

- `SuratMasuk` → `AGD/MM/YYYY/NNNN` (on `creating`); `SuratKeluar` → `UND/MM/YYYY/NNNN` (on `saving` when `status === 'disetujui'`); both use `withTrashed()` for the sequence. `SuratDinas` has **no** auto-numbering hook — nomor dibuat di `SuratDinasController::setujui()` (bukan di model), lihat Approval flows.
- `SertifikatSiswa` → `SERT/YYYY/NNNN` (on `creating`, no SoftDeletes) — **but the controller never reaches this hook**.
- **`SertifikatController::store()` always sets `no_sertifikat = "B/423.4/P.06.209/I-SMKN4/PKL/{year}"`** (it computes `$urut` and discards it). The column is `UNIQUE`, so a **second certificate created in the same year returns HTTP 500** (unique violation) — verified. The seeder already occupies `…/2025` and `…/2026`, so on a seeded DB even the *first* insert in 2026 fails. Don't assume certificate numbers are unique-per-row by construction; fix the sequence if you touch this.
- Nomor manual: form Surat Dinas punya input `no_surat` (opsional, unique nullable); surat keluar selalu digenerate lewat approve (`421.5/...`) — accessor `nomor_surat_display` hanya membungkus `no_surat`. Surat dinas tanpa nomor yang di-approve otomatis dapat nomor `421.5/...` (SK) atau `421.5.1/...` (SPT).

## Deletes / SoftDeletes

- `SoftDeletes`: `SuratMasuk`, `SuratKeluar`, `KerjaSamaIndustri` (migrations have `softDeletes()`).
- Hard delete only: `SertifikatSiswa`, `Disposisi`, `Agenda`, `SuratDinas`, `TemplateSertifikat` (no trait, no column).
- Every `destroy()` deletes the row only — uploaded `lampiran_path` files are never removed. `Storage::disk('public')->delete()` runs only in `update()` when a replacement file is uploaded. Keep it that way unless explicitly asked to clean up.

## Modul Surat Dinas vs Surat Keluar

TAHAP 1 memisahkan dua tabel/area (jangan digabung lagi):

| | **Surat Dinas** (`surat_dinas`) | **Surat Keluar** (`surat_keluars`) |
|---|---|---|
| Isi | SK & SPT (`jenis_surat enum SK,SPT`) | Surat Undangan / Surat Umum |
| Kolom inti | `no_surat` (unique nullable), `tentang_judul`, `tgl_ditetapkan`, `status` (**4 nilai**: draft/menunggu_persetujuan/disetujui/ditolak), **`detail_json`** (field khusus per jenis: menimbang/mengingat/**`dictum_list` array**/tembusan/pegawai/peserta_list/…), `approved_by`, `approved_at`, `catatan_revisi` | `no_surat` (unique nullable), `tujuan`, `perihal`, `tgl_pelaksanaan`, `waktu`, `tempat`, `acara`, `isi_surat`, `lampiran_path`, `referensi_surat_masuk_id`, `status`, `created_by`, `approved_by`, `approved_at`, `catatan_revisi` |
| Controller | `SuratDinasController`: index, createSk, createSpt, store (status awal selalu `menunggu_persetujuan`), show, cetakPdf (gate `disetujui`), setujui, revisi — **edit/update belum ada** | `SuratKeluarController`: resource + approve + status + cetakPdf |
| Route | prefix `surat-dinas`, name `surat-dinas.`, role `tu,kepala_sekolah`; approvals also allow TU + Kepala Sekolah | `surat-keluar.*`, role `tu` |
| View | `resources/views/surat-dinas/*` (index, create-sk, create-spt, show, pdf-sk, pdf-spt) | `resources/views/surat-keluar/*` (index, create, edit, show, pdf) — **view era SK/SPT sudah dihapus** |

- Route `GET /surat-keluar/create/{kategori?}`: **param opsional wajib dipertahankan** (link lama di index/show/surat-masuk memanggil `route('surat-keluar.create')` tanpa param → `MissingRequiredParametersException` bila diwajibkan). Dengan `SK`/`SPT` di-redirect ke `surat-dinas.create-sk`/`create-spt`; nilai lain redirect balik ke form dengan error.
- Menambah field Surat Keluar: update array validasi di `store()` **dan** allow-list implisit di `update()` (`$validated` hanya berisi field yang divalidasi, jadi cukup tambahkan di validate array `update()`), lalu form + `pdf.blade.php`.
- Field khusus SK/SPT disimpan sebagai array cast di `detail_json` (`SuratDinasController::detailFrom()` membuang baris peserta **dan** baris diktum kosong; SK wajib ≥1 diktum terisi). Form `create-spt` mengirim `peserta_list` sebagai array input nyata (`peserta_list[i][nama|kelas]`); form `create-sk` mengirim `dictum_list[i]` (teks bebas, Alpine `x-for` + tombol "+ Tambah Diktum", label KESATU/KEDUA/… dihitung `SuratDinas::labelDiktum($index)` — 0-based, di atas 20 jatuh `KE-{n}`).

## Modul Data Siswa & Sertifikat Massal

Prefix `/data-siswa`, name `data-siswa.`, role `tu,humas,waka_humas` (other former admin features are available to TU). Table `data_siswas`, model `DataSiswa`, status string `Pending`/`Selesai`/`Gagal` (konstanta `STATUS_*`), `tanggal` cast date. Test: `tests/Feature/DataSiswaTest.php`.

- Import: `App\Services\StudentImportParser` baca `.xlsx/.xls/.csv` (reader dipilih dari ekstensi file). Header baris 1 dipetakan longgar: `NISN`→`nisn`, `Nama Lengkap`→`nama`, `Peran/Penghargaan`→`peran`, `Tanggal`→`tanggal`; NISN & Nama wajib. Import ulang by `nisn` = update identitas, **status sertifikat tidak di-reset**.
- **Namespace PhpSpreadsheet = `PhpOffice\PhpSpreadsheet\...`, bukan `PhpSpreadsheet\...`** (runtime dep baru `phpoffice/phpspreadsheet` ^5.10). Upload PHP bernama `phpXXXX.tmp` tanpa ekstensi → file disalin dulu ke temp berekstensi asli, kalau tidak reader gagal kenali format. Rule upload `extensions:xls,xlsx,csv` + `max:2048` (php.ini `upload_max_filesize=2M`).
- Sertifikat: `App\Services\CertificateGenerator` (GD) baca aset `image/certificate/{background,frame,overlay}.png` + `font.ttf`/`font-bold.ttf` (disalin dari dompdf DejaVu); aset hilang dibuat otomatis oleh `CertificateAssetFactory::ensure()`. Output PNG ke `storage/app/public/certificates/{nisn}-{slug}.png` (disk `public`, `asset('storage/...')`; symlink `public/storage` sudah ada).
- `generate` (per baris) maupun `generate-all` me-render **semua** baris termasuk yang sudah `Selesai`; gagal → status `Gagal` + `report()`. Status jadi `Selesai` hanya setelah PNG benar-benar tertulis.
- GD: fungsi ketebalan garis adalah **`imagesetthickness()`** — `imagethickness()` tidak ada di PHP ini.
- `destroy` tetap hapus baris saja (file PNG dibiarkan), konsisten dengan konvensi repo.
- Views `resources/views/data-siswa/{index,import}.blade.php`, menu "Data Siswa" di `layouts/sim.blade.php`. Setelah menambah class Tailwind baru, jalankan `npm run build` (build terakhir sudah menyertakan modul ini).

## Frontend / build quirks

- Tailwind is **v3** (postcss.config.js + tailwind.config.js + `@tailwind` directives in `resources/css/app.css`). `@tailwindcss/vite` v4 sits unused in package.json and is not registered in `vite.config.js` — do not write v4 syntax (`@import "tailwindcss"`).
- Breeze + Alpine.js; no SPA.
- **Layout utama hanya `resources/views/layouts/sim.blade.php`** (semua halaman app memakai `@extends('layouts.sim')`, termasuk profil); `layouts/guest.blade.php` untuk halaman auth. `layouts/navigation.blade.php`, `layouts/app.blade.php` dan komponen `AppLayout` sudah **dihapus** (hanya dipakai profil era Breeze).
- PDFs via `barryvdh/laravel-dompdf` with `isRemoteEnabled` and `ini_set('memory_limit', '256M')` in the controllers (`SuratKeluarController::cetakPdf`, `SuratDinasController::cetakPdf`, `SertifikatController::cetakPdf`).

## Environment notes

- Stack versions: Laravel 13.32, PHP 8.3.31 (XAMPP `php.ini`), Vite 8, PHPUnit 12, Node 24. No `.github/` CI, no `pint.json`, no `opencode.json`.
- `CLAUDE.md` is untouched Laravel Boost bootstrap boilerplate (`laravel/boost` is **not** installed) — it tells agents to run `composer require laravel/boost`; ignore it, this file is the real guide. `README.md` is stock Laravel.
- Cetak PDF needs extensi PHP **GD** (dompdf `addPngFromFile` for logo kop) — present in this PHP build (`extension=gd`). Tanpa GD, semua route cetak PDF balik 500 `The PHP GD extension is required`.
- Git repo root is `C:\Users\HP` (home dir, no commits yet), so `git status`/`git diff` are noisy and show unrelated files — inspect paths before trusting them.
