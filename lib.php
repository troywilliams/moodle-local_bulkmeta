<?php

/**
 *
 * @global type $SITE
 * @param type $navigation
 * @param context_course $context
 * @return type
 */
function local_bulkmeta_extends_settings_navigation($navigation, $context) {
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

    // Only add link when in the context of a course
    if ($context instanceof context_course) {
         $courseadmin = $navigation->get('courseadmin');
         $users = $courseadmin->get('users');
         if ($users ) {
             $url = new moodle_url('/local/bulkmeta/manage.php', array('id' => $context->instanceid));
             $users->add(get_string('pluginname', 'local_bulkmeta'), $url, navigation_node::TYPE_CUSTOM, $shorttext=null, 'localbulkmeta', new pix_icon('i/enrolusers', ''));
         }
    }
    
}