<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_role extends CI_Model
{
    // ---- Role ----

    public function all_with_counts()
    {
        return $this->db
            ->select('r.id, r.name, r.description, r.is_system, COUNT(u.id) AS user_count')
            ->from('roles r')
            ->join('users u', 'u.role_id = r.id', 'left')
            ->group_by('r.id')
            ->order_by('r.id', 'ASC')
            ->get()->result_array();
    }

    public function find($id)
    {
        return $this->db->where('id', (int) $id)->get('roles')->row_array();
    }

    public function name_exists($name, $except_id = null)
    {
        $this->db->where('name', $name);
        if ($except_id) {
            $this->db->where('id !=', (int) $except_id);
        }
        return $this->db->count_all_results('roles') > 0;
    }

    public function create($name, $description)
    {
        $this->db->insert('roles', ['name' => $name, 'description' => $description, 'is_system' => 0]);
        return $this->db->insert_id();
    }

    public function update($id, $name, $description)
    {
        return $this->db->where('id', (int) $id)->update('roles', ['name' => $name, 'description' => $description]);
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete('roles');
    }

    public function user_count($id)
    {
        return $this->db->where('role_id', (int) $id)->count_all_results('users');
    }

    // ---- Permission ----

    public function all_permissions()
    {
        return $this->db->order_by('group_key', 'ASC')->order_by('sort_order', 'ASC')->get('permissions')->result_array();
    }

    /** Kode permission milik role. Super Admin = semua permission. */
    public function permission_codes($role_id, $is_super = false)
    {
        if ($is_super) {
            return array_column($this->db->select('code')->get('permissions')->result_array(), 'code');
        }
        $rows = $this->db->select('p.code')
            ->from('role_permissions rp')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', (int) $role_id)
            ->get()->result_array();
        return array_column($rows, 'code');
    }

    /** Ganti seluruh permission sebuah role (atomik). $codes harus sudah divalidasi. */
    public function set_permissions($role_id, array $codes)
    {
        $this->db->trans_start();
        $this->db->where('role_id', (int) $role_id)->delete('role_permissions');
        if (!empty($codes)) {
            $perm_rows = $this->db->select('id')->where_in('code', $codes)->get('permissions')->result_array();
            $batch = [];
            foreach ($perm_rows as $p) {
                $batch[] = ['role_id' => (int) $role_id, 'permission_id' => (int) $p['id']];
            }
            if (!empty($batch)) {
                $this->db->insert_batch('role_permissions', $batch);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
