<?php

class Process_admin extends Portal_Controller
{
    public $userId = '';
    public $usertype = '';
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Users_model', 'users');
        $this->load->model('Employee_model', 'employee');
        $this->load->model('Request_model', 'request');
        $this->usertype = $this->session->userdata('usertype');
        $this->userId = $this->session->userdata('empid');
    }

    function process_list()
    {
        $input = $this->input->post();
        $co = $input['co'];
        if ($this->usertype == 'administrator') {
            $data = [];
            $employees = $this->request->getArray('masterlist_2ndgen', ['status' => 1]);
            foreach ($employees as $e) {
                $empId = $e['emp_id'];
                $cond = ['emp_id' => $empId, 'co_schedule' => $co, 'status' => 1];
                $check = $this->request->getRow('tk_employee_cutoff_schedule', $cond);
                if ($check) {
                    $name = $this->request->getRow('employee3', ['emp_id' => $empId])['name'];
                    $col = [];
                    $col[] = $empId;
                    $col[] = $name;
                    $col[] = '<input class="form-check-input" type="checkbox" name="empIds[]" value="' . $empId . '">';
                    $data[] = $col;
                }
            }
            echo json_encode(['data' => $data]);
        } else {
            $data = [];
            $employees = $this->request->getArray('masterlist_2ndgen', ['status' => 1]);
            foreach ($employees as $e) {
                $empId = $e['emp_id'];

                $cond = ['approvee' => $empId, 'approver' => $this->userId];
                $check1 = $this->request->getRow('tk_approvers_subordinates', $cond);
                $cond = ['emp_id' => $empId, 'co_schedule' => $co, 'status' => 1];
                $check2 = $this->request->getRow('tk_employee_cutoff_schedule', $cond);
                if ($check1 && $check2) {
                    $name = $this->request->getRow('employee3', ['emp_id' => $empId])['name'];
                    $col = [];
                    $col[] = $empId;
                    $col[] = $name;
                    $col[] = '<input class="form-check-input" type="checkbox" name="empIds[]" value="' . $empId . '">';
                    $data[] = $col;
                }
            }
            echo json_encode(['data' => $data]);
        }
    }

    function process_options()
    {
        $input = $this->input->post();
        $co = $input['co'];

        $y = date('Y');
        $cm = round(date('m'));
        // $lm = round(date('m', strtotime('-1 month')));
        // $lm = round(date('m'));
        // $curDate = date('Y-m-d');

        $cond = ['co_schedule' => $co, 'status' => 1];
        $extend = $this->request->getRow('tk_cutoff_extension', $cond);
        $starDate = $extend['startDate'];
        $endDate = $extend['endDate'];

        $data = '';
        $cond = ['id' => $co, 'status' => 1];
        $cutoffs = $this->request->getArray('tk_cutoff_schedule', $cond);
        foreach ($cutoffs as $c) {
            $co = $c['id'];

            for ($m = $cm; $m >= 1; $m--) {

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
                if ($starDate == $f1 && $endDate == $f2) {
                    break;
                }

                $data .= '<option value="' . $payload2 . '">' . $s . '</option>';
                if ($starDate == $s1 && $endDate == $s2) {
                    break;
                }
            }
        }
        echo $data;
    }

    function process_cutoff()
    {
        $input = $this->input->post();
        $payload = $input['payload'];

        list($co, $co_startDate, $co_endDate, $cutoff) = explode('|', $payload);

        $cond = ['render_type' => 'duty', 'limit_type' => 'maximum', 'limit_duration' => 'daily'];
        $dutyMax = $this->request->getRow('tk_render_limits', $cond)['hours_rendered'];

        $cond = ['render_type' => 'duty', 'limit_type' => 'minimum', 'limit_duration' => 'weekly'];
        $dutyWeekMin = $this->request->getRow('tk_render_limits', $cond)['hours_rendered'];

        $empIds = $input['empIds'];
        foreach ($empIds as $empId) {

            $getS = date('N', strtotime($co_startDate));
            $getE = date('N', strtotime($co_endDate));

            if ($getS == 1) {
                $startDate = date('Y-m-d', strtotime($co_startDate));
            } else {
                $startDate = date('Y-m-d', strtotime($co_startDate . ' -' . ($getS - 1) . ' days'));
            }

            if ($getE == 7) {
                $endDate = date('Y-m-d', strtotime($co_endDate));
            } else {
                $endDate = date('Y-m-d', strtotime($co_endDate . ' -' . $getE . ' days'));
            }

            $total_holidays = 0;
            $total_leaves = 0;
            $co_days = 0;
            $co_hours = 0;
            $cos = $co_startDate;
            $coe = $co_endDate;
            while ($cos <= $coe) {
                ++$co_days;

                $cond = ['date' => $cos, 'date_change' => '0000-00-00'];
                $holidays = $this->request->getRow('tk_holidays', $cond);
                if ($holidays) {
                    ++$total_holidays;
                }

                $cond = ['date_change' => $cos];
                $holidays = $this->request->getRow('tk_holidays', $cond);
                if ($holidays) {
                    ++$total_holidays;
                }

                $cond = ['emp_id' => $empId, 'date' => $cos];
                $check = $this->request->getRow('tk_leave_request_dates', $cond);
                if ($check) {
                    $leaveId = $check['leaveId'];

                    $cond = ['emp_id' => $empId, 'leaveId' => $leaveId, 'isFixed' => 1, 'status' => 'approved'];
                    $leave = $this->request->getRow('tk_leave_requests', $cond);
                    if ($leave) {
                        if ($leave['code'] == 'vacation' && $leave['payment'] == 'unpaid') {
                        } else {
                            $hours = $leave['total_hours'] / $leave['total_days'];
                            $total_leaves += $hours;
                        }
                    }

                    $cond = ['emp_id' => $empId, 'leaveId' => $leaveId, 'isFixed' => 0];
                    $leave = $this->request->getRow('tk_leave_requests', $cond);
                    if ($leave) {

                        $cond = "leaveId='$leaveId' AND emp_id='$empId' AND status='approved' GROUP BY groupId";
                        $details = $this->request->getArray('tk_leave_request_details', $cond);
                        foreach ($details as $l) {
                            $total_leaves += $l['group_hours'];
                        }
                    }
                }

                $cond = ['emp_id' => $empId, 'attendance_date' => $cos];
                $attendance = $this->request->getArray('tk_attendance_records', $cond);
                foreach ($attendance as $a) {

                    $total_day_duty_hours = 0;
                    $cond = ['attendance_id' => $a['id'], 'status' => 'approved'];
                    $groups = $this->request->getArray('tk_attendance_groups', $cond);
                    foreach ($groups as $g) {
                        $total_day_duty_hours += round($g['total_hours'], 2);
                    }

                    if ($total_day_duty_hours > $dutyMax) {
                        $co_hours += $dutyMax;
                    } else {
                        $co_hours += $total_day_duty_hours;
                    }
                }

                $cos = date('Y-m-d', strtotime($cos . ' +1 day'));
            }

            $week1 = 0;
            $week2 = 0;
            $total_days = 0;
            $total_hours = 0;
            $sdate = $startDate;
            $edate = $endDate;
            while ($sdate <= $edate) {
                ++$total_days;

                $cond = ['emp_id' => $empId, 'date' => $sdate];
                $check = $this->request->getRow('tk_leave_request_dates', $cond);
                if ($check) {
                    $leaveId = $check['leaveId'];

                    $cond = ['emp_id' => $empId, 'leaveId' => $leaveId, 'isFixed' => 1, 'status' => 'approved'];
                    $leave = $this->request->getRow('tk_leave_requests', $cond);
                    if ($leave) {
                        if ($leave['code'] == 'vacation' && $leave['payment'] == 'unpaid') {
                        } else {
                            $hours = $leave['total_hours'] / $leave['total_days'];
                            $total_hours += $hours;
                        }
                    }

                    $cond = ['emp_id' => $empId, 'leaveId' => $leaveId, 'isFixed' => 0];
                    $leave = $this->request->getRow('tk_leave_requests', $cond);
                    if ($leave) {

                        $cond = "leaveId='$leaveId' AND emp_id='$empId' AND status='approved' GROUP BY groupId";
                        $details = $this->request->getArray('tk_leave_request_details', $cond);
                        foreach ($details as $l) {
                            $total_hours += $l['group_hours'];
                        }
                    }
                }

                $cond = ['emp_id' => $empId, 'attendance_date' => $sdate];
                $attendance = $this->request->getArray('tk_attendance_records', $cond);
                foreach ($attendance as $a) {

                    $total_day_duty_hours = 0;
                    $cond = ['attendance_id' => $a['id'], 'status' => 'approved'];
                    $groups = $this->request->getArray('tk_attendance_groups', $cond);
                    foreach ($groups as $g) {
                        $total_day_duty_hours += round($g['total_hours'], 2);
                    }

                    if ($total_day_duty_hours > $dutyMax) {
                        $total_hours += $dutyMax;
                    } else {
                        $total_hours += $total_day_duty_hours;
                    }
                }

                if ($total_days == 7) {
                    $week1 = $total_hours;
                }

                if ($total_days == 14) {
                    $week2 = $total_hours - $week1;
                }

                $sdate = date('Y-m-d', strtotime($sdate . ' +1 day'));
            }

            $deficit_week1 = 0;
            if ($dutyWeekMin > $week1) {
                $deficit_week1 = $dutyWeekMin - $week1;
            }

            $deficit_week2 = 0;
            if ($dutyWeekMin > $week2) {
                $deficit_week2 = $dutyWeekMin - $week2;
            }

            $deficit_hours = $deficit_week1 + $deficit_week2;


            $cond = ['emp_id' => $empId, 'co_startDate' => $co_startDate, 'co_endDate' => $co_endDate];
            $check = $this->request->getRow('tk_employee_cutoff', $cond);
            if (!$check) {
                $data = [
                    'emp_id' => $empId,
                    'cutoff' => $cutoff,
                    'co_startDate' => $co_startDate,
                    'co_endDate' => $co_endDate,
                    'co_days' => $co_days,
                    'co_hours' => $co_hours,
                    'co_leaves' => $total_leaves,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'total_days' => $total_days,
                    'total_hours' => $total_hours,
                    'total_holidays' => $total_holidays,
                    'deficit_hours' => $deficit_hours,
                    'co_schedule' => $co,
                    'created' => date('Y-m-d H:i:s'),
                ];

                $this->request->insert('tk_employee_cutoff', $data);
            } else {
                $data = [
                    'total_days' => $total_days,
                    'total_hours' => $total_hours,
                    'total_holidays' => $total_holidays,
                    'deficit_hours' => $deficit_hours,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'co_days' => $co_days,
                    'co_hours' => $co_hours,
                    'co_leaves' => $total_leaves,
                ];
                $this->request->update_table('tk_employee_cutoff', $data, $cond);
            }

            $cond = ['emp_id' => $empId, 'co_startDate' => $co_startDate, 'co_endDate' => $co_endDate];
            $check = $this->request->getRow('tk_employee_deductions', $cond);
            if (!$check) {
                $data = [
                    'emp_id' => $empId,
                    'co_startDate' => $co_startDate,
                    'co_endDate' => $co_endDate,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'total_deduction' => $deficit_hours,
                    'generated_date' => date('Y-m-d H:i:s'),
                    'generated_by' => $this->userId,
                ];

                $this->request->insert('tk_employee_deductions', $data);
            } else {
                $data = [
                    'total_deduction' => $deficit_hours,
                    'updated_date' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->userId,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ];
                $this->request->update_table('tk_employee_deductions', $data, $cond);
            }
        }
        echo json_encode(['message' => 'success']);
        return;
    }
}
