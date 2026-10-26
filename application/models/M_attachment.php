<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_attachment extends CI_Model
{
    public function add($task_id, $type, $name, $url, $uploaded_by = null)
    {
        $this->db->insert('attachments', [
            'task_id'     => $task_id,
            'type'        => $type,
            'name'        => $name,
            'url'         => $url,
            'uploaded_by' => $uploaded_by,
        ]);
        return $this->db->insert_id();
    }

    public function find($id)
    {
        return $this->db->where('id', $id)->get('attachments')->row_array();
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete('attachments');
    }
}
