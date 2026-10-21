<?php

class Setup_admin extends Portal_Controller
{
    public $ppdf;
    public $userId;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Users_model', 'users');
        $this->load->model('Employee_model', 'employee');
        $this->load->model('Request_model', 'request');
        $this->load->library('ppdf');
        $this->userId = $this->session->userdata('empid');
    }

    public function viewRender()
    {
        $input = $this->input->post();
        $id = $input['id'];

        echo '<div class="row g-3">';
        if ($id == 'add') {
            $name = '';
            $render_type = '';
            $limit_type = '';
            $limit_duration = '';
            $hours_rendered = '';
            $status = '';
        } else {
            $render = $this->request->getRow('tk_render_limits', ['tkrlId' => $id]);
            $name = $render['name'];
            $render_type = $render['render_type'];
            $limit_type = $render['limit_type'];
            $limit_duration = $render['limit_duration'];
            $hours_rendered = $render['hours_rendered'];
            $status = $render['status'];

            echo '<input type="hidden" name="id" value="' . $id . '">';
        }

        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Name</label>';
        echo '<input type="text" class="form-control form-control-sm" name="name" value="' . $name . '" placeholder="Render name...">';
        echo '</div>';

        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Render Type</label>';
        echo '<select class="form-control form-control-sm" name="render_type">';
        echo '<option value="">---Select Render Type---</option>';
        echo '<option value="duty" ' . ($render_type == 'duty' ? 'selected' : '') . '>Duty</option>';
        echo '<option value="leave" ' . ($render_type == 'leave' ? 'selected' : '') . '>Leave</option>';
        echo '</select>';
        echo '</div>';

        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Limit Type</label>';
        echo '<select class="form-control form-control-sm" name="limit_type">';
        echo '<option value="">---Select Limit Type---</option>';
        echo '<option value="minimum" ' . ($limit_type == 'minimum' ? 'selected' : '') . '>Minimum</option>';
        echo '<option value="maximum" ' . ($limit_type == 'maximum' ? 'selected' : '') . '>Maximum</option>';
        echo '</select>';
        echo '</div>';

        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Limit Duration</label>';
        echo '<select class="form-control form-control-sm" name="limit_duration">';
        echo '<option value="">---Select Limit Duration---</option>';
        echo '<option value="daily" ' . ($limit_duration == 'daily' ? 'selected' : '') . '>Daily</option>';
        echo '<option value="weekly" ' . ($limit_duration == 'weekly' ? 'selected' : '') . '>Weekly</option>';
        echo '<option value="monthly" ' . ($limit_duration == 'monthly' ? 'selected' : '') . '>Monthly</option>';
        echo '</select>';
        echo '</div>';

        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Hours Rendered</label>';
        echo '<input type="number" class="form-control form-control-sm" name="hours_rendered" min="1" value="' . $hours_rendered . '">';
        echo '</div>';

        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
        echo '<select class="form-control form-control-sm" name="status">';
        echo '<option value="">---Select Status---</option>';
        echo '<option value="1" ' . ($status == '1' ? 'selected' : '') . '>Active</option>';
        echo '<option value="0" ' . ($status == '0' ? 'selected' : '') . '>Inactive</option>';
        echo '</select>';
        echo '</div>';

        echo '</div>';
    }

    public function updateRender()
    {
        $input = $this->input->post();
        $id = $input['id'];
        $name = $input['name'];
        $render_type = $input['render_type'];
        $limit_type = $input['limit_type'];
        $limit_duration = $input['limit_duration'];
        $hours_rendered = $input['hours_rendered'];
        $status = $input['status'];

        $cond = [
            'tkrlId !=' => $id,
            'render_type' => $render_type,
            'limit_type' => $limit_type,
            'limit_duration' => $limit_duration,
            'hours_rendered' => $hours_rendered
        ];
        $check = $this->request->getRow('tk_render_limits', $cond);
        if ($check) {
            echo json_encode(['message' => 'Render type already exists.']);
            return;
        }

        $data = [
            'name'                => $name,
            'render_type'         => $render_type,
            'limit_type'          => $limit_type,
            'limit_duration'      => $limit_duration,
            'hours_rendered'      => $hours_rendered,
            'status'              => $status,
            'updated_by'          => $this->userId,
            'date_updated'        => date('Y-m-d H:i:s'),
        ];

        $update = $this->request->update_table('tk_render_limits', $data, ['tkrlId' => $id]);
        if ($update) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function addRender()
    {
        $input = $this->input->post();
        $name = $input['name'];
        $render_type = $input['render_type'];
        $limit_type = $input['limit_type'];
        $limit_duration = $input['limit_duration'];
        $hours_rendered = $input['hours_rendered'];
        $status = $input['status'];

        $cond = [
            'render_type' => $render_type,
            'limit_type' => $limit_type,
            'limit_duration' => $limit_duration,
            'hours_rendered' => $hours_rendered
        ];
        $check = $this->request->getRow('tk_render_limits', $cond);
        if ($check) {
            echo json_encode(['message' => 'Render type already exists.']);
            return;
        }

        $data = [
            'name'                => $name,
            'render_type'         => $render_type,
            'limit_type'          => $limit_type,
            'limit_duration'      => $limit_duration,
            'hours_rendered'      => $hours_rendered,
            'status'              => $status,
            'created_by'          => $this->userId,
            'date_created'        => date('Y-m-d H:i:s'),
        ];

        $insert = $this->request->insert('tk_render_limits', $data);
        if ($insert) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function viewVLdefaults()
    {
        $input = $this->input->post();
        $id = $input['id'];

        $vl = $this->request->getRow('tk_vl_defaults', ['id' => $id]);
        $payment = $vl['payment'];
        $days = $vl['total_days'];
        $status = $vl['status'];
        echo '<div class="row g-3">';
        echo '<input type="hidden" name="id" value="' . $id . '">';
        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">VL Default ID</label>';
        echo '<input type="text" class="form-control form-control-sm" value="' . $id . '" disabled>';
        echo '</div>';
        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Payment Type</label>';
        echo '<select class="form-control form-control-sm" name="payment" disabled>';
        echo '<option value="">---Select Limit Type---</option>';
        echo '<option value="paid" ' . ($payment == 'paid' ? 'selected' : '') . '>PAID</option>';
        echo '<option value="unpaid" ' . ($payment == 'unpaid' ? 'selected' : '') . '>UNPAID</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Allocated Days</label>';
        echo '<input type="number" class="form-control form-control-sm" name="total_days" min="1" value="' . $days . '">';
        echo '</div>';
        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
        echo '<select class="form-control form-control-sm" name="status" disabled>';
        echo '<option value="">---Select Limit Type---</option>';
        echo '<option value="1" ' . ($status == '1' ? 'selected' : '') . '>Active</option>';
        echo '<option value="0" ' . ($status == '0' ? 'selected' : '') . '>Inactive</option>';
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public function updateVLdefaults()
    {
        $input = $this->input->post();
        $id = $input['id'];
        $total_days = $input['total_days'];
        $data = [
            'total_days' => $total_days,
            'updated_by' => $this->userId,
            'date_updated' => date('Y-m-d H:i:s'),
        ];

        $update = $this->request->update_table('tk_vl_defaults', $data, ['id' => $id]);
        if ($update) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function updateRequestForm()
    {
        $input = $this->input->post();
        $id = $input['id'];

        $request = $this->request->getRow('tk_request_type', ['tkrtId' => $id]);
        $name = $request['request'];
        $code = $request['code'];
        $acronym = $request['acroname'];
        $fixed_hour = $request['fixed_hour'];
        $fixed_type = $request['fixed_type'];
        $status = $request['status'];

        echo '<div class="row g-3">';
        echo '<input type="hidden" name="id" value="' . $id . '">';
        echo '<div class="col-12 col-md-12">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Request</label>';
        echo '<input type="text" class="form-control form-control-sm" name="request" value="' . $name . '" placeholder="Request name...">';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Code</label>';
        echo '<input type="text" class="form-control form-control-sm" name="code" value="' . $code . '" placeholder="Code..." style="text-transform: lowercase;" readonly>';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Acronym</label>';
        echo '<input type="text" class="form-control form-control-sm" name="acroname" value="' . $acronym . '" placeholder="Acronym..." style="text-transform: uppercase;">';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Fixed Hours</label>';
        echo '<input type="number" class="form-control form-control-sm" name="fixed_hour" min="1" value="' . $fixed_hour . '">';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Fixed Type</label>';
        echo '<select class="form-control form-control-sm" name="fixed_type">';
        echo '<option value="">---Select Fixed Type---</option>';
        echo '<option value="0" ' . ($fixed_type == 0 ? 'selected' : '') . '>Full Day/Hourly</option>';
        echo '<option value="1" ' . ($fixed_type == 1 ? 'selected' : '') . '>Full Day</option>';
        echo '<option value="2" ' . ($fixed_type == 2 ? 'selected' : '') . '>Hourly</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
        echo '<select class="form-control form-control-sm" name="status">';
        echo '<option value="">---Select Status---</option>';
        echo '<option value="active" ' . ($status == 'active' ? 'selected' : '') . '>Active</option>';
        echo '<option value="inactive" ' . ($status == 'inactive' ? 'selected' : '') . '>Inactive</option>';
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public function updateRequest()
    {
        $input = $this->input->post();
        $id = $input['id'];
        $name = $input['request'];
        $code = $input['code'];
        $acronym = $input['acroname'];
        $fixed_hour = $input['fixed_hour'];
        $fixed_type = $input['fixed_type'];
        $status = $input['status'];

        $cond = ['tkrtId !=' => $id, 'code' => $code,];
        $check = $this->request->getRow('tk_request_type', $cond);
        if ($check) {
            echo json_encode(['message' => 'Request code already exists.']);
            return;
        }

        $data = [
            'request'    => $name,
            'code'       => strtolower($code),
            'acroname'   => strtoupper($acronym),
            'fixed_hour' => $fixed_hour,
            'fixed_type' => $fixed_type,
            'status'     => $status,
            'updated_by' => $this->userId,
            'date_updated' => date('Y-m-d H:i:s'),
        ];

        $update = $this->request->update_table('tk_request_type', $data, ['tkrtId' => $id]);
        if ($update) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function addRequestForm()
    {
        $input = $this->input->post();
        echo '<div class="row g-3">';
        echo '<div class="col-12 col-md-12">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Request</label>';
        echo '<input type="text" class="form-control form-control-sm" name="request" value="" placeholder="Request name...">';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Code</label>';
        echo '<input type="text" class="form-control form-control-sm" name="code" value="" placeholder="Code..." style="text-transform: lowercase;">';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Acronym</label>';
        echo '<input type="text" class="form-control form-control-sm" name="acroname" value="" placeholder="Acronym..." style="text-transform: uppercase;">';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Fixed Hours</label>';
        echo '<input type="number" class="form-control form-control-sm" name="fixed_hour" min="1" value="">';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Fixed Type</label>';
        echo '<select class="form-control form-control-sm" name="fixed_type">';
        echo '<option value="">---Select Fixed Type---</option>';
        echo '<option value="0">Full Day/Hourly</option>';
        echo '<option value="1">Full Day</option>';
        echo '<option value="2">Hourly</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
        echo '<select class="form-control form-control-sm" name="status">';
        echo '<option value="">---Select Status---</option>';
        echo '<option value="active">Active</option>';
        echo '<option value="inactive">Inactive</option>';
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public function addRequest()
    {
        $input      = $this->input->post();
        $request    = $input['request'];
        $code       = $input['code'];
        $acronym    = $input['acroname'];
        $fixed_hour = $input['fixed_hour'];
        $fixed_type = $input['fixed_type'];
        $status     = $input['status'];

        $check = $this->request->getRow('tk_request_type', ['code' => strtolower($code)]);
        if ($check) {
            echo json_encode(['message' => 'Request code already exists.']);
            return;
        }

        $data = [
            'request'       => $request,
            'code'          => strtolower($code),
            'acroname'      => strtoupper($acronym),
            'fixed_hour'    => $fixed_hour,
            'fixed_type'    => $fixed_type,
            'status'        => $status,
            'created_by'    => $this->userId,
            'date_created'  => date('Y-m-d H:i:s'),
        ];

        $insert = $this->request->insert('tk_request_type', $data);
        if ($insert) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function updateMatpatForm()
    {
        $input = $this->input->post();
        $id = $input['id'];
        $request = $this->request->getRow('tk_matpat_details', ['id' => $id]);
        $code = $request['code'];
        $delivery_type = $request['delivery_type'];
        $allocated_days = $request['allocated_days'];
        $status = $request['status'];

        echo '<div class="row g-3">';
        echo '<input type="hidden" name="id" value="' . $id . '">';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Request</label>';
        echo '<select class="form-control form-control-sm" name="code" onchange="getDeliveryType(this.value)">';
        echo '<option value="">---Select Request---</option>';
        echo '<option value="maternity" ' . ($code == 'maternity' ? 'selected' : '') . '>Maternity</option>';
        echo '<option value="paternity" ' . ($code == 'paternity' ? 'selected' : '') . '>Paternity</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Delivery Type</label>';
        echo '<select class="form-control form-control-sm" name="delivery_type"  id="delivery_type" ' . ($code == 'paternity' ? 'disabled' : '') . '>';
        echo '<option value="">---Select Delivery Type---</option>';
        echo '<option value="normal" ' . ($delivery_type == 'normal' ? 'selected' : '') . '>NORMAL</option>';
        echo '<option value="cesarean" ' . ($delivery_type == 'cesarean' ? 'selected' : '') . '>CESAREAN</option>';
        echo '<option value="miscarriage" ' . ($delivery_type == 'miscarriage' ? 'selected' : '') . '>MISCARRIAGE</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Allocated Days</label>';
        echo '<input type="number" class="form-control form-control-sm" name="allocated_days" min="1" value="' . $allocated_days . '">';
        echo '</div>';
        echo '<div class="col-12 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Status</label>';
        echo '<select class="form-control form-control-sm" name="status">';
        echo '<option value="">---Select Status---</option>';
        echo '<option value="1" ' . ($status == '1' ? 'selected' : '') . '>Active</option>';
        echo '<option value="0" ' . ($status == '0' ? 'selected' : '') . '>Inactive</option>';
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public function updateMatpat()
    {
        $input = $this->input->post();
        $id = $input['id'];
        $code = $input['code'];
        $allocated_days = $input['allocated_days'];
        $status = $input['status'];

        $delivery_type = '';
        if ($code === 'maternity') {
            $delivery_type = $input['delivery_type'];
        }

        $cond = ['id !=' => $id, 'code' => $code, 'delivery_type' => $delivery_type];
        $check = $this->request->getRow('tk_matpat_details', $cond);
        if ($check) {
            echo json_encode(['message' => 'Request and delivery type already exists.']);
            return;
        }

        $data = [
            'code'              => $code,
            'allocated_days'    => $allocated_days,
            'delivery_type'     => $delivery_type,
            'status'            => $status,
            'updated_by'        => $this->userId,
            'date_updated'      => date('Y-m-d H:i:s'),
        ];
        $cond = ['id' => $id];
        $update = $this->request->update_table('tk_matpat_details', $data, $cond);

        echo json_encode(['message' => 'success']);
        return;
    }

    public function updateExtension()
    {
        $input = $this->input->post();
        $co_schedule = $input['co_schedule'];
        $startDate = $input['startDate_' . $co_schedule];
        $endDate = $input['endDate_' . $co_schedule];

        $data = [
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'updated_by'    => $this->userId,
            'date_updated'  => date('Y-m-d H:i:s')
        ];
        $cond = ['co_schedule' => $co_schedule];
        $update = $this->request->update_table('tk_cutoff_extension', $data, $cond);

        echo json_encode(['message' => 'success']);
        return;
    }

    public function updateScheduleForm()
    {
        $input = $this->input->post();
        $empId = $input['empId'];
        $name = $this->request->getRow('employee3', ['emp_id' => $empId])['name'];

        $cond = ['emp_id' => $empId];
        $s = $this->request->getRow('tk_employee_cutoff_schedule', $cond);
        $id = $s['id'];
        $co = $s['co_schedule'];

        $cond = ['id' => $co, 'status' => 1];
        $cos = $this->request->getArray('tk_cutoff_schedule', $cond);
        foreach ($cos as $c) {
            if ($c) {

                $sFC = $c['startFC'];
                $eFC = $c['endFC'];

                $sSC = $c['startSC'];
                $eSC = $c['endSC'];

                $schedule = "$sFC-$eFC / $sSC-$eSC";
            }
        }

        echo '<div class="row g-3">';
        echo '<input type="hidden" name="id" value="' . $id . '">';
        echo '<input type="hidden" name="emp_id" value="' . $empId . '">';
        echo '<div class="col-12 col-md-12">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Name</label>';
        echo '<input type="text" class="form-control form-control-sm" value="' . $name . '" disabled>';
        echo '</div>';
        echo '<div class="col-12 col-md-12">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Cutoff</label>';
        echo '<select class="form-control form-control-sm" name="co_schedule">';
        echo '<option value="">---Select Cutoff---</option>';
        $cutoffs = $this->request->getArray('tk_cutoff_schedule', ['status' => 1]);
        foreach ($cutoffs as $c) {
            $id = $c['id'];
            $sFC = $c['startFC'];
            $eFC = $c['endFC'];

            $sSC = $c['startSC'];
            $eSC = $c['endSC'];

            $schedule = "$sFC-$eFC / $sSC-$eSC";

            echo '<option value="' . $id . '" ' . ($id == $co ? 'selected' : '') . '>' . $schedule . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public function updateSchedule()
    {
        $input = $this->input->post();
        $id = $input['id'];
        $emp_id = $input['emp_id'];
        $co = $input['co_schedule'];

        $data = ['co_schedule' => $co];
        $cond = ['id' => $id, 'emp_id' => $emp_id];
        $update = $this->request->update_table('tk_employee_cutoff_schedule', $data, $cond);
        if ($update) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    function generateVL()
    {
        $input = $this->input->post();
        $year = $input['year'];
        $employees = $this->request->getArray('masterlist_2ndgen', ['status' => 1]);
        foreach ($employees as $e) {
            $empId = $e['emp_id'];

            $cond = ['emp_id' => $empId, 'year' => $year];
            $vl = $this->request->getRow('tk_vacation_leave_balance', $cond);
            if (!$vl) {

                $cond = ['payment' => 'paid', 'status' => '1'];
                $paid = $this->request->getRow('tk_vl_defaults', $cond)['total_days'];
                $cond = ['payment' => 'unpaid', 'status' => '1'];
                $unpaid = $this->request->getRow('tk_vl_defaults', $cond)['total_days'];
                $total = $paid + $unpaid;

                $data = [
                    'emp_id'                => $empId,
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
        }
        echo json_encode(['message' => 'success']);
        return;
    }

    public function addCutoff()
    {
        $data = $this->input->post();
        $cond = $data;
        $check = $this->request->getRow('tk_cutoff_schedule', $cond);
        if ($check) {
            echo json_encode(['message' => 'Cutoff schedule already exists.']);
            return;
        }

        $data['status'] = 1;
        $data['created_by'] = $this->userId;
        $data['date_created'] = date('Y-m-d H:i:s');
        $insert = $this->request->insert('tk_cutoff_schedule', $data);
        if ($insert) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function vlAccess()
    {
        $input = $this->input->post();
        $year = $input['year'];
        $status = $input['status'];

        if ($year == 'off') {
            $cond = ['1' => 1];
            $data = ['status' => $status, 'updated_by' => $this->userId, 'date_updated' => date('Y-m-d H:i:s')];
            $update = $this->request->update_table('tk_vl_payment_access', $data, $cond);
            if ($update) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }

        $cond = ['year' => $year];
        $check = $this->request->getRow('tk_vl_payment_access', $cond);
        if ($check) {

            $data = ['status' => $status, 'updated_by' => $this->userId, 'date_updated' => date('Y-m-d H:i:s')];
            $update = $this->request->update_table('tk_vl_payment_access', $data, $cond);
            if ($update) {
                echo json_encode(['message' => 'success']);
                return;
            }
        } else {

            $data = ['year' => $year, 'status' => $status, 'created_by' => $this->userId, 'date_created' => date('Y-m-d H:i:s')];
            $insert = $this->request->insert('tk_vl_payment_access', $data);
            if ($insert) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }
    }

    public function addHoliday()
    {
        $data = $this->input->post();
        $data['status'] = 1;
        $data['created_by'] = $this->userId;
        $data['date_created'] = date('Y-m-d H:i:s');

        $cond = ['date' => $data['date']];
        $check = $this->request->getRow('tk_holidays', $cond);
        if ($check) {
            echo json_encode(['message' => 'Holiday date already exists.']);
            return;
        }

        $insert = $this->request->insert('tk_holidays', $data);
        if ($insert) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function updateHolidayForm()
    {
        $input = $this->input->post();
        $id = $input['id'];

        $h = $this->request->getRow('tk_holidays', ['id' => $id]);
        $holiday = $h['holiday'];
        $date = $h['date'];

        $date_change = '';
        if ($h['date_change'] != '0000-00-00') {
            $date_change = $h['date_change'];
        }

        echo '<div class="row">';
        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Date</label>';
        echo '<input type="hidden" value="' . $id . '" name="id">';
        echo '<input type="text" class="form-control form-control-sm datePicker" value="' . $date . '" placeholder="YYYY-MM-DD" name="date">';
        echo '</div>';
        echo '<div class="col-6 col-md-6">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Date Changed</label>';
        echo '<input type="text" class="form-control form-control-sm datePickerClear" value="' . $date_change . '" placeholder="YYYY-MM-DD" name="date_change">';
        echo '</div>';
        echo '<div class="col-6 col-md-12 mt-3">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Holiday</label>';
        echo '<input type="text" class="form-control form-control-sm" value="' . $holiday . '" placeholder="Holiday name..." name="holiday">';
        echo '</div>';
        echo '<div class="col-6 col-md-12 mt-3">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">Holiday Type</label>';
        echo '<select class="form-control form-control-sm select2_update" name="type">';
        echo '<option value="">---</option>';
        echo '<option value="Legal" ' . ($h['type'] == 'Legal' ? 'selected' : '') . '>Legal</option>';
        echo '<option value="Special" ' . ($h['type'] == 'Special' ? 'selected' : '') . '>Special</option>';
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public function updateHoliday()
    {
        $data = $this->input->post();
        $data['updated_by'] = $this->userId;
        $data['date_updated'] = date('Y-m-d H:i:s');

        $id = $data['id'];
        $cond = ['id !=' => $id, 'date' => $data['date']];
        $check = $this->request->getRow('tk_holidays', $cond);
        if ($check) {
            echo json_encode(['message' => 'Holiday date already exists.']);
            return;
        }

        if ($data['date_change'] != '') {
            $cond = ['id !=' => $id, 'date_change' => $data['date_change']];
            $check = $this->request->getRow('tk_holidays', $cond);
            if ($check) {
                echo json_encode(['message' => 'Holiday date already exists.']);
                return;
            }
        }

        $update = $this->request->update_table('tk_holidays', $data, ['id' => $id]);
        if ($update) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }
}
