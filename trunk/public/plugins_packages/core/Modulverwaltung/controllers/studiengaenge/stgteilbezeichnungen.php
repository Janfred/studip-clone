<?php
/**
 * stgteilbezeichnungen.php - Studiengaenge_StgteilbezeichnungenController
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

class Studiengaenge_StgteilbezeichnungenController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/studiengaenge/stgteilbezeichnungen');
        $this->action = $action;

        if (Request::isXhr()) {
            $this->response->add_header('Content-Type',
                    'text/html; charset=WINDOWS-1252');
            $this->set_layout(null);
        }
    }

    public function index_action()
    {
        $this->stgteilbezeichnungen = StgteilBezeichnung::getAllEnriched();
        PageLayout::setTitle(_('Alle Studiengangteil-Bezeichnungen'));
        $this->setSidebar();
    }

    /**
     * Creates a new Studiengangteil-Bezeichnung
     */
    function stgteilbezeichnung_action($bezeichnung_id = null)
    {
        $this->stgteilbezeichnung = StgteilBezeichnung::get($bezeichnung_id);
        if ($this->stgteilbezeichnung->isNew()) {
            PageLayout::setTitle(_('Neue Studiengangteil-Bezeichnung anlegen'));
            $success_message = ('Die Studiengangteil-Bezeichnung "%s" wurde angelegt.');
        } else {
            $this->bezeichnung_id = $this->stgteilbezeichnung->getId();
            PageLayout::setTitle(_('Studiengangteil-Bezeichnung bearbeiten'));
            $success_message = _('Die Studiengangteil-Bezeichnung "%s" wurde geändert.');
        }
        if (Request::submitted('store')) {
            $stored = false;
            $this->stgteilbezeichnung->name = trim(Request::get('name'));
            $this->stgteilbezeichnung->name_en = trim(Request::get('name_en'));
            $this->stgteilbezeichnung->name_kurz = trim(Request::get('name_kurz'));
            $this->stgteilbezeichnung->name_kurz_en = trim(Request::get('name_kurz_en'));
            try {
                $stored = $this->stgteilbezeichnung->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message,
                            htmlReady($this->stgteilbezeichnung->name)));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->relocate($this->url_for('/index'));
            }
        }
        $this->cancel_url = $this->url_for('/index');

        $this->setSidebar();
        if (!$this->stgteilbezeichnung->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink( _('Log-Einträge dieser Studiengangteil-Bezeichnung'),
                    $this->url_for('shared/log_event/show/' . $this->stgteilbezeichnung->id),
                    Icon::create('log', 'clickable'), array('data-dialog' => ''));
        }

    }

    /**
     * Deletes a Abschluss-Kategorie
     */
    function delete_action($stgteilbezeichnung_id, $delete = null)
    {
        $stgteilbezeichnung = StgteilBezeichnung::get($stgteilbezeichnung_id);
        if ($stgteilbezeichnung->isNew()) {
             PageLayout::postError( _('Unbekannte Studiengangteil-Bezeichnung'));
        } else {
            if (!is_null($delete)) {
                if ($stgteilbezeichnung->count_studiengaenge) {
                    PageLayout::postError( sprintf(_('Löschen nicht möglich! Die Studiengangteil-Bezeichnung "%s" wird bereits verwendet!'),
                            htmlReady($stgteilbezeichnung->name)));
                } else {
                    PageLayout::postSuccess(sprintf(_('Studiengangteil-Bezeichnung "%s" gelöscht!'),
                            htmlReady($stgteilbezeichnung->name)));
                    $stgteilbezeichnung->delete();
                }
            } else {
                $this->flash_set('dialog', sprintf(_('Wollen Sie wirklich die Studiengangteil-Bezeichnung "%s" löschen?'),
                                $stgteilbezeichnung->name),
                        '/delete', $stgteilbezeichnung->getId() . '/1',
                        'studiengaenge/stgteilbezeichnungen');
            }
        }
        $this->redirect('studiengaenge/stgteilbezeichnungen');
    }

    /**
     * sorts Teilstudiengaenge
     */
    public function sort_action()
    {
        $orderedIds = Request::getArray('newOrder');
        $stgteilbezeichnungen = SimpleORMapCollection::createFromArray(
                StgteilBezeichnung::getAll());
        if (is_array($orderedIds)) {
            $i = 1;
            foreach ($orderedIds as $id) {
                $stgteilbezeichnung = $stgteilbezeichnungen->find($id);
                if ($stgteilbezeichnung) {
                    if ($stgteilbezeichnung->position != $i) {
                        $stgteilbezeichnung->position = $i;
                        $stgteilbezeichnung->store();
                    }
                    $i++;
                }
            }
        }
        $this->set_status(200);
        $this->render_nothing();
    }

    public function details_action($bezeichnung_id)
    {
        $this->stgteilbezeichnung = StgteilBezeichnung::get($bezeichnung_id);
        $this->bezeichnung_id = $this->stgteilbezeichnung->getId();
        if (!Request::isXhr()){
            $this->perform_relayed('stgteilbezeichnungen');
            return true;
        }
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));

        if (MvvPerm::havePermCreate('StgteilBezeichnung')) {
            $widget  = new ActionsWidget();
            $widget->addLink( _('Neue Studiengangteil-Bezeichnung anlegen'),
                            $this->url_for('/stgteilbezeichnung'),
                            Icon::create('file+add', 'clickable'));
            $sidebar->addWidget($widget);
        }
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_("Sie können die Reihenfolge der Studiengangteil-Bezeichnungen durch Ziehen der Zeilen ändern.").'</br>'));
        $widget->addElement(new WidgetElement(_("Eine Studiengangteil-Bezeichnung kann aufgeklappt werden, um Details anzuzeigen.")));
        $helpbar->addWidget($widget);

        $this->sidebar_rendered = true;
    }

}
