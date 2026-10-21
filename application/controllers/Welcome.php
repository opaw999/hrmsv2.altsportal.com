<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends Portal_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('array');
        $this->loggedIn();
    }

    public function index()
    {
        $this->load->view('layouts/login');
    }
}
