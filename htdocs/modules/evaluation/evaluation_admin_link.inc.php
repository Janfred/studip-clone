<?php
/**
 * Overview of all existing evaluations
 *
 * @author  Dennis Reil <Dennis.Reil@offis.de>
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
require_once($ABSOLUTE_PATH_STUDIP."modules/evaluation/evaluation.config.php");
require_once (EVAL_LIB_COMMON);
require_once ("evaluation_admin_link.lib.php");
require_once (EVAL_FILE_EVAL);
require_once (EVAL_FILE_EVALDB);
require_once("modules/evaluation/classes/EvaluationTree.class.php");
require_once("modules/evaluation/classes/EvaluationLink.class.php");
# ====================================================== end: including files #

define ("DISCARD_OPENID", "discard_openid");

/* Create objects ---------------------------------------------------------- */
$db  = new EvaluationObjectDB();
if ($db->isError)
  return EvalCommon::showErrorReport ($db, _("Datenbankfehler"));
$lib = new EvalLinkOverview ($db, $perm, $user);
/* ------------------------------------------------------------ end: objects */

/* Set variables ----------------------------------------------------------- */
if( $sess->is_registered("evalID") )   $sess->unregister("evalID");
if( $sess->is_registered("rangeID") )  $sess->unregister("rangeID");

if (!empty($the_range))
     $rangeID = $the_range;

$rangeID = ($rangeID) ? $rangeID : $SessSemName[1];

if (empty ($rangeID) || ($rangeID == get_username ($user->id)))
     $rangeID = $user->id;

$debug = 0;

$evalAction = $lib->getPageCommand();
 
$action = $_REQUEST["action"];

$openID = $_REQUEST["openID"];
$evalID = $_REQUEST["evalID"];
$search = $_REQUEST["search"]; // range
$templates_search = $_REQUEST["templates_search"];
$search = $templates_search;
/* ---------------------------------------------------------- end: variables */
$db2 = new EvaluationDB();
$eval = new Evaluation($evalID);
$eval->loadChildren = 10;
$db2->load($eval);

$rangeID = $GLOBALS["SessSemName"][1];

$eval_check = $_REQUEST["eval_check"];

$studipdb = new DB_Seminar();

if ($action == "link"){
	// Benutzer hat übernehmen geklickt
	
	// echo("Verlinkung wurde angefordert<p>");	
	$args["evalID"] = $evalID;
	$args["load_mode"] = EVAL_LOAD_FIRST_CHILDREN;
	$evaltree = new EvaluationTree($args);
	
	
	foreach ($eval_check as $evalcheckid) {
		// echo("$evalcheckid <br>");		
		$studipdb->query(sprintf("delete from eval_link where eval_id='%s'",$evalID));
		$studipdb->query(sprintf("insert into eval_link (eval_id, linked_eval_id) values ('%s','%s')",$evalID,$evalcheckid));
	}
	// $evaltree->eval->save();
	
}

/* Javascript function ----------------------------------------------------- */
/* Blue title -------------------------------------------------------------- */
$title = EvalCommon::createTitle ("Verknüpfung zur Evaluation: " . $eval->getTitle(), EVAL_PIC_ICON);
echo $title->createContent ();
/* -------------------------------------------------------------- end: title */

/* Maintable with white border --------------------------------------------- */
$table = $lib->createMainTable ();
/* -----------------------------------------------------------end: maintable */


/* Own templates ----------------------------------------------------------- */
$evalIDArray = $db->getEvaluationIDs ($rangeID,EVAL_STATE_ACTIVE);
// echo("RangeID: $rangeID <br>");

$templateTable = new HTML ("table");
$templateTable->addAttr ("border","0");
$templateTable->addAttr ("align", "center");
$templateTable->addAttr ("cellspacing", "0");
$templateTable->addAttr ("cellpadding", "2");
$templateTable->addAttr ("width", "100%");
$templateTr = new HTML ("tr");
$templateTd = new HTML ("td");
$templateTd->addAttr ("colspan", "7");



$b = new HTML ("b");

if (!empty ($evalIDArray)) {
   $templateTable->addContent ($lib->createGroupTitle (array (
                  " ",
                  _("Titel"),
                  _("Verknüpfen"),
                  " ",
                  " ",
                  " ",
                  _("Bearbeiten"),
                  _("Löschen")), YES, "user_template" ));
   /*
   echo("Childs: " . $eval->getNumberChildren() . "<br>");
   $children = $eval->getChildren();
   $childrenids = array();
   foreach ($children as $child){
   	if ($child->getChildType() == "EvaluationLink"){
   		// echo("ChildID: " . $child->getObjectID() . "<br>");
   		$childrenids[] = $child->getObjectID();
   	}
   }
   */
   
   $studipdb->query(sprintf("select * from eval_link where eval_id='%s'",$evalID));
   if ($studipdb->next_record()){
   	 // ergebnis vorhanden
   	 $linkedevalid = $studipdb->f("linked_eval_id");
   }
   
   ?>
   <form action="admin_evaluation.php" method="POST">
   <input type="hidden" name="evalID" value="<?= $evalID ?>" />
   <input type="hidden" name="page" value="link" />
   <input type="hidden" name="action" value="link" />
   <?
   
   foreach ($evalIDArray as $number => $evalID) {
      $childeval = new Evaluation ($evalID);
      $open = ($openID == $evalID);
      // echo("Ergebnis: $evalID - " . array_search($evalID,$childrenids) . "<br>");
      
      if ($evalID != $eval->getObjectId()){
	      //if (array_search($evalID,$childrenids)){
	      if ($childeval->getObjectId() == $linkedevalid){
	      	?> 
	      	<input type="radio" name="eval_check[]" value="<?= $evalID ?>" checked /> <?= $childeval->getTitle()?><br>
	      	<?
	      }
	      else {
	      	?> 
	      	<input type="radio" name="eval_check[]" value="<?= $evalID ?>" /> <?= $childeval->getTitle()?><br>
	      	<?
	      }
      }
     	
	}
    ?>
    <input type="radio" name="eval_check[]" value="keine" />keine Verknüpfung<p>
    <input type="submit" value="Übernehmen" />
    <a href="/admin_evaluation.php?view=eval_sem">zur Evaluationsveraltung</a>
    </form>
    <?
} else {
	  $tr = new HTML ("tr");
      $td = new HTML ("td");
      $td->addAttr ("colspan", "10");
      $td->addContent ($lib->createInfoCol (_("Keine eigenen Evaluationsvorlagen vorhanden.")));
      $tr->addContent($td);
      $templateTable->addContent ($tr);
}

/* ------------------------------------------------------ end: own templates */



/* Create header with logo and safeguard messages -------------------------- */
if ( is_array($safeguard) ){
   if ($safeguard["option"] == DISCARD_OPENID)
      $openID = NULL;
   $safeguard = $safeguard["msg"];
}
/*
if( empty($openID) ) {
    $table->addContent ($lib->createHeader ($safeguard, $templateTable, $foundTable));
} else {
    $table->addContent ($lib->createHeader (" ", $templateTable, $foundTable));
}
/* ------------------------------------------------------------- end: header */

// $table->addContent($templateTable);
// $table->addContent ($lib->createClosingRow());

$tr = new HTML ("tr");
$td = new HTML ("td");
// $td->addAttr ("class", "steel1");
$td->addContent (new HTMLempty ("br"));
$tr->addContent($td);
$table->addContent($tr);


/* ---------------------------------------------------------- end: templates */


/* Create line with informations ------------------------------------------- */
$tr = new HTML ("tr");
$td = new HTML ("td");
$td->addAttr ("class", "blank");
$td->addContent (new HTMLempty ("br"));
$line = new HTMLempty ("hr");
$line->addAttr ("size", "1");
$line->addAttr ("noshade", "noshade");
#$td->addContent ($line);
$td->addContent (new HTMLempty ("br"));

echo $table->createContent ();


if ($debug) {
    echo "<pre>";
    echo "rangeid = $rangeID\n";
    echo "<font color=red>Nach Evaluationen suchen...</font><br>";
    $evalArray = $db->getEvaluationIDs ($rangeID);
    echo "ed(n) ".count($evalArray)." Evaluation(en) gefunden...</font><br>";
    $evalArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_NEW);
    echo "Es wurde(n) ".count($evalArray)." neue Evaluation(en) gefunden...</font><br>";
    $evalArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_ACTIVE);
    echo "Es wurde(n) ".count($evalArray)." laufende Evaluation(en) gefunden...</font><br>";
    $evalArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_STOPPED);
    echo "Es wurde(n) ".count($evalArray)." gestoppte Evaluation(en) gefunden...</font><br>";

    echo EvalCommon::createErrorReport($db);

    print_r($_POST);
}

# PHP-LIB: close session ==================================================== #
require_once ($ABSOLUTE_PATH_STUDIP . "html_end.inc.php");
//page_close ();
# ============================================================== end: PHP-LIB #



?>
