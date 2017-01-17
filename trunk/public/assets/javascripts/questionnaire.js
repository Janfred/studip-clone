/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, jQuery, STUDIP */

STUDIP.Questionnaire = {
    lastUpdate: null,
    periodicalPushData: function () {
        var questionnaires = {
            "questionnaire_ids": [],
            "last_update": STUDIP.Questionnaire.lastUpdate
        };
        STUDIP.Questionnaire.lastUpdate = Math.floor(Date.now() / 1000);
        jQuery(".questionnaire_results").each(function () {
            questionnaires.questionnaire_ids.push(jQuery(this).data("questionnaire_id"));
        });
        if (questionnaires.questionnaire_ids.length > 0) {
            return questionnaires;
        }
    },
    updateQuestionnaireResults: function (data) {
        for (var questionnaire_id in data) {
            if (data[questionnaire_id].html) {
                var new_view = jQuery(data[questionnaire_id].html);
                jQuery(".questionnaire_results.questionnaire_" + questionnaire_id).replaceWith(new_view);
                jQuery(document).trigger("dialog-open");
            }
        }
    },
    updateOverviewQuestionnaire: function (data) {
        if (jQuery("#questionnaire_overview tr#questionnaire_" + data.questionnaire_id).length > 0) {
            jQuery("#questionnaire_overview tr#questionnaire_" + data.questionnaire_id).replaceWith(data.overview_html);
        } else {
            if (jQuery("#questionnaire_overview").length > 0) {
                jQuery(data.overview_html).hide().insertBefore("#questionnaire_overview > tbody > :first-child").delay(300).fadeIn();
                jQuery("#questionnaire_overview .noquestionnaires").remove();
            }
            if (data.message) {
                jQuery("#layout_content").prepend(data.message);
            }
        }
        if (jQuery(".questionnaire_widget .widget_questionnaire_" + data.questionnaire_id).length > 0) {
            if (data.widget_html) {
                jQuery(".questionnaire_widget .widget_questionnaire_" + data.questionnaire_id).replaceWith(data.widget_html);
            } else {
                jQuery(".questionnaire_widget .widget_questionnaire_" + data.questionnaire_id).remove();
            }
        } else {
            if (jQuery(".questionnaire_widget").length > 0 && data.widget_html) {
                jQuery(".ui-dialog-content").dialog("close");
                if (jQuery(".questionnaire_widget > article").length > 0) {
                    jQuery(data.widget_html).hide().insertBefore(".questionnaire_widget > article:first-of-type, .questionnaire_widget > section:first-of-type").delay(300).fadeIn();
                } else {
                    jQuery(".questionnaire_widget .noquestionnaires").replaceWith(data.widget_html).hide().delay(300).fadeIn();
                }
            } else {
                if (data.message) {
                    jQuery("#layout_content").prepend(data.message);
                    jQuery.scrollTo("#layout_content", 400);
                }
            }
        }
        jQuery(document).trigger("dialog-open");
    },
    updateWidgetQuestionnaire: function (html) {
        //update the results of a questionnaire
        var questionnaire_id = jQuery(html).data("questionnaire_id");
        jQuery(".questionnaire_widget .widget_questionnaire_" + questionnaire_id).replaceWith(html);
        jQuery(document).trigger("dialog-open");
    },
    beforeAnswer: function () {
        var form = jQuery(this).closest("form")[0];
        var questionnaire_id = jQuery(form).closest("article").data("questionnaire_id");
        if (jQuery(form).is(".questionnaire_widget form")) {
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + "dispatch.php/questionnaire/answer/" + questionnaire_id,
                data: new FormData(form),
                cache: false,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (output) {
                    jQuery(form).replaceWith(output);
                    jQuery(document).trigger("dialog-open");
                }
            });
            jQuery(form).css("opacity", "0.5");
            return false;
        } else {
            return true;
        }

    },
    addQuestion: function (questiontype) {
        jQuery.ajax({
            "url": STUDIP.ABSOLUTE_URI_STUDIP + "dispatch.php/questionnaire/add_question",
            "data": {
                "questiontype": questiontype
            },
            "dataType": "json",
            "success": function (output) {
                jQuery(output.html).hide().insertBefore(".questionnaire_edit .add_questions").show("fade");
            }
        });
    }
};
jQuery(function () {
    jQuery(document).on("paste", ".questionnaire_edit .options > li input", function (ui) {
        var event = ui.originalEvent;
        var text = event.clipboardData.getData("text");
        text = text.split(/[\n\t]/);
        if (text.length > 1) {
            if (text[0]) {
                this.value += text.shift().trim();
            }
            var current = jQuery(this).closest("li");
            for (var i in text) {
                if (text[i].trim()) {
                    var li = jQuery(jQuery(this).closest(".options").data("optiontemplate"));
                    li.find("input").val(text[i].trim());
                    li.insertAfter(current);
                    current = li;
                }
            }
            STUDIP.Questionnaire.Test.updateCheckboxValues();
            event.preventDefault();
        }
    });
    jQuery(document).on("blur", ".questionnaire_edit .options > li:last-child input:text", function () {
        if (this.value) {
            jQuery(this).closest(".options").append(jQuery(this).closest(".options").data("optiontemplate"));
            jQuery(this).closest(".options").find("li:last-child input").focus();
        }
    });
    jQuery(document).on("click", ".questionnaire_edit .options .delete", function () {
        var icon = this;
        STUDIP.Dialog.confirm(jQuery(this).closest(".questionnaire_edit").find(".delete_question").text(), function () {
            jQuery(icon).closest("li").fadeOut(function() {
                jQuery(this).remove();
            });
        });
    });
    jQuery(document).on("click", ".questionnaire_edit .options .add", function () {
        jQuery(this).closest(".options").append(jQuery(this).closest(".options").data("optiontemplate"));
        jQuery(this).closest(".options").find("li:last-child input:text").focus();
    });

    /*
     * This fixes the tab problem in chartist see:
     * https://github.com/gionkunz/chartist-js/issues/119
     */
    jQuery(document).on("click", "article.studip .toggle", function () {
        jQuery(this).find('.ct-chart').each(function (i, e) {
            e.__chartist__.update();
        });
    });
});
