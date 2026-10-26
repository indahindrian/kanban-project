<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard - Project Management</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
  :root {
    --bg: #f0f2f7;
    --card-bg: #ffffff;
    --border: #dfe1e6;
    --text: #172b4d;
    --muted: #6b778c;
    --accent: #0052cc;
    --accent-light: #4c9aff;
    --sidebar-w: 232px;
    --danger: #de350b;
    --success: #36b37e;
    --warning: #ffab00;
  }
  * { box-sizing: border-box; }
  body { margin:0; font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: var(--text); -webkit-font-smoothing:antialiased; }
  ::-webkit-scrollbar { width:9px; height:9px; }
  ::-webkit-scrollbar-thumb { background:#c1c7d0; border-radius:8px; }
  ::-webkit-scrollbar-track { background:transparent; }

  .app { display:flex; min-height:100vh; }

  /* ===== SIDEBAR ===== */
  .sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background:linear-gradient(180deg,#1a2b4c,#131f38); color:#fff; padding:20px 0; display:flex; flex-direction:column; box-shadow:2px 0 8px rgba(9,30,66,.15); }
  .sidebar-title { padding:0 22px 20px; font-weight:700; font-size:16px; letter-spacing:.2px; border-bottom:1px solid rgba(255,255,255,.1); margin-bottom:14px; }
  .sidebar-user { margin-top:auto; padding:16px 22px; border-top:1px solid rgba(255,255,255,.1); font-size:12px; color:#c3cbe0; }
  .sidebar-user .role-badge { background:linear-gradient(135deg,#4c9aff,#0052cc); padding:2px 8px; border-radius:9px; font-size:10px; text-transform:uppercase; margin-left:6px; font-weight:700; letter-spacing:.3px; }
  .sidebar-user a { display:inline-flex; align-items:center; gap:5px; margin-top:10px; color:#c3cbe0; text-decoration:none; transition:color .15s; }
  .sidebar-user a:hover { color:#fff; }
  .nav-item { display:flex; align-items:center; gap:12px; padding:12px 22px; margin:2px 10px; border-radius:8px; cursor:pointer; font-size:14px; color:#aebbdb; transition:background .15s, color .15s; }
  .nav-item:hover { background:rgba(255,255,255,.07); color:#fff; }
  .nav-item.active { background:linear-gradient(135deg, rgba(76,154,255,.25), rgba(76,154,255,.08)); color:#fff; font-weight:600; box-shadow:inset 0 0 0 1px rgba(76,154,255,.35); }
  .nav-icon { width:18px; text-align:center; font-size:15px; }

  /* ===== MAIN ===== */
  .main { flex:1; display:flex; flex-direction:column; min-width:0; }
  .topbar { background:#fff; border-bottom:1px solid var(--border); padding:16px 28px; font-weight:700; font-size:16px; letter-spacing:.1px; }
  .content { padding:22px 28px; overflow-x:auto; }
  .panel { display:none; }
  .panel.active { display:block; animation:fadeIn .2s ease; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:none; } }

  /* ===== KANBAN ===== */
  .board { display:flex; gap:18px; align-items:flex-start; }
  .column { background:#e9ebf3; border-radius:12px; width:284px; min-width:284px; padding:12px; flex-shrink:0; box-shadow:0 1px 2px rgba(9,30,66,.08); }
  .column-header { display:flex; justify-content:space-between; align-items:center; padding:6px 8px 12px; font-weight:700; font-size:13px; letter-spacing:.2px; text-transform:uppercase; }
  .column-header .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:7px; }
  .badge { background:#dfe1e6; border-radius:12px; padding:2px 9px; font-size:12px; color:var(--muted); font-weight:600; }
  .col-no_status .column-header .dot { background:#8993a4; }
  .col-processing .column-header .dot { background:var(--warning); }
  .col-processing .column-header { color:#a35a00; }
  .col-closed .column-header .dot { background:var(--success); }
  .col-closed .column-header { color:#1f7a4d; }
  .task-list { min-height: 40px; }
  .task-card { background:var(--card-bg); border-radius:10px; padding:12px 14px; margin-bottom:10px; box-shadow:0 1px 2px rgba(9,30,66,.15), 0 0 0 1px rgba(9,30,66,.03); cursor:grab; transition:box-shadow .15s ease, transform .15s ease; }
  .task-card-overdue { box-shadow:0 1px 2px rgba(9,30,66,.15), inset 3px 0 0 var(--danger); }
  .task-card:hover { box-shadow:0 6px 14px rgba(9,30,66,.18), 0 0 0 1px rgba(9,30,66,.04); transform:translateY(-2px); }
  .task-card:active { cursor:grabbing; }
  .task-title { font-size:14px; font-weight:500; margin-bottom:10px; line-height:1.35; }
  .task-footer { display:flex; justify-content:space-between; align-items:center; margin-top:10px; }
  .due { font-size:11px; color:var(--muted); background:#f0f2f7; padding:3px 8px; border-radius:10px; font-weight:600; }
  .due-overdue { background:var(--danger); color:#fff; }
  .avatar { width:25px; height:25px; border-radius:50%; background:var(--accent); color:#fff; font-size:11px; display:flex; align-items:center; justify-content:center; font-weight:700; box-shadow:0 0 0 2px #fff; }
  .progress-bar { height:6px; background:#dfe1e6; border-radius:6px; overflow:hidden; }
  .progress-fill { height:100%; background:linear-gradient(90deg,var(--accent-light),var(--accent)); border-radius:6px; transition:width .25s ease; }
  .empty { color:var(--muted); font-size:12px; padding:10px 4px; }
  .card-avatars { display:flex; }
  .card-avatars .avatar { margin-left:-7px; }
  .card-avatars .avatar:first-child { margin-left:0; }
  .add-card-btn { width:100%; text-align:left; background:none; border:2px dashed transparent; color:var(--muted); padding:9px 8px; cursor:pointer; font-size:13px; font-weight:500; border-radius:8px; transition:background .15s, border-color .15s; }
  .add-card-btn:hover { background:rgba(9,30,66,.06); border-color:rgba(9,30,66,.12); color:var(--text); }

  /* ===== GANTT ===== */
  .gantt-toolbar { display:flex; gap:8px; margin-bottom:14px; }
  .gantt-toolbar button { border:1px solid var(--border); background:#fff; padding:7px 16px; border-radius:6px; cursor:pointer; font-size:13px; font-weight:500; transition:all .15s; }
  .gantt-toolbar button:hover { border-color:var(--accent-light); }
  .gantt-toolbar button.active { background:var(--accent); color:#fff; border-color:var(--accent); }
  .bar-wrapper .bar { fill: #4c9aff !important; }
  .bar-wrapper .bar-progress { fill: #0052cc !important; }

  /* ===== SCOREBOARD ===== */
  .score-wrap { display:flex; gap:20px; flex-wrap:wrap; }
  .chart-box { background:#fff; border-radius:12px; padding:18px; flex:1; min-width:320px; box-shadow:0 1px 3px rgba(9,30,66,.12); }
  table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(9,30,66,.12); }
  th, td { text-align:left; padding:12px 16px; font-size:14px; border-bottom:1px solid #f0f0f0; }
  th { background:#f8f9fb; color:#6b778c; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.3px; }
  tbody tr:hover { background:#f8f9fb; }
  tbody tr:last-child td { border-bottom:none; }

  /* ===== LABEL CHIP (di card) ===== */
  .label-row { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:9px; }
  .label-chip { height:9px; width:34px; border-radius:5px; }
  .card-meta-row { display:flex; gap:8px; align-items:center; font-size:11px; color:var(--muted); }
  .card-meta-row .icon-badge { display:flex; align-items:center; gap:3px; background:#f0f2f7; padding:3px 7px; border-radius:10px; font-weight:600; }

  /* ===== MODAL CARD DETAIL ===== */
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(9,30,66,.6); backdrop-filter:blur(2px); z-index:100; align-items:flex-start; justify-content:center; overflow-y:auto; padding:48px 16px; }
  .modal-overlay.open { display:flex; animation:fadeIn .15s ease; }
  .modal-box { background:#fff; border-radius:14px; width:100%; max-width:660px; padding:26px 28px; position:relative; box-shadow:0 20px 48px rgba(9,30,66,.35); }
  .modal-close { position:absolute; top:18px; right:18px; width:30px; height:30px; background:#f0f2f7; border:none; border-radius:50%; font-size:18px; cursor:pointer; color:var(--muted); transition:background .15s, color .15s; }
  .modal-close:hover { background:#dfe1e6; color:var(--text); }
  .modal-header-row { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
  .status-select { padding:7px 12px; border:none; border-radius:6px; font-size:13px; font-weight:700; background:#dfe1e6; color:var(--text); cursor:pointer; }
  .status-select.st-no_status { background:#e4e6ea; color:#42526e; }
  .status-select.st-processing { background:#fff0d9; color:#a35a00; }
  .status-select.st-closed { background:#d8f4e8; color:#1f7a4d; }
  .complete-toggle { width:28px; height:28px; border-radius:50%; border:2px solid var(--border); cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:#fff; color:#fff; font-size:14px; transition:all .15s; }
  .complete-toggle:hover { border-color:var(--success); }
  .complete-toggle.done { background:var(--success); border-color:var(--success); box-shadow:0 2px 6px rgba(54,179,126,.4); }
  .modal-title-input { width:100%; font-size:21px; font-weight:700; border:none; padding:6px 8px; margin:2px 0 16px -8px; border-radius:6px; transition:background .15s; }
  .modal-title-input:hover { background:#f4f5f7; }
  .modal-title-input:focus { outline:2px solid var(--accent-light); background:#fff; }
  .modal-section { margin-bottom: 24px; }
  .modal-section-label { font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; gap:8px; }
  .modal-section-label .icon { margin-right:5px; opacity:.8; }
  .modal-textarea { width:100%; min-height:76px; border:1px solid var(--border); border-radius:8px; padding:11px; font-family:inherit; font-size:14px; resize:vertical; transition:border-color .15s; }
  .modal-textarea:focus { outline:none; border-color:var(--accent-light); box-shadow:0 0 0 3px rgba(76,154,255,.15); }
  .due-date-input { padding:7px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px; }
  .btn-sm { border:none; background:#ebecf0; border-radius:6px; padding:6px 13px; font-size:12px; cursor:pointer; font-weight:600; color:var(--text); transition:background .15s, transform .1s; }
  .btn-sm:hover { background:#dfe1e6; }
  .btn-sm:active { transform:scale(.97); }
  .btn-sm-primary { background:var(--accent); color:#fff; box-shadow:0 2px 6px rgba(0,82,204,.35); }
  .btn-sm-primary:hover { background:#0747a6; }
  .btn-delete { border:none; background:none; color:var(--muted); font-size:12px; cursor:pointer; padding:4px 8px; border-radius:5px; font-weight:600; transition:background .15s, color .15s; }
  .btn-delete:hover { background:#ffebe6; color:var(--danger); }

  /* labels & members picker */
  .pill-picker-row { display:flex; flex-wrap:wrap; gap:7px; }
  .label-pill { display:flex; align-items:center; gap:6px; padding:6px 12px; border-radius:5px; font-size:12px; font-weight:600; color:#fff; cursor:pointer; opacity:.4; transition:opacity .15s, transform .1s; }
  .label-pill:hover { opacity:.75; }
  .label-pill.selected { opacity:1; box-shadow:0 0 0 2px rgba(9,30,66,.15); }
  .label-pill.selected::before { content:"✓"; }
  .label-create-form { display:flex; gap:8px; margin-top:12px; align-items:center; background:#f8f9fb; padding:10px; border-radius:8px; }
  .label-create-form input[type=text] { flex:1; padding:6px 9px; border:1px solid var(--border); border-radius:5px; font-size:12px; }
  .member-pill { display:flex; align-items:center; gap:7px; padding:5px 12px 5px 5px; border-radius:20px; font-size:12px; font-weight:500; cursor:pointer; background:#f0f2f7; transition:background .15s, transform .1s; }
  .member-pill:hover { background:#e4e6ea; }
  .member-pill.selected { background:var(--accent); color:#fff; }
  .member-pill.selected .avatar { box-shadow:0 0 0 2px rgba(255,255,255,.6); }

  /* checklist */
  .checklist-block { margin-bottom:14px; padding:14px 16px; background:#f8f9fb; border-radius:10px; }
  .checklist-title-row { display:flex; justify-content:space-between; align-items:center; font-size:13px; font-weight:700; margin-bottom:8px; }
  .checklist-progress-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
  .checklist-progress-row .progress-bar { flex:1; }
  .checklist-progress-row .progress-fill.full { background:linear-gradient(90deg,#57d9a3,var(--success)); }
  .checklist-item-row { display:flex; align-items:center; gap:9px; padding:5px 6px; border-radius:6px; transition:background .12s; }
  .checklist-item-row:hover { background:#eef0f5; }
  .checklist-item-row input[type=checkbox] { width:16px; height:16px; accent-color: var(--success); cursor:pointer; }
  .checklist-item-row.done span { text-decoration:line-through; color:var(--muted); }
  .checklist-item-row .del-btn { margin-left:auto; background:none; border:none; color:var(--muted); cursor:pointer; font-size:12px; opacity:0; padding:3px 7px; border-radius:5px; transition:opacity .12s, background .12s, color .12s; }
  .checklist-item-row:hover .del-btn { opacity:1; }
  .checklist-item-row .del-btn:hover { background:#ffebe6; color:var(--danger); }
  .checklist-add-row { display:flex; gap:7px; margin-top:9px; }
  .checklist-add-row input { flex:1; padding:7px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px; background:#fff; }

  /* attachments */
  .attachment-row { display:flex; align-items:center; gap:10px; padding:8px 10px; font-size:13px; border-radius:8px; transition:background .12s; }
  .attachment-row:hover { background:#f8f9fb; }
  .attachment-row .icon { width:30px; height:30px; border-radius:7px; background:#e4ecfc; color:var(--accent); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; }
  .attachment-row a { color:var(--text); text-decoration:none; font-weight:500; }
  .attachment-row a:hover { color:var(--accent); text-decoration:underline; }
  .attachment-actions { display:flex; gap:8px; margin-top:10px; }
  /* ===== LAYOUT PROJECT (breadcrumb, kolom melebar, pengaturan) ===== */
  .column { flex:1 1 300px; width:auto; max-width:440px; }
  .card-avatars .avatar { margin-left:-4px; }
  .sidebar-back { display:block; padding:0 22px 14px; font-size:12px; color:#8fa0c9; text-decoration:none; }
  .sidebar-back:hover { color:#fff; }
  .nav-sep { height:1px; background:rgba(255,255,255,.1); margin:10px 22px; }
  .crumb { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:500; color:var(--muted); }
  .crumb a { color:var(--muted); text-decoration:none; }
  .crumb a:hover { color:var(--accent); text-decoration:underline; }
  .crumb .sep { color:#c1c7d0; }
  .crumb #panel-title { color:var(--text); font-weight:700; }
  .settings { max-width:760px; }
  .settings .s-card { background:#fff; border-radius:12px; padding:22px 24px; margin-bottom:16px; box-shadow:0 1px 3px rgba(9,30,66,.12); }
  .settings label { display:block; font-size:12px; font-weight:600; color:var(--muted); margin-bottom:6px; }
  .settings input, .settings textarea, .settings select { width:100%; padding:9px 11px; border:1px solid var(--border); border-radius:6px; font-size:14px; font-family:inherit; color:var(--text); background:#fff; }
  .settings input:disabled, .settings textarea:disabled, .settings select:disabled { background:#f4f5f7; color:var(--muted); }
  .settings textarea { resize:vertical; min-height:72px; }
  .settings .s-field { margin-bottom:16px; }
  .settings .s-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .settings .s-dates { padding:16px; background:#f8f9fb; border-radius:8px; margin-bottom:14px; }
  .settings .s-dates:last-child { margin-bottom:0; }
  .settings .s-dates-title { font-size:12px; font-weight:700; color:var(--text); margin-bottom:12px; }
  .settings .s-note { font-size:12px; margin-top:4px; }
  .settings .late { color:#bf2600; } .settings .ontime { color:#006644; } .settings .pending { color:var(--muted); }
  .settings .s-actions { position:sticky; bottom:0; display:flex; align-items:center; gap:12px; padding:14px 0; background:var(--bg); }
  .settings .s-save { border:none; border-radius:6px; padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer; background:var(--accent); color:#fff; }
  .settings .s-save:disabled { opacity:.6; cursor:wait; }
  .settings .s-msg { font-size:13px; font-weight:600; }
  @media (max-width:720px) { .settings .s-row { grid-template-columns:1fr; } }
  /* ===== TAMBAH TASK ===== */
  .topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; }
  .btn-add-task { border:none; background:var(--accent); color:#fff; border-radius:6px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; transition:background .15s; }
  .btn-add-task:hover { background:#0747a6; }
  .composer textarea { width:100%; min-height:62px; resize:vertical; border:2px solid var(--accent-light); border-radius:10px; padding:10px 12px; font:inherit; font-size:14px; line-height:1.35; color:var(--text); background:#fff; box-shadow:0 1px 2px rgba(9,30,66,.15); }
  .composer textarea:focus { outline:none; border-color:var(--accent); }
  .composer-actions { display:flex; align-items:center; gap:8px; margin-top:8px; }
  .composer-actions .btn-add { border:none; background:var(--accent); color:#fff; border-radius:6px; padding:7px 14px; font-size:13px; font-weight:600; cursor:pointer; }
  .composer-actions .btn-add:disabled { opacity:.6; cursor:wait; }
  .composer-actions .btn-cancel { border:none; background:none; color:var(--muted); font-size:13px; font-weight:600; cursor:pointer; padding:7px 8px; }
  .composer-actions .btn-cancel:hover { color:var(--text); }
  .composer-hint { margin-left:auto; font-size:11px; color:var(--muted); }
  .composer-err { color:var(--danger); font-size:12px; font-weight:600; margin-top:6px; }
  .topbar-actions { display:flex; gap:8px; }
  .btn-detail { border:1px solid var(--border); background:#fff; color:var(--text); border-radius:6px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; transition:border-color .15s; }
  .btn-detail:hover { border-color:var(--accent-light); }
  .drawer-overlay { display:none; position:fixed; inset:0; background:rgba(9,30,66,.45); z-index:90; }
  .drawer-overlay.open { display:block; }
  .drawer { position:fixed; top:0; right:0; bottom:0; width:min(540px,100vw); background:var(--bg); z-index:95; display:flex; flex-direction:column; box-shadow:-8px 0 32px rgba(9,30,66,.25); transform:translateX(100%); visibility:hidden; transition:transform .22s ease, visibility 0s .22s; }
  .drawer.open { transform:none; visibility:visible; transition:transform .22s ease; }
  .drawer-head { display:flex; justify-content:space-between; align-items:center; padding:16px 20px; background:#fff; border-bottom:1px solid var(--border); font-size:16px; }
  .drawer-body { flex:1; overflow-y:auto; padding:20px 20px 0; }
  .drawer .settings { max-width:none; }
  @media (prefers-reduced-motion:reduce) { .drawer, .drawer.open { transition:none; } }
  /* ===== FORM TASK LENGKAP ===== */
  .nt-heading { margin:0 0 18px; font-size:19px; }
  .nt-field { margin-bottom:14px; }
  .nt-field > label { display:block; font-size:12px; font-weight:600; color:var(--muted); margin-bottom:6px; }
  .nt-field input, .nt-field textarea, .nt-field select { width:100%; padding:9px 11px; border:1px solid var(--border); border-radius:6px; font:inherit; font-size:14px; color:var(--text); background:#fff; }
  .nt-field textarea { resize:vertical; min-height:70px; }
  .nt-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  .nt-picks { display:flex; flex-wrap:wrap; gap:8px; }
  .nt-none { font-size:12px; color:var(--muted); }
  .pick { display:inline-flex; align-items:center; gap:6px; padding:5px 11px; border:1px solid var(--border); border-radius:16px; background:#fff; font-size:13px; cursor:pointer; color:var(--text); }
  .pick:hover { border-color:var(--accent-light); }
  .pick.on { background:#deebff; border-color:var(--accent); color:var(--accent); font-weight:600; }
  .pick-label { background:var(--c); border-color:transparent; color:#fff; opacity:.55; }
  .pick-label.on { background:var(--c); color:#fff; opacity:1; box-shadow:0 0 0 2px #fff, 0 0 0 4px var(--c); }
  .nt-err { min-height:18px; color:var(--danger); font-size:13px; font-weight:600; margin-bottom:8px; }
  .nt-actions { display:flex; justify-content:flex-end; gap:8px; }
  .nt-actions .nt-cancel { border:none; background:#ebecf0; color:var(--text); border-radius:6px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; }
  @media (max-width:520px) { .nt-row { grid-template-columns:1fr; } }
</style>
</head>
<body>

<div class="app">

  <div class="sidebar">
    <div class="sidebar-title" id="sidebar-title">Project Management</div>
    <a class="sidebar-back" href="<?= site_url('project') ?>">&larr; Semua project</a>
    <div class="nav-item active" data-panel="kanban" onclick="switchPanel('kanban')">
      <span class="nav-icon">&#9635;</span> Kanban
    </div>
    <div class="nav-item" data-panel="gantt" onclick="switchPanel('gantt')">
      <span class="nav-icon">&#9776;</span> GANTT
    </div>
    <div class="nav-item" data-panel="scoreboard" onclick="switchPanel('scoreboard')">
      <span class="nav-icon">&#9733;</span> Scoreboard
    </div>

    <div class="sidebar-user">
      <?= html_escape($user_name) ?><span class="role-badge"><?= $is_atasan ? 'Atasan' : 'Anggota' ?></span>
      <a href="<?= site_url('auth/logout') ?>">Logout</a>
    </div>
  </div>

  <div class="main">
    <div class="topbar">
      <nav class="crumb">
        <a href="<?= site_url('project') ?>">Projects</a><span class="sep">/</span>
        <span id="crumb-project">...</span><span class="sep">/</span>
        <span id="panel-title">Kanban</span>
      </nav>
      <div class="topbar-actions">
        <button class="btn-detail" onclick="openDetailPanel()">Detail project</button>
        <button class="btn-add-task" id="btn-add-task" onclick="openNewTaskModal('no_status')">+ Task baru</button>
      </div>
    </div>

    <div class="content">

      <div class="panel active" id="panel-kanban">
        <div class="board" id="kanban-board">
          <div class="empty">Memuat data...</div>
        </div>
      </div>

      <div class="panel" id="panel-gantt">
        <div class="gantt-toolbar">
          <button onclick="ganttChart && ganttChart.change_view_mode('Day')">Day</button>
          <button onclick="ganttChart && ganttChart.change_view_mode('Week')" class="active">Week</button>
          <button onclick="ganttChart && ganttChart.change_view_mode('Month')">Month</button>
        </div>
        <svg id="gantt"></svg>
      </div>

      <div class="panel" id="panel-scoreboard">
        <div class="score-wrap">
          <div class="chart-box">
            <canvas id="scoreChart" height="220"></canvas>
          </div>
          <div style="flex:1; min-width:320px;">
            <table>
              <thead>
                <tr><th>Rank</th><th>Anggota</th><th>Poin</th><th>Selesai</th><th>Tepat Waktu</th></tr>
              </thead>
              <tbody id="score-table-body"></tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>

<!-- ===== MODAL: TASK/CARD DETAIL ===== -->
<div class="modal-overlay" id="task-modal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal()">&times;</button>

    <div class="modal-header-row">
      <button class="complete-toggle" id="complete-toggle" onclick="toggleComplete()" title="Tandai selesai">&#10003;</button>
      <select class="status-select" id="modal-status" onchange="changeStatus(this.value)">
        <option value="no_status">No status</option>
        <option value="processing">Processing</option>
        <option value="closed">Closed</option>
      </select>
    </div>

    <input type="text" class="modal-title-input" id="modal-title" placeholder="Judul task" onblur="saveTitle()">

    <div class="label-row" id="modal-label-preview" style="margin-bottom:16px;"></div>

    <!-- LABELS -->
    <div class="modal-section">
      <div class="modal-section-label"><span><span class="icon">&#127991;&#65039;</span>Label</span></div>
      <div class="pill-picker-row" id="label-picker"></div>
      <div class="label-create-form">
        <input type="color" id="new-label-color" value="#0052CC" style="width:32px; height:30px; padding:0; border:1px solid var(--border); border-radius:5px;">
        <input type="text" id="new-label-name" placeholder="Nama label baru...">
        <button class="btn-sm" onclick="createNewLabel()">+ Buat label</button>
      </div>
    </div>

    <!-- DUE DATE -->
    <div class="modal-section">
      <div class="modal-section-label"><span><span class="icon">&#128197;</span>Due date</span></div>
      <input type="date" class="due-date-input" id="modal-due-date" onchange="saveDueDate(this.value)">
    </div>

    <!-- MEMBERS -->
    <div class="modal-section">
      <div class="modal-section-label"><span><span class="icon">&#128101;</span>Members</span></div>
      <div class="pill-picker-row" id="member-picker"></div>
    </div>

    <!-- DESCRIPTION -->
    <div class="modal-section">
      <div class="modal-section-label"><span><span class="icon">&#128221;</span>Deskripsi</span></div>
      <textarea class="modal-textarea" id="modal-description"></textarea>
      <div style="margin-top:8px;">
        <button class="btn-sm btn-sm-primary" onclick="saveDescription()">Simpan</button>
      </div>
    </div>

    <!-- CHECKLIST -->
    <div class="modal-section">
      <div class="modal-section-label">
        <span><span class="icon">&#9745;&#65039;</span>Checklist</span>
        <button class="btn-sm" onclick="addChecklist()">+ Tambah checklist</button>
      </div>
      <div id="checklist-blocks"></div>
    </div>

    <!-- ATTACHMENTS -->
    <div class="modal-section">
      <div class="modal-section-label"><span><span class="icon">&#128206;</span>Attachment</span></div>
      <div id="attachment-list"></div>
      <div class="attachment-actions">
        <button class="btn-sm" onclick="promptAddLink()">&#128279; Tambah link</button>
        <label class="btn-sm" style="cursor:pointer;">
          &#128206; Upload file
          <input type="file" id="file-input" style="display:none;" onchange="handleFileSelected(event)">
        </label>
      </div>
    </div>

  </div>
</div>

<div class="modal-overlay" id="new-task-modal" onclick="if (event.target === this) closeNewTaskModal()">
  <div class="modal-box" style="max-width:560px" role="dialog" aria-label="Task baru">
    <button class="modal-close" onclick="closeNewTaskModal()" aria-label="Tutup">&times;</button>
    <h2 class="nt-heading">Task baru</h2>
    <div class="nt-field"><label for="nt-title">Judul task</label>
      <input id="nt-title" type="text" maxlength="200" onkeydown="if (event.key === 'Enter') { event.preventDefault(); submitNewTask(); }"></div>
    <div class="nt-field"><label for="nt-desc">Deskripsi</label><textarea id="nt-desc"></textarea></div>
    <div class="nt-row">
      <div class="nt-field"><label for="nt-status">Status</label>
        <select id="nt-status"><option value="no_status">No Status</option><option value="processing">Processing</option><option value="closed">Closed</option></select></div>
      <div class="nt-field"><label for="nt-due">Tenggat</label><input id="nt-due" type="date"></div>
    </div>
    <div class="nt-field"><label>Anggota</label><div class="nt-picks" id="nt-users"></div></div>
    <div class="nt-field"><label>Label</label><div class="nt-picks" id="nt-labels"></div></div>
    <div class="nt-err" id="nt-err" role="alert"></div>
    <div class="nt-actions">
      <button class="nt-cancel" onclick="closeNewTaskModal()">Batal</button>
      <button class="btn-add-task" id="nt-submit">Tambah task</button>
    </div>
  </div>
</div>

<div class="drawer-overlay" id="drawer-overlay" onclick="closeDetailPanel()"></div>
<aside class="drawer" id="detail-drawer" aria-hidden="true" aria-label="Detail project">
  <div class="drawer-head">
    <strong>Detail project</strong>
    <button class="modal-close" style="position:static" onclick="closeDetailPanel()" aria-label="Tutup">&times;</button>
  </div>
  <div class="drawer-body"><div class="settings" id="settings-wrap"><div class="empty">Memuat data...</div></div></div>
</aside>

<script>
const panelTitles = { kanban: 'Kanban', gantt: 'GANTT Chart', scoreboard: 'Scoreboard' };
const loaded = { kanban: false, gantt: false, scoreboard: false };
let currentPanel = 'kanban';
let ganttChart = null;

function switchPanel(name) {
  document.querySelectorAll('.nav-item').forEach(el => el.classList.toggle('active', el.dataset.panel === name));
  document.querySelectorAll('.panel').forEach(el => el.classList.remove('active'));
  document.getElementById('panel-' + name).classList.add('active');
  document.getElementById('panel-title').innerText = panelTitles[name];
  currentPanel = name;
  history.replaceState(null, '', '#' + name);
  document.getElementById('btn-add-task').style.display = name === 'kanban' ? '' : 'none';

  if (!loaded[name]) {
    if (name === 'kanban') loadKanban();
    if (name === 'gantt') loadGantt();
    if (name === 'scoreboard') loadScoreboard();
    loaded[name] = true;
  }
}

// ===== KANBAN =====
const projectId = <?= json_encode($project_id ?? null) ?>;
const STATUS_LABELS = { no_status: 'No status', processing: 'Processing', closed: 'Closed' };
let allLabels = [];
let allUsers = [];
let activeTaskId = null;
let currentTask = null;

async function api(url, params = {}) {
  const res = await fetch('<?= site_url('project') ?>/' + url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(params).toString()
  });
  return res.json();
}

const AVATAR_PALETTE = ['#0052CC', '#36B37E', '#FF5630', '#6554C0', '#00875A', '#FF8B00', '#00B8D9', '#DE350B'];
function avatarColor(id) {
  const n = Number(id) || 0;
  return AVATAR_PALETTE[n % AVATAR_PALETTE.length];
}

function initials(name) {
  return (name || '').trim().charAt(0).toUpperCase();
}

function avatarHtml(m, size) {
  const style = size ? `width:${size}px;height:${size}px;font-size:${Math.round(size * 0.42)}px;` : '';
  return `<span class="avatar" style="background:${avatarColor(m.id)};${style}" title="${m.name}">${initials(m.name)}</span>`;
}

function taskCardHtml(t) {
  const labels = t.labels || [];
  const members = t.members || [];
  const today = new Date().toISOString().slice(0, 10);
  const isOverdue = t.due_date && t.status !== 'closed' && t.due_date < today;
  return `
    <div class="task-card ${isOverdue ? 'task-card-overdue' : ''}" data-id="${t.id}" onclick="openTaskModal(${t.id})">
      ${labels.length ? `<div class="label-row">${labels.map(l => `<div class="label-chip" style="background:${l.color}" title="${l.name}"></div>`).join('')}</div>` : ''}
      <div class="task-title">${settingsEsc(t.title)}</div>
      <div class="progress-bar"><div class="progress-fill" style="width:${t.progress}%"></div></div>
      <div class="task-footer">
        <div class="card-meta-row">
          ${t.due_date ? `<span class="due ${isOverdue ? 'due-overdue' : ''}" title="${isOverdue ? 'Sudah lewat deadline' : ''}">&#128197; ${t.due_date}${isOverdue ? ' &middot; Terlambat' : ''}</span>` : ''}
          ${t.checklist_total ? `<span class="icon-badge">&#9745;&#65039; ${t.checklist_done}/${t.checklist_total}</span>` : ''}
          ${t.attachment_count ? `<span class="icon-badge">&#128206; ${t.attachment_count}</span>` : ''}
        </div>
        <div class="card-avatars">
          ${members.slice(0, 3).map(m => avatarHtml(m)).join('')}
        </div>
      </div>
    </div>`;
}

async function loadKanban() {
  if (!projectId) return;
  const res = await fetch('<?= site_url('project/getKanban') ?>?project_id=' + projectId);
  const json = await res.json();
  if (json.code !== '00') return;

  if (json.data.project_name) {
    document.getElementById('sidebar-title').innerText = json.data.project_name;
    document.getElementById('crumb-project').innerText = json.data.project_name;
  }

  allLabels = json.data.all_labels || [];
  allUsers = json.data.all_users || [];

  renderBoard(json.data.columns);
  if (reopenComposer) { const st = reopenComposer; reopenComposer = null; openComposer(st); }
}

function renderBoard(columns) {
  const board = document.getElementById('kanban-board');
  board.innerHTML = '';
  columns.forEach(col => {
    const colEl = document.createElement('div');
    colEl.className = 'column col-' + col.status_key;
    colEl.innerHTML = `
      <div class="column-header"><span><span class="dot"></span>${col.label}</span><span class="badge">${col.tasks.length}</span></div>
      <div class="task-list" data-status="${col.status_key}">
        ${col.tasks.length === 0 ? '<div class="empty">Tidak ada task</div>' : col.tasks.map(taskCardHtml).join('')}
      </div>
      <div class="composer-slot" data-status="${col.status_key}"><button class="add-card-btn" onclick="openComposer('${col.status_key}')">+ Tambah card</button></div>`;
    board.appendChild(colEl);
  });

  document.querySelectorAll('.task-list').forEach(el => {
    new Sortable(el, {
      group: 'kanban',
      animation: 150,
      onEnd: async (evt) => {
        const taskId = evt.item.dataset.id;
        const newStatus = evt.to.dataset.status;
        await api('updateTaskStatus', { task_id: taskId, new_status: newStatus });
        loadKanban();
      }
    });
  });
}

let reopenComposer = null;

function closeComposers() {
  document.querySelectorAll('.composer-slot').forEach(slot => {
    const st = slot.dataset.status;
    slot.innerHTML = `<button class="add-card-btn" onclick="openComposer('${st}')">+ Tambah card</button>`;
  });
}

function openComposer(statusKey) {
  switchPanel('kanban');
  closeComposers();
  const slot = document.querySelector(`.composer-slot[data-status="${statusKey}"]`);
  if (!slot) return;
  slot.innerHTML = `
    <div class="composer">
      <textarea placeholder="Tulis judul task..." maxlength="200"></textarea>
      <div class="composer-actions">
        <button class="btn-add" type="button">Tambah task</button>
        <button class="btn-cancel" type="button">Batal</button>
        <button class="btn-cancel btn-full" type="button">Isi lengkap</button>
      </div>
    </div>`;
  const ta = slot.querySelector('textarea');
  slot.querySelector('.btn-add').onclick = () => submitComposer(statusKey, slot);
  slot.querySelector('.btn-cancel').onclick = closeComposers;
  slot.querySelector('.btn-full').onclick = () => openNewTaskModal(statusKey, slot.querySelector('textarea').value);
  ta.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); submitComposer(statusKey, slot); }
    if (e.key === 'Escape') closeComposers();
  });
  slot.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  ta.focus();
}

async function submitComposer(statusKey, slot) {
  const ta = slot.querySelector('textarea');
  const btn = slot.querySelector('.btn-add');
  const title = ta.value.trim();
  slot.querySelectorAll('.composer-err').forEach(el => el.remove());
  if (!title) { ta.focus(); return; }
  btn.disabled = true; btn.textContent = 'Menyimpan...';
  try {
    const res = await api('createTask', { project_id: projectId, title });
    if (res.code !== '00') throw new Error(res.message);
    if (statusKey !== 'no_status') {
      const mv = await api('updateTaskStatus', { task_id: res.data.id, new_status: statusKey });
      if (mv.code !== '00') throw new Error(mv.message);
    }
    reopenComposer = statusKey;   // tetap terbuka supaya bisa langsung input task berikutnya
    loadKanban();
  } catch (e) {
    btn.disabled = false; btn.textContent = 'Tambah task';
    slot.querySelector('.composer').insertAdjacentHTML('beforeend', `<div class="composer-err">Gagal menambah task: ${settingsEsc(e.message)}</div>`);
  }
}

// ===== MODAL: CARD DETAIL =====
async function openTaskModal(taskId) {
  activeTaskId = taskId;
  const res = await fetch('<?= site_url('project/getTaskDetail') ?>?id=' + taskId);
  const json = await res.json();
  if (json.code !== '00') return;
  currentTask = json.data;
  renderModal();
  document.getElementById('task-modal').classList.add('open');
}

async function refreshModal() {
  const res = await fetch('<?= site_url('project/getTaskDetail') ?>?id=' + activeTaskId);
  const json = await res.json();
  if (json.code === '00') {
    currentTask = json.data;
    renderModal();
  }
}

function renderModal() {
  const t = currentTask;

  const statusSelect = document.getElementById('modal-status');
  statusSelect.value = t.status;
  statusSelect.className = 'status-select st-' + t.status;
  document.getElementById('complete-toggle').classList.toggle('done', t.status === 'closed');
  document.getElementById('modal-title').value = t.title;
  document.getElementById('modal-description').value = t.description || '';
  document.getElementById('modal-due-date').value = t.due_date || '';

  document.getElementById('modal-label-preview').innerHTML = (t.labels || []).map(l =>
    `<div class="label-chip" style="background:${l.color}; width:48px;" title="${l.name}"></div>`
  ).join('');

  renderLabelPicker();
  renderMemberPicker();
  renderChecklists();
  renderAttachments();
}

function closeModal() {
  document.getElementById('task-modal').classList.remove('open');
  activeTaskId = null;
  currentTask = null;
  loadKanban();
}

// ----- status / complete -----
async function changeStatus(newStatus) {
  await api('updateTaskStatus', { task_id: activeTaskId, new_status: newStatus });
  await refreshModal();
}

async function toggleComplete() {
  const res = await api('toggleTaskComplete', { task_id: activeTaskId });
  if (res.code === '00') {
    currentTask = res.data;
    renderModal();
  }
}

// ----- title / description / due date -----
async function saveTitle() {
  const title = document.getElementById('modal-title').value.trim();
  if (!title) return;
  await api('updateTaskDetail', { task_id: activeTaskId, title });
}

async function saveDescription() {
  const description = document.getElementById('modal-description').value;
  await api('updateTaskDetail', { task_id: activeTaskId, description });
}

async function saveDueDate(value) {
  await api('updateTaskDetail', { task_id: activeTaskId, due_date: value });
}

// ----- labels -----
function renderLabelPicker() {
  const picker = document.getElementById('label-picker');
  const selectedIds = (currentTask.labels || []).map(l => l.id);
  picker.innerHTML = allLabels.map(l => `
    <div class="label-pill ${selectedIds.includes(l.id) ? 'selected' : ''}" style="background:${l.color}" onclick="toggleLabel(${l.id})">${l.name}</div>
  `).join('');
}

async function toggleLabel(labelId) {
  const selectedIds = (currentTask.labels || []).map(l => l.id);
  if (selectedIds.includes(labelId)) {
    await api('detachLabel', { task_id: activeTaskId, label_id: labelId });
  } else {
    await api('attachLabel', { task_id: activeTaskId, label_id: labelId });
  }
  await refreshModal();
}

async function createNewLabel() {
  const name = document.getElementById('new-label-name').value.trim();
  const color = document.getElementById('new-label-color').value;
  if (!name) return;

  const res = await api('createLabel', { project_id: projectId, name, color });
  if (res.code === '00') {
    allLabels.push(res.data);
    document.getElementById('new-label-name').value = '';
    await api('attachLabel', { task_id: activeTaskId, label_id: res.data.id });
    await refreshModal();
  }
}

// ----- members -----
function renderMemberPicker() {
  const picker = document.getElementById('member-picker');
  const selectedIds = (currentTask.members || []).map(m => m.id);
  picker.innerHTML = allUsers.map(u => `
    <div class="member-pill ${selectedIds.includes(u.id) ? 'selected' : ''}" onclick="toggleMember(${u.id})">
      ${avatarHtml(u, 20)} ${u.name}
    </div>
  `).join('');
}

async function toggleMember(userId) {
  const selectedIds = (currentTask.members || []).map(m => m.id);
  if (selectedIds.includes(userId)) {
    await api('removeTaskMember', { task_id: activeTaskId, user_id: userId });
  } else {
    await api('addTaskMember', { task_id: activeTaskId, user_id: userId });
  }
  await refreshModal();
}

// ----- checklist -----
function renderChecklists() {
  const wrap = document.getElementById('checklist-blocks');
  const checklists = currentTask.checklists || [];

  if (checklists.length === 0) {
    wrap.innerHTML = '<div class="empty" style="padding:4px 0;">Belum ada checklist</div>';
    return;
  }

  wrap.innerHTML = checklists.map(c => {
    const items = c.items || [];
    const done = items.filter(i => Number(i.is_done) === 1).length;
    const pct = items.length ? Math.round((done / items.length) * 100) : 0;
    return `
      <div class="checklist-block">
        <div class="checklist-title-row">
          <span>${c.title}</span>
          <button class="btn-delete" onclick="deleteChecklist(${c.id})">Hapus</button>
        </div>
        <div class="checklist-progress-row">
          <span style="font-size:12px; color:var(--muted); min-width:32px; font-weight:600;">${pct}%</span>
          <div class="progress-bar"><div class="progress-fill ${pct === 100 ? 'full' : ''}" style="width:${pct}%"></div></div>
        </div>
        <div>
          ${items.map(item => `
            <div class="checklist-item-row ${Number(item.is_done) === 1 ? 'done' : ''}">
              <input type="checkbox" ${Number(item.is_done) === 1 ? 'checked' : ''} onchange="toggleChecklistItem(${item.id})">
              <span>${item.text}</span>
              <button class="del-btn" onclick="deleteChecklistItem(${item.id})">Hapus</button>
            </div>
          `).join('')}
        </div>
        <div class="checklist-add-row">
          <input type="text" placeholder="Tambah item..." onkeydown="if(event.key==='Enter'){addChecklistItem(${c.id}, this); event.preventDefault();}">
          <button class="btn-sm" onclick="addChecklistItem(${c.id}, this.previousElementSibling)">Tambah</button>
        </div>
      </div>`;
  }).join('');
}

async function addChecklist() {
  const title = prompt('Judul checklist:', 'Checklist');
  if (!title) return;
  await api('addChecklist', { task_id: activeTaskId, title });
  await refreshModal();
}

async function deleteChecklist(checklistId) {
  await api('deleteChecklist', { checklist_id: checklistId });
  await refreshModal();
}

async function addChecklistItem(checklistId, inputEl) {
  const text = inputEl.value.trim();
  if (!text) return;
  await api('addChecklistItem', { checklist_id: checklistId, text });
  inputEl.value = '';
  await refreshModal();
}

async function toggleChecklistItem(itemId) {
  await api('toggleChecklistItem', { item_id: itemId });
  await refreshModal();
}

async function deleteChecklistItem(itemId) {
  await api('deleteChecklistItem', { item_id: itemId });
  await refreshModal();
}

// ----- attachments -----
function attachmentIcon(type) {
  return type === 'link' ? '&#128279;' : '&#128196;';
}

function renderAttachments() {
  const list = document.getElementById('attachment-list');
  const attachments = currentTask.attachments || [];
  if (attachments.length === 0) {
    list.innerHTML = '<div class="empty" style="padding:6px 0;">Belum ada attachment</div>';
    return;
  }
  list.innerHTML = attachments.map(a => `
    <div class="attachment-row">
      <div class="icon">${attachmentIcon(a.type)}</div>
      <a href="${a.url}" target="_blank" rel="noopener">${a.name}</a>
      <button class="btn-delete" style="margin-left:auto;" onclick="deleteAttachment(${a.id})">Hapus</button>
    </div>
  `).join('');
}

async function promptAddLink() {
  const url = prompt('Masukkan URL link (contoh: https://drive.google.com/...)');
  if (!url) return;
  const name = prompt('Nama tampilan untuk link ini:', url);
  await api('addAttachmentLink', { task_id: activeTaskId, url, name: name || url });
  await refreshModal();
}

async function handleFileSelected(evt) {
  const file = evt.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);
  formData.append('task_id', activeTaskId);

  const res = await fetch('<?= site_url('project/uploadAttachmentFile') ?>', { method: 'POST', body: formData });
  const json = await res.json();
  evt.target.value = '';
  if (json.code !== '00') {
    alert('Upload gagal: ' + json.message);
    return;
  }
  await refreshModal();
}

async function deleteAttachment(attId) {
  await api('deleteAttachment', { id: attId });
  await refreshModal();
}

// ===== GANTT =====
async function loadGantt() {
  if (!projectId) return;
  const res = await fetch('<?= site_url('project/getGantt') ?>?project_id=' + projectId);
  const json = await res.json();
  if (json.code !== '00' || json.data.length === 0) return;

  const tasks = json.data.map(t => ({
    id: t.id,
    name: `${t.name} (${t.assignee})`,
    start: t.start,
    end: t.end,
    progress: t.progress,
  }));

  ganttChart = new Gantt("#gantt", tasks, { view_mode: 'Week', language: 'en' });
}

// ===== SCOREBOARD =====
async function loadScoreboard() {
  if (!projectId) return;
  const res = await fetch('<?= site_url('project/getScoreboard') ?>?project_id=' + projectId);
  const json = await res.json();
  if (json.code !== '00') return;

  const data = json.data;
  document.getElementById('score-table-body').innerHTML = data.map(row => `
    <tr>
      <td>#${row.rank}</td>
      <td>${row.user}</td>
      <td>${row.total_points}</td>
      <td>${row.tasks_done}</td>
      <td>${row.tasks_ontime}</td>
    </tr>`).join('');

  new Chart(document.getElementById('scoreChart'), {
    type: 'bar',
    data: {
      labels: data.map(d => d.user),
      datasets: [{ label: 'Total Poin', data: data.map(d => d.total_points), backgroundColor: '#0052cc', borderRadius: 4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
}

// Load panel pertama (Kanban) saat halaman dibuka
loadKanban();

// ===== PENGATURAN PROJECT (dulu halaman detail terpisah) =====
const settingsIsAtasan = <?= json_encode((bool) $is_atasan) ?>;
const settingsEsc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

async function loadSettings() {
  const wrap = document.getElementById('settings-wrap');
  const res = await fetch('<?= site_url('project/getProjectDetail') ?>?id=' + projectId);
  const json = await res.json();
  if (json.code !== '00') { wrap.innerHTML = '<div class="s-card">Project tidak ditemukan atau Anda tidak punya akses.</div>'; return; }
  renderSettings(json.data);
}

function settingsDateNote(rencana, aktual, label) {
  if (!aktual) return `<div class="s-note pending">Belum ${label}</div>`;
  if (!rencana) return `<div class="s-note pending">Tanggal rencana belum diisi</div>`;
  return new Date(aktual) > new Date(rencana)
    ? `<div class="s-note late">Terlambat dari rencana</div>`
    : `<div class="s-note ontime">Sesuai atau lebih cepat dari rencana</div>`;
}

function renderSettings(p) {
  const ro = settingsIsAtasan ? '' : 'disabled';
  const sel = v => p.status === v ? 'selected' : '';
  document.getElementById('settings-wrap').innerHTML = `
    <div class="s-card">
      <div class="s-field"><label>Nama project</label><input type="text" id="f-name" value="${settingsEsc(p.name)}" ${ro}></div>
      <div class="s-field"><label>Deskripsi</label><textarea id="f-desc" ${ro}>${settingsEsc(p.description)}</textarea></div>
      <div class="s-row">
        <div class="s-field"><label>Status</label>
          <select id="f-status" ${ro}>
            <option value="active" ${sel('active')}>Berjalan</option>
            <option value="on_hold" ${sel('on_hold')}>Ditunda</option>
            <option value="done" ${sel('done')}>Selesai</option>
          </select></div>
        <div class="s-field"><label>PIC</label><input type="text" value="${settingsEsc(p.owner)}" disabled></div>
      </div>
      <div class="s-field" style="margin-bottom:0"><label>Progress task (${settingsEsc(p.progress)}%)</label>
        <div class="progress-bar"><div class="progress-fill" style="width:${Number(p.progress) || 0}%"></div></div></div>
    </div>
    <div class="s-card">
      <div class="s-dates">
        <div class="s-dates-title">Tanggal mulai</div>
        <div class="s-row">
          <div><label>Rencana</label><input type="date" id="f-rencana-mulai" value="${settingsEsc(p.tanggal_rencana_mulai)}" ${ro}></div>
          <div><label>Aktual</label><input type="date" id="f-aktual-mulai" value="${settingsEsc(p.tanggal_aktual_mulai)}" ${ro}>${settingsDateNote(p.tanggal_rencana_mulai, p.tanggal_aktual_mulai, 'mulai')}</div>
        </div>
      </div>
      <div class="s-dates">
        <div class="s-dates-title">Tanggal selesai</div>
        <div class="s-row">
          <div><label>Rencana</label><input type="date" id="f-rencana-selesai" value="${settingsEsc(p.tanggal_rencana_selesai)}" ${ro}></div>
          <div><label>Aktual</label><input type="date" id="f-aktual-selesai" value="${settingsEsc(p.tanggal_aktual_selesai)}" ${ro}>${settingsDateNote(p.tanggal_rencana_selesai, p.tanggal_aktual_selesai, 'selesai')}</div>
        </div>
      </div>
      ${settingsIsAtasan ? '' : '<div class="s-note pending">Hanya atasan yang bisa mengubah pengaturan project ini.</div>'}
    </div>
    ${settingsIsAtasan ? `<div class="s-actions"><button class="s-save" id="s-save" onclick="saveSettings()">Simpan perubahan</button><span class="s-msg" id="s-msg"></span></div>` : ''}`;
}

async function saveSettings() {
  const btn = document.getElementById('s-save'), msg = document.getElementById('s-msg');
  const v = id => document.getElementById(id).value;
  btn.disabled = true; msg.style.color = ''; msg.textContent = 'Menyimpan...';
  try {
    const res = await fetch('<?= site_url('project/updateProjectDetail') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        id: projectId, name: v('f-name'), description: v('f-desc'), status: v('f-status'),
        tanggal_rencana_mulai: v('f-rencana-mulai'), tanggal_aktual_mulai: v('f-aktual-mulai'),
        tanggal_rencana_selesai: v('f-rencana-selesai'), tanggal_aktual_selesai: v('f-aktual-selesai')
      }).toString()
    });
    const json = await res.json();
    if (json.code !== '00') throw new Error(json.message);
    document.getElementById('sidebar-title').innerText = json.data.name;
    document.getElementById('crumb-project').innerText = json.data.name;
    renderSettings(json.data);
    const m = document.getElementById('s-msg'); m.style.color = 'var(--success)'; m.textContent = 'Perubahan tersimpan';
  } catch (e) {
    btn.disabled = false; msg.style.color = 'var(--danger)'; msg.textContent = 'Gagal menyimpan: ' + e.message;
  }
}

// buka panel sesuai #hash (mis. dashboard/12#gantt), agar refresh dan tombol Back tidak kembali ke Kanban
// ===== TAMBAH TASK (FORM LENGKAP) =====
let ntUsers = new Set(), ntLabels = new Set();
function ntToggle(set, id, el) {
  set.has(id) ? set.delete(id) : set.add(id);
  el.classList.toggle('on', set.has(id));
  el.setAttribute('aria-pressed', set.has(id));
}
function openNewTaskModal(statusKey, prefillTitle) {
  switchPanel('kanban'); closeComposers();
  ntUsers = new Set(); ntLabels = new Set();
  const $ = id => document.getElementById(id);
  $('nt-title').value = prefillTitle || ''; $('nt-desc').value = ''; $('nt-due').value = '';
  $('nt-status').value = statusKey || 'no_status'; $('nt-err').textContent = '';
  const sb = $('nt-submit'); sb.disabled = false; sb.textContent = 'Tambah task'; sb.onclick = submitNewTask;
  $('nt-users').innerHTML = allUsers.length ? allUsers.map(u =>
    `<button type="button" class="pick" aria-pressed="false" onclick="ntToggle(ntUsers, ${Number(u.id)}, this)">${avatarHtml(u, 20)} ${settingsEsc(u.name)}</button>`).join('')
    : '<span class="nt-none">Belum ada anggota di project ini</span>';
  $('nt-labels').innerHTML = allLabels.length ? allLabels.map(l =>
    `<button type="button" class="pick pick-label" aria-pressed="false" style="--c:${settingsEsc(l.color)}" onclick="ntToggle(ntLabels, ${Number(l.id)}, this)">${settingsEsc(l.name)}</button>`).join('')
    : '<span class="nt-none">Belum ada label</span>';
  $('new-task-modal').classList.add('open');
  $('nt-title').focus();
}
function closeNewTaskModal() { document.getElementById('new-task-modal').classList.remove('open'); }

async function submitNewTask() {
  const $ = id => document.getElementById(id);
  const title = $('nt-title').value.trim(), err = $('nt-err'), btn = $('nt-submit');
  err.textContent = '';
  if (!title) { err.textContent = 'Judul task wajib diisi.'; $('nt-title').focus(); return; }
  btn.disabled = true; btn.textContent = 'Menyimpan...';
  let taskId = null;
  try {
    const res = await api('createTask', { project_id: projectId, title });
    if (res.code !== '00') throw new Error(res.message);
    taskId = res.data.id;
    const status = $('nt-status').value;
    const jobs = [api('updateTaskDetail', { task_id: taskId, title, description: $('nt-desc').value, due_date: $('nt-due').value })];
    if (status !== 'no_status') jobs.push(api('updateTaskStatus', { task_id: taskId, new_status: status }));
    ntUsers.forEach(id => jobs.push(api('addTaskMember', { task_id: taskId, user_id: id })));
    ntLabels.forEach(id => jobs.push(api('attachLabel', { task_id: taskId, label_id: id })));
    const failed = (await Promise.all(jobs)).find(r => r.code !== '00');
    await loadKanban();
    if (failed) throw new Error('Task sudah dibuat, tetapi sebagian isian gagal disimpan (' + failed.message + '). Lengkapi lewat card-nya.');
    closeNewTaskModal();
  } catch (e) {
    err.textContent = e.message;
    btn.disabled = false;
    if (taskId) { btn.textContent = 'Tutup'; btn.onclick = closeNewTaskModal; } else { btn.textContent = 'Tambah task'; }
  }
}

function openDetailPanel() {
  document.getElementById('detail-drawer').classList.add('open');
  document.getElementById('detail-drawer').setAttribute('aria-hidden', 'false');
  document.getElementById('drawer-overlay').classList.add('open');
  history.replaceState(null, '', '#detail');
  loadSettings();
}
function closeDetailPanel() {
  document.getElementById('detail-drawer').classList.remove('open');
  document.getElementById('detail-drawer').setAttribute('aria-hidden', 'true');
  document.getElementById('drawer-overlay').classList.remove('open');
  history.replaceState(null, '', '#' + currentPanel);
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeDetailPanel(); closeNewTaskModal(); } });

(function () {
  const h = location.hash.slice(1);
  if (h === 'detail' || h === 'pengaturan') openDetailPanel();
  else if (h && h !== 'kanban' && panelTitles[h]) switchPanel(h);
})();
loaded.kanban = true;
</script>

</body>
</html>
