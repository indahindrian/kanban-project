<?php $this->load->view('system/_header'); ?>

<div class="banner banner-blue">
  <b>Halaman Manajemen User (/user):</b> Digunakan oleh Super Admin untuk mendaftarkan akun tim kantor serta menentukan role dasar tiap akun. Akun tidak dihapus, hanya dinonaktifkan, agar riwayat project dan Scoreboard tetap utuh.
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-head"><h2>Tambah Pengguna Baru</h2><span class="tag">POST /user/store</span></div>
    <div class="field"><label for="n-name">Nama Lengkap</label><input id="n-name" type="text" maxlength="100" placeholder="Contoh: Pak Zacky"></div>
    <div class="field"><label for="n-email">Alamat Email</label><input id="n-email" type="email" maxlength="150" placeholder="zacky@perusahaan.co.id"></div>
    <div class="field"><label for="n-pass">Password Initial</label><input id="n-pass" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter"></div>
    <div class="field"><label for="n-role">Pilih Role Akses</label><select id="n-role"></select></div>
    <button class="btn btn-primary btn-block" id="n-save" onclick="createUser()">+ Simpan Akun Baru</button>
    <div class="msg" id="n-msg"></div>
  </div>

  <div class="card">
    <div class="card-head">
      <div><h2 id="list-title">Daftar Pengguna Tim</h2><div class="sub">Anggota tim terdaftar yang memiliki akses ke aplikasi</div></div>
      <span class="badge-ok" id="list-badge">...</span>
    </div>
    <table>
      <thead><tr><th>Pengguna</th><th>Role saat ini</th><th>Status</th><th class="num">Aksi</th></tr></thead>
      <tbody id="user-tbody"><tr><td colspan="4" class="empty">Memuat...</td></tr></tbody>
    </table>
    <div class="msg" id="list-msg"></div>
  </div>
</div>

<!-- Modal edit user -->
<div class="modal-backdrop" id="edit-modal">
  <div class="modal">
    <h3>Edit user</h3>
    <input type="hidden" id="e-id">
    <div class="field"><label for="e-name">Nama Lengkap</label><input id="e-name" type="text" maxlength="100"></div>
    <div class="field"><label for="e-email">Alamat Email</label><input id="e-email" type="email" maxlength="150"></div>
    <div class="field"><label for="e-role">Role Akses</label><select id="e-role"></select></div>
    <div class="msg" id="e-msg"></div>
    <div class="modal-actions">
      <button class="btn" onclick="closeModal('edit-modal')">Batal</button>
      <button class="btn btn-primary" id="e-save" onclick="saveEdit()">Simpan</button>
    </div>
  </div>
</div>

<!-- Modal reset password -->
<div class="modal-backdrop" id="reset-modal">
  <div class="modal">
    <h3>Reset password</h3>
    <p class="muted" style="font-size:13px;margin:0 0 14px">Password baru untuk <b id="r-name"></b>. Sampaikan ke yang bersangkutan.</p>
    <input type="hidden" id="r-id">
    <div class="field"><label for="r-pass">Password baru (min. 8 karakter)</label><input id="r-pass" type="password" autocomplete="new-password"></div>
    <div class="msg" id="r-msg"></div>
    <div class="modal-actions">
      <button class="btn" onclick="closeModal('reset-modal')">Batal</button>
      <button class="btn btn-primary" id="r-save" onclick="saveReset()">Reset</button>
    </div>
  </div>
</div>

<?php $this->load->view('system/_footer'); ?>
<script>
const MY_ID = <?= (int) $my_id ?>;
let USERS = [], ROLES = [];

function roleOptions(selected) {
  return ROLES.map(r => '<option value="' + Number(r.id) + '"' + (Number(r.id) === Number(selected) ? ' selected' : '') + '>' + esc(r.name) + '</option>').join('');
}

async function load() {
  const json = await api('<?= site_url('user/listUsers') ?>', null, 'GET');
  const tbody = document.getElementById('user-tbody');
  if (json.code !== '00') {
    tbody.innerHTML = '<tr><td colspan="4" class="empty" style="color:var(--danger)">' + esc(json.message) + '</td></tr>';
    return;
  }
  USERS = json.data.users;
  ROLES = json.data.roles;

  const sel = document.getElementById('n-role');
  const keep = sel.value;
  // Default pilihan di form tambah user: role non-sistem pertama (bukan Super Admin)
  sel.innerHTML = roleOptions(keep || (ROLES.find(r => !Number(r.is_system)) || ROLES[0] || {}).id);

  const active = USERS.filter(u => Number(u.is_active)).length;
  document.getElementById('list-title').innerText = 'Daftar Pengguna Tim (' + USERS.length + ' User)';
  document.getElementById('list-badge').innerText = active + ' aktif';

  tbody.innerHTML = USERS.map(u => {
    const isMe = Number(u.id) === MY_ID;
    const on = Number(u.is_active) === 1;
    return '<tr>' +
      '<td><div class="person"><div class="av-sm">' + esc(initials(u.name)) + '</div><div><b>' + esc(u.name) + (isMe ? ' <span class="muted" style="font-weight:400">(Anda)</span>' : '') + '</b><span>' + esc(u.email) + '</span></div></div></td>' +
      '<td><span class="chip chip-blue">' + esc(u.role_name || '-') + '</span></td>' +
      '<td><span class="' + (on ? 'dot-ok' : 'dot-off') + '">' + (on ? 'Aktif' : 'Nonaktif') + '</span></td>' +
      '<td><div class="row-actions">' +
        '<button class="btn btn-sm" onclick="openEdit(' + Number(u.id) + ')">Edit</button>' +
        '<button class="btn btn-sm" onclick="openReset(' + Number(u.id) + ')">Reset password</button>' +
        (isMe ? '' : '<button class="btn btn-sm ' + (on ? 'btn-danger' : '') + '" onclick="toggleActive(' + Number(u.id) + ')">' + (on ? 'Nonaktifkan' : 'Aktifkan') + '</button>') +
      '</div></td></tr>';
  }).join('') || '<tr><td colspan="4" class="empty">Belum ada user.</td></tr>';
}

async function createUser() {
  const btn = document.getElementById('n-save');
  btn.disabled = true;
  const json = await api('<?= site_url('user/store') ?>', {
    name: document.getElementById('n-name').value,
    email: document.getElementById('n-email').value,
    password: document.getElementById('n-pass').value,
    role_id: document.getElementById('n-role').value
  });
  btn.disabled = false;
  flash('n-msg', json.code === '00', json.code === '00' ? 'Akun baru berhasil disimpan.' : json.message);
  if (json.code === '00') {
    ['n-name', 'n-email', 'n-pass'].forEach(i => document.getElementById(i).value = '');
    load();
  }
}

function openEdit(id) {
  const u = USERS.find(x => Number(x.id) === id);
  if (!u) return;
  document.getElementById('e-id').value = u.id;
  document.getElementById('e-name').value = u.name;
  document.getElementById('e-email').value = u.email;
  document.getElementById('e-role').innerHTML = roleOptions(u.role_id);
  document.getElementById('e-msg').className = 'msg';
  openModal('edit-modal');
}

async function saveEdit() {
  const btn = document.getElementById('e-save');
  btn.disabled = true;
  const json = await api('<?= site_url('user/update') ?>', {
    id: document.getElementById('e-id').value,
    name: document.getElementById('e-name').value,
    email: document.getElementById('e-email').value,
    role_id: document.getElementById('e-role').value
  });
  btn.disabled = false;
  if (json.code !== '00') return flash('e-msg', false, json.message);
  closeModal('edit-modal');
  flash('list-msg', true, 'User berhasil diperbarui.');
  load();
}

async function toggleActive(id) {
  const u = USERS.find(x => Number(x.id) === id);
  if (!u) return;
  const on = Number(u.is_active) === 1;
  if (on && !confirm('Nonaktifkan "' + u.name + '"? User tidak akan bisa login, tapi riwayatnya tetap tersimpan.')) return;
  const json = await api('<?= site_url('user/toggleActive') ?>', { id });
  flash('list-msg', json.code === '00', json.code === '00' ? (on ? 'User dinonaktifkan.' : 'User diaktifkan.') : json.message);
  if (json.code === '00') load();
}

function openReset(id) {
  const u = USERS.find(x => Number(x.id) === id);
  if (!u) return;
  document.getElementById('r-id').value = u.id;
  document.getElementById('r-name').innerText = u.name;
  document.getElementById('r-pass').value = '';
  document.getElementById('r-msg').className = 'msg';
  openModal('reset-modal');
}

async function saveReset() {
  const btn = document.getElementById('r-save');
  btn.disabled = true;
  const json = await api('<?= site_url('user/resetPassword') ?>', {
    id: document.getElementById('r-id').value,
    password: document.getElementById('r-pass').value
  });
  btn.disabled = false;
  if (json.code !== '00') return flash('r-msg', false, json.message);
  closeModal('reset-modal');
  flash('list-msg', true, 'Password user berhasil direset.');
}

load();
</script>
</body>
</html>
