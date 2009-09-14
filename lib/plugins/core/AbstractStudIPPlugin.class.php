<?php
# Lifter007: TODO
// vim: expandtab
/**
 * Abstract class for a plugin in Stud.IP.
 * Don't use this as a base class for creating your own plugin. Look at
 * AbstractStudIPStandardPlugin, AbstractStudIPSystemPlugin or
 * AbstractStudIPAdministrationPlugin for creating a plugin.
 *
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 * @version $Revision$
 * @see AbstractStudIPStandardPlugin, AbstractStudIPSystemPlugin, AbstractStudIPAdministrationPlugin
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

abstract class AbstractStudIPPlugin {

    /**
     * plugin meta data
     */
    protected $plugin_info;

    /**
     * constructor
     */
    function __construct() {
        $plugin_manager = PluginManager::getInstance();
        $this->plugin_info = $plugin_manager->getPluginInfo(get_class($this));
    }

    /**
     * Return the ID of this plugin.
     */
    function getPluginId() {
        return $this->plugin_info['id'];
    }

    /**
     * Return the name of this plugin.
     */
    function getPluginName() {
        return $this->plugin_info['name'];
    }

    /**
     * Return the filesystem path to this plugin.
     */
    function getPluginPath() {
        $packagepath = $GLOBALS['pluginenv']->getRelativepackagepath();

        return $packagepath . '/' . $this->plugin_info['path'];
    }

    /**
     * Return the URL of this plugin. Can be used to refer to resources
     * (images, style sheets, etc.) inside the installed plugin package.
     */
    function getPluginURL() {
        return $GLOBALS['ABSOLUTE_URI_STUDIP'] . $this->getPluginpath();
    }

    /**
     * This method dispatches all actions.
     *
     * @param  string  the part of the dispatch path, that were not consumed yet
     *
     * @return void
     */
    abstract function perform($unconsumed_path);
}
