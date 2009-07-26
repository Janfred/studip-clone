<?
# Lifter002: TODO
/**
* studygroup_admin.php
*
* create/admin gruop
*
*
* @author               Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
* @version              $Id: studygroup_admin.php 11028 2008-12-15 07:48:38Z tthelen $
* @access               public
* @package              studip_core
* @modulegroup          views
* @module               groups
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2008 Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
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


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ('lib/msg.inc.php'); //Funktionen fuer Nachrichtenmeldungen
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once ('config.inc.php');
require_once ('lib/log_events.inc.php');
require_once ('lib/classes/Seminar.class.php');

$messages=array();

if ($_REQUEST['cmd']=='edit') {
	$editmode=1;
	$CURRENT_PAGE = _("Arbeitsgruppe bearbeiten");
} else {
	$createmode=1;
	$CURRENT_PAGE = _("Arbeitsgruppe anlegen");
}

if ($createmode && $_REQUEST['group_new']) {
	//checks
	if (!$_REQUEST['groupname']) {
		$messages[]=array("error",_("Bitte Gruppennamen angeben"));
	} else {
		$db=new DB_Seminar();
		$db->query("SELECT * FROM seminare WHERE name='".$_REQUEST['groupname']."'");
		if ($db->nf()) {
			$messages[]=array("error",_("Eine Veranstaltung/Arbeitsgruppe mit diesem Namen existiert bereits. Bitte wählen Sie einen anderen Namen"));
		}
	}
	if (!$_REQUEST['grouptermsofuse_ok']) {
		$messages[]=array("error",_("Sie müssen die Nutzungsbedingungen durch Setzen des Häkchens bei 'Einverstanden' akzeptieren."));
	}
	if (count($messages)!=0) {
		$check_error=TRUE;
	}


	// do it
	if (!$check_error) { // no errors
		$sem=new Seminar();
		$sem->name=$_REQUEST['groupname'];
		$sem->description=$_REQUEST['groupdescription'];
		$sem->status=99;
		$sem->read_level=1;
		$sem->write_level=1;

		$sem->admission_type=0; 
		if ($_REQUEST['groupaccess']=='all') {
			$sem->admission_prelim=0;
		} else {
			$sem->admission_prelim=1;
			$sem->admission_prelim_txt=_("Die ModeratorInnen der Arbeitsgruppe können Ihren Aufnahmewunsch bestätigen oder ablehnen. Erst nach Bestätigung erhalten Sie vollen Zugriff auf die Gruppe.");
		}
		$sem->admission_endtime=-1;
		$sem->admission_binding=0;
		$sem->admission_starttime=-1;
		$sem->admission_endtime_sem=-1;
		$sem->visible=1;

		$semdata=new SemesterData();
		$this_semester=$semdata->getSemesterDataByDate(time());
		$sem->semester_start_time=$this_semester['beginn'];
		$sem->semester_duration_time=-1;
		$sem->institut_id=''; // TODO: default inst id!

		$sem->store();
		$semid=$sem->id;
		$userid=$GLOBALS['auth']->auth['uid'];

		// insert dozent
		$q="INSERT INTO seminar_user SET seminar_id='$semid', user_id='$userid', status='dozent'";
		$db=new DB_Seminar();
		$db->query($q);

		$mods=new Modules();
		$bitmask=0;
		if ($_REQUEST['groupmodule_forum']) {
			$mods->setBit($bitmask, $mods->registered_modules["forum"]["id"]);
		}
		if ($_REQUEST['groupmodule_files']) {
			$mods->setBit($bitmask, $mods->registered_modules["documents"]["id"]);
		}
		#if ($_REQUEST['groupmodule_members']) {
		$mods->setBit($bitmask, $mods->registered_modules["participants"]["id"]);
		#}
		if ($_REQUEST['groupmodule_wiki']) {
			$mods->setBit($bitmask, $mods->registered_modules["wiki"]["id"]);
		}
		if ($_REQUEST['groupmodule_literature']) {
			$mods->setBit($bitmask, $mods->registered_modules["literature"]["id"]);
		}
		$sem->modules=$bitmask;
		$mods->writeBin($semid, $bitmask, 'sem');

		#$messages[]=array("info","Gruppe angelegt!");	
		// work done. locate to new group.
		header("Location:seminar_main.php?auswahl=".$semid);

		exit();

	}
}

// Start of Output
# get and fill the template
$template =& $template_factory->open('studygroup_admin');
$template->set_attribute('messages', $messages);

if ($check_error) {
	$template->set_attribute('groupame',$_REQUEST['groupname']);
	$template->set_attribute('groupdescription',$_REQUEST['groupdescription']);
	$template->set_attribute('groupmodules_files',$_REQUEST['groupmodules_files']);
	$template->set_attribute('groupmodules_forum',$_REQUEST['groupmodules_forum']);
	$template->set_attribute('groupmodules_wiki',$_REQUEST['groupmodules_wiki']);
	$template->set_attribute('groupmodules_members',$_REQUEST['groupmodules_members']);
	$template->set_attribute('groupmodules_literature',$_REQUEST['groupmodules_literature']);
	$template->set_attribute('grouptermsofuse_ok',$_REQUEST['grouptermsofuse_ok']);
} else if ($createmode && !$_REQUEST['group_new']) {
	$template->set_attribute('groupame','');
	$template->set_attribute('groupdescription',_("Hier aussagekräftige Beschreibung eingeben."));
	$template->set_attribute('groupmodule_files',TRUE);
	$template->set_attribute('groupmodule_forum',TRUE);
	$template->set_attribute('groupmodule_wiki',TRUE);
	$template->set_attribute('groupmodule_members',TRUE);
	$template->set_attribute('groupmodule_literature',TRUE);
} else if ($editmode) {
	$sem=new Seminar($SessSemName[1]);
	$template->set_attribute('groupname',$sem->name);
	$template->set_attribute('groupdescription',$sem->description);
	$mods=new Modules();
	$template->set_attribute('groupmodule_forum', $mods->getStatus('forum',$sem->id,'sem'));
	$template->set_attribute('groupmodule_wiki', $mods->getStatus('wiki',$sem->id,'sem'));
	$template->set_attribute('groupmodule_members', $mods->getStatus('participants',$sem->id,'sem'));
	$template->set_attribute('groupmodule_files', $mods->getStatus('files',$sem->id,'sem'));
	$template->set_attribute('groupmodule_literature', $mods->getStatus('literature',$sem->id,'sem'));
}



# get the layout template
$layout = $GLOBALS['template_factory']->open('layouts/base');

$infobox=array();
$infobox['picture']='infoboxbild_studygroup.jpg';
$infobox['content']=array(
        array(
        'kategorie'=>_("Information"), 
        'eintrag'=>array(
            array("text"=>"Studiengruppen sind eine einfache Möglichkeit, mit Kommilitonen, Kollegen und anderen zusammenzuarbeiten. Jeder kann Studiengruppen gründen.","icon"=>"ausruf_small2.gif"))),
        array(
        'kategorie'=>_("Aktionen"), 
        'eintrag'=>array(
            array("text"=>"Neue Studiengruppe gründen", "icon"=>"icon-cont.gif"),
	    array("text"=>"Studiengruppe löschen", "icon"=>"icon-wiki.gif"))),
     );

$layout->set_attribute('current_page', $CURRENT_PAGE);
$layout->set_attribute('content_for_layout', $template->render());
$layout->set_attribute('infobox', $infobox);
if ($editmode) {
	$layout->set_attribute('tabs','links_openobject');
}
echo $layout->render();

page_close();
//<!-- $Id: show_log.php 11028 2008-12-15 07:48:38Z tthelen $ -->
