<?php
/**
 * The persistence for standard plugins.
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$ 
 * $Id$
 * @package pluginengine
 */

class StandardPluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {

	// point of integration id
	var $poiid;

	function StandardPluginIntegratorEnginePersistence() {
    	// Konstruktor der Oberklasse aufrufen
    	parent::AbstractPluginIntegratorEnginePersistence();
    }
    
    /**
    * Sets a new point of integration for this pluginengine. Usually the point of integration
    * is the current course or institute.
    * @param $newid the new point of integration id
    */
    function setPoiid($newid){
	    $this->poiid = $newid;
    }
    
    /**
    * Returns the id for the point of integration
    * @return the point of integration id
    */
    function getPoiid(){
	    return $this->poiid;
    }
    
    /**
     * Returns all registered plugins
     * @return a list of plugins
     */
    function getAllInstalledPlugins(){
    	// only return standard plugins
    	$plugins = parent::executePluginQuery("where plugintype='Standard'");
    	return $this->getActivationsForPlugins($plugins);
    }
    
    /**
    * Retrieve the activation information for a list of plugins
    * @param $plugins 
    */
    function getActivationsForPlugins($plugins){
    	if ($this->connection == null){
    		$this->connection = PluginEngine::getPluginDatabaseConnection();
    	}
    	// Veranstaltungsid aus poiid bestimmen
		$id = trim(str_replace($GLOBALS["SessSemName"]["class"],"",$this->poiid));
    	foreach ($plugins as $plugin){   		
			$result =& $this->connection->execute("select pat.* from plugins_activated pat where pat.pluginid=? and pat.poiid=? "
					   . "union "
					   . "select p.pluginid,?,'on' from seminar_inst s join Institute i on i.Institut_id=s.institut_id join plugins_default_activations pa on i.fakultaets_id=pa.institutid or i.Institut_id=pa.institutid join plugins p on pa.pluginid=p.pluginid where s.seminar_id=? and p.pluginid=?",array($plugin->getPluginid(),$this->poiid,$this->poiid,$id,$plugin->getPluginid()));
    		if ($result){
    			// 
    			if (!$result->EOF){
    				// echo("DEBUG: " . $result->fields("pluginid") . " - " . $result->fields("state") . "<br>");
    				if ($result->fields("state") == "on"){
    					$plugin->setActivated(true);
    				}
    				else {
    					$plugin->setActivated(false);
    				}
    			}
    			else {
    				// no information for this plugin
    				$plugin->setActivated(false);
    			}
    			$result->Close();
    		} 
    		else {
    			// no information for this plugin
    			$plugin->setActivated(false);
    		}
    		$extplugins[] = $plugin;
    	}
    	return $extplugins;
    }
    
    /**
     * Returns all registered and enabled plugins.
     * @return a list of enabled plugins
     */
    function getAllEnabledPlugins(){
    	$plugins = parent::executePluginQuery("where plugintype='Standard' and enabled='yes'");
  		return $this->getActivationsForPlugins($plugins);
    }
    
    /**
     * Returns all activated and globally for this poi activated plugins 
     * @return all activated plugins
     */
    function getAllActivatedPlugins(){
    	// Veranstaltungsid aus poiid bestimmen
		$id = trim(str_replace($GLOBALS["SessSemName"]["class"],"",$this->poiid));
		$result =& $this->connection->execute("select p.* from plugins_activated pat inner join plugins p using (pluginid) where pat.poiid=? and pat.state='on' "
					   . "union "
					   . "select distinct p.* from seminar_inst s, plugins p join Institute i on i.Institut_id=s.institut_id join plugins_default_activations pa on i.fakultaets_id=pa.institutid or i.Institut_id=pa.institutid left join plugins_activated pad on p.pluginid=pad.pluginid where s.seminar_id=? and pa.pluginid=p.pluginid and ((pad.state <> 'off' and pad.poiid=?) or pad.pluginid is null)",array($this->poiid,$id,$this->poiid));
// etwas übersichtlicher für MySQL 4.1 
// where s.seminar_id=? and p.pluginid not in (select pluginid from plugins_activated pad where pad.poiid=? and state='off'
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		// echo ("keine aktivierten Plugins<br>");
    		return array();
    	}
    	else {
    		$plugins = array();
    		while (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
    			// Klasse instanziieren
    			$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
            	$plugin->setPluginid($result->fields("pluginid"));
            	$plugin->setPluginname($result->fields("pluginname"));
            	$plugin->setUser($this->getUser());
            	$plugin->setActivated(true);
            	$plugins[] = $plugin;
    			
            	$result->MoveNext();
        	}    
        	$result->Close();
        	return $plugins; 
    	}
    	
    }
    
    /**
     * Returns all registered and deactivated plugins
     * @return a list of deactivated plugins
     */
    function getAllDeActivatedPlugins(){
    	$plugins = array();
		// plugins default activations is not useful, just search in plugins_activated    	    	
    	$result = &$this->connection->execute("SELECT p.* FROM plugins p left join plugins_activated a on p.pluginid=a.pluginid where p.plugintype='Standard' and (a.pluginid is null or a.poiid<>?) and a.state='off'", array($this->poiid));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		return array();
    	}
    	else {
    		while (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
            	// Klasse instanziieren
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
            	$plugin->setPluginid($result->fields("pluginid"));
            	$plugin->setPluginname($result->fields("pluginname"));
            	$plugin->setActivated(false);
            	$plugin->setUser($this->getUser());
            	$plugins[] = $plugin;
            	
            	$result->MoveNext();
        	}    
        	$result->Close();
        	return $plugins; 
    	}
    }
    
    /**
     * saves a plugin and its active state
     * @param $plugin the plugin to save
     */
    function savePlugin($plugin){
    	parent::savePlugin($plugin);
	    if (is_object($plugin) && is_subclass_of($plugin,'AbstractStudIPStandardPlugin')){
    		// get state
    		if ($plugin->isActivated()){
    			$state = "on";
    		} 
    		else {
    			$state = "off";
    		}
    		// save active state
    		$this->connection->execute("replace into plugins_activated (pluginid,poiid,state) values (?,?,?)", array($plugin->getPluginId(), $this->poiid,$state));
    	}
    	else {
    		// TODO: richtige Fehlerbehandlung
    		echo ("ERROR: kein gültiger Parameter<br>");
    		echo ("<pre>");
    		print_r($plugin);
    		echo ("</pre>");
    	}
    }
    
    function getPlugin($id){
    	//TODO: Wieso hier ein Join? Wird das so noch benötigt?
    	$result = &$this->connection->execute("Select p.* from plugins p left join plugins_activated a on p.pluginid=a.pluginid where p.pluginid=? and p.plugintype='Standard' and (a.poiid=? or (a.pluginid is null))",array($id, $this->poiid));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		return null;
    	}
    	else {
    		if (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
            	// Klasse instanziieren
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
            	$plugin->setPluginid($result->fields("pluginid"));
            	$plugin->setPluginname($result->fields("pluginname"));
            	$plugin->setUser($this->getUser());
        	}    
        	$result->Close();
        	return $plugin; 
    	}
    }
    
    function deinstallPlugin($plugin){
	    parent::deinstallPlugin($plugin);
	    // kill the activation information
	    $this->connection->execute("delete from plugins_default_activations where pluginid=?",array($plugin->getPluginid()));
    }
    
    /**
    * Save the default activations for a plugin
    * @param $plugin for which the default activation should be saved
    * @param $instituteids array of ids of the institutes for which the plugin should be activated as default
    * @return true - successful operation
    		  false - operation not successful
    */
    function saveDefaultActivations($plugin,$instituteids){
    	if (is_a($plugin,"AbstractStudIPStandardPlugin") || !is_array($instituteids)){
    		$this->connection->execute("delete from plugins_default_activations where pluginid=?", array($plugin->getPluginid()));
    		foreach ($instituteids as $instid) {
    			// now save every instituteid
    			$this->connection->execute("insert into plugins_default_activations (pluginid,institutid) values (?,?)",array($plugin->getPluginid(),$instid));	
    		}
    		return true;
    	}
    	else {
    		return false;
    	}
    }
    
    /**
    * Removes the default activations for a plugin
    * @param $plugin for which the default activation should be saved
    * @return true - successful operation
    		  false - operation not successful
    */
    function removeDefaultActivations($plugin){
    	if (is_a($plugin,"AbstractStudIPStandardPlugin") || !is_array($instituteids)){
    		$this->connection->execute("delete from plugins_default_activations where pluginid=?", array($plugin->getPluginid()));
    		return true;
    	}
    	else {
    		return false;
    	}
    }
    
    
    /**
    * Returns the default activations for a specific plugin
    * @param $plugin the plugin for which the default activation should be returned
    * @return the ids to the institutes
    */
    function getDefaultActivations($plugin){
    	if (is_a($plugin,"AbstractStudIPStandardPlugin")){
    		$result =& $this->connection->execute("select * from plugins_default_activations where pluginid=?", array($plugin->getPluginid()));
    		if (!$result){
    			// error or no result
    			return array();
    		}
    		else {
    			// get the ids
    			$institutids = array();
    			while (!$result->EOF) {
    				$institutids[] = $result->fields("institutid");
    				$result->MoveNext();
    			}
    			$result->Close();
    			return $institutids;
    		}
    	}
    	return array();
    }
    
    /**
    * Returns the default activations for a specific poi
    * @param $poiid the poi for which the default activation should be returned
    * @return the plugins, which are activated for this poi
    */
    function getDefaultActivationsForPOI($poiid){
    	$result =& $this->connection->execute("select p.* from seminar_inst s inner join Institute i on i.Institut_id=s.institut_id inner join plugins_default_activations pa on i.fakultaets_id=pa.institutid or i.Institut_id=pa.institutid inner join plugins p on pa.pluginid=p.pluginid where s.seminar_id=?", array($poiid));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		// echo ("keine standardmäßig aktivierten Plugins<br>");
    		return array();
    	}
    	else {
    		$plugins = array();
    		while (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
    			// Klasse instanziieren
    			$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
            	$plugin->setPluginid($result->fields("pluginid"));
            	$plugin->setPluginname($result->fields("pluginname"));
            	$plugin->setUser($this->getUser());
            	$plugins[] = $plugin;
    			
            	$result->MoveNext();
        	}    
        	$result->Close();
        	return $plugins;
    	}
    }
}
?>