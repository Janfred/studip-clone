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
require_once(EVAL_FILE_ANSWERDB);
# ====================================================== end: including files #

# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALQUESTIONDB Instance of an evaluationQuestionDB object
 * @access public
 */
define ("INSTANCEOF_EVALQUESTIONDB", "EvalQuestionDB");
# =========================================================================== #


class EvaluationQuestionDB extends EvaluationObjectDB {

# Define all required variables ============================================= #

# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function EvaluationQuestionDB () {

    /* Set default values ------------------------------------------------ */
    parent::EvaluationObjectDB ();
    $this->instanceof = INSTANCEOF_EVALGROUPDB;
    /* ------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #

# Define public functions =================================================== #
  /**
   * Loads a question from the DB
   * @access  public
   * @param   EvaluationQuestion   &$questionObject   The question object
   */
  function load (&$questionObject) {
    /* load question ------------------------------------------------------- */
    $query =
      "SELECT".
      " * ".
      "FROM".
      " evalquestion ".
      "WHERE".
      " evalquestion_id = '".$questionObject->getObjectID ()."'".
      "ORDER BY".
      " position ";
    $this->db->query ($query);

    if ($this->db->next_record () == 0)
      return $this->throwError (1,
            _("Keine Frage mit dieser ID gefunden."));
    if ($this->db->Errno)
      return $this->throwError (2,
            _("Fehler beim Laden. Fehlermeldung: ").
            $this->db->Error);

    $questionObject->setParentID       ($this->db->f("parent_id"));
    $questionObject->setType           ($this->db->f("type"));
    $questionObject->setPosition       ($this->db->f("position"));
    $questionObject->setText           ($this->db->f("text"));
    $questionObject->setMultiplechoice ($this->db->f("multiplechoice"));
    /* --------------------------------------------------------------------- */


    /* load children ------------------------------------------------------- */
    if ($questionObject->loadChildren != EVAL_LOAD_NO_CHILDREN)
       EvaluationAnswerDB::addChildren ($questionObject);
    /* ------------------------------------------------------ end: questions */
  } //loaded



  /**
   * Writes or updates a question into the DB
   * @access  public
   * @param   EvaluationQuestion   &$questionObject   The question object
   */
  function save (&$questionObject) {
    if (EVAL_DEBUGLEVEL >= 1)
      echo "DB: Speichere Fragenobjekt<br>\n";
    if ($this->exists ($questionObject->getObjectID ())) {
      $sql =
   "UPDATE".
   " evalquestion ".
   "SET".
   " parent_id       = '".$questionObject->getParentID()."',".
   " type            = '".$questionObject->getType()."',".
   " position        = '".$questionObject->getPosition()."',".
   " text            = '".$questionObject->getText(YES)."',".
   " multiplechoice  = '".$questionObject->isMultiplechoice()."' ".
   "WHERE".
   " evalquestion_id = '".$questionObject->getObjectID()."'";
    } else {
      $sql =
   "INSERT INTO".
   " evalquestion ".
   "SET".
   " evalquestion_id = '".$questionObject->getObjectID()."',".
   " parent_id       = '".$questionObject->getParentID()."',".
   " type            = '".$questionObject->getType()."',".
   " position        = '".$questionObject->getPosition()."',".
   " text            = '".$questionObject->getText(YES)."',".
   " multiplechoice  = '".$questionObject->isMultiplechoice()."'";
    }
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1, _("Fehler beim Speichern. Fehlermeldung: ").
            $this->db->Error);
  } // saved


  /**
   * Deletes a question
   * @access public
   * @param  object EvaluationQuestion &$questionObject The question to delete
   * @throws  error
   */
  function delete (&$questionObject) {
    /* delete question ----------------------------------------------------- */
    $sql =
      "DELETE FROM".
      " evalquestion ".
      "WHERE".
      " evalquestion_id = '".$questionObject->getObjectID ()."'";
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1, _("Fehler beim Löschen. Fehlermeldung: ").
            $this->db->Error);
    /* ------------------------------------------------------- end: deleting */
  } // deleted


  /**
   * Checks if question with this ID exists
   * @access  public
   * @param   string   $questionID   The questionID
   * @return  bool     YES if exists
   */
  function exists ($questionID) {
    $sql =
      "SELECT".
      " 1 ".
      "FROM".
      " evalquestion ".
      "WHERE".
      " evalquestion_id = '".$questionID."'";
    $this->db->query ($sql);

    return $this->db->next_record () ? YES : NO;
  }

/**
   * Checks if a template exists with this title
   * @access  public
   * @param   string   $questionTitle   The title of the question
   * @param   string   $userID          The user id
   * @return  bool     YES if exists
   */
  function titleExists ($questionTitle, $userID) {
    $sql =
       "SELECT".
       " 1 ".
       "FROM".
       " evalquestion ".
       "WHERE".
       " text = '".$questionTitle."'".
       " AND ".
       " parent_id = '".$userID."'";

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
      " evalquestion_id ".
      "FROM".
      " evalquestion ".
      "WHERE".
      " parent_id = '".$parentObject->getObjectID ()."' ".
      "ORDER BY".
      " position";
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1,
            _("Fehler beim Laden. Fehlermeldung: ").
            $this->db->Error);

    $loadChildren = $parentObject->loadChildren == EVAL_LOAD_ALL_CHILDREN
         ? EVAL_LOAD_ALL_CHILDREN
         : EVAL_LOAD_NO_CHILDREN;

    while ($this->db->next_record ()) {
      $parentObject->addChild (new EvaluationQuestion
                ($this->db->f ("evalquestion_id"),
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
      return INSTANCEOF_EVALQUESTION;
    } else {
      $dbObject = new EvaluationAnswerDB ();
      return $dbObject->getType ($objectID);
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
      parent::EvaluationObjectDB ();

    $sql =
      "SELECT".
      " parent_id ".
      "FROM".
      " evalquestion ".
      "WHERE".
      " evalquestion_id = '".$objectID."'";
    $this->db->query ($sql);
    $this->db->next_record ();

    return $this->db->f ("parent_id");
  }

  /**
   * Returns the ids of the Answertemplates of a user
   * @access public
   * @param  string  $userID  The user id
   * @return array  The ids of the answertemplates
   */
  function getTemplateID ($userID) {
     $array = array();

     if(EvaluationObjectDB::getGlobalPerm()=="root")
         $sql =
            "SELECT".
            " evalquestion_id ".
            "FROM".
            " evalquestion ".
            "WHERE".
            " parent_id = '0' ";
      else
         $sql =
            "SELECT".
            " evalquestion_id ".
            "FROM".
            " evalquestion ".
            "WHERE".
            " parent_id = '".$userID."' ".
            "OR ".
            " parent_id = '0' ";

      $sql .= " ORDER BY text";

      $this->db->query ($sql);
      while($this->db->next_record ())
         array_push($array, $this->db->f ("evalquestion_id"));

      return $array;
  }


}
