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
 * Form library code.
 *
 * @package    local_bulkmeta
 * @copyright  2014 Troy Williams
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');

class bulkmeta_manage_form extends moodleform {

    protected $course;

    public function definition() {
        global $PAGE;

        $searchtext = optional_param('bulkmeta_searchtext', '', PARAM_TEXT);

        $mform  = $this->_form;
        $course = $this->_customdata['course'];
        $this->course = $course;

        $mform->disable_form_change_checker();

        $mform->addElement('html', html_writer::tag('h1', get_string('pluginname', 'local_bulkmeta')));

        $currentlylinked = local_bulkmeta_manager::linked_courses($course->id);
        $linkedcoursesdata = array($currentlylinked->label => $currentlylinked->results);

        $mform->addElement('selectgroups', 'bulkmeta_unlink', get_string('linkedcourses', 'local_bulkmeta'), $linkedcoursesdata,
                           array('size' => 10, 'multiple' => true));
        $mform->addElement('submit', 'bulkmeta_removebutton', get_string('unlinkselected', 'local_bulkmeta'));

        $mform->addElement('html', html_writer::empty_tag('br'));

        $found = local_bulkmeta_manager::search($course->id, $searchtext);
        $foundcoursesdata = array($found->label => $found->results);

        $mform->addElement('selectgroups', 'bulkmeta_link', '', $foundcoursesdata, array('size' => 10, 'multiple' => true));

        $searchgroup = array();
        $searchgroup[] = $mform->createElement('text', 'bulkmeta_searchtext');
        $mform->setType('bulkmeta_searchtext', PARAM_TEXT);
        $searchgroup[] = $mform->createElement('submit', 'bulkmeta_searchbutton', get_string('search'));
        $mform->registerNoSubmitButton('bulkmeta_searchbutton');
        $searchgroup[] = $mform->createElement('submit', 'bulkmeta_clearbutton', get_string('clear'));
        $mform->registerNoSubmitButton('bulkmeta_clearbutton');
        $searchgroup[] = $mform->createElement('submit', 'bulkmeta_addbutton', get_string('linkselected', 'local_bulkmeta'));
        $mform->addGroup($searchgroup, 'searchgroup', get_string('search') , array(''), false);

        $mform->addElement('checkbox', 'bulkmeta_option_searchanywhere', get_string('searchanywhere', 'local_bulkmeta'));
        user_preference_allow_ajax_update('bulkmeta_option_searchanywhere', 'bool');
        $searchanywhere = get_user_preferences('bulkmeta_option_searchanywhere', false);
        $this->set_data(array('bulkmeta_option_searchanywhere' => $searchanywhere));

        $mform->addElement('checkbox', 'bulkmeta_option_idnumber',
                           get_string('includeinsearch', 'local_bulkmeta', get_string('idnumbercourse')));

        user_preference_allow_ajax_update('bulkmeta_option_idnumber', 'bool');
        $includeidnumber = get_user_preferences('bulkmeta_option_idnumber', false);
        $this->set_data(array('bulkmeta_option_idnumber' => $includeidnumber));

        $mform->addElement('checkbox', 'bulkmeta_option_summary',
                           get_string('includeinsearch', 'local_bulkmeta', get_string('coursesummary')));

        user_preference_allow_ajax_update('bulkmeta_option_summary', 'bool');
        $includesummary = get_user_preferences('bulkmeta_option_summary', false);
        $this->set_data(array('bulkmeta_option_summary' => $includesummary));

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        $this->set_data(array('id' => $course->id));

        $cancellink = html_writer::link(new moodle_url('/enrol/instances.php', array('id' => $course->id)), get_string('cancel'));
        $mform->addElement('static', 'cancel', $cancellink);
        $mform->closeHeaderBefore('cancel');

        $PAGE->requires->yui_module('moodle-local_bulkmeta-selector',
                                    'M.local_bulkmeta.selector.init',
                                    array($course->id, 'bulkmeta'));

    }

    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        return $errors;
    }

}
