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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->dirroot/enrol/meta/locallib.php");
require_once("$CFG->dirroot/local/bulkmeta/manage_form.php");
require_once("$CFG->dirroot/local/bulkmeta/locallib.php");

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

$pageurl = new moodle_url('/local/bulkmeta/manage.php');
$pageurl->param('id', $course->id);

$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');

navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id' => $course->id)));

require_login($course);
require_capability('moodle/course:enrolconfig', $context);

// If not enabled redirect enrolment management page
if (!enrol_is_enabled('meta')) {
    notice(get_string('notenabled', 'local_bulkmeta'), new moodle_url('/admin/settings.php?section=manageenrols'));
}

if (optional_param('links_clearbutton', 0, PARAM_RAW) && confirm_sesskey()) {
    redirect($pageurl);
}

// get the course meta link enrolment plugin
$enrol = enrol_get_plugin('meta');

if (!$enrol->get_newinstance_link($course->id)) {
    redirect(new moodle_url('/enrol/instances.php', array('id' => $course->id, '')));
}

$mform = new bulkmeta_manage_form($pageurl->out(false), array('course' => $course));
// redirect to instance page on cancel
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/enrol/instances.php', array('id' => $course->id)));
}
// handle add and removes
if ($mform->is_submitted()) {
    $data = $mform->get_data();
    // process courses to be linked
    if (isset($data->bulkmeta_addbutton) && !empty($data->bulkmeta_link)) {
        foreach ($data->bulkmeta_link as $courseidtolink) {
            if (!empty($courseidtolink)) { // because of formlib selectgroups
                $enrol->add_instance($course, array('customint1' => $courseidtolink));
            }
        }
        enrol_meta_sync($course->id);
        redirect(new moodle_url('/local/bulkmeta/manage.php', array('id' => $course->id)));
    }
    // process courses to be unlinked
    if (isset($data->bulkmeta_removebutton) && !empty($data->bulkmeta_unlink)) {
        list($insql, $inparams) = $DB->get_in_or_equal($data->bulkmeta_unlink, SQL_PARAMS_NAMED);
        $params = array_merge(array('courseid' => $data->id), $inparams);
        $instances = $DB->get_records_select('enrol', 
                                             "enrol = 'meta' AND courseid = :courseid AND customint1 ". $insql,
                                             $params);
        foreach ($instances as $instance) {
            $enrol->delete_instance($instance);
        }
        enrol_meta_sync($course->id);
        redirect(new moodle_url('/local/bulkmeta/manage.php', array('id' => $course->id)));
    }
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'local_bulkmeta'));
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
