<?php
/**
 * user.php - controller class for the user-administration
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       2.1
 */
require_once 'vendor/email_message/blackhole_message.php';

/**
 *
 * controller class for the user-administration
 *
 */
class Admin_UserController extends AuthenticatedController
{
    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;
        parent::before_filter($action, $args);
        
        // user must have root permission if restricted user management is disabled
        $perm->check(Config::get()->RESTRICTED_USER_MANAGEMENT ? 'root' : 'admin');
        
        // set navigation
        Navigation::activateItem('/admin/user/index');
        
        //PageLayout
        PageLayout::setHelpKeyword("Admins.Benutzerkonten");
        PageLayout::setTitle(_("Personenverwaltung"));
        
        $this->action = $action;
        $this->args   = $args;
        
        NotificationCenter::addObserver($this, 'addSidebar', 'SidebarWillRender');
    }
    
    /**
     * Display searchbox and all searched users (if any).
     *
     * @param bool $advanced open or close the advanced searchfields
     */
    public function index_action($advanced = false)
    {
        global $perm;
        
        $this->perm = $perm;
        $request    = '';
        //Daten annehmen
        if (Request::submitted('reset')) {
            unset($_SESSION['admin']['user']);
        } elseif (Request::submitted('search')) {
            $request = $_SESSION['admin']['user'] = Request::getInstance();
        }
        
        //Suchparameter und Ergebnisse vorhanden
        if (isset($_SESSION['admin']['user']) && $_SESSION['admin']['user']['results']) {
            $request = $_SESSION['admin']['user'];
        }
        
        if (!empty($request)) {
            // Inaktivität für die suche anpassen
            $inaktiv = [$request['inaktiv'], $request['inaktiv_tage']];
            if (empty($request['inaktiv_tage']) && $request['inaktiv'] != 'nie') {
                $inaktiv = null;
            }
        }
        
        //Datafields
        $datafields = DataField::getDataFields("user");
        foreach ($datafields as $datafield) {
            if ($datafield->accessAllowed()) {
                $this->datafields[] = $datafield;
            }
        }
        
        //wenn suche durchgeführt
        if (!empty($request)) {
            //suche mit datafields
            foreach ($this->datafields as $datafield) {
                if (strlen($request[$datafield->id]) > 0
                    && !(in_array($datafield->type, words('selectbox radio')) && $request[$datafield->id] === '---ignore---')
                ) {
                    $search_datafields[$datafield->id] = $request[$datafield->id];
                }
            }
            
            //Suchparameter
            $this->user   = $request;
            $this->sortby = Request::option('sortby', 'username');
            $this->order  = Request::option('order', 'asc');
            if (Request::int('toggle')) {
                $this->order = $this->order == 'desc' ? 'asc' : 'desc';
            }
            $request['vorname']  = ($request['vorname']) ? $request['vorname'] : null;
            $request['nachname'] = ($request['nachname']) ? $request['nachname'] : null;
            
            //Daten abrufen
            $this->users = UserModel::getUsers($request['username'], $request['vorname'],
                $request['nachname'], $request['email'], $inaktiv, $request['perm'],
                $request['locked'], $search_datafields, $request['userdomains'], $request['auth_plugins'],
                $this->sortby, $this->order, $request['degree'], $request['studycourse'], $request['institute']);
            
            // Fehler abfangen
            if ($this->users === 0) {
                PageLayout::postMessage(MessageBox::info(_('Sie haben keine Suchkriterien ausgewählt!')));
            } elseif (count($this->users) < 1 && Request::submitted('search')) {
                PageLayout::postMessage(MessageBox::info(_('Es wurden keine Personen mit diesen Suchkriterien gefunden.')));
            } else {
                $_SESSION['admin']['user']['results'] = true;
                PageLayout::postMessage(MessageBox::info(sprintf(_('Es wurden %s Personen mit diesen Suchkriterien gefunden.'), count($this->users))));
            }
            if (is_array($this->users) && Request::submitted('export')) {
                $tmpname  = md5(uniqid('tmp'));
                $captions = ['username',
                             'vorname',
                             'nachname',
                             'email',
                             'status',
                             'authentifizierung',
                             'domänen',
                             'registriert seit',
                             'inaktiv seit'];
                $mapper   = function ($u) {
                    return [$u['username'],
                            $u['Vorname'],
                            $u['Nachname'],
                            $u['Email'],
                            $u['perms'],
                            $u['auth_plugin'],
                            $u['userdomains'],
                            strftime('%x', $u['mkdate']),
                            strftime('%x', $u['changed_timestamp'])];
                };
                if (array_to_csv(array_map($mapper, $this->users), $GLOBALS['TMP_PATH'] . '/' . $tmpname, $captions)) {
                    $this->redirect(GetDownloadLink($tmpname, 'nutzer-export.csv', 4));
                }
            }
        }
        $this->degrees                = Abschluss::findBySQL('1 order by name');
        $this->studycourses           = Fach::findBySQL('1 order by name');
        $this->userdomains            = UserDomain::getUserDomains();
        $this->institutes             = Institute::getInstitutes();
        $this->available_auth_plugins = UserModel::getAvailableAuthPlugins();
        
        //show datafields search
        if ($advanced
            || count($search_datafields) > 0
            || (!empty($request)
                && ($request['auth_plugins'] || $request['userdomains'] || $request['degree'] || $request['institute'] || $request['studycourse'])
            )
        ) {
            $this->advanced = true;
        }
    }
    
    /**
     * Deleting one or more users
     *
     * @param md5 $user_id
     * @param string $parent redirect to this page after deleting users
     */
    public function delete_action($user_id = null, $parent = '')
    {
        //deleting one user
        if (!is_null($user_id)) {
            $user = UserModel::getUser($user_id);
            
            //check user
            if (empty($user)) {
                PageLayout::postError(_('Fehler! Zu löschende Person ist nicht vorhanden.'));
                //antwort ja
            } elseif (!empty($user) && Request::submitted('delete')) {
                CSRFProtection::verifyUnsafeRequest();
                
                //if deleting user, go back to mainpage
                $parent = '';
                
                //deactivate message
                if (!Request::int('mail')) {
                    $dev_null       = new blackhole_message_class();
                    $default_mailer = StudipMail::getDefaultTransporter();
                    StudipMail::setDefaultTransporter($dev_null);
                }
                //preparing delete
                $umanager = new UserManagement();
                $umanager->getFromDatabase($user_id);
                
                //delete
                if ($umanager->deleteUser(Request::option('documents', false))) {
                    $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($umanager->msg, 0, -1)));
                    PageLayout::postSuccess(htmlReady(sprintf(_('"%s %s (%s)" wurde erfolgreich gelöscht.'), $user['Vorname'], $user['Nachname'], $user['username'])), $details);
                } else {
                    $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($umanager->msg, 0, -1)));
                    PageLayout::postError(htmlReady(sprintf(_('Fehler! "%s %s (%s)" konnte nicht gelöscht werden.'), $user['Vorname'], $user['Nachname'], $user['username'])), $details);
                }
                
                //reavtivate messages
                if (!Request::int('mail')) {
                    StudipMail::setDefaultTransporter($default_mailer);
                }
                
                //sicherheitsabfrage
            } elseif (!empty($user) && !Request::submitted('back')) {
                
                $this->flash['delete'] = [
                    'question' => sprintf(_('Wollen Sie "%s %s (%s)" wirklich löschen?'), $user['Vorname'], $user['Nachname'], $user['username']),
                    'action'   => ($parent != '') ? $this->url_for('admin/user/delete/' . $user_id . '/' . $parent) : $this->url_for('admin/user/delete/' . $user_id),
                ];
            }
            
            //deleting more users
        } else {
            $user_ids = Request::getArray('user_ids');
            
            if (count($user_ids) == 0) {
                PageLayout::postError(_('Bitte wählen Sie mindestens eine Person zum Löschen aus.'));
                $this->redirect('admin/user/' . $parent);
                return;
            }
            
            if (Request::submitted('delete')) {
                
                CSRFProtection::verifyUnsafeRequest();
                
                //deactivate message
                if (!Request::int('mail')) {
                    $dev_null       = new blackhole_message_class();
                    $default_mailer = StudipMail::getDefaultTransporter();
                    StudipMail::setDefaultTransporter($dev_null);
                }
                
                foreach ($user_ids as $i => $user_id) {
                    $users[$i] = UserModel::getUser($user_id);
                    //preparing delete
                    $umanager = new UserManagement();
                    $umanager->getFromDatabase($user_id);
                    
                    //delete
                    if ($umanager->deleteUser(Request::option('documents', false))) {
                        $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($umanager->msg, 0, -1)));
                        PageLayout::postSuccess(htmlReady(sprintf(_('"%s %s (%s)" wurde erfolgreich gelöscht'),
                            $users[$i]['Vorname'],
                            $users[$i]['Nachname'],
                            $users[$i]['username'])), $details);
                    } else {
                        $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($umanager->msg, 0, -1)));
                        PageLayout::postError(htmlReady(sprintf(_('Fehler! "%s %s (%s)" konnte nicht gelöscht werden'),
                            $users[$i]['Vorname'],
                            $users[$i]['Nachname'],
                            $users[$i]['username'])), $details);
                    }
                }
                
                //reactivate messages
                if (!Request::int('mail')) {
                    StudipMail::setDefaultTransporter($default_mailer);
                }
                
                //sicherheitsabfrage
            } elseif (!Request::submitted('back')) {
                $users = [];
                foreach ($user_ids as $user_id) {
                    $users[] = UserModel::getUser($user_id);
                }
                $this->flash['delete'] = [
                    'question' => _('Wollen Sie folgende Personen wirklich löschen?'),
                    'action'   => $this->url_for('admin/user/delete'),
                    'users'    => $users,
                ];
            }
        }
        
        //liste wieder anzeigen
        if ($parent == 'edit') {
            $this->redirect('admin/user/edit/' . $user_id);
        } else {
            $this->redirect('admin/user/' . $parent);
        }
    }
    
    /**
     * Display all information according to the selected user. All details can
     * be changed and deleted.
     *
     * @param md5 $user_id
     */
    public function edit_action($user_id = null)
    {
        global $perm, $auth;
        
        //check submitted user_id
        if (is_null($user_id)) {
            if (Request::option('user')) {
                $user_id = Request::option('user');
            } else {
                PageLayout::postInfo(_('Sie haben niemanden ausgewählt!'));
                //liste wieder anzeigen
                $this->redirect('admin/user/');
                return;
            }
        }
        
        //get user
        $this->user = User::find($user_id);
        
        // Änderungen speichern
        if (Request::submitted('edit')) {
            if (Request::get('auth_plugin') == 'preliminary') {
                Request::set('auth_plugin', null);
            }
            $editPerms = Request::getArray('perms');
            $um        = new UserManagement($user_id);
            
            //new user data
            $editUser = [];
            if (count($editPerms)) {
                $editUser['auth_user_md5.perms'] = $editPerms[0];
            }
            foreach (words('Vorname Nachname auth_plugin visible') as $param) {
                if (Request::get($param)) $editUser['auth_user_md5.' . $param] = Request::get($param);
            }
            foreach (words('title_front title_rear geschlecht') as $param) {
                if (Request::get($param) !== null) $editUser['user_info.' . $param] = Request::get($param);
            }
            //change username
            if (Request::get('username') && $this->user['username'] != Request::get('username')) {
                $editUser['auth_user_md5.username'] = Request::get('username');
            }
            //change email
            if (Request::get('Email') && $this->user['Email'] != Request::get('Email')) {
                //disable mailbox validation
                if (Request::get('disable_mail_host_check')) {
                    $GLOBALS['MAIL_VALIDATE_BOX'] = false;
                }
                $editUser['auth_user_md5.Email'] = Request::get('Email');
            }
            
            //change password
            if (($GLOBALS['perm']->have_perm('root') && Config::get()->ALLOW_ADMIN_USERACCESS) && (Request::get('pass_1') != '' || Request::get('pass_2') != '')) {
                if (Request::get('pass_1') == Request::get('pass_2')) {
                    if (strlen(Request::get('pass_1')) < 4) {
                        $details[] = _("Das Passwort ist zu kurz. Es sollte mindestens 4 Zeichen lang sein.");
                    } else {
                        $um->changePassword(Request::get('pass_1'));
                    }
                } else {
                    $details[] = _("Bei der Wiederholung des Passwortes ist ein Fehler aufgetreten! Bitte geben Sie das exakte Passwort ein!");
                }
            }
            
            //deleting validation-key
            if (Request::get('delete_val_key') == "1") {
                $editUser['auth_user_md5.validation_key'] = '';
                $details[]                                = _('Der Validation-Key wurde entfernt.');
            }
            
            //locking the user
            if (Request::get('locked')) {
                $editUser['auth_user_md5.locked']       = 1;
                $editUser['auth_user_md5.lock_comment'] = Request::get('locked_comment');
                $editUser['auth_user_md5.locked_by']    = $auth->auth["uid"];
                $details[]                              = _('Person wurde gesperrt.');
            }
            
            //changing studiendaten
            if (in_array($editPerms[0], ['autor', 'tutor', 'dozent']) && Request::option('new_studiengang') != 'none' && Request::option('new_abschluss') != 'none') {
                //change studycourses
                if (Request::option('new_studiengang') == 'none' || Request::option('new_abschluss') == 'none') {
                    $details[] = _('<b>Der Studiengang wurde nicht hinzugefügt.</b> Bitte geben Sie Fach und Abschluss ein.');
                } else {
                    $user_stc = UserStudyCourse::find([
                        $user_id,
                        Request::option('new_studiengang'),
                        Request::option('new_abschluss'),
                    ]);
                    if (!$user_stc) {
                        UserStudyCourse::create([
                            'user_id'      => $user_id,
                            'fach_id'      => Request::option('new_studiengang'),
                            'semester'     => Request::int('fachsem'),
                            'abschluss_id' => Request::option('new_abschluss'),
                        ]);
                        $details[] = _('Der Studiengang wurde hinzugefügt.');
                    } else {
                        $user_stc->semester = Request::int('fachsem');
                        if ($user_stc->store()) {
                            $details[] = _('Der Studiengang wurde geändert.');
                        } else {
                            $details[] = _('Der Studiengang wurde nicht geändert.');
                        }
                    }
                }
            }
            
            // change version of studiengang if module management is enabled
            if (PluginEngine::getPlugin('MVVPlugin') && in_array($editPerms[0], ['autor', 'tutor', 'dozent'])) {
                $change_versions = Request::getArray('change_version');
                foreach ($change_versions as $fach_id => $abschluesse) {
                    foreach ($abschluesse as $abschluss_id => $version_id) {
                        $version = reset(StgteilVersion::findByFachAbschluss(
                            $fach_id, $abschluss_id, $version_id));
                        if ($version && $version->hasPublicStatus('genehmigt')) {
                            $user_stc = UserStudyCourse::find([
                                $user_id,
                                $fach_id,
                                $abschluss_id]);
                            if ($user_stc) {
                                $user_stc->version_id = $version->getId();
                                $any_change           = $user_stc->store() != false;
                            }
                        }
                    }
                }
                if ($any_change) {
                    $details[] = _('Die Versionen der Studiengänge wurden geändert.');
                }
            }
            
            //change institute for studiendaten
            if (in_array($editPerms[0], ['autor', 'tutor', 'dozent'])
                && Request::option('new_student_inst')
                && Request::option('new_student_inst') != Request::option('new_inst')
                && $GLOBALS['perm']->have_studip_perm("admin", Request::option('new_student_inst'))
            ) {
                StudipLog::log('INST_USER_ADD', Request::option('new_student_inst'), $user_id, 'user');
                $db = DBManager::get()->prepare("INSERT IGNORE INTO user_inst (user_id, Institut_id, inst_perms) "
                                                . "VALUES (?,?,'user')");
                $db->execute([$user_id, Request::option('new_student_inst')]);
                NotificationCenter::postNotification('UserInstitutionDidCreate', Request::option('new_student_inst'), $user_id);
                $details[] = _('Die Einrichtung wurde hinzugefügt.');
            }
            
            //change institute
            if (Request::option('new_inst')
                && Request::option('new_student_inst') != Request::option('new_inst')
                && $editPerms[0] != 'root'
                && $GLOBALS['perm']->have_studip_perm("admin", Request::option('new_inst'))
            ) {
                StudipLog::log('INST_USER_ADD', Request::option('new_inst'), $user_id, $editPerms[0]);
                $db = DBManager::get()->prepare("REPLACE INTO user_inst (user_id, Institut_id, inst_perms) "
                                                . "VALUES (?,?,?)");
                $db->execute([$user_id, Request::option('new_inst'), $editPerms[0]]);
                NotificationCenter::postNotification('UserInstitutionDidUpdate', Request::option('new_inst'), $user_id);
                checkExternDefaultForUser($user_id);
                $details[] = _('Die Einrichtung wurde hinzugefügt.');
            } elseif (Request::option('new_inst') != 'none' && Request::option('new_student_inst') == Request::option('new_inst') && $editPerms[0] != 'root') {
                $details[] = _('<b>Die Einrichtung wurde nicht hinzugefügt.</b> Sie können keine Person gleichzeitig als Studierende/-r und als Mitarbeiter/-in einer Einrichtung hinzufügen.');
            }
            
            //change userdomain
            if (Request::get('new_userdomain', 'none') != 'none' && $editPerms[0] != 'root') {
                $domain = new UserDomain(Request::get('new_userdomain'));
                $domain->addUser($user_id);
                $result = AutoInsert::instance()->saveUser($user_id);
                
                $details[] = _('Die Nutzerdomäne wurde hinzugefügt.');
                foreach ($result['added'] as $item) {
                    $details[] = sprintf(_("Das automatische Eintragen in die Veranstaltung <em>%s</em> wurde durchgeführt."), $item);
                }
                foreach ($result['removed'] as $item) {
                    $details[] = sprintf(_("Das automatische Austragen aus der Veranstaltung <em>%s</em> wurde durchgeführt."), $item);
                }
            }
            
            //change datafields
            $datafields = Request::getArray('datafields');
            foreach (DataFieldEntry::getDataFieldEntries($user_id) as $id => $entry) {
                if (isset($datafields[$id])) {
                    $entry->setValueFromSubmit($datafields[$id]);
                    if ($entry->isValid()) {
                        $entry->store();
                    }
                }
            }
            
            //change ablaufdatum
            if (Request::get('expiration_date_delete') == 1) {
                UserConfig::get($user_id)->delete("EXPIRATION_DATE");
            } elseif (Request::get('expiration_date')) {
                $a = explode(".", stripslashes(trim(Request::get('expiration_date'))));
                if ($timestamp = @mktime(0, 0, 0, $a[1], $a[0], $a[2])) {
                    UserConfig::get($user_id)->store("EXPIRATION_DATE", $timestamp);
                    $details[] = _("Das Ablaufdatum wurde geändert.");
                } else {
                    $details[] = _("Das Ablaufdatum wurde in einem falschen Format angegeben.");
                }
            }
            
            if ($GLOBALS['perm']->have_perm('root') && Request::get('lock_rule')) {
                $st = DBManager::get()->prepare("UPDATE user_info SET lock_rule=? WHERE user_id=?");
                $st->execute([(Request::option('lock_rule') == 'none' ? '' : Request::option('lock_rule')), $user_id]);
                if ($st->rowCount()) {
                    $details[] = _("Die Sperrebene wurde geändert.");
                }
            }
            
            if (!Request::int('u_edit_send_mail')) {
                $dev_null       = new blackhole_message_class();
                $default_mailer = StudipMail::getDefaultTransporter();
                StudipMail::setDefaultTransporter($dev_null);
                $GLOBALS['MAIL_VALIDATE_BOX']  = false;
                $GLOBALS['MAIL_VALIDATE_HOST'] = false;
            }
            //save action and messages
            $um->changeUser($editUser);
            if (!Request::int('u_edit_send_mail')) {
                StudipMail::setDefaultTransporter($default_mailer);
            }
            //get message
            $umdetails = explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($um->msg, 0, -1)));
            $details   = array_reverse(array_merge((array)$details, (array)$umdetails));
            PageLayout::postInfo(_('Hinweise:'), $details);
            
            $this->redirect('admin/user/edit/' . $user_id);
        }
        
        
        $this->prelim = $this->user->auth_plugin == 'preliminary';
        if ($this->prelim) {
            $this->available_auth_plugins['preliminary'] = _("vorläufig");
        }
        foreach ($GLOBALS['STUDIP_AUTH_PLUGIN'] as $ap) {
            $this->available_auth_plugins[strtolower($ap)] = $ap;
        }
        $this->about = new about($this->user['username'], '');
        
        if (count($this->user->institute_memberships)) {
            $this->student_institutes = $this->user->institute_memberships->filter(function ($a) {
                return $a->inst_perms == 'user';
            });
            $this->institutes         = $this->user->institute_memberships->filter(function ($a) {
                return $a->inst_perms != 'user';
            });
        }
        
        $this->available_institutes = Institute::getMyInstitutes();
        $this->userfields           = DataFieldEntry::getDataFieldEntries($user_id, 'user');
        $this->userdomains          = UserDomain::getUserDomainsForUser($user_id);
        if (LockRules::CheckLockRulePermission($user_id) && LockRules::getObjectRule($user_id)->description) {
            PageLayout::postMessage(MessageBox::info(formatLinks(LockRules::getObjectRule($user_id)->description)));
        }
    }
    
    /*
     * Adding a new user to Stud.IP
     */
    public function new_action($prelim = false)
    {
        global $perm, $auth;
        
        $this->perm   = $perm;
        $this->prelim = $prelim;
        
        //check auth_plugins
        if (!in_array("Standard", $GLOBALS['STUDIP_AUTH_PLUGIN']) && !$prelim) {
            PageLayout::postInfo(_('Die Standard-Authentifizierung ist ausgeschaltet. Das Anlegen von neuen Benutzern ist nicht möglich!'));
            $this->redirect('admin/user');
        }
        
        //get formdata
        $this->user = [
            'username'    => Request::get('username'),
            'perm'        => Request::option('perm'),
            'visible'     => Request::get('visible'),
            'Vorname'     => Request::get('Vorname'),
            'Nachname'    => Request::get('Nachname'),
            'geschlecht'  => Request::int('geschlecht'),
            'title_front' => Request::get('title_front'),
            'title_rear'  => Request::get('title_rear'),
            'Email'       => Request::get('Email'),
            'auth_plugin' => Request::get('auth_plugin'),
            'institute'   => Request::option('institute'),
        ];
        
        //save new user
        if (Request::submitted('speichern')) {
            
            //disable mailbox validation
            if (Request::get('disable_mail_host_check')) {
                $GLOBALS['MAIL_VALIDATE_BOX'] = false;
            }
            
            //messagebox details
            $details = [];
            
            //new user data
            $newuser = [
                'auth_user_md5.username'    => $this->user['username'],
                'auth_user_md5.Vorname'     => $this->user['Vorname'],
                'auth_user_md5.Nachname'    => $this->user['Nachname'],
                'auth_user_md5.Email'       => $this->user['Email'],
                'auth_user_md5.perms'       => $this->user['perm'],
                'auth_user_md5.auth_plugin' => $this->user['auth_plugin'],
                'auth_user_md5.visible'     => $this->user['visible'],
                'user_info.title_front'     => $this->user['title_front'],
                'user_info.title_rear'      => $this->user['title_rear'],
                'user_info.geschlecht'      => $this->user['geschlecht'],
            ];
            
            //create new user
            $UserManagement = new UserManagement();
            if (!$prelim) {
                $created = $UserManagement->createNewUser($newuser);
            } else {
                $created = $UserManagement->createPreliminaryUser($newuser);
            }
            if ($created) {
                
                //get user_id
                $user_id = $UserManagement->user_data['auth_user_md5.user_id'];
                
                //new user is added to an institute
                if (Request::get('institute')
                    && $perm->have_studip_perm('admin', Request::get('institute'))
                    && $UserManagement->user_data['auth_user_md5.perms'] != 'root'
                    && ($UserManagement->user_data['auth_user_md5.perms'] != 'admin'
                        || ($perm->is_fak_admin() && !Institute::find(Request::get('institute'))->isFaculty())
                        || $perm->have_perm('root'))
                ) {
                    
                    //log
                    StudipLog::log('INST_USER_ADD', Request::option('institute'), $user_id, $UserManagement->user_data['auth_user_md5.perms']);
                    
                    //insert into database
                    $db    = DBManager::get()->prepare("INSERT INTO user_inst (user_id, Institut_id, inst_perms) VALUES (?, ?, ?)");
                    $check = $db->execute([$user_id, Request::option('institute'), $UserManagement->user_data['auth_user_md5.perms']]);
                    NotificationCenter::postNotification('UserInstitutionDidCreate', Request::option('institute'), $user_id);
                    checkExternDefaultForUser($user_id);
                    
                    //send email, if new user is an admin
                    if ($check) {
                        //check recipients
                        if (Request::get('enable_mail_admin') == "admin" && Request::get('enable_mail_dozent') == "dozent") {
                            $in  = words('admin dozent');
                            $wem = "Admins und Dozenten";
                        } elseif (Request::get('enable_mail_admin') == "admin") {
                            $in  = 'admin';
                            $wem = "Admins";
                        } elseif (Request::get('enable_mail_dozent') == "dozent") {
                            $in  = 'dozent';
                            $wem = "Dozenten";
                        }
                        
                        if (!empty($in) && Request::get('perm') == 'admin') {
                            
                            $i     = 0;
                            $notin = [];
                            
                            $sql       = "SELECT Name FROM Institute WHERE Institut_id = ?";
                            $statement = DBManager::get()->prepare($sql);
                            $statement->execute([
                                Request::option('institute'),
                            ]);
                            $inst_name = $statement->fetchColumn();
                            
                            //get admins
                            $sql
                                       = "SELECT user_id, b.Vorname, b.Nachname, b.Email
                                    FROM user_inst AS a
                                    INNER JOIN auth_user_md5 AS b USING (user_id)
                                    WHERE a.Institut_id = ? AND a.inst_perms IN (?) AND a.user_id != ?";
                            $statement = DBManager::get()->prepare($sql);
                            $statement->execute([
                                Request::option('institute'),
                                $in,
                                $user_id,
                            ]);
                            $users = $statement->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($users as $admin) {
                                $subject  = _("Neuer Administrator in Ihrer Einrichtung angelegt");
                                $mailbody = sprintf(_("Liebe(r) %s %s,\n\n"
                                                      . "in der Einrichtung '%s' wurde %s %s als Administrator eingetragen "
                                                      . " und steht Ihnen als neuer Ansprechpartner bei Fragen oder Problemen "
                                                      . "in Stud.IP zur Verfügung. "),
                                    $admin['Vorname'], $admin['Nachname'],
                                    $inst_name, $this->user['Vorname'], $this->user['Nachname']);
                                
                                StudipMail::sendMessage($admin['Email'], $subject, $mailbody);
                                $notin[] = $admin['user_id'];
                                $i++;
                            }
                            
                            //Noch ein paar Mails für die Fakultätsadmins
                            if ($in != 'dozent') {
                                $notin[] = $user_id;
                                //get admins
                                $sql
                                           = "SELECT a.user_id, b.Vorname, b.Nachname, b.Email
                                        FROM user_inst AS a
                                        INNER JOIN auth_user_md5 AS b USING (user_id)
                                        WHERE a.user_id NOT IN (?) AND a.Institut_id IN (
                                            SELECT fakultaets_id
                                            FROM Institute
                                            WHERE Institut_id = ? AND fakultaets_id != Institut_id
                                        ) AND a.inst_perms = 'admin'";
                                $statement = DBManager::get()->prepare($sql);
                                $statement->execute([
                                    $notin,
                                    Request::option('institute'),
                                ]);
                                $fak_admins = $statement->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($fak_admins as $admin) {
                                    $subject  = _("Neuer Administrator in Ihrer Einrichtung angelegt");
                                    $mailbody = sprintf(_("Liebe(r) %s %s,\n\n"
                                                          . "in der Einrichtung '%s' wurde %s %s als Administrator eingetragen "
                                                          . " und steht Ihnen als neuer Ansprechpartner bei Fragen oder Problemen "
                                                          . "in Stud.IP zur Verfügung. "),
                                        $admin['Vorname'], $admin['Nachname'],
                                        $inst_name, $this->user['Vorname'], $this->user['Nachname']);
                                    
                                    StudipMail::sendMessage($admin['Email'], $subject, $mailbody);
                                    $i++;
                                }
                            }
                            $details[] = sprintf(_('Es wurden ingesamt %s Mails an die %s der Einrichtung "%s" geschickt.'), $i, $wem, htmlReady($inst_name));
                        }
                        
                        $details[] = sprintf(_('Person wurde erfolgreich in die Einrichtung "%s" mit dem Status "%s" eingetragen.'), htmlReady($inst_name), $UserManagement->user_data['auth_user_md5.perms']);
                    } else {
                        $details[] = sprintf(_('Person konnte nicht in die Einrichtung "%s" eingetragen werden.'), htmlReady($inst_name));
                    }
                }
                
                //adding userdomain
                if (Request::get('select_dom_id')) {
                    $domain = new UserDomain(Request::get('select_dom_id'));
                    if ($perm->have_perm('root') || in_array($domain, UserDomain::getUserDomainsForUser($auth->auth["uid"]))) {
                        $domain->addUser($user_id);
                        $details[] = sprintf(_('Person wurde in Nutzerdomäne "%s" eingetragen.'), htmlReady($domain->getName()));
                    } else {
                        $details[] = _('Person konnte nicht in die Nutzerdomäne eingetragen werden.');
                    }
                    $result = AutoInsert::instance()->saveUser($user_id);
                    
                    foreach ($result['added'] as $item) {
                        $details[] = sprintf(_('Das automatische Eintragen in die Veranstaltung <em>%s</em> wurde durchgeführt.'), $item);
                    }
                    foreach ($result['removed'] as $item) {
                        $details[] = sprintf(_('Das automatische Austragen aus der Veranstaltung <em>%s</em> wurde durchgeführt.'), $item);
                    }
                }
                
                //get message
                $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($UserManagement->msg, 0, -1)));
                PageLayout::postSuccess(_('Person wurde angelegt.'), $details);
                $this->redirect('admin/user/edit/' . $user_id);
            } else {
                //get message
                $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($UserManagement->msg, 0, -1)));
                PageLayout::postError(_('Person konnte nicht angelegt werden.'), $details);
            }
        }
        
        if ($this->perm->have_perm('root')) {
            $sql
                     = "SELECT Institut_id, Name, 1 AS is_fak
                    FROM Institute
                    WHERE Institut_id=fakultaets_id
                    ORDER BY Name";
            $faks    = DBManager::get()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $domains = UserDomain::getUserDomains();
        } else {
            $sql
                       = "SELECT a.Institut_id, Name, b.Institut_id = b.fakultaets_id AS is_fak
                    FROM user_inst a
                    LEFT JOIN Institute b USING (Institut_id)
                    WHERE a.user_id = ? AND a.inst_perms = 'admin'
                    ORDER BY is_fak, Name";
            $statement = DBManager::get()->prepare($sql);
            $statement->execute([$auth->auth['uid']]);
            $faks    = $statement->fetchAll(PDO::FETCH_ASSOC);
            $domains = UserDomain::getUserDomainsForUser($auth->auth["uid"]);
        }
        
        $query
                   = "SELECT Institut_id, Name
                  FROM Institute
                  WHERE fakultaets_id = ? AND institut_id != fakultaets_id
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);
        
        foreach ($faks as $index => $fak) {
            if ($fak['is_fak']) {
                $statement->execute([$fak['Institut_id']]);
                $faks[$index]['institutes'] = $statement->fetchAll(PDO::FETCH_ASSOC);
                $statement->closeCursor();
            }
        }
        
        $this->domains = $domains;
        $this->faks    = $faks;
        $this->perms   = $perm;
    }
    
    /**
     * Migrate 2 users to 1 account. This is a part of the old numit-plugin
     */
    function migrate_action($user_id = null)
    {
        //check submitted form
        if (Request::submitted('umwandeln')) {
            $old_id = Request::option('old_id');
            $new_id = Request::option('new_id');
            
            //check existing users
            if (User::exists($old_id) && User::exists($new_id)) {
                $identity = Request:: get('convert_ident');
                $details  = UserModel::convert($old_id, $new_id, $identity);
                
                //delete old user
                if (Request::get('delete_old')) {
                    //no messaging
                    $dev_null       = new blackhole_message_class();
                    $default_mailer = StudipMail::getDefaultTransporter();
                    StudipMail::setDefaultTransporter($dev_null);
                    
                    //preparing delete
                    $umanager = new UserManagement();
                    $umanager->getFromDatabase($old_id);
                    
                    //delete
                    $umanager->deleteUser();
                    $details = array_merge($details, explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($umanager->msg, 0, -1))));
                    
                    //reactivate messaging
                    StudipMail::setDefaultTransporter($default_mailer);
                }
                
                PageLayout::postSuccess(_('Die Personen wurden migriert.'), $details);
                $this->redirect('admin/user/edit/' . $new_id);
            } else {
                PageLayout::postError(_('Bitte wählen Sie zwei gültige Personen aus.'));
            }
        }
        $this->user = $user_id ? User::find($user_id) : null;
    }
    
    /**
     * Set the password of an user to a new random password, without security-query
     *
     * @param md5 $user_id
     */
    public function change_password_action($user_id)
    {
        // mail address did not change, so skip this check
        $GLOBALS['MAIL_VALIDATE_BOX'] = false;
        $UserManagement               = new UserManagement($user_id);
        if ($UserManagement->setPassword()) {
            PageLayout::postSuccess(_('Das Passwort wurde neu gesetzt.'));
        } else {
            $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', substr($UserManagement->msg, 0, -1)));
            PageLayout::postError(_('Die Änderungen konnten nicht gespeichert werden.'), $details);
        }
        $this->redirect('admin/user/edit/' . $user_id);
    }
    
    /**
     * Unlock an user, without security-query
     *
     * @param md5 $user_id
     */
    public function unlock_action($user_id)
    {
        $user               = User::find($user_id);
        $user->locked       = 0;
        $user->lock_comment = null;
        $user->locked_by    = null;
        
        if ($user->store()) {
            PageLayout::postSuccess(_('Person wurde entsperrt.'));
        } else {
            PageLayout::postError(_('Person konnte nicht entsperrt werden.'));
        }
        $this->redirect('admin/user/edit/' . $user_id);
    }
    
    /**
     * Display institute informations of an user and save changes to it.
     *
     * @param md5 $user_id
     * @param md5 $institute_id
     */
    public function edit_institute_action($user_id, $institute_id)
    {
        if (Request::submitted('uebernehmen') && $GLOBALS['perm']->have_studip_perm("admin", $institute_id)) {
            //standard-values
            $values = [];
            foreach (words('inst_perms visible raum sprechzeiten Telefon Fax') as $param) {
                $values[$param] = Request::get(strtolower($param), '');
            }
            foreach (words('externdefault visible') as $param) {
                $values[$param] = Request::int($param, 0);
            }
            
            //change datafields
            $datafields = Request::getArray('datafields');
            foreach ($datafields as $id => $data) {
                $datafield = DataField::find($id);
                $entry     = DataFieldEntry::createDataFieldEntry($datafield, [$user_id, $institute_id]);
                $entry->setValueFromSubmit($data);
                if ($entry->isValid()) {
                    $entry->store();
                }
            }
            
            //store to database
            UserModel::setInstitute($user_id, $institute_id, $values);
            
            //output
            PageLayout::postSuccess(_('Die Einrichtungsdaten der Person wurden geändert.'));
            $this->redirect('admin/user/edit/' . $user_id);
        }
        
        $this->user       = UserModel::getUser($user_id, null, true);
        $this->institute  = UserModel::getInstitute($user_id, $institute_id);
        $about            = new about($this->user['username'], '');
        $this->perms      = $about->allowedInstitutePerms();
        $this->datafields = DataFieldEntry::getDataFieldEntries([$user_id, $institute_id], 'userinstrole');
    }
    
    /**
     * Delete an studycourse of an user , without a security-query
     *
     * @param md5 $user_id
     * @param md5 $fach_id
     * @param md5 $abschluss_id
     */
    public function delete_studycourse_action($user_id, $fach_id, $abschlus_id)
    {
        $user_stc = UserStudyCourse::find([$user_id, $fach_id, $abschlus_id]);
        $deleted  = false;
        if ($user_stc) {
            $deleted = $user_stc->delete();
        }
        if ($deleted) {
            PageLayout::postMessage(MessageBox::success(_('Die Zuordnung zum Studiengang wurde gelöscht.')));
        } else {
            PageLayout::postMessage(MessageBox::error(_('Die Zuordnung zum Studiengang konnte nicht gelöscht werden.')));
        }
        $this->redirect('admin/user/edit/' . $user_id);
    }
    
    /**
     * Delete an institute of an user , without a security-query
     *
     * @param md5 $user_id
     * @param md5 $institut_id
     */
    public function delete_institute_action($user_id, $institut_id)
    {
        if ($GLOBALS['perm']->have_studip_perm("admin", $institut_id)) {
            $groups     = GetAllStatusgruppen($institut_id);
            $group_list = GetRoleNames($groups, 0, '', true);
            if (is_array($group_list) && count($group_list) > 0) {
                $query
                           = "DELETE FROM statusgruppe_user
                          WHERE statusgruppe_id IN (?) AND user_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([array_keys($group_list), $user_id]);
            }
            
            $db = DBManager::get()->prepare("DELETE FROM user_inst WHERE user_id = ? AND Institut_id = ?");
            $db->execute([$user_id, $institut_id]);
            if ($db->rowCount() == 1) {
                StudipLog::log('INST_USER_DEL', $institut_id, $user_id);
                NotificationCenter::postNotification('UserInstitutionDidDelete', $institut_id, $user_id);
                checkExternDefaultForUser($user_id);
                if (UserConfig::get($user_id)->MY_INSTITUTES_DEFAULT == $institut_id) {
                    UserConfig::get($user_id)->delete('MY_INSTITUTES_DEFAULT');
                }
                PageLayout::postSuccess(_('Die Zuordnung zur Einrichtung wurde gelöscht.'));
            } else {
                PageLayout::postError(_('Die Zuordnung zur Einrichtung konnte nicht gelöscht werden.'));
            }
        } else {
            PageLayout::postError(_('Die Zuordnung zur Einrichtung konnte nicht gelöscht werden.'));
        }
        $this->redirect('admin/user/edit/' . $user_id);
    }
    
    /**
     * Delete an assignment of an user to an userdomain, without a security-query
     *
     * @param md5 $user_id
     */
    public function delete_userdomain_action($user_id)
    {
        $domain_id = Request::get('domain_id');
        $domain    = new UserDomain($domain_id);
        $domain->removeUser($user_id);
        $result = AutoInsert::instance()->saveUser($user_id);
        
        $details = [];
        
        foreach ($result['added'] as $item) {
            $details[] = sprintf(_('Das automatische Eintragen in die Veranstaltung <em>%s</em> wurde durchgeführt.'), $item);
        }
        foreach ($result['removed'] as $item) {
            $details[] = sprintf(_('Das automatische Austragen aus der Veranstaltung <em>%s</em> wurde durchgeführt.'), $item);
        }
        
        PageLayout::postSuccess(_('Die Zuordnung zur Nutzerdomäne wurde erfolgreich gelöscht.'), $details);
        $this->redirect('admin/user/edit/' . $user_id);
    }
    
    /**
     * Reset notfication for user
     * @param $user_id
     */
    public function reset_notification_action($user_id)
    {
        $resetted = DBManager::get()->execute("UPDATE seminar_user SET notification=0 WHERE user_id=?", [$user_id]);
        PageLayout::postSuccess(sprintf(_('Die Benachrichtigungseinstellungen für %s Veranstaltungen wurden zurück gesetzt.'), $resetted));
        $this->redirect('admin/user/edit/' . $user_id);
    }
    
    /**
     * Show user activities
     * @param $user_id
     * @throws Exception
     */
    public function activities_action($user_id)
    {
        $this->user     = User::find($user_id);
        $this->fullname = $this->user->getFullname();
        $this->user     = $this->user->toArray();
        
        if (is_null($this->user)) {
            throw new Exception(_('Nutzer nicht gefunden'));
        }
        PageLayout::setTitle(sprintf(_('Datei- und Aktivitätsübersicht für %s'), $this->fullname));
        
        
        $this->queries = $this->getActivities($user_id);
        
        $memberships = DBManager::get()->fetchAll("SELECT seminar_user.*, seminare.Name as course_name
                             FROM seminar_user
                             LEFT JOIN seminare USING (seminar_id)
                             WHERE user_id = ? ORDER BY seminare.start_time DESC, seminare.Name",
            [$user_id],
            'CourseMember::buildExisting');
        
        $courses        = [];
        $course_files   = [];
        $closed_courses = [];
        $this->sections = [];
        
        foreach ($memberships as $membership) {
            if (!Request::get('view') || Request::get('view') === 'files') {
                // count files for course
                $count = StudipDocument::countBySql('user_id = ? AND seminar_id =?', [$user_id, $membership->seminar_id]);
                
                if ($count) {
                    if (!isset($course_files[$membership->seminar_id])) {
                        $course_files[$membership->course->start_semester->name][$membership->course->id]['course'] = $membership->course;
                    }
                    $course_files[$membership->course->start_semester->name][$membership->course->id]['files'] = $count;
                }
            }
            if (in_array(Request::get('view'), words('courses closed_courses'))) {
                // check for closed courses
                $closed_course
                    = $closed_course = DBManager::get()->fetchColumn('SELECT COUNT(sc.seminar_id) FROM seminar_courseset sc
                  INNER JOIN courseset_rule cr ON cr.set_id=sc.set_id AND cr.type="ParticipantRestrictedAdmission"
                  WHERE sc.seminar_id =?', [$membership->seminar_id]);
                
                if ((int)$closed_course) {
                    $closed_courses[$membership->course->start_semester->name][$membership->course->id] = $membership;
                } else {
                    $courses[$membership->course->start_semester->name][$membership->course->id] = $membership;
                }
            }
        }
        
        if (!Request::get('view') || Request::get('view') === 'files') {
            $institutes = Institute::getMyInstitutes($user_id);
            if (!empty($institutes)) {
                foreach ($institutes as $index => $institute) {
                    $count = StudipDocument::countBySql('user_id = ? AND seminar_id =?', [$user_id, $institute['Institut_id']]);
                    
                    if ($count) {
                        $institutes[$index]['files'] = $count;
                    } else {
                        unset($institutes[$index]);
                    }
                }
            }
        }
        
        if (Request::get('view') == 'seminar_wait') {
            // waiting list
            $seminar_wait = AdmissionApplication::findByUser($user_id);
        }
        
        if (Request::get('view') == 'priorities') {
            // priorities
            $priorities = DBManager::get()->fetchAll('SELECT * FROM `priorities` WHERE `user_id` = ?', [$user_id]);
        }
        
        if (!empty($course_files)) {
            $this->sections['course_files'] = $course_files;
        }
        if (!empty($institutes)) {
            $this->sections['institutes'] = $institutes;
        }
        if (!empty($courses)) {
            
            $this->sections['courses'] = $courses;
        }
        if (!empty($courses)) {
            $this->sections['closed_courses'] = $closed_courses;
        }
        
        if (count($seminar_wait)) {
            $this->sections['seminar_wait'] = $seminar_wait;
        }
        
        if (!empty($priorities)) {
            $this->sections['priorities'] = $priorities;
        }
    }
    
    /**
     * List files for course or institute
     * @param $user_id
     * @param $course_id
     */
    public function list_files_action($user_id, $range_id)
    {
        $this->user  = User::find($user_id);
        $this->files = StudipDocument::findBySQL('user_id = ? AND seminar_id = ? ORDER BY name', [$user_id, $range_id]);
        
        $this->range = Course::find($range_id);
        if (is_null($this->range)) {
            $this->range = Institute::find($range_id);
        }
        PageLayout::setTitle(sprintf(_('Dateiübersicht für %s'), $this->range->getFullname()));
    }
    
    /**
     * Show file details
     * @param $file_id
     */
    public function file_details_action($file_id)
    {
        $file        = StudipDocument::find($file_id);
        $this->files = [$file];
        PageLayout::setTitle(sprintf(_('Detail für %s'), $file->name));
        $this->render_template('admin/user/list_files');
    }
    
    private function getActivities($user_id)
    {
        $queries[] = [
            'desc'    => _('Eingetragen in Veranstaltungen (dozent / tutor / autor / user)'),
            'query'   => "SELECT CONCAT_WS(' / ', SUM(status = 'dozent'), SUM(status = 'tutor'),
                                          SUM(status = 'autor'), SUM(status = 'user'))
                  FROM seminar_user
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "courses",
        ];
        $queries[] = [
            'desc'    => _('Eingetragen in geschlossenen Veranstaltungen (dozent / tutor / autor / user)'),
            'query'   => "SELECT CONCAT_WS(' / ', SUM(su.status = 'dozent'), SUM(su.status = 'tutor'),
                                          SUM(su.status = 'autor'), SUM(su.status = 'user'))
                  FROM seminar_user AS su
                  INNER JOIN seminar_courseset sc USING (seminar_id)
                  INNER JOIN courseset_rule cr ON cr.set_id=sc.set_id AND cr.type='ParticipantRestrictedAdmission'
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "closed_courses",
        ];
        $queries[] = [
            'desc'    => _("Eingetragen in Wartelisten (wartend / vorläufig akzeptiert)"),
            'query'   => "SELECT CONCAT_WS(' / ', SUM(status = 'awaiting'), SUM(status = 'accepted'))
                  FROM admission_seminar_user
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "seminar_wait",
        ];
        $queries[] = [
            'desc'    => _("Eingetragen in Anmeldelisten"),
            'query'   => "SELECT COUNT(*)
                  FROM priorities
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "priorities",
        ];
        $queries[] = [
            'desc'  => _("Eingetragen in Einrichtungen (admin / dozent / tutor / autor)"),
            'query' => "SELECT CONCAT_WS(' / ', SUM(inst_perms = 'admin'), SUM(inst_perms = 'dozent'),
                                          SUM(inst_perms = 'tutor'), SUM(inst_perms = 'autor'))
                  FROM user_inst
                  WHERE user_id = ?
                  GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Ankündigungen"),
            'query' => "SELECT COUNT(*) FROM news WHERE user_id = ? GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Wikiseiten"),
            'query' => "SELECT COUNT(*) FROM wiki WHERE user_id = ? GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Umfragen"),
            'query' => "SELECT COUNT(*) FROM questionnaires WHERE user_id = ? GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Evaluationen"),
            'query' => "SELECT COUNT(*) FROM eval WHERE author_id = ? GROUP BY author_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Literatureinträge"),
            'query' => "SELECT COUNT(*) FROM lit_catalog WHERE user_id = ? GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Ressourcenobjekte"),
            'query' => "SELECT COUNT(*) FROM resources_objects WHERE owner_id = ? GROUP BY owner_id",
        ];
        $queries[] = [
            'desc'    => _("Anzahl der Dateien (hochgeladen / verlinkt)"),
            'query'   => "SELECT CONCAT_WS(' / ', COUNT(*) - COUNT(NULLIF(url,'')), COUNT(NULLIF(url,'')))
                  FROM dokumente
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "files",
        ];
        $queries[] = [
            'desc'    => _("Gesamtgröße der hochgeladenen Dateien (MB)"),
            'query'   => "SELECT FORMAT(SUM(filesize)/1024/1024,2)
                  FROM dokumente
                  WHERE user_id = ? AND (url IS NULL OR url = '')
                  GROUP BY user_id",
            'details' => "files",
        ];
        
        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $table     = $plugin->getEntryTableInfo();
            $queries[] = [
                'desc'  => $plugin->getPluginName() . ' - ' . _("Anzahl der Postings"),
                'query' => 'SELECT COUNT(*) FROM `' . $table['table'] . '`
            WHERE `' . $table['user_id'] . '` = ?
            GROUP BY `' . $table['user_id'] . '`',
            ];
        }
        
        // Evaluate queries
        foreach ($queries as $index => $query) {
            $statement = DBManager::get()->prepare($query['query']);
            $statement->execute([$user_id]);
            $queries[$index]['value'] = $statement->fetchColumn() ?: 0;
        }
        
        return $queries;
    }
    
    
    /**
     * Download documents
     * @param $user_id
     * @param string $course_id
     */
    public function download_user_files_action($user_id, $course_id = '')
    {
        $query      = "SELECT dokument_id FROM dokumente WHERE user_id = ?";
        $parameters = [$user_id];
        
        if ($course_id !== '') {
            $query .= " AND seminar_id = ?";
            $parameters[] = $course_id;
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        
        $download_ids = $statement->fetchAll(PDO::FETCH_COLUMN);
        $zip_file_id  = createSelectedZip($download_ids, false);
        
        $user     = User::find($user_id);
        $filename = prepareFilename($user->username . '-' . _("Dokumente") . '.zip');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Location: ' . getDownloadLink($zip_file_id, $filename, 4));
        header('Pragma: public');
        
        $this->render_nothing();
        
    }
    
    /**
     * Init sidebar
     */
    public function addSidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->setImage('sidebar/person-sidebar.png');
        
        $actions = new ActionsWidget();
        
        if (in_array('Standard', $GLOBALS['STUDIP_AUTH_PLUGIN'])) {
            $actions->addLink(_('Neues Konto anlegen'),
                $this->url_for('admin/user/new'),
                Icon::create('person+add', 'clickable'))
                    ->asDialog();
        }
        $actions->addLink(_('Vorläufiges Konto anlegen'),
            $this->url_for('admin/user/new/prelim'),
            Icon::create('date+add', 'clickable'))
                ->asDialog();
        $actions->addLink(_('Konten zusammenführen'),
            $this->url_for('admin/user/migrate/' . (($this->user && is_array($this->user)) ? $this->user['user_id'] : '')),
            Icon::create('persons+new', 'clickable'));
        
        $search = new SearchWidget();
        $search->addNeedle(_('Person suchen'),
            'user_id',
            true,
            new StandardSearch('user_id'),
            'function (value) { document.location = STUDIP.URLHelper.getURL("dispatch.php/admin/user/edit/" + value); }');
        
        $sidebar->addWidget($actions);
        $sidebar->addWidget($search);
        
        if ($this->action === 'index' && count($this->users) > 0) {
            $export = new ExportWidget();
            $export->addLink(_('Suchergebnis exportieren'),
                $this->url_for('admin/user?export=1'),
                Icon::create('persons+move_right', 'clickable'));
            $sidebar->addWidget($export);
        }
        
        if (!$this->user || !is_array($this->user)) {
            return;
        }
        
        $user_actions = new ActionsWidget();
        $user_actions->setTitle(sprintf(_('Aktionen für "%s"'), $this->user['username']));
        
        $user_actions->addLink(_('Nachricht an Person verschicken'),
            URLHelper::getLink('dispatch.php/messages/write?rec_uname=' . $this->user['username']),
            Icon::create('mail', 'clickable'))
                     ->asDialog();
        
        if ($this->user['locked']) {
            $user_actions->addLink(_('Personenaccount entsperren'),
                $this->url_for('admin/user/unlock/' . $this->user['user_id']),
                Icon::create('lock-unlocked', 'clickable'));
        }
        if ($this->user['auth_plugin'] !== 'preliminary' && ($GLOBALS['perm']->have_perm('root') || $GLOBALS['perm']->is_fak_admin() || !in_array($this->user['perms'], words('root admin')))) {
            if (!StudipAuthAbstract::CheckField('auth_user_md5.password', $this->user['auth_plugin'])) {
                $user_actions->addLink(_('Neues Passwort setzen'),
                    $this->url_for('admin/user/change_password/' . $this->user['user_id']),
                    Icon::create('key', 'clickable'));
            }
            $user_actions->addLink(_('Person löschen'),
                $this->url_for('admin/user/delete/' . $this->user['user_id'] . '/edit'),
                Icon::create('trash', 'clickable'));
        }
        if (Config::get()->MAIL_NOTIFICATION_ENABLE && CourseMember::findOneBySQL("user_id = ? AND notification <> 0", [$this->user['user_id']])) {
            $user_actions->addLink(_('Benachrichtigungen zurücksetzen'),
                $this->url_for('admin/user/reset_notification/' . $this->user['user_id']),
                Icon::create('refresh', 'clickable'));
        }
        
        if ($this->action == 'activities') {
            $user_actions->addLink(_('Alle Dateien als ZIP herunterladen'),
                $this->url_for('admin/user/download_user_files/' . $this->user['user_id']),
                Icon::create('folder-full', 'clickable'));
        }
        
        $sidebar->insertWidget($user_actions, 'actions', 'user_actions');
        
        $views = new ViewsWidget();
        $views->addLink(_('Zurück zur Übersicht'),
            $this->url_for('admin/user'))
              ->setActive(false);
        $views->addLink(_('Person verwalten'),
            $this->url_for('admin/user/edit/' . $this->user['user_id']))
              ->setActive($this->action == 'edit');
        $views->addLink(_('Zum Profil'),
            URLHelper::getLink('dispatch.php/profile?username=' . $this->user['username']),
            Icon::create('person', 'clickable'));
        
        if ($GLOBALS['perm']->have_perm('root')) {
            $views->addLink(_('Datei- und Aktivitätsübersicht'),
                $this->url_for('admin/user/activities/' . $this->user['user_id']),
                Icon::create('vcard', 'clickable'))
                  ->setActive($this->action == 'activities');
            
            
            if (Config::get()->LOG_ENABLE) {
                $views->addLink(_('Personeneinträge im Log'),
                    URLHelper::getLink('dispatch.php/event_log/show?search=' . $this->user['username'] . '&type=user&object_id=' . $this->user['user_id']),
                    Icon::create('log', 'clickable'));
            }
        }
        $sidebar->insertWidget($views, 'user_actions', 'views');
    }
}
