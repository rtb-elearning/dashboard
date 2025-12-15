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
 * RTB Dashboard main page.
 *
 * @package    local_rtbdashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/rtbdashboard/lib.php');

// Require login.
require_login();

// Set up the page context.
$context = context_system::instance();
$PAGE->set_context($context);

// Check capability.
require_capability('local/rtbdashboard:view', $context);

$PAGE->set_url(new moodle_url('/local/rtbdashboard/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('page_title', 'local_rtbdashboard'));
$PAGE->set_heading(get_string('page_heading', 'local_rtbdashboard'));

// Add body classes for plugin-specific page styling.
$PAGE->add_body_class('local-rtbdashboard-plugin');
$PAGE->add_body_class('local-rtbdashboard-page');

// Load custom CSS.
$PAGE->requires->css('/local/rtbdashboard/styles.css');

// Load custom JavaScript module.
$PAGE->requires->js_call_amd('local_rtbdashboard/dashboard', 'init');

// Add breadcrumb navigation.
$PAGE->navbar->add(get_string('pluginname', 'local_rtbdashboard'));

// Prepare user data for Preact (via data attributes).
$userroles = [];
foreach (get_user_roles($context, $USER->id) as $role) {
    $userroles[] = $role->shortname;
}

$userdata = [
    'id' => $USER->id,
    'fullname' => fullname($USER),
    'firstname' => $USER->firstname,
    'lastname' => $USER->lastname,
    'email' => $USER->email,
    'avatar' => $OUTPUT->get_generated_image_for_id($USER->id),
    'roles' => $userroles,
];

// Get role IDs for students and teachers.
$studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
$teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);

// Count students and teachers by role assignments.
$totalstudents = $studentroleid ? $DB->count_records('role_assignments', ['roleid' => $studentroleid]) : 0;
$totalteachers = $teacherroleid ? $DB->count_records('role_assignments', ['roleid' => $teacherroleid]) : 0;

// Get teacher list (limit 5) with user details.
$teachers = [];
if ($teacherroleid) {
    $teacherrecords = $DB->get_records_sql(
        "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, u.picture
         FROM {user} u
         JOIN {role_assignments} ra ON ra.userid = u.id
         WHERE ra.roleid = ? AND u.deleted = 0
         ORDER BY u.lastname, u.firstname
         LIMIT 5",
        [$teacherroleid]
    );
    foreach ($teacherrecords as $teacher) {
        $teachers[] = [
            'id' => $teacher->id,
            'firstname' => $teacher->firstname,
            'lastname' => $teacher->lastname,
            'fullname' => fullname($teacher),
            'email' => $teacher->email,
            'avatar' => $OUTPUT->get_generated_image_for_id($teacher->id),
        ];
    }
}

// Prepare stats data for Preact.
$statsdata = [
    'totalCourses' => $DB->count_records('course') - 1, // Exclude site course.
    'totalUsers' => $DB->count_records('user', ['deleted' => 0]) - 1, // Exclude guest.
    'totalStudents' => $totalstudents,
    'totalTeachers' => $totalteachers,
    'totalEnrollments' => $DB->count_records('user_enrolments'),
    'totalActivities' => $DB->count_records('course_modules'),
    'teachers' => $teachers,
];

// Prepare data for template.
$templatecontext = [
    'user_data_json' => json_encode($userdata, JSON_HEX_QUOT | JSON_HEX_APOS),
    'stats_data_json' => json_encode($statsdata, JSON_HEX_QUOT | JSON_HEX_APOS),
    'active_page' => 'home',
];

// Output the page.
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_rtbdashboard/root', $templatecontext);
echo $OUTPUT->footer();
