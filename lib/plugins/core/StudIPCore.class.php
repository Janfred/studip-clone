<?php
/* vim: noexpandtab */
/**
 *  Base functionality for accessing data in the Stud.IP database,
 *  used by the plugin engine and plugins.
 *  @author  Dennis Reil, <Dennis.Reil@offis.de>
 *  @package pluginengine
 *  @subpackage core
 *  @version $Revision$
 */
class StudIPCore{

	/**
	 * Returns all Institutes, which are registered in the studip database
	 */
	function getInstitutes() {

		$db = DBManager::get();

		$cache = StudipCacheFactory::getCache();
		$key = 'plugins/StudIPCore/getInstitutes';

		$institutes = NULL;

		# caching is enabled, try to get the cached institutes
		if ($GLOBALS["PLUGINS_CACHING"]) {
			$cached_result = $cache->read($key);
			if ($cached_result !== FALSE) {
				# the cache contains strings not objects
				$institutes = unserialize($cached_result);
			}
		}


		# either we do not cache or we missed
		if (is_null($institutes)) {

			# get institutes from database
			$institutes = array();
			$sql = "SELECT LPAD(Institut_id,32,'0') as inst_id, ".
			       "Institut_id, Name, fakultaets_id FROM Institute ".
			       "ORDER BY fakultaets_id, inst_id, Name";
			foreach ($db->query($sql) as $row) {

				$instid   = $row["Institut_id"];
				$parentid = $row["fakultaets_id"];
				$name     = $row["Name"];

				// a new parent element
				if ($instid == $parentid) {

					$institute = new StudIPInstitute();
					$institute->setId($instid);
					$institute->setName($name);

					// institute already created
					if (is_object($institutes[$instid])) {
						$childs = $institutes[$instid]->getAllChildInstitutes();
						foreach ($childs as $child){
							$institute->addChild($child);
						}
					}
					$institutes[$instid] = $institute;
				}

				// a child institute
				else {
					$child = new StudIPInstitute();
					$child->setId($instid);
					$child->setName($name);
					$institute = $institutes[$parentid];
					if (!is_object($institute)) {
						$institute = new StudIPInstitute();
					}
					$institute->addChild($child);
					$institutes[$parentid] = $institute;
				}
			}

			# now cache the instituts
			if ($GLOBALS["PLUGINS_CACHING"]) {
				$cache->write($key, serialize($institutes),
											$GLOBALS['PLUGINS_CACHE_TIME']);
			}
		}

		return $institutes;
	}

	function getInstituteByFacultyId($facultyid) {

		$db = DBManager::get();

		$cache = StudipCacheFactory::getCache();
		$key = 'plugins/StudIPCore/getInstituteByFacultyId/' . $facultyid;

		$institutes = NULL;

		# caching is enabled, try to get the cached institutes
		if ($GLOBALS["PLUGINS_CACHING"]) {
			$cached_result = $cache->read($key);
			if ($cached_result !== FALSE) {
				# the cache contains strings not objects
				$institutes = unserialize($cached_result);
			}
		}


		# either we do not cache or we missed
		if (is_null($institutes)) {

			$stmt = $db->prepare("SELECT Institut_id, Name, fakultaets_id ".
			                     "FROM Institute ".
			                     "WHERE fakultaets_id=? ".
			                     "AND Institut_id <> fakultaets_id");

			$stmt->execute(array($facultyid));

			$institutes = array();
			while ($row = $stmt->fetch()) {
				$instid = $row["Institut_id"];
				$name = $row["Name"];

				$institute = new StudIPInstitute();
				$institute->setId($instid);
				$institute->setName($name);

				$institutes[] = $institute;
			}

			# now cache the instituts
			if ($GLOBALS["PLUGINS_CACHING"]) {
				$cache->write($key, serialize($institutes),
											$GLOBALS['PLUGINS_CACHE_TIME']);
			}
		}

		return $institutes;
	}

	function getInstitute($instituteid) {

		$db = DBManager::get();

		$cache = StudipCacheFactory::getCache();
		$key = 'plugins/StudIPCore/getInstitute/' . $instituteid;

		$institute = NULL;

		# caching is enabled, try to get the cached institutes
		if ($GLOBALS["PLUGINS_CACHING"]) {
			$cached_result = $cache->read($key);
			if ($cached_result !== FALSE) {
				# the cache contains strings not objects
				$institute = unserialize($cached_result);
			}
		}


		# either we do not cache or we missed
		if (is_null($institute)) {


			$stmt = $db->prepare("SELECT Institut_id, Name, fakultaets_id ".
			                     "FROM Institute ".
			                     "WHERE Institut_id=?");

			$stmt->execute(array($instituteid));

			$institute = array();
			if ($row = $stmt->fetch()) {
				$instid   = $row["Institut_id"];
				$parentid = $row["fakultaets_id"];
				$name     = $row["Name"];

				$institute = new StudIPInstitute();
				$institute->setId($instid);
				$institute->setName($name);

				// Childs abfragen und einfügen
				if ($parentid == $instid) {
					foreach ($this->getInstituteByFacultyId($parentid) as $child) {
						$institute->addChild($child);
					}
				}
			}

			# now cache the institute
			if ($GLOBALS["PLUGINS_CACHING"]) {
				$cache->write($key, serialize($institute),
											$GLOBALS['PLUGINS_CACHE_TIME']);
			}
		}

		return $institute;
	}


	/**
	 * Returns all semester registered in the stud.ip database
	 *
	 * @return unknown
	 */
	function getSemester() {

		$db = DBManager::get();

		$cache = StudipCacheFactory::getCache();
		$key = 'plugins/StudIPCore/getSemester';

		$semester = NULL;

		# caching is enabled, try to get the cached semester
		if ($GLOBALS["PLUGINS_CACHING"]) {
			$cached_result = $cache->read($key);
			if ($cached_result !== FALSE) {
				# the cache contains strings not objects
				$semester = unserialize($cached_result);
			}
		}


		# either we do not cache or we missed
		if (is_null($semester)) {

			$stmt = $db->prepare("SELECT * FROM semester_data ORDER BY beginn DESC");

			$stmt->execute();

			$semester = array();
			$current = time();
			while ($row = $stmt->fetch()) {

				if ($current >= $row["beginn"] &&
				    $current <= $row["ende"]) {
					$semester[] = array("id"              => $row["semester_id"],
					                    "name"            => $row["name"],
					                    "currentsemester" => true);
				}
				else {
					$semester[] = array("id"              => $row["semester_id"],
					                    "name"            => $row["name"],
					                    "currentsemester" => false);
				}

			}

			# now cache the semesters
			if ($GLOBALS["PLUGINS_CACHING"]) {
				$cache->write($key, serialize($semester),
											$GLOBALS['PLUGINS_CACHE_TIME']);
			}
		}

		return $semester;
	}

	function getSeminarsForInstitute($instituteid, $semesterid) {


		$db = DBManager::get();

		$cache = StudipCacheFactory::getCache();
		$key = 'plugins/StudIPCore/getSeminarsForInstitute/'.
		       $instituteid.'/'.$semesterid;

		$courses = NULL;

		# caching is enabled, try to get the cached courses
		if ($GLOBALS["PLUGINS_CACHING"]) {
			$cached_result = $cache->read($key);
			if ($cached_result !== FALSE) {
				# the cache contains strings not objects
				$courses = unserialize($cached_result);
			}
		}


		# either we do not cache or we missed
		if (is_null($courses)) {

			$stmt = $db->prepare("SELECT se.* FROM seminar_inst s ".
			                     "JOIN seminare se ON (s.seminar_id=se.seminar_id) ".
			                     "WHERE s.institut_id=? AND se.start_time = (".
			                     "SELECT beginn FROM semester_data ".
			                     "WHERE semester_id=?) ".
			                     "UNION SELECT * FROM seminare sem ".
			                     "WHERE sem.institut_id=? AND sem.start_time=(".
			                     "SELECT beginn FROM semester_data ".
			                     "WHERE semester_id=?)");
			$stmt->execute(array($instituteid, $semesterid,
			                     $instituteid, $semesterid));

			$courses = array();
			while ($row = $stmt->fetch()) {
				$courses[] = array("id"    => $row["Seminar_id"],
				                   "titel" => $row["Name"]);
			}

			# now cache the courses
			if ($GLOBALS["PLUGINS_CACHING"]) {
				$cache->write($key, serialize($courses),
											$GLOBALS['PLUGINS_CACHE_TIME']);
			}
		}

		return $courses;
	}
}
