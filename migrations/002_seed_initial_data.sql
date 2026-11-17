-- migrations/002_seed_initial_data.sql
-- Data Awal SI-PERSURATAN

-- 1. Roles
INSERT OR IGNORE INTO roles (id, name, slug, description) VALUES
(1, 'Super Admin', 'superadmin', 'Akses penuh ke seluruh konfigurasi, user, backup, dan sistem persuratan'),
(2, 'Admin Persuratan', 'admin_persuratan', 'Mengelola surat masuk, surat keluar, agenda, arsip, template, dan laporan'),
(3, 'Pimpinan', 'pimpinan', 'Melihat surat masuk/keluar, memberikan instruksi disposisi, dan menyetujui surat'),
(4, 'Sekretaris', 'sekretaris', 'Mempersiapkan draf surat, verifikasi agenda, dan koordinasi disposisi'),
(5, 'Petugas', 'petugas', 'Mencatat surat fisik, unggah scan PDF/lampiran, dan penomoran agenda'),
(6, 'Pegawai', 'pegawai', 'Menerima lembar disposisi, menindaklanjuti tugas surat, dan melihat arsip terbuka');

-- 2. Permissions
INSERT OR IGNORE INTO permissions (id, name, slug, module, description) VALUES
(1, 'Lihat Surat Masuk', 'surat_masuk.view', 'surat_masuk', 'Melihat daftar dan detail surat masuk'),
(2, 'Tambah Surat Masuk', 'surat_masuk.create', 'surat_masuk', 'Mencatat dan mengunggah surat masuk'),
(3, 'Edit Surat Masuk', 'surat_masuk.edit', 'surat_masuk', 'Mengubah data surat masuk'),
(4, 'Hapus Surat Masuk', 'surat_masuk.delete', 'surat_masuk', 'Menghapus data surat masuk (soft delete)'),
(5, 'Lihat Surat Keluar', 'surat_keluar.view', 'surat_keluar', 'Melihat daftar dan detail surat keluar'),
(6, 'Buat Surat Keluar', 'surat_keluar.create', 'surat_keluar', 'Membuat draft surat keluar atau generator'),
(7, 'Edit Surat Keluar', 'surat_keluar.edit', 'surat_keluar', 'Mengubah draf surat keluar'),
(8, 'Hapus Surat Keluar', 'surat_keluar.delete', 'surat_keluar', 'Menghapus surat keluar'),
(9, 'Setujui Surat Keluar', 'surat_keluar.approve', 'surat_keluar', 'Menyetujui dan menerbitkan surat keluar'),
(10, 'Lihat Disposisi', 'disposisi.view', 'disposisi', 'Melihat riwayat disposisi'),
(11, 'Buat Disposisi', 'disposisi.create', 'disposisi', 'Memberikan instruksi lembar disposisi'),
(12, 'Tindak Lanjut Disposisi', 'disposisi.action', 'disposisi', 'Menindaklanjuti dan menyelesaikan tugas disposisi'),
(13, 'Buku Agenda', 'agenda.view', 'agenda', 'Melihat dan mencetak buku agenda'),
(14, 'Arsip Digital', 'arsip.manage', 'arsip', 'Mengelola arsip digital, retensi dan pencarian'),
(15, 'Generator & Template', 'template.manage', 'template', 'Mengatur template surat resmi'),
(16, 'Master Data', 'master.manage', 'master', 'Mengelola klasifikasi, pejabat, dan penomoran'),
(17, 'Laporan & Statistik', 'laporan.view', 'laporan', 'Melihat dan mengekspor laporan persuratan'),
(18, 'Backup & Restore', 'backup.manage', 'backup', 'Melakukan backup arsip data dan pemulihan'),
(19, 'Manajemen Pengguna', 'users.manage', 'users', 'Mengelola akun pengguna dan hak akses'),
(20, 'Pengaturan Sistem', 'settings.manage', 'settings', 'Mengatur identitas instansi dan sistem');

-- 3. Role Permissions Mapping
-- Superadmin gets all
INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Admin Persuratan
INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES
(2, 1), (2, 2), (2, 3), (2, 5), (2, 6), (2, 7), (2, 10), (2, 12), (2, 13), (2, 14), (2, 15), (2, 16), (2, 17);

-- Pimpinan
INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES
(3, 1), (3, 5), (3, 6), (3, 9), (3, 10), (3, 11), (3, 13), (3, 14), (3, 15), (3, 17);

-- Sekretaris
INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES
(4, 1), (4, 2), (4, 3), (4, 5), (4, 6), (4, 7), (4, 10), (4, 12), (4, 13), (4, 14), (4, 15), (4, 17);

-- Petugas
INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES
(5, 1), (5, 2), (5, 5), (5, 6), (5, 10), (5, 13), (5, 14);

-- Pegawai
INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES
(6, 1), (6, 5), (6, 6), (6, 10), (6, 12), (6, 14), (6, 15);

-- 4. Initial Users (Password: admin123)
INSERT OR IGNORE INTO users (id, username, email, password_hash, full_name, nip, jabatan, phone, role_id, is_active) VALUES
(1, 'admin', 'admin@persuratan.go.id', '$2y$10$ENctaZO2gwPdLbw7n3xboeN1FBpDtpcNep4NoUZdtZdrpwfrR3eJ2', 'Ahmad Fauzi, S.Kom, M.M.', '198001012005011001', 'Super Administrator & Pranata Komputer', '081234567890', 1, 1),
(2, 'admin_surat', 'arsiparis@persuratan.go.id', '$2y$10$ENctaZO2gwPdLbw7n3xboeN1FBpDtpcNep4NoUZdtZdrpwfrR3eJ2', 'Siti Rahmawati, S.AP', '198503152009022002', 'Admin Persuratan & Arsiparis Ahli', '081234567891', 2, 1),
(3, 'pimpinan', 'kepala@persuratan.go.id', '$2y$10$ENctaZO2gwPdLbw7n3xboeN1FBpDtpcNep4NoUZdtZdrpwfrR3eJ2', 'Dr. H. Bambang Sudaryanto, M.Si', '197205121998031003', 'Kepala Instansi / Badan', '081234567892', 3, 1),
(4, 'sekretaris', 'sekretaris@persuratan.go.id', '$2y$10$ENctaZO2gwPdLbw7n3xboeN1FBpDtpcNep4NoUZdtZdrpwfrR3eJ2', 'Dewi Lestari, S.Sos', '198807202011012004', 'Sekretaris Pimpinan & Tata Usaha', '081234567893', 4, 1),
(5, 'petugas', 'petugas@persuratan.go.id', '$2y$10$ENctaZO2gwPdLbw7n3xboeN1FBpDtpcNep4NoUZdtZdrpwfrR3eJ2', 'Budi Santoso, A.Md', '199211102014021005', 'Petugas Registrasi & Front Office', '081234567894', 5, 1),
(6, 'pegawai', 'pegawai@persuratan.go.id', '$2y$10$ENctaZO2gwPdLbw7n3xboeN1FBpDtpcNep4NoUZdtZdrpwfrR3eJ2', 'Rian Pratama, S.T', '199504182019031006', 'Staf Teknis Pelaksana', '081234567895', 6, 1);

-- 5. Klasifikasi Surat Standar Kearsipan Nasional
INSERT OR IGNORE INTO klasifikasi_surat (id, kode, nama, uraian, retensi_aktif, retensi_inaktif, keterangan) VALUES
(1, '005', 'Undangan Resmi', 'Surat undangan pertemuan dinas, rapat dinas, dan upacara kenegaraan', 2, 3, 'Arsip Fasilitatif'),
(2, '010', 'Urusan Dalam', 'Pemberitahuan internal, ketertiban kantor, dan instruksi fasilitas', 2, 5, 'Arsip Fasilitatif'),
(3, '100', 'Pemerintahan', 'Kebijakan pemerintahan, tata kelola otonomi, dan perizinan', 5, 10, 'Arsip Substantif'),
(4, '420', 'Pendidikan & Pelatihan', 'Pendidikan kedinasan, bimbingan teknis, workshop, dan sertifikasi', 3, 5, 'Arsip Substantif'),
(5, '800', 'Kepegawaian Umum', 'Formasi, pengadaan, dan administrasi personil', 5, 10, 'Arsip Kepegawaian'),
(6, '820', 'Mutasi & Surat Tugas', 'Surat perintah tugas (SPT), surat perjalanan dinas, dan penugasan khusus', 2, 5, 'Arsip Kepegawaian'),
(7, '900', 'Keuangan', 'Penyusunan anggaran, pertanggungjawaban, dan perbendaharaan', 5, 10, 'Arsip Keuangan');

-- 6. Pejabat & Penandatangan
INSERT OR IGNORE INTO pejabat (id, nip, nama, jabatan, unit_kerja, signature_file, stempel_file, is_active) VALUES
(1, '197205121998031003', 'Dr. H. Bambang Sudaryanto, M.Si', 'Kepala Badan', 'Pimpinan Instansi', 'signature_kepala.png', 'stempel_resmi.png', 1),
(2, '198503152009022002', 'Siti Rahmawati, S.AP', 'Kepala Bagian Tata Usaha', 'Sekretariat', 'signature_kabag.png', 'stempel_resmi.png', 1);

-- 7. Aturan Penomoran Surat
INSERT OR IGNORE INTO nomor_surat (id, kode_klasifikasi, format_pola, counter, tahun, reset_period, last_number) VALUES
(1, '005', '{nomor}/{kode}/BPTI-XI/{tahun}', 12, 2026, 'yearly', '012/005/BPTI-XI/2026'),
(2, '820', '{nomor}/ST/{kode}/BPTI/{tahun}', 45, 2026, 'yearly', '045/ST/820/BPTI/2026'),
(3, '010', '{nomor}/SE/{kode}/BPTI/{tahun}', 8, 2026, 'yearly', '008/SE/010/BPTI/2026');

-- 8. Pengaturan Sistem
INSERT OR IGNORE INTO pengaturan (key_name, value_text, category, description) VALUES
('nama_instansi', 'BADAN PENGEMBANGAN TEKNOLOGI INFORMASI DAN ARSIP', 'identitas', 'Nama resmi instansi / lembaga'),
('sub_instansi', 'Pemerintah Provinsi DKI Jakarta', 'identitas', 'Sub-nama instansi / organisasi induk'),
('alamat_instansi', 'Jl. Medan Merdeka Barat No. 12, Gambir, Jakarta Pusat 10110', 'identitas', 'Alamat lengkap instansi'),
('telepon_instansi', '(021) 3845678 / 3845679', 'kontak', 'Nomor telepon kantor'),
('email_instansi', 'sekretariat@persuratan.go.id', 'kontak', 'Alamat email resmi'),
('website_instansi', 'https://persuratan.go.id', 'kontak', 'Website resmi'),
('logo_instansi', 'assets/img/logo-instansi.svg', 'tampilan', 'Logo instansi'),
('kop_garis_tebal', '1', 'tampilan', 'Tampilkan garis pemisah kop surat ganda'),
('auto_backup_interval', 'daily', 'keamanan', 'Interval backup otomatis sistem'),
('retensi_default_tahun', '5', 'arsip', 'Masa retensi arsip default'),
('pwa_theme_color', '#2563EB', 'pwa', 'Warna tema PWA (Primary Blue)');

-- 9. Template Surat Resmi (16 Variasi Lengkap Sesuai Dokumen Bagian 3.3)
INSERT OR IGNORE INTO template_surat (id, nama_template, jenis_surat, kode, kop_surat_html, konten_template, format_nomor, is_system) VALUES
(1, 'Surat Undangan Rapat / Dinas', 'undangan', 'TPL-UND', '', '<p>Dengan hormat,</p><p>Sehubungan dengan pelaksanaan program kerja tahun anggaran 2026, kami mengundang Bapak/Ibu untuk hadir pada rapat koordinasi yang akan dilaksanakan pada:</p><table class="table-content"><tr><td width="160"><strong>Hari / Tanggal</strong></td><td>: {hari_tanggal}</td></tr><tr><td><strong>Waktu</strong></td><td>: {waktu} WIB s.d Selesai</td></tr><tr><td><strong>Tempat</strong></td><td>: {tempat}</td></tr><tr><td><strong>Agenda</strong></td><td>: {agenda_rapat}</td></tr></table><p>Mengingat pentingnya agenda rapat ini, kami mengharapkan kehadiran tepat waktu.</p><p>Demikian surat undangan ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>', '{nomor}/005/UND/BPTI/{tahun}', 1),

(2, 'Surat Perintah Tugas (SPT)', 'surat_tugas', 'TPL-STG', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">SURAT PERINTAH TUGAS<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span></p><p>Yang bertanda tangan di bawah ini:</p><table class="table-content"><tr><td width="160"><strong>Nama</strong></td><td>: {nama_pejabat}</td></tr><tr><td><strong>NIP</strong></td><td>: {nip_pejabat}</td></tr><tr><td><strong>Jabatan</strong></td><td>: {jabatan_pejabat}</td></tr></table><p><strong>MEMERINTAHKAN:</strong></p><p>Kepada:</p><table class="table-content"><tr><td width="160"><strong>Nama Petugas</strong></td><td>: {nama_petugas}</td></tr><tr><td><strong>NIP / Jabatan</strong></td><td>: {nip_petugas} / {jabatan_petugas}</td></tr></table><p>Untuk melaksanakan tugas:</p><p style="padding-left: 20px; font-style: italic;">"{uraian_tugas}"</p><table class="table-content"><tr><td width="160"><strong>Waktu Pelaksanaan</strong></td><td>: {waktu_pelaksanaan}</td></tr><tr><td><strong>Lokasi Tujuan</strong></td><td>: {lokasi_tugas}</td></tr></table><p>Demikian Surat Tugas ini diberikan untuk dilaksanakan dengan penuh tanggung jawab serta melaporkan hasilnya setelah tugas selesai.</p>', '{nomor}/820/ST/BPTI/{tahun}', 1),

(3, 'Surat Keterangan Resmi', 'keterangan', 'TPL-KET', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">SURAT KETERANGAN<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span></p><p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p><table class="table-content"><tr><td width="160"><strong>Nama</strong></td><td>: {nama_subjek}</td></tr><tr><td><strong>NIK / NIP</strong></td><td>: {identitas_subjek}</td></tr><tr><td><strong>Jabatan / Instansi</strong></td><td>: {jabatan_instansi}</td></tr></table><p>Adalah benar yang bersangkutan {keterangan_isi}. Surat keterangan ini diterbitkan untuk dipergunakan sebagaimana mestinya.</p>', '{nomor}/010/KET/BPTI/{tahun}', 1),

(4, 'Surat Pengantar Dokumen', 'pengantar', 'TPL-PNT', '', '<p>Bersama ini kami kirimkan dokumen/berkas sebagaimana rincian berikut:</p><table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse: collapse; margin: 15px 0;"><thead><tr style="background:#f3f4f6;"><th>No</th><th>Jenis Dokumen yang Dikirimkan</th><th>Banyaknya</th><th>Keterangan</th></tr></thead><tbody><tr><td align="center">1</td><td>{nama_dokumen_kirim}</td><td align="center">{jumlah_berkas}</td><td>{keterangan_dokumen}</td></tr></tbody></table><p>Harap diterima dan ditandatangani tanda terima berkas terlampir.</p>', '{nomor}/010/SP/BPTI/{tahun}', 1),

(5, 'Surat Keputusan (SK)', 'sk', 'TPL-SK', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">KEPUTUSAN KEPALA BADAN<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span><br><br>TENTANG<br>{judul_sk}</p><p><strong>MENIMBANG:</strong><br>{poin_menimbang}</p><p><strong>MENGINGAT:</strong><br>{poin_mengingat}</p><p style="text-align:center; font-weight:bold;">MEMUTUSKAN:</p><p><strong>MENETAPKAN:</strong><br>{poin_menetapkan}</p>', '{nomor}/SK/BPTI/{tahun}', 1),

(6, 'Surat Pemberitahuan', 'pemberitahuan', 'TPL-PBR', '', '<p>Dengan hormat,</p><p>Sehubungan dengan {perihal}, bersama ini kami beritahukan kepada seluruh jajaran bahwa:</p><div style="margin: 10px 0; padding-left: 15px;">{isi_pemberitahuan}</div><p>Demikian pemberitahuan ini disampaikan untuk diketahui dan dilaksanakan sesuai petunjuk teknis.</p>', '{nomor}/010/PBR/BPTI/{tahun}', 1),

(7, 'Surat Permohonan', 'permohonan', 'TPL-PMH', '', '<p>Dengan hormat,</p><p>Dalam rangka mendukung kelancaran operasional {kegiatan}, bersama surat ini kami mengajukan permohonan {uraian_permohonan}.</p><p>Sebagai bahan pertimbangan, terlampir kami sampaikan dokumen pendukung terkait.</p><p>Besar harapan kami permohonan ini dapat disetujui. Atas perhatian dan bantuannya kami ucapkan terima kasih.</p>', '{nomor}/100/PMH/BPTI/{tahun}', 1),

(8, 'Surat Rekomendasi', 'rekomendasi', 'TPL-RKM', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">SURAT REKOMENDASI<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span></p><p>Pimpinan Badan Pengembangan Teknologi Informasi dan Arsip memberikan rekomendasi kepada:</p><table class="table-content"><tr><td width="160"><strong>Nama / Lembaga</strong></td><td>: {nama_rekomendasi}</td></tr><tr><td><strong>Kegiatan / Keperluan</strong></td><td>: {keperluan_rekomendasi}</td></tr></table><p>Berdasarkan evaluasi administrasi, yang bersangkutan telah memenuhi standar kualifikasi yang ditetapkan.</p>', '{nomor}/420/RKM/BPTI/{tahun}', 1),

(9, 'Surat Pernyataan', 'pernyataan', 'TPL-PRN', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">SURAT PERNYATAAN<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span></p><p>Saya yang bertanda tangan di bawah ini menyatakan dengan sesungguhnya bahwa:</p><div style="margin: 15px 0;">{isi_pernyataan}</div><p>Pernyataan ini dibuat dengan sadar tanpa ada paksaan dari pihak manapun untuk digunakan sebagaimana mestinya.</p>', '{nomor}/010/PRN/BPTI/{tahun}', 1),

(10, 'Surat Kuasa Kedinasan', 'kuasa', 'TPL-KSA', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">SURAT KUASA<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span></p><p>Memberikan kuasa penuh kepada:</p><table class="table-content"><tr><td width="160"><strong>Nama Penerima Kuasa</strong></td><td>: {nama_penerima_kuasa}</td></tr><tr><td><strong>NIP / Identitas</strong></td><td>: {identitas_penerima}</td></tr></table><p>Untuk mewakili dan melakukan pengurusan: {hal_kuasa}.</p>', '{nomor}/010/KSA/BPTI/{tahun}', 1),

(11, 'Surat Edaran', 'edaran', 'TPL-EDR', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">SURAT EDARAN<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span><br><br>TENTANG<br>{hal_edaran}</p><p>{isi_edaran}</p>', '{nomor}/SE/010/BPTI/{tahun}', 1),

(12, 'Surat Permohonan / Pemberian Izin', 'izin', 'TPL-IZN', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">SURAT IZIN<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span></p><p>Diberikan izin kepada: <strong>{nama_pemohon}</strong> untuk melaksanakan {uraian_izin} terhitung mulai tanggal {tgl_mulai} sampai dengan {tgl_selesai}.</p>', '{nomor}/820/IZN/BPTI/{tahun}', 1),

(13, 'Berita Acara (BA)', 'berita_acara', 'TPL-BAC', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">BERITA ACARA SERAH TERIMA / KEGIATAN<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span></p><p>Pada hari ini {hari_tanggal}, bertempat di {lokasi}, telah dilaksanakan kegiatan {uraian_kegiatan} dengan rincian hasil terlampir.</p>', '{nomor}/BA/010/BPTI/{tahun}', 1),

(14, 'Perjanjian Kerjasama (MoU)', 'perjanjian', 'TPL-MOU', '', '<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">PERJANJIAN KERJASAMA (MoU)<br><span style="font-size: 14px; font-weight: normal;">Nomor: {nomor_surat}</span></p><p>Perjanjian ini dibuat dan disepakati oleh PIHAK PERTAMA dan PIHAK KEDUA mengenai: {pokok_kerjasama}.</p>', '{nomor}/MOU/100/BPTI/{tahun}', 1),

(15, 'Nota Dinas Internal', 'nota_dinas', 'TPL-NDN', '', '<table class="table-content" style="border-bottom: 2px solid #333; margin-bottom: 15px; padding-bottom: 10px;"><tr><td width="120"><strong>Kepada Yth.</strong></td><td>: {tujuan_nota}</td></tr><tr><td><strong>Dari</strong></td><td>: {pengirim_nota}</td></tr><tr><td><strong>Tanggal</strong></td><td>: {tanggal_surat}</td></tr><tr><td><strong>Nomor</strong></td><td>: {nomor_surat}</td></tr><tr><td><strong>Hal</strong></td><td>: {perihal}</td></tr></table><p>{isi_nota_dinas}</p>', '{nomor}/ND/BPTI/{tahun}', 1),

(16, 'Template Bebas / Standar Instansi', 'bebas', 'TPL-BBS', '', '<p>Dengan hormat,</p><div style="margin: 15px 0;">{isi_surat_bebas}</div><p>Demikian surat ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.</p>', '{nomor}/010/BPTI/{tahun}', 1);

-- 10. Sample Surat Masuk
INSERT OR IGNORE INTO surat_masuk (id, nomor_agenda, nomor_surat, tanggal_surat, tanggal_terima, pengirim, asal_instansi, perihal, isi_ringkas, kategori, klasifikasi_id, tingkat_urgensi, status, penerima_user_id) VALUES
(1, 'AG-M-001/2026', '005/142/DISPUSIP/XI/2026', '2026-11-10', '2026-11-12', 'Kepala Dinas Perpustakaan dan Kearsipan', 'Dinas Perpustakaan & Kearsipan DKI', 'Undangan Rapat Koordinasi Digitalisasi Arsip Daerah', 'Koordinasi integrasi sistem persuratan digital tahun 2027', 'Dinas', 1, 'penting', 'disposisi', 5),
(2, 'AG-M-002/2026', '800/520/BKD/XI/2026', '2026-11-11', '2026-11-13', 'Badan Kepegawaian Daerah', 'Pemerintah Provinsi DKI Jakarta', 'Usulan Kebutuhan Formasi Tenaga Pranata Komputer', 'Pengisian formasi ASN pranata komputer dan arsiparis tahun 2027', 'Kepegawaian', 5, 'segera', 'tindak_lanjut', 5),
(3, 'AG-M-003/2026', '010/88/KEMENKOMINFO/XI/2026', '2026-11-14', '2026-11-15', 'Kementerian Komunikasi dan Informatika', 'Kementerian Kominfo RI', 'Sosialisasi Standar Keamanan Sistem Administrasi Elektronik', 'Penyuluhan panduan implementasi SPBE persuratan', 'Pemberitahuan', 3, 'biasa', 'diterima', 5);

-- 11. Sample Surat Keluar
INSERT OR IGNORE INTO surat_keluar (id, nomor_agenda, nomor_surat, tanggal_surat, tujuan, perihal, isi_ringkas, template_id, klasifikasi_id, tingkat_kerahasiaan, pejabat_id, status, token_verifikasi, created_by) VALUES
(1, 'AG-K-001/2026', '012/005/BPTI-XI/2026', '2026-11-14', 'Kepala Dinas Komunikasi, Informatika dan Statistik DKI', 'Undangan Diskusi Implementasi PWA Persuratan', 'Undangan diskusi teknis arsitektur persuratan berbasis offline-first PWA', 1, 1, 'biasa', 1, 'diterbitkan', 'VERIF-7F9A1B2C-2026', 2),
(2, 'AG-K-002/2026', '045/ST/820/BPTI/2026', '2026-11-15', 'Rian Pratama, S.T & Budi Santoso, A.Md', 'Surat Tugas Pendampingan Sosialisasi Sistem Kearsipan', 'Penugasan asistensi teknis implementasi SI-Persuratan', 2, 6, 'biasa', 1, 'disetujui', 'VERIF-8E3A4D9F-2026', 4),
(3, 'AG-K-003/2026', 'DRAFT-003/2026', '2026-11-16', 'Kementerian PAN-RB', 'Laporan Evaluasi Mandiri Kearsipan Instansi Semester II', 'Laporan kepatuhan kearsipan digital', 16, 7, 'terbatas', 1, 'draft', 'VERIF-9C2E5A8B-2026', 2);

-- 12. Sample Disposisi & Detail
INSERT OR IGNORE INTO disposisi (id, surat_masuk_id, pemberi_user_id, sifat, tenggat_waktu, catatan_instruksi) VALUES
(1, 1, 3, 'penting', '2026-11-20', 'Tolong pelajari kesiapan integrasi API dan hadir mendampingi pimpinan pada rapat Dispusip.'),
(2, 2, 3, 'segera', '2026-11-18', 'Segera siapkan data peta jabatan pranata komputer & arsiparis untuk bahan verifikasi BKD.');

INSERT OR IGNORE INTO disposisi_detail (id, disposisi_id, penerima_user_id, status, tanggal_dibaca, tanggal_selesai) VALUES
(1, 1, 2, 'dalam_proses', '2026-11-13 09:15:00', NULL),
(2, 1, 6, 'dalam_proses', '2026-11-13 09:30:00', NULL),
(3, 2, 4, 'selesai', '2026-11-14 08:00:00', '2026-11-15 14:00:00');

-- 13. Sample Tindak Lanjut
INSERT OR IGNORE INTO tindak_lanjut (id, disposisi_detail_id, surat_masuk_id, user_id, tindakan, catatan, status) VALUES
(1, 3, 2, 4, 'Penyusunan draft peta jabatan dan analisis beban kerja', 'Data kebutuhan pranata komputer (3 orang) dan arsiparis (2 orang) telah selesai dihimpun dan dikirim ke BKD.', 'selesai');

-- 14. Sample Buku Agenda
INSERT OR IGNORE INTO agenda_surat (id, jenis, nomor_agenda, tanggal_agenda, referensi_id, nomor_surat, tanggal_surat, pengirim_penerima, perihal, keterangan) VALUES
(1, 'masuk', 'AG-M-001/2026', '2026-11-12', 1, '005/142/DISPUSIP/XI/2026', '2026-11-10', 'Dinas Perpustakaan & Kearsipan DKI', 'Undangan Rapat Koordinasi Digitalisasi Arsip Daerah', 'Surat fisik masuk via loket'),
(2, 'masuk', 'AG-M-002/2026', '2026-11-13', 2, '800/520/BKD/XI/2026', '2026-11-11', 'Pemerintah Provinsi DKI Jakarta', 'Usulan Kebutuhan Formasi Tenaga Pranata Komputer', 'Disampaikan langsung oleh kurir dinas'),
(3, 'keluar', 'AG-K-001/2026', '2026-11-14', 1, '012/005/BPTI-XI/2026', '2026-11-14', 'Kepala Dinas Kominfotik DKI', 'Undangan Diskusi Implementasi PWA Persuratan', 'Dikirim via kurir dan email dinas');

-- 15. Sample Arsip Digital
INSERT OR IGNORE INTO arsip (id, kode_arsip, jenis_dokumen, referensi_id, judul, nomor_dokumen, tanggal_dokumen, klasifikasi_id, tahun, lokasi_fisik, file_path, status_retensi, is_important) VALUES
(1, 'ARS-2026-001', 'surat_masuk', 1, 'Undangan Rapat Koordinasi Digitalisasi Arsip Daerah', '005/142/DISPUSIP/XI/2026', '2026-11-10', 1, 2026, 'Bantex Kearsipan 01 / Rak A-2', 'documents/surat_masuk/arsip_undangan_dispusip.pdf', 'aktif', 1),
(2, 'ARS-2026-002', 'surat_keluar', 1, 'Undangan Diskusi Implementasi PWA Persuratan', '012/005/BPTI-XI/2026', '2026-11-14', 1, 2026, 'Bantex Surat Keluar 2026 / Rak B-1', 'documents/surat_keluar/undangan_pwa.pdf', 'aktif', 0);

-- 16. Sample Log Aktivitas
INSERT OR IGNORE INTO aktivitas_log (id, user_id, action, module, record_id, details, ip_address, user_agent) VALUES
(1, 1, 'LOGIN', 'auth', 1, 'Super Administrator login berhasil ke sistem', '127.0.0.1', 'Mozilla/5.0 Antigravity Browser'),
(2, 5, 'CREATE', 'surat_masuk', 1, 'Registrasi surat masuk baru Nomor 005/142/DISPUSIP/XI/2026', '127.0.0.1', 'Mozilla/5.0 Antigravity Browser'),
(3, 3, 'CREATE', 'disposisi', 1, 'Pemberian instruksi lembar disposisi untuk surat AG-M-001/2026', '127.0.0.1', 'Mozilla/5.0 Antigravity Browser');
