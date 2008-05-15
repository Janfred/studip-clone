<?php

/**
 * The persistence for standard plugins.
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class StandardPluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {

  // point of integration id
  var $poiid;

  /**
   * Sets a new point of integration for this pluginengine. Usually the point of integration
   * is the current course or institute.
   * @param $newid the new point of integration id
   */
  function setPoiid($newid) {
    $this->poiid = $newid;
  }

  /**
   * Returns the id for the point of integration
   * @return the point of integration id
   */
  function getPoiid() {
    return $this->poiid;
  }

  /**
   * Returns all registered plugins
   * @return a list of plugins
   */
  function getAllInstalledPlugins() {
    // only return standard plugins
    $plugins = parent::executePluginQuery("where plugintype='Standard'");
    return $this->getActivationsForPlugins($plugins);
  }

  /**
   * Retrieve the activation information for a list of plugins
   * @param $plugins
   */
  function getActivationsForPlugins($plugins) {

    // Veranstaltungsid aus poiid bestimmen
    $id = trim(str_replace($_SESSION["SessSemName"]["class"], "", $this->poiid));

    $stmt = DBManager::get()->prepare(
      "SELECT pat.* FROM plugins_activated pat ".
      "WHERE pat.pluginid=? AND pat.poiid=? ".
      "UNION ".
      "SELECT p.pluginid, ?, 'on' ".
      "FROM seminar_inst s ".
      "JOIN Institute i ON i.Institut_id=s.institut_id ".
      "JOIN plugins_default_activations pa ".
      "ON i.fakultaets_id=pa.institutid OR i.Institut_id=pa.institutid ".
      "JOIN plugins p ON pa.pluginid=p.pluginid ".
      "WHERE s.seminar_id=? AND p.pluginid=?");

    foreach ($plugins as $plugin) {
      $result = $stmt->execute(array($plugin->getPluginid(),
                                     $this->poiid,
                                     $this->poiid,
                                     $id,
                                     $plugin->getPluginid()));

      if ($result) {
        if ($row = $stmt->fetch()) {
          $plugin->setActivated($row['state'] === 'on');
        }
        // no information for this plugin
        else {
          $plugin->setActivated(false);
        }
      }

      // no information for this plugin
      else {
        $plugin->setActivated(false);
      }

      $extplugins[] = $plugin;
    }
    return $extplugins;
  }

  /**
   * Returns all registered and enabled plugins.
   * @return a list of enabled plugins
   */
  function getAllEnabledPlugins() {
    $plugins = parent::executePluginQuery("where plugintype='Standard' and enabled='yes'");
    return $this->getActivationsForPlugins($plugins);
  }

  /**
   * Returns all activated and globally for this poi activated plugins
   * @return all activated plugins
   */
  function getAllActivatedPlugins() {
    // Veranstaltungsid aus poiid bestimmen
    if (isset($_SESSION["SessSemName"]["class"]) && strlen(trim($_SESSION["SessSemName"]["class"])) >0) {
      $id = trim(str_replace($_SESSION["SessSemName"]["class"],"",$this->poiid));
    }
    else {
      $id = trim(str_replace("sem","",$this->poiid));
      $id = trim(str_replace("inst","",$id));
    }
    $user = $this->getUser();
    $userid = $user->getUserid();

    $stmt = DBManager::get()->prepare(
      "SELECT p.* FROM plugins p ".
      "INNER JOIN plugins_activated pat USING (pluginid) ".
      "JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
      "JOIN roles_user r ON r.roleid=rp.roleid ".
      "WHERE r.userid=? AND pat.poiid=? AND pat.state='on' ".

      "UNION ".

      "SELECT p.* FROM auth_user_md5 au, plugins p ".
      "INNER JOIN plugins_activated pat USING (pluginid) ".
      "JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
      "JOIN roles_studipperms rps ON rps.roleid=rp.roleid ".
      "WHERE rps.permname = au.perms AND au.user_id=? AND ".
      "pat.poiid=? AND pat.state='on' ".

      "UNION ".

      "SELECT DISTINCT p.* FROM seminar_inst s ".
      "INNER JOIN Institute i ON (i.Institut_id = s.institut_id) ".
      "INNER JOIN plugins_default_activations pa ".
        "ON (i.fakultaets_id = pa.institutid ".
        "OR i.Institut_id = pa.institutid) ".
      "INNER JOIN plugins p ON (p.pluginid = pa.pluginid AND p.enabled='yes') ".
      "LEFT JOIN plugins_activated pad ".
        "ON (pad.poiid = ? AND pad.pluginid = p.pluginid ) ".
      "WHERE s.seminar_id = ? AND (pad.state != 'off' OR pad.state IS NULL)");

    $result = $stmt->execute(array($userid, $this->poiid, $userid,
                                   $this->poiid, $this->poiid, $id));

    // TODO: Fehlermeldung ausgeben
    // echo ("keine aktivierten Plugins<br>");
    if (!$result) {
      return array();
    }


    $plugins = array();
    while ($row = $stmt->fetch()) {
      $pluginclassname = $row["pluginclassname"];
      $pluginpath = $row["pluginpath"];

      // Klasse instanziieren
      $plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
      if ($plugin != null) {
        $plugin->setId($id);
        $plugin->setPluginid($row["pluginid"]);
        $plugin->setPluginname($row["pluginname"]);
        $plugin->setUser($this->getUser());
        $plugin->setActivated(true);
        $plugins[] = $plugin;
      }
    }
    return $plugins;
  }


  /**
   * saves a plugin and its active state
   * @param $plugin the plugin to save
   */
  function savePlugin(AbstractStudIPStandardPlugin $plugin) {
    parent::savePlugin($plugin);

    // get state
    $state = $plugin->isActivated() ? "on" : "off";

    // save active state
    $stmt = DBManager::get()->prepare(
      "REPLACE INTO plugins_activated ".
      "(pluginid, poiid, state) ".
      "VALUES (?, ?, ?)");
    $stmt->execute(array($plugin->getPluginId(), $this->poiid, $state));
  }


  function getPlugin($id) {
    $user = $this->getUser();
    $userid = $user->getUserid();

    //TODO: Wieso hier ein Join? Wird das so noch benˆtigt?

    $stmt = DBManager::get()->prepare(
      "SELECT p.* FROM plugins p ".
      "LEFT JOIN plugins_activated a ON p.pluginid=a.pluginid ".
      "WHERE p.pluginid IN (".
        "SELECT rp.pluginid FROM roles_plugins rp ".
        "WHERE rp.roleid IN (".
          "SELECT r.roleid FROM roles_user r ".
          "WHERE r.userid=? ".
          "UNION ".
          "SELECT rp.roleid FROM roles_studipperms rp, auth_user_md5 a ".
          "WHERE rp.permname = a.perms AND a.user_id=?".
        ")".
      ") AND p.pluginid=? AND p.plugintype='Standard' AND ".
      "(a.poiid=? OR (a.pluginid is null))");

    $result = $stmt->execute(array($userid, $userid, $id, $this->poiid));

    // TODO: Fehlermeldung ausgeben
    if (!$result) {
      return null;
    }

    if (($row = $stmt->fetch()) !== FALSE) {

      $pluginclassname = $row["pluginclassname"];
      $pluginpath = $row["pluginpath"];

      // Klasse instanziieren
      $plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
      if ($plugin != null) {
        $plugin->setPluginid($row["pluginid"]);
        $plugin->setPluginname($row["pluginname"]);
        $plugin->setUser($this->getUser());
      }
    }
    return $plugin;
  }

  function deinstallPlugin($plugin) {
    parent::deinstallPlugin($plugin);

    // kill the activation information
    $stmt = DBManager::get()->prepare(
      "DELETE FROM plugins_default_activations WHERE pluginid=?");
    $stmt->execute(array($plugin->getPluginId()));
  }

  /**
   * Save the default activations for a plugin
   * @param $plugin for which the default activation should be saved
   * @param $instituteids array of ids of the institutes for which the plugin should be activated as default
   * @return true - successful operation
             false - operation not successful
   */
  function saveDefaultActivations(AbstractStudIPStandardPlugin $plugin,
                                  $instituteids) {

    if (!is_array($instituteids)) {
      return FALSE;
    }

    $plugin_id = $plugin->getPluginId();

    $stmt = DBManager::get()->prepare(
      "DELETE FROM plugins_default_activations WHERE pluginid=?");
    $stmt->execute(array($plugin_id));

    // now save every instituteid
    $stmt = DBManager::get()->prepare(
      "INSERT INTO plugins_default_activations ".
      "(pluginid, institutid) ".
      "VALUES (?,?)");

    foreach ($instituteids as $instid) {
      $stmt->execute(array($plugin_id, $instid));
    }

    return true;
  }

  /**
   * Removes the default activations for a plugin
   * @param $plugin for which the default activation should be saved
   * @return true - successful operation
            false - operation not successful
   */
  function removeDefaultActivations(AbstractStudIPStandardPlugin $plugin) {
    $stmt = DBManager::get()->prepare(
      "DELETE FROM plugins_default_activations WHERE pluginid=?");
    $stmt->execute(array($plugin->getPluginid()));
    return true;
  }


  /**
   * Returns the default activations for a specific plugin
   * @param $plugin the plugin for which the default activation should be returned
   * @return the ids to the institutes
   */
  function getDefaultActivations(AbstractStudIPStandardPlugin $plugin) {

    $stmt = DBManager::get()->prepare(
      "SELECT institutid FROM plugins_default_activations ".
      "WHERE pluginid=?");

    $result = $stmt->execute(array($plugin->getPluginid()));

    // error or no result
    if (!$result) {
      return array();
    }

    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
  }

  /**
   * Returns the default activations for a specific poi
   * @param $poiid the poi for which the default activation should be returned
   * @return the plugins, which are activated for this poi
   */
  function getDefaultActivationsForPOI($poiid) {

    $user = $this->getUser();
    $userid = $user->getUserid();

    $stmt = DBManager::get()->prepare(
      "SELECT p.* FROM seminar_inst s ".
      "INNER JOIN Institute i ON i.Institut_id=s.institut_id ".
      "INNER JOIN plugins_default_activations pa ".
        "ON i.fakultaets_id=pa.institutid OR i.Institut_id=pa.institutid ".
      "INNER JOIN plugins p ON pa.pluginid=p.pluginid ".
      "WHERE p.pluginid IN (".
        "SELECT rp.pluginid FROM roles_plugins rp ".
        "WHERE rp.roleid IN (".
          "SELECT r.roleid FROM roles_user r ".
          "WHERE r.userid=? ".
          "UNION ".
          "SELECT rp.roleid FROM roles_studipperms rp, auth_user_md5 a ".
          "WHERE rp.permname = a.perms AND a.user_id=?".
        ")".
      ") AND s.seminar_id=?");

    $result = $stmt->execute(array($userid, $userid, $poiid));

    // TODO: Fehlermeldung ausgeben
    // echo ("keine standardm‰ﬂig aktivierten Plugins<br>");
    if (!$result) {
      return array();
    }

    // get the ids
    $plugins = array();
    while ($row = $stmt->fetch()) {
      $pluginclassname = $ow["pluginclassname"];
      $pluginpath = $row["pluginpath"];

      // Klasse instanziieren
      $plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);

      if ($plugin != null) {
        $plugin->setPluginid($row["pluginid"]);
        $plugin->setPluginname($row["pluginname"]);
        $plugin->setUser($this->getUser());
        $plugins[] = $plugin;
      }
    }
    return $plugins;
  }
}
