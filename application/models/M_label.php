<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_label extends CI_Model
{
    public function get_by_project($project_id)
    {
        return $this->db->where('project_id', $project_id)->order_by('id', 'ASC')->get('labels')->result_array();
    }

    public function create($project_id, $name, $color)
    {
        $this->db->insert('labels', [
            'project_id' => $project_id,
            'name'       => $name,
            'color'      => $color,
        ]);
        return $this->db->insert_id();
    }

    public function attach($task_id, $label_id)
    {
        if ($this->db->where(['task_id' => $task_id, 'label_id' => $label_id])->get('task_labels')->num_rows() > 0) {
            return true;
        }
        return $this->db->insert('task_labels', ['task_id' => $task_id, 'label_id' => $label_id]);
    }

    public function detach($task_id, $label_id)
    {
        return $this->db->where(['task_id' => $task_id, 'label_id' => $label_id])->delete('task_labels');
    }
}
