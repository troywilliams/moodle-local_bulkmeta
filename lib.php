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

defined('MOODLE_INTERNAL') || die();

/**
 * Libray code.
 *
 * @package    local_bulkmeta
 * @copyright  2014 Troy Williams
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This function extends the settings navigation block, if in a course
 * and have correct permissions a link to bulk meta course linkage page
 * will be added.
 *
 * @global type $SITE
 * @param type $navigation
 * @param context_course $context
 * @return type
 */
function local_bulkmeta_extend_settings_navigation($navigation, $context) {
    global $SITE;

    if (!isloggedin()) {
        return;
    }

    if (is_null($navigation) or is_null($context)) {
        return;
    }

    if ($context->instanceid === $SITE->id) {
        return;
    }

    if (!enrol_is_enabled('meta')) {
        return;
    }

    if (!has_capability('enrol/meta:config', $context)) {
        return;
    }

    // Only add link when in the context of a course.
    if ($context instanceof context_course) {
        $courseadmin = $navigation->get('courseadmin');
        $users = $courseadmin->get('users');
        if ($users) {
            $url = new moodle_url('/local/bulkmeta/manage.php', array('id' => $context->instanceid));
            $users->add(get_string('pluginname', 'local_bulkmeta'), $url, navigation_node::TYPE_CUSTOM,
                        null, 'localbulkmeta', new pix_icon('i/enrolusers', ''));
        }
    }

}
