<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

/**
 * abstract class for the visualization of plugins
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPPluginVisualization {
	// reference to plugin
	var $pluginref;

	function AbstractStudIPPluginVisualization($plugin){
		$this->pluginref = $plugin;
	}
}
?>
