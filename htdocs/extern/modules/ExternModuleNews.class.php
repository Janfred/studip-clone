<?
/**
* ExternModuleNews.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModuleNews
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleNews.class.php
// 
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternModule.class.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/views/extern_html_templates.inc.php");

class ExternModuleNews extends ExternModule {

	var $field_names = array();
	var $data_fields = array("date", "topic");
	var $registered_elements = array("Body", "TableHeader", "TableHeadrow", "TableRow",
																		"ContentNews", "Link", "TableFooter");

	/**
	*
	*/
	function ExternModuleNews () {
		$this->field_names = array
		(
				_("Datum/Autor"),
				_("Nachricht")
		);
		
	}
	
	function checkRangeId ($range_id) {
		$range = get_object_type($range_id);
		
		if ($range == "inst" || $range == "fak")
			return TRUE;
			
		return FALSE;
	}
	
	function printout ($args) {
		if ($this->config->getValue("Main", "wholesite")) {
			if ($body_class = $this->config->getValue("Main", "bodyclass"))
				$body_class = "class=\"$body_class\" ";
			else
				 $body_class = "";
				 
			if ($body_style = $this->config->getValue("Main", "bodystyle"))
				$body_style = "style=\"$body_style\"";
			else
				$body_style = "";
				
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"), $body_class . $body_style);
		}
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/news.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		if ($this->config->getValue("Main", "wholesite")) {
			if ($body_class = $this->config->getValue("Main", "bodyclass"))
				$body_class = "class=\"$body_class\" ";
			else
				 $body_class = "";
				 
			if ($body_style = $this->config->getValue("Main", "bodystyle"))
				$body_style = "style=\"$body_style\"";
			else
				$body_style = "";
				
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"), $body_class . $body_style);
		}
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/news_preview.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
}
?> 
