<?php
/*
 * Central point of access to plugins. Builds the top navigation and shows
 * the result of a plugins show implementation in the middle
 *
 *
 * @author Dennis Reil, CELab <dennis.reil@offis.de>
 * @date 04.07.2005
 * @version $Revision$
 * @package pluginengine
 * $HeadURL$
 * $Revision$
 * $Author$
 */
ob_start();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ('lib/seminar_open.php'); 		// initialise Stud.IP-Session

// read in the command and pluginid
$cmd = 'action' . $_GET["cmd"];
$pluginid = $_GET["id"];

// create plugin persistence objects
$pluginengine = PluginEngine::getPluginPersistence();

$plugin = NULL;

// pluginid is a real numeric id
if (is_numeric($pluginid)) {
	$plugin = $pluginengine->getPlugin($pluginid);
}

// pluginid is probably a plugin classname
else {
	// try to find a plugin class, satisfying the request
	$pluginid = $pluginengine->getPluginId($pluginid);
	if (UNKNOWN_PLUGIN_ID !== $pluginid) {
		$plugin = $pluginengine->getPlugin($pluginid);
	}
}


// either there is no such plugin or I am not permitted
if (is_null($plugin)) {

	if ($pluginengine->pluginExists($pluginid)) {
		$auth->login_if(TRUE);
	}

	else {
		include 'lib/include/html_head.inc.php';
		include 'lib/include/header.php';
		StudIPTemplateEngine::makeHeadline(_("Plugin nicht vorhanden"));
		StudIPTemplateEngine::showErrorMessage(_("Das angeforderte Plugin ist nicht vorhanden."));
		include 'lib/include/html_end.inc.php';
		exit;
	}
}

if (!array_search(strtolower($cmd),array_map('strtolower', get_class_methods($plugin)))) {
	include 'lib/include/html_head.inc.php';
	include 'lib/include/header.php';
	StudIPTemplateEngine::makeHeadline(_("Plugin-Operation nicht vorhanden"));
	StudIPTemplateEngine::showErrorMessage(_("Das Plugin verfügt nicht über die gewünschte Operation"));
	include 'lib/include/html_end.inc.php';
	exit;
}

if (array_search('initialize',array_map('strtolower', get_class_methods($plugin)))){
	// the plugin has an initialize-method
	// call it
	$plugin->initialize();
}

// moved down to allow the plugin to add extra headers
include ('lib/include/html_head.inc.php');
include ('lib/include/header.php');

$type = PluginEngine::getTypeOfPlugin($plugin);

// set the gettext-domain
$domain = "gtdomain_" . get_class($plugin);
bindtextdomain($domain,$plugindbenv->getBasepath() . $plugin->getPluginpath() . "/locale");
textdomain($domain);
$pluginparams = $_GET["plugin_subnavi_params"];

if ($type == "Standard"){
	// diplay the admin_menu
	if ($cmd == "actionshowConfigurationPage" && $perm->have_perm("admin")){
		include('lib/include/links_admin.inc.php');

	}
	// display the course menu
	include ('lib/include/links_openobject.inc.php');

	// let the plugin show its view
	$pluginnav = $plugin->getNavigation();

	if (is_object($pluginnav)){
		$iconname = "";
		if ($pluginnav->hasIcon()){
			$iconname = $plugin->getPluginpath() . "/" . $pluginnav->getIcon();
		}
		else {
			$iconname = $plugin->getPluginiconname();
		}

		if (isset($SessSemName["header_line"])){
			StudIPTemplateEngine::makeHeadline(sprintf("%s - %s",$SessSemName["header_line"],$plugin->getDisplaytitle()),true,$iconname);
		}
		else {
			StudIPTemplateEngine::makeHeadline(sprintf("%s",$plugin->getDisplaytitle()),true,$iconname);
		}
	}
	else {
		StudIPTemplateEngine::makeHeadline($plugin->getPluginname(),true,$plugin->getPluginiconname());
	}

	StudIPTemplateEngine::startContentTable(true);
	$plugin->$cmd($pluginparams);
	StudIPTemplateEngine::endContentTable();
}
else if ($type == "Administration") {
	// Administration-Plugins only accessible by users with admin rights
	if ($perm->have_perm("admin")){
	   // display the admin menu
	   include ('lib/include/links_admin.inc.php');

	   // let the plugin show its view
	   $pluginnav = $plugin->getNavigation();
	   if ($pluginnav->hasIcon()){
	   		StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginpath() . "/" .$pluginnav->getIcon());
	   }
	   else {
	   		StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginiconname());
	   }
	   StudIPTemplateEngine::startContentTable(true);
	   $plugin->$cmd($pluginparams);
	   StudIPTemplateEngine::endContentTable();

	}
	else {
		StudIPTemplateEngine::makeHeadline(_("fehlende Rechte"));
		StudIPTemplateEngine::showErrorMessage(_("Sie verfügen nicht über ausreichend Rechte für diese Aktion."));
	}
}
else if ($type == "System") {
	$pluginnav = $plugin->getNavigation();
	if ($pluginnav->hasIcon()){
		StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginpath() . "/" .$pluginnav->getIcon());
	}
	else {
		StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginiconname());
	}

	StudIPTemplateEngine::startContentTable();
	// let the plugin show its view
	$plugin->$cmd($pluginparams);
	StudIPTemplateEngine::endContentTable();
}
else if ($type == "Homepage"){
	textdomain('studip');
	// show the admin-Tabs
	$hpusername = $_GET["requesteduser"];
	$admin_darf = FALSE;
	$db = new DB_Seminar();

	if (empty($hpusername)){
		$hpusername = $GLOBALS["auth"]->auth["uname"];
	}

	if ($GLOBALS["auth"]->auth["uname"] == $hpusername){
		$admin_darf = true;
	}

	$db->query("SELECT * FROM auth_user_md5  WHERE username ='$hpusername'");
	$db->next_record();
	if (!$db->nf()) {
		parse_window ("error§"._("Es wurde kein Nutzer unter dem angegebenen Nutzernamen gefunden!")."<br />"._(" Wenn Sie auf einen Link geklickt haben, kann es sein, dass sich der Username des gesuchten Nutzers ge&auml;ndert hat, oder der Nutzer gel&ouml;scht wurde.")."§", "§", _("Benutzer nicht gefunden"));
		die;
	} else{
		$user_id=$db->f("user_id");
	}

	$requser = new StudIPUser();
	$requser->setUserid($user_id);
	$plugin->setRequestedUser($requser);

	//Bin ich ein Inst_admin, und ist der user in meinem Inst Tutor oder Dozent?
	$db->query("SELECT b.inst_perms FROM user_inst AS a LEFT JOIN user_inst AS b USING (Institut_id) WHERE (b.user_id = '$user_id') AND (b.inst_perms = 'autor' OR b.inst_perms = 'tutor' OR b.inst_perms = 'dozent') AND (a.user_id = '$user->id') AND (a.inst_perms = 'admin')");
	if ($db->num_rows())
		$admin_darf = TRUE;
	if ($perm->is_fak_admin()){
		$db->query("SELECT c.user_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.fakultaets_id)  LEFT JOIN user_inst c ON(b.Institut_id=c.Institut_id) WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND c.user_id='$user_id'");
		if ($db->next_record())
		$admin_darf = TRUE;
	}
	if ($perm->have_perm("root")) {
		$admin_darf=TRUE;
	}

	IF ($perm->have_perm("root") OR $admin_darf == TRUE) { // Es werden die Editreiter angezeigt, wenn ich &auml;ndern darf
		// rights should be checked
		$username = $hpusername;
		include('lib/include/links_about.inc.php');
	}
	textdomain($domain);
	$pluginnav = $plugin->getNavigation();
	StudIPTemplateEngine::makeHeadline($plugin->getDisplaytitle(),true,$plugin->getPluginiconname());
	StudIPTemplateEngine::startContentTable();
	// let the plugin show its view
	$plugin->$cmd($pluginparams);
	StudIPTemplateEngine::endContentTable();
} else if ($type == "Portal" || $type == "Core"){
	StudIPTemplateEngine::makeHeadline($plugin->getDisplaytitle(),true,$plugin->getPluginiconname());
	StudIPTemplateEngine::startContentTable();
	// let the plugin show its view
	$plugin->$cmd($pluginparams);
	StudIPTemplateEngine::endContentTable();
}
else {
	 // Further plugin types have to be integrated here
	 echo (_("Unbekannter Plugin-Typ"));
}
// restore the domain
textdomain("studip");
// close the page
include ('lib/include/html_end.inc.php');
page_close();
ob_end_flush();
?>
