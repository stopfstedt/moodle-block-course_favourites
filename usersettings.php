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

// something

    print_footer();

?>