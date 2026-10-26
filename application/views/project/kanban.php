<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kanban - Project Management</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<style>
  :root {
    --bg: #f4f5f7;
    --card-bg: #ffffff;
    --border: #dfe1e6;
    --text: #172b4d;
    --muted: #6b778c;
    --accent: #0052cc;
  }
  * { box-sizing: border-box; }
  body { margin:0; font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: var(--text); }
  .topbar { background:#1a2b4c; color:#fff; padding:12px 20px; font-weight:600; display:flex; justify-content:space-between; align-items:center; }
  .board { display:flex; gap:16px; padding:20px; overflow-x:auto; align-items:flex-start; }
  .column { background:#ebecf0; border-radius:8px; width:280px; min-width:280px; padding:10px; flex-shrink:0; }
  .column-header { display:flex; justify-content:space-between; align-items:center; padding:4px 8px 10px; font-weight:600; font-size:14px; }
  .badge { background:#dfe1e6; border-radius:12px; padding:1px 8px; font-size:12px; color:var(--muted); }
  .col-no_status .column-header { color:#42526e; }
  .col-processing .column-header { color:#b06000; }
  .col-processing { border-top:3px solid #ffab00; }
  .col-closed { border-top:3px solid #36b37e; }
  .task-list { min-height: 40px; }
  .task-card { background:var(--card-bg); border-radius:6px; padding:10px 12px; margin-bottom:8px; box-shadow:0 1px 2px rgba(9,30,66,.2); cursor:grab; }
  .task-card:active { cursor:grabbing; }
  .task-title { font-size:14px; margin-bottom:8px; }
  .task-footer { display:flex; justify-content:space-between; align-items:center; }
  .due { font-size:11px; color:var(--muted); }
  .avatar { width:24px; height:24px; border-radius:50%; background:var(--accent); color:#fff; font-size:11px; display:flex; align-items:center; justify-content:center; font-weight:600; }
  .progress-bar { height:5px; background:#dfe1e6; border-radius:4px; margin-top:6px; overflow:hidden; }
  .progress-fill { height:100%; background:var(--accent); }
  .sortable-ghost { opacity:.4; }
  .empty { color:var(--muted); font-size:12px; padding:10px 4px; }
</style>
</head>
<body>

<div class="topbar">
  <span x-data x-text="document.title"></span>
  <span id="project-name">...</span>
</div>

<div class="board" x-data="kanbanBoard()" x-init="load()">
  <template x-for="col in columns" :key="col.status_key">
    <div class="column" :class="'col-' + col.status_key">
      <div class="column-header">
        <span x-text="col.label"></span>
        <span class="badge" x-text="col.tasks.length"></span>
      </div>
      <div class="task-list" :data-status="col.status_key" x-ref="'list_' + col.status_key">
        <template x-if="col.tasks.length === 0">
          <div class="empty">Tidak ada task</div>
        </template>
        <template x-for="task in col.tasks" :key="task.id">
          <div class="task-card" :data-id="task.id">
            <div class="task-title" x-text="task.title"></div>
            <div class="progress-bar"><div class="progress-fill" :style="'width:' + task.progress + '%'"></div></div>
            <div class="task-footer" style="margin-top:8px;">
              <span class="due" x-text="task.due_date"></span>
              <span class="avatar" x-show="task.assigned_to" x-text="task.assigned_to ? task.assigned_to.name.charAt(0) : ''"></span>
            </div>
          </div>
        </template>
      </div>
    </div>
  </template>
</div>

<script>
function kanbanBoard() {
  return {
    columns: [],
    async load() {
      const res = await fetch('<?= base_url('project/getKanban') ?>');
      const json = await res.json();
      if (json.code === '00') {
        document.getElementById('project-name').innerText = json.data.project_name;
        this.columns = json.data.columns;
        this.$nextTick(() => this.initSortable());
      }
    },
    initSortable() {
      document.querySelectorAll('.task-list').forEach(el => {
        new Sortable(el, {
          group: 'kanban',
          animation: 150,
          onEnd: async (evt) => {
            const taskId = evt.item.dataset.id;
            const newStatus = evt.to.dataset.status;
            await fetch('<?= base_url('project/updateTaskStatus') ?>', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `task_id=${taskId}&new_status=${newStatus}`
            });
          }
        });
      });
    }
  }
}
</script>

</body>
</html>
