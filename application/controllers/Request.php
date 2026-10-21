<?php

class Request extends Portal_Controller
{
    public $userId = '';
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Users_model', 'users');
        $this->load->model('Employee_model', 'employee');
        $this->load->model('Request_model', 'request');
        $this->userId = $this->session->userdata('empid');
    }

    public function getMonthCells()
    {
        $start = $this->input->post('start');
        $end   = $this->input->post('end');

        $cells = [];
        $cursor = strtotime($start);
        $endTs  = strtotime($end);

        while ($cursor <= $endTs) {
            $date    = date('Y-m-d', $cursor);
            $dayNum  = (int) date('j', $cursor);
            $dow     = (int) date('w', $cursor);
            $isOtherMonth = false; // JS will handle other-month styling; here we just build content
            $isToday = ($date === date('Y-m-d'));

            $cell = $this->build_cell_html($date, $dayNum, $isOtherMonth, $isToday, $this->userId);

            // Strip the outer wrapper — JS only needs the inner HTML
            // build_cell_html returns: <div class="calendar-cell ...">...</div>
            // We'll extract inner content below.
            $cells[$date] = $cell;

            $cursor = strtotime('+1 day', $cursor);
        }

        // Convert each full cell wrapper into just the inner HTML the JS expects
        foreach ($cells as $date => $html) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            libxml_clear_errors();
            $wrapper = $doc->getElementsByTagName('div')->item(0);
            $inner = '';
            if ($wrapper) {
                foreach ($wrapper->childNodes as $child) {
                    $inner .= $doc->saveHTML($child);
                }
            }
            $cells[$date] = $inner;
        }

        echo json_encode(['cells' => $cells]);
    }

    public function build_cell_html($date, $dayNum, $isOtherMonth, $isToday, $empid)
    {
        $cell = '<div class="calendar-cell ' . ($isOtherMonth ? 'other-month' : '') . ' ' . ($isToday ? 'today' : '') . '" data-date="' . $date . '">';

        $dayOfWeek = date('N', strtotime($date));
        $isWeekend = ($dayOfWeek == 7);

        $hol = $this->request->getRow('tk_holidays', ['date' => $date, 'date_change' => '0000-00-00']);
        if (!$hol) {
            $hol = $this->request->getRow('tk_holidays', ['date_change' => $date]);
        }

        $log = $this->request->getRow('tk_attendance_records', ['emp_id' => $empid, 'attendance_date' => $date]);

        $row = [];
        $check = $this->request->getRow('tk_leave_request_dates', ['emp_id' => $empid, 'date' => $date]);
        if ($check) {
            $cond = ['leaveId' => $check['leaveId']];
            $row = $this->request->getRow('tk_leave_requests', $cond);
        }
        $cond = "emp_id='$empid' AND isFixed='1' AND status ='disapproved' AND ('$date' BETWEEN startDate AND endDate)";
        $disp = $this->request->getArray('tk_leave_requests', $cond);


        if ($row && $log) {
            $leaveacro = '';
            $leaveTypeText = '';
            $code = $row['code'];
            $id = $row['leaveId'];
            $isFixed = $row['isFixed'];
            $td = 1;
            if ($row['total_days']) {
                $td = $row['total_days'];
            }
            $payment = $row['payment'];
            $total_hours = round($row['total_hours'], 2);
            $leave_total = $total_hours / $td;

            if ($row['status'] == 'approved') {
                $status = '<i class="bi bi-check-circle-fill"></i>';
            } else if ($row['status'] == 'disapproved') {
                $status = '<i class="bi bi-x-circle-fill text-danger"></i>';
            } else {
                $status = '⏳';
            }

            $duty_total = round($log['hours_worked'], 2);

            $overall_total =  $leave_total + $duty_total;
            $cell .= '<div class="day-row"><div class="day-number">' . $dayNum . '</div><span class="total-hours text-dark">' . $overall_total . ' hrs.</span></div>';

            $l = $this->request->get_leave_type(['code' => $code]);
            $leaveacro = $l['acroname'];
            $leaveTypeText = $l['request'];
            if ($code === 'vacation') {
                $leaveacro = $l['acroname'] . ' ' . $payment;
                $text = '<span>' . ucwords($leaveacro) . ' [' . $leave_total . ']</span>';
                $cell .= '<span id="' . $id . '" class="leave-badge v-' . $payment . '" title="' . ucwords($leaveTypeText) . '">' . $status . ' ' . $text . '</span>';
            } else if ($code !== 'vacation' && $isFixed) {
                $text = '<span>' . ucwords($leaveacro) . ' [' . $leave_total . ']</span>';
                $cell .= '<span id="' . $id . '" class="leave-badge other-leave" title="' . ucwords($leaveTypeText) . '">' . $status . ' ' . $text . '</span>';
            } else {
                // $g = [];
                $cond = "leaveId='$id' AND status!='cancelled' GROUP BY groupId ORDER BY tklrdId ASC";
                $groups = $this->request->getArray('tk_leave_request_details', $cond);
                foreach ($groups as $c) {

                    $gh = round($c['group_hours'], 2);
                    $l = $this->request->get_leave_type(['code' => $c['code']]);
                    $leaveacro = $l['acroname'];
                    $leaveTypeText = $l['request'];

                    $text = '<span>' . ucwords($leaveacro) . ' [' . $gh . ']</span>';

                    if ($c['status'] == 'approved') {
                        $status = '<i class="bi bi-check-circle-fill"></i>';
                    } else if ($c['status'] == 'disapproved') {
                        $status = '<i class="bi bi-x-circle-fill text-danger"></i>';
                    } else {
                        $status = '⏳';
                    }

                    $cell .= '<span id="' . $id . '" class="leave-badge other-leave" title="' . ucwords($leaveTypeText) . '">' . $status . '' . $text . '</span>';
                }
            }

            if ($log['status'] == 'approved') {
                $status = '<i loglass="bi bi-check-circle-fill"></i>';
            } else if ($log['status'] == 'disapproved') {
                $status = '<i class="bi bi-x-circle-fill text-danger"></i>';
            } else {
                $status = '⏳';
            }

            $duty_hours = 0;
            $cond = ['attendance_id' => $log['id'], 'status' => 'approved'];
            $gs = $this->request->getArray('tk_attendance_groups', $cond);
            foreach ($gs as $key => $g) {
                $duty_hours += $g['total_hours'];
            }

            if ($duty_hours > 9) {
                $duty_hours = 9;
            }

            $id = $log['id'];
            $text = '<span>Duty HW [' . round($duty_hours, 2) . ']</span>';
            $cell .= '<span id="duty" class="leave-badge log-inoffice" title="On Duty"><span></span>' . $text . '</span>';
        } else if ($row) {
            $leaveacro = '';
            $leaveTypeText = '';
            $code = $row['code'];
            $id = $row['leaveId'];
            $isFixed = $row['isFixed'];
            $td = 1;
            if ($row['total_days']) {
                $td = $row['total_days'];
            }
            $payment = $row['payment'];
            $total_hours = round($row['total_hours'], 2);
            $tt = $total_hours / $td;

            if ($row['status'] == 'approved') {
                $status = '<i class="bi bi-check-circle-fill"></i>';
            } else if ($row['status'] == 'disapproved') {
                $status = '<i class="bi bi-x-circle-fill text-danger"></i>';
            } else {
                $status = '⏳';
            }

            $l = $this->request->get_leave_type(['code' => $code]);
            $leaveacro = $l['acroname'];
            $leaveTypeText = $l['request'];
            if ($code === 'vacation') {
                $leaveacro = $l['acroname'] . ' ' . $payment;
                $cell .= '<div class="day-row"><div class="day-number">' . $dayNum . '</div><span class="total-hours text-dark">' . $tt . ' hrs.</span></div>';
                $text = '<span>' . ucwords($leaveacro) . ' [' . $tt . ']</span>';
                $cell .= '<span id="' . $id . '" class="leave-badge v-' . $payment . '" title="' . ucwords($leaveTypeText) . '">' . $status . ' ' . $text . '</span>';
            } else if ($code !== 'vacation' && $isFixed) {

                $cell .= '<div class="day-row"><div class="day-number">' . $dayNum . '</div><span class="total-hours text-dark">' . $tt . ' hrs.</span></div>';
                $text = '<span>' . ucwords($leaveacro) . ' [' . $tt . ']</span>';
                $cell .= '<span id="' . $id . '" class="leave-badge other-leave" title="' . ucwords($leaveTypeText) . '">' . $status . ' ' . $text . '</span>';
            } else {
                $g = [];
                $cell .= '<div class="day-row"><div class="day-number">' . $dayNum . '</div><span class="total-hours text-dark">' . $tt . ' hrs.</span></div>';
                $cond = "leaveId='$id' AND status!='cancelled' GROUP BY groupId ORDER BY tklrdId ASC";
                $groups = $this->request->getArray('tk_leave_request_details', $cond);
                foreach ($groups as $c) {

                    $gh = round($c['group_hours'], 2);
                    $l = $this->request->get_leave_type(['code' => $c['code']]);
                    $leaveacro = $l['acroname'];
                    $leaveTypeText = $l['request'];

                    $text = '<span>' . ucwords($leaveacro) . ' [' . $gh . ']</span>';

                    if ($c['status'] == 'approved') {
                        $status = '<i class="bi bi-check-circle-fill"></i>';
                    } else if ($c['status'] == 'disapproved') {
                        $status = '<i class="bi bi-x-circle-fill text-danger"></i>';
                    } else {
                        $status = '⏳';
                    }

                    $cell .= '<span id="' . $id . '" class="leave-badge other-leave" title="' . ucwords($leaveTypeText) . '">' . $status . '' . $text . '</span>';
                }
            }

            foreach ($disp as $d) {
                $d_id = $d['leaveId'];
                if ($d_id == $id) {
                    continue;
                }

                $code = $d['code'];
                $payment = $d['payment'];

                $td = 1;
                if ($d['total_days']) {
                    $td = $d['total_days'];
                }

                $total_hours = round($d['total_hours'], 2);
                $d_tt = $total_hours / $td;

                if ($d['status'] == 'approved') {
                    $status = '<i class="bi bi-check-circle-fill"></i>';
                } else if ($d['status'] == 'disapproved') {
                    $status = '<i class="bi bi-x-circle-fill text-danger"></i>';
                } else {
                    $status = '⏳';
                }

                $l = $this->request->get_leave_type(['code' => $code]);
                $leaveacro = $l['acroname'];
                $leaveTypeText = $l['request'];
                if ($code === 'vacation') {
                    $leaveacro = $l['acroname'] . ' ' . $payment;
                    $badge = 'v-' . $payment;
                } else {
                    $badge = 'other-leave';
                }

                $text = '<span>' . ucwords($leaveacro) . ' [' . $d_tt . ']</span>';
                $cell .= '<span id="' . $d_id . '" class="leave-badge ' . $badge . '" title="' . ucwords($leaveTypeText) . '">' . $status . ' ' . $text . '</span>';
            }
        } else if ($log) {
            if ($log['status'] == 'approved') {
                $status = '<i loglass="bi bi-check-circle-fill"></i>';
            } else if ($log['status'] == 'disapproved') {
                $status = '<i class="bi bi-x-circle-fill text-danger"></i>';
            } else {
                $status = '⏳';
            }

            $g_hours = 0;
            $cond = ['attendance_id' => $log['id'], 'status' => 'approved'];
            $gs = $this->request->getArray('tk_attendance_groups', $cond);
            foreach ($gs as $key => $g) {
                $g_hours += $g['total_hours'];
            }

            if ($g_hours > 9) {
                $g_hours = 9;
            }

            $id = $log['id'];
            $tt = round($log['hours_worked'], 2);

            if ($tt > 9) {
                $tt = 9;
            }
            $text = '<span>Duty HW [' . round($g_hours, 2) . ']</span>';
            $cell .= '<div class="day-row"><div class="day-number">' . $dayNum . '</div><span class="total-hours text-dark">' . $tt . ' hrs.</span></div>';
            $cell .= '<span id="duty" class="leave-badge log-inoffice" title="On Duty"><span></span>' . $text . '</span>';
        } else if ($disp) {
            foreach ($disp as $d) {
                $d_id = $d['leaveId'];
                $code = $d['code'];
                $payment = $d['payment'];

                $td = 1;
                if ($d['total_days']) {
                    $td = $d['total_days'];
                }

                $total_hours = round($d['total_hours'], 2);
                $d_tt = $total_hours / $td;

                if ($d['status'] == 'approved') {
                    $status = '<i class="bi bi-check-circle-fill"></i>';
                } else if ($d['status'] == 'disapproved') {
                    $status = '<i class="bi bi-x-circle-fill text-danger"></i>';
                } else {
                    $status = '⏳';
                }

                $l = $this->request->get_leave_type(['code' => $code]);
                $leaveacro = $l['acroname'];
                $leaveTypeText = $l['request'];
                if ($code === 'vacation') {
                    $leaveacro = $l['acroname'] . ' ' . $payment;
                    $badge = 'v-' . $payment;
                } else {
                    $badge = 'other-leave';
                }
                $cell .= '<div class="day-row"><div class="day-number">' . $dayNum . '</div><span class="total-hours text-dark">' . $d_tt . ' hrs.</span></div>';
                $text = '<span>' . ucwords($leaveacro) . ' [' . $d_tt . ']</span>';
                $cell .= '<span id="' . $d_id . '" class="leave-badge ' . $badge . '" title="' . ucwords($leaveTypeText) . '">' . $status . ' ' . $text . '</span>';
            }
        } else if ($hol) {
            $cell .= '<div class="day-number">' . $dayNum . '</div>';
            $cell .= '<div class="holiday-label mt-3">' . $hol['holiday'] . '</div>';
        } else {
            $cell .= '<div class="day-number">' . $dayNum . '</div>';
        }

        $old_p = $this->request->getRow('tk_change_dayoff', ['emp_id' => $empid, 'old_dayoff' => $date, 'status' => 'pending']);
        $new_p = $this->request->getRow('tk_change_dayoff', ['emp_id' => $empid, 'new_dayoff' => $date, 'status' => 'pending']);
        $cond = "emp_id='$empid' AND old_dayoff='$date' AND status='approved' ORDER BY tkcdoId DESC";
        $old_a = $this->request->getRow('tk_change_dayoff', $cond);
        $cond = "emp_id='$empid' AND new_dayoff='$date' AND status='approved' ORDER BY tkcdoId DESC";
        $new_a = $this->request->getRow('tk_change_dayoff', $cond);

        if ($isWeekend) {
            if ($old_p) {
                $status = '<span>⏳</span>';
                $cell .= '<div class="day-off-label mt-3">Day Off</div>';
                $cell .= '<span class="dayoff-badge dayoff-pending" title="">' . $status . ' CDO</span>';
            } else if ($new_p) {
                $status = '<span>⏳</span>';
                $cell .= '<span class="dayoff-badge dayoff-pending" title="">' . $status . ' CDO</span>';
            } else if ($new_a) {
                if ($old_a) {
                    if ($new_a['tkcdoId'] > $old_a['tkcdoId']) {
                        $cell .= '<div class="day-off-label mt-3">Day Off</div>';
                    }
                } else {
                    $cell .= '<div class="day-off-label mt-3">Day Off</div>';
                }
            } else if ($old_a) {
                if ($new_a) {
                    if ($old_a['tkcdoId'] < $new_a['tkcdoId']) {
                        $cell .= '<div class="day-off-label mt-3">Day Off</div>';
                    }
                } else {
                    $cell .= '';
                }
            } else {
                $cell .= '<div class="day-off-label mt-3">Day Off</div>';
            }
        } else {

            if ($old_p) {
                $status = '<span>⏳</span>';
                $cell .= '<div class="day-off-label mt-3">Day Off</div>';
                $cell .= '<span class="dayoff-badge dayoff-pending" title="">' . $status . ' CDO</span>';
            } else if ($new_p) {
                $status = '<span>⏳</span>';
                $cell .= '<span class="dayoff-badge dayoff-pending" title="">' . $status . ' CDO</span>';
            } else if ($new_a) {
                if ($old_a) {
                    if ($new_a['tkcdoId'] > $old_a['tkcdoId']) {
                        $cell .= '<div class="day-off-label mt-3">Day Off</div>';
                    }
                } else {
                    $cell .= '<div class="day-off-label mt-3">Day Off</div>';
                }
            } else if ($old_a) {
                if ($new_a) {
                    if ($old_a['tkcdoId'] < $new_a['tkcdoId']) {
                        $cell .= '<div class="day-off-label mt-3">Day Off</div>';
                    } else {
                        $cell .= '';
                    }
                } else {
                    $cell .= '';
                }
            } else {
                $cell .= '';
            }
        }

        $cell .= '</div>';
        return $cell;
    }

    public function get_week_totals()
    {
        $start = $this->input->post('start');
        $end   = $this->input->post('end');

        $cond = ['render_type' => 'duty', 'limit_type' => 'maximum', 'limit_duration' => 'daily'];
        $dutyMax = $this->request->getRow('tk_render_limits', $cond)['hours_rendered'];

        $cond = ['render_type' => 'duty', 'limit_type' => 'minimum', 'limit_duration' => 'weekly'];
        $dutyWeekMin = $this->request->getRow('tk_render_limits', $cond)['hours_rendered'];

        $total_duty_hours = 0;
        $total_leave_hours = 0;

        $total_week_hours = 0;
        $cond = "emp_id='$this->userId' AND (date BETWEEN '$start' AND '$end')";
        $dates = $this->request->getArray('tk_leave_request_dates', $cond);
        foreach ($dates as $key => $v) {
            $leaveId = $v['leaveId'];

            $cond = ['leaveId' => $leaveId, 'isFixed' => 0];
            $l = $this->request->getRow('tk_leave_requests', $cond);
            if ($l) {

                $cond = "leaveId='$leaveId' AND status='approved' GROUP BY groupId";
                $lrd = $this->request->getArray('tk_leave_request_details', $cond);
                foreach ($lrd as $ld) {
                    $total_leave_hours += round($ld['group_hours'], 2);
                }
            }

            $cond = ['leaveId' => $leaveId, 'status' => 'approved', 'isFixed' => 1];
            $l = $this->request->getRow('tk_leave_requests', $cond);
            if ($l) {
                if ($l['code'] == 'vacation' && $l['payment'] == 'unpaid') {
                    continue;
                }
                $hours = $l['total_hours'];
                $days = $l['total_days'];
                $total_leave_hours += round($hours / $days, 2);
            }
        }

        $cond = "emp_id='$this->userId' AND (attendance_date BETWEEN '$start' AND '$end')";
        $logs = $this->request->getArray('tk_attendance_records', $cond);
        foreach ($logs as $log) {

            $total_day_duty_hours = 0;
            $cond = ['attendance_id' => $log['id'], 'status' => 'approved'];
            $groups = $this->request->getArray('tk_attendance_groups', $cond);
            foreach ($groups as $g) {
                $total_day_duty_hours += round($g['total_hours'], 2);
            }

            if ($total_day_duty_hours > $dutyMax) {
                $total_duty_hours += $dutyMax;
            } else {
                $total_duty_hours += $total_day_duty_hours;
            }
        }

        $total_week_hours = $total_duty_hours + $total_leave_hours;
        if ($total_week_hours > $dutyWeekMin) {
            $total_deficit_hours = 0;
        } else {
            $total_deficit_hours = round($dutyWeekMin - $total_week_hours, 2);
        }

        $data = [
            'total_duty_hours' => $total_duty_hours,
            'total_leave_hours' => $total_leave_hours,
            'total_week_hours' => $total_week_hours,
            'total_deficit_hours' => $total_deficit_hours
        ];
        echo json_encode($data);
    }

    public function save_leave()
    {
        $input = $this->input->post();
        $data = json_decode($input['payload'], true);
        $empid = $this->session->userdata('empid');
        // var_dump($data);
        $mergedAttachments = [];
        if (!empty($_FILES['merged']['name'])) {
            foreach ($_FILES['merged']['name'] as $rowId => $names) {

                for ($i = 0; $i < count($names); $i++) {
                    if (empty($names[$i])) continue;

                    $_FILES['file']['name']     = $_FILES['merged']['name'][$rowId][$i];
                    $_FILES['file']['type']     = $_FILES['merged']['type'][$rowId][$i];
                    $_FILES['file']['tmp_name'] = $_FILES['merged']['tmp_name'][$rowId][$i];
                    $_FILES['file']['error']    = $_FILES['merged']['error'][$rowId][$i];
                    $_FILES['file']['size']     = $_FILES['merged']['size'][$rowId][$i];

                    $ext     = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION));
                    $newName =  $empid . '_' . date('Ymd') . '_' . $rowId . '_' . $i  . '.' . $ext;

                    $config = [
                        'upload_path'   => './uploads/leaves/',
                        'allowed_types' => 'pdf|jpg|jpeg|png|doc|docx',
                        'max_size'      => 2048,
                        'file_name'     => $newName,
                        'overwrite'     => true,
                    ];
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('file')) {
                        $mergedAttachments[$rowId][] = $this->upload->data('file_name');
                    }
                }
            }
        }

        $fixedAttachments = [];
        if (!empty($_FILES['fixedattachment']['name'][0])) {

            $files = $_FILES['fixedattachment'];
            for ($i = 0; $i < count($files['name']); $i++) {
                $_FILES['file']['name']     = $files['name'][$i];
                $_FILES['file']['type']     = $files['type'][$i];
                $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['file']['error']    = $files['error'][$i];
                $_FILES['file']['size']     = $files['size'][$i];

                $ext     = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                $newName = $empid . '_' . date('YmdHis') . '_' . $i . '.' . $ext;

                $config = [
                    'upload_path'   => './uploads/leaves/',
                    'allowed_types' => 'pdf|jpg|jpeg|png|doc|docx',
                    'max_size'      => 2048,
                    'file_name'     => $newName,
                    'overwrite'     => true,
                ];
                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if ($this->upload->do_upload('file')) {
                    $fixedAttachments[] = $this->upload->data('file_name');
                }
            }
        }

        $type           = $data['type'];
        $created        = date('Y-m-d H:i:s');
        $typeId         = $this->request->get_typeId($type);
        $fixed_hour     = $this->request->get_fixed_hour($type);

        $isTimeRange = false;
        if (isset($data['isTimeRange'])) {
            $isTimeRange = ($data['isTimeRange']) ? true : false;
        }

        if ($type === 'maternity') {

            $mat = $data['matPatData'];
            $matDays = $mat['days'];
            $deliveryType = $mat['deliveryType'];
            $dateBackToWork = $mat['dateBackToWork'];
            $matDetails = $mat['details'];

            $daysByYear = [];
            $startDate = $mat['dateStart'];
            $endDate   = $mat['dateEnd'];
            $start = new DateTime($startDate);
            $end   = new DateTime($endDate);

            while ($start <= $end) {
                $dateStr   = $start->format('Y-m-d');
                $year      = $start->format('Y');

                $leave = [];
                $cond = ['emp_id' => $empid, 'date' => $dateStr];
                $check = $this->request->getRow('tk_leave_request_dates', $cond);
                if ($check) {
                    $cond = "leaveId='$check[leaveId]' AND (status='approved' OR status='pending')";
                    $leave = $this->request->getRow('tk_leave_requests', $cond);
                }

                if ($leave) {
                    $leaveStatus = ucwords($leave['status']);
                    $cond = ['code' => $leave['code']];
                    $request = $this->request->getRow('tk_request_type', $cond)['request'];
                    echo json_encode(['message' => "You have a $leaveStatus $request request for $dateStr that conflicts with this requested date range."]);
                    return;
                }

                if (!isset($daysByYear[$year])) {
                    $daysByYear[$year] = [
                        'count' => 0,
                        'dates' => [],
                    ];
                }
                $daysByYear[$year]['count']++;
                $daysByYear[$year]['dates'][] = $dateStr;

                $start->modify('+1 day');
            }

            $save = [];
            $save['emp_id']         = $empid;
            $save['typeId']         = $typeId;
            $save['code']           = $type;
            $save['startDate']      = $mat['dateStart'];
            $save['endDate']        = $mat['dateEnd'];
            $save['reason']         = $matDetails;
            $save['isFixed']        = true;
            $save['total_days']     = $matDays;
            $save['total_hours']    = $matDays * $fixed_hour;

            $id = ($data['id']) ? $data['id'] : '';
            if ($id) {
                $save['updated_geo_location'] = $data['fixedgeoTagLocation'];
                $save['updated_at'] = $created;
                $update = $this->request->update_table('tk_leave_requests', $save, ['leaveId' => $id]);
                $leaveId = $id;

                $matData = [
                    'leaveId'       => $leaveId,
                    'emp_id'        => $empid,
                    'delivery_type' => $deliveryType,
                    'startDate'     => $startDate,
                    'endDate'       => $endDate,
                    'total_days'    => $matDays,
                    'total_hours'   => $matDays * $fixed_hour,
                    'backtowork'    => $dateBackToWork,
                    'details'       => $matDetails,
                    'status'        => 'pending',
                ];
                $update = $this->request->update_table('tk_maternity_leave_details', $matData, ['leaveId' => $id]);
            } else {
                $save['status']         = 'pending';
                $save['geo_location']   = $data['fixedgeoTagLocation'];
                $save['created_at']     = $created;
                $leaveId = $this->request->insert('tk_leave_requests', $save);
                $matData = [
                    'leaveId'       => $leaveId,
                    'emp_id'        => $empid,
                    'delivery_type' => $deliveryType,
                    'startDate'     => $startDate,
                    'endDate'       => $endDate,
                    'total_days'    => $matDays,
                    'total_hours'   => $matDays * $fixed_hour,
                    'backtowork'    => $dateBackToWork,
                    'details'       => $matDetails,
                    'status'        => 'pending',
                    'created'       => $created
                ];
                $mat = $this->request->insert('tk_maternity_leave_details', $matData);
            }

            foreach ($daysByYear as $year => $info) {

                $days = $info['count'];
                $dates = $info['dates'];
                foreach ($dates as $date) {
                    $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $date];
                    $this->request->delete_table('tk_leave_request_dates', $cond);

                    $d = [
                        'leaveId'   => $leaveId,
                        'emp_id'    => $empid,
                        'code'      => $type,
                        'year'      => $year,
                        'date'      => $date,
                        'status'    => 'done',
                        'created'   => $created
                    ];
                    $this->request->insert('tk_leave_request_dates', $d);
                }
            }

            foreach ($daysByYear as $year => $info) {
                $this->count_vl_balance($empid, $year);
            }

            echo json_encode(['message' => 'success', 'data' => array_merge($save, ['leaveId' => $leaveId])]);
            return;
        } else if ($type === 'paternity') {
            $pat = $data['matPatData'];
            $patDays = $pat['days'];
            $application = $pat['application'];
            $deliveryOnOrBefore = $pat['deliveryOnOrBefore'];
            $patDetails = $pat['details'];

            $daysByYear = [];
            $startDate = $pat['dateStart'];
            $endDate   = $pat['dateEnd'];
            $start = new DateTime($startDate);
            $end   = new DateTime($endDate);

            while ($start <= $end) {
                $dateStr   = $start->format('Y-m-d');
                $year      = $start->format('Y');

                $leave = [];
                $cond = ['emp_id' => $empid, 'date' => $dateStr];
                $check = $this->request->getRow('tk_leave_request_dates', $cond);
                if ($check) {
                    $cond = "leaveId='$check[leaveId]' AND (status='approved' OR status='pending')";
                    $leave = $this->request->getRow('tk_leave_requests', $cond);
                }

                if ($leave) {
                    $leaveStatus = ucwords($leave['status']);
                    $cond = ['code' => $leave['code']];
                    $request = $this->request->getRow('tk_request_type', $cond)['request'];
                    echo json_encode(['message' => "You have a $leaveStatus $request request for $dateStr that conflicts with this requested date range."]);
                    return;
                }

                if (!isset($daysByYear[$year])) {
                    $daysByYear[$year] = [
                        'count' => 0,
                        'dates' => [],
                    ];
                }
                $daysByYear[$year]['count']++;
                $daysByYear[$year]['dates'][] = $dateStr;

                $start->modify('+1 day');
            }

            $total_days = 0;
            foreach ($daysByYear as $year => $info) {
                $days = $info['count'];
                $total_days += $days;
            }

            $save = [];
            $save['emp_id']         = $empid;
            $save['typeId']         = $typeId;
            $save['code']           = $type;
            $save['startDate']      = $pat['dateStart'];
            $save['endDate']        = $pat['dateEnd'];
            $save['reason']         = $patDetails;
            $save['isFixed']        = true;
            $save['total_days']     = $total_days;
            $save['total_hours']    = $total_days * $fixed_hour;

            $id = ($data['id']) ? $data['id'] : '';
            if ($id) {
                $save['updated_geo_location'] = $data['fixedgeoTagLocation'];
                $save['updated_at'] = $created;
                $update = $this->request->update_table('tk_leave_requests', $save, ['leaveId' => $id]);
                $leaveId = $id;
                $patData = [
                    'leaveId'       => $leaveId,
                    'emp_id'        => $empid,
                    'delivery_date' => $deliveryOnOrBefore,
                    'application'   => $application,
                    'startDate'     => $pat['dateStart'],
                    'endDate'       => $pat['dateEnd'],
                    'total_days'    => $total_days,
                    'total_hours'   => $total_days * $fixed_hour,
                    'details'       => $patDetails,
                    'status'        => 'pending',
                ];
                $update = $this->request->update_table('tk_paternity_leave_details', $patData, ['leaveId' => $id]);
            } else {
                $save['status']         = 'pending';
                $save['geo_location']   = $data['fixedgeoTagLocation'];
                $save['created_at']     = $created;
                $leaveId = $this->request->insert('tk_leave_requests', $save);
                $patData = [
                    'leaveId'       => $leaveId,
                    'emp_id'        => $empid,
                    'delivery_date' => $deliveryOnOrBefore,
                    'application'   => $application,
                    'startDate'     => $pat['dateStart'],
                    'endDate'       => $pat['dateEnd'],
                    'total_days'    => $total_days,
                    'total_hours'   => $total_days * $fixed_hour,
                    'details'       => $patDetails,
                    'status'        => 'pending',
                    'created'       => $created
                ];
                $pat = $this->request->insert('tk_paternity_leave_details', $patData);
            }

            foreach ($daysByYear as $year => $info) {

                $days = $info['count'];
                $dates = $info['dates'];
                foreach ($dates as $date) {
                    $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $date];
                    $this->request->delete_table('tk_leave_request_dates', $cond);

                    $d = [
                        'leaveId'   => $leaveId,
                        'emp_id'    => $empid,
                        'code'      => $type,
                        'year'      => $year,
                        'date'      => $date,
                        'status'    => 'done',
                        'created'   => $created
                    ];
                    $this->request->insert('tk_leave_request_dates', $d);
                }
            }

            foreach ($daysByYear as $year => $info) {
                $this->count_vl_balance($empid, $year);
            }

            echo json_encode(['message' => 'success', 'data' => array_merge($save, ['leaveId' => $leaveId])]);
            return;
        } else if ($type === 'vacation') {

            $daysByYear = [];
            $startDate = $data['startDate'];
            $endDate   = $data['endDate'];
            $start = new DateTime($startDate);
            $end   = new DateTime($endDate);

            while ($start <= $end) {
                $dateStr   = $start->format('Y-m-d');
                $year      = $start->format('Y');

                $dayOfWeek = $start->format('N');

                $cond = ['date' => $dateStr, 'date_change' => '0000-00-00'];
                $hol = $this->request->getRow('tk_holidays', $cond);
                if (!$hol) {
                    $hol = $this->request->getRow('tk_holidays', ['date_change' => $dateStr]);
                }

                $cond = ['emp_id' => $empid, 'attendance_date' => $dateStr];
                $duty = $this->request->getRow('tk_attendance_records', $cond);

                $leave = [];
                $cond = ['emp_id' => $empid, 'date' => $dateStr];
                $check = $this->request->getRow('tk_leave_request_dates', $cond);
                if ($check) {
                    if (!$data['id']) {
                        $cond = "leaveId='$check[leaveId]' AND (status='approved' OR status='pending')";
                        $leave = $this->request->getRow('tk_leave_requests', $cond);
                    }
                }

                $cond = "emp_id='$empid' AND old_dayoff='$dateStr' AND status='approved' ORDER BY tkcdoId DESC";
                $old_a = $this->request->getRow('tk_change_dayoff', $cond);
                $cond = "emp_id='$empid' AND new_dayoff='$dateStr' AND status='approved' ORDER BY tkcdoId DESC";
                $new_a = $this->request->getRow('tk_change_dayoff', $cond);

                $count = false;
                if (!$hol && !$leave && !$duty) {

                    if ($dayOfWeek != 7) {
                        if ($old_a && $new_a) {
                            if ($old_a['tkcdoId'] > $new_a['tkcdoId']) {
                                $count = true;
                            }
                        } else if ($old_a) {
                            $count = true;
                        } else {
                            if (!$new_a) {
                                $count = true;
                            }
                        }
                    }

                    if ($dayOfWeek == 7) {
                        if ($old_a && $new_a) {
                            if ($old_a['tkcdoId'] > $new_a['tkcdoId']) {
                                $count = true;
                            }
                        } else if ($old_a) {
                            $count = true;
                        } else {
                            $count = false;
                        }
                    }
                }

                if ($count) {
                    if (!isset($daysByYear[$year])) {
                        $daysByYear[$year] = [
                            'count' => 0,
                            'dates' => [],
                        ];
                    }
                    $daysByYear[$year]['count']++;
                    $daysByYear[$year]['dates'][] = $dateStr;
                }

                $start->modify('+1 day');
            }

            $total_days = 0;
            foreach ($daysByYear as $year => $info) {

                $days = $info['count'];
                $dates = $info['dates'];
                $total_days += $days;

                $vl = $this->generate_vl_year($empid, $year);
                if ($vl) {
                    $cond = ['emp_id' => $empid, 'year' => $year];
                    $check_bal = $this->request->getRow('tk_vacation_leave_balance', $cond);
                    if ($data['vpayment'] === 'paid') {
                        if ($check_bal['paidDaysRemaining'] < $days) {
                            $id = ($data['id']) ? $data['id'] : '';
                            if ($id) {
                                $cond = ['leaveId' => $id, 'emp_id' => $empid, 'year' => $year];
                                $check_dates = $this->request->getArray('tk_leave_request_dates', $cond);

                                $t = $check_bal['paidDaysRemaining'] + count($check_dates);
                                if ($t < $days) {
                                    echo json_encode(['message' => "$year Vacation Leave balance for paid leave $check_bal[paidDaysRemaining] day(s) is not sufficient for your request of $days days(s)."]);
                                    return;
                                }
                            } else {
                                echo json_encode(['message' => "$year Vacation Leave balance for paid leave $check_bal[paidDaysRemaining] day(s) is not sufficient for your request of $days days(s)."]);
                                return;
                            }
                        }
                    } else {
                        if ($check_bal['unpaidDaysRemaining'] < $days) {
                            $id = ($data['id']) ? $data['id'] : '';
                            if ($id) {
                                $cond = ['leaveId' => $id, 'emp_id' => $empid, 'year' => $year];
                                $check_dates = $this->request->getArray('tk_leave_request_dates', $cond);

                                $t = $check_bal['unpaidDaysRemaining'] + count($check_dates);
                                if ($t < $days) {
                                    echo json_encode(['message' => "$year Vacation Leave balance for unpaid leave $check_bal[unpaidDaysRemaining] day(s) is not sufficient for your request of $days days(s)."]);
                                    return;
                                }
                            } else {
                                echo json_encode(['message' => "$year Vacation Leave balance for unpaid leave $check_bal[unpaidDaysRemaining] day(s) is not sufficient for your request of $days days(s)."]);
                                return;
                            }
                        }
                    }
                }
            }

            if (!$total_days) {
                echo json_encode(['message' => 'Date(s) selected have active leave requests.']);
                return;
            }

            $save = [];
            $save['emp_id']         = $empid;
            $save['typeId']         = $typeId;
            $save['code']           = $type;
            $save['startDate']      = $data['startDate'];
            $save['endDate']        = $data['endDate'];
            $save['reason']         = $data['notes'];
            $save['payment']        = $data['vpayment'];
            $save['isFixed']        = true;
            $save['total_days']     = $total_days;
            $save['total_hours'] = $total_days * $fixed_hour;

            $id = ($data['id']) ? $data['id'] : '';
            if ($id) {
                $save['updated_geo_location'] = $data['fixedgeoTagLocation'];
                $save['updated_at'] = $created;
                $update = $this->request->update_table('tk_leave_requests', $save, ['leaveId' => $id]);
                $leaveId = $id;
            } else {
                $save['status']         = 'pending';
                $save['geo_location']   = $data['fixedgeoTagLocation'];
                $save['created_at']     = $created;
                $leaveId = $this->request->insert('tk_leave_requests', $save);
            }

            foreach ($daysByYear as $year => $info) {

                $days = $info['count'];
                $dates = $info['dates'];
                foreach ($dates as $date) {
                    $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $date];
                    $chk = $this->request->getRow('tk_leave_request_dates', $cond);
                    if ($chk) {
                        $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $date];
                        $d = ['code' => $type, 'payment' => $data['vpayment']];
                        $this->request->update_table('tk_leave_request_dates', $d, $cond);
                    } else {
                        $d = [
                            'leaveId'   => $leaveId,
                            'emp_id'    => $empid,
                            'code'      => $type,
                            'year'      => $year,
                            'date'      => $date,
                            'payment'   => $data['vpayment'],
                            'status'    => 'done',
                            'created'   => $created
                        ];
                        $this->request->insert('tk_leave_request_dates', $d);
                    }
                }

                $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'year' => $year];
                $check_dates = $this->request->getArray('tk_leave_request_dates', $cond);
                foreach ($check_dates as $d) {
                    if (!in_array($d['date'], $dates)) {
                        $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $d['date']];
                        $this->request->delete_table('tk_leave_request_dates', $cond);
                    }
                }
            }

            foreach ($daysByYear as $year => $info) {
                $this->count_vl_balance($empid, $year);
            }

            if ($fixedAttachments) {
                foreach ($fixedAttachments as $attachment) {
                    $upload = [
                        'leaveId' => $leaveId,
                        'emp_id'   => $empid,
                        'attachment' => $attachment,
                        'isFixed'   => 1,
                        'status'   => 'active',
                        'created_at' => date('Y-m-d H:i:s'),
                    ];

                    $this->request->insert('tk_leave_request_attachments', $upload);
                }
            }

            echo json_encode(['message' => 'success', 'data' => array_merge($save, ['leaveId' => $leaveId])]);
            return;
        } else if ($type !== 'vacation' && !$isTimeRange) {

            $daysByYear = [];
            $startDate = $data['startDate'];
            $endDate   = $data['endDate'];
            $start = new DateTime($startDate);
            $end   = new DateTime($endDate);

            while ($start <= $end) {
                $dateStr   = $start->format('Y-m-d');
                $year      = $start->format('Y');

                $dayOfWeek = $start->format('N');

                $cond = ['date' => $dateStr, 'date_change' => '0000-00-00'];
                $hol = $this->request->getRow('tk_holidays', $cond);
                if (!$hol) {
                    $hol = $this->request->getRow('tk_holidays', ['date_change' => $dateStr]);
                }

                $cond = ['emp_id' => $empid, 'attendance_date' => $dateStr];
                $duty = $this->request->getRow('tk_attendance_records', $cond);

                $leave = [];
                $cond = ['emp_id' => $empid, 'date' => $dateStr];
                $check = $this->request->getRow('tk_leave_request_dates', $cond);
                if ($check) {
                    if (!$data['id']) {
                        $cond = "leaveId='$check[leaveId]' AND (status='approved' OR status='pending')";
                        $leave = $this->request->getRow('tk_leave_requests', $cond);
                    }
                }

                $cond = "emp_id='$empid' AND old_dayoff='$dateStr' AND status='approved' ORDER BY tkcdoId DESC";
                $old_a = $this->request->getRow('tk_change_dayoff', $cond);
                $cond = "emp_id='$empid' AND new_dayoff='$dateStr' AND status='approved' ORDER BY tkcdoId DESC";
                $new_a = $this->request->getRow('tk_change_dayoff', $cond);

                $count = false;
                if (!$hol && !$leave && !$duty) {

                    if ($dayOfWeek != 7) {
                        if ($old_a && $new_a) {
                            if ($old_a['tkcdoId'] > $new_a['tkcdoId']) {
                                $count = true;
                            }
                        } else if ($old_a) {
                            $count = true;
                        } else {
                            if (!$new_a) {
                                $count = true;
                            }
                        }
                    }

                    if ($dayOfWeek == 7) {
                        if ($old_a && $new_a) {
                            if ($old_a['tkcdoId'] > $new_a['tkcdoId']) {
                                $count = true;
                            }
                        } else if ($old_a) {
                            $count = true;
                        } else {
                            $count = false;
                        }
                    }
                }

                if ($count) {
                    if (!isset($daysByYear[$year])) {
                        $daysByYear[$year] = [
                            'count' => 0,
                            'dates' => [],
                        ];
                    }
                    $daysByYear[$year]['count']++;
                    $daysByYear[$year]['dates'][] = $dateStr;
                }

                $start->modify('+1 day');
            }

            $total_days = 0;
            foreach ($daysByYear as $year => $info) {
                $days = $info['count'];
                $total_days += $days;
            }

            if (!$total_days) {
                echo json_encode(['message' => 'Date(s) selected have active leave requests.']);
                return;
            }

            $save = [];
            $save['emp_id']         = $empid;
            $save['typeId']         = $typeId;
            $save['code']           = $type;
            $save['startDate']      = $data['startDate'];
            $save['endDate']        = $data['endDate'];
            $save['reason']         = $data['notes'];
            $save['isFixed']        = true;
            $save['total_days']     = $total_days;
            $save['total_hours']    = $total_days * $fixed_hour;

            $id = ($data['id']) ? $data['id'] : '';
            if ($id) {
                $save['updated_geo_location'] = $data['fixedgeoTagLocation'];
                $save['updated_at'] = $created;
                $update = $this->request->update_table('tk_leave_requests', $save, ['leaveId' => $id]);
                $leaveId = $id;
            } else {
                $save['status']         = 'pending';
                $save['geo_location']   = $data['fixedgeoTagLocation'];
                $save['created_at']     = $created;
                $leaveId = $this->request->insert('tk_leave_requests', $save);
            }

            if ($type === 'medical') {
                $cond = ['leaveId' => $leaveId, 'emp_id' => $empid];
                $this->request->delete_table('tk_leave_request_assisted_persons', $cond);

                $d = [
                    'leaveId'           => $leaveId,
                    'emp_id'            => $empid,
                    'assisted_person'   => $data['assistedPerson'],
                    'isFixed'           => 1,
                    'status'            => 'active',
                    'created'           => $created
                ];
                $this->request->insert('tk_leave_request_assisted_persons', $d);
            }

            foreach ($daysByYear as $year => $info) {

                $days = $info['count'];
                $dates = $info['dates'];
                foreach ($dates as $date) {
                    $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $date];
                    $chk = $this->request->getRow('tk_leave_request_dates', $cond);
                    if ($chk) {
                        $d = ['code' => $type, 'payment' => ''];
                        $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $date];
                        $this->request->update_table('tk_leave_request_dates', $d, $cond);
                    } else {
                        $d = [
                            'leaveId'   => $leaveId,
                            'emp_id'    => $empid,
                            'code'      => $type,
                            'year'      => $year,
                            'date'      => $date,
                            'status'    => 'done',
                            'created'   => $created
                        ];
                        $this->request->insert('tk_leave_request_dates', $d);
                    }
                }

                $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'year' => $year];
                $check_dates = $this->request->getArray('tk_leave_request_dates', $cond);
                foreach ($check_dates as $d) {
                    if (!in_array($d['date'], $dates)) {
                        $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $d['date']];
                        $this->request->delete_table('tk_leave_request_dates', $cond);
                    }
                }
            }

            foreach ($daysByYear as $year => $info) {
                $this->count_vl_balance($empid, $year);
            }

            if ($fixedAttachments) {
                foreach ($fixedAttachments as $attachment) {
                    $upload = [
                        'leaveId' => $leaveId,
                        'emp_id'   => $empid,
                        'attachment' => $attachment,
                        'isFixed'   => 1,
                        'status'   => 'active',
                        'created_at' => date('Y-m-d H:i:s'),
                    ];

                    $this->request->insert('tk_leave_request_attachments', $upload);
                }
            }

            echo json_encode(['message' => 'success', 'data' => array_merge($save, ['leaveId' => $leaveId])]);
            return;
        } else {

            $daysByYear = [];
            $startDate = $data['startDate'];
            $endDate   = $data['endDate'];
            $start = new DateTime($startDate);
            $end   = new DateTime($endDate);

            while ($start <= $end) {
                $dateStr   = $start->format('Y-m-d');
                $year      = $start->format('Y');

                $dayOfWeek = $start->format('N');

                $cond = ['date' => $dateStr, 'date_change' => '0000-00-00'];
                $hol = $this->request->getRow('tk_holidays', $cond);
                if (!$hol) {
                    $hol = $this->request->getRow('tk_holidays', ['date_change' => $dateStr]);
                }

                $leave = [];
                $cond = ['emp_id' => $empid, 'date' => $dateStr];
                $check = $this->request->getRow('tk_leave_request_dates', $cond);
                if ($check) {
                    $cond = "leaveId='$check[leaveId]' AND isFixed='1' AND (status='approved' OR status='pending')";
                    $leave = $this->request->getRow('tk_leave_requests', $cond);
                }

                $cond = "emp_id='$empid' AND old_dayoff='$dateStr' AND status='approved' ORDER BY tkcdoId DESC";
                $old_a = $this->request->getRow('tk_change_dayoff', $cond);
                $cond = "emp_id='$empid' AND new_dayoff='$dateStr' AND status='approved' ORDER BY tkcdoId DESC";
                $new_a = $this->request->getRow('tk_change_dayoff', $cond);

                $count = false;
                if (!$hol && !$leave) {
                    if ($dayOfWeek != 7) {
                        if ($old_a && $new_a) {
                            if ($old_a['tkcdoId'] > $new_a['tkcdoId']) {
                                $count = true;
                            }
                        } else if ($old_a) {
                            $count = true;
                        } else {
                            if (!$new_a) {
                                $count = true;
                            }
                        }
                    }

                    if ($dayOfWeek == 7) {
                        if ($old_a && $new_a) {
                            if ($old_a['tkcdoId'] > $new_a['tkcdoId']) {
                                $count = true;
                            }
                        } else if ($old_a) {
                            $count = true;
                        } else {
                            $count = false;
                        }
                    }
                }

                if ($count) {
                    if (!isset($daysByYear[$year])) {
                        $daysByYear[$year] = ['count' => 0, 'dates' => []];
                    }
                    $daysByYear[$year]['count']++;
                    $daysByYear[$year]['dates'][] = $dateStr;
                }

                $start->modify('+1 day');
            }

            $total_days = 0;
            foreach ($daysByYear as $year => $info) {
                $days = $info['count'];
                $total_days += $days;
            }

            if (!$total_days) {
                echo json_encode(['message' => 'Date(s) selected have active leave requests.']);
                return;
            }

            $totalMinutes = 0;
            for ($i = 8; $i <= 18; $i++) {
                if ($data['ischk' . $i]) {
                    $t1 = $data['t_' . $i . '_1'];
                    $t2 = $data['t_' . $i . '_2'];

                    if ($t1 === '' || $t2 === '') continue;

                    // start: if minutes are NOT 00, subtract 1 minute
                    $minS = (int)date('i', strtotime($t1));
                    if ($minS !== 0) {
                        $t1 = date('H:i', strtotime($t1) - 60);
                    }

                    // end: if minutes are 59, add 1 minute
                    $minE = (int)date('i', strtotime($t2));
                    if ($minE === 59) {
                        $t2 = date('H:i', strtotime($t2) + 60);
                    }

                    $startM = (int)date('G', strtotime($t1)) * 60 + (int)date('i', strtotime($t1));
                    $endM   = (int)date('G', strtotime($t2)) * 60 + (int)date('i', strtotime($t2));

                    $diff = $endM - $startM;
                    if ($diff < 0) $diff += 24 * 60;

                    $totalMinutes += $diff;
                }

                $s = 1;
                while (isset($data['ischk' . $i . 's' . $s])) {
                    if ($data['ischk' . $i . 's' . $s]) {
                        $st1 = $data['t_' . $i . 's' . $s . '_1'];
                        $st2 = $data['t_' . $i . 's' . $s . '_2'];

                        if ($st1 !== '' && $st2 !== '') {

                            // start: if minutes are NOT 00, subtract 1 minute
                            $minSt1 = (int)date('i', strtotime($st1));
                            if ($minSt1 !== 0) {
                                $st1 = date('H:i', strtotime($st1) - 60);
                            }

                            // end: if minutes are 59, add 1 minute
                            $minSt2 = (int)date('i', strtotime($st2));
                            if ($minSt2 === 59) {
                                $st2 = date('H:i', strtotime($st2) + 60);
                            }

                            $sStartM = (int)date('G', strtotime($st1)) * 60 + (int)date('i', strtotime($st1));
                            $sEndM   = (int)date('G', strtotime($st2)) * 60 + (int)date('i', strtotime($st2));

                            $sDiff = $sEndM - $sStartM;
                            if ($sDiff < 0) $sDiff += 24 * 60;

                            $totalMinutes += $sDiff;
                        }
                    }
                    $s++;
                }
            }

            $total_hours = round($totalMinutes / 60, 2);

            $rowchk = [];
            $gs = $data['groupStructure'];
            $mergedData = $data['mergedData'];
            foreach ($mergedData as $i => $md) {
                $row_hours = 0;
                $gId = $md['groupId'];
                if ($data['ischk' . $i]) {
                    $t1 = $data['t_' . $i . '_1'];
                    $t2 = $data['t_' . $i . '_2'];

                    if ($t1 === '' || $t2 === '') continue;

                    // start: if minutes are NOT 00, subtract 1 minute
                    $minS = (int)date('i', strtotime($t1));
                    if ($minS !== 0) {
                        $t1 = date('H:i', strtotime($t1) - 60);
                    }

                    // end: if minutes are 59, add 1 minute
                    $minE = (int)date('i', strtotime($t2));
                    if ($minE === 59) {
                        $t2 = date('H:i', strtotime($t2) + 60);
                    }

                    $startM = (int)date('G', strtotime($t1)) * 60 + (int)date('i', strtotime($t1));
                    $endM   = (int)date('G', strtotime($t2)) * 60 + (int)date('i', strtotime($t2));

                    $diff = $endM - $startM;
                    if ($diff < 0) $diff += 24 * 60;

                    $row_hours += $diff;
                }
                $th = round($row_hours / 60, 2);
                $rowchk[$gId] = $th;
            }

            foreach ($gs as $g) {
                $row_hours = 0;
                $gsId = $g['groupId'];

                foreach ($g['rows'] as $r) {

                    if ($data['ischk' . $r]) {
                        $t1 = $data['t_' . $r . '_1'];
                        $t2 = $data['t_' . $r . '_2'];

                        if ($t1 === '' || $t2 === '') continue;

                        $minS = (int)date('i', strtotime($t1));
                        if ($minS !== 0) {
                            $t1 = date('H:i', strtotime($t1) - 60);
                        }

                        $minE = (int)date('i', strtotime($t2));
                        if ($minE === 59) {
                            $t2 = date('H:i', strtotime($t2) + 60);
                        }

                        $startM = (int)date('G', strtotime($t1)) * 60 + (int)date('i', strtotime($t1));
                        $endM   = (int)date('G', strtotime($t2)) * 60 + (int)date('i', strtotime($t2));

                        $diff = $endM - $startM;
                        if ($diff < 0) $diff += 24 * 60;

                        $row_hours += $diff;
                    }
                }
                $th = round($row_hours / 60, 2);
                $rowchk[$gsId] = $th;
            }
            // var_dump($mergedData);
            // var_dump($mergedData[11]['groupId']);

            $save = [];
            $save['emp_id']         = $empid;
            $save['typeId']         = $typeId;
            $save['code']           = $type;
            $save['startDate']      = $data['startDate'];
            $save['endDate']        = $data['endDate'];
            $save['total_hours']    = $total_days * $total_hours;
            $save['total_days']     = $total_days;

            $save['isFixed']        = false;

            $id = ($data['id']) ? $data['id'] : '';
            if ($id) {
                $save['updated_geo_location'] = $data['fixedgeoTagLocation'];
                $save['updated_at'] = $created;
                $update = $this->request->update_table('tk_leave_requests', $save, ['leaveId' => $id]);
                $delete = $this->request->delete_table('tk_request_time_table', ['leaveId' => $id]);
                $delete = $this->request->delete_table('tk_leave_splitrows', ['leaveId' => $id]);
                $delete = $this->request->delete_table('tk_leave_request_assisted_persons', ['leaveId' => $id]);
                $leaveId = $id;
            } else {
                $save['status']         = 'pending';
                $save['geo_location']   = $data['fixedgeoTagLocation'];
                $save['created_at']     = $created;
                $leaveId = $this->request->insert('tk_leave_requests', $save);
            }

            $isDuty = false;
            foreach ($daysByYear as $year => $info) {

                $days = $info['count'];
                $dates = $info['dates'];
                foreach ($dates as $date) {
                    $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $date];
                    $chk = $this->request->getRow('tk_leave_request_dates', $cond);
                    if ($chk) {
                        $d = ['code' => $type, 'payment' => ''];
                        $cond = ['leaveId' => $leaveId, 'emp_id' => $empid, 'date' => $date];
                        $this->request->update_table('tk_leave_request_dates', $d, $cond);
                    } else {
                        $d = [
                            'leaveId'   => $leaveId,
                            'emp_id'    => $empid,
                            'code'      => $type,
                            'year'      => $year,
                            'date'      => $date,
                            'status'    => 'done',
                            'created'   => $created
                        ];
                        $this->request->insert('tk_leave_request_dates', $d);
                    }

                    $cond = ['emp_id' => $empid, 'attendance_date' => $date];
                    $records = $this->request->getRow('tk_attendance_records', $cond);
                    if ($records) {
                        $isDuty = true;
                    }
                }
            }

            foreach ($data['splitRows'] as $sp) {
                $splitRows = [
                    'leaveId'       => $leaveId,
                    'emp_id'        => $empid,
                    'time_row'      => $sp['id'],
                    'parent'        => $sp['parent'],
                    'splitParent'   => $sp['splitParent'],
                    'ischk'         => $sp['ischk'],
                    't1'            => date("H:i:s", strtotime($sp['t1'])),
                    't2'            => date("H:i:s", strtotime($sp['t2'])),
                ];

                if ($isDuty) {
                    if ($sp['ischk']) {
                        $ins = $this->request->insert('tk_leave_splitrows', $splitRows);
                    }
                } else {
                    $ins = $this->request->insert('tk_leave_splitrows', $splitRows);
                }
            }

            if ($mergedAttachments) {
                $mergedData = $data['mergedData'];
                foreach ($mergedAttachments as $rowId => $arrAttachment) {

                    foreach ($arrAttachment as $attachment) {

                        $upload = [
                            'leaveId' => $leaveId,
                            'emp_id'   => $empid,
                            'attachment' => $attachment,
                            'isFixed'   => 0,
                            'groupId'   => $mergedData[$rowId]['groupId'],
                            'time_row'  => $rowId,
                            'status'   => 'active',
                            'created_at' => date('Y-m-d H:i:s'),
                        ];

                        $this->request->insert('tk_leave_request_attachments', $upload);
                    }
                }
            }
            // var_dump($data['editedGroups']);

            $activeRows = [];
            $editedGroups = $data['editedGroups'];
            $groupStructure = $data['groupStructure'];
            $mergedData = $data['mergedData'];
            foreach ($mergedData as $tr => $md) {

                $activeRows[$tr] = $md['groupId'];

                $details = ['time_row' => $tr];
                $cond = ['leaveId' => $leaveId, 'groupId' => $md['groupId']];
                $this->request->update_table('tk_leave_request_attachments', $details, $cond);

                if ($md['leaveType'] === 'medical') {
                    $d = [
                        'leaveId'           => $leaveId,
                        'emp_id'            => $empid,
                        'groupId'           => $md['groupId'],
                        'time_row'          => $tr,
                        'assisted_person'   => $md['assistedPerson'],
                        'isFixed'           => 0,
                        'status'            => 'active',
                        'created'           => $created
                    ];
                    $this->request->insert('tk_leave_request_assisted_persons', $d);
                }

                $cond = ['emp_id' => $empid, 'leaveId' => $leaveId, 'groupId' => $md['groupId']];
                $check = $this->request->getRow('tk_leave_request_details', $cond);
                if ($check) {

                    foreach ($editedGroups as $e_g => $e_rows) {
                        if ($e_g == $md['groupId']) {
                            $details = [
                                'group_hours'       => $rowchk[$md['groupId']],
                                'code'              => $md['leaveType'],
                                'time_range'        => $md['timeRange'],
                                'updated_location'  => $md['geoLocRow'],
                                'remarks'           => $md['remarks'],
                                'updated_date'      => date('Y-m-d H:i:s'),
                                'status'            => 'pending'
                            ];

                            $cond = "leaveId='$leaveId' AND groupId='$md[groupId]' AND status NOT IN ('approved', 'disapproved')";
                            $this->request->update_table('tk_leave_request_details', $details, $cond);
                        }
                    }

                    foreach ($groupStructure as $group) {
                        $groupId = $group['groupId'];
                        if ($groupId === $md['groupId']) {

                            $rows = $group['rows'];
                            foreach ($rows as $t) {
                                $activeRows[$t] = $md['groupId'];
                                $cond = ['leaveId' => $leaveId, 'groupId' => $md['groupId'], 'time_row' => $t];
                                $check = $this->request->getRow('tk_leave_request_details', $cond);
                                if (!$check) {
                                    $details = [
                                        'leaveId'       => $leaveId,
                                        'emp_id'        => $empid,
                                        'groupId'       => $groupId,
                                        'group_hours'   => $rowchk[$md['groupId']],
                                        'code'          => $md['leaveType'],
                                        'time_range'    => $md['timeRange'],
                                        'location'      => $md['geoLocRow'],
                                        'time_row'      => $t,
                                        'remarks'       => $md['remarks'],
                                        'created'       => date('Y-m-d H:i:s'),
                                        'status'        => 'pending'
                                    ];
                                    $this->request->insert('tk_leave_request_details', $details);
                                }
                            }
                        }
                    }
                } else {

                    $details = [
                        'leaveId'       => $leaveId,
                        'emp_id'        => $empid,
                        'groupId'       => $md['groupId'],
                        'group_hours'   => $rowchk[$md['groupId']],
                        'code'          => $md['leaveType'],
                        'time_range'    => $md['timeRange'],
                        'location'      => $md['geoLocRow'],
                        'time_row'      => $tr,
                        'remarks'       => $md['remarks'],
                        'created'       => date('Y-m-d H:i:s'),
                        'status'        => 'pending'
                    ];

                    $grp = false;
                    foreach ($groupStructure as $group) {
                        $groupId = $group['groupId'];
                        if ($groupId === $md['groupId']) {
                            $grp = true;
                            $rows = $group['rows'];
                            foreach ($rows as $t) {
                                $activeRows[$t] = $md['groupId'];
                                $details['time_row'] = $t;

                                $ldetails = $this->request->insert('tk_leave_request_details', $details);
                            }
                        }
                    }

                    if (!$grp) {

                        $ldetails = $this->request->insert('tk_leave_request_details', $details);
                    }
                }
            }

            $cond = ['emp_id' => $empid, 'leaveId' => $leaveId];
            $details = $this->request->getArray('tk_leave_request_details', $cond);
            foreach ($details as $key => $d) {

                if (!isset($activeRows[$d['time_row']])) {
                    $cond = ['leaveId' => $leaveId, 'groupId' => $d['groupId'], 'time_row' => $d['time_row'], 'status' => 'pending'];
                    $this->request->delete_table('tk_leave_request_details', $cond);
                    $cond = ['leaveId' => $leaveId, 'groupId' => $d['groupId'], 'time_row' => $d['time_row']];
                    $this->request->delete_table('tk_leave_request_attachments', $cond);
                } else {
                    if ($activeRows[$d['time_row']] != $d['groupId']) {
                        $cond = ['leaveId' => $leaveId, 'groupId' => $d['groupId'], 'time_row' => $d['time_row'], 'status' => 'pending'];
                        $this->request->delete_table('tk_leave_request_details', $cond);
                        $cond = ['leaveId' => $leaveId, 'groupId' => $d['groupId'], 'time_row' => $d['time_row']];
                        $this->request->delete_table('tk_leave_request_attachments', $cond);
                    }
                }
            }

            $time = [];
            $time['leaveId'] = $leaveId;
            $time['emp_id'] = $empid;
            for ($i = 8; $i <= 18; $i++) {
                if ($data['ischk' . $i]) {
                    $time['time_' . $i . '_1'] = date("H:i:s", strtotime($data['t_' . $i . '_1']));
                    $time['time_' . $i . '_2'] = date("H:i:s", strtotime($data['t_' . $i . '_2']));
                } else {
                    $time['time_' . $i . '_1'] = date("H:i:s", strtotime($data['t_' . $i . '_1']));
                    $time['time_' . $i . '_2'] = date("H:i:s", strtotime($data['t_' . $i . '_2']));
                }
            }

            $ltime = $this->request->insert('tk_request_time_table', $time);

            echo json_encode(['message' => 'success', 'data' => array_merge($save, ['leaveId' => $leaveId])]);
            return;
        }
    }

    public function delete_leave()
    {
        $input = $this->input->post();
        $empid = $this->session->userdata('empid');
        $leaveId = $input['leaveId'];
        $date = $input['date'];

        $cond = ['emp_id' => $empid, 'leaveId' => $leaveId];
        $lr = $this->request->getRow('tk_leave_requests', $cond);
        if ($lr) {
            $type = $lr['code'];

            $this->request->delete_table('tk_leave_requests', ['leaveId' => $leaveId]);
            $this->request->delete_table('tk_leave_request_details', ['leaveId' => $leaveId]);
            $this->request->delete_table('tk_request_time_table', ['leaveId' => $leaveId]);
            $this->request->delete_table('tk_leave_request_dates', ['leaveId' => $leaveId]);
            $this->request->delete_table('tk_leave_splitrows', ['leaveId' => $leaveId]);
            $this->request->delete_table('tk_leave_request_attachments', ['leaveId' => $leaveId]);
            $this->request->delete_table('tk_leave_request_assisted_persons', ['leaveId' => $leaveId]);
            $this->request->delete_table('tk_maternity_leave_details', ['leaveId' => $leaveId]);
            $this->request->delete_table('tk_paternity_leave_details', ['leaveId' => $leaveId]);

            if ($type === 'vacation') {
                $yearStart = (int) date('Y', strtotime($lr['startDate']));
                $yearEnd   = (int) date('Y', strtotime($lr['endDate']));
                while ($yearStart <= $yearEnd) {

                    $this->count_vl_balance($empid, $yearStart);

                    $yearStart++;
                }
            }


            echo json_encode(['message' => 'success']);
        }
    }

    public function delete_date_leave()
    {
        $input = $this->input->post();
        $empid = $this->session->userdata('empid');
        $leaveId = $input['leaveId'];
        $date = $input['date'];
        $cond = ['leaveId' => $leaveId, 'isFixed' => 1];
        $lr = $this->request->getRow('tk_leave_requests', $cond);
        if ($lr) {
            $type = $lr['code'];
            if ($type !== 'maternity' && $type !== 'paternity') {

                $cond = ['leaveId' => $leaveId, 'date' => $date];
                $this->request->delete_table('tk_leave_request_dates', $cond);

                $deduct = 7;

                if ($type === 'vacation') {
                    $year = date('Y', strtotime($date));
                    $this->count_vl_balance($empid, $year);
                }

                $cond = ['leaveId' => $leaveId];
                $data = ['total_days' => $lr['total_days'] - 1, 'total_hours' => $lr['total_hours'] - $deduct,];
                $this->request->update_table('tk_leave_requests', $data, $cond);

                echo json_encode(['message' => 'success']);
            }
        }
    }

    public function edit_leave_form()
    {
        $empid = $this->session->userdata('empid');
        $input = $this->input->post();
        $date = $input['date'];
        $leaveId = $input['leaveId'];
        $leave = $this->request->get_leave($leaveId);
        $details = $this->request->get_leave_details($leaveId);
        $timeTable = $this->request->get_leave_time_table($leaveId);
        $splitrows = $this->request->getArray('tk_leave_splitrows', ['leaveId' => $input['leaveId']]);
        $maternity = $this->request->getRow('tk_maternity_leave_details', ['leaveId' => $input['leaveId']]);
        $paternity = $this->request->getRow('tk_paternity_leave_details', ['leaveId' => $input['leaveId']]);
        $assistance = $this->request->getArray('tk_leave_request_assisted_persons', ['leaveId' => $input['leaveId']]);
        $attachments = $this->request->get_leave_attachments($leaveId);
        $applicant = $this->request->getRow('applicant', ['app_id' => $empid]);
        $registered_loc = $this->request->getRow('masterlist_2ndgen', ['emp_id' => $empid])['location'];
        $applicant['registered_loc'] = $registered_loc;
        $groups = [];
        $time = [];
        $duty = $this->request->getRow('tk_attendance_records', ['emp_id' => $empid, 'attendance_date' => $date]);
        if ($duty) {
            $groups = $this->request->getArray('tk_attendance_groups', ['attendance_id' => $duty['id']]);
            $cond = "attendance_id='$duty[id]' ORDER BY start_time";
            $time = $this->request->getArray('tk_attendance_time_entries', $cond);
        }

        $approvedBy = '';
        if ($leave) {
            if ($leave['status'] === 'approved' || $leave['status'] === 'disapproved') {
                $approvedBy = $this->request->getRow('employee3', ['emp_id' => $leave['approvedBy']])['name'];
            }
        }
        $data = [
            'leave'         => $leave,
            'details'       => $details,
            'timeTable'     => $timeTable,
            'attachments'   => $attachments,
            'maternity'     => $maternity,
            'paternity'     => $paternity,
            'splitrows'     => $splitrows,
            'approvedBy'    => $approvedBy,
            'employee'      => $applicant,
            'assistance'    => $assistance,
            'duty'          => $duty,
            'duty_groups'   => $groups,
            'duty_time'     => $time,
        ];

        echo json_encode($data);
        return;
    }

    public function check_date_leave()
    {
        $input = $this->input->post();
        $date = $input['date'];
        $empid = $this->session->userdata('empid');

        $applicant = $this->request->getRow('applicant', ['app_id' => $empid]);
        $registered_loc = $this->request->getRow('masterlist_2ndgen', ['emp_id' => $empid])['location'];
        $applicant['registered_loc'] = $registered_loc;
        $groups = [];
        $duty = $this->request->getRow('tk_attendance_records', ['emp_id' => $empid, 'attendance_date' => $date]);
        if ($duty) {
            $groups = $this->request->getArray('tk_attendance_groups', ['attendance_id' => $duty['id']]);
            $time = $this->request->getArray('tk_attendance_time_entries', ['attendance_id' => $duty['id']]);
        }

        $leaveId = '';
        $days = $this->request->getArray('tk_leave_request_dates', ['emp_id' => $empid, 'date' => $date]);
        foreach ($days as $d) {
            // $cond = ['leaveId' => $d['leaveId'], 'status !=' => 'disapproved'];
            $cond = ['leaveId' => $d['leaveId']];
            $leaveId = $this->request->getRow('tk_leave_requests', $cond)['leaveId'];
        }

        $old_p = $this->request->getRow('tk_change_dayoff', ['emp_id' => $empid, 'old_dayoff' => $date, 'status' => 'pending']);
        $new_p = $this->request->getRow('tk_change_dayoff', ['emp_id' => $empid, 'new_dayoff' => $date, 'status' => 'pending']);
        $cond = "emp_id='$empid' AND old_dayoff='$date' AND status='approved' ORDER BY tkcdoId DESC";
        $old_a = $this->request->getRow('tk_change_dayoff', $cond);
        $cond = "emp_id='$empid' AND new_dayoff='$date' AND status='approved' ORDER BY tkcdoId DESC";
        $new_a = $this->request->getRow('tk_change_dayoff', $cond);

        $dayOfWeek = date('N', strtotime($date));

        $select = '<option value="">Select Dayoff Date</option>';

        $dates = [];
        $given = new DateTime($date);
        $dow = (int) $given->format('w');
        $sunday = new DateTime($date);
        $sunday->modify('-' . $dow . ' days');
        for ($i = 0; $i < 7; $i++) {
            $current = new DateTime($sunday->format('Y-m-d'));
            $current->modify('+' . $i . ' days');

            if ($current->format('Y-m-d') === $given->format('Y-m-d')) {
                continue;
            }

            // $dates[] = $current->format('Y-m-d');
            $dateStr = $current->format('Y-m-d');

            $hol = $this->request->getRow('tk_holidays', ['date' => $dateStr]);
            $cond = ['emp_id' => $empid, 'date' => $dateStr];
            $chk = $this->request->getRow('tk_leave_request_dates', $cond);
            $cond = ['emp_id' => $empid, 'new_dayoff' => $dateStr];
            $do  = $this->request->getRow('tk_change_dayoff', $cond);
            $cond = ['emp_id' => $empid, 'attendance_date' => $dateStr];
            $log = $this->request->getRow('tk_attendance_records', $cond);

            if (!$hol && !$chk && !$log) {
                $select .= '<option value="' . $dateStr . '">' . $dateStr . '</option>';
            } else {
                if ($hol) {
                    $holiday = $hol['holiday'];
                    $select .= '<option value="' . $dateStr . '" disabled>' . $dateStr . ' ' . $holiday . '</option>';
                }

                if ($chk && $log) {
                    $cond = ['code' => $chk['code']];
                    $l = $this->request->getRow('tk_request_type', $cond)['acroname'];
                    $select .= '<option value="' . $dateStr . '" disabled>' . $dateStr . ' ' . $l . ' | Duty HW</option>';
                } else if ($chk) {

                    $cond = ['code' => $chk['code']];
                    $l = $this->request->getRow('tk_request_type', $cond)['acroname'];
                    $select .= '<option value="' . $dateStr . '" disabled>' . $dateStr . ' ' . $l . '</option>';
                } elseif ($log) {
                    $select .= '<option value="' . $dateStr . '" disabled>' . $dateStr . ' Duty HW</option>';
                }
            }
        }

        $cond = ['emp_id' => $empid, 'attendance_date' => $date];
        $duty = $this->request->getArray('tk_attendance_records', $cond);

        if ($duty && $leaveId) {
            echo json_encode(['day' => 'duty and leave', 'id' => $leaveId, 'employee' => $applicant, 'duty' => $duty, 'groups' => $groups, 'time' => $time]);
            return;
        }

        if ($duty) {
            echo json_encode(['day' => 'duty', 'employee' => $applicant, 'duty' => $duty, 'groups' => $groups, 'time' => $time]);
            return;
        }

        $holiday = $this->request->getRow('tk_holidays', ['date' => $date, 'date_change' => '0000-00-00']);
        if (!$holiday) {
            $holiday = $this->request->getRow('tk_holidays', ['date_change' => $date]);
        }

        if ($leaveId) {
            echo json_encode(['day' => 'leave', 'id' => $leaveId, 'employee' => $applicant]);
            return;
        }

        if ($holiday) {
            echo json_encode(['day' => 'holiday', 'holiday' => $holiday, 'employee' => $applicant]);
            return;
        } else if ($dayOfWeek == 7 || $old_p || $new_p || $old_a || $new_a) {
            if ($old_p) {
                echo json_encode(['day' => 'dayoff', 'id' => $old_p, 'dates' => $select, 'employee' => $applicant]);
            } else if ($new_p) {
                echo json_encode(['day' => 'open', 'id' => 0, 'employee' => $applicant]);
                return;
            } else if ($old_a) {
                if ($new_a) {
                    if ($old_a['tkcdoId'] < $new_a['tkcdoId']) {
                        echo json_encode(['day' => 'dayoff', 'id' => 0, 'dates' => $select, 'employee' => $applicant]);
                        return;
                    } else {
                        echo json_encode(['day' => 'open', 'id' => 0, 'employee' => $applicant]);
                        return;
                    }
                } else {
                    echo json_encode(['day' => 'open', 'id' => 0, 'employee' => $applicant]);
                    return;
                }
            } else if ($new_a) {
                if ($old_a) {
                    if ($old_a['tkcdoId'] < $new_a['tkcdoId']) {
                        echo json_encode(['day' => 'dayoff', 'id' => 0, 'dates' => $select, 'employee' => $applicant]);
                        return;
                    } else {
                        echo json_encode(['day' => 'open', 'id' => 0, 'employee' => $applicant]);
                        return;
                    }
                } else {
                    echo json_encode(['day' => 'dayoff', 'id' => 0, 'dates' => $select, 'employee' => $applicant]);
                    return;
                }
            } else {
                echo json_encode(['day' => 'dayoff', 'id' => 0, 'dates' => $select, 'employee' => $applicant]);
                return;
            }
        } else {
            echo json_encode(['day' => 'open', 'id' => 0, 'employee' => $applicant]);
            return;
        }
    }

    public function delete_leave_attachment()
    {
        $input = $this->input->post();
        $tklraId = $input['tklraId'];
        $fileName = $input['fileName'];
        $this->request->delete_table('tk_leave_request_attachments', ['tklraId' => $tklraId]);
        echo json_encode(['message' => 'success',]);
    }

    public function save_dayoff()
    {
        $empid = $this->session->userdata('empid');
        $input = $this->input->post();
        $old_do = $input['old_do'];
        $new_do = $input['new_do'];
        $remarks = $input['remarks'];

        $cond = ['emp_id' => $empid, 'old_dayoff' => $old_do, 'status' => 'pending'];
        $chk = $this->request->getRow('tk_change_dayoff', $cond);
        if ($chk) {
            if ($chk['status'] == 'pending') {
                $cdo = ['new_dayoff' => $new_do, 'remarks' => $remarks];
                $cond = ['emp_id' => $empid, 'old_dayoff' => $old_do];
                $do = $this->request->update_table('tk_change_dayoff', $cdo, $cond);

                $cond = ['tkcdoId' => $chk['tkcdoId']];
                $data = ['old_do' => $old_do, 'new_do' => $new_do,];
                $do = $this->request->update_table('tk_change_dayoff_request', $data, $cond);

                if ($do) {
                    echo json_encode(['message' => 'updated']);
                    return;
                }
            }
        } else {
            $cdo = [
                'emp_id' => $empid,
                'old_dayoff' => $old_do,
                'new_dayoff' => $new_do,
                'status' => 'pending',
                'remarks' => $remarks,
                'created' => date('Y-m-d H:i:s')
            ];
            $doId = $this->request->insert('tk_change_dayoff', $cdo);

            $insert = [
                'tkcdoId' => $doId,
                'emp_id' => $empid,
                'old_do' => $old_do,
                'new_do' => $new_do,
                'created' => date('Y-m-d H:i:s'),
                'remarks' => $remarks,
                'status' => 'pending'
            ];
            $this->request->insert('tk_change_dayoff_request', $insert);
            if ($doId) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }
    }

    public function cancel_dayoff()
    {
        $input = $this->input->post();
        $dayoffId = $input['dayoffId'];
        $do = $this->request->getRow('tk_change_dayoff', ['tkcdoId' => $dayoffId]);
        if ($do) {
            $cond = ['tkcdoId' => $dayoffId];
            $this->request->delete_table('tk_change_dayoff', $cond);
            $data = ['status' => 'cancelled'];
            $upd = $this->request->update_table('tk_change_dayoff_request', $data, $cond);
            if ($upd) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }
    }

    public function get_relatives()
    {
        $input = $this->input->post();
        $empid = $this->session->userdata('empid');
        $relatives = [];
        $applicant = $this->request->getRow('applicant', ['app_id' => $empid]);
        if ($applicant) {
            if ($applicant['father']) {
                $relatives['FATHER'] = $applicant['father'];
            }
            if ($applicant['mother']) {
                $relatives['MOTHER'] = $applicant['mother'];
            }
            if ($applicant['spouse']) {
                $relatives['SPOUSE'] = $applicant['spouse'];
            }
        }

        $i = 1;
        $children = $this->request->getArray('masterlist_2ndgen_children', ['emp_id' => $empid]);
        foreach ($children as $key => $child) {
            $relatives['CHILD_' . $i] = $child['children_name'];
            $i++;
        }


        echo json_encode(['rel' => $relatives]);
    }

    public function get_approval_details()
    {
        $leaveId = $this->input->post('leaveId');
        $groupId = $this->input->post('groupId');
        $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
        $leave = $this->request->getRow('tk_leave_request_details', $cond);
        if ($leave) {
            if ($leave['status'] != 'pending') {
                $cond = "leaveId='$leaveId' AND groupId='$groupId' ORDER BY tkraId DESC LIMIT 1";
                $approval = $this->request->getRow('tk_leave_request_approved', $cond);
                if ($approval) {
                    $date = date('F j, Y h:i A', strtotime($approval['approved_datetime']));
                    $time = date('h:i:s A', strtotime($approval['approved_datetime']));
                    $name = $this->request->getRow('employee3', ['emp_id' => $approval['approved_by']])['name'];

                    if ($approval['status'] == 'approved') {
                        $label_name = 'Approved By';
                        $label_date = 'Approved Date';
                        $label_time = 'Approved Time';
                        $statusColor = 'text-success fw-bold';
                    } else {
                        $label_name = 'Disapproved By';
                        $label_date = 'Disapproved Date';
                        $label_time = 'Disapproved Time';
                        $statusColor = 'text-danger fw-bold';
                    }

                    echo '<div class="card">';
                    echo '<div class="card-body">';

                    echo '<div class="row g-3">';
                    echo '<div class="col-12 col-md-12">';
                    echo '<label class="form-label small fw-semibold text-muted mb-1">' . $label_name . '</label>';
                    echo '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
                    echo '</div>';

                    echo '<div class="col-6 col-md-6">';
                    echo '<label class="form-label small fw-semibold text-muted mb-1">' . $label_date . '</label>';
                    echo '<input type="text" class="form-control form-control-sm" value="' . $date . '" disabled>';
                    echo '</div>';
                    // echo '<div class="col-12 col-md-6">';
                    // echo '<label class="form-label small fw-semibold text-muted mb-1">' . $label_time . '</label>';
                    // echo '<input type="text" class="form-control form-control-sm" value="' . $time . '" disabled>';
                    // echo '</div>';
                    echo '<div class="col-6 col-md-6">';
                    echo '<label class="form-label small fw-semibold text-muted mb-1">Approval Status</label>';
                    echo '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . strtoupper($approval['status']) . '" disabled>';
                    echo '</div>';
                    echo '<div class="col-12 col-md-12">';
                    echo '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
                    echo '<textarea class="form-control form-control-sm" disabled>' . $approval['comments'] . '</textarea>';
                    echo '</div>';
                    echo '</div>';

                    echo '</div>';
                    echo '</div>';
                }
            }
        }
    }

    public function generate_vl_year($empid, $year)
    {
        $cond = ['emp_id' => $empid, 'year' => $year];
        $vl = $this->request->getRow('tk_vacation_leave_balance', $cond);
        if (!$vl) {

            $cond = ['payment' => 'paid', 'status' => '1'];
            $paid = $this->request->getRow('tk_vl_defaults', $cond)['total_days'];
            $cond = ['payment' => 'unpaid', 'status' => '1'];
            $unpaid = $this->request->getRow('tk_vl_defaults', $cond)['total_days'];
            $total = $paid + $unpaid;

            $data = [
                'emp_id'                => $empid,
                'year'                  => $year,
                'totalAllocated'        => $total,
                'paidDaysAllocated'     => $paid,
                'unpaidDaysAllocated'   => $unpaid,
                'paidDaysUsed'          => 0,
                'unpaidDaysUsed'        => 0,
                'paidDaysRemaining'     => $paid,
                'unpaidDaysRemaining'   => $unpaid,
                'totalDaysRemaining'    => $total,
                'lastUpdated'           => date('Y-m-d H:i:s')
            ];
            $vl = $this->request->insert('tk_vacation_leave_balance', $data);
        }

        return $vl;
    }

    public function count_vl_balance($empid, $year)
    {
        $paid_total = 0;
        $cond = ['emp_id' => $empid, 'code' => 'vacation', 'year' => $year, 'payment' => 'paid'];
        $vl_paid = $this->request->getArray('tk_leave_request_dates', $cond);
        if ($vl_paid) {
            $paid_total += count($vl_paid);
        }

        $cond = ['emp_id' => $empid, 'year' => $year];
        $vl_payment = $this->request->getArray('tk_vl_payment', $cond);
        foreach ($vl_payment as $vp) {
            $paid_total += $vp['total_days'];
        }

        $cond = ['emp_id' => $empid, 'code' => 'vacation', 'year' => $year, 'payment' => 'unpaid'];
        $vl_unpaid = $this->request->getArray('tk_leave_request_dates', $cond);

        $cond = ['emp_id' => $empid, 'year' => $year];
        $vl = $this->request->getRow('tk_vacation_leave_balance', $cond);
        if ($vl) {
            $upd = [
                'paidDaysUsed' => $paid_total,
                'paidDaysRemaining' => $vl['paidDaysAllocated'] - $paid_total,
                'unpaidDaysUsed' => count($vl_unpaid),
                'unpaidDaysRemaining' => $vl['unpaidDaysAllocated'] - count($vl_unpaid),
                'totalDaysRemaining' => $vl['totalAllocated'] - ($paid_total + count($vl_unpaid)),
                'lastUpdated' => date('Y-m-d H:i:s')
            ];
            $this->request->update_table('tk_vacation_leave_balance', $upd, $cond);
        }
    }

    public function viewFixedRequest()
    {
        $data = '';
        $input = $this->input->post();
        $leaveId = $input['leaveId'];
        $date = $input['date'];
        $cond = ['leaveId' => $leaveId];
        $leave = $this->request->getRow('tk_leave_requests', $cond);
        if ($leave) {
            $code = $leave['code'];
            $request = $this->request->getRow('tk_request_type', ['code' => $code, 'status' => 'active'])['request'];
            $name = $this->request->getRow('employee3', ['emp_id' => $leave['emp_id']])['name'];
            $hours = $leave['total_hours'] / $leave['total_days'];

            $submitted = date('Y-m-d h:i:s A', strtotime($leave['created_at']));
            $updated = '';
            if ($leave['updated_at'] != '0000-00-00 00:00:00') {
                $updated = date('Y-m-d h:i:s A', strtotime($leave['updated_at']));
            }

            if ($leave['status'] === 'disapproved') {
                $statusColor = 'text-danger fw-bold';
            } else if ($leave['status'] === 'approved') {
                $statusColor = 'text-success fw-bold';
            } else {
                $statusColor = 'text-warning fw-bold';
            }

            $data .= '<div class="row g-3">';

            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Type</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $request . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Hours</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $hours . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Start Date</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['startDate'] . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">End Date</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['endDate'] . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
            $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . strtoupper($leave['status']) . '" disabled>';
            $data .= '</div>';

            if ($code == 'vacation') {
                $data .= '<div class="col-6 col-md-6 mt-2">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-2 d-block">Payment Status</label>';
                $data .= '<div class="form-check form-check-inline">';
                $data .= '<input class="form-check-input" type="radio" name="payment_v" id="paymentPaid" value="paid" disabled ' . ($leave['payment'] == 'paid' ? 'checked' : '') . '>';
                $data .= '<label class="form-check-label" for="paymentPaid">Paid</label>';
                $data .= '</div>';
                $data .= '<div class="form-check form-check-inline">';
                $data .= '<input class="form-check-input" type="radio" name="payment_v" id="paymentUnpaid" value="unpaid" disabled ' . ($leave['payment'] == 'unpaid' ? 'checked' : '') . '>';
                $data .= '<label class="form-check-label" for="paymentUnpaid">Unpaid</label>';
                $data .= '</div>';
                $data .= '</div>';
            }

            if ($code == 'medical') {
                $data .= '<div class="col-6 col-md-6 mt-2">';

                $data .= '<div class="row">';
                $data .= '<div class="col-12 col-md-12">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Person Being Assisted</label>';
                $data .= '</div>';
                $data .= '<div class="col-12 col-md-12">';
                $cond = ['leaveId' => $leaveId];
                $asst = $this->request->getArray('tk_leave_request_assisted_persons', $cond);
                foreach ($asst as $a) {
                    $data .= '<span class="badge bg-secondary py-2 px-2 me-1" style="font-size: 0.70rem;">' . $a['assisted_person'] . '</span>';
                }
                $data .= '</div>';
                $data .= '</div>';

                $data .= '</div>';
            }

            if ($code == 'paternity') {
                $cond = ['leaveId' => $leaveId];
                $pat = $this->request->getRow('tk_paternity_leave_details', $cond);

                $data .= '<div class="col-6 col-md-6 mt-2">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Delivery On Or Before</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $pat['delivery_date'] . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6 mt-2">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Application</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $pat['application'] . '" disabled>';
                $data .= '</div>';
            }

            if ($code == 'maternity') {
                $cond = ['leaveId' => $leaveId];
                $mat = $this->request->getRow('tk_maternity_leave_details', $cond);

                $data .= '<div class="col-6 col-md-6 mt-2">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Delivery Type</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $mat['delivery_type'] . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6 mt-2">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Back to Work</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $mat['backtowork'] . '" disabled>';
                $data .= '</div>';
            }

            $data .= '<div class="col-6 col-md-6 mt-2">';

            $data .= '<div class="row">';
            $data .= '<div class="col-12 col-md-12">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Attachments</label>';
            $data .= '</div>';
            $data .= '<div class="col-12 col-md-12">';
            $i = 0;
            $cond = ['leaveId' => $leaveId];
            $attachments = $this->request->getArray('tk_leave_request_attachments', $cond);
            foreach ($attachments as $a) {
                $i++;
                $att_name = 'Attachment ' . $i;
                $fn = $a['attachment'];
                $link = base_url('uploads/leaves/' . $fn);
                $data .= '<a href="javascript:;" class="badge bg-primary py-2 px-2 me-1 text-decoration-none" onclick="viewAttachments(\'' . $link . '\')">' . $att_name . '</a>';
            }
            if (!$attachments) {
                $data .= '<span class="text-muted small"><i>No Attachments</i></span>';
            }
            $data .= '</div>';
            $data .= '</div>';

            $data .= '</div>';

            if ($code !== 'vacation' && $code !== 'medical') {
                $data .= '<div class="col-6 col-md-6 mt-2">';
                $data .= '</div>';
            }

            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Submitted Date</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $submitted . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Updated Date</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $updated . '" disabled>';
            $data .= '</div>';

            $data .= '<div class="col-12 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Submitted Geo Location</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['geo_location'] . '" disabled>';
            $data .= '</div>';

            $data .= '<div class="col-12 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Updated Geo Location</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['updated_geo_location'] . '" disabled>';
            $data .= '</div>';

            $data .= '<div class="col-12 col-md-12 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Remarks</label>';
            $data .= '<textarea class="form-control form-control-sm" disabled>' . $leave['reason'] . '</textarea>';
            $data .= '</div>';

            $data .= '</div>';

            $cond = "leaveId='$leaveId' ORDER BY tkraId DESC LIMIT 1";
            $approval = $this->request->getRow('tk_leave_request_approved', $cond);
            if ($approval) {
                $date = date('F j, Y', strtotime($approval['approved_datetime']));
                $time = date('h:i:s A', strtotime($approval['approved_datetime']));
                $name = $this->request->getRow('employee3', ['emp_id' => $approval['approved_by']])['name'];

                if ($approval['status'] == 'approved') {
                    $label_name = 'Approved By';
                    $label_date = 'Approved Date';
                    $label_time = 'Approved Time';
                    $statusColor = 'text-success fw-bold';
                } else {
                    $label_name = 'Disapproved By';
                    $label_date = 'Disapproved Date';
                    $label_time = 'Disapproved Time';
                    $statusColor = 'text-danger fw-bold';
                }

                $data .= '<div class="card mt-3">';
                $data .= '<div class="card-body">';

                $data .= '<div class="row g-3">';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">' . $label_name . '</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approval Status</label>';
                $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . strtoupper($approval['status']) . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">' . $label_date . '</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $date . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">' . $label_time . '</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $time . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-12 col-md-12">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
                $data .= '<textarea class="form-control form-control-sm" disabled>' . $approval['comments'] . '</textarea>';
                $data .= '</div>';
                $data .= '</div>';

                $data .= '</div>';
                $data .= '</div>';
            }
        }

        echo json_encode(['form' => $data]);
    }

    public function leave_options()
    {
        $input = $this->input->post();
        $fixed_type = $input['fixed_type'];

        $data = [];
        $cond = ['fixed_type' => $fixed_type, 'status' => 'active'];
        $request = $this->request->getArray('tk_request_type', $cond);
        foreach ($request as $r) {
            $name = $r['request'];
            $code = $r['code'];
            $acronym = $r['acroname'];
            $col = [];
            $col['value'] = $code;
            $col['label'] = $name;
            $data[] = $col;
        }

        echo json_encode(['options' => $data]);
    }

    public function getMatpatDays()
    {
        $input = $this->input->post();
        $code = $input['code'];
        $cond = ['code' => $code, 'status' => 1];
        $matpat = $this->request->getArray('tk_matpat_details', $cond);
        $data = [];
        foreach ($matpat as $m) {
            $delivery_type = $m['delivery_type'];
            $allocated_days = $m['allocated_days'];

            $data[$delivery_type] = round($allocated_days, 2);
            if ($code == 'paternity') {
                $data = round($allocated_days, 2);
            }
        }
        echo json_encode(['matpat' => $data]);
    }
}
