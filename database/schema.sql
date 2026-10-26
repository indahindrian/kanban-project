-- =========================================================
-- SCHEMA - Project Management / Kanban Module
-- SEMUA TABEL PAKAI PREFIX "kb_" supaya aman digabung ke
-- database e-client_dummy tanpa bentrok tabel yang sudah ada.
-- =========================================================

CREATE TABLE kb_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kb_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status ENUM('active','on_hold','done') DEFAULT 'active',
    owner_id INT NOT NULL,
    tanggal_rencana_mulai DATE NULL,
    tanggal_rencana_selesai DATE NULL,
    tanggal_aktual_mulai DATE NULL,
    tanggal_aktual_selesai DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES kb_users(id)
);

CREATE TABLE kb_project_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('manager','member') DEFAULT 'member',
    FOREIGN KEY (project_id) REFERENCES kb_projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES kb_users(id) ON DELETE CASCADE
);

CREATE TABLE kb_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    status ENUM('no_status','processing','closed') DEFAULT 'no_status',
    start_date DATE NULL,
    due_date DATE NULL,
    progress TINYINT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES kb_projects(id) ON DELETE CASCADE
);

CREATE TABLE kb_task_members (
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (task_id, user_id),
    FOREIGN KEY (task_id) REFERENCES kb_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES kb_users(id) ON DELETE CASCADE
);

CREATE TABLE kb_task_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    points INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES kb_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES kb_users(id)
);

CREATE TABLE kb_labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7) NOT NULL,
    FOREIGN KEY (project_id) REFERENCES kb_projects(id) ON DELETE CASCADE
);

CREATE TABLE kb_task_labels (
    task_id INT NOT NULL,
    label_id INT NOT NULL,
    PRIMARY KEY (task_id, label_id),
    FOREIGN KEY (task_id) REFERENCES kb_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES kb_labels(id) ON DELETE CASCADE
);

CREATE TABLE kb_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    title VARCHAR(100) DEFAULT 'Checklist',
    sort_order INT DEFAULT 0,
    FOREIGN KEY (task_id) REFERENCES kb_tasks(id) ON DELETE CASCADE
);

CREATE TABLE kb_checklist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT NOT NULL,
    text VARCHAR(255) NOT NULL,
    is_done TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (checklist_id) REFERENCES kb_checklists(id) ON DELETE CASCADE
);

CREATE TABLE kb_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    type ENUM('link','file') NOT NULL,
    name VARCHAR(150) NOT NULL,
    url VARCHAR(500) NULL,
    uploaded_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES kb_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES kb_users(id)
);
