<?php
// vim: noexpandtab
/**
 * plugin id unknown
 */
define("UNKNOWN_PLUGIN_ID", -1);

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class AbstractPluginIntegratorEnginePersistence {
	var $environment;
	var $user;

	function AbstractPluginIntegratorEnginePersistence() {
		$this->user = null;
	}

	function setEnvironment(Environment $environment) {
		$this->environment = $environment;
	}

	function getEnvironment() {
		return $this->environment;
	}

	function setUser(StudIPUser $newuser) {
		$this->user = $newuser;
	}

	function getUser() {
		return $this->user;
	}

	/**
	 * Registers a new plugin in the database
	 * @return the pluginid
	 */
	function registerPlugin($plugin, $pluginclassname, $pluginpath,
	                        $dependentonplugin = null) {
		$db = DBManager::get();
		$type = PluginEngine::getTypeOfPlugin($plugin);
		if (strlen($type) > 0) {

			// try to find an existing entry to update
			$stmt = $db->prepare("SELECT pluginid FROM plugins ".
			  "WHERE pluginclassname=? AND plugintype=?");
			$stmt->execute(array($pluginclassname, $type));
			$row = $stmt->fetch();

			if ($row !== FALSE) {
				// try to update this entry
				$pluginid = $row["pluginid"];
				$stmt = $db->prepare("UPDATE plugins SET pluginpath=? ".
				  "WHERE plugintype=? AND pluginid=?");
				$stmt->execute(array($pluginpath, $type, $pluginid));
			}

			else {
				if (is_null($dependentonplugin)) {
					$stmt = $db->prepare(
					  "INSERT INTO plugins (pluginclassname, pluginname, pluginpath, ".
					  "plugintype, enabled, navigationpos) ".
					  "SELECT ?, ?, ?, ?, 'no', max(navigationpos) + 1 FROM plugins ".
					  "WHERE plugintype=?");
					$stmt->execute(array($pluginclassname, $plugin->getPluginname(),
					                     $pluginpath, $type, $type));
				}
				else {
					$stmt = $db->prepare(
					  "INSERT into plugins (pluginclassname, pluginname, pluginpath, ".
					  "plugintype, enabled, navigationpos, dependentonid) ".
					  "SELECT ?, ?, ?, ?, 'no', max(navigationpos) + 1, ? FROM plugins ".
					   "WHERE plugintype=?");
					$stmt->execute(array($pluginclassname, $plugin->getPluginname(),
					                     $pluginpath, $type,
					                     $dependentonplugin->getPluginid(), $type));
				}

				$pluginid = $db->lastInsertId();

				// now register the system roles to this plugin.
				$stmt = $db->prepare("INSERT INTO roles_plugins (roleid, pluginid) ".
				                     "SELECT roleid, ? FROM roles WHERE system='y'");
				$stmt->execute(array($pluginid));
			}

			return $pluginid;
		}
	}

	/**
	 * updates plugin base data like position in the navigation
	 */
	function savePlugin($plugin) {
		// keine Funktion
		$enabled = $plugin->isEnabled() ? "yes" : "no";

		$stmt = DBManager::get()->prepare(
		  "UPDATE plugins SET pluginname=?, enabled=?, navigationpos=? ".
		  "WHERE pluginid=?");
		$stmt->execute(array($plugin->getPluginname(),$enabled,$plugin->getNavigationPosition(),$plugin->getPluginid()));
	}

	function executePluginQuery($filter, $params = array(), $attendroles = true) {
		$user = $this->getUser();
		$userid = $user->getUserid();

		if (!empty($filter) && $attendroles) {
			// look for where in filter
			if (!(($pos = strpos($filter,"where")) === false)) {
				if (($pos == 0) || ($pos == 1)) {
					// where at the beginning
					$filter = str_replace("where", "", $filter);
				}
			}
			$pos = strpos($filter, "order");
			if ($pos === false || $pos > 1) {
				$filter = "and " . $filter;
			} else {
				$filter = " " . $filter;
			}
		}

		if ($attendroles) {
			// ok, filter should start with no where clause
			$params = array_merge(array($userid), (array)$params,
			                      array($userid), (array)$params);
			$filter = "JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
			          "JOIN roles_user r ON rp.roleid=r.roleid ".
			          "WHERE r.userid=? {$filter} ".
			          "UNION SELECT p.* FROM plugins p ".
			          "JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
			          "JOIN roles_studipperms rps ON rp.roleid=rps.roleid ".
			          "JOIN auth_user_md5 au ON rps.permname=au.perms ".
			          "WHERE au.user_id=? " . $filter;
		}

		// cache results for cache_time seconds
		$plugins = array();
		$stmt = DBManager::get()->prepare("SELECT p.* FROM plugins p " . $filter);
		$stmt->execute($params);

		$rolemgmt = new de_studip_RolePersistence();
		$userroles = $user->getAssignedRoles(true);
		while ($row = $stmt->fetch()) {
			$pluginclassname = $row["pluginclassname"];
			$pluginpath = $row["pluginpath"];
			$pluginid = $row["pluginid"];
			$rolerestriction = $rolemgmt->getAssignedPluginRoles($pluginid);
			$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
			if ($plugin != null) {
				$plugin->setPluginid($pluginid);
				$plugin->setPluginname($row["pluginname"]);
				$plugin->setNavigationPosition($row["navigationpos"]);
				if ($row["enabled"] == 'yes') {
					$plugin->setEnabled(true);
				}
				else {
					$plugin->setEnabled(false);
				}
				if (!is_null($row["dependentonid"])) {
					$plugin->setDependentOnOtherPlugin(true);
				}
				$plugins[] = $plugin;
			}
		}
		return $plugins;
	}

	/**
	  * Liefert alle in der Datenbank bekannten Plugins zurück
	  */
	function getAllInstalledPlugins() {
		return $this->executePluginQuery("order by plugintype, navigationpos, ".
		                                 "pluginname, enabled",
		                                 array(),
		                                 false);
	}

	function getPlugins($enabled = false) {
		$filter = $enabled ? 'yes' :  'no';
		return $this->executePluginQuery("where enabled=? order by navigationpos, ".
		                                 "pluginname, plugintype",
		                                 array($filter));
	}

	/**
	 * Liefert alle in der Datenbank bekannten und aktivierten Plugins zurück
	 */
	function getAllEnabledPlugins() {
		return $this->getPlugins(true);
	}

	/**
	 * Liefert alle in der Datenbank bekannten und aktivierten Plugins zurück
	 */
	function getAllDisabledPlugins() {
		return $this->getPlugins(false);
	}

	function getAllActivatedPlugins() {
		// TODO: Implementierung
		return null;
	}

	function getAllDeactivatedPlugins() {
		// TODO: Implementierung
		return null;
	}

	function getPlugin($id) {
		$plugins = $this->executePluginQuery("where p.pluginid=?", array($id));
		return count($plugins) === 1 ? $plugins[0] : null;
	}


	function pluginExists($id) {
		$stmt = DBManager::get()->prepare("SELECT * from plugins WHERE pluginid=?");
		$stmt->execute(array($id));
		return $stmt->fetch() !== FALSE;
	}


	function deinstallPlugin($plugin)	{
		// check, if there are dependent plugins
		// this plugin is a plugin without dependencies
		if (!$plugin->isDependentOnOtherPlugin()) {
			$dependentplugins = $this->executePluginQuery("where p.dependentonid=?",
			                                    array($plugin->getPluginid()), false);
			if (is_array($dependentplugins)) {
				// deinstall Plugin first
				foreach ($dependentplugins as $dependentplugin) {
					$this->deinstallPlugin($dependentplugin);
				}
			}
		}
		$db = DBManager::get();
		$stmt = $db->prepare("DELETE FROM plugins WHERE pluginid=?");
		$stmt->execute(array($plugin->getPluginid()));
		$stmt = $db->prepare("DELETE FROM plugins_activated WHERE pluginid=?");
		$stmt->execute(array($plugin->getPluginid()));
		$stmt = $db->prepare("DELETE FROM roles_plugins WHERE pluginid=?");
		$stmt->execute(array($plugin->getPluginid()));
	}

	/**
	 * Searches for $pluginclassname in the plugins database
	 *
	 * @return true  - plugin called $pluginclassname was found in the database
	 *         false - plugin not found
	 */
	function isPluginRegistered($pluginclassname) {
		$stmt = DBManager::get()->prepare("SELECT * FROM plugins ".
		  "WHERE pluginclassname=?");
		$stmt->execute(array($pluginclassname));

		$row = $stmt->fetch();
		if ($row === FALSE) {
			return FALSE;
		}

		return is_numeric($row['pluginid']);
	}

	/**
	 * Searches for $pluginname in the plugins database
	 *
	 * @param  string  class name
	 *
	 * @return int     the id of the plugin
	 */
	function getPluginId($pluginclassname) {
		$stmt = DBManager::get()->prepare("SELECT pluginid FROM plugins ".
		  "WHERE pluginclassname=?");
		$stmt->execute(array($pluginclassname));
		$row = $stmt->fetch();
		if ($row === FALSE) {
			throw new Studip_PluginNotFoundException();
		}
		return $row['pluginid'];
	}
}
