<?php
date_default_timezone_set('Asia/Manila');

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('array');
    }
}

class Portal_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('encryption');

        // Set delimiters globally so you don't get <p> tags in your JSON
        $this->form_validation->set_error_delimiters('', '<br>');

        $this->sessionData = $this->session->userdata();
    }

    public function loggedIn()
    {
        if (isset($this->sessionData['isLiveUser']) && $this->sessionData['isLiveUser'] === TRUE) {
            redirect('#/time-log', 'refresh');
        }

        if (isset($this->sessionData['isLiveAdmin']) && $this->sessionData['isLiveAdmin'] === TRUE) {
            redirect('admin', 'refresh');
        }
    }

    public function adminLoggedIn()
    {
        if (isset($this->sessionData['isLiveAdmin']) && $this->sessionData['isLiveAdmin'] === TRUE) {
            redirect('admin', 'refresh');
        }

        if (isset($this->sessionData['isLiveUser']) && $this->sessionData['isLiveUser'] === TRUE) {
            redirect('#/time-log', 'refresh');
        }
    }

    public function notLoggedIn()
    {
        if (!isset($this->sessionData['isLiveUser']) || $this->sessionData['isLiveUser'] !== TRUE) {
            $this->session->unset_userdata('isLiveUser');

            if (isset($this->sessionData['isLiveAdmin']) || $this->sessionData['isLiveAdmin'] === TRUE) {
                redirect('admin', 'refresh');
            } else {
                redirect('login', 'refresh');
            }
        }
    }

    public function notLoggedInAdmin()
    {
        if (!isset($this->sessionData['isLiveAdmin']) || $this->sessionData['isLiveAdmin'] !== TRUE) {
            $this->session->unset_userdata('isLiveAdmin');

            if (isset($this->sessionData['isLiveUser']) || $this->sessionData['isLiveUser'] === TRUE) {
                redirect('#', 'refresh');
            } else {
                redirect('admin/login', 'refresh');
            }
        }
    }

    protected function restrictToAdmin()
    {
        if ($this->sessionData['usertype'] !== 'administrator') {
            // Option 1: Show standard 404
            show_404();
        }
    }

    protected function jsonResponse($status = true, $message = '', $data = [])
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        exit;
    }

    protected function escapeData($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->escapeData($value);
            }
        } elseif (is_string($data)) {
            return html_escape($data);
        }
        return $data;
    }

    protected function post($key, $clean = TRUE)
    {
        // $clean is TRUE by default here!
        return $this->input->post($key, $clean);
    }

    protected function postArray($fields = [])
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $this->post($field);
        }
        return $data;
    }

    public function encryptData($string)
    {
        return base64_encode($this->encryption->encrypt($string));
    }

    public function decryptData($string)
    {
        return $this->encryption->decrypt(base64_decode($string));
    }

    public function renderTemplate($page, $title, $data = array(), $api = null, $id = null)
    {
        $data = $this->escapeData($data);
        $data['title'] = $title;
        $data['api'] = $api;
        $data['id'] = $id;
    }
}
