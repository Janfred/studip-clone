<?php

/**
 * This file is used to insert a vote in Stud.IP.
 *
 * @author     Alexander Willner <mail@AlexanderWillner.de>,
 *             Michael Cohrs <michael@cohrs.de>
 * @version    $Id$
 * @copyright  2003 Stud.IP-Project (GNU General Public License)
 * @access     public
 * @module     vote_show
 * @package    vote
 */


# Include all required files ================================================ #

require_once ($ABSOLUTE_PATH_STUDIP."vote/view/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."vote/view/vote_show.lib.php");
require_once ($ABSOLUTE_PATH_STUDIP."vote/VoteDB.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."vote/Vote.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."vote/TestVote.class.php");

# ====================================================== end: including files #


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

   if (empty ($activeVotes) AND 
       empty ($stoppedVotes) AND
       !($perm->have_studip_perm ("tutor", $rangeID) OR
	 get_username($userID) == $rangeID)) {

      $voteDB->finalize ();
      return;
   }
   /* ---------------------------------------------------------------------- */

   echo "<a name=\"votetop\"></a>";
   $debug.="rangeid=$rangeID\nuserid=$userID\n";

   /* Show the vote box ---------------------------------------------------- */
   $width = ($isHomepage)? "100%" : "70%";

   if ($perm->have_studip_perm ("tutor", $rangeID) OR
       get_username($userID) == $rangeID)
      echo createBoxHeader (_("Votings und Tests"), $width, "",
			    VOTE_ICON_BIG, 
			    _("Votings und Tests. Umfragen und mehr..."), 
			    VOTE_FILE_ADMIN."?page=overview&rangeID=".$rangeID.
			    ($GLOBALS['SessSemName']["class"]=="sem"
			     ? "&new_sem=TRUE&view=vote_sem"
			     : "&new_inst=TRUE&view=vote_inst"),
			    VOTE_ICON_ARROW, _("Votings/Tests bearbeiten"));
   else
      echo createBoxHeader (_("Votings und Tests"), $width, "",
			    VOTE_ICON_BIG, 
			    _("Votings und Tests. Umfragen und mehr..."));

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

   /* Show all active Votes ------------------------------------------------ */
   foreach ($activeVotes as $tmpVote) {
      $voteID = $tmpVote["voteID"];
      if ($tmpVote["type"] == INSTANCEOF_TEST)
	 $vote = &new TestVote ($voteID);
      else
	 $vote = &new Vote ($voteID);
	 
      if ($vote->isError ()) {
	 echo createErrorReport ($vote, _("Fehler beim Einlesen des Votes"));
      }

      /* Get post and get-variables ---------------------------------------- */
      $formID = $_REQUEST["voteformID"];
      $openID = $_REQUEST["voteopenID"];
      $open = (($openID == $voteID) || $_GET["openAllVotes"]) && (!$_GET["closeVotes"]);

      $voted = isset( $_POST["voteButton_x"] );
      $changeAnswer = isset( $_POST["changeAnswerButton_x"] );
      $answerChanged = $_POST["answerChanged"];
      $previewResults = isset( $_POST["previewButton_x"] );
      if ( !$previewResults ) $previewResults = $_GET["previewResults"];
      /* ------------------------------------------------------------------- */

      /* Show headlines ---------------------------------------------------- */
      echo createBoxLineHeader ();
      echo createVoteHeadline ( $vote, $open, $openID );

      if ( $open ) {
	 echo createBoxContentHeader ();
	 echo createFormHeader ($vote);

	 if ($_GET["voteaction"]=="saved" && $voteID == $_GET["voteID"])
	    echo createReportMessage
	       (_("Die &Auml;nderungen wurden gespeichert"),
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
	       echo createErrorReport ($vote, 
				       _("Fehler bei der Abstimmung"));
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
   if (!empty ($stoppedVotes)) {

      $openStoppedVotes = $_GET["openStoppedVotes"];
      if (!isset($openStoppedVotes))
	 $openStoppedVotes = NO;

      echo createBoxLineHeader ();            
      echo createStoppedVotesHeadline ($stoppedVotes, $openStoppedVotes);

      if( $openStoppedVotes ) {
	 foreach ($stoppedVotes as $tmpVote) {
	    $voteID = $tmpVote["voteID"];

	    if ($tmpVote["type"] == INSTANCEOF_TEST)
	       $vote = &new TestVote ($voteID);
	    else
	       $vote = &new Vote ($voteID);
	       
	    echo createBoxContentHeader ();
	    echo createStoppedVoteHeader ($vote);
#	       echo createBoxHeader (formatReady($vote->getTitle()), "90%", "",
#				     "", "", "", "", "", "quote");
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
   if (empty ($activeVotes) AND empty ($stoppedVotes)) {
      echo VOTE_MESSAGE_EMPTY;
   }
   /* ---------------------------------------------------------------------- */

   if ((count ($activeVotes) + count ($stoppedVotes)) > 1)
      echo createOpeningOrClosingArrow ();

   echo createBoxFooter ();
   $voteDB->finalize ();

#   echo "<pre>$debug</pre>";

}

# ===================================================== end: public functions #

?>