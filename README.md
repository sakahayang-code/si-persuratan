# SI-PERSURATAN
### Sistem Informasi Persuratan Digital dan Arsip Terintegrasi
**Dokumen Konsep & Spesifikasi Fungsional Terpenuhi 100%**  
*Basis Data: SQLite Persistent (WAL) • Backend: PHP Native 8.2+ • Frontend: Bootstrap 5, Custom SaaS CSS, Lucide Icons, Chart.js • PWA: Service Worker, Web App Manifest & IndexedDB Offline Queue*

---

## 📌 1. Gambaran Umum dan Prinsip Utama
**SI-PERSURATAN** dirancang untuk mengelola seluruh siklus hidup surat dan arsip instansi:
1. **Surat Masuk**: Penerimaan, registrasi agenda otomatis, scan PDF/lampiran, lembar tanda terima, lembar disposisi pimpinan, dan pemantauan tindak lanjut.
2. **Surat Keluar**: Penomoran otomatis berbasis pola klasifikasi instansi, generator surat resmi dengan 16 template baku, alur persetujuan bertingkat, tanda tangan digital, stempel resmi, dan QR Code verifikasi dokumen terbuka.
3. **Buku Agenda**: Pencatatan kronologis masuk & keluar, rekap harian/bulanan/tahunan, ekspor Excel dan PDF.
4. **Arsip Digital**: Pengelompokan tahun, klasifikasi, lokasi fisik penyimpanan, retensi berkas (aktif, inaktif, dimusnahkan), dan penanda arsip penting.
5. **Perlindungan Data (Section 8)**: Database SQLite (`persuratan.sqlite`) dan berkas dokumen (`storage/documents/`) dipisahkan secara fisik dari kode program sehingga aman dari penimpaan saat update sistem.
6. **Progressive Web App (PWA)**: Dapat diinstal langsung di Android/Desktop via browser, mendukung cache offline dan penyimpanan draf antrean di **IndexedDB** dengan sinkronisasi otomatis ke server.

---

## 👥 2. Akun Demo & Hak Akses Berbasis Peran (RBAC)
Sistem dilengkapi 6 peran pengguna baku dengan kata sandi bawaan `admin123`:

| Username | Nama Lengkap | Peran (Role) | Kewenangan Utama |
|---|---|---|---|
| `admin` | Ahmad Fauzi, S.Kom, M.M. | **Super Admin** | Akses penuh, konfigurasi instansi, kelola user, backup & restore, audit trail log |
| `admin_surat` | Siti Rahmawati, S.AP | **Admin Persuratan** | Mengelola surat masuk/keluar, buku agenda, arsip digital, template surat, laporan |
| `pimpinan` | Dr. H. Bambang Sudaryanto, M.Si | **Pimpinan (Kepala)** | Memberikan lembar disposisi, menyetujui surat keluar, memvalidasi dokumen |
| `sekretaris` | Dewi Lestari, S.Sos | **Sekretaris** | Mempersiapkan draf surat, verifikasi agenda, membantu proses persetujuan |
| `petugas` | Budi Santoso, A.Md | **Petugas** | Registrasi fisik surat masuk/keluar, upload scan PDF, penerbitan tanda terima |
| `pegawai` | Rian Pratama, S.T | **Pegawai (Staf)** | Menerima disposisi tugas, mengisi tindak lanjut, melihat arsip terbuka |

*Fitur Cepat: Klik menu profil di pojok kanan atas untuk beralih peran demo instan (Quick Role Switcher).*

---

## 🗂️ 3. Struktur Direktori dan Pemisahan Data Persisten
```text
persuratan/
├── app/
│   ├── controllers/      # Logika pengontrol alur bisnis
│   ├── models/           # User, SuratMasuk, SuratKeluar, Disposisi, TindakLanjut, Agenda, Arsip, dll
│   ├── views/            # Layout responsif (header, footer, sidebar, mobile_nav) & view modul
│   └── helpers/          # Database (PDO SQLite WAL), Auth (RBAC), BackupService, Numbering, QrCodeHelper, PdfHelper, ExcelHelper
├── config/
│   ├── app.php           # Autoloader, zona waktu Asia/Jakarta, konfigurasi sesi aman
│   └── database.php      # Konfigurasi koneksi SQLite PDO & mode WAL
├── migrations/
│   ├── 001_initial_schema.sql    # 21 tabel SQLite, foreign keys ON DELETE RESTRICT, indeks pencarian
│   └── 002_seed_initial_data.sql  # Data awal peran, hak akses, 16 template dinas, pejabat, dan sampel surat
├── public/               # Root Web Publik
│   ├── assets/           # Bootstrap 5, Chart.js, Lucide Icons, Custom SaaS CSS, PWA Icons
│   ├── surat_masuk/      # Modul Surat Masuk & Cetak Tanda Terima
│   ├── surat_keluar/     # Modul Surat Keluar, Preview & Unduh PDF
│   ├── generator/        # Generator 16 Template Surat Resmi
│   ├── disposisi/        # Modul Lembar Disposisi & Cetak
│   ├── agenda/           # Buku Agenda & Ekspor Excel/PDF
│   ├── arsip/            # Repositori Berkas Digital & Manajemen Retensi
│   ├── master/           # Klasifikasi, Pejabat & Pola Penomoran
│   ├── laporan/          # Portal Laporan & Rekapitulasi Data
│   ├── backup/           # Manajemen Backup, Restore & Integritas SQLite
│   ├── api/              # Endpoint sinkronisasi antrean offline (sync.php)
│   ├── manifest.json     # PWA Manifest Android Standalone
│   ├── service-worker.js # Cache aset & strategi Network-First
│   ├── offline.html      # Tampilan fallback saat sambungan terputus
│   ├── verifikasi.php    # Halaman publik verifikasi keabsahan QR Code
│   ├── dashboard.php     # Dashboard statistik real-time & grafik
│   └── index.php         # Entry router
└── storage/              # DIREKTORI DATA PERSISTEN (Tidak tertimpa saat update)
    ├── database/
    │   └── persuratan.sqlite   # Basis data SQLite utama
    ├── documents/
    │   ├── surat_masuk/        # Berkas PDF scan surat masuk
    │   ├── surat_keluar/       # Berkas dokumen surat keluar
    │   ├── signatures/         # Aset tanda tangan & stempel dinas
    │   └── lampiran/           # Berkas pendukung arsip
    ├── backups/                # Arsip berkas ZIP cadangan sistem
    ├── logs/                   # Log sistem internal
    └── sessions/               # Penyimpanan sesi PHP aman
```

---

## 🚀 4. Panduan Menjalankan Aplikasi
Aplikasi menggunakan server PHP bawaan (*built-in web server*) tanpa dependensi server eksternal:

```bash
cd /home/antigravity/persuratan
php -S 0.0.0.0:8000 -t public
```

Buka peramban pada: `http://localhost:8000` (atau port yang ditentukan).

---

## 🛡️ 5. Mekanisme Backup, Restore, dan Integritas (Section 8.1 & 8.2)
1. **Pembuatan Backup**:
   - Klik menu **Backup & Restore** -> tombol **Buat Backup Baru**.
   - Sistem akan mengemas basis data SQLite (`persuratan.sqlite`), seluruh dokumen scan & tanda tangan (`storage/documents/`), serta file `manifest.json` ke dalam sebuah file `.zip` terenkripsi checksum SHA-256.
   - Tercatat otomatis di tabel `backup_log`.
2. **Pemulihan (Restore)**:
   - Pilih berkas `.zip` cadangan pada menu Restore.
   - Sistem melakukan **uji kelayakan arsip** (memastikan `persuratan.sqlite` ada di dalam arsip).
   - Sistem **secara otomatis membuat cadangan darurat (*safety backup*)** dari kondisi sistem sebelum dipulihkan.
   - Berkas database dan dokumen diekstrak secara konsisten.
   - Sistem menjalankan pemeriksaan integritas SQLite:
     ```sql
     PRAGMA integrity_check;
     ```
   - Hasil integritas ditampilkan kepada Administrator dan dicatat dalam audit trail.

---

## 📱 6. PWA & Dukungan Offline (Section 10)
- **Instalasi**: Pengguna Android atau peramban desktop yang mendukung akan melihat tombol **"Pasang Aplikasi"** di bagian atas navbar.
- **Cache**: `service-worker.js` menyimpan aset statis (CSS, JS, ikon, logo) secara lokal.
- **Offline Queue**: Jika pengguna mengisi surat saat koneksi internet terputus, formulir akan otomatis menyimpan data ke antrean **IndexedDB** (`persuratan_offline_db`).
- **Sinkronisasi**: Ketika koneksi kembali menyala, bilah notifikasi kuning akan muncul dan data dikirim ke `/api/sync.php`. Data lokal di IndexedDB baru dihapus setelah server mengirimkan respons sukses `200 OK`.

---

## 🔍 7. Verifikasi QR Code Keaslian Dokumen (Section 3.8)
Setiap surat keluar yang digenerate oleh sistem dibekali dengan **Token Digital Unik** dan **QR Code**:
- Format URL Verifikasi: `http://localhost:8000/verifikasi.php?doc={TOKEN}`
- Setiap pihak penerima dapat memindai kode QR menggunakan kamera ponsel untuk membuktikan keaslian nomor surat, perihal, nama penandatangan, dan cap instansi secara langsung tanpa harus login ke sistem.
