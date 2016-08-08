<?php
# Lifter010: TODO
/**
 * studycourse.php - controller class for the studycourses
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
 * @package     studycourses
 * @since       2.0
 */

require_once 'app/models/studycourse.php';

/**
 *
 *
 */
class Admin_StudycourseController extends AuthenticatedController
{
    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;

        parent::before_filter($action, $args);

        // user must have root permission
        $perm->check('root');

        // set navigation
        Navigation::activateItem('/admin/locations/studycourse');
    }

    /**
     * Maintenance view for profession with the degrees
     */
    public function profession_action()
    {
        //set title
        PageLayout::setTitle(_('Verwaltung der Studiengänge'));

        //get data
        $this->studycourses = Fach::findBySQL('1 ORDER BY name');
        
        $this->infobox = $this->setSidebar();

        //sorting
        if(Request::get('sortby') == 'users') {
            usort($this->studycourses, array('Admin_StudycourseController', 'sortByUsers'));
        }
    }

    /**
     * Maintenance view for degrees with the professions
     */
    public function degree_action()
    {
        // set title
        PageLayout::setTitle(_('Gruppierung von Studienabschlüssen'));

        //get data
        $this->studydegrees = Abschluss::findBySQL('1 ORDER BY name');
        $this->infobox = $this->setSidebar();

        //sorting
        if(Request::get('sortby') == 'users') {
            usort($this->studydegrees, array('Admin_StudycourseController', 'sortByUsers'));
        }
    }

    /**
     * Edit the selected profession
     * @param $prof_id
     */
    public function edit_profession_action($prof_id)
    {
        $this->edit = Fach::find($prof_id);
        if (!$this->edit) {
            $this->flash['error'] = _('Unbekanntes Fach!');
            $this->redirect('admin/studycourse/profession');
        }
        //save changes
        if (Request::submitted('uebernehmen')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($this->edit && trim(Request::get('professionname'))) {
                $this->edit->name = trim(Request::get('professionname'));
                $this->edit->beschreibung = trim(Request::get('description'));
                $this->edit->store();
                $this->flash['success'] = sprintf(_('Das Studienfach "%s" wurde erfolgreich aktualisiert!'), htmlReady($this->edit->name));
                $this->flash['success_detail'] = array(_('Beschreibung: ') . htmlReady($this->edit->beschreibung));
                $this->redirect('admin/studycourse/profession');
            } else {
                $this->flash['error'] = _('Bitte geben Sie mindestens einen Namen für das Fach ein!');
            }
        }

        PageLayout::setTitle(_('Fach bearbeiten'));
        $this->infobox = $this->setSidebar();
    }

    /**
     * Edit the selected degree
     * @param $deg_id
     */
    public function edit_degree_action($deg_id)
    {
        $this->edit = Abschluss::find($deg_id);
        if (!$this->edit) {
            $this->flash['error'] = _('Unbekannter Abschluss!');
            $this->redirect('admin/studycourse/degree');
        }
        if (Request::submitted('uebernehmen')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($this->edit && trim(Request::get('degreename'))) {
                $this->edit->name = trim(Request::get('degreename'));
                $this->edit->beschreibung = trim(Request::get('description'));
                $this->edit->store();
                $this->flash['success'] = sprintf(_('Der Abschluss "%s" wurde erfolgreich aktualisiert!'), htmlReady($this->edit->name));
                $this->flash['success_detail'] = array(_('Beschreibung: ') . htmlReady($this->edit->beschreibung));
                $this->redirect('admin/studycourse/degree');
            } else {
                $this->flash['error'] = _('Bitte geben Sie mindestens einen Namen für den Abschluss ein!');
            }
        }

        PageLayout::setTitle(_('Abschluss bearbeiten'));
        $this->infobox = $this->setSidebar();
    }

    /**
     * Delete a profession
     * Only if count_user=0
     *
     * @param string $prof_id
     */
    function delete_profession_action($prof_id)
    {
        $profession = Fach::find($prof_id);
        if (!$profession) {
            $this->flash['error'] = _('Unbekannter Abschluss!');
            $this->redirect('admin/studycourse/profession');
        } else {
            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                //Check ob studiengang leer ist
                if ($profession->count_user == 0) {
                    if ($profession->delete()) {
                        $this->flash['success'] = _("Der Studiengang wurde erfolgreich gelöscht!");
                    } else {
                        $this->flash['error'] = _("Interner Fehler im Löschvorgang! Bitte probieren Sie es erneut.");
                    }
                } else {
                    $this->flash['error']=_("Zu löschende Studiengänge müssen leer sein!");
                }
            } elseif (!Request::get('back')) {
                $this->flash['delete'] = array('name' => $profession->name, 'studiengang_id' => $profession->id);
            }
            $this->redirect('admin/studycourse/profession');
        }
    }

    /**
     * Delete a degree
     * Only if count_user = 0
     *
     * @param string $deg_id
     */
    function delete_degree_action($deg_id)
    {
        $degree = Abschluss::find($deg_id);
        if (!$degree) {
            $this->flash['error'] = _('Unbekannter Abschluss!');
            $this->redirect('admin/studycourse/degree');
        } else {
            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                //Check ob Abschluss leer ist
                if ($degree->count_user == 0) {
                    if ($degree->delete()) {
                        $this->flash['success'] = _('Der Abschluss wurde erfolgreich gelöscht!');
                    } else {
                        $this->flash['error'] = _('Interner Fehler im Löschvorgang! Bitte probieren Sie es erneut.');
                    }
                } else {
                    $this->flash['error'] = _('Zu löschende Abschlüsse müssen leer sein!');
                }
            } elseif (!Request::isPost()) {
                $this->flash['delete'] = array('name' => $degree->name, 'abschluss_id' => $degree->id);
            }
            $this->redirect('admin/studycourse/degree');
        }
    }

    /**
     * Create a new profession
     */
    function newprofession_action()
    {
        if (Request::submitted('anlegen')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->prof_name = trim(Request::get('professionname'));
            $this->prof_desc = trim(Request::get('description'));
            if ($this->prof_name) {
                $prof_exists = Fach::findOneBySQL('name = ?', [$this->prof_name]);
                if ($prof_exists) {
                    $this->flash['error'] = sprintf(_('Ein Studienfach mit dem Namen "%s" existiert bereits!'), htmlReady($this->prof_name));
                } else {
                    $profession = new Fach();
                    $profession->name = $this->prof_name;
                    $profession->beschreibung = $this->prof_desc;
                    $profession->store();
                    $this->flash['success'] = sprintf(_('Das Studienfach "%s" wurde erfolgreich angelegt!'), htmlReady($this->prof_name));
                    $this->redirect('admin/studycourse/profession');
                }
            } else {
                $this->flash['error'] = _('Bitte geben Sie mindestens einen Namen für das Studienfach ein!');
            }
        }

        PageLayout::setTitle(_('Neues Studienfach anlegen'));
        $this->infobox = $this->setSidebar();
    }

    /**
     * Create a new degree
     */
    function newdegree_action()
    {
        if (Request::submitted('anlegen')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->degree_name = trim(Request::get('degreename'));
            $this->degree_desc = trim(Request::get('description'));
            if ($this->degree_name) {
                $degree_exists = Abschluss::findOneBySQL('name = ?', [$this->degree_name]);
                if ($degree_exists) {
                    $this->flash['error'] = sprintf(_('Ein Studienabschluss mit dem Namen "%s" existiert bereits!'), htmlReady($this->degree_name));
                } else {
                    $degree = new Abschluss();
                    $degree->name = $this->degree_name;
                    $degree->beschreibung = $this->degree_desc;
                    $degree->store();
                    $this->flash['success'] = sprintf(_('Der Studienabschluss "%s" wurde erfolgreich angelegt!'), htmlReady($this->degree_name));
                    $this->redirect('admin/studycourse/degree');
                }
            } else {
                $this->flash['error'] = _("Bitte geben Sie mindestens einen Namen für den Abschluss ein!");
            }
        }

        PageLayout::setTitle(_('Anlegen von Studienabschlüssen'));
        $this->infobox = $this->setSidebar();
    }

    /**
     * Create the messagebox
     */
    private function setSidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->setTitle(_('Studiengänge'));
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $links = new ActionsWidget();
        $links->addLink(_('Gruppierung nach Fächer'), $this->url_for('/profession'), Icon::create('visibility-visible', 'clickable'));
        $links->addLink(_('Gruppierung nach Abschlüssen'), $this->url_for('/degree'), Icon::create('visibility-visible', 'clickable'));
        $links->addLink(_('Neues Studienfach anlegen'), $this->url_for('/newprofession'), Icon::create('add', 'clickable'));
        $links->addLink(_('Neuen Studienabschluss anlegen'), $this->url_for('/newdegree'), Icon::create('add', 'clickable'));
        $sidebar->addWidget($links);
    }

    private static function sortByUsers($a, $b)
    {
        if ($a->count_user == $b->count_user) {
            return 0;
        }
        return ($a->count_user > $b->count_user) ? -1 : 1;
    }
}
