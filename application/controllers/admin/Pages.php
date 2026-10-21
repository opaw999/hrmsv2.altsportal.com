<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pages extends Portal_Controller
{
    public $usertype = '';
    public function __construct()
    {
        parent::__construct();
        $this->notLoggedInAdmin();
        $this->load->model('Request_model', 'request');
        $this->load->model('admin/Users_model', 'users');
        $this->usertype = $this->session->userdata('usertype');
    }

    public function index()
    {
        $data['userData'] = $this->sessionData;
        $data['page'] = 'requests';
        $this->load->view('layouts/admin/main', $data);
    }

    public function loginPage()
    {
        $this->load->view('layouts/login');
    }

    public function menu($file = 'requests')
    {

        if (!file_exists(APPPATH . "views/modules/admin/$file.php")) {
            show_404();
        }
        $data = [];
        $empId = $this->session->userdata('empid');
        $cond = ['menu_code' => $file, 'status' => 1];
        $menu = $this->request->getRow('tk_admin_menus', $cond);

        // if (!$menu) {
        // redirect(base_url());
        // return;
        // }

        if (!$menu) {
            $cond = ['submenu_code' => $file, 'status' => 1];
            $menu = $this->request->getRow('tk_admin_submenus', $cond);
            $data['page_title'] = $menu['submenu_name'];
        } else {
            $data['page_title'] = $menu['menu_name'];
        }


        // $cond = ['emp_id' => $empId, 'menu_id' => $menu['id'], 'status' => 1];
        // $roles = $this->request->getRow('tk_admin_menu_access', $cond);
        // if (!$roles) {
        //     redirect(base_url());
        //     return;
        // }




        $data['userData'] = $this->sessionData;
        $data['page'] = $file;

        $this->load->view('partials/admin/_navbar', $data);
        $this->load->view('partials/admin/_sidebar', $data);
        $this->load->view('modules/admin/' . $file, $data);
        $this->load->view('partials/admin/_footer', $data);
    }

    public function access_control()
    {
        $data['page_title'] = 'Access Control';
        $data['page_sub_title'] = '';
        $data['userData'] = $this->sessionData;
        $data['page'] = 'access-control';
        $this->load->view('partials/admin/_navbar', $data);
        $this->load->view('partials/admin/_sidebar', $data);
        $this->load->view('modules/admin/access-control', $data);
        $this->load->view('partials/admin/_footer', $data);
    }

    public function profile()
    {
        $data['page_title'] = 'User Profile';
        $data['page_sub_title'] = '';
        $data['userData'] = $this->sessionData;
        $data['page'] = 'profile';
        $this->load->view('partials/admin/_navbar', $data);
        $this->load->view('partials/admin/_sidebar', $data);
        $this->load->view('modules/admin/profile', $data);
        $this->load->view('partials/admin/_footer', $data);
    }
}
