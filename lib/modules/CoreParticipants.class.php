<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreParticipants implements StudipModule {
    
    function getIconNavigation($course_id, $last_visit, $user_id) {
        $navigation = new Navigation(_('Teilnehmende'), "seminar_main.php?auswahl=".$course_id."&redirect_to=dispatch.php/course/members");
        $navigation->setImage(Icon::create('persons', 'inactive'));
        return $navigation;
    }
    
    function getTabNavigation($course_id) {
        #$navigation = new AutoNavigation(_('Teilnehmende'));
        $navigation = new Navigation(_('Teilnehmende'));
        $navigation->setImage(Icon::create('persons', 'info_alt'));
        $navigation->setActiveImage(Icon::create('persons', 'info'));
        $navigation->addSubNavigation('view', new Navigation(_('Teilnehmende'), 'dispatch.php/course/members'));
        if (Course::find($course_id)->aux_lock_rule) {
            $navigation->addSubNavigation('additional', new Navigation(_('Zusatzangaben'), 'dispatch.php/course/members/additional'));
        }

        $navigation->addSubNavigation('statusgroups', new Navigation(_('Gruppen'), 'dispatch.php/course/statusgroups'));

        return array('members' => $navigation);
    }

    /** 
     * @see StudipModule::getMetadata()
     */ 
    function getMetadata()
    {
        return array(
            'summary' => _('Liste aller Teilnehmenden einschließlich Nachrichtenfunktionen'),
            'description' => _('Die Teilnehmenden werden gruppiert nach ihrer '.
                'jeweiligen Funktion in einer Tabelle gelistet. Für Lehrende '.
                'werden sowohl das Anmeldedatum als auch der Studiengang mit '.
                'Semesterangabe dargestellt. Die Liste kann in verschiedene '.
                'Formate exportiert werden. Außerdem gibt es die '.
                'Möglichkeiten, eine Rundmail an alle zu schreiben (nur '.
                'Lehrende) bzw. einzelne Teilnehmende separat anzuschreiben.'),
            'displayname' => _('Teilnehmende'),
            'keywords' => _('Rundmail an einzelne, mehrere oder alle Teilnehmenden;
                            Gruppierung nach Lehrenden, Tutor/-innen und Studierenden (Autor/-innen);
                            Aufnahme neuer Studierender (Autor/-innen) und Tutor/-innen;
                            Import einer Teilnehmendenliste;
                            Export der Teilnehmendenliste;
                            Einrichten von Gruppen;
                            Anzeige Studiengang und Fachsemester'),
            'descriptionshort' => _('Liste aller Teilnehmenden einschließlich Nachrichtenfunktionen'),
            'descriptionlong' => _('Die Teilnehmenden werden gruppiert nach ihrer jeweiligen Rolle in '.
                                   'einer Tabelle gelistet. Für Lehrende werden sowohl das Anmeldedatum '.
                                   'als auch der Studiengang mit Semesterangabe der Studierenden dargestellt. '.
                                   'Die Liste kann in verschiedene Formate exportiert werden. Außerdem gibt '.
                                   'es die Möglichkeiten für Lehrende, allen eine Rundmail zukommen zu lassen '.
                                   'bzw. einzelne Teilnehmende separat anzuschreiben.'),
            'category' => _('Lehr- und Lernorganisation'),
            'icon' => Icon::create('persons', 'info'),
            'screenshots' => array(
                'path' => 'plus/screenshots/TeilnehmerInnen',
                'pictures' => array(
                    0 => array('source' => 'Liste_aller_Teilnehmenden_einer_Veranstaltung.jpg', 'title' => _('Liste aller Teilnehmenden einer Veranstaltung')),
                    1 => array( 'source' => 'Rundmail_an_alle_TeilnehmerInnen_einer_Veranstaltung.jpg', 'title' => _('Rundmail an alle Teilnehmdenden einer Veranstaltung'))
                )
            )
        );
    }
}
