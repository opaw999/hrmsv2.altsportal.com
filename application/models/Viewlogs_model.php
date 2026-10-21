<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Viewlogs_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_cutOff_sched($emp_id){
        return $this->db->select('startFC, endFC, startSC, endSC')
            ->from('tk_employee_cutoff_schedule AS tkecs')
            ->join('tk_cutoff_schedule AS tkcs', 'tkcs.id = tkecs.co_schedule')
            ->where('emp_id', $emp_id)
            ->where('tkecs.status', 1)
            ->get()
            ->row_array();
    }

    /**
     * Return distinct months (YYYY-MM) and years that the employee
     * actually has cutoff records for.
     */
    public function get_cutoff_months_and_years($emp_id)
    {
        $rows = $this->db
            ->select('YEAR(co_startDate) AS y, MONTH(co_startDate) AS m')
            ->from('tk_employee_cutoff')
            ->where('emp_id', $emp_id)
            ->group_by(array('y', 'm'))
            ->order_by('y', 'DESC')
            ->order_by('m', 'DESC')
            ->get()
            ->result_array();

        $years  = array();
        $months = array();   // ['2026-09', '2026-10', ...]

        foreach ($rows as $r) {
            $y = (int) $r['y'];
            $m = (int) $r['m'];
            if ($y && $m) {
                $years[$y] = true;
                $months[sprintf('%04d-%02d', $y, $m)] = true;
            }
        }

        return array(
            'years'  => array_keys($years),
            'months' => array_keys($months),
        );
    }

    /**
     * Return the actual cutoffs the employee has in tk_employee_cutoff
     * for a given year (optional).
     */
    public function get_employee_cutoffs($emp_id, $year = null)
    {
        $this->db
            ->select('
                co_startDate,
                co_endDate,
                co_hours,
                co_days,
                deficit_hours,
                co_leaves
            ')
            ->from('tk_employee_cutoff')
            ->where('emp_id', $emp_id);

        if ($year) {
            $this->db
                ->where('YEAR(co_startDate)', $year);
        }

        return $this->db
            ->order_by('co_startDate', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Get all data needed for DTR display:
     * - attendance entries
     * - holidays
     * - whole-day leaves
     * - hourly leaves
     */
    public function get_dtr_data($emp_id, $start_date, $end_date) {

        // 1. Attendance records
        $attendance = $this->db->select('*')
            ->from('tk_attendance_records')
            ->where('emp_id', $emp_id)
            ->where('attendance_date >=', $start_date)
            ->where('attendance_date <=', $end_date)
            ->order_by('attendance_date', 'ASC')
            ->get()
            ->result_array();

        // 2. Holidays
        $holidays = $this->db->select('date, holiday')
            ->from('tk_holidays')
            ->where('date >=', $start_date)
            ->where('date <=', $end_date)
            ->get()
            ->result_array();

        // 3. Whole-day leaves (isFixed = 1)
        $wholeday_leaves = $this->db
            ->select('
                tk_leave_request_dates.code,
                tk_leave_request_dates.date,
                tk_leave_request_dates.payment,
                tk_leave_requests.leaveId      AS leave_id,
                tk_leave_requests.status    AS leave_status
            ')
            ->from('tk_leave_request_dates')
            ->join('tk_leave_requests', 'tk_leave_request_dates.leaveId = tk_leave_requests.leaveId')
            ->where('tk_leave_requests.emp_id', $emp_id)
            ->where('tk_leave_request_dates.date >=', $start_date)
            ->where('tk_leave_request_dates.date <=', $end_date)
            ->where('tk_leave_requests.isFixed', '1')
            // ->where('tk_leave_requests.status', 'approved')
            ->get()
            ->result_array();

        // 4. Hourly leaves (isFixed = 0)
        $hourly_leaves = $this->db
            ->select('
                tk_leave_request_details.tklrdId,
                tk_leave_request_details.leaveId,
                tk_leave_request_details.code,
                tk_leave_request_details.groupId,
                tk_leave_request_details.group_hours,
                tk_leave_request_details.time_range,
                tk_leave_request_details.time_row,
                tk_leave_request_details.remarks,
                tk_leave_request_details.status       AS detail_status,
                tk_leave_requests.startDate           AS startDate,
                tk_leave_requests.endDate             AS endDate,
                tk_leave_requests.leaveId             AS leave_id,
            ')
            ->from('tk_leave_request_details')
            ->join('tk_leave_requests', 'tk_leave_request_details.leaveId = tk_leave_requests.leaveId')
            ->where('tk_leave_requests.emp_id', $emp_id)
            ->where('tk_leave_requests.startDate <=', $end_date)     // overlap
            ->where('tk_leave_requests.endDate   >=', $start_date)   // overlap
            ->where('tk_leave_requests.isFixed', '0')
            // ->where('tk_leave_requests.status', 'approved')
            ->order_by('tk_leave_requests.startDate', 'ASC')
            ->order_by('tk_leave_request_details.time_range', 'ASC')
            ->group_by('tk_leave_request_details.groupId ')
            ->get()
            ->result_array();

        $cutoff_summary = $this->db
            ->select('co_days, co_hours, deficit_hours, co_leaves')
            ->from('tk_employee_cutoff')
            ->where('emp_id', $emp_id)
            ->where('co_startDate', $start_date)
            ->where('co_endDate', $end_date)
            ->get()
            ->result_array();

        $change_dayoffs = $this->db
        ->select('tkcdoId, old_dayoff, new_dayoff, created')
        ->from('tk_change_dayoff')
        ->where('emp_id', $emp_id)
        ->where('status', 'approved')
        ->order_by('created', 'ASC')
        ->order_by('tkcdoId', 'ASC')
        ->get()
        ->result_array();

        return [
            'attendance'        => $attendance,
            'holidays'          => $holidays,
            'wholeday_leaves'   => $wholeday_leaves,
            'hourly_leaves'     => $hourly_leaves,
            'change_dayoffs'  => $change_dayoffs,
            'cutoff_summary'    => $cutoff_summary,
        ];
    }

    private function get_change_dayoffs($emp_id)
    {
        return $this->db
            ->select('tkcdoId, old_dayoff, new_dayoff, created')
            ->from('tk_change_dayoff')
            ->where('emp_id', $emp_id)
            ->where('status', 'approved')
            ->order_by('created', 'ASC')
            ->order_by('tkcdoId', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Get all attendance-group segments for a given employee on a given date.
     * Joins to tk_attendance_records to filter by emp + date.
     */
    public function get_daily_entries($emp_id, $date)
    {
        return $this->db
            ->select('
                g.id,
                g.group_index,
                g.type,
                g.status,
                g.approved_by,
                g.approved_at,
                g.category,
                g.time_range,
                g.total_hours,
                g.remarks,
                g.location,
                g.latitude,
                g.longitude,
                g.created_at,
                a.attendance_date
            ')
            ->from('tk_attendance_groups AS g')
            ->join('tk_attendance_records AS a', 'g.attendance_id = a.id', 'inner')
            ->where('a.emp_id', $emp_id)
            ->where('a.attendance_date', $date)
            ->order_by('g.group_index', 'ASC')
            ->order_by('g.id', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Get full details for a leave request.
     *
     * Adjust table/column names to match your schema.
     */
    public function get_leave_details($emp_id, $leave_id)
    {
        // Main leave request (tk_leave_requests columns only)
        $leave = $this->db
            ->select('
                lr.leaveId,
                lr.emp_id,
                lr.code,
                lr.payment,
                lr.geo_location,
                lr.startDate,
                lr.endDate,
                lr.isFixed,
                lr.status,
                lr.reason
            ')
            ->from('tk_leave_requests AS lr')
            ->where('lr.leaveId', $leave_id)
            ->where('lr.emp_id', $emp_id)
            ->get()
            ->row_array();

        if (!$leave) return null;

        // Hourly sub-details
        $details = array();
        if ((int) $leave['isFixed'] === 0) {
            $details = $this->db
                ->select('
                    tklrdId,
                    leaveId,
                    groupId,
                    code,
                    group_hours,
                    time_row,
                    time_range,
                    remarks,
                    location,
                    status,
                    created
                ')
                ->from('tk_leave_request_details')
                ->where('leaveId', $leave['leaveId'])
                ->order_by('time_row', 'ASC')
                ->order_by('time_range', 'ASC')
                ->group_by('groupId')
                ->get()
                ->result_array();
        }

        // Whole-day per-date rows
        $dates = array();
        if ((int) $leave['isFixed'] === 1) {
            $dates = $this->db
                ->select('tklrdId, leaveId, emp_id, code, year, date, payment, status, created')
                ->from('tk_leave_request_dates')
                ->where('leaveId', $leave['leaveId'])
                ->order_by('date', 'ASC')
                ->get()
                ->result_array();
        }

        $leave['details'] = $details;
        $leave['dates']   = $dates;

        return $leave;
    }
}