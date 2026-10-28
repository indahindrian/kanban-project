<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Audit Log System - butuh permission 'system.audit'. */
class C_audit extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('response');
        $this->load->model('M_audit');
    }

    public function index()
    {
        if (!$this->require_permission('system.audit')) return;
        $this->load->view('system/audit', ['page_title' => 'Audit Log System', 'active' => 'audit']);
    }

    public function listLogs()
    {
        try {
            if (!$this->require_permission_api('system.audit')) return;
            give_response('00', 'GET AUDIT LOG SUKSES', $this->M_audit->recent(200));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }
}
