<?php

// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


if (version_compare(PHP_VERSION, '5.2', '<')) {
  require_once('vendor/phpxmlrpc/xmlrpc.inc');
  require_once('vendor/phpxmlrpc/jsonrpc.inc');
  require_once('vendor/phpxmlrpc/json_extension_api.inc');
}

/**
 * LockRules.class.php
 *
 *
 *
 * @author     Mark Sievers <msievers@uos.de>
 * @version    $Id: LockRules.class.php,v 1.7 2003/11/13 07:56:11 msievers Exp $
 * @access     public
 * @modulegroup
 * @module
 * @package
 */

class LockRules {

  function getLockText() {
    return '';
  }

  function output_locked_fielddata($field_data) {
    $return =  $field_data ? htmlReady($field_data)
      : "<font size=\"-1\"><i>". ("k.A.")."</i></font>";

    $return .= "<br/>" . $this->getLockText();
    return $return;
  }

  function getAllLockRules() {
    $i = 0;
    $lockdata = array();
    foreach (DBManager::get()->query("SELECT * FROM lock_rules") as $row) {
      $lockdata[$i++] = $this->wrapLockRules($row);
    }

    if (!sizeof($lockdata)) {
      return 0;
    }

    return $lockdata;
  }

  function getSemLockRule($sem_id) {
    $stmt = DBManager::get()->prepare(
      "SELECT lock_rule FROM seminare WHERE Seminar_id = ?");
    $result = $stmt->execute(array($sem_id));
    if (!$result) {
      echo "Error! query not succeeded";
      return 0;
    }
    $row = $stmt->fetch();
    if ($row === FALSE) {
      return 0;
    }

    return $this->getLockRule($row["lock_rule"]);
  }

  function getLockRule($lock_id) {

    $stmt = DBManager::get()->prepare(
      "SELECT * FROM lock_rules WHERE lock_id = ?");
    $result = $stmt->execute(array($lock_id));
    if (!$result) {
      echo "Error! query not succeeded";
      return 0;
    }
    $row = $stmt->fetch();
    if ($row === FALSE) {
      return 0;
    }

    return $this->wrapLockRules($row);
  }

  function wrapLockRules($row) {
    $lockdata = array();
    $lockdata["lock_id"]     = $row["lock_id"];
    $lockdata["name"]        = $row["name"];
    $lockdata["description"] = $row["description"];
    $lockdata['attributes']  = json_decode($row["attributes"], true);
    return $lockdata;
  }

  function insertNewLockRule($lockdata) {
    $lock_id = md5(uniqid("Legolas"));

    $json_attributes = json_encode($lockdata['attributes']);

    $stmt = DBManager::get()->prepare(
      "INSERT INTO lock_rules (lock_id, name, description, attributes) ".
      "VALUES (?, ?, ?, ?)");

    $result = $stmt->execute(array($lock_id,
                                   $lockdata["name"],
                                   $lockdata["description"],
                                   $json_attributes));

    if (!$result) {
      echo "Error! insert_query not succeeded";
      return 0;
    }

    return $lock_id;
  }

  function updateExistingLockRule($lockdata) {

    $stmt = DBManager::get()->prepare(
      "UPDATE lock_rules SET ".
      "name=?, description=?, attributes=? ".
      "WHERE lock_id=?");

    return $stmt->execute(array($lockdata["name"],
                                $lockdata["description"],
                                json_encode($lockdata['attributes']),
                                $lockdata["lock_id"])) ? 1 : 0;
  }

  function getLockRuleByName($name) {
    $stmt = DBManager::get()->prepare("SELECT lock_id FROM lock_rules ".
                                      "WHERE name=?");
                                      "WHERE name='".$name."'";
    if  (!$stmt->execute(array($name))) {
      echo "Error! query not succeeded";
      return 0;
    }
    $row = $stmt->fetch();
    if ($row === FALSE) {
      return 0;
    }
    return $row["lock_id"];
  }

  function deleteLockRule($lock_id) {
    $stmt = DBManager::get()->prepare(
      "DELETE FROM lock_rules ".
      "WHERE lock_id=?");

    return $stmt->execute(array($lock_id)) ? 1 : 0;
  }

}
