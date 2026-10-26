<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Scoreboard - Project Management</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
  body { margin:0; font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background:#f4f5f7; color:#172b4d; }
  .topbar { background:#1a2b4c; color:#fff; padding:12px 20px; font-weight:600; }
  .wrap { padding:20px; display:flex; gap:20px; flex-wrap:wrap; }
  .chart-box { background:#fff; border-radius:8px; padding:16px; flex:1; min-width:320px; box-shadow:0 1px 2px rgba(9,30,66,.15); }
  table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 2px rgba(9,30,66,.15); }
  th, td { text-align:left; padding:10px 14px; font-size:14px; border-bottom:1px solid #f0f0f0; }
  th { background:#f8f9fb; color:#6b778c; font-weight:600; font-size:12px; text-transform:uppercase; }
  .rank-1 { color:#b06000; font-weight:700; }
  .rank-2 { color:#42526e; font-weight:700; }
  .rank-3 { color:#8b572a; font-weight:700; }
</style>
</head>
<body>

<div class="topbar">Scoreboard Anggota</div>

<div class="wrap">
  <div class="chart-box">
    <canvas id="scoreChart" height="220"></canvas>
  </div>
  <div style="flex:1; min-width:320px;">
    <table>
      <thead>
        <tr><th>Rank</th><th>Anggota</th><th>Poin</th><th>Task Selesai</th><th>Tepat Waktu</th></tr>
      </thead>
      <tbody id="score-table-body"></tbody>
    </table>
  </div>
</div>

<script>
async function loadScoreboard() {
  const res = await fetch('<?= base_url('project/getScoreboard') ?>');
  const json = await res.json();
  if (json.code !== '00') return;

  const data = json.data;

  // Table
  const tbody = document.getElementById('score-table-body');
  tbody.innerHTML = data.map(row => `
    <tr>
      <td class="rank-${row.rank}">#${row.rank}</td>
      <td>${row.user}</td>
      <td>${row.total_points}</td>
      <td>${row.tasks_done}</td>
      <td>${row.tasks_ontime}</td>
    </tr>
  `).join('');

  // Chart
  new Chart(document.getElementById('scoreChart'), {
    type: 'bar',
    data: {
      labels: data.map(d => d.user),
      datasets: [{
        label: 'Total Poin',
        data: data.map(d => d.total_points),
        backgroundColor: '#0052cc',
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

loadScoreboard();
</script>

</body>
</html>
