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
 * Handles AJAX Javascript requests.
 *
 * @package   blocks-course_favourites
 * @copyright &copy; 2014 The Regents of the University of California
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Carson Tam <carson.tam@ucsf.edu>, Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/dmllib.php');

$userid    = required_param('userid', PARAM_INT);
$sortorder = required_param('sortorder', PARAM_SEQUENCE);

// Check for permissions .... ?

if (!ajaxenabled() || empty($USER->ajax)) {
    error_log('Course AJAX not allowed');
    die;
}

require_sesskey();

$data = new stdClass();
$data->userid    = $userid;
$data->sortorder = $sortorder;

if ($id = $DB->get_field('block_course_favourites', 'id', array('userid' => $userid))) {
    if (!empty($id)) {
        // update record
        $data->id = $id;
        $DB->update_record('block_course_favourites', $data);
    }
} else {
    $DB->insert_record('block_course_favourites', $data);
}

?>