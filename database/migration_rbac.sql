-- =========================================================
-- Migration: RBAC dinamis (Role + Hak Akses + Audit Log)
-- Jalankan SEKALI, setelah migration_auth_roles.sql.
-- Tabel memakai nama tanpa prefix (users, projects, ...) sesuai model aplikasi.
--
-- Efek ke akun lama:
--   users.role 'atasan'  -> role "Atasan / Monitoring" (HANYA LIHAT)
--   users.role 'anggota' -> role "R&D / MDS"
--   Kolom users.role (enum) dihapus, diganti users.role_id + users.is_active.
-- Akun Super Admin baru dibuat: admin@example.com / password123
--   >>> GANTI PASSWORD-NYA SEGERA setelah login pertama. <<<
-- =========================================================

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,   -- 1 = Super Admin: akses penuh, tidak bisa diubah/dihapus
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    group_key VARCHAR(20) NOT NULL,            -- main | doc | system
    sort_order INT NOT NULL DEFAULT 0,
    super_only TINYINT(1) NOT NULL DEFAULT 0   -- 1 = tidak boleh diberikan ke role selain Super Admin
);

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    target VARCHAR(150) NULL,
    detail TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created (created_at)
);

-- ---------- Seed role ----------
INSERT INTO roles (name, description, is_system) VALUES
('Super Admin',         'Akses penuh ke seluruh sistem, termasuk pengelolaan akun dan hak akses.', 1),
('Atasan / Monitoring', 'Memantau perkembangan seluruh proyek (hanya lihat).', 0),
('R&D / MDS',           'PIC proyek: menambah proyek dan mengedit proyek yang dikerjakan.', 0),
('Admin TW',            'Technical Writer: membantu mengelola dokumen proyek.', 0);

-- ---------- Seed permission (10 fitur, sesuai mockup) ----------
INSERT INTO permissions (code, name, description, group_key, sort_order, super_only) VALUES
('dashboard.view',     'Dashboard Utama',               'Melihat ringkasan status proyek',            'main',   1, 0),
('project.view_all',   'Lihat Semua Proyek',            'Akses monitoring seluruh proyek tim',        'main',   2, 0),
('project.create',     'Buat Proyek Baru',              'Menambahkan inisiatif proyek baru',          'main',   3, 0),
('project.edit_own',   'Edit Proyek (PIC Only)',        'Hanya mengedit proyek yang di-assign',       'main',   4, 0),
('doc.upload',         'Unggah Cheat Sheet / BRD',      'Upload lampiran berkas dokumen',             'doc',    1, 0),
('doc.update_status',  'Update Status Dokumen',         'Mengisi persentase dokumen (e.g. 90%)',      'doc',    2, 0),
('doc.edit_others',    'Edit Dokumen Proyek Lain',      'Khusus role Technical Writer',               'doc',    3, 0),
('system.users',       'Kelola Pengguna (User)',        'Tambah / nonaktifkan akun kantor',           'system', 1, 1),
('system.permissions', 'Atur Hak Akses (Permissions)',  'Mengubah role dan centang hak akses ini',    'system', 2, 1),
('system.audit',       'Audit Log System',              'Melihat riwayat aktivitas user',             'system', 3, 1);

-- ---------- Hak akses default tiap role ----------
-- Super Admin tidak perlu baris di sini: kode aplikasi selalu memberi semua izin.
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.name = 'Atasan / Monitoring' AND p.code IN ('dashboard.view', 'project.view_all');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.name = 'R&D / MDS' AND p.code IN ('dashboard.view', 'project.view_all', 'project.create', 'project.edit_own', 'doc.upload', 'doc.update_status');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.name = 'Admin TW' AND p.code IN ('dashboard.view', 'project.view_all', 'doc.upload', 'doc.update_status', 'doc.edit_others');

-- ---------- Pindahkan tabel users ke model role baru ----------
ALTER TABLE users
    ADD COLUMN role_id INT NULL AFTER email,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role_id;

UPDATE users u JOIN roles r ON r.name = 'Atasan / Monitoring' SET u.role_id = r.id WHERE u.role = 'atasan';
UPDATE users u JOIN roles r ON r.name = 'R&D / MDS'           SET u.role_id = r.id WHERE u.role = 'anggota';

-- Super Admin awal (password: password123 -> WAJIB diganti)
INSERT INTO users (name, email, password, role_id, is_active)
SELECT 'Administrator', 'admin@example.com',
       '$2y$10$k8Snbbr815lIEdlbIAI7.uCIQtQefPcpFBJjkLpYowV8o9ZcL1z2W', r.id, 1
FROM roles r WHERE r.name = 'Super Admin';

ALTER TABLE users
    MODIFY role_id INT NOT NULL,
    ADD CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    DROP COLUMN role;
