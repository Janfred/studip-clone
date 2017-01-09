<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * class to handle ILIAS 5.2 user-accounts
 *
 * This class contains methods to handle connected ILIAS 5 user-accounts.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias5ConnectedUser
 * @package    ELearning-Interface
 */
class Ilias5ConnectedUser extends Ilias4ConnectedUser
{
    var $roles;
    var $user_sid;
    /**
     * constructor
     *
     * init class.
     * @access
     * @param string $cms system-type
     */
    function __construct($cms, $user_id = false)
    {
        // get auth_plugin
        $user_id = $user_id ? $user_id : $GLOBALS['user']->id;
        $this->auth_plugin = DBManager::get()->query("SELECT IFNULL(auth_plugin, 'standard') FROM auth_user_md5 WHERE user_id = '" . $user_id . "'")->fetchColumn();
        parent::__construct($cms, $user_id);
    }

    /**
    * verify login data
    *
    * returns true, if login-data is valid
    * @access public
    * @param string $username username
    * @param string $password password
    * @return boolean login-validation
    */
    function verifyLogin($username, $password)
    {
        global $connected_cms, $messages;
        $result = $connected_cms[$this->cms_type]->soap_client->checkPassword($username, $password);
        if (strpos($result, '::') > 0)
            return true;
        return false;
    }
}