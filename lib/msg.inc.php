<?php
/*
msg.inc.php - Modul zur Ausgabe von Nachrichten auf Administrationsseiten von Stud.IP.
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

/*
Diese Funktion zeigt Messages mit zugehoerigenm Symbol.
ACHTUNG: Die Funktion wird innerhalb einer Tabelle aufgerufen, daher
wird eine eigene Tabellenzelle geoeffnet
*/

// $Id$

//Displays Errormessages (kritischer Abbruch, Symbol "X")
function my_error($msg, $class="blank", $colspan=2, $add_row=TRUE, $small = false) {
	global $CANONICAL_RELATIVE_PATH_STUDIP;
	$pic = ($small ? 'x_small2.gif' : 'x.gif');
	$width = ($small ? 22 : 50);

?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>
			<table border=0 cellspacing=0 cellpadding=2>
				<tr>
					<td class="<? echo $class?>" align="center" width="<?=$width?>"><img src="<?= $GLOBALS['ASSETS_URL']."images/$pic" ?>"></td>
					<td class="<? echo $class?>" align="left"><font color="#FF2020" <?=($small ? 'size="-1"' : '')?>><?php print $msg ?></font></td>
				</tr>
			</table>
		</td>
	</tr>
	<? if ($add_row) { ?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>&nbsp;</td>
	</tr>
	<?}
}


//Displays Successmessages (Information ueber erfolgreiche Aktion, Symbol Haken)

function my_msg($msg, $class="blank", $colspan=2, $add_row=TRUE, $small = false) {
	global $CANONICAL_RELATIVE_PATH_STUDIP;
	$pic = ($small ? 'ok_small2.gif' : 'ok.gif');
	$width = ($small ? 22 : 50);
	?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>
			<table border=0 cellspacing=0 cellpadding=2>
				<tr>
					<td class="<? echo $class?>" align="center" width="<?=$width?>"><img src="<?= $GLOBALS['ASSETS_URL']."images/$pic" ?>"></td>
					<td class="<? echo $class?>" align="left"><font color="#008000" <?=($small ? 'size="-1"' : '')?>><?php print $msg ?></font></td>
				</tr>
			</table>
		</td>
	</tr>
	<? if ($add_row) { ?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>&nbsp;</td>
	</tr>
	<?}
}

//Displays Informationmessages  (Hinweisnachrichten, Symbol Ausrufungszeichen)

function my_info($msg, $class="blank", $colspan=2, $add_row=TRUE, $small = false) {
	global $CANONICAL_RELATIVE_PATH_STUDIP;
	$pic = ($small ? 'ausruf_small2.gif' : 'ausruf.gif');
	$width = ($small ? 22 : 50);
	?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>
			<table border=0 cellspacing=0 cellpadding=2>
				<tr>
					<td class="<? echo $class?>" align="center" width="<?=$width?>"><img src="<?= $GLOBALS['ASSETS_URL']."images/$pic" ?>"></td>
					<td class="<? echo $class?>" align="left"><font color="#000000" <?=($small ? 'size="-1"' : '')?>><?php print $msg ?></font></td>
				</tr>
			</table>
		</td>
	</tr>
	<? if ($add_row) { ?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>&nbsp;</td>
	</tr>
	<?}
}

//Kombinierte Nachrichten zerlegen
function parse_msg($long_msg,$separator="§", $class="blank", $colspan=2, $add_row=TRUE, $small = true) {
  $msg = explode ($separator,$long_msg);
	for ($i=0; $i < count($msg); $i=$i+2) {
		switch ($msg[$i]) {
			case "error" : my_error($msg[$i+1], $class, $colspan, $add_row, $small); break;
			case "info" : my_info($msg[$i+1], $class, $colspan, $add_row, $small); break;
			case "msg" : my_msg($msg[$i+1], $class, $colspan, $add_row, $small); break;
		}
	}
  return;
}

function parse_msg_array($msg, $class = "blank", $colspan = 2, $add_row = true, $small = false){
	if(is_array($msg)){
		foreach($msg as $one_msg){
			list($type, $content) = $one_msg;
			call_user_func('my_' . $type, $content, $class, $colspan, $add_row, $small);
		}
	}
}

function parse_msg_to_string($long_msg, $separator="§", $class="blank", $colspan=2, $add_row=TRUE, $small = false){
	ob_start();
	parse_msg($long_msg, $separator, $class, $colspan, $add_row, $small);
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}

function parse_msg_array_to_string($msg, $class = "blank", $colspan = 2, $add_row = true, $small = false){
	ob_start();
	parse_msg_array($msg, $class, $colspan, $add_row, $small);
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}

//Kombinierte Nachrichten zerlegen und in eigenem Fenster anzeigen
function parse_window ($long_msg,$separator="§", $titel, $add_msg="") {

if ($titel == "")
	$titel= _("Fehler");
if ($add_msg == "")
	$add_msg= sprintf(_("%sHier%s geht es zur&uuml;ck zur Startseite."), "<a href=\"index.php\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;";
?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=70%>
<tr valign=top align=middle>
	<td class="topic" align="left"><b>&nbsp; <? echo $titel?></b></td>
</tr>
<tr><td class="blank">&nbsp;</td></tr>
<?

  $msg = explode ($separator,$long_msg);
	for ($i=0; $i < count($msg); $i=$i+2) {
		switch ($msg[$i]) {
			case "error" : my_error($msg[$i+1], "blank", 1); break;
			case "info" : my_info($msg[$i+1], "blank", 1); break;
			case "msg" : my_msg($msg[$i+1], "blank", 1); break;
		}
	}
	if ($add_msg) {
?>
	<tr><td class="blank"><font size=-1><? echo $add_msg ?></font>

	</td></tr>
<?
	}
?>
</table>
<?
  return;
}
?>
