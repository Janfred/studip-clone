<?
/**
* list_assign.inc.php
* 
* library, contains functions to create the AssinEvents
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @package		resources
* @modulegroup	resources_modules
* @module		list_assign.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// list_assign.inc.php
// Library der Funktionen zur Erstellung von AssignEvents
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignObject.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignEvent.class.php");


function list_restore_assign(&$this, $resource_id, $begin, $end, $user_id='', $range_id='', $filter = FALSE){
	$db = new DB_Seminar();

	$year = date("Y", $begin);
	$month = date("n", $begin);
	
	//create the query
	$query = sprintf("SELECT assign_id, resource_id, begin, end, repeat_end, repeat_quantity, "
				."repeat_interval, repeat_month_of_year, repeat_day_of_month, "
				."repeat_week_of_month, repeat_day_of_week FROM resources_assign ");
	if ($range_id) $query.= sprintf("LEFT JOIN  resources_user_resources USING (resource_id) ");
	$query.= "WHERE ";
	if ($resource_id) $query.= sprintf("resources_assign.resource_id = '%s' AND ", $resource_id);
	if ($user_id) $query.= sprintf("resources_assign.assign_user_id = '%s'  AND ", $user_id);
	if ($range_id) $query.= sprintf("resources_user_resources.user_id = '%s'  AND ", $range_id);
	$query .= sprintf("(begin BETWEEN %s AND %s OR (begin <= %s AND (repeat_end > %s OR end > %s)))"
				 . " ORDER BY begin ASC", $begin, $end, $end, $begin, $begin);

	//send the query
	$db->query($query);
	
	//handle the assigns und create all the repeated stuff
	while($db->next_record()) {
		$assign_object =& AssignObject::Factory($db->f("assign_id"));
		create_assigns($assign_object, $this, $begin, $end, $filter);
	}
}

function create_assigns($assign_object, &$this, $begin='', $end='', $filter = FALSE) {
	$year_offset=0;
	$week_offset=0;
	$month_offset=0;
	$day_offset=0;
	$quantity=0;
	$temp_ts=0;

	//if no begin/end-date submitted, we create all the assigns from the given assign-object
	if (!$begin)
		$begin = $assign_object->getBegin();
	if (!$end)
		$end = $assign_object->getRepeatEnd();
	$ao_repeat_mode = $assign_object->getRepeatMode();
	$ao_begin = $assign_object->getBegin();
	$ao_end = $assign_object->getEnd();
	$ao_r_end = $assign_object->getRepeatEnd();
	$ao_r_q = $assign_object->getRepeatQuantity();
	$ao_r_i = $assign_object->getRepeatInterval();
	if ($ao_repeat_mode == "na") {
		// date without repeatation, we have to create only one event (object = event)
		$assEvt = new AssignEvent($assign_object->getId(), $ao_begin, $ao_end,
								$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
								$assign_object->getUserFreeName());
		$assEvt->setRepeatMode($ao_repeat_mode);
		if (!isFiltered($filter, $assEvt->getRepeatMode(TRUE)))
			$this->events[] = $assEvt;
	} elseif ($ao_repeat_mode == "sd") {
		// several days mode, we create multiple assigns
		
		//first day
		$temp_ts_end=mktime(23, 59, 59,
					date("n",$ao_begin), 
					date("j",$ao_begin),
					date("Y",$ao_begin));

		if (($temp_ts_end <= $end) && ($ao_begin >= $begin)) {
			$assEvt = new AssignEvent($assign_object->getId(), $ao_begin, $temp_ts_end,
								$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
								$assign_object->getUserFreeName());
			$assEvt->setRepeatMode($ao_repeat_mode);
			if (!isFiltered($filter, $assEvt->getRepeatMode()))
				$this->events[] = $assEvt;
		}
		//in between days
		for ($d=date("j",$ao_begin)+1; $d<=date("j",$ao_r_end)-1; $d++) {
			$temp_ts=mktime(0, 0, 0,
					date("n",$ao_begin), 
					$d,
					date("Y",$ao_begin));

			$temp_ts_end=mktime(23, 59, 59,
					date("n",$ao_begin), 
					$d,
					date("Y",$ao_begin));

			if (($temp_ts_end <= $end) && ($temp_ts >= $begin)) {
				$assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
								$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
								$assign_object->getUserFreeName());
				$assEvt->setRepeatMode($ao_repeat_mode);
				if (!isFiltered($filter, $assEvt->getRepeatMode()))
					$this->events[] = $assEvt;								
			}
		}
				
		//last_day
		$temp_ts=mktime(0, 0, 0,
					date("n",$ao_r_end), 
					date("j",$ao_r_end),
					date("Y",$ao_r_end));

		$temp_ts_end=mktime(date("G",$ao_end), 
					date("i",$ao_end), 
					0, 
					date("n",$ao_r_end), 
					date("j",$ao_r_end),
					date("Y",$ao_r_end));

		if (($temp_ts_end <= $end) && ($temp_ts >= $begin)) {
			$assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
								$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
								$assign_object->getUserFreeName());
			$assEvt->setRepeatMode($ao_repeat_mode);
			if (!isFiltered($filter, $assEvt->getRepeatMode()))
				$this->events[] = $assEvt;								
		}
		
	} elseif ((($ao_r_end >= $begin) && ($ao_begin <= $end)) ||
			(($begin == -1) &&($end == -1) && ($ao_r_q  >0)))
		do { 

		//create a temp_ts to try every possible repeatation
		$temp_ts=mktime(date("G",$ao_begin), 
					date("i",$ao_begin), 
					0, 
					date("n",$ao_begin)+($month_offset * $ao_r_i), 
					date("j",$ao_begin)+($week_offset * $ao_r_i * 7) + ($day_offset * $ao_r_i), 
					date("Y",$ao_begin)+($year_offset * $ao_r_i));
		$temp_ts_end=mktime(date("G",$ao_end), 
					date("i",$ao_end), 
					0, 
					date("n",$ao_begin) + ($month_offset * $ao_r_i), 
					date("j",$ao_end)+($week_offset * $ao_r_i * 7)  + ($day_offset * $ao_r_i),  
					date("Y",$ao_end)+($year_offset * $ao_r_i));
		//change the offsets
		if ($ao_repeat_mode == "y") $year_offset++;
		if ($ao_repeat_mode == "w") $week_offset++;
		if ($ao_repeat_mode == "m") $month_offset++;
		if ($ao_repeat_mode == "d") $day_offset++;

		//inc the count
		$quantity++;
		
		//check if we want to show the event and if it is not outdated
		if (($begin == -1) && ($end == -1) && ($ao_r_q  >0))
			 	$this->events[] = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
										$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
										$assign_object->getUserFreeName());
		elseif ($temp_ts >= $begin) {
			 if (($temp_ts <=$end) && ($temp_ts <= $ao_r_end) && (($quantity <= $ao_r_q ) || ($ao_r_q  == -1)))  {
			 	$assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
										$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
										$assign_object->getUserFreeName());
				$assEvt->setRepeatMode($ao_repeat_mode);
				if (!isFiltered($filter, $assEvt->getRepeatMode()))
					$this->events[] = $assEvt;										
			}
		}
		
		//security break
		if ($quantity > 150)
			break;
			
		} while((($temp_ts <=$end) && ($temp_ts <= $ao_r_end) && ($quantity < $ao_r_q  || $ao_r_q  == -1)) || 
				(($begin == -1) &&($end == -1) &&($ao_r_q ) >0) && ($quantity < $ao_r_q ));
}

function isFiltered($filter, $mode) {
	$filters = array(	// filter rules (a filter consists of one or more repeat_modes)
		"all"=>array("na", "sd", "meta", "d", "m", "y", "w"),
		"single"=>array("na", "sd"),
		"repeated"=>array("meta", "d", "m", "y", "w"));
	if ($filter) {
		if (in_array($mode, $filters[$filter]))
			return FALSE;
		else
			return TRUE;
	} else
		return FALSE;
}

?>
