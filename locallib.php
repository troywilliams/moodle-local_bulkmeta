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

class local_bulkmeta_manager {

    const MAX_RESULTS = 100;

    public static function linked_courses($courseid) {
        global $DB;

        $result = new stdClass();
        $result->matches = 0;
        $result->results = array();

        // Build count sql.
        $countsql = "SELECT
                      COUNT(1)
                       FROM {course} c
                      WHERE c.id IN (SELECT e.customint1
                                       FROM {enrol} e
                                      WHERE e.enrol = 'meta'
                                        AND e.courseid = :courseid)";

        // Build select sql.
        $selectsql = "SELECT c.id, c.shortname, c.fullname, c.visible
                        FROM {course} c
                       WHERE c.id IN (SELECT e.customint1
                                        FROM {enrol} e
                                       WHERE e.enrol = 'meta'
                                         AND e.courseid = :courseid)
                    ORDER BY c.shortname ASC";

        // Add courseid to params.
        $params = array('courseid' => $courseid);

        $result->matches = $DB->count_records_sql($countsql, $params);
        if ($result->matches) {

            $result->label = get_string('metalinkedcourses',
                                        'local_bulkmeta', $result->matches);

            // Fetch records.
            $courses = $DB->get_records_sql($selectsql, $params);
            foreach ($courses as $c) {
                $result->results[$c->id] = shorten_text(format_string($c->fullname), 80, true);
            }

        } else {

            $result->label = get_string('nometalinkedcourses',
                                        'local_bulkmeta');
        }

        return $result;
    }

    public static function search($courseid, $query) {
        global $DB;

        $result = new stdClass();
        $result->label = '';
        $result->query = $query;
        $result->maxlimit = self::MAX_RESULTS;
        $result->matches = 0;
        $result->results = array();

        // Build sql for excluded courses.
        $existing = $DB->get_records('enrol', array('enrol' => 'meta', 'courseid' => $courseid), '', 'customint1, id');
        $excludes = array_merge(array_keys($existing), array(SITEID, $courseid));
        list($excludesql, $excludeparams) = $DB->get_in_or_equal($excludes, SQL_PARAMS_NAMED, 'ex', false);
        $excludesql = 'c.id '.$excludesql;

        // Build search sql.
        $searchsql = '';
        $searchparams = array();
        if (!empty($query)) {
            $searchanywhere = get_user_preferences('bulkmeta_option_searchanywhere', false);
            if ($searchanywhere) {
                $query = '%' . $query . '%';
            } else {
                $query = $query . '%';
            }
            $searchfields = array('c.shortname', 'c.fullname');
            if (get_user_preferences('bulkmeta_option_idnumber', false)) {
                $searchfields[] = 'c.idnumber';
            }
            if (get_user_preferences('bulkmeta_option_summary', false)) {
                $searchfields[] = 'c.summary';
            }
            for ($i = 0; $i < count($searchfields); $i++) {
                $searchlikes[$i] = $DB->sql_like($searchfields[$i], ":s{$i}", false, false);
                $searchparams["s{$i}"] = $query;
            }
            $searchsql = ' AND (' .implode(' OR ', $searchlikes).')';
        }

        // Put all the params together.
        $params = array_merge(array('contextlevel' => CONTEXT_COURSE), $excludeparams, $searchparams);

        // Build count statement.
        $countsql = "SELECT
                      COUNT(1)
                       FROM {course} c
                      WHERE $excludesql $searchsql";

        // Get the raw count.
        $result->matches = $DB->count_records_sql($countsql, $params);

        // Gravy?
        if ($result->matches <= $result->maxlimit) {

            $sql = "SELECT c.id, c.shortname, c.fullname, c.visible
                      FROM {course} c
                 LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                     WHERE $excludesql $searchsql
                  ORDER BY c.shortname ASC";

            // Fetch records, check if current user has valid permissions.
            $courses = $DB->get_records_sql($sql, $params, 0, $result->maxlimit);
            foreach ($courses as $c) {
                $coursecontext = context_course::instance($c->id);
                if (!$c->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                    $result->removed++;
                    continue;
                }
                if (!has_capability('enrol/meta:selectaslinked', $coursecontext)) {
                    $result->removed++;
                    continue;
                }
                foreach ($courses as $c) {
                    $result->results[$c->id] = shorten_text(format_string($c->fullname), 80, true);
                }
            }
        }

        // Build the label.
        if ($result->matches > $result->maxlimit) {
            if ($result->query) {
                $result->label = get_string('toomanycoursesmatchsearch', 'local_bulkmeta', $result);
            } else {
                $result->label = get_string('toomanycoursestoshow', 'local_bulkmeta', $result->matches);
            }
        } else {
            if (!empty($result->removed)) {
                $result->label = get_string('coursesmatchingsearchremoved', 'local_bulkmeta', $result);
            } else {
                $result->label = get_string('coursesmatchingsearch', 'local_bulkmeta', $result->matches);
            }
        }

        return $result;
    }

}
