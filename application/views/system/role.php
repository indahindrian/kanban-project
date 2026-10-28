<?php $this->load->view('system/_header'); ?>

<div class="banner banner-amber">
  <b>Halaman Kelola Role (/role):</b> Digunakan untuk menambah atau mengubah nama jabatan/peran di kantor (misal: penambahan role baru seperti QA / Tester atau Project Manager di masa depan). Role baru belum punya hak akses apa pun; atur di halaman Hak Akses.
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-head"><h2>Tambah Role Baru</h2><span class="tag">POST /role/store</span></div>
    <div class="field"><label for="n-name">Nama Peran / Role</label><input id="n-name" type="text" maxlength="100" placeholder="Contoh: QA / Tester"></div>
    <div class="field"><label for="n-desc">Keterangan / Deskripsi</label><textarea id="n-desc" maxlength="255" placeholder="Deskripsi singkat tanggung jawab role ini..."></textarea></div>
    <button class="btn btn-dark btn-block" id="n-save" onclick="createRole()">+ Simpan Role</button>
    <div class="msg" id="n-msg"></div>
  </div>

  <div class="card">
    <div class="card-head">
      <div><h2 id="list-title">Daftar Peran Terdaftar</h2><div class="sub">Master data role pengguna sistem</div></div>
    </div>
    <table>
      <thead><tr><th>ID</th><th>Nama Role</th><th>Jumlah Pengguna</th><th class="num">Aksi</th></tr></thead>
      <tbody id="role-tbody"><tr><td colspan="4" class="empty">Memuat...</td></tr></tbody>
    </table>
    <div class="msg" id="list-msg"></div>
  </div>
</div>

<!-- Modal edit role -->
<div class="modal-backdrop" id="edit-modal">
  <div class="modal">
    <h3>Edit role</h3>
    <input type="hidden" id="e-id">
    <div class="field"><label for="e-name">Nama Peran / Role</label><input id="e-name" type="text" maxlength="100"></div>
    <div class="field"><label for="e-desc">Keterangan / Deskripsi</label><textarea id="e-desc" maxlength="255"></textarea></div>
    <div class="msg" id="e-msg"></div>
    <div class="modal-actions">
      <button class="btn" onclick="closeModal('edit-modal')">Batal</button>
      <button class="btn btn-primary" id="e-save" onclick="saveEdit()">Simpan</button>
    </div>
  </div>
</div>

<?php $this->load->view('system/_footer'); ?>
<script>
let ROLES = [];

async function load() {
  const json = await api('<?= site_url('role/listRoles') ?>', null, 'GET');
  const tbody = document.getElementById('role-tbody');
  if (json.code !== '00') {
    tbody.innerHTML = '<tr><td colspan="4" class="empty" style="color:var(--danger)">' + esc(json.message) + '</td></tr>';
    return;
  }
  ROLES = json.data;
  document.getElementById('list-title').innerText = 'Daftar Peran Terdaftar (' + ROLES.length + ' Role)';
  tbody.innerHTML = ROLES.map((r, i) => {
    const sys = Number(r.is_system) === 1;
    return '<tr>' +
      '<td class="muted">' + (i + 1) + '</td>' +
      '<td><b>' + esc(r.name) + '</b>' + (r.description ? '<div class="muted" style="font-size:12px;margin-top:2px">' + esc(r.description) + '</div>' : '') + '</td>' +
      '<td><span class="chip">' + Number(r.user_count) + ' User</span></td>' +
      '<td><div class="row-actions">' + (sys
        ? '<span class="muted" style="font-size:12px" title="Role sistem">Terkunci</span>'
        : '<button class="btn btn-sm" onclick="openEdit(' + Number(r.id) + ')">Edit</button>' +
          '<button class="btn btn-sm btn-danger" onclick="removeRole(' + Number(r.id) + ')">Hapus</button>') +
      '</div></td></tr>';
  }).join('');
}

async function createRole() {
  const btn = document.getElementById('n-save');
  btn.disabled = true;
  const json = await api('<?= site_url('role/store') ?>', {
    name: document.getElementById('n-name').value,
    description: document.getElementById('n-desc').value
  });
  btn.disabled = false;
  flash('n-msg', json.code === '00', json.code === '00' ? 'Role berhasil disimpan. Atur hak aksesnya di halaman Hak Akses.' : json.message);
  if (json.code === '00') {
    document.getElementById('n-name').value = '';
    document.getElementById('n-desc').value = '';
    load();
  }
}

function openEdit(id) {
  const r = ROLES.find(x => Number(x.id) === id);
  if (!r) return;
  document.getElementById('e-id').value = r.id;
  document.getElementById('e-name').value = r.name;
  document.getElementById('e-desc').value = r.description || '';
  document.getElementById('e-msg').className = 'msg';
  openModal('edit-modal');
}

async function saveEdit() {
  const btn = document.getElementById('e-save');
  btn.disabled = true;
  const json = await api('<?= site_url('role/update') ?>', {
    id: document.getElementById('e-id').value,
    name: document.getElementById('e-name').value,
    description: document.getElementById('e-desc').value
  });
  btn.disabled = false;
  if (json.code !== '00') return flash('e-msg', false, json.message);
  closeModal('edit-modal');
  flash('list-msg', true, 'Role berhasil diperbarui.');
  load();
}

async function removeRole(id) {
  const r = ROLES.find(x => Number(x.id) === id);
  if (!r || !confirm('Hapus role "' + r.name + '"?')) return;
  const json = await api('<?= site_url('role/delete') ?>', { id });
  flash('list-msg', json.code === '00', json.code === '00' ? 'Role berhasil dihapus.' : json.message);
  if (json.code === '00') load();
}

load();
</script>
</body>
</html>
