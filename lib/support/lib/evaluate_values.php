<?
# Lifter002: TODO
/**
* evaluate_values.php
* 
* handles all values, which are sent from the supportdb
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>
* @version		$Id$
* @access		public
* @package		support
* @modulegroup		support
* @module		evaluate_values.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// evaluate_values.php
// Auswerten der Werte aus der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>
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

require_once ("$RELATIVE_PATH_SUPPORT/lib/ContractObject.class.php");
require_once ("$RELATIVE_PATH_SUPPORT/lib/RequestObject.class.php");
require_once ("$RELATIVE_PATH_SUPPORT/lib/EventObject.class.php");
require_once ("$RELATIVE_PATH_SUPPORT/supportConfig.inc.php");

/*****************************************************************************
empfangene Werte auswerten und Befehle ausfuehren
/*****************************************************************************/

//is the user a supporter?
if ($perm->have_studip_perm ("dozent", $SessSemName[1]))
	$supporter = TRUE;


//got a fresh session?
if ((sizeof ($_REQUEST) == 1) && (!$view)) {
	$supportdb_data='';
	$supportdb_data["view"] = "overview";
	$supportdb_data["user_action_con"] = FALSE;
}

if ($view)
	$supportdb_data["view"] = $view;


//open a contract
if ($con_open) {
	$supportdb_data["con_opens"][$con_open] = TRUE;
	$supportdb_data["actual_con"] = $con_open;
	$supportdb_data["user_action_con"] = TRUE;
}

if ($edit_con) {
	$supportdb_data["actual_con"] = $edit_con;
	$edit_con_object = $edit_con;
}
	
//Close a contract
if ($con_close) {
	unset($supportdb_data["con_opens"][$con_close]);
	if ($con_close == $supportdb_data["actual_con"])
		unset($resources_data["actual_con"]);
	$supportdb_data["user_action_con"] = TRUE;		
}

//Open a request
if ($req_open) {
	$supportdb_data["req_opens"][$req_open] = TRUE;
	$supportdb_data["actual_req"] = $req_open;
}

if ($edit_req) {
	$supportdb_data["actual_req"] = $edit_req;
	$edit_req_object = $edit_req;
}

//Close a request
if ($req_close) {
	unset($supportdb_data["req_opens"][$req_close]);
	if ($req_close == $supportdb_data["actual_req"])
		unset($resources_data["actual_req"]);
}

//Show the request of a contract (change the view from contract to request)
if ($show_con_req)
	$supportdb_data["actual_con"] = $show_con_req;
	
//Close a contract
if ($con_close) {
	unset($supportdb_data["con_opens"][$con_close]);
	if ($con_close == $supportdb_data["actual_con"])
		unset($resources_data["actual_con"]);
}

//create a new con
if (($supporter) && ($create_con)) {
	$con_end = mktime(23,59,59, date("m", time()), date("d", time()), (date("Y", time())+1));
	
	$createCon = new ContractObject('', FALSE, $SessSemName[1], 50, time(), $con_end);
	$createCon->create();

	$supportdb_data["con_opens"][$createCon->getId()] = TRUE;
	$supportdb_data["actual_con"] = $createCon->getId();
	$edit_con_object = $createCon->getId();
}

//cancel a just created con
if (($supporter) && ($cancel_edit_con)) {
	$killCon = new ContractObject($cancel_edit_con);
	if (($killCon->isUnchanged()) && ($killCon->isDeleteable())) {
		$killCon->delete();

		unset($supportdb_data["con_opens"][$killCon->getId()]);
		if ($kill_con == $supportdb_data["actual_con"])
			unset($supportdb_data["actual_con"]);
	}
}


//kill a new con
if (($supporter) && ($kill_con)) {
	$killCon = new ContractObject($kill_con);
	if ($killCon->isDeleteable ())
		if ($killCon->delete())
			$msg->addMsg(4);

	unset($supportdb_data["con_opens"][$killCon->getId()]);
	if ($kill_con == $supportdb_data["actual_con"])
		unset($supportdb_data["actual_con"]);
}

//changes for a contract are coming in...
if (($supporter) && ($sent_con_id)) {
	require_once ('lib/calendar_functions.inc.php'); //needed for extended checkdate
	$changedCon = new ContractObject($sent_con_id);
	$changedCon->restore();
	
	if ($con_given_points >= $changedCon->getRemainingPoints())
		$changedCon->setGivenPoints($con_given_points);
	else
		$msg->addMsg(1);
	
	$changedCon->setInstitutId($con_institut_id);
	
	$illegal_begin = FALSE;
	$illegal_end = FALSE;
	
	//checkdates
	if (!check_date($con_begin_month, $con_begin_day, $con_begin_year, 0, 0)) {
		$msg->addMsg(2);
		$illegal_begin=TRUE;
	} else
		$con_begin = mktime(0,0,0,$con_begin_month, $con_begin_day, $con_begin_year);

	if (!check_date($con_end_month, $con_end_day, $con_end_year, 23, 59)) {
		$msg -> addMsg(3);
		$illegal_end=TRUE;						
	} else
		$con_end = mktime(23,59,59,$con_end_month, $con_end_day, $con_end_year);
		
	if ((!$illegal_begin) && (!$illegal_end) && ($con_begin < $con_end)) {
		$changedCon->setContractBegin($con_begin);
		$changedCon->setContractEnd($con_end);
	} elseif ((!$illegal_begin) && ($con_begin < $changedCon->getContractEnd()))
		$changedCon->setContractBegin($con_begin);
	elseif ((!$illegal_end) && ($con_end > $changedCon->getContractBegin()))
		$changedCon->setContractEnd($con_end);	
	
	$changedCon->store();
}

//create a new request
if (($supporter) && ($create_req)) {
	$req_date = time();
	
	$createdReq = new RequestObject('', $supportdb_data["actual_con"], '', $req_date, '', '', '');
	$createdReq->create();

	$supportdb_data["req_opens"][$createdReq->getId()] = TRUE;
	$supportdb_data["actual_req"] = $createdReq->getId();
	$edit_req_object = $createdReq->getId();
	
	//check, if participants for the Veranstaltung are avaiable
	$query = sprintf("SELECT user_id FROM seminar_user WHERE seminar_id = '%s' AND status IN ('tutor', 'autor')", $SessSemName[1]);
	$db->query($query);
	if (!$db->nf()) {
		$msg->addMsg(9);
	}
}

//cancel a just created request
if (($supporter) && ($cancel_edit_req)) {
	$killReq = new RequestObject($cancel_edit_req);
	if (($killReq->isUnchanged()) && ($killReq->isDeleteable())) {
		$killReq->delete();

		unset($supportdb_data["req_opens"][$killReq->getId()]);
		if ($kill_req == $supportdb_data["actual_req"])
			unset($supportdb_data["actual_req"]);
	}
}

//kill a request
if (($supporter) && ($kill_req)) {
	$killReq = new RequestObject($kill_req);
	if ($killReq->isDeleteable())
		if ($killReq->delete())
			$msg->addMsg(5);

	unset($supportdb_data["req_opens"][$killReq->getId()]);
	if ($kill_req == $supportdb_data["actual_req"])
		unset($supportdb_data["actual_req"]);
}

//changes for a request are coming in...
if (($supporter) && ($sent_req_id)) {
	require_once ('lib/calendar_functions.inc.php'); //needed for extended checkdate
	$changedReq = new RequestObject($sent_req_id);
	$changedReq->restore();

	$changedReq->setName($req_name);
	$changedReq->setUserId($req_user_id);
	if ($req_channel)
		$changedReq->setChannel($req_channel);
	if ($req_topic_id != "FALSE")
		$changedReq->setTopicId($req_topic_id);
	else
		$changedReq->setTopicId(FALSE);	
	
	//checkdates
	if (!check_date($req_month, $req_day, $req_year, $req_hour, $req_min)) {
		$msg->addMsg(6);
	} else
		$changedReq->setDate(mktime($req_hour,$req_min,0,$req_month, $req_day, $req_year));
	
	$changedReq->store();
	}

//create a new event
if (($supporter) && ($create_evt)) {
	$evt_end = mktime(date("H", time()),(date("i", time())+30),0, date("m", time()), date("d", time()), date("Y", time()));

	$createdEvt = new EventObject('', $create_evt, time(), $evt_end, '', '');
	$createdEvt->create();

	$supportdb_data["evt_edits"][$createdEvt->getId()] = TRUE;
}

//edit a new event
if (($supporter) && ($edit_evt)) {
	$supportdb_data["evt_edits"][$edit_evt] = TRUE;
}

//changes for one ore more events coming in...
if (($supporter) && ($evt_sent_x)) {
	require_once ('lib/calendar_functions.inc.php'); //needed for extended checkdate
	
	foreach ($evt_id as $key=>$id) {
		$changedEvt = new EventObject($id);
		$changedEvt->restore();

		if (($evt_user_id[$key]) && ($evt_user_id[$key] != "FALSE") )
			$changedEvt->setUserId($evt_user_id[$key]);
		
		$illegal_begin = FALSE;
		$illegal_end = FALSE;

		//checkdates
		if (!check_date($evt_begin_month[$key], $evt_begin_day[$key], $evt_begin_year[$key], $evt_begin_hour[$key], $evt_begin_min[$key])) {
			//$msg->addMsg(2);
			$illegal_begin=TRUE;
		} else
			$evt_begin = mktime($evt_begin_hour[$key],$evt_begin_min[$key],0,$evt_begin_month[$key], $evt_begin_day[$key], $evt_begin_year[$key]);

		if (!check_date($evt_end_month[$key], $evt_end_day[$key], $evt_end_year[$key], $evt_end_hour[$key], $evt_end_min[$key])) {
			//$msg -> addMsg(3);
			$illegal_end=TRUE;						
		} else
			$evt_end = mktime($evt_end_hour[$key],$evt_end_min[$key],0,$evt_end_month[$key], $evt_end_day[$key], $evt_end_year[$key]);
		
		if ((!$illegal_begin) && (!$illegal_end) && ($evt_begin < $evt_end)) {
			$changedEvt->setBegin($evt_begin);
			$changedEvt->setEnd($evt_end);
		} elseif ((!$illegal_begin) && ($evt_begin < $changedEvt->getEnd()))
			$changedEvt->setBegin($evt_begin);
		elseif ((!$illegal_end) && ($evt_end > $changedEvt->getBegin()))
			$changedEvt->setEnd($evt_end);

		if ($changedEvt->getUserId()) {
			$changedEvt->store();
			if ((!$illegal_begin) && (!$illegal_end) && (!$changedEvt->isUnchanged()));
				unset($supportdb_data["evt_edits"][$id]);			
		} else
			$msg->addMsg(7);
	}
}

//kill an events
if (($supporter) && ($kill_evt)) {
	$killEvt = new EventObject($kill_evt);
	$killEvt->delete();

	unset($supportdb_data["evt_edits"][$kill_evt]);
}

//Illegal view?
if (($supportdb_data["view"] == "requests") && (!$supportdb_data["actual_con"])) {
	$msg->addMsg(8);
	$supportdb_data["view"] = "overview";
}

//search expression?
if (($search_exp) || ($show_all))
	$supportdb_data["req_search_exp"] = $search_exp;

//reset s search?
if ($reset_search_x) {
	unset ($supportdb_data["req_search_exp"]);
}	unset ($supportdb_data["req_show_all"]);
	
//show all requests?
if ($show_all)
	$supportdb_data["req_show_all"] = TRUE;
?>
