<?php
/**
 * dokumente.php - controller class for Dokumente
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

class Materialien_DokumenteController extends MVVController
{

    public $filter = array();
    private $show_sidebar_search = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

      //  $this->flashRestore();
        $this->filter = $this->sessGet('filter', array());

        // set navigation
        Navigation::activateItem($this->me . '/materialien/dokumente');
        $this->action = $action;

        if (Request::isXhr()) {
            $this->response->add_header('Content-Type',
                    'text/html; charset=WINDOWS-1252');
            $this->set_layout(null);
        }

    }

    public function index_action()
    {
        PageLayout::setTitle(_('Verwaltung der Dokumente'));

        $this->initPageParams();

        $search_result = $this->getSearchResult('Dokument');

        $filter = array_merge(
                array('mvv_dokument.dokument_id' => $search_result),
                (array) $this->filter);
        $this->dokumente = Dokument::getAllEnriched($this->sortby, $this->order,
                self::$items_per_page,
                self::$items_per_page * ($this->page - 1),
                $filter);
        if (!empty($this->filter)) {
            $search_result = array_keys($this->dokumente);
        }

        if (!isset($this->dokument_id)) {
            $this->dokument_id = null;
        }
        if (!count($this->dokumente)) {
            PageLayout::postInfo(sprintf(
                    _('Es wurden noch keine Dokumente angelegt. Klicken Sie %shier%s, um ein neues Dokument anzulegen.'),
                    '<a href="'
                    . $this->url_for('/dokument') . '">',
                    '</a>'));
        }
        $this->count = Dokument::getCountFilter($filter);
        $this->show_sidebar_search = true;
        $this->setSidebar();
    }

    public function details_action($dokument_id = null)
    {
        $this->dokument = Dokument::find($dokument_id);
        if (!$this->dokument) {
            throw new Trails_Exception(404);
        }
        $this->dokument_id = $this->dokument->id;
        $this->relations = $this->dokument->getRelations();
        if (!Request::isXhr()) {
            $this->perform_relayed('index');
            return;
        }
    }

    /**
     * Edits the selected document
     */
    public function dokument_action($dokument_id = null)
    {
        $this->dokument = Dokument::get($dokument_id);
        if ($this->dokument->isNew()) {
            PageLayout::setTitle(_("Neues Dokument anlegen"));
            $success_message = _("Das Dokument <em>%s</em> wurde angelegt.");
        } else {
            PageLayout::setTitle(_('Dokument bearbeiten'));
            $success_message = _('Das Dokument "%s" wurde geändert.');
        }
        $success = false;
        //save changes
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->dokument->url = trim(Request::get('url'));
            $this->dokument->name = trim(Request::get('name'));
            $this->dokument->name_en = trim(Request::get('name_en'));
            $this->dokument->linktext = trim(Request::get('linktext'));
            $this->dokument->linktext_en = trim(Request::get('linktext_en'));
            $this->dokument->beschreibung = trim(Request::get('beschreibung'));
            $this->dokument->beschreibung_en = trim(Request::get('beschreibung_en'));
            try {
                $stored = $this->dokument->store();
            } catch (InvalidValuesException $e) {
                Pagelayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                $this->reset_search;
                $success = true;
                if (!Request::isXhr()) {
                    if ($stored) {
                        PageLayout::postSuccess(sprintf($success_message, htmlReady($this->dokument->name)));
                    } else {
                        PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                    }
                    $this->redirect($this->url_for('/index'));
                }
            }
        }
        $this->cancel_url = $this->url_for('/index');
        if (Request::isXhr()) {
            if ($success) {
                $ret = [
                        'func' => "MVV.Content.addItemFromDialog",
                        'payload' => [
                            'target' => 'dokumente',
                            'item_id' => $this->dokument->id,
                            'item_name' => $this->dokument->getDisplayName()
                        ]
                ];
                $this->response->add_header('X-Dialog-Close', 1);
                $this->response->add_header('X-Dialog-Execute', json_encode(studip_utf8encode($ret)));
                $this->render_nothing();
                return;
            }
        }

        $this->setSidebar();
        $sidebar = Sidebar::get();
        $action_widget = $sidebar->getWidget('actions');
        $action_widget->addLink( _('Log-Einträge dieses Dokumentes'),
                $this->url_for('shared/log_event/show', $this->dokument->id),
                Icon::create('log', 'clickable'), ['data-dialog' => '']);
    }

    /**
     * Deletes a document
     */
    function delete_action($dokument_id)
    {
        $dokument = Dokument::get($dokument_id);
        if (Request::submitted('yes')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($dokument->isNew()) {
                PageLayout::postError( _('Das Dokument kann nicht gelöscht werden (unbekanntes Dokument).'));
            } else {
                CSRFProtection::verifyUnsafeRequest();
                $name = $dokument->name;
                $dokument->delete();
                PageLayout::postSuccess(sprintf(_('Das Dokument "%s" wurde gelöscht.'), htmlReady($name)));
            }
        }
        if (!Request::isPost()) {
            $relations = $dokument->getCountRelations();
            if ($relations) {
                $this->flash_set('dialog', sprintf(_('Wollen Sie das Dokument "%s" wirklich löschen?')
                        . ' '
                        . ngettext('Dieses Dokument wird von einem Objekt referenziert.',
                                'Dieses Dokument wird von %s Objekten referenziert.', $relations),
                        $dokument->name, $relations),
                        '/delete/' . $dokument->id, '/index');
            } else {
                $this->flash_set('dialog', sprintf(_('Wollen Sie das Dokument "%s" wirklich löschen?'),
                                $dokument->name),
                        '/delete/' . $dokument->getId() . '/1',
                        '/index');
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * do the search
     */
    public function search_action()
    {
        $this->do_search('Dokument',
                trim(Request::get('dokument_suche_parameter')),
                Request::option('dokument_suche'));
        $this->perform_relayed('index');
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('Dokument');
        $this->filter = array();
        $this->sessRemove('filter');
        $this->perform_relayed('index');
    }

    /**
     * sets filter parameters and store these in the session
     */
    public function set_filter_action()
    {
        // filtered by object type (Zuordnungen)
        $this->filter['mvv_dokument_zuord.object_type']
                = strlen(Request::get('zuordnung_filter'))
                ? Request::option('zuordnung_filter') : null;
        // store filter
        $this->reset_search();
        $this->sessSet('filter', $this->filter);
        $this->perform_relayed('index');
    }

    public function reset_filter_action()
    {
        $this->filter = array();
        $this->reset_search();
        $this->sessRemove('filter');
        $this->perform_relayed('index');
    }

    public function ref_properties_action($dokument_id, $object_id, $object_type)
    {
        if (Request::isXhr()) {
            $this->dokument = Dokument::find($dokument_id);
            if ($this->dokument) {
                $this->relation = $this->dokument->getRelationByObject(
                        $object_id, $object_type);
            } else {
                $this->render_nothing();
            }
        } else {
            $this->render_nothing();
        }
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {

        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));

        if (MvvPerm::get('Dokument')->havePermCreate()) {
            $widget  = new ActionsWidget();
            $widget->addLink( _('Neues Dokument anlegen'),
                            $this->url_for('/dokument'),
                            Icon::create('file+add', 'clickable'));
            $sidebar->addWidget($widget);
        }

        if ($this->show_sidebar_search) {
            $this->sidebar_search();
            $this->sidebar_filter();
        }
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf dieser Seite können Sie Dokumente verwalten, die mit Studiengängen, Studiengangteilen usw. verknüpft sind.')));
        $helpbar->addWidget($widget);

        $this->sidebar_rendered = true;
    }

    /**
     * adds the search funtion to the sidebar
     */
    private function sidebar_search()
    {
        $template_factory = $this->get_template_factory();
        $query = 'SELECT dokument_id, name '
                . 'FROM mvv_dokument '
                . 'WHERE name LIKE :input '
                . 'OR url LIKE :input';
        $search_term =
                $this->search_term ? $this->search_term : _('Dokument suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('/search'));
        $widget->addNeedle(_('Dokument suchen'), 'dokument_suche', true,
            new SQLSearch($query, $search_term, 'dokument_id'),
            'function () { $(this).closest("form").submit(); }',
            $this->search_term);
        $widget->setTitle('Suche');
        $sidebar->addWidget($widget, 'search');
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        $template_factory = $this->get_template_factory();
        $filter_template = $template_factory->render('shared/filter',
                    array(
                        'zuordnungen'
                            => Dokument::getAllRelations($this->search_result['Dokument']),
                        'selected_zuordnung'
                            => $this->filter['mvv_dokument_zuord.object_type'],
                        'action' => $this->url_for(
                                '/set_filter'),
                        'action_reset' => $this->url_for(
                                '/reset_filter')));

        $sidebar = Sidebar::get();
        $widget  = new SidebarWidget();
        $widget->setTitle('Filter');
        $widget->addElement(new WidgetElement($filter_template));
        $sidebar->addWidget($widget,"filter");
    }

}
