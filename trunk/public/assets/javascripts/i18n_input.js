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
            $('div.i18n_group:not(.single_lang)', root).each(function () {
                var languages = $(this).find('input, textarea'),
                    select    = $('<select tabindex="-1">').addClass('i18n').css('background-image', $(languages).first().css('background-image'));
                select.change(function () {
                    var opt   = $(this).find('option:selected'),
                        index = opt.index();
                    languages.not(':eq(' + index + ')').hide();
                    languages.eq(index).show().focus();
                    $(this).css('background-image', opt.css('background-image'));
                });
                languages.each(function (id, lang) {
                    select.append($('<option>', {text: $(lang).data().lang_desc}).css('background-image', $(lang).css('background-image')));
                });
                $(this).append(select);
                languages.css('background-image', '').not(':eq(0)').hide();

                $('.i18n[required]').on('invalid', function () {
                    if ($(this).siblings('.i18n[required]:visible').is(':invalid')) {
                        return;
                    }
                    $(this).show().siblings('.i18n[required]').hide();
                    $(this).siblings('select').val($(this).data().lang_desc).change();
                });
            });
        }
    };

}(jQuery, STUDIP));
