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
	if ($range_id) $query.= sprintf("LEFT JOIN  resources_user_resources USING resource_id ");
	$query.= "WHERE ";
	if ($resource_id) $query.= sprintf("resources_assign.resource_id = '%s' AND ", $resource_id);
	if ($user_id) $query.= sprintf("resources_assign.assign_user_id = '%s'  AND ", $user_id);
	if ($range_id) $query.= sprintf("resources_user_resources.user_id = '%s'  AND ", $range_id);
	$query .= sprintf("(begin BETWEEN %s AND %s OR (begin <= %s AND repeat_end > %s ))"
				 . " ORDER BY begin ASC", $begin, $end, $end, $begin);

	//send the query
	$db->query($query);
	
	//handle the assigns und create all the repeated stuff
	while($db->next_record()) {
		$assign_object = new AssignObject($db->f("assign_id"));
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
		
	if ($assign_object->getRepeatMode() == "na") {
		// date without repeatation, we have to create only one event (object = event)
		$assEvt = new AssignEvent($assign_object->getId(), $assign_object->getBegin(), $assign_object->getEnd(),
								$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
								$assign_object->getUserFreeName());
		$assEvt->setRepeatMode($assign_object->getRepeatMode());
		if (!isFiltered($filter, $assEvt->getRepeatMode(TRUE)))
			$this->events[] = $assEvt;
	} elseif ($assign_object->getRepeatMode() == "sd") {
		// several days mode, we create multiple assigns
		
		//first day
		$temp_ts_end=mktime(23, 59, 59,
					date("n",$assign_object -> getBegin()), 
					date("j",$assign_object -> getBegin()),
					date("Y",$assign_object -> getBegin()));

		if (($temp_ts_end <= $end) && ($assign_object->getBegin() >= $begin)) {
			$assEvt = new AssignEvent($assign_object->getId(), $assign_object->getBegin(), $temp_ts_end,
								$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
								$assign_object->getUserFreeName());
			$assEvt->setRepeatMode($assign_object->getRepeatMode());
			if (!isFiltered($filter, $assEvt->getRepeatMode()))
				$this->events[] = $assEvt;
		}
		//in between days
		for ($d=date("j",$assign_object -> getBegin())+1; $d<=date("j",$assign_object -> getRepeatEnd())-1; $d++) {
			$temp_ts=mktime(0, 0, 0,
					date("n",$assign_object -> getBegin()), 
					$d,
					date("Y",$assign_object -> getBegin()));

			$temp_ts_end=mktime(23, 59, 59,
					date("n",$assign_object -> getBegin()), 
					$d,
					date("Y",$assign_object -> getBegin()));

			if (($temp_ts_end <= $end) && ($temp_ts >= $begin)) {
				$assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
								$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
								$assign_object->getUserFreeName());
				$assEvt->setRepeatMode($assign_object->getRepeatMode());
				if (!isFiltered($filter, $assEvt->getRepeatMode()))
					$this->events[] = $assEvt;								
			}
		}
				
		//last_day
		$temp_ts=mktime(0, 0, 0,
					date("n",$assign_object -> getRepeatEnd()), 
					date("j",$assign_object -> getRepeatEnd()),
					date("Y",$assign_object -> getRepeatEnd()));

		$temp_ts_end=mktime(date("G",$assign_object -> getEnd()), 
					date("i",$assign_object -> getEnd()), 
					0, 
					date("n",$assign_object -> getRepeatEnd()), 
					date("j",$assign_object -> getRepeatEnd()),
					date("Y",$assign_object -> getRepeatEnd()));

		if (($temp_ts_end <= $end) && ($temp_ts >= $begin)) {
			$assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
								$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
								$assign_object->getUserFreeName());
			$assEvt->setRepeatMode($assign_object->getRepeatMode());
			if (!isFiltered($filter, $assEvt->getRepeatMode()))
				$this->events[] = $assEvt;								
		}
		
	} elseif ((($assign_object -> getRepeatEnd() >= $begin) && ($assign_object -> getBegin() <= $end)) ||
			(($begin == -1) &&($end == -1) && ($assign_object->getRepeatQuantity() >0)))
		do { 

		//create a temp_ts to try every possible repeatation
		$temp_ts=mktime(date("G",$assign_object -> getBegin()), 
					date("i",$assign_object -> getBegin()), 
					0, 
					date("n",$assign_object -> getBegin())+($month_offset * $assign_object ->getRepeatInterval()), 
					date("j",$assign_object -> getBegin())+($week_offset * $assign_object ->getRepeatInterval() * 7) + ($day_offset * $assign_object ->getRepeatInterval()), 
					date("Y",$assign_object -> getBegin())+($year_offset * $assign_object ->getRepeatInterval()));
		$temp_ts_end=mktime(date("G",$assign_object -> getEnd()), 
					date("i",$assign_object -> getEnd()), 
					0, 
					date("n",$assign_object -> getBegin()) + ($month_offset * $assign_object ->getRepeatInterval()), 
					date("j",$assign_object -> getEnd())+($week_offset * $assign_object ->getRepeatInterval() * 7)  + ($day_offset * $assign_object ->getRepeatInterval()),  
					date("Y",$assign_object -> getEnd())+($year_offset * $assign_object ->getRepeatInterval()));
		//change the offsets
		if ($assign_object->getRepeatMode() == "y") $year_offset++;
		if ($assign_object->getRepeatMode() == "w") $week_offset++;
		if ($assign_object->getRepeatMode() == "m") $month_offset++;
		if ($assign_object->getRepeatMode() == "d") $day_offset++;

		//inc the count
		$quantity++;
		
		//check if we want to show the event and if it is not outdated
		if (($begin == -1) && ($end == -1) && ($assign_object->getRepeatQuantity() >0))
			 	$this->events[] = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
										$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
										$assign_object->getUserFreeName());
		elseif ($temp_ts >= $begin) {
			 if (($temp_ts <=$end) && ($temp_ts <= $assign_object -> getRepeatEnd()) && (($quantity <= $assign_object->getRepeatQuantity()) || ($assign_object->getRepeatQuantity() == -1)))  {
			 	$assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
										$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
										$assign_object->getUserFreeName());
				$assEvt->setRepeatMode($assign_object->getRepeatMode());
				if (!isFiltered($filter, $assEvt->getRepeatMode()))
					$this->events[] = $assEvt;										
			}
		}
		
		//security break
		if ($quantity > 150)
			break;
			
		} while((($temp_ts <=$end) && ($temp_ts <= $assign_object -> getRepeatEnd()) && ($quantity < $assign_object->getRepeatQuantity() || $assign_object->getRepeatQuantity() == -1)) || 
				(($begin == -1) &&($end == -1) &&($assign_object->getRepeatQuantity()) >0) && ($quantity < $assign_object->getRepeatQuantity()));
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