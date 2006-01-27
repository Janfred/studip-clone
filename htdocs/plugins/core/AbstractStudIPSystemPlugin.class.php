<?php

/**
 * Ausgangspunkt für Systemplugins. Systemplugins können in das Hauptmenü
 * integriert werden oder aber ohne GUI im Hintergrund laufen.
 * @author Dennis Reil <dennis.reil@offis.de>
 */

class AbstractStudIPSystemPlugin extends AbstractStudIPPlugin{
	
	function AbstractStudIPSystemPlugin(){
		// Konstruktor der Basisklasse aufrufen
		AbstractStudIPPlugin::AbstractStudIPPlugin();
		$this->pluginengine = PluginEngine::getPluginPersistence("System");
	}
}
?>