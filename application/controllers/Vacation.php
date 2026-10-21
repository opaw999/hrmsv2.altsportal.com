<?php

class Vacation extends Portal_Controller
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

    public function emp_vl()
    {
        echo '<div class="d-none d-md-block table-responsive">';
        echo '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="vacationTable">';
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

        $empid = $this->session->userdata('empid');
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

        echo ' <div class="d-flex d-md-none flex-column gap-2">';
        foreach ($vls as $vl) {
            $date = date('F j, Y', strtotime($vl['created_at']));
            $start = date('F j, Y', strtotime($vl['startDate']));
            $end = date('F j, Y', strtotime($vl['endDate']));
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

            echo '<div class="ledger-mobile-item">';
            echo '<a href="javascript:;" class="text-decoration-none" onclick="vl_details(\'' . $vl['leaveId'] . '\')">';
            echo '<div class="d-flex justify-content-between align-items-center text-dark mb-1">';
            echo '<div class="fw-medium">Date Filed: <span class="fw-bold">' . $date . '</span></div>';
            echo '<div class="d-flex align-items-center gap-1">' . $status . '</div>';
            echo '</div>';

            echo '<div class="d-flex align-items-center fst-italic">';
            echo '<div class="fw-medium text-dark " style="font-size: .90rem;">' . $start . ' - ' . $end . '</div>';
            echo '</div>';

            echo '<div class="d-flex align-items-center fst-italic mb-1">';
            echo '<div class="fw-medium text-dark " style="font-size: .90rem;">' . $total_days . ' day(s) / ' . $total_hours . ' hours</div>';
            echo '</div>';

            echo '<div class="d-flex align-items-center fst-italic">';
            echo '<div class="fw-medium text-dark " style="font-size: .90rem;">' . $payment . '</div>';
            echo '</div>';
            echo '</a>';
            echo '</div>';
        }
        echo '</div>';
    }

    public function vl_details()
    {
        $data = '';
        $input = $this->input->post();
        $leaveId = $input['leaveId'];
        $cond = ['leaveId' => $leaveId];
        $leave = $this->request->getRow('tk_leave_requests', $cond);
        if ($leave) {
            $code = $leave['code'];
            $request = $this->request->getRow('tk_request_type', ['code' => $code, 'status' => 'active'])['request'];
            $name = $this->request->getRow('employee3', ['emp_id' => $leave['emp_id']])['name'];
            $hours = round($leave['total_hours'], 2);
            $days = $leave['total_days'];

            $submitted = date('F j, Y h:i:s A', strtotime($leave['created_at']));
            $updated = '';
            if ($leave['updated_at'] != '0000-00-00 00:00:00') {
                $updated = date('F j, Y h:i:s A', strtotime($leave['updated_at']));
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
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Requested By</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Start Date</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . date('F j, Y', strtotime($leave['startDate'])) . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">End Date</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . date('F j, Y', strtotime($leave['endDate'])) . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Days</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $days . '" disabled>';
            $data .= '</div>';
            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Total Hours</label>';
            $data .= '<input type="text" class="form-control form-control-sm" value="' . $hours . '" disabled>';
            $data .= '</div>';
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

            $data .= '<div class="col-6 col-md-6 mt-2">';
            $data .= '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
            $data .= '<input type="text" class="form-control form-control-sm ' . $statusColor . '" value="' . strtoupper($leave['status']) . '" disabled>';
            $data .= '</div>';

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

            $data .= '<div class="col-12 col-md-12 mt-2">';

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

            $cond = ['leaveId' => $leaveId];
            $check = $this->request->getRow('tk_leave_request_approved', $cond);
            if ($check) {

                $cond = "leaveId='$leaveId' ORDER BY tkraId DESC LIMIT 1";
                $approvals = $this->request->getArray('tk_leave_request_approved', $cond);
                foreach ($approvals as $approval) {

                    $date = date('F j, Y', strtotime($approval['approved_datetime']));
                    $time = date('h:i:s A', strtotime($approval['approved_datetime']));
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

    public function request_vlPayment()
    {
        $input = $this->input->post();
        $year = $input['year'];

        $cond = ['emp_id' => $this->userId, 'year' => $year];
        $vl = $this->request->getRow('tk_vacation_leave_balance', $cond);
        if ($vl) {
            if (!$vl['paidDaysRemaining']) {
                echo json_encode(['message' => 'You have no paid vacation leave remaining']);
                return;
            }

            $paid = $vl['paidDaysRemaining'];
            $paidUsed = $vl['paidDaysUsed'];
            $total = $vl['totalDaysRemaining'];

            $cond = ['emp_id' => $this->userId, 'year' => $year];
            $data = [
                'paidDaysRemaining' => 0,
                'paidDaysUsed' => $paidUsed + $paid,
                'totalDaysRemaining' => $total - $paid,
            ];
            $update = $this->request->update_table('tk_vacation_leave_balance', $data, $cond);

            $data = [
                'emp_id' => $this->userId,
                'year' => $year,
                'total_days' => $paid,
                'status' => 0,
                'date' => date('Y-m-d H:i:s'),
            ];

            $insert = $this->request->insert('tk_vl_payment', $data);
            if ($insert) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }
    }
}
