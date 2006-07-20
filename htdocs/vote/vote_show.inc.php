<?php

/**
 * This file is used to insert a vote in Stud.IP.
 *
 * @author      Alexander Willner <mail@AlexanderWillner.de>,
 *              Michael Cohrs <michael A7 cohrs D07 de>
 * @version     $Id$
 * @copyright   2003 Stud.IP-Project (GNU General Public License)
 * @access      public
 * @module      vote_show
 * @package     vote
 * @modulegroup vote_modules
 */


# Include all required files ================================================ #

require_once ($ABSOLUTE_PATH_STUDIP."vote/view/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."vote/view/vote_show.lib.php");
require_once ($ABSOLUTE_PATH_STUDIP."vote/VoteDB.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."vote/Vote.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."vote/TestVote.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."modules/evaluation/evaluation.config.php");
require_once (EVAL_FILE_OBJECTDB);
require_once (EVAL_FILE_EVAL);
require_once (EVAL_LIB_SHOW);
# ====================================================== end: including files #

// don't use evaluation.css here
unset ($_include_extra_stylesheet);

# Define public functions =================================================== #

/**
 * Starts waiting votes and shows active votes.
 * @param    $rangeID     string  The unique range id
 * @param    $userID      string  The unique user id
 * @param    $perm        string  The perms of the user
 * @param    $isHomepage  string  When the function is called on a homepage
 * @access   public
 */

function show_votes ($rangeID, $userID, $perm, $isHomepage = NO) {   
   /* Set variables -------------------------------------------------------- */
   $voteDB  = &new VoteDB ();
   if ($voteDB->isError ()) {
      echo createErrorReport ($voteDB, _("Vote-Datenbankfehler"));
      return;
   }  
   $evalDB  = &new EvaluationDB ();
   if ($evalDB->isError ()) {
      echo createErrorReport ($evalDB, _("Evaluation-Datenbankfehler"));
      return;
   }  
   
   if ($perm->have_studip_perm ("tutor", $rangeID) ||
       get_username($userID) == $rangeID)
      $haveFullPerm = true;
   else
      $haveFullPerm = false;
   
   $debug = "";
   /* ---------------------------------------------------------------------- */

   /* Start waiting votes -------------------------------------------------- */
   $voteDB->startWaitingVotes ();
   if ($voteDB->isError ()) {
      echo createErrorReport ($voteDB, 
			      _("Datenbankfehler bei Voteaktivierung"));
   }
   /* ---------------------------------------------------------------------- */

   /* Do nothing if there is no vote --------------------------------------- */
   $activeVotes  = $voteDB->getActiveVotes ($rangeID);
   $stoppedVotes = $voteDB->getStoppedVisibleVotes ($rangeID);
   $activeEvals  = array ();
   $stoppedEvals = array ();
  
   if (!($rangeID2 = get_userid($rangeID)))
     $rangeID2 = $rangeID;
   
   $activeEvals  = $evalDB->getEvaluationIDs ($rangeID2, EVAL_STATE_ACTIVE);
   if ($evalDB->isError ()) {
      echo createErrorReport ($evalDB, 
            _("Datenbankfehler beim Auslesen der EvaluationsIDs."));
   }
   
   
   if ($haveFullPerm) {
     $stoppedEvals = $evalDB->getEvaluationIDs ($rangeID2, EVAL_STATE_STOPPED);
     if ($evalDB->isError ()) {
         echo createErrorReport ($evalDB, 
         _("Datenbankfehler beim Auslesen der EvaluationsIDs."));
     }
   }
   
   if (empty ($activeVotes) && 
       empty ($stoppedVotes) &&
       empty ($activeEvals) && 
       empty ($stoppedEvals) &&
       !($perm->have_studip_perm ("tutor", $rangeID) ||
	 get_username($userID) == $rangeID)) {
     $voteDB->finalize ();
     return;
   }
   /* ---------------------------------------------------------------------- */

   echo "<a name=\"votetop\"></a>";
   $debug.="rangeid=$rangeID\nuserid=$userID\n";

   /* Show the vote box ---------------------------------------------------- */
   $width = ($isHomepage)? "100%" : "70%";
   
   // bei range_id = Studip 
   // aktuelle Evaluationen des Benutzers anzeigen
   // zunächst müssen dazu alle Veranstaltungen des Benutzers bestimmt werden
   
   if ($rangeID == "studip") {
   
   $db = new DB_Seminar();
   $db->query ("SELECT sem_tree_id,seminare.Name, seminare.Seminar_id, seminare.status as sem_status, seminar_user.status, seminar_user.gruppe,
				seminare.chdate, seminare.visible, admission_binding,modules,IFNULL(visitdate,0) as visitdate
				FROM seminar_user LEFT JOIN seminare  USING (Seminar_id) 
				LEFT JOIN object_user_visits ouv ON (ouv.object_id=seminar_user.Seminar_id AND ouv.user_id='$user->id' AND ouv.type='sem')
				LEFT JOIN seminar_sem_tree sst ON (sst.seminar_id=seminar_user.seminar_id)
				WHERE seminar_user.user_id = '". $userID . "'");
   $currentid = 0;
   $activecourseEvals = array();
   while ($db->next_record()){
   	  $semid = $db->f("Seminar_id");
   	  $evalids = $evalDB->getEvaluationIDs($semid,EVAL_STATE_ACTIVE,true);
   	  //$evalids = array($evalDB->getEvaluationIDs($db->f("Seminar_id"),EVAL_STATE_ACTIVE),$db->f("Name"));
   	  $semname = $db->f("Name");	  
   	  if (count($evalids) > 0){   	  	
   	  	$semarray[] = $evalids;
   	  	$semarray[] = $semid;
   	  	$semarray[] = $semname;
   	  
   	  	$activecourseEvals[] = $semarray;
   	  }   	  
   	  $semarray = array();
   }
   
   if (count($activecourseEvals) > 0) {
	   if ($perm->have_studip_perm ("tutor", $rangeID) OR
	       get_username($userID) == $rangeID){
	      		echo createBoxHeader (_("Lehrveranstaltungsevaluationen zu meinen Veranstaltungen"), $width, "",
				    VOTE_ICON_BIG, 
				    _("Evaluationen..."), 
				    VOTE_FILE_ADMIN."?page=overview&rangeID=".$rangeID.
				    ($GLOBALS['SessSemName']["class"]=="sem"
				     ? "&new_sem=TRUE&view=vote_sem"
				     : "&new_inst=TRUE&view=vote_inst"),
				    VOTE_ICON_ARROW, _("Umfragen bearbeiten"));
	   }
	   else {
	      	echo createBoxHeader (_("Lehrveranstaltungsevaluationen zu meinen Veranstaltungen"), $width, "",
				    VOTE_ICON_BIG, 
				    _("Evaluationen..."));
	   }
  }
  else {
  	echo ("<br>");
  	//echo("<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" align=\"center\" width=\"$width\"><tr><td class=\"steel1\" colspan=\"3\">");
  }

  
  
  	/* create an anchor ---------------------------------------------------- */
   echo "<a name=\"vote\"></a>";
   /* ---------------------------------------------------------------------- */

   /* Javascript function for show-link */
   echo EvalCommon::createEvalShowJS( NO, NO );
   
  /* Show all active evals for courses, the user is in------------------------------------------------ */
  if (is_Array($activecourseEvals)){
	  foreach ($activecourseEvals as $semarray){
	   foreach ($semarray[0] as $evalID) {
	      $eval = &new Evaluation ($evalID, NULL, EVAL_LOAD_NO_CHILDREN);
		  $usersvoted = $evalDB->getUserVoted($evalID);
		  if ($userID )
	      if ($eval->isError ()) {
	         echo createErrorReport ($vote, _("Fehler beim Einlesen der Evaluation"));
	      }
	      if (!$eval->isProtected()){
		  	// überspringen, gehört nicht zu uniweiten Evaluationen
		  	continue;
		  }
	      if ($eval->isLinked()){
	      	// Bearbeitung beenden, nicht anzeigen.
	      	continue;
	      }
	      
	     
	      $haveFullPerm = $haveFullPerm || ($userID == $eval->getAuthorID());
	 	  
	      /* Get post and get-variables ---------------------------------------- */
	      $formID = $_REQUEST["voteformID"];
	      $openID = $_REQUEST["voteopenID"];
	      $open = (($openID == $evalID) || $_GET["openAllVotes"]) && (!$_GET["closeVotes"]);
	      /* ------------------------------------------------------------------- */
	      /* Show headlines ---------------------------------------------------- */
	      echo createBoxLineHeader ();
	      echo createVoteHeadline ( $eval, $open, $openID, $evalDB, $isHomepage,$semarray[2],$semarray[1]);
	
	      if ( $open ) {
		 	 object_set_visit($evalID, "eval"); //set a visittime for this eval
		 
	         echo createBoxContentHeader ();
	         echo createFormHeader ($eval);
	         
	     	/* User has already used the vote --------------------------------- */
	         $hasVoted = $evalDB->hasVoted ($evalID, $userID);
	         $numberOfVotes = $evalDB->getNumberOfVotes ($evalID);
	         $evalNoPermissons = EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval);
	         
	         $table = new HTML ("table");
	         $table->addAttr("style", "font-size:1.2em;");
	         $table->addAttr("width", "100%");
	         $table->addAttr("border", "0");
	         $tr = new HTML ("tr");
	         $td = new HTML ("td");
	         
	         $maxTitleLength = ($isHomepage)
	            ? VOTE_SHOW_MAXTITLELENGTH
	            : VOTE_SHOW_MAXTITLELENGTH - 10;
	
	         if (strlen (formatReady($eval->getTitle())) > $maxTitleLength){
	            $b = new HTML ("b");
	            $b->addHTMLContent(formatReady($eval->getTitle(). " in " . $semarray[2]));
	            
	            $td->addContent($b);
	            $td->addContent( new HTMLempty ("br") );
	            $td->addContent( new HTMLempty ("br") );
	         }
	         
		 $td->addAttr("style", "font-size:0.8em;");
	         $td->addHTMLContent(formatReady($eval->getText ()));
	         $td->addContent(new HTMLempty ("br"));
	         $td->addContent(new HTMLempty ("br"));
	         
	         if (! $hasVoted ) {
	            $div = new HTML ("div");
	            $div->addAttr ("align", "center");
	            $div->addContent (EvalShow::createVoteButton ($eval));
	            $td->addContent ($div);
	         }
	         
	         $tr->addContent ($td);
	         $table->addContent ($tr);
	         $table->addContent (EvalShow::createEvalMetaInfo ($eval, $hasVoted));
	         
	         if ($eval->isProtected() && !$perm->have_perm("admin")){
	         		if ($perm->have_perm("dozent")){
		         		$tr = new HTML ("tr");
			            $td = new HTML ("td");
			            $td->addAttr ("align", "left");
			            $td->addAttr("style", "font-size:0.8em;color:#ff0000;");
			            $td->addContent (_("Die Evaluation ist geschützt und darf nur durch den Administrator verändert werden."));
			            $tr->addContent ($td);
			            $table->addContent ($tr);
			            
		           
		         		// ermöglicht, Evaluation mit dieser zu verknüpfen
		         		$tr = new HTML ("tr");
			            $td = new HTML ("td");
			            $td->addAttr ("align", "left");
			            $td->addAttr("style", "font-size:0.8em;color:#ff0000;");
			            if ($eval->hasVoted()){
			            	$td->addContent (_("Dieser Evaluation darf keine Verknüpfung mehr zu einem anderen Fragebogen hinzugefügt werden."));
			            }
			            else {
			            	$link = new HTML( "a" );
						    $link->addAttr( "href", "admin_evaluation.php?page=link&evalID=".$evalID );
						    $img = new HTMLEmpty( "img" );
						    $img->addString( makeButton( "zuweisen", "src" ).tooltip(_("Evaluation mit anderer Evaluation verknüpfen")) );
						    $img->addAttr( "border", "0" );
						    $img->addAttr( "align", "middle" );
						    $link->addContent( $img );
						    $td->addContent($link);
			            }
			            $tr->addContent ($td);
			            $table->addContent ($tr);
		         	}
	         }
	         else {
	         	 if ( $haveFullPerm ) {
		            if (!($range = get_username($rangeID2)))
		               $range = $rangeID2;
		            $tr = new HTML ("tr");
		            $td = new HTML ("td");
		            $td->addAttr ("align", "center");
		            $td->addContent (EvalShow::createOverviewButton ($range, $eval->getObjectID ()));
		
		            if ( $evalNoPermissons == 0 ) {
		            $td->addContent (EvalShow::createStopButton ($eval));
		            $td->addContent (EvalShow::createDeleteButton ($eval));
		            $td->addContent (EvalShow::createExportButton ($eval));
		            }
		            
		            $tr->addContent ($td);
		            $table->addContent ($tr);
		         }
		         
		         
	         }
	         
	         echo $table->createContent ();
	         //echo createVoteForm ($eval, $userID);
	     /* --------------------------------------------------------------- */
	      //echo createFormFooter ($eval, $userID, $perm, $rangeID);
	      echo createBoxContentFooter ();
	      }
	      /* ------------------------------------------------------------------- */
	      
	      echo createBoxLineFooter ();
	   }
	  }
	 }
      echo "<tr><td height=10><p></td></tr>";
   }
   // ende Übersicht über Evaluationen in meinen Veranstaltungen
    
   if (count($activeEvals) + count($stoppedEvals) > 0) {
	   if ($perm->have_studip_perm ("tutor", $rangeID) OR
	       get_username($userID) == $rangeID)
	      echo createBoxHeader (_("Evaluationen"), $width, "",
				    VOTE_ICON_BIG, 
				    _("Evaluationen..."), 
				    VOTE_FILE_ADMIN."?page=overview&rangeID=".$rangeID.
				    ($GLOBALS['SessSemName']["class"]=="sem"
				     ? "&new_sem=TRUE&view=vote_sem"
				     : "&new_inst=TRUE&view=vote_inst"),
				    VOTE_ICON_ARROW, _("Umfragen bearbeiten"));
	   else
	      echo createBoxHeader (_("Evaluationen"), $width, "",
				    VOTE_ICON_BIG, 
				    _("Evaluationen..."));

  }
  else {
  	echo ("<br>");
  	//echo("<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" align=\"center\" width=\"$width\"><tr><td class=\"steel1\" colspan=\"3\">");
  }
   /* create an anchor ---------------------------------------------------- */
   echo "<a name=\"vote\"></a>";
   /* ---------------------------------------------------------------------- */

   $debug .= "<b>_post_</b>\n";
   foreach ($_POST as $key=>$item ) {
      $debug .= "$key: $item\n";
   }
   $debug .= "<br><b>_get_</b>\n";
   foreach ($_GET as $key=>$item ) {
      $debug .= "$key: $item\n";
   }

   /* Javascript function for show-link */
   echo EvalCommon::createEvalShowJS( NO, NO );
   
  /* Show all active evals ------------------------------------------------ */
   foreach ($activeEvals as $evalID) {
      $eval = &new Evaluation ($evalID, NULL, EVAL_LOAD_NO_CHILDREN);
	  
      if ($eval->isError ()) {
         echo createErrorReport ($vote, _("Fehler beim Einlesen der Evaluation"));
      }
      if ($eval->isLinked()){
      	// Bearbeitung beenden, nicht anzeigen.
      	continue;
      }
      $haveFullPerm = $haveFullPerm || ($userID == $eval->getAuthorID());

      /* Get post and get-variables ---------------------------------------- */
      $formID = $_REQUEST["voteformID"];
      $openID = $_REQUEST["voteopenID"];
      $open = (($openID == $evalID) || $_GET["openAllVotes"]) && (!$_GET["closeVotes"]);
      /* ------------------------------------------------------------------- */

      /* Show headlines ---------------------------------------------------- */
      echo createBoxLineHeader ();
      echo createVoteHeadline ( $eval, $open, $openID, $evalDB, $isHomepage);

      if ( $open ) {
	 object_set_visit($evalID, "eval"); //set a visittime for this eval
	 
         echo createBoxContentHeader ();
         echo createFormHeader ($eval);
         
     	/* User has already used the vote --------------------------------- */
         $hasVoted = $evalDB->hasVoted ($evalID, $userID);
         $numberOfVotes = $evalDB->getNumberOfVotes ($evalID);
         $evalNoPermissons = EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval);
         
         $table = new HTML ("table");
         $table->addAttr("style", "font-size:1.2em;");
         $table->addAttr("width", "100%");
         $table->addAttr("border", "0");
         $tr = new HTML ("tr");
         $td = new HTML ("td");
         
         $maxTitleLength = ($isHomepage)
            ? VOTE_SHOW_MAXTITLELENGTH
            : VOTE_SHOW_MAXTITLELENGTH - 10;

         if (strlen (formatReady($eval->getTitle ())) > $maxTitleLength){
            $b = new HTML ("b");
            $b->addHTMLContent(formatReady($eval->getTitle ()));
            
            $td->addContent($b);
            $td->addContent( new HTMLempty ("br") );
            $td->addContent( new HTMLempty ("br") );
         }
         
	 $td->addAttr("style", "font-size:0.8em;");
         $td->addHTMLContent(formatReady($eval->getText ()));
         $td->addContent(new HTMLempty ("br"));
         $td->addContent(new HTMLempty ("br"));
         
         if (! $hasVoted ) {
            $div = new HTML ("div");
            $div->addAttr ("align", "center");
            $div->addContent (EvalShow::createVoteButton ($eval));
            $td->addContent ($div);
         }
         
         $tr->addContent ($td);
         $table->addContent ($tr);
         $table->addContent (EvalShow::createEvalMetaInfo ($eval, $hasVoted));
         
         if ($eval->isProtected() && !$perm->have_perm("admin")){
         		if ($perm->have_perm("dozent")){
	         		$tr = new HTML ("tr");
		            $td = new HTML ("td");
		            $td->addAttr ("align", "left");
		            $td->addAttr("style", "font-size:0.8em;color:#ff0000;");
		            $td->addContent (_("Die Evaluation ist geschützt und darf nur durch den Administrator verändert werden."));
		            $tr->addContent ($td);
		            $table->addContent ($tr);
		            
	           
	         		// ermöglicht, Evaluation mit dieser zu verknüpfen
	         		$tr = new HTML ("tr");
		            $td = new HTML ("td");
		            $td->addAttr ("align", "left");
		            $td->addAttr("style", "font-size:0.8em;color:#ff0000;");
		            if ($eval->hasVoted()){
		            	$td->addContent (_("Dieser Evaluation darf keine Verknüpfung mehr zu einem anderen Fragebogen hinzugefügt werden."));
		            }
		            else {
		            	$link = new HTML( "a" );
					    $link->addAttr( "href", "admin_evaluation.php?page=link&evalID=".$evalID );
					    $img = new HTMLEmpty( "img" );
					    $img->addString( makeButton( "zuweisen", "src" ).tooltip(_("Evaluation mit anderer Evaluation verknüpfen")) );
					    $img->addAttr( "border", "0" );
					    $img->addAttr( "align", "middle" );
					    $link->addContent( $img );
					    $td->addContent($link);
		            }
		            $tr->addContent ($td);
		            $table->addContent ($tr);
	         	}
         }
         else {
         	 if ( $haveFullPerm ) {
	            if (!($range = get_username($rangeID2)))
	               $range = $rangeID2;
	            $tr = new HTML ("tr");
	            $td = new HTML ("td");
	            $td->addAttr ("align", "center");
	            $td->addContent (EvalShow::createOverviewButton ($range, $eval->getObjectID ()));
	
	            if ( $evalNoPermissons == 0 ) {
	            $td->addContent (EvalShow::createStopButton ($eval));
	            $td->addContent (EvalShow::createDeleteButton ($eval));
	            $td->addContent (EvalShow::createExportButton ($eval));
	            }
	            
	            $tr->addContent ($td);
	            $table->addContent ($tr);
	         }
	         
	         
         }
         
         echo $table->createContent ();
         //echo createVoteForm ($eval, $userID);
     /* --------------------------------------------------------------- */
      //echo createFormFooter ($eval, $userID, $perm, $rangeID);
      echo createBoxContentFooter ();
      }
      /* ------------------------------------------------------------------- */
      
      echo createBoxLineFooter ();
   }
   /* ---------------------------------------------------------------------- */
   if (count ($activeEvals) + count ($stoppedEvals) > 1)
      echo createOpeningOrClosingArrow ();
   echo "<tr><td height=10><p></td></tr>";
   if ($perm->have_studip_perm ("tutor", $rangeID) OR
       get_username($userID) == $rangeID)
      echo createBoxHeader (_("Umfragen"), $width, "",
			    VOTE_ICON_BIG, 
			    _("Umfragen und mehr..."), 
			    VOTE_FILE_ADMIN."?page=overview&rangeID=".$rangeID.
			    ($GLOBALS['SessSemName']["class"]=="sem"
			     ? "&new_sem=TRUE&view=vote_sem"
			     : "&new_inst=TRUE&view=vote_inst"),
			    VOTE_ICON_ARROW, _("Umfragen bearbeiten"));
   else
      echo createBoxHeader (_("Umfragen"), $width, "",
			    VOTE_ICON_BIG, 
			    _("Umfragen und mehr..."));

   /* Show all active Votes ------------------------------------------------ */
   foreach ($activeVotes as $tmpVote) {

      $voteID = $tmpVote["voteID"];
   
      if ($tmpVote["type"] == INSTANCEOF_TEST)
         $vote = new TestVote ($voteID);
      else
         $vote = new Vote ($voteID);
      
      if ($vote->isError ()) {
	 echo createErrorReport ($vote, _("Fehler beim Einlesen des Votes"));
      }

      $haveFullPerm = $perm->have_studip_perm ("tutor", $vote->getRangeID()) ||
	  $userID == $vote->getAuthorID();

      /* Get post and get-variables ---------------------------------------- */
      $formID = $_REQUEST["voteformID"];
      $openID = $_REQUEST["voteopenID"];
      $open = (($openID == $voteID) || $_GET["openAllVotes"]) && (!$_GET["closeVotes"]);

      $voted = isset( $_POST["voteButton_x"] );
      $changeAnswer = isset( $_POST["changeAnswerButton_x"] );
      $answerChanged = $_POST["answerChanged"];
      $previewResults = isset( $_POST["previewButton_x"] );
      if ( !$previewResults ) $previewResults = $_GET["previewResults"];
      $previewResults = $previewResults &&
	  ($vote->getResultvisibility() == VOTE_RESULTS_ALWAYS || $haveFullPerm);
      /* ------------------------------------------------------------------- */

      /* Show headlines ---------------------------------------------------- */
      echo createBoxLineHeader ();
      echo createVoteHeadline ( $vote, $open, $openID, NULL, $isHomepage );

      if ( $open ) {
	 object_set_visit($voteID, "vote"); //set a visittime for this vote
     
	 echo createBoxContentHeader ();
	 echo createFormHeader ($vote);

	 if ($_GET["voteaction"]=="saved" && $voteID == $_GET["voteID"])
	    echo createReportMessage (_("Die &Auml;nderungen wurden gespeichert"),
				      VOTE_ICON_SUCCESS, VOTE_COLOR_SUCCESS).
		"<br>\n";
					 
	 /* User has already used the vote --------------------------------- */
	 if ( $voteDB->isAssociated ($voteID, $userID) &&
	      (! $changeAnswer) && (! $answerChanged) ) {
	    echo createSuccessReport ($vote, NO);
	 }
	  
	 /* User clicked 'preview' ---------------------------------------- */
	 elseif ($previewResults) {
	    echo createVoteResult($vote, $previewResults);
	 }
	  
	 /* User has just voted ------------------------------------------- */
	 elseif (($voted && $formID == $voteID && !$changeAnswer) ||
		 ($voted && $formID == $voteID && $answerChanged)
		 ) {
	    $vote->executeAssociate ($userID, $_POST["answer"]);
	    if ($vote->isError ()) {
	       echo createErrorReport ($vote, _("Fehler bei der Abstimmung"));
	       echo createVoteForm ($vote, $userID);
	    } else {
	       if ($answerChanged)
		  echo createSuccessReport ($vote, NO, YES);
	       else
		  echo createSuccessReport ($vote);
	    }
	 }
	 /* --------------------------------------------------------------- */

	 /* User has not yet used the vote or wants to change his answer -- */
	 else {
	    echo createVoteForm ($vote, $userID);
	 }
	 /* --------------------------------------------------------------- */
	 echo createFormFooter ($vote, $userID, $perm, $rangeID);
	 echo createBoxContentFooter ();
	 $vote->finalize ();

      }
      /* ------------------------------------------------------------------- */
      echo createBoxLineFooter ();
   }
   /* ---------------------------------------------------------------------- */

   

   /* Show all stopped Votes ----------------------------------------------- */
   if (!empty ($stoppedVotes) || (!empty ($stoppedEvals) && $haveFullPerm)) {

      $openStoppedVotes = $_GET["openStoppedVotes"];
      if (!isset($openStoppedVotes))
	 $openStoppedVotes = NO;

      echo createBoxLineHeader ();            
      echo createStoppedVotesHeadline ($stoppedVotes, $openStoppedVotes, $stoppedEvals);

      if( $openStoppedVotes ) {

	foreach ($stoppedEvals as $evalID) {
            $eval = new Evaluation ($evalID, NULL, EVAL_LOAD_NO_CHILDREN);
            echo createBoxContentHeader ();
            echo createStoppedVoteHeader ($eval, $evalDB);
            echo createFormHeader ($eval);
            $table = new HTML ("table");
            $table->addAttr("class", "inday");
            $table->addAttr("width", "100%");
            $table->addAttr("border", "0");
            $tr = new HTML ("tr");
            $td = new HTML ("td");
	    $td->addAttr ("style", "font-size:0.8em;");
	    $td->addHTMLContent(formatReady($eval->getText ()));
            $tr->addContent ($td);
            $table->addContent ($tr);
            $table->addContent (EvalShow::createEvalMetaInfo ($eval, $hasVoted));
            $tr = new HTML ("tr");
            $td = new HTML ("td");
            $td->addAttr ("align", "center");
            $td->addContent (EvalShow::createOverviewButton ($rangeID2, $evalID));
            $td->addContent (EvalShow::createContinueButton ($eval));
            $td->addContent (EvalShow::createDeleteButton ($eval));
            $td->addContent (EvalShow::createExportButton ($eval));
            $tr->addContent ($td);
            $table->addContent ($tr);
            echo $table->createContent ();

            echo createStoppedVoteFooter ();
            echo createBoxContentFooter ();
         }

	 foreach ($stoppedVotes as $tmpVote) {
	    $voteID = $tmpVote["voteID"];

	    if ($tmpVote["type"] == INSTANCEOF_TEST)
	       $vote = &new TestVote ($voteID);
	    else
	       $vote = &new Vote ($voteID);
	       
	    echo createBoxContentHeader ();
	    echo createStoppedVoteHeader ($vote);
	    echo createFormHeader ($vote);
	    echo createVoteResult ($vote);
	    echo createFormFooter ($vote, $userID, $perm, $rangeID);
	    echo createStoppedVoteFooter ();
	    echo createBoxContentFooter ();
	 }
      }
      echo createBoxLineFooter ();
   }
   /* ---------------------------------------------------------------------- */
   
   /* Show text if no vote is available ------------------------------------ */
     if (empty ($activeVotes) AND empty ($stoppedVotes) AND
       empty ($activeEvals) AND (empty ($stoppedEvals) && $haveFullPerm)
      ) {
      echo VOTE_MESSAGE_EMPTY;
   }
   /* ---------------------------------------------------------------------- */

     if ((count ($activeVotes) +
	  count ($stoppedVotes)) > 1)
       echo createOpeningOrClosingArrow ();
     
   echo createBoxFooter ();
   $voteDB->finalize ();

#   echo "<pre>$debug</pre>";

}

# ===================================================== end: public functions #

?>