<?php
class Applicant_model extends MY_Model {

    protected $table = 'applicant';
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

     public function getAppDetails($id)
    {
        return $this->where([
            'app_id' => $id
            ])->first();
    }

}