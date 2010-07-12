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
 * Code called from AJAX functions.
 *
 * @package   blocks-course_favourites
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/ajax/ajaxlib.php');

class coursefav_jsportal extends jsportal {
    function print_javascript($blockid, $courseid = 0, $return=false) {
        global $CFG, $USER;

        $output = '';

        $output .= "<script type=\"text/javascript\">\n";
        // TODO: the 4 lines below may not be needed anymore
        $output .= "    main.portal.strings['wwwroot']='".$CFG->wwwroot."';\n";
        $output .= "    main.portal.strings['pixpath']='".$CFG->pixpath."';\n";
        $output .= "    main.portal.strings['move']='".get_string('move')."';\n";
        $output .= "    main.portal.strings['sesskey']='".$USER->sesskey."';\n";

        $output .= "    var crsfavmain = new coursefav_main();\n";

        $output .= "    crsfavmain.portal.strings['wwwroot']='".$CFG->wwwroot."';\n";
        $output .= "    crsfavmain.portal.strings['pixpath']='".$CFG->pixpath."';\n";
        $output .= "    crsfavmain.portal.strings['move']='".get_string('move')."';\n";
        $output .= "    crsfavmain.portal.strings['sesskey']='".$USER->sesskey."';\n";
        $output .= "    crsfavmain.portal.strings['blockid']='".$blockid."';\n";
        $output .= "    crsfavmain.portal.strings['userid']='".$USER->id."';\n";
        $output .= "</script>";

        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
}

?>