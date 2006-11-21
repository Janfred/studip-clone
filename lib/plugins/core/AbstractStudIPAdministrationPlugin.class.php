<?php

/**
 * Ausgangspunkt für Administrationsplugins, also Plugins, die speziell im
 * Adminstrator- / Root-Bereich angezeigt werden.
 * @author Dennis Reil <dennis.reil@offis.de>
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPAdministrationPlugin extends AbstractStudIPPlugin{

	var $topnavigation;

	function AbstractStudIPAdministrationPlugin(){
		// Konstruktor der Basisklasse aufrufen
    	AbstractStudIPPlugin::AbstractStudIPPlugin();
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
    function setTopnavigation($newnavigation){
    	if (is_a($newnavigation,'PluginNavigation') || is_subclass_of($newnavigation,'PluginNavigation')){
	    	// $newnavigation->setPluginpath($this->getPluginpath());
    		$this->topnavigation = $newnavigation;
    	}
    }
}
?>
