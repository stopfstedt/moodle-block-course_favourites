<?php
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
 * Course favourites block main block class.
 *
 * @package   blocks-course_favourites
 * @copyright &copy; 2014 The Regents of the University of California
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Carson Tam <carson.tam@ucsf.edu>, Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/blocks/course_favourites/lib.php');

class block_course_favourites extends block_list {
    function init() {
        $this->title   = get_string('course_favourites', 'block_course_favourites');
    }

    function get_content() {
        global $CFG, $DB, $USER, $COURSE, $OUTPUT;

        if ($this->content !== NULL) {
          return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->items  = array();
        $this->content->icons  = array();
        $this->content->footer = '';

        if (!isloggedin() or isguestuser()) {
            return $this->content;
        }

        $icon  = $OUTPUT->pix_icon('course_favourites', get_string('coursecategory'), 'block_course_favourites');

        $sql = "SELECT ra.id
                FROM {$CFG->prefix}role_assignments ra
                INNER JOIN {$CFG->prefix}context ctx ON ra.contextid = ctx.id
                WHERE ctx.contextlevel = " . CONTEXT_COURSE . "
                AND ra.userid = {$USER->id}";

        // Verify if the user has a role in any course
        if (!empty($CFG->block_course_favourites_musthaverole) && !$DB->record_exists_sql($sql)) {

            $this->content->items[] = get_string('nocoursesforyou', 'block_course_favourites');
            $this->content->footer = '<a href="' . $CFG->wwwroot . '/blocks/course_favourites/usersettings.php?' .
                                     'courseid=' . $COURSE->id . '">' . get_string('settings', 'block_course_favourites') .
                                     '</a>';
            $this->content->icons[] = '';
        } else {

            $noselection = true;

            // Verify further whether the user has created their favourites list
            if (($sortorder = $DB->get_field('block_course_favourites', 'sortorder', array('userid' => $USER->id)))) {
                $noselection = false;

                // Print list of courses work done here.....
                $crsfavs = get_user_fav_courses($USER->id);
                $class  = '';

                if (!empty($crsfavs)) {
                    foreach ($crsfavs as $crsfav) {
                        if ($crsfav->visible) {
                            $class = '';
                        } else {
                            $class = 'class="dimmed"';
                        }

                        $this->content->items[] = '<a ' . $class . ' title="' . $crsfav->shortname . '" '.
                                                  'href="' . $CFG->wwwroot . '/course/view.php?id=' .
                                                  $crsfav->id . '">' . $icon . format_string($crsfav->fullname) . '</a>';
                    }
                }

                $this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/course_favourites/usersettings.php?' .
                                         'courseid=' . $COURSE->id . '">' . get_string('settings', 'block_course_favourites') .
                                         '</a>';
            }

            // print intro/help message if no selection has been created by the user
            if ($noselection) {
                $this->content->items[] = get_string('noselecedcoursesforyou', 'block_course_favourites');
                $this->content->icons[] = '';

                $this->content->footer = '<a href="' . $CFG->wwwroot . '/blocks/course_favourites/usersettings.php?' .
                                         'courseid=' . $COURSE->id . '">' . get_string('settings', 'block_course_favourites') .
                                         '</a>';
            }
        }

        if (!empty($footer)) {
            $this->content->footer = $footer;
        }

        return $this->content;
    }

    function has_config() {
      return true;
    }

    /**
     * Which page types this block may appear on.
     *
     * @return array page-type prefix => true/false.
     */
    function applicable_formats() {
      return array('all' => true);
    }


    public function instance_allow_multiple() {
      return false;
    }
}
