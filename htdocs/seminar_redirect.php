<?php
/*
seminar_redirect.php - Setzt Sessionvariablen und springt danach direkt weiter ins Forum oder andere Teile von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>, André Noack <andre.noack@gmx.net>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");

// wichtiger Teil aus seminar_open.php
	
	if ($auth->is_authenticated() && $user->id != "nobody") {
		if ($SessionStart > $CurrentLogin) {      // gerade eingeloggt
			$LastLogin=$CurrentLogin;
			$CurrentLogin=$SessionStart;
			$user->register("loginfilelast");
			$user->register("loginfilenow");
			$user->register("CurrentLogin");
			$user->register("LastLogin");
		}
  }
	if ($SessionStart==0) { 
		$SessionStart=time(); 
		$SessionSeminar="";
		$SessSemName="";
		$sess->register("SessionStart");
		$sess->register("SessionSeminar");
		$sess->register("SessSemName");
	}		


if (isset($auswahl) && $auswahl!="") {
	// dieses Seminar wurde gerade eben betreten
	$SessionSeminar="$auswahl";
	$db=new DB_Seminar;
	$db->query ("SELECT Institut_id, Name, Seminar_id, Ort, Untertitel, start_time, status FROM seminare WHERE Seminar_id='$auswahl'");
	if ($db->next_record()) {
		$SessSemName[0] = $db->f("Name");
		$SessSemName[1] = $db->f("Seminar_id");
		$SessSemName[2] = $db->f("Ort");
		$SessSemName[3] = $db->f("Untertitel");
		$SessSemName[4] = $db->f("start_time");
		$SessSemName[5] = $db->f("Institut_id");
		$SessSemName["art_generic"]="Veranstaltung";
		$SessSemName["class"]="sem";
		$SessSemName["art_num"]=$db->f("status");		 //numerisch die Typnummer
		if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME)
			$SessSemName["art"] = "Veranstaltung";
		else
			$SessSemName["art"] = $SEM_TYPE[$db->f("status")]["name"];
		$nr = $db->f("Seminar_id");
		$loginfilelast["$nr"] = $loginfilenow["$nr"];
		$loginfilenow["$nr"] = time();
	} else {
		$SessSemName[1]="";
	}
}	else {
		$auswahl=$SessSemName[1];
}
	

if ($SessSemName[1] =="")
	{
	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	parse_window ("error§Sie haben keine Veranstaltung gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher eine Veranstaltung gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich länger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen. </font>", "§",
				"Keine Veranstaltung gew&auml;hlt", 
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
	}
else 
	{
	switch ($target) {
		case "folder.php":
			header("Location: folder.php?cmd=tree");
			break;
		case "dates.php":
			header("Location: dates.php");
			break;
		case "literatur.php":
			header("Location: literatur.php");
			break;
		case "forum.php":
		default:
			header("Location: forum.php");
			break;
	}
	page_close();
	die;
	}
