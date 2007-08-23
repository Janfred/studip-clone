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
include ('lib/functions.php');

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

                StudIPTemplateEngine::startContentTable(true);
		StudIPTemplateEngine::showErrorMessage(_("Das angeforderte Plugin ist nicht vorhanden."));
                StudIPTemplateEngine::endContentTable();

		include 'lib/include/html_end.inc.php';
		exit;
	}
}

if (!array_search(strtolower($cmd),array_map('strtolower', get_class_methods($plugin)))) {
	include 'lib/include/html_head.inc.php';
	include 'lib/include/header.php';

        StudIPTemplateEngine::startContentTable(true);
	StudIPTemplateEngine::showErrorMessage(_("Das Plugin verfügt nicht über die gewünschte Operation"));
	StudIPTemplateEngine::endContentTable();

	include 'lib/include/html_end.inc.php';
	exit;
}

if (array_search('initialize',array_map('strtolower', get_class_methods($plugin)))){
	// the plugin has an initialize-method
	// call it
	$plugin->initialize();
}

$type = PluginEngine::getTypeOfPlugin($plugin);

if ($type == 'Homepage') {
	$username = isset($_GET['requesteduser']) ?
                        $_GET['requesteduser'] : $GLOBALS["auth"]->auth["uname"];

        $requser = new StudIPUser();
        $user_id = get_userid($username);
        $requser->setUserid($user_id);
        $plugin->setRequestedUser($requser);
}

global $CURRENT_PAGE, $SessSemName;

if ($type == 'Standard') {
        $CURRENT_PAGE = $SessSemName['header_line'].' - '.$plugin->getDisplayTitle();
} else {
        $CURRENT_PAGE = $plugin->getDisplayTitle();
}

$iconname = $plugin->getPluginiconname();       // currently unused

// moved down to allow the plugin to add extra headers
include ('lib/include/html_head.inc.php');
include ('lib/include/header.php');

// set the gettext-domain
$domain = "gtdomain_" . get_class($plugin);
bindtextdomain($domain,$plugindbenv->getBasepath() . $plugin->getPluginpath() . "/locale");
textdomain($domain);
$pluginparams = $_GET["plugin_subnavi_params"];

if ($cmd == "actionshowConfigurationPage" || $cmd == "actionshowDescriptionalPage") {
	// special actions only accessible by users with admin rights
	if ($perm->have_perm("admin")){
                // display the admin menu
                include ('lib/include/links_admin.inc.php');
	}
	else {
                $error = _("Sie verfügen nicht über ausreichend Rechte für diese Aktion.");
	}
}
else if ($type == "Administration") {
	// Administration-Plugins only accessible by users with admin rights
	if ($perm->have_perm("admin")){
                // display the admin menu
                include ('lib/include/links_admin.inc.php');
	}
	else {
                $error = _("Sie verfügen nicht über ausreichend Rechte für diese Aktion.");
	}
}
else if ($type == "Standard"){
        // display the course menu
        include ('lib/include/links_openobject.inc.php');
}
else if ($type == "Homepage"){
	if ($user_id != '') {
                $db = new DB_Seminar();
                $admin_darf = false;

                // Bin ich ein Inst_admin, und ist der user in meinem Inst Tutor oder Dozent?
                $db->query("SELECT b.inst_perms FROM user_inst AS a LEFT JOIN user_inst AS b USING (Institut_id) WHERE (b.user_id = '$user_id') AND (b.inst_perms = 'autor' OR b.inst_perms = 'tutor' OR b.inst_perms = 'dozent') AND (a.user_id = '$user->id') AND (a.inst_perms = 'admin')");

                if ($perm->have_perm("root"))
                        $admin_darf = true;
                else if ($GLOBALS["auth"]->auth["uname"] == $username)
                        $admin_darf = true;
                else if ($db->num_rows())
                        $admin_darf = true;
                else if ($perm->is_fak_admin()) {
                        $db->query("SELECT c.user_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.fakultaets_id)  LEFT JOIN user_inst c ON(b.Institut_id=c.Institut_id) WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND c.user_id='$user_id'");
                        if ($db->next_record())
                                $admin_darf = true;
                }

                if ($admin_darf == true) {
                        // show the admin tabs if user may edit
                        // $username is passed to links_about.inc.php
                        include('lib/include/links_about.inc.php');
                }
	} else{
                $error = _("Es wurde kein Nutzer unter dem angegebenen Nutzernamen gefunden!")."<br />".
                         _("Wenn Sie auf einen Link geklickt haben, kann es sein, dass sich der Username des gesuchten Nutzers geändert hat oder der Nutzer gelöscht wurde.");
	}
}

StudIPTemplateEngine::startContentTable(true);

if (isset($error)) {
        StudIPTemplateEngine::showErrorMessage($error);
} else {
	// let the plugin show its view
	$plugin->$cmd($pluginparams);
}

StudIPTemplateEngine::endContentTable();

// restore the domain
textdomain("studip");
// close the page
include ('lib/include/html_end.inc.php');
page_close();
ob_end_flush();
?>
