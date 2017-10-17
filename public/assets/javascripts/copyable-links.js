/*jslint browser: true */
/*global jQuery */
(function ($) {
    'use strict';

    $(document).on('click', 'a.copyable-link', function (event) {
        event.preventDefault();

        // Create dummy element and position it off screen
        // This element must be "visible" (as in "not hidden") or otherwise
        // the copy command will fail
        var dummy = $('<textarea>').val(this.href).css({
            position: 'absolute',
            left: '-9999px'
        }).appendTo('body');

        // Select text and copy it to clipboard
        dummy[0].select();
        document.execCommand('Copy');
        dummy.remove();

        // Show visual confirmation
        $('<div class="copyable-link-confirmation">')
            .text('Link wurde kopiert'.toLocaleString())
            .insertBefore(this);
        $(this).parent().addClass('copyable-link-animation').on('animationend', function () {
            $(this).removeClass('copyable-link-animation').find('.copyable-link-confirmation').remove();
        });
    });
}(jQuery));
