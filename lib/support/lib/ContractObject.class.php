<?
# Lifter002: TODO
/**
* ContractObject.class.php
* 
* class for a contract object for supportdb
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		support
* @module		ContractObject.class.php
* @package		support
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ContractObject.class.php
// Klasse fuer ein ContractObject
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
ContractObject, zentrale Klasse der Contract Objekte
/*****************************************************************************/
class ContractObject {
	var $id;					//resource_id des Objects;
	var $db;					//Datenbankanbindung;
	var $institut_id;				//institut;
	var $range_id;					//range too show (ich which Stud.IP object should the contract be?)
	var $given_points;				//the points that were give at contraction
	var $contract_begin;				//contract start date
	var $contract_end;				//contract end date

	
	//Konstruktor
	function ContractObject($id='', $institut_id='', $range_id='', $given_points=0, $contract_begin='', $contract_end='') {
		global $user;
		
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;
		
		if(func_num_args() == 1) {
			$this->id = func_get_arg(0);
			$this->restore();
		} elseif(func_num_args() == 6) {
			$this->id = func_get_arg(0);
			$this->institut_id = func_get_arg(1);
			$this->range_id = func_get_arg(2);			
			$this->given_points = func_get_arg(3);
			$this->contract_begin = func_get_arg(4);
			$this->contract_end = func_get_arg(5);
			if (!$this->id)
				$this->id=$this->createId();
		}
	}

	function createId() {
		return md5(uniqid("powersupport"));
	}

	function create() {
		$query = sprintf("SELECT contract_id FROM support_contract WHERE contract_id ='%s'", $this->id);
		$this->db->query($query);
		if ($this->db->nf()) {
			return $this->store();
		} else
			return $this->store(TRUE);
	}
	
	function setInstitutId($id){
		$this->institut_id= $id;
	}

	function setRangeId($id){
		$this->range_id= $id;
	}

	function setGivenPoints($points){
		$this->given_points= $points;
	}

	function setContractBegin($begin){
		$this->contract_begin=$begin;
	}

	function setContractEnd($end){
		$this->contract_end= $end;
	}

	function getId() {
		return $this->id;
	}
	
	function getRangeId() {
		return $this->range_id;
	}

	function getInstitutId() {
		return $this->institut_id;
	}

	function getGivenPoints() {
		return $this->given_points;
	}
	
	function getRemainingPoints() {
		$remaining_points = $this->given_points - $this->getUsedPoints();
		return $remaining_points;
	}

	function getUsedPoints() {
		$query = sprintf ("SELECT * FROM support_event LEFT JOIN support_request USING (request_id) WHERE contract_id = '%s'", $this->id);
		$this->db->query($query); 
		
		$points = 0;
		
		while ($this->db->next_record()) {
			$points = $points + $this->db->f("used_points");
		}
		
		$remaining_points = $this->given_points - $points;
		return $points;
		
	}
	
	function getRequests() {
		$query = sprintf ("SELECT COUNT(*) AS anzahl FROM support_request WHERE contract_id = '%s'", $this->id);
		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("anzahl");
	}

	function getEvents() {
		$query = sprintf ("SELECT COUNT(*) AS anzahl FROM support_event LEFT JOIN support_request USING (request_id) WHERE contract_id = '%s'", $this->id);
		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("anzahl");
	}

	function getContractBegin() {
		return $this->contract_begin;
	}

	function getContractEnd() {
		return $this->contract_end;
	}

	function isUnchanged() {
		if ($this->mkdate == $this->chdate)
			return TRUE;
		else
			return FALSE;
	}

	function isDeleteable() {
		$query = sprintf ("SELECT request_id FROM support_request WHERE contract_id = '%s'", $this->id);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return FALSE;
		else 
			return TRUE;
	}
	
	function isOldestActive() {
		$query = sprintf ("SELECT contract_id FROM support_contract WHERE contract_end > '%s' AND range_id = '%s' ORDER by contract_begin ASC", time(), $this->range_id);
		$this->db->query($query);
		$this->db->next_record();

		if ($this->id == $this->db->f("contract_id"))
			return TRUE;
		else
			return FALSE;
	}
	
	function calculatePoints($begin, $end) {
		$points = ceil(($end - $begin) / 15);
		return $points;
	}
	
	function restore() {
		$query = sprintf("SELECT * FROM support_contract WHERE contract_id='%s' ",$this->id);

		$this->db->query($query);

		if($this->db->next_record()) {
			$this->range_id = $this->db->f("range_id");
			$this->institut_id = $this->db->f("institut_id");
			$this->given_points = $this->db->f("given_points");
			$this->contract_begin = $this->db->f("contract_begin");
			$this->contract_end = $this->db->f("contract_end");
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
			$query = sprintf("INSERT INTO support_contract SET contract_id='%s', institut_id='%s', range_id='%s', " 
				."given_points='%s', contract_begin='%s', contract_end='%s', mkdate='%s', chdate='%s' "
						 , $this->id, $this->institut_id, $this->range_id, $this->given_points, $this->contract_begin
						 , $this->contract_end, $mkdate, $chdate);
		} else
			$query = sprintf("UPDATE support_contract SET institut_id='%s', range_id='%s', " 
				."given_points='%s', contract_begin='%s', contract_end='%s' WHERE contract_id = '%s'"
						 , $this->institut_id, $this->range_id, $this->given_points, $this->contract_begin
						 , $this->contract_end, $this->id);
			$this->db->query($query);
			if ($this->db->affected_rows()) {
				$query = sprintf("UPDATE support_contract SET chdate='%s' WHERE contract_id='%s' ", $chdate, $this->id);
				$this->db->query($query);
				return TRUE;
			} else
				return FALSE;
		
		return FALSE;
	}

	function delete() {
		$query = sprintf("DELETE FROM support_contract WHERE contract_id = '%s' ", $this->id);
		$this->db->query($query);
		
		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;
	}
}