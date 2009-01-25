<?php
/*
* AbstractStudienmodulManagement.class.php
*
* Copyright (C) 2008 - André Noack <noack@data-quest.de>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 2 of
* the License, or (at your option) any later version.
*/


/**
* AbstractStudienmodulManagement Core Plugin.
*
* @author    anoack
* @copyright (c) Authors
* @version   $Id$
*/

class AbstractStudienmodulManagementPlugin extends AbstractStudIPCorePlugin {
	
	protected $modulecatalog_navigation;
	protected $mymodules_navigation;
	
	public $show_study_courses = false;
	
	function __construct() {
		parent::AbstractStudIPCorePlugin();
	}
	
	function setNavigation($navigation){
		$navigation->setPlugin($this);
		$this->modulecatalog_navigation = $navigation;
	}
	
	function setMyModulesNavigation($navigation){
		$navigation->setPlugin($this);
		$navigation->setCommand('showMyModules');
		$this->mymodules_navigation = $navigation;
	}
	
	function getPluginname() {
		return 'StudienmodulManagement';
	}
	
	function display_action($action) {

		$action = strtolower($action);
		if($action == 'actionshowmodule'){
			list($module_id, $semester_id) = explode('/', $this->unconsumed_path);
			$GLOBALS['CURRENT_PAGE'] = _("Details eines Studienmodul") . ' - ' . $this->getModuleTitle($module_id, $semester_id);
		} else {
			$GLOBALS['CURRENT_PAGE'] = $this->modulecatalog_navigation->getDisplayName();
		}
		include 'lib/include/html_head.inc.php';
		include 'lib/include/header.php';
		if($action != 'actionshowmodule') include 'lib/include/links_seminare.inc.php';

		$this->$action($module_id, $semester_id);

		// close the page
		include 'lib/include/html_end.inc.php';
		page_close();
	}
	
	function getCurrentView(){
		if($this->getCommand() == 'showMyModules'){
			return 'my_modules'; 
		}
		
		$view = "plugin_" . $this->getPluginId();
		if(is_object($this->modulecatalog_navigation)){
			$submenu = (array)$this->modulecatalog_navigation->getSubMenu();
			if(!count($submenu)) $submenu[0] = $this->modulecatalog_navigation;
			foreach ($submenu as $key => $submenuitem){
				if($submenuitem->isActive()){
					$view = "plugin_" . $this->getPluginId() . "_" . $key;
					break;
				}
			}
		}
		return $view;
	}
	
	function getModuleCatalogNavigation(){
		$structure = array();
		if(is_object($this->modulecatalog_navigation)){
			$structure["plugin_" . $this->getPluginid()] = array('topKat' => '',
																'name' => $this->modulecatalog_navigation->getDisplayName(),
																'link' => $this->modulecatalog_navigation->getLink(),
			 													'active' => false);
			$submenu = (array)$this->modulecatalog_navigation->getSubMenu();
			if(!count($submenu)) $submenu[0] = $this->modulecatalog_navigation;
			foreach ($submenu as $key => $submenuitem){
				$structure["plugin_" . $this->getPluginId() . "_" . $key] = array (
																			'topKat' => "plugin_" . $this->getPluginId(),
																			'name' => $submenuitem->getDisplayname(),
																			'link' => $submenuitem->getLink(),
																			'active' => false);
			}
		}
		return $structure;
	}
	
	function getMyModulesNavigation(){
		return $this->mymodules_navigation;
	}
	
	function actionShow() {
	}
	
	function actionShowMyModules() {
	}
	
	function actionShowModule($module_id, $semester_id = ''){
	}
	
	function getModuleTitle($module_id, $semester_id = ''){
		return "";
	}
	
	function getModuleDescription($module_id, $semester_id = ''){
		return "";
	}
	
	function getModuleInfoIcon($module_id, $semester_id = ''){
		return "";
	}
	
	function isModule($module_id){
		return false;
	}
	
	function getModulesForSeminar($seminar_id){
		return array();
	}
	
	function getModuleDescriptionLink($module_id, $semester_id = ''){
		return PluginEngine::GetLink($this, array(), "showmodule/$module_id/$semester_id");
	}
	
	function getModuleDescriptionUrl($module_id, $semester_id = ''){
		return PluginEngine::GetUrl($this, array(), "showmodule/$module_id/$semester_id");
	}
	
	function actionShowAdministrationPage() {
	}
	
	function getModuleSearchText(){
	}
	
	function getModuleSearchLink(){
	}
	
	function getModuleSearchIcon(){
	}
}

?>