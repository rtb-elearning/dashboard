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
 * Library functions for local_elby_dashboard.
 *
 * @package    local_elby_dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add nodes to the global navigation.
 *
 * @param global_navigation $navigation
 */
function local_elby_dashboard_extend_navigation(global_navigation $navigation) {
    global $CFG;

    // Add Elby Dashboard to the custom menu (appears in "More" menu in Moodle 5.1).
    $dashboardurl = new moodle_url('/local/elby_dashboard/index.php');
    $menuitem = get_string('pluginname', 'local_elby_dashboard') . '|' . $dashboardurl->out();

    if (empty($CFG->custommenuitems)) {
        $CFG->custommenuitems = '';
    }

    // Only add if not already present.
    if (strpos($CFG->custommenuitems, '/local/elby_dashboard/index.php') === false) {
        $CFG->custommenuitems = trim($CFG->custommenuitems) . "\n" . $menuitem;
    }

    // Also add to secondary navigation (navigation drawer).
    $node = $navigation->add(
        get_string('pluginname', 'local_elby_dashboard'),
        $dashboardurl,
        navigation_node::TYPE_CUSTOM,
        null,
        'elby_dashboard',
        new pix_icon('i/dashboard', get_string('pluginname', 'local_elby_dashboard'))
    );

    // Make it visible in the navigation drawer.
    $node->showinflatnavigation = true;
}

/**
 * Add nodes to the settings navigation.
 *
 * @param settings_navigation $navigation
 * @param context $context
 */
function local_elby_dashboard_extend_settings_navigation(settings_navigation $navigation, context $context) {
    global $PAGE;

    // Only add admin link if user has site config capability.
    $systemcontext = context_system::instance();
    if (has_capability('moodle/site:config', $systemcontext)) {
        // Try to add to site administration if available.
        if ($settingnode = $navigation->find('siteadministration', navigation_node::TYPE_SITE_ADMIN)) {
            $adminurl = new moodle_url('/local/elby_dashboard/admin/index.php');
            $adminnode = navigation_node::create(
                get_string('nav_admin', 'local_elby_dashboard'),
                $adminurl,
                navigation_node::TYPE_SETTING,
                null,
                'elby_dashboard_admin',
                new pix_icon('i/settings', get_string('nav_admin', 'local_elby_dashboard'))
            );
            $settingnode->add_node($adminnode);
        }
    }
}

/**
 * Serve plugin files.
 *
 * @param stdClass $course Course object
 * @param stdClass $cm Course module object
 * @param context $context Context object
 * @param string $filearea File area
 * @param array $args Arguments
 * @param bool $forcedownload Force download
 * @param array $options Additional options
 * @return bool|void
 */
function local_elby_dashboard_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    // Valid file areas for this plugin.
    $validareas = ['logo'];

    if (!in_array($filearea, $validareas)) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $filename = array_pop($args);
    $itemid = 0;

    $file = $fs->get_file($context->id, 'local_elby_dashboard', $filearea, $itemid, '/', $filename);

    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
