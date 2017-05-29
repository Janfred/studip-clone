/*jslint browser: true, nomen: true, plusplus: true*/
/*global STUDIP, jQuery, _ */
(function ($, STUDIP, _) {
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
        var main = $('#barTopMenu'),
            sink = $('li.overflow', main),
            y,
            element,
            elements = $([]), // Yes, we really need an empty object;
            counter = 1;
        if (main.length === 0 || sink.length === 0) {
            return;
        }

        // Reset sink (hide and lose all content)
        main.removeClass('overflown');
        $('li', sink).remove().insertBefore(sink);

        if ($('html').is('.responsive-display')) {
            return;
        }

        // Check whether the elements need to be rearranged
        y = $('a:first', main).position().top;
        if (sink.prev().position().top > y) {

            element = sink.prev();
            while (element.length > 0 && (element.position().top > y || counter-- > 0)) {
                elements = elements.add(element);
                element = element.prev();
            }

            $('ul', sink).prepend(elements.remove());
        }
        main.toggleClass('overflown', sink.find('li').length > 0);

        setCookie('navigation-length', main.children(':not(.overflow)').length, 30);
    };

    // Throttle shrinker
    STUDIP.NavigationShrinker = _.throttle(shrinker, 100);

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

}(jQuery, STUDIP, _));
