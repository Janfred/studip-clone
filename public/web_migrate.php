<?php
/*
 * web_migrate.php - web-based migrator for Stud.IP
 * Copyright (C) 2007  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/migrations/db_migration.php';
require_once 'lib/migrations/db_schema_version.php';
require_once 'lib/migrations/migrator.php';
require_once 'lib/visual.inc.php';

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth",
                "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$auth->login_if(!$perm->have_perm("root"));
$perm->check("root");

$_language_path = init_i18n($_language);

$CURRENT_PAGE = _("Datenbank Migration");

include 'lib/include/html_head.inc.php';
include 'lib/include/header.php';

$path = $STUDIP_BASE_PATH.'/db/migrations';
$verbose = false;
$target = NULL;

$version =& new DBSchemaVersion('studip');
$migrator =& new Migrator($path, $version, $verbose);

if (isset($_REQUEST['start']) || isset($_REQUEST['start_x']))
{
    $migrator->migrate_to($target);
}

$current = $version->get();
$migrations = $migrator->relevant_migrations($target);

$template =& $template_factory->open('web_migrate');
$template->set_attribute('assets', $GLOBALS['ASSETS_URL']);
$template->set_attribute('current', $current);
$template->set_attribute('migrations', $migrations);

echo $template->render();

include 'lib/include/html_end.inc.php';
?>
