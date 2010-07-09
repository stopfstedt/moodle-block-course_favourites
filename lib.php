<?php // $Id: $

/** return an array of the user's favourite courses
 *  in the correct order.  Also removes any courese that no longer
 *  exist
 */
function get_user_fav_courses($blockinstance, $userid) {

    $crsfav     = array();
    $courses    = array();
    $coursesori = array();

    $crsfav = get_record('block_course_favourites', 'userid', $userid, 'blockid', $blockinstance);

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

    $allcourses = get_records('course');

    // Verify whether the any of the favourite courses still exist
    foreach ($tempcourses as $key => $courseid) {
        if (!array_key_exists($key, $allcourses)) {
            unset($tempcourses[$key]);
        } else {
            $tempcourses[$key] = new stdClass();
            $tempcourses[$key]->id = $key;
            $tempcourses[$key]->fullname = format_string($allcourses[$key]->fullname);
            $tempcourses[$key]->visible = $allcourses[$key]->visible;
            $tempcourses[$key]->fav = 1;
        }
    }

    return $tempcourses;
}

/**
 * Return an array of all the courese that exist
 * including the user's favorite selected courses
 *
 */
function get_complete_course_list($userobj, $showall = 0, $favcourses = array()) {

      // Non-cached - get accessinfo
      if (isset($userobj->access)) {
          $accessinfo = $userobj->access;
      } else {
          $accessinfo = get_user_access_sitewide($userobj->id);
      }

      // Get courses the user is allowed to see
      $capcourses = get_user_courses_bycap($userobj->id, 'gradereport/user:view', $accessinfo, false,
                                        'c.sortorder ASC', array('visible', 'fullname'));


      $templist = $favcourses;

      foreach ($capcourses as $courses) {
          if (1 == $courses->id) {
              continue;
          }

          if (!array_key_exists($courses->id, $favcourses)) {
              $index = $courses->id;
              $templist[$index]->id = $index;
              $templist[$index]->fullname = $courses->fullname;
              $templist[$index]->visible = $courses->visible;
              $templist[$index]->fav = 0;
          }
      }

      return $templist;

}

/**
 * Add a favourite course to the favourte courses list
 */
function add_favourite_course($blockinstance, $userid, $courseid, $sortorder) {

    $crsfav = get_record('block_course_favourites', 'userid', $userid, 'blockid', $blockinstance);

    // Insert new record
    if (empty($crsfav)) {
        $crsfav = new stdClass;
        $crsfav->userid = $userid;
        $crsfav->blockid = $blockinstance;
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
 function remove_favourite_course($blockinstance, $userid, $courseid) {

    $crsfav = get_record('block_course_favourites', 'userid', $userid, 'blockid', $blockinstance);

    $templist = explode(',', $crsfav->sortorder);

    $key = array_search($courseid, $templist);

    if (false !== $key) {
        unset($templist[$key]);
        $crsfav->sortorder = implode(',', $templist);
    }

    update_record('block_course_favourites', $crsfav);

    return;
 }

 function move_favourite_course($blockinstance, $userid, $courseid, $sortorder) {
    $crsfav = get_record('block_course_favourites', 'userid', $userid, 'blockid', $blockinstance);

    $templist = explode(',', $crsfav->sortorder);

    // If the course selected to move isn't a marked as a course favourite then do nothing
    if (false === array_search($courseid, $templist)) {
        return;
    }
 }

?>