<?php
class Users_model extends MY_Model
{
    protected $table = 'tk_user_admin';
    protected $primaryKey = 'tkuaId';

    public function __construct()
    {
        parent::__construct();
    }

    public function checkLogin($username, $password)
    {
        return $this->where(['username' => $username, 'password' => md5($password), 'status' => 1])->first();
    }
}
