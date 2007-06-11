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
require_once 'vendor/flexi/flexi.php';

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth",
                "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$auth->login_if(!$perm->have_perm("root"));
$perm->check("root");

$_language_path = init_i18n($_language);

include 'lib/include/html_head.inc.php';

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
$entries = $migrator->migration_classes();
ksort($entries);

foreach ($entries as $number => $entry)
{
    if ($current < $number)
    {
        list($file, $class) = $entry;
        require_once $file;

        $migration =& new $class();
        $migrations[] = array('number' => $number,
                              'name' => $class,
                              'description' => $migration->description());
    }
}

$template_factory =& new Flexi_TemplateFactory($STUDIP_BASE_PATH.'/templates');

$template =& $template_factory->open('web_migrate');
$template->set_attribute('assets', $GLOBALS['ASSETS_URL']);
$template->set_attribute('current', $current);
$template->set_attribute('migrations', $migrations);

echo $template->render();

include 'lib/include/html_end.inc.php';
?>
