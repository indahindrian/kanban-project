<?php
$active    = isset($active) ? $active : '';
$show_tabs = isset($show_tabs) ? $show_tabs : true;
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= html_escape($page_title) ?> - Project Management</title>
<style>
  /* Tema disamakan dengan halaman dashboard (project/dashboard.php) */
  :root {
    --bg: #f0f2f7; --card: #ffffff; --border: #dfe1e6; --text: #172b4d; --muted: #6b778c;
    --accent: #0052cc; --accent-light: #4c9aff; --sidebar-w: 232px;
    --danger: #de350b; --success: #36b37e; --warning: #ffab00;
  }
  * { box-sizing: border-box; }
  body { margin:0; font-family:-apple-system,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:var(--text); -webkit-font-smoothing:antialiased; }
  ::-webkit-scrollbar { width:9px; height:9px; }
  ::-webkit-scrollbar-thumb { background:#c1c7d0; border-radius:8px; }
  ::-webkit-scrollbar-track { background:transparent; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:none; } }

  .app { display:flex; min-height:100vh; }

  /* ===== SIDEBAR ===== */
  .sidebar { width:var(--sidebar-w); min-width:var(--sidebar-w); background:linear-gradient(180deg,#1a2b4c,#131f38); color:#fff; padding:20px 0; display:flex; flex-direction:column; box-shadow:2px 0 8px rgba(9,30,66,.15); }
  .sidebar-title { padding:0 22px 20px; font-weight:700; font-size:16px; letter-spacing:.2px; border-bottom:1px solid rgba(255,255,255,.1); margin-bottom:14px; }
  .nav-section { padding:14px 22px 6px; font-size:11px; letter-spacing:.06em; text-transform:uppercase; color:#8fa0c9; font-weight:700; }
  .nav-item { display:flex; align-items:center; gap:12px; padding:12px 22px; margin:2px 10px; border-radius:8px; cursor:pointer; font-size:14px; color:#aebbdb; text-decoration:none; transition:background .15s, color .15s; }
  .nav-item:hover { background:rgba(255,255,255,.07); color:#fff; }
  .nav-item.active { background:linear-gradient(135deg, rgba(76,154,255,.25), rgba(76,154,255,.08)); color:#fff; font-weight:600; box-shadow:inset 0 0 0 1px rgba(76,154,255,.35); }
  .nav-icon { width:18px; text-align:center; font-size:15px; }
  .sidebar-user { margin-top:auto; padding:16px 22px; border-top:1px solid rgba(255,255,255,.1); font-size:12px; color:#c3cbe0; }
  .sidebar-user .role-badge { background:linear-gradient(135deg,#4c9aff,#0052cc); padding:2px 8px; border-radius:9px; font-size:10px; text-transform:uppercase; margin-left:6px; font-weight:700; letter-spacing:.3px; color:#fff; }
  .sidebar-user a { display:inline-flex; align-items:center; gap:5px; margin-top:10px; margin-right:14px; color:#c3cbe0; text-decoration:none; transition:color .15s; }
  .sidebar-user a:hover, .sidebar-user a.active { color:#fff; }

  /* ===== MAIN ===== */
  .main { flex:1; display:flex; flex-direction:column; min-width:0; }
  .topbar { background:#fff; border-bottom:1px solid var(--border); padding:16px 28px 0; }
  .topbar h1 { margin:0; padding-bottom:14px; font-weight:700; font-size:16px; letter-spacing:.1px; }
  .topbar.no-tabs h1 { padding-bottom:16px; }
  .tabs { display:flex; gap:24px; }
  .tabs a { padding:8px 2px 11px; font-size:14px; color:var(--muted); text-decoration:none; border-bottom:2px solid transparent; font-weight:600; transition:color .15s; }
  .tabs a:hover { color:var(--text); }
  .tabs a.active { color:var(--accent); border-bottom-color:var(--accent); }
  .content { padding:22px 28px; overflow-x:auto; animation:fadeIn .2s ease; }

  .banner { border-radius:10px; padding:14px 18px; font-size:13.5px; line-height:1.5; margin-bottom:20px; border:1px solid; }
  .banner-blue   { background:#deebff; border-color:#b3d4ff; }
  .banner-amber  { background:#fffae6; border-color:#ffe380; }
  .banner-indigo { background:#eae6ff; border-color:#c0b6f2; }
  .grid-2 { display:grid; grid-template-columns:minmax(300px,370px) 1fr; gap:20px; align-items:start; }
  @media (max-width:1000px) { .grid-2 { grid-template-columns:1fr; } }

  .card { background:var(--card); border-radius:12px; padding:22px; box-shadow:0 1px 3px rgba(9,30,66,.12); }
  .card-head { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; padding-bottom:14px; margin-bottom:16px; border-bottom:1px solid #f0f0f0; }
  .card-head h2 { margin:0; font-size:15px; font-weight:700; }
  .card-head .sub { font-size:12.5px; color:var(--muted); margin-top:2px; }
  .card-head .tag { font-size:11.5px; color:var(--muted); white-space:nowrap; padding-top:4px; }
  .badge-ok { font-size:12px; font-weight:700; color:#006644; background:#e3fcef; padding:4px 12px; border-radius:12px; white-space:nowrap; }

  .field { margin-bottom:14px; }
  .field label { display:block; font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; }
  .field input, .field select, .field textarea { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; font-family:inherit; background:#fff; color:var(--text); transition:border-color .15s; }
  .field textarea { min-height:90px; resize:vertical; }
  .field input:focus, .field select:focus, .field textarea:focus { outline:none; border-color:var(--accent-light); box-shadow:0 0 0 3px rgba(76,154,255,.15); }
  .field input:disabled { background:#f4f5f7; color:var(--muted); }

  .btn { padding:9px 16px; border-radius:6px; border:1px solid var(--border); background:#fff; color:var(--text); font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:border-color .15s, background .15s; }
  .btn:hover { border-color:var(--accent-light); }
  .btn:disabled { opacity:.5; cursor:not-allowed; }
  .btn-primary, .btn-dark { background:var(--accent); border-color:var(--accent); color:#fff; box-shadow:0 2px 6px rgba(0,82,204,.35); }
  .btn-primary:hover, .btn-dark:hover { background:#0747a6; border-color:#0747a6; }
  .btn-block { width:100%; padding:11px; font-size:14px; }
  .btn-sm { padding:5px 11px; font-size:12px; border:none; background:#ebecf0; }
  .btn-sm:hover { background:#dfe1e6; border:none; }
  .btn-danger { color:var(--danger); }
  .btn-sm.btn-danger:hover { background:#ffebe6; }
  .msg { font-size:12.5px; margin-top:10px; display:none; font-weight:600; }
  .msg.ok { display:block; color:#1f7a4d; }
  .msg.err { display:block; color:var(--danger); }

  table { width:100%; border-collapse:collapse; font-size:14px; }
  th { text-align:left; font-size:11px; letter-spacing:.3px; text-transform:uppercase; color:var(--muted); font-weight:700; background:#f8f9fb; padding:11px 14px; }
  th:first-child { border-radius:8px 0 0 8px; } th:last-child { border-radius:0 8px 8px 0; }
  td { padding:13px 14px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
  tr:last-child td { border-bottom:none; }
  td.num, th.num { text-align:right; }
  .row-actions { display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap; }
  .person { display:flex; align-items:center; gap:12px; }
  .av-sm { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; background:var(--accent); color:#fff; box-shadow:0 0 0 2px #fff; }
  .person b { display:block; font-size:14px; font-weight:600; }
  .person span { font-size:12px; color:var(--muted); }
  .chip { display:inline-block; font-size:12px; font-weight:600; padding:3px 10px; border-radius:10px; background:#f0f2f7; color:var(--muted); }
  .chip-blue { background:#deebff; color:#0747a6; }
  .dot-ok, .dot-off { font-size:12.5px; font-weight:600; }
  .dot-ok { color:#1f7a4d; } .dot-off { color:var(--muted); }
  .dot-ok::before, .dot-off::before { content:''; display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:7px; background:currentColor; }
  .dot-ok::before { background:var(--success); }
  .muted { color:var(--muted); }
  .empty { text-align:center; padding:26px; color:var(--muted); font-size:13px; }

  .modal-backdrop { position:fixed; inset:0; background:rgba(9,30,66,.6); backdrop-filter:blur(2px); display:none; align-items:flex-start; justify-content:center; overflow-y:auto; z-index:100; padding:64px 16px; }
  .modal-backdrop.open { display:flex; animation:fadeIn .15s ease; }
  .modal { background:#fff; border-radius:14px; padding:26px 28px; width:100%; max-width:440px; box-shadow:0 20px 48px rgba(9,30,66,.35); }
  .modal h3 { margin:0 0 16px; font-size:18px; font-weight:700; }
  .modal-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:18px; }
</style>
</head>
<body>
<div class="app">

  <div class="sidebar">
    <div class="sidebar-title">Project Management</div>

    <a class="nav-item" href="<?= site_url('project') ?>"><span class="nav-icon">&#9635;</span> Dashboard</a>
    <a class="nav-item" href="<?= site_url('project/planningPage') ?>"><span class="nav-icon">&#9776;</span> Planning</a>
    <a class="nav-item" href="<?= site_url('project/ganttGlobalPage') ?>"><span class="nav-icon">&#9636;</span> Gantt Chart</a>

    <?php if ($can_manage_users || $can_manage_roles || $can_view_audit): ?>
    <div class="nav-section">Pengaturan sistem</div>
    <?php if ($can_manage_users): ?>
    <a class="nav-item <?= $active === 'user' ? 'active' : '' ?>" href="<?= site_url('user') ?>"><span class="nav-icon">&#9786;</span> Manajemen User</a>
    <?php endif; ?>
    <?php if ($can_manage_roles): ?>
    <a class="nav-item <?= $active === 'role' ? 'active' : '' ?>" href="<?= site_url('role') ?>"><span class="nav-icon">&#9873;</span> Kelola Role</a>
    <a class="nav-item <?= $active === 'permission' ? 'active' : '' ?>" href="<?= site_url('permission') ?>"><span class="nav-icon">&#9881;</span> Hak Akses</a>
    <?php endif; ?>
    <?php if ($can_view_audit): ?>
    <a class="nav-item <?= $active === 'audit' ? 'active' : '' ?>" href="<?= site_url('audit') ?>"><span class="nav-icon">&#9783;</span> Audit Log</a>
    <?php endif; ?>
    <?php endif; ?>

    <div class="sidebar-user">
      <span id="sb-name"><?= html_escape($user_name) ?></span><span class="role-badge"><?= html_escape($role_name) ?></span>
      <br>
      <a href="<?= site_url('akun') ?>" class="<?= $active === 'account' ? 'active' : '' ?>">Akun saya</a>
      <a href="<?= site_url('auth/logout') ?>">Logout</a>
    </div>
  </div>

  <div class="main">
    <div class="topbar <?= $show_tabs ? '' : 'no-tabs' ?>">
      <h1><?= html_escape($page_title) ?></h1>
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
