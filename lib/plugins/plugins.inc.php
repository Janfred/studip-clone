<?php

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 */

if ($GLOBALS['PLUGINS_ENABLE']){
	// AdoDB-Database Interface
	require_once("vendor/adodb/adodb.inc.php");

	// the plugin interface classes
	require_once("core/StudIPInstitute.class.php");
	require_once("core/AbstractStudIPPluginVisualization.class.php");
	require_once("core/AdminInfo.class.php");
	require_once("core/ChangeMessage.class.php");
	require_once("core/HelpInfo.class.php");
	require_once("core/StudipPluginNavigation.class.php");
	require_once("core/PluginNavigation.class.php");
	require_once("core/Permission.class.php");
	require_once("core/StudIPUser.class.php");
	require_once("core/Environment.class.php");
	require_once("core/DBEnvironment.class.php");
	require_once("core/AbstractStudIPPlugin.class.php");
	require_once("core/AbstractStudIPLegacyPlugin.class.php");
	require_once("core/AbstractStudIPCorePlugin.class.php");
	require_once("core/AbstractStudIPAdministrationPlugin.class.php");
	require_once("core/AbstractStudIPStandardPlugin.class.php");
	require_once("core/AbstractStudIPSystemPlugin.class.php");
	require_once("core/AbstractStudIPHomepagePlugin.class.php");
	require_once("core/AbstractStudIPPortalPlugin.class.php");
	require_once("core/AbstractPluginPersistence.class.php");
	require_once("core/StudIPCore.class.php");
	require_once("engine/AbstractPluginIntegratorEnginePersistence.class.php");
	require_once("engine/StandardPluginIntegratorEnginePersistence.class.php");
	require_once("engine/AdministrationPluginIntegratorEnginePersistence.class.php");
	require_once("engine/SystemPluginIntegratorEnginePersistence.class.php");
	require_once("engine/HomepagePluginIntegratorEnginePersistence.class.php");
	require_once("engine/PortalPluginIntegratorEnginePersistence.class.php");
	require_once("engine/StudIPTemplateEngine.class.php");
	require_once("engine/PluginEngine.class.php");
	require_once("core/de_studip_Role.class.php");
	require_once("db/de_studip_RolePersistence.class.php");

	// create a plugin environment
	$plugindbenv = new DBEnvironment();
	$plugindbenv->setDbtype("mysql");
	$plugindbenv->setDbhost($GLOBALS["DB_STUDIP_HOST"]);
	$plugindbenv->setDbuser($GLOBALS["DB_STUDIP_USER"]);
	$plugindbenv->setDbpassword($GLOBALS["DB_STUDIP_PASSWORD"]);
	$plugindbenv->setDbname($GLOBALS["DB_STUDIP_DATABASE"]);
	$plugindbenv->setBasepath($GLOBALS["STUDIP_BASE_PATH"] .'/public/');
	if (isset($GLOBALS["PLUGINS_PATH"]) && !empty($GLOBALS["PLUGINS_PATH"])){
		$plugindbenv->setPackagebasepath($GLOBALS["PLUGINS_PATH"]);
	}
	else {
		// set the default
		$plugindbenv->setPackagebasepath($GLOBALS["STUDIP_BASE_PATH"].
		                                 '/public/plugins_packages');
	}
	$plugindbenv->setRelativepackagepath("plugins_packages");
	$plugindbenv->setTmppath($GLOBALS["TMP_PATH"]);

	$GLOBALS["ADODB_CACHE_DIR"] = $GLOBALS["TMP_PATH"];
}


?>
