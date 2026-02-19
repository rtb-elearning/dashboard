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
 * Scheduled task to auto-link users to SDMS by email.
 *
 * @package    local_elby_dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_elby_dashboard\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Daily task that links unlinked users whose email contains their SDMS code.
 */
class auto_link_by_email extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_auto_link_by_email', 'local_elby_dashboard');
    }

    /**
     * Execute the task.
     *
     * Finds users with RTB emails who are not yet linked to SDMS,
     * extracts the SDMS code from the email prefix, and links them
     * via sync_service->link_user().
     */
    public function execute(): void {
        global $DB;

        $syncservice = new \local_elby_dashboard\sync_service();

        // Find unlinked users with RTB emails.
        $sql = "SELECT u.id, u.email
                FROM {user} u
                LEFT JOIN {elby_sdms_users} su ON su.userid = u.id
                WHERE su.id IS NULL AND u.deleted = 0
                  AND (u.email LIKE :email1 OR u.email LIKE :email2)
                ORDER BY u.id ASC";
        $users = $DB->get_records_sql($sql, [
            'email1' => '%@rtb.ac.rw',
            'email2' => '%@rtb.gov.rw',
        ], 0, 200); // Process up to 200 per run.

        $linked = 0;
        $failed = 0;
        $skipped = 0;
        foreach ($users as $user) {
            $sdmscode = explode('@', $user->email)[0];
            if (!ctype_digit($sdmscode)) {
                mtrace("  Skipping user {$user->id} ({$user->email}): non-numeric email prefix.");
                $skipped++;
                continue;
            }
            try {
                // Try student first.
                $ok = $syncservice->link_user($user->id, $sdmscode, 'student');
                if (!$ok) {
                    // Try teacher/staff.
                    $ok = $syncservice->link_user($user->id, $sdmscode, 'staff');
                }
                if ($ok) {
                    $linked++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                $detail = $e->getMessage();
                if ($e instanceof \moodle_exception && !empty($e->debuginfo)) {
                    $detail .= ' [debug: ' . $e->debuginfo . ']';
                }
                mtrace("  Failed to link user {$user->id} ({$user->email}): " . $detail);
            }
        }
        mtrace("Auto-link by email: {$linked} linked, {$failed} failed, {$skipped} skipped out of " . count($users) . " unlinked users.");
    }
}
