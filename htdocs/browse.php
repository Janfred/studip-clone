<?php
/*
browse.php - Personen-Suche in Stud.IP
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

?>
<html>
<head>
        <link rel="stylesheet" href="style.css" type="text/css">
        <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
        <body bgcolor=white>

<title>Stud.IP</title>
</head>


<?php
        require_once "seminar_open.php"; //hier werden die sessions initialisiert
?>

<!-- hier muessen Seiten-Initialisierungen passieren -->

<?php

include "header.php";   //hier wird der "Kopf" nachgeladen
require_once "config.inc.php";   //wir brauchen die Auto-Eintrag-Seminare
require_once "visual.inc.php";
require_once "functions.php";

$sess->register("browse_data");

if ($send) {
	$browse_data["Vorname"]=$Vorname;
	$browse_data["Nachname"]=$Nachname;
	$browse_data["inst_id"]=$inst_id;
	$browse_data["sem_id"]=$sem_id;
}


if ($sortby)
	$browse_data["sortby"]=$sortby;
	
if ($group)
	$browse_data["group"]=$group;
			
		
?>
<body>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2><img src="pictures/suchen.gif" border="0" align="texttop"><b>&nbsp;Suche nach Personen</td>
</tr>
<?
if ($sms_msg)
	{
	echo"<tr><td class=\"blank\"colspan=2><br>";
	parse_msg (rawurldecode($sms_msg));
	echo"</td></tr>";
	}
?>
<tr>
<td class="blank" align = left width="60%"><br /><blockquote>
Hier k&ouml;nnen Sie die Homepages aller Nutzer abrufen, die im System registriert sind. <br />Sie erhalten auf den Nutzerhomepages von Institutsmitarbeitern auch weiterf&uuml;hrende Informationen wie Sprechstunden und R&auml;ume.<br />W&auml;hlen Sie den gew&uuml;nschen Bereich aus oder suchen Sie nach einem Namen!
<br><br><a href='score.php'>Zur Stud.IP Rangliste</a>
</blockquote></td>
<td class="blank" align = right><img src="pictures/board2.jpg" border="0"></td>
</tr>
<tr><td class="blank" colspan=2>
<blockquote>
<br>

<table class="blank" width="90%" cellpadding=2 cellspacing=1 border=0>
<!-- form zur wahl der institute -->
<form action="browse.php" method="POST">
<tr>
	<td width="20%" class="steel1" align="left">
		&nbsp;<b><font size=-1>Institute</font></b>
	</td>
	<td align="left" class="steel1" colspan=3>
		<font size=-1><SELECT Name="inst_id" size="1">
	<?
	$db2=new DB_Seminar;
	if ($perm->have_perm("admin"))
		$db2->query("SELECT * FROM Institute WHERE name != '- - -' ORDER BY name");
	else
		$db2->query("SELECT * FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE name != '- - -' AND user_id = '$user->id' ORDER BY name");
	if ($inst_id == "")
		printf ("<option value=\"0\">- - -\n");
	while ($db2->next_record())
		{
		$inst_name=htmlReady(my_substr($db2->f("Name"), 0, 40));
		printf ("<option %s value=\"%s\">%s\n", $db2->f("Institut_id") == $browse_data["inst_id"]  ? "selected" : "", $db2->f("Institut_id"), $inst_name);
		}
	?>
	</select></font></td>
	<td width="10%" class="steel1" align="center">
		<input type="HIDDEN" name="group" value="Institut">
		<input type="HIDDEN" name="send" value="TRUE">
 		<input type="IMAGE" value="Anzeigen" src="pictures/buttons/anzeigen-button.gif" border=0>
 	</td>
</tr>
</form>

<!-- form zur wahl der seminare -->
<form action="browse.php" method="POST">
<tr> 
	<td width="20%" class="steel1" align="left">
		&nbsp;<b><font size=-1>Veranstaltungen</font></b>
	</td>
  	<td align="left" class="steel1" colspan=3>
  		<font size=-1><SELECT Name="sem_id" size="1">
 	<?
	$db2=new DB_Seminar;
	if ($perm->have_perm("admin"))
		$db2->query("SELECT * FROM seminare ORDER BY Name");
	else {
		$templist = "'" . implode ("', '", $AUTO_INSERT_SEM) . "'";
		$db2->query("SELECT * FROM seminar_user LEFT JOIN seminare USING (Seminar_id) WHERE seminare.Seminar_id NOT IN ($templist) AND user_id = '$user->id' ORDER BY Name");
	}
	if ($sem_id == "")
		printf ("<option value=\"0\">- - -\n");
	while ($db2->next_record())
		{
		$sem_name=htmlReady(my_substr($db2->f("Name"), 0, 40));
		printf ("<option %s value=\"%s\">%s\n", $db2->f("Seminar_id") == $browse_data["sem_id"] ? "selected" : "", $db2->f("Seminar_id"), $sem_name);
		}
 	?>
	</select></font></td>
	<td width="10%" class="steel1" align="center">
		<input type="HIDDEN" name="group" value="Seminar">
  		<input type="IMAGE" value="Anzeigen" src="pictures/buttons/anzeigen-button.gif" border=0>
		<input type="HIDDEN" name="send" value="TRUE">  		
  	</td>
</tr>
</form>

<!-- form zur freien Suche -->
<form action="browse.php" method="POST">
<tr> 
	<td width="20%" class="steel1" align="left">
		<b><font size=-1>&nbsp;Vorname</font></b>
	</td>
  	<td width="30%" class="steel1" align="left">
		<input type="text" style="width: 75%" size=10 length=255 name="Vorname" value="<? echo htmlReady(stripslashes($browse_data["Vorname"])) ?>">
	</td>
	<td width="10%" class="steel1" align="left">
		<b><font size=-1>&nbsp;Nachname</font></b>
	</td>
  	<td width="30%" class="steel1" align="left">
		<input type="text" style="width: 75%" size=10 maxlength=255 name="Nachname" value="<? echo htmlReady(stripslashes($browse_data["Nachname"])) ?>">
	</td>
	<td width="10%" class="steel1" align="center">
		<input type="HIDDEN" name="group" value="Search">
		<input type="IMAGE" value="Suchen" src="pictures/buttons/suchen-button.gif" border=0>
		<input type="HIDDEN" name="send" value="TRUE">		
	</td>
</tr></form>

<?php
if ($perm->have_perm("admin")):
?>
<!-- alle Benutzer, ab global admin -->
<form action="browse.php" method="POST">
<tr> 
	<td class="steel1" align="left"width="80%" colspan=4>
		&nbsp;<b><font size=-1>Alle Benutzer</font></b>
	</td>
	<td class="steel1" width="20%" align="left">
		<input type="HIDDEN" name="group" value="All">
  		<input type="IMAGE" value="Anzeigen" src="pictures/buttons/anzeigen-button.gif" border=0>
		<input type="HIDDEN" name="send" value="TRUE">  		
  	</td>
</tr></form>
<?php
endif;
?>

</table>
<br />


<?php
## nachsehen, ob wir ein Sortierkriterium haben, sonst nach username
if (!isset($browse_data["sortby"]) || $browse_data["sortby"]=="") $browse_data["sortby"] = "Nachname";

// global
if ($browse_data["group"]=="All" && $perm->have_perm("admin")){  // nur global admin darf alle Benutzer sehen
 	$query = "SELECT * FROM auth_user_md5 ORDER BY ".$browse_data["sortby"];
}

// nach instituten
if($browse_data["group"]=="Institut"){
	$db2->query("SELECT Institut_id FROM user_inst WHERE Institut_id = '".$browse_data["inst_id"]."' AND user_id = '$user->id'");
	if ($db2->num_rows() > 0 || $perm->have_perm("admin")){  // entweder wir gehoeren auch zum Institut oder sind global admin
  	$query = "SELECT * FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) WHERE Institut_id ='".$browse_data["inst_id"]."' ORDER BY ".$browse_data["sortby"];
	}
} 

// nach seminaren
if($browse_data["group"]=="Seminar"){
	$templist = "'" . implode ("', '", $AUTO_INSERT_SEM) . "'";
	$db2->query("SELECT Seminar_id FROM seminar_user WHERE Seminar_id NOT IN ($templist) AND Seminar_id = '".$browse_data["sem_id"]."' AND user_id = '$user->id'");
	if ($db2->num_rows() > 0 || $perm->have_perm("admin")){  // entweder wir gehoeren auch zum Seminar oder sind global admin
 		$query = "SELECT * FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE Seminar_id ='".$browse_data["sem_id"]."' ORDER BY ".$browse_data["sortby"];
	}
} 

// freie Suche
if($browse_data["group"]=="Search"){
 	$query = "SELECT * FROM auth_user_md5 ";
	$browse_data["Vorname"] = str_replace("%", "\%", $browse_data["Vorname"]);	// tss, tss, tss
	$browse_data["Vorname"] = str_replace("_", "\_", $browse_data["Vorname"]);	
	$browse_data["Nachname"] = str_replace("%", "\%", $browse_data["Nachname"]);	
	$browse_data["Nachname"] = str_replace("_", "\_", $browse_data["Nachname"]);
	if ($browse_data["Vorname"] != "" && strlen($browse_data["Vorname"]) > 2){  
		if ($browse_data["Nachname"] != "" && strlen($browse_data["Nachname"]) > 2){ // wir haben Vornamen und Nachnamen zum Suchen
	 		$query .= " WHERE Vorname LIKE '%".$browse_data["Vorname"]."%' AND Nachname LIKE '%".$browse_data["Nachname"]."%' ";
		} else {              // wir haben einen Vornamen zum Suchen
	 		$query .= " WHERE Vorname LIKE '%".$browse_data["Vorname"]."%' ";
		}
	}	else {
		if ($browse_data["Nachname"] != "" && strlen($browse_data["Nachname"]) > 2){ // wir haben einen Nachnamen zum Suchen
	 		$query .= " WHERE Nachname LIKE '%".$browse_data["Nachname"]."%' ";
		} else {              // wir haben nix oder Muell zum Suchen. PFUI!
	 		$query .= " WHERE Vorname ='- - -' AND Nachname = '- - -' ";
		}
	}
	$query .= " ORDER BY ".$browse_data["sortby"];
} 


if (isset($query)):
    $db = new DB_Seminar;	
    $db->query($query);

// ausgabe der tabellenueberschrift
	print ("<table class=\"blank\" width=\"90%\" cellpadding=2 cellspacing=0 border=0>");
	print ("<tr valign=top align=middle>");

	if ($db->num_rows() > 0):
// wir haben ein Ergebnis
		switch ($browse_data["group"]) {
			case "Seminar":
				echo "<td class=\"steel\" nowrap valign=bottom width=\"25%\"><a href=\"browse.php?sortby=Vorname\"><b>Vorname</b></a></td>";
				echo "<td class=\"steel\" nowrap valign=bottom width=\"25%\"><a href=\"browse.php?sortby=Nachname\"><b>Nachname</b></a></td>";
				echo "<td class=\"steel\" nowrap valign=bottom width=\"25%\"><a href=\"browse.php?sortby=status\"><b>Status in der Veranstaltung</b></a></td>";
			break;
			case "Institut":
				echo "<td class=\"steel\" nowrap valign=bottom width=\"22%\"><a href=\"browse.php?sortby=Vorname\"><b>Vorname</b></a></td>";
				echo "<td class=\"steel\" nowrap valign=bottom width=\"22%\"><a href=\"browse.php?sortby=Nachname\"><b>Nachname</b></a></td>";
				echo "<td class=\"steel\" nowrap valign=bottom width=\"22%\"><a href=\"browse.php?sortby=Funktion\"><b>Funktion am Institut</b></a></td>";
			break;
			case "Search":
				echo "<td class=\"steel\" valign=bottom nowrap width=\"25%\"><a href=\"browse.php?sortby=Vorname\"><b>Vorname</b></a></td>";
				echo "<td class=\"steel\" valign=bottom nowrap width=\"25%\"><a href=\"browse.php?sortby=Nachname\"><b>Nachname</b></a></td>";
				echo "<td class=\"steel\" valign=bottom nowrap width=\"25%\"><a href=\"browse.php?sortby=perms\"><b>globaler Status</b></a></td>";
			break;
			default:
				echo "<td class=\"steel\" valign=bottom nowrap width=\"25%\"><a href=\"browse.php?sortby=Vorname\"><b>Vorname</b></a></td>";
				echo "<td class=\"steel\" valign=bottom nowrap width=\"25%\"><a href=\"browse.php?sortby=Nachname\"><b>Nachname</b></a></td>";
				echo "<td class=\"steel\" valign=bottom nowrap width=\"25%\"><a href=\"browse.php?sortby=perms\"><b>globaler Status</b></a></td>";
			break;
		}
		echo "<td class=\"steel\" nowrap width=\"25%\" valign=bottom align=\"center\"><b>Nachricht verschicken</b><img src=\"pictures/blank.gif\" width=1 height=20></td>";
		echo  "</tr>";
  	
		//anfuegen der daten an tabelle in schleife...
	$c=0;
  	while ($db->next_record()) {
	  	if ($c % 2)
			$class="steel1";
		else
			$class="steelgraulight"; 
		$c++;
			switch ($group) {
				case "Seminar":
					printf("<tr valign=middle align=left><td class=\"$class\"><font size=-1> &nbsp;%s</font></td>", $db->f("Vorname"));
					printf("<td class=\"$class\"><font size=-1><a href=\"about.php?username=%s\"> &nbsp;%s</a></font></td>", $db->f("username"), $db->f("Nachname"));
					printf("<td class=\"$class\"><font size=-1> &nbsp;%s</font></td>", htmlReady($db->f("status")));
					break;
				case "Institut":
					printf("<tr valign=middle align=left><td class=\"$class\"><font size=-1> &nbsp;%s</font></td>", $db->f("Vorname"));
					printf("<td class=\"$class\"><font size=-1><a href=\"about.php?username=%s\"> &nbsp;%s</a></font></td>", $db->f("username"), $db->f("Nachname"));
					if ($db->f("inst_perms") == "user")
						printf("<tdclass=\"$class\"><font size=-1> &nbsp;Studierender &nbsp;</font></td>");
					else
						($db->f("Funktion")) ? printf("<td class=\"$class\"><font size=-1> &nbsp;%s &nbsp;</font></td>", htmlReady($INST_FUNKTION[$db->f("Funktion")]["name"])) : printf("<td class=\"$class\"><font size=-1> &nbsp;keine Funktion &nbsp;</font></td>");
					break;
				default:
					printf("<tr valign=middle align=left><td class=\"$class\"><font size=-1> &nbsp;%s</font></td>", $db->f("Vorname"));
					printf("<td class=\"$class\"><font size=-1><a href=\"about.php?username=%s\"> &nbsp;%s</a></font></td>", $db->f("username"), $db->f("Nachname"));
					printf("<td class=\"$class\"><font size=-1> &nbsp;%s</font></td>", $db->f("perms"));
					break;
			}
			echo "<td class=\"$class\" align=\"center\"><a href=\"sms.php?sms_source_page=browse.php&cmd=write&rec_uname=", $db->f("username"),"\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=0></a></td></tr>";
		}
		print("</table><br /><br />");
	else: // wir haben kein Ergebnis
		printf("<th nowrap>Niemand gefunden!</th></tr></table><br /><br />");
	endif;
endif;
?>

</table>
<?
          page_close()
 ?>
</body>
</html>