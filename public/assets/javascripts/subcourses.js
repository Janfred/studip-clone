/*jslint browser: true, indent: 4 */
/*global jQuery */

(function ($) {
    'use strict';

    // Open action menu on click on the icon
    $(document).on('click', '.toggle-subcourses', function (event) {
        var row = $(this).closest('tr');

        if ($(this).hasClass('open')) {
            $(this).removeClass('open');
            $(this).children('.icon-shape-remove').addClass('hidden-js');
            $(this).children('.icon-shape-add').removeClass('hidden-js');
            $('tr.subcourse-' + $(this).closest('tr').data('course-id')).addClass('hidden-js');
            row.removeClass('has-subcourses');
        } else if ($(this).hasClass('loaded')) {
            $(this).addClass('open');
            $(this).children('.icon-shape-add').addClass('hidden-js');
            $(this).children('.icon-shape-remove').removeClass('hidden-js');
            $('tr.subcourse-' + $(this).closest('tr').data('course-id')).removeClass('hidden-js');
            row.addClass('has-subcourses');
        } else {
            $.ajax(
                $(this).data('get-subcourses-url'),
                {
                    success: function (data, status, xhr) {
                        $(data).insertAfter(row);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert('Status: ' + textStatus + "\nError: " + errorThrown);
                    }
                }
            );
            $(this).addClass('loaded').addClass('open');
            $(this).children('.icon-shape-add').addClass('hidden-js');
            $(this).children('.icon-shape-remove').removeClass('hidden-js');
            row.addClass('has-subcourses');
        }

        // Stop event so the following close event will not be fired
        event.stopPropagation();

        return false;
    });

}(jQuery));
