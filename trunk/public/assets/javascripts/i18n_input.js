/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */

(function ($, STUDIP) {
    'use strict';

    $(document).ready(function () {
        STUDIP.i18n.init();
        STUDIP.i18n.startFormSubmitHandler();
    });

    STUDIP.i18n = {
        init: function () {
            $('div.i18n_group:not(.single_lang)').each(function () {
                var languages = $(this).find('input, textarea'),
                    select    = $('<select tabindex="-1">').addClass('i18n').css('background-image', $(languages).first().css('background-image'));
                select.change(function (event) {
                    var opt   = $(this).find('option:selected'),
                        index = opt.index();
                    languages.not(':eq(' + index + ')').hide();
                    languages.eq(index).show().focus();
                    $(this).css('background-image', opt.css('background-image'));

                    if (event.hasOwnProperty('originalEvent')) {
                        // Reset validator on not programmatically changed select
                        $(this).closest('form').data('validator').reset($(this).siblings());
                    }
                });
                languages.each(function (id, lang) {
                    select.append($('<option>', {text: $(lang).data().lang_desc}).css('background-image', $(lang).css('background-image')));
                });
                $(this).prepend(select);
                languages.css('background-image', '').not(':eq(0)').hide();
            });
        },
        startFormSubmitHandler: function () {
            $(document).on('submit', 'form:has(div.i18n_group:not(.single_lang))', function () {
                var all = $();

                $('div.i18n_group:has(.i18n[required])', this).each(function () {
                    var invalid = $('.i18n[required]:not(select)', this).filter(':not([value]),[value=""]').first();
                    if (invalid.length === 0) {
                        return;
                    }
                    // Show invalid element, hide others
                    invalid.show().siblings(':not(select)').hide();

                    // Adjust select
                    $(invalid).siblings('select').val($(invalid).data().lang_desc).change();

                    // Store invalid element
                    all = all.add(invalid);
                });

                // No invalid element, bail out
                if (all.length === 0) {
                    return true;
                }

                // Focus on first invalid element
                all.first().focus();
                $(window).trigger('resize');
                return false;
            });
        }
    };

}(jQuery, STUDIP));
