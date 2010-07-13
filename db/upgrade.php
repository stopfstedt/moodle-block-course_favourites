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
 * Database upgrade script.
 *
 * @package   blocks-course_favourites
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Justin Filip <jfilip@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function xmldb_block_course_favourites_upgrade($oldversion = 0) {
    global $CFG, $THEME, $db;

    $result = true;

    if ($result && $oldversion < 2010071300) {
        // Look for any duplicate records for a user and let's take the first one created to keep and delete the rest.
        if ($rs = get_recordset('block_course_favourites', '', '', 'userid ASC, id ASC', 'id, userid')) {
            $curuserid = 0;
            $deleteids = array();

            while ($record = rs_fetch_next_record($rs)) {
                if ($record->userid != $curuserid) {
                    $curuserid = $record->userid;
                } else {
                    $deleteids[] = $record->id;
                }
            }

            rs_close($rs);

            if (!empty($deleteids)) {
                if (count($deleteids) > 1) {
                    $result = $result && delete_records_select('block_course_favourites', 'id IN (' . implode(', ', $deleteids) . ')');
                } else {
                    $result = $result && delete_records('block_course_favourites', 'id', current($deleteids));
                }
            }
        }

        // Now we remove the 'blockid' field from the table.
        $table = new XMLDBTable('block_course_favourites');
        $field = new XMLDBField('blockid');
        $index = new XMLDBIndex('blockid');

        $result = $result && drop_field($table, $field);
        $result = $result && drop_index($table, $index);

        // We're also going to modify the 'sortorder' column to be a text field.
        $field = new XMLDBField('sortorder');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'userid');

        $result = $result && change_field_type($table, $field);
        $result = $result && change_field_precision($table, $field);
        $result = $result && change_field_notnull($table, $field);
        $result = $result && change_field_default($table, $field);
    }

    return $result;
}

?>