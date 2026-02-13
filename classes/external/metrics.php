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
 * External API for metrics data.
 *
 * Provides web service endpoints for querying computed metrics.
 *
 * @package    local_elby_dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_elby_dashboard\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use context_system;

/**
 * External API for metrics data.
 */
class metrics extends external_api {

    // =========================================================================
    // get_school_metrics — Full school metrics record for a period.
    // =========================================================================

    /**
     * Parameters for get_school_metrics.
     */
    public static function get_school_metrics_parameters(): external_function_parameters {
        return new external_function_parameters([
            'school_code' => new external_value(PARAM_TEXT, 'SDMS school code'),
            'courseid' => new external_value(PARAM_INT, 'Course ID (0 for school-wide)', VALUE_DEFAULT, 0),
            'period_type' => new external_value(PARAM_TEXT, 'Period type: weekly or monthly', VALUE_DEFAULT, 'weekly'),
        ]);
    }

    /**
     * Get school metrics for the most recent matching period.
     *
     * @param string $schoolcode SDMS school code.
     * @param int $courseid Course ID (0 for school-wide).
     * @param string $periodtype Period type.
     * @return array School metrics data.
     */
    public static function get_school_metrics(string $schoolcode, int $courseid = 0,
            string $periodtype = 'weekly'): array {
        global $DB;

        $params = self::validate_parameters(
            self::get_school_metrics_parameters(),
            ['school_code' => $schoolcode, 'courseid' => $courseid, 'period_type' => $periodtype]
        );
        $schoolcode = $params['school_code'];
        $courseid = $params['courseid'];
        $periodtype = $params['period_type'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:viewreports', $context);

        // Resolve school.
        $school = $DB->get_record('elby_schools', ['school_code' => $schoolcode], 'id, school_name, school_code');
        if (!$school) {
            return [
                'success' => false,
                'error' => 'School not found',
                'school_code' => $schoolcode,
                'school_name' => '',
                'metrics' => null,
            ];
        }

        // Get the most recent metrics record.
        $metrics = $DB->get_records_sql(
            "SELECT *
             FROM {elby_school_metrics}
             WHERE schoolid = :schoolid
               AND courseid = :courseid
               AND period_type = :periodtype
             ORDER BY period_start DESC
             LIMIT 1",
            [
                'schoolid' => $school->id,
                'courseid' => $courseid,
                'periodtype' => $periodtype,
            ]
        );

        $metrics = reset($metrics);

        if (!$metrics) {
            return [
                'success' => true,
                'error' => '',
                'school_code' => $school->school_code,
                'school_name' => $school->school_name,
                'metrics' => null,
            ];
        }

        return [
            'success' => true,
            'error' => '',
            'school_code' => $school->school_code,
            'school_name' => $school->school_name,
            'metrics' => [
                'period_start' => (int) $metrics->period_start,
                'period_end' => (int) $metrics->period_end,
                'period_type' => $metrics->period_type,
                'total_enrolled' => (int) $metrics->total_enrolled,
                'total_active' => (int) $metrics->total_active,
                'total_inactive' => (int) $metrics->total_inactive,
                'new_enrollments' => (int) $metrics->new_enrollments,
                'avg_actions_per_student' => (float) ($metrics->avg_actions_per_student ?? 0),
                'avg_active_days' => (float) ($metrics->avg_active_days ?? 0),
                'avg_time_spent_minutes' => (float) ($metrics->avg_time_spent_minutes ?? 0),
                'total_resource_views' => (int) $metrics->total_resource_views,
                'avg_resources_per_student' => (float) ($metrics->avg_resources_per_student ?? 0),
                'total_submissions' => (int) $metrics->total_submissions,
                'total_quiz_attempts' => (int) $metrics->total_quiz_attempts,
                'avg_assignment_score' => (float) ($metrics->avg_assignment_score ?? 0),
                'avg_quiz_score' => (float) ($metrics->avg_quiz_score ?? 0),
                'submission_rate' => (float) ($metrics->submission_rate ?? 0),
                'avg_course_progress' => (float) ($metrics->avg_course_progress ?? 0),
                'completion_rate' => (float) ($metrics->completion_rate ?? 0),
                'high_engagement_count' => (int) $metrics->high_engagement_count,
                'medium_engagement_count' => (int) $metrics->medium_engagement_count,
                'low_engagement_count' => (int) $metrics->low_engagement_count,
                'at_risk_count' => (int) $metrics->at_risk_count,
            ],
        ];
    }

    /**
     * Return structure for get_school_metrics.
     */
    public static function get_school_metrics_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether data was found'),
            'error' => new external_value(PARAM_TEXT, 'Error message'),
            'school_code' => new external_value(PARAM_TEXT, 'School code'),
            'school_name' => new external_value(PARAM_TEXT, 'School name'),
            'metrics' => new external_single_structure([
                'period_start' => new external_value(PARAM_INT, 'Period start timestamp'),
                'period_end' => new external_value(PARAM_INT, 'Period end timestamp'),
                'period_type' => new external_value(PARAM_TEXT, 'Period type'),
                'total_enrolled' => new external_value(PARAM_INT, 'Total enrolled students'),
                'total_active' => new external_value(PARAM_INT, 'Total active students'),
                'total_inactive' => new external_value(PARAM_INT, 'Total inactive students'),
                'new_enrollments' => new external_value(PARAM_INT, 'New enrollments'),
                'avg_actions_per_student' => new external_value(PARAM_FLOAT, 'Average actions per student'),
                'avg_active_days' => new external_value(PARAM_FLOAT, 'Average active days'),
                'avg_time_spent_minutes' => new external_value(PARAM_FLOAT, 'Average time spent in minutes'),
                'total_resource_views' => new external_value(PARAM_INT, 'Total resource views'),
                'avg_resources_per_student' => new external_value(PARAM_FLOAT, 'Average resources per student'),
                'total_submissions' => new external_value(PARAM_INT, 'Total submissions'),
                'total_quiz_attempts' => new external_value(PARAM_INT, 'Total quiz attempts'),
                'avg_assignment_score' => new external_value(PARAM_FLOAT, 'Average assignment score'),
                'avg_quiz_score' => new external_value(PARAM_FLOAT, 'Average quiz score'),
                'submission_rate' => new external_value(PARAM_FLOAT, 'Submission rate'),
                'avg_course_progress' => new external_value(PARAM_FLOAT, 'Average course progress'),
                'completion_rate' => new external_value(PARAM_FLOAT, 'Completion rate'),
                'high_engagement_count' => new external_value(PARAM_INT, 'High engagement count'),
                'medium_engagement_count' => new external_value(PARAM_INT, 'Medium engagement count'),
                'low_engagement_count' => new external_value(PARAM_INT, 'Low engagement count'),
                'at_risk_count' => new external_value(PARAM_INT, 'At-risk count'),
            ], 'Metrics data', VALUE_OPTIONAL),
        ]);
    }

    // =========================================================================
    // get_student_list — Paginated student metrics list.
    // =========================================================================

    /**
     * Parameters for get_student_list.
     */
    public static function get_student_list_parameters(): external_function_parameters {
        return new external_function_parameters([
            'school_code' => new external_value(PARAM_TEXT, 'SDMS school code (empty for all)', VALUE_DEFAULT, ''),
            'courseid' => new external_value(PARAM_INT, 'Course ID (0 for all courses)', VALUE_DEFAULT, 0),
            'sort' => new external_value(PARAM_TEXT, 'Sort field', VALUE_DEFAULT, 'lastname'),
            'order' => new external_value(PARAM_TEXT, 'Sort order: ASC or DESC', VALUE_DEFAULT, 'ASC'),
            'page' => new external_value(PARAM_INT, 'Page number (0-based)', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Results per page', VALUE_DEFAULT, 25),
            'search' => new external_value(PARAM_TEXT, 'Search query', VALUE_DEFAULT, ''),
            'engagement_level' => new external_value(PARAM_TEXT, 'Filter: high, medium, low, at_risk, or empty',
                VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Get paginated student list with metrics.
     *
     * @param string $schoolcode School code filter.
     * @param int $courseid Course filter.
     * @param string $sort Sort field.
     * @param string $order Sort order.
     * @param int $page Page number.
     * @param int $perpage Per page count.
     * @param string $search Search query.
     * @param string $engagementlevel Engagement level filter.
     * @return array Paginated student list.
     */
    public static function get_student_list(string $schoolcode = '', int $courseid = 0,
            string $sort = 'lastname', string $order = 'ASC', int $page = 0, int $perpage = 25,
            string $search = '', string $engagementlevel = ''): array {
        global $DB;

        $params = self::validate_parameters(
            self::get_student_list_parameters(),
            [
                'school_code' => $schoolcode,
                'courseid' => $courseid,
                'sort' => $sort,
                'order' => $order,
                'page' => $page,
                'perpage' => $perpage,
                'search' => $search,
                'engagement_level' => $engagementlevel,
            ]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:viewreports', $context);

        // Sanitize sort parameters.
        $allowedsorts = ['lastname', 'firstname', 'active_days', 'total_actions',
            'quizzes_avg_score', 'course_progress', 'last_access'];
        $sort = in_array($params['sort'], $allowedsorts) ? $params['sort'] : 'lastname';
        $order = strtoupper($params['order']) === 'DESC' ? 'DESC' : 'ASC';
        $page = max(0, $params['page']);
        $perpage = min(100, max(1, $params['perpage']));

        $weekstart = \local_elby_dashboard\metrics_calculator::get_current_week_start();

        // Build query.
        $select = "SELECT u.id AS userid, u.firstname, u.lastname,
                          su.sdms_id, st.program,
                          u.lastaccess AS last_access,
                          COALESCE(um.active_days, 0) AS active_days,
                          COALESCE(um.total_actions, 0) AS total_actions,
                          um.quizzes_avg_score,
                          um.course_progress,
                          sch2.school_name,
                          sch2.school_code,
                          CASE
                              WHEN u.lastaccess < :atriskthreshold THEN 'at_risk'
                              WHEN u.lastaccess IS NULL THEN 'at_risk'
                              ELSE 'active'
                          END AS status";

        $from = " FROM {user} u
                  JOIN {elby_sdms_users} su ON su.userid = u.id AND su.user_type = 'student'
                  LEFT JOIN {elby_students} st ON st.sdms_userid = su.id
                  LEFT JOIN {elby_schools} sch2 ON sch2.id = su.schoolid
                  LEFT JOIN {elby_user_metrics} um ON um.userid = u.id
                      AND um.period_start = :weekstart AND um.period_type = 'weekly'";

        $where = " WHERE u.deleted = 0";
        $sqlparams = [
            'atriskthreshold' => time() - (7 * 86400),
            'weekstart' => $weekstart,
        ];

        // Course filter.
        if ($params['courseid'] > 0) {
            $from .= " AND um.courseid = :courseid";
            $sqlparams['courseid'] = $params['courseid'];
        }

        // School filter.
        if (!empty($params['school_code'])) {
            $where .= " AND sch2.school_code = :schoolcode";
            $sqlparams['schoolcode'] = $params['school_code'];
        }

        // Search filter.
        if (!empty($params['search'])) {
            $searchterm = '%' . $DB->sql_like_escape($params['search']) . '%';
            $where .= " AND (" . $DB->sql_like('u.firstname', ':search1', false) .
                       " OR " . $DB->sql_like('u.lastname', ':search2', false) .
                       " OR " . $DB->sql_like('su.sdms_id', ':search3', false) . ")";
            $sqlparams['search1'] = $searchterm;
            $sqlparams['search2'] = $searchterm;
            $sqlparams['search3'] = $searchterm;
        }

        // Engagement level filter.
        if (!empty($params['engagement_level'])) {
            if ($params['engagement_level'] === 'at_risk') {
                $where .= " AND (u.lastaccess < :engthreshold OR u.lastaccess IS NULL)";
                $sqlparams['engthreshold'] = time() - (7 * 86400);
            } else if ($params['engagement_level'] === 'high') {
                $where .= " AND COALESCE(um.total_actions, 0) > 50";
            } else if ($params['engagement_level'] === 'medium') {
                $where .= " AND COALESCE(um.total_actions, 0) BETWEEN 10 AND 50";
            } else if ($params['engagement_level'] === 'low') {
                $where .= " AND COALESCE(um.total_actions, 0) > 0 AND COALESCE(um.total_actions, 0) < 10";
            }
        }

        // Map sort fields that come from joins.
        $sortmap = [
            'lastname' => 'u.lastname',
            'firstname' => 'u.firstname',
            'active_days' => 'active_days',
            'total_actions' => 'total_actions',
            'quizzes_avg_score' => 'um.quizzes_avg_score',
            'course_progress' => 'um.course_progress',
            'last_access' => 'u.lastaccess',
        ];
        $sortfield = $sortmap[$sort] ?? 'u.lastname';

        $orderby = " ORDER BY {$sortfield} {$order}";

        // Count total.
        $countsql = "SELECT COUNT(DISTINCT u.id)" . $from . $where;
        $totalcount = $DB->count_records_sql($countsql, $sqlparams);

        // Fetch paginated results.
        $groupby = " GROUP BY u.id, u.firstname, u.lastname, su.sdms_id, st.program,
                     u.lastaccess, um.active_days, um.total_actions,
                     um.quizzes_avg_score, um.course_progress,
                     sch2.school_name, sch2.school_code";
        $sql = $select . $from . $where . $groupby . $orderby;
        $records = $DB->get_records_sql($sql, $sqlparams, $page * $perpage, $perpage);

        $students = [];
        foreach ($records as $rec) {
            $students[] = [
                'userid' => (int) $rec->userid,
                'fullname' => $rec->firstname . ' ' . $rec->lastname,
                'sdms_id' => $rec->sdms_id ?? '',
                'program' => $rec->program ?? '',
                'school_name' => $rec->school_name ?? '',
                'school_code' => $rec->school_code ?? '',
                'last_access' => (int) ($rec->last_access ?? 0),
                'active_days' => (int) $rec->active_days,
                'total_actions' => (int) $rec->total_actions,
                'quizzes_avg_score' => $rec->quizzes_avg_score !== null ? (float) $rec->quizzes_avg_score : null,
                'course_progress' => $rec->course_progress !== null ? (float) $rec->course_progress : null,
                'status' => $rec->status,
            ];
        }

        return [
            'students' => $students,
            'total_count' => $totalcount,
            'page' => $page,
            'perpage' => $perpage,
        ];
    }

    /**
     * Return structure for get_student_list.
     */
    public static function get_student_list_returns(): external_single_structure {
        return new external_single_structure([
            'students' => new external_multiple_structure(
                new external_single_structure([
                    'userid' => new external_value(PARAM_INT, 'Moodle user ID'),
                    'fullname' => new external_value(PARAM_TEXT, 'Full name'),
                    'sdms_id' => new external_value(PARAM_TEXT, 'SDMS ID'),
                    'program' => new external_value(PARAM_TEXT, 'Program name'),
                    'school_name' => new external_value(PARAM_TEXT, 'School name'),
                    'school_code' => new external_value(PARAM_TEXT, 'School code'),
                    'last_access' => new external_value(PARAM_INT, 'Last access timestamp'),
                    'active_days' => new external_value(PARAM_INT, 'Active days this week'),
                    'total_actions' => new external_value(PARAM_INT, 'Total actions this week'),
                    'quizzes_avg_score' => new external_value(PARAM_FLOAT, 'Average quiz score', VALUE_OPTIONAL),
                    'course_progress' => new external_value(PARAM_FLOAT, 'Course progress percentage', VALUE_OPTIONAL),
                    'status' => new external_value(PARAM_TEXT, 'Status: active or at_risk'),
                ])
            ),
            'total_count' => new external_value(PARAM_INT, 'Total matching records'),
            'page' => new external_value(PARAM_INT, 'Current page'),
            'perpage' => new external_value(PARAM_INT, 'Results per page'),
        ]);
    }

    // =========================================================================
    // get_engagement_distribution — Engagement breakdown for a school.
    // =========================================================================

    /**
     * Parameters for get_engagement_distribution.
     */
    public static function get_engagement_distribution_parameters(): external_function_parameters {
        return new external_function_parameters([
            'school_code' => new external_value(PARAM_TEXT, 'SDMS school code'),
            'courseid' => new external_value(PARAM_INT, 'Course ID (0 for school-wide)', VALUE_DEFAULT, 0),
            'period_type' => new external_value(PARAM_TEXT, 'Period type: weekly or monthly', VALUE_DEFAULT, 'weekly'),
        ]);
    }

    /**
     * Get engagement distribution for a school.
     *
     * @param string $schoolcode SDMS school code.
     * @param int $courseid Course ID (0 for school-wide).
     * @param string $periodtype Period type.
     * @return array Engagement distribution data.
     */
    public static function get_engagement_distribution(string $schoolcode, int $courseid = 0,
            string $periodtype = 'weekly'): array {
        global $DB;

        $params = self::validate_parameters(
            self::get_engagement_distribution_parameters(),
            ['school_code' => $schoolcode, 'courseid' => $courseid, 'period_type' => $periodtype]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:viewreports', $context);

        $school = $DB->get_record('elby_schools', ['school_code' => $params['school_code']], 'id');
        if (!$school) {
            return [
                'high_engagement_count' => 0,
                'medium_engagement_count' => 0,
                'low_engagement_count' => 0,
                'at_risk_count' => 0,
                'total_enrolled' => 0,
            ];
        }

        // Get the most recent metrics record.
        $metrics = $DB->get_records_sql(
            "SELECT high_engagement_count, medium_engagement_count, low_engagement_count,
                    at_risk_count, total_enrolled
             FROM {elby_school_metrics}
             WHERE schoolid = :schoolid
               AND courseid = :courseid
               AND period_type = :periodtype
             ORDER BY period_start DESC
             LIMIT 1",
            [
                'schoolid' => $school->id,
                'courseid' => $params['courseid'],
                'periodtype' => $params['period_type'],
            ]
        );

        $metrics = reset($metrics);

        if (!$metrics) {
            return [
                'high_engagement_count' => 0,
                'medium_engagement_count' => 0,
                'low_engagement_count' => 0,
                'at_risk_count' => 0,
                'total_enrolled' => 0,
            ];
        }

        return [
            'high_engagement_count' => (int) $metrics->high_engagement_count,
            'medium_engagement_count' => (int) $metrics->medium_engagement_count,
            'low_engagement_count' => (int) $metrics->low_engagement_count,
            'at_risk_count' => (int) $metrics->at_risk_count,
            'total_enrolled' => (int) $metrics->total_enrolled,
        ];
    }

    /**
     * Return structure for get_engagement_distribution.
     */
    public static function get_engagement_distribution_returns(): external_single_structure {
        return new external_single_structure([
            'high_engagement_count' => new external_value(PARAM_INT, 'High engagement count'),
            'medium_engagement_count' => new external_value(PARAM_INT, 'Medium engagement count'),
            'low_engagement_count' => new external_value(PARAM_INT, 'Low engagement count'),
            'at_risk_count' => new external_value(PARAM_INT, 'At-risk count'),
            'total_enrolled' => new external_value(PARAM_INT, 'Total enrolled'),
        ]);
    }
}
