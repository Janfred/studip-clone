/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */

/* ------------------------------------------------------------------------
 * News
 * ------------------------------------------------------------------------ */
(function ($, STUDIP) {
    'use strict';

    STUDIP.News = {
        /**
         * (Re-)initialise news-page, f.e. to stay in dialog
         */
        init: function (id) {
            // prevent forms within dialog from reloading whole page, and reload dialog instead
            $('#' + id + ' form').on('click', function (event) {
                $(this).data('clicked', $(event.target));
            });
            $(document).on('change', '#' + id + ' form .news_date', function () {
                // This is neccessary since datepickers are initialiszed on focus
                // which might not have occured yet
                STUDIP.UI.Datepicker.init();

                var start = $('#news_startdate').blur().datepicker('getDate'),
                    duration,
                    end,
                    result;
                if ($(this).is('#news_duration')) {
                    duration = window.parseInt(this.value, 10);
                    result   = new Date(start);
                    result.setDate(result.getDate() + duration);

                    $('#news_enddate').datepicker('setDate', result);
                } else {
                    start    = $('#news_startdate').datepicker('getDate');
                    end      = $('#news_enddate').datepicker('getDate');
                    duration = (end - start) / (24 * 60 * 60 * 1000);
                    duration = Math.max(0, duration);

                    $('#news_duration').val(duration);
                }
            });

            $('#' + id + ' form').on('submit', function (event) {
                event.preventDefault();

                var textarea, button, form_route, form_data;
                if (STUDIP.editor_enabled) {
                    textarea = $('textarea.news_body');
                    // wysiwyg is active, ensure HTML markers are set
                    textarea.val(STUDIP.wysiwyg.markAsHtml(textarea.val()));
                }

                button     = $(this).data('clicked').attr('name');
                form_route = $(this).attr('action');
                form_data  = $(this).serialize() + '&' + button + '=1';

                $(this).find('input[name=' + button + ']').showAjaxNotification('left');
                STUDIP.News.update_dialog(id, form_route, form_data);
            });
        },

        init_dialog: function () {
            $('.add_toolbar').addToolbar();

            if (document.createElement('textarea').style.resize === undefined) {
                $('textarea.resizable').resizable({
                    handles: 's',
                    minHeight: 50,
                    zIndex: 1
                });
            }
        },

        get_dialog: function (id, route, from_x, from_y) {
            // initialize dialog
            $('body').append('<div id="' + id + '"></div>');
            $('#' + id).dialog({
                modal: true,
                resizable: false,
                width: 100,
                height: 40,
                title: 'Dialog wird geladen...'.toLocaleString(),
                hide: 'fadeOut',
                // define close animation
                beforeClose: function () {
                    $('#' + id).dialog('widget').stop(true, true);
                    $('#' + id).dialog('widget').animate({
                        width: 100,
                        height: 40,
                        left: from_x - 50,
                        top: $(document).scrollTop() + from_y - 20,
                        opacity: 0
                    }, {
                        duration: 400,
                        easing: 'swing'
                    });
                }
            });
            // show pre-loading dialog animation
            $('#' + id).html('<div class="ajax_notification" style="text-align: center; padding-right: 24px; padding-top: 55px"><div class="notification"></div></div>');
            $('#' + id).dialog('option', 'position', [from_x - 50, from_y - 20]);
            $('#' + id).dialog('widget').css('opacity', 0);
            $('#' + id).dialog('widget').animate({
                width: STUDIP.News.dialog_width,
                height: STUDIP.News.dialog_height,
                left: (window.innerWidth / 2) - (STUDIP.News.dialog_width / 2),
                top: $(document).scrollTop() + (window.innerHeight / 2) - (STUDIP.News.dialog_height / 2),
                opacity: 1
            }, {
                duration: 400,
                easing: 'swing'
            });

            // load actual dialog content
            $.get(route, 'html').done(function (html, status, xhr) {
                $('#' + id).dialog('option', 'title', decodeURIComponent(xhr.getResponseHeader('X-Title')));
                $('#' + id).dialog('widget').stop(true, true);
                // set to full size (even if dialog.close was triggered before)
                $('#' + id).dialog('widget').animate({
                    left: (window.innerWidth / 2) - (STUDIP.News.dialog_width / 2),
                    top: $(document).scrollTop() + (window.innerHeight / 2) - (STUDIP.News.dialog_height / 2),
                    opacity: 1
                }, 0);
                $('#' + id).dialog({
                    height: STUDIP.News.dialog_height,
                    width: STUDIP.News.dialog_width
                });
                $('#' + id).html(html);
                $('#' + id + '_content').css({
                    height : (STUDIP.News.dialog_height - 120) + 'px',
                    maxHeight: (STUDIP.News.dialog_height - 120) + 'px'
                });
                $('.ui-dialog-content').css('padding-right', '1px');

                STUDIP.News.init_dialog();
                STUDIP.News.init(id);
            }).fail(function () {
                window.alert('Fehler beim Aufruf des News-Controllers'.toLocaleString());
            });
        },

        update_dialog: function (id, route, form_data) {
            if (!STUDIP.News.pending_ajax_request) {
                STUDIP.News.pending_ajax_request = true;

                $.post(route, form_data, 'html').done(function (html) {
                    var obj;

                    STUDIP.News.pending_ajax_request = false;
                    if (html.length > 0) {
                        $('#' + id).html(html);
                        $('#' + id + '_content').css({
                            'height' : (STUDIP.News.dialog_height - 120) + 'px',
                            'maxHeight': (STUDIP.News.dialog_height - 120) + 'px'
                        });
                        // scroll to anker
                        obj = $('a[name=anker]');
                        if (obj.length > 0) {
                            $('#' + id + '_content').scrollTop(obj.position().top);
                        }
                    } else {
                        $('#' + id).dialog('close');
                        obj = $('#admin_news_form');
                        if (obj.length > 0) {
                            $('#admin_news_form').submit();
                        } else {
                            location.replace(STUDIP.URLHelper.getURL(location.href, {nsave: 1}));
                        }
                    }

                    STUDIP.News.init_dialog();
                    STUDIP.News.init(id);
                }).fail(function () {
                    STUDIP.News.pending_ajax_request = false;
                    window.alert('Fehler beim Aufruf des News-Controllers'.toLocaleString());
                });
            }
        },

        toggle_category_view: function (id) {
            if ($('input[name=' + id + '_js]').val() === 'toggle') {
                $('input[name=' + id + '_js]').val('');
            } else {
                $('input[name=' + id + '_js]').val('toggle');
            }
            if ($('#' + id + '_content').is(':visible')) {
                $("#" + id + '_content').slideUp(400);
                $('#' + id + ' input[type=image]:first')
                    .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/arr_1right.svg');
            } else {
                $('#' + id + '_content').slideDown(400);
                $('#' + id + ' input[type=image]:first')
                    .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/arr_1down.svg');
            }
        }
    };

    $(document).ready(function () {
        STUDIP.News.dialog_width = window.innerWidth * (1 / 2);
        STUDIP.News.dialog_height = window.innerHeight - 60;
        if (STUDIP.News.dialog_width < 550) {
            STUDIP.News.dialog_width = 550;
        }
        if (STUDIP.News.dialog_height < 400) {
            STUDIP.News.dialog_height = 400;
        }
        STUDIP.News.pending_ajax_request = false;

        $(document).on('click', 'a[rel~="get_dialog"]', function (event) {
            event.preventDefault();
            var from_x = $(this).position().left + ($(this).outerWidth() / 2),
                from_y = $(this).position().top + ($(this).outerHeight() / 2) - $(document).scrollTop();
            STUDIP.News.get_dialog('news_dialog', $(this).attr('href'), from_x, from_y);
        });

        $(document).on('click', 'a[rel~="close_dialog"]', function (event) {
            event.preventDefault();
            $('#news_dialog').dialog('close');
        });

        // open/close categories without ajax-request
        $(document).on('click', '.news_category_header', function (event) {
            event.preventDefault();
            STUDIP.News.toggle_category_view($(this).parent('div').attr('id'));
        });
        $(document).on('click', '.news_category_header input[type=image]', function (event) {
            event.preventDefault();
        });
    });
}(jQuery, STUDIP));
