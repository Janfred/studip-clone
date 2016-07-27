(function($, STUDIP) {
    STUDIP.ActivityFeed = {
        user_id : null,
        polling: null,
        initial: true,
        scrolledfrom: null,
        maxheight: null,

        init: function() {
            STUDIP.ActivityFeed.maxheight = parseInt($('#stream-container').css('max-height').replace(/[^-\d\.]/g, ''));

            STUDIP.ActivityFeed.loadFeed('');

            $('#stream-container').scroll(function () {
                var scrollBottom = $('#stream-container').scrollTop() + $('#stream-container').height() + 250;

                if ($('#stream-container').prop('scrollHeight') < scrollBottom) {
                    STUDIP.ActivityFeed.loadFeed('');
                }
            });

        },

        getTemplate: _.memoize(function(name) {
            return _.template($("script." + name).html());
        }),

        loadFeed: function(filtertype) {
            if (STUDIP.ActivityFeed.user_id === null) {
                console.log('Could not retrieve activities, no valid user id found!');
                return false;
            }

            if (STUDIP.ActivityFeed.polling || !STUDIP.ActivityFeed.scrolledfrom) {
                return false;
            }

            STUDIP.ActivityFeed.polling = true;

            $.ajax(STUDIP.URLHelper.resolveURL('api.php/user/' + STUDIP.ActivityFeed.user_id
                    + '/activitystream?filtertype=' + filtertype + '&scrollfrom=' + STUDIP.ActivityFeed.scrolledfrom), {
                success: function(data) {
                    var stream        = STUDIP.ActivityFeed.getTemplate('activity_stream');
                    var activity      = STUDIP.ActivityFeed.getTemplate('activity');
                    var activity_urls = STUDIP.ActivityFeed.getTemplate('activity-urls');
                    var activities    = data;

                    var num_entries   = Object.keys(activities).length;


                    var lastelem = $(activities).last();

                    if(lastelem[0]) {
                        STUDIP.ActivityFeed.scrolledfrom  = lastelem[0].mkdate;
                    } else {
                        STUDIP.ActivityFeed.scrolledfrom = false;
                    }

                    if (STUDIP.ActivityFeed.initial) {            // replace data in DOM
                        $('#stream-container').html('');
                    }

                    $('#stream-container').append(stream({
                        stream        : activities,
                        num_entries   : num_entries,
                        activity      : activity,
                        activity_urls : activity_urls,
                        user_id       :  STUDIP.ActivityFeed.user_id
                    }));


                    STUDIP.ActivityFeed.initial = false;
                    STUDIP.ActivityFeed.polling = false;


                    if ($('#stream-container').height() < STUDIP.ActivityFeed.maxheight) {
                        STUDIP.ActivityFeed.loadFeed('');
                    }


                    $('.provider_circle').unbind( "click" ).click(function() {
                        $(this).parent().parent().children('.activity-content').toggle();
                    });

                    $("#toggle-all-activities").unbind( "click" ).click(function() {
                        $('.activity-content').toggle();
                        if( $("#toggle-all-activities").data('toggled') == 'true') {
                            $("#toggle-all-activities").data('toggled', 'false').attr('src', STUDIP.ASSETS_URL + "images/icons/blue/no-activity.svg");
                        } else {
                            $("#toggle-all-activities").data('toggled', 'true').attr('src', STUDIP.ASSETS_URL + "images/icons/blue/activity.svg");
                        }
                    });

                    $("#toggle-user-activities").unbind( "click" ).click(function() {
                        $('span.right').parent().parent().toggle();
                        if( $("#toggle-user-activities").data('toggled') == 'true') {
                            $("#toggle-user-activities").data('toggled', 'false').attr('src', STUDIP.ASSETS_URL + "images/icons/blue/visibility-visible/headache.svg");
                        } else {
                            $("#toggle-user-activities").data('toggled', 'true').attr('src', STUDIP.ASSETS_URL + "images/icons/blue/visibility-invisible/headache.svg");
                        }
                    });
                }
            });

        },

        update : function(html) {
            $('#afeed').replaceWith(html);

        }
    };
})(jQuery, STUDIP);
