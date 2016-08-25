<?php
/**
 * faecher.php - controller class for Faecher
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

require_once dirname(__FILE__) . '/../MVV.class.php';

/**
 *
 *
 */
class Fachabschluss_FaecherController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // set navigation
        Navigation::activateItem($this->me . '/fachabschluss/faecher');
        $this->action = $action;
        if (Request::isXhr()) {
            $this->response->add_header('Content-Type',
                    'text/html; charset=WINDOWS-1252');
            $this->set_layout(null);
        }
    }

    /**
     * Shows all Faecher
     */
    public function index_action()
    {
        //set title
        PageLayout::setTitle(_('Verwaltung der Fächer'));

        $this->initPageParams();

        // Nur Fächer mit verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $filter = array('mfi.institut_id' => MvvPerm::getOwnInstitutes());

        //get data
        $this->faecher = Fach::getAllEnriched($this->sortby, $this->order,
                self::$items_per_page,
                self::$items_per_page * ($this->page - 1), $filter);
        if (!isset($this->fach_id)) {
            $this->fach_id = null;
        }
        if (sizeof($this->faecher) == 0) {
            PageLayout::postInfo(_('Es wurden noch keine Fächer angelegt.'));
        }
        $this->count = Fach::getCount($filter);

        $this->setSidebar();

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten können Sie Fächer verwalten und neue Fächer anlegen.').'</br>'));
        $widget->addElement(new WidgetElement(_('Ein Fach kann aufgeklappt werden, um die Abschlüsse anzuzeigen, die dem Fach bereits zugeordnet wurden.')));
        $helpbar->addWidget($widget);
    }

    /**
     * Shows details of LV-Gruppe
     */
    public function details_action($fach_id = null)
    {
        $this->fach = Fach::get($fach_id);
        $this->fach_id = $this->fach->id;
        if (!Request::isXhr()) {
            $this->perform_relayed('index');
            return;
        }
    }

    /**
     * Edits the selected Fach
     */
    public function fach_action($fach_id = null)
    {
        $this->fach = Fach::get($fach_id);
        if ($this->fach->isNew()) {
            PageLayout::setTitle(_('Neues Fach anlegen'));
            $success_message = _('Das Fach "%s" wurde angelegt.');
        } else {
            PageLayout::setTitle(_('Fach bearbeiten'));
            $success_message = _('Das Fach "%s" wurde geändert.');
        }
        //save changes
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->fach->name = trim(Request::get('name'));
            $this->fach->name_en = trim(Request::get('name_en'));
            $this->fach->name_kurz = trim(Request::get('name_kurz'));
            $this->fach->name_kurz_en = trim(Request::get('name_kurz_en'));
            $this->fach->beschreibung = trim(Request::get('beschreibung'));
            $this->fach->beschreibung_en = trim(Request::get('beschreibung_en'));
            $this->fach->schlagworte = trim(Request::get('schlagworte'));
            $this->fach->assignFachbereiche(Request::optionArray('institut_items'));
            try {
                $stored = $this->fach->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                $this->sessSet('sortby', 'chdate');
                $this->sessSet('order', 'DESC');
                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message, htmlReady($this->fach->name)));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/index'));
                return;
            }
        }

        // Einrichtung
        $search = new StandardSearch('Institut_id');
        $this->qs_search_id = md5(serialize($search));
        $this->search_institutes_id = md5(serialize($search));
        $this->search_institutes = QuickSearch::get('institut_id',
                new StandardSearch('Institut_id'))
                    ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                    ->noSelectbox();
        
        if (!$this->fach->isNew()) {
            $this->setSidebar();
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(_('Log-Einträge dieses Faches'),
                    $this->url_for('shared/log_event/show', $this->fach->getId()),
                    Icon::create('log', 'clickable'))->asDialog();
        }
    }

    /**
     * Deletes a Fach
     */
    public function delete_action($fach_id)
    {
        $fach = Fach::get($fach_id);
        if (Request::submitted('yes')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($fach->isNew()) {
                PageLayout::postError( _('Das Fach kann nicht gelöscht werden (unbekanntes Fach).'));
            } else {
                $name = $fach->name;
                $fach->delete();
                PageLayout::postSuccess(sprintf(_('Das Fach "%s" wurde gelöscht.'), htmlReady($name)));
            }
        }
        if (!Request::isPost()) {
            $this->flash_set('dialog', sprintf(_('Wollen Sie wirklich das Fach "%s" löschen?'),
                            $fach->name),
                    '/delete/' . $fach->id,
                    '/index');
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * Shows all Faecher grouped by Fachbereich
     */
    public function fachbereiche_action()
    {
        $filter = array('mfi.institut_id' => MvvPerm::getOwnInstitutes());

        $this->initPageParams('fachbereiche');

        $this->fachbereiche = Fach::getAllFachbereiche(
                $this->sortby, $this->order, $filter);
        PageLayout::setTitle(_('Fächer nach Fachbereichen gruppiert'));

        $this->setSidebar();

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Ein Fachbereich kann aufgeklappt werden, um die Fächer anzuzeigen, die dem Fachbereich bereits zugeordnet wurden.')));
        $helpbar->addWidget($widget);
    }

    /**
     * Shows all Faecher of a selected Fachbereich
     */
    public function details_fachbereich_action($fachbereich_id = null)
    {
        $this->faecher = Fach::findByFachbereich($fachbereich_id);
        $this->fachbereich_id = $fachbereich_id;
        if (!Request::isXhr()) {
            $this->perform_relayed('fachbereiche');
            return;
        }
    }

    public function abschluss_action($abschluss_id = null)
    {
        $response = $this->relay('fachabschluss/abschluesse/abschluss/' . $abschluss_id);
        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            if ($response->headers['Location']) {
                $this->redirect($response->headers['Location']);
            } else {
              $this->relocate('fachabschluss/abschluesse/abschluss', $abschluss_id);
            }
        }
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');

        $widget  = new ViewsWidget();
        $widget->addLink( _('Fächer mit zugeordneten Abschlüssen'),
                $this->url_for('/index'))->setActive(
                                in_array($this->action, words('index details')));
        $widget->addLink( _('Gruppierung nach Fachbereichen'),
                $this->url_for('/fachbereiche'))->setActive(
                                in_array($this->action,
                                    words('fachbereiche details_fachbereich')));

        $sidebar->addWidget($widget);

        if (MvvPerm::havePermCreate('Fach')) {
            $widget  = new ActionsWidget();
            $widget->addLink( _('Neues Fach anlegen'),
                            $this->url_for('/fach'),
                            Icon::create('file+add', 'clickable'));
            $sidebar->addWidget($widget);
        }
        $this->sidebar_rendered = true;
    }
}
