<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Userlog_model extends MY_Model {

    protected $table = 'user_logs';
    protected $primaryKey = 'ulog_id';

    public function __construct() {
        parent::__construct();
    }

}