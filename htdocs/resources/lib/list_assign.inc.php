<?

function list_restore_assign(&$this){
	$db = new DB_Seminar();
	
	//recatch the values
	$end = $this->end;
	$start = $this->start;
	$range_id = $this->range_id;
	$user_id = $this->user_id;
	$resource_id = $this->resource_id;
	
	$year = date("Y", $this->start);
	$month = date("n", $this->start);
	
	//create the query
	$query = sprintf("SELECT assign_id, resource_id, begin, end, repeat_end, repeat_quantity, "
				."repeat_interval, repeat_month_of_year, repeat_day_of_month, repeat_month, "
				."repeat_week_of_month, repeat_day_of_week, repeat_week FROM resources_assign ");
	if ($range_id) $query.= sprintf("LEFT JOIN  resources_user_resources USING resource_id ");
	$query.= "WHERE ";
	if ($resource_id) $query.= sprintf("resources_assign.resource_id = '%s' AND ", $resource_id);
	if ($user_id) $query.= sprintf("resources_assign.assign_user_id = '%s'  AND ", $user_id);
	if ($range_id) $query.= sprintf("resources_user_resources.user_id = '%s'  AND ", $range_id);
	$query .= sprintf("(begin BETWEEN %s AND %s OR (begin <= %s AND repeat_end > %s ))"
				 . " ORDER BY begin ASC", $start, $end, $end, $start);

	//send the query
	$db->query($query);
	
	//handle the assigns und create all the repeated stuff
	while($db->next_record()){
		$year_offset=0;
		$week_offset=0;
		$month_offset=0;
		$day_offset=0;
		$quantity=0;
		$temp_ts=0;
		
		$assign_object = new AssignObject($db->f("assign_id"));
		if ($assign_object->getRepeatMode() == "na") {
			// date without repeatation, we have to create only one event (object = event)
			$event = new AssignEvent($assign_object->getId(), $assign_object->getBegin(), $assign_object->getEnd(),
									$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
									$assign_object->getUserFreeName());
			$this->events[] = $event;
		} elseif (($assign_object -> getRepeatEnd() >= $start) && ($assign_object -> getBegin() <= $end))
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
			
			//check if we want to show the event and if it is not outdated
			if ($temp_ts >= $start) {
				 if (($temp_ts <=$end) && ($temp_ts <= $assign_object -> getRepeatEnd()) && (($quantity < $assign_object->getRepeatQuantity()) || ($assign_object->getRepeatQuantity() == -1)))  {
				 	$event = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
											$assign_object->getResourceId(), $assign_object->getAssignUserId(), 
											$assign_object->getUserFreeName());
				
					$this->events[] = $event;
					$quantity++;
					}
				}
			} while(($temp_ts <=$end) && ($temp_ts <= $assign_object -> getRepeatEnd()) && ($quantity < $assign_object->getRepeatQuantity() || $assign_object->getRepeatQuantity() == -1));
	}
}

?>