<?php
/**
*  Base functionality for accessing data in the Stud.IP database, used by the plugin engine and plugins.
*  @author  Dennis Reil, <Dennis.Reil@offis.de>
*  @package pluginengine
*  @version $Revision$
*/
class StudIPCore{
	
	/**
	* Returns all Institutes, which are registered in the studip database
	*/
	function getInstitutes(){
		$dbconn =& PluginEngine::getPluginDatabaseConnection();
		// Cache the query for 300 seconds
    	$result =& $dbconn->CacheExecute(300,"select Institut_id, Name, fakultaets_id from Institute order by Name");
    	
    	$institutes = array();
    	while (!$result->EOF){
    		$instid = $result->fields("Institut_id");
    		$parentid = $result->fields("fakultaets_id");
    		$name = $result->fields("Name");
    		
    		if ($instid == $parentid){
    			// a new parent element
    			$institute = new StudIPInstitute();
    			$institute->setId($instid);
    			$institute->setName($name);
    			if (is_object($institutes[$instid])){
    				// institute already created
    				$childs = $institutes[$instid]->getAllChildInstitutes();
    				foreach ($childs as $child){
    					$institute->addChild($child);
    				}
    			}
    			$institutes[$instid] = $institute;
    		}
    		else {
    			// a child institute
    			$child = new StudIPInstitute();
    			$child->setId($instid);
    			$child->setName($name);
    			$institute = $institutes[$parentid];
    			if (!is_object($institute)){
    				$institute = new StudIPInstitute();
    			}
    			$institute->addChild($child);
    			$institutes[$parentid] = $institute;
    		}
    		$result->MoveNext();
    	}
    	$result->Close();
    	return $institutes;
	}
}
?>