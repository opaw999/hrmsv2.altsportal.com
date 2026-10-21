<?php
class Masterlist_model extends MY_Model {

    protected $table = 'masterlist_2ndgen';
    protected $primaryKey = 'emp_id';

    public function __construct()
    {
        parent::__construct();
    }

     public function getMasterListDetails($id)
    {
        return $this->where([
            'emp_id' => $id
            ])->first();
    }

}