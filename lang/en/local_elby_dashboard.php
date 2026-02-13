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

// General strings.
$string['loading'] = 'Loading...';
$string['error'] = 'An error occurred';
$string['no_data'] = 'No data available';
