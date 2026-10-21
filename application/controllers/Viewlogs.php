<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Manila');

class Viewlogs extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Viewlogs_model');
        $this->load->helper(array('url', 'file'));
        
        // JSON response
        header('Content-Type: application/json');
    }

    public function get_cutoff_menu()
    {
        $emp_id = $this->session->userdata('empid');

        $menu = $this->generate_dtr_menu($emp_id);

        echo json_encode([
            'success' => true,
            'menu' => $menu
        ]);
    }


    public function generate_dtr_menu($emp_id)
    {
        $cutoff_sched = $this->Viewlogs_model->get_cutOff_sched($emp_id);

        $cut1_st = $cutoff_sched['startFC'] ? $cutoff_sched['startFC'] : '';
        $cut1_en = $cutoff_sched['endFC'] ? $cutoff_sched['endFC'] : '';

        $cut2_st = $cutoff_sched['startSC'] ? $cutoff_sched['startSC'] : '';
        $cut2_en = $cutoff_sched['endSC'] ? $cutoff_sched['endSC'] : '';

        $base = date('Y-m-d', strtotime('first day of this month'));

        $menu = [];

        // First cutoff: previous month 24 -> current month 08
        $menu['first'] =
            date('Y M', strtotime("$base -1 month")) . " $cut1_st to " .
            date('Y M') . " $cut1_en";

        // Second cutoff: current month 09 -> current month 23
        $menu['second'] =
            date('Y M') . " $cut2_st to " .
            date('Y M') . " $cut2_en";

        // Third cutoff: current month 24 -> next month 08
        $menu['third'] =
            date('Y M') . " $cut1_st to " .
            date('Y M', strtotime("$base +1 month")) . " $cut1_en";

        return $menu;
    }

    public function get_cutoff_entries()
    {
        $emp_id     = $this->session->userdata('empid');
        $start_date = $this->input->post('start_date');
        $end_date   = $this->input->post('end_date');

        $HOURS_PER_WHOLE_DAY = 7;   // 1 whole-day leave = 7 hrs
        $MAX_HOURS_PER_DAY   = 9;   // cap per attendance row

        // Validate format YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid date range: start=' . $start_date . ' end=' . $end_date
            ]);
            return;
        }

        if ($start_date > $end_date) {
            echo json_encode(['success' => false, 'message' => 'Start date must be before end date.']);
            return;
        }

        $data = $this->Viewlogs_model->get_dtr_data($emp_id, $start_date, $end_date);

        // Index lookup tables
        $attendance_by_date = [];
        foreach ($data['attendance'] as $row) {
            $attendance_by_date[$row['attendance_date']] = $row;
        }

        $holiday_by_date = [];
        foreach ($data['holidays'] as $row) {
            $holiday_by_date[$row['date']] = $row['holiday'];
        }

        $wholeday_by_date = array();
        foreach ($data['wholeday_leaves'] as $row) {
            $wholeday_by_date[$row['date']][] = array(
                'code'     => $row['code'],
                'leave_id' => $row['leave_id'],
                'leave_status' => $row['leave_status'],
                'payment' => $row['payment'],
            );
        }

        $hourly_by_date = array();
        foreach ($data['hourly_leaves'] as $row) {
            // Expand across the leave's date range (usually just one day for hourly)
            $cur  = new DateTime($row['startDate']);
            $last = new DateTime($row['endDate']);
            $last->modify('+1 day');   // inclusive

            while ($cur < $last) {
                $d = $cur->format('Y-m-d');
                if ($d >= $start_date && $d <= $end_date) {
                    $hourly_by_date[$d][] = array(
                        'code'        => $row['code'],
                        'leave_id'    => $row['leave_id'],
                        'time_range'  => $row['time_range'],    // "08:00 AM - 08:59 AM"
                        'group_hours' => $row['group_hours'],   // 1.00
                        'remarks'     => $row['remarks'],
                        'detail_status' => $row['detail_status'],
                    );
                }
                $cur->modify('+1 day');
            }
        }

        $cutoff_summary = !empty($data['cutoff_summary'])
            ? $data['cutoff_summary']
            : array('co_days' => 0, 'co_hours' => 0, 'deficit_hours' => 0, 'co_leaves' => 0);
        // ============================================================
        // Effective day-off set (Sundays + change-of-day-off overrides)
        // ============================================================
        $dayoff_set = [];
        $scan = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            (new DateTime($end_date))->modify('+1 day')
        );
        foreach ($scan as $dt) {
            if ($dt->format('N') == 7) {   // Sunday
                $dayoff_set[$dt->format('Y-m-d')] = true;
            }
        }
        foreach ($data['change_dayoffs'] as $change) {
            unset($dayoff_set[$change['old_dayoff']]);
            $dayoff_set[$change['new_dayoff']] = true;
        }

        // ============================================================
        // Build rows
        // ============================================================
        $HOURS_PER_WHOLE_DAY = 7;   // ← 1 whole-day leave = 7 hours

        $rows             = [];
        $totalHours       = 0;
        $totalLeaveHours  = 0;

        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            (new DateTime($end_date))->modify('+1 day')
        );

        foreach ($period as $dt) {
            $date = $dt->format('Y-m-d');

            $row = [
                'date'         => $date,
                'date_label'   => $dt->format('M d, Y'),
                'day'          => $dt->format('D'),
                'in1'          => '',
                'out1'         => '',
                'in2'          => '',
                'out2'         => '',
                'hours_worked' => '',
                'leave_badges' => [],
                'holiday'      => null,
                'is_day_off'   => false,
                'is_holiday'   => false,
                'has_entry'    => false,
            ];

            // Holiday
            if (isset($holiday_by_date[$date])) {
                $row['is_holiday'] = true;
                $row['holiday']    = $holiday_by_date[$date];
            }

            // Day off (effective set)
            if (isset($dayoff_set[$date]) && !$row['is_holiday']) {
                $row['is_day_off'] = true;
            }

            // Attendance entry
            if (isset($attendance_by_date[$date])) {
                $att = $attendance_by_date[$date];

                // Cap hours worked at 9 per day
                $rawHours   = (float) $att['hours_worked'];
                $cappedHrs  = min($rawHours, $MAX_HOURS_PER_DAY);

                $row['in1']          = $att['in1'];
                $row['out1']         = $att['out1'];
                $row['in2']          = $att['in2'];
                $row['out2']         = $att['out2'];
                $row['hours_worked'] = $cappedHrs;      // ← capped value shown in HW column
                $row['has_entry']    = true;

                if (!$row['is_day_off'] && !$row['is_holiday']) {
                    $totalHours += $cappedHrs;          // ← capped value added to total
                }
            }

            // Whole-day leave → 7 hrs per leave
            if (isset($wholeday_by_date[$date])) {
                foreach ($wholeday_by_date[$date] as $item) {
                    $label = $this->resolve_leave_code($item['code']);
                    $row['leave_badges'][] = array(
                        'type'     => 'wholeday',
                        'label'    => $label,
                        'leave_id' => (int) $item['leave_id'],
                        'status'   => strtolower($item['leave_status']),
                        'payment'  => $item['payment'],
                    );
                    $totalLeaveHours += $HOURS_PER_WHOLE_DAY;
                }
            }

            // Hourly leave
            if (isset($hourly_by_date[$date])) {
                foreach ($hourly_by_date[$date] as $item) {
                    $label = $this->resolve_leave_code($item['code']);
                    $hours = (float) $item['group_hours'];

                    $row['leave_badges'][] = array(
                        'type'     => 'hourly',
                        'label'    => $label,
                        'leave_id' => (int) $item['leave_id'],
                        'hours'    => $hours,
                        'status'   => strtolower($item['detail_status']), 
                    );

                    $totalLeaveHours += $hours;   // ← ADD THIS
                }
            }

            $rows[] = $row;
        }

        echo json_encode([
            'success'           => true,
            'rows'              => $rows,
            'total_hours'       => round($totalHours, 2),
            'total_leave_hours' => $totalLeaveHours,                              // ← NEW
            'total_leaves'      => $totalLeaveHours / $HOURS_PER_WHOLE_DAY,       // keep raw count
            'cutoff_summary'    => $cutoff_summary,
            'start_date'        => $start_date,
            'end_date'          => $end_date,
        ]);
    }

    public function get_daily_entries()
    {
        $emp_id = $this->session->userdata('empid');
        $date   = $this->input->post('date');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(['success' => false, 'message' => 'Invalid date.']);
            return;
        }

        $entries = $this->Viewlogs_model->get_daily_entries($emp_id, $date);

        echo json_encode([
            'success' => true,
            'date'    => $date,
            'entries' => $entries,
        ]);
    }

    public function get_leave_details()
    {
        $emp_id   = $this->session->userdata('empid');
        $leave_id = (int) $this->input->post('leave_id');

        if (!$leave_id) {
            echo json_encode(['success' => false, 'message' => 'Missing leave_id.']);
            return;
        }

        $details = $this->Viewlogs_model->get_leave_details($emp_id, $leave_id);

        if (!$details) {
            echo json_encode(['success' => false, 'message' => 'Leave not found.']);
            return;
        }

        // Add long names for display
        $details['code_label'] = $this->resolve_leave_long_name($details['code']);

        foreach ($details['details'] as &$d) {
            $d['code_label'] = $this->resolve_leave_long_name($d['code']);
        }
        unset($d);

        foreach ($details['dates'] as &$d) {
            $d['code_label'] = $this->resolve_leave_long_name($d['code']);
        }
        unset($d);

        echo json_encode(['success' => true, 'leave' => $details]);
    }

    private function leave_code_map()
    {
        return [
            'sick'     => 'M/SL',
            'vacation' => 'VL',
            'medical'  => 'MAL',
            'valid'    => 'GSL',
            'personal' => 'APL',
        ];
    }

    private function resolve_leave_code($type, $fallback = '')
    {
        $key = strtolower(trim((string) $type));
        $map = $this->leave_code_map();

        // Explicit isset() check — works in all PHP 5 versions
        if (isset($map[$key])) {
            return $map[$key];
        }

        // Fallback: use provided fallback, or uppercase the key
        if ($fallback !== '' && $fallback !== null) {
            return $fallback;
        }

        return strtoupper($key);
    }

    private function leave_long_name_map()
    {
        return array(
            'sick'     => 'Medical/Sick Leave',
            'vacation' => 'Vacation Leave',
            'medical'  => 'Medical Assistance Leave',
            'valid'    => 'Government-Mandated Leave Benefits',
            'personal' => 'Approved Personal Leave',
        );
    }

    private function resolve_leave_long_name($code, $fallback = '')
    {
        $key = strtolower(trim((string) $code));
        $map = $this->leave_long_name_map();

        if (isset($map[$key])) {
            return $map[$key];
        }

        if ($fallback !== '' && $fallback !== null) {
            return $fallback;
        }

        return ucwords(str_replace('_', ' ', $key));
    }

    public function get_previous_cutoffs()
    {
        $emp_id = $this->session->userdata('empid');
        $year   = (int) $this->input->get('selectedYear');

        // Get available years and months
        $available = $this->Viewlogs_model->get_cutoff_months_and_years($emp_id);

        // If no year specified, default to the latest available year
        if (!$year && !empty($available['years'])) {
            $year = $available['years'][0];   // already DESC
        }
        if (!$year) $year = (int) date('Y');

        // Pull actual cutoffs from tk_employee_cutoff
        $rows = $this->Viewlogs_model->get_employee_cutoffs($emp_id, $year);

        // Filter by months that exist for this employee (in case a year has gaps)
        $allowedMonths = $available['months'];
        $cutoffs = array();
        $n = 1;

        foreach ($rows as $r) {
            $startMonth = substr($r['co_startDate'], 0, 7);   // "YYYY-MM"

            // Option C: only include if the START month is in the allowed list
            if (!in_array($startMonth, $allowedMonths)) {
                continue;
            }

            $startTs = strtotime($r['co_startDate']);
            $endTs   = strtotime($r['co_endDate']);

            $cutoffs[] = array(
                'number'       => $n++,
                'start'        => $r['co_startDate'],
                'end'          => $r['co_endDate'],
                'display'      => date('M d', $startTs) . ' to ' . date('M d', $endTs),
                'display_full' => date('F d, Y', $startTs) . ' to ' . date('F d, Y', $endTs),
                'hours'        => isset($r['co_hours'])       ? $r['co_hours']       : null,
                'days'         => isset($r['co_days'])        ? $r['co_days']        : null,
                'deficit'      => isset($r['deficit_hours'])  ? $r['deficit_hours']  : null,
                'leaves'       => isset($r['co_leaves'])      ? $r['co_leaves']      : null,
            );
        }

        echo json_encode(array(
            'success'      => true,
            'year'         => $year,
            'year_range'   => array(
                'min' => !empty($available['years']) ? min($available['years']) : $year,
                'max' => !empty($available['years']) ? max($available['years']) : $year,
                'years' => $available['years'],   // full list so JS can build the dropdown
            ),
            'months'       => $available['months'],
            'total_cutoffs'=> count($cutoffs),
            'cutoffs'      => $cutoffs,
        ));
    }
}