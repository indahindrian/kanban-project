<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>GANTT Global - Project Management</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<style>
  :root { --bg:#f4f5f7; --border:#dfe1e6; --text:#172b4d; --muted:#6b778c; --accent:#0052cc; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:-apple-system,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:var(--text); }
  .topbar { background:#111c3f; color:#fff; padding:16px 28px; display:flex; justify-content:space-between; align-items:center; }
  .topbar a { color:#c3cbe0; text-decoration:none; font-size:13px; }
  .topbar a:hover { color:#fff; }
  .toolbar { padding:16px 28px 0; display:flex; gap:8px; }
  .toolbar button { border:1px solid var(--border); background:#fff; padding:6px 14px; border-radius:4px; cursor:pointer; font-size:13px; }
  .toolbar button.active { background:var(--accent); color:#fff; border-color:var(--accent); }
  #gantt-container { padding:16px 28px 28px; overflow-x:auto; }
  .empty { color:var(--muted); font-size:13px; padding:20px 28px; }
  .bar-wrapper .bar { fill:#4c9aff !important; }
  .bar-wrapper .bar-progress { fill:#0052cc !important; }
</style>
</head>
<body>

<div class="topbar">
  <span>GANTT Chart Global — Semua Project</span>
  <a href="<?= site_url('project') ?>">&larr; Kembali ke Dashboard</a>
</div>
<div class="toolbar">
  <button onclick="ganttChart && ganttChart.change_view_mode('Day')">Day</button>
  <button onclick="ganttChart && ganttChart.change_view_mode('Week')" class="active">Week</button>
  <button onclick="ganttChart && ganttChart.change_view_mode('Month')">Month</button>
</div>
<div id="gantt-container">
  <svg id="gantt"></svg>
</div>

<script>
let ganttChart;

async function loadGantt() {
  const res = await fetch('<?= site_url('project/getGanttGlobal') ?>');
  const json = await res.json();
  if (json.code !== '00') return;

  if (json.data.length === 0) {
    document.getElementById('gantt-container').innerHTML = '<div class="empty">Belum ada task dengan tanggal untuk ditampilkan.</div>';
    return;
  }

  const tasks = json.data.map(t => ({
    id: t.id,
    name: t.name,
    start: t.start,
    end: t.end,
    progress: t.progress,
  }));

  ganttChart = new Gantt("#gantt", tasks, {
    view_mode: 'Week',
    language: 'en',
  });
}
loadGantt();
</script>

</body>
</html>
