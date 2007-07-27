<?php
/*
teilnehmer_view.php - Konfiguration der zusätzlich angezeigten Datenfeldern
in der Teilnehmeransicht

Copyright (C) 2007 

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

include "lib/seminar_open.php"; //hier werden die sessions initialisiert

require_once ("lib/msg.inc.php");
require_once ("lib/visual.inc.php");
require_once ("lib/functions.php");
require_once ("lib/admission.inc.php");	//Funktionen der Teilnehmerbegrenzung
require_once ("lib/statusgruppe.inc.php");	//Funktionen der Statusgruppen
require_once ("lib/messaging.inc.php");	//Funktionen des Nachrichtensystems
require_once ("config/config.inc.php");		//We need the config for some parameters of the class of the Veranstaltung
require_once ("lib/classes/Table.class.php");
require_once ("lib/classes/ZebraTable.class.php");

// Start  of Output
include ("lib/include/html_head.inc.php"); // Output of html head
include ("lib/include/header.php");   //hier wird der "Kopf" nachgeladen

checkObject();
checkObjectModule("participants");

include ("lib/include/links_openobject.inc.php");

$cssSw=new cssClassSwitcher;

// Aenderungen nur in dem Seminar, in dem ich gerade bin...

$db=new DB_Seminar;

$id = $SessSemName[1];

$sem_type = $SessSemName["art_num"];

if ($cmd == "change" && $perm->have_perm("dozent")) {
	foreach ($_REQUEST as $key => $val) {
		if ($key[0] == "#") {
			$zw = substr($key, 1, strlen($key));
			if ($val == 1) {
				$db->query("REPLACE INTO teilnehmer_view (datafield_id, seminar_id) VALUES ('$zw', '$id')");
			} else {
				$db->query("DELETE FROM teilnehmer_view WHERE datafield_id = '$zw' AND seminar_id = '$id'");
			}
		}
	}
}

$tbl_blank = array("class" => "blank", "colspan" => "2");

$table = new ContainerTable(array("cellspacing" => 0, "border" => "0", "width" => "100%", "cellpadding" => "0"));
$tbl2 = new ZebraTable(array("width" => "99%", "align" => "center"));

// Titelleiste und Leere Zeile
echo $table->headerRow("&nbsp;<b>". _("Teilnehmeransicht konfigurieren")."</b>");
echo $table->openRow().$table->openCell($tbl_blank);

// Daten
echo $tbl2->open();

if(is_array($GLOBALS['TEILNEHMER_VIEW']))
{
  foreach ($GLOBALS['TEILNEHMER_VIEW'] as $val)
  {
    $rights[$val["field"]] = TRUE;
  }
}

if (!isset($rights)) 
  $none = TRUE; 

if (!$none) {
	$query = "SELECT * FROM teilnehmer_view WHERE seminar_id = '$id'";
	$db->query($query);

	$active = array();
	while ($db->next_record()) {
		$active[$db->f("datafield_id")] = TRUE;
	}

	echo "<FORM action=\"$PHPSELF\" method=\"post\">";
	
	foreach ($GLOBALS['TEILNEHMER_VIEW'] as $data) {
		if ($rights[$data["field"]] == TRUE) {
			echo $tbl2->openRow();
			echo $tbl2->cell($data["name"], array("width" => "50%"));
			echo $tbl2->cell(($active[$data["field"]] ? _("wird angezeigt") :_("wird nicht angezeigt")), array("width" => "25%"));
			echo $tbl2->cell(sprintf("<INPUT type=\"radio\" name=\"#".$data["field"]."\" value=\"1\" %s>". _("anzeigen")."<INPUT type=\"radio\" name=\"#".$data["field"]."\" value=\"0\" %s>". _("nicht anzeigen"), ($active[$data["field"]]) ? "checked" : "", ($active[$data["field"]]) ? "" : "checked"));
			echo $tbl2->closeRow();
		}
	}

	echo $tbl2->openRow();
	echo $tbl2->cell("&nbsp;",array("colspan" => "2"));
	echo $tbl2->cell("<INPUT type=\"image\" ".makeButton("auswaehlen","src").">");
	echo $tbl2->closeRow();
	echo $tbl2->close();
	echo "<INPUT type=\"hidden\" name=\"cmd\" value=\"change\">";
	echo "</FORM>";
} else {
	echo "&nbsp;&nbsp;<B>". _("Die erweiterte Datenanzeige ist nicht aktiviert.") ."</B>";
}

// Abschluss für unten (Leere Zeile)
echo $table->closeCell().$table->closeRow();
echo $table->blankCell($tbl_blank);
// Alles schließen
echo $table->close();

// Save data back to database.
page_close();
?>
</body>
</html>

