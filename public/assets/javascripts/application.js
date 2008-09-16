/* ------------------------------------------------------------------------
 * application.js
 * This file is part of Stud.IP - http://www.studip.de
 *
 * Stud.IP is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Stud.IP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Stud.IP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

/* ------------------------------------------------------------------------
 * the global STUDIP namespace
 * ------------------------------------------------------------------------ */
if (typeof STUDIP == "undefined" || !STUDIP) {
    var STUDIP = {};
}


/* ------------------------------------------------------------------------
 * study area selection for courses
 * ------------------------------------------------------------------------ */

STUDIP.study_area_selection = {

  url: function(action, args) {
    return STUDIP.ABSOLUTE_URI_STUDIP + "dispatch.php/course/study_areas/" +
           $A(arguments).join("/");
  },

  swishAndFlick: function(element, target) {

    // clone element
    var clone = element.cloneNode(true);
    element.parentNode.insertBefore(clone, element);
    clone.absolutize();

    target = $(target);
    var o = target.cumulativeOffset();
    o[1] += target.getHeight();

    new Effect.Parallel(
      [
        new Effect.Move(clone, { sync: true, x: o[0], y: o[1], mode: "absolute"}),
        new Effect.Opacity(clone, { sync: true, from: 1, to: 0})
      ],
      {
        duration:    0.4,
        delay:       0,
        transition:  Effect.Transitions.sinoidal,
        afterFinish: function() { clone.remove(); }
      });
  },

  add: function(id, course_id) {

      course_id = course_id || "";

      // may not be visible at the current
      $$(".study_area_selection_add_" + id).each(function(add) {
          // prevent selecting twice
          add.disable();
          new Effect.Opacity(add, {from: 1, to: 0, duration: 0.25,
            afterFinish: function() {
              add.setStyle({visibility: "hidden"}).enable();
            }
          });
        });

      new Ajax.Request(STUDIP.study_area_selection.url("add", course_id), {
        method: "post",
        parameters: { "id": id },
        onSuccess: function(transport) {
          STUDIP.study_area_selection.swishAndFlick($$(".study_area_selection_add_" + id)[0],
                                                    "study_area_selection_selected");
          $("study_area_selection_none").fade();
          $("study_area_selection_selected").insert(transport.responseText);
          STUDIP.study_area_selection.refreshSelection();
        }});
  },

  remove: function(id, course_id) {

      course_id = course_id || "";

      var selection = $("study_area_selection_" + id);

      if (selection.siblings().size() == 0) {
        $("study_area_selection_at_least_one").appear();
        $("study_area_selection_at_least_one").fade({ delay: 5, queue: 'end' });
        selection.shake();
        return;
      }

      new Ajax.Request(STUDIP.study_area_selection.url("remove", course_id), {
        method: "post",
        parameters: { "id": id },
        onSuccess: function(transport) {
          selection.remove();
          if (!$$("#study_area_selection_selected li").length) {
            $("study_area_selection_none").appear();
          }

          $$(".study_area_selection_add_" + id).each(function(add) {
            add.setStyle({opacity: 0, visibility: "visible"});
            new Effect.Opacity(add, {from: 0, to: 1});
          });

          STUDIP.study_area_selection.refreshSelection();
        },
        onFailure: function() {
          selection.appear();
        }
      });
  },

  expandSelection: function(id, course_id) {

    course_id = course_id || "";

    new Ajax.Request(STUDIP.study_area_selection.url("expand", course_id, id), {
      method: 'post',
      onSuccess: function(transport) {
        $("study_area_selection_selectables").down("ul").replace(transport.responseText);
      }});
  },

  refreshSelection: function() {
    $$("#study_area_selection_selected li").each(function(element, index) {
      if (index % 2) {
        element.removeClassName("odd").addClassName("even");
      } else {
        element.removeClassName("even").addClassName("odd");
      }
    });
  }
};



/* ------------------------------------------------------------------------
 * application wide setup
 * ------------------------------------------------------------------------ */
document.observe('dom:loaded', function() {

  // message highlighting
  $$(".effect_highlight").invoke('highlight');

  // ajax responder
  var indicator = $('ajax_notification');
  if (indicator) {
    Ajax.Responders.register({
      onCreate:   function(request) {
        if (Ajax.activeRequestCount) {
          request.usability_timer = setTimeout(function() {
            indicator.show();
          }, 100);
        }
      },
      onComplete: function(request) {
        clearTimeout(request.usability_timer);
        if (!Ajax.activeRequestCount) {
          indicator.hide();
        }
      }
    });
  }
});
