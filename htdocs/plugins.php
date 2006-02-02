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

// read in the command and pluginid
$cmd = $_GET["cmd"];
$pluginid = $_GET["id"];

// create plugin persistence objects
$pluginengine = PluginEngine::getPluginPersistence();

// create an instance of the queried pluginid
$plugin = $pluginengine->getPlugin($pluginid);

include ("seminar_open.php"); 		// initialise Stud.IP-Session
include ("html_head.inc.php");
include ("header.php");


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
	// display the course menu
	include ("links_openobject.inc.php");
	// let the plugin show its view
	echo ("<div align=\"center\">");
	echo ("<table width=\"100%\">");
	echo("<tr><td>");
	$plugin->$cmd($pluginparams);
	echo ("</td></tr>");
	echo("</table>");
	echo ("</div>");
}
else if ($type == "Administration") {
	// Administration-Plugins only accessible by users with admin rights
	if ($perm->have_perm("admin")){
	   // let the plugin show its view   
	   echo ("<div align=\"center\">");
	   echo ("<table width=\"100%\">");
	   echo("<tr><td>");
	   $plugin->$cmd($pluginparams);   
	   echo ("</td></tr>");
	   echo("</table>");
	   echo ("</div>");
	}
	else {
		StudIPTemplateEngine::makeHeadline(_("fehlende Rechte"));
		StudIPTemplateEngine::showErrorMessage(_("Sie verfügen nicht über ausreichend Rechte für diese Aktion."));
	}
}
else if ($type == "System") {
	 // let the plugin show its view
	 echo ("<div align=\"center\">");
	 echo ("<table width=\"100%\">");
	 echo("<tr><td>");
	 $plugin->$cmd($pluginparams);
	 echo ("</td></tr>");
	 echo("</table>");
	 echo ("</div>");
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
