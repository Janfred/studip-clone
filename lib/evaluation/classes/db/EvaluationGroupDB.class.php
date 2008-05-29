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
require_once(EVAL_FILE_ANSWERDB);
# ====================================================== end: including files #


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALGROUPDB Instance of an evaluationGroupDB object
 * @access public
 */
define ("INSTANCEOF_EVALGROUPDB", "EvalGroupDB");
# =========================================================================== #


/**
 * Databaseclass for all evaluationgroups
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 * @version $Id: EvaluationGroupDB.class.php,v 1.14 2004/01/02 02:56:17 awillner
Exp $ *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvaluationGroupDB extends EvaluationObjectDB {

# Define all required variables ============================================= #

# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function EvaluationGroupDB () {
    /* Set default values -------------------------------------------------- */
    parent::EvaluationObjectDB ();
    $this->instanceof = INSTANCEOF_EVALGROUPDB;
    /* --------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
  /**
   * Loads an evaluationgroup from DB into an object
   *
   * @access private
   * @param  object  EvaluationGroup &$groupObject  The group to load
   * @throws error
   */
  function load (&$groupObject) {
    /* load group ---------------------------------------------------------- */
    $query =
      "SELECT".
      " * ".
      "FROM".
      " evalgroup ".
      "WHERE".
      " evalgroup_id = '".$groupObject->getObjectID ()."'".
      "ORDER BY".
      " position ";
    $this->db->query ($query);

    if ($this->db->next_record () == 0)
      return $this->throwError (1,
            _("Keine Gruppe mit dieser ID gefunden."));
    if ($this->db->Errno)
      return $this->throwError (2,
            _("Fehler beim Laden. Fehlermeldung: ").
            $this->db->Error);

    $groupObject->setParentID   ($this->db->f ("parent_id"));
    $groupObject->setTitle      ($this->db->f ("title"));
    $groupObject->setText       ($this->db->f ("text"));
    $groupObject->setPosition   ($this->db->f ("position"));
    $groupObject->setChildType  ($this->db->f ("child_type"));
    $groupObject->setMandatory  ($this->db->f ("mandatory"));
    $groupObject->setTemplateID ($this->db->f ("template_id"));

    /* ----------------------------------------------------------- end: load */


    /* load children ------------------------------------------------------- */
    if ($groupObject->loadChildren != EVAL_LOAD_NO_CHILDREN) {
       if ($groupObject->loadChildren == EVAL_LOAD_ONLY_EVALGROUP) {
          EvaluationGroupDB::addChildren ($groupObject);
       } else {
          EvaluationGroupDB::addChildren ($groupObject);
          EvaluationQuestionDB::addChildren ($groupObject);
       }
    }
    /* ------------------------------------------------------ end: questions */

    return;
  }


  /**
   * Saves a group
   * @access public
   * @param  object   EvaluationGroup  &$groupObject  The group to save
   * @throws  error
   */
  function save (&$groupObject) {
    if (EVAL_DEBUGLEVEL >= 1)
      echo "DB: Speichere Gruppenobjekt<br>\n";
    /* save group ---------------------------------------------------------- */
    if ($this->exists ($groupObject->getObjectID ())) {
         $sql =
   "UPDATE".
   " evalgroup ".
   "SET".
   " title        = '".$groupObject->getTitle (YES)."',".
   " text         = '".$groupObject->getText (YES)."',".
   " child_type   = '".$groupObject->getChildType ()."',".
   " position     = '".$groupObject->getPosition ()."',".
   " template_id  = '".$groupObject->getTemplateID()."',".
   " mandatory    = '".$groupObject->isMandatory ()."' ".
   "WHERE".
   " evalgroup_id = '".$groupObject->getObjectID ()."'";
    } else {
      $sql =
   "INSERT INTO".
   " evalgroup ".
   "SET".
   " evalgroup_id  = '".$groupObject->getObjectID ()."',".
   " parent_id     = '".$groupObject->getParentID ()."',".
   " title         = '".$groupObject->getTitle (YES)."',".
   " text          = '".$groupObject->getText (YES)."',".
   " child_type    = '".$groupObject->getChildType ()."',".
   " mandatory     = '".$groupObject->isMandatory ()."',".
   " template_id  = '".$groupObject->getTemplateID()."',".
   " position      = '".$groupObject->getPosition ()."'";
    }
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1, _("Fehler beim Speichern. Fehlermeldung: ").
            $this->db->Error);
    /* ------------------------------------------------------ end: groupsave */
  } // saved


 /**
   * Deletes a group
   * @access public
   * @param  object   EvaluationGroup  &$groupObject  The group to delete
   * @throws  error
   */
  function delete (&$groupObject) {
    /* delete group -------------------------------------------------------- */
    $sql =
      "DELETE FROM".
      " evalgroup ".
      "WHERE".
      " evalgroup_id = '".$groupObject->getObjectID ()."'";
    $this->db->query ($sql);
    if ($this->db->Errno)
      return $this->throwError (1, _("Fehler beim Löschen. Fehlermeldung: ").
            $this->db->Error);
    /* ------------------------------------------------------- end: deleting */
  } // deleted

  /**
   * Checks if group with this ID exists
   * @access  public
   * @param   string   $groupID   The groupID
   * @return  bool     YES if exists
   */
  function exists ($groupID) {
    $sql =
      "SELECT".
      " 1 ".
      "FROM".
      " evalgroup ".
      "WHERE".
      " evalgroup_id = '".$groupID."'";
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
         " evalgroup_id ".
         "FROM".
         " evalgroup ".
         "WHERE".
         " parent_id = '".$parentObject->getObjectID ()."' ".
         "ORDER BY".
         " position";
      $this->db->query ($sql);
      if ($this->db->Errno)
         return $this->throwError (1,
            _("Fehler beim Laden. Fehlermeldung: ").
            $this->db->Error);

      if (($loadChildren = $parentObject->loadChildren) == EVAL_LOAD_NO_CHILDREN)
         $loadChildren = EVAL_LOAD_NO_CHILDREN;

      while ($this->db->next_record ()) {
         $groupID = $this->db->f ("evalgroup_id");
         $parentObject->addChild (
            new EvaluationGroup ($groupID, $parentObject, $loadChildren)
                                 );
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
      return INSTANCEOF_EVALGROUP;
    } else {
      $dbObject = new EvaluationQuestionDB ();
      return $dbObject->getType ($objectID);
    }
  }


   /**
    * Returns whether the childs are groups or questions
    * @access   public
    * @param    string   $objectID   The object id
    */
    function getChildType ($objectID) {
        /* Make this function static ---------------------------------------- */
        if (empty ($this->db)) {
            $this->db = parent::getDBObject ();
        }
        /* ----------------------------------------------------- end : static */

        $sql =
            "SELECT".
            " child_type ".
            "FROM".
            " evalgroup ".
            "WHERE".
            " evalgroup_id = '".$objectID."' ";
        $this->db->query ($sql);
        if ($this->db->next_record ()) {
                return $this->db->f ("child_type");
        }

        return NULL;

#        /* Look for question childs ---------------------------------------- */
#        $sql =
#            "SELECT".
#            " 1 ".
#            "FROM".
#            " evalquestion ".
#            "WHERE".
#            " evalgroup_id = '".$objectID."' ";
#        $this->db->query ($sql);
#        if ($this->db->next_record ()) {
#                return INSTANCEOF_EVALQUESTION;
#        }
#        /* --------------------------------------------------- end: children */
#
#        /* Look for group childs ------------------------------------------- */
#        $sql =
#            "SELECT".
#            " 1 ".
#            "FROM".
#            " evalgroup ".
#            "WHERE".
#            " parent_id = '".$objectID."' ";
#        $this->db->query ($sql);
#        if ($this->db->next_record ()) {
#                return INSTANCEOF_EVALGROUP;
#        }
#        /* --------------------------------------------------- end: children */
#
#        return EVALGROUP_TYPE_UNDEF;
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
      " evalgroup ".
      "WHERE".
      " evalgroup_id = '".$objectID."'";
    $this->db->query ($sql);
    $this->db->next_record ();

    return $this->db->f ("parent_id");
  }
# ===================================================== end: public functions #


# Define private functions ================================================== #

# ==================================================== end: private functions #

}

?>
