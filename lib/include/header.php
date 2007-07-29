<?php
/**
* header
*
* head line of Stud.IP
*
*
* @author		Stefan Suchi <suchi@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	visual
* @module		header.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// header.php
// head line of Stud.IP
// Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+
require_once ('lib/classes/HeaderController.class.php');

if ($GLOBALS['SHOW_TERMS_ON_FIRST_LOGIN']) {
	require_once ('lib/terms.inc.php');
	check_terms($user->id, $GLOBALS['language_path']);
}

if ($GLOBALS['USER_VISIBILITY_CHECK']) {
	require_once('lib/user_visible.inc.php');
	first_decision($GLOBALS['user']->id);
}

$header_controller = new HeaderController();
$header_controller->help_keyword = $GLOBALS['HELP_KEYWORD'];
$header_controller->current_page = $GLOBALS['CURRENT_PAGE'];
$header_template =& $GLOBALS['template_factory']->open('header');
$header_controller->fillTemplate($header_template);
echo $header_template->render();

include 'lib/include/check_sem_entry.inc.php'; //hier wird der Zugang zum Seminar ueberprueft
//<!-- $Id$ -->
?>
