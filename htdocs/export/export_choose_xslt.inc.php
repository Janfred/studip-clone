<?

require_once ("$ABSOLUTE_PATH_STUDIPexport/export_config.inc.php");   // Konfigurationsdatei
require_once ("$ABSOLUTE_PATH_STUDIPexport/export_xslt_vars.inc.php");   // Liste der XSLT-Skripts
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");   // Datumsfunktionen

$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
$cssSw->enableHover();

// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

echo "\n" . $cssSw->GetHoverJSFunction() . "\n";

	include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");
$cssSw->enableHover();


function CheckParam()
{
global $range_id, $ex_type, $xml_file_id, $page, $format, $choose, $xslt_files;
if ($page==1) 
{
	while (list($key, $val) = each($xslt_files))
		if ($val[$ex_type] AND $val[$format])
			$mod_counter++;
	if ($mod_counter == 0)
	{	
		echo "<table>";
		my_error("F&uuml;r dieses Format sind keine Ausgabemodule installiert.<br>Bitte w&auml;hlen Sie ein anderes Ausgabeformat.");
		echo "</table>";
		$page = 0;
	}

	if ($format == "")
		$page = 0;
	reset($xslt_files);
}

if ( ($page==2) AND ($choose == "") )
	$page = 1;
if (($xml_file_id != "") AND (in_array($ex_type, array("veranstaltung", "person", "forschung"))))
	return true;
echo "<table>";
my_error("<b>Unzul&auml;ssiger Seitenaufruf!</b>");
echo "</table>";
}

?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="2"><b>Konvertierung der Daten</b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="2">&nbsp; 
		</td>
	</tr>
	<tr valign="top">
     		<td width="90%" class="blank">
<? CheckParam();
?>

<?		
if (!isset($page) or ($page == 0)) // Seite 1 : Auswahl des Dateiformats
{ 
	?>
	<form method="POST" action="<? echo $PHP_SELF ?>">
	<table>
	<?
	my_info("Bitte w&auml;hlen Sie, in welchem Format die Daten ausgegeben werden sollen!<br>");
	?>
	</table>
	Ausgabeformat: <select name="format">
	<?
	while (list($key, $val) = each($output_formats))
	{
		?><option value="<? echo $key;?>" <? if ($format==$key) echo " selected";?>><? echo $val . " (" . $key . ")";
	}
	?>
	</select><br>
	<br><br>
	Name der Datei (z.B. "Test"):
	<input type="text" name="xslt_filename" value="<? echo $xslt_filename;?>">
	<input type="hidden" name="page" value="1"><br><br><br><br>
	<input type="hidden" name="ex_type" value="<? echo $ex_type; ?>">
	<input type="hidden" name="xml_file_id" value="<? echo $xml_file_id;?>">
	<center><input type="IMAGE" <? echo makeButton("weiter", "src"); ?> name=""></center>
	</form>
	<?
	$infobox = array	(			
	array ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => "Diese Seite bereitet die Datenausgabe vor.<br><i>Schritt 1/3</i>"
							 )
						)
		)
	);
	$link = "<a href=\"./test.xml"."\">";
	$infobox[1]["kategorie"] = "Aktionen:";
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
									"text"  => "Bitte w&auml;hlen Sie das Dateiformat, in dem ihre Daten ausgegeben werden sollen.."
								);
}
elseif ($page == 1) // Seite 2 : Auswahl des XSLT-Scripts
{
	?>
	<table>
	<?
	my_info("W&auml;hlen Sie bitte eine der folgenden XSLT-Dateien und klicken Sie auf 'weiter'");
	?>
	</table>
	<form method="POST" action="<? echo $PHP_SELF ?>">
	<br><br>
	<table cellspacing="0" cellpadding="1" border="0" width="100%">
		<tr align="center" valign="top">
			<th width="5%"><b>&nbsp;</b></th>
			<th width="15%" align="left">Ausgabemodul</th>
			<th width="80%"><b>Beschreibung</b></th>
		</tr>
	<?
	$opt_num = 0;
	while (list($key, $val) = each($xslt_files))
	{
		if ($val[$ex_type] AND $val[$format])
		{
			$cssSw->switchClass();
			?><tr <? echo $cssSw->getHover();?>>
			<td class="<? echo $cssSw->getClass(); ?>">&nbsp;<input type="radio" name="choose" value="<? echo $key?>" <? if ($opt_num == 0) echo " checked"?>>&nbsp;</td>
			<td class="<? echo $cssSw->getClass(); ?>"><? echo $val["name"]?>&nbsp;</td>
			<td class="<? echo $cssSw->getClass(); ?>"><? echo $val["desc"];?></td>
			</tr><?
			$opt_num++;
		}
	}
	?>
	</table>
	<br>
	<input type="hidden" name="page" value="2">
	<input type="hidden" name="format" value="<? echo $format;?>">
	<input type="hidden" name="ex_type" value="<? echo $ex_type; ?>">
	<input type="hidden" name="xml_file_id" value="<? echo $xml_file_id;?>">
	<input type="hidden" name="xslt_filename" value="<? echo $xslt_filename;?>">
	<center><input type="IMAGE" <? echo makeButton("weiter", "src"); ?> name=""></center>
	</form>
	<?
	$infobox = array	(			
	array ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => "Diese Seite bereitet die Datenausgabe vor.<br><i>Schritt 2/3</i>"
							 )
						)
		)
	);
	$link = "<a href=\"./test.xml"."\">";
	$infobox[1]["kategorie"] = "Aktionen:";
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
									"text"  => "W&auml;hlen Sie bitte eins der zur Verf&uuml;gung stehenden Ausgabemodule"
								);
}
elseif ($page == 2)  // Seite 3 : dudeldadel
{
	?>
	<table>
	<?
	my_info("Die ben&ouml;tigten Dateien liegen nun zum Download bereit.");
	?>
	</table>
	<form method="POST" action="<? echo $PHP_SELF;?>">
	<br><br>
	<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><?
	printhead ("99%", 0, "", "close", true, "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\"><img src=\"./pictures/" . $export_icon["xml"] . "\" border=0></a>", "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\">" . $xml_filename . "</a>", "XML-Daten");
	?></tr></table><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>
	<?
	printcontent("99%", FALSE, "In dieser Datei sind die Daten als XML-Tags gespeichert. Diese Tags k&ouml;nnen mit einem XSLT-Script verarbeitet werden. Klicken Sie auf den Dateinamen, um die Datei herunterzuladen.<br>", "");
	?>
	</tr></table>
	<?
	?><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><?
	printhead ("99%", 0, "", "close", true, "<a href=\"sendfile.php?type=2&file_id=" . $xslt_files[$choose]["file"] . "&file_name=" . $xslt_files[$choose]["name"] . ".xsl\"><img src=\"./pictures/" . $export_icon["xslt"] . "\" border=0></a>", "<a href=\"sendfile.php?type=2&file_id=" . $xslt_files[$choose]["file"] . "&file_name=" . $xslt_files[$choose]["name"] . ".xsl\">" . $xslt_files[$choose]["name"] . ".xsl</a>", "XSLT-Datei");
	?></tr></table><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>
	<?
	printcontent("99%", FALSE, "Dies ist das XSLT-Script zur Konvertierung der Daten. Klicken Sie auf den Dateinamen, um die Datei zu &ouml;ffnen.<br>", "");
	?>
	</tr></table>
	<br><br><br>
	<input type="hidden" name="page" value="3">
	<input type="hidden" name="choose" value="<? echo $choose;?>">
	<input type="hidden" name="format" value="<? echo $format;?>">
	<input type="hidden" name="ex_type" value="<? echo $ex_type; ?>">
	<input type="hidden" name="xml_file_id" value="<? echo $xml_file_id;?>">
	<input type="hidden" name="xslt_filename" value="<? echo $xslt_filename;?>">
	<?
	if ($XSLT_ENABLE) 
	{
		?>Um die Daten mit dem installierten XSLT-Prozessor in das gew&uuml;nschte Format zu bringen, klicken Sie bitte auf 'weiter'.<br><br><?
		?><center><input type="IMAGE" <? echo makeButton("weiter", "src"); ?> name=""></center><?
	}
	?>
	</form>
	<?
	$infobox = array	(			
	array ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => "Diese Seite bereitet die Datenausgabe vor.<br><i>Schritt 3/3</i>"
							 )
						)
		)
	);
	$link = "<a href=\"./test.xml"."\">";
	$infobox[1]["kategorie"] = "Aktionen:";
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
									"text"  => "Sie k&ouml;nnen nun XML-Daten und  Ausgabemodul herunterladen."
								);
	if ($XSLT_ENABLE) 
	{
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
									"text"  => "Wenn Sie auf 'weiter' klicken, wird mit dem installierten XSLT-Prozessor die Ausgabedatei erzeugt."
								);
	}
}
	?>
	</td>
	<td width="270" NOWRAP class="blank" align="center" valign="top">
	<?

print_infobox ($infobox,"pictures/export.jpg");

?>		
		</td>		
	</tr>
	<tr>
		<td class="blank" colspan="2">&nbsp; 
		</td>
	</tr>
</table>
<p>&nbsp;</p>
<?