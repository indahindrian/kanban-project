<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Planning - Project Management</title>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<style>
  :root { --bg:#f4f5f7; --card-bg:#fff; --border:#dfe1e6; --text:#172b4d; --muted:#6b778c; --accent:#0052cc; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:-apple-system,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:var(--text); }
  .topbar { background:#1a2b4c; color:#fff; padding:14px 24px; display:flex; justify-content:space-between; align-items:center; }
  .topbar a { color:#c3cbe0; text-decoration:none; font-size:13px; }
  .topbar a:hover { color:#fff; }
  .layout { display:flex; gap:20px; padding:24px; align-items:flex-start; }

  /* ===== FILTER SIDEBAR ===== */
  .filter-sidebar { width:220px; min-width:220px; display:flex; flex-direction:column; gap:14px; }
  .filter-box { background:var(--card-bg); border:1px solid var(--border); border-radius:8px; padding:16px; }
  .filter-title { font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; margin-bottom:10px; }
  .filter-item { display:flex; align-items:center; gap:8px; padding:6px 0; font-size:13px; }
  .filter-item input { width:15px; height:15px; }
  .filter-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
  .filter-empty { color:var(--muted); font-size:12px; }
  .btn-block { width:100%; margin-top:4px; }

  /* ===== CALENDAR CARD ===== */
  .calendar-card { flex:1; min-width:0; background:var(--card-bg); border:1px solid var(--border); border-radius:8px; padding:18px; }
  .fc { font-family:-apple-system,"Segoe UI",Roboto,sans-serif; font-size:13px; }
  .fc .fc-toolbar-title { font-size:16px; color:var(--text); }
  .fc .fc-button-primary { background:var(--accent); border-color:var(--accent); }
  .fc .fc-button-primary:hover { background:#0047b3; }
  .fc .fc-button-primary:disabled { background:#8fb4e8; border-color:#8fb4e8; }
  .fc-event { cursor:grab; font-size:11.5px; padding:1px 4px; }
  .fc-daygrid-event-dot { display:none; }
  .fc-daygrid-day:hover { background:#f4f8ff; cursor:pointer; }

  /* ===== MODAL (dipakai untuk Tambah & Edit) ===== */
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(9,30,66,.55); z-index:100; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:10px; width:100%; max-width:420px; padding:22px; }
  .modal-box h3 { margin:0 0 16px; font-size:15px; }
  .field { margin-bottom:13px; }
  .field label { display:block; font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; margin-bottom:5px; }
  .field input, .field select { width:100%; padding:8px 10px; border:1px solid var(--border); border-radius:5px; font-size:13.5px; font-family:inherit; background:#fff; }
  .field input[readonly] { background:#f4f5f7; color:var(--muted); }
  .modal-actions { display:flex; justify-content:space-between; align-items:center; margin-top:16px; }
  .modal-actions-right { display:flex; gap:8px; }
  .btn { border:none; border-radius:6px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:500; }
  .btn-outline { background:#fff; border:1px solid var(--border); color:var(--text); }
  .btn-primary { background:var(--accent); color:#fff; }
  .btn-danger { background:#fff; color:#bf2600; border:1px solid #ffbdad; }
  .btn-danger:hover { background:#ffebe6; }
  .form-error { color:#bf2600; font-size:12px; margin-top:8px; display:none; }
</style>
</head>
<body>

<div class="topbar">
  <span>Planning</span>
  <a href="<?= site_url('project') ?>">&larr; Kembali ke Dashboard</a>
</div>

<div class="layout">

  <div class="filter-sidebar">
    <?php if ($is_atasan): ?>
    <div class="filter-box">
      <div class="filter-title">Anggota</div>
      <div id="member-filter"><div class="filter-empty">Memuat...</div></div>
    </div>
    <?php endif; ?>

    <div class="filter-box">
      <div class="filter-title">Kategori</div>
      <div id="category-filter"><div class="filter-empty">Memuat...</div></div>
    </div>

    <div class="filter-box">
      <button class="btn btn-primary btn-block" onclick="openAddEventModal()">+ Tambah kegiatan</button>
      <button class="btn btn-outline btn-block" onclick="exportCsv()">&#8595; Export CSV</button>
      <button class="btn btn-outline btn-block" onclick="window.print()">&#128424; Print</button>
    </div>
  </div>

  <div class="calendar-card">
    <div id="calendar"></div>
  </div>

</div>

<!-- ===== MODAL: TAMBAH / EDIT KEGIATAN ===== -->
<div class="modal-overlay" id="event-modal">
  <div class="modal-box">
    <h3 id="event-modal-title">Tambah kegiatan</h3>
    <div class="field">
      <label>Project</label>
      <select id="ev-project" onchange="onProjectChange()"></select>
    </div>
    <div class="field">
      <label>Judul kegiatan</label>
      <input type="text" id="ev-title" placeholder="Contoh: Review dokumen">
    </div>
    <?php if ($is_atasan): ?>
    <div class="field">
      <label>Ditugaskan ke</label>
      <select id="ev-member"></select>
    </div>
    <?php endif; ?>
    <div class="field">
      <label>Kategori (opsional)</label>
      <select id="ev-category"><option value="">Tanpa kategori</option></select>
    </div>
    <div class="field">
      <label>Tanggal</label>
      <input type="date" id="ev-date">
    </div>
    <div class="form-error" id="ev-error"></div>
    <div class="modal-actions">
      <button class="btn btn-danger" id="ev-delete-btn" style="display:none;" onclick="deleteEvent()">Hapus</button>
      <div class="modal-actions-right">
        <button class="btn btn-outline" onclick="closeEventModal()">Batal</button>
        <button class="btn btn-primary" onclick="submitEvent()">Simpan</button>
      </div>
    </div>
  </div>
</div>

<script>
const isAtasan = <?= $is_atasan ? 'true' : 'false' ?>;
const currentUserId = <?= json_encode($user_id) ?>;
let projectsList = [];
let usersList = [];
let calendar;
let editingTaskId = null; // null = mode tambah, terisi = mode edit

// ===== Load data awal (dropdown project & anggota) =====
async function loadInitialData() {
  const [projRes, userRes] = await Promise.all([
    fetch('<?= site_url('project/getProjects') ?>').then(r => r.json()),
    isAtasan ? fetch('<?= site_url('project/getUsers') ?>').then(r => r.json()) : Promise.resolve({ code: '00', data: [] }),
  ]);
  projectsList = projRes.code === '00' ? projRes.data : [];
  usersList = userRes.code === '00' ? userRes.data : [];

  document.getElementById('ev-project').innerHTML = projectsList
    .map(p => `<option value="${p.id}">${p.name}</option>`).join('') || '<option value="">Belum ada project</option>';

  if (isAtasan) {
    document.getElementById('ev-member').innerHTML = usersList
      .map(u => `<option value="${u.id}">${u.name}</option>`).join('');
  }

  onProjectChange();
}

async function onProjectChange() {
  const projectId = document.getElementById('ev-project').value;
  const catSelect = document.getElementById('ev-category');
  if (!projectId) { catSelect.innerHTML = '<option value="">Tanpa kategori</option>'; return; }

  const res = await fetch(`<?= site_url('project/getLabels') ?>?project_id=${projectId}`).then(r => r.json());
  const labels = res.code === '00' ? res.data : [];
  catSelect.innerHTML = '<option value="">Tanpa kategori</option>' +
    labels.map(l => `<option value="${l.id}" data-color="${l.color}">${l.name}</option>`).join('');
}

// ===== Kalender =====
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  let seenUsers = {};
  let seenCategories = {};

  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
    height: 'auto',
    firstDay: 1,
    editable: true,
    eventStartEditable: true,
    events: function (fetchInfo, successCallback, failureCallback) {
      const start = fetchInfo.startStr.slice(0, 10);
      const end = fetchInfo.endStr.slice(0, 10);
      fetch(`<?= site_url('project/getPlanning') ?>?start=${start}&end=${end}`)
        .then(r => r.json())
        .then(json => {
          const events = json.code === '00' ? json.data : [];
          seenUsers = {};
          seenCategories = {};
          events.forEach(e => {
            seenUsers[e.extendedProps.user] = e.backgroundColor;
            if (e.extendedProps.label_name) {
              seenCategories[e.extendedProps.label_name] = e.extendedProps.label_color;
            }
          });
          if (isAtasan) renderMemberFilter();
          renderCategoryFilter();
          successCallback(events);
        })
        .catch(failureCallback);
    },
    eventClick: function (info) {
      openEditEventModal(info.event);
    },
    eventDrop: async function (info) {
      const newDate = info.event.startStr.slice(0, 10);
      const taskId = info.event.extendedProps.task_id;
      const res = await fetch('<?= site_url('project/updateTaskDetail') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ task_id: taskId, due_date: newDate }).toString(),
      }).then(r => r.json());
      if (res.code !== '00') {
        alert('Gagal memindahkan jadwal: ' + (res.message || ''));
        info.revert();
      }
    },
    dateClick: function (info) {
      openAddEventModal(info.dateStr.slice(0, 10));
    },
  });

  calendar.render();
  loadInitialData();

  function renderMemberFilter() {
    const el = document.getElementById('member-filter');
    const names = Object.keys(seenUsers);
    if (names.length === 0) { el.innerHTML = '<div class="filter-empty">Belum ada kegiatan.</div>'; return; }
    el.innerHTML = names.map(name => `
      <label class="filter-item">
        <input type="checkbox" checked onchange="toggleFilter('user', '${name.replace(/'/g, "\\'")}', this.checked)">
        <span class="filter-dot" style="background:${seenUsers[name]}"></span>
        <span>${name}</span>
      </label>
    `).join('');
  }

  function renderCategoryFilter() {
    const el = document.getElementById('category-filter');
    const cats = Object.keys(seenCategories);
    let html = `
      <label class="filter-item">
        <input type="checkbox" checked onchange="toggleFilter('nocat', '', this.checked)">
        <span class="filter-dot" style="background:#c1c7d0"></span>
        <span>Tanpa kategori</span>
      </label>`;
    html += cats.map(name => `
      <label class="filter-item">
        <input type="checkbox" checked onchange="toggleFilter('category', '${name.replace(/'/g, "\\'")}', this.checked)">
        <span class="filter-dot" style="background:${seenCategories[name]}"></span>
        <span>${name}</span>
      </label>
    `).join('');
    el.innerHTML = html || '<div class="filter-empty">Belum ada kategori.</div>';
  }
});

function toggleFilter(type, value, checked) {
  calendar.getEvents().forEach(ev => {
    let match = false;
    if (type === 'user') match = ev.extendedProps.user === value;
    if (type === 'category') match = ev.extendedProps.label_name === value;
    if (type === 'nocat') match = !ev.extendedProps.label_name;
    if (match) ev.setProp('display', checked ? 'auto' : 'none');
  });
}

// ===== Modal Tambah/Edit Kegiatan =====
function openAddEventModal(prefillDate) {
  editingTaskId = null;
  document.getElementById('event-modal-title').innerText = 'Tambah kegiatan';
  document.getElementById('ev-title').value = '';
  document.getElementById('ev-title').removeAttribute('readonly');
  document.getElementById('ev-project').disabled = false;
  document.getElementById('ev-date').value = prefillDate || new Date().toISOString().slice(0, 10);
  document.getElementById('ev-error').style.display = 'none';
  document.getElementById('ev-delete-btn').style.display = 'none';
  onProjectChange();
  document.getElementById('event-modal').classList.add('open');
}

function openEditEventModal(event) {
  const p = event.extendedProps;
  editingTaskId = p.task_id;
  document.getElementById('event-modal-title').innerText = 'Edit kegiatan';
  document.getElementById('ev-title').value = event.title.replace(p.user + ': ', '');
  document.getElementById('ev-project').value = p.project_id;
  document.getElementById('ev-project').disabled = true;
  document.getElementById('ev-date').value = event.startStr.slice(0, 10);
  document.getElementById('ev-error').style.display = 'none';
  document.getElementById('ev-delete-btn').style.display = 'inline-block';

  onProjectChange().then(() => {
    if (p.label_name) {
      const select = document.getElementById('ev-category');
      [...select.options].forEach(o => { if (o.text === p.label_name) select.value = o.value; });
    }
  });

  if (isAtasan) document.getElementById('ev-member').value = p.user_id;

  document.getElementById('event-modal').classList.add('open');
}

function closeEventModal() {
  document.getElementById('event-modal').classList.remove('open');
}

async function submitEvent() {
  const project_id = document.getElementById('ev-project').value;
  const title = document.getElementById('ev-title').value.trim();
  const due_date = document.getElementById('ev-date').value;
  const category_id = document.getElementById('ev-category').value;
  const member_id = isAtasan ? document.getElementById('ev-member').value : currentUserId;
  const errorEl = document.getElementById('ev-error');

  if (!project_id || !title || !due_date) {
    errorEl.textContent = 'Project, judul, dan tanggal wajib diisi.';
    errorEl.style.display = 'block';
    return;
  }

  try {
    let task_id = editingTaskId;

    if (!task_id) {
      const createRes = await fetch('<?= site_url('project/createTask') ?>', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ project_id, title }).toString(),
      }).then(r => r.json());
      if (createRes.code !== '00') throw new Error(createRes.message);
      task_id = createRes.data.id;

      await fetch('<?= site_url('project/addTaskMember') ?>', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ task_id, user_id: member_id }).toString(),
      });
    } else {
      await fetch('<?= site_url('project/updateTaskDetail') ?>', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ task_id, title, due_date }).toString(),
      });
      if (isAtasan) {
        await fetch('<?= site_url('project/addTaskMember') ?>', {
          method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ task_id, user_id: member_id }).toString(),
        });
      }
    }

    if (!editingTaskId) {
      await fetch('<?= site_url('project/updateTaskDetail') ?>', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ task_id, due_date }).toString(),
      });
    }
    if (category_id) {
      await fetch('<?= site_url('project/attachLabel') ?>', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ task_id, label_id: category_id }).toString(),
      });
    }

    closeEventModal();
    calendar.refetchEvents();
  } catch (err) {
    errorEl.textContent = 'Gagal menyimpan: ' + (err.message || 'terjadi kesalahan');
    errorEl.style.display = 'block';
  }
}

async function deleteEvent() {
  if (!editingTaskId) return;
  if (!confirm('Yakin mau hapus kegiatan ini?')) return;

  const res = await fetch('<?= site_url('project/deleteTask') ?>', {
    method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ task_id: editingTaskId }).toString(),
  }).then(r => r.json());

  if (res.code === '00') {
    closeEventModal();
    calendar.refetchEvents();
  } else {
    alert('Gagal menghapus: ' + (res.message || ''));
  }
}

// ===== Export CSV =====
function exportCsv() {
  const events = calendar.getEvents().filter(e => e.display !== 'none');
  if (events.length === 0) { alert('Tidak ada kegiatan untuk di-export.'); return; }

  let csv = 'Tanggal,Anggota,Kegiatan,Project,Kategori,Status\n';
  events.forEach(e => {
    const p = e.extendedProps;
    const title = e.title.replace(p.user + ': ', '');
    csv += [e.startStr.slice(0, 10), p.user, title, p.project, p.label_name || '-', p.status].map(v => `"${v}"`).join(',') + '\n';
  });

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'planning-' + new Date().toISOString().slice(0, 10) + '.csv';
  link.click();
}
</script>

</body>
</html>