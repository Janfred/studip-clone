<?
/*
admin_statusgruppe.php - Statusgruppen-Verwaltung von Stud.IP.
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
	$auth->login_if($auth->auth["uid"] == "nobody");
	$perm->check("tutor");

$hash_secret = "dslkjjhetbjs";
$msg = array();
include ('lib/seminar_open.php'); // initialise Stud.IP-Session

require_once ('config.inc.php');
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once ('lib/admission.inc.php');
require_once ('lib/statusgruppe.inc.php');
require_once ('lib/datei.inc.php');

$HELP_KEYWORD="Basis.VeranstaltungenVerwaltenGruppen";

//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
$CURRENT_PAGE = _("Verwaltung von Gruppen und Funktionen");

//prebuild navi and the object switcher (important to do already here and to use ob!)
ob_start();
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
$links = ob_get_clean();
//get ID, if a object is open
if ($SessSemName[1])
	$range_id = $SessSemName[1];
//Change header_line if open object
$header_line = getHeaderLine($range_id);
if ($header_line)
	$CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
echo $links;
// Rechtecheck

$_range_type = get_object_type($range_id);
if (!($_range_type == "sem" && $perm->have_studip_perm("tutor",$range_id)) &&
	!(($_range_type == "inst" || $_range_type == "fak") && $perm->have_studip_perm("admin",$range_id))) {
	echo "</tr></td></table>";
	page_close();
	die;
}



// Beginn Funktionsteil

// Hilfsfunktionen

function GetPresetGroups ($view, $veranstaltung_class)
{ 	global $INST_STATUS_GROUPS, $SEM_STATUS_GROUPS;
        echo "<select name=\"move_old_statusgruppe\">";
	if ($view == "statusgruppe_inst" || $view == "statusgruppe_fak") {
		for ($i=0; $i<sizeof($INST_STATUS_GROUPS["default"]); $i++) {
			printf ("<option>%s</option>",$INST_STATUS_GROUPS["default"][$i]);
		}
	}
	if ($view == "statusgruppe_sem") {
		if (isset($SEM_STATUS_GROUPS[$veranstaltung_class])) {   // wir sind in einer Veranstaltung die Presets hat
			$key = $veranstaltung_class;
		} else {
			$key = "default";
		}
		for ($i=0; $i<sizeof($SEM_STATUS_GROUPS[$key]); $i++) {
			printf ("<option>%s</option>",$SEM_STATUS_GROUPS[$key][$i]);
		}
	}
	echo "</select>";
}

function MovePersonStatusgruppe ($range_id, $AktualMembers="", $InstitutMembers="", $Freesearch="", $workgroup_mode=FALSE)
{ global $_range_type,$perm,$view;
		
		if($view == 'statusgruppe_sem'){
			list($self_assign_all, $self_assign_exclusive) = CheckSelfAssignAll($range_id);
			if($self_assign_exclusive){
				$assigned = GetAllSelected($range_id);
			}
		}
		
		while (list($key, $val) = each ($_POST)) {
			$statusgruppe_id = substr($key, 0, -2);
		}
		$db=new DB_Seminar;
		$db2=new DB_Seminar;
		$mkdate = time();
		if ($AktualMembers != "") {
			for ($i  = 0; $i < sizeof($AktualMembers); $i++) {
				$user_id = get_userid($AktualMembers[$i]);
				if( !($self_assign_exclusive && in_array($user_id, $assigned)) ){
					InsertPersonStatusgruppe ($user_id, $statusgruppe_id);
				}
			}
		}
		if (isset($InstitutMembers) && $InstitutMembers != "---") {
			$user_id = get_userid($InstitutMembers);
			if( !($self_assign_exclusive && in_array($user_id, $assigned)) ){
				$writedone = InsertPersonStatusgruppe ($user_id, $statusgruppe_id);
			}
			if ($writedone) {
				if ($workgroup_mode == TRUE) {
					$globalperms = get_global_perm($user_id);
					if ($globalperms == "tutor" || $globalperms == "dozent") {
						insert_seminar_user($range_id, $user_id, "tutor", FALSE);
					} else {
						insert_seminar_user($range_id, $user_id, "autor", FALSE);
					}
				} else {
					insert_seminar_user($range_id, $user_id, "autor", FALSE);
				}
			}
		}
		if ($Freesearch != "") {
			for ($i  = 0; $i < sizeof($Freesearch); $i++) {
				$user_id = get_userid($Freesearch[$i]);
				if( !($self_assign_exclusive && in_array($user_id, $assigned)) ){
					$writedone = InsertPersonStatusgruppe ($user_id, $statusgruppe_id);
				}
				if ($writedone) {
					if ($_range_type == "sem") {
						if ($workgroup_mode == TRUE) {
							$globalperms = get_global_perm($user_id);
							if ($globalperms == "tutor" || $globalperms == "dozent") {
								insert_seminar_user($range_id, $user_id, "tutor", FALSE);
							} else {
								insert_seminar_user($range_id, $user_id, "autor", FALSE);
							}
						} else {
							insert_seminar_user($range_id, $user_id, "autor", FALSE);
						}
					} elseif ($_range_type == "inst" || $_range_type == "fak") {
						$globalperms = get_global_perm($user_id);
						if ($perm->get_studip_perm($range_id, $user_id) == FALSE) {
							$db2->query("INSERT INTO user_inst SET Institut_id = '$range_id', user_id = '$user_id', inst_perms = '$globalperms'");
						}
						if ($perm->get_studip_perm($range_id, $user_id) =="user") {
							$db2->query("UPDATE user_inst SET inst_perms = '$globalperms' WHERE user_id = '$user_id' AND Institut_id = '$range_id'");
						}
					}
				}
			}
		}
}


// Sortieren nach Nachname

function SortByName($statusgruppe_id) {
    $position = 1;
    $db = new DB_Seminar();
    $db2 = new DB_Seminar();
    // Zuerst Mitglieder der Gruppe nach Nachnamen sortiert aus DB holen
    $sql =      "SELECT * FROM statusgruppe_user su
                LEFT JOIN auth_user_md5 a ON a.user_id=su.user_id
                WHERE statusgruppe_id = '".$statusgruppe_id."'
                ORDER BY a.Nachname";
    $db->query($sql);
    while ($db->next_record()) {
        // Positionierung neu vergeben
        $sql =  "UPDATE statusgruppe_user
                SET position=$position
                WHERE user_id = '".$db->f("user_id")."' AND statusgruppe_id='".$statusgruppe_id."'";
        $position++;
        $db2->query($sql);
    }
}

// Funktionen zur reinen Augabe von Statusgruppendaten


function PrintAktualStatusgruppen ($range_id, $view, $edit_id="")
{	global $PHP_SELF, $_fullname_sql;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT name, statusgruppe_id, size FROM statusgruppen WHERE range_id = '$range_id' ORDER BY position ASC");
	$AnzahlStatusgruppen = $db->num_rows();
	$i = 0;
	while ($db->next_record()) {
		$statusgruppe_id = $db->f("statusgruppe_id");
		$size = $db->f("size");
		echo "\n<table width=\"95%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
		echo "\n\t<tr>";
		echo "\n\t\t<td width=\"5%\">";
		printf ("<input type=\"IMAGE\" name=\"%s\" src=\"".$GLOBALS['ASSETS_URL']."images/move.gif\" border=\"0\" %s>&nbsp; </td>", $statusgruppe_id, tooltip(_("Markierte Personen dieser Gruppe zuordnen")));
		printf ("<td width=\"85%%\" class=\"%s\">&nbsp; %s </td><td class=\"%s\" width=\"5%%\" NOWRAP>%s
				%s
                <a href=\"$PHP_SELF?view=".$view."&cmd=sort_by_name&statusgruppe_id=".$statusgruppe_id."#".$statusgruppe_id."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/sort.gif\" border=\"0\" ".tooltip(_("Nach Nachnamen sortieren"))."></a>
                <a href=\"$PHP_SELF?cmd=edit_statusgruppe&edit_id=%s&range_id=%s&view=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/einst.gif\" border=\"0\" %s></a>
				</td>",
				$edit_id == $statusgruppe_id?"topicwrite":"topic",
				htmlReady($db->f("name")),
				$edit_id == $statusgruppe_id?"topicwrite":"topic",
				CheckStatusgruppeFolder($statusgruppe_id) ? "<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-disc.gif\" ".tooltip(_("Dateiordner vorhanden")).">" : "",
				CheckSelfassign($statusgruppe_id)?"<img src=\"".$GLOBALS['ASSETS_URL']."images/nutzer.gif\" ".tooltip(_("Personen können sich dieser Gruppe selbst zuordnen")).">":"",
				$statusgruppe_id,
				$range_id,
				$view,
				tooltip(_("Gruppenname oder -größe anpassen")));
		printf ( "<td width=\"5%%\"><a href=\"$PHP_SELF?cmd=remove_statusgruppe&statusgruppe_id=%s&range_id=%s&view=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash_att.gif\" border=\"0\" %s></a></td>",$statusgruppe_id, $range_id, $view, tooltip(_("Gruppe mit Personenzuordnung entfernen")));
		echo 	"\n\t</tr>";

		$db2->query ("SELECT statusgruppe_user.user_id, " . $_fullname_sql['full'] . " AS fullname , username, position FROM statusgruppe_user LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id) WHERE statusgruppe_id = '$statusgruppe_id' ORDER BY position ASC");
		$k = 1;
		while ($db2->next_record()) {
			if ($k > $size) {
				$farbe = "#AAAAAA";
			} else {
				$farbe = "#000000";
			}
			if ($k % 2) {
				$class="steel1";
			} else {
				$class="steelgraulight";
			}
			printf ("\n\t<tr>\n\t\t<td><font color=\"%s\">$k</font></td>", $farbe);
			printf ("<td class=\"%s\" ><font size=\"2\">%s</font></td>",$class, htmlReady($db2->f("fullname")));
			printf ("<td class=\"$class\" nowrap align=\"center\">");
			if ($k < $db2->num_rows())
				printf("<a href=\"$PHP_SELF?cmd=move_down&username=%s&statusgruppe_id=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/move_down.gif\" border=\"0\" %s></a>", $db2->f("username"), $statusgruppe_id, tooltip(_("Person nach unten bewegen")));
			else echo "&nbsp;&nbsp;&nbsp;";
			printf ("&nbsp;&nbsp;");
			if ($k > 1)
				printf("<a href=\"$PHP_SELF?cmd=move_up&username=%s&statusgruppe_id=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/move_up.gif\" border=\"0\" %s></a>", $db2->f("username"), $statusgruppe_id, tooltip(_("Person nach oben bewegen")));
			else echo "&nbsp;&nbsp;&nbsp;";
			printf ("&nbsp;</td><td><a href=\"$PHP_SELF?cmd=remove_person&statusgruppe_id=%s&username=%s&range_id=%s&view=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\" %s></a></td>", $statusgruppe_id, $db2->f("username"), $range_id, $view, tooltip(_("Person aus der Gruppe entfernen")));
			echo "\n\t</tr>";
			$k++;
		}
		while ($k <= $db->f("size")) {
			echo "\n\t<tr>\n\t\t<td><font color=\"#FF4444\">$k</font></td>";
			printf ("<td class=\"blank\" colspan=\"3\">&nbsp; </td>");
			echo "\n\t</tr>";
			$k++;
		}
		$i++;
		echo "</table>";
		if ($i < $AnzahlStatusgruppen) {
			printf ("<p align=\"center\"><a href=\"$PHP_SELF?cmd=swap&statusgruppe_id=%s&range_id=%s&view=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/move_up.gif\"  vspace=\"1\" width=\"13\" height=\"11\" border=\"0\"  %s><img src=\"".$GLOBALS['ASSETS_URL']."images/move_down.gif\" vspace=\"1\" width=\"13\" height=\"11\" border=\"0\" %s></a><br>&nbsp;",$statusgruppe_id, $range_id, $view, tooltip(_("Gruppenreihenfolge tauschen")), tooltip(_("Gruppenreihenfolge tauschen")));
		}
	}
}

function PrintSearchResults ($search_exp, $range_id)
{ global $SessSemName, $_fullname_sql,$_range_type;
	$db=new DB_Seminar;
	if ($_range_type == "sem") {
		$query = "SELECT a.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, perms FROM auth_user_md5 a ".
		"LEFT JOIN user_info USING (user_id) LEFT JOIN seminar_user b ON (b.user_id=a.user_id AND b.seminar_id='$range_id')  ".
		"WHERE perms IN ('autor','tutor','dozent') AND ISNULL(b.seminar_id) AND ".
		"(username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ".
		"ORDER BY Nachname";
	} else {
		$query = "SELECT DISTINCT auth_user_md5.user_id, " . $_fullname_sql['full_rev'] ." AS fullname, username, perms ".
		"FROM auth_user_md5 LEFT JOIN user_info USING (user_id) LEFT JOIN user_inst ON user_inst.user_id=auth_user_md5.user_id AND Institut_id = '$inst_id' ".
		"WHERE perms !='root' AND perms !='admin' AND perms !='user' AND (user_inst.inst_perms = 'user' OR user_inst.inst_perms IS NULL) ".
		"AND (Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%') ORDER BY Nachname ";
	}
	$db->query($query); // results all users which are not in the seminar
	if (!$db->num_rows()) {
		echo "&nbsp; " . _("keine Treffer") . "&nbsp; ";
	} else {
		echo "&nbsp; <select name=\"Freesearch[]\" size=\"4\" multiple>";
		while ($db->next_record()) {
			printf ("<option value=\"%s\">%s - %s\n", $db->f("username"), htmlReady(my_substr($db->f("fullname"),0,35)." (".$db->f("username").")"), $db->f("perms"));
		}
		echo "</select>";
	}
}

function PrintAktualMembers ($range_id)
{
	global $_fullname_sql,$_range_type;
	$bereitszugeordnet = GetAllSelected($range_id);
	if ($_range_type == "sem") {
		list($self_assign_all, $self_assign_exclusive) = CheckSelfAssignAll($range_id);
		echo "<font size=\"-1\">&nbsp; " . _("TeilnehmerInnen der Veranstaltung") . "</font><br>";
		$query = "SELECT seminar_user.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, perms FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id)  WHERE Seminar_id = '$range_id' ORDER BY Nachname ASC";
	} else {
		echo "<font size=\"-1\">&nbsp; " . _("MitarbeiterInnen der Einrichtung") . "</font><br>";
		$query = "SELECT user_inst.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, inst_perms AS perms FROM user_inst LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id)  WHERE Institut_id = '$range_id' AND inst_perms != 'user' AND inst_perms != 'admin' ORDER BY Nachname ASC";
	}
	echo "&nbsp; <select size=\"10\" name=\"AktualMembers[]\" multiple>";
	$db=new DB_Seminar;
	$db->query ($query);
	while ($db->next_record()) {
		if (in_array($db->f("user_id"), $bereitszugeordnet)) {
			$tmpcolor = "#777777";
		} else {
			$tmpcolor = "#000000";
		}
		if(!($self_assign_exclusive && in_array($db->f("user_id"), $bereitszugeordnet)) )
		printf ("<option style=\"color:%s;\" value=\"%s\">%s - %s\n", $tmpcolor, $db->f("username"), htmlReady(my_substr($db->f("fullname"),0,35)." (".$db->f("username").")"), $db->f("perms"));
	}
	echo "</select>";
}

function PrintInstitutMembers ($range_id)
{
	global $_fullname_sql;
	echo "<font size=\"-1\">&nbsp; " . _("MitarbeiterInnen der Einrichtungen") . "</font><br>";
	echo "&nbsp; <select name=\"InstitutMembers\">";
	$db=new DB_Seminar;
	$query = "SELECT a.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, inst_perms, perms FROM seminar_inst d LEFT JOIN user_inst a USING(Institut_id) ".
			"LEFT JOIN auth_user_md5  b USING(user_id) LEFT JOIN user_info USING (user_id) ".
			"LEFT JOIN seminar_user c ON (c.user_id=a.user_id AND c.seminar_id='$range_id')  ".
			"WHERE d.seminar_id = '$range_id' AND a.inst_perms IN ('tutor','dozent') AND ISNULL(c.seminar_id) GROUP BY a.user_id ORDER BY Nachname";
	$db->query($query); // ergibt alle berufbaren Personen
		printf ("<option>---</option>");
	while ($db->next_record()) {
		printf ("<option value=\"%s\">%s - %s\n", $db->f("username"), htmlReady(my_substr($db->f("fullname"),0,35)." (".$db->f("username").")"), $db->f("perms"));
	}
	echo "</select>";
}


// Ende Funktionen

// fehlende Werte holen

	$view = "statusgruppe_" . $_range_type;

	$db=new DB_Seminar;
	$db->query ("SELECT Name, status FROM seminare WHERE Seminar_id = '$range_id'");
	if (!$db->next_record()) {
		$db->query ("SELECT Name, type FROM Institute WHERE Institut_id = '$range_id'");
			if ($db->next_record()) {
				$tmp_typ = ($db->f('type')) ? $GLOBALS['INST_TYPE'][$db->f('type')]['name'] : $GLOBALS['INST_TYPE'][1]['name'];
			}
	} else {
		if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME) {
			$tmp_typ = _("Veranstaltung");
		} else {
			$tmp_typ = $SEM_TYPE[$db->f("status")]["name"];
			$veranstaltung_class = $SEM_TYPE[$db->f("status")]["class"];
		}
	}
	$workgroup_mode = $SEM_CLASS[$veranstaltung_class]['workgroup_mode'];  // are we in a workgroup?
	$tmp_name=$db->f("Name");
// Abfrage der Formulare und Aktionen

	// neue Statusgruppe hinzufuegen

	if (($cmd=="add_new_statusgruppe") && ($new_statusgruppe_name != "")) {
		AddNewStatusgruppe ($new_statusgruppe_name, $range_id, $new_statusgruppe_size, $new_selfassign, $new_doc_folder);
	}

	// Sortierung nach Nachname

	if ($cmd=="sort_by_name" && isset($statusgruppe_id)) {
		SortByName($statusgruppe_id);
	}

	// bestehende Statusgruppe editieren

	if (($cmd=="edit_existing_statusgruppe") && ($new_statusgruppe_name != "")) {
		EditStatusgruppe ($new_statusgruppe_name, $new_statusgruppe_size, $update_id, $new_selfassign, $new_doc_folder);
	}

	// bestehende Statusgruppe in Textfeld

	if ($cmd=="move_old_statusgruppe")  {
		$statusgruppe_name = $move_old_statusgruppe;
	} else {
		$statusgruppe_name = "unbenannt";
	}

	// zuordnen von Personen zu einer Statusgruppe
	if ($cmd=="move_person" && ($AktualMembers !="" || $InstitutMembers !="---" || $Freesearch !=""))  {

		while (list($key, $val) = each ($_POST)) {
			$statusgruppe_id = substr($key, 0, -2);
		}
		reset ($_POST);
		if ($statusgruppe_id != "sear")
			MovePersonStatusgruppe ($range_id, $AktualMembers, $InstitutMembers, $Freesearch, $workgroup_mode);
	}

	// Entfernen von Personen aus einer Statusgruppe

	if ($cmd=="remove_person") {
		RemovePersonStatusgruppe ($username, $statusgruppe_id);
	}

	// Entfernen von Statusgruppen

	if ($cmd=="remove_statusgruppe") {
		DeleteStatusgruppe ($statusgruppe_id);
	}

	// Aendern der Position

	if ($cmd=="swap") {
		SwapStatusgruppe ($statusgruppe_id);
	}

	// Reihenfolge innerhalb Gruppe ändern

	if ($cmd=="move_up") {
	 	MovePersonPosition ($username, $statusgruppe_id, "up");
	}

	if ($cmd=="move_down") {
	 	MovePersonPosition ($username, $statusgruppe_id, "down");
	}


	if(isset($_REQUEST['change_self_assign_x'])){
		SetSelfAssignAll($range_id, (bool)$_REQUEST['toggle_selfassign_all']);
		SetSelfAssignExclusive($range_id, (bool)$_REQUEST['toggle_selfassign_exclusive']);
		$check_multiple = CheckStatusgruppeMultipleAssigns($range_id);
		if(count($check_multiple)){
			$multis = '<ul>';
			foreach($check_multiple as $one){
				$multis .= '<li>' . htmlReady(get_fullname($one['user_id']) . ' ('. $one['gruppen'] . ')').'</li>';
			}
			$multis .= '</ul>';
			$msg[] = array('error', 
			_("Achtung, folgende Teilnehmer sind bereits in mehr als einer Gruppe eingetragen. Sie müssen die Eintragungen manuell korrigieren, um den exklusiven Selbsteintrag einzuschalten.")
			. '<br>'. $multis);
			SetSelfAssignExclusive($range_id, false);
		}
	}
	
// Ende Abfrage Formulare

$show_doc_folder = false;
if($view == 'statusgruppe_sem'){
	$module_check = new Modules();
	if($module_check->getStatus('documents', $range_id, 'sem')){
		$show_doc_folder = true;
	}
}

// Beginn Darstellungsteil

// Anfang Edit-Bereich

?><table cellspacing="0" cellpadding="0" border="0" width="100%">
	</tr><tr><td class="blank" colspan="2">&nbsp; </td></tr></table>

<table class="blank" width="100%" border="0" cellspacing="0">
  <tr>
    <td align="right" width="<?=($show_doc_folder ? '40' : '50')?>%" class="blank">

<?


	if ($cmd!="edit_statusgruppe") { // normale Anzeige
?>
	 	<form action="<? echo $PHP_SELF ?>?cmd=move_old_statusgruppe" method="POST">
	 	<?
	 	echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">&nbsp; ";
      	  	echo"<input type=\"HIDDEN\" name=\"view\" value=\"$view\"><font size=\"2\">" . _("Vorlagen:") . "</font>&nbsp; ";
		GetPresetGroups ($view,$veranstaltung_class);
		printf ("&nbsp; <input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/move.gif\" border=\"0\" %s>&nbsp;  ", tooltip(_("in Namensfeld übernehmen")));
	  ?>
	  </form>
<?
	}
?>
    </td>
    <td align="right" width="80%" NOWRAP class="blank" valign="top">
<?
	if ($cmd!="edit_statusgruppe") { // normale Anzeige
?>
		<form action="<? echo $PHP_SELF ?>?cmd=add_new_statusgruppe" method="POST">
		<?
	  	  echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">";
  	      	  echo"<input type=\"HIDDEN\" name=\"view\" value=\"$view\">";
	  	?>
	        <font size="2"><?=_("Gruppenname:")?> </font>
	        <input type="text" name="new_statusgruppe_name" value="<? echo htmlready(stripslashes($statusgruppe_name));?>">
	        &nbsp; <font size="2"><?=_("Gruppengröße:")?></font>
	        <input name="new_statusgruppe_size" type="text" value="" size="1">
	        <font size="2">&nbsp;
	      <?
		if($view == 'statusgruppe_sem') {
			echo _("Selbsteintrag");
			echo "<input style=\"vertical-align:middle\" type=\"checkbox\" name=\"new_selfassign\" value=\"1\">";
			if($show_doc_folder){
				echo chr(10) . '&nbsp;' . _("Dateiordner");
				echo chr(10) . '<input style="vertical-align:middle" type="checkbox" name="new_doc_folder" value="1">';
			}
		}
	      ?>
	        &nbsp; &nbsp; &nbsp; <b><?=_("Einf&uuml;gen")?></b>&nbsp;
	        <?
	    	printf ("<input type=\"IMAGE\" name=\"add_new_statusgruppe\" src=\"".$GLOBALS['ASSETS_URL']."images/move_down.gif\" border=\"0\" value=\" neue Statusgruppe \" %s>&nbsp;  &nbsp; &nbsp; ", tooltip(_("neue Gruppe anlegen")));
	    	?>
	      </form>
<?
	} else { // editieren einer bestehenden Statusgruppe
?>
		<form action="<? echo $PHP_SELF ?>?cmd=edit_existing_statusgruppe" method="POST">
		<?
		$db->query ("SELECT name, size FROM statusgruppen WHERE statusgruppe_id = '$edit_id'");
		if ($db->next_record()) {
			$gruppe_name = $db->f("name");
			$gruppe_anzahl = $db->f("size");
		}
	  	  echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">";
  	  	  echo"<input type=\"HIDDEN\" name=\"update_id\" value=\"$edit_id\">";
	    	  echo"<input type=\"HIDDEN\" name=\"view\" value=\"$view\">";
	  	?>
	        <font size="2"><?=_("neuer Gruppenname:")?> </font>
	        <input type="text" name="new_statusgruppe_name" value="<? echo htmlReady($gruppe_name);?>">
	        &nbsp; &nbsp; <font size="2"><?=_("neue Gruppengr&ouml;&szlig;e:")?></font>
	        <input name="new_statusgruppe_size" type="text" value="<? echo $gruppe_anzahl;?>" size="3"><font size="2">&nbsp; &nbsp;
<?	        echo _("Selbsteintrag");
		$check_self_assign = CheckSelfAssign($edit_id);
		echo "<input name=\"new_selfassign\" type=\"checkbox\" value=\"".($check_self_assign == 0 ? 1 : $check_self_assign)."\"";
	        if ($check_self_assign)
	        	echo "checked";
	        echo ">";
			if($show_doc_folder){
				if(CheckStatusgruppeFolder($edit_id)){
					echo chr(10). '&nbsp;' . _("Dateiordner vorhanden");
				} else {
					echo chr(10) . '&nbsp;' . _("Dateiordner");
					echo chr(10) . '<input style="vertical-align:middle" type="checkbox" name="new_doc_folder" value="1">';
				}
			}
?>
	        &nbsp; &nbsp; &nbsp; <b><?=_("&Auml;ndern")?></b>&nbsp;
	        <?
	    	printf ("<input type=\"IMAGE\" name=\"add_new_statusgruppe\" src=\"".$GLOBALS['ASSETS_URL']."images/move_down.gif\" border=\"0\" value=\" neue Statusgruppe \" %s>&nbsp;  &nbsp; &nbsp; ", tooltip(_("Gruppe anpassen")));
	    	?>
	      </form>
<?
	}
?>

      </td>
  </tr>
</table><?
// Ende Edit-Bereich
if(count($msg)){
?>
<table width="100%" border="0" cellspacing="0">
<?=parse_msg_array($msg)?>
</table>
<?
}

// Anfang Personenbereich
$db->query ("SELECT name, statusgruppe_id, size FROM statusgruppen WHERE range_id = '$range_id' ORDER BY position ASC");
if ($db->num_rows()>0) {   // haben wir schon Gruppen? dann Anzeige
	?><table width="100%" border="0" cellspacing="0">
<tr>
<td class="steel1" valign="top" width="50%">
<?if($view == 'statusgruppe_sem'){
	list($self_assign_all, $self_assign_exclusive) = CheckSelfAssignAll($range_id);
	?>
<table cellpadding="2" cellspacing="2" border="0" style="border:1px solid;margin:10px">
<tr>
<form action="<?=$PHP_SELF?>?range_id=<?=$range_id?>&view=statusgruppe_sem" method="post">
<td width="300"><font size="-1"><?=_("Selbsteintrag in allen Gruppen eingeschaltet")?></td>
<td>
<input type="checkbox" name="toggle_selfassign_all" value="1" <?=($self_assign_all ? 'checked' : '')?>>
</td>
<td rowspan="2">
&nbsp;
<?=makeButton('uebernehmen2','input',_("Einstellungen zum Selbsteintrag ändern"),'change_self_assign')?>
&nbsp;

</td>
</tr>
<tr>
<td width="300"><font size="-1"><?=_("Selbsteintrag nur in einer Gruppe erlauben")?></td>
<td>
<input type="checkbox" name="toggle_selfassign_exclusive" value="1" <?=($self_assign_exclusive ? 'checked' : '')?>>
</td>
</tr>
</form>
</table>
<?}?>
<form action="<? echo $PHP_SELF ?>?cmd=move_person" method="POST">
<?
	echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">\n";
	echo"<input type=\"HIDDEN\" name=\"view\" value=\"$view\">\n";
	if ($db->num_rows() > 0) {
		$nogroups = 1;
		if ($_range_type == "sem" || $_range_type == "inst" || $_range_type == "fak") {
			PrintAktualMembers ($range_id);
		}
		?>
		<br><br>
		<?
		if ($_range_type == "sem") {
			PrintInstitutMembers ($range_id);
		}
		?>
       	   <br><br>
		<?
		if ($search_exp) {
			PrintSearchResults(trim($search_exp), $range_id);
			printf ("<input type=\"IMAGE\" name=\"search\" src= \"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" border=\"0\" value=\" Personen suchen\" %s>&nbsp;  ", tooltip(_("neue Suche")));
		} else {
			echo "<font size=\"-1\">&nbsp; " . _("freie Personensuche") . "</font><br>";
			echo "&nbsp; <input type=\"text\" name=\"search_exp\" value=\"\">";
			printf ("<input type=\"IMAGE\" name=\"search\" src= \"".$GLOBALS['ASSETS_URL']."images/suchen.gif\" border=\"0\" value=\" Personen suchen\" %s>&nbsp;  ", tooltip(_("Person suchen")));
		}
	}
		?>
	<br><br>
    </td>
<? // Ende Personen-Bereich
?>
<? // Anfang Gruppenuebersicht

    	printf ("<td class=\"blank\" width=\"50%%\" align=\"center\" valign=\"top\">");
	PrintAktualStatusgruppen ($range_id, $view, $edit_id);
	?>
	<br>&nbsp;
   </form>
  </td>
 </tr>
</table>
<?
} else { // es sind noch keine Gruppen angelegt, daher Infotext
?>
<table class="blank" width="100%" border="0" cellspacing="0">
	<?

	if (get_config("EXTERNAL_HELP")) {
		$help_url=format_help_url("Basis.VeranstaltungenVerwaltenGruppen");
	} else {
		$help_url="help/index.php?referrer_page=admin_statusgruppe.php";
	}
	$msg = "info§" . _("Es sind noch keine Gruppen oder Funktionen angelegt worden.")
	. "<br>" . _("Um für diesen Bereich Gruppen oder Funktionen anzulegen, nutzen Sie bitte die obere Zeile!")
	. "<br><br>" . _("Mit dem Feld 'Gruppengröße' haben Sie die Möglichkeit, die Sollstärke für eine Gruppe festzulegen. Dieser Wert wird nur für die Anzeige benutzt - es können auch mehr Personen eingetragen werden.")
	. "<br>" . _("Wenn Sie Gruppen angelegt haben, können Sie diesen Personen zuordnen. Jeder Gruppe können beliebig viele Personen zugeordnet werden. Jede Person kann beliebig vielen Gruppen zugeordnet werden.")
	. "<br><br>" . sprintf(_("Lesen Sie weitere Bedienungshinweise in der %sHilfe%s nach!"), "<a href=\"".$help_url."\">", "</a>")
	. "§";
	parse_msg($msg);
	?>
</table>
<?php
}
// Ende Gruppenuebersicht
include ('lib/include/html_end.inc.php');
// Ende Darstellungsteil
page_close();
?>
