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
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/blocks/course_favourites/lib.php');

class block_course_favourites extends block_base {
    function init() {
        $this->title   = get_string('blockname', 'block_course_favourites');
        $this->version = 2010071300;
    }

    function get_content() {
        global $CFG, $USER, $COURSE;

        if ($this->content !== NULL) {
          return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        if (!isloggedin()) {
            return $this->content;
        }

        $text = '';
        $footer = '';

        // Non-cached - get accessinfo
        if (isset($USER->access)) {
            $accessinfo = $USER->access;
        } else {
            $accessinfo = get_user_access_sitewide($USER->id);
        }

//        $courses = get_user_courses_bycap($USER->id, 'gradereport/user:view', $accessinfo, false,
//                                          'c.sortorder ASC', array('visible'));

        $sql = "SELECT ra.id
                FROM {$CFG->prefix}role_assignments ra
                INNER JOIN {$CFG->prefix}context ctx ON ra.contextid = ctx.id
                WHERE ctx.contextlevel = " . CONTEXT_COURSE . "
                AND ra.userid = {$USER->id}";

        // Verify if the user has a role in any course
        if (!empty($CFG->block_course_favourites_musthaverole) && !record_exists_sql($sql)) {
            $text = get_string('nocoursesforyou', 'block_course_favourites');
        } else {
            $noselection = true;

            // Verify further whether the user has created their favourites list
            if (($sortorder = get_field('block_course_favourites', 'sortorder', 'userid', $USER->id))) {
                $noselection = false;

                // Print list of courses work done here.....
                $crsfavs = get_user_fav_courses($USER->id);

                $class = '';

                if (!empty($crsfavs)) {
                    foreach ($crsfavs as $crsfav) {
                        if ($crsfav->visible) {
                            $class = '';
                        } else {
                            $class = 'class="dimmed"';
                        }

                        $text .= '<div class="block-course-favs" >' .
                                 '<a '. $class . ' href="' . $CFG->wwwroot . '/course/view.php?id=' .
                                 $crsfav->id . '">' . format_string($crsfav->fullname) . '</a></div>';
                    }
                }

                $footer = '<a href="'.$CFG->wwwroot.'/blocks/course_favourites/usersettings.php?' .
                          'courseid=' . $COURSE->id . '">' . get_string('settings', 'block_course_favourites') .
                          '</a>';
            }

            // print intro/help message if no selection has been created by the user
            if ($noselection) {
                $text = get_string('noselecedcoursesforyou', 'block_course_favourites');

                $footer = '<a href="' . $CFG->wwwroot . '/blocks/course_favourites/usersettings.php?' .
                          'courseid=' . $COURSE->id . '">' . get_string('settings', 'block_course_favourites') .
                          '</a>';
            }
        }

        if (!empty($text)) {
            $this->content->text = $text;
        }

        if (!empty($footer)) {
            $this->content->footer = $footer;
        }

        return $this->content;
    }
}

?>