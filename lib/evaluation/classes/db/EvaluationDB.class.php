<?php
# Lifter002: TODO
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
require_once(EVAL_FILE_GROUPDB);
# ====================================================== end: including files #


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALDB Is instance of an evaluationDB object
 * @access public
 */
define ("INSTANCEOF_EVALDB", "EvalDB");

/**
 * @const EVAL_STATE_NEW Beschreibung
 * @access public
 */
define ("EVAL_STATE_NEW", "new");

/**
 * @const EVAL_STATE_ACTIVE Beschreibung
 * @access public
 */
define ("EVAL_STATE_ACTIVE", "active");

/**
 * @const EVAL_STATE_STOPPED Beschreibung
 * @access public
 */
define ("EVAL_STATE_STOPPED", "stopped");
# =========================================================================== #


/**
 * Databaseclass for all evaluations
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 * @version $Id$
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvaluationDB extends EvaluationObjectDB {

# Define all required variables ============================================= #

# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function EvaluationDB () {
    /* Set default values -------------------------------------------------- */
    parent::EvaluationObjectDB ();
    $this->instanceof = INSTANCEOF_EVALDBOBJECT;
    /* --------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
  /**
   * Loads an evaluation from DB into an object
   *
   * @access public
   * @param  object EvaluationObject &$evalObject  The evaluation to load
   * @throws error
   */
  function load (&$evalObject) {
    /* load evaluation basics ---------------------------------------------- */
    $sql =
      "SELECT".
      " * ".
      "FROM".
      " eval ".
      "WHERE".
      " eval_id = '".$evalObject->getObjectID ()."'";

    if ($this->db->Debug)
       $sql .= " #eval->load ()";
    $this->db->query ($sql);

    if ($this->db->next_record () == 0)
      return $this->throwError (1,
            _("Keine Evaluation mit dieser ID gefunden."));
    if ($this->db->Errno)
      return $this->throwError (2,
            _("Fehler beim Laden. Fehlermeldung: ").
            $this->db->Error);

    $evalObject->setAuthorID     ($this->db->f ("author_id"));
    $evalObject->setTitle        ($this->db->f ("title"));
    $evalObject->setText         ($this->db->f ("text"));
    $evalObject->setStartdate    ($this->db->f ("startdate"));
    $evalObject->setStopdate     ($this->db->f ("stopdate"));
    $evalObject->setTimespan     ($this->db->f ("timespan"));
    $evalObject->setCreationdate ($this->db->f ("mkdate"));
    $evalObject->setChangedate   ($this->db->f ("chdate"));
    $evalObject->setAnonymous    ($this->db->f ("anonymous"));
    $evalObject->setVisible      ($this->db->f ("visible"));
    $evalObject->setShared       ($this->db->f ("shared"));
    /* --------------------------------------------------------- end: values */


    /* load ranges --------------------------------------------------------- */
    $sql =
      "SELECT".
      " range_id ".
      "FROM".
      " eval_range ".
      "WHERE".
      " eval_id = '".$evalObject->getObjectID ()."'";

    if ($this->db->Debug)
       $sql .= " #eval->load ()";
    $this->db->query ($sql);

    while ($this->db->next_record ()) {
      $evalObject->addRangeID ($this->db->f ("range_id"));
    }
    /* --------------------------------------------------------- end: ranges */


    /* load groups --------------------------------------------------------- */
    if ($evalObject->loadChildren != EVAL_LOAD_NO_CHILDREN) {
        EvaluationGroupDB::addChildren ($evalObject);
     }
    /* ---------------------------------------------------------- end: group */

  } // loaded


  /**
   * Saves an evaluation
   * @access public
   * @param  object   Evaluation  &$evalObject  The evaluation to save
   * @throws  error
   */
  function save (&$evalObject) {     
    if (EVAL_DEBUGLEVEL >= 1)
      echo "DB: Speichere Evaluationsobjekt<br>\n";

    $startdate = $evalObject->getStartdate() == NULL
   ? "NULL"
   : $evalObject->getStartdate();

    $stopdate = $evalObject->getStopdate() == NULL
   ? "NULL"
   : $evalObject->getStopdate();

    $timespan = $evalObject->getTimespan() == NULL
   ? "NULL"
   : $evalObject->getTimespan();

    /* save evaluation ----------------------------------------------------- */
    if ($this->exists ($evalObject->getObjectID ())) {
      $sql =
   "UPDATE".
   " eval ".
   "SET".
   " title     = '".$evalObject->getTitle (YES)."',".
   " text      = '".$evalObject->getText (YES)."',".
   " startdate =  ".$startdate.",".
   " stopdate  =  ".$stopdate.",".
   " timespan  =  ".$timespan.",".
   " mkdate    = '".$evalObject->getCreationdate ()."',".
   " chdate    = '".$evalObject->getChangedate ()."',".
   " anonymous = '".$evalObject->isAnonymous ()."',".
   " visible   = '".$evalObject->isVisible ()."',".
   " shared    = '".$evalObject->isShared ()."'".
   "WHERE".
   " eval_id   = '".$evalObject->getObjectID ()."'";
    } else {
      $sql =
   "INSERT INTO".
   " eval ".
   "SET".
   " eval_id   = '".$evalObject->getObjectID ()."',".
   " author_id = '".$evalObject->getAuthorID ()."',".
   " title     = '".$evalObject->getTitle (YES)."',".
   " text      = '".$evalObject->getText (YES)."',".
   " startdate =  ".$startdate.",".
   " stopdate  =  ".$stopdate.",".
   " timespan  =  ".$timespan.",".
   " mkdate    = '".$evalObject->getCreationdate ()."',".
   " chdate    = '".$evalObject->getChangedate ()."',".
   " anonymous = '".$evalObject->isAnonymous ()."',".
   " visible   = '".$evalObject->isVisible ()."',".
   " shared    = '".$evalObject->isShared ()."'";
    }

    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1, _("Fehler beim Speichern. Fehlermeldung: ").
            $this->db->Error);
    /* ------------------------------------------------------- end: evalsave */

    /* connect to ranges --------------------------------------------------- */
      $sql =
         "DELETE FROM".
         " eval_range ".
         "WHERE".
         " eval_id  = '".$evalObject->getObjectID ()."'";
      $this->db->query ($sql);
      if ($this->db->Errno)
         return $this->throwError (1, _("Fehler beim Löschen von Bereichen. Fehlermeldung: ".$this->db->Error));

      while ($rangeID = $evalObject->getNextRangeID ()) {
         $sql =
            "INSERT INTO".
            " eval_range ".
            "SET".
            " eval_id  = '".$evalObject->getObjectID ()."',".
            " range_id = '".$rangeID."'";
         $this->db->query ($sql);
         if ($this->db->Errno)
            return $this->throwError (1, _("Fehler beim Verknüpfen mit Bereichen. Fehlermeldung: ".$this->db->Error));
      }
    /* ----------------------------------------------------- end: connecting */
  } //...saved


  /**
   * Deletes an evaluation
   * @access public
   * @param  object   Evaluation  &$evalObject  The evaluation to delete
   * @throws  error
   */
  function delete (&$evalObject) {
    /* delete evaluation --------------------------------------------------- */
    $sql =
      "DELETE FROM eval WHERE eval_id = '".$evalObject->getObjectID ()."'";
    $this->db->query ($sql);

    if ($this->db->Errno)
      $this->throwError (1, _("Fehler beim Löschen. Fehlermeldung: ").
          $this->db->Error);
    /* ------------------------------------------------------- end: deleting */

    /* delete rangeconnects ------------------------------------------------ */
    $sql =
      "DELETE FROM".
      " eval_range ".
      "WHERE".
      " eval_id = '".$evalObject->getObjectID ()."'";
    $this->db->query ($sql);

    if ($this->db->Errno)
      return $this->throwError (2, _("Fehler beim entfernen der Verknüfungen. Fehlermeldung: ").$this->db->Error);
    /* ------------------------------------------------------- end: deleting */

    /* delete userconnects ------------------------------------------------- */
    $sql =
      "DELETE FROM".
      " eval_user ".
      "WHERE".
      " eval_id = '".$evalObject->getObjectID ()."'";
    $this->db->query ($sql);

    if ($this->db->Errno)
      return $this->throwError (2, _("Fehler beim entfernen der Verknüfungen. Fehlermeldung: ").$this->db->Error);
    /* ------------------------------------------------------- end: deleting */

  } // deleted




  /**
   * Checks if evaluation with this ID exists
   * @access  public
   * @param   string   $evalID   The evalID
   * @return  bool     YES if exists
   */
  function exists ($evalID) {
    $sql =
      "SELECT".
      " 1 ".
      "FROM".
      " eval ".
      "WHERE".
      " eval_id = '".$evalID."'";
    $this->db->query ($sql);

    return $this->db->next_record () ? YES : NO;
  }

  /**
   * Checks if someone used the evaluation
   * @access  public
   * @param   string   $evalID   The eval id
   * @param   string   $userID   The user id
   * @return  bool     YES if evaluation was used
   */
  function hasVoted ($evalID, $userID = "") {
    /* ask database ------------------------------------------------------- */
    $sql =
      "SELECT".
      " 1 ".
      "FROM".
      " eval_user ".
      "WHERE".
      " eval_id = '".$evalID."'";
    if (!empty ($userID))
      $sql .= " AND user_id = '".$userID."'";

    if ($this->db->Debug)
       $sql .= " #eval->hasVoted ()";
    $this->db->query ($sql);
    /* --------------------------------------------------------- end: asking */

    return $this->db->next_record () ? YES : NO;
  }

  /**
   * Returns the type of an objectID
   * @access public
   * @param  string  $objectID  The objectID
   * @return string  INSTANCEOF_x, else NO
   */
  function getType ($objectID) {
    if ($this->exists ($objectID)) {
      return INSTANCEOF_EVAL;
    } else {
      $dbObject = new EvaluationGroupDB ();
      return $dbObject->getType ($objectID);
    }
  }
# ===================================================== end: public functions #


# Define private functions ================================================== #

# ==================================================== end: private functions #


# Define static functions =================================================== #
  /**
   * Connect a user with an evaluation
   * @access   public
   * @param    string   $evalID   The evaluation id
   * @param    string   $userID   The user id
   */
  function connectWithUser ($evalID, $userID) {
    if (!is_object ($this->db))
      $this->db = DatabaseObject::getDBObject ();

    if (empty ($userID))
      die ("EvaluationDB::connectWithUser: UserID leer!!");

    $sql =
      "INSERT IGNORE INTO".
      " eval_user ".
      "SET".
      " eval_id  = '".$evalID."',".
      " user_id = '".$userID."'";
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1, _("Fehler beim Verknüpfen mit Benutzer. Fehlermeldung: ".$this->db->Error));

  }

   /**
    * Removes the connection of an evaluation with a user or all users
    * @access   public
    * @param    string   $evalID   The evaluation id
    * @param    string   $userID   The user id
    */
   function removeUser ($evalID, $userID = "") {
      $db = DatabaseObject::getDBObject ();

      $sql =
        "DELETE FROM".
         " eval_user ".
         "WHERE".
         " eval_id  = '".$evalID."'";

      if (!empty ($userID)) {
         $sql .= " AND user_id = '".$userID."'";
      }

      $db->query ($sql);
      if ($db->Errno)
         return $this->throwError (1, _("Fehler beim Löschen von Usern. Fehlermeldung: ".$db->Error));

  }

  /**
   * Get number of users who participated in the eval
   * @access public
   * @param  string   $evalID  The eval id
   * @return integer  The number of users
   */
   function getNumberOfVotes ($evalID) {
       $db = DatabaseObject::getDBObject ();

    $sql =
      "SELECT".
      " count(DISTINCT user_id) ".
      "AS".
      " number ".
      "FROM".
      " eval_user ".
      "WHERE".
      " eval_id = '".$evalID."'";
    /* ------------------------------------------------------------------- */
    $db->query ($sql);
    $db->next_record ();
    return $db->f ("number");
  }

   /**
   * Get users who participated in the eval
   * @access public
   * @param  string   $evalID     The eval id
   * @param  array    $answerIDs  The answerIDs to get the pseudonym users
   * @return integer  The number of users
   */
   function getUserVoted ($evalID, $answerIDs = array (), $questionIDs = array ()) {
      if (!is_object ($this->db))
         $this->db = DatabaseObject::getDBObject ();

      $result = array ();

      /* ask database ------------------------------------------------------- */
      if (empty ($answerIDs) && empty ($questionIDs)) {
          $sql =
            "SELECT DISTINCT".
            " user_id ".
            "FROM".
            " eval_user ".
            "WHERE".
            " eval_id = '".$evalID."'";
       } elseif (empty ($questionIDs)) {
         $sql =
            "SELECT DISTINCT".
            " user_id ".
            "FROM".
            " evalanswer_user ".
            "WHERE".
            " evalanswer_id IN ('".join("','", $answerIDs)."')";
       } else {
	    $sql =
            "SELECT DISTINCT".
            " user_id ".
            "FROM".
            " evalanswer INNER JOIN evalanswer_user USING(evalanswer_id) ".
            "WHERE".
            " parent_id IN ('".join("','", $questionIDs)."')";
	   }
	   
      $this->db->query ($sql);
       if ($this->db->Errno)
         return $this->throwError (1, _("EvalDB::getUserVoted - Fehlermeldung: ").$this->db->Error);
       /* ------------------------------------------------ end: asking database */

       /* Fill up the array with IDs ----------------------------------------- */
       while ($this->db->next_record ()) {
         array_push ($result, $this->db->f ("user_id"));
       }
       /* ------------------------------------------------------- end: filling */

    return $result;
  }


  /**
   *
   * @access public
   * @param  string   $search_str
   * @return array
   */
   function search_range($search_str) {
      return search_range($search_str, true);
   }
# ===================================================== end: static functions #

}

?>
