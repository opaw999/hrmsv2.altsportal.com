<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Deductions extends Portal_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('download');
        $this->load->model('Payroll_model', 'payroll');
    }

    public function export_csv()
    {
        // Ensure download helper is loaded
        $this->load->helper('download');

        $cutoff_period = $this->input->post('cutoff_period', TRUE);
        $month         = $this->input->post('cutoff_month', TRUE);
        $year          = (int) ($this->input->post('cutoff_year', TRUE) ?: date('Y'));

        if (empty($cutoff_period) || empty($month)) {
            show_error('Please select cutoff period, month, and year.', 400);
            return;
        }

        $mo_int = (int) $month;
        $mo_pad = str_pad($mo_int, 2, '0', STR_PAD_LEFT);
        $yr2    = substr((string) $year, -2); // e.g. "26"

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May',     6 => 'June',     7 => 'July',  8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        // =========================================================================
        // 1. DATE CALCULATION (Y-m-d format & dynamic labels)
        // =========================================================================
        if ($cutoff_period === '24_to_08') {
            // Starts on 24th of selected month, ends on 08th of NEXT month
            $start_date = sprintf('%04d-%02d-24', $year, $mo_int);

            $next_mo_int = ($mo_int === 12) ? 1 : ($mo_int + 1);
            $next_year   = ($mo_int === 12) ? ($year + 1) : $year;
            $next_yr2    = substr((string) $next_year, -2);
            $next_mo_pad = str_pad($next_mo_int, 2, '0', STR_PAD_LEFT);

            $end_date    = sprintf('%04d-%02d-08', $next_year, $next_mo_int);

            // e.g. 032426-040826
            $cutoff_code = "{$mo_pad}24{$yr2}-{$next_mo_pad}08{$next_yr2}";

            // Filename based on selections: deductions_March_24_to_April_08_2026.csv
            $cur_month_name  = $monthNames[$mo_int];
            $next_month_name = $monthNames[$next_mo_int];
            $filename        = "deductions_{$cur_month_name}_24_to_{$next_month_name}_08_{$year}.csv";

        } else {
            // "09_to_23" (March 09 to 24 within the same month)
            $start_date  = sprintf('%04d-%02d-09', $year, $mo_int);
            $end_date    = sprintf('%04d-%02d-23', $year, $mo_int);

            // e.g. 030926-032426
            $cutoff_code = "{$mo_pad}09{$yr2}-{$mo_pad}23{$yr2}";

            $cur_month_name = $monthNames[$mo_int];
            $filename       = "deductions_{$cur_month_name}_09_to_23_{$year}.csv";
        }

        // =========================================================================
        // 2. QUERY DATABASE USING Y-m-d (co_startDate)
        // =========================================================================
        $db_records = $this->db
            ->select('
                d.emp_id,
                e.payroll_no,
                e.name,
                d.total_deduction,
                r.curr_rate
            ')
            ->from('tk_employee_deductions d')
            ->join('masterlist_2ndgen m', 'm.emp_id = d.emp_id AND m.status = 1', 'inner')
            ->join('employee3 e', 'e.emp_id = d.emp_id', 'left')
            ->join('masterlist_2ndgen_rate_assn r', 'r.emp_id = d.emp_id', 'left')
            ->where('d.co_startDate >=', $start_date)
            ->where('d.co_endDate <=', $end_date)
            ->order_by('e.name', 'ASC')
            ->get()
            ->result_array();

        $export_rows = [];

        foreach ($db_records as $rec) {
            // 1. Resolve and decrypt hourly rate safely
            $clean_payroll_no = preg_replace('/^[a-zA-Z]+/', '', trim($rec['payroll_no']));

            $rawRate = !empty($rec['curr_rate']) ? $rec['curr_rate'] : 0;
            $hourlyRate = 0.00;

            if (!empty($rawRate) && $rawRate !== '0') {
                if (is_numeric($rawRate)) {
                    $hourlyRate = (float) $rawRate;
                } else if (function_exists('decrypt_data')) {
                    $decrypted = decrypt_data($rawRate);
                    $hourlyRate = ($decrypted !== false && is_numeric($decrypted)) ? (float) $decrypted : 0.00;
                }
            }

            // 2. Compute Total Deduction: (hours deficiency/deduction) * (hourly rate)
            $hoursDeficiency = (float) $rec['total_deduction'];
            $totalAmount     = $hoursDeficiency * $hourlyRate;

            // 3. Format row: [Sal #, Name, Cutoff, Code 10, Space, Total Amount]
            $export_rows[] = [
                $clean_payroll_no,
                $rec['name'],
                $cutoff_code,
                '10',
                ' ',
                number_format($totalAmount, 2, '.', '')
            ];
        }   

        // =========================================================================
        // 3. GENERATE & STREAM CSV FILE
        // =========================================================================
        $file_handle = fopen('php://memory', 'w');
        fputs($file_handle, "\xEF\xBB\xBF"); // UTF-8 BOM

        foreach ($export_rows as $row) {
            fputcsv($file_handle, $row);
        }

        fseek($file_handle, 0);
        $csv_content = stream_get_contents($file_handle);
        fclose($file_handle);

        force_download($filename, $csv_content, 'text/csv');
    }

}