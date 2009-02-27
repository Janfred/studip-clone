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

STUDIP.OverDiv = Object.extend(Class.create(),
	{
		overdivs: {},
		BindInline: function(options, event) {
			event = Event.extend(event);
			if(!this.overdivs[options.id]){
				options.event_type = event.type;
				this.overdivs[options.id] = new STUDIP.OverDiv(options);
			}
			this.overdivs[options.id].show(event);
			return false;
		},
		
		BindToEvent: function(options, event_type) {
			event_type = event_type || 'mouseover';
			if(!this.overdivs[options.id]){
				options.event_type = event.type;
				this.overdivs[options.id] = new STUDIP.OverDiv(options);
				Event.observe($(options.initiator), event_type, this.overdivs[options.id].show.bindAsEventListener(this.overdivs[options.id]));
			}
			return this.overdivs[options.id];
		}
	}
);

STUDIP.OverDiv.prototype = {
	options: {
				id:'',
				title:'',
				content:'',
				content_url:'',
				content_element_type:'',
				position: 'bottom right',
				width: 400,
				is_moveable: true,
				inititator: null,
				event_type: 'mouseover'
	},
	is_drawn: false,
	is_hidden: true,
	id: '',
	container: null,
	title: null,
	content: null,
	
	initialize: function(options) {
		Object.extend(this.options, options || {});
		this.id = this.options.id;
		this.initiator = $(this.options.initiator);
		if(options.content_element_type){
			this.options.content_url = STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/content_element/get_formatted/' + options.content_element_type + '/' + this.id;
		}
	},
	
	draw: function() {
		if(!this.is_drawn){
			var outer = new Element('div', {className: 'overdiv', id: 'overdiv_' + this.id});
			var inner = new Element('div', {className: 'title'});
			var title = new Element('h4', {className: 'title'});
			var closer = new Element('a', {className: 'title', href:'#'});
			var content = new Element('div', {className: 'content'});
			if(this.options.is_moveable){
				closer.appendChild(new Element('img', {src: 'assets/images/hide.gif'}));
				Event.observe(closer, 'click', this.hide.bindAsEventListener(this));
				Event.observe(inner, 'dblclick', this.scale.bindAsEventListener(this));
				new Draggable(outer, {scroll:window, handle:inner});
			}
			title.innerHTML = this.options.title;
			content.innerHTML = this.options.content;
			this.title = title;
			this.content = content;
			inner.appendChild(title);
			inner.appendChild(closer);
			outer.appendChild(inner);
			outer.appendChild(content);
			this.container = outer;
			this.container.absolutize();
			this.container.setStyle({width: this.options.width + 'px'});
			this.container.hide();
			$('overdiv_container').appendChild(this.container);
			this.is_drawn = true;
			if(this.options.content_url){
				var self = this;
				new Ajax.Request(this.options.content_url, {
						method: 'get',
						onSuccess: function(transport){self.update(transport)}
				});
			}
		}
	},
	
	update: function(transport){
		this.title.innerHTML = transport.responseJSON.title;
		this.content.innerHTML = transport.responseJSON.content;
	},
	
	getPosition: function(){
		var x = this.initiator.cumulativeOffset().left;
		var y = this.initiator.cumulativeOffset().top;
		var ho = this.initiator.getWidth() / 2;
		var vo = this.initiator.getHeight() / 2;
		var positions = $w(this.options.position);
		for(i = 0; i < positions.length; ++i) {
			switch(positions[i].toLowerCase()) {
			case 'left':
				ho = this.container.getWidth() * -1;
				break;
			case 'right':
				ho = this.initiator.getWidth();
				break;
			case 'center':
				ho = this.initiator.getWidth() / 2;
				break;
			case 'top':
				vo = this.container.getHeight() * -1;
				break;
			case 'middle':
				vo = this.initiator.getHeight() / 2;
				break;
			case 'bottom':
				vo = this.initiator.getHeight();
				break;
			default:
			}
		}
		return {left: Math.floor(x + ho), top: Math.floor(y + vo) };
	},
	
	show: function(event){
		this.draw();
		this.container.setStyle(this.getPosition());
		this.container.show();
		this.is_hidden = false;
		if(this.options.event_type == 'mouseover'){
			Event.observe(this.initiator, 'mouseout', this.hide.bindAsEventListener(this));
		}
		event.stop();
	},
	
	hide: function(event){
		if(!(this.options.is_moveable && this.isChildOfContainer($(event.relatedTarget)))){
			this.container.hide();
			this.is_hidden = true;
		}
		if(this.options.event_type == 'mouseover'){
			Event.stopObserving(this.initiator, 'mouseout', this.hide.bindAsEventListener(this));
		}
		event.stop();
	},
	
	scale: function(event){
		new Effect.Scale(this.container, this.container.getWidth() <= this.options.width ? Math.floor(this.options.width/2) : Math.floor(this.options.width/8), {scaleContent:false,scaleY:false});
	},
	
	isChildOfContainer: function(obj){
		var i = 3;
		do {
			if(obj == this.container)
				return true;
			if(obj) obj = obj.parentNode;
		} while(obj && i--);
		return false;
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
