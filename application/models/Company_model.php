<?php
class Company_model extends MY_Model {

    protected $table = 'locate_company';
    protected $primaryKey = 'company_code';

    public function __construct()
    {
        parent::__construct();
    }

     public function getCompanyDetailsByID($id)
    {
        return $this->where([
            'company_code' => $id
            ])->first();
    }

}