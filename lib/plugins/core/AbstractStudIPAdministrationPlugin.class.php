<?php

/**
 * Ausgangspunkt für Administrationsplugins, also Plugins, die speziell im
 * Adminstrator- / Root-Bereich angezeigt werden.
 * @author Dennis Reil <dennis.reil@offis.de>
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPAdministrationPlugin extends AbstractStudIPLegacyPlugin{

	var $topnavigation;

	function AbstractStudIPAdministrationPlugin(){
		// Konstruktor der Basisklasse aufrufen
    	parent::AbstractStudIPLegacyPlugin();
    	$this->topnavigation = null;
    	$this->pluginengine = PluginEngine::getPluginPersistence("Administration");
	}

    /**
     * Verfügt dieses Plugin über einen Eintrag auf der Startseite des
     * Administrators
     * @return  true 	- Hauptmenü vorhanden
     * 			false	- kein Hauptmenü vorhanden
     */
    function hasTopNavigation(){
    	if ($this->topnavigation != null){
    		return true;
    	}
    	else {
    		return false;
    	}
    }

    /**
     * Liefert den Menüeintrag zurück
     * @return das Menü, oder null, wenn kein Menü vorhanden ist
     */
    function getTopNavigation(){
    	return $this->topnavigation;
    }

    /**
     * Setzt das Hauptmenü des Plugins
     */
    function setTopnavigation(StudipPluginNavigation $newnavigation){
    		$this->topnavigation = $newnavigation;
    		$this->topnavigation->setPlugin($this);
   	}


  /**
   * This abstract method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string the name of the action to accomplish
   *
   * @return void
   */
  function display_action($action) {

    $GLOBALS['CURRENT_PAGE'] = $this->getDisplayTitle();

    include 'lib/include/html_head.inc.php';
    include 'lib/include/header.php';

    $pluginparams = $_GET["plugin_subnavi_params"];

    // Administration-Plugins only accessible by users with admin rights
    if (!$GLOBALS['perm']->have_perm("admin")) {
      throw new Exception(_("Sie verfügen nicht über ausreichend Rechte für diese Aktion."));
    }

    // display the admin menu
    include 'lib/include/links_admin.inc.php';

    // let the plugin show its view
    StudIPTemplateEngine::startContentTable(true);
    $this->$action($pluginparams);
    StudIPTemplateEngine::endContentTable();

    // close the page
    include 'lib/include/html_end.inc.php';
    page_close();
    }
}
?>
