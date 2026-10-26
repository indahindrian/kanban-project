-- =========================================================
-- Migration: role-based access + project planning dates.
-- SEMUA TABEL PAKAI PREFIX "kb_"
-- =========================================================

ALTER TABLE kb_users ADD COLUMN role ENUM('anggota','atasan') NOT NULL DEFAULT 'anggota' AFTER email;

-- Demo credentials (password sama untuk semua: "password123")
UPDATE kb_users SET role = 'atasan', password = '$2y$10$k8Snbbr815lIEdlbIAI7.uCIQtQefPcpFBJjkLpYowV8o9ZcL1z2W' WHERE email = 'siti@example.com';
UPDATE kb_users SET role = 'anggota', password = '$2y$10$k8Snbbr815lIEdlbIAI7.uCIQtQefPcpFBJjkLpYowV8o9ZcL1z2W' WHERE email = 'budi@example.com';
UPDATE kb_users SET role = 'anggota', password = '$2y$10$k8Snbbr815lIEdlbIAI7.uCIQtQefPcpFBJjkLpYowV8o9ZcL1z2W' WHERE email = 'andi@example.com';

-- Project tambahan untuk mendemokan perbedaan akses:
-- "Migrasi Server Internal" -> Andi jadi anggota, Budi TIDAK di-assign.
INSERT INTO kb_projects (name, description, status, owner_id, tanggal_rencana_mulai, tanggal_rencana_selesai, tanggal_aktual_mulai) VALUES
('Migrasi Server Internal', 'Pemindahan aplikasi internal ke server baru.', 'active', 1, '2026-07-15', '2026-09-01', '2026-07-18');
SET @p2 = LAST_INSERT_ID();
INSERT INTO kb_project_members (project_id, user_id, role) VALUES (@p2, 1, 'manager'), (@p2, 3, 'member');
INSERT INTO kb_tasks (project_id, title, status, due_date, progress) VALUES
(@p2, 'Setup server baru', 'processing', '2026-08-01', 30),
(@p2, 'Migrasi database', 'no_status', '2026-08-15', 0);

-- "Redesign Landing Page" -> tidak ada anggota di-assign sama sekali,
-- cuma atasan (Siti) yang owner. Anggota manapun tidak akan melihat ini.
INSERT INTO kb_projects (name, description, status, owner_id, tanggal_rencana_mulai, tanggal_rencana_selesai, tanggal_aktual_mulai, tanggal_aktual_selesai) VALUES
('Redesign Landing Page', 'Perbarui tampilan landing page produk.', 'done', 1, '2026-06-01', '2026-06-20', '2026-06-01', '2026-06-25');
SET @p3 = LAST_INSERT_ID();
INSERT INTO kb_project_members (project_id, user_id, role) VALUES (@p3, 1, 'manager');
INSERT INTO kb_tasks (project_id, title, status, due_date, progress) VALUES (@p3, 'Landing page baru', 'closed', '2026-06-20', 100);

-- Tandai project pertama juga dengan tanggal rencana (buat demo rencana vs aktual)
UPDATE kb_projects SET tanggal_rencana_mulai = '2026-07-10', tanggal_rencana_selesai = '2026-08-10', tanggal_aktual_mulai = '2026-07-10'
WHERE name = 'Test Project (Entite Racine)';
