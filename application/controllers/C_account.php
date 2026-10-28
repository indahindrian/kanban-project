<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Akun saya: ubah profil dan password sendiri (semua user yang login). */
class C_account extends MY_Controller
{
    const MIN_PASSWORD = 8;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('response');
        $this->load->model('M_user');
        $this->load->model('M_audit');
    }

    public function index()
    {
        if (!$this->require_login()) return;
        $this->load->view('account/kelola_akun', [
            'page_title' => 'Akun Saya',
            'active'     => 'account',
            'show_tabs'  => false,
        ]);
    }

    public function getMe()
    {
        try {
            if (!$this->require_login_api()) return;
            $user = $this->M_user->find_with_role($this->current_user['id']);
            give_response('00', 'GET PROFIL SUKSES', [
                'id'        => $user['id'],
                'name'      => $user['name'],
                'email'     => $user['email'],
                'role_name' => $user['role_name'],
            ]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function updateProfile()
    {
        try {
            if (!$this->require_login_api()) return;

            $id    = (int) $this->current_user['id'];
            $name  = trim((string) $this->input->post('name'));
            $email = strtolower(trim((string) $this->input->post('email')));

            if ($name === '' || mb_strlen($name) > 100) return give_response('01', 'Nama wajib diisi (maks. 100 karakter)');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) return give_response('01', 'Format email tidak valid');
            if ($this->M_user->email_exists($email, $id)) return give_response('01', 'Email sudah dipakai user lain');

            $this->M_user->update_fields($id, ['name' => $name, 'email' => $email]);
            $this->session->set_userdata(['user_name' => $name, 'user_email' => $email]);
            give_response('00', 'PROFIL BERHASIL DIPERBARUI', ['name' => $name, 'email' => $email]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function changePassword()
    {
        try {
            if (!$this->require_login_api()) return;

            $id      = (int) $this->current_user['id'];
            $old     = (string) $this->input->post('old_password');
            $new     = (string) $this->input->post('new_password');
            $confirm = (string) $this->input->post('confirm_password');

            $user = $this->M_user->find($id);
            if (!$user || !password_verify($old, $user['password'])) return give_response('01', 'Password lama salah');
            if (strlen($new) < self::MIN_PASSWORD) return give_response('01', 'Password minimal ' . self::MIN_PASSWORD . ' karakter');
            if ($new !== $confirm) return give_response('01', 'Konfirmasi password tidak sama');

            $this->M_user->set_password($id, $new);
            $this->M_audit->log($id, 'password.change', $user['email']);
            give_response('00', 'PASSWORD BERHASIL DIUBAH');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }
}
