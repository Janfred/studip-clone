<?
/*
institute_browse.inc.php - Universeller Seminarbrowser zum Includen, Stud.IP - 0.8.20020328
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

include ($ABSOLUTE_PATH_STUDIP."header.php");   //hier wird der "Kopf" nachgeladen
require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");

$sess->register("institut_browse_data");
$cssSw=new cssClassSwitcher;

if ($send) {	
	$institut_browse_data["fak_id"]=$fak_id;
	$institut_browse_data["pers"]=$pers;
	$institut_browse_data["s_string"]=stripslashes(htmlReady($s_string));
	$institut_browse_data["s_mitarbeiter"]=stripslashes(htmlReady($s_mitarbeiter));
	$institut_browse_data["s_sem"]=stripslashes(htmlReady($s_sem));
	$institut_browse_data["search"]=TRUE;
	}
//echo $institut_browse_data["fak_id"];

if ($sortby)
	$institut_browse_data["sortby"]=$sortby;
	
?>
<body>
<table width="100%" border=0 cellpadding=2 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><img src="pictures/suchen.gif" border="0" align="texttop"><b>&nbsp;Suche nach Einrichtungen</td>
	</tr>
	<?
	if ($msg)	{
	parse_msg ($sms_msg);
	}
	?>
	<tr>
		<td class="blank" width="60%" align="center">
			<blockquote>
			<br />
				<p>
				<form  name="search" method="post" action="<? echo $PHP_SELF?>" >
					<table border=0 cellspacing=0 cellpadding=2>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" colspan=2>
							<b><font size=-1>Bitte geben Sie hier Ihre Suchkriterien ein:</font></b><br /><font size=-1>Wenn Sie keinen Suchbegriff angeben, werden alle Einrichtungen angezeigt.</font>
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>Name der Einrichtung:</font>
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%">
								<input  type="text" size=30 maxlength=255 name="s_string" value="<? echo $institut_browse_data["s_string"] ?>">
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>Einrichtung dieses Mitarbeiters:</font>
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%">
								<input  type="text"  size=30 maxlength=255 name="s_mitarbeiter" value="<? echo $institut_browse_data["s_mitarbeiter"] ?>">
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>Einrichtung dieser Veranstaltung:</font>
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%">
								<input  type="text"  size=30 maxlength=255 name="s_sem" value="<? echo $institut_browse_data["s_sem"] ?>">
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>Fakult&auml;t</font>
							</td>
							<td  class="<? echo $cssSw->getClass() ?>" width="90%">
								<font size=-1>
								<select name="fak_id">
								<option selected value=0>alle</option>
								<?
								$db->query("SELECT Name, Fakultaets_id FROM Fakultaeten");
								while ($db->next_record()) 
									if ($institut_browse_data["fak_id"] == $db->f("Fakultaets_id"))
										echo "<option selected value=\"", $db->f("Fakultaets_id"), "\">", $db->f("Name"), "</option>";
									else
										echo "<option value=\"", $db->f("Fakultaets_id"), "\">", $db->f("Name"), "</option>";
								?>
								</select>
								</font>
							</td>
						</tr>						
						<?/*
						<tr <? $cssSw->switchClass() ?>>
							<td width="10%">
								&nbsp; 
							</td>
							<td width="90%">
								<input  type="checkbox" name="pers" <? if ($institut_browse_data["pers"]) echo "checked" ?>>
								<font size=-1>Nur Einrichtungen anzeigen, an denen ich studiere</font>
							</td>
						</tr>
						*/?>
					   	<tr <? $cssSw->switchClass() ?>>
					   		<td class="<? echo $cssSw->getClass() ?>" width="10%">
					   			&nbsp; 
					   		</td>
					   		<td class="<? echo $cssSw->getClass() ?>" width="90%">
					   			<center>
					   				<input type="IMAGE" border=0 src="pictures/buttons/suchestarten-button.gif" value="Suche starten">
					   			</center
					   		</td>
						</tr>
					</table>
					<br />
					<input type="HIDDEN" name="send" value="TRUE">
				</form>
			</blockquote>
		</td>
		<td class="blank" align = right valign=top><img src="pictures/archiv.jpg" border="0">
		</td>
	</tr>

<?
//Suchen
if ($institut_browse_data["search"]) {
if (!$institut_browse_data["sortby"])
	$institut_browse_data["sortby"]="Name";

if ($institut_browse_data["pers"])
	$query ="SELECT DISTINCT Institute.Institut_id, Institute.Name, Fakultaeten.Name AS fakultaet, url FROM Institute LEFT JOIN user_inst ON (user_inst.Institut_id = Institute.Institut_id) LEFT JOIN seminar_inst USING (institut_id) LEFT JOIN seminar_user ON (seminar_user.seminar_id = seminar_inst.seminar_id) LEFT JOIN Fakultaeten ON (Institute.Fakultaets_id = Fakultaeten.Fakultaets_id) LEFT JOIN auth_user_md5 ON (user_inst.user_id = auth_user_md5.user_id) LEFT JOIN seminare ON (seminar_inst.seminar_id = seminare.seminar_id) WHERE user_inst.user_id = '".$user->id."' OR seminar_user.user_id = '".$user->id."' AND ";
else
	$query ="SELECT DISTINCT Institute.Institut_id, Institute.Name, Fakultaeten.Name AS fakultaet, url FROM Institute LEFT JOIN user_inst ON (user_inst.Institut_id = Institute.Institut_id AND inst_perms != 'user') LEFT JOIN seminar_inst USING (institut_id) LEFT JOIN Fakultaeten ON (Institute.Fakultaets_id = Fakultaeten.Fakultaets_id) LEFT JOIN auth_user_md5 ON (user_inst.user_id = auth_user_md5.user_id) LEFT JOIN seminare ON (seminar_inst.seminar_id = seminare.seminar_id)  WHERE ";

if ($institut_browse_data["s_string"])
	$query .= "(Institute.Name LIKE '%".$institut_browse_data["s_string"]."%' OR Strasse LIKE '%".$institut_browse_data["s_string"]."%' OR Plz  LIKE '%".$institut_browse_data["s_string"]."%' OR url  LIKE '%".$institut_browse_data["s_string"]."%' OR Institute.telefon LIKE '%".$institut_browse_data["s_string"]."%' OR Institute.email LIKE '%".$institut_browse_data["s_string"]."%' OR Institute.fax LIKE '%".$institut_browse_data["s_string"]."%')";
else
	$query .= "Institute.Name LIKE '%%'";
if ($institut_browse_data["fak_id"])
	$query .= " AND Institute.Fakultaets_id LIKE '%".$institut_browse_data["fak_id"]."%'";

if ($institut_browse_data["s_mitarbeiter"])
	$query .= " AND (auth_user_md5.Nachname  LIKE '%".$institut_browse_data["s_mitarbeiter"]."%' OR auth_user_md5.username  LIKE '%".$institut_browse_data["s_mitarbeiter"]."%')";

if ($institut_browse_data["s_sem"])
	$query .= " AND (seminare.Name LIKE '%".$institut_browse_data["s_sem"]."%' OR seminare.Untertitel LIKE '%".$institut_browse_data["s_sem"]."%' OR seminare.Beschreibung LIKE '%".$institut_browse_data["s_sem"]."%')";

$query .= " ORDER BY ".$institut_browse_data["sortby"];
$db->query($query);
IF (!$db->affected_rows() == 0) {
	$hits = $db->affected_rows();
	
	?>
	<tr>
		<td class="blank" colspan=2>
	<?
		
echo "<blockquote><b><font size=-1>Es wurden $hits Einrichtungen gefunden.</font></b></blockquote>";

echo "<br /><br /><table class=\"blank\"  width=\"99%\" align=center cellspacing=0 border=0>";
echo "<tr height=28><td  width=\"1%\" class=\"steel\"><img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; </td><td  width=\"33%\" class=\"steel\" align=center valign=bottom><b><a href=\"$PHP_SELF?sortby=Name\">Name</a></b></td><td  width=\"33%\" class=\"steel\" align=center valign=bottom><b><a href=\"$PHP_SELF?sortby=url\">Homepage</a></b></td><td  width=\"33%\" class=\"steel\" align=center valign=bottom><b><a href=\"$PHP_SELF?sortby=fakultaet\">Fakult&auml;t</a></b></td></tr>\n";

$c=0;
while ($db->next_record()) {
  	if ($c % 2)
		$class="steelgraulight";
	else
		$class="steel1"; 
	$c++;
	
	echo "<tr><td class=\"$class\" WIDTH=\"1%\">&nbsp;</td>";
	echo "<td class=\"$class\"><a href=\"institut_main.php?auswahl=".$db->f("Institut_id")."\">".htmlReady($db->f("Name"))."</a></td>"; 
	echo "<td class=\"$class\">".formatReady("[".$db->f("url")."]".$db->f("url"))."</td>";
	echo "<td class=\"$class\">".htmlReady($db->f("fakultaet"))."</td></tr>\n";		
}

echo "</tr></table>";
} else {
	echo "<tr><td class=\"blank\" colspan=2><blockquote><font size=-1><b>Es wurde keine Einrichtung gefunden.</b></font></blockquote>";
}
}
page_close()
?>
		</td>
	</tr>
</table>
</body>