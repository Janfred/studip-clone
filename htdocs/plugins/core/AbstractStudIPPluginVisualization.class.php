<?php
/**
 * abstract class for the visualization of plugins
 * @author Dennis Reil <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
class AbstractStudIPPluginVisualization {
	// reference to plugin
	var $pluginref;	
	
	function AbstractStudIPPluginVisualization($plugin){
		$this->pluginref = $plugin;
	}
}
?>