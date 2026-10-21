<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Timelog_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // public function save_attendance_timelog($payload)
    // {
    //     $emp_id          = $payload['emp_id'];
    //     $attendance_date = $payload['date'];
    //     $punch_type      = $payload['punch_type'];
    //     $current_time    = $payload['current_time'];
    //     $hour_num        = $payload['hour_num'];

    //     $this->db->trans_start();

    //     // 1. Ensure master daily record in tk_attendance_records
    //     $record = $this->db
    //         ->where('emp_id', $emp_id)
    //         ->where('attendance_date', $attendance_date)
    //         ->get('tk_attendance_records')
    //         ->row();

    //     if (!$record) {
    //         $record_data = array(
    //             'emp_id'          => $emp_id,
    //             'attendance_date' => $attendance_date,
    //             'submitted_at'    => date('Y-m-d H:i:s'),
    //             'in1'             => $current_time,
    //             'out1'            => NULL,
    //             'in2'             => NULL,
    //             'out2'            => NULL,
    //             'hours_worked'    => 0,
    //             'remarks'         => $payload['remarks'],
    //             'status'          => 'approved',
    //             'created_at'      => date('Y-m-d H:i:s'),
    //             'updated_at'      => date('Y-m-d H:i:s')
    //         );
    //         $this->db->insert('tk_attendance_records', $record_data);
    //         $attendance_id = $this->db->insert_id();
    //     } else {
    //         $attendance_id = $record->id;
    //     }

    //     $display_time = date('h:i A', strtotime($current_time));

    //     // 2. Handle PUNCH IN
    //     if ($punch_type === 'in') {
    //         // Count previous groups for this day to set 0-based sequential index (0, 1, 2...)
    //         $group_index = $this->db
    //             ->where('attendance_id', $attendance_id)
    //             ->count_all_results('tk_attendance_groups');

    //         $group_data = array(
    //             'attendance_id' => $attendance_id,
    //             'group_index'   => $group_index,
    //             'type'          => 'regular',
    //             'category'      => '',
    //             'time_range'    => $display_time, // e.g. "08:59 AM"
    //             'total_hours'   => 0,
    //             'remarks'       => ($hour_num < 12) ? 'Morning In' : 'Afternoon In',
    //             'status'        => 'pending',
    //             'location'      => $payload['location'],
    //             'latitude'      => $payload['latitude'],
    //             'longitude'     => $payload['longitude'],
    //             'accuracy'      => $payload['accuracy']
    //         );
    //         $this->db->insert('tk_attendance_groups', $group_data);
    //         $group_id = $this->db->insert_id();

    //         // Insert matching time entry row
    //         $entry_data = array(
    //             'attendance_id' => $attendance_id,
    //             'group_id'      => $group_id,
    //             'hour'          => $hour_num,
    //             'start_time'    => $current_time,
    //             'end_time'      => '00:00:00'
    //         );
    //         $this->db->insert('tk_attendance_time_entries', $entry_data);

    //     } 
    //     // 3. Handle PUNCH OUT
    //     else {
    //         // Locate the current active group
    //         $last_group = $this->db
    //             ->where('attendance_id', $attendance_id)
    //             ->order_by('id', 'DESC')
    //             ->limit(1)
    //             ->get('tk_attendance_groups')
    //             ->row();

    //         if ($last_group) {
    //             $group_id = $last_group->id;

    //             // Locate matching open time entry
    //             $last_entry = $this->db
    //                 ->where('group_id', $group_id)
    //                 ->group_start()
    //                     ->where('end_time', '00:00:00')
    //                     ->or_where('end_time IS NULL', null, false)
    //                     ->or_where('end_time', '')
    //                 ->group_end()
    //                 ->order_by('id', 'DESC')
    //                 ->limit(1)
    //                 ->get('tk_attendance_time_entries')
    //                 ->row();

    //             $group_hours = 0;
    //             if ($last_entry) {
    //                 $this->db->where('id', $last_entry->id)->update('tk_attendance_time_entries', [
    //                     'end_time' => $current_time
    //                 ]);

    //                 $start_sec = strtotime($last_entry->start_time);
    //                 $end_sec   = strtotime($current_time);
    //                 if ($end_sec > $start_sec) {
    //                     $group_hours = round(($end_sec - $start_sec) / 3600, 2);
    //                 }
    //             }

    //             // Update group's range (e.g., "08:59 AM to 09:59 AM") and total_hours
    //             $start_display = explode(' to ', $last_group->time_range)[0];
    //             $updated_range = $start_display . ' to ' . $display_time;

    //             $this->db->where('id', $group_id)->update('tk_attendance_groups', [
    //                 'time_range'        => $updated_range,
    //                 'total_hours'       => $group_hours,
    //                 'updated_location'  => $payload['location'],
    //                 'updated_latitude'  => $payload['latitude'],
    //                 'updated_longitude' => $payload['longitude'],
    //                 'status'            => 'approved',
    //                 'remarks'           => ($hour_num < 12) ? 'Morning Out' : 'End Shift'
    //             ]);
    //         }
    //     }

    //     // 4. Update in1/out1, in2/out2, and total hours_worked (WITH OVERLAP MERGING)
    //     $all_entries = $this->db
    //         ->select('start_time, end_time')
    //         ->where('attendance_id', $attendance_id)
    //         ->order_by('start_time', 'ASC')
    //         ->get('tk_attendance_time_entries')
    //         ->result();

    //     $in1  = null;
    //     $out1 = null;
    //     $in2  = null;
    //     $out2 = null;
    //     $valid_intervals = [];

    //     foreach ($all_entries as $entry) {
    //         $has_valid_out = (!empty($entry->end_time) && $entry->end_time !== '00:00:00');
    //         $start_hour    = (int) date('G', strtotime($entry->start_time));

    //         // Populate in1/out1 (morning < 12) & in2/out2 (afternoon >= 12)
    //         if ($start_hour < 12) {
    //             if ($in1 === null) {
    //                 $in1 = $entry->start_time; // Earliest morning in
    //             }
    //             if ($has_valid_out) {
    //                 $out1 = $entry->end_time;  // Latest morning out
    //             }
    //         } else {
    //             if ($in2 === null) {
    //                 $in2 = $entry->start_time; // Earliest afternoon in
    //             }
    //             if ($has_valid_out) {
    //                 $out2 = $entry->end_time;  // Latest afternoon out
    //             }
    //         }

    //         // Collect valid completed time intervals in epoch seconds
    //         if ($has_valid_out) {
    //             $s = strtotime("{$attendance_date} {$entry->start_time}");
    //             $e = strtotime("{$attendance_date} {$entry->end_time}");
    //             if ($e > $s) {
    //                 $valid_intervals[] = ['start' => $s, 'end' => $e];
    //             }
    //         }
    //     }

    //     // =========================================================================
    //     // INTERVAL MERGING: Avoid double counting overlapping time spans
    //     // =========================================================================
    //     $total_worked_seconds = 0;

    //     if (!empty($valid_intervals)) {
    //         // Sort by start time ascending
    //         usort($valid_intervals, function ($a, $b) {
    //             if ($a['start'] == $b['start']) {
    //                 return 0;
    //             }
    //             return ($a['start'] < $b['start']) ? -1 : 1;
    //         });

    //         $merged = [];
    //         $current = $valid_intervals[0];

    //         for ($i = 1; $i < count($valid_intervals); $i++) {
    //             $next = $valid_intervals[$i];

    //             // If overlapping or contiguous (next starts before or when current ends)
    //             if ($next['start'] <= $current['end']) {
    //                 // Extend the end time to the maximum of both
    //                 $current['end'] = max($current['end'], $next['end']);
    //             } else {
    //                 // No overlap; push current and advance
    //                 $merged[] = $current;
    //                 $current = $next;
    //             }
    //         }
    //         $merged[] = $current;

    //         // Sum the duration of merged non-overlapping blocks
    //         foreach ($merged as $block) {
    //             $total_worked_seconds += ($block['end'] - $block['start']);
    //         }
    //     }

    //     $final_hours_worked = round($total_worked_seconds / 3600, 2);

    //     $this->db->where('id', $attendance_id)->update('tk_attendance_records', [
    //         'in1'          => $in1,
    //         'out1'         => $out1,
    //         'in2'          => $in2,
    //         'out2'         => $out2,
    //         'hours_worked' => $final_hours_worked,
    //         'updated_at'   => date('Y-m-d H:i:s')
    //     ]);

    //     $this->db->where('id', $attendance_id)->update('tk_attendance_records', [
    //         'in1'          => $in1,
    //         'out1'         => $out1,
    //         'in2'          => $in2,
    //         'out2'         => $out2,
    //         'hours_worked' => $final_hours_worked,
    //         'updated_at'   => date('Y-m-d H:i:s')
    //     ]);

    //     $this->db->trans_complete();

    //     if ($this->db->trans_status() === FALSE) {
    //         return ['success' => false, 'message' => 'Database transaction failed.'];
    //     }

    //     return [
    //         'success'      => true,
    //         'id'           => $attendance_id,
    //         'hours_worked' => $final_hours_worked,
    //         'message'      => ($punch_type === 'in') ? 'Time In recorded successfully.' : 'Time Out recorded successfully.'
    //     ];
    // }

    public function save_attendance_timelog($payload)
    {
        $emp_id          = $payload['emp_id'];
        $attendance_date = $payload['date'];
        $punch_type      = $payload['punch_type'];
        $current_time    = $payload['current_time'];
        $hour_num        = $payload['hour_num'];

        $this->db->trans_start();

        // 1. Ensure master daily record in tk_attendance_records
        $record = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $attendance_date)
            ->get('tk_attendance_records')
            ->row();

        if (!$record) {
            $record_data = array(
                'emp_id'          => $emp_id,
                'attendance_date' => $attendance_date,
                'submitted_at'    => date('Y-m-d H:i:s'),
                'in1'             => $current_time,
                'out1'            => NULL,
                'in2'             => NULL,
                'out2'            => NULL,
                'hours_worked'    => 0,
                'remarks'         => $payload['remarks'],
                'status'          => 'approved',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s')
            );
            $this->db->insert('tk_attendance_records', $record_data);
            $attendance_id = $this->db->insert_id();
        } else {
            $attendance_id = $record->id;
        }

        $display_time = date('h:i A', strtotime($current_time));

        // 2. Handle PUNCH IN
        if ($punch_type === 'in') {
            $group_index = $this->db
                ->where('attendance_id', $attendance_id)
                ->count_all_results('tk_attendance_groups');

            $group_data = array(
                'attendance_id' => $attendance_id,
                'group_index'   => $group_index,
                'type'          => 'regular',
                'category'      => '',
                'time_range'    => $display_time,
                'total_hours'   => 0,
                'remarks'       => ($hour_num < 12) ? 'Morning In' : 'Afternoon In',
                'status'        => 'pending',
                'location'      => $payload['location'],
                'latitude'      => $payload['latitude'],
                'longitude'     => $payload['longitude'],
                'accuracy'      => $payload['accuracy']
            );
            $this->db->insert('tk_attendance_groups', $group_data);
            $group_id = $this->db->insert_id();

            // Insert matching time entry row
            $entry_data = array(
                'attendance_id' => $attendance_id,
                'group_id'      => $group_id,
                'hour'          => $hour_num,
                'start_time'    => $current_time,
                'end_time'      => '00:00:00'
            );
            $this->db->insert('tk_attendance_time_entries', $entry_data);

        } 
        // 3. Handle PUNCH OUT
        else {
            $last_group = $this->db
                ->where('attendance_id', $attendance_id)
                ->where('type', 'regular')
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('tk_attendance_groups')
                ->row();

            if ($last_group) {
                $group_id = $last_group->id;

                $last_entry = $this->db
                    ->where('group_id', $group_id)
                    ->group_start()
                        ->where('end_time', '00:00:00')
                        ->or_where('end_time IS NULL', null, false)
                        ->or_where('end_time', '')
                    ->group_end()
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('tk_attendance_time_entries')
                    ->row();

                $group_hours = 0;
                if ($last_entry) {
                    $this->db->where('id', $last_entry->id)->update('tk_attendance_time_entries', [
                        'end_time' => $current_time
                    ]);

                    // Calculate duration clamped within 08:00:00 - 19:00:00
                    $raw_start = strtotime("{$attendance_date} {$last_entry->start_time}");
                    $raw_end   = strtotime("{$attendance_date} {$current_time}");
                    $day_start = strtotime("{$attendance_date} 08:00:00");
                    $day_end   = strtotime("{$attendance_date} 19:00:00");

                    $clamped_start = max($raw_start, $day_start);
                    $clamped_end   = min($raw_end, $day_end);

                    if ($clamped_end > $clamped_start) {
                        $group_hours = round(($clamped_end - $clamped_start) / 3600, 2);
                    }
                }

                $start_display = explode(' to ', $last_group->time_range)[0];
                $updated_range = $start_display . ' to ' . $display_time;

                $this->db->where('id', $group_id)->update('tk_attendance_groups', [
                    'time_range'        => $updated_range,
                    'total_hours'       => $group_hours,
                    'updated_location'  => $payload['location'],
                    'updated_latitude'  => $payload['latitude'],
                    'updated_longitude' => $payload['longitude'],
                    'status'            => 'approved',
                    'remarks'           => ($hour_num < 12) ? 'Morning Out' : 'End Shift'
                ]);
            }
        }

        // =========================================================================
        // 4. Update in1/out1, in2/out2, and total hours_worked (8 AM to 7 PM Clamped)
        // =========================================================================
        $all_entries = $this->db
            ->select('start_time, end_time')
            ->where('attendance_id', $attendance_id)
            ->order_by('start_time', 'ASC')
            ->get('tk_attendance_time_entries')
            ->result();

        $in1  = null;
        $out1 = null;
        $in2  = null;
        $out2 = null;
        $valid_intervals = [];

        $window_start_sec = strtotime("{$attendance_date} 08:00:00");
        $window_end_sec   = strtotime("{$attendance_date} 19:00:00");

        foreach ($all_entries as $entry) {
            $has_valid_out = (!empty($entry->end_time) && $entry->end_time !== '00:00:00');
            $start_hour    = (int) date('G', strtotime($entry->start_time));

            // Populate in1/out1 (morning < 12) & in2/out2 (afternoon >= 12) with actual punch times
            if ($start_hour < 12) {
                if ($in1 === null) {
                    $in1 = $entry->start_time;
                }
                if ($has_valid_out) {
                    $out1 = $entry->end_time;
                }
            } else {
                if ($in2 === null) {
                    $in2 = $entry->start_time;
                }
                if ($has_valid_out) {
                    $out2 = $entry->end_time;
                }
            }

            // Clamp interval to 8:00 AM - 7:00 PM
            if ($has_valid_out) {
                $raw_s = strtotime("{$attendance_date} {$entry->start_time}");
                $raw_e = strtotime("{$attendance_date} {$entry->end_time}");

                // Apply 08:00:00 to 19:00:00 boundary
                $s = max($raw_s, $window_start_sec);
                $e = min($raw_e, $window_end_sec);

                // Only add if the clamped segment is valid
                if ($e > $s) {
                    $valid_intervals[] = ['start' => $s, 'end' => $e];
                }
            }
        }

        // =========================================================================
        // INTERVAL MERGING: Prevent double-counting overlapping intervals
        // =========================================================================
        $total_worked_seconds = 0;

        if (!empty($valid_intervals)) {
            usort($valid_intervals, function ($a, $b) {
                if ($a['start'] == $b['start']) {
                    return 0;
                }
                return ($a['start'] < $b['start']) ? -1 : 1;
            });

            $merged  = [];
            $current = $valid_intervals[0];

            for ($i = 1; $i < count($valid_intervals); $i++) {
                $next = $valid_intervals[$i];

                if ($next['start'] <= $current['end']) {
                    $current['end'] = max($current['end'], $next['end']);
                } else {
                    $merged[] = $current;
                    $current  = $next;
                }
            }
            $merged[] = $current;

            foreach ($merged as $block) {
                $total_worked_seconds += ($block['end'] - $block['start']);
            }
        }

        $final_hours_worked = round($total_worked_seconds / 3600, 2);

        $this->db->where('id', $attendance_id)->update('tk_attendance_records', [
            'in1'          => $in1,
            'out1'         => $out1,
            'in2'          => $in2,
            'out2'         => $out2,
            'hours_worked' => $final_hours_worked,
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Database transaction failed.'];
        }

        return [
            'success'      => true,
            'id'           => $attendance_id,
            'hours_worked' => $final_hours_worked,
            'message'      => ($punch_type === 'in') ? 'Time In recorded successfully.' : 'Time Out recorded successfully.'
        ];
    }

    public function getAttendanceRecords($empId)
    {
        $data = $this->db->select('id')
                ->where('emp_id', $empId)
                ->where('attendance_date', date('Y-m-d'))
                ->get('tk_attendance_records')
                ->row();
        
        return $data;
    } 

    public function getAttendanceGroup($attendanceId)
    {
        $data = $this->db->select('*')
                ->where('attendance_id', $attendanceId)
                ->get('tk_attendance_groups')
                ->row();
        
        return $data;
    }

    public function getAttendanceTimeEntries($attendanceId)
    {
        $data = $this->db->select('*')
                ->where('attendance_id', $attendanceId)
                ->get('tk_attendance_time_entries')
                ->row();
        
        return $data;
    }
    
    public function cancelAndLogAttendanceGroup($empId, $groupId, $attendanceId)
    {
        $this->db->trans_start();

        // 1. Fetch the existing group record from tk_attendance_groups
        $group = $this->db
                ->where('group_index', $groupId)
                ->where('attendance_id', $attendanceId)
                ->get('tk_attendance_groups')->row_array();

        if (!$group) {
            $this->db->trans_rollback();
            return false;
        }

        // 2. Prepare the log payload (substituting attendance_id with emp_id)
        $logData = [
            'emp_id'            => $empId,
            'group_index'       => $group['group_index'],
            'type'              => $group['type'],
            'status'            => $group['status'],
            'approved_by'       => $group['approved_by'],
            'approved_at'       => $group['approved_at'],
            'category'          => $group['category'],
            'time_range'        => $group['time_range'],
            'total_hours'       => $group['total_hours'],
            'remarks'           => $group['remarks'],
            'location'          => $group['location'],
            'latitude'          => $group['latitude'],
            'longitude'         => $group['longitude'],
            'accuracy'          => $group['accuracy'],
            'updated_location'  => $group['updated_location'],
            'updated_latitude'  => $group['updated_latitude'],
            'updated_longitude' => $group['updated_longitude'],
            'updated_at'        => $group['updated_at'],
            'created_at'        => $group['created_at']
        ];

        // =========================================================================
        //  HERE IS THE INSERTION OF LOGS:
        // =========================================================================
        $this->db->insert('tk_attendance_groups_logs', $logData);

        // 4. Delete from original groups and time entries
        $this->db
            ->where('emp_id',$empId)
            ->where('attendance_date', date('Y-m-d'))
            ->delete('tk_attendance_records');

        $this->db->trans_complete();

        return $this->db->trans_status() !== false;
    }


}