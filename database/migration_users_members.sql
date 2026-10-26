-- =========================================================
-- Migration: users + task_members (multi-assignee per task)
-- Dijalankan setelah schema.sql. Menambahkan tabel users
-- (belum ada tabel users existing di database `kanban`),
-- lalu mengganti kolom tunggal tasks.assigned_to dengan
-- relasi many-to-many task_members (mirip Trello "Members").
-- =========================================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS task_members (
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (task_id, user_id),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE tasks DROP COLUMN assigned_to;

ALTER TABLE projects ADD CONSTRAINT fk_projects_owner FOREIGN KEY (owner_id) REFERENCES users(id);
ALTER TABLE project_members ADD CONSTRAINT fk_pm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE attachments ADD CONSTRAINT fk_attachments_user FOREIGN KEY (uploaded_by) REFERENCES users(id);
ALTER TABLE task_activities ADD CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES users(id);

-- NOTE: password kolom diisi placeholder karena modul login belum dibuat.
-- Ganti dengan password_hash() PHP asli begitu ada halaman auth.
INSERT INTO users (name, email, password) VALUES
('Siti', 'siti@example.com', 'not_set'),
('Budi', 'budi@example.com', 'not_set'),
('Andi', 'andi@example.com', 'not_set');
