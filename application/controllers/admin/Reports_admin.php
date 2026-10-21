<?php

class Reports_admin extends Portal_Controller
{
    public $ppdf;
    public $userType = '';
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Users_model', 'users');
        $this->load->model('Employee_model', 'employee');
        $this->load->model('Request_model', 'request');
        $this->userType = $this->session->userdata('usertype');
    }

    function generate_options()
    {
        $input = $this->input->post();
        $co = $input['co'];

        $y = date('Y');
        $cm = round(date('m'));
        $lm = 1;
        $sql = "SELECT YEAR(MIN(co_startDate)) AS earliest_year, MONTH(MIN(co_startDate)) AS earliest_month FROM `tk_employee_cutoff` WHERE co_schedule='$co';";
        $query = $this->request->queryRow($sql);
        if ($query) {
            $d = round(date('m'));
            $lm = ($query['earliest_month']) ? round($query['earliest_month']) : $d;
        }

        $data = '<option value="">---Select Cutoff---</option>';
        $cond = ['id' => $co, 'status' => 1];
        $cutoffs = $this->request->getArray('tk_cutoff_schedule', $cond);
        foreach ($cutoffs as $c) {
            $co = $c['id'];

            for ($m = $cm; $m >= $lm; $m--) {

                $sFC = $c['startFC'];
                $eFC = $c['endFC'];

                $sSC = $c['startSC'];
                $eSC = $c['endSC'];

                $fco1 = date('F j, Y', strtotime("$y-$m-$sFC"));
                $fco2 = date('F j, Y', strtotime("$y-$m-$eFC +1 month"));
                $f = "$fco1 - $fco2";

                $sco1 = date('F j, Y', strtotime("$y-$m-$sSC"));
                $sco2 = date('F j, Y', strtotime("$y-$m-$eSC"));
                $s = "$sco1 - $sco2";

                $f1 = date('Y-m-d', strtotime($fco1));
                $f2 = date('Y-m-d', strtotime($fco2));
                $s1 = date('Y-m-d', strtotime($sco1));
                $s2 = date('Y-m-d', strtotime($sco2));

                $payload1 = "$co|$f1|$f2|1";
                $payload2 = "$co|$s1|$s2|2";

                $data .= '<option value="' . $payload1 . '">' . $f . '</option>';
                $data .= '<option value="' . $payload2 . '">' . $s . '</option>';
            }
        }
        echo $data;
    }

    public function cutoffReportTable()
    {
        $payload = $this->input->post('payload');
        list($co, $co_startDate, $co_endDate, $cutoff) = explode('|', $payload);
        $sd = date('F j, Y', strtotime($co_startDate));
        $ed = date('F j, Y', strtotime($co_endDate));
        $cond = ['co_startDate' => $co_startDate, 'co_endDate' => $co_endDate];
        $query = $this->request->getArray('tk_employee_deductions', $cond);
        if ($query) {

            echo '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="reportTable">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Salary No.</th>';
            echo '<th>Name</th>';
            echo '<th>Deduction</th>';
            echo '</tr>';
            echo '</thead>';

            foreach ($query as $row) {
                $empId = $row['emp_id'];
                $salary_no = $this->request->getRow('employee3', ['emp_id' => $empId])['payroll_no'];
                $name = $this->request->getRow('employee3', ['emp_id' => $empId])['name'];
                $deduction = round($row['total_deduction'], 2);
                echo '<tr>';
                echo '<td>' . $salary_no . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $deduction . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }

    public function generate_cutoff_report()
    {
        $payload = $this->input->post('cutoff');
        list($co, $co_startDate, $co_endDate, $cutoff) = explode('|', $payload);

        $sd = date('F j, Y', strtotime($co_startDate));
        $ed = date('F j, Y', strtotime($co_endDate));

        $empId = $this->session->userdata('empid');
        $name = $this->request->getRow('employee3', ['emp_id' => $empId])['name'];

        $filename = 'Cutoff_Report_' . date('Ymd');

        header("Cache-Control: public");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-disposition: attachment; filename=" . $filename . ".xls");

        // Output HTML table Excel can read
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="utf-8"></head>';
        echo '<body>';
        echo '<style type="text/css">
                th,
                td {
                    border: 1px solid black;
                    border-width: thin;
                    padding: 4px;
                    font-size: 12px;
                    font-family: Calibri, sans-serif; /* Set font style to Calibri */
                }
                i, b {
                    font-family: Calibri, sans-serif; /* Optional: For italic and bold texts */
                }
            </style>';
        echo '<table>';
        echo '<tr><th colspan="3">CUTOFF REPORT</th></tr>';
        echo '<tr><th colspan="3">' . $sd . ' to ' . $ed . '</th></tr>';
        echo '<tr>';
        echo '<th>Salary No.</th>';
        echo '<th>Name</th>';
        echo '<th>Total Deduction</th>';
        echo '</tr>';

        $cond = ['co_startDate' => $co_startDate, 'co_endDate' => $co_endDate];
        $query = $this->request->getArray('tk_employee_deductions', $cond);
        foreach ($query as $row) {
            $empId = $row['emp_id'];
            $salary = $this->request->getRow('employee3', ['emp_id' => $empId])['payroll_no'];
            $salary_no = str_replace('CM', '', $salary); // remove 'CM'
            $name = $this->request->getRow('employee3', ['emp_id' => $empId])['name'];
            $deduction = $row['total_deduction'];
            echo '<tr>';
            echo '<td>' . $salary_no . '</td>';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $deduction . '</td>';
            echo '</tr>';
        }


        echo '</table>';
        echo '</body>';
        echo '</html>';
        exit;
    }

    public function vlpaymentReportTable()
    {
        $input = $this->input->post();
        $year = $input['year'];

        echo '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="reportTable">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Salary No.</th>';
        echo '<th>Name</th>';
        echo '<th>Total Days</th>';
        echo '</tr>';
        echo '</thead>';

        $cond = "year='$year' AND status='1' GROUP BY emp_id";
        $employees = $this->request->getArray('tk_vl_payment', $cond);
        // $employees = $this->request->getArray('masterlist_2ndgen', ['status' => 1]);
        foreach ($employees as $e) {
            $empId = $e['emp_id'];
            $emp = $this->request->getRow('employee3', ['emp_id' => $empId]);
            $name = $emp['name'];
            $salary = $emp['payroll_no'];

            $total = 0;
            $cond = ['emp_id' => $empId, 'year' => $year, 'status' => 1];
            $payment = $this->request->getArray('tk_vl_payment', $cond);
            if ($payment) {
                foreach ($payment as $p) {
                    $total += $p['total_days'];
                }
                echo '<tr>';
                echo '<td>' . $salary . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $total . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    }

    public function generate_vl_payment()
    {

        $input = $this->input->post();
        $year = $input['year'];

        $filename = 'VL_Payment_Report_' . $year;

        header("Cache-Control: public");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-disposition: attachment; filename=" . $filename . ".xls");

        // Output HTML table Excel can read
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="utf-8"></head>';
        echo '<body>';
        echo '<style type="text/css">
                th,
                td {
                    border: 1px solid black;
                    border-width: thin;
                    padding: 4px;
                    font-size: 12px;
                    font-family: Calibri, sans-serif; /* Set font style to Calibri */
                }
                i, b {
                    font-family: Calibri, sans-serif; /* Optional: For italic and bold texts */
                }
            </style>';
        echo '<table>';
        echo '<tr><th colspan="3">Vacation Leave Payment Report</th></tr>';
        echo '<tr><th colspan="3">For the Year ' . $year . '</th></tr>';
        echo '<tr>';
        echo '<th>Salary No.</th>';
        echo '<th>Name</th>';
        echo '<th>Total Days</th>';
        echo '</tr>';


        $cond = "year='$year' AND status='1' GROUP BY emp_id";
        $employees = $this->request->getArray('tk_vl_payment', $cond);
        // $employees = $this->request->getArray('masterlist_2ndgen', ['status' => 1]);
        foreach ($employees as $e) {
            $empId = $e['emp_id'];
            $emp = $this->request->getRow('employee3', ['emp_id' => $empId]);
            $name = $emp['name'];
            $salary = $emp['payroll_no'];
            $salary_no = str_replace('CM', '', $salary); // remove 'CM'
            $total = 0;
            $cond = ['emp_id' => $empId, 'year' => $year, 'status' => 1];
            $payment = $this->request->getArray('tk_vl_payment', $cond);
            if ($payment) {
                foreach ($payment as $p) {
                    $total += $p['total_days'];
                }
                echo '<tr>';
                echo '<td>' . $salary_no . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $total . '</td>';
                echo '</tr>';
            }
        }

        echo '</table>';
        echo '</body>';
        echo '</html>';
        exit;
    }

    public function generate_dtr()
    {
        $this->load->library('ppdf');
        $input = $this->input->get();
        $empId = $input['empId'];
        $payload = $input['payload'];
        list($co, $co_startDate, $co_endDate, $cutoff) = explode('|', $payload);

        $sd = date('F j, Y', strtotime($co_startDate));
        $ed = date('F j, Y', strtotime($co_endDate));

        $employee = $this->request->getRow('employee3', ['emp_id' => $empId]);
        $name = $employee['name'];
        $position = $employee['position'];
        $salary = $employee['payroll_no'];

        $cond = ['emp_id' => $empId, 'co_startDate' => $co_startDate, 'co_endDate' => $co_endDate];
        $dtr = $this->request->getRow('tk_employee_cutoff', $cond);
        $hw = round($dtr['co_hours'], 2);
        $deduction = round($dtr['deficit_hours'], 2);
        $leaves = round($dtr['co_leaves'], 2);

        $this->ppdf->SetAuthor('HRMS - Portal');
        $this->ppdf->SetTitle('Daily Time Record');
        $this->ppdf->SetSubject('DTR');
        $this->ppdf->SetKeywords('DTR');

        $this->ppdf->setPrintHeader(false);
        $this->ppdf->setPrintFooter(false);

        $this->ppdf->setHeaderFont(array('PDF_FONT_NAME_MAIN', '', 'PDF_FONT_SIZE_MAIN'));
        $this->ppdf->setFooterFont(array('PDF_FONT_NAME_DATA', '', 'PDF_FONT_SIZE_DATA'));

        $this->ppdf->SetDefaultMonospacedFont('PDF_FONT_MONOSPACED');

        $this->ppdf->SetAutoPageBreak(TRUE, 15);

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $this->ppdf->setLanguageArray($l);
        }

        // set margins
        $this->ppdf->SetMargins(12, 12, 12);
        $this->ppdf->SetHeaderMargin('PDF_MARGIN_HEADER');
        $this->ppdf->SetFooterMargin('PDF_MARGIN_FOOTER');

        $this->ppdf->AddPage();

        $this->ppdf->SetFont('times', 'B', 10, 'L');
        $this->ppdf->Cell(180, 5, 'Daily Time Record', 0, 1, 'C');
        $this->ppdf->Cell(180, 5, 'For the Period of ' . $sd . ' to ' . $ed, 0, 1, 'C');
        $this->ppdf->Ln(2);

        $this->ppdf->SetFont('times', '', 11);
        $html = '';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td width="10%">Name</td>';
        $html .= '<td width="5%">:</td>';
        $html .= '<td width="55%">' . $name . '</td>';
        $html .= '<td width="20%">Total Hours</td>';
        $html .= '<td width="5%">:</td>';
        $html .= '<td width="5%">' . $hw . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td>Salary No.</td>';
        $html .= '<td>:</td>';
        $html .= '<td>' . $salary . '</td>';
        $html .= '<td>Total Deduction</td>';
        $html .= '<td>:</td>';
        $html .= '<td>' . $deduction . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td>Position</td>';
        $html .= '<td>:</td>';
        $html .= '<td>' . $position . '</td>';
        $html .= '<td>Total Leave Hours</td>';
        $html .= '<td>:</td>';
        $html .= '<td>' . $leaves . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '<br><br>';
        $html .= '<table>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 5px 3px 5px;">Date</th>';
        $html .= '<th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 5px 3px 5px;">Day</th>';
        $html .= '<th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 5px 3px 5px;">IN1</th>';
        $html .= '<th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 5px 3px 5px;">OUT1</th>';
        $html .= '<th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 5px 3px 5px;">IN2</th>';
        $html .= '<th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 5px 3px 5px;">OUT2</th>';
        $html .= '<th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 5px 3px 5px;text-align:center;">HW</th>';
        $html .= '<th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 5px 3px 5px;text-align:center;">Requests</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $s = date('Y-m-d', strtotime($co_startDate));
        $e = date('Y-m-d', strtotime($co_endDate));
        while ($s <= $e) {
            $tableDate = date('M. j, Y', strtotime($s));
            $tableDay = date('D', strtotime($s));
            $date = date('Y-m-d', strtotime($s));

            $holiday = false;
            $holiday_name = '';
            $check = $this->request->getRow('tk_holidays', ['date' => $date, 'date_change' => '0000-00-00']);
            if ($check) {
                $holiday_name = $check['holiday'];
                $holiday = true;
            } else {
                $check = $this->request->getRow('tk_holidays', ['date_change' => $date]);
                if ($check) {
                    $holiday_name = $check['holiday'];
                    $holiday = true;
                }
            }

            $dayoff = false;
            $cond = "emp_id='$empId' AND old_dayoff='$date' AND status='approved' ORDER BY tkcdoId DESC";
            $old_a = $this->request->getRow('tk_change_dayoff', $cond);
            $cond = "emp_id='$empId' AND new_dayoff='$date' AND status='approved' ORDER BY tkcdoId DESC";
            $new_a = $this->request->getRow('tk_change_dayoff', $cond);
            $dayOfWeek = date('N', strtotime($date));
            $isWeekend = ($dayOfWeek == 7);
            if ($new_a) {
                if ($old_a) {
                    if ($new_a['tkcdoId'] > $old_a['tkcdoId']) {
                        $dayoff = true;
                    }
                } else {
                    $dayoff = true;
                }
            } else {
                if ($isWeekend && !$old_a) {
                    $dayoff = true;
                }
            }

            $hw = 0;
            $in1 = $out1 = $in2 = $out2 = $request = $info = '';

            $onDuty = false;
            $cond = ['emp_id' => $empId, 'attendance_date' => $date];
            $duty = $this->request->getRow('tk_attendance_records', $cond);
            if ($duty) {
                $onDuty = true;
                if ($duty['in1']) {
                    $in1 = date('h:i A', strtotime($duty['in1']));
                }
                if ($duty['out1']) {
                    $out1 = date('h:i A', strtotime($duty['out1']));
                }
                if ($duty['in2']) {
                    $in2 = date('h:i A', strtotime($duty['in2']));
                }
                if ($duty['out2']) {
                    $out2 = date('h:i A', strtotime($duty['out2']));
                }

                if ($duty['hours_worked'] > 9) {
                    $hw += 9;
                } else {
                    $hw += $duty['hours_worked'];
                }
            }

            $onLeave = false;
            $fixed = false;
            $request = '';
            $cond = ['emp_id' => $empId, 'date' => $date];
            $check = $this->request->getRow('tk_leave_request_dates', $cond);
            if ($check) {
                $onLeave = true;
                $leaveId = $check['leaveId'];
                $cond = ['leaveId' => $leaveId, 'isFixed' => 1];
                $leave = $this->request->getRow('tk_leave_requests', $cond);
                if ($leave) {
                    $fixed = true;
                    // $hw += $leave['total_hours'] / $leave['total_days'];
                    $name = $this->request->getRow('tk_request_type', ['code' => $leave['code']])['acroname'];
                    if ($leave['status'] === 'approved') {
                        $color = 'style="color:blue"';
                    } else if ($leave['status'] === 'disapproved') {
                        $color = 'style="color:red"';
                    } else {
                        $color = 'style="color:red"';
                    }
                    $request = "<span $color>[ $name ]</span>";
                }

                $cond = ['leaveId' => $leaveId, 'isFixed' => 0];
                $leave = $this->request->getRow('tk_leave_requests', $cond);
                if ($leave) {
                    $fixed = false;
                    $cond = "leaveId='$leaveId' AND emp_id='$empId' GROUP BY groupId";
                    $details = $this->request->getArray('tk_leave_request_details', $cond);
                    foreach ($details as $d) {
                        // $hw += $d['group_hours'];
                        $name = $this->request->getRow('tk_request_type', ['code' => $d['code']])['acroname'];
                        $stat = ucwords($d['status']);

                        if ($d['status'] === 'approved') {
                            $color = 'style="color:blue"';
                        } else if ($d['status'] === 'disapproved') {
                            $color = 'style="color:red"';
                        } else {
                            $color = 'style="color:red"';
                        }
                        // $request .= "[ $name ] ";
                        $request .= "<span $color>[ $name ]</span>";
                    }
                }
            }

            if ($holiday && $dayoff) {
                $html .= '<tr>';
                $html .= '<td>' . $tableDate . '</td>';
                $html .= '<td>' . $tableDay . '</td>';
                $html .= '<td colspan="4" style="color:red; text-align:center;">[ ' . $holiday_name . ' ] [ DAY-OFF ]</td>';
                $html .= '<td style="text-align:center;">' . $hw . '</td>';
                $html .= '<td style="text-align:center; font-size: 9px;">' . $request . '</td>';
                $html .= '</tr>';
            } else if ($holiday) {
                $html .= '<tr>';
                $html .= '<td>' . $tableDate . '</td>';
                $html .= '<td>' . $tableDay . '</td>';
                $html .= '<td colspan="4" style="color:red; text-align:center;">[ ' . $holiday_name . ' ]</td>';
                $html .= '<td style="text-align:center;">' . $hw . '</td>';
                $html .= '<td style="text-align:center; font-size: 9px;">' . $request . '</td>';
                $html .= '</tr>';
            } else if ($dayoff) {
                $html .= '<tr>';
                $html .= '<td>' . $tableDate . '</td>';
                $html .= '<td>' . $tableDay . '</td>';
                $html .= '<td colspan="4" style="color:#ff0000; text-align:center;">[ DAY-OFF ]</td>';
                $html .= '<td style="text-align:center;">' . $hw . '</td>';
                $html .= '<td style="text-align:center; font-size: 9px;">' . $request . '</td>';
                $html .= '</tr>';
            } else {
                $html .= '<tr>';
                $html .= '<td>' . $tableDate . '</td>';
                $html .= '<td>' . $tableDay . '</td>';
                $html .= '<td>' . $in1 . '</td>';
                $html .= '<td>' . $out1 . '</td>';
                $html .= '<td>' . $in2 . '</td>';
                $html .= '<td>' . $out2 . '</td>';
                $html .= '<td style="text-align:center;">' . $hw . '</td>';
                $html .= '<td style="text-align:center; font-size: 9px;">' . $request . '</td>';
                $html .= '</tr>';
            }


            $s = date('Y-m-d', strtotime($tableDate . ' +1 day'));
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<hr>';
        $html .= '<br>';
        $html .= '<i style="font-size:9px">Note: Requests color coding is based on the status of the request. ';
        $html .= '<span style="color:blue;">[BLUE]</span> for Approved, ';
        $html .= '<span style="color:red;">[RED]</span> for Diaspproved and ';
        $html .= '<span style="color:red;">[RED]</span> for Pending</i>';

        $this->ppdf->writeHTML($html, true, false, true, false, '');

        // ob_clean();
        // $this->ppdf->Output('generate_dtr.pdf', 'I');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->ppdf->Output('generate_dtr.pdf', 'I');
        exit;
    }
}
