<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>GANTT - Project Management</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<style>
  body { margin:0; font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background:#f4f5f7; }
  .topbar { background:#1a2b4c; color:#fff; padding:12px 20px; font-weight:600; }
  .toolbar { padding:12px 20px; display:flex; gap:8px; }
  .toolbar button { border:1px solid #dfe1e6; background:#fff; padding:6px 14px; border-radius:4px; cursor:pointer; font-size:13px; }
  .toolbar button.active { background:#0052cc; color:#fff; border-color:#0052cc; }
  #gantt-container { padding:0 20px 20px; overflow-x:auto; }
  .bar-wrapper .bar { fill: #4c9aff !important; }
  .bar-wrapper .bar-progress { fill: #0052cc !important; }
</style>
</head>
<body>

<div class="topbar">GANTT Chart - Semua Task per Anggota</div>
<div class="toolbar">
  <button onclick="ganttChart.change_view_mode('Day')">Day</button>
  <button onclick="ganttChart.change_view_mode('Week')" class="active">Week</button>
  <button onclick="ganttChart.change_view_mode('Month')">Month</button>
</div>
<div id="gantt-container">
  <svg id="gantt"></svg>
</div>

<script>
let ganttChart;

async function loadGantt() {
  const res = await fetch('<?= base_url('project/getGantt') ?>');
  const json = await res.json();
  if (json.code !== '00') return;

  const tasks = json.data.map(t => ({
    id: t.id,
    name: `${t.name} (${t.assignee})`,
    start: t.start,
    end: t.end,
    progress: t.progress,
  }));

  ganttChart = new Gantt("#gantt", tasks, {
    view_mode: 'Week',
    language: 'en',
    on_click: (task) => console.log('Task clicked:', task),
    on_date_change: (task, start, end) => {
      // Nanti: kirim AJAX ke controller untuk update start/end date di DB
      console.log('Date changed:', task.id, start, end);
    },
    on_progress_change: (task, progress) => {
      // Nanti: kirim AJAX untuk update progress di DB
      console.log('Progress changed:', task.id, progress);
    },
  });
}

loadGantt();
</script>

</body>
</html>
