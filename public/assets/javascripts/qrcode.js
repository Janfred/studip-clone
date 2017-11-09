/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, jQuery, STUDIP */

STUDIP.QRCode = {
    show: function () {
        jQuery("#qr_code .url").text(jQuery(this).attr("href"));
        jQuery("#qr_code .description").text(jQuery(this).data("qr-code"));
        jQuery("#qr_code .code").html(jQuery(this).find(".qrcode_image").clone());
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
    jQuery("a[data-qr-code]").each(function () {
        jQuery("a[data-qr-code]").append('<div class="qrcode_image"></div>');
        new QRCode(
            jQuery(this).find(".qrcode_image")[0], {
                text: jQuery(this).attr("href"),
                width: 1280,
                height: 1280,
                correctLevel: 3
            }
        );
        jQuery(this).data("qr-code-image", jQuery("#qr_code .code img").attr("src"));
    });
    jQuery(document).on("click", "a[data-qr-code]", STUDIP.QRCode.show);
});