<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_user extends CI_Model
{
    public function all()
    {
        return $this->db->select('id, name, email')->order_by('name', 'ASC')->get('users')->result_array();
    }

    public function find($id)
    {
        return $this->db->where('id', $id)->get('users')->row_array();
    }

    public function find_by_email($email)
    {
        return $this->db->where('email', $email)->get('users')->row_array();
    }
}
