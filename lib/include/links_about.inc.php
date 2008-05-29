<?
# Lifter002: 
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
	$ILIAS_CONNECT_ENABLE,
	$i_page,
	$MAIL_NOTIFICATION_ENABLE,
	$perm,
	$username,
	$view;


require_once 'lib/include/reiter.inc.php';

$reiter=new reiter;

if (!$username){
	$username = $auth->auth['uname'];
}


//Create Reitersystem

//Topkats
$structure = array();
$structure["alle"] = array('topKat' => '', 'name' => _("Alle"), 'link' => "about.php?username=$username", 'active' => FALSE);
$structure["bild"] = array('topKat' => '', 'name' => _("Bild"), 'link' => "edit_about.php?view=Bild&username=$username", 'active' => FALSE);
$structure["daten"] = array('topKat' => '', 'name' => _("Nutzerdaten"), 'link' => "edit_about.php?view=Daten&username=$username", 'active' => FALSE);
$structure["sonstiges"] = array('topKat' => '', 'name' => _("eigene Kategorien"), 'link' => "edit_about.php?view=Sonstiges&username=$username", 'active' => FALSE);
$structure['tools'] = array('topKat' => '', 'name' => _("Tools"), 'link' => "admin_news.php?range_id=self&username=$username", 'active' => FALSE);
if ($username == $auth->auth["uname"]) {
// if (!$perm->have_perm("admin"))
	$structure['mystudip'] = array('topKat' => "", 'name' => _("My Stud.IP"), 'link' => "edit_about.php?view=allgemein&username=$username", 'active' => FALSE);
}
if ($GLOBALS["PLUGINS_ENABLE"]){
	// PluginEngine aktiviert.
	// Prüfen, ob HomepagePlugins vorhanden sind.
	$requser = new StudIPUser();
	$requser->setUserid(get_userid($username));
	$homepagepluginpersistence = PluginEngine::getPluginPersistence("Homepage");
	$activatedhomepageplugins = $homepagepluginpersistence->getAllActivatedPlugins();
	if (!is_array($activatedhomepageplugins)){
		$activatedhomepageplugins = array();
	}
	foreach ($activatedhomepageplugins as $activatedhomepageplugin){
		$activatedhomepageplugin->setRequestedUser($requser);
		// hier nun die HomepagePlugins anzeigen
		if ($activatedhomepageplugin->hasNavigation()){
			$hppluginnav = $activatedhomepageplugin->getNavigation();
			$structure["hpplugin_" . $activatedhomepageplugin->getPluginid()] = array('topKat' => '', 'name' => $hppluginnav->getDisplayname(), 'link' => $hppluginnav->getLink(), 'active' => FALSE);
			$pluginsubmenu["_hpplugin_" . $activatedhomepageplugin->getPluginId()] = array('topKat'=>"hpplugin_" . $activatedhomepageplugin->getPluginId(), 'name'=>$hppluginnav->getDisplayname(), 'link'=>$hppluginnav->getLink(), 'active'=>false);
			$submenu = $hppluginnav->getSubMenu();
			// create bottomkats for activated plugins
			foreach ($submenu as $submenuitem){
				// create entries in a temporary structure and add it to structure later
				$pluginsubmenu["hpplugin_" . $activatedhomepageplugin->getPluginId() . "_" . $submenuitem->getDisplayname()] = array ('topKat'=>"hpplugin_" . $activatedhomepageplugin->getPluginId(), 'name'=>$submenuitem->getDisplayname(), 'link'=> $submenuitem->getLink(), 'active'=>false);
			}
		}
	}
	// now insert the bottomkats
	$structure = array_merge((array)$structure, (array)$pluginsubmenu);
}
//Bottomkats
$structure["_alle"] = array('topKat' => "alle", 'name' => _("Pers&ouml;nliche Homepage"), 'link' => "about.php?username=$username", 'active' => FALSE);
$structure["_bild"] = array('topKat' => "bild", 'name' => _("Hochladen des pers&ouml;nlichen Bildes"), 'link' => "edit_about.php?view=Bild&username=$username", 'active' => FALSE);
$structure["_daten"] = array('topKat' => "daten", 'name' => _("Allgemein"), 'link' => "edit_about.php?view=Daten&username=$username", 'active' => FALSE);

$structure["lebenslauf"] = array('topKat' => 'daten', 'name' => _("Privat"), 'link' => "edit_about.php?view=Lebenslauf&username=$username", 'active' => FALSE);
if ($my_about->auth_user["perms"] != "dozent" && $my_about->auth_user["perms"] != "admin" && $my_about->auth_user["perms"] != "root") {
	$structure["studium"] = array('topKat' => 'daten', 'name' => _("Studiendaten"), 'link' => "edit_about.php?view=Studium&username=$username", 'active' => FALSE);
}

if ($my_about->auth_user['perms'] != 'root') {
	$structure["karriere"] = array('topKat' => 'daten', 'name' => _("Einrichtungsdaten"), 'link' => "edit_about.php?view=Karriere&username=$username", 'active' => FALSE);
}

$structure["_sonstiges"] = array('topKat' => "sonstiges", 'name' => _("Eigene Kategorien bearbeiten"), 'link' => "edit_about.php?view=Sonstiges&username=$username", 'active' => FALSE);
$structure["news"] = array('topKat' => 'tools', 'name' => _("News"), 'link' => "admin_news.php?range_id=self&username=$username", 'active' => FALSE);
$structure["lit"] = array('topKat' => 'tools', 'name' => _("Literatur"), 'link' => "admin_lit_list.php?_range_id=self&username=$username", 'active' => FALSE);
$structure["vote"] = array('topKat' => 'tools', 'name' => _("Votings und Tests"), 'link' => "admin_vote.php?page=overview&showrangeID=$username&username=$username", 'active' => FALSE);
$structure["eval"] = array('topKat' => 'tools', 'name' => _("Evaluationen"), 'link' => "admin_evaluation.php?rangeID=$username", 'active' => FALSE);

$structure["allgemein"] = array('topKat' => 'mystudip', 'name' => _("Allgemeines"), 'link' => "edit_about.php?view=allgemein&username=$username", 'active' => FALSE);
$structure["forum"] = array('topKat' => 'mystudip', 'name' => _("Forum"), 'link' => "edit_about.php?view=Forum&username=$username", 'active' => FALSE);
if (!$perm->have_perm("admin")) {
	if ($CALENDAR_ENABLE)
		$structure["calendar"] = array('topKat' => 'mystudip', 'name' => _("Terminkalender"), 'link' => "edit_about.php?view=calendar&username=$username", 'active' => FALSE);
	$structure["stundenplan"] = array('topKat' => 'mystudip', 'name' => _("Stundenplan"), 'link' => "edit_about.php?view=Stundenplan&username=$username", 'active' => FALSE);
}
$structure["messaging"] = array('topKat' => 'mystudip', 'name' => _("Messaging"), 'link' => "edit_about.php?view=Messaging&username=$username", 'active' => FALSE);
$structure["rss"]=array ('topKat'=>"mystudip", 'name'=>_("RSS Feeds"), 'link'=>"edit_about.php?view=rss&username=$username", 'active'=>FALSE);
if ($MAIL_NOTIFICATION_ENABLE && !$perm->have_perm('admin')) {
	$structure['notification'] = array('topKat' => 'mystudip', 'name' => _("Benachrichtigung"), 'link' => 'edit_about.php?view=notification', 'active' => FALSE);
}
if ($perm->have_perm("autor") AND $ILIAS_CONNECT_ENABLE) {
	$structure["ilias"] = array('topKat' => 'mystudip', 'name' => _("Mein ILIAS-Account"), 'link' => "migration2studip.php", 'active' => FALSE);
}
if ($perm->have_perm("autor") AND $ELEARNING_INTERFACE_ENABLE) {
	$structure["elearning"]=array ('topKat'=>"tools", 'name'=>_("Meine Lernmodule"), 'link'=>"my_elearning.php", 'active'=>FALSE);
}
if (!$perm->have_perm("admin")) {
	$structure["login"] = array('topKat' => 'mystudip', 'name' => _("Login"), 'link' => "edit_about.php?view=Login&username=$username", 'active' => FALSE);
}
// check if view is maintained by a plugin
$found = false;
if ($GLOBALS['PLUGINS_ENABLE']){
	if (is_array($activatedhomepageplugins)){
		$pluginid = PluginEngine::getCurrentPluginId();
		// Namen der aufgerufenen Datei aus der URL herausschneiden
		if (strlen($i_page) <= 0){
			$i_page = basename($GLOBALS['PHP_SELF']);
		}
		if ($i_page == "plugins.php"){
			foreach ($activatedhomepageplugins as $activatedhomepageplugin){
				$activatedhomepageplugin->setRequestedUser($requser);
				if ($activatedhomepageplugin->hasNavigation() && ($activatedhomepageplugin->getPluginId() == $pluginid)){
					// Hauptmenü gefunden
					$reiter_view="hpplugin_" . $activatedhomepageplugin->getPluginId();
					$navi = $activatedhomepageplugin->getNavigation();
					$submenu = $navi->getSubMenu();

					if ($submenu != null) {
						foreach ($submenu as $submenuitem) {
							if ($submenuitem->isActive()) {
								$reiter_view="hpplugin_" . $activatedhomepageplugin->getPluginId() . "_" . $submenuitem->getDisplayname();
							}
						}
					}
					$found = true;
					break;
				}
			}
		}
	}
}

if (!$found){
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
		switch ($view) {
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
