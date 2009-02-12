<?php
/*
 * siteinfo - display information about Stud.IP
 *
 * Copyright (c) 2008  Ansgar Bockstiegel
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('lib/visual.inc.php');
require_once('lib/user_visible.inc.php');

class Siteinfo {
    private $sme;
    private $rubrics_empty;

    function __construct() {
        $this->sme = new SiteinfoMarkupEngine();
    }
    function get_detail_content($id) {
        global $perm;
        if ($id == 0) {
            if ($perm->have_perm('root')) {
                if ($this->rubrics_empty) {
                    return _("Benutzen Sie den Link »neue Rubrik anlegen« in der Infobox, um eine Rubrik anzulegen.");
                } else {
        	        return _("Benutzen Sie den Link »neue Seite anlegen« in der Infobox, um eine Seite in dieser Rubrik anzulegen.");
                }
        	} else {
    	        return _("Der für diese Stud.IP-Installation verantwortliche Administrator muss hier noch Inhalte einfügen.")."<br />".rootlist();
        	}
        } else {
            $db = DBManager::get();
            $sql = "SELECT content
                    FROM siteinfo_details
                    WHERE detail_id = ".$db->quote($id,PDO::PARAM_INT);
            $result = $db->query($sql);
            $rows = $result->fetch();
            return $rows[0];
        }
    }

    function get_detail_name($id) {
        $db = DBManager::get();
        $sql = "SELECT name
                FROM siteinfo_details
                WHERE detail_id = ".$db->quote($id,PDO::PARAM_INT);
        $result = $db->query($sql);
        $rows = $result->fetch();
        return $rows[0];    
    }

    function get_detail_content_processed($id) {
        $content = $this->get_detail_content($id);
        $output = $this->sme->siteinfoDirectives(formatReady(language_filter($content)));
        return $output;
    }

    function save($type, $input) {
        $db = DBManager::get();
        $rubric_name = $input['rubric_name'] ? $input['rubric_name'] : "unbenannt";
        $detail_name = $input['detail_name'] ? $input['detail_name'] : "unbenannt";
        switch ($type) {
            case "update_detail":
                $db->exec("UPDATE siteinfo_details
                           SET rubric_id = ".$db->quote($input['rubric_id'],PDO::PARAM_INT).",
                               name = ".$db->quote($detail_name).",
                               content = ".$db->quote($input['content'])."
                           WHERE detail_id=".$db->quote($input['detail_id'],PDO::PARAM_INT));
                $rubric = $input['rubric_id'];
                $detail = $input['detail_id'];
                break;
            case "insert_detail":
                $db->exec("INSERT 
                           INTO siteinfo_details 
                           (rubric_id,
                            name,
                            content)
                           VALUES (".$db->quote($input['rubric_id'],PDO::PARAM_INT).", 
                                   ".$db->quote($detail_name).", 
                                   ".$db->quote($input['content']).");");
                $rubric = $input['rubric_id'];
                $detail = $db->lastInsertId();
                break;
            case "update_rubric":
                $db->exec("UPDATE siteinfo_rubrics
                           SET name = ".$db->quote($rubric_name)."
                           WHERE rubric_id = ".$db->quote($input['rubric_id'],PDO::PARAM_INT).";");
                $rubric = $input['rubric_id'];
                $detail = $this->first_detail_id($rubric);
                break;
            case "insert_rubric":
                $db->exec("INSERT 
                           INTO siteinfo_rubrics
                           (name)
                           VALUES (".$db->quote($rubric_name).");");
                $rubric = $db->lastInsertId();
                $detail = 0;
        }
        return array($rubric, $detail);
    }

    function delete($type,$id) {
        $db = DBManager::get();
        if($type=="rubric") {
            $db->exec("DELETE FROM siteinfo_details WHERE rubric_id = ".$db->quote($id).";");
            $db->exec("DELETE FROM siteinfo_rubrics WHERE rubric_id = ".$db->quote($id).";");
        } else {
            $db->exec("DELETE FROM siteinfo_details WHERE detail_id = ".$db->quote($id).";");
        }
    }

    function first_detail_id($rubric = NULL) {
        $db = DBManager::get();
        $rubric_id = $rubric ? $rubric : $this->first_rubric_id();
        $sql = "SELECT detail_id
                FROM siteinfo_details ";
        if($rubric_id) {
            $sql .= "WHERE rubric_id = ".$db->quote($rubric_id,PDO::PARAM_INT);
        }
        $sql .= " ORDER BY position ASC
                 LIMIT 1";
        $result = $db->query($sql);
        $rows = $result->fetch();
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            return 0;
        }
    }

    function first_rubric_id() {
        $sql = "SELECT rubric_id
                FROM siteinfo_rubrics
                ORDER BY position ASC
                LIMIT 1";
        $result = DBManager::get()->query($sql);
        $rows = $result->fetch();
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            $this->rubrics_empty = TRUE;
            return NULL;
        }
    }

    function rubric_for_detail($id) {
        $db = DBManager::get();
        $sql = "SELECT rubric_id
                FROM siteinfo_details
                WHERE detail_id = ".$db->quote($id,PDO::PARAM_INT);
        $result = $db->query($sql);
        $rows = $result->fetch();
        return $rows[0];
    }

    function rubric_name($id) {
        $db = DBManager::get();
        $sql = "SELECT name
                FROM siteinfo_rubrics
                WHERE rubric_id = ".$db->quote($id,PDO::PARAM_INT);
        $result = $db->query($sql);
        $rows = $result->fetch();
        return $rows[0];
    }

    function get_all_rubrics() {
        $sql = "SELECT rubric_id, name
                FROM siteinfo_rubrics";
        $result = DBManager::get()->query($sql);
        $rows = $result->fetchAll();
        return $rows;
    }
}

class SiteinfoMarkupEngine {
    private $tf;
    //to preserve (parts?) of the old impressum.php-functionality
    //here a modified copy of wiki-engine supports specialized markup

    function __construct() {
        $this->tf = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'].'/app/views/siteinfo/markup/');
        $this->siteinfoMarkup("/\(:version:\)/e",'$this->version()');
        $this->siteinfoMarkup("/\(:uniname:\)/e",'$this->uniName()');
        $this->siteinfoMarkup("/\(:unicontact:\)/e",'$this->uniContact()');
        $this->siteinfoMarkup("/\(:userinfo ([a-z_@\-]*):\)/e",'$this->userinfo("$1")');
        $this->siteinfoMarkup("/\(:rootlist:\)/e",'$this->rootlist()');
        $this->siteinfoMarkup("/\(:adminlist:\)/e",'$this->adminlist()');
        $this->siteinfoMarkup("/\(:coregroup:\)/e",'$this->coregroup()');
        $this->siteinfoMarkup("/\(:toplist ([a-z]*):\)/ei",'$this->toplist("$1")');
        $this->siteinfoMarkup("/\(:indicator ([a-z_\-]*):\)/ei",'$this->indicator("$1")');
        $this->siteinfoMarkup("/\(:history:\)/e",'$this->history()');
        $this->siteinfoMarkup("'\[style=([^\]]*)\]\s*(.*?)\s*\[/style\]'s",'<div style="$1">$2</div>');
    }

    function siteinfoMarkup($pattern, $replace) {
           global $siteinfo_directives;
           $siteinfo_directives[] = array($pattern, $replace);
    }
    function siteinfoDirectives($str) {
           global $siteinfo_directives; // array of pattern-replace-arrays
           if (is_array($siteinfo_directives)) {
                   foreach ($siteinfo_directives as $direct) {
                        $str = preg_replace($direct[0],$direct[1],$str);
                   }
           }
           return $str;
    }


    function version() {
        global $SOFTWARE_VERSION;
        return $SOFTWARE_VERSION;
    }

    function uniName() {
        global $UNI_NAME;
        return $UNI_NAME;
    }

    function uniContact() {
        global $UNI_CONTACT;
        $template = $this->tf->open('uniContact');
        $template->contact = $UNI_CONTACT;
        return $template->render();
    }

    function userinfo($input) {
        $template = $this->tf->open('userinfo');
        $db = DBManager::get();
        $sql = "SELECT ".$GLOBALS['_fullname_sql']['full'] ." AS fullname,
                       Email, 
                       username 
                FROM auth_user_md5 
                LEFT JOIN user_info USING (user_id) 
                WHERE username=".$db->quote($input)."
                AND ".get_vis_query(); 
        $result = $db->query($sql);
        if ($result->rowCount() == 1) {
            $user = $result->fetch(PDO::FETCH_ASSOC);
            $template->user = $user['username'];
            $template->fullname = htmlReady($user['fullname']);
            $template->email = FixLinks(htmlReady($user['Email']));
        } else {
            $template->error = _("Nutzer nicht gefunden.");
        }
        return $template->render();
    }

    function rootlist() {
        $template = $this->tf->open('rootlist');
        $sql = "SELECT ".$GLOBALS['_fullname_sql']['full'] ." AS fullname,
                       Email, 
                       username 
                FROM auth_user_md5 
                LEFT JOIN user_info USING (user_id) 
                WHERE perms='root' 
                AND ".get_vis_query()." 
                ORDER BY Nachname";
        $result = DBManager::get()->query($sql);
        if ($result->rowCount() > 0) {
            $template->users = $result->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $template->error = _("keine. Na sowas. Das kann ja eigentlich gar nicht sein...");
        }
        return $template->render();
    }

    function adminList() {
        $template = $this->tf->open('adminList');
        $sql = "SELECT Institute.Name AS institute,
                ".$GLOBALS['_fullname_sql']['full'] ." AS fullname,
                auth_user_md5.Email,
                auth_user_md5.username
                FROM user_inst
                LEFT JOIN Institute ON (user_inst.institut_id = Institute.Institut_id)
                LEFT JOIN auth_user_md5 USING (user_id)
                LEFT JOIN user_info USING (user_id)
                WHERE inst_perms='admin'
                AND ".get_vis_query()." 
                ORDER BY Institute.Name, auth_user_md5.Nachname, auth_user_md5.Vorname";
        $result = DBManager::get()->query($sql);
        if ($result->rowCount() > 0) {
            $template->admins = $result->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $template->error = _("keine. Na sowas. Das kann ja eigentlich gar nicht sein...");
        }
        return $template->render();
    }

    function coregroup() {
        $cache = StudipCacheFactory::getCache();
        if (!($remotefile = $cache->read('coregroup'))) {
            $remotefile = file ('http://www.studip.de/crew.php');
            $cache->write('coregroup', $remotefile);
        }
        $out = implode($remotefile,'');
        $out = substr($out, stripos($out, "<table"), strrpos($out, "</table>"));
        $out = str_replace(array('class="normal"','align="left"'), array("",""), $out);
        return $out;
    }

    function toplist($item) {
        $template = $this->tf->open('toplist');
        switch ($item) {
            case "mostparticipants":
                $template->heading = _("die meisten Teilnehmer");
                $sql = "SELECT seminar_user.seminar_id,
                               seminare.name AS display,
                               count(seminar_user.seminar_id) as count 
                        FROM seminar_user 
                        INNER JOIN seminare USING(seminar_id) 
                        WHERE seminare.visible = 1 
                        GROUP BY seminar_user.seminar_id 
                        ORDER BY count DESC 
                        LIMIT 10";
                $template->type = "seminar";
                break;
            case "recentlycreated":
                $template->heading = _("zuletzt angelegt");
                $sql = "SELECT seminare.seminar_id, 
                               seminare.name AS display, 
                               FROM_UNIXTIME(mkdate, '%d.%m.%Y %h:%i:%s') AS count 
                        FROM seminare 
                        WHERE visible = 1 
                        ORDER BY mkdate DESC 
                        LIMIT 10";
                $template->type = "seminar";
                break;
            case "mostdocuments":
                $template->heading = _("die meisten Materialien (Dokumente)");
                $sql = "SELECT a.seminar_id, 
                               b.name AS display, 
                               count(a.seminar_id) as count 
                        FROM seminare b  
                        INNER JOIN dokumente a USING(seminar_id) 
                        WHERE b.visible=1 
                        GROUP BY a.seminar_id  
                        ORDER BY count DESC 
                        LIMIT 10";
                $template->type = "seminar";
                break;
            case "mostpostings":
                $template->heading = _("die aktivsten Veranstaltungen (Postings der letzten zwei Wochen)");
                $sql = " SELECT a.seminar_id, 
                                b.name AS display, 
                                count( a.seminar_id ) AS count
                         FROM px_topics a
                         INNER JOIN seminare b USING ( seminar_id )
                         WHERE b.visible = 1
                         AND a.mkdate > UNIX_TIMESTAMP( NOW( ) - INTERVAL 2 WEEK )
                         GROUP BY a.seminar_id
                         ORDER BY count DESC
                         LIMIT 10 ";
                $template->type = "seminar";
                break;
            case "mostvisitedhomepages":
                $template->heading = _("die beliebtesten Homepages (Besucher)");
                $sql = "SELECT auth_user_md5.user_id, 
                               username, 
                               views as count, 
                             ".$GLOBALS['_fullname_sql']['full'] . " AS display
                        FROM object_views 
                        LEFT JOIN auth_user_md5 ON(object_id=auth_user_md5.user_id) 
                        LEFT JOIN user_info USING (user_id) 
                        WHERE auth_user_md5.user_id IS NOT NULL
                        ORDER BY count DESC 
                        LIMIT 10";
                $template->type = "user";
                break;
        }
        if($sql) {
            $result = DBManager::get()->query($sql);
    	    if  ($result->rowCount() > 0) {
                $template->lines = $result->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $template->error = _("für die gewählte Option stehen keine Daten zur Verfügung");
            }
        } else {
            $template->error = _("die gewählte Option ist nicht verfügbar");
        }
        return $template->render();
    }

    function indicator($key) {
        $template = $this->tf->open('indicator');
        $db = DBManager::get();
        $indicator['seminar_all'] = array("query" => "SELECT count(*) from seminare",
                                          "title" => _("Aktive Veranstaltungen"),
                                          "detail" => _("Alle Veranstaltungen, die nicht archiviert wurden."));
        $indicator['seminar_archived'] = array("query" => "SELECT count(*) from archiv",
                                               "title" => _("Archivierte Veranstaltungen"),
                                               "detail" => _("Alle Veranstaltungen, die archiviert wurden."));
        $indicator['institute_secondlevel_all'] = array("query" => "SELECT count(*) FROM Institute WHERE Institut_id != fakultaets_id",
                                                        "title" => _("beteiligte Einrichtungen"),
                                                        "detail" => _("alle Einrichtungen außer den Fakultäten"));
        $indicator['institute_firstlevel_all'] = array("query" => "SELECT count(*) FROM Institute WHERE Institut_id = fakultaets_id",
                                                       "title" => _("beteiligte Fakultäten"),
                                                       "detail" => _("alle Fakultäten"));
        $indicator['user_admin'] = array("query" => "SELECT count(*) from auth_user_md5 WHERE perms='admin'",
                                "title" => _("registrierte Administratoren"),
                                "detail" => "");
        $indicator['user_dozent'] = array("query" => "SELECT count(*) from auth_user_md5 WHERE perms='dozent'",
                                "title" => _("registrierte Dozenten"),
                                "detail" => "");
        $indicator['user_tutor'] = array("query" => "SELECT count(*) from auth_user_md5 WHERE perms='tutor'",
                                "title" => _("registrierte Tutoren"),
                                "detail" => "");
        $indicator['user_autor'] = array("query" => "SELECT count(*) from auth_user_md5 WHERE perms='autor'",
                                "title" => _("registrierte Autoren"),
                                "detail" => "");
        $indicator['posting'] = array("query" => "SELECT count(*) from px_topics",
                                "title" => _("Postings"),
                                "detail" => "");
        $indicator['document'] = array("query" => "SELECT count(*) from dokumente WHERE url = ''",
                                "title" => _("Dokumente"),
                                "detail" => "");
        $indicator['link'] = array("query" => "SELECT count(*) from dokumente WHERE url != ''",
                                "title" => _("verlinkte Dateien"),
                                "detail" => "");
        $indicator['litlist'] = array("query" => "SELECT count(*) from lit_list",
                                "title" => _("Literaturlisten"),
                                "detail" => "");
        $indicator['termin'] = array("query" => "SELECT count(*) from termine",
                                "title" => _("Termine"),
                                "detail" => "");
        $indicator['news'] = array("query" => "SELECT count(*) from news",
                                "title" => _("News"),
                                "detail" => "");
        $indicator['guestbook'] = array("query" => "SELECT count(*) from user_info WHERE guestbook='1'",
                                "title" => _("Gästebücher"),
                                "detail" => "");
        $indicator['vote'] = array("query" => "SELECT count(*) from vote WHERE type='vote'",
                                "title" => _("Umfragen"),
                                "detail" => "",
                                "constraint" => $GLOBALS['VOTE_ENABLE']);
        $indicator['test'] = array("query" => "SELECT count(*) from vote WHERE type='test'",
                                "title" => _("Tests"),
                                "detail" => "",
                                "constraint" => $GLOBALS['VOTE_ENABLE']);
        $indicator['evaluation'] = array("query" => "SELECT count(*) from eval",
                                "title" => _("Evaluationen"),
                                "detail" => "",
                                "constraint" => $GLOBALS['VOTE_ENABLE']);
        $indicator['wiki_pages'] = array("query" => "SELECT COUNT(DISTINCT keyword) as count from wiki",
                                "title" => _("Wiki-Seiten"),
                                "detail" => "",
                                "constraint" => $GLOBALS['WIKI_ENABLE']);
        $indicator['lernmodul'] = array("query" => "SELECT COUNT(DISTINCT co_id) as count from seminar_lernmodul",
                                "title" => _("ILIAS-Lernmodule"),
                                "detail" => "",
                                "constraint" => $GLOBALS['ILIAS_CONNECT_ENABLE']);
        $indicator['resource'] = array("query" => "SELECT COUNT(*) from resources_objects",
                                "title" => _("Ressourcen-Objekte"),
                                "detail" => "von Stud.IP verwaltete Ressourcen wie Räume oder Geräte",
                                "constraint" => $RESOURCES_ENABLE);
        if (in_array($key,array_keys($indicator))) {
            if (!isset($indicator[$key]['constraint']) || $indicator[$key]['constraint']) {
                $result = $db->query($indicator[$key]['query']);
                $rows = $result->fetch(PDO::FETCH_NUM);
                $template->title = $indicator[$key]['title'];
                if ($indicator[$key]['detail']) {
                    $template->detail = $indicator[$key]['detail'];
                }
                $template->count = $rows[0];
            } else {
                $template->error = sprintf(_("Voraussetzungen für Option %s nicht vorhanden"),"&raquo;".$key."&laquo;");
            }
        } else {
            $template->error = sprintf(_("Option %s nicht verfügbar"),"&raquo;".$key."&laquo;");
        }
        return $template->render();
    }

    function history() {
        $history = file($ABSOLUTE_PATH_STUDIP.'history.txt');
        $out =  formatReady(implode('',$history));
    	return $out;
    }
}

function language_filter($input) {
    $pattern = "'\[lang=(\w*)\]\s*(.*?)\s*\[/lang\]'es";
    $output = preg_replace($pattern,'stripforeignlanguage(\'$1\', \'$2\')',$input);
    return $output;
}

function stripforeignlanguage($language, $text) {
    global $_language;
    list($primary, $sub) = explode('_',$_language);
    if (($language==$primary) || ($language==$_language)) {
        return $text;
    } else {
        return '';
    }
}

?>
