<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* startup_checks.php
*
* checks if all requirements to create Veranstaltungen are set up. If evreything is fine, no output will be generated.
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @module       startup_checks.php
* @modulegroup      admin
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_modules.php
// ueberprueft, oba alle Voraussetzungen zum Anlegen von Veranstaltungen erfüllt sind. Wenn alles in Ordnung ist, wird keine Ausgabe erzeugt.
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

use Studip\Button, Studip\LinkButton;

$perm->check('dozent');

$checks=new StartupChecks;
$list = $checks->getCheckList();

$problems_found = 0;

foreach ($list as $key=>$val) {
    if ($val){
        if ($checks->registered_checks[$key]['msg_fak_admin'] && $perm->is_fak_admin()) {
            $msgText = $checks->registered_checks[$key]["msg_fak_admin"]; 
        } else {
            $msgText = $checks->registered_checks[$key]["msg"];
            $msgText .= ' <br><i> Aktion: '.formatReady("=)");
            $msgText .= '&nbsp;<a href="'.($checks->registered_checks[$key]["link_fak_admin"] && $perm->is_fak_admin() ? 
            $checks->registered_checks[$key]["link_fak_admin"] : $checks->registered_checks[$key]["link"]).'" > '.
            ($checks->registered_checks[$key]["link_name_fak_admin"] && $perm->is_fak_admin() ? 
            $checks->registered_checks[$key]["link_name_fak_admin"] : $checks->registered_checks[$key]["link_name"]).' </a></i>';
        }
        $problems[$problems_found] = $msgText;
        $problems_found++;
    }
}

if ($problems_found > 1) {
    $moreProbs = " (Beachten Sie bitte die angegebene Reihenfolge!)";
}

if ($problems_found) {
?>
    <table class="default">
        <tr>
             <td class="blank">
                <?= MessageBox::info(_("Das Anlegen einer Veranstaltung ist leider zu diesem Zeitpunkt noch nicht möglich, 
                da zunächst die folgenden Voraussetzungen geschaffen werden müssen.".$moreProbs), $problems); ?>
            </td>
        </tr>
        <tr>
            <td align="center">
                <?= LinkButton::create(_('Aktualisieren'), URLHelper::getURL(''))?>
            </td>
        </tr>
        <tr>
            <td class="blank">&nbsp;</td>
        </tr>
    </table>
<?php
return false;
}
