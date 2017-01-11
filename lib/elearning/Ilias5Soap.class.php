<?php
/**
 * class to use ILIAS-5-Webservices
 *
 * This class contains methods to connect to the ILIAS-5-Soap-Server.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias5Soap
 * @package    ELearning-Interface
 */
class Ilias5Soap extends Ilias4Soap
{
    var $cms_type;
    var $admin_sid;
    var $user_sid;
    var $user_type;
    var $soap_cache;
    var $separator_string;

    /**
     * constructor
     *
     * init class.
     * @access
     * @param string $cms system-type
     */
    function __construct($cms)
    {
        parent::__construct($cms);
        $this->seperator_string = " / ";
    }

    /**
    * login
    *
    * login to soap-webservice
    * @access public
    * @return string result
    */
    function login()
    {
        global $ELEARNING_INTERFACE_MODULES, $connected_cms;
        if ($this->user_type == "admin") {
            $param = array(
                'client' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["client"],
                'username' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["username"],
                'password' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["password"]
                );
            $result = $this->call('login', $param);
        } elseif ($this->user_type == "user") {
            $param = array(
                'sid' => $this->admin_sid,
                'user_id' => $connected_cms[$this->cms_type]->user->getId()
                );
            $result = $this->call('loginStudipUser', $param);
        }
        if ($this->user_type == "admin")
            $this->admin_sid = $result;
        if ($this->user_type == "user")
            $this->user_sid = $result;
        return $result;
    }

    /**
    * Check Auth
    *
    * login to soap-webservice
    * @access public
    * @return string result
    */
    function checkPassword($username, $password)
    {
        global $ELEARNING_INTERFACE_MODULES, $connected_cms;
        $param = array(
            'client' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["client"],
            'username' => $username,
            'password' => $password
        );
        $result = $this->call('login', $param);
        return $result;
    }
}