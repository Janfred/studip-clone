<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

/**
 *
 *	@author Dennis Reil, <dennis.reil@offis.de>
 *	@version $Revision$
 *	@package pluginengine
 *
 */

class PluginNavigation extends StudipPluginNavigation {

  protected $plugin;

  /**
   * The cmd of this Navigation object.
   *
   * @access private
   * @var string
   */
  protected $cmd = 'show';


  /**
   * Returns the cmd of this Navigation object.
   *
   * @return string  the cmd
   */
  function getCommand() {
    return $this->cmd;
  }


  /**
   * Sets the cmd of this Navigation's object.
   *
   * @param  string  the cmd
   *
   * @return void
   */
  function setCommand($cmd) {
    $this->cmd = $cmd;
  }

  function getPlugin() {
    return $this->plugin;
  }


  function setPlugin(StudIPPlugin $plugin) {
    $this->plugin = $plugin;

    foreach ($this->getSubNavigation() as $nav) {
      $nav->setPlugin($plugin);
    }
  }

    function addSubNavigation($name, PluginNavigation $navigation)
    {
        parent::addSubNavigation($name, $navigation);

        if (isset($this->plugin)) {
            $navigation->setPlugin($this->plugin);
        }
    }


    /**
     * Returns the link used by this Navigation object.
     */
    function getURL() {
        return PluginEngine::getURL($this->plugin, $this->params, $this->cmd);
    }

    /**
     * Add a new parameter for the link generation. If the key is already
     * in use, its value is replaced with the new one.
     */
    function addLinkParam($key, $value){
    	$this->params[$key] = $value;
    }


    /**
     * @deprecated
     */
    function setLinkParam($link){
	$this->addLinkParam('plugin_subnavi_params', $link);
    }
}
?>
