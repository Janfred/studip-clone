<?php
# Lifter007: TODO

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * NOTE: This interface will change significantly in Stud.IP 1.11.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

interface StandardPlugin {

  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   *
   * @return void
   */
  function setId($newid);

  /**
   * Hat sich seit dem letzten Login etwas geändert?
   *
   * @param  type       letzter Loginzeitpunkt des Benutzers
   *
   * @return boolean    <description>
   */
  function hasChanged($lastlogin);

  /**
   * Nachricht für tooltip in der Übersicht
   *
   * @param  type       letzter Loginzeitpunkt des Benutzers; optional,
   *                    default: false
   *
   * @return string     <description>
   */
  function getOverviewMessage($has_changed = FALSE);

  /**
   * Wird dieses Plugin in der Übersicht angezeigt?
   *
   * @return boolean    <description>
   */
  function isShownInOverview();

  /**
   * <MethodDescription>
   *
   * @return string     <description>
   */
  function getChangeindicatoriconname();

  /**
   * returns the score which the current user get's for activities in this
   * plugin
   *
   * @return integer    <description>
   */
  function getScore();
}
