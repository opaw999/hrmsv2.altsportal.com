<?php
class Bunit_model extends MY_Model {

    protected $table = 'locate_business_unit';
    protected $primaryKey = 'bcode';

    public function __construct()
    {
        parent::__construct();
    }

     public function getBunitDetailsByID($companyCode, $bunitCode)
    {
        return $this->where([
            'company_code' => $companyCode,
            'bunit_code' => $bunitCode
            ])->first();
    }

}