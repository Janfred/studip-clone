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

            // for browsers supporting native HTML5 form validation:
            // add invalid-handler to every input and textarea on the page
            $('input, textarea').on('invalid', function() {
                $(this).attr('aria-invalid', 'true').change(function () {
                    $(this).removeAttr('aria-invalid');
                });

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

    function createSelect2(element) {
        if ($(element).data('select2')) {
            return;
        }

        var select_classes = $(element).removeClass('select2-awaiting').attr('class'),
            option         = $('<option>'),
            width          = $(element).outerWidth(true),
            wrapper        = $('<div class="select2-wrapper">').css('display', $(element).clone().css('display')),
            placeholder;

        $(wrapper).add(element).css('width', width);

        if ($('.is-placeholder', element).length > 0) {
            placeholder = $('.is-placeholder', element).text();

            option.attr('selected', $(element).val() === '');
            $('.is-placeholder', element).replaceWith(option);
        }

        $(element).select2({
            adaptDropdownCssClass: function () {
                return select_classes;
            },
            allowClear: placeholder !== undefined,
            minimumResultsForSearch: $(element).closest('.sidebar').length > 0 ? 15 : 10,
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

        $(element).next().andSelf().wrapAll(wrapper);
    }

    $(document).on('ready dialog-update', function () {
        // Well, this is really nasty: Select2 can't determine the select
        // element's width if it is hidden (by itself or by it's parent).
        // This is due to the fact that elements are not rendered when hidden
        // (which seems pretty obvious when you think about it) but elements
        // only have a width when they are rendered (pretty obvious as well).
        //
        // Thus, we need to handle the visible elements first and apply
        // select2 directly.
        $('select.nested-select:not(:has(optgroup)):visible').each(function () {
            createSelect2(this);
        });

        // The hidden need a little more love. The only, almost sane-ish
        // solution seems to be to attach a mutation observer to the closest
        // visible element from the requested select element and observe style,
        // class and attribute changes in order to detect when the select
        // element itself will become visible. Pretty straight forward, huh?
        $('select.nested-select:not(:has(optgroup)):hidden:not(.select2-awaiting)').each(function () {
            var observer = new window.MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if ($('select.select2-awaiting', mutation.target).length > 0) {
                        $('select.select2-awaiting', mutation.target).removeClass('select2-awaiting').each(function () {
                            createSelect2(this);
                        });
                        observer.disconnect();
                        observer = null;
                    }
                });
            });
            observer.observe($(this).closest(':visible')[0], {
                attributeOldValue: true,
                attributes: true,
                attributeFilter: ['style', 'class'],
                characterData: false,
                childList: true,
                subtree: false
            });

            $(this).addClass('select2-awaiting');
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
        $('select.nested-select:not(:has(optgroup))', data.dialog).each(function () {
            if (!$(this).data('select2')) {
                return;
            }
            $(this).select2('close');
        });
    });

}(jQuery, STUDIP));

