<?php
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
