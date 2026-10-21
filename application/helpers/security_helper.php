<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('encrypt_data')) {
    function encrypt_data($data) {
        $ci =& get_instance();
        $ci->load->library('encryption');
        return $ci->encryption->encrypt($data);
    }
}

if (!function_exists('decrypt_data')) {
    function decrypt_data($data) {
        $ci =& get_instance();
        $ci->load->library('encryption');
        return $ci->encryption->decrypt($data);
    }
}