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
 * @copyright &copy; 2014 The Regents of the University of California
 * @copyright 2010 Remote Learner - http://www.remote-learner.net/
 * @author    Carson Tam <carson.tam@ucsf.edu>, Akin Delamarre <adelamarre@remote-learner.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* This script is a modified version of http://developer.yahoo.com/yui/examples/dragdrop/dd-reorder.html */

(function() {

var Dom = YAHOO.util.Dom;
var Event = YAHOO.util.Event;
var DDM = YAHOO.util.DragDropMgr;


// DDApp
YAHOO.DDApp = {
    init: function() {

		var allcourses = document.getElementById('allclasses');
		
		var allcourses_count = allcourses.childNodes.length;

        new YAHOO.util.DDTarget('allclasses');
        
        if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) {
            var i = 0;
        } else {
            var i = 1;
        }
 
	   for (i; i < allcourses_count; i++) {
	       var classid = allcourses.childNodes[i].id
         new YAHOO.DDList(classid, 'courses');
	   }

        Event.on("showButton", "click", this.showOrder);
        Event.on("switchButton", "click", this.switchStyles);
    },

    showOrder: function() {
        var parseList = function(ul, title) {
            var items = ul.getElementsByTagName("li");
            var out = title + ": ";
            for (i=0;i<items.length;i=i+1) {
                out += items[i].id + " ";
            }
            return out;
        };

        var ul1=Dom.get("ul1"), ul2=Dom.get("ul2");
        alert(parseList(ul1, "List 1") + "\n" + parseList(ul2, "List 2"));

    },

    switchStyles: function() {
        Dom.get("ul1").className = "draglist_alt";
        Dom.get("ul2").className = "draglist_alt";
    }
};

// A custom drag and drop class that extends DDProxy

YAHOO.DDList = function(id, sGroup, config) {

    YAHOO.DDList.superclass.constructor.call(this, id, sGroup, config);

    this.logger = this.logger || YAHOO;
    var el = this.getDragEl();
    Dom.setStyle(el, "opacity", 0.67); // The proxy is slightly transparent

    this.goingUp = false;
    this.lastY = 0;
    this.handle = null;
    this.commandContainer = null;
        
    this.init_buttons();

};

YAHOO.extend(YAHOO.DDList, YAHOO.util.DDProxy, {

	init_buttons: function() {
	    var commandContainer = YAHOO.util.Dom.getElementsByClassName('commands',
	            'span', this.getEl())[0];

	    if (commandContainer == null) {
	        YAHOO.log('Cannot find command container for '+this.getEl().id, 'error');
	        return;
	    }
	

		/// Favourites button
	    var handleRef2 = main.mk_button('a', '/s/yes.gif', 'Add',
	            [['style', '']],
	            [['height', '11'], ['width', '11'], ['style', 'margin-right:3px; border:0;']]);

	    YAHOO.util.Dom.generateId(handleRef2, 'add-' + this.getEl().id + '-');

  	    commandContainer.appendChild(handleRef2);
  	    
  	    var crslinkel = document.getElementById(this.getEl().id + '-link');
  	    
		Event.on(handleRef2.id, "click", this.addClickHandlercallback, crslinkel);
		
		// Move handle
	    var handleRef = main.mk_button('a', '/i/move_2d.gif', 'Move',
	            [['style', 'cursor:move']],
	            [['height', '11'], ['width', '11'], ['style', 'margin-right:3px; border:0;']]);
	
	    YAHOO.util.Dom.generateId(handleRef, 'move-' + this.getEl().id + '-');

	    this.handle = handleRef;

	    commandContainer.appendChild(handleRef);

	    this.setHandleElId(this.handle.id);
	},

	addClickHandlercallback: function(e, obj) {

		// Get the current background colour of the clicked element
		var style = YAHOO.util.Dom.getStyle(obj, 'background-color');
		
		// If transparent then make  bold and vice versa and add/remove userfav class
		if ('transparent' == style) {
	        YAHOO.util.Dom.setStyle(obj, 'background-color', '#FFFFCC');
	        YAHOO.util.Dom.addClass(obj.parentNode, 'usrfav');

			//alert(crsfavlist[crsfavlist.length - 1].id);
	        
		} else {
			YAHOO.util.Dom.setStyle(obj, 'background-color', 'transparent');
	        YAHOO.util.Dom.removeClass(obj.parentNode, 'usrfav');

			//alert(crsfavlist[crsfavlist.length - 1].id);
		}
		
		var courselist = '';

		// Get parent ul element needed to increase performance
		var parent_ul = obj.parentNode.parentNode;

    	// Get a list of all of the selected elements
		var crsfavlist = YAHOO.util.Dom.getElementsByClassName('usrfav', 'li', parent_ul);
		
		for (var i = 0; i < crsfavlist.length; i++) {
			if (0 == i) {
				courselist = crsfavlist[i].id.replace(/course-/i, '');
			} else {
				courselist = courselist + ',' + crsfavlist[i].id.replace(/course-/i, '');
			}
		}

		crsfavmain.connect('POST', '', null, courselist);

	},
	
    startDrag: function(x, y) {
        this.logger.log(this.id + " startDrag");

        // make the proxy look like the source element
        var dragEl = this.getDragEl();
        var clickEl = this.getEl();
        Dom.setStyle(clickEl, "visibility", "hidden");

        dragEl.innerHTML = clickEl.innerHTML;

        Dom.setStyle(dragEl, "color", Dom.getStyle(clickEl, "color"));
        Dom.setStyle(dragEl, "backgroundColor", Dom.getStyle(clickEl, "backgroundColor"));
        Dom.setStyle(dragEl, "border", "2px solid gray");
    },

    endDrag: function(e) {

        var srcEl = this.getEl();
        var proxy = this.getDragEl();

        // Show the proxy element and animate it to the src element's location
        Dom.setStyle(proxy, "visibility", "");
        var a = new YAHOO.util.Motion( 
            proxy, { 
                points: { 
                    to: Dom.getXY(srcEl)
                }
            }, 
            0.2, 
            YAHOO.util.Easing.easeOut 
        )
        var proxyid = proxy.id;
        var thisid = this.id;

        // Hide the proxy and show the source element when finished with the animation
        a.onComplete.subscribe(function() {
                Dom.setStyle(proxyid, "visibility", "hidden");
                Dom.setStyle(thisid, "visibility", "");
            });
        a.animate();

		// Get parent ul element needed to increase performance
		var parent_ul = srcEl.parentNode;

		var courselist = '';

    	// Get a list of all of the selected elements
		var crsfavlist = YAHOO.util.Dom.getElementsByClassName('usrfav', 'li', parent_ul);

		for (var i = 0; i < crsfavlist.length; i++) {
			if (0 == i) {
				courselist = crsfavlist[i].id.replace(/course-/i, '');
			} else {
				courselist = courselist + ',' + crsfavlist[i].id.replace(/course-/i, '');
			}
		}

		crsfavmain.connect('POST', '', null, courselist);

    },

    onDragDrop: function(e, id) {

        // If there is one drop interaction, the li was dropped either on the list,
        // or it was dropped on the current location of the source element.
        if (DDM.interactionInfo.drop.length === 1) {


            // The position of the cursor at the time of the drop (YAHOO.util.Point)
            var pt = DDM.interactionInfo.point; 

            // The region occupied by the source element at the time of the drop
            var region = DDM.interactionInfo.sourceRegion; 

            // Check to see if we are over the source element's location.  We will
            // append to the bottom of the list once we are sure it was a drop in
            // the negative space (the area of the list without any list items)
            if (!region.intersect(pt)) {
                var destEl = Dom.get(id);
                var destDD = DDM.getDDById(id);
                destEl.appendChild(this.getEl());
                destDD.isEmpty = false;
                DDM.refreshCache();
            }

        }
    },

    onDrag: function(e) {

        // Keep track of the direction of the drag for use during onDragOver
        var y = Event.getPageY(e);

        if (y < this.lastY) {
            this.goingUp = true;
        } else if (y > this.lastY) {
            this.goingUp = false;
        }

        this.lastY = y;
    },

    onDragOver: function(e, id) {

        var srcEl = this.getEl();
        var destEl = Dom.get(id);

        // We are only concerned with list items, we ignore the dragover
        // notifications for the list.
        if (destEl.nodeName.toLowerCase() == "li") {
            var orig_p = srcEl.parentNode;
            var p = destEl.parentNode;

            if (this.goingUp) {
                p.insertBefore(srcEl, destEl); // insert above
            } else {
                p.insertBefore(srcEl, destEl.nextSibling); // insert below
            }

            DDM.refreshCache();
        }
    }
});

Event.onDOMReady(YAHOO.DDApp.init, YAHOO.DDApp, true);

})();
