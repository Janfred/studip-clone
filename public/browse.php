<?php
# Lifter002: TODO
# Lifter003: TODO
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
// $Id$

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$perm->check('user');

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ('config.inc.php');   //wir brauchen die Auto-Eintrag-Seminare
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once('lib/statusgruppe.inc.php');
require_once('lib/user_visible.inc.php');
if ($GLOBALS['CHAT_ENABLE']){
	include_once $RELATIVE_PATH_CHAT."/chat_func_inc.php";
}

$HELP_KEYWORD="Basis.SuchenPersonen";
$CURRENT_PAGE = _("Personensuche");
// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

$sess->register("browse_data");

if ($send) {
	$browse_data["Vorname"] = trim($Vorname);
	$browse_data["Nachname"] = trim($Nachname);
	$browse_data["inst_id"] = $inst_id;
	$browse_data["sem_id"] = $sem_id;
}

$accepted_columns = array('Nachname', 'perms', 'status');
if($sortby) $browse_data['sortby'] = in_array($sortby, $accepted_columns)? $sortby:'';

if ($group) {
	$browse_data["group"]=$group;
	$browse_data["sortby"]='';
}

if ($reset)
	$browse_data='';


?>
<body>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2>&nbsp;</td>
</tr>
<?
if ($sms_msg)
	{
	echo"<tr><td class=\"blank\"colspan=2><br>";
	parse_msg ($sms_msg);
	echo"</td></tr>";
	$sms_msg = '';
	$sess->unregister('sms_msg');
	}
?>
<tr>
<td class="blanksmall" align = left width="60%"><br /><blockquote>
<?
echo _("Hier k&ouml;nnen Sie die Homepages aller NutzerInnen abrufen, die im System registriert sind.") . "<br />";
echo _("Sie erhalten auf den Homepages von MitarbeiternInnen an Einrichtungen auch weiterf&uuml;hrende Informationen, wie Sprechstunden und Raumangaben.") . "<br />";
echo _("W&auml;hlen Sie den gew&uuml;nschen Bereich aus oder suchen Sie nach einem Namen!");
?>
<br><br><a href="score.php"><?=_("Zur Stud.IP-Rangliste")?></a>
</blockquote></td>
<td class="blank" align = right><br><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/board2.jpg" border="0"></td>
</tr>
<tr><td class="blank" colspan=2>
<blockquote>
<br>

<table class="blank" width="90%" cellpadding=2 cellspacing=0 border=0>
<!-- form zur wahl der institute -->
<form action="browse.php" method="POST">
<tr>
	<td width="20%" class="steel1" align="left">
		&nbsp;<b><font size=-1><?=_("Einrichtungen")?></font></b>
	</td>
	<td align="left" class="steel1" colspan=3>
		<font size=-1><SELECT Name="inst_id" size="1">
	<?
	$db2=new DB_Seminar;
	if ($perm->have_perm("admin"))
		$db2->query("SELECT * FROM Institute WHERE name != '- - -' AND (Institute.modules & 16) ORDER BY name");
	else
		$db2->query("SELECT * FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE name != '- - -' AND user_id = '$user->id'  AND (Institute.modules & 16) ORDER BY name");
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
 		<input type="IMAGE" value="Anzeigen" <?=makeButton("anzeigen", "src")?> border=0>
 	</td>
</tr>
</form>

<!-- form zur wahl der seminare -->
<form action="browse.php" method="POST">
<tr>
	<td width="20%" class="steel1" align="left">
		&nbsp;<b><font size=-1><?=_("Veranstaltungen")?></font></b>
	</td>
  	<td align="left" class="steel1" colspan=3>
  		<font size=-1><SELECT Name="sem_id" size="1">
 	<?
	$db2=new DB_Seminar;
	if ($perm->have_perm("admin"))
		$db2->cache_query("SELECT * FROM seminare ORDER BY Name");
	else {
		if ($AUTO_INSERT_SEM) {
			$templist = "'" . implode ("', '", $AUTO_INSERT_SEM) . "'";
			$db2->query("SELECT * FROM seminar_user LEFT JOIN seminare USING (Seminar_id) WHERE seminare.Seminar_id NOT IN ($templist) AND user_id = '$user->id' AND (seminare.modules & 8) ORDER BY Name");
		} else {
			$db2->query("SELECT * FROM seminar_user LEFT JOIN seminare USING (Seminar_id) WHERE user_id = '$user->id' AND (seminare.modules & 8) ORDER BY Name");
		}
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
  		<input type="IMAGE" value="Anzeigen" <?=makeButton("anzeigen", "src")?> border=0>
		<input type="HIDDEN" name="send" value="TRUE">
  	</td>
</tr>
</form>

<!-- form zur freien Suche -->
<form action="browse.php" method="POST">
<tr>
	<td width="20%" class="steel1" align="left">
		<b><font size=-1>&nbsp;<?=_("Vorname")?></font></b>
	</td>
  	<td width="30%" class="steel1" align="left">
		<input id="Vorname" type="text" style="width: 75%" size=10 length=255 name="Vorname" value="<? echo htmlReady(stripslashes($browse_data["Vorname"])) ?>">
		<div id="Vorname_choices" class="autocomplete"></div>
	</td>
	<td width="10%" class="steel1" align="left">
		<b><font size=-1>&nbsp;<?=_("Nachname")?></font></b>
	</td>
  	<td width="30%" class="steel1" align="left">
		<input id="Nachname" type="text" style="width: 75%" size=10 maxlength=255 name="Nachname" value="<? echo htmlReady(stripslashes($browse_data["Nachname"])) ?>">
		<div id="Nachname_choices" class="autocomplete"></div>
	</td>
	<td width="10%" class="steel1" align="center">
		<input type="HIDDEN" name="group" value="Search">
		<input type="IMAGE" value="Suchen" <?=makeButton("suchen", "src")?> border=0>
		<input type="HIDDEN" name="send" value="TRUE">
	</td>
</tr></form>

<script type="text/javascript">
	Event.observe(window, 'load', function() {
		new Ajax.Autocompleter('Vorname',
		                       'Vorname_choices',
		                       'dispatch.php/autocomplete/person/given',
		                       { minChars: 3, paramName: 'value', method: 'get' });
		new Ajax.Autocompleter('Nachname',
		                       'Nachname_choices',
		                       'dispatch.php/autocomplete/person/family',
		                       { minChars: 3, paramName: 'value', method: 'get',
		                         afterUpdateElement: function (input, item) {
		                           var username = encodeURI(item.down('span.username').firstChild.nodeValue);
		                           document.location = "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>about.php?username=" + username;
		                         }});
	});
</script>


<?php
if ($perm->have_perm("admin")):
?>
<!-- alle Benutzer, ab global admin -->
<!--deprecated, in big Installations this Options would be a killer - use the user management instead
<form action="browse.php" method="POST">
<tr>
	<td class="steel1" align="left"width="80%" colspan=4>
		&nbsp;<b><font size=-1><?=_("Alle NutzerInnen")?></font></b>
	</td>
	<td class="steel1" width="20%" align="left">
		<input type="HIDDEN" name="group" value="All">
  		<input type="IMAGE" value="Anzeigen" <?=makeButton("anzeigen", "src")?> border=0>
		<input type="HIDDEN" name="send" value="TRUE">
  	</td>
</tr></form>-->
<?php
endif;
?>
<tr>
	<td class="steel1" align="left" width="100%" colspan="5">
		<a href="<? echo $PHP_SELF ?>?reset=TRUE"><?=makeButton("neuesuche", "img")?></a></font>
  	</td>
</tr></form>


</table>
<br />


<?php
## nachsehen, ob wir ein Sortierkriterium haben, sonst nach username
if (!isset($browse_data["sortby"]) || $browse_data["sortby"]=="") $browse_data["sortby"] = "Nachname";

unset($query);

// global
if ($browse_data["group"]=="All" && $perm->have_perm("admin")){  // nur global admin darf alle Benutzer sehen
 	$query = "SELECT " . $_fullname_sql['full_rev'] ." AS fullname,username,perms,auth_user_md5.user_id FROM auth_user_md5 LEFT JOIN user_info USING (user_id) ORDER BY ".$browse_data["sortby"];
}

// nach instituten
if($browse_data["group"]=="Institut"){
	$einrichtungssuche = true;
	$db2->query("SELECT Institut_id FROM user_inst WHERE Institut_id = '".$browse_data["inst_id"]."' AND user_id = '$user->id'");
	if ($db2->num_rows() > 0 || $perm->have_perm("admin")){  // entweder wir gehoeren auch zum Institut oder sind global admin
  	$query = "SELECT " . $_fullname_sql['full_rev'] ." AS fullname ,username,user_inst.inst_perms,user_inst.user_id,user_inst.Institut_id FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE Institut_id ='".$browse_data["inst_id"]."' ORDER BY ".$browse_data["sortby"];
	}
}

// nach seminaren
if($browse_data["group"]=="Seminar"){
	if ($AUTO_INSERT_SEM) {
		$templist = "'" . implode ("', '", $AUTO_INSERT_SEM) . "'";
		$db2->query("SELECT Seminar_id FROM seminar_user WHERE Seminar_id NOT IN ($templist) AND Seminar_id = '".$browse_data["sem_id"]."' AND user_id = '$user->id'");
	} else {
		$db2->query("SELECT Seminar_id FROM seminar_user WHERE Seminar_id = '".$browse_data["sem_id"]."' AND user_id = '$user->id'");
	}
	if ($db2->num_rows() > 0 || $perm->have_perm("admin")){  // entweder wir gehoeren auch zum Seminar oder sind global admin
 		$query = "SELECT " . $_fullname_sql['full_rev'] ." AS fullname ,username,seminar_user.status,auth_user_md5.user_id FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE Seminar_id ='".$browse_data["sem_id"]."' ORDER BY ".$browse_data["sortby"];
	}
}

// freie Suche
if($browse_data["group"]=="Search"){
 	$query = "SELECT " . $_fullname_sql['full_rev'] ." AS fullname,username,perms,auth_user_md5.user_id FROM auth_user_md5 LEFT JOIN user_info USING (user_id)";
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
if (!$browse_data["group"])
	unset($query);

if (isset($query)) {
    $db = new DB_Seminar;
    $db->query($query);

// ausgabe der tabellenueberschrift
	print ("<table class=\"blank\" width=\"90%\" cellpadding=2 cellspacing=0 border=0>");
	print ("<tr valign=top align=middle>");

	if ($db->num_rows() > 0) {
		$visible = 0;				//VIS: first, we have to save all data, that needs to be displayed
		$data = array();
		while ($db->next_record()) {
			if (get_visibility_by_id($db->f("user_id")) || ($einrichtungssuche && $db->f("perms") != "autor" && $db->f("perms") != "user")) {
				$visible++;
				$data[] = $db->Record;
			}
		}
		if ($visible > 0) {

// wir haben ein Ergebnis
		switch ($browse_data["group"]) {
			case "Seminar":
				echo "<td class=\"steel\" nowrap valign=bottom width=\"50%\"><a href=\"browse.php?sortby=Nachname\"><b>" . _("Name") . "</b></a></td>";
				echo "<td class=\"steel\" nowrap valign=bottom width=\"25%\"><a href=\"browse.php?sortby=status\"><b>" . _("Status in der Veranstaltung") . "</b></a></td>";
			break;
			case "Institut":
				echo "<td class=\"steel\" nowrap valign=bottom width=\"44%\"><a href=\"browse.php?sortby=Nachname\"><b>" . _("Name") . "</b></a></td>";
				echo "<td class=\"steel\" nowrap valign=bottom width=\"22%\"><b>" . _("Funktion an der Einrichtung") . "</b></td>";
			break;
			case "Search":
				echo "<td class=\"steel\" valign=bottom nowrap width=\"50%\"><a href=\"browse.php?sortby=Nachname\"><b>" . _("Name") . "</b></a></td>";
				echo "<td class=\"steel\" valign=bottom nowrap width=\"25%\"><a href=\"browse.php?sortby=perms\"><b>" . _("globaler Status") . "</b></a></td>";
			break;
			default:
				echo "<td class=\"steel\" valign=bottom nowrap width=\"50%\"><a href=\"browse.php?sortby=Nachname\"><b>" . _("Name") . "</b></a></td>";
				echo "<td class=\"steel\" valign=bottom nowrap width=\"25%\"><a href=\"browse.php?sortby=perms\"><b>" . _("globaler Status") . "</b></a></td>";
			break;
		}
		echo "<td class=\"steel\" nowrap width=\"25%\" valign=bottom align=\"center\"><b>" . _("Nachricht verschicken") . "</b><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=1 height=20></td>";
		echo  "</tr>";

		//anfuegen der daten an tabelle in schleife...
	$c=0;
  	foreach ($data as $val) {		// now iterate trough data-array instead of database-array
	  	if ($c % 2)
			$class="steelgraulight";
		else
			$class="steel1";
		$c++;
			switch ($browse_data["group"]) {
				case "Seminar":
					print("<tr valign=middle align=left>");
					printf("<td class=\"$class\"><font size=-1><a href=\"about.php?username=%s\"> &nbsp;%s</a></font></td>", $val["username"], htmlReady($val["fullname"]));
					printf("<td class=\"$class\"><font size=-1> &nbsp;%s</font></td>", htmlReady($val["status"]));
					break;
				case "Institut":
					print("<tr valign=middle align=left>");
					printf("<td class=\"$class\"><font size=-1><a href=\"about.php?username=%s\"> &nbsp;%s</a></font></td>", $val["username"], htmlReady($val["fullname"]));
					if ($val["inst_perms"] == "user")
						print("<td class=\"$class\"><font size=-1> &nbsp;Studierender &nbsp;</font></td>");
					else {
						//statusgruppen
						$gruppen = GetRoleNames(GetAllStatusgruppen($val["Institut_id"],$val["user_id"]));
						(is_array($gruppen)) ? printf("<td class=\"$class\"><font size=-1> &nbsp;%s &nbsp;</font></td>", htmlReady(join(", ", array_values($gruppen)))) : printf("<td class=\"$class\"><font size=-1> &nbsp;" . _("keiner Funktion zugeordnet") . "&nbsp;</font></td>");
					}
					break;
				default:
					print("<tr valign=middle align=left>");
					printf("<td class=\"$class\"><font size=-1><a href=\"about.php?username=%s\"> &nbsp;%s</a></font></td>", $val["username"], htmlReady($val["fullname"]));
					printf("<td class=\"$class\"><font size=-1> &nbsp;%s</font></td>", $val["perms"]);
					break;
			}
			echo "<td class=\"$class\" align=\"center\">";
			if ($GLOBALS['CHAT_ENABLE']){
				echo chat_get_online_icon($val["user_id"],$val["username"]) . "&nbsp;";
			}
			echo "<a href=\"sms_send.php?sms_source_page=browse.php&rec_uname=", $val["username"],"\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" " . tooltip(_("Nachricht an User verschicken")) . " border=0></a></td></tr>";
		}
		print("</table><br /><br />");
		} else {

			printf("<th nowrap>" . _("Niemand gefunden!") . "</th></tr></table><br /><br />");
		}
	} else { // wir haben kein Ergebnis
		printf("<th nowrap>" . _("Niemand gefunden!") . "</th></tr></table><br /><br />");
	}
}
?>

</table>
<?php
include ('lib/include/html_end.inc.php');
  page_close();
?>
