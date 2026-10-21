<?php

class Dtr_admin extends Portal_Controller
{
    public $usertype = '';
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Users_model', 'users');
        $this->load->model('Employee_model', 'employee');
        $this->load->model('Request_model', 'request_model'); // <-- alias added
        $this->usertype = $this->session->userdata('usertype');
    }

    public function cutoffTable()
    {
        $data = [];
        $co = $this->input->post('co');
        $cond = "co_schedule='$co' AND cutoff='1' GROUP BY co_startDate";
        $query = $this->request_model->getArray('tk_employee_cutoff', $cond);
        foreach ($query as $row) {
            $dateFrom = strtotime($row['co_startDate']);
            $df = date('F d, Y', strtotime($row['co_startDate']));
            $dt = date('F d, Y', strtotime($row['co_endDate']));
            $id = $co . '|1|' . $df . '|' . $dt;

            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = '<a href="javascript:;" id="' . $id . '" class="btn btn-sm btn-orange emp_list">View</a>';
            $data[] = $sub_array;
        }

        $cond = "co_schedule='$co' AND cutoff='2' GROUP BY co_startDate";
        $query = $this->request_model->getArray('tk_employee_cutoff', $cond);
        foreach ($query as $row) {
            $dateFrom = strtotime($row['co_startDate']);
            $df = date('F d, Y', strtotime($row['co_startDate']));
            $dt = date('F d, Y', strtotime($row['co_endDate']));
            $id = $co . '|2|' . $df . '|' . $dt;

            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = '<a href="javascript:;" id="' . $id . '" class="btn btn-sm btn-orange emp_list">View</a>';
            $data[] = $sub_array;
        }

        echo json_encode(['data' => $data]);
    }

    public function co_employee_list()
    {
        $data = [];
        $input = $this->input->post();
        $co = $input['co'];
        $cutoff = $input['cutoff'];
        $dateFrom = date('Y-m-d', strtotime($input['dateFrom']));
        $dateTo = date('Y-m-d', strtotime($input['dateTo']));

        $cond = ['co_schedule' => $co, 'cutoff' => $cutoff, 'co_startDate' => $dateFrom, 'co_endDate' => $dateTo];
        $query = $this->request_model->getArray('tk_employee_cutoff', $cond);
        foreach ($query as $row) {
            $empId = $row['emp_id'];
            $name = $this->request_model->getRow('employee3', ['emp_id' => $empId])['name'];
            // $location = $this->request_model->getRow('masterlist_2ndgen', ['emp_id' => $empId])['location'];
            $position = $this->request_model->getRow('employee3', ['emp_id' => $empId])['position'];
            $id = $empId . '|' . $co  . '|' . $dateFrom . '|' . $dateTo . '|' . $cutoff;

            $sub_array = [];
            $sub_array[] = $empId;
            $sub_array[] = $name;
            $sub_array[] = $position;
            // $sub_array[] = $location;
            $sub_array[] = '<a href="javascript:;" id="' . $id . '" class="btn btn-sm btn-orange view_dtr">View</a>';
            $data[] = $sub_array;
        }

        echo json_encode(['data' => $data]);
    }

    public function view_dtr()
    {
        $input  = $this->input->post();
        $empId  = $input['empId'];
        $payload     = $input['payload'];

        list($co, $sd, $ed, $cutoff) = explode('|', $payload);
        $co_startDate   = date('Y-m-d', strtotime($sd));
        $co_endDate     = date('Y-m-d', strtotime($ed));


        $emp = $this->request_model->getRow('employee3', ['emp_id' => $empId]);
        $name = $emp['name'];
        $position = $emp['position'];

        $photo = 'assets/images/admin.png';
        $img = $this->request_model->getRow('applicant', ['app_id' => $empId])['photo'];
        if ($img) {
            $p = explode('/', $img);
            $photo = 'assets/images/2ndgen/' . $p[3];
            if (!file_exists($photo)) {
                $photo = 'assets/images/admin.png';
            }
        }
        $data = '';
        $data .= '<div class="row g-2">';

        $data .= '<div class="col-md-5">';
        $data .= '<div class="d-flex justify-content-right gap-3">';
        $data .= '<img src="' . base_url($photo) . '" alt="Avatar" class="img-thumbnail" style="width: 100px; height: 100px;">';
        $data .= '<div>';
        $data .= '<div class="fw-bold text-dark fs-5">' . $name . '</div>';
        $data .= '<div class="text-muted"><i>' . $empId . '</i></div>';
        $data .= '<div class="text-muted mb-1"><i>' . $position . '</i></div>';
        $data .= '<span class="badge bg-light text-success border border-success rounded-pill px-2 py-1 mb-1" style="font-size: 0.7rem;"><i class="bi bi-circle-fill me-1"></i>Active </span>';
        $data .= '</div>';
        $data .= '</div>';
        $data .= '</div>';

        $data .= '<div class="col-md-7">';

        $data .= '<div class="row">';

        $data .= '<div class="col-md-12 dtr_totals">';
        $data .= '<div class="row text-center">
                    <div class="col-3">
                        <div class="p-2 rounded-2 bg-primary bg-opacity-10">
                            <div class="fw-bold text-primary">0</div>
                            <div class="fw-semibold text-primary text-opacity-75 small">Total Hours</div>
                        </div>
                    </div>
                    <div class="col-3">
                            <div class="p-2 rounded-2 bg-primary bg-opacity-10">
                                <div class="fw-bold text-primary">0</div>
                                <div class="fw-semibold text-primary text-opacity-75 small">Total Leave Hours</div>
                            </div>
                        </div>
                    <div class="col-3">
                        <div class="p-2 rounded-2 bg-danger bg-opacity-10">
                            <div class="fw-bold text-danger">0</div>
                            <div class="fw-semibold text-danger text-opacity-75 small">Total Deduction</div>
                        </div>
                    </div>
                </div>';
        $data .= '</div>';

        $data .= '<div class="col-md-9">';
        $data .= '<div class="form-group row mt-2">';
        // $data .= '<label for="cutoff" class="col-sm-1 col-form-label">CUTOFF</label>';
        // $data .= '<div class="col-sm-6">';
        $data .= '<select class="form-control form-control-sm" onchange="dtr_details(\'' . $empId . '\',this.value)" id="cutoff" disabled>';
        $data .= '<option value="">---Select Cutoff---</option>';
        $empCutoffs = $this->employeeCutoffs($empId, $co);
        foreach ($empCutoffs as $ec) {
            $ex = explode('|', $ec);
            $start = date('F j, Y', strtotime($ex[1]));
            $end = date('F j, Y', strtotime($ex[2]));
            $c = "$start to $end";
            if ($ec == $payload) {
                $data .= '<option value="' . $ec . '" class="text-center" selected>For ' . $c . ' cutoff.</option>';
            } else {
                $data .= '<option value="' . $ec . '">' . $c . '</option>';
            }
        }
        $data .= '</select>';
        // $data .= '</div>';
        $data .= '</div>';
        $data .= '</div>';

        $data .= '</div>';

        $data .= '</div>';

        $data .= '</div>';

        $data .=    '<div class="row g-2 dtr_details">
                        <div style="text-align: center;">
                        <img src="' . base_url('assets/images/loader_seq.gif') . '">
                        </div>
                    </div>';
        echo json_encode(['form' => $data]);
    }

    public function dtr_totals()
    {
        $data = '';
        $input = $this->input->post();
        $empId = $input['empId'];
        $payload = $input['payload'];
        list($co, $sd, $ed, $cutoff) = explode('|', $payload);

        $cond = ['emp_id' => $empId, 'cutoff' => $cutoff, 'co_schedule' => $co, 'co_startDate' => $sd, 'co_endDate' => $ed];
        $totals = $this->request_model->getRow('tk_employee_cutoff', $cond);
        if ($totals) {
            $hours = round($totals['co_hours'], 2);
            $deduction =  round($totals['deficit_hours'], 2);
            $leave =  round($totals['co_leaves'], 2);
            $data .= '<div class="row text-center">
                        <div class="col-3">
                            <div class="p-2 rounded-2 bg-primary bg-opacity-10">
                                <div class="fw-bold text-primary">' . $hours . '</div>
                                <div class="fw-semibold text-primary text-opacity-75 small">Total Hours</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded-2 bg-primary bg-opacity-10">
                                <div class="fw-bold text-primary">' . $leave . '</div>
                                <div class="fw-semibold text-primary text-opacity-75 small">Total Leave Hours</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded-2 bg-danger bg-opacity-10">
                                <div class="fw-bold text-danger">' . $deduction . '</div>
                                <div class="fw-semibold text-danger text-opacity-75 small">Total Deduction</div>
                            </div>
                        </div>
                    </div>';
        }
        echo json_encode(['form' => $data]);
        return;
    }

    public function dtr_details()
    {
        $input = $this->input->post();
        $empId = $input['empId'];
        $payload = $input['payload'];
        list($co, $co_startDate, $co_endDate, $cutoff) = explode('|', $payload);

        $cond = ['render_type' => 'duty', 'limit_type' => 'maximum', 'limit_duration' => 'daily'];
        $dutyMax = $this->request_model->getRow('tk_render_limits', $cond)['hours_rendered'];

        $data = '';
        $data .= '<div class="col-12 col-md-12">';
        $data .= '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="dtrTable">';
        $data .= '<thead>';
        $data .= '<tr>';
        $data .= '<th>Date</th>';
        $data .= '<th>Day</th>';
        $data .= '<th>IN1</th>';
        $data .= '<th>OUT1</th>';
        $data .= '<th>IN2</th>';
        $data .= '<th>OUT2</th>';
        $data .= '<th class="text-center">HW</th>';
        $data .= '<th class="text-center">REQUESTS</th>';
        $data .= '<th class="text-center">INFO</th>';
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '<tbody>';

        $s = date('Y-m-d', strtotime($co_startDate));
        $e = date('Y-m-d', strtotime($co_endDate));
        while ($s <= $e) {
            $tableDate = date('M. j, Y', strtotime($s));
            $tableDay = date('D', strtotime($s));
            $date = date('Y-m-d', strtotime($s));

            $holiday = false;
            $holiday_name = '';
            $check = $this->request_model->getRow('tk_holidays', ['date' => $date, 'date_change' => '0000-00-00']);
            if ($check) {
                $holiday_name = $check['holiday'];
                $holiday = true;
            } else {
                $check = $this->request_model->getRow('tk_holidays', ['date_change' => $date]);
                if ($check) {
                    $holiday_name = $check['holiday'];
                    $holiday = true;
                }
            }

            $dayoff = false;
            $cond = "emp_id='$empId' AND old_dayoff='$date' AND status='approved' ORDER BY tkcdoId DESC";
            $old_a = $this->request_model->getRow('tk_change_dayoff', $cond);
            $cond = "emp_id='$empId' AND new_dayoff='$date' AND status='approved' ORDER BY tkcdoId DESC";
            $new_a = $this->request_model->getRow('tk_change_dayoff', $cond);
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
            $info = '';
            $hw = 0;
            $in1 = $out1 = $in2 = $out2 = $request = $info = '';

            $cond = ['emp_id' => $empId, 'attendance_date' => $date];
            $duty = $this->request_model->getRow('tk_attendance_records', $cond);
            if ($duty) {
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

                if ($duty['hours_worked'] > $dutyMax) {
                    $hw += $dutyMax;
                } else {
                    $hw += $duty['hours_worked'];
                }

                $cond = ['attendance_id' => $duty['id']];
                $dutyGroups = $this->request_model->getArray('tk_attendance_groups', $cond);
                foreach ($dutyGroups as $g) {
                }

                $click = 'onclick="dtr_duty(\'' . $duty['id'] . '\',\'' . $tableDate . '\',\'' . date('l', strtotime($s)) . '\',)"';
                $class = 'class="text-primary text-decoration-none"';
                $info = '<a href="javascript:;" ' . $class . ' ' . $click . '><i class="bi bi-info-circle"></i></a>';
            }

            $request = '';
            $cond = ['emp_id' => $empId, 'date' => $date];
            $check = $this->request_model->getRow('tk_leave_request_dates', $cond);
            if ($check) {
                $leaveId = $check['leaveId'];

                $cond = ['leaveId' => $leaveId, 'isFixed' => 1];
                $leave = $this->request_model->getRow('tk_leave_requests', $cond);
                if ($leave) {

                    $groupId = '';
                    $l_fname = $this->request_model->getRow('tk_request_type', ['code' => $leave['code']])['request'];
                    $l_name = $this->request_model->getRow('tk_request_type', ['code' => $leave['code']])['acroname'];
                    if ($leave['status'] === 'approved') {
                        $l_icon = '<i class="bi bi-check-circle-fill me-2"></i>';
                        $l_class = 'badge bg-light text-success border border-success rounded-pill px-2 py-1 text-decoration-none';
                    } else if ($leave['status'] === 'disapproved') {
                        $l_icon = '<i class="bi bi-x-circle-fill me-2"></i>';
                        $l_class = 'badge bg-light text-danger border border-danger rounded-pill px-2 py-1 text-decoration-none';
                    } else {
                        $l_icon = '<span class="me-2">⏳</span>';
                        $l_class = 'badge bg-light text-warning border border-warning rounded-pill px-2 py-1 text-decoration-none';
                    }
                    $click = 'onclick="dtr_request(\'' . $empId . '\',\'' . $leaveId . '\',\'' . $groupId . '\',\'' . $l_fname . '\')"';
                    $payment = '';
                    if ($leave['code'] === 'vacation') {
                        $payment = '[' . strtoupper($leave['payment']) . ']';
                        if ($leave['payment'] === 'unpaid') {
                            $l_class = 'badge bg-light text-danger border border-danger rounded-pill px-2 py-1 text-decoration-none';
                        }
                    }
                    $request = '<a href="javascript:;" class="' . $l_class . '" ' . $click . '>' . $l_icon . '' . $l_name . ' ' . $payment . '</a>';
                }

                $cond = ['leaveId' => $leaveId, 'isFixed' => 0];
                $leave = $this->request_model->getRow('tk_leave_requests', $cond);
                if ($leave) {
                    $cond = "leaveId='$leaveId' AND emp_id='$empId' GROUP BY groupId";
                    $details = $this->request_model->getArray('tk_leave_request_details', $cond);
                    foreach ($details as $d) {
                        $groupId = $d['groupId'];
                        $l_fname = $this->request_model->getRow('tk_request_type', ['code' => $d['code']])['request'];
                        $l_name = $this->request_model->getRow('tk_request_type', ['code' => $d['code']])['acroname'];
                        if ($d['status'] === 'approved') {
                            $l_icon = '<i class="bi bi-check-circle-fill me-2"></i>';
                            $l_class = 'badge bg-light text-success border border-success rounded-pill px-2 py-1 me-1 text-decoration-none';
                        } else if ($d['status'] === 'disapproved') {
                            $l_icon = '<i class="bi bi-x-circle-fill me-2"></i>';
                            $l_class = 'badge bg-light text-danger border border-danger rounded-pill px-2 py-1 me-1 text-decoration-none';
                        } else {
                            $l_icon = '<span class="me-2">⏳</span>';
                            $l_class = 'badge bg-light text-warning border border-warning rounded-pill px-2 py-1 me-1 text-decoration-none';
                        }
                        $click = 'onclick="dtr_request(\'' . $empId . '\',\'' . $leaveId . '\',\'' . $groupId . '\',\'' . $l_fname . '\')"';
                        $request .= '<a href="javascript:;" class="' . $l_class . '" ' . $click . '>' . $l_icon . '' . $l_name . '</a>';
                    }
                }
            }

            if ($holiday && $dayoff) {
                $data .= '<tr>';
                $data .= '<td>' . $tableDate . '</td>';
                $data .= '<td>' . $tableDay . '</td>';
                $data .= '<td colspan="4" class="text-danger text-center">[ ' . $holiday_name . ' ] [ DAY-OFF ]</td>';
                $data .= '<td class="text-center">' . $hw . '</td>';
                $data .= '<td class="text-center">' . $request . '</td>';
                $data .= '<td class="text-center">' . $info . '</td>';
                $data .= '</tr>';
            } else if ($holiday) {
                $data .= '<tr>';
                $data .= '<td>' . $tableDate . '</td>';
                $data .= '<td>' . $tableDay . '</td>';
                $data .= '<td colspan="4" class="text-danger text-center">[ ' . $holiday_name . ' ]</td>';
                $data .= '<td class="text-center">' . $hw . '</td>';
                $data .= '<td class="text-center">' . $request . '</td>';
                $data .= '<td class="text-center">' . $info . '</td>';
                $data .= '</tr>';
            } else if ($dayoff) {
                $data .= '<tr>';
                $data .= '<td>' . $tableDate . '</td>';
                $data .= '<td>' . $tableDay . '</td>';
                $data .= '<td colspan="4" class="text-danger text-center">[ DAY-OFF ]</td>';
                $data .= '<td class="text-center">' . $hw . '</td>';
                $data .= '<td class="text-center">' . $request . '</td>';
                $data .= '<td class="text-center">' . $info . '</td>';
                $data .= '</tr>';
            } else {
                $data .= '<tr>';
                $data .= '<td>' . $tableDate . '</td>';
                $data .= '<td>' . $tableDay . '</td>';
                $data .= '<td>' . $in1 . '</td>';
                $data .= '<td>' . $out1 . '</td>';
                $data .= '<td>' . $in2 . '</td>';
                $data .= '<td>' . $out2 . '</td>';
                $data .= '<td class="text-center">' . $hw . '</td>';
                $data .= '<td class="text-center">' . $request . '</td>';
                $data .= '<td class="text-center">' . $info . '</td>';
                $data .= '</tr>';
            }


            $s = date('Y-m-d', strtotime($tableDate . ' +1 day'));
        }

        $data .= '</tbody>';
        $data .= '</table>';
        $data .= '</div>';

        echo json_encode(['form' => $data]);
    }

    public function employeeCutoffs($empId, $co)
    {
        $data = [];
        $cond = "emp_id='$empId' AND co_schedule='$co' AND cutoff='1' GROUP BY co_startDate";
        $query = $this->request_model->getArray('tk_employee_cutoff', $cond);
        foreach ($query as $row) {
            $df = $row['co_startDate'];
            $dt = $row['co_endDate'];
            $payload = $co . '|' . $df . '|' . $dt . '|1';
            $data[] = $payload;
        }

        $cond = "emp_id='$empId' AND co_schedule='$co' AND cutoff='2' GROUP BY co_startDate";
        $query = $this->request_model->getArray('tk_employee_cutoff', $cond);
        foreach ($query as $row) {
            $df = $row['co_startDate'];
            $dt = $row['co_endDate'];
            $payload = $co . '|' . $df . '|' . $dt . '|2';
            $data[] = $payload;
        }

        return $data;
    }

    public function dtr_request()
    {
        $data = '<div class="row g-2 mb-3">';
        $input = $this->input->post();
        $empId = $input['empId'];
        $leaveId = $input['leaveId'];
        $groupId = $input['groupId'];

        $cond = ['leaveId' => $leaveId];
        $leave = $this->request_model->getRow('tk_leave_requests', $cond);
        if ($leave) {
            $data .= '<div class="row g-3">';
            if ($leave['isFixed']) {
                $code = $leave['code'];
                $request = $this->request_model->getRow('tk_request_type', ['code' => $code, 'status' => 'active'])['request'];
                $name = $this->request_model->getRow('employee3', ['emp_id' => $leave['emp_id']])['name'];
                $filed = date('F j, Y', strtotime($leave['created_at']));

                if ($leave['status'] === 'approved') {
                    $statusColor = 'text-success fw-bold';
                } else if ($leave['status'] === 'disapproved') {
                    $statusColor = 'text-danger fw-bold';
                } else {
                    $statusColor = 'text-warning fw-bold';
                }

                $data .= '<div class="col-6 col-md-6 mt-1">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Filed</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $filed . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6 mt-1">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Type</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $request . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Start Date</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['startDate'] . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">End Date</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['endDate'] . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Days</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['total_days'] . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Hours</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . round($leave['total_hours'], 2) . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
                $data .= '</div>';
                $data .= '<div class="col-6 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
                $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . strtoupper($leave['status']) . '" disabled>';
                $data .= '</div>';

                if ($code == 'vacation') {
                    $data .= '<div class="col-12 col-md-6">';
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
                    $data .= '<div class="col-12 col-md-6">';

                    $data .= '<div class="row">';
                    $data .= '<div class="col-12 col-md-12">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Person Being Assisted</label>';
                    $data .= '</div>';
                    $data .= '<div class="col-12 col-md-12">';
                    $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                    $asst = $this->request_model->getArray('tk_leave_request_assisted_persons', $cond);
                    foreach ($asst as $a) {
                        $data .= '<span class="badge bg-secondary py-2 px-2 me-1" style="font-size: 0.70rem;">' . $a['assisted_person'] . '</span>';
                    }
                    $data .= '</div>';
                    $data .= '</div>';

                    $data .= '</div>';
                }

                if ($code == 'paternity') {
                    $cond = ['leaveId' => $leaveId];
                    $pat = $this->request_model->getRow('tk_paternity_leave_details', $cond);

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Delivery On Or Before</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $pat['delivery_date'] . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Application</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $pat['application'] . '" disabled>';
                    $data .= '</div>';
                }

                if ($code == 'maternity') {
                    $cond = ['leaveId' => $leaveId];
                    $mat = $this->request_model->getRow('tk_maternity_leave_details', $cond);

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Delivery Type</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $mat['delivery_type'] . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Back to Work</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $mat['backtowork'] . '" disabled>';
                    $data .= '</div>';
                }

                $data .= '<div class="col-12 col-md-6">';

                $data .= '<div class="row">';
                $data .= '<div class="col-12 col-md-12">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Attachments</label>';
                $data .= '</div>';
                $data .= '<div class="col-12 col-md-12">';
                $i = 0;
                $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                $attachments = $this->request_model->getArray('tk_leave_request_attachments', $cond);
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
                    $data .= '<div class="col-12 col-md-6">';
                    $data .= '</div>';
                }

                $data .= '<div class="col-12 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Submitted Geo Location</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['geo_location'] . '" disabled>';
                $data .= '</div>';

                $data .= '<div class="col-12 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Updated Geo Location</label>';
                $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['updated_geo_location'] . '" disabled>';
                $data .= '</div>';

                $data .= '<div class="col-12 col-md-12">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Remarks</label>';
                $data .= '<textarea class="form-control form-control-sm" disabled>' . $leave['reason'] . '</textarea>';
                $data .= '</div>';
            } else {
                $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                $details = $this->request_model->getRow('tk_leave_request_details', $cond);
                if ($details) {
                    $code = $details['code'];
                    $request = $this->request_model->getRow('tk_request_type', ['code' => $code, 'status' => 'active'])['request'];
                    $name = $this->request_model->getRow('employee3', ['emp_id' => $leave['emp_id']])['name'];

                    $sDate = date('F j, Y H:i A', strtotime($details['created']));
                    $uDate = '';
                    if ($details['updated_date'] != '0000-00-00 00:00:00') {
                        $uDate = date('F j, Y H:i A', strtotime($details['updated_date']));
                    }

                    if ($details['status'] === 'approved') {
                        $statusColor = 'text-success fw-bold';
                    } else if ($details['status'] === 'disapproved') {
                        $statusColor = 'text-danger fw-bold';
                    } else {
                        $statusColor = 'text-warning fw-bold';
                    }

                    $data .= '<input type="hidden" value="' . $leaveId . '" id="leaveId">';
                    $data .= '<input type="hidden" value="' . $groupId . '" id="groupId">';
                    // $data .= '<div class="col-6 col-md-6 mt-1">';
                    // $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Filed</label>';
                    // $data .= '<input type="text" class="form-control form-control-sm" value="' . $filed . '" disabled>';
                    // $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6 mt-1">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6 mt-1">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Type</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $request . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Start Date</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['startDate'] . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">End Date</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['endDate'] . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Days</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $leave['total_days'] . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Hours</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . round($details['group_hours'], 2) . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Time Range</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $details['time_range'] . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
                    $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . strtoupper($details['status']) . '" disabled>';
                    $data .= '</div>';

                    if ($code == 'medical') {
                        $data .= '<div class="col-12 col-md-6">';

                        $data .= '<div class="row">';
                        $data .= '<div class="col-12 col-md-12">';
                        $data .= '<label class="form-label small fw-semibold text-muted mb-1">Person Being Assisted</label>';
                        $data .= '</div>';
                        $data .= '<div class="col-12 col-md-12">';
                        $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                        $asst = $this->request_model->getArray('tk_leave_request_assisted_persons', $cond);
                        foreach ($asst as $a) {
                            $data .= '<span class="badge bg-secondary py-2 px-2 me-1" style="font-size: 0.70rem;">' . $a['assisted_person'] . '</span>';
                        }
                        $data .= '</div>';
                        $data .= '</div>';

                        $data .= '</div>';
                    }

                    $data .= '<div class="col-12 col-md-6">';

                    $data .= '<div class="row">';
                    $data .= '<div class="col-12 col-md-12">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Attachments</label>';
                    $data .= '</div>';
                    $data .= '<div class="col-12 col-md-12">';
                    $i = 0;
                    $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                    $attachments = $this->request_model->getArray('tk_leave_request_attachments', $cond);
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

                    if ($code !== 'medical') {
                        $data .= '<div class="col-12 col-md-6">';
                        $data .= '</div>';
                    }

                    $data .= '<div class="col-12 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Submitted Date</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $sDate . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-12 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Updated Date</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $uDate . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-12 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Submitted Geo Location</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $details['location'] . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-12 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Updated Geo Location</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $details['updated_location'] . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-12 col-md-12">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Remarks</label>';
                    $data .= '<textarea class="form-control form-control-sm" disabled>' . $details['remarks'] . '</textarea>';
                    $data .= '</div>';
                }
            }
            $data .= '</div>';

            $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
            $check = $this->request_model->getRow('tk_leave_request_approved', $cond);
            if ($check) {

                $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                $cond = "leaveId='$leaveId' AND groupId='$groupId' ORDER BY tkraId DESC LIMIT 1";
                $approvals = $this->request_model->getArray('tk_leave_request_approved', $cond);
                foreach ($approvals as $approval) {

                    $date = date('F j, Y', strtotime($approval['approved_datetime']));
                    $time = date('h:i:s A', strtotime($approval['approved_datetime']));
                    $name = $this->request_model->getRow('employee3', ['emp_id' => $approval['approved_by']])['name'];

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
        }

        echo json_encode(['form' => $data]);
    }

    public function dtr_duty()
    {
        $data = '';
        $input = $this->input->post();
        $id = $input['id'];
        $cond = ['attendance_id' => $id];
        $groups = $this->request_model->getArray('tk_attendance_groups', $cond);
        foreach ($groups as $g) {

            $category = strtoupper($g['category']);
            $time_range = $g['time_range'];
            $th = round($g['total_hours'], 2);
            $remarks = '...';
            if ($g['remarks']) {
                $remarks = $g['remarks'];
            }

            $location = $g['location'];
            $stat = $g['status'];


            if ($g['type'] == 'outoffice') {
                $type = 'OUT OFFICE';
            } else if ($g['type'] == 'inoffice') {
                $type = 'IN OFFICE';
            } else {
                $type = 'REGULAR';
            }

            if ($g['status'] == 'approved') {

                $icon = '<i class="bi bi-check-circle"></i>';
                $color = 'text-success';
            } else if ($g['status'] == 'disapproved') {
                $icon = '<i class="bi bi-x-circle"></i>';
                $color = 'text-danger';
            } else {
                $icon = '⏳';
                $color = 'text-warning';
            }

            $data .= '<div class="col-12 col-sm-12 d-none d-sm-block mb-2">';

            $data .= '<div class="duty-stat-card stat-' . $stat . ' shadow-sm">';

            $data .= '<div class="d-flex align-items-center justify-content-between mb-1">';
            $data .= '<span class="text-muted fw-bold  fs-6">' . $type . ' <i class="small">(' . ucwords($g['status']) . ')</i></span>';
            $data .= '<div class="ledger-stat-icon">' . $icon . '</div>';
            $data .= '</div>';

            $data .= '<div class="duty-stat-value ' . $color . '">';
            $data .= $th . ' <span class="fs-6 fw-semibold text-muted">Hours</span>';
            $data .= '</div>';

            if ($g['type'] == 'outoffice') {
                $data .= '<small class="text-muted d-none d-sm-block" style="font-size: 0.80rem;"><i class="bi bi-bookmarks"></i> ' . $category . '</small>';
            }
            $data .= '<small class="text-muted d-none d-sm-block" style="font-size: 0.80rem;"><i class="bi bi-clock"></i> ' . $time_range . '</small>';
            $data .= '<small class="text-muted d-none d-sm-block" style="font-size: 0.80rem;"><i class="bi bi-geo-alt"></i> ' . $location . '</small>';
            $data .= '<small class="text-muted d-none d-sm-block" style="font-size: 0.80rem;"><i class="bi bi-chat-left-dots"></i> ' . $remarks . '</small>';

            $data .= '</div>';

            $data .= '</div>';
        }
        $data .= '</div>';

        echo json_encode(['form' => $data]);
    }
}
