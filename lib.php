<?php // $Id: $

/** return an array of the user's favourite courses
 *  in the correct order.  Also removes any courese that no longer
 *  exist
 */
function get_user_fav_courses($blockinstance, $userid) {

    $crsfav = array();

    if (($id = get_field('block_course_favourites', 'id', 'blockid', $blockinstance, 'userid', $userid))) {
        $crsfav = get_records('block_course_favourites_selection', 'cfid', $id, 'parent ASC');
    }

    // Go through the favorites and remove courses that no longer exist
    if (!empty($crsfav)) {

        // Get the first record in the list
        $rec = each($crsfav);
        $delkey = false;

        // Doing the DO!
        do {

            // Check if delkey is set.  If set then we must delete that record from the
            // user's favourites list and re-order the favourites list
            if ($delkey) {
            }
            // Check if the current record exists in the course table
            if (!record_exists('course', 'id', $rec->courseid)) {

                // Save key of record that doesn't exist
                $delkey = key($crsfav);
            } else {

                // Reset delkey flag
                $delkey = false;
            }

        } while (($rec = each($crsfav)));

        // Check delkey flag.  If set then there is a record that has not been
        // removed yet
        if ($delkey) {
            delete_records('block_course_favourites_selection', 'id', $delkey);
        }
    }

    $i = 0;
    for ($i = 0; $i < count($crsfav); $i++) {
    }

    foreach ($crsfav as $key => $data) {
        if (!record_exists('course', 'id', $crsfav->courseid)) {

        }
    }
    return $crsfav;
}

/**
 * Return an array of all the courese that exist
 * including the user's favorite selected courses
 *
 */
function get_courses_list($userobj, $showall = false, $favcourses = array()) {
    $courses = array();

    // Non-cached - get accessinfo
    if (isset($userobj->access)) {
        $accessinfo = $userobj->access;
    } else {
        $accessinfo = get_user_access_sitewide($userobj->id);
    }

    // Get courses the user is allowed to see
    $capcourses = get_user_courses_bycap($userobj->id, 'gradereport/user:view', $accessinfo, false,
                                      'c.sortorder ASC', array('visible'));

    // Remove courses that are hidden
    if (!$showall) {
        foreach ($capcourses as $key => $data) {
            if ($data->visible) {
                // Re-map the array so that the course ids are keys
                // to the array
                $courses[$data->id] = $data;
            }
        }
    } else { // Remap
        foreach ($capcourses as $key => $data) {
            $courses[$data->id] = $data;
        }
    }




}
?>