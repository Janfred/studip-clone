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
    $id = trim(str_replace($GLOBALS["SessSemName"]["class"], "", $this->poiid));

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
    if (isset($GLOBALS["SessSemName"]["class"]) && strlen(trim($GLOBALS["SessSemName"]["class"])) >0) {
      $id = trim(str_replace($GLOBALS["SessSemName"]["class"],"",$this->poiid));
    }
    else {
      $id = trim(str_replace("sem","",$this->poiid));
      $id = trim(str_replace("inst","",$id));
    }
    $user = $this->getUser();
    $userid = $user->getUserid();
    $query = "select p.* from plugins p inner join plugins_activated pat using (pluginid)
            join roles_plugins rp on p.pluginid=rp.pluginid
            join roles_user r on r.roleid=rp.roleid
            where r.userid=? and pat.poiid=? and pat.state='on'
            union
            select p.* from auth_user_md5 au, plugins p inner join plugins_activated pat using (pluginid)
            join roles_plugins rp on p.pluginid=rp.pluginid
            join roles_studipperms rps on rps.roleid=rp.roleid
            where rps.permname = au.perms and au.user_id=? and pat.poiid=? and pat.state='on'
            "
      .  "UNION
        SELECT DISTINCT p.*
        FROM seminar_inst s
        INNER JOIN Institute i ON (i.Institut_id = s.institut_id)
        INNER JOIN plugins_default_activations pa ON (i.fakultaets_id = pa.institutid
        OR i.Institut_id = pa.institutid)
        INNER JOIN plugins p ON (p.pluginid = pa.pluginid AND p.enabled='yes')
        LEFT JOIN plugins_activated pad ON (pad.poiid = ? AND pad.pluginid = p.pluginid )
        WHERE s.seminar_id = ?
        AND (pad.state != 'off' OR pad.state IS NULL)";

    # TODO (mlunzena) PDO-ify
    if ($GLOBALS["PLUGINS_CACHING"]) {
      $result =& $this->connection->CacheExecute($GLOBALS['PLUGINS_CACHE_TIME'],$query,array($userid,$this->poiid,$userid,$this->poiid,$this->poiid,$id));
    }
    else {
      $result =& $this->connection->execute($query,array($userid,$this->poiid,$userid,$this->poiid,$this->poiid,$id));
    }

    if (!$result) {
      // TODO: Fehlermeldung ausgeben
      // echo ("keine aktivierten Plugins<br>");
      return array();
    }
    else {
      $plugins = array();
      while (!$result->EOF) {
        $pluginclassname = $result->fields("pluginclassname");
        $pluginpath = $result->fields("pluginpath");
        // Klasse instanziieren
        $plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
        if ($plugin !=null) {
          $plugin->setId($id);
          $plugin->setPluginid($result->fields("pluginid"));
          $plugin->setPluginname($result->fields("pluginname"));
          $plugin->setUser($this->getUser());
          $plugin->setActivated(true);
          $plugins[] = $plugin;
        }
        $result->MoveNext();
      }
      $result->Close();
      return $plugins;
    }
  }


  /**
   * saves a plugin and its active state
   * @param $plugin the plugin to save
   */
  function savePlugin($plugin) {
    parent::savePlugin($plugin);
    if (is_object($plugin) && is_subclass_of($plugin,'AbstractStudIPStandardPlugin')) {
      // get state
      if ($plugin->isActivated()) {
        $state = "on";
      }
      else {
        $state = "off";
      }
      // save active state
      # TODO (mlunzena) PDO-ify
      $this->connection->execute("replace into plugins_activated (pluginid,poiid,state) values (?,?,?)", array($plugin->getPluginId(), $this->poiid,$state));
    }
    else {
      // TODO: richtige Fehlerbehandlung
      echo ("ERROR: kein gültiger Parameter<br>");
      echo ("<pre>");
      print_r($plugin);
      echo ("</pre>");
    }
  }

  function getPlugin($id) {
    $user = $this->getUser();
    $userid = $user->getUserid();
    //TODO: Wieso hier ein Join? Wird das so noch benötigt?
    # TODO (mlunzena) PDO-ify
    $result = &$this->connection->execute("Select p.* from plugins p left join plugins_activated a on p.pluginid=a.pluginid where p.pluginid in (select rp.pluginid from roles_plugins rp where rp.roleid in (SELECT r.roleid FROM roles_user r where r.userid=? union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id=?)) and p.pluginid=? and p.plugintype='Standard' and (a.poiid=? or (a.pluginid is null))",array($userid,$userid,$id, $this->poiid));
    if (!$result) {
      // TODO: Fehlermeldung ausgeben
      return null;
    }
    else {
      if (!$result->EOF) {
        $pluginclassname = $result->fields("pluginclassname");
        $pluginpath = $result->fields("pluginpath");
        // Klasse instanziieren
        $plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
        if ($plugin != null) {
          $plugin->setPluginid($result->fields("pluginid"));
          $plugin->setPluginname($result->fields("pluginname"));
          $plugin->setUser($this->getUser());
        }
      }
      $result->Close();
      return $plugin;
    }
  }

  function deinstallPlugin($plugin) {
    parent::deinstallPlugin($plugin);
    // kill the activation information
    # TODO (mlunzena) PDO-ify
    $this->connection->execute("delete from plugins_default_activations where pluginid=?",array($plugin->getPluginid()));
  }

  /**
   * Save the default activations for a plugin
   * @param $plugin for which the default activation should be saved
   * @param $instituteids array of ids of the institutes for which the plugin should be activated as default
   * @return true - successful operation
             false - operation not successful
   */
  function saveDefaultActivations($plugin, $instituteids) {
    if (is_a($plugin,"AbstractStudIPStandardPlugin") || !is_array($instituteids)) {
      # TODO (mlunzena) PDO-ify
      $this->connection->execute("delete from plugins_default_activations where pluginid=?", array($plugin->getPluginid()));
      foreach ($instituteids as $instid) {
        // now save every instituteid
        # TODO (mlunzena) PDO-ify
        $this->connection->execute("insert into plugins_default_activations (pluginid,institutid) values (?,?)",array($plugin->getPluginid(),$instid));
      }
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Removes the default activations for a plugin
   * @param $plugin for which the default activation should be saved
   * @return true - successful operation
            false - operation not successful
   */
  function removeDefaultActivations($plugin) {
    if (is_a($plugin,"AbstractStudIPStandardPlugin") || !is_array($instituteids)) {
      # TODO (mlunzena) PDO-ify
      $this->connection->execute("delete from plugins_default_activations where pluginid=?", array($plugin->getPluginid()));
      return true;
    }
    else {
      return false;
    }
  }


  /**
   * Returns the default activations for a specific plugin
   * @param $plugin the plugin for which the default activation should be returned
   * @return the ids to the institutes
   */
  function getDefaultActivations($plugin) {
    if (is_a($plugin,"AbstractStudIPStandardPlugin")) {
      # TODO (mlunzena) PDO-ify
      $result =& $this->connection->execute("select * from plugins_default_activations where pluginid=?", array($plugin->getPluginid()));
      if (!$result) {
        // error or no result
        return array();
      }
      else {
        // get the ids
        $institutids = array();
        while (!$result->EOF) {
          $institutids[] = $result->fields("institutid");
          $result->MoveNext();
        }
        $result->Close();
        return $institutids;
      }
    }
    return array();
  }

  /**
   * Returns the default activations for a specific poi
   * @param $poiid the poi for which the default activation should be returned
   * @return the plugins, which are activated for this poi
   */
  function getDefaultActivationsForPOI($poiid) {
    $user = $this->getUser();
    $userid = $user->getUserid();
    # TODO (mlunzena) PDO-ify
    $result =& $this->connection->execute("select p.* from seminar_inst s inner join Institute i on i.Institut_id=s.institut_id inner join plugins_default_activations pa on i.fakultaets_id=pa.institutid or i.Institut_id=pa.institutid inner join plugins p on pa.pluginid=p.pluginid where p.pluginid in (select rp.pluginid from roles_plugins rp where rp.roleid in (SELECT r.roleid FROM roles_user r where r.userid=? union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id=?)) and s.seminar_id=?", array($userid,$userid,$poiid));
    if (!$result) {
      // TODO: Fehlermeldung ausgeben
      // echo ("keine standardmäßig aktivierten Plugins<br>");
      return array();
    }
    else {
      $plugins = array();
      while (!$result->EOF) {
        $pluginclassname = $result->fields("pluginclassname");
        $pluginpath = $result->fields("pluginpath");
        // Klasse instanziieren
        $plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
        if ($plugin != null) {
          $plugin->setPluginid($result->fields("pluginid"));
          $plugin->setPluginname($result->fields("pluginname"));
          $plugin->setUser($this->getUser());
          $plugins[] = $plugin;
        }
        $result->MoveNext();
      }
      $result->Close();
      return $plugins;
    }
  }
}
