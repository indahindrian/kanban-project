<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Manajemen User (halaman 1) - butuh permission 'system.users'.
 * Akun tidak dihapus, hanya dinonaktifkan, supaya riwayat project,
 * Scoreboard, dan attachment tetap utuh.
 */
class C_user extends MY_Controller
{
    const MIN_PASSWORD = 8;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('response');
        $this->load->model('M_user');
        $this->load->model('M_role');
        $this->load->model('M_audit');
    }

    public function index()
    {
        if (!$this->require_permission('system.users')) return;
        $this->load->view('system/user', [
            'page_title' => 'Manajemen User',
            'active'     => 'user',
            'my_id'      => (int) $this->current_user['id'],
        ]);
    }

    public function listUsers()
    {
        try {
            if (!$this->require_permission_api('system.users')) return;
            give_response('00', 'GET USERS SUKSES', [
                'users' => $this->M_user->all_detailed(),
                'roles' => $this->M_role->all_with_counts(),
            ]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function store()
    {
        try {
            if (!$this->require_permission_api('system.users')) return;

            $name     = trim((string) $this->input->post('name'));
            $email    = strtolower(trim((string) $this->input->post('email')));
            $password = (string) $this->input->post('password');
            $role_id  = (int) $this->input->post('role_id');

            if ($err = $this->validate_identity($name, $email, null)) return give_response('01', $err);
            if (strlen($password) < self::MIN_PASSWORD) {
                return give_response('01', 'Password minimal ' . self::MIN_PASSWORD . ' karakter');
            }
            if ($err = $this->validate_role($role_id)) return give_response('01', $err);

            $id = $this->M_user->create($name, $email, $password, $role_id);
            $this->M_audit->log($this->current_user['id'], 'user.create', $email, 'role_id=' . $role_id);
            give_response('00', 'USER BERHASIL DITAMBAHKAN', ['id' => $id]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function update()
    {
        try {
            if (!$this->require_permission_api('system.users')) return;

            $id      = (int) $this->input->post('id');
            $name    = trim((string) $this->input->post('name'));
            $email   = strtolower(trim((string) $this->input->post('email')));
            $role_id = (int) $this->input->post('role_id');

            $target = $this->M_user->find_with_role($id);
            if (!$target) return give_response('01', 'User tidak ditemukan');
            if ($err = $this->validate_identity($name, $email, $id)) return give_response('01', $err);
            if ($err = $this->validate_role($role_id)) return give_response('01', $err);

            // Jangan sampai sistem tidak punya Super Admin aktif.
            $role = $this->M_role->find($role_id);
            if ((int) $target['role_is_system'] === 1 && (int) $role['is_system'] !== 1
                && (int) $target['is_active'] === 1
                && $this->M_user->count_active_super_admins($id) < 1) {
                return give_response('01', 'Tidak bisa mengubah role: ini satu-satunya Super Admin aktif');
            }

            $this->M_user->update_fields($id, ['name' => $name, 'email' => $email, 'role_id' => $role_id]);
            $this->M_audit->log($this->current_user['id'], 'user.update', $email, 'role_id=' . $role_id);
            give_response('00', 'USER BERHASIL DIPERBARUI');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function toggleActive()
    {
        try {
            if (!$this->require_permission_api('system.users')) return;

            $id     = (int) $this->input->post('id');
            $target = $this->M_user->find_with_role($id);
            if (!$target) return give_response('01', 'User tidak ditemukan');

            if ($id === (int) $this->current_user['id']) {
                return give_response('01', 'Anda tidak bisa menonaktifkan akun sendiri');
            }
            $new_state = (int) $target['is_active'] ? 0 : 1;
            if ($new_state === 0 && (int) $target['role_is_system'] === 1
                && $this->M_user->count_active_super_admins($id) < 1) {
                return give_response('01', 'Tidak bisa menonaktifkan satu-satunya Super Admin aktif');
            }

            $this->M_user->update_fields($id, ['is_active' => $new_state]);
            $this->M_audit->log($this->current_user['id'], $new_state ? 'user.activate' : 'user.deactivate', $target['email']);
            give_response('00', $new_state ? 'USER DIAKTIFKAN' : 'USER DINONAKTIFKAN');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function resetPassword()
    {
        try {
            if (!$this->require_permission_api('system.users')) return;

            $id       = (int) $this->input->post('id');
            $password = (string) $this->input->post('password');

            $target = $this->M_user->find($id);
            if (!$target) return give_response('01', 'User tidak ditemukan');
            if (strlen($password) < self::MIN_PASSWORD) {
                return give_response('01', 'Password minimal ' . self::MIN_PASSWORD . ' karakter');
            }

            $this->M_user->set_password($id, $password);
            $this->M_audit->log($this->current_user['id'], 'user.reset_password', $target['email']);
            give_response('00', 'PASSWORD USER BERHASIL DIRESET');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // ---------------------------------------------------------
    private function validate_identity($name, $email, $except_id)
    {
        if ($name === '' || mb_strlen($name) > 100) return 'Nama wajib diisi (maks. 100 karakter)';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) return 'Format email tidak valid';
        if ($this->M_user->email_exists($email, $except_id)) return 'Email sudah dipakai user lain';
        return null;
    }

    private function validate_role($role_id)
    {
        $role = $this->M_role->find($role_id);
        if (!$role) return 'Role tidak valid';
        if ((int) $role['is_system'] === 1 && !$this->is_super_admin()) return 'Hanya Super Admin yang bisa memberi role Super Admin';
        return null;
    }
}
