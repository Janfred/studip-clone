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
   * <MethodDescription>
   *
   * @return type       <description>
   */
  function getId();

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
   * Liefert die Änderungsmeldungen für die übergebenen ids zurück
   *
   * @param  type       letzter Loginzeitpunkt des Benutzers
   * @param  type       ein Array von Veranstaltungs- bzw. Institutionsids, zu
   *                    denen die Änderungsnachricht bestimmt werden soll.
   *
   * @return array      Änderungsmeldungen
   */
  function getChangeMessages($lastlogin, $ids);

  /**
   * <MethodDescription>
   *
   * @return string     <description>
   */
  function getChangeindicatoriconname();

  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   *
   * @return void
   */
  function setChangeindicatoriconname($newicon);

  /**
   * <MethodDescription>
   *
   * @param  boolean    optional, default: true
   *
   * @return void
   */
  function setShownInOverview($value = true);

  /**
   * returns the score which the current user get's for activities in this
   * plugin
   *
   * @return integer    <description>
   */
  function getScore();
}
