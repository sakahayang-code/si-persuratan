-- migrations/001_initial_schema.sql
-- Skema Tabel Database SQLite SI-PERSURATAN

CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    module TEXT NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INTEGER NOT NULL,
    permission_id INTEGER NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    nip TEXT,
    jabatan TEXT,
    phone TEXT,
    role_id INTEGER NOT NULL,
    avatar TEXT,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS klasifikasi_surat (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode TEXT NOT NULL UNIQUE,
    nama TEXT NOT NULL,
    uraian TEXT,
    retensi_aktif INTEGER DEFAULT 2, -- tahun
    retensi_inaktif INTEGER DEFAULT 5, -- tahun
    keterangan TEXT,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS pejabat (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nip TEXT NOT NULL UNIQUE,
    nama TEXT NOT NULL,
    jabatan TEXT NOT NULL,
    unit_kerja TEXT,
    signature_file TEXT,
    stempel_file TEXT,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS nomor_surat (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode_klasifikasi TEXT,
    format_pola TEXT NOT NULL DEFAULT '{nomor}/{kode}/{bulan_romawi}/{tahun}',
    counter INTEGER NOT NULL DEFAULT 0,
    tahun INTEGER NOT NULL,
    reset_period TEXT NOT NULL DEFAULT 'yearly', -- 'yearly', 'monthly', 'never'
    last_number TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS template_surat (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama_template TEXT NOT NULL,
    jenis_surat TEXT NOT NULL,
    kode TEXT NOT NULL UNIQUE,
    kop_surat_html TEXT,
    konten_template TEXT NOT NULL,
    variabel_fields TEXT, -- JSON array of dynamic fields
    format_nomor TEXT,
    is_system INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS surat_masuk (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nomor_agenda TEXT NOT NULL,
    nomor_surat TEXT NOT NULL,
    tanggal_surat DATE NOT NULL,
    tanggal_terima DATE NOT NULL,
    pengirim TEXT NOT NULL,
    asal_instansi TEXT NOT NULL,
    perihal TEXT NOT NULL,
    isi_ringkas TEXT,
    kategori TEXT DEFAULT 'Umum',
    klasifikasi_id INTEGER,
    tingkat_urgensi TEXT NOT NULL DEFAULT 'biasa', -- 'biasa', 'penting', 'segera', 'rahasia'
    file_dokumen TEXT,
    lampiran_files TEXT, -- JSON list of files
    status TEXT NOT NULL DEFAULT 'diterima', -- 'diterima', 'disposisi', 'tindak_lanjut', 'selesai'
    penerima_user_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (klasifikasi_id) REFERENCES klasifikasi_surat (id) ON DELETE SET NULL,
    FOREIGN KEY (penerima_user_id) REFERENCES users (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS surat_keluar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nomor_agenda TEXT NOT NULL,
    nomor_surat TEXT NOT NULL,
    tanggal_surat DATE NOT NULL,
    tujuan TEXT NOT NULL,
    perihal TEXT NOT NULL,
    isi_ringkas TEXT,
    template_id INTEGER,
    template_data TEXT, -- JSON values
    klasifikasi_id INTEGER,
    tingkat_kerahasiaan TEXT NOT NULL DEFAULT 'biasa', -- 'biasa', 'terbatas', 'rahasia', 'sangat_rahasia'
    pejabat_id INTEGER,
    file_dokumen TEXT,
    lampiran_files TEXT, -- JSON list
    status TEXT NOT NULL DEFAULT 'draft', -- 'draft', 'menunggu_persetujuan', 'disetujui', 'diterbitkan', 'dikirim', 'ditolak'
    verified_at DATETIME,
    verified_by INTEGER,
    catatan_revisi TEXT,
    token_verifikasi TEXT UNIQUE,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (template_id) REFERENCES template_surat (id) ON DELETE SET NULL,
    FOREIGN KEY (klasifikasi_id) REFERENCES klasifikasi_surat (id) ON DELETE SET NULL,
    FOREIGN KEY (pejabat_id) REFERENCES pejabat (id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users (id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS disposisi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    surat_masuk_id INTEGER NOT NULL,
    pemberi_user_id INTEGER NOT NULL,
    sifat TEXT NOT NULL DEFAULT 'biasa', -- 'biasa', 'penting', 'rahasia', 'segera'
    tenggat_waktu DATE,
    catatan_instruksi TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (surat_masuk_id) REFERENCES surat_masuk (id) ON DELETE RESTRICT,
    FOREIGN KEY (pemberi_user_id) REFERENCES users (id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS disposisi_detail (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    disposisi_id INTEGER NOT NULL,
    penerima_user_id INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'belum_dibaca', -- 'belum_dibaca', 'dibaca', 'dalam_proses', 'selesai'
    tanggal_dibaca DATETIME,
    tanggal_selesai DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disposisi_id) REFERENCES disposisi (id) ON DELETE CASCADE,
    FOREIGN KEY (penerima_user_id) REFERENCES users (id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS tindak_lanjut (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    disposisi_detail_id INTEGER,
    surat_masuk_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    tindakan TEXT NOT NULL,
    catatan TEXT,
    file_lampiran TEXT,
    status TEXT NOT NULL DEFAULT 'selesai', -- 'proses', 'selesai'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disposisi_detail_id) REFERENCES disposisi_detail (id) ON DELETE SET NULL,
    FOREIGN KEY (surat_masuk_id) REFERENCES surat_masuk (id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS agenda_surat (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    jenis TEXT NOT NULL, -- 'masuk', 'keluar'
    nomor_agenda TEXT NOT NULL,
    tanggal_agenda DATE NOT NULL,
    referensi_id INTEGER NOT NULL,
    nomor_surat TEXT NOT NULL,
    tanggal_surat DATE NOT NULL,
    pengirim_penerima TEXT NOT NULL,
    perihal TEXT NOT NULL,
    keterangan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS arsip (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode_arsip TEXT NOT NULL UNIQUE,
    jenis_dokumen TEXT NOT NULL, -- 'surat_masuk', 'surat_keluar', 'sk', 'dokumen_lain'
    referensi_id INTEGER,
    judul TEXT NOT NULL,
    nomor_dokumen TEXT NOT NULL,
    tanggal_dokumen DATE NOT NULL,
    klasifikasi_id INTEGER,
    tahun INTEGER NOT NULL,
    lokasi_fisik TEXT,
    file_path TEXT NOT NULL,
    status_retensi TEXT NOT NULL DEFAULT 'aktif', -- 'aktif', 'inaktif', 'dimusnahkan'
    is_important INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (klasifikasi_id) REFERENCES klasifikasi_surat (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS tanda_tangan (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pejabat_id INTEGER NOT NULL,
    nama_file TEXT NOT NULL,
    deskripsi TEXT,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pejabat_id) REFERENCES pejabat (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS dokumen (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    jenis_entitas TEXT NOT NULL, -- 'surat_masuk', 'surat_keluar', 'tindak_lanjut', 'arsip'
    entitas_id INTEGER NOT NULL,
    nama_file TEXT NOT NULL,
    path_file TEXT NOT NULL,
    mime_type TEXT,
    ukuran_file INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifikasi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    judul TEXT NOT NULL,
    pesan TEXT NOT NULL,
    link_url TEXT,
    tipe TEXT NOT NULL DEFAULT 'info', -- 'info', 'surat_baru', 'disposisi', 'deadline', 'approval'
    is_read INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS aktivitas_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL, -- 'CREATE', 'UPDATE', 'DELETE', 'APPROVE', 'EXPORT', 'BACKUP', 'RESTORE', 'LOGIN'
    module TEXT NOT NULL, -- 'auth', 'surat_masuk', 'surat_keluar', 'disposisi', 'arsip', 'backup', 'settings'
    record_id INTEGER,
    details TEXT,
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS pengaturan (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key_name TEXT NOT NULL UNIQUE,
    value_text TEXT,
    category TEXT NOT NULL DEFAULT 'umum',
    description TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS backup_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename TEXT NOT NULL,
    filepath TEXT NOT NULL,
    filesize INTEGER NOT NULL,
    type TEXT NOT NULL DEFAULT 'manual', -- 'manual', 'auto_daily', 'pre_update', 'pre_restore'
    status TEXT NOT NULL DEFAULT 'success', -- 'success', 'failed'
    checksum TEXT,
    notes TEXT,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
);

-- Search Indexes (Optimasi performa & pencarian arsip cepat)
CREATE INDEX IF NOT EXISTS idx_surat_masuk_nomor ON surat_masuk (nomor_surat);
CREATE INDEX IF NOT EXISTS idx_surat_masuk_tgl ON surat_masuk (tanggal_surat);
CREATE INDEX IF NOT EXISTS idx_surat_masuk_pengirim ON surat_masuk (pengirim);
CREATE INDEX IF NOT EXISTS idx_surat_masuk_status ON surat_masuk (status);
CREATE INDEX IF NOT EXISTS idx_surat_masuk_klasifikasi ON surat_masuk (klasifikasi_id);

CREATE INDEX IF NOT EXISTS idx_surat_keluar_nomor ON surat_keluar (nomor_surat);
CREATE INDEX IF NOT EXISTS idx_surat_keluar_tgl ON surat_keluar (tanggal_surat);
CREATE INDEX IF NOT EXISTS idx_surat_keluar_tujuan ON surat_keluar (tujuan);
CREATE INDEX IF NOT EXISTS idx_surat_keluar_status ON surat_keluar (status);
CREATE INDEX IF NOT EXISTS idx_surat_keluar_token ON surat_keluar (token_verifikasi);

CREATE INDEX IF NOT EXISTS idx_disposisi_surat ON disposisi (surat_masuk_id);
CREATE INDEX IF NOT EXISTS idx_disp_detail_penerima ON disposisi_detail (penerima_user_id);
CREATE INDEX IF NOT EXISTS idx_disp_detail_status ON disposisi_detail (status);

CREATE INDEX IF NOT EXISTS idx_arsip_nomor ON arsip (nomor_dokumen);
CREATE INDEX IF NOT EXISTS idx_arsip_tahun ON arsip (tahun);
CREATE INDEX IF NOT EXISTS idx_arsip_status ON arsip (status_retensi);

CREATE INDEX IF NOT EXISTS idx_agenda_nomor ON agenda_surat (nomor_agenda);
CREATE INDEX IF NOT EXISTS idx_aktivitas_user ON aktivitas_log (user_id);
CREATE INDEX IF NOT EXISTS idx_aktivitas_module ON aktivitas_log (module);
