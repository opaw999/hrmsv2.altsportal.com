<?php
class Department_model extends MY_Model {

    protected $table = 'locate_department';
    protected $primaryKey = 'dcode';

    public function __construct()
    {
        parent::__construct();
    }

     public function getDeptDetailsByID($companyCode, $bunitCode, $deptCode)
    {
        return $this->where([
            'company_code' => $companyCode,
            'bunit_code' => $bunitCode,
            'dept_code' => $deptCode
            ])->first();
    }

}