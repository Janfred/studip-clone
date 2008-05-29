<?php
# Lifter002: TODO
/**
 * Beschreibung
 *
 * @author      Alexander Willner <mail@AlexanderWillner.de>
 * @version     $Id$
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 * @modulegroup evaluation_modules
 *
 */

// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2004 Stud.IP
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+

# Include all required files ================================================ #
require_once("lib/evaluation/evaluation.config.php");
require_once(EVAL_FILE_OBJECTDB);
# ====================================================== end: including files #

# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALANSWERDB Instance of an evaluationanswerDB object
 * @access public
 */
define ("INSTANCEOF_EVALANSWERDB", "EvalANSWERDB");
# =========================================================================== #


class EvaluationAnswerDB extends EvaluationObjectDB {


# Define all required variables ============================================= #

# ============================================================ end: variables #

# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function EvaluationAnswerDB () {

    /* Set default values ------------------------------------------------ */
    parent::EvaluationObjectDB ();
    $this->instanceof = INSTANCEOF_EVALANSWERDB;
    /* ------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
  /**
   * Loads answers of a group from the DB
   * @access  public
   * @param   EvaluationAnswer   &&$answerObject   The answer object
   */
  function load (&$answerObject) {
    /* load answer --------------------------------------------------------- */
    $query =
      "SELECT".
      " * ".
      "FROM".
      " evalanswer ".
      "WHERE".
      " evalanswer_id = '".$answerObject->getObjectID ()."'".
      "ORDER BY".
      " position ";
    $this->db->query ($query);

    if ($this->db->next_record () == 0)
      return $this->throwError (2,
            _("Keine Antwort mit dieser ID gefunden."));
    if ($this->db->Errno)
      return $this->throwError (3,
            _("Fehler beim Laden.") .' '. _("Fehlermeldung:"). ' '.
            $this->db->Error);

    $answerObject->setObjectID ($this->db->f("evalanswer_id"));
    $answerObject->setParentID ($this->db->f("parent_id"));
    $answerObject->setPosition ($this->db->f("position"));
    $answerObject->setText     ($this->db->f("text"));
    $answerObject->setValue    ($this->db->f("value"));
    $answerObject->setRows     ($this->db->f("rows"));
    $answerObject->setResidual ($this->db->f("residual"));
    /* --------------------------------------------------------------------- */

  } //loaded


  /**
   * Loads the votes from the users for this answer
   * @access   public
   * @param    EvaluationAnswer   &$answerObject   The answer object
   */
   function loadVotes (&$answerObject) {
      /* load users -------------------------------------------------------- */
      $sql =
         "SELECT".
         " user_id ".
         "FROM".
         " evalanswer_user ".
         "WHERE".
         " evalanswer_id = '".$answerObject->getObjectID ()."'";
      $this->db->query ($sql);

      while ($this->db->next_record ()) {
         $answerObject->addUserID ($this->db->f ("user_id"), NO);
      }
   }
   /* ----------------------------------------------------------- end: users */

  /**
   * Writes answers into the DB
   * @access    public
   * @param     EvaluationAnswer   &$answerObject       The answerobject
   * @throws    error
   */
  function save (&$answerObject) {
    if (EVAL_DEBUGLEVEL >= 1)
      echo "DB: Speichere Antwortobjekt<br>\n";
    /* save answers -------------------------------------------------------- */
    if ($this->exists ($answerObject->getObjectID ())) {
      $sql =
   "UPDATE".
   " evalanswer ".
   "SET".
   " parent_id       = '".$answerObject->getParentID()."',".
   " position        = '".$answerObject->getPosition()."',".
   " text            = '".$answerObject->getText(YES)."',".
   " value           = '".$answerObject->getValue()."',".
   " rows            = '".$answerObject->getRows()."', ".
   " residual        = '".$answerObject->isResidual()."' ".
   "WHERE".
   " evalanswer_id   = '".$answerObject->getObjectID()."'";
    } else {
      $sql =
   "INSERT INTO".
   " evalanswer ".
   "SET".
   " evalanswer_id   = '".$answerObject->getObjectID()."',".
   " parent_id       = '".$answerObject->getParentID()."',".
   " position        = '".$answerObject->getPosition()."',".
   " text            = '".$answerObject->getText(YES)."',".
   " value           = '".$answerObject->getValue()."',".
   " rows            = '".$answerObject->getRows()."',".
   " residual        = '".$answerObject->isResidual()."' ";
#   " counter         = '".$answerObject->getCounter()."'";
    }
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1, _("Fehler beim Speichern.") .' '. _("Fehlermeldung:"). ' '.
            $this->db->Error);
    /* ----------------------------------------------------- end: answersave */

    /* connect answer to users --------------------------------------------- */
    while ($userID = $answerObject->getNextUserID ()) {
      $sql =
          "INSERT INTO".
          " evalanswer_user ".
          "SET".
          " evalanswer_id  = '".$answerObject->getObjectID ()."',".
          " user_id = '".$userID."'";
       $this->db->query ($sql);
       if ($this->db->Errno)
          return $this->throwError (1,
          _("Fehler beim Verknüpfen mit Benutzern.") . ' '. _("Fehlermeldung:"). ' '.
          $this->db->Error);
    }
    /* ----------------------------------------------------- end: connecting */

    /* connect user with evaluation ---------------------------------------- */
    # Disabled this because of performance problems. Do it with
    # $eval->connectWithUser ($evalID, $userID)
    #$answerID = $answerObject->getObjectID ();
    #$userID = $answerObject->getCurrentUser ();
    #if (!empty ($userID)) {
    #  $evalID = EvaluationObjectDB::getEvalID ($answerID);
    #  EvaluationDB::connectWithUser ($evalID, $userID);
    #}
    /* ----------------------------------------------------- end: connecting */

  } // saved

  /**
   * Deletes all votes from the users for this answers
   * @access   public
   * @param    EvaluationAnswer   &$answerObject   The answer object
   */
  function resetVotes (&$answerObject) {
   /* delete userconnects ------------------------------------------------- */
    $sql =
      "DELETE FROM".
      " evalanswer_user ".
      "WHERE".
      " evalanswer_id = '".$answerObject->getObjectID ()."'";
    $this->db->query ($sql);

    if ($this->db->Errno)
      return $this->throwError (2,
       _("Fehler beim entfernen der Verknüfungen.") .' '. _("Fehlermeldung:"). ' ' .
       $this->db->Error);
    /* ------------------------------------------------------- end: deleting */
  }

  /**
   * Deletes a answer
   * @access public
   * @param  EvaluationAnswer   &$answerObject   The answer to delete
   * @throws  error
   */
  function delete (&$answerObject) {
    /* delete answer ----------------------------------------------------- */
    $sql =
      "DELETE FROM".
      " evalanswer ".
      "WHERE".
      " evalanswer_id = '".$answerObject->getObjectID ()."'";
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1, _("Fehler beim Löschen.") . ' ' . _("Fehlermeldung:"). ' '.
            $this->db->Error);
    /* ------------------------------------------------------- end: deleting */

    $this->resetVotes ($answerObject);
  } // deleted


  /**
   * Checks if answer with this ID exists
   * @access  public
   * @param   string   $answerID   The answerID
   * @return  bool     YES if exists
   */
  function exists ($answerID) {
    $sql =
      "SELECT".
      " 1 ".
      "FROM".
      " evalanswer ".
      "WHERE".
      " evalanswer_id = '".$answerID."'";
    $this->db->query ($sql);

    return $this->db->next_record () ? YES : NO;
  }


  /**
   * Adds the children to a parent object
   * @access  public
   * @param   EvaluationObject  &$parentObject  The parent object
   */
  function addChildren (&$parentObject) {
    $sql =
      "SELECT".
      " evalanswer_id ".
      "FROM".
      " evalanswer ".
      "WHERE".
      " parent_id = '".$parentObject->getObjectID ()."' ".
      "ORDER BY".
      " position";
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1,
            _("Fehler beim Laden.") .' ' . _("Fehlermeldung:").' '.
            $this->db->Error);

    $loadChildren = $parentObject->loadChildren == EVAL_LOAD_ALL_CHILDREN
         ? EVAL_LOAD_ALL_CHILDREN
         : EVAL_LOAD_NO_CHILDREN;

    while ($this->db->next_record ()) {
      $parentObject->addChild (new EvaluationAnswer
                ($this->db->f ("evalanswer_id"),
                $parentObject, $loadChildren));
    }

    return;
  }

  /**
   * Returns the type of an objectID
   * @access public
   * @param  string  $objectID  The objectID
   * @return string  INSTANCEOF_x, else NO
   */
  function getType ($objectID) {
    if ($this->exists ($objectID)) {
      return INSTANCEOF_EVALANSWER;
    } else {
      return NO;
    }
  }

  /**
   * Returns the id from the parent object
   * @access public
   * @param  string  $objectID  The object id
   * @return string  The id from the parent object
   */
  function getParentID ($objectID) {
    if (empty ($this->db))
      EvaluationObjectDB::EvaluationObjectDB ();

    $sql =
      "SELECT".
      " parent_id ".
      "FROM".
      " evalanswer ".
      "WHERE".
      " evalanswer_id = '".$objectID."'";
    $this->db->query ($sql);
    $this->db->next_record ();

    return $this->db->f ("parent_id");
  }

   /**
    * Give all textanswers for a user and question for the export
    * @access  public
    * @param   string   $questionID   The question id
    * @param   string   $userID       The user id
    */
   function getUserAnwerIDs ($questionID, $userID) {
      $result = array ();

      /* ask database ------------------------------------------------------- */
      $sql =
            "SELECT".
            " a.evalanswer_id as ttt ".
            "FROM".
            " evalanswer a, evalanswer_user b ".
            "WHERE".
            " a.parent_id = '".$questionID."'".
            " AND".
            " a.evalanswer_id = b.evalanswer_id";
      if (!empty ($userID)) {
         $sql .=
            " AND".
            " b.user_id = '".$userID."'";

      }

      $this->db->query ($sql);
      if ($this->db->Errno)
         return $this->throwError (1,
         "AnswerDB::getUserAnswer - ". _("Fehlermeldung:"). ' '.
         $this->db->Error);
      /* -------------------------------------------------------- end: asking */

      /* Fill up the array with the result ---------------------------------- */
       while ($this->db->next_record ()) {
         array_push ($result, $this->db->f ("ttt"));
       }
      /* ------------------------------------------------------- end: filling */

       return $result;
  }

  /**
   * Checks whether a user has voted for an answer
   * @access   public
   * @param    string   $answerID   The answer id
   * @param    string   $userID     The user id
   * @return   boolean  YES if user has voted for the answer
   */
  function hasVoted ($answerID, $userID) {
   $sql =
      "SELECT".
      " 1 ".
      "FROM".
      " evalanswer_user ".
      "WHERE".
      " evalanswer_id = '".$answerID."'".
      " AND".
      " user_id = '".$userID."'";
    $this->db->query ($sql);

    return $this->db->next_record () ? YES : NO;
  }
  
   function getAllAnswers ($question_id, $userID, $only_user_answered = false) {
   $sql =
      "SELECT".
      " evalanswer.*, COUNT(IF(user_id='$userID',1,NULL)) AS has_voted ".
      "FROM".
      " evalanswer LEFT JOIN " .
	  " evalanswer_user USING(evalanswer_id) ".
      "WHERE".
      " parent_id = '".$question_id."'".
      ($only_user_answered ?  " AND user_id = '".$userID."' " : "") .
	  " GROUP BY evalanswer.evalanswer_id ORDER BY position";
    $this->db->query ($sql);
	$ret = array();
	while($this->db->next_record ()) $ret[] = $this->db->Record;
    return $ret;
  }
}
?>
