<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kelola Role (halaman 2) - butuh permission 'system.permissions'.
 * Role sistem (Super Admin) tidak bisa diubah atau dihapus.
 */
class C_role extends MY_Controller
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
        $this->load->view('system/role', ['page_title' => 'Kelola Peran / Role', 'active' => 'role']);
    }

    public function listRoles()
    {
        try {
            if (!$this->require_permission_api('system.permissions')) return;
            give_response('00', 'GET ROLES SUKSES', $this->M_role->all_with_counts());
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function store()
    {
        try {
            if (!$this->require_permission_api('system.permissions')) return;

            $name = trim((string) $this->input->post('name'));
            $desc = trim((string) $this->input->post('description'));
            if ($err = $this->validate($name, $desc, null)) return give_response('01', $err);

            $id = $this->M_role->create($name, $desc);
            $this->M_audit->log($this->current_user['id'], 'role.create', $name);
            give_response('00', 'ROLE BERHASIL DITAMBAHKAN', ['id' => $id]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function update()
    {
        try {
            if (!$this->require_permission_api('system.permissions')) return;

            $id   = (int) $this->input->post('id');
            $name = trim((string) $this->input->post('name'));
            $desc = trim((string) $this->input->post('description'));

            $role = $this->M_role->find($id);
            if (!$role) return give_response('01', 'Role tidak ditemukan');
            if ((int) $role['is_system'] === 1) return give_response('01', 'Role sistem tidak bisa diubah');
            if ($err = $this->validate($name, $desc, $id)) return give_response('01', $err);

            $this->M_role->update($id, $name, $desc);
            $this->M_audit->log($this->current_user['id'], 'role.update', $name);
            give_response('00', 'ROLE BERHASIL DIPERBARUI');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function delete()
    {
        try {
            if (!$this->require_permission_api('system.permissions')) return;

            $id   = (int) $this->input->post('id');
            $role = $this->M_role->find($id);
            if (!$role) return give_response('01', 'Role tidak ditemukan');
            if ((int) $role['is_system'] === 1) return give_response('01', 'Role sistem tidak bisa dihapus');
            if ($this->M_role->user_count($id) > 0) {
                return give_response('01', 'Role masih dipakai user. Pindahkan user ke role lain dulu.');
            }

            $this->M_role->delete($id);
            $this->M_audit->log($this->current_user['id'], 'role.delete', $role['name']);
            give_response('00', 'ROLE BERHASIL DIHAPUS');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    private function validate($name, $desc, $except_id)
    {
        if ($name === '' || mb_strlen($name) > 100) return 'Nama role wajib diisi (maks. 100 karakter)';
        if (mb_strlen($desc) > 255) return 'Keterangan maksimal 255 karakter';
        if ($this->M_role->name_exists($name, $except_id)) return 'Nama role sudah ada';
        return null;
    }
}
