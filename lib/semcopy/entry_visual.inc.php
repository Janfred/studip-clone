<?
/*
* entry_visual.inc.php
* used by /public/copy_assi.php
* Part of Alternative Copy Mechanism (ACM) 
* written by Dirk Oelkers <d.oelkers@fh-wolfenbuettel.de>

* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2>
	<blockquote>
	<? 
	printf(_("Die Veranstaltung wurde zum Kopieren ausgewählt."). " <br><br>");
	printf(_("Sie haben an dieser Stelle zwei Möglichkeiten. <br><br>"));
	printf(_("1. Sie kopieren Ihre Veranstaltung mit der alten Kopierfunktion, via Assistent.<br><br>"));
	printf(_("&nbsp; &nbsp; Diese Methode hat allerdings den Nachteil, das keine Inhalte kopiert werden können.<br><br>"));
	printf(_("2. Sie kopieren mit der neuen Kopierfunktion<br><br>"));
	printf(_("&nbsp; &nbsp; Bei dieser Methode geben Sie nur den neuen Titel für die Kopie an und die Inhalte, die in die Kopie übernommen werden sollen<br><br>"));

	printf(_("Für Methode 1 klicken Sie %shier%s."),'<a href="admin_seminare_assi.php?cmd=do_copy&cp_id='.$SessSemName[1].'&start_level=TRUE&class=1">','</a><br><br>');

	printf(_("Für Methode 2 klicken Sie %shier%s."),'<a href="copy_assi.php?cmd=show_copy_form">','</a><br><br>');
	?>

	</blockquote>
	<br />
	</td></tr>
	</table>
