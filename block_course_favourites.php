<?php // $Id: $

require_once('lib.php');

class block_course_favourites extends block_base {
    function init() {
        $this->title   = get_string('course_favourites', 'block_course_favourites');
        $this->version = 201006290;
    }

    function get_content() {
        global $CFG, $USER, $COURSE;

        if ($this->content !== NULL) {
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

        $courses = get_user_courses_bycap($USER->id, 'gradereport/user:view', $accessinfo, false,
                                          'c.sortorder ASC', array('visible'));

        // Verify whether the user is a guest if so display nothing
        if (empty($USER->id)) {

            $text = '';
        } else {

            // Verify if the user has a role in any course
            if (empty($courses)) {

                $text = get_string('nocoursesforyou', 'block_course_favourites');
            } else {

                $noselection = true;

                // Verify further whether the user has created their favourites list
                if (($sortorder = get_field('block_course_favourites', 'sortorder', 'userid', $USER->id, 'blockid', $this->instance->id))) {

                    $noselection = false;

                    // Print list of courses work done here.....
                    $crsfavs = get_user_fav_courses($this->instance->id, $USER->id);

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

                    $footer = '<a href="'.$CFG->wwwroot.'/blocks/course_favourites/usersettings.php?'.
                              'blockid='. $this->instance->id.'&courseid='.$COURSE->id.'">'.
                              get_string('settings', 'block_course_favourites') . '</a>';

                }

                // print intro/help message if no selection has been created by the user
                if ($noselection) {
                    $text = get_string('noselecedcoursesforyou', 'block_course_favourites');

                    $footer = '<a href="'.$CFG->wwwroot.'/blocks/course_favourites/usersettings.php?'.
                              'blockid='. $this->instance->id.'&courseid='.$COURSE->id.'">'.
                              get_string('settings', 'block_course_favourites') . '</a>';
                }
            }
        }

        $this->content         =  new stdClass;
        $this->content->text   = $text;
        $this->content->footer = $footer;

    }
}
?>
