<?php
/* vim: noexpandtab */
/**
 *  Base functionality for accessing data in the Stud.IP database, used by the plugin engine and plugins.
 *  @author  Dennis Reil, <Dennis.Reil@offis.de>
 *  @package pluginengine
 *  @subpackage core
 *  @version $Revision$
 */
class StudIPCore{

	/**
	* Returns all Institutes, which are registered in the studip database
	*/
	function getInstitutes(){
		$dbconn =& PluginEngine::getPluginDatabaseConnection();
		// Cache the query for 300 seconds
		if ($GLOBALS["PLUGINS_CACHING"]){
		   	$result =& $dbconn->CacheExecute($GLOBALS['PLUGINS_CACHE_TIME'],"select LPAD(Institut_id,32,'0') as inst_id,Institut_id, Name, fakultaets_id from Institute order by fakultaets_id,inst_id,Name");
		}
		else {
			$result =& $dbconn->Execute("select LPAD(Institut_id,32,'0') as inst_id,Institut_id, Name, fakultaets_id from Institute order by fakultaets_id,inst_id,Name");
		}
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

	function getInstituteByFacultyId($facultyid){
		$dbconn =& PluginEngine::getPluginDatabaseConnection();
		// Cache the query for 300 seconds
		if ($GLOBALS['PLUGINS_CACHING']){
			$result =& $dbconn->CacheExecute($GLOBALS['PLUGINS_CACHE_TIME'],"select Institut_id, Name, fakultaets_id from Institute where fakultaets_id=? and Institut_id <> fakultaets_id",array($facultyid));
		}
		else {
			$result =& $dbconn->Execute("select Institut_id, Name, fakultaets_id from Institute where fakultaets_id=? and Institut_id <> fakultaets_id",array($facultyid));
		}

		$institutes = array();
		while (!$result->EOF){
			$instid = $result->fields("Institut_id");
			$name = $result->fields("Name");
			$institute = new StudIPInstitute();
			$institute->setId($instid);
			$institute->setName($name);

			$institutes[] = $institute;
			$result->moveNext();
		}
		$result->Close();
		return $institutes;
	}

	function getInstitute($instituteid){
		$dbconn =& PluginEngine::getPluginDatabaseConnection();
		// Cache the query for 300 seconds
		if ($GLOBALS['PLUGINS_CACHING']){
			$result =& $dbconn->CacheExecute($GLOBALS['PLUGINS_CACHE_TIME'],"select Institut_id, Name, fakultaets_id from Institute where Institut_id=?",array($instituteid));
		}
		else {
			$result =& $dbconn->Execute("select Institut_id, Name, fakultaets_id from Institute where Institut_id=?",array($instituteid));
		}

		if (!$result->EOF){
			$instid = $result->fields("Institut_id");
			$parentid = $result->fields("fakultaets_id");
			$name = $result->fields("Name");
			$institute = new StudIPInstitute();
			$institute->setId($instid);
			$institute->setName($name);
			if ($parentid == $instid){
				// Childs abfragen und einfügen
				$institutes = $this->getInstituteByFacultyId($parentid);
				foreach ($institutes as $child){
					$institute->addChild($child);
				}
			}
		}
		$result->Close();
		return $institute;
	}


	/**
	 * Returns all semester registered in the stud.ip database
	 *
	 * @return unknown
	 */
	function getSemester(){
		$dbconn =& PluginEngine::getPluginDatabaseConnection();
		// Cache the query for 300 seconds
		if ($GLOBALS['PLUGINS_CACHING']){
    		$result =& $dbconn->CacheExecute($GLOBALS['PLUGINS_CACHE_TIME'],"select * from semester_data order by beginn desc");
		}
		else {
			$result =& $dbconn->Execute("select * from semester_data order by beginn desc");
		}

		$semester = array();
		$current = time();
		while (!$result->EOF){

			if ($current >= $result->fields("beginn") && $current <= $result->fields("ende")){
				$semester[] = array("id" => $result->fields("semester_id"),"name" => $result->fields("name"), "currentsemester" => true);
			}
			else {
				$semester[] = array("id" => $result->fields("semester_id"),"name" => $result->fields("name"),"currentsemester" => false);;
			}
			$result->moveNext();
		}
		$result->Close();
		return $semester;
	}

	function getSeminarsForInstitute($instituteid,$semesterid){
		$dbconn =& PluginEngine::getPluginDatabaseConnection();
		// Cache the query for 300 seconds
		if ($GLOBALS['PLUGINS_CACHING']){
			$result =& $dbconn->CacheExecute($GLOBALS['PLUGINS_CACHE_TIME'],"SELECT se.* FROM seminar_inst s join seminare se on (s.seminar_id=se.seminar_id) where s.institut_id=? and se.start_time = (select beginn from semester_data where semester_id=?) union select * from seminare sem where sem.institut_id=? and sem.start_time=(select beginn from semester_data where semester_id=?)",array($instituteid,$semesterid,$instituteid,$semesterid));
		}
		else {
			$result =& $dbconn->Execute("SELECT se.* FROM seminar_inst s join seminare se on (s.seminar_id=se.seminar_id) where s.institut_id=? and se.start_time = (select beginn from semester_data where semester_id=?) union select * from seminare sem where sem.institut_id=? and sem.start_time=(select beginn from semester_data where semester_id=?)",array($instituteid,$semesterid,$instituteid,$semesterid));
		}
		$courses = array();
		while (!$result->EOF){
			//if (empty($courses) || (!array_search($result->fields("Seminar_id"),$courses))){
			$courses[] = array("id" => $result->fields("Seminar_id"),"titel" => $result->fields("Name"));
			// }

			$result->moveNext();
		}
		$result->Close();
		return $courses;
	}
}
