/*jslint browser: true, nomen: true */
/*global jQuery, STUDIP, _ */

(function ($, STUDIP, _) {
    'use strict';

    function handleBlockAppointments() {
        $('#block_appointments_days input').click(function () {
            var clicked_id = parseInt(this.id.split('_').pop(), 10);
            if (clicked_id === 0 || clicked_id === 1) {
                $('#block_appointments_days input:checkbox').prop('checked', function (i) {
                    return i === clicked_id;
                });
            } else {
                $('#block_appointments_days_0').prop('checked', false);
                $('#block_appointments_days_1').prop('checked', false);
            }
        });
    }

    $(document).ready(handleBlockAppointments);
    $(document).on('dialog-open dialog-update', handleBlockAppointments);

    $(document).on('change', 'select[name=room_sd]', function () {
        $('input[type=radio][name=room][value=room]').prop('checked', true);
    });

    $(document).on('focus', 'input[name=freeRoomText_sd]', function () {
        $('input[type=radio][name=room][value=freetext]').prop('checked', true);
    });

    $(document).on('click', 'a.bookable_rooms_action', function (event) {
        var select = $(this).prev('select')[0],
            me = $(this);
        if (select !== null && select !== undefined) {
            if (me.data('state') === 'enabled') {
                STUDIP.Raumzeit.disableBookableRooms(me);
            } else {
                if (me.data('options') === undefined) {
                    me.data('options', $(select).children('option').clone(true));
                } else {
                    $(select).empty().append(me.data('options').clone(true));
                }
                $.ajax({
                    type: 'POST',
                    url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/resources/helpers/bookable_rooms',
                    data: {
                        rooms: _.pluck(select.options, 'value'),
                        selected_dates: _.pluck($('input[name="singledate[]"]:checked'), 'value'),
                        singleDateID: $('input[name=singleDateID]').attr('value'),
                        new_date: _.map($('#startDate,#start_stunde,#start_minute,#end_stunde,#end_minute'), function (v) {
                            return {name: v.id, value: v.value};
                        })
                    },
                    success: function (result) {
                        if ($.isArray(result)) {
                            if (result.length) {
                                var not_bookable_rooms = _.map(result, function (v) {
                                    return $(select).children('option[value=' + v + ']').text().trim();
                                });
                                select.title = 'Nicht buchbare Räume:'.toLocaleString() + ' ' + not_bookable_rooms.join(', ');
                            } else {
                                select.title = '';
                            }
                            _.each(result, function (v) {
                                $(select).children('option[value=' + v + ']').prop('disabled', true);
                            });
                        } else {
                            select.title = '';
                        }
                        me.attr('title', 'Alle Räume anzeigen'.toLocaleString());
                        me.data('state', 'enabled');
                    }
                });
            }
        }
        event.preventDefault();
    });

    $(document).on('change', 'input[name="singledate[]"]', function () {
        STUDIP.Raumzeit.disableBookableRooms($('a.bookable_rooms_action'));
    });

    $(document).ready(function () {
        $('a.bookable_rooms_action').show();
    });

    STUDIP.Dialog.handlers.header['X-Raumzeit-Update-Times'] = function (json) {
        var info = $.parseJSON(json);
        $('.course-admin #course-' + info.course_id + ' .raumzeit').html(info.html);
    };

    $(document).on('change', '.datesBulkActions', function () {
        var $button = $(this).next('button');
        if ($(this).val() === 'delete') {
            $button.attr('data-confirm', 'Wollen Sie die gewünschten Termine wirklich löschen?'.toLocaleString());
        } else {
            if ($button.attr('data-confirm')) {
                $button.removeAttr('data-confirm');
            }
        }
    });

    $(document).on('change', '#edit-cycle', function () {
        var start = $('input[name=start_time]', this)[0],
            end = $('input[name=end_time]', this)[0],
            changed = start.defaultValue
                && end.defaultValue
                && (start.value !== start.defaultValue || end.value !== end.defaultValue);
        // check if new time exceeds the current one and add security question if necessary
        if (changed && (start.value < start.defaultValue || end.value > end.defaultValue)) {
            $(this).attr('data-confirm', 'Wenn Sie die regelmäßige Zeit ändern, '
                + 'verlieren Sie die Raumbuchungen für alle in der Zukunft liegenden Termine! '
                + 'Sind Sie sicher, dass Sie die regelmäßige Zeit ändern möchten?'.toLocaleString());
        } else {
            // remove security question - not necessary (any more)
            $(this).attr('data-confirm', null);
        }
    });
}(jQuery, STUDIP, _));
