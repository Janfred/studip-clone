<?php
/**
 * lvgruppen.php - controller class for LV-Gruppen
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

class Lvgruppen_LvgruppenController extends MVVController
{
    public $filter = array();
    public $semester_filter = null;
    private $show_sidebar_search = false;
    private $show_sidebar_filter = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/lvgruppen/lvgruppen');
        $this->filter = $this->sessGet('filter', array());
        $this->semester_filter = $this->sessGet('semester_filter', null);
        $this->action = $action;

        if (Request::isXhr()) {
            $this->response->add_header('Content-Type',
                    'text/html; charset=WINDOWS-1252');
            $this->set_layout(null);
        }
    }

    /**
     * show list of Lehrveranstaltungsgruppen
     */
    public function index_action()
    {
        // set title
        PageLayout::setTitle(_('Verwaltung der Lehrveranstaltungsgruppen'));

        $this->initPageParams();
        $this->initSearchParams();

        $search_result = $this->getSearchResult('Lvgruppe');

        // Nur LvGruppen an Modulen von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $own_institutes = MvvPerm::getOwnInstitutes();
        if (!$this->filter['mvv_modul_inst.institut_id']) {
            $this->filter['mvv_modul_inst.institut_id'] = $own_institutes;
        } else if (sizeof($own_institutes)) {
            $this->filter['mvv_modul_inst.institut_id'] = array_intersect(
                    (array) $this->filter['mvv_modul_inst.institut_id'],
                    $own_institutes);
        }


        if (count($this->filter['mvv_modul_inst.institut_id'])) {
            $filter = array_merge(
                array(
                    'mvv_lvgruppe.lvgruppe_id' => $search_result,
                    'mvv_modul_inst.gruppe'      => 'hauptverantwortlich'),
                (array) $this->filter);
        } else {
            $filter = array_merge(
                array('mvv_lvgruppe.lvgruppe_id' => $search_result),
                (array) $this->filter);
        }

        $this->semester_filter =
                $this->semester_filter ?: Semester::findCurrent()->semester_id;
        $author_sql = null;
        $this->lvgruppen = Lvgruppe::getAllEnriched(
                $this->sortby,
                $this->order,
                Lvgruppe::getFilterSql($filter, true, $author_sql),
                self::$items_per_page,
                self::$items_per_page * ($this->page - 1),
                $this->semester_filter);
        if (!empty($this->filter)) {
            $this->search_result['Lvgruppe'] = $this->lvgruppen->pluck('id');
        } else if (sizeof($this->lvgruppen) == 0) {
            PageLayout::postInfo(_('Es wurden keine Lehrveranstaltungsgruppen gefunden.'));
        }
                
        $this->count = Lvgruppe::getCount($filter, $this->semester_filter);
        
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten können Sie Lehrveranstaltungsgruppen verwalten.').'</br>'));
        $widget->addElement(new WidgetElement(_('Eine Lehrveranstaltungsgruppe kann aufgeklappt werden, um die Lehrveranstaltungen anzuzeigen, die dieser Gruppe bereits zugeordnet wurden.')));
        $helpbar->addWidget($widget);

        $this->show_sidebar_search = true;
        $this->show_sidebar_filter = true;

        $this->setSidebar();
        $sidebar = Sidebar::get();
        $widget  = new ActionsWidget();
        if (MvvPerm::havePermCreate('Lvgruppe')) {
            $widget->addLink(_('Neue Lehrveranstaltungsgruppe anlegen'),
                    $this->url_for('/lvgruppe'),
                    Icon::create('file+add', 'clickable'));
        }
        $widget->addLink( _('Lehrveranstaltungsgruppen mit Zuordnungen exportieren'),
                $this->url_for('/export_xls'),
                Icon::create('download', 'clickable'));
        $sidebar->addWidget($widget);
    }

    public function details_action($lvgruppe_id = null)
    {
        $this->lvgruppe = Lvgruppe::find($lvgruppe_id);
        if (!$this->lvgruppe) {
            throw new Exception(_('Unbekannte LV-Gruppe'));
        }

        $this->display_semesters = [];
        if ($this->semester_filter != 'all') {
            // show courses of the current and next semester
            $this->courses = $this->lvgruppe->getAllAssignedCourses(false,
                $this->semester_filter);
            $semester = Semester::find($this->semester_filter);            
            if ($semester && $semester->getcurrent()) {
                $next_sem = Semester::findNext();
                $this->display_semesters[] = $next_sem;
                $this->courses = array_merge($this->courses,
                    $this->lvgruppe->getAllAssignedCourses(false, $next_sem->id));
            }
            $this->display_semesters[] = $semester;
        } else {
            // show courses of all elapsed, current and next semesters
            $this->courses = $this->lvgruppe->getAllAssignedCourses();
            $next_sem = Semester::findNext();
            $this->display_semesters =
                    Semester::findBySQL('beginn <= ? ORDER BY beginn DESC',
                            [$next_sem->beginn]);
        }

        $this->set_trails_filter(end($this->display_semesters)->beginn,
                reset($this->display_semesters)->beginn);

        $this->trail_classes = words('Modulteil Modul StgteilAbschnitt StgteilVersion '
                . 'Studiengang Fachbereich');

        $this->trails = $this->lvgruppe->getTrails(
                $this->trail_classes, MvvTreeItem::TRAIL_SHOW_INCOMPLETE);

        if (!Request::isXhr()){
            $this->perform_relayed('index');
            return true;
        }
    }

    public function lvgruppe_action($lvgruppe_id = null)
    {
        $this->lvgruppe = Lvgruppe::find($lvgruppe_id);
        if (!$this->lvgruppe) {
            $this->lvgruppe = new Lvgruppe();
            PageLayout::setTitle(_('Neue Lehrveranstaltungsgruppe anlegen'));
            $success_message = _('Die Lehrveranstaltungsgruppe "%s" wurde angelegt.');
            $this->headline = _('Neue Lehrveranstaltungsgruppe anlegen.');
        } else {
            PageLayout::setTitle(_('Lehrveranstaltungsgruppe bearbeiten'));
            $success_message = _('Die Lehveranstaltungsgruppe "%s" wurde geändert.');
            $this->headline = sprintf(_('Lehrveranstaltungsgruppe "%s" bearbeiten.'),
                $this->lvgruppe->getDisplayName());
        }
        $this->cancel_url = $this->url_for('/index');
        $this->submit_url = $this->url_for('/lvgruppe/'
                . $this->lvgruppe->getId());
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->lvgruppe->name = trim(Request::get('name'));
            $this->lvgruppe->alttext = trim(Request::get('alttext'));
            $this->lvgruppe->alttext_en = trim(Request::get('alttext_en'));
            try {
                $this->lvgruppe->verifyPermission();
                $stored = $this->lvgruppe->store();
            } catch (InvalidValuesException $e) {
                 PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                $this->sessSet('sortby', 'chdate');
                $this->sessSet('order', 'DESC');
                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message,
                            htmlReady($this->lvgruppe->getDisplayName())));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/index'));
                return;
            }
        }

        $this->setSidebar();
        if (!$this->lvgruppe->isNew()) {
            $sidebar = Sidebar::get();
            $widget  = new ActionsWidget();
            $widget->addLink( _('Log-Einträge dieser LV-Gruppe'),
                    $this->url_for('shared/log_event/show', $this->lvgruppe->getId()),
                    Icon::create('log', 'clickable'), array('data-dialog' => 'size=auto'));
            $sidebar->addWidget($widget);
        }
    }

    function delete_action($lvgruppe_id)
    {
        $lvgruppe = Lvgruppe::find($lvgruppe_id);
        if (!$lvgruppe) {
            throw new Exception(_('Unbekannte LV-Gruppe'));
        }
        $perm = MvvPerm::get($lvgruppe);
        if (Request::submitted('yes')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($lvgruppe->isNew()) {
                PageLayout::postError( _('Die Lehrveranstaltungsgruppe kann nicht gelöscht werden (unbekannte Lehrveranstaltungsgruppe).'));
            } elseif (count($lvgruppe->courses)
                    || count($lvgruppe->modulteile)
                    || count($lvgruppe->archived_courses)) {
                PageLayout::postError( _('Die Lehrveranstaltungsgruppe kann nicht gelöscht werden, da sie mit Veranstaltungen oder Modulteilen verknüpft ist.'));
            } else {
                if ($perm->havePerm(MvvPerm::PERM_CREATE)) {
                    $name = $lvgruppe->getDisplayName();
                    $lvgruppe->delete();
                    PageLayout::postSuccess(sprintf(_('Die Lehrveranstaltungsgruppe "%s" wurde gelöscht.'), htmlReady($name)));
                } else {
                    throw new Trails_Exception(403, _('Keine Berechtigung'));
                }
            }
        }
        if (!Request::isPost()) {
            $this->flash_set('dialog', sprintf(_('Wollen Sie wirklich die Lehrveranstaltungsgruppe "%s" loeschen?'),
                            $lvgruppe->getDisplayName()),
                    '/delete/'
                    . $lvgruppe->getId(),
                    '/index');
        }
        $this->redirect('lvgruppen/lvgruppen');
    }

    function export_xls_action()
    {
        $this->initSearchParams();
        $this->initPageParams();

        $search_result = $this->getSearchResult('Lvgruppe');

        if (count($this->filter['mvv_modul_inst.institut_id'])) {
            $filter = array_merge(
                array(
                    'mvv_lvgruppe.lvgruppe_id' => $search_result,
                    'mvv_modul_inst.gruppe'      => 'hauptverantwortlich'),
                (array) $this->filter);
        } else {
            $filter = array_merge(
                array('mvv_lvgruppe.lvgruppe_id' => $search_result),
                (array) $this->filter);
        }

        $this->semester_filter =
                $this->semester_filter ?: Semester::findCurrent()->getId();

        $this->lvgruppen = Lvgruppe::getAllEnriched(
                $this->sortby, $this->order,
                $filter, null, null, $this->semester_filter);

        if ($this->semester_filter == all) {
            $semester = Semester::getAll();
            $this->set_trails_filter(end($semester)->beginn,
                    reset($semester)->beginn);
        } else {
            $semester = Semester::find($this->semester_filter);
            $this->set_trails_filter($semester->beginn, $semester->beginn);
        }

        $this->response->add_header('Content-type', 'application/vnd.ms-excel');
        $this->render_template('lvgruppen/lvgruppen/export_xls', null);
    }

    /**
     * do the search
     */
    public function search_action()
    {
        $this->reset_search('Lvgruppe');
        $this->do_search('Lvgruppe',
                trim(Request::get('lvgruppe_suche_parameter')),
                Request::option('lvgruppe_suche'));
        $this->redirect($this->url_for('/index'));
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('Lvgruppe');
        $this->redirect($this->url_for('/index'));
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));

        if ($this->show_sidebar_search) {
            $this->sidebar_search();
        }
        if ($this->show_sidebar_filter) {
            $this->sidebar_filter();
        }
        $this->sidebar_rendered = true;
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        $template_factory = $this->get_template_factory();
        $template = $template_factory->open('shared/filter');

        // Semester
        $template->set_attribute('semester', array_reverse(Semester::getAll()));
        $template->set_attribute('selected_semester', $this->semester_filter);
        $template->set_attribute('default_semester',
                Semester::findCurrent()->semester_id);
        $template->set_attribute('semester_caption', _('Verwendung in Semester:'));

        // Fachbereiche
        $template->set_attribute('fachbereiche',
                Lvgruppe::getAllAssignedInstitutes('name', 'ASC'));
        $template->set_attribute('selected_fachbereich',
                $this->filter['mvv_modul_inst.institut_id']);
        $template->set_attribute('fachbereich_caption', _('Verwendet von Fachbereich:'));

        $template->set_attribute('action',
                $this->url_for('/set_filter'));
        $template->set_attribute('action_reset',
                $this->url_for('/reset_filter'));

        $sidebar = Sidebar::get();
        $widget  = new SidebarWidget();
        $widget->setTitle(_('Filter'));
        $widget->addElement(new WidgetElement($template->render()));
        $sidebar->addWidget($widget, 'filter');
    }

    /**
     * sets filter parameters and stores filters in session
     */
    public function set_filter_action()
    {
        // Zugeordnete Fachbereiche
        $this->filter['mvv_modul_inst.institut_id']
                = strlen(Request::get('fachbereich_filter'))
                ? Request::option('fachbereich_filter') : null;

        // Semester
        $this->semester_filter = strlen(Request::get('semester_filter'))
                ? Request::option('semester_filter') : null;

        // store filter
        $this->sessSet('filter', $this->filter);
        $this->sessSet('semester_filter', $this->semester_filter);
        $this->sessRemove('page');
        $this->perform_relayed('index');
    }

    public function reset_filter_action()
    {
        $this->filter = array();
        $this->sessRemove('filter');
        $this->semester_filter = null;
        $this->sessRemove('semester_filter');
        $this->sessRemove('page');
        $this->perform_relayed('index');
    }

    /**
     * adds the search function to the sidebar
     */
    private function sidebar_search()
    {
        $template_factory = $this->get_template_factory();
        $query = "SELECT lvgruppe_id, name "
                . 'FROM mvv_lvgruppe '
                . 'WHERE name LIKE :input';
        $search_term =
                $this->search_term ? $this->search_term : _('LV-Gruppe suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('lvgruppen/lvgruppen/search'));
        $widget->addNeedle(_('LV-Gruppe suchen'), 'lvgruppe_suche', true,
            new SQLSearch($query, $search_term, 'lvgruppe_id'),
            'function () { $(this).closest("form").submit(); }',
            $this->search_term);
        $widget->setTitle(_('Suche'));
        $sidebar->addWidget($widget, 'search');
    }

    private function set_trails_filter($end, $start)
    {
        // show only pathes with modules valid in the selected semester
        ModuleManagementModelTreeItem::setObjectFilter('Modulteil',
            function ($mt, $params) {
                $start_sem = Semester::find($mt->modul->start);
                $end_sem = Semester::find($mt->modul->end);
                if ($end_sem) {
                    if (($params['start'] >= $start_sem->beginn
                            && $params['start'] <= $end_sem->ende)
                        || ($params['ende'] >= $start_sem->beginn
                            && $params['ende'] <= $end_sem->ende)) {
                        return true;
                    }
                } else {
                    if ($params['end'] >= $start_sem->beginn) {
                        return true;
                    }
                }
                return false;
            }, compact(words('start end'))
        );
    }
}
