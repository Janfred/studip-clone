<?
/**
* EventObject.class.php
* 
* class for a event object for supportdb
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		support
* @module		EventObject.class.php
* @package		support
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// RequestObject.class.php
// Klasse fuer ein RequestObject
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

/*****************************************************************************
EventObject, zentrale Klasse der Contract Objekte
/*****************************************************************************/
class EventObject {
	var $id;					//event_id des Objects;
	var $db;					//Datenbankanbindung;
	var $request_id;				//the assigned request;
	var $begin;					//the begin ts of the event
	var $end;					//the end ts of the event
	var $user_id;					//the id of the supporting user
	var $used_points;				//the used poinjts for this event
	
	//Konstruktor
	function EventObject($id='', $request_id='', $begin='', $end='', $user_id='', $used_points='') {
		global $user;
		
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;
		
		if(func_num_args() == 1) {
			$this->id = func_get_arg(0);
			$this->restore();
		} elseif(func_num_args() == 6) {
			$this->id = func_get_arg(0);
			$this->request_id = func_get_arg(1);
			$this->begin = func_get_arg(2);			
			$this->end = func_get_arg(3);
			$this->user_id = func_get_arg(4);
			$this->used_points = func_get_arg(5);
			if (!$this->id)
				$this->id=$this->createId();
		}

	}

	function createId() {
		return md5(uniqid("binnachladen"));
	}

	function create() {
		$query = sprintf("SELECT event_id FROM support_event WHERE event_id ='%s'", $this->id);
		$this->db->query($query);
		if ($this->db->nf()) {
			return $this->store();
		} else
			return $this->store(TRUE);
	}
	
	function setRequestId($id){
		$this->request_id= $id;
	}

	function setBegin($ts){
		$this->begin= $ts;
	}

	function setEnd($ts){
		$this->end= $ts;
	}

	function setUserId($id){
		$this->user_id=$id;
	}

	function setUsed_points($points){
		$this->used_points= $points;
	}

	function getId() {
		return $this->id;
	}
	
	function getBegin() {
		return $this->begin;
	}

	function getRequestId() {
		return $this->request_id;
	}

	function getEnd() {
		return $this->end;
	}
	
	function getUserId() {
		return $this->user_id;
	}

	function getUsed_points() {
		return $this->used_points;
	}

	function isUnchanged() {
		if ($this->mkdate == $this->chdate)
			return TRUE;
		else
			return FALSE;
	}

	//private
	function calculatePoints() {
		$points = ceil(($this->end - $this->begin) / 60 / 15);
		return $points;
	}
	
	function restore() {
		$query = sprintf("SELECT * FROM support_event WHERE event_id='%s' ",$this->id);

		$this->db->query($query);

		if($this->db->next_record()) {
			$this->request_id = $this->db->f("request_id");
			$this->begin = $this->db->f("begin");
			$this->end = $this->db->f("end");
			$this->user_id = $this->db->f("user_id");
			$this->used_points = $this->db->f("used_points");
			$this->mkdate =$this->db->f("mkdate");
			$this->chdate =$this->db->f("chdate");
			return TRUE;
		}
		return FALSE;
	}

	function store($create=''){
		// only store, if the object isn't new, else create
		$chdate = time();
		$mkdate = time();
		if($create) {
			
			$query = sprintf("INSERT INTO support_event SET event_id = '%s', request_id='%s', begin='%s', " 
				."end='%s', used_points='%s', user_id ='%s', mkdate='%s', chdate='%s' "
						 , $this->id, $this->request_id, $this->begin, $this->end
						 , $this->calculatePoints(), $this->user_id, $mkdate, $chdate);
		} else
			$query = sprintf("UPDATE support_event SET request_id='%s', begin='%s', " 
				."end='%s', used_points='%s', user_id ='%s' WHERE event_id = '%s'"
						 , $this->request_id, $this->begin, $this->end
						 , $this->calculatePoints(), $this->user_id, $this->id);
			$this->db->query($query);
			if ($this->db->affected_rows()) {
				$query = sprintf("UPDATE support_event SET chdate='%s' WHERE event_id='%s' ", $chdate, $this->id);
				$this->db->query($query);
				$this->chdate = $chdate;
				return TRUE;
			} else
				return FALSE;
		
		return FALSE;
	}

	function delete() {
		$query = sprintf("DELETE FROM support_event WHERE event_id = '%s' ", $this->id);
		$this->db->query($query);

		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;
	}
}