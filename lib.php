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
 * Library of API functions.
 *
 * @package   blocks-course_favourites
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/** return an array of the user's favourite courses
 *  in the correct order.  Also removes any courese that no longer
 *  exist
 */
function get_user_fav_courses($userid) {

    $crsfav     = array();
    $courses    = array();
    $coursesori = array();

    $crsfav = get_record('block_course_favourites', 'userid', $userid);

    // Explode course list into an array
    if (!empty($crsfav)) {
        $coursesori = explode(',', $crsfav->sortorder);

    } else {
        return array();
    }

    // Determine which courses have been deleted
    $courses = remove_deleted_courses($coursesori, $userid);

    // Remove coures if the user doesn't have the proper role(s) for
    // Non-cached - get accessinfo
    $accessinfo = get_user_access_sitewide($userid);

    // Get courses the user is allowed to see
    $capcourses = get_user_courses_bycap($userid, 'gradereport/user:view', $accessinfo, false,
                                      'c.sortorder ASC', array('visible', 'fullname'));

    // Remove courses the user can no longer see because the user does not have the role(s)
    foreach ($courses as $key2 => $course) {

        foreach ($capcourses as $key => $capcourse) {

            if ($course->id == $capcourse->id) {
                $found = true;
                break;
            } else {
                $found = false;
            }
        }

        // If the favourite course was not found in the courses the user is capable of seeing then remove it
        if (!$found) {
            unset($courses[$key2]);
        }
    }

    // Update fav courses if any have been deleted
    if (count($courses) != count($coursesori)) {
        $crsfav->sortorder = implode(',', array_keys($courses));
        update_record('block_course_favourites', $crsfav);
    }

    return $courses;

}

/**
 * Remove deleted courses
 */
function remove_deleted_courses($usrcourses = array()) {

    if (empty($usrcourses)) {
        return $usrcourses;
    }

    $tempcourses = array_combine($usrcourses, $usrcourses);

//    $allcourses = get_records('course');

    // Verify whether the any of the favourite courses still exist
    foreach ($tempcourses as $key => $courseid) {
        if (empty($courseid) || !record_exists('course', 'id', $courseid)) {
            unset($tempcourses[$key]);
        } else {
            $tempcourses[$key] = new stdClass();
            $tempcourses[$key]->id       = $key;
            $tempcourses[$key]->fullname = get_field('course', 'fullname', 'id', $courseid);
            $tempcourses[$key]->visible  = get_field('course', 'visible', 'id', $courseid);
            $tempcourses[$key]->fav      = 1;
        }
    }

    return $tempcourses;
}

/**
 * Return an array of all the courese that exist
 * including the user's favorite selected courses
 *
 * @uses $CFG
 */
function get_complete_course_list($userobj, $showall = 0, $favcourses = array()) {
    global $CFG;

      // Non-cached - get accessinfo
      if (isset($userobj->access)) {
          $accessinfo = $userobj->access;
      } else {
          $accessinfo = get_user_access_sitewide($userobj->id);
      }

      $doanything = empty($CFG->block_course_favourites_musthaverole);

      // Get courses the user is allowed to see
      $capcourses = get_user_courses_bycap($userobj->id, 'gradereport/user:view', $accessinfo, $doanything,
                                           'c.fullname ASC', array('visible', 'fullname'));

      $templist = $favcourses;

      foreach ($capcourses as $course) {
          if ($course->id === SITEID) {
              continue;
          }

          if (!array_key_exists($course->id, $favcourses)) {
              $index = $course->id;
              $templist[$index]->id       = $index;
              $templist[$index]->fullname = $course->fullname;
              $templist[$index]->visible  = $course->visible;
              $templist[$index]->fav      = 0;
          }
      }

      return $templist;

}

/**
 * Add a favourite course to the favourte courses list
 */
function add_favourite_course($userid, $courseid, $sortorder) {

    $crsfav = get_record('block_course_favourites', 'userid', $userid);

    // Insert new record
    if (empty($crsfav)) {
        $crsfav = new stdClass;
        $crsfav->userid    = $userid;
        $crsfav->sortorder = $courseid;
        insert_record('block_course_favourites', $crsfav);

        return;
    }


    $templist = array();
    $insert = false;
    $favourites = explode(',', $crsfav->sortorder);
    $sortorder = trim($sortorder, ',');
    $courselist = explode(',', $sortorder);

    // This course it the first in the list
    if (empty($sortorder)) {
        $crsfav->sortorder = $courseid . ',' . $crsfav->sortorder;
    } else {
        foreach ($courselist as $key => $data) {
            if ($data == $courseid) {
                $templist[] = $data;
            } elseif (false !== array_search($data, $favourites)) {
                $templist[] = $data;

            }
        }

        $crsfav->sortorder = implode(',', $templist);
    }

    update_record('block_course_favourites', $crsfav);


    return;
}

/**
 * Remove a favourites course from the favourties list
 */
 function remove_favourite_course($userid, $courseid) {

    $crsfav = get_record('block_course_favourites', 'userid', $userid);

    $templist = explode(',', $crsfav->sortorder);

    $key = array_search($courseid, $templist);

    if (false !== $key) {
        unset($templist[$key]);
        $crsfav->sortorder = implode(',', $templist);
    }

    update_record('block_course_favourites', $crsfav);

    return;
 }

/**
 * Code to move a course from one spot to another
 */
function move_favourite_course($userid, $coursetomove, $courseid, $sortorder) {
    $crsfav = get_record('block_course_favourites', 'userid', $userid);

    $templist = explode(',', $crsfav->sortorder);
    $sortorder = trim($sortorder, ',');
    $sortorder = explode(',', $sortorder);

    // If the course selected to move isn't a marked as a course favourite then do nothing
    if (false === array_search($coursetomove, $templist)) {
        return;
    } else {

        // Check if the course to be moved is either the last or second last
        // course id in the sequence.  If so then no move needs to happen
        $last = count($sortorder) - 1;

        $orikey = array_search($coursetomove, $sortorder);

        // TODO: Why are we checking for second-last in the list and not moving it?
        if (false !== $orikey && ($last == $orikey)) {
            // No move is neccessary
            return;
        } else {

            // Check if $courseid is 'fist' or 'last'
            if (0 == strcmp('first', $courseid)) {

                // Find the course that is to be moved, and remove it from the sort order
                $orikey = array_search($coursetomove, $templist);
                unset($templist[$orikey]);

                // Update the sort order with the removed course
                $crsfav->sortorder = implode(',', $templist);

                // Update the sort order with the new order
                $crsfav->sortorder = $coursetomove . ',' .$crsfav->sortorder;
                update_record('block_course_favourites', $crsfav);
                return;

            } elseif (0 == strcmp('last', $courseid)) {
                // Find the course that is to be moved, and remove it from the sort order
                $orikey = array_search($coursetomove, $templist);
                unset($templist[$orikey]);

                // Update the sort order with the removed course
                $crsfav->sortorder = implode(',', $templist);
                $crsfav->sortorder =  $crsfav->sortorder . ',' . $coursetomove;
                update_record('block_course_favourites', $crsfav);
                return;
            }

            // A move is needed, retrieve the location of the course where the
            // selected course is going to move to
            $key = array_search($courseid, $templist);


            if (false !== $key && ( false !== strpos($crsfav->sortorder, $templist[$key]) ) ) {

                // Remove the original course from the list as it is about to be moved
                // to a new location
                $orikey = array_search($coursetomove, $templist);
                unset($templist[$orikey]);
                $crsfav->sortorder = implode(',', $templist);


                // Insert the course to be move in the new list now
                $crsfav->sortorder = str_replace($templist[$key], $courseid . ',' . $coursetomove, $crsfav->sortorder);
            }
        }

        update_record('block_course_favourites', $crsfav);
    }
 }

?>