<?
# Lifter002: TODO
/**
* studygroup_teilnehmer.php
*
* member administration for studygroups
*
*
* @author               Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
* @version              $$
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

// check
checkObject();

//hack
$rechte=1;

function accept_user($username) {
	$q="SELECT asu.user_id FROM admission_seminar_user asu LEFT JOIN auth_user_md5 au ON (au.user_id=asu.user_id) WHERE au.username='$username' AND asu.seminar_id='".$GLOBALS['SessSemName'][1]."'";
	print $q;
	$db=new DB_Seminar();
	$db->query($q);
	if ($db->nf()==1) {
		$db->next_record();
		$accept_user_id=$db->f('user_id');
		print $accept_user_id;
		$q="INSERT INTO seminar_user SET user_id='".$accept_user_id."', seminar_id='".$GLOBALS['SessSemName'][1]."', status='autor', position=0, gruppe=0, admission_studiengang_id=0, notification=0, mkdate=NOW(), comment='', visible='yes'";
		$db->query($q);
		$q="DELETE FROM admission_seminar_user WHERE user_id='".$accept_user_id."' AND seminar_id='".$GLOBALS['SessSemName'][1]."'";
		$db->query($q);
	}
}

// check params
if ($rechte && $_REQUEST['accept']) {
	accept_user($_REQUEST['accept']);
}
	


$CURRENT_PAGE = _("Mitglieder verwalten");
$msgs=array();

# get the layout template
$layout = $GLOBALS['template_factory']->open('layouts/base');

# get the template
$template =& $template_factory->open('studygroup_teilnehmer');
$template->set_attribute('msgs', $msgs);

$sem=new Seminar($SessSemName[1]);
$template->set_attribute('groupname',$sem->name);
$template->set_attribute('groupdescription',$sem->description);
$template->set_attribute('moderators', $sem->getMembers('dozent'));
$template->set_attribute('members', array_merge($sem->getMembers('dozent'), $sem->getMembers('tutor'), $sem->getMembers('autor')));
$template->set_attribute('accepted', $sem->getAdmissionMembers('accepted'));
$template->set_attribute('rechte', $rechte);

$infobox=array();
$infobox['picture']='groups.jpg';
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
$layout->set_attribute('tabs','links_openobject');
echo $layout->render();

page_close();
//<!-- $Id: show_log.php 11028 2008-12-15 07:48:38Z tthelen $ -->
