<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Deduction_model extends MY_Model {

    protected $table = 'tk_employee_deductions';
    protected $primaryKey = 'id';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieve distinct cutoff years sorted in descending order
     *
     * @return array List of unique years e.g. ['2026', '2025', '2024']
     */
    public function getCutOffYear()
    {
        $query = $this->db
            ->select('DISTINCT(YEAR(co_startDate)) AS year', FALSE)
            ->where('co_startDate IS NOT NULL', null, false)
            ->where('co_startDate !=', '0000-00-00')
            ->order_by('year', 'DESC')
            ->get($this->table);

        return array_column($query->result_array(), 'year');
    }

}