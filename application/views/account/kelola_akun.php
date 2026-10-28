<?php $this->load->view('system/_header'); ?>

<div style="max-width:620px">
  <div class="card" style="margin-bottom:22px">
    <div class="card-head"><div><h2>Profil</h2><div class="sub">Nama dan email dipakai untuk login dan tampil di card task.</div></div></div>
    <div class="field"><label for="p-name">Nama</label><input id="p-name" type="text" maxlength="100"></div>
    <div class="field"><label for="p-email">Email</label><input id="p-email" type="email" maxlength="150"></div>
    <div class="field"><label>Role</label><input id="p-role" type="text" disabled></div>
    <button class="btn btn-primary" id="btn-profile" onclick="saveProfile()">Simpan profil</button>
    <div class="msg" id="msg-profile"></div>
  </div>

  <div class="card">
    <div class="card-head"><div><h2>Ganti password</h2><div class="sub">Minimal 8 karakter.</div></div></div>
    <div class="field"><label for="pw-old">Password lama</label><input id="pw-old" type="password" autocomplete="current-password"></div>
    <div class="field"><label for="pw-new">Password baru</label><input id="pw-new" type="password" autocomplete="new-password"></div>
    <div class="field"><label for="pw-confirm">Konfirmasi password baru</label><input id="pw-confirm" type="password" autocomplete="new-password"></div>
    <button class="btn btn-primary" id="btn-pw" onclick="savePassword()">Ubah password</button>
    <div class="msg" id="msg-pw"></div>
  </div>
</div>

<?php $this->load->view('system/_footer'); ?>
<script>
async function loadMe() {
  const json = await api('<?= site_url('akun/getMe') ?>', null, 'GET');
  if (json.code !== '00') return flash('msg-profile', false, json.message);
  document.getElementById('p-name').value = json.data.name;
  document.getElementById('p-email').value = json.data.email;
  document.getElementById('p-role').value = json.data.role_name || '';
}

async function saveProfile() {
  const btn = document.getElementById('btn-profile');
  btn.disabled = true;
  const json = await api('<?= site_url('akun/updateProfile') ?>', {
    name: document.getElementById('p-name').value,
    email: document.getElementById('p-email').value
  });
  btn.disabled = false;
  flash('msg-profile', json.code === '00', json.code === '00' ? 'Profil berhasil disimpan.' : json.message);
  if (json.code === '00') {
    document.getElementById('sb-name').innerText = json.data.name;
    
  }
}

async function savePassword() {
  const btn = document.getElementById('btn-pw');
  btn.disabled = true;
  const json = await api('<?= site_url('akun/changePassword') ?>', {
    old_password: document.getElementById('pw-old').value,
    new_password: document.getElementById('pw-new').value,
    confirm_password: document.getElementById('pw-confirm').value
  });
  btn.disabled = false;
  flash('msg-pw', json.code === '00', json.code === '00' ? 'Password berhasil diubah.' : json.message);
  if (json.code === '00') ['pw-old', 'pw-new', 'pw-confirm'].forEach(i => document.getElementById(i).value = '');
}

loadMe();
</script>
</body>
</html>
