<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Manila');

class Payroll extends Portal_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Payroll_model', 'payroll');
        $this->load->model('Employee_model', 'employee');
        
        // JSON response
        header('Content-Type: application/json');
    }

    /**
     * GET: payroll/get_hourly_rates
     */
    public function get_hourly_rates()
    {
        $employees = $this->payroll->get_employee_hourly_rates();
        
        if (!empty($employees) && is_array($employees)) {
            foreach ($employees as &$emp) {
                // 1. Format name from ALL CAPS to Title Case
                if (!empty($emp['name'])) {
                    $emp['name'] = mb_convert_case($emp['name'], MB_CASE_TITLE, 'UTF-8');
                }

                // 2. Resolve and decrypt the hourly rate
                // Check whichever column alias your query returns: curr_rate or hourly_rate
                $rawRate = isset($emp['curr_rate']) ? $emp['curr_rate'] : (isset($emp['hourly_rate']) ? $emp['hourly_rate'] : null);

                $decryptedVal = 0.00;

                if (!empty($rawRate) && $rawRate !== '0') {
                    // If it is already a plain numeric value (legacy data), use it directly
                    if (is_numeric($rawRate)) {
                        $decryptedVal = floatval($rawRate);
                    } else {
                        // Decrypt using the security_helper function
                        $decrypted = decrypt_data($rawRate);
                        $decryptedVal = ($decrypted !== false && is_numeric($decrypted)) ? floatval($decrypted) : 0.00;
                    }
                }

                // Standardize both aliases so frontend scripts receive clean numbers
                $emp['hourly_rate'] = number_format($decryptedVal, 2, '.', '');
                $emp['curr_rate']   = $emp['hourly_rate'];
            }
            unset($emp); // Unset pointer reference
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'total'  => count($employees),
                'data'   => $employees
            ]));
    }

    /**
     * POST: payroll/update_hourly_rate
     */
    public function update_hourly_rate()
    {
        $salNo  = trim($this->input->post('salNo'));
        $empID  = trim($this->input->post('empID'));
        $hourly_rate = floatval($this->input->post('hourly_rate'));

        if (empty($salNo) || $hourly_rate <= 0) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Please provide a valid Salary Number and Hourly Rate.'
                ]));
        }

        $hashRate = encrypt_data($hourly_rate);

        $saved = $this->payroll->update_rate($salNo, $hashRate, $empID);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => $saved,
                'message' => $saved ? 'Hourly rate saved successfully.' : 'Unable to update rate.'
            ]));
    }

    public function get_rate_logs()
    {
        $empID = trim($this->input->get('emp_id'));

        if (empty($empID)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Employee ID is required.',
                    'data'    => []
                ]));
        }

        $logs = $this->payroll->get_employee_rate_logs($empID);

        if (!empty($logs) && is_array($logs)) {
            foreach ($logs as &$item) {
                $employee = $this->employee->getEmpDetailsByID($item['edited_by']);
                // Format created_at to "YYYY, Mon DD hh:mm AM/PM" (e.g. 2026, Oct 15 04:11 PM)
                if (!empty($item['created_at'])) {
                    $item['created_at'] = date('Y, M d h:i A', strtotime($item['created_at']));
                }

                // Decrypt rate_from
                if (!empty($item['rate_from']) && !is_numeric($item['rate_from'])) {
                    $decryptedFrom = decrypt_data($item['rate_from']);
                    $item['rate_from'] = ($decryptedFrom !== false && is_numeric($decryptedFrom)) ? $decryptedFrom : 0;
                }

                // Decrypt rate_to
                if (!empty($item['rate_to']) && !is_numeric($item['rate_to'])) {
                    $decryptedTo = decrypt_data($item['rate_to']);
                    $item['rate_to'] = ($decryptedTo !== false && is_numeric($decryptedTo)) ? $decryptedTo : 0;
                }
                $item['edited_by'] = $employee->name;
            }
            unset($item);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data'   => $logs
            ]));
    }
    

}