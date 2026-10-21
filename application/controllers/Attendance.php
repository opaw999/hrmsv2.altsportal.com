<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Manila');

class Attendance extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Tk_attendance_model');
        $this->load->helper(array('url', 'file'));
        
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

    // public function test_upload_view() {
    //     $this->load->view('test_upload');
    // }

    /**
     * GET /api/attendance/load/{date}
     */
    public function load($date = null) {
        if (!$date) {
            echo json_encode(array('success' => false, 'message' => 'Date is required'));
            return;
        }

        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        $record = $this->Tk_attendance_model->get_attendance($emp_id, $date);

        echo json_encode(array(
            'success' => true,
            'data'    => $record,
        ));
    }

    /**
     * GET /api/attendance/events
     */
    public function events() {
        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        $events = $this->Tk_attendance_model->get_events($emp_id);

        echo json_encode(array(
            'success' => true,
            'data'    => $events,
        ));
    }

    /**
     * POST /api/attendance/save
     */
    public function save() {
        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        // Read JSON body
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!$payload) {
            echo json_encode(array('success' => false, 'message' => 'Invalid JSON payload'));
            return;
        }

        // Inject emp_id from session
        $payload['emp_id'] = $emp_id;

        // Validate required fields
        if (empty($payload['date']) || empty($payload['timeEntries'])) {
            echo json_encode(array('success' => false, 'message' => 'Missing required fields'));
            return;
        }

        $result = $this->Tk_attendance_model->save_attendance($payload);

        echo json_encode($result);
    }

    public function saveTimeLog()
    {
        // Read raw JSON stream[cite: 2]
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

        // Extract employee, punch type, and date[cite: 2]
        $emp_id     = !empty($payload['emp_id']) ? $payload['emp_id'] : ($this->session->userdata('empid') ?: $this->session->userdata('user_id'));
        $punch_type = isset($payload['punch_type']) ? strtolower($payload['punch_type']) : 'in';
        $date_str   = !empty($payload['date']) ? $payload['date'] : date('Y-m-d');

        // Extract time & coordinates from groups[0][cite: 2]
        $group0     = !empty($payload['groups'][0]) ? $payload['groups'][0] : [];
        $time_str   = !empty($group0['timeRange']) ? $group0['timeRange'] : date('H:i:s');
        $hour_num   = (int) date('G', strtotime($time_str)); // Numeric hour (e.g. 8 for 08:35:00, 14 for 14:00:00)

        $location   = isset($group0['location']) ? $group0['location'] : (isset($payload['location']) ? $payload['location'] : null);
        $latitude   = isset($group0['latitude']) ? $group0['latitude'] : (isset($payload['latitude']) ? $payload['latitude'] : null);
        $longitude  = isset($group0['longitude']) ? $group0['longitude'] : (isset($payload['longitude']) ? $payload['longitude'] : null);
        $accuracy   = isset($group0['accuracy']) ? $group0['accuracy'] : (isset($payload['accuracy']) ? $payload['accuracy'] : 0);

        // Fetch existing daily record[cite: 2]
        $existing = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $date_str)
            ->get('tk_attendance_records')
            ->row();

        $in1  = $existing ? $existing->in1 : null; //[cite: 2]
        $out1 = $existing ? $existing->out1 : null; //[cite: 2]
        $in2  = $existing ? $existing->in2 : null; //[cite: 2]
        $out2 = $existing ? $existing->out2 : null; //[cite: 2]

        // Shift routing: Morning (<12:00 PM) routes to slot 1; Afternoon/Evening routes to slot 2
        $is_morning = ($hour_num < 12);

        if ($punch_type === 'in') {
            if ($is_morning) {
                $in1 = empty($in1) ? $time_str : $in1;
            } else {
                $in2 = empty($in2) ? $time_str : $in2;
            }
        } else {
            if ($is_morning) {
                $out1 = $time_str;
            } else {
                $out2 = $time_str;
            }
        }

        $modelPayload = array(
            'emp_id'          => $emp_id,
            'date'            => $date_str,
            'punch_type'      => $punch_type,
            'current_time'    => $time_str,
            'hour_num'        => $hour_num,
            'remarks'         => !empty($payload['remarks']) ? $payload['remarks'] : 'Biometric Web Punch',
            'dateInfo'        => array(
                'in1'         => $in1,
                'out1'        => $out1,
                'in2'         => $in2,
                'out2'        => $out2
            ),
            'location'        => $location,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'accuracy'        => $accuracy
        );

        $this->load->model('Tk_attendance_model');
        $result = $this->Tk_attendance_model->save_attendance_timelog($modelPayload);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    // display time in/out
    public function get_today_logs()
    {
        $emp_id = $this->session->userdata('empid') ?: $this->session->userdata('user_id');
        $date_str = $this->input->get('date') ?: date('Y-m-d');

        // Fetch the daily attendance record
        $record = $this->db
            ->where('emp_id', $emp_id)
            ->where('attendance_date', $date_str)
            ->get('tk_attendance_records')
            ->row();

        if (!$record) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => true,
                    'logs'   => [],
                    'hours_worked' => 0
                ]));
        }

        // Join time entries with group details (location, coordinates, remarks)
        $entries = $this->db
            ->select('e.*, g.location, g.latitude, g.longitude, g.remarks as group_remarks')
            ->from('tk_attendance_time_entries e')
            ->join('tk_attendance_groups g', 'g.id = e.group_id', 'left')
            ->where('e.attendance_id', $record->id)
            ->order_by('e.id', 'ASC')
            ->get()
            ->result_array();

        $logs = [];
        foreach ($entries as $row) {
            $loc = !empty($row['location']) ? $row['location'] : 'Island City Mall, Tagbilaran, Bohol';
            $lat = $row['latitude'];
            $lng = $row['longitude'];

            // 1. Time In Punch
            if (!empty($row['start_time']) && $row['start_time'] !== '00:00:00') {
                $in_hour = (int) date('G', strtotime($row['start_time']));
                $period = ($in_hour < 12) ? 'Morning In' : (($in_hour < 18) ? 'Afternoon In' : 'Evening In');

                $logs[] = [
                    'type'        => 'in',
                    'actionTitle' => 'Time In',
                    'shiftPeriod' => $period,
                    'timeStr'     => date('h:i:s A', strtotime($row['start_time'])),
                    'dateStr'     => date('M d, Y', strtotime($record->attendance_date)),
                    'locationVal' => $loc,
                    'lat'         => $lat,
                    'lng'         => $lng
                ];
            }

            // 2. Time Out Punch
            if (!empty($row['end_time']) && $row['end_time'] !== '00:00:00') {
                $out_hour = (int) date('G', strtotime($row['end_time']));
                $period = ($out_hour < 12) ? 'Morning Out' : (($out_hour < 18) ? 'Afternoon Out' : 'End Shift');

                $logs[] = [
                    'type'        => 'out',
                    'actionTitle' => 'Time Out',
                    'shiftPeriod' => $period,
                    'timeStr'     => date('h:i:s A', strtotime($row['end_time'])),
                    'dateStr'     => date('M d, Y', strtotime($record->attendance_date)),
                    'locationVal' => $loc,
                    'lat'         => $lat,
                    'lng'         => $lng
                ];
            }
        }

        // Sort newest punches on top
        $logs = array_reverse($logs);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'       => true,
                'logs'         => $logs,
                'hours_worked' => $record->hours_worked
            ]));
    }

    /**
     * POST /api/attendance/upload
     */
    public function upload() {
        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        if (empty($_FILES['files'])) {
            echo json_encode(array('success' => false, 'message' => 'No files uploaded'));
            return;
        }

        // Upload config
        $upload_path = FCPATH . 'uploads/attendance/' . date('Y/m/');
        
        // ========== DIAGNOSTIC: Check folder status ==========
        if (!is_dir($upload_path)) {
            // Try to create parent first
            $parent = FCPATH . 'uploads/attendance/' . date('Y') . '/';
            
            if (!is_dir($parent)) {
                if (!@mkdir($parent, 0775, true)) {
                    echo json_encode(array(
                        'success' => false,
                        'message' => 'Cannot create parent folder',
                        'path' => $parent,
                        'parent_writable' => is_writable(FCPATH . 'uploads/'),
                        'server_user' => get_current_user() . ' (exec) / ' . exec('whoami'),
                        'diagnostic' => array(
                            'FCPATH' => FCPATH,
                            'uploads_exists' => is_dir(FCPATH . 'uploads'),
                            'uploads_writable' => is_writable(FCPATH . 'uploads'),
                            'attendance_exists' => is_dir(FCPATH . 'uploads/attendance'),
                            'attendance_writable' => is_writable(FCPATH . 'uploads/attendance'),
                        )
                    ));
                    return;
                }
            }
            
            if (!@mkdir($upload_path, 0775, true)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Cannot create upload folder',
                    'path' => $upload_path,
                    'parent_writable' => is_writable($parent),
                    'diagnostic' => array(
                        'server_user' => exec('whoami'),
                        'parent' => $parent,
                    )
                ));
                return;
            }
        }
        
        if (!is_writable($upload_path)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Upload folder not writable',
                'path' => $upload_path,
                'server_user' => exec('whoami'),
                'folder_owner' => function_exists('posix_getpwuid') 
                    ? posix_getpwuid(fileowner($upload_path))['name'] 
                    : 'unknown',
            ));
            return;
        }
        // =====================================================

        $config = array(
            'upload_path'   => $upload_path,
            'allowed_types' => 'pdf|doc|docx|jpg|jpeg|png|gif|xls|xlsx',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        );

        $this->load->library('upload', $config);

        $uploaded = array();
        $errors = array();

        $files = $_FILES['files'];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            $_FILES['file']['name']     = $files['name'][$i];
            $_FILES['file']['type']     = $files['type'][$i];
            $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['file']['error']    = $files['error'][$i];
            $_FILES['file']['size']     = $files['size'][$i];

            $this->upload->initialize($config);

            if ($this->upload->do_upload('file')) {
                $data = $this->upload->data();
                $relative_path = 'uploads/attendance/' . date('Y/m/') . $data['file_name'];

                $uploaded[] = array(
                    'name' => $files['name'][$i],
                    'path' => $relative_path,
                    'size' => $data['file_size'] * 1024,
                    'type' => $data['file_type'],
                );
            } else {
                $errors[] = array(
                    'name'  => $files['name'][$i],
                    'error' => $this->upload->display_errors('', ''),
                );
            }
        }

        echo json_encode(array(
            'success' => count($uploaded) > 0,
            'files'   => $uploaded,
            'errors'  => $errors,
        ));
    }

    /**
     * DELETE /api/attendance/delete/{id}
     */
    public function delete($id = null) {
        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        if (!$id) {
            echo json_encode(array('success' => false, 'message' => 'ID required'));
            return;
        }

        // Verify ownership
        $record = $this->db
            ->where('id', $id)
            ->where('emp_id', $emp_id)
            ->get('tk_attendance_records')
            ->row();

        if (!$record) {
            echo json_encode(array('success' => false, 'message' => 'Record not found or access denied'));
            return;
        }

        $this->db->where('id', $id);
        $this->db->delete('tk_attendance_records');

        echo json_encode(array(
            'success' => true,
            'message' => 'Attendance deleted',
        ));
    }

    public function delete_group($groupId = null) {
        header('Content-Type: application/json');
        
        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }
        
        if (!$groupId) {
            echo json_encode(array('success' => false, 'message' => 'Group ID required'));
            return;
        }
        
        // Verify the group belongs to this employee
        $group = $this->db
            ->select('g.*, r.emp_id, r.id as attendance_id')
            ->from('tk_attendance_groups g')
            ->join('tk_attendance_records r', 'r.id = g.attendance_id')
            ->where('g.id', $groupId)
            ->where('r.emp_id', $emp_id)
            ->get()
            ->row();
        
        if (!$group) {
            echo json_encode(array('success' => false, 'message' => 'Group not found or access denied'));
            return;
        }

        // ⭐ Block deletion of approved out-of-office groups
        if (strtolower($group->type) === 'outoffice' && strtolower($group->status) === 'approved') {
            echo json_encode(array(
                'success' => false,
                'message' => 'This out-of-office group is approved and cannot be deleted.'
            ));
            return;
        }
        
        $attendance_id = $group->attendance_id;
        
        // Start transaction
        $this->db->trans_start();
        
        // 1. Delete attachments for this group
        $this->db->where('group_id', $groupId);
        $this->db->delete('tk_attendance_attachments');
        
        // 2. Delete time entries for this group
        $this->db->where('group_id', $groupId);
        $this->db->delete('tk_attendance_time_entries');
        
        // 3. Delete the group itself
        $this->db->where('id', $groupId);
        $this->db->delete('tk_attendance_groups');
        
        // 4. Check remaining groups for this attendance record
        $remainingGroups = $this->db
            ->where('attendance_id', $attendance_id)
            ->count_all_results('tk_attendance_groups');
        
        $record_deleted = false;
        
        // 5. If no groups left, delete the main record too
        if ($remainingGroups === 0) {
            $this->db->where('id', $attendance_id);
            $this->db->delete('tk_attendance_records');
            $record_deleted = true;
        } else {
            // Recalculate totals: in1/out1/in2/out2/hours_worked based on remaining entries
            $this->recalculate_record_totals($attendance_id);
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('success' => false, 'message' => 'Transaction failed'));
            return;
        }
        
        echo json_encode(array(
            'success' => true,
            'message' => 'Group deleted successfully',
            'record_deleted' => $record_deleted,
            'remaining_groups' => $remainingGroups
        ));
    }

    /**
     * POST /api/attendance/cancel-open-entry/{id}
     *
     * Logs the open entry into tk_attendance_groups_logs, then deletes the
     * group and its child rows. If no groups remain for the parent attendance
     * record, the record itself is deleted too.
     */
    public function cancel_open_entry($groupId = null) {
        header('Content-Type: application/json');

        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        if (!$groupId) {
            echo json_encode(array('success' => false, 'message' => 'Group ID required'));
            return;
        }

        // Verify the group belongs to this employee
        $group = $this->db
            ->select('g.*, r.emp_id, r.id AS attendance_id')
            ->from('tk_attendance_groups g')
            ->join('tk_attendance_records r', 'r.id = g.attendance_id')
            ->where('g.id', $groupId)
            ->where('r.emp_id', $emp_id)
            ->get()
            ->row();

        if (!$group) {
            echo json_encode(array('success' => false, 'message' => 'Group not found or access denied'));
            return;
        }

        $attendance_id = $group->attendance_id;

        $this->db->trans_start();

        // ⭐ Log the group before deleting
        $this->Tk_attendance_model->log_open_entry_before_delete($groupId);

        // Delete attachments
        $this->db->where('group_id', $groupId);
        $this->db->delete('tk_attendance_attachments');

        // Delete time entries
        $this->db->where('group_id', $groupId);
        $this->db->delete('tk_attendance_time_entries');

        // Delete the group itself
        $this->db->where('id', $groupId);
        $this->db->delete('tk_attendance_groups');

        // Check remaining groups
        $remaining = $this->db
            ->where('attendance_id', $attendance_id)
            ->count_all_results('tk_attendance_groups');

        $record_deleted = false;

        if ($remaining === 0) {
            $this->db->where('id', $attendance_id);
            $this->db->delete('tk_attendance_records');
            $record_deleted = true;
        } else {
            $this->recalculate_record_totals($attendance_id);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('success' => false, 'message' => 'Transaction failed'));
            return;
        }

        echo json_encode(array(
            'success'          => true,
            'message'          => 'Open entry cancelled',
            'record_deleted'   => $record_deleted,
            'remaining_groups' => $remaining,
        ));
    }

    /**
     * Recalculate totals (in1/out1/in2/out2/hours_worked) for an attendance record
     */
    private function recalculate_record_totals($attendance_id) {
        // Get all remaining time entries sorted by hour
        $entries = $this->db
            ->where('attendance_id', $attendance_id)
            ->order_by('hour', 'ASC')
            ->get('tk_attendance_time_entries')
            ->result_array();
        
        if (empty($entries)) {
            return;
        }
        
        $in1 = null;
        $out1 = null;
        $in2 = null;
        $out2 = null;
        
        $noonHour = 12;
        
        foreach ($entries as $entry) {
            $hour = (int) $entry['hour'];
            
            if ($hour <= $noonHour) {
                if ($in1 === null) $in1 = $entry['start_time'];
                $out1 = $entry['end_time'];
            } else {
                if ($in2 === null) $in2 = $entry['start_time'];
                $out2 = $entry['end_time'];
            }
        }
        
        // Calculate total hours
        $totalHours = 0;
        if ($in1 && $out1) {
            $totalHours += $this->time_to_hours($in1, $out1);
        }
        if ($in2 && $out2) {
            $totalHours += $this->time_to_hours($in2, $out2);
        }
        
        // Update the main record
        $this->db->where('id', $attendance_id);
        $this->db->update('tk_attendance_records', array(
            'in1' => $in1,
            'out1' => $out1,
            'in2' => $in2,
            'out2' => $out2,
            'hours_worked' => round($totalHours, 2),
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Helper: calculate hours between two time strings
     */
    private function time_to_hours($start, $end) {
        $startTs = strtotime($start);
        $endTs = strtotime($end);
        return ($endTs - $startTs) / 3600;
    }

    public function leaves() {
        header('Content-Type: application/json');
        
        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }
        
        $leaves = $this->Tk_attendance_model->get_leaves($emp_id);
        
        echo json_encode(array(
            'success' => true,
            'data'    => $leaves,
        ));
    }

    public function holidays() {
        header('Content-Type: application/json');
        
        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }
        
        $holidays = $this->Tk_attendance_model->get_holidays($emp_id);

        echo json_encode(array(
            'success' => true,
            'data'    => $holidays,
        ));
    }

    public function dayoffs() {
        header('Content-Type: application/json');

        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        $dayoffs = $this->Tk_attendance_model->get_dayoffs($emp_id);

        echo json_encode(array(
            'success' => true,
            'data'    => $dayoffs,
        ));
    }

    /**
     * GET /api/attendance/attendance-dates?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function attendance_dates() {
        header('Content-Type: application/json');

        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        $from = $this->input->get('from');
        $to   = $this->input->get('to');

        if (!$from || !$to) {
            echo json_encode(array('success' => false, 'message' => 'from and to are required'));
            return;
        }

        $dates = $this->Tk_attendance_model->get_attendance_dates($emp_id, $from, $to);

        echo json_encode(array(
            'success' => true,
            'data'    => $dates,
        ));
    }

    /**
     * POST /api/attendance/save-dayoff
     * Save a day-off change for a specific week.
     */
    public function save_dayoff() {
        header('Content-Type: application/json');

        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        // Read JSON body
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!$payload) {
            echo json_encode(array('success' => false, 'message' => 'Invalid JSON payload'));
            return;
        }

        // Inject emp_id from session (ignore any emp_id in payload)
        $payload['emp_id'] = $emp_id;

        // Validate required fields
        if (empty($payload['old_date']) || empty($payload['new_date'])) {
            echo json_encode(array('success' => false, 'message' => 'old_date and new_date are required'));
            return;
        }

        $result = $this->Tk_attendance_model->save_dayoff_change($payload);

        echo json_encode($result);
    }

    public function cancel_dayoff() {
        header('Content-Type: application/json');

        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!$payload || empty($payload['id'])) {
            echo json_encode(array('success' => false, 'message' => 'Request ID required'));
            return;
        }

        $result = $this->Tk_attendance_model->cancel_dayoff_change($emp_id, $payload['id']);
        echo json_encode($result);
    }

    /**
     * GET /api/attendance/leave-blocked-hours?date=YYYY-MM-DD
     */
    public function leave_blocked_hours() {
        header('Content-Type: application/json');

        $emp_id = $this->session->userdata('empid');
        if (!$emp_id) {
            echo json_encode(array('success' => false, 'message' => 'Not logged in'));
            return;
        }

        $date = $this->input->get('date');
        if (!$date) {
            echo json_encode(array('success' => false, 'message' => 'date is required'));
            return;
        }

        $blocked = $this->Tk_attendance_model->get_leave_blocked_hours($emp_id, $date);

        echo json_encode(array(
            'success' => true,
            'data'    => $blocked,
        ));
    }

}