/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */
(function ($, STUDIP) {
    'use strict';

    STUDIP.Members = {
        addPersonToSelection: function (userId, name) {
            var target = $('#persons-to-add'),
                newEl  = $('<li>').html($('<span>').html(name).text()),
                input  = $('<input type="hidden" name="users[]">').val(userId),
                remove = $('<img>').attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg');

            remove.on('click', function () {
                $(this).parent().remove();
            });

            newEl.append(input, remove).appendTo(target);

            return false;
        }
    };

    $(document).on('click', 'a[rel~="comment_dialog"]', function (event) {
        var href      = $(this).attr('href'),
            container = $('<div>');

        // Load response into a helper container, open dialog after loading
        // has finished.
        container.load(href, function (response, status, xhr) {
            $(this).dialog({
                title:     decodeURIComponent(xhr.getResponseHeader('X-Title')) || '',
                width:     '40em',
                modal:     true,
                resizable: false
            });
        });

        event.preventDefault();
    });

    $(document).ready(function () {
        $('a.get-course-members').on('click', function () {
            var dataEl = $('article#course-members-' + $(this).data('course-id')),
                url;
            if ($.trim(dataEl.html()).length === 0) {
                url = $(this).data('get-members-url');

                dataEl.html($('<img>').attr({
                    width: 32,
                    height: 32,
                    src: STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg'
                }));

                $.get(url).done(function (html) {
                    dataEl.html(html);
                });
            }
        });
    });

}(jQuery, STUDIP));
