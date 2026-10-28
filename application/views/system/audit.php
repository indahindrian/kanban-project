<?php $this->load->view('system/_header'); ?>

<div class="banner banner-blue">
  <b>Audit Log (/audit):</b> Riwayat 200 aktivitas terakhir: login, perubahan user, role, dan hak akses.
</div>

<div class="card">
  <table>
    <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Target</th><th>Detail</th></tr></thead>
    <tbody id="tbody"><tr><td colspan="5" class="empty">Memuat...</td></tr></tbody>
  </table>
</div>

<?php $this->load->view('system/_footer'); ?>
<script>
(async function () {
  const json = await api('<?= site_url('audit/listLogs') ?>', null, 'GET');
  const tbody = document.getElementById('tbody');
  if (json.code !== '00') {
    tbody.innerHTML = '<tr><td colspan="5" class="empty" style="color:var(--danger)">' + esc(json.message) + '</td></tr>';
    return;
  }
  tbody.innerHTML = json.data.map(l =>
    '<tr><td class="muted" style="white-space:nowrap">' + esc(l.created_at) + '</td>' +
    '<td>' + esc(l.user_name || '-') + '</td>' +
    '<td><span class="chip">' + esc(l.action) + '</span></td>' +
    '<td>' + esc(l.target || '') + '</td>' +
    '<td class="muted" style="font-size:12.5px">' + esc(l.detail || '') + '</td></tr>'
  ).join('') || '<tr><td colspan="5" class="empty">Belum ada aktivitas.</td></tr>';
})();
</script>
</body>
</html>
