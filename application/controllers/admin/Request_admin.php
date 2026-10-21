<?php

class Request_admin extends Portal_Controller
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

    public function requestPage()
    {
        $leaves = $this->request->getArray('tk_request_type', ['status' => 'active']);
        foreach ($leaves as $l) {
            $code = $l['code'];
            $request = $l['request'];

            echo '<div class="col-12 col-md-4">';
            echo '<div class="log-stat-card shadow-sm gap-3">';
            echo '<div class="fw-bold text-dark fs-6 text-center pb-3"><u>' . $request . '</u> </div>';
            echo '<div class="row mt-3">';

            $status = ['pending', 'approved', 'disapproved'];
            foreach ($status as $s) {
                if ($s == 'pending') {
                    $border = 'border border-warning';
                    $color = 'text-warning';
                } else if ($s == 'approved') {
                    $border = 'border border-success';
                    $color = 'text-success';
                } else {
                    $border = 'border border-danger';
                    $color = 'text-danger';
                }

                if ($this->usertype == 'administrator') {
                    $fixed = $this->request->count_leave($code, $s);
                    $grouped = $this->request->count_leave_group($code, $s);
                } else {
                    $fixed = $this->request->count_leave_sup($code, $s);
                    $grouped = $this->request->count_leave_group_sup($code, $s);
                }

                $leave = count($fixed) + count($grouped);

                echo '<div class="col-4 text-center">';
                echo '<a href="javascript:;" class="text-decoration-none" onclick="viewRequests(\'' . $code . '\',\'' . $request . '\',\'' . $s . '\')">';
                echo '<div class="rounded-circle ' . $border . ' w-50 mx-auto d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">';
                echo '<div class="fw-bold fs-6 ' . $color . '">' . $leave . '</div>';
                echo '</div>';
                echo '<div class="fw-semibold ' . $color . '">' . ucwords($s) . '</div>';
                echo '</a>';
                echo '</div>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        //DAYOFF
        echo '<div class="col-12 col-md-4">';
        echo '<div class="log-stat-card shadow-sm gap-3">';
        echo '<div class="fw-bold text-dark fs-6 text-center pb-3"><u>Change Dayoff</u> </div>';
        echo '<div class="row mt-3">';
        $status = ['pending', 'approved', 'disapproved'];
        foreach ($status as $s) {
            if ($s == 'pending') {
                $border = 'border border-warning';
                $color = 'text-warning';
            } else if ($s == 'approved') {
                $border = 'border border-success';
                $color = 'text-success';
            } else {
                $border = 'border border-danger';
                $color = 'text-danger';
            }

            if ($this->usertype == 'administrator') {
                $dayoff = $this->request->getArray('tk_change_dayoff', ['status' => $s]);
            } else {
                $dayoff = $this->request->count_do_sup($s);
            }

            echo '<div class="col-4 text-center">';
            echo '<a href="javascript:;" class="text-decoration-none" onclick="viewChangeDayoff(\'' . $s . '\')">';
            echo '<div class="rounded-circle ' . $border . ' w-50 mx-auto d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">';
            echo '<div class="fw-bold fs-6 ' . $color . '">' . count($dayoff) . '</div>';
            echo '</div>';
            echo '<div class="fw-semibold ' . $color . '">' . ucwords($s) . '</div>';
            echo '</a>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
        echo '</div>';

        //DUTY Out of Office
        echo '<div class="col-12 col-md-4">';
        echo '<div class="log-stat-card shadow-sm gap-3">';
        echo '<div class="fw-bold text-dark fs-6 text-center pb-3"><u>Out of Office</u> </div>';
        echo '<div class="row mt-3">';
        $status = ['pending', 'approved', 'disapproved'];
        foreach ($status as $s) {

            if ($s == 'pending') {
                $border = 'border border-warning';
                $color = 'text-warning';
            } else if ($s == 'approved') {
                $border = 'border border-success';
                $color = 'text-success';
            } else {
                $border = 'border border-danger';
                $color = 'text-danger';
            }

            if ($this->usertype == 'administrator') {
                $cond = ['type' => 'outoffice', 'status' => $s];
                $duty = $this->request->getArray('tk_attendance_groups', $cond);
            } else {
                $duty = $this->request->count_duty_sup($s);
            }
            echo '<div class="col-4 text-center">';
            echo '<a href="javascript:;" class="text-decoration-none" onclick="viewDutyHours(\'' . $s . '\')">';
            echo '<div class="rounded-circle ' . $border . ' w-50 mx-auto d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">';
            echo '<div class="fw-bold fs-6 ' . $color . '">' . count($duty) . '</div>';
            echo '</div>';
            echo '<div class="fw-semibold ' . $color . '">' . ucwords($s) . '</div>';
            echo '</a>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
        echo '</div>';


        //VL Payment

        $year = date('Y');
        $a = $this->request->getRow('tk_vl_payment_access', ['year' =>  $year, 'status' => 1]);
        if ($a) {

            echo '<div class="col-12 col-md-4">';
            echo '<div class="log-stat-card shadow-sm gap-3">';
            echo '<div class="fw-bold text-dark fs-6 text-center pb-3"><u>VL Payment</u> </div>';
            echo '<div class="row mt-3">';
            $status = [0, 1];
            foreach ($status as $s) {

                if ($s == 0) {
                    $stat = 'Pending';
                    $border = 'border border-warning';
                    $color = 'text-warning';
                } else if ($s == 1) {
                    $stat = 'Approved';
                    $border = 'border border-success';
                    $color = 'text-success';
                } else {
                    $stat = 'Disapproved';
                    $border = 'border border-danger';
                    $color = 'text-danger';
                }

                if ($this->usertype == 'administrator') {
                    $vl = $this->request->getArray('tk_vl_payment', ['status' => $s]);
                } else {
                    $vl = $this->request->count_vlPayment_sup($s);
                }

                echo '<div class="col-4 text-center">';
                echo '<a href="javascript:;" class="text-decoration-none" onclick="viewVLperYear(\'' . $s . '\')">';
                echo '<div class="rounded-circle ' . $border . ' w-50 mx-auto d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">';
                echo '<div class="fw-bold fs-6 ' . $color . '">' . count($vl) . '</div>';
                echo '</div>';
                echo '<div class="fw-semibold ' . $color . '">' . $stat . '</div>';
                echo '</a>';
                echo '</div>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }

    public function view_requests()
    {
        $input = $this->input->post();
        $code = $input['code'];
        $status = $input['status'];
        echo '<div class="align-items-center pb-2 border-bottom">';
        $i = 0;
        $scheds = $this->request->getArray('tk_cutoff_schedule', ['status' => 1]);
        foreach ($scheds as $c) {
            $i++;
            $id = $c['id'];
            $sFC = $c['startFC'];
            $eFC = $c['endFC'];

            $sSC = $c['startSC'];
            $eSC = $c['endSC'];

            $schedule = "$sFC-$eFC / $sSC-$eSC";

            $payload = "$id|$code|$status";

            if ($i == 1) {
                echo '<a href="javascript:;" id="' . $payload . '" class="btn btn-sm btn-primary co_schedule active me-1">' . $schedule . '</a>';
            } else {
                echo '<a href="javascript:;" id="' . $payload . '" class="btn btn-sm btn-secondary co_schedule me-1">' . $schedule . '</a>';
            }
        }
        echo '</div>';
        echo '<div class="d-none d-md-block table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle" id="cutoffTable">
                    <thead>
                        <tr>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>' . ucwords($status) . '</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>';
        echo '</div>';
    }

    public function requestCutoffTable()
    {
        $payload = $this->input->post('payload');
        list($co, $code, $status) = explode('|', $payload);

        $yearMax = date('Y');
        $sql = "SELECT YEAR(MAX(endDate)) AS earliest_year, MONTH(MAX(endDate)) AS earliest_month FROM `tk_leave_requests`;";
        $max = $this->request->queryRow($sql);
        if ($max) {
            $yearMax = $max['earliest_year'];
        }

        $yearMin = date('Y');
        $sql = "SELECT YEAR(MIN(startDate)) AS earliest_year, MONTH(MIN(startDate)) AS earliest_month FROM `tk_leave_requests`;";
        $min = $this->request->queryRow($sql);
        if ($min) {
            $yearMin = $min['earliest_year'];
        }

        $data = [];
        $cutoffs = $this->request->getArray('tk_cutoff_schedule', ['id' => $co, 'status' => 1]);
        foreach ($cutoffs as $c) {
            $co = $c['id'];

            $sFC = $c['startFC'];
            $eFC = $c['endFC'];

            $sSC = $c['startSC'];
            $eSC = $c['endSC'];

            for ($y = $yearMax; $y >= $yearMin; $y--) {
                for ($m = 12; $m >= 1; $m--) {


                    $fco1 = date('F j, Y', strtotime("$y-$m-$sFC"));
                    $fco2 = date('F j, Y', strtotime("$y-$m-$eFC +1 month"));

                    $sco1 = date('F j, Y', strtotime("$y-$m-$sSC"));
                    $sco2 = date('F j, Y', strtotime("$y-$m-$eSC"));

                    $f1 = date('Y-m-d', strtotime($fco1));
                    $f2 = date('Y-m-d', strtotime($fco2));
                    $s1 = date('Y-m-d', strtotime($sco1));
                    $s2 = date('Y-m-d', strtotime($sco2));

                    $o1 = date('Ymd', strtotime($f1));
                    $o2 = date('Ymd', strtotime($s1));

                    if ($this->usertype == 'administrator') {
                        $r1f = $this->request->co_fixed_leave($f1, $f2, $co, $code, $status);
                        $r1g = $this->request->co_grouped_leave($f1, $f2, $co, $code, $status);

                        $r2f = $this->request->co_fixed_leave($s1, $s2, $co, $code, $status);
                        $r2g = $this->request->co_grouped_leave($s1, $s2, $co, $code, $status);
                    } else {
                        $r1f = $this->request->co_fixed_leave_sup($f1, $f2, $co, $code, $status);
                        $r1g = $this->request->co_grouped_leave_sup($f1, $f2, $co, $code, $status);

                        $r2f = $this->request->co_fixed_leave_sup($s1, $s2, $co, $code, $status);
                        $r2g = $this->request->co_grouped_leave_sup($s1, $s2, $co, $code, $status);
                    }

                    if ($r1f || $r1g) {

                        $p1 = "$co|1|$fco1|$fco2";

                        $col1 = [];
                        $col1[] = '<span style="display:none;">' . $o1 . '</span>' . $fco1;
                        $col1[] = $fco2;
                        $col1[] = count($r1f) + count($r1g);
                        $col1[] = '<a href="javascript:;" id="' . $p1 . '" class="btn btn-sm btn-orange leave_list">View</a>';
                        $data[] = $col1;
                    }

                    if ($r2f || $r2g) {
                        $p2 = "$co|2|$sco1|$sco2";

                        $col2 = [];
                        $col2[] = '<span style="display:none;">' . $o2 . '</span>' . $sco1;
                        $col2[] = $sco2;
                        $col2[] = count($r2f) + count($r2g);
                        $col2[] = '<a href="javascript:;" id="' . $p2 . '" class="btn btn-sm btn-orange leave_list">View</a>';
                        $data[] = $col2;
                    }
                }
            }
        }
        echo json_encode(['data' => $data]);
    }

    public function request_leave_list()
    {
        $input = $this->input->post();
        $co = $input['co'];
        $cutoff = $input['cutoff'];
        $startDate = date('Y-m-d', strtotime($input['startDate']));
        $endDate = date('Y-m-d', strtotime($input['endDate']));
        $code = $input['code'];
        $status = $input['status'];

        if ($this->usertype == 'administrator') {
            $req1 = $this->request->co_fixed_leave($startDate, $endDate, $co, $code, $status);
            $req2 = $this->request->co_grouped_leave($startDate, $endDate, $co, $code, $status);
        } else {
            $req1 = $this->request->co_fixed_leave_sup($startDate, $endDate, $co, $code, $status);
            $req2 = $this->request->co_grouped_leave_sup($startDate, $endDate, $co, $code, $status);
        }

        $data = [];
        foreach ($req1 as $r) {
            $id = $r['leaveId'];
            $empid = $r['emp_id'];
            $name = $this->request->getRow('employee3', ['emp_id' => $empid])['name'];

            $filed = date('M. j, Y', strtotime($r['created_at']));
            $start = date('M. j, Y', strtotime($r['startDate']));
            $end = date('M. j, Y', strtotime($r['endDate']));
            $period = $start . ' - ' . $end;
            if ($r['startDate'] == $r['endDate']) {
                $period = $start;
            }

            $groupId = '';
            if ($r['status'] === 'approved') {
                $stat = '<span class="badge bg-success py-1" style="font-size: 0.68rem;">Approved</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewRequestApproved(\'' . $id . '\',\'' . $groupId . '\',\'' . $code . '\')">View</a>';
            } else if ($r['status'] === 'disapproved') {
                $stat = '<span class="badge bg-danger py-1" style="font-size: 0.68rem;">Disapproved</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewRequestApproved(\'' . $id . '\',\'' . $groupId . '\',\'' . $code . '\')">View</a>';
            } else {
                $stat = '<span class="badge bg-warning text-white py-1" style="font-size: 0.68rem;">Pending</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewRequestPending(\'' . $id . '\',\'' . $groupId . '\',\'' . $code . '\')">View</a>';
            }
            $order =  date('YmdHis', strtotime($r['startDate']));


            $col1 = [];
            $col1[] = '<span style="display:none;">' . $order . '</span>' . $filed;
            $col1[] = $name;
            $col1[] = $period;
            $col1[] = $r['total_days'];
            $col1[] = round($r['total_hours'], 2);
            $col1[] = $stat;
            $col1[] = $action;
            $data[] = $col1;
        }

        foreach ($req2 as $r) {
            $id = $r['leaveId'];
            $groupId = $r['groupId'];

            $empid = $r['emp_id'];
            $name = $this->request->getRow('employee3', ['emp_id' => $empid])['name'];
            $filed = date('M. j, Y', strtotime($r['created']));

            $cond = ['leaveId' => $id];
            $leave = $this->request->getRow('tk_leave_requests', $cond);
            $start = date('M. j, Y', strtotime($leave['startDate']));
            $end = date('M. j, Y', strtotime($leave['endDate']));
            $period = $start . ' - ' . $end;
            if ($leave['startDate'] == $leave['endDate']) {
                $period = $start;
            }
            if ($r['status'] === 'approved') {
                $stat = '<span class="badge bg-success py-1" style="font-size: 0.68rem;">Approved</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewRequestApproved(\'' . $id . '\',\'' . $groupId . '\',\'' . $code . '\')">View</a>';
            } else if ($r['status'] === 'disapproved') {
                $stat = '<span class="badge bg-danger py-1" style="font-size: 0.68rem;">Disapproved</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewRequestApproved(\'' . $id . '\',\'' . $groupId . '\',\'' . $code . '\')">View</a>';
            } else {
                $stat = '<span class="badge bg-warning text-white py-1" style="font-size: 0.68rem;">Pending</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewRequestPending(\'' . $id . '\',\'' . $groupId . '\',\'' . $code . '\')">View</a>';
            }

            $order =  date('YmdHis', strtotime($leave['startDate']));


            $col2 = [];
            $col2[] = '<span style="display:none;">' . $order . '</span>' . $filed;
            $col2[] = $name;
            $col2[] = $period;
            $col2[] = $leave['total_days'];
            $col2[] = round($r['group_hours'], 2);
            $col2[] = $stat;
            $col2[] = $action;
            $data[] = $col2;
        }

        echo json_encode(['data' => $data]);
    }

    public function view_pending_requests()
    {
        $data = '';
        $input = $this->input->post();
        $leaveId = $input['leaveId'];
        $groupId = $input['groupId'];
        $code = $input['code'];
        $cond = ['leaveId' => $leaveId];
        $leave = $this->request->getRow('tk_leave_requests', $cond);
        if ($leave) {
            if ($leave['isFixed']) {
                $request = $this->request->getRow('tk_request_type', ['code' => $code, 'status' => 'active'])['request'];
                $name = $this->request->getRow('employee3', ['emp_id' => $leave['emp_id']])['name'];
                $filed = date('Y-m-d', strtotime($leave['created_at']));

                $data .= '<div class="row g-3">';
                $data .= '<input type="hidden" value="' . $leaveId . '" id="leaveId">';
                $data .= '<input type="hidden" value="' . $groupId . '" id="groupId">';
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
                $data .= '<input type="hidden" id="original_status" value="' . $leave['status'] . '">';
                $data .= '<input type="text" class="form-control form-control-sm text-warning fw-bold" value="' . strtoupper($leave['status']) . '" disabled>';
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
                    $mat = $this->request->getRow('tk_maternity_leave_details', $cond);

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

                $data .= '<div class="col-12 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Remarks</label>';
                $data .= '<textarea class="form-control form-control-sm" disabled>' . $leave['reason'] . '</textarea>';
                $data .= '</div>';

                $data .= '<div class="col-12 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
                $data .= '<textarea class="form-control form-control-sm" placeholder="Comments..." id="approverComments"></textarea>';
                $data .= '</div>';

                $data .= '</div>';
            } else {
                $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                $details = $this->request->getRow('tk_leave_request_details', $cond);
                if ($details) {
                    $request = $this->request->getRow('tk_request_type', ['code' => $code, 'status' => 'active'])['request'];
                    $name = $this->request->getRow('employee3', ['emp_id' => $leave['emp_id']])['name'];
                    $sDate = date('F j, Y H:i A', strtotime($details['created']));
                    $uDate = '';
                    if ($details['updated_date'] != '0000-00-00 00:00:00') {
                        $uDate = date('F j, Y H:i A', strtotime($details['updated_date']));
                    }

                    $data .= '<div class="row g-3">';
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
                    $data .= '<input type="hidden" id="original_status" value="' . $details['status'] . '">';
                    $data .= '<input type="text" class="form-control form-control-sm text-warning fw-bold" value="' . strtoupper($details['status']) . '" disabled>';
                    $data .= '</div>';

                    if ($code == 'medical') {
                        $data .= '<div class="col-12 col-md-6">';

                        $data .= '<div class="row">';
                        $data .= '<div class="col-12 col-md-12">';
                        $data .= '<label class="form-label small fw-semibold text-muted mb-1">Person Being Assisted</label>';
                        $data .= '</div>';
                        $data .= '<div class="col-12 col-md-12">';
                        $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                        $asst = $this->request->getArray('tk_leave_request_assisted_persons', $cond);
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

                    $data .= '<div class="col-12 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Remarks</label>';
                    $data .= '<textarea class="form-control form-control-sm" disabled>' . $details['remarks'] . '</textarea>';
                    $data .= '</div>';

                    $data .= '<div class="col-12 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
                    $data .= '<textarea class="form-control form-control-sm" placeholder="Comments..." id="approverComments"></textarea>';
                    $data .= '</div>';

                    $data .= '</div>';
                }
            }
        }

        echo json_encode(['form' => $data]);
    }

    public function view_approved_requests()
    {
        $data = '';
        $actualStatus = '';
        $input = $this->input->post();
        $leaveId = $input['leaveId'];
        $groupId = $input['groupId'];
        $code = $input['code'];
        $cond = ['leaveId' => $leaveId];
        $leave = $this->request->getRow('tk_leave_requests', $cond);
        if ($leave) {
            $data .= '<div class="row g-3">';
            if ($leave['isFixed']) {
                $actualStatus = $leave['status'];
                $request = $this->request->getRow('tk_request_type', ['code' => $code, 'status' => 'active'])['request'];
                $name = $this->request->getRow('employee3', ['emp_id' => $leave['emp_id']])['name'];
                $filed = date('Y-m-d', strtotime($leave['created_at']));

                $statusColor = 'text-success fw-bold';
                if ($leave['status'] === 'disapproved') {
                    $statusColor = 'text-danger fw-bold';
                }

                $data .= '<input type="hidden" value="' . $leaveId . '" id="leaveId">';
                $data .= '<input type="hidden" value="' . $groupId . '" id="groupId">';
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
                $data .= '<input type="hidden" id="approval_status" value="' . $leave['status'] . '">';
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
                    $mat = $this->request->getRow('tk_maternity_leave_details', $cond);

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
                $details = $this->request->getRow('tk_leave_request_details', $cond);
                if ($details) {
                    $actualStatus = $details['status'];
                    $request = $this->request->getRow('tk_request_type', ['code' => $code, 'status' => 'active'])['request'];
                    $name = $this->request->getRow('employee3', ['emp_id' => $leave['emp_id']])['name'];

                    $sDate = date('F j, Y H:i A', strtotime($details['created']));
                    $uDate = '';
                    if ($details['updated_date'] != '0000-00-00 00:00:00') {
                        $uDate = date('F j, Y H:i A', strtotime($details['updated_date']));
                    }

                    $statusColor = 'text-success fw-bold';
                    if ($details['status'] === 'disapproved') {
                        $statusColor = 'text-danger fw-bold';
                    }

                    $data .= '<input type="hidden" value="' . $leaveId . '" id="leaveId">';
                    $data .= '<input type="hidden" value="' . $groupId . '" id="groupId">';
                    // $data .= '<div class="col-6 col-md-6 mt-1">';
                    // $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Filed</label>';
                    // $data .= '<input type="text" class="form-control form-control-sm" value="' . $filed . '" disabled>';
                    // $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
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
                    $data .= '<input type="hidden" id="approval_status" value="' . $details['status'] . '">';
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
                        $asst = $this->request->getArray('tk_leave_request_assisted_persons', $cond);
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
            $check = $this->request->getRow('tk_leave_request_approved', $cond);
            if ($check) {

                $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                $cond = "leaveId='$leaveId' AND groupId='$groupId' ORDER BY tkraId DESC LIMIT 1";
                $approvals = $this->request->getArray('tk_leave_request_approved', $cond);
                foreach ($approvals as $approval) {

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

                $data .= '<div class="row mt-3">';
                $data .= '<div class="col-12 col-md-12">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
                $data .= '<textarea class="form-control form-control-sm" id="approverComments" placeholder="Comments..."></textarea>';
                $data .= '</div>';
                $data .= '</div>';
            }
        }

        echo json_encode(['form' => $data, 'status' => $actualStatus]);
    }

    public function approveRequest()
    {
        $input = $this->input->post();
        $leaveId = $input['leaveId'];
        $remarks = $input['remarks'];
        $groupId = $input['groupId'];
        $status = $input['status'];

        $cond = ['leaveId' => $leaveId];
        $leave = $this->request->getRow('tk_leave_requests', $cond);
        if ($leave) {

            if ($leave['isFixed']) {
                $cond = ['status' => 'active', 'code' => $leave['code']];
                $requestType = $this->request->getRow('tk_request_type', $cond);
                $code = $requestType['code'];
                $request = $requestType['request'];

                $data = [
                    'leaveId'           => $leaveId,
                    'approved_by'       => $this->userId,
                    'approved_datetime' => date('Y-m-d H:i:s'),
                    'comments'          => $remarks,
                    'status'            => $status,
                ];

                $approved = $this->request->insert('tk_leave_request_approved', $data);

                if ($code === 'maternity') {
                    $data = ['status' => $status];
                    $cond = ['leaveId' => $leaveId];
                    $this->request->update_table('tk_maternity_leave_details', $data, $cond);
                }

                if ($code === 'paternity') {
                    $data = ['status' => $status];
                    $cond = ['leaveId' => $leaveId];
                    $this->request->update_table('tk_paternity_leave_details', $data, $cond);
                }

                // if ($status == 'disapproved') {
                //     $this->request->delete_table('tk_leave_request_dates', ['leaveId' => $leaveId]);
                // }

                if ($code === 'vacation') {
                    if ($status == 'disapproved') {
                        $yearStart = (int) date('Y', strtotime($leave['startDate']));
                        $yearEnd   = (int) date('Y', strtotime($leave['endDate']));
                        while ($yearStart <= $yearEnd) {

                            $this->count_vl_balance($leave['emp_id'], $yearStart);

                            $yearStart++;
                        }
                    }
                }

                $cond = ['leaveId' => $leaveId];
                $data = ['status' => $status, 'approvedBy' => $this->userId, 'approvedDate' => date('Y-m-d H:i:s')];
                $update = $this->request->update_table('tk_leave_requests', $data, $cond);

                if ($approved && $update) {
                    echo json_encode(['message' => 'success', 'code' => $code, 'request' => $request]);
                    return;
                }
            } else {
                $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                $details = $this->request->getRow('tk_leave_request_details', $cond);
                if ($details) {
                    $cond = ['status' => 'active', 'code' => $details['code']];
                    $requestType = $this->request->getRow('tk_request_type', $cond);
                    $code = $requestType['code'];
                    $request = $requestType['request'];

                    $data = [
                        'leaveId'           => $leaveId,
                        'groupId'           => $groupId,
                        'approved_by'       => $this->userId,
                        'approved_datetime' => date('Y-m-d H:i:s'),
                        'comments'          => $remarks,
                        'status'            => $status,
                    ];

                    $approved = $this->request->insert('tk_leave_request_approved', $data);

                    $data = ['status' => $status];
                    $cond = ['leaveId' => $leaveId, 'groupId' => $groupId];
                    $update = $this->request->update_table('tk_leave_request_details', $data, $cond);

                    $cond = ['leaveId' => $leaveId, 'status' => 'pending'];
                    $check = $this->request->getRow('tk_leave_request_details', $cond);
                    if (!$check) {
                        $data = ['status' => $status];
                        $cond = ['leaveId' => $leaveId, 'status' => 'pending'];
                        // $this->request->update_table('tk_leave_requests', $data, $cond);
                    }

                    if ($approved && $update) {
                        echo json_encode(['message' => 'success', 'code' => $code, 'request' => $request]);
                        return;
                    }
                }
            }
        }
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

    public function view_change_dayoff()
    {
        $input = $this->input->post();
        $status = $input['status'];
        echo '<div class="align-items-center pb-2 border-bottom">';
        $i = 0;
        $scheds = $this->request->getArray('tk_cutoff_schedule', ['status' => 1]);
        foreach ($scheds as $c) {
            $i++;
            $id = $c['id'];
            $sFC = $c['startFC'];
            $eFC = $c['endFC'];

            $sSC = $c['startSC'];
            $eSC = $c['endSC'];

            $schedule = "$sFC-$eFC / $sSC-$eSC";

            $payload = "$id|$status";

            if ($i == 1) {
                echo '<a href="javascript:;" id="' . $payload . '" class="btn btn-sm btn-primary do_schedule active me-1">' . $schedule . '</a>';
            } else {
                echo '<a href="javascript:;" id="' . $payload . '" class="btn btn-sm btn-secondary do_schedule me-1">' . $schedule . '</a>';
            }
        }
        echo '</div>';
        echo '<div class="d-none d-md-block table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle" id="doCutoffTable">
                    <thead>
                        <tr>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>' . ucwords($status) . '</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>';
        echo '</div>';
    }

    public function doCutoffTable()
    {
        $payload = $this->input->post('payload');
        list($co, $status) = explode('|', $payload);

        $yearMax = date('Y');
        $sql = "SELECT YEAR(MAX(old_dayoff)) AS earliest_year, MONTH(MAX(old_dayoff)) AS earliest_month FROM `tk_change_dayoff`;";
        $max = $this->request->queryRow($sql);
        if ($max) {
            $yearMax = $max['earliest_year'];
        }

        $yearMin = date('Y');
        $sql = "SELECT YEAR(MIN(old_dayoff)) AS earliest_year, MONTH(MIN(old_dayoff)) AS earliest_month FROM `tk_change_dayoff`;";
        $min = $this->request->queryRow($sql);
        if ($min) {
            $yearMin = $min['earliest_year'];
        }

        $data = [];
        $cutoffs = $this->request->getArray('tk_cutoff_schedule', ['id' => $co, 'status' => 1]);
        foreach ($cutoffs as $c) {
            $co = $c['id'];

            $sFC = $c['startFC'];
            $eFC = $c['endFC'];

            $sSC = $c['startSC'];
            $eSC = $c['endSC'];

            for ($y = $yearMax; $y >= $yearMin; $y--) {
                for ($m = 12; $m >= 1; $m--) {


                    $fco1 = date('F j, Y', strtotime("$y-$m-$sFC"));
                    $fco2 = date('F j, Y', strtotime("$y-$m-$eFC +1 month"));

                    $sco1 = date('F j, Y', strtotime("$y-$m-$sSC"));
                    $sco2 = date('F j, Y', strtotime("$y-$m-$eSC"));

                    $f1 = date('Y-m-d', strtotime($fco1));
                    $f2 = date('Y-m-d', strtotime($fco2));
                    $s1 = date('Y-m-d', strtotime($sco1));
                    $s2 = date('Y-m-d', strtotime($sco2));

                    $o1 = date('Ymd', strtotime($f1));
                    $o2 = date('Ymd', strtotime($s1));

                    if ($this->usertype == 'administrator') {
                        $dos1 = $this->request->co_dayoff($f1, $f2, $co, $status);
                        $dos2 = $this->request->co_dayoff($s1, $s2, $co, $status);
                    } else {
                        $dos1 = $this->request->co_dayoff_sup($f1, $f2, $co, $status);
                        $dos2 = $this->request->co_dayoff_sup($s1, $s2, $co, $status);
                    }

                    if ($dos1) {

                        $p1 = "$co|1|$fco1|$fco2";

                        $col1 = [];
                        $col1[] = '<span style="display:none;">' . $o1 . '</span>' . $fco1;
                        $col1[] = $fco2;
                        $col1[] = count($dos1);
                        $col1[] = '<a href="javascript:;" id="' . $p1 . '" class="btn btn-sm btn-orange dayoff_list">View</a>';
                        $data[] = $col1;
                    }

                    if ($dos2) {
                        $p2 = "$co|2|$sco1|$sco2";

                        $col2 = [];
                        $col2[] = '<span style="display:none;">' . $o2 . '</span>' . $sco1;
                        $col2[] = $sco2;
                        $col2[] = count($dos2);
                        $col2[] = '<a href="javascript:;" id="' . $p2 . '" class="btn btn-sm btn-orange dayoff_list">View</a>';
                        $data[] = $col2;
                    }
                }
            }
        }
        echo json_encode(['data' => $data]);
    }

    public function request_dayoff_list()
    {
        $input = $this->input->post();
        $co = $input['co'];
        $cutoff = $input['cutoff'];
        $startDate = date('Y-m-d', strtotime($input['startDate']));
        $endDate = date('Y-m-d', strtotime($input['endDate']));
        $status = $input['status'];

        if ($this->usertype == 'administrator') {
            $dos = $this->request->co_dayoff($startDate, $endDate, $co, $status);
        } else {
            $dos = $this->request->co_dayoff_sup($startDate, $endDate, $co, $status);
        }

        $data = [];
        foreach ($dos as $do) {
            $id = $do['tkcdoId'];
            $empid = $do['emp_id'];
            $name = $this->request->getRow('employee3', ['emp_id' => $empid])['name'];
            $filed = date('M. j, Y', strtotime($do['created']));
            $oldDayoff = date('M. j, Y', strtotime($do['old_dayoff']));
            $newDayoff = date('M. j, Y', strtotime($do['new_dayoff']));
            $status = $do['status'];

            $old = date('l', strtotime($do['old_dayoff']));
            $new = date('l', strtotime($do['new_dayoff']));

            if ($do['status'] === 'approved') {
                $status = '<span class="badge bg-success py-1" style="font-size: 0.68rem;">Approved</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewCDOApproved(\'' . $id . '\')">View</a>';
            } else if ($do['status'] === 'disapproved') {
                $status = '<span class="badge bg-danger py-1" style="font-size: 0.68rem;">Disapproved</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewCDOApproved(\'' . $id . '\')">View</a>';
            } else {
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewCDOPending(\'' . $id . '\')">View</a>';
                $status = '<span class="badge bg-warning text-white py-1" style="font-size: 0.68rem;">Pending</span>';
            }
            $order = date('YmdHis', strtotime($do['created']));

            $col = [];
            $col[] = '<span style="display:none;">' . $order . '</span>' . $filed;
            $col[] = $name;
            $col[] = $oldDayoff . ' (' . $old . ')';
            $col[] = $newDayoff . ' (' . $new . ')';
            $col[] = $status;
            $col[] = $action;
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function view_change_dayoff_()
    {
        $input = $this->input->post();
        $status = $input['status'];
        if ($this->usertype == 'administrator') {
            $cond = "status='$status' ORDER BY old_dayoff ASC, new_dayoff ASC";
            $dos = $this->request->getArray('tk_change_dayoff', $cond);
        } else {
            $sql = "SELECT t1.* FROM tk_change_dayoff AS t1 INNER JOIN tk_approvers_subordinates as t2 ON t1.emp_id=t2.approvee WHERE t2.approver='$this->userId' AND t1.status='$status' ORDER BY t1.old_dayoff ASC, t1.new_dayoff ASC";
            $dos = $this->request->queryArray($sql);
        }
        echo '<div class="table-responsive">';
        echo '<table class="table table-sm mb-0 align-middle text-nowrap" id="cdoTable">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Date Filed</th>';
        echo '<th>Requested By</th>';
        echo '<th>Old Dayoff</th>';
        echo '<th>New Dayoff</th>';
        echo '<th>Status</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($dos as $do) {
            $id = $do['tkcdoId'];
            $empid = $do['emp_id'];
            $name = $this->request->getRow('employee3', ['emp_id' => $empid])['name'];
            $date = date('M j, Y', strtotime($do['created']));
            $status = $do['status'];

            $old = date('l', strtotime($do['old_dayoff']));
            $new = date('l', strtotime($do['new_dayoff']));

            if ($do['status'] === 'approved') {
                $status = '<span class="badge bg-success py-1" style="font-size: 0.68rem;">Approved</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewCDOApproved(\'' . $id . '\')">View</a>';
            } else if ($do['status'] === 'disapproved') {
                $status = '<span class="badge bg-danger py-1" style="font-size: 0.68rem;">Disapproved</span>';
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewCDOApproved(\'' . $id . '\')">View</a>';
            } else {
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewCDOPending(\'' . $id . '\')">View</a>';
                $status = '<span class="badge bg-warning text-white py-1" style="font-size: 0.68rem;">Pending</span>';
            }

            echo '<tr>';
            echo '<td><span style="display: none;">' . date('YmdHis', strtotime($do['created'])) . '</span>' . $date . '</td>';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $do['old_dayoff'] . ' (' . $old . ')</td>';
            echo '<td>' . $do['new_dayoff'] . ' (' . $new . ')</td>';
            echo '<td>' . $status . '</td>';
            echo '<td>' . $action . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    public function view_pending_cdo()
    {
        $input = $this->input->post();
        $id = $input['cdoId'];
        $cdo = $this->request->getRow('tk_change_dayoff', ['tkcdoId' => $id]);
        if ($cdo) {
            $name = $this->request->getRow('employee3', ['emp_id' => $cdo['emp_id']])['name'];
            $created = date('Y-m-d', strtotime($cdo['created']));
            $data = '';
            $data .= '<div class="row g-3">';
            $data .= '<input type="hidden" value="' . $id . '" id="cdoId">';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Filed</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $created . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested Code</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $id . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Old Dayoff</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $cdo['old_dayoff'] . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">New Dayoff</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $cdo['new_dayoff'] . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . ucwords($cdo['status']) . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-12 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Remarks</label>';
            $data .= '<textarea class="form-control form-control-sm" placeholder="Remarks..." disabled>' . $cdo['remarks'] . '</textarea>';
            $data .= '</div>';
            $data .= '<div class="col-12 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
            $data .= '<textarea class="form-control form-control-sm" placeholder="Comments..." id="approverComments"></textarea>';
            $data .= '</div>';
            $data .= '</div>';
            echo json_encode(['form' => $data]);
        }
    }

    public function view_approved_cdo()
    {
        $input = $this->input->post();
        $id = $input['cdoId'];
        $cdo = $this->request->getRow('tk_change_dayoff', ['tkcdoId' => $id]);
        if ($cdo) {
            $name = $this->request->getRow('employee3', ['emp_id' => $cdo['emp_id']])['name'];
            $created = date('Y-m-d', strtotime($cdo['created']));

            $statusColor = 'text-success fw-bold';
            if ($cdo['status'] === 'disapproved') {
                $statusColor = 'text-danger fw-bold';
            }

            $data = '';
            $data .= '<div class="row g-3">';
            $data .= '<input type="hidden" value="' . $id . '" id="cdoId">';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Filed</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $created . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested Code</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $id . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Old Dayoff</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $cdo['old_dayoff'] . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">New Dayoff</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $cdo['new_dayoff'] . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
            $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . strtoupper($cdo['status']) . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-12 col-md-12">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Remarks</label>';
            $data .= '<textarea class="form-control form-control-sm" placeholder="Remarks..." disabled>' . $cdo['remarks'] . '</textarea>';
            $data .= '</div>';

            $data .= '</div>';

            $cdoDetails = $this->request->getRow('tk_change_dayoff_request', ['tkcdoId' => $id]);
            $appBy = $this->request->getRow('employee3', ['emp_id' => $cdoDetails['approved_by']])['name'];
            $appDate = date('Y-m-d h:i:s A', strtotime($cdoDetails['approved_datetime']));

            if ($cdoDetails['status'] == 'approved') {
                $label_name = 'Approved By';
                $label_date = 'Approved Date';
            } else {
                $label_name = 'Disapproved By';
                $label_date = 'Disapproved Date';
            }

            $data .= '<div class="card mt-3">';
            $data .= '<div class="card-body">';

            $data .= '<div class="row g-3">';

            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">' . $label_name . '</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $appBy . '" disabled>';
            $data .= '</div>';

            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">' . $label_date . '</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $appDate . '" disabled>';
            $data .= '</div>';

            $data .= '<div class="col-12 col-md-12">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
            $data .= '<textarea class="form-control form-control-sm" placeholder="Comments..." disabled>' . $cdoDetails['comments'] . '</textarea>';
            $data .= '</div>';

            $data .= '</div>';

            $data .= '</div>';
            $data .= '</div>';
            echo json_encode(['form' => $data]);
        }
    }

    public function approvechangeDayoff()
    {
        $input = $this->input->post();
        $cdoId = $input['cdoId'];
        $remarks = $input['remarks'];
        $status = $input['status'];

        $cond = ['tkcdoId' => $cdoId, 'status' => 'pending'];
        $check = $this->request->getRow('tk_change_dayoff', $cond);
        if ($check) {
            $date = $check['new_dayoff'];
            $reqBy = $check['emp_id'];

            $duty = $this->request->getRow('tk_attendance_records', ['emp_id' => $reqBy, 'attendance_date' => $date]);
            if ($duty) {
                echo json_encode(['message' => 'Duty Hours is active for this date.']);
                return;
            }

            $days = $this->request->getArray('tk_leave_request_dates', ['emp_id' => $reqBy, 'date' => $date]);
            foreach ($days as $d) {
                $cond = ['leaveId' => $d['leaveId'], 'status !=' => 'disapproved'];
                $leave = $this->request->getRow('tk_leave_requests', $cond);
                if ($leave) {
                    echo json_encode(['message' => 'Leave request is active for this date.']);
                    return;
                }
            }

            $cond = ['tkcdoId' => $cdoId, 'status' => 'pending'];
            $data = ['status' => $status];
            $upd = $this->request->update_table('tk_change_dayoff', $data, $cond);

            $cond = ['tkcdoId' => $cdoId, 'status' => 'pending'];
            $data = ['status' => $status, 'comments' => $remarks, 'approved_by' => $this->userId, 'approved_datetime' => date('Y-m-d H:i:s')];
            $upd = $this->request->update_table('tk_change_dayoff_request', $data, $cond);

            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function view_duty()
    {
        $input = $this->input->post();
        $status = $input['status'];
        echo '<div class="align-items-center pb-2 border-bottom">';
        $i = 0;
        $scheds = $this->request->getArray('tk_cutoff_schedule', ['status' => 1]);
        foreach ($scheds as $c) {
            $i++;
            $id = $c['id'];
            $sFC = $c['startFC'];
            $eFC = $c['endFC'];

            $sSC = $c['startSC'];
            $eSC = $c['endSC'];

            $schedule = "$sFC-$eFC / $sSC-$eSC";

            $payload = "$id|$status";

            if ($i == 1) {
                echo '<a href="javascript:;" id="' . $payload . '" class="btn btn-sm btn-primary duty_schedule active me-1">' . $schedule . '</a>';
            } else {
                echo '<a href="javascript:;" id="' . $payload . '" class="btn btn-sm btn-secondary duty_schedule me-1">' . $schedule . '</a>';
            }
        }
        echo '</div>';
        echo '<div class="d-none d-md-block table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle" id="dutyCutoffTable">
                    <thead>
                        <tr>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>' . ucwords($status) . '</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>';
        echo '</div>';
    }

    public function dutyCutoffTable()
    {
        $payload = $this->input->post('payload');
        list($co, $status) = explode('|', $payload);

        $yearMax = date('Y');
        $sql = "SELECT YEAR(MAX(attendance_date)) AS earliest_year, MONTH(MAX(attendance_date)) AS earliest_month FROM `tk_attendance_records`;";
        $max = $this->request->queryRow($sql);
        if ($max) {
            $yearMax = $max['earliest_year'];
        }

        $yearMin = date('Y');
        $sql = "SELECT YEAR(MIN(attendance_date)) AS earliest_year, MONTH(MIN(attendance_date)) AS earliest_month FROM `tk_attendance_records`;";
        $min = $this->request->queryRow($sql);
        if ($min) {
            $yearMin = $min['earliest_year'];
        }

        $data = [];
        $cutoffs = $this->request->getArray('tk_cutoff_schedule', ['id' => $co, 'status' => 1]);
        foreach ($cutoffs as $c) {
            $co = $c['id'];

            $sFC = $c['startFC'];
            $eFC = $c['endFC'];

            $sSC = $c['startSC'];
            $eSC = $c['endSC'];

            for ($y = $yearMax; $y >= $yearMin; $y--) {
                for ($m = 12; $m >= 1; $m--) {


                    $fco1 = date('F j, Y', strtotime("$y-$m-$sFC"));
                    $fco2 = date('F j, Y', strtotime("$y-$m-$eFC +1 month"));

                    $sco1 = date('F j, Y', strtotime("$y-$m-$sSC"));
                    $sco2 = date('F j, Y', strtotime("$y-$m-$eSC"));

                    $f1 = date('Y-m-d', strtotime($fco1));
                    $f2 = date('Y-m-d', strtotime($fco2));
                    $s1 = date('Y-m-d', strtotime($sco1));
                    $s2 = date('Y-m-d', strtotime($sco2));

                    $o1 = date('Ymd', strtotime($f1));
                    $o2 = date('Ymd', strtotime($s1));

                    if ($this->usertype == 'administrator') {
                        $duty1 = $this->request->co_duty($f1, $f2, $co, $status);
                        $duty2 = $this->request->co_duty($s1, $s2, $co, $status);
                    } else {
                        $duty1 = $this->request->co_duty_sup($f1, $f2, $co, $status);
                        $duty2 = $this->request->co_duty_sup($s1, $s2, $co, $status);
                    }

                    // if ($this->usertype == 'administrator') {
                    //     $cond = ['type' => 'outoffice', 'status' => $status];
                    //     $attendance = $this->request->getArray('tk_attendance_groups', $cond);
                    // } else {
                    //     $sql = "SELECT t1.* FROM tk_attendance_groups AS t1 
                    //     INNER JOIN tk_attendance_records as t2 ON t1.attendance_id=t2.id INNER JOIN tk_approvers_subordinates as t3 ON t2.emp_id=t3.approvee WHERE t3.approver='$this->userId' AND t1.type='outoffice' AND t1.status='$status'";
                    //     $attendance = $this->request->queryArray($sql);
                    // }

                    if ($duty1) {

                        $p1 = "$co|1|$fco1|$fco2";

                        $col1 = [];
                        $col1[] = '<span style="display:none;">' . $o1 . '</span>' . $fco1;
                        $col1[] = $fco2;
                        $col1[] = count($duty1);
                        $col1[] = '<a href="javascript:;" id="' . $p1 . '" class="btn btn-sm btn-orange duty_list">View</a>';
                        $data[] = $col1;
                    }

                    if ($duty2) {
                        $p2 = "$co|2|$sco1|$sco2";

                        $col2 = [];
                        $col2[] = '<span style="display:none;">' . $o2 . '</span>' . $sco1;
                        $col2[] = $sco2;
                        $col2[] = count($duty2);
                        $col2[] = '<a href="javascript:;" id="' . $p2 . '" class="btn btn-sm btn-orange duty_list">View</a>';
                        $data[] = $col2;
                    }
                }
            }
        }
        echo json_encode(['data' => $data]);
    }

    public function request_duty_list()
    {
        $input = $this->input->post();
        $co = $input['co'];
        $cutoff = $input['cutoff'];
        $startDate = date('Y-m-d', strtotime($input['startDate']));
        $endDate = date('Y-m-d', strtotime($input['endDate']));
        $status = $input['status'];

        if ($this->usertype == 'administrator') {
            $duty = $this->request->co_duty($startDate, $endDate, $co, $status);
        } else {
            $duty = $this->request->co_duty_sup($startDate, $endDate, $co, $status);
        }

        $data = [];
        foreach ($duty as $a) {
            $id     = $a['attendance_id'];
            $gIndex = $a['group_index'];

            $record = $this->request->getRow('tk_attendance_records', ['id' => $id]);
            if ($record) {
                $name = $this->request->getRow('employee3', ['emp_id' => $record['emp_id']])['name'];
                $filed = date('M j, Y', strtotime($a['created_at']));
                $order = date('YmdHis', strtotime($a['created_at']));

                if ($a['status'] === 'approved') {
                    $stat = '<span class="badge bg-success py-1" style="font-size: 0.68rem;">Approved</span>';
                } else if ($a['status'] === 'disapproved') {
                    $stat = '<span class="badge bg-danger py-1" style="font-size: 0.68rem;">Disapproved</span>';
                } else {
                    $stat = '<span class="badge bg-warning text-white py-1" style="font-size: 0.68rem;">Pending</span>';
                }
                $category = strtoupper($a['category']);
                $th = round($a['total_hours'], 2);
                $date   = date('M j, Y', strtotime($record['attendance_date']));
                $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewDutyHoursReq(\'' . $id . '\',\'' . $gIndex . '\',\'' . $status . '\')">View</a>';

                $col = [];
                $col[] = '<span style="display:none;">' . $order . '</span>' . $filed;
                $col[] = $name;
                $col[] = $date;
                $col[] = $category;
                $col[] = $th;
                $col[] = $stat;
                $col[] = $action;
                $data[] = $col;
            }
        }

        echo json_encode(['data' => $data]);

        // echo '<div class="table-responsive">';
        // echo '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="dutyTable">';
        // echo '<thead>';
        // echo '<tr>';
        // echo '<th>Date Filed</th>';
        // echo '<th>Requested By</th>';
        // echo '<th>Duty Date</th>';
        // echo '<th>Category</th>';
        // echo '<th>Total Hours</th>';
        // echo '<th>Status</th>';
        // echo '<th>Action</th>';
        // echo '</tr>';
        // echo '</thead>';
        // echo '<tbody>';


        // if ($this->usertype == 'administrator') {
        //     $cond = ['type' => 'outoffice', 'status' => $status];
        //     $attendance = $this->request->getArray('tk_attendance_groups', $cond);
        // } else {
        //     $sql = "SELECT t1.* FROM tk_attendance_groups AS t1 INNER JOIN tk_attendance_records as t2 ON t1.attendance_id=t2.id INNER JOIN tk_approvers_subordinates as t3 ON t2.emp_id=t3.approvee WHERE t3.approver='$this->userId' AND t1.type='outoffice' AND t1.status='$status'";
        //     $attendance = $this->request->queryArray($sql);
        // }

        // foreach ($attendance as $a) {
        //     $id     = $a['attendance_id'];
        //     $gIndex = $a['group_index'];

        //     $record = $this->request->getRow('tk_attendance_records', ['id' => $id]);
        //     if ($record) {
        //         $name = $this->request->getRow('employee3', ['emp_id' => $record['emp_id']])['name'];
        //         $created = date('M j, Y', strtotime($a['created_at']));
        //         $order = date('YmdHis', strtotime($a['created_at']));

        //         if ($a['status'] === 'approved') {
        //             $stat = '<span class="badge bg-success py-1" style="font-size: 0.68rem;">Approved</span>';
        //         } else if ($a['status'] === 'disapproved') {
        //             $stat = '<span class="badge bg-danger py-1" style="font-size: 0.68rem;">Disapproved</span>';
        //         } else {
        //             $stat = '<span class="badge bg-warning text-white py-1" style="font-size: 0.68rem;">Pending</span>';
        //         }
        //         $category = strtoupper($a['category']);
        //         $th = round($a['total_hours'], 2);
        //         $date   = date('M j, Y', strtotime($record['attendance_date']));
        //         $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewDutyHoursReq(\'' . $id . '\',\'' . $gIndex . '\',\'' . $status . '\')">View</a>';

        //         echo '<tr>';
        //         echo '<td><span style="display: none;">' . $order . '</span>' . $created . '</td>';
        //         echo '<td>' . $name . '</td>';
        //         echo '<td>' . $date . '</td>';
        //         echo '<td>' . $category . '</td>';
        //         echo '<td>' . $th . '</td>';
        //         echo '<td>' . $stat . '</td>';
        //         echo '<td>' . $action . '</td>';
        //         echo '</tr>';
        //     }
        // }
        // echo '</tbody>';
        // echo '</table>';
        // echo '</div>';
    }

    public function view_duty_request()
    {
        $data = '';
        $input = $this->input->post();
        $id = $input['id'];
        $gIndex = $input['gIndex'];
        $status = $input['status'];

        $cond = ['attendance_id' => $id, 'group_index' => $gIndex, 'status' => $status];
        $duty = $this->request->getRow('tk_attendance_groups', $cond);
        if ($duty) {
            $groupId = $duty['id'];
            $record = $this->request->getRow('tk_attendance_records', ['id' => $id]);
            if ($record) {

                $time_range = $duty['time_range'];

                $name = $this->request->getRow('employee3', ['emp_id' => $record['emp_id']])['name'];
                $date = date('F j, Y', strtotime($record['attendance_date']));
                $filed = date('F j, Y', strtotime($record['created_at']));

                $submitted_date = date('F j, Y h:i:s A', strtotime($duty['created_at']));
                $updated_date = '';
                if ($duty['updated_at'] != null) {
                    $updated_date = date('F j, Y h:i:s A', strtotime($duty['updated_at']));
                }

                $submitted_location = $duty['location'];
                $updated_location = $duty['updated_location'];

                $hw = round($duty['total_hours'], 2);
                $remarks = $duty['remarks'];
                $category = strtoupper($duty['category']);


                if ($duty['status'] === 'approved') {
                    $statusColor = 'text-success fw-bold';
                } else if ($duty['status'] === 'disapproved') {
                    $statusColor = 'text-danger fw-bold';
                } else {
                    $statusColor = 'text-warning fw-bold';
                }

                $reqStat = strtoupper($duty['status']);

                if ($duty['status'] == 'pending') {
                    $data .= '<div class="row g-3">';

                    $data .= '<input type="hidden" id="recordsId" value="' . $id . '">';
                    $data .= '<input type="hidden" id="groupId" value="' . $groupId . '">';

                    // $data .= '<div class="col-6 col-md-6">';
                    // $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Filed</label>';
                    // $data .= '<input type="text" class="form-control form-control-sm" value="' . $filed . '" disabled>';
                    // $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Attendance Date</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $date . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Time Range</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $time_range . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Hours Worked</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $hw . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Category</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $category . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Status</label>';
                    $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . $reqStat . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-12 col-md-12">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-0">Attachments</label>';
                    $data .= '</div>';
                    $data .= '<div class="col-12 col-md-12 mt-1">';

                    $i = 0;
                    $cond = ['attendance_id' => $id, 'group_id' => $duty['id']];
                    $attachments = $this->request->getArray('tk_attendance_attachments', $cond);
                    foreach ($attachments as $a) {
                        $i++;
                        $fn = $a['file_name'];
                        $path = $a['file_path'];
                        $link = base_url($path);

                        $data .= '<span class="btn btn-sm btn-primary me-1" onclick="viewAttachments(\'' . $link . '\')">' . $fn . '</span>';
                    }

                    if (!$attachments) {
                        $data .= '<span class="text-muted">No Attachments</span>';
                    }
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Submitted</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $submitted_date . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Updated</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $updated_date . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Submitted Location</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $submitted_location . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Updated Location</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $updated_location . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Remarks</label>';
                    $data .= '<textarea class="form-control form-control-sm" placeholder="Remarks..." disabled>' . $remarks . '</textarea>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
                    $data .= '<textarea class="form-control form-control-sm" id="approversComments" placeholder="Comments..."></textarea>';
                    $data .= '</div>';

                    $data .= '</div>';
                } else {
                    $data .= '<div class="row g-3">';

                    $data .= '<input type="hidden" id="recordsId" value="' . $id . '">';
                    $data .= '<input type="hidden" id="groupId" value="' . $groupId . '">';

                    // $data .= '<div class="col-6 col-md-6">';
                    // $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Filed</label>';
                    // $data .= '<input type="text" class="form-control form-control-sm" value="' . $filed . '" disabled>';
                    // $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Attendance Date</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $date . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Time Range</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $time_range . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Hours Worked</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $hw . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Category</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $category . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Status</label>';
                    $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . $reqStat . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-12 col-md-12">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-0">Attachments</label>';
                    $data .= '</div>';
                    $data .= '<div class="col-12 col-md-12 mt-1">';

                    $i = 0;
                    $cond = ['attendance_id' => $id, 'group_id' => $groupId];
                    $attachments = $this->request->getArray('tk_attendance_attachments', $cond);
                    foreach ($attachments as $a) {
                        $i++;
                        $fn = $a['file_name'];
                        $path = $a['file_path'];
                        $link = base_url($path);

                        $data .= '<span class="btn btn-sm btn-primary me-1" onclick="viewAttachments(\'' . $link . '\')">' . $fn . '</span>';
                    }

                    if (!$attachments) {
                        $data .= '<span class="text-muted">No Attachments</span>';
                    }
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Submitted</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $submitted_date . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Updated</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $updated_date . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Submitted Location</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $submitted_location . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Updated Location</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $updated_location . '" disabled>';
                    $data .= '</div>';

                    $data .= '<div class="col-6 col-md-12">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Remarks</label>';
                    $data .= '<textarea class="form-control form-control-sm" placeholder="Remarks..." disabled>' . $remarks . '</textarea>';
                    $data .= '</div>';

                    $data .= '</div>';

                    $cond = ['attendance_id' => $id, 'group_id' => $groupId];
                    $approval = $this->request->getRow('tk_attendance_approved', $cond);
                    if ($approval) {

                        $datetime = date('Y-m-d h:i:s A', strtotime($approval['approved_date']));
                        $name = $this->request->getRow('employee3', ['emp_id' => $approval['approved_by']])['name'];

                        if ($approval['status'] == 'approved') {
                            $label_name = 'Approved By';
                            $label_date = 'Approved Date';
                            $label_time = 'Approved Time';
                        } else {
                            $label_name = 'Disapproved By';
                            $label_date = 'Disapproved Date';
                            $label_time = 'Disapproved Time';
                        }

                        $data .= '<div class="card mb-3 mt-3">';
                        $data .= '<div class="card-body">';

                        $data .= '<div class="row g-3">';
                        $data .= '<div class="col-6 col-md-6">';
                        $data .= '<label class="form-label small fw-semibold text-muted mb-1">' . $label_name . '</label>';
                        $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
                        $data .= '</div>';
                        $data .= '<div class="col-6 col-md-6">';
                        $data .= '<label class="form-label small fw-semibold text-muted mb-1">' . $label_date . '</label>';
                        $data .= '<input type="text" class="form-control form-control-sm" value="' . $datetime . '" disabled>';
                        $data .= '</div>';
                        $data .= '<div class="col-12 col-md-12">';
                        $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approver\'s Comments</label>';
                        $data .= '<textarea class="form-control form-control-sm" disabled>' . $approval['remarks'] . '</textarea>';
                        $data .= '</div>';
                        $data .= '</div>';

                        $data .= '</div>';
                        $data .= '</div>';
                    }
                }
            }
        }

        echo json_encode(['form' => $data]);
    }

    public function approve_duty_hours()
    {
        $input = $this->input->post();
        $recordsId = $input['recordsId'];
        $groupId = $input['groupId'];
        $remarks = $input['remarks'];
        $status = $input['status'];

        $cond = ['id' => $groupId, 'attendance_id' => $recordsId, 'status' => 'pending'];
        $check = $this->request->getRow('tk_attendance_groups', $cond);
        if ($check) {
            $data = [
                'attendance_id' => $recordsId,
                'group_id'      => $groupId,
                'approved_by'   => $this->userId,
                'approved_date' => date('Y-m-d H:i:s'),
                'remarks'       => $remarks,
                'status'        => $status,
            ];
            $approval = $this->request->insert('tk_attendance_approved', $data);

            $data = ['status' => $status, 'approved_by' => $this->userId, 'approved_at' => date('Y-m-d H:i:s')];
            $cond = ['id' => $groupId, 'attendance_id' => $recordsId, 'status' => 'pending'];
            $this->request->update_table('tk_attendance_groups', $data, $cond);

            if ($approval) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }
    }

    public function employee_vl()
    {
        $input = $this->input->post();
        $empId = $input['emp_id'];
        $emp = $this->request->getRow('employee3', ['emp_id' => $empId]);
        $name = $emp['name'];
        $position = $emp['position'];

        $photo = 'assets/images/admin.png';
        $img = $this->request->getRow('applicant', ['app_id' => $empId])['photo'];
        if ($img) {
            $p = explode('/', $img);
            $photo = 'assets/images/2ndgen/' . $p[3];
            if (!file_exists($photo)) {
                $photo = 'assets/images/admin.png';
            }
        }

        echo '<div class="row g-2">';
        echo '<div class="col-md-12">';
        echo '<div class="d-flex justify-content-right mb-2 border-bottom pb-2 gap-3">';
        echo '<img src="' . base_url($photo) . '" alt="Avatar" class="img-thumbnail m-2" style="width: 100px; height: 100px;">';
        echo '<div>';
        echo '<div class="fw-bold text-dark fs-4">' . $name . '</div>';
        echo '<div class="text-muted"><i>' . $empId . '</i></div>';
        echo '<div class="text-muted mb-2"><i>' . $position . '</i></div>';
        echo '<span class="badge bg-light text-success border border-success rounded-pill px-2 py-1" style="font-size: 0.7rem;"><i class="bi bi-circle-fill me-1"></i>Active </span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="d-none d-md-block table-responsive">';
        echo '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="employee_vl_year_table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Date From</th>';
        echo '<th>Date To</th>';
        echo '<th>Total Remaining Leave</th>';
        echo '<th>Remaining Paid Leave</th>';
        echo '<th>Remaining Unpaid Leave</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';

        $this->generate_vl_year($empId, date('Y'));
        $cond = ['emp_id' => $empId];
        $vls = $this->request->getArray('tk_vacation_leave_balance', $cond);
        foreach ($vls as $vl) {
            $year = $vl['year'];
            $df = date('F j, Y', strtotime("$year-01-01"));
            $dt  = date('F j, Y', strtotime("$year-12-31"));

            $total = $vl['totalDaysRemaining'];
            $paid = $vl['paidDaysRemaining'];
            $unpaid = $vl['unpaidDaysRemaining'];
            $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="vl_list(\'' . $empId . '\',\'' . $year . '\')">View</a>';
            echo '<tr>';
            echo    '<td><span style="display: none;">' . date('Y', strtotime($df)) . '</span>' . $df . '</td>';
            echo    '<td>' . $dt . '</td>';
            echo    '<td class="fw-bold">' . $total . '</td>';
            echo    '<td class="fw-bold text-success">' . $paid . '</td>';
            echo    '<td class="fw-bold text-danger">' . $unpaid . '</td>';
            echo    '<td>' . $action . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }

    public function vl_list()
    {
        echo '<div class="d-none d-md-block table-responsive">';
        echo '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="vl_list_table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Date Filed</th>';
        echo '<th>Start Date</th>';
        echo '<th>End Date</th>';
        echo '<th>Total Days</th>';
        echo '<th>Total Hours</th>';
        echo '<th>Payment</th>';
        echo '<th>Status</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $input = $this->input->post();

        $empid = $this->input->post('empId');
        $year = $this->input->post('year');
        $vls = $this->request->get_vl_list_details($empid, $year);
        foreach ($vls as $vl) {
            $date = date('M. j, Y', strtotime($vl['created_at']));
            $start = date('M. j, Y', strtotime($vl['startDate']));
            $end = date('M. j, Y', strtotime($vl['endDate']));
            $total_days = $vl['total_days'];
            $total_hours = round($vl['total_hours'], 2);
            $payment = '<span class="badge bg-light text-danger border border-danger rounded-pill px-2 py-1" style="font-size: 0.68rem;">Unpaid</span>';
            if ($vl['payment'] == 'paid') {
                $payment = '<span class="badge bg-light text-success border border-success rounded-pill px-2 py-1" style="font-size: 0.68rem;">Paid</span>';
            }

            if ($vl['status'] == 'approved') {
                $status = '<span class="badge bg-success py-1" style="font-size: 0.68rem;">Approved</span>';
            } else if ($vl['status'] == 'disapproved') {
                $status = '<span class="badge bg-danger py-1" style="font-size: 0.68rem;">Disapproved</span>';
            } else {
                $status = '<span class="badge bg-warning text-white py-1" style="font-size: 0.68rem;">Pending</span>';
            }

            $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="vl_details(\'' . $vl['leaveId'] . '\')">View</a>';

            echo '<tr>';
            echo '<td><span style="display: none;">' . date('YmdHis', strtotime($vl['created_at'])) . '</span>' . $date . '</td>';
            echo '<td>' . $start . '</td>';
            echo '<td>' . $end . '</td>';
            echo '<td>' . $total_days . '</td>';
            echo '<td>' . $total_hours . '</td>';
            echo '<td>' . $payment . '</td>';
            echo '<td>' . $status . '</td>';
            echo '<td>' . $action . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
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

    public function search_employee()
    {
        $input = $this->input->post();
        $string = trim($input['string']);

        echo '<ul class="list-group">';
        $employees = $this->request->search_employee($string);
        foreach ($employees as $e) {
            $empId = $e['emp_id'];
            $name = trim($e['name']);
            $onclick = 'onclick="selectEmployee(\'' . $empId . '\',\'' . $name . '\')"';
            echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
        }
        echo '</ul>';
    }

    public function viewVLperYear()
    {
        $input = $this->input->post();
        $status = $input['status'];
        if ($status == 0) {
            $stat = 'Pending';
        } else if ($status == 1) {
            $stat = 'Approved';
        } else {
            $stat = 'Disapproved';
        }
        echo '<div class="d-none d-md-block table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle" id="vlPerYearTable">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>' . $stat . '</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

        $years = $this->request->getArray('tk_vl_payment_access', ['1' => 1]);
        foreach ($years as $y) {
            $year = $y['year'];
            $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="vl_payment_list(\'' . $year . '\',\'' . $status . '\')">View</a>';


            if ($this->usertype == 'administrator') {
                $cond = ['year' => $year, 'status' => $status];
                $payment = $this->request->getArray('tk_vl_payment', $cond);
            } else {
                $payment = $this->request->viewVLperYear_sup($year, $status);
            }

            if ($payment) {
                echo '<tr>';
                echo '<td>' . $year . '</td>';
                echo '<td>' . count($payment) . '</td>';
                echo '<td>' . $action . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    public function vl_payment_list()
    {
        $input = $this->input->post();
        $year = $input['year'];
        $status = $input['status'];

        $data = [];
        if ($this->usertype == 'administrator') {
            $cond = ['year' => $year, 'status' => $status];
            $vls = $this->request->getArray('tk_vl_payment', $cond);
        } else {
            $vls = $this->request->viewVLperYear_sup($year, $status);
        }
        foreach ($vls as $vl) {
            $id = $vl['id'];
            $date = date('F j, Y', strtotime($vl['date']));
            $name = $this->request->getRow('employee3', ['emp_id' => $vl['emp_id']])['name'];
            $total = $vl['total_days'];
            if ($vl['status'] == 0) {
                $status = '<span class="badge bg-warning text-white py-1" style="font-size: 0.68rem;">Pending</span>';
            } else if ($vl['status'] == 1) {
                $status = '<span class="badge bg-success py-1" style="font-size: 0.68rem;">Approved</span>';
            } else {
                $status = '<span class="badge bg-danger py-1" style="font-size: 0.68rem;">Disapproved</span>';
            }
            $col = [];
            $col[] = '<span style="display:none;">' . date('YmdHis', strtotime($vl['date'])) . '</span>' . $date;
            $col[] = $name;
            $col[] = $total;
            $col[] = $status;
            $col[] = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewVLpaymentReq(\'' . $id . '\')">View</a>';
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function viewVLpayment()
    {
        $data = '';
        $input = $this->input->post();
        $id = $input['id'];
        $vl = $this->request->getRow('tk_vl_payment', ['id' => $id]);
        if ($vl) {
            $name = $this->request->getRow('employee3', ['emp_id' => $vl['emp_id']])['name'];
            $date = date('F j, Y h:i A', strtotime($vl['date']));
            $year = $vl['year'];

            if ($vl['status'] == 0) {
                $status = 'PENDING';
                $statusColor = 'text-warning fw-bold';
            } else if ($vl['status'] == 1) {
                $status = 'APPROVED';
                $statusColor = 'text-success fw-bold';
            } else {
                $status = 'DISAPPROVED';
                $statusColor = 'text-danger fw-bold';
            }

            $data .= '<div class="row g-3">';

            $data .= '<input type="hidden" value="' . $id . '" id="vlPaymentId">';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Date Filed</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $date . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
            $data .= '</div>';

            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">VL Year</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $year . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Days</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $vl['total_days'] . '" disabled>';
            $data .= '</div>';

            $data .= '<div class="col-6 col-md-6">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Request Status</label>';
            $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . $status . '" disabled>';
            $data .= '</div>';

            if ($vl['status'] == 0) {
                $data .= '<div class="col-12 col-md-6">';
                $data .= '<label class="form-label small fw-semibold text-muted mb-1" >Approver\'s Comments</label>';
                $data .= '<textarea class="form-control form-control-sm" id="vlPaymentComments" placeholder="Comments..."></textarea>';
                $data .= '</div>';
            } else {

                $cond = ['payment_id' => $id, 'emp_id' => $vl['emp_id']];
                $approval = $this->request->getRow('tk_vl_payment_approval', $cond);
                if ($approval) {

                    $date = date('F j, Y', strtotime($approval['date_approved']));
                    $time = date('h:i A', strtotime($approval['date_approved']));
                    $name = $this->request->getRow('employee3', ['emp_id' => $approval['approved_by']])['name'];

                    if ($approval['status'] == 1) {
                        $status = 'APPROVED';
                        $label_name = 'Approved By';
                        $label_date = 'Approved Date';
                        $label_time = 'Approved Time';
                        $statusColor = 'text-success fw-bold';
                    } else {
                        $status = 'DISAPPROVED';
                        $label_name = 'Disapproved By';
                        $label_date = 'Disapproved Date';
                        $label_time = 'Disapproved Time';
                        $statusColor = 'text-danger fw-bold';
                    }
                    $data .= '<div class="col-12 col-md-12">';

                    $data .= '<div class="card">';
                    $data .= '<div class="card-body">';

                    $data .= '<div class="row g-3">';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">' . $label_name . '</label>';
                    $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
                    $data .= '</div>';
                    $data .= '<div class="col-6 col-md-6">';
                    $data .= '<label class="form-label small fw-semibold text-muted mb-1">Approval Status</label>';
                    $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . $status . '" disabled>';
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

                    $data .= '</div>';
                }
            }

            $data .= '</div>';
        }

        echo json_encode(['form' => $data, 'status' => $vl['status']]);
    }

    public function vlPaymentApproval()
    {
        $input = $this->input->post();
        $id = $input['id'];
        $status = $input['status'];
        $comments = $input['comments'];

        $cond = ['id' => $id, 'status' => 0];
        $vl = $this->request->getRow('tk_vl_payment', ['id' => $id]);
        if ($vl) {
            $emp_id = $vl['emp_id'];
            $year = $vl['year'];

            $cond = ['id' => $id];
            $data = ['status' => $status];
            $this->request->update_table('tk_vl_payment', $data, $cond);

            $data = [
                'payment_id' => $id,
                'emp_id' => $emp_id,
                'year' => $year,
                'status' => $status,
                'comments' => $comments,
                'approved_by' => $this->userId,
                'date_approved' => date('Y-m-d H:i:s'),
            ];
            $this->request->insert('tk_vl_payment_approval', $data);


            echo json_encode(['message' => 'success', 'year' => $year]);
            return;
        }
    }
}
