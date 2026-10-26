<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_project extends CI_Model
{
    private function base_select()
    {
        return $this->db
            ->select('p.id, p.name, p.description, p.status, p.owner_id, u.name AS owner,
                      p.tanggal_rencana_mulai, p.tanggal_rencana_selesai,
                      p.tanggal_aktual_mulai, p.tanggal_aktual_selesai,
                      ROUND(COALESCE(AVG(t.progress), 0)) AS progress')
            ->from('projects p')
            ->join('users u', 'u.id = p.owner_id', 'left')
            ->join('tasks t', 't.project_id = p.id', 'left')
            ->group_by('p.id');
    }

    public function get_all()
    {
        return $this->base_select()->order_by('p.created_at', 'DESC')->get()->result_array();
    }

    /**
     * Atasan lihat semua project. Anggota hanya lihat project yang mereka
     * jadi member-nya (project_members), sesuai flow "project yang di-assign".
     */
    public function get_all_for_user($user_id, $is_atasan)
    {
        $query = $this->base_select();
        if (!$is_atasan) {
            $query->join('project_members pm', 'pm.project_id = p.id')
                ->where('pm.user_id', $user_id);
        }
        return $query->order_by('p.created_at', 'DESC')->get()->result_array();
    }

    public function get_by_id($id)
    {
        return $this->base_select()->where('p.id', $id)->get()->row_array();
    }

    public function update($id, array $data)
    {
        $fields = array_intersect_key($data, array_flip([
            'name', 'description', 'status', 'owner_id',
            'tanggal_rencana_mulai', 'tanggal_rencana_selesai',
            'tanggal_aktual_mulai', 'tanggal_aktual_selesai',
        ]));
        if (empty($fields)) {
            return false;
        }
        return $this->db->where('id', $id)->update('projects', $fields);
    }
}
