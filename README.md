# SIM Sekolah — SMKN 4 Tanjungpinang

Sistem Informasi Manajemen Sekolah berbasis web untuk administrasi surat masuk, surat keluar, surat dinas, disposisi, kerja sama industri, Praktik Kerja Lapangan (PKL), dan sertifikat siswa. Tampilan aplikasi menggunakan bahasa Indonesia dengan akses berdasarkan peran pengguna.

Dokumentasi ini mengikuti implementasi rute, controller, model, dan seeder dalam proyek. Email dan password di bawah adalah akun bawaan seeder untuk mencoba aplikasi, bukan daftar akun pada server produksi.

## Daftar isi

- [Fitur utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Instalasi](#instalasi)
- [Email dan password setiap role](#email-dan-password-setiap-role)
- [Hak akses pengguna](#hak-akses-pengguna)
- [Alur kerja fitur](#alur-kerja-fitur)
- [Format impor siswa](#format-impor-siswa)
- [Penomoran dokumen](#penomoran-dokumen)
- [Halaman aplikasi](#halaman-aplikasi)
- [Struktur proyek](#struktur-proyek)
- [Pengembangan dan pengujian](#pengembangan-dan-pengujian)
- [Pemecahan masalah](#pemecahan-masalah)
- [Catatan implementasi](#catatan-implementasi)

## Fitur utama

| Fitur | Fungsi |
|---|---|
| Halaman depan dan autentikasi | Informasi sekolah, login, registrasi, logout, dan pemulihan password. |
| Dashboard | Ringkasan data dan pekerjaan sesuai peran. |
| Surat masuk | Pencatatan, lampiran, pencarian, penerusan, dan pemantauan tindak lanjut. |
| Disposisi | Instruksi Kepala Sekolah, penugasan penerima, lembar disposisi, progres, dan arsip. |
| Surat keluar | Surat undangan dan rekomendasi mutasi siswa, pemeriksaan, persetujuan, PDF, serta status pengiriman. |
| Surat dinas | Surat Keputusan (SK) dan Surat Perintah Tugas (SPT) dalam modul tersendiri. |
| Pemeriksaan Kepala TU | Antrean gabungan surat keluar dan surat dinas sebelum persetujuan Kepala Sekolah. |
| Laporan surat | Rekap surat masuk/keluar/dinas dan disposisi dengan filter tanggal serta PDF. |
| Kerja sama industri | Data mitra, MoU, persetujuan Humas, masa kerja sama, dan agenda industri. |
| Data siswa dan PKL | Agenda/periode PKL, impor Excel/CSV, pengelompokan kelas, serta pemrosesan PKL. |
| Sertifikat PKL | Verifikasi, penerbitan, pengelolaan sertifikat, dan PDF satuan/per kelas. |
| Notifikasi | Informasi pekerjaan dan administrasi berdasarkan data aplikasi. |
| Pengguna dan profil | Pengelolaan akun oleh TU serta perubahan profil/password pribadi. |

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | PHP 8.3 dan Laravel 13 |
| Frontend | Blade, Tailwind CSS 3, Alpine.js |
| Autentikasi | Laravel Breeze |
| Database bawaan | SQLite |
| Build aset | Vite 8, Node.js, npm |
| PDF | `barryvdh/laravel-dompdf` |
| Impor Excel/CSV | `phpoffice/phpspreadsheet` |
| Pengujian dan format | PHPUnit 12, Laravel Pint |

Siapkan Git, Composer, PHP, Node.js, dan npm. Node.js 24 dapat digunakan untuk lingkungan pengembangan proyek ini. Aktifkan ekstensi PHP yang dibutuhkan dependensi, khususnya PDO SQLite/SQLite, GD, ZIP, XML/DOM, dan Mbstring. GD dibutuhkan untuk gambar dalam PDF.

Versi paket terpasang mengikuti `composer.lock` dan `package-lock.json`. Tailwind yang digunakan aplikasi adalah versi 3 melalui PostCSS.

## Instalasi

### 1. Ambil proyek dan pasang dependensi

```bash
git clone <URL_REPOSITORY>
cd <FOLDER_REPOSITORY>
composer install
```

Ganti placeholder dengan URL Git dan folder proyek. Jalankan seluruh perintah dari folder yang berisi `artisan`.

### 2. Konfigurasi awal

Untuk instalasi baru di Windows PowerShell:

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File -Path database/database.sqlite
}
php artisan key:generate
```

Alternatif Linux/macOS:

```bash
test -f .env || cp .env.example .env
touch database/database.sqlite
php artisan key:generate
```

Konfigurasi utama untuk pengembangan lokal:

```dotenv
APP_NAME="SIM Sekolah SMKN 4 Tanjungpinang"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Tanpa pengaturan `DB_DATABASE` tambahan, SQLite menggunakan `database/database.sqlite`. Sesi, antrean, dan cache berbasis database membutuhkan tabel hasil migrasi.

### 3. Migrasi, data awal, dan storage

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

Seeder membuat akun bawaan serta contoh agenda, mitra industri, surat masuk/keluar, disposisi, template, dan sertifikat. Jika `public/storage` sudah ada, tidak perlu membuat tautannya ulang.

Seeder mencari data yang sudah ada sebelum membuat data baru. Menjalankannya ulang juga memperbarui akun bawaan, mengaktifkannya, dan mengembalikan password akun tersebut menjadi `password123`.

### 4. Build dan jalankan

```bash
npm install
npm run build
php artisan serve
```

Buka **http://localhost:8000** dan login melalui **http://localhost:8000/login**. `.npmrc` proyek mengatur `ignore-scripts=true`.

Untuk pengembangan dengan pembaruan aset otomatis:

```bash
composer run dev
```

Perintah tersebut memanggil `php artisan dev` untuk menjalankan server, pendengar antrean, dan Vite. Alternatifnya, jalankan `php artisan serve` dan `npm run dev` pada terminal terpisah.

### Setup singkat

Setelah file SQLite tersedia, instalasi baru dapat menggunakan:

```bash
composer run setup
php artisan db:seed
php artisan storage:link
php artisan serve
```

`composer run setup` memasang dependensi, menyiapkan `.env` jika belum ada, membuat application key, menjalankan migrasi, dan membangun frontend. Seeder dan tautan storage dijalankan terpisah.

## Email dan password setiap role

Akun berikut tersedia setelah `php artisan db:seed`. Sumber: [DatabaseSeeder](database/seeders/DatabaseSeeder.php) dan [KepalaTUSeeder](database/seeders/KepalaTUSeeder.php).

| Peran | Kode role | Email | Password bawaan |
|---|---|---|---|
| Kepala Sekolah | `kepala_sekolah` | `kepsek@smkn4.com` | `password123` |
| Kepala Tata Usaha | `kepala_tu` | `kepala.tu@smkn4.com` | `password123` |
| Tata Usaha / Koordinator TU | `tu` | `tu@smkn4.com` | `password123` |
| Humas dan Hubungan Industri | `humas` | `humas@smkn4.com` | `password123` |
| Waka Kurikulum | `waka_kurikulum` | `kurikulum@smkn4.com` | `password123` |
| Waka Kesiswaan | `waka_kesiswaan` | `wakakesiswaan@smkn4.com` | `password123` |
| Waka Sarana Prasarana | `waka_sarpras` | `wakasarpras@smkn4.com` | `password123` |
| Waka Humas | `waka_humas` | `wakahumas@smkn4.com` | `password123` |
| Pembina OSIS | `pembina_osis` | `pembina.osis@smkn4.com` | `password123` |
| Bendahara Sekolah/BOS | `bendahara_bos` | `bendahara.bos@smkn4.com` | `password123` |
| Bendahara SPP | `bendahara_spp` | `bendahara.spp@smkn4.com` | `password123` |
| Siswa | `siswa` | Belum tersedia di seeder; ditentukan saat membuat akun | Ditentukan saat membuat akun |

Untuk mencoba role siswa, daftar melalui `/register` atau buat akun melalui pengelolaan pengguna menggunakan akun TU. Registrasi publik menghasilkan pengguna aktif dengan role `siswa`. Tidak ada akun admin atau guru bawaan yang aktif.

## Hak akses pengguna

Semua role aktif dapat membuka dashboard, profil, dan notifikasi. Hak akses berikut mengacu pada [rute aplikasi](routes/web.php), middleware, dan pemeriksaan controller.

| Peran | Akses dan tanggung jawab khusus |
|---|---|
| Kepala Sekolah | Membaca surat masuk, mengambil keputusan surat yang diteruskan TU, menerbitkan lembar disposisi, memantau disposisi, menyetujui/merevisi surat keluar dan dinas. |
| Kepala TU | Membaca dan memeriksa surat keluar/dinas dalam antrean gabungan, menyelesaikan pemeriksaan atau mengembalikan revisi. |
| TU | Mengelola surat masuk/keluar/dinas, meneruskan surat, memantau disposisi, memproses disposisi untuk akunnya, membuat laporan, mengelola pengguna, mitra, agenda industri, siswa, dan sertifikat. |
| Humas | Mengelola/menyetujui mitra, agenda industri, agenda PKL, siswa, dan sertifikat. Rute disposisi tersedia dengan pemeriksaan kepemilikan data. |
| Waka Humas | Akses industri/PKL seperti Humas, serta menerima dan memproses disposisi. |
| Waka Kurikulum, Waka Kesiswaan, Waka Sarpras, Pembina OSIS, Bendahara BOS, Bendahara SPP | Membaca surat yang didisposisikan kepada akunnya, mengunduh dokumen terkait, memberi tanggapan, memperbarui progres, dan melihat arsip terkait. |
| Siswa | Dashboard, profil, dan notifikasi; belum memiliki akses langsung ke modul data siswa atau sertifikat. |

Tujuan disposisi baru tersedia untuk delapan role: Waka Kurikulum, Waka Kesiswaan, Waka Sarpras, Waka Humas, Pembina OSIS, TU, Bendahara BOS, dan Bendahara SPP. `humas` dan `waka_humas` merupakan dua role berbeda.

Akun tidak aktif dikeluarkan dari sesi saat mengakses rute dengan middleware role. Persetujuan akhir surat dilakukan oleh Kepala Sekolah.

## Alur kerja fitur

### 1. Autentikasi dan profil

1. Buka login dan masukkan email/password akun aktif.
2. Setelah masuk, gunakan menu sesuai role.
3. Buka **Profil** untuk memperbarui nama/email atau mengganti password menggunakan password saat ini.
4. Fitur penghapusan akun pada profil memerlukan password saat ini.
5. Gunakan **Logout** untuk mengakhiri sesi.

Registrasi `/register` menghasilkan role siswa. Rute aplikasi saat ini tidak mewajibkan verifikasi email. Pemulihan password mengikuti konfigurasi mail; `.env.example` memakai `MAIL_MAILER=log`, sehingga pesan pengembangan dicatat ke log.

### 2. Dashboard

Dashboard menampilkan ringkasan administrasi dan pekerjaan. Statistik persuratan mengikuti peran: TU/Kepala Sekolah memperoleh ringkasan administrasi surat, penerima memperoleh ringkasan disposisi terkait, dan Kepala TU memperoleh jumlah surat menunggu pemeriksaan. Gunakan kartu/menu yang tersedia untuk membuka pekerjaan.

### 3. Surat masuk

**Pelaksana: TU. Pengambil keputusan: Kepala Sekolah.**

1. TU menambahkan nomor surat asal, tanggal surat, tanggal diterima, pengirim, dan perihal.
2. Tambahkan keterangan/tindak lanjut dan lampiran bila tersedia.
3. Sistem membuat nomor agenda dan menyimpan status awal `diterima`.
4. TU membuka detail lalu meneruskan kepada Kepala Sekolah. Sistem mencatat waktu/pengguna penerus dan mengubah status menjadi `diproses`.
5. Kepala Sekolah membuka antrean surat yang sudah diteruskan.
6. Kepala Sekolah menerbitkan disposisi atau menolak surat. Penerbitan disposisi menghasilkan status `didisposisi` dan keputusan `setuju`; penolakan mencatat keputusan `tidak_setuju`.
7. TU memantau detail dan riwayat tindak lanjut. Pencarian mendukung nomor surat/agenda, pengirim, dan perihal, dengan filter status/sifat.

Lampiran menerima PDF, JPG/JPEG, PNG, DOC, atau DOCX dengan batas validasi 10 MB. TU dan Kepala Sekolah dapat membaca surat masuk; penerima hanya dapat membuka surat/lampiran yang terkait disposisinya.

### 4. Disposisi dan arsip

```text
TU mencatat surat → TU meneruskan kepada Kepala Sekolah
  → Kepala Sekolah memilih penerima dan instruksi
  → Penerima menanggapi dan menindaklanjuti
  → Semua penerima selesai → Surat masuk selesai
```

1. Kepala Sekolah membuka surat yang telah diteruskan dan belum memperoleh keputusan/disposisi.
2. Pilih tujuan disposisi, instruksi/harapan, sifat surat, dan catatan.
3. Sistem membuat penugasan kepada akun aktif pada role tujuan. Satu surat dapat memiliki beberapa penerima.
4. Penerima membuka antrean miliknya, membaca surat/instruksi, lalu memberi tanggapan atau memperbarui progres.
5. Status pekerjaan adalah `menunggu`, `diproses`, dan `selesai`. Keputusan persetujuan disimpan terpisah dari status pekerjaan.
6. Hanya penerima yang dituju dapat memberi tanggapan dan mengubah progres penugasannya. TU/Kepala Sekolah dapat memantau detail.
7. Status surat masuk diselaraskan dengan seluruh disposisi; surat selesai ketika semua penugasan selesai.
8. Gunakan arsip untuk menelusuri riwayat serta unduh surat/lembar disposisi sesuai akses.

Pengisian dan penerbitan lembar disposisi dilakukan Kepala Sekolah. Penghapusan disposisi juga dibatasi kepada Kepala Sekolah.

### 5. Surat keluar: undangan dan rekomendasi mutasi

```text
TU membuat draf/pengajuan → Kepala TU memeriksa
  → TU menerima hasil pemeriksaan → TU meneruskan
  → Kepala Sekolah menyetujui/meminta revisi
  → TU mencetak, mencatat pengiriman, dan mengarsipkan
```

1. TU memilih jenis surat dan mengisi form. Kop serta bagian tetap dokumen disusun dari template.
2. Nomor surat dapat diisi manual secara unik atau dikosongkan untuk dibuat saat persetujuan.
3. Simpan sebagai `draft` atau ajukan sebagai `menunggu_persetujuan`.
4. Kepala TU memeriksa. Jika perlu revisi, catatan diisi dan surat dikembalikan dengan status `ditolak`.
5. TU memperbaiki lalu mengajukan ulang untuk pemeriksaan kembali.
6. Jika pemeriksaan selesai, TU memilih **Teruskan ke Kepala Sekolah**. Pemeriksaan selesai tidak otomatis meneruskan surat.
7. Kepala Sekolah menyetujui atau meminta revisi. Persetujuan mencatat penyetuju, waktu, dan nomor jika kosong.
8. TU membuka PDF, menandai surat `dikirim`, kemudian `diarsipkan`. Status pengiriman adalah pencatatan; tidak mengirim email secara otomatis.

| Jenis | Data yang disiapkan |
|---|---|
| Undangan | Tanggal surat, tahun pelajaran, penerima/alamat, kegiatan, keperluan, tanggal pelaksanaan, waktu, dan tempat. |
| Mutasi siswa | Identitas siswa, NISN, kelas/semester, tempat/tanggal lahir, data orang tua, sekolah asal/alamat, sekolah tujuan, alasan mutasi, dan keterangan dokumen lampiran. |

Surat dapat merujuk surat masuk. Form edit menerima status `draft`, `ditolak`, atau `minta_revisi`. Pengajuan ulang menghapus penanda pemeriksaan/penerusan sebelumnya agar kembali melalui Kepala TU.

### 6. Surat dinas: SK dan SPT

SK/SPT memiliki modul, tabel, dan template tersendiri. Tautan lama pembuatan SK/SPT melalui surat keluar diarahkan ke Surat Dinas.

| Jenis | Isi utama |
|---|---|
| Surat Keputusan | Judul, tempat/tanggal penetapan, tahun pelajaran/tanggal berlaku sesuai form, menimbang, mengingat, diktum, dan tembusan. Minimal satu diktum terisi. |
| Surat Perintah Tugas | Dasar, pegawai, pangkat/golongan, NIP, jabatan, uraian tugas, lokasi, tanggal pelaksanaan, dan daftar peserta sesuai form. |

1. TU memilih **Buat SK** atau **Buat SPT** dan mengisi data; nomor boleh kosong atau manual secara unik.
2. Sistem menyimpan pengajuan `menunggu_persetujuan`; data khusus jenis surat disimpan dalam `detail_json`.
3. Kepala TU memeriksa pada antrean gabungan. Revisi wajib disertai catatan.
4. Surat yang dikembalikan Kepala TU dapat diedit TU dan diajukan ulang.
5. Setelah pemeriksaan selesai, TU meneruskan kepada Kepala Sekolah.
6. Kepala Sekolah menyetujui (`disetujui`) atau meminta revisi (`ditolak`, catatan wajib).
7. TU mencetak PDF setelah disetujui. Endpoint PDF menolak surat dinas yang belum disetujui.

Saat ini edit surat dinas hanya tersedia untuk pengembalian dari Kepala TU sebelum diteruskan kepada Kepala Sekolah. Pengembalian dari Kepala Sekolah belum memiliki alur edit ulang yang sama.

### 7. Pemeriksaan Kepala TU

1. Login dengan `kepala.tu@smkn4.com`.
2. Buka **Pemeriksaan Kepala TU**, yang menggabungkan surat keluar/dinas menunggu dan belum diperiksa.
3. Buka detail dan periksa kelengkapan.
4. Selesaikan pemeriksaan atau kembalikan revisi dengan catatan.
5. TU melaksanakan perbaikan atau penerusan kepada Kepala Sekolah.

Pemeriksaan administrasi Kepala TU merupakan tahap sebelum persetujuan akhir Kepala Sekolah.

### 8. Laporan surat

**Akses: TU.**

1. Buka **Laporan Surat**.
2. Pilih surat masuk, surat keluar, surat dinas, dan/atau disposisi.
3. Tentukan tanggal mulai/selesai bila diperlukan.
4. Periksa rekap lalu cetak PDF A4 landscape.

Filter memakai tanggal diterima untuk surat masuk, tanggal pelaksanaan untuk surat keluar, tanggal penetapan untuk surat dinas, dan tanggal pembuatan untuk disposisi. Tanpa pilihan khusus, laporan mencakup keempat jenis.

### 9. Kerja sama industri dan MoU

**Akses: TU, Humas, Waka Humas.**

1. Tambahkan nama industri, alamat, kontak/email, tanggal mulai/berakhir, status, dan catatan.
2. Unggah MoU bila tersedia: PDF/DOC/DOCX maksimal 10 MB menurut validasi aplikasi.
3. Data buatan TU berstatus persetujuan `pending` dan menunggu Humas/Waka Humas.
4. Humas/Waka Humas menyetujui pengajuan. Data yang dibuat keduanya langsung memiliki persetujuan `approved`.
5. Cari/filter mitra berdasarkan nama/alamat, status, persetujuan, atau tahun.
6. Perubahan data oleh TU mengembalikan persetujuan ke `pending` untuk diperiksa ulang.

Status masa kerja sama (`aktif`, `akan_berakhir`, `berakhir`) terpisah dari status persetujuan (`pending`, `approved`).

### 10. Agenda industri

1. TU/Humas/Waka Humas membuka detail mitra dan menambahkan agenda.
2. Isi judul, deskripsi, tanggal mulai/selesai, waktu, lokasi, dan jenis.
3. Pilih `rapat`, `kegiatan`, `deadline`, atau `lainnya`; tandai penting bila diperlukan.
4. Simpan; gunakan detail/edit untuk memperbarui atau menghapus agenda.

Agenda terikat pada mitra pemiliknya. Rute saat ini tidak menyediakan modul `/agenda` umum untuk semua role. Agenda PKL pada Data Siswa merupakan fitur periode tersendiri.

### 11. Data siswa dan agenda PKL

**Akses: TU, Humas, Waka Humas.**

1. Buka **Data Siswa** lalu buat agenda/periode PKL.
2. Isi nama, deskripsi bila diperlukan, serta tanggal mulai/selesai.
3. Buka agenda dari arsip untuk melihat siswa pada periode tersebut.
4. Impor file Excel/CSV melalui agenda terpilih; lihat format di bawah.
5. Periksa ringkasan data baru, diperbarui, dan dilewati.
6. Cari berdasarkan NISN, nama, kelas, industri, atau peran. Pilih kelas untuk aksi massal.

Impor ulang mencari data berdasarkan **NISN dan periode**, memperbarui identitas tanpa mereset status sertifikat pada data siswa. Baris tanpa NISN/nama dilewati. Industri wajib terisi sebelum proses PKL.

Menghapus satu siswa menghapus baris data siswa tersebut. Menghapus agenda PKL menghapus **agenda, semua siswa, dan sertifikat terkait periode itu** dalam satu transaksi.

### 12. Proses PKL dan sertifikat per kelas

```text
Buat agenda → Impor siswa → Pilih kelas/program keahlian
  → Siapkan PKL (draft) → Selesaikan PKL (terverifikasi)
  → Terbitkan (diterbitkan) → Unduh PDF kelas
```

1. Buka agenda dan pilih kelas.
2. Pilih program keahlian: `RPL`, `DKV`, `TKJ`, `PSPT`, `Animasi`, atau `Pengembangan Gim`.
3. Siapkan PKL per siswa/kelas. Sistem membuat atau memperbarui sertifikat `draft`, memakai industri dari impor dan tanggal dari agenda.
4. Setelah kegiatan selesai, tandai PKL selesai per siswa/kelas sehingga sertifikat menjadi `terverifikasi`.
5. Terbitkan sertifikat kelas. Hanya sertifikat terverifikasi pada kelas dan periode terpilih yang diproses.
6. Sistem mencatat nomor jika belum ada, pengguna penerbit, dan waktu penerbitan.
7. Unduh PDF kelas, berisi sertifikat `diterbitkan` dengan urutan nama siswa.

Aksi kelas memakai kombinasi kelas dan periode. Persiapan kelas melewati sertifikat terbit; menjalankannya ulang pada sertifikat terverifikasi dapat mengembalikan data itu ke `draft`.

### 13. Sertifikat PKL satuan

1. Buka **Sertifikat** dan cari nama, NISN, nomor, kelas, atau industri.
2. Periksa identitas, program keahlian, industri, periode, nilai/predikat, dan status pada detail.
3. Edit untuk memperbaiki data. Form edit meminta mitra industri terdaftar.
4. Melalui aksi status, verifikasi `draft → terverifikasi`, kemudian terbitkan `terverifikasi → diterbitkan`.
5. Buka PDF satuan untuk melihat/mencetak sertifikat.

Data baru dibuat melalui alur Data Siswa/PKL; rute pembuatan sertifikat manual tidak dibuka. PDF kelas hanya mengambil sertifikat terbit, sedangkan endpoint PDF satuan saat ini tidak membatasi status.

### 14. Notifikasi

Halaman notifikasi menyusun informasi surat keluar menunggu dan surat masuk baru untuk TU/Kepala Sekolah, disposisi menunggu bagi penerima, serta mitra berstatus `akan_berakhir` bagi peran terkait. Informasi diurutkan dari waktu terbaru.

Data dihitung saat halaman dibuka. Controller saat ini tidak menyediakan tandai sudah dibaca atau pengiriman push/email otomatis. Akses tujuan tautan tetap mengikuti role.

### 15. Pengelolaan pengguna

**Akses: TU, melalui Pengaturan → Kelola User.**

1. Cari pengguna berdasarkan nama, email, NISN, atau filter role.
2. Buat akun dengan nama, email unik, password/konfirmasi, role, dan status aktif.
3. Isi NISN, kelas, dan program keahlian bila relevan.
4. Password minimal delapan karakter. Saat edit, kosongkan password untuk mempertahankannya.
5. Aktifkan/nonaktifkan atau hapus akun sesuai kebutuhan. Penghapusan akun sendiri melalui menu pengguna ditolak.

Email domain lama `@smkn4tanjungpinang.sch.id` yang dimasukkan melalui pengelolaan pengguna dinormalisasi menjadi `@smkn4.com`.

## Format impor siswa

File `.xlsx`, `.xls`, atau `.csv` maksimal **2 MB**. Baris pertama wajib memiliki header **NIS/NISN, Nama Lengkap, Kelas, dan Industri**. Parser membaca sheet aktif.

| NISN | Nama Lengkap | Kelas | Industri | Peran/Penghargaan | Tanggal |
|---|---|---|---|---|---|
| 0012345678 | Siswa Contoh A | XII RPL 1 | PT Contoh Digital | Peserta PKL | 2026-07-01 |
| 0012345679 | Siswa Contoh B | XII RPL 1 | CV Contoh Teknologi | Peserta PKL | 2026-07-01 |

Peran dan tanggal opsional. Simpan NISN sebagai teks agar nol di depan terjaga. Beberapa variasi header dikenali, misalnya `NIS`, `Rombel`, `Tempat PKL`, dan `Perusahaan`. Impor harus dilakukan dari agenda yang sudah dibuat.

## Penomoran dokumen

| Dokumen | Pola otomatis | Waktu pemberian |
|---|---|---|
| Agenda surat masuk | `AGD/MM/YYYY/NNNN` | Saat pencatatan surat. |
| Undangan | `421.5/SMKN4/MM/YYYY/NNNN` | Persetujuan jika nomor kosong. |
| Mutasi siswa | `421.5.2/SMKN4/MM/YYYY/NNNN` | Persetujuan jika nomor kosong. |
| SK | `421.5/SMKN4/MM/YYYY/NNNN` | Persetujuan jika nomor kosong. |
| SPT | `421.5.1/SMKN4/MM/YYYY/NNNN` | Persetujuan jika nomor kosong. |
| Sertifikat | `SERT/YYYY/NNNN` | Saat pembuatan data; penerbitan juga melengkapi nomor kosong pada data lama. |

`MM` adalah bulan, `YYYY` tahun, dan `NNNN` nomor urut minimal empat digit. Nomor manual yang sudah ada dipertahankan saat persetujuan. Contoh seeder dapat memakai nomor lama atau nomor dokumen fisik.

Surat keluar memakai akhiran terbesar pada prefix bulan/tahun terkait, termasuk data soft delete. Surat dinas menghitung nomor tersendiri berdasarkan prefix jenis dan bulan/tahun. Nomor kedua modul bukan satu urutan global karena tabelnya terpisah.

## Halaman aplikasi

| URL | Fungsi | Akses utama |
|---|---|---|
| `/` | Halaman depan | Publik |
| `/login`, `/register`, `/forgot-password` | Autentikasi | Tamu |
| `/dashboard`, `/profile`, `/notifikasi` | Fitur umum | Semua role aktif |
| `/surat-masuk` | Pencatatan surat masuk | TU |
| `/disposisi/antrian`, `/disposisi/arsip` | Antrean dan riwayat | Sesuai role dan kepemilikan |
| `/surat-keluar` | Pengelolaan surat keluar | TU |
| `/surat-keluar/pemeriksaan-kepala-tu` | Antrean pemeriksaan gabungan | Kepala TU |
| `/surat-dinas` | Pengelolaan SK/SPT | TU |
| `/laporan-surat` | Rekap dan cetak laporan | TU |
| `/kerja-sama` | Mitra dan agenda industri | TU, Humas, Waka Humas |
| `/data-siswa` | Arsip agenda PKL | TU, Humas, Waka Humas |
| `/data-siswa/agenda/{periode}` | Siswa dan proses PKL per agenda | TU, Humas, Waka Humas |
| `/sertifikat` | Pengelolaan sertifikat | TU, Humas, Waka Humas |
| `/pengaturan/users` | Pengelolaan pengguna | TU |

Detail dokumen dan aksi keputusan memiliki pemeriksaan akses tambahan. Gunakan `php artisan route:list` untuk daftar lengkap.

## Struktur proyek

```text
app/
  Http/Controllers/       # Alur permintaan setiap modul
  Http/Middleware/        # Pembatasan akses role
  Models/                 # Data, relasi, dan penomoran
  Services/               # Pembaca impor dan pembuat dokumen
database/
  migrations/             # Struktur/perubahan database
  factories/              # Data pengujian
  seeders/                # Akun bawaan dan data contoh
resources/
  views/                  # Halaman Blade dan template PDF
  css/                    # Tailwind
  js/                     # JavaScript frontend
routes/
  web.php                 # Rute fitur dan role
  auth.php                # Rute autentikasi
public/                   # Aset publik dan hasil build
storage/app/public/       # Unggahan disk public
tests/                    # Pengujian fitur dan unit
```

Layout utama adalah `resources/views/layouts/sim.blade.php`; autentikasi memakai layout guest. PDF sertifikat memakai `PklCertificatePdf`, impor siswa memakai `StudentImportParser`.

Relasi utama: surat masuk memiliki disposisi ke pengguna dan dapat dirujuk surat keluar; mitra memiliki agenda industri; periode PKL memiliki data siswa dan sertifikat. Impor siswa merupakan pencatatan administrasi dan tidak otomatis membuat akun login.

## Pengembangan dan pengujian

| Perintah | Kegunaan |
|---|---|
| `composer run setup` | Dependensi, konfigurasi awal, migrasi, build. |
| `composer run dev` / `php artisan dev` | Lingkungan pengembangan. |
| `php artisan serve` | Server lokal Laravel. |
| `npm run dev` / `npm run build` | Pengembangan/build frontend. |
| `php artisan migrate` | Migrasi database. |
| `php artisan db:seed` | Akun bawaan dan data contoh. |
| `php artisan storage:link` | Tautan storage publik. |
| `php artisan route:list` | Daftar endpoint. |
| `php artisan test` | Semua pengujian. |
| `composer test` | Bersihkan cache konfigurasi lalu tes. |
| `php artisan test --filter=DataSiswaTest` | Satu kelompok tes. |
| `vendor/bin/pint --test app/Http/Controllers/NamaController.php` | Periksa format file PHP tertentu. |

Di Windows, Pint juga dapat dipanggil dengan `vendor\bin\pint`. Tidak ada `php artisan pint`. Batasi format otomatis pada file yang dikerjakan karena format seluruh repository belum seragam.

Tes memakai SQLite dalam memori, sesi `array`, dan antrean `sync`. Password factory tes adalah `password`; password akun seeder adalah `password123`. Tes yang tersedia mencakup autentikasi, disposisi/role, persetujuan surat, PDF, laporan, data siswa, agenda industri, dashboard, dan pengguna. Jalankan tes untuk mengetahui hasil/jumlah pada versi yang digunakan.

## Pemecahan masalah

| Gejala | Tindakan |
|---|---|
| Akun demo gagal login | Pastikan seeder dijalankan pada database aplikasi; periksa email, password, dan status aktif. |
| SQLite tidak ditemukan | Buat `database/database.sqlite`, periksa koneksi, jalankan migrasi. |
| Tabel sesi/cache belum ada | Jalankan `php artisan migrate`. |
| Application key kosong | Pada instalasi baru, jalankan `php artisan key:generate`. |
| Manifest Vite hilang/CSS belum berubah | Jalankan `npm install` dan `npm run build`, atau aktifkan Vite saat pengembangan. |
| PDF gagal terkait GD | Aktifkan GD pada PHP server/CLI lalu mulai ulang server. |
| Lampiran publik tidak terbuka | Periksa berkas storage dan tautan `public/storage`. |
| Impor ditolak | Pilih agenda, pastikan ekstensi/ukuran benar dan empat header wajib tersedia. |
| Persiapan PKL gagal | Lengkapi industri melalui impor ulang pada periode yang sama. |
| PDF kelas kosong | Pastikan sertifikat sudah terbit pada kelas dan periode terpilih. |
| Surat belum masuk antrean Kepala Sekolah | Selesaikan pemeriksaan Kepala TU dan penerusan TU; surat masuk juga harus diteruskan TU. |
| Respons 403 | Periksa role, status aktif, kepemilikan data, dan syarat status dokumen. |
| Respons 409 | Tahap tidak sesuai atau keputusan sudah dilakukan; buka ulang detail terbaru. |
| Email reset tidak masuk | Default mail adalah log; periksa `storage/logs/laravel.log` atau konfigurasi email. |

Batas unggah efektif mengikuti nilai terkecil antara validasi aplikasi dan PHP (`upload_max_filesize`/`post_max_size`). Lampiran yang diizinkan 10 MB oleh aplikasi dapat gagal bila PHP hanya mengizinkan 2 MB.

## Catatan implementasi

- Dokumentasi mengikuti kode yang tersedia; beberapa catatan historis proyek dapat berbeda dari implementasi terbaru.
- Revisi surat dinas oleh Kepala Sekolah tercatat, tetapi edit ulang saat ini hanya mengizinkan pengembalian pada tahap Kepala TU.
- Edit surat keluar mendukung `minta_revisi`, tetapi form edit berfokus pada isian umum dan belum memvalidasi ulang seluruh field khusus form pembuatan undangan/mutasi.
- PDF surat dinas dibatasi setelah persetujuan. Endpoint PDF surat keluar dan sertifikat satuan belum menerapkan pembatasan status yang sama.
- Aksi status sertifikat memeriksa urutan tahap, tetapi form edit juga menerima status; pemeriksaan urutan belum seragam pada semua jalur pembaruan.
- Surat masuk, surat keluar, dan mitra memakai soft delete. Penghapusan berkas unggahan tidak otomatis dilakukan saat baris pemiliknya dihapus.
- Kode pembuat sertifikat PNG lama masih tersedia, tetapi alur Data Siswa pada rute saat ini memakai persiapan PKL, verifikasi, penerbitan, dan PDF.
- Di lingkungan agent, `laravel/pao` dapat menghasilkan output tes/Pint berupa JSON. Untuk output biasa di PowerShell, atur `$env:PAO_DISABLE='1'` sebelum menjalankan perintah.

Rujukan pengembangan: [rute](routes/web.php), [model pengguna](app/Models/User.php), [seeder](database/seeders/DatabaseSeeder.php), dan [panduan repository](AGENTS.md).
