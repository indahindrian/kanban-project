<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_task extends CI_Model
{
    public function get_by_project($project_id)
    {
        $tasks = $this->db->where('project_id', $project_id)
            ->order_by('id', 'ASC')
            ->get('tasks')
            ->result_array();

        if (empty($tasks)) {
            return [];
        }

        $ids = array_column($tasks, 'id');
        $labels_by_task     = $this->labels_for_tasks($ids);
        $members_by_task    = $this->members_for_tasks($ids);
        $checklist_by_task  = $this->checklist_progress_for_tasks($ids);
        $attach_count       = $this->attachment_counts_for_tasks($ids);

        foreach ($tasks as &$task) {
            $tid = $task['id'];
            $task['labels']            = $labels_by_task[$tid] ?? [];
            $task['members']           = $members_by_task[$tid] ?? [];
            $task['checklist_total']   = $checklist_by_task[$tid]['total'] ?? 0;
            $task['checklist_done']    = $checklist_by_task[$tid]['done'] ?? 0;
            $task['attachment_count']  = $attach_count[$tid] ?? 0;
        }
        unset($task);

        return $tasks;
    }

    public function get_detail($task_id)
    {
        $task = $this->db->where('id', $task_id)->get('tasks')->row_array();
        if (!$task) {
            return null;
        }

        $task['labels']      = $this->labels_for_tasks([$task_id])[$task_id] ?? [];
        $task['members']     = $this->members_for_tasks([$task_id])[$task_id] ?? [];
        $task['attachments'] = $this->db->where('task_id', $task_id)->order_by('id', 'ASC')->get('attachments')->result_array();
        $task['checklists']  = $this->checklists_with_items($task_id);

        return $task;
    }

    private function labels_for_tasks(array $task_ids)
    {
        $rows = $this->db->select('tl.task_id, l.id, l.name, l.color')
            ->from('task_labels tl')
            ->join('labels l', 'l.id = tl.label_id')
            ->where_in('tl.task_id', $task_ids)
            ->get()->result_array();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['task_id']][] = ['id' => $r['id'], 'name' => $r['name'], 'color' => $r['color']];
        }
        return $out;
    }

    private function members_for_tasks(array $task_ids)
    {
        $rows = $this->db->select('tm.task_id, u.id, u.name')
            ->from('task_members tm')
            ->join('users u', 'u.id = tm.user_id')
            ->where_in('tm.task_id', $task_ids)
            ->get()->result_array();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['task_id']][] = ['id' => $r['id'], 'name' => $r['name']];
        }
        return $out;
    }

    private function checklist_progress_for_tasks(array $task_ids)
    {
        $rows = $this->db->select('c.task_id, COUNT(ci.id) AS total, SUM(ci.is_done) AS done')
            ->from('checklists c')
            ->join('checklist_items ci', 'ci.checklist_id = c.id', 'left')
            ->where_in('c.task_id', $task_ids)
            ->group_by('c.task_id')
            ->get()->result_array();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['task_id']] = ['total' => (int) $r['total'], 'done' => (int) $r['done']];
        }
        return $out;
    }

    private function attachment_counts_for_tasks(array $task_ids)
    {
        $rows = $this->db->select('task_id, COUNT(*) AS cnt')
            ->from('attachments')
            ->where_in('task_id', $task_ids)
            ->group_by('task_id')
            ->get()->result_array();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['task_id']] = (int) $r['cnt'];
        }
        return $out;
    }

    private function checklists_with_items($task_id)
    {
        $checklists = $this->db->where('task_id', $task_id)->order_by('sort_order', 'ASC')->get('checklists')->result_array();
        if (empty($checklists)) {
            return [];
        }

        $checklist_ids = array_column($checklists, 'id');
        $items = $this->db->where_in('checklist_id', $checklist_ids)
            ->order_by('sort_order', 'ASC')
            ->get('checklist_items')
            ->result_array();

        $items_by_checklist = [];
        foreach ($items as $item) {
            $items_by_checklist[$item['checklist_id']][] = $item;
        }

        foreach ($checklists as &$c) {
            $c['items'] = $items_by_checklist[$c['id']] ?? [];
        }
        unset($c);

        return $checklists;
    }

    public function get_project_id($task_id)
    {
        $row = $this->db->select('project_id')->where('id', $task_id)->get('tasks')->row_array();
        return $row ? $row['project_id'] : null;
    }

    public function create($project_id, $title)
    {
        $this->db->insert('tasks', [
            'project_id' => $project_id,
            'title'      => $title,
            'status'     => 'no_status',
        ]);
        return $this->db->insert_id();
    }

    public function update_status($task_id, $status)
    {
        return $this->db->where('id', $task_id)->update('tasks', ['status' => $status]);
    }

    public function update_fields($task_id, array $data)
    {
        $fields = array_intersect_key($data, array_flip(['title', 'description', 'due_date', 'start_date', 'progress']));
        if (empty($fields)) {
            return false;
        }
        return $this->db->where('id', $task_id)->update('tasks', $fields);
    }

    public function log_activity($task_id, $user_id, $action, $points = 0)
    {
        $this->db->insert('task_activities', [
            'task_id' => $task_id,
            'user_id' => $user_id,
            'action'  => $action,
            'points'  => $points,
        ]);
    }
}
