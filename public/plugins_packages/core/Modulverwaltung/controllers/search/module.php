<?php
/**
 * module.php - Search_ModuleController
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

class Search_ModuleController extends MVVController
{
    private $drill_down_filter = array();

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem('/search/module/modulsuche');
        $this->drill_down_type = Request::option('type');
        $this->drill_down_id = Request::option('id');
        $this->sterm = Request::get('sterm');
        if ($this->sterm) {
            URLHelper::bindLinkParam('sterm', $this->sterm);
        }
        if ($this->drill_down_type) {
            URLHelper::bindLinkParam('type', $this->drill_down_type);
        }
        if ($this->drill_down_id) {
            if($this->drill_down_id == '0') {
                $this->reset_drilldown();
            } else {
                URLHelper::bindLinkParam('id', $this->drill_down_id);
            }
        }
        $this->setSidebar();
    }

    protected function isVisible()
    {
        return $this->plugin->isVisibleSearch();
    }

    public function after_filter($action, $args) {
        parent::after_filter($action, $args);
    }

    public function index_action()
    {
        //set title
        PageLayout::setTitle(_('Suche nach Modulen'));

        $template = $this->get_template_factory()
                ->open('search/module/_infobox_info');

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement($template->render().'</br>'));
        $widget->addElement(new WidgetElement(_('Auch unvollständige Namen (mindestens 3 Zeichen) werden akzeptiert.')));
        $helpbar->addWidget($widget);

        $this->initPageParams();

        if($sem = Request::option('sem_select')) {
            $this->sessSet('selected_semester', $sem);
        }

        $semesterSwitch = intval(get_config('SEMESTER_TIME_SWITCH'));
        $currentSemester = SemesterData::GetInstance()
        ->getSemesterDataByDate(time() + $semesterSwitch * 7 * 24 * 60 * 60);
        $selected_semester = Semester::find($this->sessGet('selected_semester',
            $currentSemester['semester_id']));

        $do_search = Request::int('do_search');
        if (strlen(trim(str_replace('%', '', $this->sterm))) < 3) {
            if ($do_search) {
                PageLayout::postInfo(_('Der Suchbegriff muss mindestens 3 Zeichen lang sein.'));
            }
        } else {
            // reset old search
            if ($do_search) {
                $this->page = 1;
                $this->reset_drilldown();
            }

            // search
            // search in module content
            $this->search_result['Modul'] = Modul::search($this->sterm);

            // search for responsible institutes
            $search_responsible_institutes
                    = $this->search_responsible_institutes();
            // search for faecher
            $search_faecher = $this->search_faecher();
            // search for studiengaenge
            $search_studiengaenge = $this->search_studiengaenge();
            $this->search_result['Modul'] = array_unique(
                    array_merge($this->search_result['Modul'],
                            $search_responsible_institutes,
                            $search_faecher,
                            $search_studiengaenge));

            foreach ($this->search_result['Modul'] as $i => $mod_id) {
                $modul = Modul::find($mod_id);
                $start_sem = Semester::find($modul->start);
                $end_sem = Semester::find($modul->end);
                if ($start_sem->beginn > $selected_semester->beginn || ($selected_semester->ende > $end_sem->ende && $end_sem != null)) {
                    unset($this->search_result['Modul'][$i]);
                }
            }

            if ($do_search) {
                PageLayout::postInfo(sprintf(ngettext(
                            '%s Modul gefunden für die Suche nach <em>%s</em>',
                            '%s Module gefunden für die Suche nach <em>%s</em>',
                            count($this->search_result['Modul'])),
                            count($this->search_result['Modul']),
                            htmlReady($this->sterm)));
            }
        }

        /*if ($this->drill_down_type) {
            $this->drilldown();
        }*/
        $sidebar = Sidebar::get();

        $widget = new SelectWidget(_('Semesterauswahl'),
            $this->url_for('',array('sterm' => $this->sterm)), 'sem_select');
        $options = [];
        $semester = SemesterData::GetSemesterArray();
        unset($semester[0]);
        $semester = array_reverse($semester, true);
        foreach ($semester as $sem) {
            $options[$sem['semester_id']] = $sem['name'];
        }
        $widget->setOptions($options, $selected_semester->semester_id);
        $widget->setMaxLength(100);
        $sidebar->addWidget($widget, 'sem_filter');

        $this->input_search = $this->sterm;
        $this->result_count = count($this->search_result['Modul']);

        $active_list = Request::get('actlist', 'studiengaenge');

        $drill_down['studiengaenge']['objects'] =
                $this->drilldown_studiengaenge($this->search_result['Modul']);
        $drill_down['faecher']['objects'] =
                $this->drilldown_faecher($this->search_result['Modul']);
        $drill_down['institutes']['objects'] =
                $this->drilldown_institutes($this->search_result['Modul']);
        if (count($drill_down['institutes']['objects'])
                || count($drill_down['studiengaenge']['objects'])
                || count($drill_down['faecher']['objects'])) {
            $drill_down['studiengaenge']['name'] =
                    _('Studiengänge');
            $drill_down['faecher']['name'] =
                    _('Fächer');
            $drill_down['institutes']['name'] =
                    _('Verantwortliche Einrichtungen');
            $template = $this->get_template_factory()
                    ->open('search/module/_drill_down_list');
            $template->set_attribute('lists', $drill_down);
            $template->set_attribute('act_list', $active_list);
            $template->set_attribute('controller', $this);
            $template->set_attribute('drill_down_id', $this->drill_down_id);

            /*
            $widget  = new SidebarWidget();
            $widget->setTitle('Treffer im Bereich:');
            $widget->addElement(new WidgetElement($template->render()));
            $sidebar->addWidget($widget, 'Treffer');
            */


            $widget = new SelectWidget(_('Studiengänge'),
                $this->url_for('',array('sterm' => $this->sterm, 'type' => 'Studiengang')), 'id');
            $options = array(0 => 'Alle');
            if(!empty($drill_down['studiengaenge']['objects'])){
                foreach ($drill_down['studiengaenge']['objects'] as $studiengang) {
                    $options[$studiengang->studiengang_id] = $studiengang->name;
                }
            }
            $widget->setOptions($options, null);
            $widget->setMaxLength(100);
            $sidebar->addWidget($widget, 'studiengaenge_filter');


            $widget = new SelectWidget(_('Fächer'),
                $this->url_for('',array('sterm' => $this->sterm, 'type' => 'Fach')), 'id');
            $options = array(0 => 'Alle');
            if(!empty($drill_down['faecher']['objects'])){
                foreach ($drill_down['faecher']['objects'] as $fach) {
                    $options[$fach->fach_id] = $fach->name;
                }
            }
            $widget->setOptions($options, null);
            $widget->setMaxLength(100);
            $sidebar->addWidget($widget, 'faecher_filter');


            $widget = new SelectWidget(_('Verantwortliche Einrichtungen'),
                $this->url_for('',array('sterm' => $this->sterm, 'type' => 'Einrichtung')), 'id');
            $options = array(0 => 'Alle');
            if(!empty($drill_down['institutes']['objects'])){
                foreach ($drill_down['institutes']['objects'] as $institut) {
                    $options[$institut->institut_id] = $institut->name;
                }
            }
            $widget->setOptions($options, null);
            $widget->setMaxLength(100);
            $sidebar->addWidget($widget, 'institutes_filter');


        }

        $this->module = array();
        if (count($this->search_result['Modul'])) {
            $this->count = count($this->search_result['Modul']);
            if (count($this->drill_down_filter)) {
                $this->search_result['Modul'] = $this->drill_down_filter;
                $this->drill_down_count = count($this->search_result['Modul']);
            }

            $this->module = Modul::getAllEnriched('bezeichnung', 'ASC',
                    self::$items_per_page,
                    self::$items_per_page * (($this->page ?: 1) - 1),
                    array('mvv_modul.modul_id' => $this->search_result['Modul']));

            if (!empty($this->drill_down_type) && !empty($this->drill_down_id)) {
                $this->module = $this->filter_modules($this->module, $this->drill_down_type, $this->drill_down_id);
            }

        }
    }


    private function filter_modules($modules, $filter_by, $filter_id) {

        foreach ($this->module as $im => $modul) {
            $found = false;

            switch ($filter_by) {
                case 'Studiengang':
                    foreach (Studiengang::findByModule($modul->id) as $stg) {
                        if ($stg->id == $filter_id) {
                            $found = true;
                            break;
                        }
                    }
                    break;
                case 'Fach':
                    foreach (Fach::findPublicByModule($modul->id) as $fa) {
                        if ($fa->id == $filter_id) {
                            $found = true;
                            break;
                        }
                    }
                    break;
                case 'Einrichtung':
                    foreach (Fachbereich::findByModule($modul->id) as $fab) {
                        if ($fab->id == $filter_id) {
                            $found = true;
                            break;
                        }
                    }
                    break;
            }

            if (!$found) unset($modules[$im]);
        }

        return $modules;
    }

    public function details_action($modul_id)
    {
        if ($this->drill_down_filter) {
            $this->drilldown();
        }

        if($sem = Request::option('sem_select')) {
            $this->sessSet('selected_semester', $sem);
        }

        $this->modul = Modul::find($modul_id);
        $courses = $this->getSemesterCourses($this->modul);
        $this->semester_select = array();
        // only valid (semesters between start and end of module)
        // semesters for selector
        // $sem_valid = false;
        // Augsburg Module ohne Angabe der Gültigkeit
        $sem_valid = !((boolean) $this->modul->start);
        $sem_number = 1;
        foreach (Semester::getAll() as $semester) {
            if ($sem_valid || $this->modul->start == $semester->getId()) {
                // show only semesters with assigned courses
                if (in_array($sem_number, $courses)) {
                    $this->semester_select[] = $semester;
                }
                $sem_valid = true;
            }
            if ($this->modul->end == $semester->getId()) {
                break;
            }
            $sem_number++;
        }
        $semesterSwitch = intval(get_config('SEMESTER_TIME_SWITCH'));
        $currentSemester = SemesterData::GetInstance()
                ->getSemesterDataByDate(time() + $semesterSwitch * 7 * 24 * 60 * 60);
        $this->selected_semester =  Semester::find($this->sessGet('selected_semester',
                $currentSemester['semester_id']));
        $this->semester_select = array_reverse($this->semester_select);
        $response = $this->relay('shared/modul/overview', $this->modul->getId(), $this->selected_semester->semester_id);

        if (Request::isXhr()) {
            $this->modul_content = $response->body;
        } else {
            $this->details_id = $modul_id;
            $this->modul_content = $response->body;
            $this->perform_relayed('index');
        }
    }

    public function drilldown_action()
    {
        $this->initPageParams();
        $this->drilldown();
        $this->page = 1;
        $this->redirect($this->url_for('/index'));
    }

    private function drilldown()
    {
        $object_type = $this->drill_down_type;
        if (in_array($object_type, array('Studiengang', 'Fach', 'Fachbereich'))) {
            $selected_object = $object_type::find($this->drill_down_id);
            if (selected_object) {
                $this->drill_down_filter = $selected_object->getRelatedModules(true,
                        $this->search_result['Modul']);
               // $this->sessSet('drill_down_filter', $this->drill_down_filter);
              //  $this->drill_down_type = $object_type;
               // $this->sessSet('drill_down_type', $object_type);
              //  $this->drill_down_id = $selected_object->getId();
               // $this->sessSet('drill_down_id', $this->drill_down_id);
            }
        }
    }

    public function reset_drilldown_action()
    {
        $this->initPageParams();
        $this->page = 1;
        $this->reset_drilldown();
        $this->redirect($this->url_for('/index'));
    }

    private function reset_drilldown()
    {
        unset($this->drill_down_filter);
        URLHelper::removeLinkParam('filter');
        unset($this->drill_down_type);
        URLHelper::removeLinkParam('type');
        unset($this->drill_down_id);
        URLHelper::removeLinkParam('id');
        /*
        $this->sessRemove('drill_down_filter');
        $this->sessRemove('drill_down_type');
        $this->sessRemove('drill_down_id');
         *
         */
    }

    private function search_responsible_persons()
    {
        $term = '%' . $this->sterm . '%';
        $stmt = DBManager::get()->prepare('SELECT modul_id, user_id FROM '
                . 'mvv_modul_user LEFT JOIN auth_user_md5 USING(user_id) '
                . 'WHERE Vorname LIKE ? OR Nachname LIKE ? '
                . ' OR username LIKE ?');
        $stmt->execute(array($term, $term, $term));
        $ret = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $module) {
            $ret[$module['modul_id']][] = $module['user_id'];
        }
        return $ret;
    }

    private function search_responsible_institutes()
    {
        $term = '%' . $this->sterm . '%';
        $modul_public_status = ModuleManagementModel::getPublicStatus('Modul');
        $version_public_status =
                ModuleManagementModel::getPublicStatus('StgteilVersion');
        $studiengang_public_status =
                ModuleManagementModel::getPublicStatus('Studiengang');
        if (count($modul_public_status) && count($version_public_status)
                && count($studiengang_public_status)) {
            $query = 'SELECT DISTINCT(mmi.modul_id) '
                    . 'FROM Institute i '
                    . 'INNER JOIN mvv_modul_inst mmi '
                    . 'ON i.Institut_id = mmi.institut_id '
                    . 'INNER JOIN mvv_modul mm ON mmi.modul_id = mm.modul_id '
                    . 'INNER JOIN mvv_stgteilabschnitt_modul msm '
                    . 'ON mm.modul_id = msm.modul_id '
                    . 'INNER JOIN mvv_stgteilabschnitt msa '
                    . 'ON msm.abschnitt_id = msa.abschnitt_id '
                    . 'INNER JOIN mvv_stgteilversion msv '
                    . 'ON msa.version_id =  msv.version_id '
                    . 'INNER JOIN mvv_stg_stgteil mss '
                    . 'ON msv.stgteil_id = mss.stgteil_id '
                    . 'INNER JOIN mvv_studiengang ms '
                    . 'ON mss.studiengang_id = ms.studiengang_id '
                    . 'WHERE i.Name LIKE ? '
                    . 'AND mm.stat IN (?) AND msv.stat IN (?) '
                    . 'AND ms.stat IN (?)';
            $params = [$term, $modul_public_status,
                $version_public_status, $studiengang_public_status];
            $stmt = DBManager::get()->prepare($query);
            $stmt->execute($params);
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN);;
    }

    private function search_studiengaenge()
    {
        $term = '%' . $this->sterm . '%';
        $modul_public_status = ModuleManagementModel::getPublicStatus('Modul');
        $version_public_status =
 ModuleManagementModel::getPublicStatus('StgteilVersion');
        $studiengang_public_status =
 ModuleManagementModel::getPublicStatus('Studiengang');
        if (count($modul_public_status) && count($version_public_status)
                && count($studiengang_public_status)) {
            $query = 'SELECT DISTINCT(mm.modul_id) '
                    . 'FROM mvv_studiengang ms '
                    . 'INNER JOIN mvv_stg_stgteil USING(studiengang_id) '
                    . 'INNER JOIN mvv_stgteilversion msv USING(stgteil_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id) '
                    . 'INNER JOIN mvv_modul mm USING(modul_id) '
                    . 'WHERE (ms.name LIKE ? OR ms.name_kurz LIKE ? '
                    . 'OR ms.name_en LIKE ? OR ms.name_kurz_en LIKE ?) '
                    . ' AND ms.stat IN (?) '
                    . 'AND msv.stat IN (?) AND mm.stat IN (?)';
            $params = [$term, $term, $term, $term,
                $studiengang_public_status, $version_public_status,
                $modul_public_status];
            $stmt = DBManager::get()->prepare($query);
            $stmt->execute($params);
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function search_faecher()
    {
        $term = '%' . $this->sterm . '%';
        $ret = array();
        $modul_public_status = ModuleManagementModel::getPublicStatus('Modul');
        $version_public_status =
 ModuleManagementModel::getPublicStatus('StgteilVersion');
        $studiengang_public_status =
 ModuleManagementModel::getPublicStatus('Studiengang');
        if (count($modul_public_status) && count($version_public_status)) {
            $query = 'SELECT DISTINCT(mm.modul_id) '
                    . 'FROM fach mf INNER JOIN mvv_stgteil USING(fach_id) '
                    . 'INNER JOIN mvv_stg_stgteil mss USING(stgteil_id) '
                    . 'INNER JOIN mvv_studiengang ms USING(studiengang_id) '
                    . 'INNER JOIN mvv_stgteilversion msv '
                    . 'ON mss.stgteil_id = msv.stgteil_id '
                    . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id) '
                    . 'INNER JOIN mvv_modul mm USING(modul_id) '
                    . 'WHERE (mf.name LIKE ? OR mf.name_kurz LIKE ? '
                    . 'OR mf.name_en LIKE ? OR mf.name_kurz_en LIKE ?) '
                    . 'AND ms.stat IN (?) AND mm.stat IN (?) '
                    . 'AND msv.stat IN (?)';
            $params = [$term, $term, $term, $term,
                $studiengang_public_status, $modul_public_status,
                $version_public_status];
            $stmt = DBManager::get()->prepare($query);
            $stmt->execute($params);
            $ret = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $ret;
    }

    private function drilldown_persons($modul_ids)
    {
        if (count($modul_ids)) {
            $stmt = DBManager::get()->prepare('SELECT mmu.modul_id, aum.user_id, '
                    . $GLOBALS['_fullname_sql']['full'] . ' AS fullname, '
                    . 'COUNT(mmu.modul_id) AS count_module '
                    . 'FROM mvv_modul_user mmu LEFT JOIN auth_user_md5 aum USING(user_id) '
                    . 'LEFT JOIN user_info ui USING(user_id) '
                    . 'WHERE mmu.modul_id IN (?) '
                    . 'GROUP BY modul_id '
                    . 'ORDER BY count_module DESC');
            $stmt->execute(array($modul_ids));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return array();
    }

    private function drilldown_institutes($modul_ids)
    {
        if (count($modul_ids)) {
            return Fachbereich::findByModule($module_ids);
        }
        return array();
    }

    private function drilldown_faecher($modul_ids)
    {
        if (count($modul_ids)) {
            return Fach::findPublicByModule($modul_ids);
        }
        return array();
    }

    private function drilldown_studiengaenge($modul_ids)
    {
        if (count($modul_ids)) {
            return Studiengang::findByModule($modul_ids);
        }
        return array();
    }

    public function reset_action()
    {
        $this->reset_search();
        $this->redirect('search/module/index');
    }

    protected function reset_search($action = '')
    {
        //parent::reset_search();
        unset($this->sterm);
        URLHelper::removeLinkParam('sterm');
        $this->reset_drilldown();
    }

    public function overview_action($modul_id) {
        if ($this->drill_down_filter) {
            $this->drilldown();
        }

        if($sem = Request::option('sem_select')) {
            $this->sessSet('selected_semester', $sem);
        }

        $this->modul = Modul::get($modul_id);
        $courses = $this->getSemesterCourses($this->modul);
        $this->semester_select = array();
        // only valid (semesters between start and end of module)
        // semesters for selector
        // $sem_valid = false;
        // Augsburg Module ohne Angabe der Gültigkeit
        $sem_valid = !((boolean) $this->modul->start);
        $sem_number = 1;
        foreach (Semester::getAll() as $semester) {
            if ($sem_valid || $this->modul->start == $semester->getId()) {
                // show only semesters with assigned courses
                if (in_array($sem_number, $courses)) {
                    $this->semester_select[] = $semester;
                }
                $sem_valid = true;
            }
            if ($this->modul->end == $semester->getId()) {
                break;
            }
            $sem_number++;
        }

        $semesterSwitch = intval(get_config('SEMESTER_TIME_SWITCH'));
        $currentSemester = SemesterData::GetInstance()
                ->getSemesterDataByDate(time() + $semesterSwitch * 7 * 24 * 60 * 60);
        $this->selected_semester =  Semester::find($this->sessGet('selected_semester',
                $currentSemester['semester_id']));
        $this->semester_select = array_reverse($this->semester_select);
        $response = $this->relay('shared/modul/overview', $this->modul->getId(), $currentSemester['semester_id']);

        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            $this->details_id = $modul_id;
            $this->modul_content = $response->body;
            $this->perform_relayed('index');
        }
    }

    public function description_action($modul_id)
    {
        if ($this->drill_down_filter) {
            $this->drilldown();
        }

        $response = $this->relay('shared/modul/description', $modul_id);
        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            $this->details_id = $modul_id;
            $this->modul_content = $response->body;
            $this->perform_relayed('index');
        }
    }

    protected function initInfobox()
    {
        $this->setInfoBoxImage('infobox/board1.jpg');
    }

    private function getSemesterCourses($modul)
    {
        $semester = Semester::getAll();
        $courses = array();
        foreach ($modul->modulteile as $modulteil) {
            foreach ($modulteil->lvgruppen as $lvgruppe) {
                $courses = array_merge($courses,
                        array_keys($lvgruppe->getAllAssignedCourses(true)));
            }
        }
        return $courses;
    }

}
