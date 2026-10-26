-- Sample data untuk testing UI Kanban (boleh dihapus/disesuaikan)
INSERT INTO kb_projects (name, description, status, owner_id) VALUES
('Test Project (Entite Racine)', 'Project percobaan untuk validasi tampilan Kanban.', 'active', 1);

SET @pid = LAST_INSERT_ID();

INSERT INTO kb_project_members (project_id, user_id, role) VALUES
(@pid, 1, 'manager'),
(@pid, 2, 'member'),
(@pid, 3, 'member');

INSERT INTO kb_labels (project_id, name, color) VALUES
(@pid, 'Urgent', '#FF5630'),
(@pid, 'Middle', '#C9A400'),
(@pid, 'Design', '#6554C0'),
(@pid, 'Backend', '#0052CC');

INSERT INTO kb_tasks (project_id, title, description, status, due_date, progress) VALUES
(@pid, 'Update Website - BIG', 'Website BIG sudah terlalu usang dan belum sesuai dengan Positioning dari BIG nya sendiri yang menjadi partner untuk diskusi tentang interior yang tidak akan ngalahin costumer, website masih terlalu hardselling sehingga haru disesuaikan dulu semua isian dan konten yang ada didalamnya.', 'processing', '2026-07-31', 60),
(@pid, 'test task 2', NULL, 'processing', '2026-07-30', 20),
(@pid, 'test task 3', NULL, 'processing', '2026-08-05', 0),
(@pid, 'test task', NULL, 'closed', '2026-07-18', 100);

SET @t1 = (SELECT id FROM kb_tasks WHERE project_id = @pid AND title = 'Update Website - BIG');
SET @t4 = (SELECT id FROM kb_tasks WHERE project_id = @pid AND title = 'test task');

INSERT INTO kb_task_labels (task_id, label_id)
SELECT @t1, id FROM kb_labels WHERE project_id = @pid AND name = 'Middle';

INSERT INTO kb_task_members (task_id, user_id) VALUES (@t1, 2), (@t4, 1);

INSERT INTO kb_checklists (task_id, title, sort_order) VALUES (@t1, 'Checklist Update Konten', 0);
SET @cl1 = LAST_INSERT_ID();

INSERT INTO kb_checklist_items (checklist_id, text, is_done, sort_order) VALUES
(@cl1, 'Sesuaikan Positioning website', 1, 0),
(@cl1, 'Buat draft konten secara keseluruhan bisa dibantu oleh Claude', 1, 1),
(@cl1, 'Update Portfolio beserta Photo dsb', 1, 2),
(@cl1, 'Sesuaikan Page setiap halaman mirip seperti eleventrans', 0, 3),
(@cl1, 'Update halaman katalog', 0, 4);

INSERT INTO kb_attachments (task_id, type, name, url, uploaded_by) VALUES
(@t1, 'link', 'Web BIG', 'https://big.example.com', 1);
