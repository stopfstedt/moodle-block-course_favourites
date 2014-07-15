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
 * @copyright &copy; 2014 The Regents of the University of California
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Carson Tam <carson.tam@ucsf.edu>, Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/ajax/ajaxlib.php');

/**
 * Used to create view of document to be passed to JavaScript on pageload.
 * We use this class to pass data from PHP to JavaScript.
 */
class jsportal {

    var $currentblocksection = null;
    var $blocks = array();


    /**
     * Takes id of block and adds it
     */
    function block_add($id, $hidden=false){
        $hidden_binary = 0;

        if ($hidden) {
            $hidden_binary = 1;
        }
        $this->blocks[count($this->blocks)] = array($this->currentblocksection, $id, $hidden_binary);
    }


    /**
     * Prints the JavaScript code needed to set up AJAX for the course.
     */
    function print_javascript($courseid, $return=false) {
        global $CFG, $USER, $COURSE;

        $blocksoutput = $output = '';
        for ($i=0; $i<count($this->blocks); $i++) {
            $blocksoutput .= "['".$this->blocks[$i][0]."',
                             '".$this->blocks[$i][1]."',
                             '".$this->blocks[$i][2]."']";

            if ($i != (count($this->blocks) - 1)) {
                $blocksoutput .= ',';
            }
        }
        $output .= "<script type=\"text/javascript\">\n";
        $output .= "    main.portal.id = ".$courseid.";\n";
        $output .= "    main.portal.blocks = new Array(".$blocksoutput.");\n";
        $output .= "    main.portal.strings['courseformat']='".$COURSE->format."';\n";
        $output .= "    main.portal.strings['wwwroot']='".$CFG->wwwroot."';\n";
        $output .= "    main.portal.strings['pixpath']='".$CFG->pixpath."';\n";
        $output .= "    main.portal.strings['marker']='".get_string('markthistopic', '', '_var_')."';\n";
        $output .= "    main.portal.strings['marked']='".get_string('markedthistopic', '', '_var_')."';\n";
        $output .= "    main.portal.numsections = ".$COURSE->numsections.";\n";
        $output .= "    main.portal.strings['hide']='".get_string('hide')."';\n";
        $output .= "    main.portal.strings['hidesection']='".get_string('hidesection', '', '_var_')."';\n";
        $output .= "    main.portal.strings['show']='".get_string('show')."';\n";
        $output .= "    main.portal.strings['delete']='".get_string('delete')."';\n";
        $output .= "    main.portal.strings['move']='".get_string('move')."';\n";
        $output .= "    main.portal.strings['movesection']='".get_string('movesection', '', '_var_')."';\n";
        $output .= "    main.portal.strings['moveleft']='".get_string('moveleft')."';\n";
        $output .= "    main.portal.strings['moveright']='".get_string('moveright')."';\n";
        $output .= "    main.portal.strings['update']='".get_string('update')."';\n";
        $output .= "    main.portal.strings['groupsnone']='".get_string('groupsnone')."';\n";
        $output .= "    main.portal.strings['groupsseparate']='".get_string('groupsseparate')."';\n";
        $output .= "    main.portal.strings['groupsvisible']='".get_string('groupsvisible')."';\n";
        $output .= "    main.portal.strings['clicktochange']='".get_string('clicktochange')."';\n";
        $output .= "    main.portal.strings['deletecheck']='".get_string('deletecheck','','_var_')."';\n";
        $output .= "    main.portal.strings['resource']='".get_string('resource')."';\n";
        $output .= "    main.portal.strings['activity']='".get_string('activity')."';\n";
        $output .= "    main.portal.strings['sesskey']='".$USER->sesskey."';\n";
        $output .= "    onloadobj.load();\n";
        $output .= "    main.process_blocks();\n";
        $output .= "</script>";
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }

}

class coursefav_jsportal extends jsportal {
    function print_javascript($courseid = 0, $return=false) {
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