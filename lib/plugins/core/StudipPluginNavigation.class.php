<?php

/*
 * StudipPluginNavigation.class.php - menus for plugins
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class StudipPluginNavigation {

  protected $plugin;
  protected $displayname;
  protected $link;
  protected $icon;
  protected $submenu;


  function StudipPluginNavigation($displayname = '', $link = '', $icon = '') {
    $this->displayname = $displayname;
    $this->link = $link;
    $this->icon = $icon;
    $this->submenu = array();
  }


  function getPlugin() {
    return $this->plugin;
  }


  function setPlugin($plugin) {
    $this->plugin = $plugin;
    foreach ($this->submenu as $item)
      $item->setPlugin($plugin);
  }


  /**
   * Returns the displayname, usually used for creating a link
   */
  function getDisplayname(){
    return $this->displayname;
  }


  function setDisplayname($newdisplayname){
    $this->displayname = $newdisplayname;
    return $this;
  }


  function getLink(){
    return $this->link;
  }


  function setLink($newlink){
    $this->link = $link;
    return $this;
  }


  function getIcon(){
    return $this->icon;
  }


  function setIcon($newicon){
    $this->icon = trim($newicon);
    return $this;
  }


  function hasIcon(){
    return strlen($this->icon) > 0;
  }


  function getSubmenu(){
    return $this->submenu;
  }


  function addSubmenu(StudipPluginNavigation $subnavigation){
    $subnavigation->setPlugin($this->getPlugin());
    $this->submenu[] = $subnavigation;
    return $this;
  }

  function removeSubmenu(StudipPluginNavigation $subnavigation){
    $this->submenu = array_diff($this->submenu, $subnavigation);
    return $this;
  }

  /**
    * Löscht das komplette Untermenü
    */
  function clearSubmenu(){
    $this->submenu = array();
    return $this;
  }

  function getCurrentSubmenuItem() {
  }
}
