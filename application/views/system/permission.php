<?php $this->load->view('system/_header'); ?>

<style>
  .toolbar { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
  .toolbar .lbl { font-size:12.5px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
  .toolbar select { padding:9px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; font-weight:600; background:#fff; min-width:240px; }
  .toolbar .sum { margin-left:auto; font-size:13px; color:var(--muted); }
  .toolbar .sum b { color:var(--accent); }
  .perm-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:22px; margin-top:22px; align-items:start; }
  @media (max-width:1100px) { .perm-grid { grid-template-columns:1fr; } }
  .perm-col h3 { margin:0; font-size:13px; letter-spacing:.06em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
  .perm-col h3 i { width:9px; height:9px; border-radius:50%; display:inline-block; }
  .perm-col .gtag { font-size:11.5px; color:var(--muted); font-weight:400; text-transform:none; letter-spacing:0; margin-left:auto; }
  .perm-item { display:flex; gap:12px; align-items:flex-start; padding:12px 10px; border-radius:10px; cursor:pointer; }
  .perm-item:hover { background:#f8fafc; }
  .perm-item.off { cursor:not-allowed; opacity:.55; }
  .perm-item.off:hover { background:none; }
  .perm-item input { width:18px; height:18px; margin-top:2px; accent-color:var(--accent); flex-shrink:0; }
  .perm-item b { display:block; font-size:14px; }
  .perm-item span { font-size:12px; color:var(--muted); }
</style>

<div class="banner banner-indigo">
  <b>Halaman Pengaturan Hak Akses (/permission):</b> Tempat mengatur izin fitur secara dinamis lewat checkbox. Pilih Role di dropdown atas, lalu centang fitur yang diperbolehkan. Perubahan berlaku langsung tanpa user perlu login ulang. Hak akses grup Pengaturan Sistem hanya untuk Super Admin.
</div>

<div class="card">
  <div class="toolbar">
    <span class="lbl">1. Pilih role yang ingin diatur:</span>
    <select id="role-select" onchange="loadRole()"></select>
    <span class="sum" id="summary"></span>
    <button class="btn btn-primary" id="btn-save" onclick="savePerms()">Simpan Hak Akses</button>
  </div>
  <div class="msg" id="msg"></div>
</div>

<div class="perm-grid" id="perm-grid"></div>

<?php $this->load->view('system/_footer'); ?>
<script>
const GROUPS = [
  { key: 'main',   title: 'Main & Proyek',      tag: 'Modular',          color: '#2563eb' },
  { key: 'doc',    title: 'Dokumentasi & TW',   tag: 'Technical Writer', color: '#16a34a' },
  { key: 'system', title: 'Pengaturan Sistem',  tag: 'Super Admin',      color: '#dc2626' }
];
let PERMS = [], LOCKED = false, CURRENT = null;

async function init() {
  const json = await api('<?= site_url('role/listRoles') ?>', null, 'GET');
  if (json.code !== '00') return flash('msg', false, json.message);
  const sel = document.getElementById('role-select');
  sel.innerHTML = json.data.map(r => '<option value="' + Number(r.id) + '">' + esc(r.name) + '</option>').join('');
  // default: role pertama yang bukan sistem (Super Admin terkunci)
  const first = json.data.find(r => !Number(r.is_system)) || json.data[0];
  if (first) sel.value = first.id;
  loadRole();
}

async function loadRole() {
  const roleId = document.getElementById('role-select').value;
  document.getElementById('msg').className = 'msg';
  const json = await api('<?= site_url('permission/getRolePermissions') ?>', { role_id: roleId }, 'GET');
  if (json.code !== '00') return flash('msg', false, json.message);
  PERMS = json.data.permissions;
  LOCKED = json.data.locked;
  CURRENT = json.data.role;
  render(json.data.granted);
}

function render(granted) {
  const grid = document.getElementById('perm-grid');
  grid.innerHTML = GROUPS.map(g => {
    const items = PERMS.filter(p => p.group_key === g.key).map(p => {
      const superOnly = Number(p.super_only) === 1;
      const disabled = LOCKED || superOnly;
      const checked = granted.indexOf(p.code) > -1;
      return '<label class="perm-item' + (disabled ? ' off' : '') + '">' +
        '<input type="checkbox" value="' + esc(p.code) + '"' + (checked ? ' checked' : '') + (disabled ? ' disabled' : '') + ' onchange="updateSummary()">' +
        '<div><b>' + esc(p.name) + '</b><span>' + esc(p.description) + '</span></div></label>';
    }).join('');
    return '<div class="card perm-col"><div class="card-head" style="margin-bottom:8px"><h3><i style="background:' + g.color + '"></i>' + esc(g.title) + '<span class="gtag">' + esc(g.tag) + '</span></h3></div>' + items + '</div>';
  }).join('');

  const btn = document.getElementById('btn-save');
  btn.disabled = LOCKED;
  if (LOCKED) flash('msg', true, 'Role Super Admin selalu memiliki akses penuh dan tidak bisa diubah.');
  updateSummary();
}

function updateSummary() {
  const total = PERMS.length;
  const on = LOCKED ? total : document.querySelectorAll('#perm-grid input:checked').length;
  document.getElementById('summary').innerHTML = 'Ringkasan: <b>' + on + ' dari ' + total + ' Fitur Tercentang</b>';
}

async function savePerms() {
  const codes = Array.from(document.querySelectorAll('#perm-grid input:checked:not(:disabled)')).map(i => i.value);
  const btn = document.getElementById('btn-save');
  btn.disabled = true;
  const json = await api('<?= site_url('permission/save') ?>', { role_id: CURRENT.id, codes: codes });
  btn.disabled = LOCKED;
  flash('msg', json.code === '00', json.code === '00' ? 'Hak akses untuk "' + CURRENT.name + '" berhasil disimpan.' : json.message);
}

init();
</script>
</body>
</html>
