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
 */

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$auth->login_if($auth->auth["uid"] == "nobody");
include ("seminar_open.php"); 		// initialise Stud.IP-Session
include ("html_head.inc.php");
include ("header.php");

// read in the command and pluginid
$cmd = $_GET["cmd"];
$pluginid = $_GET["id"];
// create plugin persistence objects
$pluginengine = PluginEngine::getPluginPersistence();

// create an instance of the queried pluginid
$plugin = $pluginengine->getPlugin($pluginid);

// TODO: insert custom error handling for plugin engine
// allowed commands
// TODO: move allowed commands to configuration
if ($cmd != ("show" || "showDescriptionalPage")) {
	die(_("Ungültiger Parameter"));
}

if ($plugin == null){
	StudIPTemplateEngine::makeHeadline(_("Plugin nicht vorhanden"));
	StudIPTemplateEngine::showErrorMessage(_("Das angeforderte Plugin ist nicht vorhanden."));
	die();
}

if (!array_search(strtolower($cmd),get_class_methods($plugin))){
	die(_("Das Plugin verfügt nicht über die gewünschte Operation"));
}

if (array_search("initialize",get_class_methods($plugin))){
	// the plugin has an initialize-method
	// call it
	$plugin->initialize();
}

// TODO: "richtige" PluginEngine instanziieren und übergeben.
$type = PluginEngine::getTypeOfPlugin($plugin);

// set the gettext-domain
$domain = "gtdomain_" . get_class($plugin);
bindtextdomain($domain,$plugindbenv->getBasepath() . $plugin->getPluginpath() . "/locale");
textdomain($domain);
$pluginparams = $_GET["plugin_subnavi_params"];

if ($type == "Standard"){
	// diplay the admin_menu
	if (($cmd == "showConfigurationPage") && $perm->have_perm("admin")){
		include("links_admin.inc.php");
	}
	// display the course menu
	include ("links_openobject.inc.php");
	// let the plugin show its view	
	$pluginnav = $plugin->getNavigation();
	if (is_object($pluginnav)){
		if (isset($SessSemName["header_line"])){
			StudIPTemplateEngine::makeHeadline(sprintf(_("%s - %s"),$SessSemName["header_line"],$pluginnav->getDisplayname()),true,$plugin->getPluginiconname());
		}
		else {
			StudIPTemplateEngine::makeHeadline(sprintf(_("%s"),$pluginnav->getDisplayname()),true,$plugin->getPluginiconname());			
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
	   include ("links_admin.inc.php");
	   
	   // let the plugin show its view	
	   $pluginnav = $plugin->getNavigation();
	   StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginiconname());
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
	
	StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginiconname());
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
include ("html_end.inc.php");
page_close();
?>
