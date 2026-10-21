<?php
class Employee_model extends MY_Model {

    protected $table = 'employee3';
    protected $primaryKey = 'user_no';

    public function __construct()
    {
        parent::__construct();
    }

     public function getEmpDetailsByID($id)
    {
        return $this->where([
            'emp_id' => $id
            ])->first();
    }

}