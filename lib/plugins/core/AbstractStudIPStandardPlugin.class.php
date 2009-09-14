<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

// vim: noexpandtab
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class AbstractStudIPStandardPlugin extends AbstractStudIPLegacyPlugin
  implements StandardPlugin {

	// Id, der dieses Plugin zugeordnet ist (bspw. Veranstaltung oder Institution)
	var $id;

	// wird dieses Plugin in der Übersicht (z.B. meine_seminare) angezeigt
	var $overview;

	// relativer Name des Icons für Änderungen an diesem Plugin
	var $changeindicatoriconname;

	// Anzeigetexte beim Ein- und Ausschaltes des Plugins
	var $pluginadmininfo;

	function AbstractStudIPStandardPlugin() {
		parent::AbstractStudIPLegacyPlugin();
		$this->changeindicatoriconname = "";
		$this->overview = false;
		// create the standard AdminInfo
		$this->admininfo = new AdminInfo();
		$this->user->permission->setPoiid($this->getId());
	}

	/**
	 * Sets the navigation of this plugin.
	 *
	 * @deprecated
	 */
	function setNavigation(StudipPluginNavigation $navigation) {
		// prepend copy of navigation to its sub navigation
		$first_item_name = key($navigation->getSubNavigation());
		$navigation_copy = clone $navigation;
		$navigation_copy->clearSubmenu();
		$navigation->insertSubNavigation('self', $first_item_name, $navigation_copy);
		$navigation->setTitle($this->getDisplayTitle());

		parent::setNavigation($navigation);

		if (Navigation::hasItem('/course')) {
			Navigation::addItem('/course/' . $this->getPluginclassname(), $navigation);
		}
	}

	function setId($id) {
		$this->id = $id;
	}

	function getId() {
		if ($this->id === NULL) {
			$this->id = $_SESSION['SessSemName'][1];
		} else {
			$this->id = str_replace($_SESSION["SessSemName"]["class"], '', $this->id);
	 	}
		return $this->id;
	}

	/**
	 * Wird dieses Plugin in der Übersicht angezeigt?
	 * Hat sich seit dem letzten Login etwas geändert?
	 *
	 * @param  string     gewählter Kurs bzw. Einrichtung
	 * @param  int        letzter Loginzeitpunkt des Benutzers
	 *
	 * @return Navigation <description>
	*/
	function getIconNavigation($semid, $lastlogin) {
		$this->setId($semid);

		if ($this->isShownInOverview()) {
			$navigation = new Navigation('', PluginEngine::getURL($this));

		  	if ($this->hasChanged($lastlogin)) {
				$navigation->setImage($this->getChangeindicatoriconname(),
						array('title' => $this->getOverviewMessage(true)));
		  	} else {
				$navigation->setImage($this->getPluginiconname(),
						array('title' => $this->getOverviewMessage(false)));
		  	}
		}

		return $navigation;
	}

	/**
	 * Hat sich seit dem letzten Login etwas geändert?
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 *
	 * @deprecated
	 */
	function hasChanged($lastlogin) {
		return false;
	}

	/**
	 * Nachricht für tooltip in der Übersicht
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 *
	 * @deprecated
	 */
	function getOverviewMessage($has_changed = false) {
		return $this->getPluginname() . ($has_changed ? ' ' . _("geändert") : '');
	}

	/**
	 * Wird dieses Plugin in der Übersicht angezeigt?
	 *
	 * @deprecated
	 */
	function isShownInOverview() {
		return $this->overview;
	}

	/**
	 * Getter- und Setter für die Attribute
	 *
	 * @deprecated
         */
	function getChangeindicatoriconname() {
		return $this->getPluginURL() . '/' . $this->changeindicatoriconname;
	}

	function setChangeindicatoriconname($icon) {
		$this->changeindicatoriconname = $icon;
	}

	function setShownInOverview($value = true) {
		$this->overview = $value;
	}

	/**
	 * Liefert die Administrationsinformationen zu diesem Plugin zurück
	 */
	function getPluginAdminInfo() {
		return $this->pluginadmininfo;
	}

	/**
	 * setzt neue Administrationsinformationen zu diesem Plugin
	 *
	 * @param AdminInfo
	 */
	function setPluginAdminInfo(AdminInfo $admininfo) {
		$this->pluginadmininfo = $admininfo;
	}

	/**
	 * returns the score which the current user get's for activities in this plugin
	 */
	function getScore()  {
		return 0;
	}

	/**
	 * Returns the state of the plugin.
	 */
	function isActivated() {
		$plugin_manager = PluginManager::getInstance();

		return $plugin_manager->isPluginActivated($this->getPluginid(), $this->getId());
	}

	/**
	 * Sets the state of the plugin.
	 *
	 * @param boolean $value
	 */
	function setActivated($value) {
		$plugin_manager = PluginManager::getInstance();

		$plugin_manager->setPluginActivated($this->getPluginid(), $this->getId(), $value);
	}

	/**
	 * This method sets everything up to perform the given action and
	 * displays the results or anything you want to.
	 *
	 * @param  string the name of the action to accomplish
	 *
	 * @return void
	 */
	function display_action($action) {
		mark_public_course();

		$GLOBALS['CURRENT_PAGE'] =
			$_SESSION['SessSemName']['header_line'] . ' - ' . $this->getDisplayTitle();

		parent::display_action($action);
	}
}
