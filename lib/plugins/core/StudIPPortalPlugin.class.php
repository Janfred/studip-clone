<?php
# Lifter007: TODO
# Lifter003: TODO

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

interface StudIPPortalPlugin {

  /**
   * Used to show an overview on the start page or portal page
   *
   * @param  boolean    is the user already logged in? optional, default: true
   *
   * @return type       <description>
   */
  function showOverview($unauthorizedview = TRUE);

  /**
   * Does this plugin have an administration page, which should be shown?
   * This default implementation only shows it for admin or root user.
   *
   * @return boolean    <description>
   */
  function hasAdministration();

  /**
   * Does the plugin have a view for a user not currently logged in.
   *
   * @return boolean    <description>
   */
  function hasUnauthorizedView();

  /**
   * Does the plugin have a view for a currently logged in user.
   *
   * @return boolean    <description>
   */
  function hasAuthorizedView();
}
