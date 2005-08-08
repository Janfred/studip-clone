<?php
/**
* SemGroupScheduleDayOfWeek.class.php
* 
* creates a grafical schedule view for different purposes, ie. a personal timetable
* or a timetable for a ressource like a room, a device or a building
* 
*
* @author		André Noack <noack@data-quest.de>
* @version		$Id$
* @access		public
* @package		resources
* @modulegroup	resources_modules
* @module		ScheduleWeek.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ScheduleWeek.class.php
// Modul zum Erstellen grafischer Belegungspl&auml;ne
// Copyright (C) 2005 André Noack <noack@data-quest.de>
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
require_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . $GLOBALS['RELATIVE_PATH_RESOURCES'] . "/views/ScheduleView.class.php";

class SemGroupScheduleDayOfWeek extends ScheduleView {
	var $categories = array(		//the categories configuration (color's and bg-image)
		"0"=>array("bg-picture"=>"pictures/calendar/category5.jpg", "border-color"=>"#505064"),
		"1"=>array("bg-picture"=>"pictures/calendar/category3.jpg", "border-color"=>"#5C2D64"),
		"2"=>array("bg-picture"=>"pictures/calendar/category9.jpg", "border-color"=>"#957C29"),
		"3"=>array("bg-picture"=>"pictures/calendar/category11.jpg", "border-color"=>"#66954F"),
		"4"=>array("bg-picture"=>"pictures/calendar/category13.jpg", "border-color"=>"#951408"),
		);
	
	//Kontruktor
	function SemGroupScheduleDayOfWeek ($start_hour = '', $end_hour = '', $rooms_to_show = array(), $start_date = '', $dow = 1) {
		
		foreach ($rooms_to_show as $id => $room_id){
			$show_columns[$id+1] = $room_id;
		}
		parent::ScheduleView($start_hour, $end_hour, $show_columns, $start_date);

		$this->dow = $dow;
		//the base_date have to be 0:00
		$first_monday = date("j",$this->start_date)  - (date("w", $this->start_date) - 1);
		if (date("w", $this->start_date) > 1){
			$first_monday += 7;
		}
		$this->base_date = mktime(0, 0, 0, date("n", $this->start_date), $first_monday + $this->dow - 1,  date("Y", $this->start_date));		
	}


	function addEvent($room_to_show_id, $name, $start_time, $end_time, $link='', $add_info='', $category=0) {
		parent::addEvent($room_to_show_id + 1, $name, $start_time, $end_time, $link, $add_info, $category);
	}
	
	function getColumnName($id){
		return htmlReady(getResourceObjectName($this->show_columns[$id]));
	}

	function getAddLink($l, $i){
		$add_link_timestamp = $this->base_date + ($i * 60 * 60);
		$add_link_timestamp .= "&show_object=" . $this->show_columns[$l];
		return sprintf ("class=\"steel1\" align=\"right\" valign=\"bottom\"><a href=\"%s%s\"><img src=\"pictures/calplus.gif\" %s border=\"0\"/></a></td>", 
						$this->add_link, $add_link_timestamp, tooltip(sprintf(_("Eine neue Belegung von %s bis %s Uhr anlegen"), date ("H:i", $add_link_timestamp), date ("H:i", $add_link_timestamp + (2 * 60 * 60)))));
	}
}
?>
