<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Base controller: login session + hak akses dinamis (role -> permission).
 *
 * Role & permission dibaca dari database di SETIAP request (bukan disimpan di
 * session), jadi perubahan di halaman Hak Akses, atau akun yang dinonaktifkan,
 * langsung berlaku tanpa user harus login ulang.
 *
 * Super Admin (roles.is_system = 1) selalu punya semua permission.
 */
class MY_Controller extends CI_Controller
{
    protected $current_user = null;
    protected $perm_codes   = [];
    private $member_cache   = [];

    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('user_id')) {
            $this->load->model('M_user');
            $this->load->model('M_role');

            $user = $this->M_user->find_with_role($this->session->userdata('user_id'));
            if (!$user || !(int) $user['is_active']) {
                // akun dihapus / dinonaktifkan setelah login -> tendang keluar
                $this->session->sess_destroy();
            } else {
                $is_super = (int) $user['role_is_system'] === 1;
                $this->current_user = [
                    'id'        => $user['id'],
                    'name'      => $user['name'],
                    'email'     => $user['email'],
                    'role_id'   => $user['role_id'],
                    'role_name' => $user['role_name'],
                    'is_super'  => $is_super,
                ];
                $this->perm_codes = $this->M_role->permission_codes($user['role_id'], $is_super);
            }
        }

        // Tersedia di semua view
        $this->load->vars([
            'user_name'          => $this->current_user ? $this->current_user['name'] : '',
            'role_name'          => $this->current_user ? $this->current_user['role_name'] : '',
            'can_create_project' => $this->has_permission('project.create'),
            'can_manage_users'   => $this->has_permission('system.users'),
            'can_manage_roles'   => $this->has_permission('system.permissions'),
            'can_view_audit'     => $this->has_permission('system.audit'),
        ]);
    }

    // ---------------------------------------------------------
    // Status login & permission
    // ---------------------------------------------------------
    protected function is_logged_in()
    {
        return $this->current_user !== null;
    }

    protected function is_super_admin()
    {
        return $this->current_user && $this->current_user['is_super'];
    }

    protected function has_permission($code)
    {
        return in_array($code, $this->perm_codes, true);
    }

    /** Boleh melihat semua project (bukan hanya yang di-assign). */
    protected function can_view_all()
    {
        return $this->has_permission('project.view_all');
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

    /** Untuk method yang me-render halaman. */
    protected function require_permission($code)
    {
        if (!$this->require_login()) {
            return false;
        }
        if (!$this->has_permission($code)) {
            show_error('Anda tidak punya izin untuk membuka halaman ini.', 403, 'Akses Ditolak');
            return false;
        }
        return true;
    }

    /** Untuk method API (JSON). */
    protected function require_permission_api($code)
    {
        if (!$this->require_login_api()) {
            return false;
        }
        if (!$this->has_permission($code)) {
            give_response('03', 'Anda tidak punya izin untuk aksi ini');
            return false;
        }
        return true;
    }

    // ---------------------------------------------------------
    // Akses per project
    // ---------------------------------------------------------
    protected function is_project_member($project_id)
    {
        if (!$this->is_logged_in()) {
            return false;
        }
        $project_id = (int) $project_id;
        if (!isset($this->member_cache[$project_id])) {
            $this->member_cache[$project_id] = $this->db
                ->where('project_id', $project_id)
                ->where('user_id', $this->current_user['id'])
                ->count_all_results('project_members') > 0;
        }
        return $this->member_cache[$project_id];
    }

    /** Boleh MELIHAT project: punya 'project.view_all' atau menjadi member. */
    protected function can_view_project($project_id)
    {
        if (!$this->is_logged_in()) {
            return false;
        }
        return $this->has_permission('project.view_all') || $this->is_project_member($project_id);
    }

    /** Nama lama, dipertahankan agar endpoint baca (GET) tidak perlu diubah. */
    protected function can_access_project($project_id)
    {
        return $this->can_view_project($project_id);
    }

    /** Ubah project/task/label/checklist: 'project.edit_own' + member project tsb. */
    protected function can_edit_project($project_id)
    {
        if (!$this->can_view_project($project_id)) {
            return false;
        }
        if ($this->is_super_admin()) {
            return true;
        }
        return $this->has_permission('project.edit_own') && $this->is_project_member($project_id);
    }

    /**
     * Dokumen (lampiran, status) boleh disentuh di project sendiri (member),
     * atau di project mana saja kalau punya 'doc.edit_others'.
     */
    private function in_doc_scope($project_id)
    {
        return $this->is_project_member($project_id) || $this->has_permission('doc.edit_others');
    }

    /** Geser status task / centang checklist. */
    protected function can_update_status($project_id)
    {
        if (!$this->can_view_project($project_id)) {
            return false;
        }
        if ($this->can_edit_project($project_id)) {
            return true;
        }
        return $this->has_permission('doc.update_status') && $this->in_doc_scope($project_id);
    }

    /** Tambah/hapus lampiran (cheat sheet, BRD, link). */
    protected function can_upload_doc($project_id)
    {
        if (!$this->can_view_project($project_id)) {
            return false;
        }
        if ($this->is_super_admin()) {
            return true;
        }
        return $this->has_permission('doc.upload') && $this->in_doc_scope($project_id);
    }
}
