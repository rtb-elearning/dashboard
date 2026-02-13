<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for local_elby_dashboard.
 *
 * @package    local_elby_dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Elby Dashboard';

// Page strings.
$string['page_title'] = 'Elby Dashboard';
$string['page_heading'] = 'Elby Dashboard';
$string['page_welcome'] = 'Welcome to Elby Dashboard';
$string['page_description'] = 'View analytics and statistics for your e-learning platform.';

// Admin page strings.
$string['admin_page_title'] = 'Dashboard Administration';
$string['admin_page_heading'] = 'Elby Dashboard Administration';
$string['admin_page_welcome'] = 'Welcome to Dashboard Administration';
$string['admin_page_description'] = 'Manage dashboard settings and view detailed analytics.';

// Navigation strings.
$string['nav_admin'] = 'Dashboard Admin';
$string['nav_overview'] = 'Overview';
$string['nav_reports'] = 'Reports';
$string['nav_settings'] = 'Settings';

// Stats labels.
$string['stats_total_courses'] = 'Total Courses';
$string['stats_total_users'] = 'Total Users';
$string['stats_total_enrollments'] = 'Total Enrollments';
$string['stats_total_activities'] = 'Total Activities';
$string['stats_active_users'] = 'Active Users (30 days)';
$string['stats_total_teachers'] = 'Total Teachers';
$string['stats_total_students'] = 'Total Students';

// Capability strings.
$string['elby_dashboard:view'] = 'View Elby Dashboard';
$string['elby_dashboard:viewreports'] = 'View detailed reports';
$string['elby_dashboard:manage'] = 'Manage dashboard settings';

// =============================================
// SIDEBAR SETTINGS
// =============================================
$string['sidenavheading'] = 'Sidebar Settings';
$string['sidenavheading_desc'] = 'Configure the appearance of the sidebar navigation.';
$string['sidenavtitle'] = 'Sidebar Title';
$string['sidenavtitle_desc'] = 'The title displayed at the top of the sidebar navigation. Default: "Dashboard"';
$string['sidenavlogo'] = 'Sidebar Logo';
$string['sidenavlogo_desc'] = 'Upload a logo image to display in the sidebar. Recommended size: 32x32 pixels. Supported formats: JPG, PNG, SVG, GIF.';

// =============================================
// COLOR SETTINGS
// =============================================
$string['colorsheading'] = 'Color Settings';
$string['colorsheading_desc'] = 'Customize the colors used throughout the dashboard.';
$string['sidenavaccentcolor'] = 'Sidebar Accent Color';
$string['sidenavaccentcolor_desc'] = 'The background color for the active menu item in the sidebar.';
$string['statcard1color'] = 'Students Card Color';
$string['statcard1color_desc'] = 'Background color for the Students statistics card.';
$string['statcard2color'] = 'Teachers Card Color';
$string['statcard2color_desc'] = 'Background color for the Teachers statistics card.';
$string['statcard3color'] = 'Users Card Color';
$string['statcard3color_desc'] = 'Background color for the Total Users statistics card.';
$string['statcard4color'] = 'Courses Card Color';
$string['statcard4color_desc'] = 'Background color for the Courses statistics card.';
$string['chartprimarycolor'] = 'Chart Primary Color';
$string['chartprimarycolor_desc'] = 'Primary color used in charts and graphs (e.g., enrollments, attendance).';
$string['chartsecondarycolor'] = 'Chart Secondary Color';
$string['chartsecondarycolor_desc'] = 'Secondary color used in charts and graphs (e.g., completions).';

// =============================================
// HEADER OPTIONS
// =============================================
$string['headerheading'] = 'Header Options';
$string['headerheading_desc'] = 'Configure which elements appear in the dashboard header.';
$string['showsearchbar'] = 'Show Search Bar';
$string['showsearchbar_desc'] = 'Display the search bar in the header.';
$string['shownotifications'] = 'Show Notifications';
$string['shownotifications_desc'] = 'Display the notification bell icon in the header.';
$string['showuserprofile'] = 'Show User Profile';
$string['showuserprofile_desc'] = 'Display the user profile section (avatar and name) in the header.';

// =============================================
// MENU CUSTOMIZATION
// =============================================
$string['menuheading'] = 'Menu Customization';
$string['menuheading_desc'] = 'Choose which menu items to display in the sidebar navigation.';
$string['showmenu_courses'] = 'Show Courses';
$string['showmenu_courses_desc'] = 'Display the Courses menu item in the sidebar.';
$string['showmenu_presence'] = 'Show Presence';
$string['showmenu_presence_desc'] = 'Display the Presence menu item in the sidebar.';
$string['showmenu_communication'] = 'Show Communication';
$string['showmenu_communication_desc'] = 'Display the Communication menu item in the sidebar.';
$string['showmenu_event'] = 'Show Event';
$string['showmenu_event_desc'] = 'Display the Event menu item in the sidebar.';
$string['showmenu_pedagogy'] = 'Show Pedagogy';
$string['showmenu_pedagogy_desc'] = 'Display the Pedagogy menu item in the sidebar.';
$string['showmenu_message'] = 'Show Message';
$string['showmenu_message_desc'] = 'Display the Message menu item in the sidebar.';
$string['showmenu_completion'] = 'Show Completion';
$string['showmenu_completion_desc'] = 'Display the Completion menu item in the sidebar.';
$string['showmenu_settings'] = 'Show Settings';
$string['showmenu_settings_desc'] = 'Display the Settings menu item in the sidebar.';

// Courses report strings.
$string['courses_report'] = 'Courses Report';
$string['courses_report_title'] = 'Course Report by School';
$string['select_course'] = 'Select a course';
$string['school_code'] = 'School Code';
$string['school_name'] = 'School Name';
$string['student_count'] = 'Students';
$string['completion_rate'] = 'Completion Rate';
$string['average_grade'] = 'Average Grade';
$string['overview'] = 'Overview';
$string['enrolled_students'] = 'Enrolled Students';
$string['unit'] = 'Unit';

// =============================================
// COURSE REPORT SETTINGS
// =============================================
$string['reportheading'] = 'Course Report Settings';
$string['reportheading_desc'] = 'Configure settings for course reports.';
$string['enrollment_cutoff_month'] = 'Enrollment Cutoff Month';
$string['enrollment_cutoff_month_desc'] = 'Only include students enrolled on or after this month in the current academic year.';
$string['enrollment_cutoff_day'] = 'Enrollment Cutoff Day';
$string['enrollment_cutoff_day_desc'] = 'The day of the month for the enrollment cutoff date.';

// =============================================
// SDMS INTEGRATION SETTINGS
// =============================================
$string['sdmsheading'] = 'SDMS Integration';
$string['sdmsheading_desc'] = 'Configure connection to the Student Data Management System API. SDMS uses IP whitelist authentication.';
$string['sdms_api_url'] = 'SDMS API URL';
$string['sdms_api_url_desc'] = 'Base URL for the SDMS API (e.g., http://sdms.internal/api). No trailing slash.';
$string['sdms_timeout'] = 'Request Timeout';
$string['sdms_timeout_desc'] = 'HTTP request timeout in seconds. Default: 30';
$string['sdms_cache_ttl'] = 'Cache TTL';
$string['sdms_cache_ttl_desc'] = 'Time-to-live for cached SDMS data in seconds. Default: 604800 (7 days)';

// SDMS error messages.
$string['sdmsapierror'] = 'SDMS API error: {$a}';
$string['nosdmsid'] = 'User does not have an SDMS ID configured';
$string['sdmsnotfound'] = 'Record not found in SDMS';
$string['sdmssyncfailed'] = 'Failed to sync from SDMS: {$a}';

// =============================================
// SDMS SELF-REGISTRATION
// =============================================
$string['sdms_signup_title'] = 'Sign Up with SDMS';
$string['sdms_signup_heading'] = 'Create Your Account';
$string['sdms_signup_subtext'] = 'Sign up using your SDMS code';
$string['sdms_lookup_heading'] = 'Find Your Account';
$string['sdms_preview_heading'] = 'Your Information';
$string['sdms_register_heading'] = 'Set Your Password';
$string['sdms_code_label'] = 'SDMS Code';
$string['sdms_code_placeholder'] = 'Enter your SDMS code';
$string['sdms_usertype_label'] = 'I am a';
$string['sdms_student'] = 'Student';
$string['sdms_staff'] = 'Staff';
$string['sdms_lookup_btn'] = 'Look Up';
$string['sdms_continue_btn'] = 'Continue to Register';
$string['sdms_register_btn'] = 'Create Account';
$string['sdms_back'] = 'Back';
$string['sdms_back_to_login'] = 'Back to login';
$string['sdms_confirm_password'] = 'Confirm Password';
$string['sdms_already_registered'] = 'Already Registered';
$string['sdms_already_registered_msg'] = 'An account with this SDMS code already exists. Please log in instead.';
$string['sdms_go_to_login'] = 'Go to Login';
$string['sdms_not_found'] = 'No record found in SDMS for this code. Please check your code and try again.';
$string['sdms_success_title'] = 'Account Created!';
$string['sdms_success_msg'] = 'Your account has been created successfully. You can now log in with your SDMS code as your username.';
$string['sdms_password_mismatch'] = 'Passwords do not match.';
$string['sdms_code_empty'] = 'Please enter your SDMS code.';
$string['sdms_rate_limited'] = 'Too many attempts. Please try again in a few minutes.';
$string['sdms_signup_email_domain'] = 'Signup Email Domain';
$string['sdms_signup_email_domain_desc'] = 'Domain used to generate email addresses for SDMS self-registration (e.g., rtb.ac.rw). Emails will be in the format: sdms_code@domain.';
$string['sdms_signup_link'] = 'Sign up with SDMS';

// Scheduled task names.
$string['task_compute_user_metrics'] = 'Compute user engagement metrics';
$string['task_aggregate_school_metrics'] = 'Aggregate school-level metrics';
$string['task_refresh_sdms_cache'] = 'Refresh stale SDMS cache records';
$string['task_cleanup_old_metrics'] = 'Clean up old metrics data';

// Metrics API strings.
$string['no_metrics_data'] = 'No metrics data available for this period';

// Schools directory strings.
$string['schools_directory'] = 'Schools Directory';
$string['school_detail'] = 'School Detail';
$string['student_list'] = 'Student List';
$string['admin_panel'] = 'Admin Panel';
$string['filter_province'] = 'Province';
$string['filter_district'] = 'District';
$string['filter_all'] = 'All';
$string['filter_course'] = 'Course';
$string['filter_school'] = 'School';
$string['filter_engagement'] = 'Engagement Level';
$string['export_csv'] = 'Export CSV';
$string['engagement_high'] = 'High Engagement';
$string['engagement_medium'] = 'Medium Engagement';
$string['engagement_low'] = 'Low Engagement';
$string['at_risk'] = 'At Risk';
$string['total_enrolled'] = 'Total Enrolled';
$string['total_active'] = 'Active This Week';
$string['avg_quiz_score'] = 'Avg Quiz Score';
$string['last_synced'] = 'Last Synced';
$string['sync_school'] = 'Sync School';
$string['sync_status'] = 'Sync Status';
$string['linked_users'] = 'Linked Users';
$string['stale_records'] = 'Stale Records';
$string['error_count'] = 'Error Count';
$string['recent_sync_logs'] = 'Recent Sync Logs';
$string['manual_sync'] = 'Manual Sync';
$string['task_schedule'] = 'Task Schedule';
$string['last_run'] = 'Last Run';
$string['next_scheduled'] = 'Next Scheduled';
$string['search_students'] = 'Search students...';
$string['search_schools'] = 'Search schools...';
$string['no_schools_found'] = 'No schools found';
$string['no_students_found'] = 'No students found';

// General strings.
$string['loading'] = 'Loading...';
$string['error'] = 'An error occurred';
$string['no_data'] = 'No data available';
