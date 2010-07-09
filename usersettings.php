<?php // $Id: $
    require_once('../../config.php');
    require_once('lib.php');
    require_once('coursefav_jsportal.php');

    defined('MOODLE_INTERNAL') OR die('Direct access to this script is forbidden');

    $blockid      = required_param('blockid', PARAM_INT);
    $courseid     = required_param('courseid', PARAM_INT);
    $favcourseid  = optional_param('favcourseid', 0, PARAM_INT);
    $action       = optional_param('action', '', PARAM_TEXT);
    $previous     = optional_param('previous', 'first', PARAM_ALPHANUM);
    $movecourseid = optional_param('movecourseid', 0, PARAM_INT);

    require_login();

    global $CFG, $USER;

    $useajax = false;

    $ajaxtestedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111);

    $usrpref = get_user_preferences(null, null, $USER->id);


    if (!$movecourseid) {

        // Check if a favourite was selected
        switch ($action) {
            case 'add':

                if ($favcourseid) {
                    add_favourite_course($blockid, $USER->id, $favcourseid, $previous);
                }
                break;
            case 'remove':

                break;
        }

    } else {

       // Handle move action
    }

    // Check whether AJAX is needed
    if (ajaxenabled($ajaxtestedbrowsers) && $USER->ajax) {     // Browser, user and site-based switches

        $useajax = true;

        require_js(array('yui_yahoo',
                         'yui_dom',
                         'yui_event',
                         'yui_dragdrop',
                         'yui_connection',
                         'yui_selector',
                         'yui_element',
                         'yui_animation',
                         'ajaxcourse',
                        ));

        require_js($CFG->wwwroot . '/blocks/course_favourites/coursefav_main.js');
        require_js($CFG->wwwroot . '/blocks/course_favourites/course_fav.js');
    }

    $navlinks = array();

    if ($courseid and 1 < $courseid) {
        $shortname = get_field('course', 'shortname', 'id', $courseid);
        $navlinks[] = array('name' => format_string($shortname), 'link' => "view.php?id=$courseid", 'type' => 'link');
    }

    $navlinks[] = array('name' => get_string('breadcrumb', 'block_course_recent'), 'link' => '', 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header_simple(get_string('header', 'block_course_recent'), '', $navigation);

    // Check if this use has configured this block instance before
    $favcourses = get_user_fav_courses($blockid, $USER->id);

    // Check for capability for hidden courses
    $showhidden = 0;
    $context = get_context_instance(CONTEXT_SYSTEM);
    if (has_capability('moodle/course:viewhiddencourses', $context, $USER->id)) {
        $showhidden = 1;
    }

    // Get a list of all courses
    $allcourses = get_complete_course_list($USER, $showhidden, $favcourses);

    // print output
    echo '<div id="block_course_fav">'."\n";

    echo '<div id="favlist_header1" class="favlist_header">'."\n";
    echo '<span id="course-header">'. get_string('coursesheader', 'block_course_favourites') . '</span>'."\n";
    echo '<span id="action-header">'. get_string('actionheader', 'block_course_favourites') . '</span>'."\n";
    echo '</div>';

    echo '<div id="favlist" class="coursefav">'."\n";
    echo '<ul id="allclasses" class="section img-text">'."\n";


    // Return all the keys in the list to keep track of previous iterations
    $coursekeys = array_keys($allcourses);
    $i = 1;
    $last = count($coursekeys);
    $previouscourse = $coursekeys[0];

    foreach ($allcourses as $coursid => $course) {

        // The batch of code below keeps track of the first or previous course id
        // This is needed to determine exactly where an course is marked favourite
        // in order to maintain the correct order.  It's also used when re-ordering
        // the coureses

        if ($i >= 2) {
            $previouscourse = $coursekeys[$i-2];
        } elseif (1 == $i) {
            $previouscourse = 'first';
        }

        // Adding CSS class information
        if ($course->fav) {
            $class = 'class="coursefav usrfav"';
            $style = 'style="background-color: #FFFFCC;"';
        } else {
            $class = 'class="coursefav"';
            $style = '';
        }

        if (!$course->visible) {
            $class2 = 'class="dimmed"';
        } else {
            $class2 = '';
        }

        // Printing the sorted list
        echo '<li id="course-'.$coursid.'" '.$class.'>'."\n";
        echo '<a id="course-' . $coursid . '-link" href="' .$CFG->wwwroot.'/view.php?id=' . $course->id .
              '" ' . $class2 . ' ' . $style . '>'. $course->fullname . '</a>'."\n";
        echo '<span class="commands">'."\n";

        // Print button for non AJAX version
        if (!$useajax && !$USER->ajax) {

            // Add the previous course in the list as a parameter because we need to know
            // where in the list to insert the course
            if (array_key_exists($previouscourse, $allcourses)) {
                $previous = $allcourses[$previouscourse]->id;
            } else {
                $previous = 'first';
            }

            // Check if the course is already a favourite and add the appropriate parameter to denote that
            if ($course->fav) {
                $action = 'remove';
            } else {
                $action = 'add';
            }

            echo '<a href="usersettings.php?blockid='.$blockid.'&amp;courseid='.$courseid.
                 '&amp;movecourseid='.$course->id.'&amp;previous='.$previous.'&amp;sesskey='.$USER->sesskey.
                 '" title="Move">'.
                 '<img class="smallicon" src="'.$CFG->pixpath.'/t/move.gif" alt="Move" /></a>';

            echo '&nbsp;&nbsp;';

            echo '<a href="usersettings.php?blockid='.$blockid.'&amp;courseid='.$courseid.
                 '&amp;favcourseid='.$course->id.'&amp;action='.$action.'&amp;previous='.
                 $previous.'&amp;sesskey='.$USER->sesskey.'" title="Favourite">'.
                 '<img class="smallicon" src="'.$CFG->pixpath.'/s/yes.gif" alt="Move" /></a>';
        }

        echo '</span>'."\n";
        echo '</li>';

        $i++;
    }

    echo '</ul>'."\n";
    echo '</div>'."\n";

    echo '</div>'."\n";

    // check for $useajax again //  $USER->ajax
    $blockportal = new coursefav_jsportal();
    $blockportal->print_javascript($blockid);
    print_footer();

?>