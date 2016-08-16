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

    // Use select2 for crossbrowser compliant select styling and
    // handling
    $.fn.select2.amd.define("select2/i18n/de", [], function() {
        return {
            inputTooLong: function(e) {
                var t = e.input.length - e.maximum;
                return 'Bitte %u Zeichen weniger eingeben'.toLocaleString().replace('%u', t);
            },
            inputTooShort: function(e) {
                var t = e.minimum - e.input.length;
                return 'Bitte %u Zeichen mehr eingeben'.toLocaleString().replace('%u', t);
            },
            loadingMore: function() {
                return 'Lade mehr Ergebnisse...'.toLocaleString();
            },
            maximumSelected: function(e) {
                var t = [
                    'Sie können nur %u Eintrag auswählen'.toLocaleString(),
                    'Sie können nur %u Einträge auswählen'.toLocaleString()
                ];
                return t[e.maximum === 1 ? 0 : 1].replace('%u', e.maximum);
            },
            noResults: function() {
                return 'Keine Übereinstimmungen gefunden'.toLocaleString();
            },
            searching: function() {
                return 'Suche...'.toLocaleString();
            }
        };
    });
    $.fn.select2.defaults.set('language', 'de');

    $(document).on('ready dialog-update', function () {
        $('select.nested-select:not(:has(optgroup))').each(function () {
            var select_classes = $(this).attr('class'),
                option         = $('<option>'),
                wrapper        = $('<div class="select2-wrapper">'),
                placeholder;

            $(this).css('width', $(this).outerWidth(true));

            if ($('.is-placeholder', this).length > 0) {
                placeholder = $('.is-placeholder', this).text();

                option.attr('selected', $(this).val() === '');
                $('.is-placeholder', this).replaceWith(option);
            }

            $(this).select2({
                adaptDropdownCssClass: function () {
                    return select_classes;
                },
                allowClear: placeholder !== undefined,
                minimumResultsForSearch: $(this).closest('.sidebar').length > 0 ? 15 : 10,
                placeholder: placeholder,
                templateResult: function (data, container) {
                    if (data.element) {
                        var option_classes = $(data.element).attr('class'),
                            element_data   = $(data.element).data();
                        $(container).addClass(option_classes);

                        // Allow text color changes (calendar needs this)
                        if (element_data.textColor) {
                            $(container).css('color', element_data.textColor);
                        }
                    }
                    return data.text;
                },
                templateSelection: function (data, container) {
                    var result       = $('<span class="select2-selection__content">').text(data.text),
                        element_data = $(data.element).data();
                    if (element_data && element_data.textColor) {
                        result.css('color', element_data.textColor);
                    }

                    return result;
                },
                width: 'style'
            });

            $(this).next().andSelf().wrapAll(wrapper);
        });

        // Unfortunately, this code needs to be duplicated because jQuery
        // namespacing kind of sucks. If the below change handler is namespaced
        // and we trigger that namespaced event here, still all change handlers
        // will execute (which is bad due to $(select).change(form.submit())).
        $('select:not([multiple])').each(function () {
            $(this).toggleClass('has-no-value', this.value === '').blur();
        });
    }).on('change', 'select:not([multiple])', function () {
        $(this).toggleClass('has-no-value', this.value === '').blur();
    }).on('dialog-close', function (event, data) {
        $('select.nested-select:not(:has(optgroup))', data.dialog).select2('close');
    });

}(jQuery, STUDIP));

