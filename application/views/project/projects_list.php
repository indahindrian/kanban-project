<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard - Project Management</title>
<style>
  :root { --bg:#f4f5f7; --card-bg:#fff; --border:#dfe1e6; --text:#172b4d; --muted:#6b778c; --accent:#0052cc; --sidebar-w:260px; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:-apple-system,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:var(--text); }
  .app { display:flex; min-height:100vh; }

  /* ===== SIDEBAR ===== */
  .sidebar { width:var(--sidebar-w); min-width:var(--sidebar-w); background:#111c3f; color:#fff; display:flex; flex-direction:column; }
  .sidebar-profile { position:relative; padding:28px 20px 20px; overflow:hidden; border-bottom:1px solid rgba(255,255,255,.08); }
  .sidebar-profile::before {
    content:''; position:absolute; inset:0;
    background:
      linear-gradient(135deg, transparent 45%, #d94f70 45%, #d94f70 55%, transparent 55%),
      linear-gradient(-135deg, transparent 55%, #e8944a 55%, #e8944a 68%, transparent 68%),
      linear-gradient(45deg, #2c3a67 0%, #1a2550 100%);
    opacity:.9; z-index:0;
  }
  .sidebar-profile-inner { position:relative; z-index:1; text-align:center; }
  .sidebar-avatar { width:56px; height:56px; border-radius:50%; background:#f4b73f; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; font-size:22px; color:#7a4b00; font-weight:700; }
  .sidebar-username { font-weight:700; font-size:14px; }
  .sidebar-fullname { font-size:12px; color:#d7dcee; margin-top:2px; }
  .sidebar-account { padding:12px 20px; font-size:12px; color:#c3cbe0; border-bottom:1px solid rgba(255,255,255,.08); }
  .sidebar-account a { color:inherit; text-decoration:none; }

  .sidebar-nav { flex:1; padding:10px 0; overflow-y:auto; }
  .nav-section-label { padding:16px 20px 6px; font-size:11px; text-transform:uppercase; color:#7f8bb3; letter-spacing:.04em; }
  .nav-item { display:block; padding:11px 20px; font-size:13.5px; color:#c3cbe0; text-decoration:none; cursor:pointer; }
  .nav-item:hover { background:rgba(255,255,255,.06); color:#fff; }
  .nav-item.group-active { background:rgba(255,255,255,.08); color:#fff; font-weight:600; }
  .nav-sub { background:rgba(0,0,0,.15); }
  .nav-sub .nav-item { padding-left:38px; font-size:13px; }
  .nav-sub .nav-item.active { background:#2b3a6b; color:#fff; font-weight:600; border-left:3px solid #4c9aff; padding-left:35px; }
  .nav-item.disabled { color:#5c688f; cursor:not-allowed; }
  .nav-item.disabled:hover { background:none; color:#5c688f; }
  .badge-soon { font-size:9px; background:rgba(255,255,255,.12); padding:1px 6px; border-radius:8px; margin-left:6px; vertical-align:middle; }

  /* ===== MAIN ===== */
  .main { flex:1; min-width:0; }
  .content-topbar { background:#fff; border-bottom:1px solid var(--border); padding:16px 28px; display:flex; justify-content:space-between; align-items:center; }
  .content-topbar h1 { font-size:17px; margin:0; }
  .topbar-actions { display:flex; gap:10px; }
  .btn { border:none; border-radius:6px; padding:9px 16px; font-size:13.5px; cursor:pointer; font-weight:500; }
  .btn-outline { background:#fff; border:1px solid var(--border); color:var(--text); }
  .btn-outline:hover { background:#f4f5f7; }
  .btn-primary { background:var(--accent); color:#fff; }
  .btn-primary:hover { background:#0047b3; }

  .wrap { padding:24px 28px; }

  /* ===== SUMMARY CARDS ===== */
  .summary-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:24px; }
  .summary-card { background:var(--card-bg); border:1px solid var(--border); border-radius:8px; padding:16px; }
  .summary-card .num { font-size:24px; font-weight:700; margin-bottom:4px; }
  .summary-card .label { font-size:12px; color:var(--muted); }
  .summary-card.overdue .num { color:#bf2600; }
  .summary-card.done .num { color:#006644; }

  /* ===== LAYOUT: grid project (kiri) + tugas saya (kanan) ===== */
  .content-layout { display:flex; gap:20px; align-items:flex-start; }
  .content-main { flex:1; min-width:0; }
  .mytasks-panel { width:280px; min-width:280px; background:var(--card-bg); border:1px solid var(--border); border-radius:8px; padding:16px; }
  .mytasks-panel h3 { font-size:13px; margin:0 0 12px; color:var(--muted); text-transform:uppercase; }
  .mytask-item { padding:9px 0 9px 10px; border-bottom:1px solid #f0f1f3; font-size:13px; cursor:pointer; border-left:3px solid transparent; }
  .mytask-item.overdue { border-left-color:#de350b; background:#fff8f6; }
  .mytask-item:last-child { border-bottom:none; }
  .mytask-item:hover .mytask-title { color:var(--accent); }
  .mytask-title { font-weight:500; margin-bottom:3px; }
  .mytask-meta { font-size:11px; color:var(--muted); }
  .mytask-meta.overdue { color:#bf2600; font-weight:600; }

  /* ===== SEARCH & SORT ===== */
  .toolbar-row { display:flex; gap:10px; margin-bottom:16px; }
  .toolbar-row input, .toolbar-row select { padding:8px 12px; border:1px solid var(--border); border-radius:6px; font-size:13px; font-family:inherit; background:#fff; }
  .toolbar-row input[type=text] { flex:1; }

  .wrap h2 { font-size:14px; margin:0 0 16px; color:var(--muted); font-weight:600; }
  .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
  .card { background:var(--card-bg); border:1px solid var(--border); border-radius:8px; padding:16px; cursor:pointer; transition:box-shadow .15s; position:relative; }
  .card:hover { box-shadow:0 2px 8px rgba(9,30,66,.15); }
  .card-title-row { display:flex; justify-content:space-between; align-items:flex-start; gap:8px; }
  .card-title { font-size:15px; font-weight:600; margin-bottom:6px; }
  .card-desc { font-size:13px; color:var(--muted); margin-bottom:12px; min-height:36px; }
  .status-pill { display:inline-block; font-size:11px; padding:2px 10px; border-radius:12px; font-weight:600; margin-bottom:10px; }
  .status-active { background:#e3fcef; color:#006644; }
  .status-on_hold { background:#fffae6; color:#974f0c; }
  .status-done { background:#deebff; color:#0052cc; }
  .overdue-badge { display:inline-block; font-size:11px; padding:2px 8px; border-radius:10px; font-weight:700; background:#de350b; color:#fff; margin-bottom:10px; margin-left:6px; cursor:help; }
  .progress-bar { height:6px; background:#ebecf0; border-radius:4px; overflow:hidden; margin-bottom:8px; }
  .progress-fill { height:100%; background:var(--accent); }
  .card-footer { display:flex; justify-content:space-between; gap:8px; font-size:11.5px; color:var(--muted); white-space:nowrap; }
  .empty { color:var(--muted); font-size:13px; padding:20px; }

  /* ===== MODAL: PROJECT BARU ===== */
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(9,30,66,.55); z-index:100; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:10px; width:100%; max-width:440px; padding:24px; }
  .modal-box h3 { margin:0 0 18px; font-size:16px; }
  .field { margin-bottom:14px; }
  .field label { display:block; font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; margin-bottom:5px; }
  .field input, .field textarea { width:100%; padding:8px 10px; border:1px solid var(--border); border-radius:5px; font-size:13.5px; font-family:inherit; }
  .field textarea { min-height:60px; resize:vertical; }
  .row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  .modal-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:18px; }
  .form-error { color:#bf2600; font-size:12px; margin-top:10px; display:none; }
</style>
</head>
<body>

<div class="app">

  <div class="sidebar">
    <div class="sidebar-profile">
      <div class="sidebar-profile-inner">
        <div class="sidebar-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
        <div class="sidebar-username"><?= html_escape($user_name) ?></div>
        <div class="sidebar-fullname"><?= html_escape($role_name) ?></div>
      </div>
    </div>
    <div class="sidebar-account" style="display:flex;gap:14px">
      <a href="<?= site_url('akun') ?>">Akun saya</a>
      <a href="<?= site_url('auth/logout') ?>">Logout</a>
    </div>

    <div class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a class="nav-item disabled" title="Belum tersedia">Dashboard utama</a>

      <a class="nav-item group-active">Project monitoring</a>
      <div class="nav-sub">
        <a class="nav-item active" href="<?= site_url('project') ?>">Dashboard</a>
        <a class="nav-item" href="<?= site_url('project/planningPage') ?>">Planning</a>
      </div>

      <?php if ($can_manage_users): ?>
      <a class="nav-item" href="<?= site_url('user') ?>">Manajemen pengguna</a>
      <?php endif; ?>
      <a class="nav-item disabled" title="Belum tersedia">Master</a>
    </div>
  </div>

  <div class="main">
    <div class="content-topbar">
      <h1>Projects</h1>
      <div class="topbar-actions">
        <button class="btn btn-outline" onclick="location.href='<?= site_url('project/ganttGlobalPage') ?>'">Gantt chart global</button>
        <?php if ($can_create_project): ?>
        <button class="btn btn-primary" onclick="openCreateModal()">+ Project baru</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="wrap">

      <!-- ===== SUMMARY CARDS ===== -->
      <div class="summary-row">
        <div class="summary-card">
          <div class="num" id="sum-total">-</div>
          <div class="label">Total Project</div>
        </div>
        <div class="summary-card overdue">
          <div class="num" id="sum-overdue">-</div>
          <div class="label">Task Overdue</div>
        </div>
        <div class="summary-card done">
          <div class="num" id="sum-done">-</div>
          <div class="label">Task Selesai Bulan Ini</div>
        </div>
      </div>

      <div class="content-layout">
        <div class="content-main">

          <!-- ===== SEARCH & SORT ===== -->
          <div class="toolbar-row">
            <input type="text" id="search-input" placeholder="Cari nama project..." oninput="renderProjects()">
            <select id="filter-status" onchange="renderProjects()">
              <option value="">Semua status</option>
              <option value="active">Berjalan</option>
              <option value="on_hold">Ditunda</option>
              <option value="done">Selesai</option>
            </select>
            <select id="sort-by" onchange="renderProjects()">
              <option value="name">Urutkan: Nama</option>
              <option value="progress_desc">Urutkan: Progress tertinggi</option>
              <option value="progress_asc">Urutkan: Progress terendah</option>
              <option value="overdue_desc">Urutkan: Overdue terbanyak</option>
            </select>
          </div>

          <h2><?= $is_atasan ? 'Semua project' : 'Project saya' ?></h2>
          <div class="grid" id="project-grid">
            <div class="empty">Memuat data project...</div>
          </div>
        </div>

        <!-- ===== WIDGET: TUGAS SAYA ===== -->
        <div class="mytasks-panel">
          <h3>Tugas saya</h3>
          <div id="mytasks-list"><div class="empty">Memuat...</div></div>
        </div>
      </div>

    </div>
  </div>

</div>

<!-- ===== MODAL: PROJECT BARU ===== -->
<div class="modal-overlay" id="create-modal">
  <div class="modal-box">
    <h3>Buat project baru</h3>
    <div class="field">
      <label>Nama project</label>
      <input type="text" id="np-name" placeholder="Contoh: Redesign Landing Page">
    </div>
    <div class="field">
      <label>Deskripsi</label>
      <textarea id="np-desc" placeholder="Deskripsi singkat project..."></textarea>
    </div>
    <div class="row-2">
      <div class="field">
        <label>Rencana mulai</label>
        <input type="date" id="np-start">
      </div>
      <div class="field">
        <label>Rencana selesai</label>
        <input type="date" id="np-end">
      </div>
    </div>
    <div class="form-error" id="np-error"></div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeCreateModal()">Batal</button>
      <button class="btn btn-primary" onclick="submitCreateProject()">Buat project</button>
    </div>
  </div>
</div>

<script>
const statusLabel = { active: 'Berjalan', on_hold: 'Ditunda', done: 'Selesai' };
let allProjects = [];
let overdueByProject = {};

async function loadProjects() {
  const res = await fetch('<?= site_url('project/getProjects') ?>');
  const json = await res.json();
  allProjects = json.code === '00' ? json.data : [];
  renderProjects();
}

async function loadSummary() {
  const res = await fetch('<?= site_url('project/getDashboardSummary') ?>');
  const json = await res.json();
  if (json.code !== '00') return;

  document.getElementById('sum-total').innerText = json.data.total_projects;
  document.getElementById('sum-overdue').innerText = json.data.tasks_overdue_total;
  document.getElementById('sum-done').innerText = json.data.tasks_done_this_month;
  overdueByProject = json.data.overdue_by_project || {};

  const myTasksEl = document.getElementById('mytasks-list');
  const tasks = json.data.my_tasks || [];
  myTasksEl.innerHTML = tasks.length === 0
    ? '<div class="empty">Tidak ada tugas aktif.</div>'
    : tasks.map(t => `
        <div class="mytask-item ${t.overdue ? 'overdue' : ''}" onclick="location.href='<?= site_url('project/dashboard') ?>/${t.project_id}'">
          <div class="mytask-title">${t.title}</div>
          <div class="mytask-meta ${t.overdue ? 'overdue' : ''}">${t.project_name} &middot; ${t.due_date ? t.due_date : 'Tanpa deadline'}${t.overdue ? ' (Terlambat)' : ''}</div>
        </div>
      `).join('');

  renderProjects();
}

function renderProjects() {
  const grid = document.getElementById('project-grid');
  const search = document.getElementById('search-input').value.toLowerCase();
  const statusFilter = document.getElementById('filter-status').value;
  const sortBy = document.getElementById('sort-by').value;

  let list = allProjects.filter(p => {
    const matchSearch = p.name.toLowerCase().includes(search);
    const matchStatus = !statusFilter || p.status === statusFilter;
    return matchSearch && matchStatus;
  });

  list.sort((a, b) => {
    if (sortBy === 'progress_desc') return b.progress - a.progress;
    if (sortBy === 'progress_asc') return a.progress - b.progress;
    if (sortBy === 'overdue_desc') return (overdueByProject[b.id] || 0) - (overdueByProject[a.id] || 0);
    return a.name.localeCompare(b.name);
  });

  if (allProjects.length === 0) {
    grid.innerHTML = '<div class="empty">Belum ada project yang di-assign ke kamu.</div>';
    return;
  }
  if (list.length === 0) {
    grid.innerHTML = '<div class="empty">Tidak ada project yang cocok dengan pencarian/filter.</div>';
    return;
  }

  grid.innerHTML = list.map(p => {
    const overdueCount = overdueByProject[p.id] || 0;
    const overdueTooltip = p.status === 'done'
      ? `Ada ${overdueCount} task yang belum diselesaikan meski project sudah ditandai selesai`
      : `${overdueCount} task melewati deadline dan belum closed`;
    return `
    <div class="card" onclick="location.href='<?= site_url('project/dashboard') ?>/${p.id}'">
      <div class="card-title-row">
        <span class="status-pill status-${p.status}">${statusLabel[p.status] || p.status}</span>
        ${overdueCount > 0 ? `<span class="overdue-badge" title="${overdueTooltip}">${overdueCount} overdue</span>` : ''}
      </div>
      <div class="card-title">${p.name}</div>
      <div class="card-desc">${p.description || ''}</div>
      <div class="progress-bar"><div class="progress-fill" style="width:${p.progress}%"></div></div>
      <div class="card-footer">
        <span>PIC: ${p.owner}</span>
        <span>${p.tanggal_rencana_selesai ? 'Deadline ' + p.tanggal_rencana_selesai : 'Tanpa deadline'}</span>
        <span>${p.progress}%</span>
      </div>
    </div>
  `;
  }).join('');
}

loadProjects();
loadSummary();

function openCreateModal() {
  document.getElementById('np-name').value = '';
  document.getElementById('np-desc').value = '';
  document.getElementById('np-start').value = '';
  document.getElementById('np-end').value = '';
  document.getElementById('np-error').style.display = 'none';
  document.getElementById('create-modal').classList.add('open');
}
function closeCreateModal() {
  document.getElementById('create-modal').classList.remove('open');
}

async function submitCreateProject() {
  const name = document.getElementById('np-name').value.trim();
  const errorEl = document.getElementById('np-error');
  if (!name) {
    errorEl.textContent = 'Nama project wajib diisi.';
    errorEl.style.display = 'block';
    return;
  }

  const body = new URLSearchParams({
    name: name,
    description: document.getElementById('np-desc').value.trim(),
    tanggal_rencana_mulai: document.getElementById('np-start').value,
    tanggal_rencana_selesai: document.getElementById('np-end').value,
  });

  const res = await fetch('<?= site_url('project/createProject') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  });
  const json = await res.json();

  if (json.code !== '00') {
    errorEl.textContent = json.message || 'Gagal membuat project.';
    errorEl.style.display = 'block';
    return;
  }

  closeCreateModal();
  location.href = '<?= site_url('project/dashboard') ?>/' + json.data.id;
}
</script>

</body>
</html>
