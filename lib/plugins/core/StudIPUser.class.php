<?php

/**
 * User-Object which should be used in plugins
 *
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class StudIPUser {

  var $userid;
  var $username;
  var $permission;
  var $surname;
  var $givenname;
  var $assignedroles;

  /**
   * Automatically reads in the uid of the current user
   */
  function StudIPUser() {
    $this->setUserid($GLOBALS["auth"]->auth['uid']);
  }

  function getSurname() {
    return $this->surname;
  }

  function getGivenname() {
    return $this->givenname;
  }

  # TODO (mlunzena) what a bad design, the whole idea of filling a user by
  #                 setting an ID smells
  function setUserid($id) {

    $this->userid = $id;
    $this->permission = new Permission($id);

    $row = PluginEngine::cachedCallback('plugins/StudIPUser/setUserid/' . $id,
                                        array(__CLASS__, '_setUserid'),
                                        array($id));
    if ($row !== FALSE) {
      $this->givenname = $row['Vorname'];
      $this->surname   = $row['Nachname'];
      $this->username  = $row['username'];
    }
  }

  # TODO (mlunzena) see above..
  static function _setUserid($id) {
    $stmt = DBManager::get()->prepare("SELECT Vorname, Nachname, username ".
                                      "FROM auth_user_md5 ".
                                      "WHERE user_id=?");
    $stmt->execute(array($id));
    return $stmt->fetch();
  }

  function getUserid() {
    return $this->userid;
  }

  function getPermission() {
    return $this->permission;
  }

  function getUsername() {
    return $this->username;
  }

  /**
   * checks, if this user is identical to the otheruser
   *
   * @param StudIPUser $otheruser
   * @return false - other user is not the same as this user
   *         true - both user are the same
   */
  function isSameUser(StudIPUser $otheruser) {
    return $otheruser->getUserid() === $this->getUserid();
  }

  function getAssignedRoles($withimplicit = false) {
    $rolemgmt = new de_studip_RolePersistence();
    $this->assignedroles = $rolemgmt->getAssignedRoles($this->userid,
                                                       $withimplicit);
    return $this->assignedroles;
  }
}
