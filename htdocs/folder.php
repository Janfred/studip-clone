<?php
/*
folder.php - Anzeige und Verwaltung des Ordnersystems
Copyright (C) 2001 Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>

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

ob_start();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once("$ABSOLUTE_PATH_STUDIP/datei.inc.php");
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");

$sess->register("folder_system_data");
$db=new DB_Seminar;
$db2=new DB_Seminar;

if ($folderzip) {
	$zip_file_id = createFolderZip($folderzip);
	$query = sprintf ("SELECT name FROM folder WHERE folder_id = '%s' ", $folderzip);
	$db->query($query);
	$db->next_record();
	$zip_name = prepareFilename(_("Dateiordner ").$db->f("name").".zip");
	header("Location: sendfile.php/?type=4&file_id=$zip_file_id&file_name=$zip_name");
	page_close();
	die;
}

if ($download_selected_x) {
	if (is_array($download_ids)) {
		$zip_file_id = createSelectedZip($download_ids);
		$zip_name = prepareFilename($SessSemName[0]." - "._("Dokumente").".zip");
		header("Location: sendfile.php/?type=4&file_id=$zip_file_id&file_name=$zip_name");
		page_close();
		die;
	}
}

//Switch fuer die Ansichten
if ($cmd=="tree") {
	$folder_system_data='';
	$folder_system_data["cmd"]="tree";
	}
if ($cmd=="all") {
	$folder_system_data='';
	$folder_system_data["cmd"]="all";
	}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head

//JS Routinen einbinden, wenn benoetigt. Wird in der Funktion gecheckt, ob noetig...
JS_for_upload();
//we need this <body> tag, sad but true :)
echo "\n<body onUnLoad=\"upload_end()\">"; 


include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

checkObject();
checkObjectModule("documents");

include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");


//Wenn nicht Rechte und Operation uebermittelt: Ist das mein Dokument?
if ((!$rechte) && strpos($open, "_")) {
	$db->query("SELECT user_id FROM dokumente WHERE dokument_id = '".substr($open, 0, strpos($open, "_"))."'");
	$db->next_record();
	if (($db->f("user_id") == $user->id) && ($db->f("user_id") != "nobody"))
		$owner=TRUE;
	else
		$owner=FALSE;
} else
	$owner=FALSE;

if (($rechte) || ($owner)) {
	//wurde Code fuer Anlegen von Ordnern ubermittelt (=id+"_n_"), wird entsprechende Funktion aufgerufen
	if ((strpos($open, "_n_")) && (!$cancel_x)) {
		$change=create_folder(_('Neuer Ordner'), '', substr($open, (strpos($open, "_n_"))-32, (strpos($open, "_n_"))));
		$open=$change;
		}

	//wurde Code fuer Anlegen von Ordnern der obersten Ebene ubermittelt (=id+"_a_"), wird entsprechende Funktion aufgerufen
	if (strpos($open, "_a_")) {
		if (substr($open, (strpos($open, "_a_"))-32, (strpos($open, "_a_"))) == $SessionSeminar) {
			$titel=_("Allgemeiner Dateiordner");
			$description= sprintf(_("Ablage für allgemeine Ordner und Dokumente der %s"), $SessSemName["art_generic"]);
			}
		
		$db->query("SELECT date, date_typ, content FROM termine WHERE termin_id='".substr($open, (strpos($open, "_a_"))-32, (strpos($open, "_a_")))."'");
		if ($db->next_record()) {
			//Titel basteln
			$titel=$TERMIN_TYP[$db->f("date_typ")]["name"].": ".substr($db->f("content"), 0, 35);
			if (strlen($db->f("content")) >=35)
				$titel.="...";
			$titel.=" " . _("am") . " ".date("d.m.Y ", $db->f("date"));
			$description= _("Ablage für Ordner und Dokumente zu diesem Termin");
			}
			
		$change=create_folder(addslashes($titel), $description, substr($open, (strpos($open, "_a_"))-32, (strpos($open, "_a_"))));
		}

	//wurde Code fuer Loeschen von Ordnern ubermittelt (=id+"_d_"), wird entsprechende Funktion aufgerufen
	if (strpos($open, "_d_")) {
		delete_folder(substr($open, (strpos($open, "_d_"))-32, (strpos($open, "_d_"))));
		}
	
	//wurde Code fuer Loeschen von Dateien ubermittelt (=id+"_fd_"), wird erstmal nachgefragt
	if (strpos($open, "_fd_")) {
		$db->query("SELECT filename, ". $_fullname_sql['full'] ." AS fullname, username FROM dokumente LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE dokument_id ='".substr($open, (strpos($open, "_fd_"))-32, (strpos($open, "_fd_")))."'");
		$db->next_record();
		if (getLinkPath(substr($open, (strpos($open, "_fd_"))-32, (strpos($open, "_fd_"))))) {
			$msg="info§" . sprintf("Wollen sie die Verlinkung zu <b>%s</b> von %s wirklich löschen?", htmlentities(stripslashes($db->f("filename"))), "<a href=\"about.php?username=".$db->f("username")."\">".$db->f("fullname")."</a>") . "<br>";
			$msg.="<b><a href=\"$PHP_SELF?open=".substr($open, (strpos($open, "_fd_"))-32, (strpos($open, "_fd_")))."_rl_\">" . makeButton("ja2", "img") . "</a>&nbsp;&nbsp; <a href=\"$PHP_SELF\">" . makeButton("nein", "img") . "</a>§";
		} else {
			$msg="info§" . sprintf("Wollen sie die Datei <b>%s</b> von %s wirklich löschen?", htmlentities(stripslashes($db->f("filename"))), "<a href=\"about.php?username=".$db->f("username")."\">".$db->f("fullname")."</a>") . "<br>";
			$msg.="<b><a href=\"$PHP_SELF?open=".substr($open, (strpos($open, "_fd_"))-32, (strpos($open, "_fd_")))."_rm_\">" . makeButton("ja2", "img") . "</a>&nbsp;&nbsp; <a href=\"$PHP_SELF\">" . makeButton("nein", "img") . "</a>§";
		}
	}

	//Loeschen von Dateien im wirklich-ernst Mode
	if (strpos($open, "_rm_")) {
		if (delete_document(substr($open, (strpos($open, "_rm_"))-32, (strpos($open, "_rm_")))))
			$msg.="msg§" . _("Die Datei wurde gel&ouml;scht") . "§";
		else
			$msg.="error§" . _("Die Datei konnte nicht gel&ouml;scht werden") . "§";
		} 

	//Loeschen von verlinkten Dateien im wirklich-ernst Mode
	if (strpos($open, "_rl_")) {
		if (delete_link(substr($open, (strpos($open, "_rl_"))-32, (strpos($open, "_rl_")))))
			$msg.="msg§" . _("Die Verlinkung wurde gelöscht") . "§";
		else
			$msg.="error§" . _("Die Verlinkung konnte nicht gelöscht werden") . "§";
		}

	//wurde Code fuer Aendern des Namens und der Beschreibung von Ordnern oder Dokumenten ubermittelt (=id+"_c_"), wird entsprechende Funktion aufgerufen
	if (strpos($open, "_c_")) {
		$change=substr($open, (strpos($open, "_c_"))-32, (strpos($open, "_c_")));
		}

	//wurde Code fuer Speichern von Aenderungen uebermittelt (=id+"_sc_"), wird entsprechende Funktion aufgerufen
	if ((strpos($open, "_sc_")) && (!$cancel_x)) {
		edit_item (substr($open, (strpos($open, "_sc_"))-32, (strpos($open, "_sc_"))), $type, $change_name, $change_description);
		}

	//wurde Code fuer Verschieben-Vorwaehlen uebermittelt (=id+"_m_"), wird entsprechende Funktion aufgerufen
	if ((strpos($open, "_m_")) && (!$cancel_c)) {
		$folder_system_data["move"]=substr($open, (strpos($open, "_m_"))-32, (strpos($open, "_m_")));
		}
	}


//Upload, Check auf Konsistenz mit Seminar-Schreibberechtigung
if (($SemUserStatus == "autor") || ($rechte)) {
	//wurde Code fuer Hochladen uebermittelt (=id+"_u_"), wird entsprechende Variable gesetzt
	if ((strpos($open, "_u_")) && (!$cancel_x)) {
		$folder_system_data["upload"]=substr($open, (strpos($open, "_u_"))-32, (strpos($open, "_u_")));
		}	

	//wurde Code fuer Verlinken uebermittelt (=id+"_l_"), wird entsprechende Variable gesetzt
	if ((strpos($open, "_l_")) && (!$cancel_x)) {
		$folder_system_data["link"]=substr($open, (strpos($open, "_l_"))-32, (strpos($open, "_l_")));
	}

	//wurde Code fuer Aktualisieren-Hochladen uebermittelt (=id+"_rfu_"), wird entsprechende Variable gesetzt
	if ((strpos($open, "_rfu_")) && (!$cancel_x)) {
		$folder_system_data["upload"]=substr($open, (strpos($open, "_rfu_"))-32, (strpos($open, "_rfu_")));
		$folder_system_data["refresh"]=substr($open, (strpos($open, "_rfu_"))-32, (strpos($open, "_rfu_")));
		}	
	
	//wurde eine Datei hochgeladen/aktualisiert?
	if (($cmd=="upload") && (!$cancel_x) && ($folder_system_data["upload"])) {
		upload_item ($folder_system_data["upload"], TRUE, FALSE, $folder_system_data["refresh"]);
		$open = $dokument_id;
		$close = $folder_system_data["refresh"];
		$folder_system_data["upload"]='';
		$folder_system_data["refresh"]='';		
		unset($cmd);
		}
		
	//wurde eine Datei verlinkt?
	if (($cmd=="link") && (!$cancel_x) && ($folder_system_data["link"])) {
		if (link_item ($folder_system_data["link"], TRUE, FALSE, $folder_system_data["refresh"])) {
			$open = $dokument_id;
			$close = $folder_system_data["refresh"];
			$folder_system_data["link"]='';
			$folder_system_data["refresh"]='';		
			unset($cmd);
		} else {
			$folder_system_data["linkerror"]=TRUE;	
		}
	}
		
	if ($cancel_x)  {
		$folder_system_data["upload"]='';
		$folder_system_data["refresh"]='';
		$folder_system_data["link"]='';
		unset($cmd);
	}
}
	
//wurde Code fuer Starten der Verschiebung uebermittelt (=id+"_md_"), wird entsprechende Funktion aufgerufen (hier kein Rechtecheck noetig, da Dok_id aus Sess_Variable.
if ((strpos($open, "_md_")) && (!$cancel_x)) {
	move_item ($folder_system_data["move"], substr($open, (strpos($open, "_md_"))-32, (strpos($open, "_md_"))));
	$folder_system_data["move"]='';
	}

//wurde ein weiteres Objekt aufgeklappt?
if ($folder_system_data["open"]) {
	if ((!strstr($folder_system_data["open"], $open)) &&  (!strpos($open, "_"))) {
		$folder_system_data["open"].=$open;
		}
	}
else
	$folder_system_data["open"]=$open;

//wurde ein Objekt zugeklappt?
if ($close) {
	$pos=strpos($folder_system_data["open"], $close);
	if ($pos)
		$folder_system_data["open"]=substr($folder_system_data["open"], 0, $pos).substr($folder_system_data["open"], $pos+32, strlen($folder_system_data["open"])); 
		
	else
		$folder_system_data["open"]=substr($folder_system_data["open"], 32, strlen($folder_system_data["open"])); 	
	}

// Hauptteil

 if (!isset($range_id))
 	$range_id = $SessionSeminar ;

?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td class="topic" colspan="2"><b>&nbsp;<img src="pictures/icon-disc.gif" align=absmiddle>&nbsp; <? echo $SessSemName["header_line"] . " - " . _("Dateien"); ?></b></td></tr>

		<tr>
			<td class="blank" colspan=2>&nbsp;
				<?
				if ($msg) parse_msg($msg);
				?>
			</td>
		</tr>


<?
	//Ordner die fehlen, anlegen: Allgemeiner, wenn nicht da, Ordner zu Terminen, die keinen Ordner haben
	if (($rechte) && ($folder_system_data["cmd"]=="tree")) {
		$db2->query("SELECT name FROM folder WHERE range_id='$range_id'");
		if (!$db2->affected_rows())
			$select="<option value=\"".$range_id."_a_\">" . _("Allgemeiner Dateiordner") . "</option>";
		
		$db2->query("SELECT termine.date, folder.name, termin_id, date_typ FROM termine LEFT JOIN folder ON (termin_id = folder.range_id) WHERE termine.range_id='$range_id' ORDER BY name, termine.date");
		while (($db2->next_record()) && (!$db2->f("name"))) {
			$select.="<option value=\"".$db2->f("termin_id")."_a_\">" . sprintf(_("Dateiordner zum Termin am %s [%s]"), date("d.m.Y", $db2->f("date")), $TERMIN_TYP[$db2->f("date_typ")]["name"]) . "</option>";
			}

		if ($select) {
			?>
			<tr>
			<td class="blank" colspan="2" width="100%">			
			<blockquote>
			<p valign="middle">
			<form action="<? echo $PHP_SELF?>" method="POST">
				<input type="image" name="anlegen" value="<?=_("Neuer Ordner")?>" align="absmiddle" <?=makeButton("neuerordner", "src")?> border=0 />&nbsp;
				<select name="open" style="vertical-align:middle">
					<? echo $select ?>				
				</select>
			</form>
			</p>
			</blockquote>
			<?
			}
		}
		
	//when changing, uploading or show all (for download selector), create a form
	if ((($change) || ($folder_system_data["cmd"]=="all")) && (!$folder_system_data["upload"])) {
		echo "<form method=\"post\" action=\"$PHP_SELF\">";
		}
	
	print "<tr><td class=\"blank\" colspan=\"2\" width=\"100%\">";


	if ($folder_system_data["cmd"]=="all") {
		?>
		<blockquote>
		<? printf (_("Hier sehen Sie alle Dateien, die zu dieser %s eingestellt wurden. Wenn Sie eine neue Datei einstellen m&ouml;chten, w&auml;hlen Sie bitte die Ordneransicht und &ouml;ffnen den Ordner, in den Sie die Datei einstellen wollen."), $SessSemName["art_generic"]); ?>
		</blockquote>
		<?
		if (!$folder_system_data["upload"] && !$folder_system_data["link"])
			print ("<div align=\"right\"><a href=\"$PHP_SELF?check_all=TRUE\">".makeButton("alleauswaehlen")."</a>&nbsp;<input style=\"vertical-align: middle;\" type=\"IMAGE\" name=\"download_selected\" border=\"0\" ".makeButton("herunterladen", "src")." />&nbsp;</div>");		
		}
		
	//Alle Termine der Veranstaltung holen
	$db->query("SELECT termin_id FROM termine INNER JOIN folder ON(termin_id=folder.range_id) WHERE termine.range_id='$range_id' ORDER BY date");
	
	//Treeview
	if ($folder_system_data["cmd"]=="tree") {
		//Seminar...
		display_folder_system($range_id, 0,$folder_system_data["open"], '', $change, $folder_system_data["move"], $folder_system_data["upload"], FALSE, $folder_system_data["refresh"], $folder_system_data["link"]);
		while ($db->next_record()) {
			//und einzelne Termine	
			display_folder_system($db->f("termin_id"), 0,$folder_system_data["open"], '', $change, $folder_system_data["move"], $folder_system_data["upload"], FALSE, $folder_system_data["refresh"], $folder_system_data["link"]);
			}
		}
	
	//Alle / Listview
	else {
		?><table border=0 cellpadding=0 cellspacing=0 width="100%"><tr><?
		display_folder_system($range_id, 0,$folder_system_data["open"], '', $change, $folder_system_data["move"], $folder_system_data["upload"], TRUE, $folder_system_data["refresh"], $folder_system_data["link"]);		
		?><td class="blank" width="*">&nbsp;</td></tr></table><?
		}
	
	//und Form wieder schliessen
	if ($change)
		echo "</form>";				
?>
<br>
</td>
</tr>
</table>
<br>
<?

  // Save data back to database.
  page_close()
?>
</body>
</html>
