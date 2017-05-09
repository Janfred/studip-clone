/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

(function ($) {

    var fold,
        $wrapper,
        was_below_the_fold = false,
        scroll = function (scrolltop) {
            var is_below_the_fold = scrolltop > fold,
                menu;
            if (is_below_the_fold !== was_below_the_fold) {
                $('body').toggleClass('fixed', is_below_the_fold);

                menu = $('#barTopMenu').remove();
                if (is_below_the_fold) {
                    menu.append(
                        $('.action-menu-list li', menu).remove().addClass('from-action-menu')
                    );
                    menu.appendTo('#barBottomLeft');
                } else {
                    $('.action-menu-list', menu).append(
                        $('.from-action-menu', menu).remove().removeClass('from-action-menu')
                    );
                    menu.appendTo('#flex-header');

                    $('#barTopMenu-toggle').prop('checked', false);
                }

                was_below_the_fold = is_below_the_fold;
            }
        };

    STUDIP.HeaderMagic = {
        enable: function () {
            fold = $('#flex-header').height();
            STUDIP.Scroll.addHandler('header', scroll);
        },
        disable : function () {
            STUDIP.Scroll.removeHandler('header');
            $('body').removeClass('fixed');
        }
    };

    $(document).ready(function () {
        // Test if the header is actually present
        if ($('#barBottomContainer').length > 0) {
            STUDIP.HeaderMagic.enable();
        }

        $(window).on('scroll resize', function() {
            moveTopAvatar();
        });
    });

    $('#barBottomright').ready(function(){
        $('#barBottomright ul li:not(".action-menu-item"):contains("Logout")').addClass('responsive-visible');
    })

    $('#notification_container').ready(function(){
        moveTopAvatar();
    });

    function moveTopAvatar() {
        if ($(window).scrollTop() >= $('#flex-header').offset().top
            && !(window.matchMedia('(max-width: 800px)').matches) ) {
            $('#notification_container').addClass('fixed');
            $('#barTopAvatar').addClass('fixed');
            $('#barTopAvatar .action-menu-icon').addClass('fixed');
            $('#barTopAvatar .action-menu-content').addClass('fixed');
        } else {
            $('#notification_container').removeClass('fixed');
            $('#barTopAvatar').removeClass('fixed');
            $('#barTopAvatar .action-menu-icon').removeClass('fixed');
            $('#barTopAvatar .action-menu-content').removeClass('fixed');
        }
    };

}(jQuery));

(function ($) {

    // Render a version of the icon with a punched out area for the badge
    function renderCanvas(canvas, width, height) {
        var target       = canvas.getContext('2d'),
            aspect_ratio = height ? width / height : 1;

        target.clearRect(0, 0, canvas.width, canvas.height);

        if (width === 128 && height === 32) {
            target.drawImage(this, 0, 5, 106, 28, 5, 5, 28 * aspect_ratio, 28);
        } else {
            target.drawImage(this, 14, 8, 56 * aspect_ratio, 56);
        }

        target.globalCompositeOperation = 'destination-out';
        target.beginPath();
        target.arc(canvas.width - 16, 16, 22, 0, 2 * Math.PI);
        target.fill();

        $(canvas).closest('a').addClass('canvasready');
    }

    $(document).ready(function () {
        $('html.canvas img.headericon.original').each(function () {
            var canvas = $('<canvas width="84" height="64">').addClass('headericon punch').insertAfter(this),
                width  = $(this).width(),
                height = $(this).height(),
                img    = new Image();

            // The callback is bound back to the original image because
            // context.drawImage requires the image to be in the dom or
            // the result is quirky.
            // The onload is neccessary due to the various browsers handling
            // the load event differently.
            img.onload = renderCanvas.bind(this, canvas[0], width, height);
            img.src    = this.src;
        });
    });

}(jQuery));
