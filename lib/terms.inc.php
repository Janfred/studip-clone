<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* terms.inc.php
*
* show terms on first login and check if user accept them
*
*
* @author       Zentrum VirtuOS, Osnabrueck
* @access       public
* @modulegroup      admission
* @module       admission.inc.php
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// terms.inc.php
// Zeigt die Nutzungsbedingungen und wartet, bis diese akzeptiert wurden
// Copyright (C) 2003 Zentrum VirtUOS Osnabrueck
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


function check_terms($userid, $_language_path) {

    if (Request::get('i_accept_the_terms') == "yes") {
        UserConfig::get($userid)->store('TERMS_ACCEPTED', 1);
        return;
    }

    if ($GLOBALS['auth']->auth['uid'] != 'nobody' && !empty($GLOBALS['user']) && !$GLOBALS['user']->cfg->getValue('TERMS_ACCEPTED'))
    {
        ob_start();
        PageLayout::setTitle(_('Nutzungsbedingungen'));
?>

<section class="contentbox">
    <header>
        <h1><?= _('Was ist Stud.IP?') ?></h1>
    </header>
    <section>
        <?= _('Stud.IP ist ein Open Source Projekt und steht unter der Gnu General Public License (GPL). Das System befindet sich in der ständigen Weiterentwicklung.') ?>

        <? printf(_('Für Vorschläge und Kritik findet sich immer ein Ohr. Wenden Sie sich hierzu entweder an die %sStud.IP Crew%s oder direkt an Ihren lokalen %sSupport%s.'),
            "<a href=\"mailto:studip-users@lists.sourceforge.net\">", "</a>",
            "<a href=\"dispatch.php/siteinfo/show\">", "</a>") ?>
        <br><br>
        <?= _('Um den vollen Funktionsumfang von Stud.IP nutzen zu können, müssen Sie sich am System anmelden.') ?><br>
        <?= _('Das hat viele Vorzüge:') ?><br>

        <ul>
            <li><?= _('Zugriff auf Ihre Daten von jedem internetfähigen Rechner weltweit,') ?>
            <li><?= _('Anzeige neuer Mitteilungen oder Dateien seit Ihrem letzten Besuch,') ?>
            <li><?= _('Eine eigenes Profil im System,') ?>
            <li><?= _('die Möglichkeit anderen Personen Nachrichten zu schicken oder mit ihnen zu chatten,') ?>
            <li><?= _('und vieles mehr.') ?></li>
        </ul>
        <?= _('Mit der Anmeldung werden die nachfolgenden Nutzungsbedingungen akzeptiert:') ?>
    </section>
</section>

<? include('locale/' . $GLOBALS['_language_path'] . '/LC_HELP/pages/nutzung.html'); ?>

<footer>
    <div class="button-group">
        <?= Studip\LinkButton::create(_('Ich erkenne die Nutzungsbedingungen an'), URLHelper::getLink("", array('i_accept_the_terms' => 'yes'))) ?>
        <?= Studip\LinkButton::create(_('Ich stimme den Nutzungsbedingungen nicht zu'), URLHelper::getLink('logout.php')) ?>
    </div>
</footer>

<?php
    $layout = $GLOBALS['template_factory']->open('layouts/base.php');

    $layout->content_for_layout = ob_get_clean();

    echo $layout->render();
    page_close();
    die();
    }

}
?>