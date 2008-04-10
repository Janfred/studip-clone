<?php
// vim: noexpandtab
/**
 * poiid of administrators
 */
define("PLUGIN_ADMINISTRATION_POIID","admin");

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class AdministrationPluginIntegratorEnginePersistence
	extends AbstractPluginIntegratorEnginePersistence {

	/**
	 * Liefert alle in der Datenbank bekannten Plugins zurück
	 */
	function getAllInstalledPlugins() {

		$plugins = array();
		// nur Administrations-Plugins liefern
		$plugins = parent::executePluginQuery("where plugintype='Administration' ".
		                                      "order by navigationpos, pluginname");

			$db = DBManager::get();
			$stmt = $db->prepare("SELECT * FROM plugins_activated ".
			                     "WHERE pluginid=? and poiid=?");

		foreach ($plugins as $plugin) {
			$result = $stmt->execute(array($plugin->getPluginid(),
			                               PLUGIN_ADMINISTRATION_POIID));
			$plugin->setActivated($result && $stmt->rowCount() === 1);
			$extplugins[] = $plugin;
		}
		return $extplugins;
	}

	/**
	 * Liefert alle in der Datenbank bekannten und aktivierten Plugins zurück
	 */
	function getAllActivatedPlugins() {

		$db = DBManager::get();

		$user = $this->getUser();
		$userid = $user->getUserid();

		$stmt = $db->prepare(
			"SELECT p.* FROM plugins p ".
			"JOIN plugins_activated a ON p.pluginid=a.pluginid ".
			"JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
			"JOIN roles_user r ON rp.roleid=r.roleid ".
			"WHERE a.poiid=? AND p.plugintype='Administration' AND r.userid=? ".

			"UNION ".

			"SELECT distinct p.* FROM plugins p ".
			"JOIN plugins_activated a ON p.pluginid=a.pluginid ".
			"JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
			"JOIN roles_studipperms rps ON rps.roleid=rp.roleid ".
			"JOIN auth_user_md5 au ON rps.permname = au.perms ".
			"WHERE au.user_id=? AND a.poiid=? AND p.plugintype='Administration' ".
			"ORDER BY navigationpos, pluginname");


		$result = $stmt->execute(array(PLUGIN_ADMINISTRATION_POIID,
		                               $userid,
		                               $userid,
		                               PLUGIN_ADMINISTRATION_POIID));

		$plugins = array();

		// TODO (dreil): Fehlermeldung ausgeben
		// keine aktivierten Plugins
		if (!$result) {
			return array();
		}

		else {
			while ($row = $stmt->fetch()) {
				$pluginclassname = $row["pluginclassname"];
				$pluginpath = $row["pluginpath"];

				// Klasse instanziieren
				$plugin = PluginEngine::instantiatePlugin($pluginclassname,
				                                          $pluginpath);

				if ($plugin !== null) {
					$plugin->setPluginid($row["pluginid"]);
					$plugin->setPluginname($row["pluginname"]);
					$plugin->setActivated(true);
					$plugin->setUser($this->getUser());
					$plugins[] = $plugin;
				}
			}
			return $plugins;
		}
	}

	/**
	  * Speichere ein Plugin
	  */
	function savePlugin(AbstractStudIPAdministrationPlugin $plugin) {

		parent::savePlugin($plugin);

		$db = DBManager::get();

		// Plugin speichern
		if ($plugin->isActivated()) {
			$stmt = $db->prepare("REPLACE INTO plugins_activated (pluginid, poiid) ".
			                     "VALUES (?, ?)");
			$stmt->execute(array($plugin->getPluginId(),
			                     PLUGIN_ADMINISTRATION_POIID));
		}
		// Plugin aus der aktiven Tabelle löschen
		else {
			$stmt = $db->prepare("DELETE FROM plugins_activated WHERE pluginid=?");
			$stmt->execute(array($plugin->getPluginId()));
		}
	}

	function getPlugin($id) {

		$db = DBManager::get();
		$stmt = $db->prepare(
		  "SELECT p.* FROM plugins p ".
		  "LEFT JOIN plugins_activated a ON p.pluginid=a.pluginid ".
		  "WHERE a.poiid=? AND p.pluginid=? AND p.plugintype='Administration' ".
		  "AND (a.pluginid is null)");
		$result = $stmt->execute(array(PLUGIN_ADMINISTRATION_POIID, $id));

		// TODO (dreil): Fehlermeldung ausgeben
		if (!$result) {
			return null;
		}

		$plugin = NULL;

		if ($row = $stmt->fetch()) {
			$pluginclassname = $row["pluginclassname"];
			$pluginpath = $row["pluginpath"];

			// Klasse instanziieren
			$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
			if ($plugin !== null){
				$plugin->setPluginid($row["pluginid"]);
				$plugin->setPluginname($row["pluginname"]);
				$plugin->setUser($this->getUser());
			}
		}

		return $plugin;
	}
}
