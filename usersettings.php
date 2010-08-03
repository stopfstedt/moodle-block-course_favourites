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
 * Display the page to handle user instance configuration settings.
 *
 * @package   blocks-course_favourites
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once('coursefav_jsportal.php');

defined('MOODLE_INTERNAL') OR die('Direct access to this script is forbidden');

$courseid     = required_param('courseid', PARAM_INT);
$favcourseid  = optional_param('favcourseid', 0, PARAM_INT);
$action       = optional_param('action', '', PARAM_TEXT);
$previous     = optional_param('previous', 'first', PARAM_ALPHANUM);
$movecourseid = optional_param('movecourseid', 0, PARAM_INT);
$sortorder    = optional_param('sortorder', '', PARAM_SEQUENCE);

require_login();

$usrpref = get_user_preferences(null, null, $USER->id);


if (!$movecourseid && 'move' != $action) {

    // Check if a favourite was selected
    switch ($action) {
        case 'add':
            if ($favcourseid) {
                add_favourite_course($USER->id, $favcourseid, $sortorder);
            }
            break;
        case 'remove':
            if ($favcourseid) {
                remove_favourite_course($USER->id, $favcourseid, $sortorder);
            }
            break;
    }

} else {
    // Do move work here
    if ($favcourseid) {
        move_favourite_course($USER->id, $movecourseid, $previous, $sortorder);
        $previous = '';
        $favcourseid = 0;
        $action = '';
        $movecourseid = '';
        $sortorder = '';
    }
}

// Check whether AJAX is needed
$ajaxformatfile = $CFG->dirroot.'/blocks/course_favourites/ajax.php';

// TODO: stop abusing CFG global here
$CFG->ajaxcapable = false;           // May be overridden later by ajaxformatfile
$CFG->ajaxtestedbrowsers = array();  // May be overridden later by ajaxformatfile
$useajax = false;

if (file_exists($ajaxformatfile)) {

    require_once($ajaxformatfile);

    if (ajaxenabled($CFG->ajaxtestedbrowsers) && $USER->ajax) {     // Browser, user and site-based switches
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
}

$navlinks = array();

if ($courseid && $courseid != SITEID) {
    $shortname = get_field('course', 'shortname', 'id', $courseid);
    $navlinks[] = array(
        'name' => format_string($shortname),
        'link' => $CFG->wwwroot . '/course/view.php?id=' . $courseid,
        'type' => 'link'
    );
}

$navlinks[] = array('name' => get_string('breadcrumb', 'block_course_favourites'), 'link' => '', 'type' => 'misc');
$navigation = build_navigation($navlinks);
print_header_simple(get_string('header', 'block_course_favourites'), '', $navigation);

// Check if this user has configured this block instance before
$favcourses = get_user_fav_courses($USER->id);

// Check for capability for hidden courses
$showhidden = 0;
$context = get_context_instance(CONTEXT_SYSTEM);
if (has_capability('moodle/course:viewhiddencourses', $context, $USER->id)) {
    $showhidden = 1;
}

// Get a list of all courses
$allcourses = get_complete_course_list($USER, $showhidden, $favcourses);

print_simple_box_start('center', '75%', '', '', 'generalbox');

// TODO: Define the language strings for the helptext and then uncomment the lines
//       below (maybe choose an appropriate class, too).
if ($useajax) {
    print_simple_box(get_string('helptextajax', 'block_course_favourites'),
                     'center', '75%', '', '5', 'generalbox ajax-help');
} else {
    print_simple_box(get_string('helptextnoajax', 'block_course_favourites'),
                     'center', '75%', '', '5', 'generalbox non-ajax-help');
}

// print output

// Print 'are you sure' link if move has been initiated
if (0 == strcmp('move', $action)) {
  echo '<div align="center">';
  echo get_string('areyousuremove', 'block_course_favourites', $allcourses[$movecourseid]->fullname) .
       '&nbsp;&nbsp;( <a href="usersettings.php?action=cancel&amp;sesskey='.$USER->sesskey.
       '&amp;courseid=' . $courseid . '">' . get_string('cancel', 'block_course_favourites') .
       '</a> )<br /><br />';
  echo '</div>';
}

echo '<div id="block_course_fav">'."\n";

echo '<div id="favlist_header1" class="favlist_header">'."\n";
echo '<span id="course-header">'. get_string('coursesheader', 'block_course_favourites') . '</span>'."\n";
echo '<span id="action-header">'. get_string('actionheader', 'block_course_favourites') . '</span>'."\n";
echo '</div>';

echo '<div id="favlist" class="coursefav">'."\n";
echo '<ul id="allclasses" class="section img-text">'."\n";


// Return all the keys in the list to keep track of previous iterations
$coursekeys     = array_keys($allcourses);
$i              = 1;
$last           = count($coursekeys);
$previouscourse = isset($coursekeys[0]) ? $coursekeys[0] : '';
$sortorder      = '';
$previous       = '';
$actionparam    = '';

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

    // Create an order of couses in the list
    $sortorder .= $course->id . ',';

    // Adding CSS class information
    if ($course->fav) {
        $class = 'class="coursefav usrfav"';
        $style = '';
    } else {
        $class = 'class="coursefav nonusrfav"';
        $style = '';
    }

    if (!$course->visible) {
        $class2 = 'class="dimmed"';
    } else {
        $class2 = '';
    }

    // Printing the sorted list
    echo '<li id="course-'.$coursid.'" '.$class.'>'."\n";

    // If action equals 'move', then add movement icons inbetween list
    if (0 == strcmp('move', $action)) {
        echo '<a href="usersettings.php?courseid='.$courseid.
             '&amp;favcourseid='.$course->id.'&amp;action='.$action.'&amp;movecourseid='.$movecourseid.'&amp;previous='.
             $previouscourse.'&amp;sortorder='.$sortorder.'&amp;sesskey='.$USER->sesskey.'" title="Move Here">'.
             '<img class="smallicon" src="'.$CFG->pixpath.'/movehere.gif" alt="Move Here" /></a><br />';
        // TODO use language strings in title and alt attributes
    }

    echo '<a id="course-' . $coursid . '-link" href="' .$CFG->wwwroot.'/course/view.php?id=' . $course->id .
          '" ' . $class2 . ' ' /*. $style*/ . '>'. $course->fullname . '</a>'."\n";


    // If action equals 'move', then add movement icons inbetween list
    // this one is special because it is the last one in the list
    if (0 == strcmp('move', $action) && ($last == $i)) {
        echo '<br /><a href="usersettings.php?courseid='.$courseid.
             '&amp;favcourseid='.$course->id.'&amp;action='.$action.'&amp;movecourseid='.$movecourseid.'&amp;previous=last'.
             '&amp;sortorder='.$sortorder.'&amp;sesskey='.$USER->sesskey.'" title="Move Here">'.
             '<img class="smallicon" src="'.$CFG->pixpath.'/movehere.gif" alt="Move Here" /></a>';
    }

    // Do more CSS fun if this is the last element and we're moving
    if (0 == strcmp('move', $action) && ($last == $i)) {
        echo '<span class="commands">'."\n";
    } else {
        echo '<span class="commands">'."\n";
    }

    // Print button for non AJAX version
    if (!$useajax || !$USER->ajax) {
        // Add the previous course in the list as a parameter because we need to know
        // where in the list to insert the course
        if (array_key_exists($previouscourse, $allcourses)) {
            $previous = $allcourses[$previouscourse]->id;
        } else {
            $previous = 'first';
        }

        // Check if the course is already a favourite and add the appropriate parameter to denote that
        if ($course->fav) {
            $actionparam = 'remove';
        } else {
            $actionparam = 'add';
        }

        // TODO use language strings in title and alt attributes

        echo '<a href="usersettings.php?courseid='.$courseid.
             '&amp;movecourseid='.$course->id.'&amp;action=move&amp;sesskey='.$USER->sesskey.
             '" title="Move">'.
             '<img class="smallicon" src="'.$CFG->pixpath.'/t/move.gif" alt="Move" /></a>';

        echo '&nbsp;&nbsp;';

        echo '<a href="usersettings.php?courseid='.$courseid.
             '&amp;favcourseid='.$course->id.'&amp;action='.$actionparam.'&amp;previous='.
             $previous.'&amp;sortorder='.$sortorder.'&amp;sesskey='.$USER->sesskey.'" title="Favourite">'.
             '<img class="smallicon" src="'.$CFG->pixpath.'/s/yes.gif" alt="Move" /></a>';
    }

    echo '</span>'."\n";
    echo '<div style="clear: right;"></div>' . "\n";
    echo '</li>';

    $i++;
}

echo '</ul>'."\n";
echo '</div>'."\n";

echo '</div>'."\n";

print_simple_box_end();

// check for $useajax again //  $USER->ajax
if ($useajax && $USER->ajax) {
    $blockportal = new coursefav_jsportal();
    $blockportal->print_javascript();
}
print_footer();

?>