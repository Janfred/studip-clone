/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */

(function ($, STUDIP) {
    'use strict';

    $(document).ready(function () {
        STUDIP.i18n.init();
    });

    $(document).on('dialog-update', function (event, data) {
        STUDIP.i18n.init(data.dialog);
    });

    STUDIP.i18n = {
        init: function (root) {
            $('.i18n_group', root).each(function () {
                var languages = $(this).find('> .i18n'),
                    select    = $('<select tabindex="-1">').addClass('i18n').css('background-image', $(languages).first().data('icon'));
                select.change(function () {
                    var opt   = $(this).find('option:selected'),
                        index = opt.index();
                    languages.not(':eq(' + index + ')').hide();
                    languages.eq(index).show().focus();
                    $(this).css('background-image', opt.css('background-image'));
                });
                languages.each(function (id, lang) {
                    select.append($('<option>', {text: $(lang).data('lang')}).css('background-image', $(lang).data('icon')));
                });
                $(this).append(select);
                languages.not(':eq(0)').hide();

                $('.i18n[required]').on('invalid', function () {
                    if ($(this).siblings('.i18n[required]:visible').is(':invalid')) {
                        return;
                    }
                    $(this).show().siblings('.i18n[required]').hide();
                    $(this).siblings('select').val($(this).data('lang')).change();
                });
            });
        }
    };

}(jQuery, STUDIP));
