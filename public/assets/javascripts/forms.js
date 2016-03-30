/*jslint browser: true, white: true, undef: true, nomen: true, plusplus: true, bitwise: true, newcap: true, indent: 4, unparam: true */
/*global jQuery, STUDIP */

/* ------------------------------------------------------------------------
 * Forms
 * ------------------------------------------------------------------------ */

(function ($, STUDIP) {
    'use strict';

    STUDIP.Forms = {
        initialize : function () {
            $("input[required],textarea[required]").attr('aria-required', true);
            $("input[pattern][title],textarea[pattern][title]").each(function () {
                $(this).data('message', $(this).attr('title'));
            });

            //localized messages
            $.tools.validator.localize('de', {
                '*'          : 'Bitte ändern Sie ihre Eingabe'.toLocaleString(),
                ':radio'     : 'Bitte wählen Sie einen Wert aus.'.toLocaleString(),
                ':email'     : 'Bitte geben Sie gültige E-Mail-Adresse ein'.toLocaleString(),
                ':number'    : 'Bitte geben Sie eine Zahl ein'.toLocaleString(),
                ':url'       : 'Bitte geben Sie eine gültige Web-Adresse ein'.toLocaleString(),
                '[max]'      : 'Der eingegebene Wert darf nicht größer als $1 sein'.toLocaleString(),
                '[min]'      : 'Der eingegebene Wert darf nicht kleiner als $1 sein'.toLocaleString(),
                '[required]' : 'Dies ist ein erforderliches Feld'.toLocaleString()
            });

            $('form').validator({
                position   : 'bottom left',
                offset     : [8, 0],
                message    : '<div><div class="arrow"/></div>',
                lang       : 'de',
                inputEvent : 'change'
            });

            $('form').bind("onBeforeValidate", function () {
                $("input").each(function () {
                    $(this).removeAttr('aria-invalid');
                });
            });

            $('form').bind("onFail", function (e, errors) {
                $.each(errors, function () {
                    this.input.attr('aria-invalid', 'true');
                    // get the fieldset that contains the invalid input
                    var fieldset = $(this.input).closest('fieldset');
                    // toggle the collapsed class if the fieldset is currently collapsed
                    if (fieldset.hasClass('collapsed')) {
                        fieldset.toggleClass('collapsed');
                    }
                    $.scrollTo(this.input);
                });
            });

            // for browsers supporting native HTML5 form validation:
            // add invalid-handler to every input and textarea on the page
            $('input, textarea').on('invalid', function() {
                // get the fieldset that contains the invalid input
                var fieldset = $(this).closest('fieldset');
                // toggle the collapsed class if the fieldset is currently collapsed
                if (fieldset.hasClass('collapsed')) {
                    fieldset.toggleClass('collapsed');
                }
            });

            $(document).on("change", "form.default label.file-upload input[type=file]", function (ev) {
                var selected_file = ev.target.files[0],
                    filename;
                if ($(this).closest("label").find(".filename").length) {
                    filename = $(this).closest("label").find(".filename");
                } else {
                    filename = $('<span class="filename"/>');
                    $(this).closest("label").append(filename);
                }
                filename.text(selected_file.name + " " + Math.ceil(selected_file.size / 1024) + "KB");
            });

        }
    };

    // Allow fieldsets to collapse
    $(document).on('click', 'form.default fieldset.collapsable legend,form.default.collapsable fieldset legend', function () {
        $(this).closest('fieldset').toggleClass('collapsed');
    });

    // Display a visible hint that indicates how many characters the user may
    // input if the element has a maxlength restriction.
    $(document).on('ready dialog-update', function () {
        $('form.default input[maxlength]:not(.no-hint)').each(function () {
            if ($(this).data('length-hint')) {
                return;
            }

            var width = $(this).outerWidth(true),
                hint  = $('<div class="length-hint">').hide(),
                wrap  = $('<div class="length-hint-wrapper">').width(width);

            $(this).wrap(wrap);

            hint.text('Zeichen verbleibend: '.toLocaleString());

            hint.append('<span class="length-hint-counter">');
            hint.insertBefore(this);

            $(this).focus(function () {
                hint.finish().show('slide', {direction: 'down'}, 300);
            }).blur(function () {
                hint.finish().hide('slide', {direction: 'down'}, 300);
            }).on('focus propertychange change keyup', function () {
                var count = $(this).val().length,
                    max   = parseInt($(this).attr('maxlength'), 10);

                hint.find('.length-hint-counter').text(max - count);
            });

            $(this).data('length-hint', true);

            $(this).trigger('change');
        });
    });

}(jQuery, STUDIP));

