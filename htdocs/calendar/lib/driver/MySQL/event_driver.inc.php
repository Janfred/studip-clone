<?

function event_get_description($id){
	$db = new DB_Seminar;
	$query = sprintf("SELECT termin_id, description FROM termine WHERE termin_id='%s'", $id);
	$db->query($query);
	if($db->next_record())
		return $db->f("description");
	return FALSE;
}

function event_save($this){
	global $TERMIN_TYP;
	// Natuerlich nur Speichern, wenn sich was geaendert hat
	// und es sich um einen persoenlichen Termin handelt
	if($this->chng_flag && ($this->type == -1 || $this->type == -2)){
		$db = new DB_Seminar;
		$chdate = time();
		if($this->mkd == -1)
			$mkdate = $chdate;
		else
			$mkdate = $this->mkd;
			
		if(is_int($this->desc))
			$query = sprintf("REPLACE termine (termin_id,range_id,autor_id,content,"
			       . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES"
			       . " ('%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')"
						 , $this->id, $this->user_id, $this->user_id, $this->txt, $this->start, $this->end
						 , $mkdate, $chdate, $this->type, $this->exp, $this->rep, $this->cat, $this->prio
						 , $this->loc);
		else
			$query = sprintf("REPLACE termine (termin_id,range_id,autor_id,content,description,"
		         . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES"
			       . " ('%s','%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')"
						 , $this->id, $this->user_id, $this->user_id, $this->txt, $this->desc, $this->start
						 , $this->end, $mkdate, $chdate, $this->type, $this->exp, $this->rep, $this->cat
						 , $this->prio, $this->loc);
		if($db->query($query))
			return TRUE;
		return FALSE;
	}
	return FALSE;
}

function event_delete($event_id, $user_id){
	$db = new DB_Seminar;
	$query = sprintf("DELETE FROM termine WHERE termin_id='%s' AND autor_id='%s'", $event_id, $user_id);
	if($db->query($query))
		return TRUE;
	return FALSE;
}

function event_restore($id, &$this){
	global $TERMIN_TYP, $PERS_TERMIN_KAT;
	$db = new DB_Seminar;
	if(func_num_args() == 2)
		$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user ON (range_id=Seminar_id) "
										. "WHERE (range_id='%s' OR user_id='%s') AND termin_id='%s'"
										, $this->user_id, $this->user_id, $id);
	else if(func_num_args() == 1)
		$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user ON (range_id=Seminar_id) "
										. "WHERE (range_id='%s' OR user_id='%s') AND termin_id='%s'"
										, $this->user_id, $this->user_id, $this->id);
	$db->query($query);
	
	if($db->next_record()){
		$this->id = $id;
		$this->txt = $db->f("content");
		$this->start = $db->f("date");
		$this->type = $db->f("date_typ");
		if(!$this->setEnd($db->f("end_time")))
			return FALSE;
			
		// bei Seminar-Terminen ist kein expire gesetzt
		if($TERMIN_TYP[$this->type]["ebene"] == "" && !$this->setExpire($db->f("expire")))
			return FALSE;
	
		$this->rep = $db->f("repeat");
		
		if($this->type == -1 || $this->type == -2)
			$this->cat = $db->f("color");
		else if($TERMIN_TYP[$this->type]["ebene"] == "sem"){
			$color = array("#000000","#FF0000","#FF9933","#FFCC66","#99FF99","#66CC66","#6699CC","#666699");
			$this->cat = $this->type;
			if($PERS_TERMIN_KAT[$this->type][color] == "")
				$this->col = $color[$db->f("gruppe")];
			else
				$this->col = $PERS_TERMIN_KAT[$this->type][color];
		}
		
		$this->desc = $db->f("description");
		$this->prio = $db->f("priority");
		$this->loc = $db->f("raum");
		$this->setSeminarId($db->f("Seminar_id"));
		$this->mkd = $db->f("mkdate");
		$this->chng_flag = FALSE;
		
		return TRUE;
	}
	return FALSE;
}
