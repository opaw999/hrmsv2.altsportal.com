<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Manila');

class Timelog extends Portal_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Timelog_model', 'timelog');
        
        // JSON response
        header('Content-Type: application/json');
    }

    // In your Controller (e.g., Attendance.php or System.php)
    public function get_server_time()
    {
        // Return epoch time in milliseconds
        $response = array(
            'status'    => true,
            'timestamp' => round(microtime(true) * 1000)
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // display time in/out
    public function get_today_logs()
    {
        $emp_id   = $this->session->userdata('empid') ?: $this->session->userdata('user_id');
        $date_str = $this->input->get('date') ?: date('Y-m-d');

        // 1. Fetch daily attendance record
        $record = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $date_str)
            ->get('tk_attendance_records')
            ->row();

        if (!$record) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'           => true,
                    'logs'             => [],
                    'hours_rendered'   => 0.0,
                    'hours_required'   => 7.0,
                    'hours_deficiency' => 7.0,
                    'hours_worked'     => 0.0
                ]));
        }

        // 2. Fetch all time entries joined with group details (added g.type as group_type)
        $entries = $this->db
            ->select('e.*, g.type as group_type, g.location, g.latitude, g.longitude, g.remarks as group_remarks, g.updated_location, g.updated_latitude, g.updated_longitude')
            ->from('tk_attendance_time_entries e')
            ->join('tk_attendance_groups g', 'g.id = e.group_id', 'left')
            ->where('e.attendance_id', $record->id)
            ->order_by('e.id', 'ASC')
            ->get()
            ->result_array();

        $logs = [];
        $total_seconds_rendered = 0;

        foreach ($entries as $row) {
            $loc        = !empty($row['location']) ? $row['location'] : 'Island City Mall, Tagbilaran, Bohol';
            $lat        = $row['latitude'];
            $lng        = $row['longitude'];
            $updatedLoc = $row['updated_location'];
            $updatedLat = $row['updated_latitude'];
            $updatedLng = $row['updated_longitude'];

            $has_start = (!empty($row['start_time']) && $row['start_time'] !== '00:00:00');
            $has_end   = (!empty($row['end_time']) && $row['end_time'] !== '00:00:00');

            // =========================================================================
            // 1. ACCUMULATE HOURS RENDERED FOR ALL TYPES (regular, inoffice, outoffice)
            // =========================================================================
            if ($has_start && $has_end) {
                $start_sec = strtotime("{$date_str} {$row['start_time']}");
                $end_sec   = strtotime("{$date_str} {$row['end_time']}");
                if ($end_sec > $start_sec) {
                    $total_seconds_rendered += ($end_sec - $start_sec);
                }
            }

            // =========================================================================
            // 2. ONLY SHOW "regular" PUNCHES IN THE TIME IN/OUT FEED
            // =========================================================================
            $group_type = !empty($row['group_type']) ? strtolower($row['group_type']) : 'regular';

            $has_start = (!empty($row['start_time']) && $row['start_time'] !== '00:00:00');
            $has_end   = (!empty($row['end_time']) && $row['end_time'] !== '00:00:00');

            if ($group_type === 'regular') {
                // 1. Time In Punch Entry
                if ($has_start) {
                    $in_hour = (int) date('G', strtotime($row['start_time']));
                    $period  = ($in_hour < 12) ? 'Morning In' : (($in_hour < 18) ? 'Afternoon In' : 'Evening In');

                    $logs[] = [
                        'id'          => encrypt_data($row['id']),
                        'type'        => 'in',
                        'actionTitle' => 'Time In',
                        'shiftPeriod' => $period,
                        'timeStr'     => date('h:i:s A', strtotime($row['start_time'])),
                        'dateStr'     => date('M d, Y', strtotime($record->attendance_date)),
                        'locationVal' => $loc,
                        'lat'         => $lat,
                        'lng'         => $lng,
                        // Show cancel on Time In ONLY when there is NO Time Out yet:
                        'can_cancel'  => !$has_end
                    ];
                }

                // 2. Time Out Punch Entry
                if ($has_end) {
                    $out_hour = (int) date('G', strtotime($row['end_time']));
                    $period   = ($out_hour < 12) ? 'Morning Out' : (($out_hour < 18) ? 'Afternoon Out' : 'End Shift');

                    $logs[] = [
                        'id'                 => encrypt_data($row['id']),
                        'type'               => 'out',
                        'actionTitle'        => 'Time Out',
                        'shiftPeriod'        => $period,
                        'timeStr'            => date('h:i:s A', strtotime($row['end_time'])),
                        'dateStr'            => date('M d, Y', strtotime($record->attendance_date)),
                        'locationVal'        => $loc,
                        'lat'                => $lat,
                        'lng'                => $lng,
                        'updatedLocationVal' => $updatedLoc,
                        'updatedLat'         => $updatedLat,
                        'updatedLng'         => $updatedLng,
                        // When Time Out exists, show the cancel button here:
                        'can_cancel'         => true
                    ];
                }
            }
        }

        // 3. Compute Metrics
        $hours_rendered_num   = floor(($total_seconds_rendered / 3600) * 100) / 100;
        $hours_required_num   = 7.00;
        $hours_deficiency_num = max(0, floor(($hours_required_num - $hours_rendered_num) * 100) / 100);

        $hours_rendered   = number_format($hours_rendered_num, 2, '.', '');
        $hours_required   = number_format($hours_required_num, 2, '.', '');
        $hours_deficiency = number_format($hours_deficiency_num, 2, '.', '');

        // Synchronize tk_attendance_records hours_worked with exact computed entries
        if ((float)$record->hours_worked !== (float)$hours_rendered) {
            $this->db->where('id', $record->id)->update('tk_attendance_records', [
                'hours_worked' => $hours_rendered,
                'updated_at'   => date('Y-m-d H:i:s')
            ]);
        }

        // Sort logs so newest appear first
        $logs = array_reverse($logs);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'           => true,
                'logs'             => $logs,
                'hours_rendered'   => $hours_rendered,
                'hours_required'   => $hours_required,
                'hours_deficiency' => $hours_deficiency,
                'hours_worked'     => $hours_rendered
            ]));
    }


    // public function saveTimeLog()
    // {
    //     $rawInput = file_get_contents('php://input');
    //     $payload  = json_decode($rawInput, true);

    //     if (empty($payload)) {
    //         $payload = $this->input->post();
    //     }

    //     if (empty($payload)) {
    //         return $this->output
    //             ->set_content_type('application/json')
    //             ->set_output(json_encode(['success' => false, 'message' => 'Invalid JSON payload']));
    //     }

    //     $emp_id     = !empty($payload['emp_id']) ? $payload['emp_id'] : ($this->session->userdata('empid') ?: $this->session->userdata('user_id'));
    //     $punch_type = isset($payload['punch_type']) ? strtolower($payload['punch_type']) : 'in';
    //     $date_str   = !empty($payload['date']) ? $payload['date'] : date('Y-m-d');

    //     $group0     = !empty($payload['groups'][0]) ? $payload['groups'][0] : [];
    //     $time_str   = date('H:i');
    //     $hour_num   = (int) date('G', strtotime($time_str));

    //     $location   = isset($group0['location']) ? $group0['location'] : (isset($payload['location']) ? $payload['location'] : null);
    //     $latitude   = isset($group0['latitude']) ? $group0['latitude'] : (isset($payload['latitude']) ? $payload['latitude'] : null);
    //     $longitude  = isset($group0['longitude']) ? $group0['longitude'] : (isset($payload['longitude']) ? $payload['longitude'] : null);
    //     $accuracy   = isset($group0['accuracy']) ? $group0['accuracy'] : (isset($payload['accuracy']) ? $payload['accuracy'] : 0);

    //     // =========================================================================
    //     // 1. OVERLAP CHECK: Block punches falling into inoffice/outoffice shifts
    //     // =========================================================================
    //     $existingOfficeEntry = $this->db
    //         ->select('ate.id, ate.start_time, ate.end_time, at.type')
    //         ->from('tk_attendance_time_entries ate')
    //         ->join('tk_attendance_records ar', 'ar.id = ate.attendance_id', 'inner')
    //         ->join('tk_attendance_groups at', 'at.id = ate.group_id', 'inner')
    //         ->where('ar.emp_id', $emp_id)
    //         ->where('ar.attendance_date', $date_str)
    //         ->where_in('at.type', ['inoffice', 'outoffice'])
    //         ->group_start()
    //             // Case 1: Punch falls within a closed entry
    //             ->group_start()
    //                 ->where('ate.start_time <=', $time_str)
    //                 ->where('ate.end_time >=', $time_str)
    //                 ->where('ate.end_time !=', '00:00:00')
    //                 ->where('ate.end_time IS NOT NULL', null, false)
    //             ->group_end()
    //             // Case 2: Punch falls into an open entry
    //             ->or_group_start()
    //                 ->where('ate.start_time <=', $time_str)
    //                 ->group_start()
    //                     ->where('ate.end_time', '00:00:00')
    //                     ->or_where('ate.end_time IS NULL', null, false)
    //                     ->or_where('ate.end_time', '')
    //                 ->group_end()
    //             ->group_end()
    //         ->group_end()
    //         ->get()
    //         ->row();

    //     if ($existingOfficeEntry) {
    //         $formattedStart = date('h:i A', strtotime($existingOfficeEntry->start_time));
    //         $formattedEnd   = (!empty($existingOfficeEntry->end_time) && $existingOfficeEntry->end_time !== '00:00:00')
    //             ? date('h:i A', strtotime($existingOfficeEntry->end_time))
    //             : 'present';
    //         $typeName       = ucfirst($existingOfficeEntry->type);

    //         return $this->output
    //             ->set_content_type('application/json')
    //             ->set_output(json_encode([
    //                 'success' => false,
    //                 'message' => "An existing {$typeName} entry already covers this time ({$formattedStart} - {$formattedEnd}). You cannot punch during this period."
    //             ]));
    //     }

    //     // =========================================================================
    //     // 2. FETCH ATTENDANCE RECORD
    //     // =========================================================================
    //     $existing = $this->db
    //         ->where('emp_id', $emp_id)
    //         ->where('attendance_date', $date_str)
    //         ->get('tk_attendance_records')
    //         ->row();

    //     // Check if there is an active unclosed REGULAR entry
    //     $open_entry = null;
    //     if ($existing) {
    //         $open_entry = $this->db
    //             ->select('ate.*')
    //             ->from('tk_attendance_time_entries ate')
    //             ->join('tk_attendance_groups ag', 'ag.id = ate.group_id', 'inner')
    //             ->where('ate.attendance_id', $existing->id)
    //             ->where('ag.type', 'regular')
    //             ->group_start()
    //                 ->where('ate.end_time', '00:00:00')
    //                 ->or_where('ate.end_time IS NULL', null, false)
    //                 ->or_where('ate.end_time', '')
    //             ->group_end()
    //             ->order_by('ate.id', 'DESC')
    //             ->limit(1)
    //             ->get()
    //             ->row();
    //     }

    //     $current_timestamp = strtotime("$date_str $time_str");
    //     $interval_limit    = 15 * 60; // 15 minutes (900 seconds)

    //     // =========================================================================
    //     // 3. REGULAR PUNCH IN / OUT RULES
    //     // =========================================================================
    //     if ($punch_type === 'in') {
    //         if ($open_entry) {
    //             return $this->output
    //                 ->set_content_type('application/json')
    //                 ->set_output(json_encode([
    //                     'success' => false,
    //                     'message' => 'You are currently timed in at ' . date('h:i A', strtotime($open_entry->start_time)) . '. Please time out first.'
    //                 ]));
    //         }

    //         // Enforce 15-minute wait ONLY after previous regular punch-out that occurred BEFORE current time
    //         if ($existing) {
    //             $last_completed_entry = $this->db
    //                 ->select('ate.*')
    //                 ->from('tk_attendance_time_entries ate')
    //                 ->join('tk_attendance_groups ag', 'ag.id = ate.group_id', 'inner')
    //                 ->where('ate.attendance_id', $existing->id)
    //                 ->where('ag.type', 'regular')
    //                 ->where('ate.end_time !=', '00:00:00')
    //                 ->where('ate.end_time IS NOT NULL', null, false)
    //                 ->where('ate.end_time <=', $time_str) // MUST have completed at or before current time
    //                 ->order_by('ate.end_time', 'DESC')
    //                 ->limit(1)
    //                 ->get()
    //                 ->row();

    //             if ($last_completed_entry) {
    //                 $last_out_time = strtotime("$date_str " . $last_completed_entry->end_time);
    //                 $diff = $current_timestamp - $last_out_time;

    //                 // Only evaluate if difference is positive and under the threshold
    //                 if ($diff >= 0 && $diff < $interval_limit) {
    //                     $remaining = ceil(($interval_limit - $diff) / 60);
    //                     return $this->output
    //                         ->set_content_type('application/json')
    //                         ->set_output(json_encode([
    //                             'success' => false,
    //                             'message' => "Please wait {$remaining} more minute(s) before timing in again (15-minute interval required)."
    //                         ]));
    //                 }
    //             }
    //         }
    //     } else {
    //         // PUNCH OUT: Requires an active open regular punch-in
    //         if (!$open_entry) {
    //             return $this->output
    //                 ->set_content_type('application/json')
    //                 ->set_output(json_encode([
    //                     'success' => false,
    //                     'message' => 'Cannot time out. You are not currently timed in.'
    //                 ]));
    //         }

    //         // Enforce 15 minutes between punch-in and punch-out
    //         $start_timestamp = strtotime("$date_str " . $open_entry->start_time);
    //         $diff = $current_timestamp - $start_timestamp;

    //         if ($diff >= 0 && $diff < $interval_limit) {
    //             $remaining = ceil(($interval_limit - $diff) / 60);
    //             return $this->output
    //                 ->set_content_type('application/json')
    //                 ->set_output(json_encode([
    //                     'success' => false,
    //                     'message' => "Cannot time out yet. You must wait {$remaining} more minute(s) (15-minute interval required)."
    //                 ]));
    //         }
    //     }

    //     // =========================================================================
    //     // 4. DISPATCH TO MODEL
    //     // =========================================================================
    //     $modelPayload = array(
    //         'emp_id'       => $emp_id,
    //         'date'         => $date_str,
    //         'punch_type'   => $punch_type,
    //         'current_time' => $time_str,
    //         'hour_num'     => $hour_num,
    //         'remarks'      => !empty($payload['remarks']) ? $payload['remarks'] : 'Biometric Web Punch',
    //         'location'     => $location,
    //         'latitude'     => $latitude,
    //         'longitude'    => $longitude,
    //         'accuracy'     => $accuracy
    //     );

    //     $result = $this->timelog->save_attendance_timelog($modelPayload);

    //     return $this->output
    //         ->set_content_type('application/json')
    //         ->set_output(json_encode($result));
    // }

    public function saveTimeLog()
    {
        $rawInput = file_get_contents('php://input');
        $payload  = json_decode($rawInput, true);

        if (empty($payload)) {
            $payload = $this->input->post();
        }

        if (empty($payload)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Invalid JSON payload']));
        }

        $emp_id     = !empty($payload['emp_id']) ? $payload['emp_id'] : ($this->session->userdata('empid') ?: $this->session->userdata('user_id'));
        $punch_type = isset($payload['punch_type']) ? strtolower($payload['punch_type']) : 'in';
        $date_str   = !empty($payload['date']) ? $payload['date'] : date('Y-m-d');

        $group0     = !empty($payload['groups'][0]) ? $payload['groups'][0] : [];
        // Use exact time with seconds
        $time_str   = !empty($group0['timeRange']) ? $group0['timeRange'] : date('H:i:s');
        $hour_num   = (int) date('G', strtotime($time_str));

        $location   = isset($group0['location']) ? $group0['location'] : (isset($payload['location']) ? $payload['location'] : null);
        $latitude   = isset($group0['latitude']) ? $group0['latitude'] : (isset($payload['latitude']) ? $payload['latitude'] : null);
        $longitude  = isset($group0['longitude']) ? $group0['longitude'] : (isset($payload['longitude']) ? $payload['longitude'] : null);
        $accuracy   = isset($group0['accuracy']) ? $group0['accuracy'] : (isset($payload['accuracy']) ? $payload['accuracy'] : 0);

        // =========================================================================
        // 1. OVERLAP CHECK: Block punches falling into inoffice/outoffice shifts
        // =========================================================================
        $existingOfficeEntry = $this->db
            ->select('ate.id, ate.start_time, ate.end_time, at.type')
            ->from('tk_attendance_time_entries ate')
            ->join('tk_attendance_records ar', 'ar.id = ate.attendance_id', 'inner')
            ->join('tk_attendance_groups at', 'at.id = ate.group_id', 'inner')
            ->where('ar.emp_id', $emp_id)
            ->where('ar.attendance_date', $date_str)
            ->where_in('at.type', ['inoffice', 'outoffice'])
            ->group_start()
                // Case 1: Punch falls within a closed entry
                ->group_start()
                    ->where('ate.start_time <=', $time_str)
                    ->where('ate.end_time >=', $time_str)
                    ->where('ate.end_time !=', '00:00:00')
                    ->where('ate.end_time IS NOT NULL', null, false)
                ->group_end()
                // Case 2: Punch falls into an open entry
                ->or_group_start()
                    ->where('ate.start_time <=', $time_str)
                    ->group_start()
                        ->where('ate.end_time', '00:00:00')
                        ->or_where('ate.end_time IS NULL', null, false)
                        ->or_where('ate.end_time', '')
                    ->group_end()
                ->group_end()
            ->group_end()
            ->get()
            ->row();

        if ($existingOfficeEntry) {
            $formattedStart = date('h:i A', strtotime($existingOfficeEntry->start_time));
            $formattedEnd   = (!empty($existingOfficeEntry->end_time) && $existingOfficeEntry->end_time !== '00:00:00')
                ? date('h:i A', strtotime($existingOfficeEntry->end_time))
                : 'present';
            $typeName       = ucfirst($existingOfficeEntry->type);

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => "An existing {$typeName} entry already covers this time ({$formattedStart} - {$formattedEnd}). You cannot punch during this period."
                ]));
        }

        // =========================================================================
        // 2. FETCH ATTENDANCE RECORD
        // =========================================================================
        $existing = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $date_str)
            ->get('tk_attendance_records')
            ->row();

        // Check if there is an active unclosed REGULAR entry
        $open_entry = null;
        if ($existing) {
            $open_entry = $this->db
                ->select('ate.*')
                ->from('tk_attendance_time_entries ate')
                ->join('tk_attendance_groups ag', 'ag.id = ate.group_id', 'inner')
                ->where('ate.attendance_id', $existing->id)
                ->where('ag.type', 'regular')
                ->group_start()
                    ->where('ate.end_time', '00:00:00')
                    ->or_where('ate.end_time IS NULL', null, false)
                    ->or_where('ate.end_time', '')
                ->group_end()
                ->order_by('ate.id', 'DESC')
                ->limit(1)
                ->get()
                ->row();
        }

        // =========================================================================
        // 3. REGULAR PUNCH IN / OUT RULES (15-min interval checks removed)
        // =========================================================================
        if ($punch_type === 'in') {
            if ($open_entry) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'You are currently timed in at ' . date('h:i A', strtotime($open_entry->start_time)) . '. Please time out first.'
                    ]));
            }
        } else {
            // PUNCH OUT: Requires an active open regular punch-in
            if (!$open_entry) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Cannot time out. You are not currently timed in.'
                    ]));
            }
        }

        // =========================================================================
        // 4. DISPATCH TO MODEL
        // =========================================================================
        $modelPayload = array(
            'emp_id'       => $emp_id,
            'date'         => $date_str,
            'punch_type'   => $punch_type,
            'current_time' => $time_str,
            'hour_num'     => $hour_num,
            'remarks'      => !empty($payload['remarks']) ? $payload['remarks'] : 'Biometric Web Punch',
            'location'     => $location,
            'latitude'     => $latitude,
            'longitude'    => $longitude,
            'accuracy'     => $accuracy
        );

        $this->load->model('Timelog_model', 'timelog');
        $result = $this->timelog->save_attendance_timelog($modelPayload);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function cancel_punch()
    {
        $rawInput = file_get_contents('php://input');
        $payload  = json_decode($rawInput, true);

        if (empty($payload)) {
            $payload = $this->input->post();
        }

        if (empty($payload)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Invalid JSON payload']));
        }

        $emp_id     = $this->session->userdata('empid');
        $entry_id   = decrypt_data($payload['entry_id']);
        // get the attendance records
        $attendanceRecords = $this->timelog->getAttendanceRecords($emp_id);
        // get the attendance groups
        $attendanceGroup = $this->timelog->getAttendanceGroup($attendanceRecords->id);
        
        $insertLogs = $this->timelog->cancelAndLogAttendanceGroup($emp_id, $attendanceGroup->group_index, $attendanceGroup->attendance_id);

       if ($insertLogs) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => true,
                    'message' => 'Punch entry canceled successfully.'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Failed to cancel punch. Please try again.'
                ]));
        }
       

    }
}