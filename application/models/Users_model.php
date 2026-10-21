<?php
class Users_model extends MY_Model {

    protected $table = 'users';
    protected $primaryKey = 'user_no';

    public function __construct()
    {
        parent::__construct();
    }

    public function checkLogin($username, $password)
    {
        return $this->where([
            'username' => $username,
            'password' => md5($password)
            ])->first();
    }

}