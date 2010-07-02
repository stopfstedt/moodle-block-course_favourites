<?php
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

print_object($courses);

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
                if (($id = get_field('block_course_favourites', 'id', 'blockid', $this->instance->id, 'userid', $USER->id))) {

                    if (record_exists('block_course_favourites_selection', 'cfid', $id)) {

                        $noselection = false;

                        // Print list of coruses work done here.....

                        $footer = '<a href="'.$CFG->wwwroot.'/blocks/course_favourites/usersettings.php?'.
                                  'blockid='. $this->instance->id.'&courseid='.$COURSE->id.'">'.
                                  get_string('settings', 'block_course_favourites') . '</a>';

                    }
                }

                // print intro/help message if no selection has been created by the user
                if ($noselection) {
                    $text = get_string('nocoursesforyou', 'block_course_favourites');

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
