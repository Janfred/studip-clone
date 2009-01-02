<?php
/*
 * datenschutz.php - privacy guidelines for Stud.IP
 * Copyright (C) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

page_open(array('sess' => 'Seminar_Session',
                'auth' => 'Seminar_Default_Auth',
                'perm' => 'Seminar_Perm',
                'user' => 'Seminar_User'));

$_language_path = init_i18n($_language);

$CURRENT_PAGE = _('Erläuterungen zum Datenschutz');

$template = $template_factory->open('privacy');
$layout   = $template_factory->open('layouts/base_without_infobox');
$template->set_layout($layout);

echo $template->render();
?>
