<?php

class Auth extends Portal_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->notLoggedIn();
        $this->load->library('user_agent');
        $this->load->model('Users_model', 'users');
        $this->load->model('Employee_model', 'employee');
        $this->load->model('Applicant_model', 'applicant');
        $this->load->model('Company_model', 'company');
        $this->load->model('Bunit_model', 'bunit');
        $this->load->model('Department_model', 'department');
        $this->load->model('Masterlist_model', 'masterlist');
        $this->load->model('Userlog_model', 'ulogs');
    }

    public function login()
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

            // Fetch employee details
            $employee = $this->employee->getEmpDetailsByID($user->emp_id);
            // Fetch applicant details
            $applicants = $this->applicant->getAppDetails($user->emp_id);
            // Calculate age
            $applicantBirthDate = $applicants->birthdate;
            $employeeAge = $this->calculateAge($applicantBirthDate);
            // Get company
            $company = $this->company->getCompanyDetailsByID($employee->company_code);
            // Get business unit
            $bunit = $this->bunit->getBunitDetailsByID($employee->company_code, $employee->bunit_code);
            // Get department
            $department = $this->department->getDeptDetailsByID($employee->company_code, $employee->bunit_code, $employee->dept_code);

            $masterlistDetails = $this->masterlist->getMasterListDetails($user->emp_id);

            $userAccess = $masterlistDetails ? 'time-log' : 'dashboard2';

            $this->session->set_userdata([
                'empid' => $user->emp_id,
                'username' => $user->username,
                'name' => ucwords(strtolower($employee->name)),
                'position' => $employee->position,
                'usertype' => $user->usertype,
                'userphoto' => $applicants->photo,
                'userage' => $employeeAge,
                'company' => $company->company,
                'bunit' => $bunit->business_unit,
                'department' => @$department->dept_name,
                'isLiveUser' => TRUE
            ]);

            if ($this->agent->is_mobile()) {
                $device = 'Mobile';
                $platform = $this->agent->mobile(); // e.g. "Apple iPhone", "Android"
            } else {
                $device = 'Desktop / Laptop';
                $platform = $this->agent->platform(); // e.g. "Windows 10", "Mac OS X"
            }

            $logData = [
                'emp_id' => $user->emp_id,
                'user_agent' => $device . ' ' . $platform,
                'ip_address' => $this->input->ip_address(),
                'location' => ''
            ];

            $this->ulogs->create($logData);

            return $this->jsonResponse(true, AUTH_SUCCESS, [
                'routeTo' => $userAccess
            ]);
        } else {
            return $this->jsonResponse(false, AUTH_FAILED);
        }
    }

    public function calculateAge($birthDate)
    {
        $birthDate = new DateTime($birthDate);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthDate)->y;
        return $age;
    }

    public function logout()
    {
        if ($this->agent->is_mobile()) {
            $device = 'Mobile';
            $platform = $this->agent->mobile(); // e.g. "Apple iPhone", "Android"
        } else {
            $device = 'Desktop / Laptop';
            $platform = $this->agent->platform(); // e.g. "Windows 10", "Mac OS X"
        }

        $logData = [
            'emp_id' => $this->session->userdata('empid'),
            'user_agent' => $device . ' ' . $platform,
            'ip_address' => $this->input->ip_address(),
            'location' => '',
            'log_type' => 'logout'
        ];

        $this->ulogs->create($logData);

        $this->session->sess_destroy();
        redirect('login', 'refresh');
    }
}
