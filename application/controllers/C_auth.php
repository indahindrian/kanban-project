<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('response');
        $this->load->model('M_user');
    }

    public function index()
    {
        if ($this->is_logged_in()) {
            redirect('project');
            return;
        }
        $this->load->view('auth/login');
    }

    public function login()
    {
        try {
            $email    = trim((string) $this->input->post('email'));
            $password = (string) $this->input->post('password');

            $user = $this->M_user->find_by_email($email);
            if (!$user || !password_verify($password, $user['password'])) {
                give_response('01', 'Email atau password salah');
                return;
            }
            if (!(int) $user['is_active']) {
                give_response('01', 'Akun Anda dinonaktifkan. Hubungi administrator.');
                return;
            }

            $this->session->set_userdata([
                'user_id'    => $user['id'],
                'user_name'  => $user['name'],
                'user_email' => $user['email'],
            ]);

            $this->load->model('M_audit');
            $this->M_audit->log($user['id'], 'login', $user['email']);

            give_response('00', 'LOGIN SUKSES');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth');
    }
}
