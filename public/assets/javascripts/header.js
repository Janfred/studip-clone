/*jslint browser: true, nomen: true */
/*global jQuery, STUDIP, _ */

(function ($, STUDIP) {
    'use strict';

    var fold,
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
                    menu.prependTo('#flex-header');

                    STUDIP.NavigationShrinker();

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
    }).on('mousedown', '#avatar-arrow', function (event) {
        event.stopPropagation();
        $('#header_avatar_menu .action-menu-icon').trigger('mousedown');
    });

}(jQuery, STUDIP));
