<?php
class Request_model extends MY_Model
{
    public $userId = '';
    public function __construct()
    {
        parent::__construct();
        $this->userId = $this->session->userdata('empid');
    }

    public function get_typeId($code)
    {
        $this->db->select('tkrtId');
        $this->db->from('tk_request_type');
        $this->db->where('code', $code);
        $this->db->where('status', 'active');
        $query = $this->db->get();
        return $query->row_array()['tkrtId'];
    }

    public function get_fixed_hour($code)
    {
        $this->db->select('fixed_hour');
        $this->db->from('tk_request_type');
        $this->db->where('code', $code);
        $this->db->where('status', 'active');
        $query = $this->db->get();
        return $query->row_array()['fixed_hour'];
    }

    public function get_leave_type($condition)
    {
        $this->db->select();
        $this->db->from('tk_request_type');
        $this->db->where($condition);
        $this->db->where('status', 'active');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_leave($id)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_requests');
        $this->db->where('leaveId', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_leave_details($id)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_request_details');
        $this->db->where('leaveId', $id);
        $this->db->where('status !=', 'cancelled');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_leave_time_table($id)
    {
        $this->db->select();
        $this->db->from('tk_request_time_table');
        $this->db->where('leaveId', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_cell_leave_details($id)
    {
        $this->db->select('code');
        $this->db->from('tk_leave_request_details');
        $this->db->where('leaveId', $id);
        $this->db->group_by('code');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_time_log_details($id)
    {
        $this->db->select('type');
        $this->db->from('tk_attendance_groups');
        $this->db->where('attendance_id', $id);
        $this->db->group_by('type');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_leave_attachments($id)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_request_attachments');
        $this->db->where('leaveId', $id);
        $this->db->where('status', 'active');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_vl($table, $condition)
    {
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where($condition);
        $this->db->order_by('year', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_vl_per_emp()
    {
        $this->db->select('*');
        $this->db->from('tk_vacation_leave_balance');
        $this->db->group_by('emp_id');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_vl_list_details($empid, $year)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_requests');
        $this->db->where('emp_id', $empid);
        $this->db->where('code', 'vacation');
        $this->db->group_start();
        $this->db->where("YEAR(startDate) = '$year' OR YEAR(endDate) = '$year'");
        $this->db->group_end();
        $this->db->order_by('created_at', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function insert($table, $data)
    {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update_table($table, $data, $condition)
    {
        $this->db->where($condition);
        return $this->db->update($table, $data);
    }

    public function delete_table($table, $condition)
    {
        $this->db->where($condition);
        return $this->db->delete($table);
    }

    public function getRow($table, $condition)
    {
        return $this->db->get_where($table, $condition)->row_array();
    }

    public function getArray($table, $condition)
    {
        return $this->db->get_where($table, $condition)->result_array();
    }

    public function get_week_totals_leaves($empid, $start, $end)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_request_dates');
        $this->db->where('emp_id', $empid);
        $this->db->where("(`date` BETWEEN '$start' AND '$end')");
        $query = $this->db->get();
        return $query->row_array();
    }

    public function search_employee($string)
    {
        $this->db->select('*');
        $this->db->from('employee3');
        $this->db->where('emp_id LIKE', '%' . $string . '%');
        $this->db->or_where('name LIKE', '%' . $string . '%');
        $this->db->order_by('name', 'ASC');
        $this->db->limit(10);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function queryRow($sql)
    {
        $query = $this->db->query($sql);
        return $query->row_array();
    }

    public function queryArray($sql)
    {
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function count_leave($code, $status)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_requests');
        $this->db->where('code', $code);
        $this->db->where('isFixed', 1);
        $this->db->where('status', $status);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function count_leave_group($code, $status)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_request_details');
        $this->db->where('code', $code);
        $this->db->where('status', $status);
        $this->db->group_by('groupId');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function count_leave_sup($code, $status)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_requests as t1');
        $this->db->join('tk_approvers_subordinates as t2', 't1.emp_id = t2.approvee');
        $this->db->join('tk_employee_cutoff_schedule as t3', 't1.emp_id = t3.emp_id');
        $this->db->where('t1.code', $code);
        $this->db->where('t1.isFixed', 1);
        $this->db->where('t1.status', $status);
        $this->db->where('t2.approver', $this->userId);
        $this->db->where('t3.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function count_leave_group_sup($code, $status)
    {
        $this->db->select('*');
        $this->db->from('tk_leave_request_details as t1');
        $this->db->join('tk_leave_requests as t2', 't1.leaveId = t2.leaveId');
        $this->db->join('tk_approvers_subordinates as t3', 't2.emp_id = t3.approvee');
        $this->db->join('tk_employee_cutoff_schedule as t4', 't2.emp_id = t4.emp_id');
        $this->db->where('t1.code', $code);
        $this->db->where('t1.status', $status);
        $this->db->where('t3.approver', $this->userId);
        $this->db->where('t4.status', 1);
        $this->db->group_by('t1.groupId');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function count_do_sup($status)
    {
        $this->db->select('*');
        $this->db->from('tk_change_dayoff as t1');
        $this->db->join('tk_approvers_subordinates as t2', 't1.emp_id = t2.approvee');
        $this->db->join('tk_employee_cutoff_schedule as t3', 't1.emp_id = t3.emp_id');
        $this->db->where('t1.status', $status);
        $this->db->where('t2.approver', $this->userId);
        $this->db->where('t3.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function count_duty_sup($status)
    {
        $this->db->select('*');
        $this->db->from('tk_attendance_groups as t1');
        $this->db->join('tk_attendance_records as t2', 't1.attendance_id = t2.id');
        $this->db->join('tk_approvers_subordinates as t3', 't2.emp_id = t3.approvee ');
        $this->db->join('tk_employee_cutoff_schedule as t4', 't2.emp_id = t4.emp_id');
        $this->db->where('t1.status', $status);
        $this->db->where('t1.type', 'outoffice');
        $this->db->where('t3.approver', $this->userId);
        $this->db->where('t4.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function co_fixed_leave($s, $e, $co, $code, $status)
    {
        $this->db->select('t1.*');
        $this->db->from('tk_leave_requests as t1');
        $this->db->join('tk_employee_cutoff_schedule as t2', 't1.emp_id = t2.emp_id');
        $this->db->where("(t1.`startDate` BETWEEN '$s' AND '$e')");
        $this->db->where('t1.code', $code);
        $this->db->where('t1.isFixed', 1);
        $this->db->where('t1.status', $status);
        $this->db->where('t2.co_schedule', $co);
        $this->db->where('t2.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function co_grouped_leave($s, $e, $co, $code, $status)
    {
        $this->db->select('t1.*');
        $this->db->from('tk_leave_request_details as t1');
        $this->db->join('tk_leave_requests as t2', 't1.leaveId = t2.leaveId');
        $this->db->join('tk_employee_cutoff_schedule as t3', 't2.emp_id = t3.emp_id');
        $this->db->where('t1.code', $code);
        $this->db->where('t1.status', $status);
        $this->db->where("(t2.`startDate` BETWEEN '$s' AND '$e')");
        $this->db->where('t3.co_schedule', $co);
        $this->db->where('t3.status', 1);
        $this->db->group_by('t1.groupId');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function co_fixed_leave_sup($s, $e, $co, $code, $status)
    {
        $this->db->select('t1.*');
        $this->db->from('tk_leave_requests as t1');
        $this->db->join('tk_approvers_subordinates as t2', 't1.emp_id = t2.approvee');
        $this->db->join('tk_employee_cutoff_schedule as t3', 't1.emp_id = t3.emp_id');
        $this->db->where("(t1.`startDate` BETWEEN '$s' AND '$e')");
        $this->db->where('t1.code', $code);
        $this->db->where('t1.isFixed', 1);
        $this->db->where('t1.status', $status);
        $this->db->where('t2.approver', $this->userId);
        $this->db->where('t3.co_schedule', $co);
        $this->db->where('t3.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function co_grouped_leave_sup($s, $e, $co, $code, $status)
    {
        $this->db->select('t1.*');
        $this->db->from('tk_leave_request_details as t1');
        $this->db->join('tk_leave_requests as t2', 't1.leaveId = t2.leaveId');
        $this->db->join('tk_approvers_subordinates as t3', 't2.emp_id = t3.approvee');
        $this->db->join('tk_employee_cutoff_schedule as t4', 't2.emp_id = t4.emp_id');
        $this->db->where('t1.code', $code);
        $this->db->where('t1.status', $status);
        $this->db->where("(t2.`startDate` BETWEEN '$s' AND '$e')");
        $this->db->where('t3.approver', $this->userId);
        $this->db->where('t4.co_schedule', $co);
        $this->db->where('t4.status', 1);
        $this->db->group_by('t1.groupId');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function co_dayoff($s, $e, $co, $status)
    {
        $this->db->select('t1.*');
        $this->db->from('tk_change_dayoff as t1');
        $this->db->join('tk_employee_cutoff_schedule as t2', 't1.emp_id = t2.emp_id');
        $this->db->where("(t1.`old_dayoff` BETWEEN '$s' AND '$e')");
        $this->db->where('t1.status', $status);
        $this->db->where('t2.co_schedule', $co);
        $this->db->where('t2.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function co_dayoff_sup($s, $e, $co, $status)
    {
        $this->db->select('t1.*');
        $this->db->from('tk_change_dayoff as t1');
        $this->db->join('tk_approvers_subordinates as t2', 't1.emp_id = t2.approvee');
        $this->db->join('tk_employee_cutoff_schedule as t3', 't1.emp_id = t3.emp_id');
        $this->db->where("(t1.`old_dayoff` BETWEEN '$s' AND '$e')");
        $this->db->where('t1.status', $status);
        $this->db->where('t2.approver', $this->userId);
        $this->db->where('t3.co_schedule', $co);
        $this->db->where('t3.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function co_duty($s, $e, $co, $status)
    {
        $this->db->select('t1.*');
        $this->db->from('tk_attendance_groups as t1');
        $this->db->join('tk_attendance_records as t2', 't1.attendance_id = t2.id');
        $this->db->join('tk_employee_cutoff_schedule as t3', 't2.emp_id = t3.emp_id');
        $this->db->where('t1.status', $status);
        $this->db->where('t1.type', 'outoffice');
        $this->db->where("(t2.`attendance_date` BETWEEN '$s' AND '$e')");
        $this->db->where('t3.co_schedule', $co);
        $this->db->where('t3.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function co_duty_sup($s, $e, $co, $status)
    {
        $this->db->select('t1.*');
        $this->db->from('tk_attendance_groups as t1');
        $this->db->join('tk_attendance_records as t2', 't1.attendance_id = t2.id');
        $this->db->join('tk_approvers_subordinates as t3', 't2.emp_id = t3.approvee ');
        $this->db->join('tk_employee_cutoff_schedule as t4', 't2.emp_id = t4.emp_id');
        $this->db->where('t1.status', $status);
        $this->db->where('t1.type', 'outoffice');
        $this->db->where("(t2.`attendance_date` BETWEEN '$s' AND '$e')");
        $this->db->where('t3.approver', $this->userId);
        $this->db->where('t4.co_schedule', $co);
        $this->db->where('t4.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function count_vlPayment_sup($status)
    {
        $this->db->select('*');
        $this->db->from('tk_vl_payment as t1');
        $this->db->join('tk_approvers_subordinates as t2', 't1.emp_id = t2.approvee ');
        $this->db->where('t1.status', $status);
        $this->db->where('t2.approver', $this->userId);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function viewVLperYear_sup($year, $status)
    {
        $this->db->select('*');
        $this->db->from('tk_vl_payment as t1');
        $this->db->join('tk_approvers_subordinates as t2', 't1.emp_id = t2.approvee ');
        $this->db->where('t1.year', $year);
        $this->db->where('t1.status', $status);
        $this->db->where('t2.approver', $this->userId);
        $query = $this->db->get();
        return $query->result_array();
    }
}
