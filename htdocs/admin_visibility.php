<?
/**
* admin_visibility.php - Sichtbarkeits-Administration von Stud.IP.
* Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, data-quest <info@data-quest.de>, (C) 2003 Tobias Thelen <tthelen@uni-osnabrueck.de>
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");
$perm->check("admin");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once($ABSOLUTE_PATH_STUDIP . "dates.inc.php"); // Funktionen zum Loeschen von Terminen
require_once($ABSOLUTE_PATH_STUDIP . "datei.inc.php"); // Funktionen zum Loeschen von Dokumenten
require_once($ABSOLUTE_PATH_STUDIP . "functions.php");
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/Table.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/ZebraTable.class.php");

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php"); // Output of Stud.IP head

// most of the logic happens in links_admin
// 
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php"); //Linkleiste fuer admins

function visibility_change_message($old_vis, $new_vis) {
	if ($old_vis) {
		if ($new_vis) {
			return _("ist weiterhin sichtbar");
		} else {
			return _("wurde versteckt");
		}
	} else {
		if ($new_vis) {
			return _("wurde sichtbar gemacht");
		} else {
			return _("ist weiterhin versteckt");
		}
	}
}

$sems=array();
// single delete (a Veranstaltung is open)
if ($SessSemName[1] && (!$change_visible)) {
	$visibility_sem[] = "_id_" . $SessSemName[1];
	$visibility_sem[] = "on";
	$single=true;
} 

// Handlings....
// A list was sent
if (is_array($visibility_sem)) {
	foreach($visibility_sem as $key => $val) {
		if ((substr($val, 0, 4) == "_id_") && (substr($visibility_sem[$key + 1], 0, 4) != "_id_"))
				if ($visibility_sem[$key + 1] == "on") {
					$sems[] = array("id" => substr($val, 4, strlen($val)), "visible" => 1);
				} else { 
					$sems[] = array("id" => substr($val, 4, strlen($val)), "visible" => 0);
			} 
		} 
}

// # Get a database connection
$db = new DB_Seminar;
//echo "<body>";
$containerTable=new ContainerTable();
echo $containerTable->headerRow("<b>&nbsp;" . _("&Auml;ndern der Sichtbarkeit von Veranstaltungen") . "</b>");
echo $containerTable->openRow();
echo $containerTable->openCell(array("colspan"=>"2"));

$contentTable=new ContentTable();
echo $contentTable->openCell();
$zt=new ZebraTable(array("width"=>"100%", "padding"=>"5"));
//echo $zt->open();
echo $zt->openHeaderRow();
echo $zt->cell("<b>"._("Nr.")."</b>",array("width"=>"5%"));
echo $zt->cell("<b>"._("Name")."</b>",array("width"=>"75%"));
echo $zt->cell("<b>"._("Sichtbarkeit")."</b>",array("width"=>"20%"));
echo $zt->closeRow();

if ($SessSemName[1] && (!$change_visible)) {
	$sql = "SELECT VeranstaltungsNummer, Name, visible FROM seminare WHERE Seminar_id='".$SessSemName[1]."'";	
	$db->query($sql);
	if ($db->next_record()) {
		$form	=	"<form name=\"asd\" action=\"".$PHP_SELF."\" method=\"POST\">";
		$form	.=	"<input type=\"checkbox\" name=\"visibility_sem[".$SessSemName[1]."]\"";
		if ($db->f("visible")) {
			$form .= " checked ";
		}
		$form	.=	">";
		$form	.=	"<input type=\"hidden\" name=\"all_sem[]\" value=".$SessSemName[1].">";
		$form 	.= 	"<input type=\"hidden\" name=\"change_visible\" value=\"1\">";
		$form	.=	"<input type=\"image\" ".makeButton("zuweisen","src")." border=0 align=\"absmiddle\">";
		$form	.=	"</form>";
		echo $zt->row(array(htmlready($db->f("VeranstaltungsNummer")), htmlready($db->f("Name")), $form));
	}

} else {
	for ($i=0;$i<count($all_sem);$i++) {
		$visible=false;
		$q="select VeranstaltungsNummer, Name, visible FROM seminare WHERE Seminar_id='". $all_sem[$i] . "'";
		$db->query($q);
		if ($db->next_record()) {
			if (is_array($visibility_sem)) {
				reset($visibility_sem);
				while (list($key, $val)=each($visibility_sem)) {
					if (($all_sem[$i]==$key) && $val=="on") {
						$visible = true;
					}
				}
			}
			if ($visible && ($db->f("visible")!=1)) {
				echo $zt->row(array(htmlready($db->f("VeranstaltungsNummer")), htmlready($db->f("Name")), visibility_change_message($db->f("visible"), 1)));
				$q="UPDATE seminare SET visible=1 WHERE Seminar_id='". $all_sem[$i] . "'";
				$db->query($q);
			} else if ($visible && ($db->f("visible")==1)) {
				echo $zt->row(array(htmlready($db->f("VeranstaltungsNummer")), htmlready($db->f("Name")), visibility_change_message($db->f("visible"), 1)));
			} else if (!$visible && $db->f("visible") != 0) {
				$q = "UPDATE seminare SET visible=0 WHERE Seminar_id='".$all_sem[$i]."'";
				$db->query($q);
				 echo $zt->row(array(htmlready($db->f("VeranstaltungsNummer")), htmlready($db->f("Name")), visibility_change_message($db->f("visible"), 0)));
			} else {
				echo $zt->row(array(htmlready($db->f("VeranstaltungsNummer")), htmlready($db->f("Name")), visibility_change_message($db->f("visible"), 0)));
			}
			$visible = false;
		} else {
			echo $zt->row(array("&nbsp;", $db->f("Name"), "<font color=red>". _("Änderung fehlgeschlagen") . "</font>"));
		}
	}
}

echo $zt->close();

echo $contentTable->close();

echo $containerTable->blankRow();
echo $containerTable->close();
echo "</body>";
echo "</html>";
page_close();

