<?
# Lifter002: TODO
/**
* RequestObject.class.php
* 
* class for a request object for supportdb
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		support
* @module		RequestObject.class.php
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
RequestObject, zentrale Klasse der Contract Objekte
/*****************************************************************************/
class RequestObject {
	var $id;					//resource_id des Objects;
	var $db;					//Datenbankanbindung;
	var $contract_id;				//the assigned contract;
	var $name;					//name of the request
	var $date;					//date the request was requestet :=)
	var $user_id;					//the id of the requesting user
	var $channel;					//the channel, the request came in
	var $topic_id;					//the assigned topic in the forum
	
	//Konstruktor
	function RequestObject($id='', $contract_id='', $name='', $date=0, $user_id='', $channel='', $topic_id='') {
		global $user;
		
		$this->db=new DB_Seminar;
		
		if(func_num_args() == 1) {
			$this->id = func_get_arg(0);
			$this->restore();
		} elseif(func_num_args() == 7) {
			$this->id = func_get_arg(0);
			$this->contract_id = func_get_arg(1);
			$this->name = func_get_arg(2);			
			$this->date = func_get_arg(3);
			$this->user_id = func_get_arg(4);
			$this->channel = func_get_arg(5);
			$this->topic_id = func_get_arg(6);
			if (!$this->id)
				$this->id=$this->createId();
		}

	}

	function createId() {
		return md5(uniqid("nopezope"));
	}

	function create() {
		$query = sprintf("SELECT request_id FROM support_request WHERE request_id ='%s'", $this->id);
		$this->db->query($query);
		if ($this->db->nf()) {
			return $this->store();
		} else
			return $this->store(TRUE);
	}
	
	function setContractId($id){
		$this->contract_id= $id;
	}

	function setName($id){
		$this->name= $id;
	}

	function setDate($date){
		$this->date= $date;
	}

	function setUserId($id){
		$this->user_id=$id;
	}

	function setChannel($channel){
		$this->channel= $channel;
	}

	function setTopicId($id){
		$this->topic_id= $id;
	}

	function getId() {
		return $this->id;
	}
	
	function getName() {
		return $this->name;
	}

	function getContractId() {
		return $this->contract_id;
	}

	function getDate() {
		return $this->date;
	}
	
	function getUserId() {
		return $this->user_id;
	}

	function getChannel() {
		return $this->channel;
	}

	function getTopicId() {
		return $this->topic_id;
	}

	function getEvents() {
		$query = sprintf ("SELECT COUNT(*) AS anzahl FROM support_event WHERE request_id = '%s'", $this->id);
		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("anzahl");
	}


	function isUnchanged() {
		if ($this->mkdate == $this->chdate)
			return TRUE;
		else
			return FALSE;
	}

	function isDeleteable() {
		$query = sprintf ("SELECT event_id FROM support_event WHERE request_id = '%s'", $this->id);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return FALSE;
		else 
			return TRUE;
	}
	
	function calculatePoints($begin, $end) {
		$points = ceil(($end - $begin) / 15);
		return $points;
	}
	
	function restore() {
		$query = sprintf("SELECT * FROM support_request WHERE request_id='%s' ",$this->id);

		$this->db->query($query);

		if($this->db->next_record()) {
			$this->name = $this->db->f("name");
			$this->contract_id = $this->db->f("contract_id");
			$this->date = $this->db->f("date");
			$this->user_id = $this->db->f("user_id");
			$this->channel = $this->db->f("channel");
			$this->user_id = $this->db->f("user_id");
			$this->topic_id = $this->db->f("topic_id");			
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
			
			$query = sprintf("INSERT INTO support_request SET request_id='%s', contract_id='%s', name='%s', " 
				."date='%s', channel='%s', user_id ='%s', topic_id ='%s', mkdate='%s', chdate='%s' "
						 , $this->id, $this->contract_id, $this->name, $this->date
						 , $this->channel, $this->user_id, $this->topic_id, $mkdate, $chdate);
		} else
			$query = sprintf("UPDATE support_request SET contract_id='%s', name='%s', " 
				."date='%s', user_id='%s', channel='%s', user_id ='%s', topic_id ='%s' WHERE request_id = '%s'"
						 , $this->contract_id, $this->name, $this->date, $this->user_id
						 , $this->channel, $this->user_id, $this->topic_id, $this->id);
			$this->db->query($query);
			if ($this->db->affected_rows()) {
				$query = sprintf("UPDATE support_request SET chdate='%s' WHERE request_id='%s' ", $chdate, $this->id);
				$this->db->query($query);
				return TRUE;
			} else
				return FALSE;
		
		return FALSE;
	}

	function delete() {
		$query = sprintf("DELETE FROM support_request WHERE request_id = '%s' ", $this->id);
		$this->db->query($query);

		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;
	}
}