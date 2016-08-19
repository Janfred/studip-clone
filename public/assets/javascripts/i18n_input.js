/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */

(function ($, STUDIP) {
    'use strict';

    $(document).ready(function () {
        STUDIP.i18n.init();
    });

    STUDIP.i18n = {
        init: function () {
            $('div.i18n_group:not(.single_lang)').each(function () {
                var languages = $(this).find('input, textarea'),
                    select    = $('<select tabindex="-1">').addClass('i18n').css('background-image', $(languages).first().css('background-image'));
                select.change(function () {
                    var opt   = $(this).find('option:selected'),
                        index = opt.index();
                    languages.not(':eq(' + index + ')').hide();
                    languages.eq(index).show();
                    $(this).css('background-image', opt.css('background-image'));
                });
                languages.each(function (id, lang) {
                    select.append($('<option>', {text: $(lang).data().lang_desc}).css('background-image', $(lang).css('background-image')));
                });
                $(this).prepend(select);
                languages.css('background-image', '').not(':eq(0)').hide();
            });
        }
    };

}(jQuery, STUDIP));
