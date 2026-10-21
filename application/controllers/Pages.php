<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pages extends Portal_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->notLoggedIn();

        $this->load->model('Deduction_model', 'deduction');
        $this->load->model('Tk_attendance_model');
    }

    public function index()
    {
        $data['userData'] = $this->sessionData;
        $this->load->view('layouts/main', $data);
    }

    public function dashboard()
    {
        $empid = $this->session->userdata('empid');

        $data['page_title'] = 'Calendar';
        $data['page_sub_title'] = '';
        $data['userData'] = $this->sessionData;

        // Get the registered location
        $locationData = $this->Tk_attendance_model
                            ->get_registered_location($empid);

        $data['location'] = $locationData['location'] ? $locationData['location'] : '';

        $this->load->view('modules/dashboard/index', $data);
    }

    public function dashboard2()
    {
        $data['page_title'] = 'Dashboard';
        $data['page_sub_title'] = ''; //'Display calendar, time and quotes';
        $data['userData'] = $this->sessionData;
        $this->load->view('modules/dashboard/index2', $data);
    }

    public function myprofile()
    {
        $data['userData'] = $this->sessionData;
        $this->load->view('modules/profile/index', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Payroll Pages
    |--------------------------------------------------------------------------
    */
    public function setup_hourly_rate()
    {
        $data['page_title'] = 'Setup Hourly Rate';
        $data['userData'] = $this->sessionData;
        $data['page_sub_title'] = ''; //'Setup your hourly rate for payroll';
        $this->load->view('modules/payroll/setup_hourly_rate', $data);
    }

    public function export_deduction()
    {
        $data['page_title'] = 'Export Deduction';
        $data['userData'] = $this->sessionData;
        $data['dateCutOff'] = $this->deduction->getCutOffYear();
        $data['page_sub_title'] = ''; //'Export your deduction for payroll';
        $this->load->view('modules/payroll/export_deduction', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | 2nd Gen Pages
    |--------------------------------------------------------------------------
    */
    public function timelog()
    {
        $data['page_title'] = 'Time Log';
        $data['page_sub_title'] = ''; //'Entry of your Time in and out';
        $data['userData'] = $this->sessionData;
        $this->load->view('modules/second_gen/time_log', $data);
    }

    public function formrequest()
    {
        $this->load->model('Request_model', 'request_model');
        $data['page_title'] = 'Form Request';
        $data['userData'] = $this->sessionData;
        $data['page_sub_title'] = ''; //'File your VL, SL or Business Trip etc...';
        $this->load->view('modules/second_gen/form_request', $data);
    }

    public function vlledger()
    {
        $this->load->model('Request_model', 'request_model');
        $empid = $this->session->userdata('empid');
        $this->generate_vl_year($empid, date('Y'));
        $data['page_title'] = 'Vacation Leave Ledger';
        $data['userData'] = $this->sessionData;
        $data['page_sub_title'] = ''; //'View you Vacation Leave';
        $this->load->view('modules/second_gen/vl_ledger', $data);
    }

    public function generate_vl_year($empid, $year)
    {
        $cond = ['emp_id' => $empid, 'year' => $year];
        $vl = $this->request_model->getRow('tk_vacation_leave_balance', $cond);
        if (!$vl) {

            $cond = ['payment' => 'paid', 'status' => '1'];
            $paid = $this->request_model->getRow('tk_vl_defaults', $cond)['total_days'];
            $cond = ['payment' => 'unpaid', 'status' => '1'];
            $unpaid = $this->request_model->getRow('tk_vl_defaults', $cond)['total_days'];
            $total = $paid + $unpaid;

            $data = [
                'emp_id'                => $empid,
                'year'                  => $year,
                'totalAllocated'        => $total,
                'paidDaysAllocated'     => $paid,
                'unpaidDaysAllocated'   => $unpaid,
                'paidDaysUsed'          => 0,
                'unpaidDaysUsed'        => 0,
                'paidDaysRemaining'     => $paid,
                'unpaidDaysRemaining'   => $unpaid,
                'totalDaysRemaining'    => $total,
                'lastUpdated'           => date('Y-m-d H:i:s')
            ];
            $vl = $this->request_model->insert('tk_vacation_leave_balance', $data);
        }
    }

    public function viewlogs()
    {
        $this->load->model('Request_model', 'request_model');
        $data['page_title'] = 'View Log';
        $data['userData'] = $this->sessionData;
        $data['page_sub_title'] = ''; //'View your entry per week or monthly';
        $this->load->view('modules/second_gen/view_logs', $data);
    }

    public function request_calendar($request_type)
    {
        $this->load->model('Request_model', 'request_model');
        $data['request_type'] = $request_type;
        $data['userData'] = $this->sessionData;
        $data['page_title'] = 'Form Request';
        $data['page_sub_title'] = ''; //'View your request calendar';
        $this->load->view('modules/second_gen/request_calendar', $data);
    }

    public function request_daily()
    {
        $data['page_title'] = 'Request Calendar';
        $data['userData'] = $this->sessionData;
        $data['page_sub_title'] = ''; //'View your request calendar';
        $this->load->view('modules/second_gen/request_daily', $data);
    }
}
