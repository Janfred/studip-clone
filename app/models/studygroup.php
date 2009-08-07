<?php

/*
 * Copyright (C) 2009 - André Klaßen <aklassen@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class StudygroupModel {
	function getInstalledPlugins() {
		$modules = array();

		// get standard-plugins (suitable for seminars)
		if ($GLOBALS['PLUGINS_ENABLE']){
			$plugins = PluginEngine::getPlugins('StandardPlugin');     // get all globally enabled plugins
			foreach ($plugins as $plugin ) {
				$modules[$plugin->getPluginClassName()] = $plugin->getPluginName();
			}
		}

		return $modules;
	}

	function getInstalledModules() {
		$modules = array();

		// get core modules
		$admin_modules = new AdminModules();

		foreach ($admin_modules->registered_modules as $key => $data) {
			$modules[$key] = $data['name'];
		}

		return $modules;
	}

	function getAvailability( $modules ) {
		$enabled = array();

		// get current activation-settings
		$data = Config::GetInstance()->getValue('STUDYGROUP_SETTINGS');
		$data2 = explode('|', $data);

		foreach ($data2 as $element) {
			list($key, $value) = explode(':', $element);
			$enabled[$key] = ($value) ? true : false;
		}

		if (!is_array($enabled)) {  // if not settings are there yet, set default
			foreach ($modules as $key => $name) {
				$enabled[$key] = false;
			}
		}

		return $enabled;
	}



	function getAvailableModules() {
		$modules = StudygroupModel::getInstalledModules();
		$enabled = StudygroupModel::getAvailability( $modules );

		$ret = array();

		foreach ($enabled as $key => $avail) {
			if ($avail && $modules[$key]) $ret[$key] = $modules[$key];
		}

		return $ret;
	}   

	function getAvailablePlugins() {
		$modules = StudygroupModel::getInstalledPlugins();
		$enabled = StudygroupModel::getAvailability( $modules );

		$ret = array();

		foreach ($enabled as $key => $avail) {
			if ($avail && $modules[$key]) $ret[$key] = $modules[$key];
		}

		return $ret;
	}   

	function getEnabledPlugins() {
		$enabled = array();

		if ($GLOBALS['PLUGINS_ENABLE']){
			$plugins = PluginEngine::getPlugins('StandardPlugin');     // get all globally enabled plugins
			foreach ($plugins as $plugin ) { 
				$enabled[$plugin->getPluginClassName()] = $plugin->isActivated();
			}
		}
		return $enabled;
	}   

	function getInstitutes() {
		$institues = array();

		// get faculties
		$stmt = DBManager::get()->query("SELECT Name, Institut_id, 1 AS is_fak,'admin' AS inst_perms
				FROM Institute WHERE Institut_id = fakultaets_id ORDER BY Name");
		while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$institutes[$data['Institut_id']] = array (
					'name' => $data['Name'],
					'childs' => array()
					);
			// institutes for faculties
			$stmt2 = DBManager::get()->query("SELECT a.Institut_id, a.Name FROM Institute a
					WHERE fakultaets_id='". $data['Institut_id'] ."' 
					AND a.Institut_id !='". $data['Institut_id'] . "' ORDER BY Name");
			while ($data2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
				$institutes[$data['Institut_id']]['childs'][$data2['Institut_id']] = $data2['Name'];
			}
		}   

		return $institutes;
	}

	function accept_user($username, $sem_id) {
		$stmt = DBManager::get()->query("SELECT asu.user_id FROM admission_seminar_user asu 
			LEFT JOIN auth_user_md5 au ON (au.user_id=asu.user_id) 
			WHERE au.username='$username' AND asu.seminar_id='". $sem_id ."'");
		if ($data = $stmt->fetch()) {
			$accept_user_id = $data['user_id'];

			DBManager::get()->query("INSERT INTO seminar_user SET user_id='".$accept_user_id."', seminar_id='".$sem_id."',
				status='autor', position=0, gruppe=0, admission_studiengang_id=0, notification=0, mkdate=NOW(), comment='', visible='yes'");

			DBManager::get()->query("DELETE FROM admission_seminar_user WHERE user_id='".$accept_user_id."' AND seminar_id='".$sem_id."'");
		}
	}

	function deny_user($username, $sem_id) {
		DBManager::get()->query("DELETE FROM admission_seminar_user WHERE user_id='". get_userid($username) ."' AND seminar_id='".$sem_id."'");
	}

	function promote_user($username, $sem_id, $perm) 
	{
		DBManager::get()->query( "UPDATE seminar_user SET status = '$perm' WHERE Seminar_id = '$sem_id' AND user_id = '". get_userid($username) ."'");
	}

	function remove_user($username, $sem_id, $perm) 
	{
		DBManager::get()->query("DELETE FROM seminar_user WHERE Seminar_id = '$sem_id' AND user_id = '". get_userid($username) ."'");
	}
}
