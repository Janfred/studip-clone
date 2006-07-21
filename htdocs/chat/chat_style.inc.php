<?php
/**
* chat_style.inc.php
*
* output of stylesheet
*
* @author		André Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @package		Chat
* @modulegroup	chat_modules
* @module		chat_style
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// chat_style.inc.php
// Copyright (c) 2002 André Noack <andre.noack@gmx.net>
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
<style type="text/css">
	<!--
	A:visited {	color:#3333BB;	text-decoration : none;	font-family: Arial, Helvetica, sans-serif;}
	A:link {	color:#3333BB;	text-decoration : none;	font-family: Arial, Helvetica, sans-serif;}
	A:hover {	color: #FF3333;	text-decoration : none;	font-family: Arial, Helvetica, sans-serif;}
	A:active {color: #FF3333; text-decoration : none; font-family: Arial, Helvetica, sans-serif;}
	TABLE.blank {	background-color: white;}
	TD.blank {background-color: #FFFFFF;}
	th   {border:0px solid #000000; background:#B5B5B5 url('<?= $GLOBALS['ASSETS_URL'] ?>images/steelgraudunkel.gif'); color:#FFFFFF; font-family:Arial, Helvetica, sans-serif; background-color:#B5B5B5  }
	p, td, form, ul {font-family: Arial, Helvetica, sans-serif;	color: #000000 }
	h1, h2, h3 {font-family: Arial, Helvetica, sans-serif;	color: #990000;	font-weight: bold; }
	table.header { background-image: url('<?= $GLOBALS['ASSETS_URL'] ?>images/fill1.gif');}
	TD.topic {border:0px solid #000000; background: url('<?= $GLOBALS['ASSETS_URL'] ?>images/fill1.gif'); color:#FFFFFF; font-family:Arial, Helvetica, sans-serif; background-color:#4A5681  }
	BODY {background-color:#EEEEEE;background-image:url('<?= $GLOBALS['ASSETS_URL'] ?>images/steel1.jpg');font-family: Arial, Helvetica, sans-serif;color: #000000;}
	.chat {background-color:#EEEEEE;background-image:url('<?= $GLOBALS['ASSETS_URL'] ?>images/steel1.jpg');font-family: Arial, Helvetica, sans-serif;color: #000000;}
	.quote {margin-left: 20px; padding:3px; margin-right: 8em; border: 1px solid black; background: none; background-color: #EEEEEE;}
	-->
</style>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<meta name="copyright" content="Stud.IP-Crew (crew@studip.de)">
