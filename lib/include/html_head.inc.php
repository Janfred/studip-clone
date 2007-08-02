<?php
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
<?
switch (basename($_SERVER['SCRIPT_NAME'])) {
	case 'logout.php':
		break;
	case 'sendfile.php' :
		echo "\t\t".'<base href="' . $GLOBALS['ABSOLUTE_URI_STUDIP'] . '">'. "\n";
	default:
	if ($AUTH_LIFETIME) {
		echo "\t\t".'<meta http-equiv="REFRESH" CONTENT="'.$AUTH_LIFETIME*60 .'; URL=logout.php">'. "\n";
	}
}

echo "\t\t".'<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">'. "\n";
echo "\t\t".'<meta name="copyright" content="Stud.IP-Crew (crew@studip.de)">'. "\n";

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
	<? if ( $auth->auth['jscript']) : ?>
		<script src="<?= $GLOBALS['ASSETS_URL'] ?>javascripts/prototype.js" type="text/javascript"></script>
		<script src="<?= $GLOBALS['ASSETS_URL'] ?>javascripts/scriptaculous.js" type="text/javascript"></script>
		<script type="text/javascript" language="javascript">
		// <![CDATA[
		Event.observe(window, 'load', function() {
			document.getElementsByClassName("effect_highlight").each(
				function(e) { new Effect.Highlight(e) }
			);
		});
		// ]]>
		</script>
	<? endif ?>

	</head>
	<body>
