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
 * Library functions for local_rtbdashboard.
 *
 * @package    local_rtbdashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add nodes to the global navigation.
 *
 * @param global_navigation $navigation
 */
function local_rtbdashboard_extend_navigation(global_navigation $navigation) {
    global $CFG;

    // Add RTB Dashboard to the custom menu (appears in "More" menu in Moodle 5.1).
    $dashboardurl = new moodle_url('/local/rtbdashboard/index.php');
    $menuitem = get_string('pluginname', 'local_rtbdashboard') . '|' . $dashboardurl->out();

    if (empty($CFG->custommenuitems)) {
        $CFG->custommenuitems = '';
    }

    // Only add if not already present.
    if (strpos($CFG->custommenuitems, '/local/rtbdashboard/index.php') === false) {
        $CFG->custommenuitems = trim($CFG->custommenuitems) . "\n" . $menuitem;
    }

    // Also add to secondary navigation (navigation drawer).
    $node = $navigation->add(
        get_string('pluginname', 'local_rtbdashboard'),
        $dashboardurl,
        navigation_node::TYPE_CUSTOM,
        null,
        'rtbdashboard',
        new pix_icon('i/dashboard', get_string('pluginname', 'local_rtbdashboard'))
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
function local_rtbdashboard_extend_settings_navigation(settings_navigation $navigation, context $context) {
    global $PAGE;

    // Only add admin link if user has site config capability.
    $systemcontext = context_system::instance();
    if (has_capability('moodle/site:config', $systemcontext)) {
        // Try to add to site administration if available.
        if ($settingnode = $navigation->find('siteadministration', navigation_node::TYPE_SITE_ADMIN)) {
            $adminurl = new moodle_url('/local/rtbdashboard/admin/index.php');
            $adminnode = navigation_node::create(
                get_string('nav_admin', 'local_rtbdashboard'),
                $adminurl,
                navigation_node::TYPE_SETTING,
                null,
                'rebdashboard_admin',
                new pix_icon('i/settings', get_string('nav_admin', 'local_rtbdashboard'))
            );
            $settingnode->add_node($adminnode);
        }
    }
}
