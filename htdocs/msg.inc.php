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



//Displays Errormessages (kritischer Abbruch, Symbol "X")

function my_error($msg, $class="blank", $colspan=2, $add_row=TRUE) {
?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>
			<table border=0 align="left" cellspacing=0 cellpadding=2>
				<tr>
					<td class="<? echo $class?>" align="center" width=50><img src="pictures/x.gif"></td>
					<td class="<? echo $class?>" align="left" width="*"><font color=#FF2020><?php print $msg ?></font></td>
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

function my_msg($msg, $class="blank", $colspan=2, $add_row=TRUE) {
?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>
			<table border=0 align="left" cellspacing=0 cellpadding=2>
				<tr>
					<td class="<? echo $class?>" align="center" width=50><img src="pictures/ok.gif"></td>
					<td class="<? echo $class?>" align="left" width="*"><font color=#008000><?php print $msg ?></font></td>
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

function my_info($msg, $class="blank", $colspan=2, $add_row=TRUE) {
?>
	<tr>
		<td class="<? echo $class?>" colspan=<? echo $colspan?>>
			<table border=0 align="left" cellspacing=0 cellpadding=2>
				<tr>
					<td class="<? echo $class?>" align="center" width=50><img src="pictures/ausruf.gif"></td>
					<td class="<? echo $class?>" align="left" width="*"><font color=#000000><?php print $msg ?></font></td>
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
function parse_msg($long_msg,$separator="§", $class="blank", $colspan=2, $add_row=TRUE) {
  $msg = explode ($separator,$long_msg);
	for ($i=0; $i < count($msg); $i=$i+2) {
		switch ($msg[$i]) {
			case "error" : my_error($msg[$i+1], $class, $colspan, $add_row); break;
			case "info" : my_info($msg[$i+1], $class, $colspan, $add_row); break;
			case "msg" : my_msg($msg[$i+1], $class, $colspan, $add_row); break;
		}
	}
  return;
}

//Kombinierte Nachrichten zerlegen und in eigenem Fenster anzeigen
function parse_window ($long_msg,$separator="§", $titel="Fehler", $add_msg="<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es zur&uuml;ck zur Startseite.<br />&nbsp;") {

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