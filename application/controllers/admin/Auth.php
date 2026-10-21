<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends Portal_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('admin/Users_model', 'users');
        $this->load->model('Employee_model', 'employee');
    }

    public function index()
    {
        $this->adminLoggedIn();
        $this->load->view('layouts/admin/login');
    }

    public function auth_login()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('No direct script access allowed', 403);
        }

        // Set validation rules
        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            // Validation failed
            return $this->jsonResponse(false, validation_errors('<div>', '</div>'));
        }

        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password', TRUE);

        $user = $this->users->checkLogin($username, $password);

        if ($user) {

            $employee = $this->employee->getEmpDetailsByID($user->emp_id);
            $this->session->set_userdata([
                'empid' => $user->emp_id,
                'username' => $user->username,
                'name' => ucwords(strtolower($employee->name)),
                'usertype' => $user->type,
                'isLiveAdmin' => TRUE
            ]);
            return $this->jsonResponse(true, AUTH_SUCCESS);
        } else {
            return $this->jsonResponse(false, AUTH_FAILED);
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('admin/login', 'refresh');
    }
}
