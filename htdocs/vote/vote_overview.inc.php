<?php

/**

 * Overview of all existing votes ... vote_overview.inc.php

 *

 * @author     Christian Bauer <alfredhitchcock@gmx.net>

 * @version    $Id$

 * @copyright  2003 Stud.IP-Project

 * @access     public

 * @module     vote_overview

 * @package    vote

 */

/* ************************************************************************** *
/*																			  *
/* including needed files													  *
/*																			  *
/* ************************************************************************* */
require_once($ABSOLUTE_PATH_STUDIP . "seminar_open.php");
require_once($ABSOLUTE_PATH_STUDIP . "html_head.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "header.php");
require_once($ABSOLUTE_PATH_STUDIP . "links_admin.inc.php");

include_once($ABSOLUTE_PATH_STUDIP . "vote/view/visual.inc.php");
include_once($ABSOLUTE_PATH_STUDIP . "vote/Vote.class.php");
include_once($ABSOLUTE_PATH_STUDIP.  "vote/TestVote.class.php");
include_once($ABSOLUTE_PATH_STUDIP . "vote/VoteDB.class.php");
include_once($ABSOLUTE_PATH_STUDIP . "vote/StudipObject.class.php");
include_once($ABSOLUTE_PATH_STUDIP . "vote/view/vote_overview.lib.php");
/* **END*of*including*needed*files****************************************** */


/* ************************************************************************** *
/*																			  *
/* initialize post/get variables											  *
/*																			  *
/* ************************************************************************* */
$voteaction     							= $HTTP_POST_VARS['voteaction'];
	if (empty($voteaction)) 	$voteaction	= $HTTP_GET_VARS['voteaction'];
	if (empty($voteaction))		$voteaction	= NULL;
$showrangeID								= $HTTP_POST_VARS['rangeID'];
	if(empty($showrangeID))	$showrangeID	= $HTTP_GET_VARS['showrangeID'];
	if(empty($showrangeID)) $showrangeID	= $HTTP_GET_VARS['rangeID'];
	if(empty($showrangeID)) $showrangeID	= $HTTP_POST_VARS['showrangeID'];
	if(empty($showrangeID)) $showrangeID	= NULL;
$voteID     								= $HTTP_POST_VARS['voteID'];
	if(empty($voteID))      	$voteID 	= $HTTP_GET_VARS['voteID'];
	if(empty($voteID)) 	    	$voteID 	= NULL;
$openID										= $HTTP_GET_VARS['openID'];
	if(empty($openID))			$openID 	= $HTTP_GET_VARS['voteopenID'];
	if(empty($openID))			$openID 	= NULL;
$searchRange 								= htmlready($HTTP_POST_VARS['searchRange']);
	if(empty($searchRange))		$searchRange= NULL;





/* **END*of*initialize*post/get*variables*********************************** */


/* ************************************************************************** *
/*																			  *
/* check permission															  *
/*																			  *
/* ************************************************************************* */
global $perm;

if ($perm->have_perm("root"))
	$rangemode = "root";
elseif ($perm->have_perm("admin"))
	$rangemode = "admin";
elseif ($perm->have_perm("dozent"))
	$rangemode = "dozent";
elseif ($perm->have_perm("tutor"))
	$rangemode = "dozent";
elseif ($perm->have_perm("autor"))
	$rangemode = "autor";
else
	printSafeguard("ausruf",_("Fehler: Sie haben keine Berechtigung"
				. "f&uuml;r diese Seite."));

$userID = $user->id;
if ($showrangeID){
	if (($perm->have_studip_perm("tutor",$showrangeID)) ||
		(get_username($userID) == $showrangeID)){
		}
	else{
		printSafeguard("ausruf",_("Sie haben kein Berechtigung für diesen Bereich oder der Bereich existiert nicht."
				. "Es werden Votings und Tests ihrer persönlichen Homepage angezeigt."));
		$showrangeID = get_username ($userID);
	}
}
else{
	printSafeguard("ausruf",_("Kein Bereich ausgewählt. Es werden"
				. "Votings und Tests ihrer persönlichen Homepage angezeigt."));
	$showrangeID = get_username ($userID);
	}

/* ************************************************************************** *
/*																			  *
/* construct the available ranges											  *
/*																			  *
/* ************************************************************************* */
$voteDB = &new VoteDB();

$typen = array("user"=>_("Benutzer"),"sem"=>_("Veranstaltung"),"inst"=>_("Einrichtung"),"fak"=>_("Fakult&auml;t"));

if ($rangemode == "root"){
	$range[] = array("studip",_("Systemweite Votings/Tests"));
	$range[] = array(get_username($userID),_("pers&ouml;nliche Homepage"));
	if (($showrangeID != "studip") && 
	    ($showrangeID != get_username ($userID))
		&& ($showrangeID != NULL))
		$range[] = array($showrangeID,$voteDB->getRangename($showrangeID));
}
elseif ($rangemode == "admin"){
//	$range[] = array("studip",_("Fak/InstSystemweite Votings/Tests"));
	$range[] = array(get_username($userID),_("pers&ouml;nliche Homepage"));
	if (($showrangeID != get_username ($userID))
		&& ($showrangeID != NULL))
		$range[] = array($showrangeID,$voteDB->getRangename($showrangeID));
}
elseif ($rangemode == "dozent" OR $rangemode == "tutor") {
	$range[] = array(get_username($userID),_("pers&ouml;nliche Homepage"));
	$rangeARUser = $voteDB->search_range("");
	if(!empty($rangeARUser)){
	foreach ($rangeARUser as $k => $v) {
		while (list($typen_key,$typen_value)=each ($typen)) {
       		if ($v["type"]==$typen_key){
				//$html.= "\$type: ".$v["type"]." || ID=$k -> Name=".$v["name"]."\n";
				//$range[]=array($k,["$typen_key"].":".$v["name"]);
				$range[] = array($k,$typen_value.":".$v["name"]);
				}
		}
		reset($typen);
	}
	}
}
elseif ($rangemode == "autor"){
	$range[] = array(get_username($userID),_(" auf der pers&ouml;nlichen Homepage"));
}
else{
	$range[] = array("hallo",_("Fehler: Kein gültiger User"));
}

/* ************************************************************************** *
/*																			  *
/* displays the site														  *
/*																			  *
/* ************************************************************************* */

// creates an array with all the labels
$label = createLabel();
// get the userid

// Displays the title
printSiteTitle();

// If a votes attribute(s) is to be modified, the action will be execute here.
if ($voteaction)	callSafeguard($voteaction, $voteID, $showrangeID, $searchRange);

// Displays the Options to create a new Vote or Test
// and the selection of displayed votes/tests
printSelections($range,$searchRange);
$voteDB = &new VoteDB();

// starting waiting votes
$voteDB->startWaitingVotes ();
	if ($voteDB->isError ())
		printSafeguard("ausruf",_("Fehler beim starten der wartenden"
				. "Votings und Tests."));

if ($voteaction != "search"){
	// reads the vote data into arrays
	$newvotes 		= createVoteArray(VOTE_STATE_NEW);
	$activevotes 	= createVoteArray(VOTE_STATE_ACTIVE);
	$stoppedvotes 	= createVoteArray(VOTE_STATE_STOPPED);

	// Displays the VoteArrays in a table
	printVoteTable("start_table");
	if(($rangemode == "root" ) || ($rangemode == "admin") || ($rangemode == "dozent"))
		printVoteTable("printTitle",$voteDB->getRangename($showrangeID));
	printVoteTable(VOTE_STATE_NEW, 	   $newvotes,	  $openID);
	printVoteTable(VOTE_STATE_ACTIVE,  $activevotes,  $openID);
	printVoteTable(VOTE_STATE_STOPPED, $stoppedvotes, $openID);
	printVoteTable("end_table");
}
elseif (($voteaction == "search") && (($rangemode == "root") || ($rangemode == "admin"))){
	if ($searchRange != NULL){
		$rangeAR 	= $voteDB->search_range($searchRange);
		printSearchResults($rangeAR,$searchRange);
	}
	else
		printSearchResults(NULL,NULL);
}
/* **END*of*displays*the*site*********************************************** */


/* ************************************************************************** *
/*																			  *
/* private functions														  *
/*																			  *
/* ************************************************************************* */

/*
 * modifies the vote and calls printSafeguard
 *
 * @access private
 * @param voteaction	string comprised the action
 */
function callSafeguard($voteaction, $voteID = "", $showrangeID = NULL, $search = NULL){
	$voteDB = &new voteDB;
	$votechanged = NULL;
	
	if ($type = $voteDB->getType($voteID) == "vote"){
		$vote = &new Vote($voteID);
		$typename = _("Das Voting");
	}
	else{
		$vote = &new TestVote($voteID);
		$typename = ($voteaction != "delete_request") 
			? _("Der Test")
			: _("Den Test");
	}
	
	// If theres an error ... print it and return
	if ($vote->isError()){
		createErrorReport ($vote);
		printSafeguard("",createErrorReport($vote));
		//return;
	}
	$votename = htmlReady($vote->getTitle($voteID));
	//$vote->finalize ();

	switch ($voteaction){
		case "change_visibility":
			if ($vote->getResultvisibility() != VOTE_RESULTS_NEVER){
				if($vote->isVisible()){
					$vote->executeSetVisible(NO);
					printSafeguard("ok","$typename \"$votename\" "
						. _("wurde für die Teilnehmer unsichtbar gemacht."));
				}
				else{
					$vote->executeSetVisible(YES);
						if ($vote->isError()){
							createErrorReport ($vote);
							printSafeguard("",createErrorReport($vote));
							return;
						}
					printSafeguard("ok","$typename \"$votename\" "
						. _("wurde für die Teilnehmer sichtbar gemacht."));
				}
				$votechanged = 1;
			}
			else{
				printSafeguard("ausruf","$typename \"$votename\""._(" wurde beim "
						. "Erstellen auf \"Der Teilnehmer sieht die (Zwischen-"
						. ")Ergebnisse: Nie\" eingestellt.<br> Sollen die End"
						. "ergebnisse jetzt trotzdem f&uuml;r die Teilnehmer "
						. "sichtbar gemacht werden? (Wenn dieser Eintrag "					
						. "fortgesetzt werden sollte, werden die Ergebnisse nach "
						. "Ablauf ohne weitere Nachfrage für die Teilnehmer sichtbar gemacht!)"),
						  "NeverResultvisibility",$voteID, $showrangeID);
				
			}
			break;
		case "setResultvisibility_confirmed":
			$vote->setResultvisibility(VOTE_RESULTS_AFTER_END);
			// error_ausgabe
			if ($vote->isError()){
				createErrorReport ($vote);
				printSafeguard("",createErrorReport($vote));
				return;
			}
			$vote->executeWrite();
			$vote->executeSetVisible(YES);
			if ($vote->isError()){
				createErrorReport ($vote);
				printSafeguard("",createErrorReport($vote));
				return;
			} 
			printSafeguard("ok","$typename \"$votename\""._(" wurde jetzt f&uuml;r "
				. "die Teilnehmer sichtbar gemacht."));
			$votechanged = 1;
			break;
		case "setResultvisibility_aborted":
			printSafeguard("ausruf","$typename \"$votename\""._(" wurde f&uuml;r die "
				. "Teilnehmer nicht sichtbar gemacht."));
			break;
		case "start":
			$vote->executeStart();
			// error_ausgabe
			if ($vote->isError()){
				createErrorReport ($vote);
				printSafeguard("",createErrorReport($vote));
				return;
			} 
			printSafeguard("ok","$typename \"$votename\""._(" wurde gestartet."));
			$votechanged = 1;
			break;
		case "stop":
			$vote->executeStop();
			// error_ausgabe
			if ($vote->isError()){
				createErrorReport ($vote);
				printSafeguard("",createErrorReport($vote));
				return;
			} 
			printSafeguard("ok","$typename \"$votename\""._(" wurde gestoppt."));
			$votechanged = 1;
			break;
		case "continue":
			$vote->executeContinue();
			// error_ausgabe
			if ($vote->isError()){
				createErrorReport ($vote);
				printSafeguard("",createErrorReport($vote));
				return;
			} 
			printSafeguard("ok","$typename \"$votename\" "
				. _("wurde fortgesetzt."));
			$votechanged = 1;
			break;
		case "restart":
			$vote->executeRestart();
			// error_ausgabe
			if ($vote->isError()){
				createErrorReport ($vote);
				printSafeguard("",createErrorReport($vote));
				return;
			} 
			printSafeguard("ok","$typename \"$votename\" "
				. _("wurde zur&uuml;ckgesetzt."));
			$votechanged = 1;
			break;
		case "delete_request":
			printSafeguard("ausruf","$typename \"$votename\" "
				. _("wirklich l&ouml;schen?"),"delete_request",$voteID, $showrangeID);
			break;
		case "delete_confirmed":
			$vote->executeRemove();
			// error_ausgabe
			if ($vote->isError()){
				createErrorReport ($vote);
				printSafeguard("",createErrorReport($vote));
				return;
			} 
			printSafeguard("ok","$typename \"$votename\" "
				. _("wurde gel&ouml;scht."));
			$votechanged = 1;
			break;
		case "delete_aborted":
			printSafeguard("ok","$typename \"$votename\" "
				. _("wurde nicht gel&ouml;scht."));
			break;
		case "created":
			printSafeguard("ok","$typename \"$votename\""._(" wurde angelegt."));
			break;
		case "saved":
			printSafeguard("ok","$typename \"$votename\" "
				. _("wurde mit den Ver&auml;nderungen gespeichert."));
			break;
		case "search":
			//nothing
			break;
		default:
			printSafeguard("ausruf",_("Fehler bei 'voteaction'! Es wurde versucht, eine "
				. "nicht vorhandene Aktion auszuführen."));
			break;
	}
	global $auth;
	if(($votechanged) && ($vote->getAuthorID() != $auth->auth["uid"])) {
	    // user's vote has been modified by admin/root
	    // --> send notification sms
	    $sms = new messaging();
	    $sms->insert_sms( $vote->voteDB->getAuthorUsername($vote->getAuthorID()),
			      mysql_escape_string( sprintf( _("An Ihrem %s \"%s\" wurden von dem Administrator oder der ".
						      "Administratorin %s Änderungen vorgenommen."),
						    ($vote->instanceof() == INSTANCEOF_TEST
						    ? _("Test") : _("Voting")), $vote->getTitle(),
						    $vote->voteDB->getAuthorRealname($auth->auth["uid"]) ) ),
			      "____%system%____" );
	}
	

}

/**
 * reads the vote data into an array
 *
 * @access private
 * @param mode	string 'new', 'active' or 'stopped'
 * @returns array Array with all the data
 */
function createVoteArray($mode){
 	global $rangemode,$showrangeID, $userID;

	$username = "";
	$voteDB = &new VoteDB();
	// request the right data from the db / all ranges
  if (($rangemode == "root" ) || ($rangemode == "admin") || ($rangemode == "dozent")){
		switch ($mode){
			case VOTE_STATE_NEW:
					$votearrays = $voteDB->getNewVotes($showrangeID);
				break;
			case VOTE_STATE_ACTIVE:
					$votearrays = $voteDB->getActiveVotes($showrangeID);
				break;
			case VOTE_STATE_STOPPED:
					$votearrays = $voteDB->getStoppedVotes($showrangeID);
				break;
			default:
				break;
		}
	}
  else{
/*	if ($showrangeID == "all_ranges"){
		switch ($mode){
			case VOTE_STATE_NEW:
					$votearrays = $voteDB->getNewUserVotes($userID);
				break;
			case VOTE_STATE_ACTIVE:
					$votearrays = $voteDB->getActiveUserVotes($userID);
				break;
			case VOTE_STATE_STOPPED:
					$votearrays = $voteDB->getStoppedUserVotes($userID);
				break;
			default:
				break;
		}
	}
	else { // request the right data from the db / just on range
*/
		switch ($mode){
			case VOTE_STATE_NEW:
					$votearrays = $voteDB->getNewUserVotes($userID);
				break;
			case VOTE_STATE_ACTIVE:
					$votearrays = $voteDB->getActiveUserVotes($userID,
						$showrangeID);
				break;
			case VOTE_STATE_STOPPED:
					$votearrays = $voteDB->getStoppedUserVotes($userID,
						$showrangeID);
				break;
			default:
				break;
		}
//	}
  }

	// create one array-row for each located voteID
	foreach ($votearrays as $votearray) {

		// extract the voteID
		$voteID = $votearray["voteID"];
		
		// create an object of the current vote
		if 
		 ($votearray["type"] == "vote")
			$vote = &new Vote($voteID);
		else
			$vote = &new TestVote($voteID);

		// If theres an error ... print it and return
		if ($vote->isError()){
			echo createErrorReport ($vote);
			//return;
		} 
		
		// read out the required data
		$changedate = $vote->getChangedate();
		$title = htmlready($vote->getTitle());
		$rangeID = $vote->getRangeID();
		if (($rangemode == "root" ) || ($rangemode == "admin") || ($rangemode == "dozent")){
			$authID = $vote->getAuthorID();
			$rangetitle = $voteDB->getAuthorRealname($authID);
			$username = $voteDB->getAuthorUsername ($authID);
		}
		else{
			$rangetitle = $voteDB->getRangename($rangeID);
			$username = $voteDB->getAuthorUsername ($authID);
			if($rangeID == "studip") $rangetitle = "Startseite: studip";
		}
		$votemode = $votearray["type"];

		if ($voteDB->isAssociated($voteID, $userID))
			$isAssociated = YES;
		else
			$isAssociated = NO;
		
		$vote->finalize ();
		
		// read out the special data of the status 
		switch ($mode){
			case VOTE_STATE_NEW:
					$special_data = $vote->getStartdate();
				break;
			case VOTE_STATE_ACTIVE:
					$special_data = $vote->getRealStopdate();
				break;
			case VOTE_STATE_STOPPED:
					if($vote->isVisible()) 	$special_data = "visible";
					else 				   	$special_data = "invisible";
				break;
			default:
				break;
		}
		// if $special_data contents timestamp, it shold be transformed 
		if (($mode == VOTE_STATE_NEW) || ($mode == VOTE_STATE_ACTIVE)){
			if ($special_data)
				$special_data = date("d", $special_data)."."
					.date("m", $special_data).".".date("Y", $special_data);
			else
				$special_data = "-";
		}


		$votes[] = array(
						"voteID" => $voteID,
						"changedate" => $changedate,
						"title" => $title,
						"rangetitle" => $rangetitle,
						"secial_data" => $special_data,
						"isAssociated" => $isAssociated,
						"username" => $username,
						"type" => $votemode);
	}
	
	return $votes;
}

/**
 * creates an array with all used labes
 *
 * @access private
 * @returns array an array with all the labels 
 */
function createLabel(){
	$label = array(
		// labels for printSiteTitle
		"sitetitle_title" => _("Voting-Verwaltung:"),

		// labels for printSelections
		"selections_text_vote" => _("Ein neues Voting"),
		"selections_text_test" => _("Einen neuen Test"),
		"selections_text_end" => "",
		"selections_button" => _("erstellen"),
		"selections_tooltip" => _("Voting oder Test erstellen."),
		"selections_selectrange_text" => _("Votings/Tests aus "),
		"selections_selectrange_text_end" => "",
		"selections_allranges" => _("allen Bereichen"),
		"selections_selectrange_button" => _("anzeigen"),
		"selections_selectrange_tooltip" => _("Bereich der angezeigten "
			. "Votings ausw&auml;hlen."),
			
		// labels for printSearchResults
		"searchresults_title" => _("Suchergebnisse"),
		"searchresults_no_string" => _("Bitte geben sie ein l&auml;ngeres Suchmuster ein."),
		"searchresults_no_results" => _("Keine Suchergebnisse."),
		"searchresults_no_results_range" => _("Keine Suchergebnisse in diesem Bereich."),
		
		// labels for printSearch
		"search_text" => _("Nach weiteren Bereichen suchen: "),
		"search_button" => _("suchen"),
		"search_tooltip" => _("Hier k&ouml;nnen Sie nach weiteren Bereichen suchen."),
		
		// labels for printVoteTable
		
		"table_title" => _("Votings und Tests aus dem Bereich"),
		"table_title_new" => _("Noch nicht gestartete Votings/Tests:"),
		"table_title_active" => _("Laufende Votings/Tests:"),
		"table_title_stopped" => _("Gestoppte Votings/Tests:"),	
		
		"arrow_openthis" => _("Aufklappen"),
		"arrow_closethis" => _("Zuklappen"),
		

		"title" => _("Titel"),
		"range" => _("Bereich"),
		"user" 	=> _("Autor"),

		"startdate" => _("Startdatum"),
		"enddate" => _("Ablaufdatum"),

		"visibility" => _("Sichtbarkeit"),
		"visibility_alt" => array(
			"invis" => _("Dieser Eintrag ist f&uuml;r die User unsichtbar."),
			"vis" => _("Dieser Eintrag ist f&uuml;r User sichtbar.")),
		"visibility_tooltip" => array(
			"invis" => _("Diesen Eintrag f&uuml;r die User sichtbar machen."),
			"vis" => _("Diesen Eintrag f&uuml;r die User unsichtbar machen.")),

		"status" => _("Status"),
		"status_button_new" => _("start"),
		"status_tooltip_new" => _("Diesen Eintrag jetzt starten."),
		"status_button_active" => _("stop"),
		"status_tooltip_active" => _("Diesen Eintrag jetzt stoppen."),
		"status_button_stopped" => _("fortsetzen"),
		"status_tooltip_stopped" => _("Diesen Eintrag jetzt fortsetzen."),

		"restart_button" => _("zuruecksetzen"),	
		"restart_tooltip" => _("Alle bisherig abgegebenen Antworten l&ouml;schen"),		

		"edit" => _("Bearbeiten"),
		"edit_button" => _("bearbeiten"),
		"edit_tooltip" => _("Diesen Eintrag bearbeiten"),

		"makecopy" => "",
		"makecopy_button" => _("kopieerstellen"),
		"makecopy_tooltip" => _("Diesen Eintrag jetzt als Kopie neu erstellen."),

		"delete" => _("L&ouml;schen"),
		"delete_button" => _("loeschen"),
		"delete_tooltip" => _("Diesen Eintrag l&ouml;schen"),
		"no_votes_message_new" => _("Keine nicht gestarteten Votings oder Tests vorhanden."),
		"no_votes_message_active" => _("Keine laufenden Votings oder Tests "
			. "vorhanden."),
		"no_votes_message_stopped" => _("Keine gestoppten Votings oder Tests "
			. "vorhanden."),
	);
	return $label;
}
/* **END*of*private*functions*********************************************** */
?>
