<?
# Lifter002: TODO
//			___________________________________________
//
// 			INITIALISIERUNG, HEADER, GLOBALE VARIABLEN
//			___________________________________________
//

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User", "ses" => "ses"));
$perm->check("admin");

//bla

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
//session_start();
{
	session_register("xml_import_org");
	session_register("xml_import_person");
	session_register("xml_import_raum");
	session_register("xml_import_titel");
}/**/

require_once ('config.inc.php'); 		//wir brauchen die Seminar-Typen
//require_once 'lib/functions.php';	//noch mehr Stuff
//require_once ("forum.inc");		//damit wir Themen anlegen koennen
require_once ('lib/msg.inc.php'); //Funktionen f&uuml;r Nachrichtenmeldungen
//if (!$xml_import_person)
//if ($sess->is_registered("xml_import_person")) echo "JOJO";
/*{
	$sess->register("xml_import_person");
	$sess->register("xml_import_raum");
	$sess->register("xml_import_titel");
	$sess->register("xml_import_org");
}/**/

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
$i_page = "admin_institut.php";
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins

//$xml_import_person = "MMM";
?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;Import von UniVis-Veranstaltungen im XML-Format</b></td>
</tr>
<tr><td class="blank" colspan=2>&nbsp;

<?

$db = new DB_Seminar;

$lauf=0;
if ($perm->have_perm("root")) 
	$db->query("select * from Institute ORDER BY Name");
else
	$db->query("SELECT * FROM Institute LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$user->id' AND inst_perms = 'admin' ORDER BY Name");
while ($db->next_record())
{	
	$inst_namen[$lauf] = $db->f("Name");
	$lauf++;
}

/*if ($perm->have_perm("root")) 
	$db->query("select * from faecher ORDER BY Name");
else
	$db->query("SELECT * FROM faecher LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$user->id' AND inst_perms = 'admin' ORDER BY Name");
$lauf=0;
while ($db->next_record())
{	
	$fach_namen[$lauf] = $db->f("Name");
	$lauf++;
}*/


// CHECKS:
if (isset($back))
	$assi_page = $assi_page - 2;
if ($assi_page == 0)
	unset($assi_page);
if (!isset($sem_nrp))
	$sem_nr = 0;
if (isset($sem_nrp))
	$sem_nr = $sem_nrp;
if ($sem_man <> "")
	$sem_nr = $sem_man;
if (!isset($ebene_alias))
	$ebene_alias = "Diese Ebene";
if (isset($org_name))
{
	$xml_import_org = "";
	$org_name = rawurldecode($org_name);
	$ebene_alias = rawurldecode($ebene_alias);
	$xml_import_org[$org_name]["anz"] = 1;
	$assi_page=3;
	echo "_".$org_name."_";
}

// Check, ob alle Institutsnamen eingegeben wurden und keine alten ueberschrieben werden
if (($assi_page == 3) AND (isset($edit_names)))
{
	$add_nr=0;
	$alias_nr=0;
	while (list ($key, $val) = each ($xml_import_org)) 
	{
		switch ($val["change"])
		{
			case "add": 
				$xml_import_org[$key]["name"] = $GLOBALS["inst_add".$add_nr];
				$add_nr++;
			break;
			case "anders": 
				if (trim($GLOBALS["inst_alias".$alias_nr]) == "")
				{
					my_error("Es wurde kein neuer Name f&uuml;r die Einrichtung \"" . $key ."\" angegeben.");
					$assi_page = 2;
				}
				for ($lauf=0; $lauf<sizeof($inst_namen); $lauf++)
					if (strcasecmp($GLOBALS["inst_alias".$alias_nr], $inst_namen[$lauf]) == 0)
					{
						my_error("Es existiert bereits eine Einrichtung mit dem Namen\"" . $GLOBALS["inst_alias".$alias_nr] ."\"");
						$assi_page = 2;
					}
				if ($assi_page != 2)
				{
					$xml_import_org[$key]["name"] = trim($GLOBALS["inst_alias".$alias_nr]);
					$xml_import_org[$key]["neu"] = true;
				}
				$alias_nr++;
			break;
			case "neu": 
				$xml_import_org[$key]["name"] = $key;
				$xml_import_org[$key]["neu"] = true;
			break;
			case "nicht": 
			break;
			default:
				$xml_import_org[$key]["name"] = $val["change"];
		}
	}
	reset($xml_import_org);
}

$default_orgname = "UnbekannteEinrichtung";

$v_names = array(
			"Lecture",
			"Title",
			"Person",
			"Room",
			
			"benschein", 
			"comment", 
			"ects",
			"ects_literature",
			"ects_name",
			"ects_organizational",
			"ects_summary",
			"first",
			"first_starttime", 
			"first_date",
			"first_endtime", 
			"keywords", 
			"literature", 
			"name", 
			"organizational",
			"orgname",
			"pflicht",
			"schein", 
			"senior", 
			"short",
			"richt",
			"sem",
			"studb_f", 
			"summary",
			"sws",
			"turnout",
			"starttime",
			"endtime",
			"mod",
			"time_description",
			"wday",
			"type",
			"url_description"
			
/*			"atitle",
			"firstname",
			"lastname",
			"lehr",
			"lehraufg",
			"lehrtyp",
			"email",
			"fax",
			"ort",
			"office",
			"street",
			"tel",
			"url",
			"mobile",
			"title",
			"visible",
			"work",
			"zweitmgl",
			
			"beamob",
			"beavcrpc",
			"description",
			"inet",
			"dia",
			"epidia",
			"film",
			"headphone",
			"mic",
			"leinw",
			"ohead",
			"size",
			"stuhlmob",
			"stuhlfest",
			"tafel",
			"voirec",
			"tv",
			"vcr",
			"vcrfest",
			"verd",
			"address",
			
			"text"*/
			);

$v_alias = array(
			"KEY: Seminar",
			"KEY: Titel",
			"KEY: Person",
			"KEY: Raum",

			"Benoteter Schein", 
			"Kommentar", 
			"ECTS",
			"ECTS-Literatur",
			"ECTS-Name",
			"ECTS-Organisation",
			"ECTS-Zusammenfassung",
			"first", 
			"erste Startzeit", 
			"Starttermin", 
			"erste Endzeit",
			"Schl&uuml;sselw&ouml;rter", 
			"Literatur", 
			"Name", 
			"Organisation",
			"Organisationsname",
			"Pflichtveranstaltung",
			"Schein", 
			"Senior", 
			"Kurzform",
			"Fachrichtung",
			"Univis-Sem-ID",
			"Studb_F", 
			"Zusammenfassung",
			"Semesterwochenstunden",
			"TO",
			"Startzeit",
			"Endzeit",
			"Zeitmodus",
			"Unregelm&auml;&szlig;ige Zeiten",
			"Wochentag",
			"Art der Veranstaltung",
			"Url-Weitere Informationen"
			
/*			"Alt. Titel",
			"Vorname",
			"Nachname",
			"Lehrauftrag",
			"Lehrtypus-Zusatz",
			"Lehrtypus",
			"e-mail-Adresse",
			"Fax",
			"Ort",
			"B&uuml;ro",
			"Stra&szlig;e",
			"Tel.",
			"Homepage",
			"Handy",
			"Titel",
			"Sichtbar",
			"Arbeit",
			"Zweitmitglied",
			
			"Mobiler Beamer",
			"Beamer,VCR,PC",
			"Raum-Beschreibung",
			"Internet",
			"Diaprojektor",
			"epiDiaprojektor",
			"Film",
			"Headphones",
			"Mikrophone",
			"Leinwand",
			"Overheadprojektor",
			"Sitzpl&auml;tze",
			"Mobile St&uuml;hle",
			"Feste St&uuml;hle",
			"Tafel",
			"Voicerecorder",
			"Fernseher",
			"VCR",
			"VCR fest",
			"verd",
			"Adresse",
			
			"Text"*/
			);

$sem_vars = array(
//			"benschein", 
//			"comment", 
//			"ects",
			"name", 
			"orgname",
			"classification",
			"doz",
			"room"
//			"starttime",
//			"endtime"
			);

$raum_vars = array(
			"short"
			);

$person_vars = array(
			"title",
			"firstname",
			"lastname",
			"orgname",
			"lehr",
//			"lehraufg",
			"lehrtyp",
			"email",
			"tel",
			"fax",
//			"street",
			"office"
			);

$titel_vars = array(
			"title"
			);

// Tabelle fuer Funtionen im Institut (Univis - StudIP)
$inst_funkt = array(
			"univpr" => "Professor",
			"unprzt" => "Professor",
			"ausser" => "Professor",
			"akakak" => "Akademischer Oberrat",
			"privat" => "Oberassistenten und Privatdozenten",
			"lehrbe" => "Lehrbeauftragter",
			"wissas" => "Wissenschaftlicher Assistent",
			"wissmi" => "Wissenschaftlicher Mitarbeiter",
			"lektor" => "Wissenschaftlicher Mitarbeiter",
			"honora" => "Wissenschaftlicher Mitarbeiter"
			);

//			___________________________________________
//
// 			FUNKTIONEN
//			___________________________________________
//
function lies_seminar()
{
	global $datei, $zeile, $var, $xml_import_raum, $xml_import_person, $person_vars, $xml_import_titel, $xml_import_org, $org_name, $ebene_alias, $default_orgname;

	$zeile = fgets($datei, 4096);

	$key_nr = 0;
	$var_nr = 0;
	while (((strpos($zeile, "/" . "Lecture") ===false)) AND (!feof($datei)))
	{
		if (strpos($zeile, " key=") >0)
		{
			$content = substr($zeile, strpos($zeile, "key=")+5, strpos($zeile, "\"",strpos($zeile,"key=")+5)-strpos($zeile, "key=")-5);
			$c_name = substr($zeile,1,strpos($zeile,">")-1);
			$content = substr($content, strpos($content, ":")+1, strlen($content)-strpos($content, ":")-1);
			switch ($c_name)
			{
				case "doz": 
				for ($lauf = 0; $lauf<sizeof($person_vars); $lauf++)
					$var[$c_name] .= $xml_import_person[$content][$person_vars[$lauf]]; 
				$var[$c_name] .= "<br>";
				break;
				case "room": $var[$c_name] .= $xml_import_raum[$content] . "<br>"; break;
				case "classification": $var[$c_name] .= $xml_import_titel[$content]["title"] . "<br>"; break;
			}
			$var_nr++;
		}
		elseif (strlen(strip_tags($zeile)) >2)
		{
			if ($var[ substr($zeile,1,strpos($zeile,">")-1)] != "")
				$var[ substr($zeile,1,strpos($zeile,">")-1)] .= "<br>";
			$var[ substr($zeile,1,strpos($zeile,">")-1)] .= trim( strip_tags($zeile));
			
			$var_nr++;
		}
	
		$zeile = fgets($datei, 4096);
		$zeile = str_replace("&#xFC;", "ü", $zeile);
		$zeile = str_replace("&#xE4;", "ä", $zeile);
		$zeile = str_replace("&#xF6;", "ö", $zeile);

	}
//	echo $var["orgname"] . $xml_import_org[$var["orgname"]]["anz"];
	if (!isset($var["orgname"])) 
		$var["orgname"] = $default_orgname;
	$var["orgname"] = str_replace("Eintr&auml;ge auf dieser Ebene", $ebene_alias, $var["orgname"]);
	if (!isset($xml_import_org[$var["orgname"]]["anz"]))
		$var = "";
//	echo $var["orgname"] . " - " . $xml_import_org[$var["orgname"]]["anz"] . "<br>";
}

function hole_object($string)
{
	global $datei, $zeile, $xml_import_raum, $xml_import_person, $xml_import_titel, $raum_vars, $person_vars, $titel_vars;

	while (((strpos($zeile, "/" . $string) ===false)) AND (!feof($datei)))
	{
		if ((strpos($zeile, " key=") >0) AND ($object_name == "") AND (strpos($zeile, $string)>0))
		{
			$object_name = substr($zeile, strpos($zeile, "key=")+5, strpos($zeile, "\"",strpos($zeile,"key=")+5)-strpos($zeile, "key=")-5);
			$object_name = substr($object_name, strpos($object_name, ":")+1, strlen($object_name)-strpos($object_name, ":")-1);
		} 
		elseif (strlen(strip_tags($zeile)) >2)
			switch($string)
			{
			case "Room":
				{
					if (in_array(substr($zeile,1,strpos($zeile,">")-1), $raum_vars))
					{
						$xml_import_raum[$object_name] .= strip_tags($zeile);
					}
				}
			break;
			case "Person":
				{
					if (in_array(substr($zeile,1,strpos($zeile,">")-1), $person_vars))
						$xml_import_person[$object_name][ substr($zeile,1,strpos($zeile,">")-1)] .= trim(strip_tags($zeile));
				}
			break;
			case "Title":
				{
					if (in_array(substr($zeile,1,strpos($zeile,">")-1), $titel_vars))
						$xml_import_titel[$object_name][ substr($zeile,1,strpos($zeile,">")-1)] .= trim(strip_tags($zeile));
				}
			break;
			}
	
		$zeile = fgets($datei, 4096);
		$zeile = str_replace("&#xFC;", "ü", $zeile);
		$zeile = str_replace("&#xE4;", "ä", $zeile);
		$zeile = str_replace("&#xF6;", "ö", $zeile);
	}
}

function hole_seminar()
{
	global $datei, $zeile, $sem_nr, $j, $var, $sem_vars;

	$var = "";
	lies_seminar();

	if ($var != "")
		echo "</tr><tr><td>$j</td>";
	for ($lauf = 0; $lauf<sizeof($sem_vars); $lauf++) 
		if ($var != "")
		{	
			echo "<td";
			if ($sem_vars[$lauf] == "doz") echo " nowrap";
			echo "><font size=2>" . $var[$sem_vars[$lauf]] . "</font></td>";
		}
}

function get_objects($string)
{
	global $datei, $zeile, $dateiname, $PHP_SELF, $xml_import_org, $ebene_alias, $inst_namen, $db, $perm, $default_orgname;

	$sem_anz = 0;
	while((!feof($datei)) AND (strpos($zeile, $string . " key") ===false))
	{
		$zeile = fgets($datei, 4096);
		if (strpos($zeile, "Lecture key=") >0)
			$sem_anz++;
		elseif (strpos($zeile, "orgname") >0)
		{
			$zeile = str_replace("Eintr&#xE4;ge auf dieser Ebene", $ebene_alias, $zeile);
			$zeile = str_replace("&#xFC;", "ü", $zeile);
			$zeile = str_replace("&#xE4;", "ä", $zeile);
			$zeile = str_replace("&#xF6;", "ö", $zeile);
			$zeile = str_replace("&#xDC;", "Ü", $zeile);
			$zeile = str_replace("&#xC4;", "Ä", $zeile);
			$zeile = str_replace("&#xD6;", "Ö", $zeile);
			$zeile = str_replace("&#xDF;", "ß", $zeile);
			$oname = trim(strip_tags($zeile));
			$xml_import_org[$oname]["anz"]++;
			if ($cl != "")
			{	
				$arr = explode(":", $cl);
				$xml_import_org[$oname][$cl] = $arr[2];
			}
			$cl = "";
		}
		elseif (strpos($zeile, "classification>") >0)
		{
			$cl = substr($zeile, strpos($zeile,  ":", strpos($zeile, "key=")+5)+1, strpos($zeile, "</classification>")-strpos($zeile, "key=")-15);
		}
		elseif ((strpos($zeile, "/Lecture>") >0) AND (!isset($oname)))
		{
			$xml_import_org[$default_orgname]["anz"]++;
			$arr = explode(":", $cl);
			$xml_import_org[$default_orgname][$cl] = $arr[2];
		}
	}
	if ($sem_anz > 0)
		echo "<b>$sem_anz</b> Veranstaltungen gefunden.<br>";
	if (!feof($datei))
	{
		$object_nr = 0;
		while (!feof($datei) AND ((strpos($zeile, " key") === false) OR (strpos($zeile, $string) > 0)))
		{
			if (strpos($zeile, $string . " key") != false) 
			{
				hole_object($string);
				$object_nr++;
			}
			$zeile = fgets($datei, 4096); 
		}
		switch ($string)
		{
			case "Person": $string = "Personen"; break;
			case "Room": $string = "R&auml;ume"; break;
			case "Title": $string = "Fachbereichsnamen"; break;
		}
		if ($object_nr > 0) 
			echo "<b>$object_nr</b> $string eingelesen.<br>";
	}
	else
		echo "Objekt '$string' nicht gefunden!<br>";
}


?>
<form action="<? echo $PHP_SELF;?>" method=post>
<?
//			___________________________________________
//
// 			HAUPTTEIL : DATEINAME EINGEBEN
//			___________________________________________
//
if ((!isset($assi_page)) OR (!is_readable($dateiname)))
{
	?>
	<tr><td class="blank" colspan=2>Name der XML-Datei</td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2 align=center>
	<br>
	<br>
	<center>
	<?
	if (!isset($dateiname))
	{
		$dateiname = "sowi.xml";
//		$dateiname = "ethno.xml";
	}
	?>
	Datei: <input type=text size=10 name="dateiname" value="<? echo $dateiname;?>">
	<input type=hidden name="assi_page" value="1">
	<input type=submit name="go_b" value="&ouml;ffnen">
	<br>
<!--	Eintr&auml;ge auf dieser Ebene: <input type=text size=30 name="ebene_alias" value="<? echo $ebene_alias;?>">-->
	</center>
	<br>
	<br>
	<?
	if (isset($assi_page))
		my_error("<b>Datei nicht gefunden!</b>");
}
//			_________________________________________________
//
// 			HAUPTTEIL : ASSISTENT SEITE 1: EINLESEN DER DATEN
//			_________________________________________________
//
elseif ($assi_page == 1)
{
	?>
	<tr><td class="blank" colspan=2>Auswahl der Einrichtungen</td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2 align=center>
	<?
	echo "<h2>Fakult&auml;t: ***</h2>";
	$xml_import_org = "";
	$xml_import_person = "";
	$xml_import_raum = "";
	$xml_import_titel = "";

	$datei = fopen($dateiname,"r");

	get_objects("Person");
	get_objects("Room");
	get_objects("Title");

	fclose($datei);

	if (sizeof($xml_import_org) > 0)
	{	
		$inst_nr = 0;
		echo "<b>" . sizeof($xml_import_org) . "</b> Einrichtungen gefunden:<br><table>";
		while (list ($key, $val) = each ($xml_import_org)) 
		{
			echo "<tr><td>";
			if ($key != $xml_import_org_name)
			{
				echo " <a href='" . $PHP_SELF . "?org_name=" . rawurlencode($key) . "&dateiname=$dateiname&ebene_alias=" . rawurlencode($ebene_alias) . "'>" . $key . " </a></td><td><b>" . $val["anz"] . " </b> Veranstaltung";
			}
			else 
			{
				echo " " . $key . " </td><td><b>" . $val["anz"] . " </b> Veranstaltung";
			}
			if ($val["anz"] != 1)
				echo "en";
			echo "</td><td>";
			?>
			Stud.IP-Einrichtung: 
			<?
				
			?>
			<select name="inst<? echo $inst_nr;?>" size=1>
			<option value="nicht">-> Nicht importieren
			<? if ($perm->have_perm("root")) 
				echo '<option value="anders">-> Unter anderem Namen anlegen';
			$inst_fach = str_replace( "für ", "", $key);
			$inst_fach = str_replace( "isches ", "", $inst_fach);
			$inst_fach = str_replace( "Abteilung", "", $inst_fach);
			$inst_fach = str_replace( "Einrichtung", "", $inst_fach);
			$inst_fach = str_replace( "Seminar", "", $inst_fach);
			$inst_fach = trim($inst_fach);
			if ($inst_fach == "") 
				$inst_fach = $key;
			$exist = false;
			for ($lauf=0; $lauf<sizeof($inst_namen); $lauf++)
				if (!(strpos($inst_namen[$lauf], $inst_fach) === false) OR (strcasecmp($inst_namen[$lauf], $key)==0))
				{
					$exist = true;
					echo '<option value="' . $inst_namen[$lauf] . '" selected>' . $inst_namen[$lauf];
				}
			if (($exist == false) OR (sizeof($inst_namen)>1))
				echo '<option value="add">-> Einer anderen Einrichtung hinzuf&uuml;gen';
			if (($exist == false) AND ($perm->have_perm("root"))) 
				echo '<option value="neu" selected>-> Neu anlegen';
			?>
			</select></td></tr>
			<?
			$inst_nr++;
		}
		echo "</td></tr></table>";
	}

	echo "<br>";
	?>
	<input type=hidden name="inst_anz" value="<? echo $inst_nr;?>">
	<input type=hidden name="assi_page" value="2">
	<input type=hidden name="dateiname" value="<? echo $dateiname;?>">
	<input type=submit name="back" value="Zur&uuml;ck">
	<input type=submit name="show" value="Weiter">
	<?
}
//			___________________________________________
//
// 			HAUPTTEIL : ASSISTENT SEITE 2: Umbenennen/Zuordnen von UniVis- zu Stud.IP-Instituten
//			___________________________________________
//
elseif ($assi_page == 2)
{
//	Wenn die Seite nicht zum zweiten Mal geladen wurde, ueberfluessige Institutsnamen loeschen.
	if ($inst_anz == sizeof($xml_import_org))
	{
		$inst_nr = 0;
		$lauf = 0;
		while (list ($key, $val) = each ($xml_import_org)) 
		{
			if ($GLOBALS["inst".$lauf]  == "nicht")
			{
//				array_splice($xml_import_org, $inst_nr, 1);
				unset($xml_import_org[$key]);
			}
			else
			{
//				if (in_array($GLOBALS["inst".$lauf], array("add", "anders", "neu")))
				$xml_import_org[$key]["change"] = $GLOBALS["inst".$lauf];
				$inst_nr++;
			}
			$lauf++;
		}
		reset($xml_import_org);
	}
//	Pruefen, ob Institute neu eingegeben oder zugeordnet werden sollen
/*	$inst_nr = 0;
	for ($lauf=0; $lauf<$inst_anz; $lauf++)
		if (($GLOBALS["inst".$lauf]  != "add") AND ($GLOBALS["inst".$lauf]  != "anders"))
			$inst_nr++;
	if (($inst_nr == $inst_anz) AND ($inst_nr != 0))
		$assi_page = 3;
	else*/
	{
		?>
		<tr><td class="blank" colspan=2>Zuordnung der Einrichtung</td></tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<tr><td class="blank" colspan=2 align=center>
		<?
		$inst_nr = 0;
		$add_nr = 0;
		$alias_nr = 0;
		echo "<table>";
		while (list ($key, $val) = each ($xml_import_org)) 
		{
			while ($GLOBALS["inst".$inst_nr] == "nicht")
				$inst_nr++;
//			if (!isset($GLOBALS["inst".$inst_nr]))
//				break;
//			if ($GLOBALS["inst".$inst_nr] != "nicht") 
			{
				echo "<tr><td>" . $key . " </td><td><b>" . $val["anz"] . " </b> Veranstaltung";
				if ($val["anz"] != 1)
					echo "en";
				echo "</td><td>";
			
				switch($val["change"])
				{
					case "add": 
					echo 'Hinzuf&uuml;gen zu: <select name="inst_add' . $add_nr . '">';
					for ($lauf=0; $lauf<sizeof($inst_namen); $lauf++)
					{
						if ($inst_namen[$lauf] != $key)
							echo '<option value="' . $inst_namen[$lauf] . '">' . $inst_namen[$lauf];
					}
					echo "</select>";
					$add_nr++;
					break;
					case "anders": 
					if ($perm->have_perm("root"))
						echo 'Neu anlegen als: <input type=text name="inst_alias' . $alias_nr . '" value=' . $GLOBALS["inst_alias" . $alias_nr] . ' size=40>';
					$alias_nr++;
					break;
					case "neu": 
					echo "Wird neu angelegt als: '" . $key . "'";
					break;
					default: 
					echo "Wird der Einrichtung '" . $val["change"] . "' hinzugef&uuml;gt"; 
				}
				echo "</td></tr>";
			}
			$inst_nr++;
		}
		echo "</table>";
		echo "<br><br>";
		?>
		<input type=hidden name="assi_page" value="3">
		<input type=hidden name="edit_names" value="true">
		<input type=hidden name="dateiname" value="<? echo $dateiname;?>">
		<input type=submit name="back" value="Zur&uuml;ck">
		<input type=submit name="show" value="Weiter">
		<?
	}
}
//			___________________________________________
//
// 			HAUPTTEIL : ASSISTENT SEITE 3: Fachbereiche
//			___________________________________________
//
if ($assi_page == 3)
{
		?>
		<tr><td class="blank" colspan=2>Zuordnung der F&auml;cher</td></tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<tr><td class="blank" colspan=2 align=center>
		<?
		$inst_nr = 0;
		$fach_nr = 0;
		echo "<table>";
		while (list ($key, $val) = each ($xml_import_org)) 
		{
			echo "<tr><td colspan=2><h3>" . $key;
			if (isset($val["name"]) AND ($val["name"] != $key))
				echo " -> " . $val["name"];
			echo "</h3></td></tr>";
			echo "<tr><td>";
			$inst_fach = str_replace( "f&uuml;r ", "", $key);
			$inst_fach = str_replace( "isches ", "", $inst_fach);
			$inst_fach = str_replace( "Abteilung", "", $inst_fach);
			$inst_fach = str_replace( "Zentrum", "", $inst_fach);
			$inst_fach = str_replace( "Einrichtung", "", $inst_fach);
			$inst_fach = str_replace( "Seminar", "", $inst_fach);
			$inst_fach = trim($inst_fach);
			$fach = array("");
			while (list ($key2, $val2) = each ($val)) 
				if (!in_array($key2, array("anz", "change", "name")) AND !in_array($val2, $fach))
				{
					if ($fach[1] == "")
						echo 'Bezeichnung f&uuml;r Studienfach "' . $val2 . '": </td><td><input type=text size=40 name="fach_n' . $lauf . '" value="' . $inst_fach . '"></td></tr><tr><td>';
					else
						echo 'Bezeichnung f&uuml;r Studienfach "' . $val2 . '": </td><td><input type=text size=40 name="fach_n' . $lauf . '" value="' . $val2 . '"></td></tr><tr><td>';
					$fach[ sizeof($fach)] = $val2;
				}
//			echo "Fach:" . $key . " </td><td>";
//			echo "<b>" . (sizeof($val)-1) . "</b> Bereiche:<br>";
/*			$fach_nr = 0;
			$fach = array("");
			while (list ($key2, $val2) = each ($val)) 
			{
				if ($key2 != "anz")
				{
					$arr = explode(":", $key2);
					$fach_key = $arr[2];
					$xml_import_titel[$key2]["fach"] = $fach_key;
					if (!in_array($fach_key, $fach))
					{	
						$fach[$fach_nr] = $fach_key;
						$fach_nr++;
					}
				}
			}
//			echo 'Bezeichnung f&uuml;r Studienfach "' . $fach[0] . '": </td><td><input type=text size=40 name="fach_n' . 0 . '" value="' . $inst_fach . '"></td></tr><tr><td>';
			for ($lauf=1; $lauf<$fach_nr; $lauf++)
				echo 'Studienfach "' . $fach[$lauf] . '": </td><td><input type=text size=40 name="fach_n' . $lauf . '" value="' . $fach[$lauf] . '"></td></tr><tr><td>';
*/
			reset($val);
			$tarr = array("");
			if (sizeof($fach) > 1)
			{
				echo "Studienfachbereiche:</td></tr><tr><td>";
				while (list ($key2, $val2) = each ($val)) 
				{
					if (!in_array($key2, array("anz", "name", "change")) AND (!in_array($xml_import_titel[$key2]["title"], $tarr)))
					{
						echo "- " . $xml_import_titel[$key2]["title"] . "";
						echo "</td><td><select>";
						for ($lauf=1; $lauf<sizeof($fach); $lauf++)
						{	
							echo '<option name="fach' . $lauf . '"';
							if ($val2 == $fach[$lauf])
								echo " selected";
							echo '>Fach "' . $fach[$lauf] . '"';
						}
						echo "</select><br></td></tr><tr><td>";
						$tarr[ sizeof($tarr)] = $xml_import_titel[$key2]["title"];
					}
				}
			}
			echo "&nbsp;</td></tr>";
		}
		echo "</table>";
		echo "<br><br>";
		?>
		<input type=hidden name="assi_page" value="4">
		<input type=hidden name="dateiname" value="<? echo $dateiname;?>">
		<input type=submit name="back" value="Zur&uuml;ck">
		<input type=submit name="show" value="Weiter">
		<?
}
//			___________________________________________
//
// 			HAUPTTEIL : ASSISTENT SEITE 4: Personen zuordnen
//			___________________________________________
//
if ($assi_page == 4)
{
		?>
		<tr><td class="blank" colspan=2>Zuordnung der Personen</td></tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<tr><td class="blank" colspan=2 align=center>
		<?
		echo "<table>";
		echo "<tr><th>Nr.</th><th>UniViS</th><th>Stud.IP</th></tr>";
		$person_nr = 0;
		while ((list ($key, $val) = each ($xml_import_person)))
		{
			if (!isset($xml_import_org[$val["orgname"]]))
				continue;
			if (($val["firstname"] == "") OR ($val["lastname"] == ""))
				continue;
			if (strcasecmp($val["title"] . $val["firstname"] . $val["lastname"], "n.n.") == 0)
				continue;
			$person_nr++;
			echo "<tr><td>$person_nr </td><td>";
			if (($val["lehr"] == "") AND ($val["email"] == ""))
				echo "<font color='#BBBBBB'>" . $val["title"] . " " . $val["firstname"] . " " . $val["lastname"] . " (Kein Lehrauftrag und fehlende E-Mail-Adr.)</font>";
			elseif ($val["lehr"] == "")
				echo "<font color='#999999'>" . $val["title"] . " " . $val["firstname"] . " " . $val["lastname"] . " (Kein Lehrauftrag)</font>";
			elseif ($val["email"] == "") 
				echo "<font color='#777777'>" . $val["title"] . " " . $val["firstname"] . " " . $val["lastname"] . " (E-Mail Adresse fehlt)</font>";
			else
				echo "" . $val["title"] . " " . $val["firstname"] . " " . $val["lastname"];// . "(" . $val["orgname"] . ")";

			$db->query("SELECT * FROM auth_user_md5 LEFT JOIN user_inst USING(user_id) LEFT JOIN institute USING(Institut_id)
				WHERE Vorname LIKE '%" . $val["firstname"] . "' 
				AND Nachname LIKE '%" . $val["lastname"] . "' 
				AND perms = 'dozent'");
			$pers_exist = 0;
			echo "</td><td>";
			while ($db->next_record()) 
			{	
				$p_data[$pers_exist] = "";
				if ((strcasecmp($db->f("Name"), $xml_import_org[$val["orgname"]]["name"]) != 0) OR ($db->f("Name") == ""))
					$p_data[$pers_exist]["comment"] .= "- falsche Einrichtung ";
				elseif ($db->f("inst_perms") != "dozent")
					$p_data[$pers_exist]["comment"] .= "- falscher Einrichtungs-Status ";
				$vn = "";
				if ($val["title"] != "")
					$vn = $val["title"] . " ";
				$vn .= $val["firstname"];
				if (strcasecmp(trim($db->f("Vorname")), $vn) != 0) 
					$p_data[$pers_exist]["comment"] .= "-Titel stimmt nicht &uuml;berein ";
				$p_data[$pers_exist]["name"] = "" . $db->f("Vorname") . " " . $db->f("Nachname") . "";
				$p_data[$pers_exist]["username"] = $db->f("username");
				$pers_exist++;
			}
			if ($pers_exist != 0)
				for ($i = 0; $i < $pers_exist; $i++)
				{
					if ($p_data[$i]["comment"] == "") 
						echo $p_data[$i]["name"] . ' (' . $p_data[$i]["username"] . ') <br>';
					else
						echo $p_data[$i]["name"] . ' (' . $p_data[$i]["username"] . ') ' . $p_data[$i]["comment"] . '<br>';
				}
			else
						echo "<font color='#BBBBBB'>Nicht vorhanden</font>";
/*			echo '<select name="person_' . $person_nr . '">';
			if ($pers_exist != 0)
			{
				echo '<option value="x">--- Bitte ausw&auml;hlen ---';
				for ($i = 0; $i < $pers_exist; $i++)
				{
					echo '<option value="' . $p_data[$i]["username"] . '"';
					if ($p_data[$i]["comment"] == "") 
						echo ' selected>' . $p_data[$i]["name"] . ' (' . $p_data[$i]["username"] . ') ';
					else
						echo '>' . $p_data[$i]["name"] . ' (' . $p_data[$i]["username"] . ') ' . $p_data[$i]["comment"];
				}
			}
			else
			{	
				echo '<option value="exist">Existierender Person zuordnen';
//				echo '<font color="#CC8888">' . $val["title"] . ' ' . $val["firstname"] . ' ' . $val["lastname"] . '</font>';
			}
			echo '<option value="nicht"'; 
			if (($val["email"] == "") AND ($val["lehr"] == "")) echo ' selected';
			echo '>Nicht importieren';
			if ($val["email"] == "")
			{
				echo '<option value="add"';
				if (($val["lehr"] != "") AND ($pers_exist == 0)) echo ' selected';
				echo '>Daten erg&auml;nzen';
			}
			else
			{
				echo '<option value="neu"';
				if (($val["lehr"] != "") AND ($pers_exist == 0)) echo ' selected';
				echo '>Person neu anlegen';
			}
			echo '</select>';/**/
			echo "</td></tr>";
		}
		echo "</table>";
		echo "<br><br>";
		?>
		<input type=hidden name="person_nr" value="1">
		<input type=hidden name="assi_page" value="5">
		<input type=hidden name="dateiname" value="<? echo $dateiname;?>">
		<input type=submit name="back" value="Zur&uuml;ck">
		<input type=submit name="show" value="Weiter">
		<?
}
//			___________________________________________
//
// 			HAUPTTEIL : ASSISTENT SEITE 5: Personen anlegen
//			___________________________________________
//
if ($assi_page == 5)
{
	$lauf = 0;
	if (isset($GLOBALS["person_0"]))
		while (list ($key, $val) = each ($xml_import_person)) 
		{
			switch ($GLOBALS["person_" . $lauf])
			{
				case "x": my_error("MURRRKS!!!");
				break;
				case "neu":
					$xml_import_person[$key]["change"] = $GLOBALS["person_" . $lauf];
				break;
				case "add":
					$xml_import_person[$key]["change"] = $GLOBALS["person_" . $lauf];
				break;
				case "nicht": 
					$xml_import_person[$key]["change"] = $GLOBALS["person_" . $lauf];
				break;
				case "exist":
					$xml_import_person[$key]["change"] = $GLOBALS["person_" . $lauf];
				break;
				default:
					$xml_import_person[$key]["username"] = $GLOBALS["person_" . $lauf];
			}
			$lauf++;
		}
	?>
	<tr><td class="blank" colspan=2>Personen anlegen</td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2 align=center>
	<?
	reset($xml_import_person);
	$i = 1;
	while (list ($key, $val) = each ($xml_import_person)) 
	{
		if (($i >= $person_nr) AND ($i < $person_nr+10))
		{
			echo "<b>Person $i</b><br>";
			echo '<table width="70%">';
			$uname =$val["firstname"][0] . $val["lastname"][0] . $val["lastname"][1] . $val["lastname"][2] . $val["lastname"][3] . $val["lastname"][4] . $val["lastname"][5];
			$uname = strtolower($uname);
			$uname = str_replace("ä","ae",$uname);
			$uname = str_replace("ö","oe",$uname);
			$uname = str_replace("ü","ue",$uname);
			$uname = str_replace("ß","ss",$uname);
			echo "<tr><td>Username:</td><td> " . $uname . "</td></tr>";
			echo "<tr><td>Vorname:</td><td> " . trim($val["title"] . " " . $val["firstname"]) . "</td></tr>";
			echo "<tr><td>Nachname:</td><td> " . $val["lastname"] . "</td></tr>";
			echo "<tr><td>Status:</td><td> dozent (" . $val["lehr"] . ")</td></tr>";
			echo "<tr><td>E-Mail:</td><td> " . $val["email"] . "</td></tr>";
			echo "<tr><td colspan=2>&nbsp;</td></tr>";
			echo "<tr><td>Einrichtung:</td><td>" . $xml_import_org[$val["orgname"]]["name"] . "</td></tr>";
			echo "<tr><td>Sprechzeiten:</td><td>&nbsp;" . "</td></tr>";
			echo "<tr><td>Raum:</td><td>" . $val["office"] . "</td></tr>";
			echo "<tr><td>Telefon:</td><td>" . $val["tel"] . "</td></tr>";
			echo "<tr><td>Faxnummer:</td><td>" . $val["fax"] . "</td></tr>";
			echo "<tr><td>Funktion in der Einrichtung:</td><td>" . $inst_funkt[$val["lehrtyp"]] . "</td></tr>";
/*			for ($lauf = 0; $lauf < sizeof($person_vars); $lauf++)	
			{
				echo "<tr><td>"; 
				echo $val[$person_vars[$lauf]] . "";
				echo"</td></tr>";
			}
*/			echo "</table><br>";
		}	
		$i++;
	}
	?>
	<input type=hidden name="person_nr" value="<? echo $person_nr+1;?>">
	<input type=hidden name="assi_page" value="5">
	<input type=hidden name="dateiname" value="<? echo $dateiname;?>">
	<input type=submit name="back" value="Zur&uuml;ck">
	<input type=submit name="show" value="Weiter">
	<?
}
//			___________________________________________
//
// 			HAUPTTEIL : ASSISTENT SEITE 6: Auslesen und Anzeigen der Seminare
//			___________________________________________
//
if ($assi_page == 6)
{
	?>
	<tr><td class="blank" colspan=2>Anzeige der Seminardaten</td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2 align=center>
	<?
	$datei = fopen($dateiname,"r");

	if ($datei)
	{
		$j = 0;
		while((!feof($datei)) AND ($j < $sem_nr))
		{
			$zeile = fgets($datei, 4096); 
			if (strpos($zeile, "Lecture key") >0)
				$j++;
		}
		if (feof($datei))
			echo "Seminar nicht gefunden!";

		$sem_nr = $j;
		echo "<table><tr><th>Nr.</th>";
		for ($lauf = 0; $lauf<sizeof($sem_vars); $lauf++) 
			echo "<th><b>" . $sem_vars[$lauf] . "</b></th>";
		while(!feof($datei) AND ($j<$sem_nr+600))
		{
			if (strpos($zeile, "Lecture key") != false) 
			{
				$j++;
				hole_seminar();
			}
			$zeile = fgets($datei, 4096); 
		}
		echo "</tr></table>";
	}
	fclose($datei);
	?>
	<input type=hidden name="assi_page" value="7">
	<input type=hidden name="dateiname" value="<? echo $dateiname;?>">
	<input type=submit name="back" value="Zur&uuml;ck">
	<input type=submit name="show" value="Weiter">
	<?
}

echo '</td></tr></table>
</form>';
include ('lib/include/html_end.inc.php');
page_close();
?>