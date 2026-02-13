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

    // SDMS integration APIs.
    'local_elby_dashboard_get_user_sdms_profile' => [
        'classname'    => 'local_elby_dashboard\external\sdms',
        'methodname'   => 'get_user_sdms_profile',
        'description'  => 'Get user SDMS profile from local cache (refreshes if stale)',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:view',
    ],
    'local_elby_dashboard_get_school_info' => [
        'classname'    => 'local_elby_dashboard\external\sdms',
        'methodname'   => 'get_school_info',
        'description'  => 'Get school information with hierarchy from cache (syncs on miss)',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:viewreports',
    ],
    'local_elby_dashboard_lookup_sdms_user' => [
        'classname'    => 'local_elby_dashboard\external\sdms',
        'methodname'   => 'lookup_sdms_user',
        'description'  => 'Live lookup of SDMS user data with school hierarchy (no caching)',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:manage',
    ],
    'local_elby_dashboard_link_user' => [
        'classname'    => 'local_elby_dashboard\external\sdms',
        'methodname'   => 'link_user',
        'description'  => 'Link a new SDMS user to a Moodle account',
        'type'         => 'write',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:manage',
    ],
    'local_elby_dashboard_refresh_user' => [
        'classname'    => 'local_elby_dashboard\external\sdms',
        'methodname'   => 'refresh_user',
        'description'  => 'Force refresh cached SDMS data for an existing linked user',
        'type'         => 'write',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:manage',
    ],
    'local_elby_dashboard_sync_school_now' => [
        'classname'    => 'local_elby_dashboard\external\sdms',
        'methodname'   => 'sync_school_now',
        'description'  => 'Force sync a school from SDMS API',
        'type'         => 'write',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:manage',
    ],

    // Metrics APIs.
    'local_elby_dashboard_get_school_metrics' => [
        'classname'    => 'local_elby_dashboard\external\metrics',
        'methodname'   => 'get_school_metrics',
        'description'  => 'Get aggregated school metrics for the most recent period',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:viewreports',
    ],
    'local_elby_dashboard_get_student_list' => [
        'classname'    => 'local_elby_dashboard\external\metrics',
        'methodname'   => 'get_student_list',
        'description'  => 'Get paginated student list with engagement metrics',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:viewreports',
    ],
    'local_elby_dashboard_get_engagement_distribution' => [
        'classname'    => 'local_elby_dashboard\external\metrics',
        'methodname'   => 'get_engagement_distribution',
        'description'  => 'Get engagement distribution breakdown for a school',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/elby_dashboard:viewreports',
    ],

    // SDMS self-registration APIs (no login required).
    'local_elby_dashboard_lookup_for_signup' => [
        'classname'     => 'local_elby_dashboard\external\signup',
        'methodname'    => 'lookup_for_signup',
        'description'   => 'Look up SDMS user for self-registration (no login required)',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'local_elby_dashboard_register_sdms_user' => [
        'classname'     => 'local_elby_dashboard\external\signup',
        'methodname'    => 'register_sdms_user',
        'description'   => 'Register a new Moodle user from SDMS data (no login required)',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => false,
    ],
];

$services = [
    'Elby Dashboard Service' => [
        'functions' => [
            'local_elby_dashboard_get_course_completion_stats',
            'local_elby_dashboard_get_category_completion_stats',
            'local_elby_dashboard_get_course_report_by_school',
            'local_elby_dashboard_get_all_courses_report',
            'local_elby_dashboard_get_user_sdms_profile',
            'local_elby_dashboard_get_school_info',
            'local_elby_dashboard_lookup_sdms_user',
            'local_elby_dashboard_link_user',
            'local_elby_dashboard_refresh_user',
            'local_elby_dashboard_sync_school_now',
            'local_elby_dashboard_get_school_metrics',
            'local_elby_dashboard_get_student_list',
            'local_elby_dashboard_get_engagement_distribution',
            'local_elby_dashboard_lookup_for_signup',
            'local_elby_dashboard_register_sdms_user',
        ],
        'restrictedusers' => 0,
        'enabled'         => 1,
        'shortname'       => 'elby_dashboard',
    ],
];
