<?php
/*
admin_smileys.php - Smiley-Verwaltung von Stud.IP.
Copyright (C) 2004 Tobias Thelen <tthelen@uos.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("root");

if (!$SMILEYADMIN_ENABLE) {
	print '<p>' . _('Smiley-Modul abgeschaltet.'). '</p>';
	print "</body></html>";
	page_close();
	die;
}

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php"); //Funktionen fuer Nachrichtenmeldungen
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/Table.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/ZebraTable.class.php");

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");  //Linkleiste fuer admins

function imaging($img, $img_size, $img_name) {
	global $ABSOLUTE_PATH_STUDIP, $SMILE_PATH;

	if (!$img_name) { //keine Datei ausgewählt!
		return "error§" . _("Sie haben keine Datei zum Hochladen ausgewählt!");
	}

	//Dateiendung bestimmen
	$dot = strrpos($img_name,".");
	if ($dot) {
		$l = strlen($img_name) - $dot;
		$ext = strtolower(substr($img_name,$dot+1,$l));
	}
	//passende Endung ?
	if ($ext != "gif" ) {
		$msg = "error§" . sprintf(_("Der Dateityp der Bilddatei ist falsch (%s).<br>Es ist nur die Dateiendung .gif erlaubt!"), $ext);
		return $msg;
	}

	//na dann kopieren wir mal...
	$uploaddir=$ABSOLUTE_PATH_STUDIP.$SMILE_PATH;
	$newfile = $uploaddir . "/" . $img_name;
	if(!@copy($img,$newfile)) {
		$msg = "error§" . _("Es ist ein Fehler beim Kopieren der Datei aufgetreten. Das Bild wurde nicht hochgeladen!");
		return $msg;
	} else {
		$msg = "msg§" . _("Die Bilddatei wurde erfolgreich hochgeladen.");
	}
	return $msg;
}

function show_upload_form() {
	global $PHP_SELF;

	$table=new ZebraTable(array("bgcolor"=>"#eeeeee", "align"=>"center", "padding"=>"2"));
	print $table->headerRow(array('<b>' . _('Neues Smiley hochladen') . '</b>'));
	print "<form enctype=\"multipart/form-data\" action=\"$PHP_SELF?cmd=upload&view=edit\" method=\"POST\">";
	print $table->row(array(_('1. Bilddatei auswählen:')." <input name=\"imgfile\" type=\"file\" cols=45>"));
	print $table->row(array(_('2. Bilddatei hochladen:')." <input type=\"IMAGE\" " . makeButton("absenden", "src") . " border=0 value=\"" . _("absenden") . "\">"));
	print "</form>";
	echo $table->close();
}

function show_smiley_list() {
	global $ABSOLUTE_PATH_STUDIP, $PHP_SELF, $SMILE_PATH, $SMILE_SHORT;

	function my_comp($a, $b){
		return strcasecmp($a[1], $b[1]);
	}

	$path = realpath($ABSOLUTE_PATH_STUDIP."/".$SMILE_PATH);
	$folder=dir($path);
	$SMILE_SHORT_R=array_flip($SMILE_SHORT);
	$i_smile = array();
	while ($entry=$folder->read()){
		$dot = strrpos($entry,".");
		$l = strlen($entry) - $dot;
		$name = substr($entry,0,$dot);
		$ext = strtolower(substr($entry,$dot+1,$l));
		if ($dot AND !is_dir($path."/".$entry) AND $ext=="gif"){
			$i_smile[] = array($entry,$name);
		}
	}
	$folder->close();
	usort($i_smile, "my_comp");

	echo "<form action=\"$PHP_SELF\" method=\"POST\">";
	echo "<input type=\"hidden\" name=\"cmd\" value=\"update\">";
	$table=new ZebraTable(array("bgcolor"=>"#eeeeee", "align"=>"center", "padding"=>"2"));
	echo $table->open();
	echo $table->openHeaderRow();
	echo $table->cell('<b>' . _('Nr.') . '</b>', array("align"=>"center"));
	echo $table->cell('<b>' . _('Bild') . '</b>', array("align"=>"center"));
	echo $table->cell('<b>' . _('Name') . '</b>', array("align"=>"center"));
	echo $table->cell('<b>' . _('Kurz') . '</b>', array("align"=>"center"));
	echo $table->cell('<b>' . _('Löschen') . '</b>', array("align"=>"center"));
	echo $table->closeRow();
	$count=0;
	foreach ($i_smile as $smiley) {
		$count++;
		$urlname=urlencode($smiley[1]);
		echo $table->openRow();
		echo $table->cell($count, array("align"=>"right"));
		echo $table->cell("<img src=\"."$GLOBALS["SMILE_PATH"].".$smiley[0]."\" alt=\"".$name."\">", array("align"=>"center"));
		echo $table->cell("<input name=\"rename_$urlname\" value=\"$smiley[1]\" size=20>");
		echo $table->cell("<input readonly name=\"short_$urlname\" value=\"".$SMILE_SHORT_R[$smiley[1]]."\" size=\"5\">");
		echo $table->cell("&nbsp;<a href=\"$PHP_SELF?cmd=delete&img=$urlname\"><img src=\"pictures/trash.gif\" border=0></a>&nbsp;", array("align"=>"center"));
		echo $table->closeRow();
	}
	echo $table->openRow();
	echo $table->cell("<input type=image ".makeButton("absenden","src").">", array("colspan"=>"5", "align"=>"center"));
	if ($count==0) {
		print $table->row(array("<h4>" . _("Keine Smileys vorhanden.") . "</h4>"), array("colspan"=>2, "class"=>"blank"));
	}
	echo $table->close();
}


function process_commands() {
	global $HTTP_POST_VARS, $ABSOLUTE_PATH_STUDIP, $SMILE_PATH;
	$count=0;
	foreach($HTTP_POST_VARS as $key => $val) {
		$matches=array();
		preg_match("/(short|rename)_(.*)/", $key, $matches);
		if ($matches[1]=="rename") {
			if ($matches[2]!=$val) {
				$success=rename($ABSOLUTE_PATH_STUDIP.$SMILE_PATH."/".urldecode($matches[2]).".gif", $ABSOLUTE_PATH_STUDIP.$SMILE_PATH."/".urldecode($val).".gif");
				if ($success) {
					$count++;
				} else {
					parse_msg('error§' . sprintf( _('Die Datei %s konnte nicht umbenannt werden.'),  $ABSOLUTE_PATH_STUDIP.$SMILE_PATH.'/'.urldecode($matches[2])));
				}
			}
		}
	}
	if ($count>0) {
		if ($count==1) {
			parse_msg("msg§"._("Es wurde 1 Smiley umbenannt."));
		} else {
			parse_msg("msg§".sprintf(_("Es wurden %d Smileys umbenannt."), $count));
		}
	}
}

//
// Start output
//
$container=new ContainerTable();
echo $container->headerRow("<b>&nbsp;"._("Verwaltung der Smileys")."</b>");
echo $container->openCell();

$content=new ContentTable();
echo $content->open();
echo $content->openRow();
echo $content->openCell(array("colspan"=>"2"));

if ($cmd=="upload") {
	$msg=imaging($imgfile,$imgfile_size,$imgfile_name);
	parse_msg($msg);
	$i_view="list";
} elseif ($cmd=="update") {
	process_commands();
} elseif ($cmd=="delete") {
	if (unlink($ABSOLUTE_PATH_STUDIP.$SMILE_PATH.'/'.urldecode($img).".gif")) {
		parse_msg('msg§' . _('Smiley erfolgreich gelöscht.'));
	} else {
		parse_msg('error§'. _('Fehler: Smiley konnte nicht gelöscht werden.'));
	}
}

show_upload_form();
show_smiley_list();

echo $content->close();
echo $container->blankRow();
echo $container->close();

page_close();
?>
</body>
</html>
<!-- $Id$ -->
