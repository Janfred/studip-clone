/*
 *  studipAjax.js
 *
 * Ajax functions for Stud.IP
 *
 * Copyright (c) 2007 
 *
 * Permission to use, copy, modify, and distribute this software
 * and its documentation for any purposes and without
 * fee is hereby granted provided that this copyright notice
 * appears in all copies. 
 *
 * Of course, this soft is provided "as is" without express or implied
 * warranty of any kind.
 *
 * $Id:  $
 *
 */

function studipNotepad ( ii ) {
	if ($(ii).visible()) {
		Effect.SlideUp(ii);
	} else {		
		new Ajax.Request("ajaxserver.php?ajax_cmd=" + ii, {
			onSuccess: function(transport){
				$(ii + "_txt").update(transport.responseText);
				Effect.SlideDown(ii,{
					afterFinish: function(){
						Effect.Fade(ii, {to: 0.9 });	// for IE
						new Effect.ScrollTo(ii, {offset:-50});
					}
				});
				}
			});
	}
}
