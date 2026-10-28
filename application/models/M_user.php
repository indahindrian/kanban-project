<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_user extends CI_Model
{
    /** Untuk member picker / dropdown: hanya user aktif. */
    public function all()
    {
        return $this->db->select('id, name, email')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('users')->result_array();
    }

    public function find($id)
    {
        return $this->db->where('id', $id)->get('users')->row_array();
    }

    public function find_by_email($email)
    {
        return $this->db->where('email', $email)->get('users')->row_array();
    }

    /** User + nama role + flag Super Admin (dipakai MY_Controller tiap request). */
    public function find_with_role($id)
    {
        return $this->db
            ->select('u.*, r.name AS role_name, r.is_system AS role_is_system')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $id)
            ->get()->row_array();
    }

    // ---- Kelola user ----

    public function all_detailed()
    {
        return $this->db
            ->select('u.id, u.name, u.email, u.role_id, u.is_active, u.created_at, r.name AS role_name, r.is_system AS role_is_system')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->order_by('u.is_active', 'DESC')
            ->order_by('u.name', 'ASC')
            ->get()->result_array();
    }

    public function email_exists($email, $except_id = null)
    {
        $this->db->where('email', $email);
        if ($except_id) {
            $this->db->where('id !=', (int) $except_id);
        }
        return $this->db->count_all_results('users') > 0;
    }

    public function create($name, $email, $password, $role_id)
    {
        $this->db->insert('users', [
            'name'      => $name,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'role_id'   => (int) $role_id,
            'is_active' => 1,
        ]);
        return $this->db->insert_id();
    }

    public function update_fields($id, array $fields)
    {
        return $this->db->where('id', (int) $id)->update('users', $fields);
    }

    public function set_password($id, $password)
    {
        return $this->update_fields($id, ['password' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    /** Jumlah Super Admin AKTIF (untuk mencegah sistem tanpa admin). */
    public function count_active_super_admins($except_id = null)
    {
        $this->db->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.is_system', 1)
            ->where('u.is_active', 1);
        if ($except_id) {
            $this->db->where('u.id !=', (int) $except_id);
        }
        return $this->db->count_all_results();
    }
}
