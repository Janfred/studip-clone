<?php

/*
 * Copyright (C) 2008 - Ansgar Bockstiegel <ansgar.bockstiegel@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class Step00156EditierbaresImpressum extends Migration {


  function description() {
    return 'Adds two new tables and fills them with default content.';
  }


  function up() {
    $db = DBManager::get();

    $this->announce("add new table siteinfo_rubrics");
    $db->exec("CREATE TABLE IF NOT EXISTS `siteinfo_rubrics` (
                  `rubric_id` smallint(5) unsigned NOT NULL auto_increment,
                  `position` tinyint(3) unsigned default NULL,
                  `name` varchar(255) NOT NULL,
                  PRIMARY KEY  (`rubric_id`))");

    $this->announce("fill siteinfo_rubrics with default content");
    $db->exec("INSERT INTO `siteinfo_rubrics` (`rubric_id`, `name`)
                 VALUES
                    (1, 'Über Stud.IP'),
                    (2, 'Kontakt')");

    $this->announce("add new table siteinfo_details");
    $db->exec("CREATE TABLE IF NOT EXISTS `siteinfo_details` (
                `detail_id` smallint(5) unsigned NOT NULL auto_increment,
                `rubric_id` smallint(5) unsigned NOT NULL,
                `position` tinyint(3) unsigned default NULL,
                `name` varchar(255) NOT NULL,
                `content` text NOT NULL,
                PRIMARY KEY  (`detail_id`))");

    $this->announce("fill siteinfo_details with default content");
    $db->exec("INSERT INTO `siteinfo_details` (`rubric_id`, `name`, `content`) 
               VALUES
                    (1,
                     'Ansprechpartner',
                     '(:logofloater:)".'\r\n'."(:versionfloater:)".'\n'.
                     "Für diese Stud.IP-Installation ((:uniname:)) sind folgende Administratoren zuständig:".'\n'.
                     "(:rootlist:)".'\n'.
                     "allgemeine Anfragen wie Passwort-Anforderungen u.a. richten Sie bitte an:".'\n'.
                     "(:unicontact:)".'\n'.
                     "Folgende Einrichtungen sind beteiligt:".'\n'.
                     "(Genannt werden die jeweiligen Administratoren der Einrichtungen für entsprechende Anfragen)".'\n'.
                     "(:adminlist:)'),
                    (1,
                     'Entwickler',
                     '(:logofloater:)".'\n'.
                     "(:versionfloater:)".'\n'.
                     "Stud.IP ist ein Open Source Projekt zur Unterstützung von Präsenzlehre an Universitäten, Hochschulen und anderen Bildungseinrichtungen. Das System entstand am Zentrum für interdisziplinäre Medienwissenschaft (ZiM) der Georg-August-Universität Göttingen unter Mitwirkung der Suchi & Berg GmbH (data-quest) , Göttingen. Heute erfolgt die Weiterentwicklung von Stud.IP verteilt an vielen Standorten (Göttingen, Osnabrück, Oldenburg, Bremen, Hannover, Jena und weiteren). Die Koordination der Entwicklung erfolgt durch die Stud.IP-CoreGroup.".'\n'.
                     "Stud.IP steht unter der GNU General Public License, Version 2.".'\n\n'.
                     "Weitere Informationen finden sie auf [**www.studip.de**]http://www.studip.de , [**develop.studip.de**]http://develop.studip.de und [**blog.studip.de**]http://blog.studip.de.".'\n\n'.
                     "(:coregroup:)'),
                    (2,
                     'Technik',
                     'Stud IP ist ein Open-Source Projekt und steht unter der GNU General Public License. Sämtliche zum Betrieb notwendigen Dateien können unter http://sourceforge.net/projects/studip/ heruntergeladen werden.".'\n'.
                     "Die technische Grundlage bietet ein LINUX-System mit Apache Webserver sowie eine MySQL Datenbank, die über PHP gesteuert wird.".'\n'.
                     "Im System findet ein 6-stufiges Rechtesystem Verwendung, das individuell auf verschiedenen Ebenen wirkt - etwa in Veranstaltungen, Einrichtungen, Fakultäten oder systemweit.".'\n'.
                     "Seminare oder Arbeitsgruppen können mit Passwörtern geschützt werden - die Verschlüsselung erfolgt mit einem MD5 one-way-hash.".'\n'.
                     "Das System ist zu 100% über das Internet administrierbar, es sind keine zusätzlichen Werkzeuge nötig. Ein Webbrowser der 5. Generation wird empfohlen.".'\n'.
                     "Das System wird ständig weiterentwickelt und an die Wünsche unserer Nutzer angepasst - [sagen Sie uns Ihre Meinung!]studip-users@lists.sourceforge.net'),
                    (2,
                     'Statistik', 
                     '!!!Top-Listen aller Veranstaltungen".'\n'.
                     "(:indicator seminar_all:)".'\n'.
                     "(:indicator seminar_archived:)".'\n'.
                     "(:indicator institute_firstlevel_all:)".'\n'.
                     "(:indicator institute_secondlevel_all:)".'\n'.
                     "(:indicator user_admin:)".'\n'.
                     "(:indicator user_dozent:)".'\n'.
                     "(:indicator user_tutor:)".'\n'.
                     "(:indicator user_autor:)".'\n'.
                     "(:indicator posting:)".'\n'.
                     "(:indicator document:)".'\n'.
                     "(:indicator link:)".'\n'.
                     "(:indicator litlist:)".'\n'.
                     "(:indicator termin:)".'\n'.
                     "(:indicator news:)".'\n'.
                     "(:indicator guestbook:)".'\n'.
                     "(:indicator vote:)".'\n'.
                     "(:indicator test:)".'\n'.
                     "(:indicator evaluation:)".'\n'.
                     "(:indicator wiki_pages:)".'\n'.
                     "(:indicator lernmodul:)".'\n'.
                     "(:indicator resource:)".'\n'.
                     "(:toplist mostparticipants:)".'\n'.
                     "(:toplist recentlycreated:)".'\n'.
                     "(:toplist mostdocuments:)".'\n'.
                     "(:toplist mostpostings:)".'\n'.
                     "(:toplist mostvisitedhomepages:)'),
                    (2,
                     'History',
                     '(:history:)'),
                    (2,
                     'Stud.IP-Blog',
                     'Das Blog der Stud.IP-Entwickler finden Sie unter:".'\n'.
                     "http://blog.studip.de')");

    $this->announce("done.");
  }


  function down() {
    $this->announce("remove siteinfo_details");
    $this->db->query("DROP table `siteinfo_details`");

    $this->announce("remove siteinfo_rubrics");
    $this->db->query("DROP table `siteinfo_rubrics`");

    $this->announce("done.");
  }
}
