<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Project - Project Management</title>
<style>
  :root { --bg:#f4f5f7; --card-bg:#fff; --border:#dfe1e6; --text:#172b4d; --muted:#6b778c; --accent:#0052cc; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:-apple-system,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:var(--text); }
  .topbar { background:#1a2b4c; color:#fff; padding:14px 24px; display:flex; justify-content:space-between; align-items:center; }
  .topbar a { color:#c3cbe0; text-decoration:none; font-size:13px; }
  .topbar a:hover { color:#fff; }
  .topbar-user { display:flex; align-items:center; gap:14px; font-size:13px; color:#c3cbe0; }
  .role-badge { background:rgba(255,255,255,.12); padding:2px 8px; border-radius:10px; font-size:11px; text-transform:uppercase; }
  .wrap { padding:24px; max-width:720px; }
  .card { background:var(--card-bg); border:1px solid var(--border); border-radius:8px; padding:24px; margin-bottom:16px; }
  .field { margin-bottom:16px; }
  label { display:block; font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; margin-bottom:6px; }
  input, textarea, select { width:100%; padding:8px 10px; border:1px solid var(--border); border-radius:4px; font-size:14px; font-family:inherit; color:var(--text); background:#fff; }
  textarea { resize:vertical; min-height:60px; }
  .row-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .date-compare { display:grid; grid-template-columns:1fr 1fr; gap:16px; padding:16px; background:#f8f9fb; border-radius:6px; margin-bottom:16px; }
  .date-compare-label { font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; margin-bottom:8px; grid-column:1/-1; }
  .date-status { font-size:12px; margin-top:4px; }
  .late { color:#bf2600; }
  .ontime { color:#006644; }
  .pending { color:var(--muted); }
  .actions { display:flex; gap:10px; margin-top:8px; }
  button { border:none; border-radius:4px; padding:9px 18px; font-size:14px; cursor:pointer; font-weight:500; }
  .btn-primary { background:var(--accent); color:#fff; }
  .btn-secondary { background:#ebecf0; color:var(--text); }
  .btn-kanban { background:#00875a; color:#fff; display:flex; align-items:center; gap:6px; }
  .progress-bar { height:8px; background:#ebecf0; border-radius:4px; overflow:hidden; margin-top:6px; }
  .progress-fill { height:100%; background:var(--accent); }
</style>
</head>
<body>

<div class="topbar">
  <a href="<?= site_url('project') ?>">&larr; Kembali ke daftar project</a>
  <div class="topbar-user">
    <span><?= html_escape($user_name) ?></span>
    <span class="role-badge"><?= $is_atasan ? 'Atasan' : 'Anggota' ?></span>
    <a href="<?= site_url('auth/logout') ?>">Logout</a>
  </div>
</div>

<div class="wrap" id="detail-wrap">
  <div class="card">Memuat data...</div>
</div>

<script>
const projectId = <?= json_encode($project_id) ?>;
const isAtasan = <?= json_encode($is_atasan) ?>;

async function loadDetail() {
  const res = await fetch('<?= site_url('project/getProjectDetail') ?>?id=' + projectId);
  const json = await res.json();
  if (json.code !== '00') {
    document.getElementById('detail-wrap').innerHTML = '<div class="card">Project tidak ditemukan atau tidak ada akses.</div>';
    return;
  }
  render(json.data);
}

function dateStatusHtml(rencana, aktual, label) {
  if (!aktual) return `<div class="date-status pending">Belum ${label}</div>`;
  if (!rencana) return `<div class="date-status pending">Tanggal rencana belum diisi</div>`;
  const late = new Date(aktual) > new Date(rencana);
  return late
    ? `<div class="date-status late">Terlambat dari rencana</div>`
    : `<div class="date-status ontime">Sesuai / lebih cepat dari rencana</div>`;
}

function render(p) {
  const readonly = isAtasan ? '' : 'disabled';
  document.getElementById('detail-wrap').innerHTML = `
    <div class="card">
      <div class="field">
        <label>Nama project</label>
        <input type="text" id="f-name" value="${p.name}" ${readonly}>
      </div>
      <div class="field">
        <label>Deskripsi</label>
        <textarea id="f-desc" ${readonly}>${p.description || ''}</textarea>
      </div>
      <div class="row-2">
        <div class="field">
          <label>Status</label>
          <select id="f-status" ${readonly}>
            <option value="active" ${p.status === 'active' ? 'selected' : ''}>Berjalan</option>
            <option value="on_hold" ${p.status === 'on_hold' ? 'selected' : ''}>Ditunda</option>
            <option value="done" ${p.status === 'done' ? 'selected' : ''}>Selesai</option>
          </select>
        </div>
        <div class="field">
          <label>PIC</label>
          <input type="text" value="${p.owner}" disabled>
        </div>
      </div>
      <div class="field">
        <label>Progress task (${p.progress}%)</label>
        <div class="progress-bar"><div class="progress-fill" style="width:${p.progress}%"></div></div>
      </div>
    </div>

    <div class="card">
      <div class="date-compare">
        <div class="date-compare-label">Tanggal mulai</div>
        <div>
          <label>Rencana</label>
          <input type="date" id="f-rencana-mulai" value="${p.tanggal_rencana_mulai || ''}" ${readonly}>
        </div>
        <div>
          <label>Aktual</label>
          <input type="date" id="f-aktual-mulai" value="${p.tanggal_aktual_mulai || ''}" ${readonly}>
          ${dateStatusHtml(p.tanggal_rencana_mulai, p.tanggal_aktual_mulai, 'mulai')}
        </div>
      </div>
      <div class="date-compare" style="margin-bottom:0;">
        <div class="date-compare-label">Tanggal selesai</div>
        <div>
          <label>Rencana</label>
          <input type="date" id="f-rencana-selesai" value="${p.tanggal_rencana_selesai || ''}" ${readonly}>
        </div>
        <div>
          <label>Aktual</label>
          <input type="date" id="f-aktual-selesai" value="${p.tanggal_aktual_selesai || ''}" ${readonly}>
          ${dateStatusHtml(p.tanggal_rencana_selesai, p.tanggal_aktual_selesai, 'selesai')}
        </div>
      </div>
      ${!isAtasan ? '<div style="font-size:12px; color:var(--muted); margin-top:8px;">Hanya atasan yang bisa mengubah rencana/tanggal project ini.</div>' : ''}
    </div>

    <div class="actions">
      ${isAtasan ? `<button class="btn-primary" onclick="saveDetail(${p.id})">Simpan perubahan</button>` : ''}
      <button class="btn-kanban" onclick="location.href='<?= site_url('project/dashboard') ?>/${p.id}'">Lihat Kanban &rarr;</button>
    </div>
  `;
}

async function saveDetail(id) {
  const payload = {
    id: id,
    name: document.getElementById('f-name').value,
    description: document.getElementById('f-desc').value,
    status: document.getElementById('f-status').value,
    tanggal_rencana_mulai: document.getElementById('f-rencana-mulai').value,
    tanggal_aktual_mulai: document.getElementById('f-aktual-mulai').value,
    tanggal_rencana_selesai: document.getElementById('f-rencana-selesai').value,
    tanggal_aktual_selesai: document.getElementById('f-aktual-selesai').value,
  };

  const res = await fetch('<?= site_url('project/updateProjectDetail') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(payload).toString()
  });
  const json = await res.json();
  if (json.code === '00') {
    render(json.data);
  } else {
    alert('Gagal menyimpan: ' + json.message);
  }
}

loadDetail();
</script>

</body>
</html>
