<?
$output_formats = array(
	"htm"		=>		"Hypertext (HTML)", 
	"txt"			=>		"Text (TXT)", 
	"blafasel"		=>		"Blafasel Text Format (BTF)", 
	"rtf"			=>		"Rich Text Format (RTF)", 
	"xsl"			=>		"Adobe Postscript (PDF)"
);

$xslt_files[0]["name"] = "Standardmodul";
$xslt_files[0]["desc"] = "Dies ist ein Standardmodul zur einfachen Ausgabe von Veranstaltungs- oder Personen-Daten als HTML-Datei.";
$xslt_files[0]["file"] = "html-v-1.xsl";
$xslt_files[0]["htm"] = true;
$xslt_files[0]["html"] = true;
$xslt_files[0]["person"] = true;
$xslt_files[0]["veranstaltung"] = true;

$xslt_files[1]["name"] = "Standardmodul";
$xslt_files[1]["desc"] = "Dies ist ein Standardmodul zur einfachen Ausgabe von Veranstaltungs- oder Personen-Daten als Textdatei.";
$xslt_files[1]["file"] = "txt-v-1.xsl";
$xslt_files[1]["txt"] = true;
$xslt_files[1]["person"] = true;
$xslt_files[1]["veranstaltung"] = true;

$xslt_files["txt-standard"]["name"] = "Standardmodul";
$xslt_files["txt-standard"]["desc"] = "Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten im Textformat. Die Daten werden nur mit Tabulatoren und Bindestrichen formatiert.";
$xslt_files["txt-standard"]["file"] = "txt-vp-1.xsl";
$xslt_files["txt-standard"]["txt"] = true;
$xslt_files["txt-standard"]["person"] = true;
$xslt_files["txt-standard"]["veranstaltung"] = true;

$xslt_files["html-standard"]["name"] = "Standardmodul";
$xslt_files["html-standard"]["desc"] = "Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten als HTML-Seite.";
$xslt_files["html-standard"]["file"] = "html-vp-1.xsl";
$xslt_files["html-standard"]["htm"] = true;
$xslt_files["html-standard"]["html"] = true;
$xslt_files["html-standard"]["person"] = true;
$xslt_files["html-standard"]["veranstaltung"] = true;

$xslt_files["html-druck"]["name"] = "Druckmodul";
$xslt_files["html-druck"]["desc"] = "Modul zur Ausgabe von Personen- oder Veranstaltungsdaten als HTML-Seite. Es wird eine druckbare HTML-Seite ohne Farben erzeugt.";
$xslt_files["html-druck"]["file"] = "html-vp-2.xsl";
$xslt_files["html-druck"]["htm"] = true;
$xslt_files["html-druck"]["html"] = true;
$xslt_files["html-druck"]["person"] = true;
$xslt_files["html-druck"]["veranstaltung"] = true;

$xslt_files["rtf-standard"]["name"] = "Standardmodul";
$xslt_files["rtf-standard"]["desc"] = "Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten als RTF-Datei. Die Datei kann in einer Textverarbeitung bearbeitet werden.";
$xslt_files["rtf-standard"]["file"] = "rtf-vp-1.xsl";
$xslt_files["rtf-standard"]["rtf"] = true;
$xslt_files["rtf-standard"]["person"] = true;
$xslt_files["rtf-standard"]["veranstaltung"] = true;

$xslt_files[3]["name"] = "kombi3";
$xslt_files[3]["desc"] = "Diese Datei ist rein virtuell 2";
$xslt_files[3]["file"] = "6.xsl";
$xslt_files[3]["rtf"] = true;
$xslt_files[3]["txt"] = true;
$xslt_files[3]["veranstaltung"] = true;

$xslt_files[4]["name"] = "super";
$xslt_files[4]["desc"] = "dudeldi";
$xslt_files[4]["file"] = "7.xsl";
$xslt_files[4]["rtf"] = true;
$xslt_files[4]["txt"] = true;
$xslt_files[4]["htm"] = true;
$xslt_files[4]["veranstaltung"] = true;

$xslt_files[5]["name"] = "super1";
$xslt_files[5]["desc"] = "layoutfunktionen";
$xslt_files[5]["file"] = "8.xsl";
$xslt_files[5]["htm"] = true;
$xslt_files[5]["person"] = true;

$xslt_files[6]["name"] = "standard";
$xslt_files[6]["desc"] = "test 3333333333333333333333333333333";
$xslt_files[6]["file"] = "1.xsl";
$xslt_files[6]["rtf"] = true;
$xslt_files[6]["txt"] = true;
$xslt_files[6]["htm"] = true;
$xslt_files[6]["person"] = true;


?>