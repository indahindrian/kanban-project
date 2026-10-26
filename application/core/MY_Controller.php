<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Base controller: session-based auth guard untuk 2 role,
 * "anggota" dan "atasan" (lihat database/migration_auth_roles.sql).
 */
class MY_Controller extends CI_Controller
{
    protected $current_user = null;

    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('user_id')) {
            $this->current_user = [
                'id'    => $this->session->userdata('user_id'),
                'name'  => $this->session->userdata('user_name'),
                'email' => $this->session->userdata('user_email'),
                'role'  => $this->session->userdata('user_role'),
            ];
        }
    }

    protected function is_logged_in()
    {
        return $this->current_user !== null;
    }

    protected function is_atasan()
    {
        return $this->current_user && $this->current_user['role'] === 'atasan';
    }

    protected function require_login()
    {
        if (!$this->is_logged_in()) {
            redirect('auth');
            return false;
        }
        return true;
    }

    protected function require_login_api()
    {
        if (!$this->is_logged_in()) {
            $this->load->helper('response');
            give_response('03', 'Silakan login terlebih dahulu');
            return false;
        }
        return true;
    }

    /**
     * Anggota hanya boleh akses project yang mereka jadi member-nya.
     * Atasan boleh akses semua project.
     */
    protected function can_access_project($project_id)
    {
        if (!$this->is_logged_in()) {
            return false;
        }
        if ($this->is_atasan()) {
            return true;
        }
        return (bool) $this->db
            ->where('project_id', $project_id)
            ->where('user_id', $this->current_user['id'])
            ->get('kb_project_members')
            ->num_rows();
    }
}
