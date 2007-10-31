<?php
/*
 * Plugin for the administration of plugins and a good example for an Administration-Plugin
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 */
require_once('lib/visual.inc.php');
require_once("PluginAdministrationVisualization.class.php");
require_once("PluginAdministration.class.php");

class PluginAdministrationPlugin extends AbstractStudIPAdministrationPlugin{
	// management
	var $pluginmgmt; 
	// Visualization
	var $pluginvis;	
	
	/**
	 * 
	 */
	function PluginAdministrationPlugin(){
		AbstractStudIPAdministrationPlugin::AbstractStudIPAdministrationPlugin();
		$tab = new PluginNavigation();
		$tab->setDisplayname(_("Verwaltung von Plugins"));
		$this->setNavigation($tab); 
		$this->setTopNavigation($tab);
		$this->setPluginiconname("img/einst.gif");
	}
	
	/**
	 * Initializes basic functions like the PluginVisualization and the PluginAdministration
	 *
	 */
	function initialize(){
		if ($this->pluginmgmt == null){
			$this->pluginmgmt = new PluginAdministration($this->environment);
		}
		if ($this->pluginvis == null){
			$this->pluginvis = new PluginAdministrationVisualization($this);
		}
	}
	
	function showDefaultView($pluginengine,$msg=""){
		// $this->init();
		$plugins = $pluginengine->getAllInstalledPlugins();
		$installableplugins = PluginEngine::getInstallablePlugins();
		
		$roleplugin = $pluginengine->getPlugin($pluginengine->getPluginid("de_studip_core_RoleManagementPlugin"));		
		$this->pluginvis->showPluginAdministrationList($plugins,$msg,$installableplugins,$roleplugin);
	}
	
	function actionInstallPlugin(){
		$forceupdate = $_POST["update"];
		$pluginfilename = $_POST["pluginfilename"];		
		$user = $this->getUser();
		$permission = $user->getPermission();
		// check if user has the permission to check in / update plugins
		if (!$permission->hasRootPermission() && $permission->hasAdminPermission()){
		   // show nothing		   
		   return;
		}
		
		if ($GLOBALS['PLUGINS_UPLOAD_ENABLE']){
			$upload_file = $_FILES["upload_file"]["tmp_name"];
	    	// process the upload 
	    	// and register plugin in the database;
	    	$result = $this->pluginmgmt->installPlugin($upload_file,$forceupdate);  
	    	$pluginengine = PluginEngine::getPluginPersistence();
	    	$this->showDefaultView($pluginengine,$result);
		}
		else {
			// no plugin upload enabled
			if (isset($pluginfilename) && isset($GLOBALS['NEW_PLUGINS_PATH'])){
				$newpluginfilename = $GLOBALS['NEW_PLUGINS_PATH'] . "/" . $pluginfilename;
				$result = $this->pluginmgmt->installPlugin($newpluginfilename,$forceupdate);  	    		
			}
			else {
				// nothing to do			
			}
			$pluginengine = PluginEngine::getPluginPersistence();
	    	$this->showDefaultView($pluginengine,$result);
		}
	}
	
	/**
	 * Shows the plugins view
	 *
	 */
	function actionShow(){
		$user = $this->getUser();
		$permission = $user->getPermission();
		$pluginengine = PluginEngine::getPluginPersistence();
		$adminpluginengine = PluginEngine::getPluginPersistence("Administration");
		$systempluginengine = PluginEngine::getPluginPersistence("System");
		$standardpluginengine = PluginEngine::getPluginPersistence("Standard");
		
		// check if user has the permission to check in / update plugins
		if (!$permission->hasRootPermission() && $permission->hasAdminPermission()){
		   // show nothing
		   // $this->pluginvis->showPluginList($pluginengine->getAllEnabledPlugins());
		   return;
		}
		
		$detailpage = $_GET["detailpage"];
		$zip = $_GET["zip"];
		$deinstall = $_GET["deinstall"];
		$action = $_POST["action"]; 
		$forceupdate = $_POST["update"];
		$forcedeinstall = $_REQUEST["forcedeinstall"];
		
		if (isset($action)){
		  if ($action == "config"){
		  	 // user changed the configuration of plugins
		  	 $plugins = $pluginengine->getAllInstalledPlugins();
		  	 foreach ($plugins as $plugin){
		  	 	$id = $plugin->getPluginid();
		  	 	if (!isset($_POST["available_" . $id]) && !isset($_POST["navposition_" . $id])){
		  	 	   continue;
		  	 	}
		  	 	
		  	 	if ($_POST["available_" . $id] == "1"){
		  	 	   $plugin->setEnabled(true);
		  	 	}
		  	 	else {
		  	 	   $plugin->setEnabled(false);
		  	 	}
		  	 	$navpos = $_POST["navposition_" . $id];
		  	 	if ($navpos <= 0){
		  	 	   // minimaler Wert
		  	 	   $navpos = 1;
		  	 	}
		  	 	$plugin->setNavigationPosition($navpos);
		  	 	$type = PluginEngine::getTypeOfPlugin($plugin);
		  	 	if ($type == "Administration"){
		  	 	   if ($_POST["available_" . $id] == "1"){
    		  	 	   $plugin->setActivated(true);
    		  	 	}
    		  	 	else {
    		  	 	   $plugin->setActivated(false);
    		  	 	}
    		   	   $adminpluginengine->savePlugin($plugin);
    		    }
    		    /*
    		    else if ($type == "Standard") {
    			  // keine spezielle Behandlung nötig
    			  $pluginengine->savePlugin($plugin);
    		    }
    		    else if ($type == "System"){
    			  // keine spezielle Behandlung nötig
    			  $pluginengine->savePlugin($plugin);
    		    }	
    		    else if ($type == "Homepage"){
    			  // keine spezielle Behandlung nötig
    			  $pluginengine->savePlugin($plugin);
    		    }*/	  	 	
    			else {
    			  // keine spezielle Behandlung nötig
    			  $pluginengine->savePlugin($plugin); 
    			}
    		}
    		 
    	  } else if ($action == "install"){
    	  	// if ($update == "force")
    	  	$upload_file = $_FILES["upload_file"]["tmp_name"];
    	  	// process the upload 
    	  	// and register plugin in the database;
    	  	$result = $this->pluginmgmt->installPlugin($upload_file,$forceupdate);    	  	
		  }
		}
				
		if (isset($deinstall)){
			$plugin = $pluginengine->getPlugin($deinstall);
			if (is_object($plugin)){
			       if (isset($forcedeinstall)){
			   		// Plugin notwendige Änderungen vor der deinstallation durchführen lassen
					$this->pluginmgmt->deinstallPlugin($plugin);
				}
				else {
					// ask, if it should really be deleted
					$this->pluginvis->showDeinstallQuestion($plugin);
				}
			}
			// show the default view
			$this->showDefaultView($pluginengine);
		}
		else if (isset($detailpage)){
			 // let the Plugin show its descriptional page
			 $plugin = $pluginengine->getPlugin($detailpage);
			 $plugin->showDescriptionalPage();
		}
		else if (isset($zip)){
			 $link = $this->pluginmgmt->zipPluginPackage($zip);
			 $this->pluginvis->showPluginPackageDownloadView($link);
			 $this->showDefaultView($pluginengine);
		}	
		else {
			 // the plugin was called without any parameters
			 // show the default view
			$this->showDefaultView($pluginengine,$result);
		}
	}
}
?>
