<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tk_attendance_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Save attendance (create or update)
     */
    public function save_attendance($payload) {
        $emp_id = $payload['emp_id'];
        $attendance_date = $payload['date'];

        $this->db->trans_start();

        // Check if record exists
        $existing = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $attendance_date)
            ->get('tk_attendance_records')
            ->row();

        $is_update = (bool) $existing;

        // ========== 1. Insert/Update main record ==========
        $record_data = array(
            'emp_id'          => $emp_id,
            'attendance_date' => $attendance_date,
            'submitted_at'    => date('Y-m-d H:i:s'),
            'in1'             => $this->to_nullable($this->get_val($payload, 'dateInfo', 'in1')),
            'out1'            => $this->to_nullable($this->get_val($payload, 'dateInfo', 'out1')),
            'in2'             => $this->to_nullable($this->get_val($payload, 'dateInfo', 'in2')),
            'out2'            => $this->to_nullable($this->get_val($payload, 'dateInfo', 'out2')),
            'hours_worked'    => $this->to_number($this->get_val($payload, 'dateInfo', 'hoursWork', 0)),
            'remarks'         => isset($payload['remarks']) ? $payload['remarks'] : NULL,
            'updated_at'      => date('Y-m-d H:i:s'),
        );

        if ($is_update) {
            $this->db->where('id', $existing->id);
            $this->db->update('tk_attendance_records', $record_data);
            $attendance_id = $existing->id;

            // ⭐ DO NOT DELETE groups here — step 2/2b handles them
        } else {
            $record_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tk_attendance_records', $record_data);
            $attendance_id = $this->db->insert_id();
        }

        // ========== 2. Insert/Update groups + time entries + attachments ==========
        $incomingGroupIds = array();

        // ⭐ Determine the next safe group_index (avoids collisions with existing groups)
        $maxGroupIndex = -1;
        if ($is_update) {
            $row = $this->db
                ->select_max('group_index', 'max_idx')
                ->where('attendance_id', $attendance_id)
                ->get('tk_attendance_groups')
                ->row();
            if ($row && $row->max_idx !== null) {
                $maxGroupIndex = (int) $row->max_idx;
            }
        }

        foreach ($payload['groups'] as $group_index => $group) {
            $groupType = isset($group['type']) ? strtolower($group['type']) : 'inoffice';
            $groupStatus = ($groupType === 'outoffice') ? 'pending' : 'approved';

            $groupDbId = isset($group['id']) ? $group['id'] : null;
            $isDirty   = isset($group['isDirty']) ? (bool) $group['isDirty'] : true;

            // ⭐ EXISTING + NOT DIRTY → skip
            if ($groupDbId && !$isDirty) {
                $incomingGroupIds[] = $groupDbId;
                continue;
            }

            // ⭐ EXISTING + DIRTY → update in place
            if ($groupDbId) {
                $incomingGroupIds[] = $groupDbId;

                $group_data = array(
                    'type'          => isset($group['type']) ? $group['type'] : 'inoffice',
                    'status'        => $groupStatus,
                    'category'      => isset($group['category']) ? $group['category'] : NULL,
                    'time_range'    => isset($group['timeRange']) ? $group['timeRange'] : NULL,
                    'total_hours'   => $this->to_number(isset($group['totalHours']) ? $group['totalHours'] : 0),
                    'remarks'       => isset($group['remarks']) ? $group['remarks'] : NULL,
                    'location'      => isset($group['location']) ? $group['location'] : NULL,
                    'latitude'      => isset($group['latitude']) ? $group['latitude'] : NULL,
                    'longitude'     => isset($group['longitude']) ? $group['longitude'] : NULL,
                    'accuracy'      => isset($group['accuracy']) ? $group['accuracy'] : NULL,
                    'updated_location'  => isset($group['updatedLocation']) ? $group['updatedLocation'] : NULL,
                    'updated_latitude'  => isset($group['updatedLatitude']) ? $group['updatedLatitude'] : NULL,
                    'updated_longitude' => isset($group['updatedLongitude']) ? $group['updatedLongitude'] : NULL,
                    'updated_at'        => !empty($group['updatedLocation']) ? date('Y-m-d H:i:s') : NULL,
                );

                $this->db->where('id', $groupDbId)->update('tk_attendance_groups', $group_data);
                $group_id = $groupDbId;

                // Delete old time entries + attachments, re-insert below
                $this->db->where('group_id', $group_id);
                $this->db->delete('tk_attendance_time_entries');

                $this->db->where('group_id', $group_id);
                $this->db->delete('tk_attendance_attachments');
            } else {
                // ⭐ NEW GROUP
                $group_data = array(
                    'attendance_id' => $attendance_id,
                    'group_index'   => ++$maxGroupIndex,
                    'type'          => isset($group['type']) ? $group['type'] : 'inoffice',
                    'status'        => $groupStatus,
                    'category'      => isset($group['category']) ? $group['category'] : NULL,
                    'time_range'    => isset($group['timeRange']) ? $group['timeRange'] : NULL,
                    'total_hours'   => $this->to_number(isset($group['totalHours']) ? $group['totalHours'] : 0),
                    'remarks'       => isset($group['remarks']) ? $group['remarks'] : NULL,
                    'location'      => isset($group['location']) ? $group['location'] : NULL,
                    'latitude'      => isset($group['latitude']) ? $group['latitude'] : NULL,
                    'longitude'     => isset($group['longitude']) ? $group['longitude'] : NULL,
                    'accuracy'      => isset($group['accuracy']) ? $group['accuracy'] : NULL,
                );

                $this->db->insert('tk_attendance_groups', $group_data);
                $group_id = $this->db->insert_id();
                $incomingGroupIds[] = $group_id;
            }

            // Insert time entries for this group
            if (!empty($group['hours']) && !empty($payload['timeEntries'])) {
                foreach ($payload['timeEntries'] as $entry) {
                    $entryRowKey = isset($entry['rowKey']) ? $entry['rowKey'] : (string) $entry['hour'];
                    $groupRowKeys = isset($group['rowKeys']) ? $group['rowKeys'] : array_map('strval', $group['hours']);

                    if (in_array($entryRowKey, $groupRowKeys)) {
                        $entry_data = array(
                            'attendance_id' => $attendance_id,
                            'group_id'      => $group_id,
                            'hour'          => $entry['hour'],
                            'row_key'       => $entryRowKey,
                            'start_time'    => $this->to_24h($entry['startTime']),
                            'end_time'      => $this->to_24h($entry['endTime']),
                        );
                        $this->db->insert('tk_attendance_time_entries', $entry_data);
                    }
                }
            }

            // ⭐ Insert attachments ONCE (step 3 removed)
            if (!empty($group['attachments'])) {
                foreach ($group['attachments'] as $att) {
                    $att_data = array(
                        'attendance_id' => $attendance_id,
                        'group_id'      => $group_id,
                        'file_name'     => $att['name'],
                        'file_path'     => $att['path'],
                        'file_size'     => $att['size'],
                        'file_type'     => $att['type'],
                    );
                    $this->db->insert('tk_attendance_attachments', $att_data);
                }
            }
        }

        // ⭐ 2b. DELETE groups that no longer exist
        if ($is_update && !empty($incomingGroupIds)) {
            $this->db->where('attendance_id', $attendance_id);
            $this->db->where_not_in('id', $incomingGroupIds);
            $this->db->delete('tk_attendance_groups');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Database transaction failed');
        }

        return array(
            'success' => true,
            'id'      => $attendance_id,
            'message' => $is_update ? 'Attendance updated' : 'Attendance saved',
        );
    }

    public function save_attendance_timelog($payload)
    {
        $emp_id          = $payload['emp_id'];
        $attendance_date = $payload['date'];
        $punch_type      = $payload['punch_type'];
        $current_time    = $payload['current_time'];
        $hour_num        = $payload['hour_num'];

        $this->db->trans_start();

        // 1. Check existing record
        $existing = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $attendance_date)
            ->get('tk_attendance_records')
            ->row();

        $is_update = (bool) $existing;

        $record_data = array(
            'emp_id'          => $emp_id,
            'attendance_date' => $attendance_date,
            'submitted_at'    => date('Y-m-d H:i:s'),
            'in1'             => !empty($payload['dateInfo']['in1']) ? $payload['dateInfo']['in1'] : NULL,
            'out1'            => !empty($payload['dateInfo']['out1']) ? $payload['dateInfo']['out1'] : NULL,
            'in2'             => !empty($payload['dateInfo']['in2']) ? $payload['dateInfo']['in2'] : NULL,
            'out2'            => !empty($payload['dateInfo']['out2']) ? $payload['dateInfo']['out2'] : NULL,
            'remarks'         => isset($payload['remarks']) ? $payload['remarks'] : 'Biometric Web Punch',
            'status'          => 'approved',
            'updated_at'      => date('Y-m-d H:i:s')
        );

        if ($is_update) {
            $attendance_id = $existing->id;
            $this->db->where('id', $attendance_id)->update('tk_attendance_records', $record_data);
        } else {
            $record_data['created_at']   = date('Y-m-d H:i:s');
            $record_data['hours_worked'] = 0;
            $this->db->insert('tk_attendance_records', $record_data);
            $attendance_id = $this->db->insert_id();
        }

        // 2. Manage groups and time entries
        $display_time = date('h:i A', strtotime($current_time));

        if ($punch_type === 'in') {
            // Count today's groups to index sequentially (0, 1, 2...)
            $group_count = $this->db
                ->where('attendance_id', $attendance_id)
                ->count_all_results('tk_attendance_groups');

            $group_data = array(
                'attendance_id' => $attendance_id,
                'group_index'   => $group_count,
                'type'          => 'regular',
                'category'      => '',
                'time_range'    => $display_time, // e.g. "08:35 AM"
                'total_hours'   => 0,
                'remarks'       => ($hour_num < 12) ? 'Morning In' : 'Afternoon In',
                'location'      => $payload['location'],
                'latitude'      => $payload['latitude'],
                'longitude'     => $payload['longitude'],
                'accuracy'      => $payload['accuracy']
            );
            $this->db->insert('tk_attendance_groups', $group_data);
            $group_id = $this->db->insert_id();

            // Insert matching open time entry with initial '00:00:00'
            $entry_data = array(
                'attendance_id' => $attendance_id,
                'group_id'      => $group_id,
                'hour'          => $hour_num,
                'start_time'    => $current_time,
                'end_time'      => '00:00:00'
            );
            $this->db->insert('tk_attendance_time_entries', $entry_data);

        } else {
            // PUNCH OUT: Find open group
            $last_group = $this->db
                ->where('attendance_id', $attendance_id)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('tk_attendance_groups')
                ->row();

            if ($last_group) {
                $group_id = $last_group->id;

                // Find matching entry where end_time is still '00:00:00' or NULL
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
                    // Update open entry with punch-out time
                    $this->db->where('id', $last_entry->id)->update('tk_attendance_time_entries', [
                        'end_time' => $current_time
                    ]);

                    // Calculate elapsed hours
                    $start_sec = strtotime($last_entry->start_time);
                    $end_sec   = strtotime($current_time);
                    if ($end_sec > $start_sec) {
                        $group_hours = round(($end_sec - $start_sec) / 3600, 2);
                    }
                }

                // Update group range (e.g. "08:35 AM to 05:30 PM") and total_hours
                $start_display = explode(' to ', $last_group->time_range)[0];
                $updated_range = $start_display . ' to ' . $display_time;

                $this->db->where('id', $group_id)->update('tk_attendance_groups', [
                    'time_range'  => $updated_range,
                    'total_hours' => $group_hours,
                    'remarks'     => ($hour_num < 12) ? 'Morning Out' : 'End Shift'
                ]);
            }
        }

        // 3. Recalculate total hours_worked across all completed entries
        $total_worked = 0;
        $all_entries = $this->db
            ->where('attendance_id', $attendance_id)
            ->where('end_time !=', '00:00:00')
            ->where('end_time IS NOT NULL', null, false)
            ->where('end_time !=', '')
            ->get('tk_attendance_time_entries')
            ->result();

        foreach ($all_entries as $entry) {
            $s = strtotime($entry->start_time);
            $e = strtotime($entry->end_time);
            if ($e > $s) {
                $total_worked += ($e - $s);
            }
        }
        $final_hours = round($total_worked / 3600, 2);

        $this->db->where('id', $attendance_id)->update('tk_attendance_records', [
            'hours_worked' => $final_hours
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Database transaction failed'];
        }

        return [
            'success' => true,
            'id'      => $attendance_id,
            'message' => $is_update ? 'Attendance updated successfully' : 'Attendance logged successfully'
        ];
    }

    /**
     * Get attendance record for a specific date
     */
    public function get_attendance($emp_id, $date) {
        $record = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $date)
            ->get('tk_attendance_records')
            ->row_array();

        if (!$record) return null;

        // Get groups
        $groups = $this->db
            ->where('attendance_id', $record['id'])
            ->order_by('group_index', 'ASC')
            ->get('tk_attendance_groups')
            ->result_array();

        // Get time entries
        $entries = $this->db
            ->where('attendance_id', $record['id'])
            ->order_by('hour', 'ASC')
            ->order_by('start_time', 'ASC')
            ->get('tk_attendance_time_entries')
            ->result_array();

        // Get registered location
        $location = $this->db->select('location')
            ->from('masterlist_2ndgen')
            ->where('emp_id', $emp_id)
            ->get()
            ->result_array();

        // Get attachments for each group
        foreach ($groups as &$group) {
            $group['attachments'] = $this->db
                ->where('group_id', $group['id'])
                ->get('tk_attendance_attachments')
                ->result_array();
        }

        $record['location'] = $location;
        $record['groups'] = $groups;
        $record['time_entries'] = $entries;

        return $record;
    }

    /**
     * Get all attendance events for calendar
     */
    public function get_events($emp_id) {
        $records = $this->db
            ->where('emp_id', $emp_id)
            ->get('tk_attendance_records')
            ->result_array();

        $events = array();
        foreach ($records as $record) {
            $groups = $this->db
                ->where('attendance_id', $record['id'])
                //->where('type !=', 'regular') 
                ->order_by('group_index', 'ASC')
                ->get('tk_attendance_groups')
                ->result_array();

            foreach ($groups as $group) {
                $events[] = array(
                    'date'       => $record['attendance_date'],
                    'type'       => $group['type'],
                    'category'   => $group['category'],
                    'timeRange'  => $group['time_range'],
                    'totalHours' => $group['total_hours'],
                    'groupIndex' => $group['group_index'],
                    'status'     => isset($group['status']) ? $group['status'] : 'approved', // ⭐ NEW
                );
            }
        }

        return $events;
    }

    /**
     * Get all leave requests for calendar events
     */
    public function get_leaves($emp_id) {
        $leaves = $this->db
            ->where('emp_id', $emp_id)
            ->order_by('startDate', 'ASC')
            ->get('tk_leave_requests')
            ->result_array();

        if (empty($leaves)) return array();

        $leaveIds = array_column($leaves, 'leaveId');

        // ── Fetch dates ──
        $datesRows = $this->db
            ->where_in('leaveId', $leaveIds)
            ->order_by('date', 'ASC')
            ->get('tk_leave_request_dates')
            ->result_array();

        $datesByLeave = array();
        foreach ($datesRows as $row) {
            $datesByLeave[$row['leaveId']][] = $row;
        }

        // ── Which leaves have DETAILS (hourly)? ──
        $detailRows = $this->db
            ->select('leaveId')
            ->where_in('leaveId', $leaveIds)
            ->get('tk_leave_request_details')
            ->result_array();

        $leavesWithDetails = array();
        foreach ($detailRows as $row) {
            $leavesWithDetails[$row['leaveId']] = true;
        }

        // ── Approved hours per GROUP (hourly leaves only) ──
        $approvedRows = $this->db
            ->select('leaveId, groupId, MAX(group_hours) AS group_hours')
            ->where_in('leaveId', $leaveIds)
            ->where('status', 'approved')
            ->group_by(array('leaveId', 'groupId'))
            ->get('tk_leave_request_details')
            ->result_array();

        $approvedGroupHoursByLeave = array();
        foreach ($approvedRows as $row) {
            $lid = $row['leaveId'];
            if (!isset($approvedGroupHoursByLeave[$lid])) $approvedGroupHoursByLeave[$lid] = 0;
            $approvedGroupHoursByLeave[$lid] += floatval($row['group_hours']);
        }

        // ⭐ ALL group hours regardless of status (for the title display)
        $allRows = $this->db
            ->select('leaveId, groupId, MAX(group_hours) AS group_hours')
            ->where_in('leaveId', $leaveIds)
            ->where_not_in('status', array('cancelled', 'rejected'))   // skip dead ones
            ->group_by(array('leaveId', 'groupId'))
            ->get('tk_leave_request_details')
            ->result_array();

        $allGroupHoursByLeave = array();
        foreach ($allRows as $row) {
            $lid = $row['leaveId'];
            if (!isset($allGroupHoursByLeave[$lid])) $allGroupHoursByLeave[$lid] = 0;
            $allGroupHoursByLeave[$lid] += floatval($row['group_hours']);
        }

        // ⭐ Any PENDING groups (for hourly leaves)?
        // Treat anything not finalized as pending
        $pendingRows = $this->db
            ->select('leaveId')
            ->where_in('leaveId', $leaveIds)
            ->where_not_in('status', array('approved', 'cancelled', 'disapproved', 'rejected'))
            ->group_by('leaveId')
            ->get('tk_leave_request_details')
            ->result_array();

        $leavesWithPending = array();
        foreach ($pendingRows as $row) {
            $leavesWithPending[$row['leaveId']] = true;
        }

        $typeMap = array(
            1 => 'sick', 2 => 'medical', 3 => 'personal', 4 => 'valid', 5 => 'vacation'
        );

        $events = array();

        foreach ($leaves as $leave) {
            $type = !empty($leave['code'])
                ? $leave['code']
                : (isset($typeMap[$leave['typeId']]) ? $typeMap[$leave['typeId']] : 'leave');

            $dates = isset($datesByLeave[$leave['leaveId']]) ? $datesByLeave[$leave['leaveId']] : array();

            $hasDetails = isset($leavesWithDetails[$leave['leaveId']]);
            $isFixed    = (int) $leave['isFixed'] === 1;
            $isWholeDay = $isFixed && !$hasDetails;

            // ⭐ Pending detection
            $parentIsPending = (strtolower($leave['status']) === 'pending');

            if ($isFixed) {
                $hasPending = $parentIsPending;
            } else {
                $hasPending = $parentIsPending
                    || isset($leavesWithPending[$leave['leaveId']]);
            }

            // ⭐ Hours source (approved only — used for totals)
            if ($isFixed) {
                $approvedHours = (strtolower($leave['status']) === 'approved')
                    ? floatval($leave['total_hours'])
                    : 0;
            } else {
                $approvedHours = isset($approvedGroupHoursByLeave[$leave['leaveId']])
                    ? $approvedGroupHoursByLeave[$leave['leaveId']]
                    : 0;
            }

            // ⭐ Display hours — include pending so the title can show them.
            //    This value is used ONLY for the event title; totals still use $approvedHours.
            if ($isFixed) {
                $displayHours = floatval($leave['total_hours']);   // full requested hours
            } else {
                // Sum ALL group hours, regardless of status
                $displayHours = 0;
                if (isset($allGroupHoursByLeave[$leave['leaveId']])) {
                    $displayHours = $allGroupHoursByLeave[$leave['leaveId']];
                }
            }

            // Fallback: no dates
            if (empty($dates)) {
                $event = $this->build_leave_event($leave, $type, array(
                    'date'    => $leave['startDate'],
                    'payment' => $leave['payment'],
                ), $approvedHours);
                $event['isWholeDay']    = $isWholeDay;
                $event['approvedHours'] = $approvedHours;
                $event['displayHours']  = $displayHours;
                $event['hasPending']    = $hasPending;
                $events[] = $event;
                continue;
            }

            $dayCount = count($dates);
            $hoursPerDay = $dayCount > 0 ? round($approvedHours / $dayCount, 2) : 0;
            $displayPerDay = $dayCount > 0 ? round($displayHours / $dayCount, 2) : 0;

            foreach ($dates as $d) {
                $d['status'] = null;

                $event = $this->build_leave_event($leave, $type, $d, $hoursPerDay);
                $event['isWholeDay']    = $isWholeDay;
                $event['approvedHours'] = $approvedHours;
                $event['displayHours']     = $displayPerDay;
                $event['hasPending']    = $hasPending;
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Get all active holidays for calendar events
     */
    public function get_holidays($emp_id = null) {
        $rows = $this->db
            ->where('status', 1)
            ->order_by('date', 'ASC')
            ->get('tk_holidays')
            ->result_array();

        $holidays = array();
        foreach ($rows as $row) {
            // Normalize "code" to lowercase for CSS class usage
            $code = strtolower(trim($row['type']));
            
            // Map common variants → canonical types
            $type = 'regular';
            if (strpos($code, 'special') !== false) $type = 'special';
            else if (strpos($code, 'local') !== false) $type = 'local';
            else if (strpos($code, 'legal') !== false) $type = 'legal';

            // ⭐ If date_change is a real date (not 0000-00-00), use it as the effective date
            $effectiveDate = $row['date'];
            if (!empty($row['date_change']) && $row['date_change'] !== '0000-00-00') {
                $effectiveDate = $row['date_change'];
            }

            $holidays[] = array(
                'id'   => $row['id'],
                'name' => $row['holiday'],
                'date' => $effectiveDate,        // use the moved date if present
                'type' => $type,
                'raw_type' => $row['type'],      // keep original for reference
                'original_date' => $row['date'], // what the calendar says vs. actual
            );
        }

        return $holidays;
    }

    /**
     * Get the employee's custom day-off changes.
     * Each change affects ONE week: old_date becomes working, new_date becomes off.
     */
    public function get_dayoffs($emp_id) {
        $rows = $this->db
            ->where('emp_id', $emp_id)
            ->where_in('status', array('pending', 'approved'))   // ⭐ both
            ->order_by('created', 'ASC')
            ->get('tk_change_dayoff')
            ->result_array();

        $dayoffs = array();
        foreach ($rows as $row) {
            $newDate = $row['new_dayoff'];
            if (empty($newDate) || $newDate === '0000-00-00') continue;

            $ts = strtotime($newDate);
            if (!$ts) continue;

            $dow = (int) date('w', $ts);
            $sundayTs = $ts - ($dow * 86400);
            $saturdayTs = $sundayTs + 6 * 86400;
            $derivedSunday = date('Y-m-d', $sundayTs);

            $oldDate = $row['old_dayoff'];
            if (empty($oldDate) || $oldDate === '0000-00-00') {
                $oldDate = $derivedSunday;
            }

            $dayoffs[] = array(
                'id'         => $row['tkcdoId'],
                'old_date'   => $oldDate,
                'new_date'   => $newDate,
                'status'     => $row['status'],     // ⭐ pending | approved
                'week_start' => $derivedSunday,
                'week_end'   => date('Y-m-d', $saturdayTs),
                'remarks'    => $row['remarks'],
            );
        }

        return $dayoffs;
    }

    public function has_attendance_on($emp_id, $date) {
        $count = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $date)
            ->count_all_results('tk_attendance_records');

        return $count > 0;
    }

    /**
     * Batch check — returns dates that have attendance (faster for a whole week).
     */
    public function get_attendance_dates($emp_id, $fromDate, $toDate) {
        $rows = $this->db
            ->select('attendance_date')
            ->where('emp_id', $emp_id)
            ->where('attendance_date >=', $fromDate)
            ->where('attendance_date <=', $toDate)
            ->get('tk_attendance_records')
            ->result_array();

        $dates = array();
        foreach ($rows as $r) {
            $dates[] = $r['attendance_date'];
        }
        return $dates;
    }

    /**
     * Copy an open-entry group row into tk_attendance_groups_logs
     * BEFORE it gets deleted. Used by cancel_open_entry().
     *
     * The log table has no attendance_id, so we pull emp_id from the
     * parent attendance record and store it denormalized.
     */
    public function log_open_entry_before_delete($groupId) {
        // Fetch the group + parent record's emp_id
        $row = $this->db
            ->select('g.*, r.emp_id')
            ->from('tk_attendance_groups g')
            ->join('tk_attendance_records r', 'r.id = g.attendance_id', 'left')
            ->where('g.id', $groupId)
            ->get()
            ->row_array();

        if (!$row) return false;

        $log_data = array(
            'emp_id'             => isset($row['emp_id']) ? $row['emp_id'] : null,
            'group_index'        => $row['group_index'],
            'type'               => $row['type'],
            'status'             => $row['status'],
            'approved_by'        => $row['approved_by'],
            'approved_at'        => $row['approved_at'],
            'category'           => $row['category'],
            'time_range'         => $row['time_range'],
            'total_hours'        => $row['total_hours'],
            'remarks'            => $row['remarks'],
            'location'           => $row['location'],
            'latitude'           => $row['latitude'],
            'longitude'          => $row['longitude'],
            'accuracy'           => $row['accuracy'],
            'updated_location'   => $row['updated_location'],
            'updated_latitude'   => $row['updated_latitude'],
            'updated_longitude'  => $row['updated_longitude'],
            'updated_at'         => $row['updated_at'],
            'created_at'         => $row['created_at'],
        );

        return $this->db->insert('tk_attendance_groups_logs', $log_data);
    }

    public function save_dayoff_change($payload) {
        $emp_id    = $payload['emp_id'];
        $newDate   = $payload['new_date'];
        $passedOld = $payload['old_date'];
        $remarks   = isset($payload['remarks']) ? $payload['remarks'] : 'Day-off change';

        if (empty($newDate)) {
            return array('success' => false, 'message' => 'New date is required.');
        }

        $ts = strtotime($newDate);
        if (!$ts) return array('success' => false, 'message' => 'Invalid new date.');

        // Week (Sunday → Saturday)
        $dow = (int) date('w', $ts);
        $sundayTs = $ts - ($dow * 86400);
        $saturdayTs = $sundayTs + 6 * 86400;
        $weekStart = date('Y-m-d', $sundayTs);
        $weekEnd   = date('Y-m-d', $saturdayTs);

        // ⭐ Look for a PENDING request for this week — the only thing we can update
        $existingPending = $this->db
            ->where('emp_id', $emp_id)
            ->where('status', 'pending')
            ->group_start()
                ->where('new_dayoff >=', $weekStart)
                ->where('new_dayoff <=', $weekEnd)
            ->group_end()
            ->order_by('created', 'DESC')
            ->limit(1)
            ->get('tk_change_dayoff')
            ->row();

        // ⭐ Latest APPROVED — determines the current effective day off
        $latestApproved = $this->db
            ->where('emp_id', $emp_id)
            ->where('status', 'approved')
            ->group_start()
                ->where('new_dayoff >=', $weekStart)
                ->where('new_dayoff <=', $weekEnd)
            ->group_end()
            ->order_by('created', 'DESC')
            ->limit(1)
            ->get('tk_change_dayoff')
            ->row();

        // old_date = approved's new_dayoff (current off), else the frontend default
        $oldDate = $latestApproved ? $latestApproved->new_dayoff : $passedOld;

        if ($oldDate === $newDate) {
            return array('success' => false, 'message' => 'New day off must be different from the current one.');
        }

        // Block if attendance exists on either date
        foreach (array($oldDate, $newDate) as $checkDate) {
            if ($this->has_attendance_on($emp_id, $checkDate)) {
                return array(
                    'success' => false,
                    'message' => "You already filed attendance for " . date('M d, Y', strtotime($checkDate)) . "."
                );
            }
        }

        $this->db->trans_start();

        if ($existingPending) {
            // ⭐ UPDATE — only pending can be edited
            $this->db->where('tkcdoId', $existingPending->tkcdoId)
                ->update('tk_change_dayoff', array(
                    'old_dayoff' => $oldDate,
                    'new_dayoff' => $newDate,
                    'remarks'    => $remarks,
                ));

            $newId = $existingPending->tkcdoId;

            // Update history row in place — request_status stays 'pending'
            $this->upsert_dayoff_history(array(
                'tkcdoId'        => $newId,
                'emp_id'         => $emp_id,
                'old_do'         => $oldDate,
                'new_do'         => $newDate,
                'request_status' => 'pending',
                'remarks'        => $remarks,
                'status'         => 'pending',
            ));

        } else {
            // ⭐ INSERT — no pending exists (either first request, or only approved)
            $this->db->insert('tk_change_dayoff', array(
                'emp_id'     => $emp_id,
                'old_dayoff' => $oldDate,
                'new_dayoff' => $newDate,
                'remarks'    => $remarks,
                'status'     => 'pending',
                'created'    => date('Y-m-d H:i:s'),
            ));

            $newId = $this->db->insert_id();

            $this->upsert_dayoff_history(array(
                'tkcdoId'        => $newId,
                'emp_id'         => $emp_id,
                'old_do'         => $oldDate,
                'new_do'         => $newDate,
                'request_status' => 'pending',
                'remarks'        => $remarks,
                'status'         => 'pending',
            ));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Database transaction failed.');
        }

        return array(
            'success' => true,
            'id'      => $newId,
            'message' => $existingPending ? 'Day-off request updated.' : 'Day-off change requested.',
        );
    }

    public function cancel_dayoff_change($emp_id, $id) {
        $row = $this->db
            ->where('tkcdoId', $id)
            ->where('emp_id', $emp_id)
            ->get('tk_change_dayoff')
            ->row();

        if (!$row) {
            return array('success' => false, 'message' => 'Request not found.');
        }

        if ($row->status !== 'pending') {
            return array('success' => false, 'message' => 'Only pending requests can be cancelled.');
        }

        $this->db->trans_start();

        // ⭐ UPDATE history row — mark as cancelled
        $this->db->where('tkcdoId', $id)->update('tk_change_dayoff_request', array(
            'status' => 'cancelled',
            'remarks' => $row->remarks ?: 'Cancelled by user',
        ));

        // Delete from active table
        $this->db->where('tkcdoId', $id);
        $this->db->delete('tk_change_dayoff');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Database transaction failed.');
        }

        return array(
            'success' => true,
            'message' => 'Day-off request cancelled.',
        );
    }

    /**
     * Get the blocked hours for a specific date (from any active leave).
     * Returns an array of hour integers [8, 9, 10, 11] that are covered by leaves.
     */
    public function get_leave_blocked_hours($emp_id, $date) {
        // 1. Find leaves covering this date
        $leaves = $this->db
            ->select('lr.leaveId, lr.status, lr.isFixed')
            ->from('tk_leave_requests lr')
            ->join('tk_leave_request_dates lrd', 'lrd.leaveId = lr.leaveId')
            ->where('lr.emp_id', $emp_id)
            ->where('lrd.date', $date)
            ->where_in('lr.status', array('approved', 'pending','disapproved'))
            ->get()
            ->result_array();

        if (empty($leaves)) return array();

        $leaveIds = array_column($leaves, 'leaveId');

        // 2. Fetch details ONCE
        $detailRows = $this->db
            ->where('status !=', 'cancelled')
            ->where_in('leaveId', $leaveIds)
            ->get('tk_leave_request_details')
            ->result_array();

        // Group details by leaveId
        $detailsByLeave = array();
        foreach ($detailRows as $row) {
            $detailsByLeave[$row['leaveId']][] = $row;
        }

        // 3. Determine whole-day leaves (isFixed == 1 AND no details)
        $wholeDayLeaveIds = array();
        foreach ($leaves as $leave) {
            $hasDetails = isset($detailsByLeave[$leave['leaveId']]);
            $isFixed = (int) $leave['isFixed'] === 1;

            if ($isFixed && !$hasDetails) {
                $wholeDayLeaveIds[] = $leave['leaveId'];
            }
        }

        // 4. If ANY leave is whole-day → block all 11 hours
        if (!empty($wholeDayLeaveIds)) {
            $fullDay = array();
            for ($h = 8; $h <= 18; $h++) {
                $fullDay[$h] = array(array(
                    'start'   => sprintf('%02d:00:00', $h),
                    'end'     => $h === 18 ? '19:00:00' : sprintf('%02d:59:00', $h),
                    'groupId' => null,
                    'code'    => 'leave',
                    'timeRow' => (string) $h,
                ));
            }
            return $fullDay;
        }

        // ⭐ 5. If NO details exist at all (and no whole-day), nothing to block
        if (empty($detailRows)) {
            return array();
        }

        // 6. Process hourly leaves
        $timeRows = $this->db
            ->where_in('leaveId', $leaveIds)
            ->get('tk_request_time_table')
            ->result_array();

        $timeByLeave = array();
        foreach ($timeRows as $row) {
            $timeByLeave[$row['leaveId']] = $row;
        }

        $splitRows = $this->db
            ->where_in('leaveId', $leaveIds)
            ->get('tk_leave_splitrows')
            ->result_array();

        $splitByLeaveRow = array();
        foreach ($splitRows as $row) {
            $splitByLeaveRow[$row['leaveId'] . '|' . $row['time_row']] = $row;
        }

        $blockedHours = array();

        foreach ($detailRows as $det) {
            $leaveId = $det['leaveId'];
            $timeRow = $det['time_row'];

            if (!preg_match('/^(\d+)(?:s(\d+))?$/', $timeRow, $m)) continue;

            $hour = (int) $m[1];
            $isSplit = isset($m[2]);
            $range = null;

            if ($isSplit) {
                $key = $leaveId . '|' . $timeRow;
                if (isset($splitByLeaveRow[$key])) {
                    $range = array(
                        'start' => $splitByLeaveRow[$key]['t1'],
                        'end'   => $splitByLeaveRow[$key]['t2'],
                    );
                }
            } else {
                if (isset($timeByLeave[$leaveId])) {
                    $tr = $timeByLeave[$leaveId];
                    $startKey = "time_{$hour}_1";
                    $endKey   = "time_{$hour}_2";
                    if (!empty($tr[$startKey]) && !empty($tr[$endKey])) {
                        $range = array(
                            'start' => $tr[$startKey],
                            'end'   => $tr[$endKey],
                        );
                    }
                }
            }

            if ($range) {
                if (!isset($blockedHours[$hour])) $blockedHours[$hour] = array();
                $blockedHours[$hour][] = array(
                    'start'   => $range['start'],
                    'end'     => $range['end'],
                    'groupId' => $det['groupId'],
                    'code'    => $det['code'],
                    'status'  => isset($det['status']) ? $det['status'] : null,   // ⭐ NEW
                    'timeRow' => $timeRow,
                );
            }
        }

        return $blockedHours;
    }

    // ========== Helpers ==========

    /**
     * Safely get value from nested array (PHP 5 compatible)
     */
    private function get_val($array, $key1, $key2, $default = null) {
        if (isset($array[$key1]) && isset($array[$key1][$key2])) {
            return $array[$key1][$key2];
        }
        return $default;
    }

    /**
     * Convert "8:00 AM" → "08:00:00" for TIME column
     * ⭐ If the minute is exactly :59, store as XX:59:59 so it counts as end-of-hour
     */
    private function to_24h($time12h) {
        if (!$time12h) return null;
        
        $ts = null;
        
        // Handle both "8:00 AM" and "08:00"
        if (strpos($time12h, 'AM') !== false || strpos($time12h, 'PM') !== false) {
            $ts = strtotime($time12h);
        } else {
            // Already 24h format — normalize to :00 seconds first
            $normalized = strlen($time12h) === 5 ? $time12h . ':00' : $time12h;
            $ts = strtotime($normalized);
        }
        
        if (!$ts) return null;
        
        $h = (int) date('G', $ts);
        $i = (int) date('i', $ts);
        
        // ⭐ Minute is exactly :59 → bump to :59:59 (end-of-hour marker)
        if ($i === 59) {
            return sprintf('%02d:59:59', $h);
        }
        
        return date('H:i:s', $ts);
    }

    /**
     * Return null for empty/invalid time strings
     */
    private function to_nullable($val) {
        if ($val === null || $val === '-' || $val === '') return null;
        return $this->to_24h($val);
    }

    /**
     * Return 0 for empty numbers
     */
    private function to_number($val) {
        if ($val === null || $val === '-' || $val === '') return 0;
        return floatval($val);
    }

    /**
     * Insert or update a history row for a day-off request.
     * Keyed by tkcdoId — one row per request.
     */
    private function upsert_dayoff_history($data) {
        $tkcdoId = isset($data['tkcdoId']) ? $data['tkcdoId'] : null;
        if (!$tkcdoId) return;

        $existing = $this->db
            ->where('tkcdoId', $tkcdoId)
            ->get('tk_change_dayoff_request')
            ->row();

        $rowData = array(
            'tkcdoId'          => $tkcdoId,
            'emp_id'           => $data['emp_id'],
            'old_do'           => $data['old_do'],
            'new_do'           => $data['new_do'],
            'status'           => $data['request_status'],
            'remarks'          => isset($data['remarks']) ? $data['remarks'] : '',
            'status'           => isset($data['status']) ? $data['status'] : 'pending',
            'approved_by'      => isset($data['approved_by']) ? $data['approved_by'] : '',   // ⭐ '' not null
        );

        // Only set request_datetime on the first insert
        if (!$existing) {
            //$rowData['request_datetime']  = date('Y-m-d H:i:s');
            $rowData['approved_datetime'] = '0000-00-00 00:00:00';
        }

        // If approving, stamp the approver
        if (!empty($data['approved_by'])) {
            $rowData['approved_datetime'] = date('Y-m-d H:i:s');
        }

        if ($existing) {
            $this->db->where('tkcdoId', $tkcdoId)->update('tk_change_dayoff_request', $rowData);
        } else {
            $this->db->insert('tk_change_dayoff_request', $rowData);
        }
    }

    /**
     * Build a single leave event for one day.
     */
    private function build_leave_event($leave, $type, $dateRow, $hoursForThisDay) {
        return array(
            'leaveId'    => $leave['leaveId'],
            'emp_id'     => $leave['emp_id'],
            'isFixed'    => $leave['isFixed'],
            'type'       => $type,                                   // ⭐ raw code
            'typeLabel'  => $this->leave_display_name($type),        // ⭐ friendly name
            'typeId'     => $leave['typeId'],
            'date'       => $dateRow['date'],
            'startDate'  => $dateRow['date'],
            'endDate'    => $dateRow['date'],
            'totalHours' => $hoursForThisDay,
            'approvedHours' => $hoursForThisDay,
            'totalDays'  => 1,
            'dayIndex'   => isset($dateRow['dayIndex']) ? $dateRow['dayIndex'] : 1,
            'payment'    => $leave['payment'],
            'reason'     => $leave['reason'],
            'status'     => $leave['status'],
            'approvedBy' => $leave['approvedBy'],
            'created_at' => $leave['created_at'],
        );
    }

    /**
     * Map leave code → friendly display name.
     */
    private function leave_display_name($code) {
        $map = array(
            'sick'     => 'Medical/Sick Leave',
            'vacation' => 'Vacation Leave',
            'medical'  => 'Medical Assistance Leave',
            'valid'    => 'Government-Mandated Leave Benefits',
            'personal' => 'Approved Personal Leave',
        );

        $key = strtolower(trim($code));
        return isset($map[$key]) ? $map[$key] : ucwords(str_replace(array('-', '_'), ' ', $code));
    }

    public function get_registered_location($emp_id)
    {
        return $this->db->select('location')
            ->from('masterlist_2ndgen')
            ->where('emp_id', $emp_id)
            ->get()
            ->row_array();
    }
}