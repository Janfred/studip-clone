<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 */

if ($GLOBALS['PLUGINS_ENABLE']){
	// the plugin interface classes
	require_once("core/StudIPInstitute.class.php");
	require_once("core/AdminInfo.class.php");
	require_once("core/StudipPluginNavigation.class.php");
	require_once("core/PluginNavigation.class.php");
	require_once("core/Permission.class.php");
	require_once("core/StudIPUser.class.php");
	require_once("core/Environment.class.php");
	require_once("core/AbstractStudIPPlugin.class.php");
	require_once("core/AbstractStudIPLegacyPlugin.class.php");
	require_once("core/AdministrationPlugin.class.php");
	require_once("core/StandardPlugin.class.php");
	require_once("core/SystemPlugin.class.php");
	require_once("core/HomepagePlugin.class.php");
	require_once("core/PortalPlugin.class.php");
	require_once("core/StudienmodulManagementPlugin.class.php");
	require_once("core/AbstractStudIPCorePlugin.class.php");
	require_once("core/AbstractStudIPAdministrationPlugin.class.php");
	require_once("core/AbstractStudIPStandardPlugin.class.php");
	require_once("core/AbstractStudIPSystemPlugin.class.php");
	require_once("core/AbstractStudIPHomepagePlugin.class.php");
	require_once("core/AbstractStudIPPortalPlugin.class.php");
	require_once("core/StudIPCore.class.php");
	require_once("engine/StudIPTemplateEngine.class.php");
	require_once("engine/PluginEngine.class.php");
	require_once("engine/PluginNotFound.class.php");
	require_once("core/Role.class.php");
	require_once("db/RolePersistence.class.php");

	// create a plugin environment
	$pluginenv = new Environment();
	$pluginenv->setBasepath($GLOBALS["STUDIP_BASE_PATH"] .'/public/');
	if (isset($GLOBALS["PLUGINS_PATH"]) && !empty($GLOBALS["PLUGINS_PATH"])){
		$pluginenv->setPackagebasepath($GLOBALS["PLUGINS_PATH"]);
	}
	else {
		// set the default
		$pluginenv->setPackagebasepath($GLOBALS["STUDIP_BASE_PATH"].
		                                 '/public/plugins_packages');
	}
	$pluginenv->setRelativepackagepath("plugins_packages");
	$pluginenv->setTmppath($GLOBALS["TMP_PATH"]);
}

?>
