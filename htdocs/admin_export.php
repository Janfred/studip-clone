<?
//			___________________________________________
//
// 			INITIALISIERUNG, HEADER, GLOBALE VARIABLEN
//			___________________________________________
//
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User", "ses" => "ses"));
$perm->check("admin");

include "seminar_open.php"; //hier werden die sessions initialisiert
require_once ("dates.inc.php"); 		//Datumsfunktionen
require_once ("config.inc.php"); 		//wir brauchen die Seminar-Typen
require_once ("msg.inc.php"); //Funktionen f&uuml;r Nachrichtenmeldungen
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
	<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
</head>
<body>

<?
include "header.php";   //hier wird der "Kopf" nachgeladen 
$i_page = "admin_institut.php";
include "links_admin.inc.php";  //Linkleiste fuer admins

?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;Export von Stud.IP-Veranstaltungen als Vorlesungskommentar</b></td>
</tr>
<tr><td class="blank" colspan=2 align="center">&nbsp;
<?

$db = new DB_Seminar;

include ("admin_exportfilter.inc.php");
if (isset($next))
{	
	if (!isset($export_endung[$format]) OR !isset($export_header[$format]) OR !isset($export_footer[$format])) // fehlende Variablen
	{
		echo "</td></tr>";
		my_error("Der Filter f&uuml;r das Dateiformat $format ist unvollst&auml;ndig! (admin_exportfilter.inc.php)");
		echo "<tr><td class=\"blank\" colspan=2 align=\"center\">";
	}
	else
		$export_page++;
}
if (!isset($export_page))
	$export_page = 0;
if (sizeof($export_filter) == 0)
{
	echo "</td></tr>";
	my_error("Es wurden keine Export-Filter gefunden! (Datei 'admin_export_filter.inc.php')");
	echo "<tr><td class=\"blank\" colspan=2 align=\"center\">";
	$export_page = -23;
}
//elseif (sizeof($export_format) == 1)
//	$export_page = 1; // Auswahl ueberspringen, wenn nur ein Filter vorhanden.

function formline($str1, $str2)
{
	global $export_filter, $format;
	if ($str1 != "")
	{
		$z = $export_filter[$format]["fett"];
		$z .= $export_filter[$format]["content"];
		$z .= $str1;
		$z .= $export_filter[$format]["/content"];
		$z .= $export_filter[$format]["/fett"];
	}
	$z .= $export_filter[$format]["content"];
	$z .= $str2;
	$z .= $export_filter[$format]["/content"];
	$z .= $export_filter[$format]["neuezeile"];
	return $z;
}

function gib_inhalt()
{
	global $export_filter, $format, $datei, $db, $lastbereich, $sem_nr, $bereich_nr;
	
	$sem_nr++;
	if ($db->f("name") != $lastbereich)
	{
		$bereich_nr++;
		$zeile = $export_filter[$format]["font1"] . $export_filter[$format]["size12"];
		fputs($datei, $zeile . formline($bereich_nr . ". ", $db->f("name")));
		fputs($datei, $export_filter[$format]["/font"]);
		$lastbereich = $db->f("name");
	}

	$zeile = $export_filter[$format]["font1"] . $export_filter[$format]["size10"];
	$zeile .= $export_filter[$format]["content"];
	$zeile .= "   " . $db->f("Name");
	$zeile .= $export_filter[$format]["/content"];
	$zeile .= $export_filter[$format]["/font"];
	$zeile .= $export_filter[$format]["neuezeile"];
	fputs($datei, $zeile);
}

function gib_person()
{
	global $export_filter, $format, $datei, $db;
	
/*	if ($db->f("name") != $lastbereich)
	{
		$bereich_nr++;
		$zeile = $export_filter[$format]["font1"] . $export_filter[$format]["size12"];
		fputs($datei, $zeile . formline($sem_nr . ". ", $db->f("name")));
		fputs($datei, $export_filter[$format]["/font"]);
		$lastbereich = $db->f("name");
	}

	$zeile = $export_filter[$format]["font1"] . $export_filter[$format]["size10"];
	$zeile .= $export_filter[$format]["content"];
	$zeile .= "   " . $db->f("Name");
	$zeile .= $export_filter[$format]["/content"];
	$zeile .= $export_filter[$format]["/font"];
	$zeile .= $export_filter[$format]["neuezeile"];*/

	$zeile = sprintf( $export_filter[$format]["tab5"], $db->f("Vorname") . " " . $db->f("Nachname"), $db->f("sprechzeiten"), $db->f("raum"), $db->f("Telefon"), $db->f("Fax"));
	fputs($datei, $zeile);
}

function gib_seminar()
{
	global $export_filter, $format, $datei, $db, $lastbereich, $sem_nr, $bereich_nr;
	
	$sem_nr++;
	if ($db->f("name") != $lastbereich)
	{
		$bereich_nr++;
		if ($lastbereich != "nope")
			fputs($datei, $export_filter[$format]["neueseite"]);
		$zeile = $export_filter[$format]["font1"] . $export_filter[$format]["size20"];
		fputs($datei, $zeile . formline($bereich_nr . ". ", $db->f("name")));
		fputs($datei, $export_filter[$format]["/font"]);
		fputs($datei, $export_filter[$format]["neuezeile"]);
		$lastbereich = $db->f("name");
	}

	$zeile = $export_filter[$format]["font1"] . $export_filter[$format]["size16"] . $export_filter[$format]["fett"]
	 . $export_filter[$format]["content"] . "Seminar: " . $db->f("Name") . $export_filter[$format]["/content"]
	 . $export_filter[$format]["/fett"] . $export_filter[$format]["/font"];
	$zeile = sprintf( $export_filter[$format]["rahmen"], $zeile);
	fputs($datei, $zeile);

	$zeile = $export_filter[$format]["font1"] . $export_filter[$format]["size12"];
	fputs($datei, $zeile);
	if ($db->f("Untertitel") != "") 
		fputs($datei, formline("Untertitel: ", $db->f("Untertitel")));
	if ($db->f("status") != "") 
		fputs($datei, formline("Status: ", $db->f("status")));
	if ($db->f("Ort") != "") 
		fputs($datei, formline("Ort:", $db->f("Ort")));
	if ($db->f("Beschreibung") != "") 
		fputs($datei, formline("Beschreibung: ", $db->f("Beschreibung")));
	if ($db->f("art") != "") 
		fputs($datei, formline("Art der Veranstaltung: ", $db->f("art")));
	if ($db->f("teilnehmer") != "") 
		fputs($datei, formline("Teilnahme: ", $db->f("teilnehmer")));
	if ($db->f("vorraussetzungen") != "") 
		fputs($datei, formline("Voraussetzungen: ", $db->f("vorraussetzungen")));
	if ($db->f("lernorga") != "") 
		fputs($datei, formline("Lernorganisation: ", $db->f("lernorga")));
	if ($db->f("leistungsnachweis") != "") 
		fputs($datei, formline("Leistungsnachweis: ", $db->f("leistungsnachweis")));
	if ($db->f("metadata_dates") != "") 
		fputs($datei, formline("Termin: ", view_turnus($db->f("Seminar_id"))));
	if ($db->f("sonstiges") != "") 
		fputs($datei, formline("Sonstiges: ", $db->f("sonstiges")));
	fputs($datei, $export_filter[$format]["/font"]);
	fputs($datei, $export_filter[$format]["neuezeile"]);
}

?>
<form action="<? echo $PHP_SELF;?>" method=post>
<?
if ($export_page  == 0)
{
	?>
	<br>
	<br>
	Institut: <select name="instituts_id">
	<?
	if ($perm->have_perm("root")) 
		$db->query("select * from Institute ORDER BY Name");
	else
		$db->query("SELECT * FROM Institute LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$user->id' AND inst_perms = 'admin' ORDER BY Name");
	while ($db->next_record())
	{	
		?><option value="<? echo $db->f("Institut_id");?>"><? echo $db->f("Name");
	}
	?>
	</select><br>
	<br>
	Ausgabeformat: <select name="format">
	<?
	while (list($key, $val) = each($export_filter))
	{
		?><option value="<? echo $key;?>"><? echo $export_name[$key] . " (" . $export_endung[$key] . ")";
	}
	?>
	</select></br>
	<br>
	<input type="hidden" name="export_page" value="<? echo $export_page;?>">
	<input type="submit" name="next" value="Weiter"><br>
	<br>
	<?
}
elseif ($export_page == 1)
{
	$dateiname = "kommentar" . $export_endung[$format];
	if (file_exists("./tmp/" . $dateiname)) // Datei schon vorhanden
	{	
		if (!unlink("./tmp/" . $dateiname))
		{
			echo "</td></tr>";
			my_error("Auf die Datei '$dateiname' konnte nicht zugegriffen werden, m&ouml;glicherweise wird sie gerade bearbeitet.");
			echo "</form></table></body></html>";
			exit;
		}
		echo "</td></tr>";
		my_error("Die existierende Datei '$dateiname' wird &uuml;berschrieben.");
		echo "<tr><td class=\"blank\" colspan=2 align=\"center\">";
	}
	$datei = fopen("./tmp/" . $dateiname,"w");

	if ($datei)
	{
		fputs($datei, $export_header[$format]);

		$sem_nr = 0;
		$bereich_nr = 0;
		$db->query("SELECT * FROM seminare LEFT JOIN seminar_bereich USING(seminar_id) LEFT JOIN bereiche USING(bereich_id) WHERE Institut_id = '$instituts_id' ORDER BY bereiche.name");
		$lastbereich = "nope";
		fputs($datei, $export_filter[$format]["neueseite"]);
		fputs($datei, $export_filter[$format]["font1"] . $export_filter[$format]["size14"] . $export_filter[$format]["fett"]
		 . $export_filter[$format]["content"] . "Inhaltsverzeichnis" . $export_filter[$format]["/content"]
		 . $export_filter[$format]["/fett"] . $export_filter[$format]["/font"] . $export_filter[$format]["neuezeile"] . $export_filter[$format]["neuezeile"]);
		while ($db->next_record())
		{	
			gib_inhalt();
		}
		$db->query("SELECT * FROM auth_user_md5 LEFT JOIN user_inst USING(user_id) LEFT JOIN Institute USING(Institut_id) WHERE Institute.Institut_id = '$instituts_id' AND perms = 'dozent'");
		fputs($datei, $export_filter[$format]["neueseite"]);
		fputs($datei, $export_filter[$format]["font1"] . $export_filter[$format]["size14"] . $export_filter[$format]["fett"]
		 . $export_filter[$format]["content"] . "MitarbeiterInnen" . $export_filter[$format]["/content"]
		 . $export_filter[$format]["/fett"] . $export_filter[$format]["/font"] . $export_filter[$format]["neuezeile"] . $export_filter[$format]["neuezeile"]);
		fputs($datei, $export_filter[$format]["tab"]); 
		fputs($datei, sprintf( $export_filter[$format]["tab5"], 
		$export_filter[$format]["fett"] . $export_filter[$format]["content"] . "Name" . $export_filter[$format]["/content"] . $export_filter[$format]["/fett"], 
		$export_filter[$format]["fett"] . $export_filter[$format]["content"] . "Sprechzeiten" . $export_filter[$format]["/content"] . $export_filter[$format]["/fett"], 
		$export_filter[$format]["fett"] . $export_filter[$format]["content"] . "Raum" . $export_filter[$format]["/content"] . $export_filter[$format]["/fett"], 
		$export_filter[$format]["fett"] . $export_filter[$format]["content"] . "Tel 39-" . $export_filter[$format]["/content"] . $export_filter[$format]["/fett"], 
		$export_filter[$format]["fett"] . $export_filter[$format]["content"] . "Fax" . $export_filter[$format]["/content"] . $export_filter[$format]["/fett"]));
		while ($db->next_record())
		{	
			gib_person();
		}
		fputs($datei, $export_filter[$format]["/tab"]); 
		$sem_nr = 0;
		$bereich_nr = 0;
		$db->query("SELECT * FROM seminare LEFT JOIN seminar_bereich USING(seminar_id) LEFT JOIN bereiche USING(bereich_id) WHERE Institut_id = '$instituts_id' ORDER BY bereiche.name");
		$lastbereich = "nope";
		fputs($datei, $export_filter[$format]["neueseite"]);
		fputs($datei, $export_filter[$format]["font1"] . $export_filter[$format]["size14"] . $export_filter[$format]["fett"]
		 . $export_filter[$format]["content"] . "Lehrveranstaltungen" . $export_filter[$format]["/content"]
		 . $export_filter[$format]["/fett"] . $export_filter[$format]["/font"] . $export_filter[$format]["neuezeile"] . $export_filter[$format]["neuezeile"]);
		while ($db->next_record())
		{	
			gib_seminar();
		}
		fputs($datei, $export_footer[$format]);
		if ($sem_nr == 0)
		{
			echo "</td></tr>";
			my_error("Es sind keine Veranstaltungen f&uuml;r dieses Institut eingetragen.");
			echo "<tr><td class=\"blank\" colspan=2 align=\"center\">";
		}
		else
		{	
			echo "$sem_nr Veranstaltungen wurden ausgegeben: ";
			if ($format == "html")
			{
				?><a href="<? echo "./tmp/" . $dateiname;?>"><img src='./pictures/<? echo $export_icon[$format];?>' border=0></a><?
			}
			else
			{
				?><a href="sendfile.php?type=0&file_id=<? echo "$dateiname";?>&file_name=<? echo rawurlencode($dateiname);?>"><img src='./pictures/<? echo $export_icon[$format];?>' border=0></a><?
			}
		}
	}
	else // $datei == false
	{
		echo "</td></tr>";
		my_error("Die Datei '$dateiname' konnte nicht angelegt werden.");
		echo "<tr><td class=\"blank\" colspan=2 align=\"center\">";
	}
	fclose($datei);
	?>
	<br><br>
	<?
}
?>
</form>
</td></tr></table>
<? 
page_close()
?>
</body>
</html>