<?
/*
resourcesControl.php - 0.8
Steuerung fuer Ressourcenverwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

// Default_Auth
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user"); //Noch anpassen!!!

include "seminar_open.php";

if ($RESOURCES_ENABLE)
	//Steuerung der Ressourcenverwaltung einbinden
	include($RELATIVE_PATH_RESOURCES."/resourcesControl.inc.php");
else {
	include ("header.php");
	require_once ($ABSOLUTE_PATH_STUDIP."msg.inc.php");
	?>
	<html>
		<head>
			<title>Stud.IP</title>
			<link rel="stylesheet" href="style.css" type="text/css">
		</head>
		<body bgcolor="#FFFFFF">
	<?
	parse_window ("error§Die Ressurcenverwaltung ist nicht eingebunden. Bitte aktivieren Sie sie in den Systemeinstellungen oder wenden Sie sich an den Systemadministrator.", "§",
				"Ressourcenverwaltung nicht eingebunden");
}
?>