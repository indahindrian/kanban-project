<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_checklist extends CI_Model
{
    public function create($task_id, $title = 'Checklist')
    {
        $sort = (int) $this->db->where('task_id', $task_id)->count_all_results('checklists');
        $this->db->insert('checklists', [
            'task_id'    => $task_id,
            'title'      => $title,
            'sort_order' => $sort,
        ]);
        return $this->db->insert_id();
    }

    public function delete($checklist_id)
    {
        return $this->db->where('id', $checklist_id)->delete('checklists');
    }

    public function add_item($checklist_id, $text)
    {
        $sort = (int) $this->db->where('checklist_id', $checklist_id)->count_all_results('checklist_items');
        $this->db->insert('checklist_items', [
            'checklist_id' => $checklist_id,
            'text'         => $text,
            'sort_order'   => $sort,
        ]);
        return $this->db->insert_id();
    }

    public function toggle_item($item_id)
    {
        $item = $this->db->where('id', $item_id)->get('checklist_items')->row_array();
        if (!$item) {
            return false;
        }
        return $this->db->where('id', $item_id)->update('checklist_items', ['is_done' => $item['is_done'] ? 0 : 1]);
    }

    public function delete_item($item_id)
    {
        return $this->db->where('id', $item_id)->delete('checklist_items');
    }

    public function find_item($item_id)
    {
        return $this->db->where('id', $item_id)->get('checklist_items')->row_array();
    }

    public function find($checklist_id)
    {
        return $this->db->where('id', $checklist_id)->get('checklists')->row_array();
    }
}
