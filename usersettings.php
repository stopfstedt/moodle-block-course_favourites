<?php // $Id: $
    require_once('../../config.php');
    require_once('usersettings_form.php');

    defined('MOODLE_INTERNAL') OR die('Direct access to this script is forbidden');

    require_login();

    $blockid    = required_param('blockid', PARAM_INT);
    $courseid   = required_param('courseid', PARAM_INT);

    global $CFG, $USER;

    $navlinks = array();

    if ($courseid and 1 < $courseid) {
        $shortname = get_field('course', 'shortname', 'id', $courseid);
        $navlinks[] = array('name' => format_string($shortname), 'link' => "view.php?id=$courseid", 'type' => 'link');
    }
    $navlinks[] = array('name' => get_string('breadcrumb', 'block_course_recent'), 'link' => '', 'type' => 'misc');

    $navigation = build_navigation($navlinks);

    print_header_simple(get_string('header', 'block_course_recent'), '', $navigation);

    // Check if this use has configured this block instance before
    $crsfav = get_user_fav_courses($blockid, $USER->id);

    // Check for capability for hidden courses
    $showhidden = false;
    $context = get_context_instance(CONTEXT_SYSTEM);
    if (has_capability('moodle/course:viewhiddencourses', $context, $USER->id)) {
        $showhidden = true;
    }

    // Check if AJAX is enabled
//    if ($CFG->ajaxcapable) {
//    }

    // Get a list of all courses
    if ($showhidden) {
        $courses = get_records('course');
    } else {
        $courses = get_records('course', 'visible', 1);
    }

    //

// something

    print_footer();

?>