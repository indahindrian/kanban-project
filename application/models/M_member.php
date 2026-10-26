<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_member extends CI_Model
{
    public function add($task_id, $user_id)
    {
        if ($this->db->where(['task_id' => $task_id, 'user_id' => $user_id])->get('task_members')->num_rows() > 0) {
            return true;
        }
        return $this->db->insert('task_members', ['task_id' => $task_id, 'user_id' => $user_id]);
    }

    public function remove($task_id, $user_id)
    {
        return $this->db->where(['task_id' => $task_id, 'user_id' => $user_id])->delete('task_members');
    }
}
