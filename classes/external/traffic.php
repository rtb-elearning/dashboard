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
 * External API for platform traffic data.
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
 * External API for platform traffic data.
 */
class traffic extends external_api {

    /**
     * Parameters for get_platform_traffic.
     */
    public static function get_platform_traffic_parameters(): external_function_parameters {
        return new external_function_parameters([
            'period' => new external_value(PARAM_TEXT, 'Period: daily, weekly, or monthly', VALUE_DEFAULT, 'daily'),
            'days_back' => new external_value(PARAM_INT, 'Number of days to look back (max 365)', VALUE_DEFAULT, 30),
            'from_date' => new external_value(PARAM_INT, 'Start timestamp (overrides days_back when > 0)', VALUE_DEFAULT, 0),
            'to_date' => new external_value(PARAM_INT, 'End timestamp (defaults to now)', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Get platform traffic data grouped by period.
     *
     * @param string $period Period grouping: daily, weekly, or monthly.
     * @param int $daysback Number of days to look back.
     * @param int $fromdate Start timestamp (overrides days_back when > 0).
     * @param int $todate End timestamp (defaults to now).
     * @return array Traffic data.
     */
    public static function get_platform_traffic(
        string $period = 'daily',
        int $daysback = 30,
        int $fromdate = 0,
        int $todate = 0
    ): array {
        global $DB, $CFG;

        $params = self::validate_parameters(
            self::get_platform_traffic_parameters(),
            ['period' => $period, 'days_back' => $daysback, 'from_date' => $fromdate, 'to_date' => $todate]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:viewreports', $context);

        $period = in_array($params['period'], ['daily', 'weekly', 'monthly']) ? $params['period'] : 'daily';

        if ($params['from_date'] > 0) {
            $starttime = $params['from_date'];
            $endtime = $params['to_date'] > 0 ? $params['to_date'] : time();
        } else {
            $daysback = min(365, max(1, $params['days_back']));
            $starttime = time() - ($daysback * 86400);
            $endtime = time();
        }

        // Build DB-family-specific date formatting.
        $dbfamily = $DB->get_dbfamily();
        if ($dbfamily === 'postgres') {
            switch ($period) {
                case 'weekly':
                    $dateexpr = "TO_CHAR(DATE_TRUNC('week', TO_TIMESTAMP(timecreated)), 'YYYY-MM-DD')";
                    break;
                case 'monthly':
                    $dateexpr = "TO_CHAR(DATE_TRUNC('month', TO_TIMESTAMP(timecreated)), 'YYYY-MM')";
                    break;
                default: // daily.
                    $dateexpr = "TO_CHAR(TO_TIMESTAMP(timecreated), 'YYYY-MM-DD')";
            }
        } else {
            // MariaDB / MySQL.
            switch ($period) {
                case 'weekly':
                    $dateexpr = "DATE_FORMAT(FROM_UNIXTIME(timecreated), '%x-W%v')";
                    break;
                case 'monthly':
                    $dateexpr = "DATE_FORMAT(FROM_UNIXTIME(timecreated), '%Y-%m')";
                    break;
                default: // daily.
                    $dateexpr = "DATE_FORMAT(FROM_UNIXTIME(timecreated), '%Y-%m-%d')";
            }
        }

        $sql = "SELECT {$dateexpr} AS period_label,
                       COUNT(*) AS total_actions,
                       COUNT(DISTINCT userid) AS unique_users,
                       MIN(timecreated) AS period_start
                FROM {logstore_standard_log}
                WHERE timecreated >= :starttime AND timecreated <= :endtime AND userid > 0 AND anonymous = 0
                GROUP BY period_label
                ORDER BY period_start ASC";

        $records = $DB->get_records_sql($sql, ['starttime' => $starttime, 'endtime' => $endtime]);

        $data = [];
        foreach ($records as $rec) {
            $data[] = [
                'period_label' => $rec->period_label,
                'total_actions' => (int) $rec->total_actions,
                'unique_users' => (int) $rec->unique_users,
                'period_start' => (int) $rec->period_start,
            ];
        }

        return ['data' => $data];
    }

    /**
     * Return structure for get_platform_traffic.
     */
    public static function get_platform_traffic_returns(): external_single_structure {
        return new external_single_structure([
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'period_label' => new external_value(PARAM_TEXT, 'Period label'),
                    'total_actions' => new external_value(PARAM_INT, 'Total actions in period'),
                    'unique_users' => new external_value(PARAM_INT, 'Unique users in period'),
                    'period_start' => new external_value(PARAM_INT, 'Period start timestamp'),
                ])
            ),
        ]);
    }
}
