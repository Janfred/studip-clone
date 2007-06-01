#!/usr/bin/php -q
<?php
/*
 * migrate.php - Migrations for Stud.IP
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'studip_cli_env.inc.php';
require_once 'lib/migrations/db_migration.php';
require_once 'lib/migrations/db_schema_version.php';
require_once 'lib/migrations/migrator.php';

if (isset($_SERVER["argv"])) {

  # check for command line options
  $options = getopt('m:t:v');
  if ($options === false) {
    exit(1);
  }

  # check for options
  $path = $STUDIP_BASE_PATH.'/db/migrations';
  $verbose = false;
  $target = NULL;

  foreach ($options as $option => $value) {
    switch ($option) {

      case 'm': $path = $value; break;

      case 't': $target = (int) $value; break;

      case 'v': $verbose = true; break;

    }
  }

  $version =& new DBSchemaVersion('studip');
  $migrator =& new Migrator($path, $version, $verbose);
  $migrator->migrate_to($target);
}
