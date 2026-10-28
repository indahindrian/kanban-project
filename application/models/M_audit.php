<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_audit extends CI_Model
{
    public function log($user_id, $action, $target = null, $detail = null)
    {
        $this->db->insert('audit_logs', [
            'user_id' => $user_id ? (int) $user_id : null,
            'action'  => $action,
            'target'  => $target,
            'detail'  => $detail,
        ]);
    }

    public function recent($limit = 200)
    {
        return $this->db
            ->select('a.id, a.action, a.target, a.detail, a.created_at, u.name AS user_name')
            ->from('audit_logs a')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->order_by('a.id', 'DESC')
            ->limit((int) $limit)
            ->get()->result_array();
    }
}
