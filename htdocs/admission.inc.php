<?php

/*
admission.inc.php - Funktionen die zur Teilnehmerbeschraenkung benoetigt werden
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, data-quest <info@data-quest.de>

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
Die Funktion veranstaltung_beginn errechnet den ersten Seminartermin aus dem Turnus Daten.
Zurueckgegeben wird ein String oder Timestamp. je nach return_mode (TRUE = Timestamp)
Evtl. Ergaenzungen werden im Stringmodus mit ausgegeben.
Die Funktion kann mit einer Seminar_id aufgerufen werden, dann werden saemtliche gespeicherten Daten 
beruecksichtigt. Im 'ad hoc' Modus koennen der Funktion auch die eizelnen Variabeln des Metadaten-Arrays
uebergeben werden. Dann werden konkrete Termine nur mit berruecksichtigt, sofern sie schon angelegt wurden.
*/

function check_admission ($seminar_id, $send_message=TRUE) {
}

function update_admission ($seminar_id, $send_message=TRUE) {
}