<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Hak Akses / Permissions (halaman 3) - butuh permission 'system.permissions'.
 *  - Role Super Admin: terkunci, selalu punya semua izin.
 *  - Permission bertanda super_only (grup Pengaturan Sistem) tidak bisa diberikan
 *    ke role lain, supaya tidak ada eskalasi hak akses.
 */
class C_permission extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('response');
        $this->load->model('M_role');
        $this->load->model('M_audit');
    }

    public function index()
    {
        if (!$this->require_permission('system.permissions')) return;
        $this->load->view('system/permission', ['page_title' => 'Pengaturan Hak Akses (Permissions)', 'active' => 'permission']);
    }

    public function getRolePermissions()
    {
        try {
            if (!$this->require_permission_api('system.permissions')) return;

            $role_id = (int) $this->input->get('role_id');
            $role = $this->M_role->find($role_id);
            if (!$role) return give_response('01', 'Role tidak ditemukan');

            $locked = (int) $role['is_system'] === 1;
            give_response('00', 'GET HAK AKSES SUKSES', [
                'role'        => $role,
                'locked'      => $locked,
                'permissions' => $this->M_role->all_permissions(),
                'granted'     => $this->M_role->permission_codes($role_id, $locked),
                'roles'       => $this->M_role->all_with_counts(),
            ]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function save()
    {
        try {
            if (!$this->require_permission_api('system.permissions')) return;

            $role_id = (int) $this->input->post('role_id');
            $codes   = $this->input->post('codes');
            $codes   = is_array($codes) ? array_values(array_unique(array_map('strval', $codes))) : [];

            $role = $this->M_role->find($role_id);
            if (!$role) return give_response('01', 'Role tidak ditemukan');
            if ((int) $role['is_system'] === 1) return give_response('01', 'Hak akses Super Admin tidak bisa diubah');

            $valid = [];
            foreach ($this->M_role->all_permissions() as $p) {
                $valid[$p['code']] = (int) $p['super_only'] === 1;
            }
            foreach ($codes as $code) {
                if (!isset($valid[$code])) return give_response('01', 'Hak akses tidak dikenal: ' . $code);
                if ($valid[$code]) return give_response('01', 'Hak akses sistem hanya untuk Super Admin');
            }

            if (!$this->M_role->set_permissions($role_id, $codes)) {
                return give_response('01', 'Gagal menyimpan hak akses');
            }
            $this->M_audit->log($this->current_user['id'], 'permission.update', $role['name'], implode(', ', $codes));
            give_response('00', 'HAK AKSES BERHASIL DISIMPAN');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }
}
