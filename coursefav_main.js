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
 * AJAX Javascript functions.
 *
 * @package   blocks-course_favourites
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Extend the main_class class
coursefav_main.prototype = new main_class();


function coursefav_main(){
    main_class.apply();
}

// Override the connect method
coursefav_main.prototype.connect = function(method, urlStub, callback, body) {

    var uri = this.portal.strings['wwwroot'] + '/blocks/course_favourites/rest.php';
    
    var postdata = 'userid=' + this.portal.strings['userid'] + '&sesskey=' +
                   this.portal.strings['sesskey'] + '&sortorder=' + body;

    return YAHOO.util.Connect.asyncRequest(method, uri, callback, postdata);

}