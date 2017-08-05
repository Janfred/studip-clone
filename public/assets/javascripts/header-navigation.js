/*jslint browser: true, nomen: true, plusplus: true*/
/*global STUDIP, jQuery */
(function ($, STUDIP) {
    'use strict';

    function setCookie(name, value, expiry_days) {
        var chunks = [name + '=' + value],
            date;
        if (expiry_days !== undefined) {
            date = new Date();
            date.setTime(date.getTime() + expiry_days * 24 * 60 * 60 * 1000);

            chunks.push('expires=' + date.toUTCString());
        }
        chunks.push('path=' + STUDIP.URLHelper.getURL('a', true).slice(0, -1));

        document.cookie = chunks.join(';');
    }

    // Enable shrinking of navigation
    var shrinker  = function () {
        var main  = $('#barTopMenu'),
            sink  = $('li.overflow', main),
            x     = 0,
            index = false,
            total = 0;
        if (main.length === 0 || sink.length === 0) {
            return;
        }

        // Reset sink (hide and lose all content)
        main.removeClass('overflown');
        $('> label > a', sink).removeAttr('data-badge');
        $('li', sink).remove().insertBefore(sink);

        if ($('html').is('.responsive-display')) {
            return;
        }

        $('li:not(.overflow)', main).each(function (idx) {
            var this_x = $(this).position().left;
            if (this_x > x) {
                x = this_x;
            } else {
                index = idx;
                return false;
            }
        });

        if (index !== false) {
            $('li:not(.overflow)', main)
                .slice(index - 2)
                .detach()
                .prependTo($('ul', sink))
                .each(function () {
                    total += parseInt($('a', this).data().badge, 10) || 0;
                });

            main.addClass('overflown');
            $('> label > a', sink).attr('data-badge', total);
        }

        setCookie('navigation-length', main.children(':not(.overflow)').length, 30);
    };

    // Throttle shrinker
    STUDIP.NavigationShrinker = shrinker;

    // Hide sink on touch elsewhere
    $(document).on('touchstart', function (event) {
        if ($(event.target).closest('li.overflow').length === 0) {
            $('#header-sink').prop('checked', false);
        }
        if ($(event.target).closest('li.has-subnavigation').length === 0) {
            $('.responsive-toggle').prop('checked', false);
        }
    });

    // Reshrink on resize
    $(window).on('resize', function () {
        STUDIP.NavigationShrinker();
    });

    // Shrink on domready
    $(document).ready(function () {
        STUDIP.NavigationShrinker();
    });

}(jQuery, STUDIP));
