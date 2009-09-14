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
  function setId($id);

  /**
   * Wird dieses Plugin in der Übersicht angezeigt?
   * Hat sich seit dem letzten Login etwas geändert?
   *
   * @param  string     gewählter Kurs bzw. Einrichtung
   * @param  int        letzter Loginzeitpunkt des Benutzers
   *
   * @return Navigation <description>
   */
  function getIconNavigation($semid, $lastlogin);

  /**
   * returns the score which the current user get's for activities in this
   * plugin
   *
   * @return integer    <description>
   */
  function getScore();
}
