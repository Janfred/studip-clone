<?php
/**
 * Basic methods for managing plugins
 * @author Dennis Reil <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
require_once('lib/datei.inc.php');
define("PLUGIN_UPLOAD_ERROR",1);
define("PLUGIN_MANIFEST_ERROR",2);
define("PLUGIN_ALLREADY_INSTALLED_ERROR",3);
define("PLUGIN_INSTANTIATION_EROR",5);
define("PLUGIN_MISSING_METHOD_ERROR",6);
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
		$type = PluginEngine::getTypeOfPlugin($plugin);
		$plugin->prepareUninstallation();
		$engine = PluginEngine::getPluginPersistence($type);
		if (is_object($engine)){
			$engine->deinstallPlugin($plugin);
		}			
		$pluginenv = $plugin->getEnvironment();
		// the old plugin directory has to be deleted
		$this->deletePlugindir($pluginenv->getBasepath() . "/" . $plugin->getPluginpath());			
	}
	
	
	/**
	 * Installs a new plugin.
	 * - grabs the uploaded file, unzips it, reads the manifest, creates a new plugin directory und
	 *   finally registers it in the global plugin space.
	 * @param string $uploadfilename the name of the uploaded file
	 * @return ERROR_CODE/SUCCESS_CODE
	 */
	function installPlugin($uploadfilename,$forceupdate=false){
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
		// create new filename
		$newuploadfilename = $tmppackagedir . basename($uploadfilename);
		// move package
		$copy_func = is_uploaded_file($uploadfilename) ? 'move_uploaded_file' : 'copy';
		if (!@$copy_func($uploadfilename,$newuploadfilename)){
			return PLUGIN_UPLOAD_ERROR;
		}
		else {
			// delete uploaded file
			@unlink($uploadfilename);
		}
		unzip_file($newuploadfilename,$tmppackagedir);
		
		// delete uploaded file
		@unlink($newuploadfilename);
		// search for the manifest
		if (!file_exists($tmppackagedir . "/plugin.manifest")){
			return PLUGIN_MISSING_MANIFEST_ERROR;
		}
		
		// everything ok, so far
		$plugininfos = PluginEngine::getPluginManifest($tmppackagedir);
				
		if ((strlen($plugininfos["class"]) > 0) && (strlen($plugininfos["origin"]) > 0) && (strlen($plugininfos["version"]) > 0)){
			// Plugin-Hauptclasse instanziieren
			$pluginclassname = trim($plugininfos["class"]);			
						
			// Klasse instanziieren
			if (strlen($pluginclassname) > 0){ 
				if (strlen($plugininfos["pluginname"]) > 0){
					$pluginname = $plugininfos["pluginname"];
				}
				else {
					$pluginname = $plugininfos["pluginclassname"];
				}
				
				// Neuen Pfad bestimmen
				$vendordir = $this->environment->getPackagebasepath() . "/" . $plugininfos["origin"];
				$newpluginpath = $vendordir . "/" . $pluginclassname; // . "_" . $plugininfos["version"];
				$pluginrelativepath = $plugininfos["origin"] . "/" . $pluginclassname; //  . "_" . $plugininfos["version"];
				$persistence = PluginEngine::getPluginPersistence();
				$pluginregistered = $persistence->isPluginRegistered($pluginclassname);
			
				if (!file_exists($vendordir)){
					@mkdir($vendordir);
				}
				
				if (!file_exists($newpluginpath)){
					// ok, plugin in exact this version is not installed
					@mkdir($newpluginpath);
					// do we have to delete the old plugin directory?
				}
				else {
					// directory exists
					// is the plugin already installed?
					if (!$pluginregistered){
					   // not registered in database
					   // delete directory
					   $this->deletePlugindir($newpluginpath);					   
					   // and create an empty directory
					   @mkdir($newpluginpath);
					}
					else {
						// Plugin is already registered
						if (!$forceupdate){
							// plugin is registered and installed
							// and we didn't request to do an forced update
					 		return PLUGIN_ALLREADY_INSTALLED_ERROR;
						}
						else {
							// forced update
							// only delete the plugin directory
							// registration info will be updated automatically							
							$this->deletePlugindir($newpluginpath);
						}						
    				}
				}
				// check to see, if the plugin is already registered
				if ($pluginregistered && !$forceupdate){
					return PLUGIN_ALREADY_REGISTERED_ERROR;
				}				
				// everything fine, install it
 
   				// copy files
   				$this->copyr($tmppackagedir,$newpluginpath);
   				// delete the temporary path
   				$this->deletePlugindir($tmppackagedir);
   						
				// instantiate plugin
				require_once($newpluginpath . '/' . $pluginclassname . ".class.php");

				$plugin = new $pluginclassname();
				if ($plugin == null){
					// delete Plugin directory
		        	$this->deletePlugindir($newpluginpath);
		        	return PLUGIN_INSTANTIATION_EROR;
				}
				else {
					 // check if certain methods exist in the plugin
					 $methods = array_map('strtolower', get_class_methods($plugin));
					 if (array_search('show',$methods)){
					 	// now register the plugin in the database					 	
					 	$newpluginid = $persistence->registerPlugin($plugin,$pluginclassname,$pluginrelativepath);					 	
					 	if ($newpluginid > 0){
					 		$plugin->setPluginid($newpluginid);
					 	}
					 	// create database if needed					 	
					 	if ($plugininfos["dbscheme"] != ""){
					 		$this->createDBSchemeForPlugin($newpluginpath . "/" . $plugininfos["dbscheme"]);
					 	}		
					 	// do we have additional plugin classes in this package?
					 	$additionalclasses = $plugininfos["additionalclasses"];
					 	if (is_array($additionalclasses)){					 		
					 		foreach ($additionalclasses as $additionalclass){					 		
					 			require_once($newpluginpath . '/' . $additionalclass . ".class.php");
					 			$additionalplugin = new $additionalclass();					 	
					 			$persistence->registerPlugin($additionalplugin,$additionalclass,$pluginrelativepath,$plugin);
					 		}
					 	}
					 }
					 else {
						$this->deletePlugindir($newpluginpath);	 	
					 	return PLUGIN_MISSING_METHOD_ERROR;
					 }
				}
				return PLUGIN_INSTALLATION_SUCCESSFUL;	
			} 
		}
		else {
			return PLUGIN_MANIFEST_ERROR;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $schemafile the absolute path to the schemafile
	 */
	function createDBSchemeForPlugin($schemafile){
		$conn = PluginEngine::getPluginDatabaseConnection();		
		// this should use file_get_contents() in PHP 5
		$statements = split(";[[:space:]]*\n", implode('', file($schemafile)));
		foreach ($statements as $statement) {
			$conn->execute($statement);
		}
	}

	/**
	 * recursive copy 
	 *
	 * @param string $source the sourcedirectory
	 * @param string $dest the destination directory
	 * @return true - success
	 * 		   false - error
	 */
	function copyr($source, $dest) 
	{ 
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
		// zip the plugin-Directory and send it to the client
		$persistence = PluginEngine::getPluginPersistence();
		$plugin = $persistence->getPlugin($pluginid);		
		$manifest = PluginEngine::getPluginManifest($this->environment->getBasepath() . $plugin->getPluginpath());
		$file_id = strtolower(get_class($plugin)) . "_" . $manifest["version"] . ".zip";
		create_zip_from_directory($this->environment->getBasepath() . $plugin->getPluginpath(), $GLOBALS["TMP_PATH"] . "/" . $file_id);
		return GetDownloadLink($file_id, $file_id, 4, 'force');
	}
}
?>
