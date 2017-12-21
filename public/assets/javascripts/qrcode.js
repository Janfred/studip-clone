/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, jQuery, STUDIP */

STUDIP.QRCode = {
    show: function () {
        jQuery("#qr_code").remove();
        jQuery("<div id='qr_code'/>").appendTo("body");
        jQuery("#qr_code").append("<div class='header'/>");
        jQuery("#qr_code").append("<div class='code'/>");
        jQuery("#qr_code").append("<div class='url'/>");
        jQuery("#qr_code").append("<div class='description'/>");

        var code = new QRCode(
            jQuery("#qr_code .code")[0], {
                text: jQuery(this).attr("href"),
                width: 1280,
                height: 1280,
                correctLevel: 3
            }
        );

        jQuery("#qr_code .url").text(jQuery(this).attr("href"));
        jQuery("#qr_code .description").text(jQuery(this).data("qr-code"));
        //jQuery("#qr_code .code").html(jQuery(this).find(".qrcode_image").clone());
        var qr = jQuery("#qr_code")[0];
        if (qr.requestFullscreen) {
            qr.requestFullscreen();
        } else if (qr.msRequestFullscreen) {
            qr.msRequestFullscreen();
        } else if (qr.mozRequestFullScreen) {
            qr.mozRequestFullScreen();
        } else if (qr.webkitRequestFullscreen) {
            qr.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
        return false;
    }
};

jQuery(function () {
    jQuery(document).on("click", "a[data-qr-code]", STUDIP.QRCode.show);
});