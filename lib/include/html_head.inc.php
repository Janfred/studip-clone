<?php
# Lifter002: TODO
// vim: noexpandtab
/**
* html_head.inc.php
*
* output of html-head for all Stud.IP pages<br>
* parameter <b>$_include_stylesheet</b>
* <ul><li>if not set, use default stylesheet</li>
* <li>if empty, use no stylesheet</li>
* <li>else use set stylesheet</li></ul><br>
* parameter <b>$_html_head_title</b><br>
* <ul><li>if not set use default</li>
* <li> if set use as title </li></ul>
*
* @author		Stefan Suchi <suchi@data-quest.de>
* @version		$Id$
* @access		public
* @package		studip_core
* @modulegroup	library
* @module		html_head.inc.php
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define('PHPDOC_DUMMY',true);

# necessary if you want to include html_head.inc.php in function/method scope
global  $AUTH_LIFETIME, $FAVICON, $HTML_HEAD_TITLE;

global  $auth, $user;

global  $_html_head_title,
        $_include_additional_header,
        $_include_extra_stylesheet,
        $_include_stylesheet,
        $messenger_started,
        $my_messaging_settings,
        $seminar_open_redirected;


// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// html_head.inc.php
// Copyright (c) 2002 Stefan Suchi <suchi@data-quest.de>
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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<? if (in_array(basename($_SERVER['SCRIPT_NAME']), array('dispatch.php', 'plugins.php'))) : ?>
		<base href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>">
		<? endif ?>
		<? if (basename($_SERVER['SCRIPT_NAME']) !== 'logout.php' && $AUTH_LIFETIME > 0) : ?>
			<meta http-equiv="REFRESH" CONTENT="<?= $AUTH_LIFETIME * 60 ?>; URL=<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>logout.php">
		<? endif ?>

<?
echo "\t\t".'<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">'. "\n";

if (isset($FAVICON))
		echo "\t\t".'<link rel="SHORTCUT ICON" href="'. $FAVICON.'">'."\n";

if (!isset($_html_head_title))  // if not set, use default title
	$_html_head_title = ($HTML_HEAD_TITLE) ? $HTML_HEAD_TITLE : 'Stud.IP';
echo "\t\t".'<title>'.$_html_head_title.'</title>'."\n";

if (!isset($_include_stylesheet))  // if not set, use default stylesheet
	$_include_stylesheet = 'style.css';

if ($_include_stylesheet != '')  // if empty, use no stylesheet
	echo "\t\t".'<link rel="stylesheet" href="'.$GLOBALS['ASSETS_URL'].'stylesheets/'.$_include_stylesheet.'" type="text/css">'."\n";

if (isset ($_include_extra_stylesheet))
	echo "\t\t".'<link rel="stylesheet" href="'.$GLOBALS['ASSETS_URL'].'stylesheets/'.$_include_extra_stylesheet.'" type="text/css">'."\n";
if (isset ($_include_additional_header)){
	echo "\t\t" . $_include_additional_header . "\n";
}
echo "\t\t".'<link rel="stylesheet" href="'.$GLOBALS['ASSETS_URL'].'stylesheets/header.css" type="text/css">'."\n";

unset ($_include_extra_stylesheet);
unset ($_include_stylesheet);
unset ($_html_head_title);
unset ($_include_additional_header);

//start messenger, if set
if (($my_messaging_settings['start_messenger_at_startup']) && ($auth->auth['jscript']) && (!$messenger_started) && (!$seminar_open_redirected)) {

	?>
	<script language="Javascript">
		{fenster=window.open("studipim.php","im_<?=$user->id?>","scrollbars=yes,width=400,height=300","resizable=no");}
	</script>
	<?
	$messenger_started = TRUE;
}
?>
	<?= Assets::script('prototype', 'scriptaculous', 'application') ?>
	</head>
	<body>
      <div id="ajax_notification" style="display: none;"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/ajax_indicator.gif" alt="AJAX indicator" align="absmiddle">&nbsp;Working...</div>
