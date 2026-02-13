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
 * Upgrade steps for local_elby_dashboard.
 *
 * @package    local_elby_dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_elby_dashboard_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026021301) {
        // Create all SDMS integration tables from install.xml.
        // Load the XMLDB schema and create any tables that don't exist yet.
        $xmldbfile = new xmldb_file($CFG->dirroot . '/local/elby_dashboard/db/install.xml');
        $xmldbfile->loadXMLStructure();
        $structure = $xmldbfile->getStructure();
        $tables = $structure->getTables();

        foreach ($tables as $table) {
            if (!$dbman->table_exists($table)) {
                $dbman->create_table($table);
            }
        }

        upgrade_plugin_savepoint(true, 2026021301, 'local', 'elby_dashboard');
    }

    return true;
}
