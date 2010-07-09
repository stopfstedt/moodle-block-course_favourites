<?php // $Id: $

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/dmllib.php');

$blockid    = required_param('blockid', PARAM_INT);
$userid     = required_param('userid', PARAM_INT);
$sequence   = required_param('sequence', PARAM_SEQUENCE);

// Check for permissions .... ?

if (!empty($CFG->disablecourseajax)) {
    error_log('Course AJAX not allowed');
    die;
}

require_sesskey();

$data = new stdClass();
$data->blockid = $blockid;
$data->userid = $userid;
$data->sequence = $sequence;

if ($id = get_field('block_course_favourites', 'id', 'blockid', $blockid, 'userid', $userid)) {


    if (!empty($id)) {
        // update record
        $data->id = $id;
        update_record('block_course_favourites', $data);
    }
} else {
    insert_record('block_course_favourites', $data);
}

?>