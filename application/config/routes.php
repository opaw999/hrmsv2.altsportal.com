<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Auth / Login / Logout
$route['login'] = 'welcome';
$route['check/login'] = 'auth/login';
$route['logout'] = 'auth/logout';

// Single Page Load
$route['dashboard'] = 'pages/dashboard';
$route['dashboard2'] = 'pages/dashboard2';
$route['my-profile'] = 'pages/myprofile';
$route['time-log'] = 'pages/timelog';
$route['form-request'] = 'pages/formrequest';
$route['vl-ledger'] = 'pages/vlledger';
$route['view-logs'] = 'pages/viewlogs';
$route['request_calendar/(:any)']    = 'pages/request_calendar/$1';
$route['request_daily']    = 'pages/request_daily';
$route['setup-hourly-rate'] = 'pages/setup_hourly_rate';
$route['export-deduction'] = 'pages/export_deduction';

// Leave Request
$route['createCell'] = 'request/createCell';
$route['getMonthCells'] = 'request/getMonthCells';
$route['save_leave'] = 'request/save_leave';
$route['edit_leave_form'] = 'request/edit_leave_form';
$route['check_date_leave'] = 'request/check_date_leave';
$route['delete_leave'] = 'request/delete_leave';
$route['delete_date_leave'] = 'request/delete_date_leave';
$route['delete_leave_attachment'] = 'request/delete_leave_attachment';
$route['save_dayoff'] = 'request/save_dayoff';
$route['cancel_dayoff'] = 'request/cancel_dayoff';
$route['get_week_totals'] = 'request/get_week_totals';
$route['getName'] = 'request/getName';
$route['get_relatives'] = 'request/get_relatives';
$route['get_approval_details'] = 'request/get_approval_details';
$route['viewFixedRequest'] = 'request/viewFixedRequest';
$route['leave_options'] = 'request/leave_options';
$route['getMatpatDays'] = 'request/getMatpatDays';

// VL Ledger
$route['emp_vl']            = 'vacation/emp_vl';
$route['vl_details']        = 'vacation/vl_details';
$route['request_vlPayment'] = 'vacation/request_vlPayment';

// Default Page Load
$route['default_controller'] = 'pages/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// admin

$route['admin/login']           = 'admin/auth';
$route['admin/logout']          = 'admin/auth/logout';
$route['admin/auth_login']      = 'admin/auth/auth_login';

$route['admin']                 = 'admin/pages/menu';
$route['admin/menu/(:any)']     = 'admin/pages/menu/$1';
$route['admin/access-control']  = 'admin/pages/access_control';
$route['admin/profile']         = 'admin/pages/profile';

$route['admin/requestPage']             = 'admin/request_admin/requestPage';
$route['admin/view_requests']           = 'admin/request_admin/view_requests';
$route['admin/requestCutoffTable']      = 'admin/request_admin/requestCutoffTable';
$route['admin/request_leave_list']      = 'admin/request_admin/request_leave_list';
$route['admin/view_pending_requests']   = 'admin/request_admin/view_pending_requests';
$route['admin/view_approved_requests']  = 'admin/request_admin/view_approved_requests';
$route['admin/approveRequest']          = 'admin/request_admin/approveRequest';
$route['admin/view_change_dayoff']      = 'admin/request_admin/view_change_dayoff';
$route['admin/doCutoffTable']           = 'admin/request_admin/doCutoffTable';
$route['admin/request_dayoff_list']     = 'admin/request_admin/request_dayoff_list';

$route['admin/view_pending_cdo']        = 'admin/request_admin/view_pending_cdo';
$route['admin/view_approved_cdo']       = 'admin/request_admin/view_approved_cdo';
$route['admin/approvechangeDayoff']     = 'admin/request_admin/approvechangeDayoff';
$route['admin/view_duty']               = 'admin/request_admin/view_duty';

$route['admin/dutyCutoffTable']         = 'admin/request_admin/dutyCutoffTable';
$route['admin/request_duty_list']       = 'admin/request_admin/request_duty_list';

$route['admin/view_duty_request']       = 'admin/request_admin/view_duty_request';
$route['admin/approve_duty_hours']      = 'admin/request_admin/approve_duty_hours';
$route['admin/search_employee']         = 'admin/request_admin/search_employee';
$route['admin/employee_vl']             = 'admin/request_admin/employee_vl';
$route['admin/vl_list']                 = 'admin/request_admin/vl_list';

$route['admin/viewVLperYear']           = 'admin/request_admin/viewVLperYear';
$route['admin/vl_payment_list']         = 'admin/request_admin/vl_payment_list';
$route['admin/viewVLpayment']           = 'admin/request_admin/viewVLpayment';
$route['admin/vlPaymentApproval']       = 'admin/request_admin/vlPaymentApproval';

$route['admin/cutoffTable']             = 'admin/dtr_admin/cutoffTable';
$route['admin/co_employee_list']        = 'admin/dtr_admin/co_employee_list';
$route['admin/view_dtr']                = 'admin/dtr_admin/view_dtr';
$route['admin/dtr_details']             = 'admin/dtr_admin/dtr_details';
$route['admin/dtr_totals']              = 'admin/dtr_admin/dtr_totals';
$route['admin/dtr_request']             = 'admin/dtr_admin/dtr_request';
$route['admin/dtr_duty']                = 'admin/dtr_admin/dtr_duty';

$route['admin/access_details']          = 'admin/users_admin/access_details';
$route['admin/updateAccess']            = 'admin/users_admin/updateAccess';
$route['admin/update_adminUser']        = 'admin/users_admin/update_adminUser';
$route['admin/add_adminUser']           = 'admin/users_admin/add_adminUser';
$route['admin/resetPassword']           = 'admin/users_admin/resetPassword';
$route['admin/updateSubordinates']      = 'admin/users_admin/updateSubordinates';
$route['admin/updateAdminAccount']      = 'admin/users_admin/updateAdminAccount';

$route['admin/process_list']            = 'admin/process_admin/process_list';
$route['admin/process_options']         = 'admin/process_admin/process_options';
$route['admin/process_cutoff']          = 'admin/process_admin/process_cutoff';

$route['admin/cutoffReportTable']       = 'admin/reports_admin/cutoffReportTable';
$route['admin/generate_options']        = 'admin/reports_admin/generate_options';
$route['admin/generate_cutoff_report']  = 'admin/reports_admin/generate_cutoff_report';
$route['admin/generate_dtr']            = 'admin/reports_admin/generate_dtr';
$route['admin/vlpaymentReportTable']    = 'admin/reports_admin/vlpaymentReportTable';
$route['admin/generate_vl_payment']     = 'admin/reports_admin/generate_vl_payment';

$route['admin/viewRender']              = 'admin/setup_admin/viewRender';
$route['admin/updateRender']            = 'admin/setup_admin/updateRender';
$route['admin/addRender']               = 'admin/setup_admin/addRender';
$route['admin/viewVLdefaults']          = 'admin/setup_admin/viewVLdefaults';
$route['admin/updateVLdefaults']        = 'admin/setup_admin/updateVLdefaults';
$route['admin/updateRequestForm']       = 'admin/setup_admin/updateRequestForm';
$route['admin/updateRequest']           = 'admin/setup_admin/updateRequest';
$route['admin/addRequestForm']          = 'admin/setup_admin/addRequestForm';
$route['admin/addRequest']              = 'admin/setup_admin/addRequest';
$route['admin/updateMatpatForm']        = 'admin/setup_admin/updateMatpatForm';
$route['admin/updateMatpat']            = 'admin/setup_admin/updateMatpat';
$route['admin/updateExtension']         = 'admin/setup_admin/updateExtension';
$route['admin/updateScheduleForm']      = 'admin/setup_admin/updateScheduleForm';
$route['admin/updateSchedule']          = 'admin/setup_admin/updateSchedule';
$route['admin/generateVL']              = 'admin/setup_admin/generateVL';
$route['admin/addCutoff']               = 'admin/setup_admin/addCutoff';
$route['admin/vlAccess']                = 'admin/setup_admin/vlAccess';

$route['admin/addHoliday']              = 'admin/setup_admin/addHoliday';
$route['admin/updateHolidayForm']       = 'admin/setup_admin/updateHolidayForm';
$route['admin/updateHoliday']           = 'admin/setup_admin/updateHoliday';

// attendance and time log
$route['api/attendance/load/(:any)']            = 'attendance/load/$1';
$route['api/attendance/events']                 = 'attendance/events';
$route['api/attendance/save']                   = 'attendance/save';

$route['api/attendance/upload']                 = 'attendance/upload';
$route['api/attendance/delete-group/(:num)']    = 'attendance/delete_group/$1';
$route['api/attendance/cancel-open-entry/(:num)'] = 'attendance/cancel_open_entry/$1';
$route['api/attendance/leaves']                 = 'attendance/leaves';
$route['api/attendance/holidays']               = 'attendance/holidays';
$route['api/attendance/dayoffs']                = 'attendance/dayoffs';
$route['api/attendance/attendance-dates']       = 'attendance/attendance_dates';
$route['api/attendance/save-dayoff']            = 'attendance/save_dayoff';
$route['api/attendance/cancel-dayoff']          = 'attendance/cancel_dayoff';
$route['api/attendance/leave-blocked-hours']    = 'attendance/leave_blocked_hours';
$route['api/viewlogs/get_cutoff_menu']          = 'viewlogs/get_cutoff_menu';
$route['api/viewlogs/get_previous_cutoffs']     = 'viewlogs/get_previous_cutoffs';
$route['api/viewlogs/get_cutoff_entries']       = 'viewlogs/get_cutoff_entries';
$route['api/viewlogs/get_daily_entries']        = 'viewlogs/get_daily_entries';
$route['api/viewlogs/get_leave_details']        = 'viewlogs/get_leave_details';

$route['api/attendance/save/timelog']           = 'timelog/saveTimeLog';
$route['api/attendance/get_today_logs']         = 'timelog/get_today_logs';
$route['api/attendance/cancel_punch']           = 'timelog/cancel_punch';


// Server Time API
$route['api/server_time']                       = 'timelog/get_server_time';

// Payroll
$route['api/payroll/masterfile/list']          = 'payroll/get_hourly_rates';
$route['api/payroll/masterfile/update']        = 'payroll/update_hourly_rate';
$route['api/payroll/get/rate/logs']             = 'payroll/get_rate_logs';
//Deductions
$route['deductions/export_csv']                = 'deductions/export_csv';
