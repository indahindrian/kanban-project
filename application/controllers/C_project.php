<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * C_project.php
 *
 * Modul Project Management (Project list -> Detail -> Kanban/GANTT/Scoreboard).
 * Semua tabel TANPA prefix (projects, tasks, users, project_members, dst),
 * mengikuti tabel yang sudah ada di database (lihat M_project, M_task, dll).
 */
class C_project extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('response');
        $this->load->helper('url');
        $this->load->model('M_project');
        $this->load->model('M_task');
        $this->load->model('M_label');
        $this->load->model('M_checklist');
        $this->load->model('M_attachment');
        $this->load->model('M_member');
        $this->load->model('M_user');
    }

    // =========================================================
    // VIEWS
    // =========================================================
    public function index()
    {
        if (!$this->require_login()) return;
        $data['is_atasan'] = $this->is_atasan();
        $data['user_name'] = $this->current_user['name'];
        $this->load->view('project/projects_list', $data);
    }

    public function detail($id = null)
    {
        if (!$this->require_login()) return;
        if (!$this->can_access_project($id)) {
            show_error('Anda tidak punya akses ke project ini.', 403, 'Akses Ditolak');
            return;
        }
        // halaman detail sudah digabung ke panel Detail project
        redirect('project/dashboard/' . (int) $id . '#detail');
    }

    public function dashboard($project_id = null)
    {
        if (!$this->require_login()) return;
        if (!$this->can_access_project($project_id)) {
            show_error('Anda tidak punya akses ke project ini.', 403, 'Akses Ditolak');
            return;
        }
        $data['project_id'] = $project_id;
        $data['is_atasan']  = $this->is_atasan();
        $data['user_name']  = $this->current_user['name'];
        $this->load->view('project/dashboard', $data);
    }

    public function ganttGlobalPage()
    {
        if (!$this->require_login()) return;
        $data['is_atasan'] = $this->is_atasan();
        $data['user_name'] = $this->current_user['name'];
        $this->load->view('project/gantt_global', $data);
    }

    public function planningPage()
    {
        if (!$this->require_login()) return;
        $data['is_atasan'] = $this->is_atasan();
        $data['user_name'] = $this->current_user['name'];
        $data['user_id']   = $this->current_user['id'];
        $this->load->view('project/planning', $data);
    }

    // =========================================================
    // API: PROJECTS
    // =========================================================
    public function getProjects()
    {
        try {
            if (!$this->require_login_api()) return;
            $projects = $this->M_project->get_all_for_user($this->current_user['id'], $this->is_atasan());
            give_response('00', 'GET PROJECTS SUKSES', $projects);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function getProjectDetail()
    {
        try {
            if (!$this->require_login_api()) return;
            $id = $this->input->get('id');
            if (!$this->can_access_project($id)) {
                give_response('03', 'Anda tidak punya akses ke project ini');
                return;
            }
            $project = $this->M_project->get_by_id($id);
            if (!$project) {
                give_response('01', 'Project tidak ditemukan');
                return;
            }
            give_response('00', 'GET PROJECT DETAIL SUKSES', $project);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function updateProjectDetail()
    {
        try {
            if (!$this->require_login_api()) return;
            $id = $this->input->post('id');
            if (!$this->can_access_project($id)) {
                give_response('03', 'Anda tidak punya akses ke project ini');
                return;
            }
            if (!$this->is_atasan()) {
                give_response('03', 'Hanya atasan yang bisa mengubah detail project');
                return;
            }
            $this->M_project->update($id, [
                'name'                    => $this->input->post('name'),
                'description'             => $this->input->post('description'),
                'status'                  => $this->input->post('status'),
                'tanggal_rencana_mulai'   => $this->input->post('tanggal_rencana_mulai') ?: null,
                'tanggal_rencana_selesai' => $this->input->post('tanggal_rencana_selesai') ?: null,
                'tanggal_aktual_mulai'    => $this->input->post('tanggal_aktual_mulai') ?: null,
                'tanggal_aktual_selesai'  => $this->input->post('tanggal_aktual_selesai') ?: null,
            ]);
            give_response('00', 'UPDATE PROJECT SUKSES', $this->M_project->get_by_id($id));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function createProject()
    {
        try {
            if (!$this->require_login_api()) return;

            if (!$this->is_atasan()) {
                give_response('03', 'Hanya atasan yang bisa membuat project baru');
                return;
            }

            $name = trim((string) $this->input->post('name'));
            if ($name === '') {
                give_response('01', 'Nama project wajib diisi');
                return;
            }

            $this->db->insert('projects', [
                'name'                    => $name,
                'description'             => trim((string) $this->input->post('description')),
                'status'                  => 'active',
                'owner_id'                => $this->current_user['id'],
                'tanggal_rencana_mulai'   => $this->input->post('tanggal_rencana_mulai') ?: null,
                'tanggal_rencana_selesai' => $this->input->post('tanggal_rencana_selesai') ?: null,
            ]);
            $project_id = $this->db->insert_id();

            $this->db->insert('project_members', [
                'project_id' => $project_id,
                'user_id'    => $this->current_user['id'],
                'role'       => 'manager',
            ]);

            give_response('00', 'PROJECT DIBUAT', ['id' => $project_id]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: USERS (untuk member picker & PIC)
    // =========================================================
    public function getUsers()
    {
        try {
            if (!$this->require_login_api()) return;
            give_response('00', 'GET USERS SUKSES', $this->M_user->all());
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: KANBAN BOARD
    // =========================================================
    public function getKanban()
    {
        try {
            if (!$this->require_login_api()) return;
            $project_id = $this->input->get('project_id');
            if (!$this->can_access_project($project_id)) {
                give_response('03', 'Anda tidak punya akses ke project ini');
                return;
            }

            $project = $project_id ? $this->M_project->get_by_id($project_id) : null;
            $tasks   = $project_id ? $this->M_task->get_by_project($project_id) : [];

            $columns = [
                'no_status'  => ['status_key' => 'no_status',  'label' => 'No status',  'tasks' => []],
                'processing' => ['status_key' => 'processing', 'label' => 'Processing', 'tasks' => []],
                'closed'     => ['status_key' => 'closed',     'label' => 'Closed',     'tasks' => []],
            ];
            foreach ($tasks as $t) {
                $columns[$t['status']]['tasks'][] = $t;
            }

            $data = [
                'project_id'   => $project_id,
                'project_name' => $project ? $project['name'] : null,
                'all_labels'   => $project_id ? $this->M_label->get_by_project($project_id) : [],
                'all_users'    => $this->M_user->all(),
                'columns'      => array_values($columns),
            ];

            give_response('00', 'GET KANBAN SUKSES', $data);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function createTask()
    {
        try {
            if (!$this->require_login_api()) return;
            $project_id = $this->input->post('project_id');
            $title      = trim((string) $this->input->post('title'));
            if (!$this->can_access_project($project_id)) {
                give_response('03', 'Anda tidak punya akses ke project ini');
                return;
            }
            if (!$project_id || $title === '') {
                give_response('01', 'project_id dan title wajib diisi');
                return;
            }
            $task_id = $this->M_task->create($project_id, $title);
            $this->M_task->log_activity($task_id, $this->current_user['id'], 'task_created');
            give_response('00', 'TASK DIBUAT', $this->M_task->get_detail($task_id));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function updateTaskStatus()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id    = $this->input->post('task_id');
            $new_status = $this->input->post('new_status');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }

            $this->M_task->update_status($task_id, $new_status);

            if ($new_status === 'closed') {
                $this->M_task->update_fields($task_id, ['progress' => 100]);
                $this->M_task->log_activity($task_id, $this->current_user['id'], 'task_closed', 10);
            }

            give_response('00', 'UPDATE STATUS SUKSES', ['task_id' => $task_id, 'new_status' => $new_status]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function toggleTaskComplete()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            $task = $this->M_task->get_detail($task_id);
            if (!$task) {
                give_response('01', 'Task tidak ditemukan');
                return;
            }
            if (!$this->can_access_project($task['project_id'])) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }

            $is_done = $task['status'] === 'closed';
            $new_status = $is_done ? 'processing' : 'closed';
            $this->M_task->update_status($task_id, $new_status);
            $this->M_task->update_fields($task_id, ['progress' => $is_done ? 0 : 100]);

            if (!$is_done) {
                $this->M_task->log_activity($task_id, $this->current_user['id'], 'task_closed', 10);
            }

            give_response('00', 'TOGGLE COMPLETE SUKSES', $this->M_task->get_detail($task_id));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: TASK DETAIL (modal card)
    // =========================================================
    public function getTaskDetail()
    {
        try {
            if (!$this->require_login_api()) return;
            $id = $this->input->get('id');
            $task = $this->M_task->get_detail($id);
            if (!$task) {
                give_response('01', 'Task tidak ditemukan');
                return;
            }
            if (!$this->can_access_project($task['project_id'])) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            give_response('00', 'GET TASK DETAIL SUKSES', $task);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function updateTaskDetail()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            $fields = array_filter([
                'title'       => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'due_date'    => $this->input->post('due_date') ?: null,
            ], function ($v) { return $v !== null; });

            $this->M_task->update_fields($task_id, $fields);
            give_response('00', 'TASK DETAIL DISIMPAN', $this->M_task->get_detail($task_id));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function deleteTask()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            $this->db->where('id', $task_id)->delete('tasks');
            give_response('00', 'TASK DIHAPUS');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: LABELS
    // =========================================================
    public function getLabels()
    {
        try {
            if (!$this->require_login_api()) return;
            $project_id = $this->input->get('project_id');
            if (!$this->can_access_project($project_id)) {
                give_response('03', 'Anda tidak punya akses ke project ini');
                return;
            }
            give_response('00', 'GET LABELS SUKSES', $this->M_label->get_by_project($project_id));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function createLabel()
    {
        try {
            if (!$this->require_login_api()) return;
            $project_id = $this->input->post('project_id');
            $name  = trim((string) $this->input->post('name'));
            $color = $this->input->post('color');
            if (!$this->can_access_project($project_id)) {
                give_response('03', 'Anda tidak punya akses ke project ini');
                return;
            }
            if (!$project_id || $name === '') {
                give_response('01', 'project_id dan name wajib diisi');
                return;
            }
            $id = $this->M_label->create($project_id, $name, $color);
            give_response('00', 'LABEL DIBUAT', ['id' => $id, 'name' => $name, 'color' => $color]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function attachLabel()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            $this->M_label->attach($task_id, $this->input->post('label_id'));
            give_response('00', 'LABEL DITAMBAHKAN KE TASK');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function detachLabel()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            $this->M_label->detach($task_id, $this->input->post('label_id'));
            give_response('00', 'LABEL DIHAPUS DARI TASK');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: MEMBERS (per task)
    // =========================================================
    public function addTaskMember()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            $this->M_member->add($task_id, $this->input->post('user_id'));
            give_response('00', 'MEMBER DITAMBAHKAN');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function removeTaskMember()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            $this->M_member->remove($task_id, $this->input->post('user_id'));
            give_response('00', 'MEMBER DIHAPUS');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: CHECKLIST
    // =========================================================
    public function addChecklist()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            $title = $this->input->post('title') ?: 'Checklist';
            $id = $this->M_checklist->create($task_id, $title);
            give_response('00', 'CHECKLIST DIBUAT', ['id' => $id, 'title' => $title, 'items' => []]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function deleteChecklist()
    {
        try {
            if (!$this->require_login_api()) return;
            $checklist_id = $this->input->post('checklist_id');
            $checklist = $this->M_checklist->find($checklist_id);
            if (!$checklist || !$this->can_access_project($this->M_task->get_project_id($checklist['task_id']))) {
                give_response('03', 'Anda tidak punya akses ke checklist ini');
                return;
            }
            $this->M_checklist->delete($checklist_id);
            give_response('00', 'CHECKLIST DIHAPUS');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function addChecklistItem()
    {
        try {
            if (!$this->require_login_api()) return;
            $checklist_id = $this->input->post('checklist_id');
            $checklist = $this->M_checklist->find($checklist_id);
            if (!$checklist || !$this->can_access_project($this->M_task->get_project_id($checklist['task_id']))) {
                give_response('03', 'Anda tidak punya akses ke checklist ini');
                return;
            }
            $text = trim((string) $this->input->post('text'));
            if ($text === '') {
                give_response('01', 'text wajib diisi');
                return;
            }
            $id = $this->M_checklist->add_item($checklist_id, $text);
            give_response('00', 'ITEM DITAMBAHKAN', ['id' => $id, 'text' => $text, 'is_done' => 0]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function toggleChecklistItem()
    {
        try {
            if (!$this->require_login_api()) return;
            $item_id = $this->input->post('item_id');
            $item = $this->M_checklist->find_item($item_id);
            $checklist = $item ? $this->M_checklist->find($item['checklist_id']) : null;
            if (!$checklist || !$this->can_access_project($this->M_task->get_project_id($checklist['task_id']))) {
                give_response('03', 'Anda tidak punya akses ke item ini');
                return;
            }
            $this->M_checklist->toggle_item($item_id);
            give_response('00', 'ITEM DIUPDATE', $this->M_checklist->find_item($item_id));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function deleteChecklistItem()
    {
        try {
            if (!$this->require_login_api()) return;
            $item_id = $this->input->post('item_id');
            $item = $this->M_checklist->find_item($item_id);
            $checklist = $item ? $this->M_checklist->find($item['checklist_id']) : null;
            if (!$checklist || !$this->can_access_project($this->M_task->get_project_id($checklist['task_id']))) {
                give_response('03', 'Anda tidak punya akses ke item ini');
                return;
            }
            $this->M_checklist->delete_item($item_id);
            give_response('00', 'ITEM DIHAPUS');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: ATTACHMENTS (link & file upload)
    // =========================================================
    public function addAttachmentLink()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }
            $name = trim((string) $this->input->post('name'));
            $url  = trim((string) $this->input->post('url'));
            if ($url === '') {
                give_response('01', 'url wajib diisi');
                return;
            }
            $id = $this->M_attachment->add($task_id, 'link', $name !== '' ? $name : $url, $url, $this->current_user['id']);
            give_response('00', 'LINK DITAMBAHKAN', $this->M_attachment->find($id));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function uploadAttachmentFile()
    {
        try {
            if (!$this->require_login_api()) return;
            $task_id = $this->input->post('task_id');
            if (!$task_id) {
                give_response('01', 'task_id wajib diisi');
                return;
            }
            if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
                give_response('03', 'Anda tidak punya akses ke task ini');
                return;
            }

            $upload_dir = FCPATH . 'uploads/attachments/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $config = [
                'upload_path'   => $upload_dir,
                'allowed_types' => 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|ppt|pptx|txt|zip|rar',
                'max_size'      => 10240,
                'encrypt_name'  => true,
            ];
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('file')) {
                give_response('01', $this->upload->display_errors('', ''));
                return;
            }

            $upload_data = $this->upload->data();
            $public_path = 'uploads/attachments/' . $upload_data['file_name'];

            $id = $this->M_attachment->add($task_id, 'file', $upload_data['client_name'], base_url($public_path), $this->current_user['id']);
            give_response('00', 'FILE DIUPLOAD', $this->M_attachment->find($id));
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    public function deleteAttachment()
    {
        try {
            if (!$this->require_login_api()) return;
            $id = $this->input->post('id');
            $attachment = $this->M_attachment->find($id);
            if (!$attachment || !$this->can_access_project($this->M_task->get_project_id($attachment['task_id']))) {
                give_response('03', 'Anda tidak punya akses ke attachment ini');
                return;
            }
            if ($attachment['type'] === 'file' && $attachment['url']) {
                $path = FCPATH . 'uploads/attachments/' . basename($attachment['url']);
                if (is_file($path)) {
                    unlink($path);
                }
            }
            $this->M_attachment->delete($id);
            give_response('00', 'ATTACHMENT DIHAPUS');
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: GANTT (per project)
    // =========================================================
    public function getGantt()
    {
        try {
            if (!$this->require_login_api()) return;
            $project_id = $this->input->get('project_id');
            if (!$this->can_access_project($project_id)) {
                give_response('03', 'Anda tidak punya akses ke project ini');
                return;
            }
            $tasks = $project_id ? $this->M_task->get_by_project($project_id) : [];

            $data = array_map(function ($t) {
                $members = array_column($t['members'], 'name');
                return [
                    'id'       => 'task-' . $t['id'],
                    'name'     => $t['title'],
                    'start'    => $t['start_date'] ?: $t['created_at'],
                    'end'      => $t['due_date'] ?: date('Y-m-d', strtotime('+3 days', strtotime($t['created_at']))),
                    'progress' => (int) $t['progress'],
                    'assignee' => $members ? implode(', ', $members) : '-',
                ];
            }, $tasks);

            give_response('00', 'GET GANTT SUKSES', $data);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: GANTT GLOBAL (lintas semua project yang bisa diakses user)
    // =========================================================
    public function getGanttGlobal()
    {
        try {
            if (!$this->require_login_api()) return;

            $projects = $this->M_project->get_all_for_user($this->current_user['id'], $this->is_atasan());
            if (empty($projects)) {
                give_response('00', 'GET GANTT GLOBAL SUKSES', []);
                return;
            }

            $project_ids   = array_column($projects, 'id');
            $project_names = array_column($projects, 'name', 'id');

            $tasks = $this->db->select('id, project_id, title, start_date, due_date, progress')
                ->from('tasks')
                ->where_in('project_id', $project_ids)
                ->get()->result_array();

            $data = array_map(function ($t) use ($project_names) {
                return [
                    'id'           => 'task-' . $t['id'],
                    'name'         => $t['title'] . ' (' . $project_names[$t['project_id']] . ')',
                    'start'        => $t['start_date'] ?: date('Y-m-d'),
                    'end'          => $t['due_date'] ?: date('Y-m-d', strtotime('+3 days')),
                    'progress'     => (int) $t['progress'],
                    'project_id'   => $t['project_id'],
                    'project_name' => $project_names[$t['project_id']],
                ];
            }, $tasks);

            give_response('00', 'GET GANTT GLOBAL SUKSES', $data);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: SCOREBOARD (per project)
    // =========================================================
    public function getScoreboard()
    {
        try {
            if (!$this->require_login_api()) return;
            $project_id = $this->input->get('project_id');
            if (!$this->can_access_project($project_id)) {
                give_response('03', 'Anda tidak punya akses ke project ini');
                return;
            }

            $this->db->select('u.id, u.name AS user, COALESCE(SUM(ta.points), 0) AS total_points,
                    COUNT(DISTINCT CASE WHEN ta.action = "task_closed" THEN ta.task_id END) AS tasks_done,
                    COUNT(DISTINCT CASE WHEN ta.action = "task_closed_ontime" THEN ta.task_id END) AS tasks_ontime')
                ->from('task_activities ta')
                ->join('users u', 'u.id = ta.user_id')
                ->join('tasks t', 't.id = ta.task_id')
                ->where('t.project_id', $project_id);

            $rows = $this->db->group_by('u.id, u.name')
                ->order_by('total_points', 'DESC')
                ->get()->result_array();

            $data = [];
            $rank = 1;
            foreach ($rows as $r) {
                $data[] = [
                    'rank'         => $rank++,
                    'user'         => $r['user'],
                    'total_points' => (int) $r['total_points'],
                    'tasks_done'   => (int) $r['tasks_done'],
                    'tasks_ontime' => (int) $r['tasks_ontime'],
                ];
            }

            give_response('00', 'GET SCOREBOARD SUKSES', $data);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: PLANNING (dipanggil oleh FullCalendar di planning.php)
    // =========================================================
    public function getPlanning()
    {
        try {
            if (!$this->require_login_api()) return;

            $start = $this->input->get('start') ?: date('Y-m-01');
            $end   = $this->input->get('end') ?: date('Y-m-d', strtotime('first day of next month'));

            $projects = $this->M_project->get_all_for_user($this->current_user['id'], $this->is_atasan());
            $project_ids = array_column($projects, 'id');

            if (empty($project_ids)) {
                give_response('00', 'GET PLANNING SUKSES', []);
                return;
            }

            $query = $this->db->select('t.id, t.title, t.due_date, t.status, t.project_id, p.name as project_name, u.id as user_id, u.name as user_name')
                ->from('tasks t')
                ->join('projects p', 'p.id = t.project_id')
                ->join('task_members tm', 'tm.task_id = t.id')
                ->join('users u', 'u.id = tm.user_id')
                ->where_in('t.project_id', $project_ids)
                ->where('t.due_date >=', $start)
                ->where('t.due_date <', $end);

            if (!$this->is_atasan()) {
                $query->where('u.id', $this->current_user['id']);
            }

            $rows  = $query->get()->result_array();
            $today = date('Y-m-d');

            $task_ids = array_unique(array_column($rows, 'id'));
            $labelsByTask = [];
            if (!empty($task_ids)) {
                $labelRows = $this->db->select('tl.task_id, l.name, l.color')
                    ->from('task_labels tl')
                    ->join('labels l', 'l.id = tl.label_id')
                    ->where_in('tl.task_id', $task_ids)
                    ->get()->result_array();
                foreach ($labelRows as $lr) {
                    if (!isset($labelsByTask[$lr['task_id']])) {
                        $labelsByTask[$lr['task_id']] = ['name' => $lr['name'], 'color' => $lr['color']];
                    }
                }
            }

            $palette = ['#0052CC', '#00875A', '#6554C0', '#FF8B00', '#00A3BF', '#8777D9', '#36B37E', '#974F0C'];

            $events = array_map(function ($r) use ($palette, $today, $labelsByTask) {
                $overdue = ($r['due_date'] < $today && $r['status'] !== 'closed');
                $label   = $labelsByTask[$r['id']] ?? null;

                if ($overdue) {
                    $color = '#DE350B';
                } elseif ($label) {
                    $color = $label['color'];
                } else {
                    $color = $palette[$r['user_id'] % count($palette)];
                }

                return [
                    'id'              => 'task-' . $r['id'],
                    'title'           => $r['user_name'] . ': ' . $r['title'],
                    'start'           => $r['due_date'],
                    'allDay'          => true,
                    'backgroundColor' => $color,
                    'borderColor'     => $color,
                    'extendedProps'   => [
                        'task_id'     => $r['id'],
                        'project_id'  => $r['project_id'],
                        'project'     => $r['project_name'],
                        'status'      => $r['status'],
                        'overdue'     => $overdue,
                        'user'        => $r['user_name'],
                        'user_id'     => $r['user_id'],
                        'label_name'  => $label ? $label['name'] : null,
                        'label_color' => $label ? $label['color'] : null,
                    ],
                ];
            }, $rows);

            give_response('00', 'GET PLANNING SUKSES', $events);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }

    // =========================================================
    // API: DASHBOARD SUMMARY (statistik + widget "Tugas saya")
    // =========================================================
    public function getDashboardSummary()
    {
        try {
            if (!$this->require_login_api()) return;

            $projects   = $this->M_project->get_all_for_user($this->current_user['id'], $this->is_atasan());
            $project_ids = array_column($projects, 'id');
            $today = date('Y-m-d');

            if (empty($project_ids)) {
                give_response('00', 'GET DASHBOARD SUMMARY SUKSES', [
                    'total_projects'        => 0,
                    'tasks_overdue_total'   => 0,
                    'tasks_done_this_month' => 0,
                    'my_tasks'              => [],
                    'overdue_by_project'    => new stdClass(),
                ]);
                return;
            }

            $overdueRows = $this->db->select('id, project_id, due_date')
                ->from('tasks')
                ->where_in('project_id', $project_ids)
                ->where('due_date <', $today)
                ->where('status !=', 'closed')
                ->get()->result_array();

            $overdue_by_project = [];
            foreach ($overdueRows as $r) {
                $pid = $r['project_id'];
                $overdue_by_project[$pid] = ($overdue_by_project[$pid] ?? 0) + 1;
            }

            $monthStart = date('Y-m-01');
            $doneThisMonth = $this->db->where_in('project_id', $project_ids)
                ->where('status', 'closed')
                ->where('updated_at >=', $monthStart)
                ->count_all_results('tasks');

            $myTasks = $this->db->select('t.id, t.title, t.due_date, t.status, p.id as project_id, p.name as project_name')
                ->from('tasks t')
                ->join('projects p', 'p.id = t.project_id')
                ->join('task_members tm', 'tm.task_id = t.id')
                ->where('tm.user_id', $this->current_user['id'])
                ->where_in('t.project_id', $project_ids)
                ->where('t.status !=', 'closed')
                ->order_by('t.due_date', 'ASC')
                ->limit(8)
                ->get()->result_array();

            $myTasksFormatted = array_map(function ($t) use ($today) {
                return [
                    'id'           => $t['id'],
                    'title'        => $t['title'],
                    'due_date'     => $t['due_date'],
                    'project_id'   => $t['project_id'],
                    'project_name' => $t['project_name'],
                    'overdue'      => ($t['due_date'] && $t['due_date'] < $today),
                ];
            }, $myTasks);

            give_response('00', 'GET DASHBOARD SUMMARY SUKSES', [
                'total_projects'        => count($projects),
                'tasks_overdue_total'   => count($overdueRows),
                'tasks_done_this_month' => (int) $doneThisMonth,
                'my_tasks'              => $myTasksFormatted,
                'overdue_by_project'    => $overdue_by_project,
            ]);
        } catch (Exception $exception) {
            give_response('01', $exception->getMessage());
        }
    }
}
