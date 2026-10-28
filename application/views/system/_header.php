<?php
$active    = isset($active) ? $active : '';
$show_tabs = isset($show_tabs) ? $show_tabs : true;
$initial   = strtoupper(mb_substr((string) $user_name, 0, 1));
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= html_escape($page_title) ?> - Kanban System</title>
<style>
  :root { --bg:#f1f5f9; --card:#fff; --border:#e2e8f0; --text:#0f172a; --muted:#64748b; --accent:#2563eb; --danger:#dc2626; --ok:#16a34a; --sidebar:#0b1530; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:-apple-system,"Segoe UI",Roboto,Inter,sans-serif; background:var(--bg); color:var(--text); }
  .app { display:flex; min-height:100vh; }

  /* ===== SIDEBAR ===== */
  .sidebar { width:272px; min-width:272px; background:var(--sidebar); color:#cbd5e1; display:flex; flex-direction:column; }
  .brand { display:flex; align-items:center; gap:12px; padding:20px 22px; border-bottom:1px solid rgba(255,255,255,.07); }
  .brand-logo { width:38px; height:38px; border-radius:10px; background:var(--accent); color:#fff; font-weight:800; display:flex; align-items:center; justify-content:center; }
  .brand-name { color:#fff; font-weight:700; font-size:14px; letter-spacing:.3px; }
  .brand-sub { font-size:12px; color:#94a3b8; }
  .nav { flex:1; padding:8px 14px; overflow-y:auto; }
  .nav-label { padding:18px 10px 8px; font-size:11px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; font-weight:600; }
  .nav a { display:block; padding:11px 14px; margin-bottom:4px; border-radius:10px; font-size:14px; color:#cbd5e1; text-decoration:none; }
  .nav a:hover { background:rgba(255,255,255,.07); color:#fff; }
  .nav a.active { background:var(--accent); color:#fff; font-weight:600; }
  .userbox { display:flex; align-items:center; gap:12px; padding:16px 18px; border-top:1px solid rgba(255,255,255,.07); }
  .userbox .av { width:38px; height:38px; border-radius:50%; background:#334155; color:#fff; font-weight:700; font-size:13px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .userbox .who { flex:1; min-width:0; }
  .userbox .who b { display:block; color:#fff; font-size:13.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .userbox .who span { font-size:12px; color:#94a3b8; }
  .userbox .links { font-size:12px; display:flex; flex-direction:column; align-items:flex-end; gap:3px; }
  .userbox .links a { color:#94a3b8; text-decoration:none; }
  .userbox .links a:hover { color:#fff; }

  /* ===== MAIN ===== */
  .main { flex:1; min-width:0; }
  .topbar { background:#fff; border-bottom:1px solid var(--border); padding:18px 32px 0; }
  .topbar-row { display:flex; align-items:center; gap:12px; padding-bottom:14px; }
  .topbar h1 { margin:0; font-size:22px; }
  .pill { font-size:12px; font-weight:600; color:var(--accent); background:#eff6ff; border:1px solid #bfdbfe; padding:4px 12px; border-radius:20px; }
  .tabs { display:flex; gap:26px; }
  .tabs a { padding:10px 2px 12px; font-size:14px; color:var(--muted); text-decoration:none; border-bottom:2px solid transparent; }
  .tabs a:hover { color:var(--text); }
  .tabs a.active { color:var(--accent); font-weight:600; border-bottom-color:var(--accent); }
  .content { padding:24px 32px 40px; }

  .banner { border-radius:12px; padding:16px 20px; font-size:13.5px; line-height:1.5; margin-bottom:22px; border:1px solid; }
  .banner-blue { background:#eff6ff; border-color:#bfdbfe; }
  .banner-amber { background:#fffbeb; border-color:#fde68a; }
  .banner-indigo { background:#eef2ff; border-color:#c7d2fe; }
  .grid-2 { display:grid; grid-template-columns:minmax(300px,380px) 1fr; gap:22px; align-items:start; }
  @media (max-width:1000px) { .grid-2 { grid-template-columns:1fr; } }

  .card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:22px; box-shadow:0 1px 2px rgba(15,23,42,.04); }
  .card-head { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; padding-bottom:14px; margin-bottom:16px; border-bottom:1px solid var(--border); }
  .card-head h2 { margin:0; font-size:16px; }
  .card-head .sub { font-size:12.5px; color:var(--muted); margin-top:2px; }
  .card-head .tag { font-size:11.5px; color:var(--muted); white-space:nowrap; padding-top:4px; }
  .badge-ok { font-size:12px; font-weight:600; color:#15803d; background:#f0fdf4; border:1px solid #bbf7d0; padding:4px 12px; border-radius:20px; white-space:nowrap; }

  .field { margin-bottom:14px; }
  .field label { display:block; font-size:12.5px; font-weight:600; margin-bottom:6px; }
  .field input, .field select, .field textarea { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; font-family:inherit; background:#f8fafc; color:var(--text); }
  .field textarea { min-height:90px; resize:vertical; }
  .field input:focus, .field select:focus, .field textarea:focus { outline:2px solid #93c5fd; outline-offset:-1px; background:#fff; }
  .field input:disabled { color:var(--muted); }
  .btn { padding:9px 16px; border-radius:8px; border:1px solid var(--border); background:#fff; color:var(--text); font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; }
  .btn:hover { background:#f8fafc; }
  .btn:disabled { opacity:.5; cursor:not-allowed; }
  .btn-primary { background:var(--accent); border-color:var(--accent); color:#fff; }
  .btn-primary:hover { background:#1d4ed8; }
  .btn-dark { background:#111c3f; border-color:#111c3f; color:#fff; }
  .btn-dark:hover { background:#0b1530; }
  .btn-block { width:100%; padding:12px; font-size:14px; }
  .btn-sm { padding:5px 10px; font-size:12px; }
  .btn-danger { color:var(--danger); border-color:#fecaca; }
  .btn-danger:hover { background:#fef2f2; }
  .msg { font-size:12.5px; margin-top:10px; display:none; }
  .msg.ok { display:block; color:var(--ok); }
  .msg.err { display:block; color:var(--danger); }

  table { width:100%; border-collapse:collapse; font-size:13.5px; }
  th { text-align:left; font-size:11.5px; letter-spacing:.05em; text-transform:uppercase; color:var(--muted); background:#f8fafc; padding:11px 14px; }
  th:first-child { border-radius:8px 0 0 8px; } th:last-child { border-radius:0 8px 8px 0; }
  td { padding:13px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
  tr:last-child td { border-bottom:none; }
  td.num, th.num { text-align:right; }
  .row-actions { display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap; }
  .person { display:flex; align-items:center; gap:12px; }
  .av-sm { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; background:#e0e7ff; color:#4338ca; }
  .person b { display:block; font-size:13.5px; }
  .person span { font-size:12px; color:var(--muted); }
  .chip { display:inline-block; font-size:12px; font-weight:600; padding:3px 10px; border-radius:6px; background:#f1f5f9; color:#334155; }
  .chip-blue { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
  .dot-ok, .dot-off { font-size:12.5px; font-weight:600; }
  .dot-ok { color:var(--ok); } .dot-off { color:var(--muted); }
  .dot-ok::before, .dot-off::before { content:''; display:inline-block; width:7px; height:7px; border-radius:50%; margin-right:6px; background:currentColor; }
  .muted { color:var(--muted); }
  .empty { text-align:center; padding:26px; color:var(--muted); }

  .modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,.55); display:none; align-items:center; justify-content:center; z-index:50; padding:16px; }
  .modal-backdrop.open { display:flex; }
  .modal { background:#fff; border-radius:14px; padding:24px; width:100%; max-width:440px; box-shadow:0 20px 40px rgba(15,23,42,.3); }
  .modal h3 { margin:0 0 16px; font-size:17px; }
  .modal-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:18px; }
</style>
</head>
<body>
<div class="app">

  <aside class="sidebar">
    <div class="brand">
      <div class="brand-logo">K</div>
      <div><div class="brand-name">KANBAN SYSTEM</div><div class="brand-sub">Project Monitoring</div></div>
    </div>

    <nav class="nav">
      <div class="nav-label">Menu utama</div>
      <a href="<?= site_url('project') ?>">Dashboard</a>
      <a href="<?= site_url('project/planningPage') ?>">Planning</a>
      <a href="<?= site_url('project/ganttGlobalPage') ?>">Gantt Chart</a>

      <?php if ($can_manage_users || $can_manage_roles || $can_view_audit): ?>
      <div class="nav-label">Pengaturan sistem</div>
      <?php if ($can_manage_users): ?><a href="<?= site_url('user') ?>" class="<?= $active === 'user' ? 'active' : '' ?>">Manajemen User</a><?php endif; ?>
      <?php if ($can_manage_roles): ?>
      <a href="<?= site_url('role') ?>" class="<?= $active === 'role' ? 'active' : '' ?>">Kelola Peran / Role</a>
      <a href="<?= site_url('permission') ?>" class="<?= $active === 'permission' ? 'active' : '' ?>">Hak Akses (Permissions)</a>
      <?php endif; ?>
      <?php if ($can_view_audit): ?><a href="<?= site_url('audit') ?>" class="<?= $active === 'audit' ? 'active' : '' ?>">Audit Log</a><?php endif; ?>
      <?php endif; ?>
    </nav>

    <div class="userbox">
      <div class="av" id="sb-avatar"><?= html_escape($initial) ?></div>
      <div class="who"><b id="sb-name"><?= html_escape($user_name) ?></b><span><?= html_escape($role_name) ?></span></div>
      <div class="links">
        <a href="<?= site_url('akun') ?>" class="<?= $active === 'account' ? 'active' : '' ?>">Akun saya</a>
        <a href="<?= site_url('auth/logout') ?>">Logout</a>
      </div>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <div class="topbar-row">
        <h1><?= html_escape($page_title) ?></h1>
        <?php if ($show_tabs): ?><span class="pill">CodeIgniter 3 Module</span><?php endif; ?>
      </div>
      <?php if ($show_tabs): ?>
      <div class="tabs">
        <?php if ($can_manage_users): ?><a href="<?= site_url('user') ?>" class="<?= $active === 'user' ? 'active' : '' ?>">1. Halaman User</a><?php endif; ?>
        <?php if ($can_manage_roles): ?>
        <a href="<?= site_url('role') ?>" class="<?= $active === 'role' ? 'active' : '' ?>">2. Halaman Role</a>
        <a href="<?= site_url('permission') ?>" class="<?= $active === 'permission' ? 'active' : '' ?>">3. Halaman Hak Akses</a>
        <?php endif; ?>
        <?php if ($can_view_audit): ?><a href="<?= site_url('audit') ?>" class="<?= $active === 'audit' ? 'active' : '' ?>">4. Audit Log</a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="content">
