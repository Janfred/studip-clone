<?php
/* vim: noexpandtab */
/**
 * plugin type unknown
 */
define("UNKNOWN_PLUGINTYPE", "undefined");

/**
 * Factory Class for the plugin engine
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class PluginEngine {


	/**
	 * Contains the current plugin's ID
	 *
	 * @var mixed
	 */
	private static $currentPluginId;


	/**
	 * TODO
	 *
	 * @return int  returns the current plugin's ID
	 */
	public static function getCurrentPluginId() {
		return PluginEngine::$currentPluginId;
	}


	/**
	 * TODO
	 *
	 * @param  int  the current plugin's ID
	 *
	 * @return int  returns the current plugin's ID
	 */
	public static function setCurrentPluginId($id) {
		return (PluginEngine::$currentPluginId = $id);
	}

	/**
	 * This function maps an incoming request to a tuple
	 * (pluginclassname, unconsumed rest).
	 *
	 * @return array the above mentioned tuple
	 */
	public static function routeRequest($dispatch_to) {
		$dispatch_to = ltrim($dispatch_to, '/');
		$pos = strpos($dispatch_to, '/');
		if ($pos === FALSE) {
			throw new Studip_PluginNotFoundException(
			  _("Es wurde kein Plugin gewählt."));
		}
		return array(substr($dispatch_to, 0, $pos), substr($dispatch_to, $pos + 1));
	}

	/**
	 * TODO
	 *
	 * @param  string  TODO
	 *
	 * @return int     the plugin ID of the requested plugin
	 */
	public static function getPluginIdFromRequest(&$unconsumed) {

		$dispatch_to = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

		# retrieve plugin class
		list(, $plugin_class) = explode('/', $dispatch_to);

		if (empty($plugin_class)) {
			throw new Studip_PluginNotFoundException(
			  _("Es wurde kein Plugin gewählt."));
		}

		# retrieve corresponding plugin id
		$plugin_engine = PluginEngine::getPluginPersistence();
		$plugin_id = $plugin_engine->getPluginId($plugin_class);
		PluginEngine::setCurrentPluginId($plugin_id);

		# fill reference to unconsumed path
		$unconsumed = substr($dispatch_to, strlen($plugin_class) + 1);

		return $plugin_id;
	}

	/**
	* Returns the plugin persistence object for the required plugin type.
	* @param $plugintype - Standard, Administration, System
	* @return a persistence object
	*/
	public static function getPluginPersistence($plugintype="Abstract") {
		$classname = $plugintype . "PluginIntegratorEnginePersistence";
		$persistence = new $classname();
		$conn = PluginEngine::getPluginDatabaseConnection();

		$persistence->setConnection($conn);
		$persistence->setEnvironment($GLOBALS["plugindbenv"]);

		// now set the user
		$persistence->setUser(new StudIPUser());
		return $persistence;
	}

	/**
	* @param the plugin for which a persistence object should be instantiated
	*/
	public static function getPluginPersistenceByPlugin($plugin) {
		return PluginEngine::getPluginPersistence(PluginEngine::getTypeOfPlugin($plugin));
	}

	/**
	* Returns an active connection to the plugin database
	* @return active connection to the database
	* @todo Caching of database connections ?
	*/
	public static function getPluginDatabaseConnection() {
		$env = $GLOBALS["plugindbenv"]; // get the environment
		$connection = NewADOConnection($env->dbtype);

		// connect to the database
  		$connection->Connect($env->dbhost,$env->dbuser,$env->dbpassword,$env->dbname);

    	return $connection;
	}

	/**
	* Generates a Link which can be shown in user interfaces
	* @param $plugin - the plugin to which should be linked
	* @param $params - an array with name value pairs
	* @param $cmd - command to execute by clicking the link
	* @return a link to the current plugin with the additional $params
	*/
	public static function getLink($plugin, $params=array(), $cmd="show") {
		if (is_null($plugin)) {
			return "";
		}
		$link = sprintf("plugins.php/%s/%s", urlencode($plugin->getPluginclassname()), urlencode($cmd));
		if (PluginEngine::getTypeOfPlugin($plugin) == "Homepage") {
			$requser = $plugin->getRequestedUser();
			if (is_object($requser)) {
				$params["requesteduser"] = $requser->getUsername();
			}
		}

		// add params
		if (sizeof($params)) {
			$query_string = array();
	 		foreach ($params as $key => $val)
	 			$query_string[] = urlencode($key) . '=' . urlencode($val);
	 		$link .= '?' . join('&amp;', $query_string);
		}

		return $link;
	}

	/**
	 * Generates a Link to the plugin administration which can be shown in user interfaces
	 *
	 * @param   array   an optional array with name value pairs
	 * @param   string  an optional command defaulting to 'show'
	 *
	 * @return  string  a link to the administration plugin with the additional $params
	 */
	public static function getLinkToAdministrationPlugin($params = array(), $cmd = 'show') {
		$link = "plugins.php/pluginadministrationplugin/" . $cmd;

		// add params
		if (sizeof($params)) {
			$query_string = array();
			foreach ($params as $key => $val)
				$query_string[] = urlencode($key) . '=' . urlencode($val);
			$link .= '?' . join('&amp;', $query_string);
		}

		return $link;
	}

	/**
	* Returns the plugin type
	* @return returns the type of the plugin if known by the engine
			  otherwise returns undefined
	*/
	public static function getTypeOfPlugin($plugin) {
	  if ($plugin instanceof AbstractStudIPStandardPlugin) {
			return "Standard";
		} else if ($plugin instanceof AbstractStudIPAdministrationPlugin) {
			return "Administration";
		} else if ($plugin instanceof AbstractStudIPSystemPlugin) {
			return "System";
		} else if ($plugin instanceof AbstractStudIPHomepagePlugin) {
			return "Homepage";
		} else if ($plugin instanceof AbstractStudIPPortalPlugin) {
			return "Portal";
		} else if ($plugin instanceof AbstractStudIPCorePlugin) {
			return "Core";
		}
		return UNKNOWN_PLUGINTYPE;
  }


   /**
    * Creates an instance of the desired plugin class
    * @param pluginclassname - the desired class name
    * @param pluginpath - the path to the plugin
    * @return an instance of the desired plugin or null otherwise
    */
   public static function instantiatePlugin($pluginclassname, $pluginpath) {
   		$env = $GLOBALS["plugindbenv"];
	    $absolutepluginfile = $env->getPackagebasepath() . "/" . $pluginpath . "/" . $pluginclassname . ".class.php";
	    if (!file_exists($absolutepluginfile)) {
		    return null;
	    }
	    else {
			//anoack: unschöner workaround, aber auf die Schnelle kaum anders zu lösen, solange Plugins auch vorhandenen Stud.IP code nutzen wollen :)
			global $RELATIVE_PATH_RESOURCES, $RELATIVE_PATH_CALENDAR,$RELATIVE_PATH_LEARNINGMODULES,$RELATIVE_PATH_CHAT;
			require_once($absolutepluginfile);
		    $plugin = new $pluginclassname();
		    $plugin->setEnvironment($env);
		    $plugin->setPluginpath($env->getRelativepackagepath() . "/" . $pluginpath);
		    $plugin->setBasepluginpath($pluginpath);
		    return $plugin;
	    }
   }

   /**
	* Reads the manifest of the plugin in the given path
	* @return array containing the manifest information
	* @todo Klasse für die Rückgabe realisieren
	*/
	public static function getPluginManifest($pluginpath) {
	   $pluginpath = trim($pluginpath);
	   if (!(strrpos($pluginpath,"/") == strlen($pluginpath)-1)) $pluginpath .= "/";
	   if (!file_exists($pluginpath . "plugin.manifest")) {
	   	  return array();
	   }
	   $manifest = fopen($pluginpath . "plugin.manifest","r");
	   $plugininfos = array();
		while (!feof($manifest)) {
			// Suche nach STRING1=STRING2
			$result = fscanf($manifest,"%[^=]=%[^\n]");
			if ($result) {
				if ($result[0] == "pluginclassname") {
					if ($plugininfos["class"] != "") {
						$plugininfos["additionalclasses"][] = trim($result[1]);
					}
					else {
						$plugininfos["class"] = trim($result[1]);
					}
				}
				else if ($result[0] == "origin") {
					$plugininfos["origin"] = trim($result[1]);
				}
				else if ($result[0] == "version") {
					$plugininfos["version"] = trim($result[1]);
				}
				else if ($result[0] == "pluginname") {
					$plugininfos["pluginname"] = trim($result[1]);
				}
				else if ($result[0] == "dbscheme") {
					$plugininfos["dbscheme"] = trim($result[1]);
				}
				else if ($result[0] == "uninstalldbscheme") {
					$plugininfos["uninstalldbscheme"] = trim($result[1]);
				}
			}
		}
		fclose($manifest);
		return $plugininfos;
	}

	/**
	 * Searches for plugins in the plugins installation directory, if enabled in local.inc
	 * @return list of installable names of plugin packages
	 *
	 */
	public static function getInstallablePlugins() {
		$newpluginsdir = $GLOBALS["NEW_PLUGINS_PATH"];

		if (!isset($newpluginsdir)) {
			// there's no dir defined in the local.inc
			return array();
		}
		else {
			if (!file_exists($newpluginsdir)) {
				// the directory doesn't exist
				return array();
			}
			$dir = dir($newpluginsdir);
			$installableplugins = array();
			while ($file = readdir($dir->handle)) {
				if (preg_match("/(.*)\.zip/",$file) > 0) {
					$installableplugins[] = $file;
				}
			}
			return $installableplugins;
		}
	}

	/**
	 * Saves a value to the global session
	 *
	 * @param AbstractStudIPPlugin $plugin - the plugin for which the value should be saved
	 * @param string $key - a key for the value. has to be unique for the calling plugin
	 * @param string $value - the value, which should be saved into the session
	 */
	public static function saveToSession($plugin,$key,$value) {
		$_SESSION["PLUGIN_SESSION_SPACE"][strtolower(get_class($plugin))][$key] =serialize($value);
	}


	/**
	 * Retrieves the value to key from the global plugin session
	 *
	 */
	public static function getValueFromSession($plugin,$key) {
		return unserialize($_SESSION["PLUGIN_SESSION_SPACE"][strtolower(get_class($plugin))][$key]);
	}

	/**
	 * for internal use only
	 *
	 * @param unknown_type $key
	 * @return unknown
	 */
	public static function getEngineValueFromSession($key) {
		return unserialize($_SESSION["PLUGIN_SESSION_SPACE"]["PLUGINENGINE"][$key]);
	}

	/**
	 * for internal use only
	 *
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public static function saveEngineValueToSession($key,$value) {
		$_SESSION["PLUGIN_SESSION_SPACE"]["PLUGINENGINE"][$key] = serialize($value);
	}
}
