// Extend the main_class class
coursefav_main.prototype = new main_class();


function coursefav_main(){
    main_class.apply();
}

// Override the connect method
coursefav_main.prototype.connect = function(method, urlStub, callback, body) {

    var uri = this.portal.strings['wwwroot'] + "/blocks/course_favourites/rest.php?sesskey=" + this.portal.strings['sesskey'];
    
    var postdata = "blockid=" + this.portal.strings['blockid'] + "&userid=" + this.portal.strings['userid'] + '&sequence=' + body;

    return YAHOO.util.Connect.asyncRequest(method, uri, callback, postdata);

}