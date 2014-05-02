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

require_once($CFG->libdir . '/formslib.php');

class bulkmeta_manage_form extends moodleform {
    
    const controlname = 'bulkmeta';

    protected $course;

    public function definition() {
        global $PAGE;

        $searchtext = optional_param(self::controlname .'_searchtext', '', PARAM_TEXT);

        $mform  = $this->_form;
        $course = $this->_customdata['course'];
        $this->course = $course;
        
        $mform->disable_form_change_checker();
        
        $mform->addElement('html', html_writer::tag('h1', get_string('pluginname', 'local_bulkmeta')));
      
        $currentlylinked = local_bulkmeta_manager::linked_courses($course->id);
        $linkedcoursesdata = array($currentlylinked->label => $currentlylinked->results);

        $mform->addElement('selectgroups', self::controlname . '_unlink', get_string('linkedcourses', 'local_bulkmeta'), $linkedcoursesdata,
                           array('size' => 10, 'multiple' => true));
        $mform->addElement('submit', self::controlname.'_removebutton', get_string('unlinkselected', 'local_bulkmeta'));
        
        $mform->addElement('html', html_writer::empty_tag('br'));

        $found = local_bulkmeta_manager::search($course->id, $searchtext);
        $foundcoursesdata = array($found->label => $found->results);
 
        $mform->addElement('selectgroups', self::controlname  . '_link', '', $foundcoursesdata, array('size' => 10, 'multiple' => true));
        
        $searchgroup = array();
        $searchgroup[] = $mform->createElement('text', self::controlname.'_searchtext');
        $mform->setType(self::controlname.'_searchtext', PARAM_TEXT);
        $searchgroup[] = $mform->createElement('submit', self::controlname.'_searchbutton', get_string('search'));
        $mform->registerNoSubmitButton(self::controlname.'_searchbutton');
        $searchgroup[] = $mform->createElement('submit', self::controlname.'_clearbutton', get_string('clear'));
        $mform->registerNoSubmitButton(self::controlname.'_clearbutton');
        $searchgroup[] = $mform->createElement('submit', self::controlname.'_addbutton', get_string('linkselected', 'local_bulkmeta'));
        $mform->addGroup($searchgroup, 'searchgroup', get_string('search') , array(''), false);
        
        $mform->addElement('checkbox', self::controlname.'_option_searchanywhere', get_string('searchanywhere', 'local_bulkmeta'));
        user_preference_allow_ajax_update(self::controlname.'_option_searchanywhere', 'bool');
        $searchanywhere = get_user_preferences(self::controlname.'_option_searchanywhere', false);
        $this->set_data(array(self::controlname.'_option_searchanywhere' => $searchanywhere));

        $mform->addElement('checkbox', self::controlname.'_option_idnumber',
                           get_string('includeinsearch', 'local_bulkmeta', get_string('idnumbercourse')));

        user_preference_allow_ajax_update(self::controlname.'_option_idnumber', 'bool');
        $includeidnumber= get_user_preferences(self::controlname.'_option_idnumber', false);
        $this->set_data(array(self::controlname.'_option_idnumber' => $includeidnumber));
        
        $mform->addElement('checkbox', self::controlname.'_option_summary',
                           get_string('includeinsearch', 'local_bulkmeta', get_string('coursesummary')));

        user_preference_allow_ajax_update(self::controlname.'_option_summary', 'bool');
        $includesummary= get_user_preferences(self::controlname.'_option_summary', false);
        $this->set_data(array(self::controlname.'_option_summary' => $includesummary));
        
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        $this->set_data(array('id' => $course->id));
        
        $cancellink = html_writer::link(new moodle_url('/enrol/instances.php', array('id' => $course->id)), get_string('cancel'));
        $mform->addElement('static', 'cancel', $cancellink);
        $mform->closeHeaderBefore('cancel');

        $PAGE->requires->yui_module('moodle-local_bulkmeta-selector',
                                    'M.local_bulkmeta.selector.init',
                                    array($course->id, self::controlname));

    }

    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        return $errors;
    }
    
}

?>
