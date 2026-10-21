<?php

class Users_admin extends Portal_Controller
{
    public $userId;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Users_model', 'users');
        $this->load->model('Employee_model', 'employee');
        $this->load->model('Request_model', 'request');
        $this->userId = $this->session->userdata('empid');
    }

    public function access_details()
    {
        $empId = $this->input->post('empId');
        $user = $this->request->getRow('tk_user_admin', ['emp_id' => $empId]);
        $access = $user['type'];
        $emp = $this->request->getRow('employee3', ['emp_id' => $empId]);
        $name = $emp['name'];
        $position = $emp['position'];
        if ($user['status'] == 1) {
            $class = 'badge bg-light text-success border border-success rounded-pill px-2 py-1';
            $status = 'Active';
        } else {
            $class = 'badge bg-light text-danger border border-danger rounded-pill px-2 py-1';
            $status = 'Inactive';
        }

        $photo = 'assets/images/admin.png';
        $img = $this->request->getRow('applicant', ['app_id' => $empId])['photo'];
        if ($img) {
            $p = explode('/', $img);
            $photo = 'assets/images/2ndgen/' . $p[3];
            if (!file_exists($photo)) {
                $photo = 'assets/images/admin.png';
            }
        }

        echo '<div class="row g-2 ">';

        echo '<div class="col-md-12">';
        echo '<div class="d-flex justify-content-right mb-2 pb-2 gap-3">';
        echo '<img src="' . base_url($photo) . '" alt="Avatar" class="img-thumbnail" style="width: 100px; height: 100px;">';
        echo '<div>';
        echo '<div class="fw-bold text-dark fs-6">' . $name . '</div>';
        echo '<div class="text-muted"><i>' . $empId . '</i></div>';
        echo '<div class="text-muted mb-2"><i>' . $position . '</i></div>';
        echo '<span class="' . $class . '" style="font-size: 0.7rem;"><i class="bi bi-circle-fill me-1"></i>' . $status . ' </span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '</div>';

        echo '<div class="row g-2">';

        echo '<div class="col-md-6 d-flex">';
        echo '<div class="card bg-light mb-3 flex-fill">';
        echo '<div class="card-header">Menu Access Table</div>';
        echo '<div class="card-body">';
        echo '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="accessTable">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Menu Name</th>';
        echo '<th class="text-center">Access</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $menus = $this->request->getArray('tk_admin_menus', ['status' => 1]);
        foreach ($menus as $m) {
            $id = $m['id'];
            $mn = $m['menu_name'];
            $mc = $m['menu_code'];

            $check = $this->request->getRow('tk_admin_menu_access', ['menu_id' => $id, 'emp_id' => $empId, 'status' => 1]);
            if ($check) {
                $click = 'onclick="updateAccess(\'' . $empId . '\',\'' . $id . '\',this)" checked';
            } else {
                $click = 'onclick="updateAccess(\'' . $empId . '\',\'' . $id . '\',this)"';
            }

            $action = '<input type="checkbox" ' . $click . '>';

            echo '<tr>';
            echo '<td>' . $mn . '</td>';
            echo '<td class="text-center">' . $action . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-6 d-flex">';
        echo '<div class="card bg-light mb-3 flex-fill">';
        echo '<div class="card-header">User Type/Status</div>';
        echo '<div class="card-body">';
        echo '<div class="row">';
        echo '<div class="col-md-12">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">User Type</label>';
        echo '<select class="form-control form-control-sm" id="usertype">';
        echo '<option value="administrator" ' . ($user['type'] == 'administrator' ? 'selected' : '') . '>Administrator</option>';
        echo '<option value="supervisor" ' . ($user['type'] == 'supervisor' ? 'selected' : '') . '>Supervisor</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="col-md-12 mt-2">';
        echo '<label class="form-label small fw-semibold text-muted mb-1">User Status</label>';
        echo '<select class="form-control form-control-sm" id="userstatus">';
        echo '<option value="1" ' . ($user['status'] == 1 ? 'selected' : '') . '>Active</option>';
        echo '<option value="0" ' . ($user['status'] == 0 ? 'selected' : '') . '>Inactive</option>';
        echo '</select>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="card-footer text-end">
                <button type="button" class="btn btn-sm btn-primary me-1" onclick="resetPassword(\'' . $empId . '\')">Reset Password</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="updateUser(\'' . $empId . '\')">Update</button>
            </div>';
        echo '</div>';
        echo '</div>';

        if ($access == 'supervisor') {
            echo '<div class="col-md-12">';
            echo '<div class="card bg-light">';
            echo '<div class="card-header">Subordinates Table</div>';
            echo '<div class="card-body">';
            echo '<table class="table table-sm table-hover mb-0 align-middle nowrap" id="subordinatesTable">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>HRMS ID</th>';
            echo '<th>Name</th>';
            echo '<th>Tag</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            $employees = $this->request->getArray('masterlist_2ndgen', ['status' => 1]);
            foreach ($employees as $e) {
                $approvee = $e['emp_id'];
                $emp = $this->request->getRow('employee3', ['emp_id' => $approvee]);
                $name = $emp['name'];
                $position = $emp['position'];
                $location = $e['location'];
                $salary = $emp['payroll_no'];

                $cond = ['approver' => $empId, 'approvee' => $approvee];
                $check = $this->request->getRow('tk_approvers_subordinates', $cond);
                if ($check) {
                    $click = 'onclick="updateSubordinates(\'' . $empId . '\',\'' . $approvee . '\',this)" checked';
                } else {
                    $click = 'onclick="updateSubordinates(\'' . $empId . '\',\'' . $approvee . '\',this)"';
                }

                $action = '<input type="checkbox" id="" ' . $click . '>';

                echo '<tr>';
                echo '<td>' . $approvee . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $action . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    }

    public function updateAccess()
    {
        $input = $this->input->post();
        $empId = $input['empId'];
        $id = $input['id'];

        $request = $input['request'];
        if ($request === 'add') {
            $check = $this->request->getRow('tk_admin_menu_access', ['menu_id' => $id, 'emp_id' => $empId]);
            if ($check) {
                $data = ['status' => 1];
                $cond = ['menu_id' => $id, 'emp_id' => $empId];
                $this->request->update_table('tk_admin_menu_access', $data, $cond);
            } else {
                $data = ['emp_id' => $empId, 'menu_id' => $id, 'status' => 1,];
                $this->request->insert('tk_admin_menu_access', $data);
            }
        } else {
            $cond = ['menu_id' => $id, 'emp_id' => $empId];
            $this->request->delete_table('tk_admin_menu_access', $cond);
        }
        echo json_encode(['message' => 'success']);
        return;
    }

    public function update_adminUser()
    {
        $empId = $this->input->post('empId');
        $type = $this->input->post('type');
        $status = $this->input->post('status');

        $cond = ['emp_id' => $empId];
        $check = $this->request->getRow('tk_user_admin', $cond);
        if ($check) {

            $data = ['type' => $type, 'status' => $status];
            $this->request->update_table('tk_user_admin', $data, $cond);

            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function add_adminUser()
    {
        $empId = $this->input->post('empId');
        $type = $this->input->post('type');

        $cond = ['emp_id' => $empId];
        $check = $this->request->getRow('tk_user_admin', $cond);
        if ($check) {
            echo json_encode(['message' => 'User already exists.']);
            return;
        }

        $menus = $this->input->post('menus');
        foreach ($menus as $id) {
            $check = $this->request->getRow('tk_admin_menu_access', ['emp_id' => $empId, 'menu_id' => $id]);
            if ($check) {
                $data = ['status' => 1];
                $cond = ['emp_id' => $empId, 'menu_id' => $id];
                $this->request->update_table('tk_admin_menu_access', $data, $cond);
            } else {
                $data = ['emp_id' => $empId, 'menu_id' => $id, 'status' => 1,];
                $this->request->insert('tk_admin_menu_access', $data);
            }
        }

        $data = [
            'emp_id'    => $empId,
            'username'  => $empId,
            'password'  => md5('Hrms2014'),
            'type'      => $type,
            'status'    => 1,
            'date'      => date('Y-m-d'),
        ];
        $insert = $this->request->insert('tk_user_admin', $data);
        if ($insert) {
            echo json_encode(['message' => 'success']);
            return;
        }
    }

    public function resetPassword()
    {
        $empId = $this->input->post('empId');
        $cond = ['emp_id' => $empId];
        $check = $this->request->getRow('tk_user_admin', $cond);
        if ($check) {
            $data = ['password' => md5('Hrms2014'),];
            $update = $this->request->update_table('tk_user_admin', $data, $cond);
            if ($update) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }
    }

    public function updateSubordinates()
    {
        $approver = $this->input->post('approver');
        $approvee = $this->input->post('approvee');
        $request = $this->input->post('request');

        if ($request === 'add') {
            $data = [
                'approver' => $approver,
                'approvee' => $approvee,
                'created_by' => $this->userId,
                'date_created' => date('Y-m-d H:i:s'),
            ];
            $sub = $this->request->insert('tk_approvers_subordinates', $data);
        } else {
            $cond = ['approver' => $approver, 'approvee' => $approvee];
            $this->request->delete_table('tk_approvers_subordinates', $cond);
        }

        echo json_encode(['message' => 'success']);
        return;
    }

    public function updateAdminAccount()
    {
        $input = $this->input->post();
        $userId = $input['userId'];
        $request = $input['request'];
        if ($request === 'username') {
            $username = $input['username'];

            $cond = ['tkuaId !=' => $userId, 'username' => $username];
            $check = $this->request->getRow('tk_user_admin', $cond);
            if ($check) {
                echo json_encode(['message' => 'Username already exists.']);
                return;
            }

            $data = ['username' => $username,];
            $update = $this->request->update_table('tk_user_admin', $data, ['tkuaId' => $userId]);
            if ($update) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }
        if ($request === 'password') {
            $currentPassword = $input['currentPassword'];
            $password = $input['password'];


            $cond = ['tkuaId' => $userId, 'password' => md5($currentPassword)];
            $check = $this->request->getRow('tk_user_admin', $cond);
            if (!$check) {
                echo json_encode(['message' => 'Current password does not match.']);
                return;
            }

            $data = ['password' => md5($password),];
            $update = $this->request->update_table('tk_user_admin', $data, ['tkuaId' => $userId]);
            if ($update) {
                echo json_encode(['message' => 'success']);
                return;
            }
        }
    }
}
