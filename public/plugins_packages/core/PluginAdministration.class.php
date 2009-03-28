<?php
// vim: noexpandtab
/**
 * Basic methods for managing plugins
 * @author Dennis Reil <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
require_once 'lib/datei.inc.php';
require_once 'lib/migrations/db_migration.php';
require_once 'lib/migrations/db_schema_version.php';
require_once 'lib/migrations/migrator.php';
require_once 'PluginRepository.class.php';

define("PLUGIN_UPLOAD_ERROR",1);
define("PLUGIN_MANIFEST_ERROR",2);
define("PLUGIN_ALREADY_INSTALLED_ERROR",3);
define("PLUGIN_NOT_COMPATIBLE_ERROR",4);
define("PLUGIN_INSTANTIATION_EROR",5);
define("PLUGIN_IS_NO_PLUGIN_ERROR",6);
define("PLUGIN_INSTALLATION_SUCCESSFUL",7);
define("PLUGIN_MISSING_MANIFEST_ERROR",8);
define("PLUGIN_ALREADY_REGISTERED_ERROR",9);

class PluginAdministration {
	var $environment;

	/**
	 * Creates a new object for plugin administration
	 *
	 * @param Environment $environment the plugin environment
	 */
	function PluginAdministration($environment){
		$this->environment = $environment;
	}

	/**
	 * Returns the error message for the given error/status code.
	 */
	function getErrorMessage ($error_code) {
		switch ($error_code) {
			case PLUGIN_INSTALLATION_SUCCESSFUL:
				return _("Die Installation des Plugins war erfolgreich");
			case PLUGIN_UPLOAD_ERROR:
				return _("Der Upload des Plugins ist fehlgeschlagen");
			case PLUGIN_MANIFEST_ERROR:
				return _("Das Manifest des Plugins ist nicht korrekt");
			case PLUGIN_NOT_COMPATIBLE_ERROR:
				return _("Das Plugin ist mit dieser Stud.IP-Version nicht kompatibel");
			case PLUGIN_INSTANTIATION_EROR:
				return _("Das Plugin-Objekt konnte nicht erzeugt werden");
			case PLUGIN_IS_NO_PLUGIN_ERROR:
				return _("Das Plugin enthält keine gültige Plugin-Klasse");
			case PLUGIN_ALREADY_INSTALLED_ERROR:
				return _("Das Plugin ist bereits installiert");
			case PLUGIN_MISSING_MANIFEST_ERROR:
				return _("Das Manifest des Plugins fehlt");
			case PLUGIN_ALREADY_REGISTERED_ERROR:
				return _("Das Plugin ist bereits in der Datenbank registriert");
			default:
				return _("Bei der Installation des Plugins ist ein Fehler aufgetreten");
		}
	}

	/**
	 * Deletes a directory
	 * @param string $dir the directory, which should be deleted
	 */
	function deletePlugindir($dir) {
		if (file_exists($dir)){
			rmdirr($dir);
		}
	}

	/**
	 * Does the uninstallation of a plugin.
	 *
	 * @param unknown_type $plugin
	 */
	function deinstallPlugin($plugin){
		$plugin_manager = PluginManager::getInstance();
		$plugin_manager->unregisterPlugin($plugin['id']);
		$pluginpath = $this->environment->getPackagebasepath().'/'.$plugin['path'];
		$manifest = PluginEngine::getPluginManifest($pluginpath);

		// delete database if needed
		$this->deleteDBSchema($pluginpath, $manifest);

		// the old plugin directory has to be deleted
		$this->deletePlugindir($pluginpath);
	}

	/**
	 * Installs a new plugin.
	 * - grabs the uploaded file, unzips it, reads the manifest, creates a new plugin directory und
	 *   finally registers it in the global plugin space.
	 * @param string $uploadfilename the name of the uploaded file
	 * @return ERROR_CODE/SUCCESS_CODE
	 */
	function installPlugin($uploadfilename,$forceupdate=false){
		global $SOFTWARE_VERSION;

		$currdate = mktime();
		$pluginstmpdir = $GLOBALS["TMP_PATH"] . "/plugins_tmp/";
		$tmppackagedir = $pluginstmpdir . $currdate . "/";

		// check if directory exists
		if (!file_exists($pluginstmpdir)){
			@mkdir($pluginstmpdir);
		}
		if (!file_exists($tmppackagedir)){
			@mkdir($tmppackagedir);
		}

		// extract plugin files
		unzip_file($uploadfilename, $tmppackagedir);

		// delete uploaded file
		if (is_uploaded_file($uploadfilename)) {
			unlink($uploadfilename);
		}

		// search for the manifest
		if (!file_exists($tmppackagedir . "/plugin.manifest")){
			$this->deletePlugindir($tmppackagedir);
			return PLUGIN_MISSING_MANIFEST_ERROR;
		}

		// everything ok, so far
		$plugininfos = PluginEngine::getPluginManifest($tmppackagedir);

		if (strlen($plugininfos['pluginclassname']) == 0 ||
		    strlen($plugininfos['version']) == 0 ||
		    strlen($plugininfos['origin']) == 0) {
			$this->deletePlugindir($tmppackagedir);
			return PLUGIN_MANIFEST_ERROR;
		}

		// check for compatible version
		if (isset($plugininfos['studipMinVersion']) &&
		      version_compare($plugininfos['studipMinVersion'], $SOFTWARE_VERSION) > 0 ||
		    isset($plugininfos['studipMaxVersion']) &&
		      version_compare($plugininfos['studipMaxVersion'], $SOFTWARE_VERSION) < 0) {
			$this->deletePlugindir($tmppackagedir);
			return PLUGIN_NOT_COMPATIBLE_ERROR;
		}

		// Plugin-Hauptclasse instanziieren
		$pluginclassname = $plugininfos['pluginclassname'];

		// Neuen Pfad bestimmen
		$vendordir = $this->environment->getPackagebasepath() . "/" . $plugininfos["origin"];
		$newpluginpath = $vendordir . "/" . $pluginclassname; // . "_" . $plugininfos["version"];
		$pluginrelativepath = $plugininfos["origin"] . "/" . $pluginclassname; //  . "_" . $plugininfos["version"];
		$plugin_manager = PluginManager::getInstance();
		$pluginregistered = $plugin_manager->getPluginInfo($pluginclassname);

		if (!file_exists($vendordir)){
			@mkdir($vendordir);
		}

		if (!file_exists($newpluginpath)){
			// ok, plugin in exact this version is not installed
			@mkdir($newpluginpath);
			// do we have to delete the old plugin directory?
		} else {
			// directory exists
			// is the plugin already installed?
			if (!$pluginregistered){
				// not registered in database
				// delete directory
				$this->deletePlugindir($newpluginpath);
				// and create an empty directory
				@mkdir($newpluginpath);
			} else {
				// Plugin is already registered
				if (!$forceupdate){
					// plugin is registered and installed
					// and we didn't request to do an forced update
					$this->deletePlugindir($tmppackagedir);
					return PLUGIN_ALREADY_INSTALLED_ERROR;
				} else {
					// forced update
					$this->updateDBSchema($newpluginpath, $tmppackagedir, $plugininfos);
					// only delete the plugin directory
					// registration info will be updated automatically
					$this->deletePlugindir($newpluginpath);
				}
			}
		}
		// check to see, if the plugin is already registered
		if ($pluginregistered && !$forceupdate){
			$this->deletePlugindir($tmppackagedir);
			return PLUGIN_ALREADY_REGISTERED_ERROR;
		}
		// everything fine, install it

		// copy files
		$this->copyr($tmppackagedir,$newpluginpath);
		// delete the temporary path
		$this->deletePlugindir($tmppackagedir);

		// create database if needed
		$this->createDBSchema($newpluginpath, $plugininfos, $pluginregistered && $forceupdate);

		// now register the plugin in the database
		$pluginid = $plugin_manager->registerPlugin($plugininfos['pluginname'], $pluginclassname, $pluginrelativepath);

		if ($pluginid === NULL) {
			// delete Plugin directory
			$this->deletePlugindir($newpluginpath);
			return PLUGIN_IS_NO_PLUGIN_ERROR;
		}

		// do we have additional plugin classes in this package?
		$additionalclasses = $plugininfos["additionalclasses"];

		if (is_array($additionalclasses)){
			foreach ($additionalclasses as $class){
				$plugin_manager->registerPlugin($class, $class, $pluginrelativepath, $plugin);
			}
		}

		return PLUGIN_INSTALLATION_SUCCESSFUL;
	}

	/**
	 * Downloads and installs a new plugin from the given URL.
	 *
	 * @param string $plugin_url the URL of the plugin package
	 * @return ERROR_CODE/SUCCESS_CODE
	 */
	function installPluginFromURL ($plugin_url)
	{
		$temp_name = tempnam($GLOBALS['TMP_PATH'], 'plugin');

		if (!@copy($plugin_url, $temp_name)) {
			return PLUGIN_UPLOAD_ERROR;
		}

		$status = $this->installPlugin($temp_name, true);
		unlink($temp_name);
		return $status;
	}

	/**
	 * Create the initial database schema for the plugin.
	 *
	 * @param string  $pluginpath absolute path to the plugin
	 * @param array   $manifest   plugin manifest information
	 * @param boolean $update     update installed plugin
	 */
	function createDBSchema ($pluginpath, $manifest, $update) {
		$pluginname = $manifest['pluginname'];

		if (isset($manifest['dbscheme']) && !$update) {
			$schemafile = $pluginpath.'/'.$manifest['dbscheme'];
			$statements = split(";[[:space:]]*\n", file_get_contents($schemafile));
			$db = DBManager::get();
			foreach ($statements as $statement) {
				$db->exec($statement);
			}
		}

		if (is_dir($pluginpath.'/migrations')) {
			$schema_version = new DBSchemaVersion($pluginname);
			$migrator = new Migrator($pluginpath.'/migrations', $schema_version);
			$migrator->migrate_to(null);
		}
	}

	/**
	 * Update the database schema maintained by the plugin.
	 *
	 * @param string $pluginpath     absolute path to the plugin
	 * @param string $new_pluginpath absolute path to updated plugin
	 * @param array  $manifest       plugin manifest information
	 */
	function updateDBSchema ($pluginpath, $new_pluginpath, $manifest) {
		$pluginname = $manifest['pluginname'];

		if (is_dir($pluginpath.'/migrations')) {
			$schema_version = new DBSchemaVersion($pluginname);
			$new_version = 0;

			if (is_dir($new_pluginpath.'/migrations')) {
				$migrator = new Migrator($new_pluginpath.'/migrations', $schema_version);
				$new_version = $migrator->top_version();
			}

			$migrator = new Migrator($pluginpath.'/migrations', $schema_version);
			$migrator->migrate_to($new_version);
		}
	}

	/**
	 * Delete the database schema maintained by the plugin.
	 *
	 * @param string $pluginpath absolute path to the plugin
	 * @param array  $manifest   plugin manifest information
	 */
	function deleteDBSchema ($pluginpath, $manifest) {
		$pluginname = $manifest['pluginname'];

		if (is_dir($pluginpath.'/migrations')) {
			$schema_version = new DBSchemaVersion($pluginname);
			$migrator = new Migrator($pluginpath.'/migrations', $schema_version);
			$migrator->migrate_to(0);
		}

		if (isset($manifest['uninstalldbscheme'])) {
			$schemafile = $pluginpath.'/'.$manifest['uninstalldbscheme'];
			$statements = split(";[[:space:]]*\n", file_get_contents($schemafile));
			$db = DBManager::get();
			foreach ($statements as $statement) {
				$db->exec($statement);
			}
		}
	}

	/**
	 * recursive copy
	 *
	 * @param string $source the sourcedirectory
	 * @param string $dest the destination directory
	 * @return true - success
	 *                false - error
	 */
	function copyr($source, $dest) {

		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}

		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest);
		}

		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			if ($dest !== "$source/$entry") {
				$this->copyr("$source/$entry", "$dest/$entry");
			}
		}

		// Clean up
		$dir->close();
		return true;
	}

	/**
	 * Zips a plugin packages
	 *
	 * @param int $pluginid the id of the plugin
	 * @return string a link to the file
	 */
	function zipPluginPackage($pluginid){
		$plugin_manager = PluginManager::getInstance();
		$plugin = $plugin_manager->getPluginInfoById($pluginid);
		$pluginpath = $this->environment->getPackagebasepath().'/'.$plugin['path'];
		$manifest = PluginEngine::getPluginManifest($pluginpath);
		$file_id = $plugin['class'].'-'.$manifest['version'].'.zip';

		create_zip_from_directory($pluginpath, $GLOBALS["TMP_PATH"].'/'.$file_id);
		return GetDownloadLink($file_id, $file_id, 4, 'force');
	}

	/**
	 * Fetch update information for a list of plugins. This method
	 * returns for each plugin: plugin name, current version and
	 * meta data of the plugin update, if available.
	 */ 
	function getUpdateInfo ($plugins)
	{
		$default_repository = new PluginRepository();

		foreach ($GLOBALS['PLUGIN_REPOSITORIES'] as $url) {
			$default_repository->readMetadata($url);
		}

		foreach ($plugins as $plugin) {
			$repository = $default_repository;
			$pluginpath = $this->environment->getPackagebasepath().'/'.$plugin['path'];
			$manifest = PluginEngine::getPluginManifest($pluginpath);

			if (isset($manifest['updateURL'])) {
				$repository = new PluginRepository($manifest['updateURL']);
			}

			$plugin_info = array(
				'name' => $manifest['pluginname'],
				'version' => $manifest['version']
			);

			$meta_data = $repository->getPlugin($manifest['pluginname']);

			if (isset($meta_data) &&
			    version_compare($meta_data['version'], $manifest['version']) > 0) {
				$plugin_info['update'] = $meta_data;
			}

			$update_info[$plugin['id']] = $plugin_info;
		}

		return $update_info;
	}
}
