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
 * Web service definitions for the Elby Dashboard plugin.
 *
 * @package    local_elby_dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    // Completion statistics APIs.
    'local_elby_dashboard_get_course_completion_stats' => [
        'classname'    => 'local_elby_dashboard\external\completion',
        'methodname'   => 'get_course_completion_stats',
        'description'  => 'Get completion statistics for a course including section and activity breakdown',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:view',
    ],
    'local_elby_dashboard_get_category_completion_stats' => [
        'classname'    => 'local_elby_dashboard\external\completion',
        'methodname'   => 'get_category_completion_stats',
        'description'  => 'Get completion statistics for all courses in a category (including subcategories)',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:view',
    ],

    // Course report by school APIs.
    'local_elby_dashboard_get_course_report_by_school' => [
        'classname'    => 'local_elby_dashboard\external\course_report',
        'methodname'   => 'get_course_report_by_school',
        'description'  => 'Get course report data grouped by school code with completion rates and grades per section',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:viewreports',
    ],
    'local_elby_dashboard_get_all_courses_report' => [
        'classname'    => 'local_elby_dashboard\external\course_report',
        'methodname'   => 'get_all_courses_report',
        'description'  => 'Get report data for all courses grouped by school code',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:viewreports',
    ],
];

$services = [
    'Elby Dashboard Service' => [
        'functions' => [
            'local_elby_dashboard_get_course_completion_stats',
            'local_elby_dashboard_get_category_completion_stats',
            'local_elby_dashboard_get_course_report_by_school',
            'local_elby_dashboard_get_all_courses_report',
        ],
        'restrictedusers' => 0,
        'enabled'         => 1,
        'shortname'       => 'elby_dashboard',
    ],
];
