<?php
# Lifter010: TODO

/*
 * Copyright (C) 2014 - Arne Schröder <schroeder@data-quest.de>
 *
 * formerly institut_main.php - Die Eingangsseite fuer ein Institut
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/statusgruppe.inc.php';  //Funktionen der Statusgruppen
include_once $GLOBALS['PATH_EXPORT'] . '/export_linking_func.inc.php';

class Institute_MembersController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        if (Request::option('auswahl')) {
            Request::set('cid', Request::option('auswahl'));
        }

        parent::before_filter($action, $args);

        $this->admin_view = $GLOBALS['perm']->have_perm('admin')
                            && Request::option('admin_view') !== null;

        PageLayout::addScript('multi_person_search.js');
    }

    /**
     * show institute members page
     */
    public function index_action()
    {
        // this page is used for administration (if the user has the proper rights)
        // or for just displaying the workers and their roles
        if ($this->admin_view) {
            PageLayout::setTitle(_('Verwaltung von Mitarbeiter/-innen'));
            if (Navigation::hasItem('/admin/institute/faculty')) {
                Navigation::activateItem('/admin/institute/faculty');
            }
            $GLOBALS['perm']->check('admin');
        } else {
            PageLayout::setTitle(_('Liste der Mitarbeiter/-innen'));
            if (Navigation::hasItem('/course/faculty/view')) {
                Navigation::activateItem('/course/faculty/view');
            }
            $GLOBALS['perm']->check('autor');
        }

        if (!Institute::findCurrent()) {
            require_once 'lib/admin_search.inc.php';

            // TODO: We don't seem to need this since admin_search will stop the script
            PageLayout::postInfo(_('Sie müssen zunächst eine Einrichtung auswählen'));
            $this->redirect('institute/basicdata/index?list=TRUE');
            return;
        }

        $this->institute = Institute::findCurrent();

        $groups = [];
        $group_collector = function ($group) use (&$groups, &$group_collector) {
            $groups[] = $group;
            array_map($group_collector, $group->children);
        };

        $this->institute->status_groups->each($group_collector);
        $this->groups = SimpleCollection::createFromArray(array_flatten($groups));

        if ($this->admin_view && !$GLOBALS['perm']->have_studip_perm('admin', $this->institute->id)) {
            $this->admin_view = false;
        }

        if (!$this->admin_view) {
            checkObject();
            checkObjectModule("personal");
        }

        //Change header_line if open object
        if ($header_line = getHeaderLine($this->institute->id)) {
            PageLayout::setTitle($header_line." - ".PageLayout::getTitle());
        }

        // check the given parameters or initialize them
        if ($GLOBALS['perm']->have_studip_perm('admin', $this->institute->id)) {
            $accepted_columns = array('Nachname', 'inst_perms');
        } else {
            $accepted_columns = array('Nachname');
        }

        $sortby = Request::option('sortby');
        $this->extend = Request::option('extend');
        if (!in_array($sortby, $accepted_columns)) {
            $sortby = 'Nachname';
            $this->statusgruppe_user_sortby = 'position';
        } else {
            $this->statusgruppe_user_sortby = $sortby;
        }

        $this->direction = Request::option('direction');
        if ($this->direction == "ASC") {
            $new_direction = "DESC";
        } else if ($this->direction == "DESC") {
            $new_direction = "ASC";
        } else {
            $this->direction = "ASC";
            $new_direction = "DESC";
        }

        $this->show = Request::option('show');
        if (!isset($this->show)) {
            $this->show = "funktion";
        }
        URLHelper::addLinkParam('admin_view', $this->admin_view);
        URLHelper::addLinkParam('sortby', $sortby);
        URLHelper::addLinkParam('direction', $this->direction);
        URLHelper::addLinkParam('show', $this->show);
        URLHelper::addLinkParam('extend', $this->extend);

        $cmd = Request::option('cmd');
        $role_id = Request::option('role_id');
        $username = Request::username('username');

        if ($cmd == 'removeFromGroup' && $GLOBALS['perm']->have_studip_perm('admin', $this->institute->id)) {
            $user   = User::findByUsername($username);
            $result = Statusgruppen::find($role_id)->removeUser($user->id);
            
            if ($result) {
                PageLayout::postInfo(sprintf(
                    _('%s wurde von der Liste der Mitarbeiter/-innen gelöscht.'),
                    $remove_user->getFullName()
                ));
            }
        }

        if ($cmd == 'removeFromInstitute' && $username != $GLOBALS['user']->username && $GLOBALS['perm']->get_profile_perm(get_userid($username)) == 'admin') {
            $user   = User::findByUsername($username);
            $member = InstituteMember::find([$user->id, $this->institute->id]);

            if ($member && $member->delete()) {
                PageLayout::postInfo(sprintf(
                    _('%s wurde von der Liste der Mitarbeiter/-innen gelöscht.'),
                    $user->getFullName()
                ));

                StudipLog::log('INST_USER_DEL', $this->institute->id, $user->id);
                NotificationCenter::postNotification('UserInstitutionDidDelete', $this->institute->id, $user->id);
                checkExternDefaultForUser($user->id);
            }
        }

        // Jemand soll ans Institut...
        $this->mp = MultiPersonSearch::load("inst_member_add" . $this->institute->id);
        $additionalCheckboxes = $this->mp->getAdditionalOptionArray();

        if ($additionalCheckboxes != NULL && array_search("admins", $additionalCheckboxes) !== false) {
            $enable_mail_admin = true;
        }
        if ($additionalCheckboxes != NULL && array_search("dozenten", $additionalCheckboxes) !== false) {
            $enable_mail_dozent = true;
        }

        if (count($this->mp->getAddedUsers()) > 0) {
            foreach ($this->mp->getAddedUsers() as $u_id) {

                $query = "SELECT inst_perms FROM user_inst WHERE Institut_id = ? AND user_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute(array($this->institute->id, $u_id));
                $inst_perms = $statement->fetchColumn();

                if ($inst_perms && $inst_perms != 'user') {
                    // der Admin hat Tomaten auf den Augen, der Mitarbeiter sitzt schon im Institut
                    PageLayout::postMessage(MessageBox::error(_("Die Person ist bereits in der Einrichtung eingetragen. Um Rechte etc. zu ändern folgen Sie dem Link zu den Nutzerdaten der Person!")));
                } else {  // mal nach dem globalen Status sehen
                    $query = "SELECT {$GLOBALS['_fullname_sql']['full']} AS fullname, perms
                              FROM auth_user_md5
                              LEFT JOIN user_info USING (user_id)
                              WHERE user_id = ?";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute(array($u_id));
                    $user_info = $statement->fetch(PDO::FETCH_ASSOC);

                    $Fullname = $user_info['fullname'];
                    $perms    = $user_info['perms'];

                    if ($perms == 'root') {
                        PageLayout::postMessage(MessageBox::error(_('ROOTs können nicht berufen werden!')));
                    } elseif ($perms == 'admin') {
                        if ($GLOBALS['perm']->have_perm('root') || (!$GLOBALS['SessSemName']["is_fak"] && $GLOBALS['perm']->have_studip_perm("admin",$GLOBALS['SessSemName']["fak"]))) {
                            // Emails schreiben...
                            if ($enable_mail_admin && $enable_mail_dozent) {
                                $in = array('admin', 'dozent');
                                $wem = 'Admins und Dozenten';
                            } else if($enable_mail_admin){
                                $in = array('admin');
                                $wem = 'Admins';
                            } else if($enable_mail_dozent) {
                                $in = array('dozent');
                                $wem = 'Dozenten';
                            }
                            if (!empty($in)) {
                                $notin = array();
                                $mails_sent = 0;

                                $query = "SELECT Name FROM Institute WHERE Institut_id = ?";
                                $statement = DBManager::get()->prepare($query);
                                $statement->execute(array($this->institute->id));
                                $instname = $statement->fetchColumn();

                                $vorname = $Fullname;
                                $nachname = ''; // siehe $vorname

                                $query = "SELECT user_id, Vorname, Nachname, Email
                                          FROM user_inst
                                          INNER JOIN auth_user_md5 USING (user_id)
                                          WHERE Institut_id = ? AND inst_perms IN (?)";
                                $statement = DBManager::get()->prepare($query);
                                $statement->execute(array($this->institute->id, $in));

                                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                                    $user_language = getUserLanguagePath($row['user_id']);
                                    include("locale/$user_language/LC_MAILS/new_admin_mail.inc.php");
                                    StudipMail::sendMessage($row['Email'], $subject, $mailbody);
                                    $notin[] = $row['user_id'];

                                    $mails_sent += 1;
                                }
                                if (!(count($in) == 1 && reset($in) == 'dozent')) {
                                    $notin[] = $u_id;
                                    //Noch ein paar Mails für die Fakultätsadmins
                                    $query = "SELECT user_id, Vorname, Nachname, Email
                                              FROM user_inst
                                              INNER JOIN auth_user_md5 USING (user_id)
                                              WHERE user_id NOT IN (?) AND inst_perms = 'admin'
                                                AND Institut_id IN (
                                                        SELECT fakultaets_id
                                                        FROM Institute
                                                        WHERE Institut_id = ? AND Institut_id != fakultaets_id
                                                    )";
                                    $statement = DBManager::get()->prepare($query);
                                    $statement->execute(array($notin, $this->institute->id));

                                    while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                                        $user_language = getUserLanguagePath($row['user_id']);
                                        include("locale/$user_language/LC_MAILS/new_admin_mail.inc.php");
                                        StudipMail::sendMessage($row['Email'], $subject, $mailbody);

                                        $mails_sent += 1;
                                    }
                                }
                                PageLayout::postMessage(MessageBox::info(sprintf(_("Es wurden ingesamt %s Mails an die %s der Einrichtung geschickt."),$mails_sent,$wem)));
                            }

                            StudipLog::log('INST_USER_ADD', $this->institute->id ,$u_id, 'admin');

                            // als admin aufnehmen
                            $query = "INSERT INTO user_inst (user_id, Institut_id, inst_perms)
                                      VALUES (?, ?, 'admin')";
                            $statement = DBManager::get()->prepare($query);
                            $statement->execute(array($u_id, $this->institute->id));

                            PageLayout::postMessage(MessageBox::info(sprintf(_("%s wurde als \"admin\" in die Einrichtung aufgenommen."), $Fullname)));
                            NotificationCenter::postNotification('UserInstitutionDidCreate', $this->institute->id, $u_id);

                        } else {
                            PageLayout::postMessage(MessageBox::error(_("Sie haben keine Berechtigung einen Admin zu berufen!")));
                        }
                    } else {
                        //ok, aber nur hochstufen auf Maximal-Status (hat sich selbst schonmal gemeldet als Student an dem Inst)
                        if ($inst_perms == 'user') {
                            // ok, neu aufnehmen als das was er global ist
                            $query = "UPDATE user_inst
                                      SET inst_perms = ?
                                      WHERE user_id = ? AND Institut_id = ?";
                            $statement = DBManager::get()->prepare($query);
                            $statement->execute(array($perms, $u_id, $this->institute->id));

                            StudipLog::log('INST_USER_STATUS', $this->institute->id ,$u_id, $perms);
                            NotificationCenter::postNotification('UserInstitutionPermDidUpdate', $this->institute->id, $u_id); 

                        } else {
                            $query = "INSERT INTO user_inst (user_id, Institut_id, inst_perms)
                                      VALUES (?, ?, ?)";
                            $statement = DBManager::get()->prepare($query);
                            $statement->execute(array($u_id, $this->institute->id, $perms));

                            StudipLog::log('INST_USER_ADD', $this->institute->id ,$u_id, $perms);
                        }
                        if ($statement->rowCount()) {
                            PageLayout::postMessage(MessageBox::info(sprintf(_("%s wurde als \"%s\" in die Einrichtung aufgenommen. Um Rechte etc. zu ändern folgen Sie dem Link zu den Nutzerdaten der Person!"), $Fullname, $perms)));
                            NotificationCenter::postNotification('UserInstitutionDidCreate', $this->institute->id, $u_id);

                        } else {
                            PageLayout::postMessage(MessageBox::error(sprintf(_("%s konnte nicht in die Einrichtung aufgenommen werden!"), $Fullname)));
                        }
                    }
                }
                checkExternDefaultForUser($u_id);
            }
            $this->mp->clearSession();
        }

        $lockrule = LockRules::getObjectRule($this->institute->id);
        if ($this->admin_view && $lockrule->description && LockRules::Check($this->institute->id, 'participants')) {
            PageLayout::postMessage(MessageBox::info(formatLinks($lockrule->description)));
        }

        if ($this->institute->id) {
            $inst_name = $GLOBALS['SessSemName'][0];

            if ($this->admin_view) {
                if (!LockRules::Check($this->institute->id, 'participants')) {
                    $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, {$GLOBALS['_fullname_sql']['full_rev']} as fullname, username, perms "
                        . "FROM auth_user_md5 "
                        . "LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) "
                        . "WHERE "
                        . "(username LIKE :input OR Vorname LIKE :input "
                        . "OR CONCAT(Vorname,' ',Nachname) LIKE :input "
                        . "OR CONCAT(Nachname,' ',Vorname) LIKE :input "
                        . "OR Nachname LIKE :input OR {$GLOBALS['_fullname_sql']['full_rev']} LIKE :input) "
                        . "AND visible != 'never' "
                        . " ORDER BY fullname ASC",
                        _("Nutzer suchen"), "user_id");


                    $defaultSelectedUser = new SimpleCollection(InstituteMember::findByInstituteAndStatus($this->institute->id, words('autor tutor dozent admin')));
                    URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
                    $this->mp = MultiPersonSearch::get("inst_member_add" . $this->institute->id)
                    ->setLinkText(_("Mitarbeiter/-innen hinzufügen"))
                    ->setDefaultSelectedUser($defaultSelectedUser->pluck('user_id'))
                    ->setTitle(_('Personen in die Einrichtung eintragen'))
                    ->setExecuteURL(URLHelper::getLink("dispatch.php/institute/members", array('admin_view' => 1)))
                    ->setSearchObject($search_obj)
                    ->setAdditionalHTML('<p><strong>' . _('Nur bei Zuordnung eines Admins:') .' </strong> <label>Benachrichtigung der <input name="additional[]" value="admins" type="checkbox">' . _('Admins') .'</label>
                                         <label><input name="additional[]" value="dozenten" type="checkbox">' . _('Dozenten') . '</label></p>')
                    ->render();
                }
            }

            $default_fields = array(
                'raum'         => _('Raum'),
                'sprechzeiten' => _('Sprechzeiten'),
                'telefon'      => _('Telefon'),
                'email'        => _('E-Mail'),
                'homepage'     => _('Homepage')
            );

            $this->datafields_list = DataField::getDataFields('userinstrole');

            if ($this->extend == 'yes') {
                $dview = $GLOBALS['INST_ADMIN_DATAFIELDS_VIEW']['extended'];
            } else {
                $dview = $GLOBALS['INST_ADMIN_DATAFIELDS_VIEW']['default'];
            }

            if (empty($dview)) {
                $dview = array('raum', 'sprechzeiten', 'telefon', 'email');
                if ($this->extend == 'yes') {
                    $dview[] = 'homepage';
                }
            }

            foreach ($default_fields as $key => $name) {
                if (in_array($key, $dview)) {
                    $this->struct[$key] = array('name' => $name, 'width' => '10%');
                }
            }
            foreach ($this->datafields_list as $entry) {
                if (in_array($entry->id, $dview) === TRUE) {
                    $this->struct[$entry->id] = array (
                        'name' => $entry->name,
                        'width' => '10%'
                    );
                }
            }

            // this array contains the structure of the table for the different views
            if ($this->extend == "yes") {
                switch ($this->show) {
                    case 'liste' :
                        if ($GLOBALS['perm']->have_perm("admin")) {
                            $this->table_structure = array(
                                "name" => array(
                                    "name" => _("Name"),
                                    "link" => "?sortby=Nachname&direction=" . $new_direction,
                                    "width" => "30%"),
                                "status" => array(
                                    "name" => _("Status"),
                                    "link" => "?sortby=inst_perms&direction=" . $new_direction,
                                    "width" => "10"),
                                "statusgruppe" => array(
                                    "name" => _("Funktion"),
                                    "width" => "15%")
                            );
                        }
                        else {
                            $this->table_structure = array(
                                "name" => array(
                                    "name" => _("Name"),
                                    "link" => "?sortby=Nachname&direction=" . $new_direction,
                                    "width" => "30%"),
                                "statusgruppe" => array(
                                    "name" => _("Funktion"),
                                    "width" => "10%")
                            );
                        }
                        break;
                    case 'status' :
                        $this->table_structure = array(
                            "name" => array(
                                "name" => _("Name"),
                                "link" => "?sortby=Nachname&direction=" . $new_direction,
                                "width" => "30%"),
                            "statusgruppe" => array(
                                "name" => _("Funktion"),
                                "width" => "15%")
                        );
                        break;
                    default :
                        if ($GLOBALS['perm']->have_perm("admin")) {
                            $this->table_structure = array(
                                "name" => array(
                                    "name" => _("Name"),
                                    "link" => "?sortby=Nachname&direction=" . $new_direction,
                                    "width" => "30%"),
                                "status" => array(
                                    "name" => _("Status"),
                                    "link" => "?sortby=inst_perms&direction=" . $new_direction,
                                    "width" => "10")
                            );
                        }
                        else {
                            $this->table_structure = array(
                                "name" => array(
                                    "name" => _("Name"),
                                    "link" => "?sortby=Nachname&direction=" . $new_direction,
                                    "width" => "30%")
                            );
                        }
                } // switch
            } else {
                switch ($this->show) {
                    case 'liste' :
                        if ($GLOBALS['perm']->have_perm("admin")) {
                            $this->table_structure = array(
                                "name" => array(
                                    "name" => _("Name"),
                                    "link" => "?sortby=Nachname&direction=" . $new_direction,
                                    "width" => "35%"),
                                "status" => array(
                                    "name" => _("Status"),
                                    "link" => "?sortby=inst_perms&direction=" . $new_direction,
                                    "width" => "10"),
                                "statusgruppe" => array(
                                    "name" => _("Funktion"),
                                    "width" => "15%")
                            );
                        }
                        else {
                            $this->table_structure = array(
                                "name" => array(
                                    "name" => _("Name"),
                                    "link" => "?sortby=Nachname&direction=" . $new_direction,
                                    "width" => "30%"),
                                "statusgruppe" => array(
                                    "name" => _("Funktion"),
                                    "width" => "15%")
                            );
                        }
                        break;
                    case 'status' :
                        $this->table_structure = array(
                            "name" => array(
                                "name" => _("Name"),
                                "link" => "?sortby=Nachname&direction=" . $new_direction,
                                "width" => "40%"),
                            "statusgruppe" => array(
                                "name" => _("Funktion"),
                                "width" => "20%")
                        );
                        break;
                    default :
                        if ($GLOBALS['perm']->have_perm("admin")) {
                            $this->table_structure = array(
                                "name" => array(
                                    "name" => _("Name"),
                                    "link" => "?sortby=Nachname&direction=" . $new_direction,
                                    "width" => "40%"),
                                "status" => array(
                                    "name" => _("Status"),
                                    "link" => "?sortby=inst_perms&direction=" . $new_direction,
                                    "width" => "15")
                            );
                        }
                        else {
                            $this->table_structure = array(
                                "name" => array(
                                    "name" => _("Name"),
                                    "link" => "?sortby=Nachname&direction=" . $new_direction,
                                    "width" => "40%")
                            );
                        }
                } // switch
            }

            $this->table_structure = array_merge((array)$this->table_structure, (array)$this->struct);

            if ($this->admin_view || $GLOBALS['perm']->have_studip_perm('autor', $this->institute->id)) {
                $this->table_structure['actions'] = array(
                    "name" => _("Aktionen"),
                    "width" => "5%"
                );
            }

            if ($this->show == "funktion") {
                $this->display_recursive($this->groups, 0, '', $dview);
                if ($GLOBALS['perm']->have_perm('admin')) {
                    // Collect all assigned users and then collect all
                    // institute members that have not been assigned
                    $assigned = array_unique(array_flatten($this->groups->map(function ($group) {
                        return $group->members->pluck('user_id');
                    })));
                    $institut_members = $this->institute->members->filter(function ($member) use ($assigned) {
                        if (!$GLOBALS['perm']->have_perm('admin') && !($member->visible && $member->user->visible !== 'never')) {
                            return false;
                        }
                        if ($member->inst_perms === 'user') {
                            return false;
                        }
                        return !in_array($member->user_id, $assigned);
                    })->orderBy($sortby . ' ' . $this->direction);

                    $this->renderList($institut_members, [
                        'dview'       => $dview,
                        'th_title'    => _('keiner Funktion zugeordnet'),
                    ]);
                }
            } elseif ($this->show == 'status') {
                $inst_permissions = array(
                    'admin'  => _('Admin'),
                    'dozent' => _('Lehrende'),
                    'tutor'  => _('Tutor/-in'),
                    'autor'  => _('Studierende')
                );

                foreach ($inst_permissions as $key => $permission) {
                    $institut_members = $this->institute->members->filter(function ($member) use ($key) {
                        if (!$GLOBALS['perm']->have_perm('admin') && !($member->visible && $member->user->visible !== 'never')) {
                            return false;
                        }

                        return $member->inst_perms === $key;
                    })->orderBy($sortby . ' ' . $this->direction);

                    $this->renderList($institut_members, [
                        'dview'       => $dview,
                        'mail_status' => true,
                        'key'         => $key,
                        'th_title'    => $permission,
                    ]);
                }
            } else {
                $institut_members = $this->institute->members->filter(function ($member) {
                    if (!$GLOBALS['perm']->have_perm('admin') && !($member->visible && $member->user->visible !== 'never')) {
                        return false;
                    }

                    if ($GLOBALS['perm']->have_perm('admin')) {
                        return $member->inst_perms !== 'user';
                    }

                    foreach ($this->groups as $group) {
                        if ($group->isMember($member->user_id)) {
                            return true;
                        }
                    }

                    return false;
                })->orderBy($sortby . ' ' . $this->direction);

                $this->renderList($institut_members, ['dview' => $dview]);
            }
        }
    }

    private function display_recursive($roles, $level = 0, $title = '', $dview = array())
    {
        foreach ($roles as $role) {
            if ($title == '') {
                $zw_title = $role->name;
            } else {
                $zw_title = $title .' > '. $role->name;
            }

            // Find members
            $institut_members = $this->institute->members->filter(function ($member) use ($role) {
                if ($member->inst_perms === 'user') {
                    return false;
                }
                if (!$GLOBALS['perm']->have_perm('admin') && !($member->visible && $member->user->visible !== 'never')) {
                    return false;
                }
                return $role->isMember($member->user_id);
            });

            // Sort
            if ($this->statusgruppe_user_sortby === 'position') {
                $ordered = [];
                $role->members->each(function ($member) use (&$ordered, $institut_members) {
                    $inst_member = $institut_members->findOneBy('user_id', $member->user_id);
                    if ($inst_member) {
                        $ordered[] = $inst_member;
                    }
                });
                $institut_members->exchangeArray($ordered);
            } else {
                $institut_members = $institut_members->orderBy($this->statusgruppe_user_sortby . ' ' . $this->direction);
            };

            // output
            $this->renderList($institut_members, [
                'role'        => $role,
                'th_title'    => $zw_title,
                'dview'       => $dview,

                // StEP 154: Nachricht an alle Mitglieder der Gruppe
                'mail_gruppe' => $GLOBALS['perm']->have_studip_perm('autor', $this->institute_id)
                              && $GLOBALS['ENABLE_EMAIL_TO_STATUSGROUP'],
            ]);

            if ($role->children) {
                $this->display_recursive($role->children, $level + 1, $zw_title, $dview);
            }
        }
    }
    
    private function renderList($members, $further_variables = [])
    {
        if (count($members) === 0) {
            return;
        }

        $template = $this->get_template_factory()->open('institute/members/_table_body.php');
        $template->range_id        = $this->institute->id;
        $template->struct          = $this->struct;
        $template->structure       = $this->table_structure;
        $template->datafields_list = $this->datafields_list;
        $template->groups          = $this->groups;
        $template->admin_view      = $this->admin_view;

        $template->members = $members;
        $template->set_attributes($further_variables);
        $this->table_content .= $template->render();
    }
}
