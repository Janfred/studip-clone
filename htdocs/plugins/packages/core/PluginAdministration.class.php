<?php
/**
 * Basic methods for managing plugins
 * @author Dennis Reil <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
require_once("datei.inc.php");
define("PLUGIN_UPLOAD_ERROR",1);
define("PLUGIN_MANIFEST_ERROR",2);
define("PLUGIN_ALLREADY_INSTALLED_ERROR",3);
define("PLUGIN_INSTANTIATION_EROR",5);
define("PLUGIN_MISSING_METHOD_ERROR",6);
define("PLUGIN_INSTALLATION_SUCCESSFUL",7);
define("PLUGIN_MISSING_MANIFEST_ERROR",8);
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
		if (!@copy($uploadfilename,$newuploadfilename)){
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
			$pluginname = trim($plugininfos["pluginname"]);
						
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
				$newpluginpath = $vendordir . "/" . $pluginname . "_" . $plugininfos["version"];
				$pluginrelativepath = $plugininfos["origin"] . "/" . $pluginname . "_" . $plugininfos["version"];
				$persistence = PluginEngine::getPluginPersistence();
				if (!file_exists($vendordir)){
					@mkdir($vendordir);
				}
				if (!file_exists($newpluginpath)){
					@mkdir($newpluginpath);
				}
				else {
					// directory exists
					// is the plugin already installed?
					if (!$persistence->isPluginRegistered($pluginname)){
					   // not registered in database
					   // delete directory
					   $this->deletePlugindir($newpluginpath);					   
					   // and create an empty directory
					   @mkdir($newpluginpath);
					}
					else {
						if ($forceupdate){
							// delete the plugin
							$oldpluginid = $persistence->getPluginId($pluginname);
							$oldplugin = $persistence->getPlugin($oldpluginid);
							$persistence->deinstallPlugin($oldplugin);
							@mkdir($newpluginpath);
						}
						else {
							// plugin is registered and installed
							// and we don't request to do an forced update
					 		return PLUGIN_ALLREADY_INSTALLED_ERROR;
						}
    				}
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
					 $methods = get_class_methods($plugin);
					 if (array_search('show',$methods)){
					 	// now register the plugin in the database
					 	$persistence->registerPlugin($plugin,$pluginclassname,$pluginrelativepath);
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
		$file_id = "pluginpackage_" . $pluginid . ".zip";
		create_zip_from_directory($this->environment->getBasepath() . $plugin->getPluginpath());
		// copy the generated file into the download location
		@copy($this->environment->getBasepath() . $plugin->getPluginpath() . ".zip",$GLOBALS["TMP_PATH"] . "/" . $file_id);
		// delete the generated file
		@unlink($this->environment->getBasepath() . $plugin->getPluginpath() . ".zip");
		$link = $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "sendfile.php?file_id=" . $file_id . "&type=4";
		return $link;
	}
}
?>