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

define('AJAX_SCRIPT', true);

/**
 * AJAX search handler
 *
 * @package    local_bulkmeta
 * @copyright  2014 Troy Williams
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/enrol/meta/locallib.php');
require_once($CFG->dirroot . '/local/bulkmeta/locallib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/bulkmeta/search.ajax.php');

echo $OUTPUT->header();

// Check access.
require_login();
require_sesskey();

$id         = required_param('id', PARAM_INT); // Get the course identifier parameter.
$searchtext = required_param('searchtext', PARAM_TEXT); // Get the search parameter.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$result = local_bulkmeta_manager::search($course->id, $searchtext);

echo json_encode(array('result' => $result));
