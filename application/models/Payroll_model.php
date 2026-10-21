<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payroll_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Fetch all active employees with their assigned hourly rates
     * Adjust table/column names to match your schema (e.g. tbl_employees, user_id, etc.)
     */
    public function get_employee_hourly_rates()
    {
        // Example joining employee table with an hourly rate configuration table
        // If rates are inside the employee table directly, you can query that table solely.
        $this->db->select('e.emp_id, e.name, e.payroll_no as salary_num, COALESCE(ra.curr_rate, 0.00) as hourly_rate');
        $this->db->from('employee3 e');
        $this->db->join('masterlist_2ndgen r', 'r.emp_id = e.emp_id', 'inner');
        $this->db->join('masterlist_2ndgen_rate_assn ra', 'ra.emp_id = e.emp_id', 'left');
        $this->db->where('r.status', 1);
        $this->db->order_by('e.name', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Save or update employee rate
     */
    public function update_rate($salary_num, $hashed_rate, $empID)
    {
        $existing = $this->db
            ->where('emp_id', $empID)
            ->get('masterlist_2ndgen_rate_assn')
            ->row();

        // Safely retrieve previous hashed rate; default to '0' if new record
        $oldHashedRate = ($existing && !empty($existing->curr_rate)) ? $existing->curr_rate : encrypt_data(0);

        $data = [
            'emp_id'     => $empID,
            'payroll_no' => $salary_num,
            'curr_rate'  => $hashed_rate, // Stored encrypted
            'created_by' => $this->session->userdata('empid')
        ];

        if ($existing) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->where('emp_id', $empID)->update('masterlist_2ndgen_rate_assn', $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('masterlist_2ndgen_rate_assn', $data);
        }

        // Log history preserving the encrypted/hashed rate values
        $dataLogs = [
            'emp_id'     => $empID,
            'rate_from'  => $oldHashedRate,
            'rate_to'    => $hashed_rate,
            'created_at' => date('Y-m-d H:i:s'),
            'edited_by'  => $this->session->userdata('empid')
        ];
        $this->db->insert('masterlist_2ndgen_rate_logs', $dataLogs);

        return $this->db->affected_rows() >= 0;
    }

    public function get2ndGenRate($empID)
    {
        $this->db->select('curr_rate');
        $this->db->from('masterlist_2ndgen_rate_assn');
        $this->db->where('emp_id', $empID);
        
        return $this->db->get()->row();
    }

    public function get_employee_rate_logs($empID)
    {
        return $this->db
            ->select('l.rate_from, l.rate_to, l.created_at, l.edited_by')
            ->from('masterlist_2ndgen_rate_logs l')
            ->where('l.emp_id', $empID)
            ->order_by('l.created_at', 'DESC')
            ->get()
            ->result_array();
    }

}