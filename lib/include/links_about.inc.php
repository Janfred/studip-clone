<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
// vim: noexpandtab
/*
links_about.inc.php - Navigation fuer die Uebersichtsseiten.
Copyright (C) 2002	Stefan Suchi <suchi@gmx.de>,
				Ralf Stockmann <rstockm@gwdg.de>,
				Cornelis Kater <ckater@gwdg.de
				Suchi & Berg GmbH <info@data-quest.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

global
	$auth,
	$CALENDAR_ENABLE,
	$ELEARNING_INTERFACE_ENABLE,
	$i_page,
	$MAIL_NOTIFICATION_ENABLE,
	$perm;


require_once 'lib/include/reiter.inc.php';

$reiter=new reiter;

if (isset($_REQUEST['usr_name'])) {
	$username = $_REQUEST['usr_name'];
} else if (isset($_REQUEST['username'])) {
	$username = $_REQUEST['username'];
} else {
	$username = $auth->auth['uname'];
}

// this really should not be here
$username = preg_replace('/[^\w@.-]/', '', $username);

URLHelper::addLinkParam('username', $username);

//Create Reitersystem

//Topkats
$structure = array();
$structure["alle"] = array('topKat' => '', 'name' => _("Alle"), 'link' => URLHelper::getLink('about.php'), 'active' => FALSE);
$structure["bild"] = array('topKat' => '', 'name' => _("Bild"), 'link' => URLHelper::getLink('edit_about.php?view=Bild'), 'active' => FALSE);
$structure["daten"] = array('topKat' => '', 'name' => _("Nutzerdaten"), 'link' => URLHelper::getLink('edit_about.php?view=Daten'), 'active' => FALSE);
$structure["sonstiges"] = array('topKat' => '', 'name' => _("eigene Kategorien"), 'link' => URLHelper::getLink('edit_about.php?view=Sonstiges'), 'active' => FALSE);
$structure['tools'] = array('topKat' => '', 'name' => _("Tools"), 'link' => URLHelper::getLink('admin_news.php?range_id=self'), 'active' => FALSE);
if ($username == $auth->auth["uname"]) {
// if (!$perm->have_perm("admin"))
	$structure['mystudip'] = array('topKat' => "", 'name' => _("My Stud.IP"), 'link' => URLHelper::getLink('edit_about.php?view=allgemein'), 'active' => FALSE);
}
if ($GLOBALS["PLUGINS_ENABLE"]){
	// PluginEngine aktiviert.
	// Prüfen, ob HomepagePlugins vorhanden sind.
	$requser = new StudIPUser();
	$requser->setUserid(get_userid($username));
	$activatedhomepageplugins = PluginEngine::getPlugins('Homepage');
	foreach ($activatedhomepageplugins as $activatedhomepageplugin){
		$activatedhomepageplugin->setRequestedUser($requser);
		// hier nun die HomepagePlugins anzeigen
		if ($plugin_struct = $reiter->getStructureForPlugin($activatedhomepageplugin)){
			$structure = array_merge($structure, $plugin_struct['structure']);
			if($plugin_struct['reiter_view']) $reiter_view = $plugin_struct['reiter_view'];
		}
	}
}
//Bottomkats
$structure["_alle"] = array('topKat' => "alle", 'name' => _("Persönliche Homepage"), 'link' => URLHelper::getLink('about.php'), 'active' => FALSE);
$structure["_bild"] = array('topKat' => "bild", 'name' => _("Hochladen des persönlichen Bildes"), 'link' => URLHelper::getLink('edit_about.php?view=Bild'), 'active' => FALSE);
$structure["_daten"] = array('topKat' => "daten", 'name' => _("Allgemein"), 'link' => URLHelper::getLink('edit_about.php?view=Daten'), 'active' => FALSE);

$structure["lebenslauf"] = array('topKat' => 'daten', 'name' => _("Privat"), 'link' => URLHelper::getLink('edit_about.php?view=Lebenslauf'), 'active' => FALSE);
if ($my_about->auth_user["perms"] != "dozent" && $my_about->auth_user["perms"] != "admin" && $my_about->auth_user["perms"] != "root") {
	$structure["studium"] = array('topKat' => 'daten', 'name' => _("Studiendaten"), 'link' => URLHelper::getLink('edit_about.php?view=Studium'), 'active' => FALSE);
}

if ($my_about->auth_user['perms'] != 'root') {
	if (count(UserDomain::getUserDomains())) {
		$structure["userdomains"] = array('topKat' => 'daten', 'name' => _("Nutzerdomänen"), 'link' => URLHelper::getLink("edit_about.php?view=userdomains"), 'active' => FALSE);
	}
	if ($my_about->special_user) {
		$structure["karriere"] = array('topKat' => 'daten', 'name' => _("Einrichtungsdaten"), 'link' => URLHelper::getLink('edit_about.php?view=Karriere'), 'active' => FALSE);
	}
}

$structure["_sonstiges"] = array('topKat' => "sonstiges", 'name' => _("Eigene Kategorien bearbeiten"), 'link' => URLHelper::getLink('edit_about.php?view=Sonstiges'), 'active' => FALSE);
$structure["news"] = array('topKat' => 'tools', 'name' => _("News"), 'link' => URLHelper::getLink('admin_news.php?range_id=self'), 'active' => FALSE);
$structure["lit"] = array('topKat' => 'tools', 'name' => _("Literatur"), 'link' => URLHelper::getLink('admin_lit_list.php?_range_id=self'), 'active' => FALSE);
$structure["vote"] = array('topKat' => 'tools', 'name' => _("Votings und Tests"), 'link' => URLHelper::getLink("admin_vote.php?page=overview&showrangeID=$username"), 'active' => FALSE);
$structure["eval"] = array('topKat' => 'tools', 'name' => _("Evaluationen"), 'link' => URLHelper::getLink("admin_evaluation.php?rangeID=$username"), 'active' => FALSE);

$structure["allgemein"] = array('topKat' => 'mystudip', 'name' => _("Allgemeines"), 'link' => URLHelper::getLink('edit_about.php?view=allgemein'), 'active' => FALSE);
$structure["forum"] = array('topKat' => 'mystudip', 'name' => _("Forum"), 'link' => URLHelper::getLink('edit_about.php?view=Forum'), 'active' => FALSE);
if (!$perm->have_perm("admin")) {
	if ($CALENDAR_ENABLE)
		$structure["calendar"] = array('topKat' => 'mystudip', 'name' => _("Terminkalender"), 'link' => URLHelper::getLink('edit_about.php?view=calendar'), 'active' => FALSE);
	$structure["stundenplan"] = array('topKat' => 'mystudip', 'name' => _("Stundenplan"), 'link' => URLHelper::getLink('edit_about.php?view=Stundenplan'), 'active' => FALSE);
}
$structure["messaging"] = array('topKat' => 'mystudip', 'name' => _("Messaging"), 'link' => URLHelper::getLink('edit_about.php?view=Messaging'), 'active' => FALSE);
$structure["rss"]=array ('topKat'=>"mystudip", 'name'=>_("RSS-Feeds"), 'link' => URLHelper::getLink('edit_about.php?view=rss'), 'active'=>FALSE);
if ($MAIL_NOTIFICATION_ENABLE && !$perm->have_perm('admin')) {
	$structure['notification'] = array('topKat' => 'mystudip', 'name' => _("Benachrichtigung"), 'link' => URLHelper::getLink('edit_about.php?view=notification'), 'active' => FALSE);
}
if ($perm->have_perm("autor") AND $ELEARNING_INTERFACE_ENABLE) {
	$structure["elearning"]=array ('topKat'=>"tools", 'name'=>_("Meine Lernmodule"), 'link' => URLHelper::getLink('my_elearning.php'), 'active'=>FALSE);
}
if (!$perm->have_perm("admin")) {
	$structure["login"] = array('topKat' => 'mystudip', 'name' => _("Login"), 'link' => URLHelper::getLink('edit_about.php?view=Login'), 'active' => FALSE);
}

if (!$reiter_view){
//View festlegen
switch ($i_page) {
	case "about.php" :
		$reiter_view="alle";
	break;
	case "migration2studip.php" :
		$reiter_view="ilias";
	break;
	case "my_elearning.php" :
		$reiter_view="elearning";
	break;
	case "edit_about.php" :
		switch ($_REQUEST['view']) {
			case "Bild":
				$reiter_view="bild";
			break;
			case "Daten":
				$reiter_view="daten";
			break;
			case "Karriere":
				$reiter_view="karriere";
			break;
			case "Lebenslauf":
				$reiter_view="lebenslauf";
			break;
			case 'Studium':
				$reiter_view='studium';
			break;
			case "userdomains":
				$reiter_view="userdomains";
			break;
			case "Sonstiges":
				$reiter_view="sonstiges";
			break;
			case "rss":
				$reiter_view="rss";
			break;
			case "Login":
				$reiter_view="login";
			break;
			case "allgemein":
				$reiter_view="allgemein";
			break;
			case "Forum":
				$reiter_view="forum";
			break;
			case "calendar":
				if ($CALENDAR_ENABLE)
					$reiter_view="calendar";
				break;
			case "Stundenplan":
				$reiter_view="stundenplan";
			break;
			case "Messaging":
				$reiter_view="messaging";
				break;
			case 'notification' :
					$reiter_view = 'notification';
				break;
		}
	break;
	case "admin_news.php":
		$reiter_view = "news";
	break;
	case "admin_lit_list.php":
	case "admin_lit_element.php":
	case "lit_search.php":
		$reiter_view = "lit";
	break;
	case "admin_vote.php":
		$reiter_view = "vote";
	break;
   case "admin_evaluation.php":
   case "eval_summary.php":
   case "eval_config.php":
   		$reiter_view = "eval";
   break;
   default :
		$reiter_view="alle";
	break;
}
}
$reiter->create($structure, $reiter_view);
?>
