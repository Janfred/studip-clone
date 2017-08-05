/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

STUDIP.Geolocation = {
    mycoords: null,
    periodicalPushData: function () {
        console.log(STUDIP.Geolocation.mycoords);
        return STUDIP.Geolocation.mycoords;
    }
};

$(document).ready(function () {
    if (STUDIP.ACTIVATE_GEOLOCATION && "geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            STUDIP.Geolocation.mycoords = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            console.log(STUDIP.Geolocation.mycoords);
        });
    }
});