<?php
/**
 * studiengaenge.php - Search_StudiengaengeController
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
require_once dirname(__FILE__) . '/BreadCrumb.class.php';

class Search_StudiengaengeController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        ModuleManagementModel::setLanguage($_SESSION['_language']);
        
        // set navigation
        Navigation::activateItem('/search/module/studiengaenge');
        $this->setSidebar();
        $this->breadCrumb = new BreadCrumb();

        if (Request::isXhr()) {
            $this->response->add_header('Content-Type',
                    'text/html; charset=WINDOWS-1252');
            $this->set_layout(null);
        }
    }

    protected function isVisible()
    {
        return $this->plugin->isVisibleSearch();
    }

    public function index_action()
    {
        $this->categories = AbschlussKategorie::getAllEnriched();

        $this->abschluss_url = $this->url_for('abschlusskategorie/show/');

        $this->breadCrumb->init();
        $this->breadCrumb->append(_('Studienangebot'));
    }

    public function kategorie_action($kategorie_id)
    {
        $kategorie = AbschlussKategorie::get($kategorie_id);
        $studiengaenge = Studiengang::findByAbschlussKategorie($kategorie->getId());

        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }

        $studiengaenge_abschluss = array();
        $abschluesse = array();
        foreach ($studiengaenge as $studiengang) {
            if (in_array($studiengang->stat, $status_filter)) {
                $abschluss = Abschluss::find($studiengang->abschluss_id);
                $abschluesse[$abschluss->getId()] = $abschluss;
                $studiengaenge_abschluss[$studiengang->abschluss_id][$studiengang->getId()] = $studiengang;
            }
        }
        $this->breadCrumb->append($kategorie->getDisplayName());
        $this->kategorie = $kategorie;
        $this->abschluesse = $abschluesse;
        $this->studiengaenge = $studiengaenge_abschluss;
    }

    public function studiengang_action($studiengang_id)
    {
        $studiengang = Studiengang::find($studiengang_id);

        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }

        if (!$studiengang || !in_array($studiengang->stat, $status_filter)) {
            PageLayout::postError( _('Unbekannter Studiengang.'));
            $this->redirect('search/studiengaenge');
            return null;
        }

        $method = $studiengang->typ;
        $this->studiengangName = $studiengang->getDisplayName();
        $this->abschlussName = Abschluss::get($studiengang->abschluss_id)->getDisplayName();
        $this->breadCrumb->append($this->studiengangName);
        $this->$method($studiengang_id);
    }

    private function mehrfach($studiengang_id)
    {
        $studiengang = Studiengang::find($studiengang_id);

        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }

        if (!$studiengang || !in_array($studiengang->stat, $status_filter)) {
            PageLayout::postError( _('Unbekannter Studiengang.'));
            $this->redirect('search/studiengaenge');
            return null;
        }

        $faecher = Fach::findByStudiengang($studiengang->getId());
        $studiengangTeilBezeichnungen = $studiengang->stgteil_bezeichnungen;

        $punkte = array();
        $fachNamen = array();
        $teilNamen = array();
        foreach ($faecher as $fach) {
            $fachNamen[$fach->id] = $fach->getDisplayName();
            $punkte[$fach->id] = array();
            foreach ($studiengangTeilBezeichnungen as $studiengangTeilBezeichnung) {

                $schnittpunkte = StudiengangTeil::findByStudiengangStgteilBez(
                        $studiengang->getId(), $studiengangTeilBezeichnung->getId());
                $teilNamen[$studiengangTeilBezeichnung->id] = $studiengangTeilBezeichnung->getDisplayName();

                foreach ($schnittpunkte as $schnittpunkt) {
                    if ($schnittpunkt->fach_id === $fach->getId()) {
                        $punkte[$fach->id][$studiengangTeilBezeichnung->id] = $schnittpunkt->getId();
                    }
                }
            }
        }

        $this->studiengang_id = $studiengang->id;
        $this->data = $punkte;
        $this->fachNamen = $fachNamen;
        $this->teilNamen = $teilNamen;

        if (!$this->verlauf_url) {
            $this->verlauf_url = 'search/studiengaenge/verlauf';
        }
        $this->render_template('search/studiengaenge/mehrfach', $this->layout);
    }

    private function einfach($studiengang_id)
    {
        $studiengangTeile = StudiengangTeil::findByStudiengang($studiengang_id);
        if (count($studiengangTeile) == 1) {
            $teil = $studiengangTeile;
            $id = $teil[0]->getId();
            $this->verlauf_action($id);
            $this->render_template('search/studiengaenge/verlauf', $this->layout);
        } else {
            // Einfach-Studiengang mit Ausprägungen
            // (unterschiedliche Studiengangteile direkt am Studiengang, ohne
            // Studiengangteil-Bezeichnungen)
            $this->data = array();
            foreach ($studiengangTeile as $teil) {
                $this->data[$teil->getId()] = $teil->fach->getDisplayName();
            }
            if (!$this->verlauf_url) {
                $this->verlauf_url = 'search/studiengaenge/verlauf';
            }
            $this->breadCrumb->append(Studiengang::get($studiengang_id)->getDisplayName());
            $this->render_template('search/studiengaenge/einfach', $this->layout);
        }
    }

    public function verlauf_action($stgteil_id, $stgteil_bez_id = null, $studiengang_id = null)
    {
        $sem = Request::option('semester');
        if ($sem) {
            $this->sessSet('selected_semester', $sem);
        }

        $studiengangTeil = StudiengangTeil::find($stgteil_id);
        $versionen = StgteilVersion::findByStgteil($stgteil_id, 'start', 'DESC')->filter(
            function ($version) {
                $public = $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['public'];
                return ($public ? true : false);
            });
        if (!$studiengangTeil || count($versionen) === 0) {
            PageLayout::postInfo(_('Kein Verlaufsplan im gewählten Bereich verfügbar.'));
        } else {
            $version_id = Request::option('version', $this->sessGet('selected_version'));
            if ($versionen->findOneBy('id', $version_id)) {
                $this->cur_version_id = $version_id;
            } else {
                $this->cur_version_id = $this->findCurrentVersion($versionen);
            }
            $this->sessSet('selected_version', $this->cur_version_id);
            
            $this->semesters = $this->getSemester($versionen->findOneBy('id', $this->cur_version_id));

            $semester_time_switch = (int) Config::get()->getValue('SEMESTER_TIME_SWITCH');
            $cur_semester = Semester::findByTimestamp(time()
                    + $semester_time_switch * 7 * 24 * 60 * 60);
            
            $active_semester = $this->sessGet('selected_semester');
            if ($active_semester) {
                $this->active_sem = $this->semesters[$active_semester];
            } else if ($cur_semester) {
                $this->active_sem = $cur_semester->id;
            } else {
                $this->active_sem = Semester::find($this->sessGet('selected_semester', Semester::findCurrent()->id));
            }
            $this->active_sem = $this->semesters[$this->active_sem->id] ? $this->active_sem : null;
            if (!$this->active_sem && count($this->semesters)) {
                $active_sem = reset($this->semesters);
                $this->active_sem = Semester::find($active_sem['semester_id']);
            }
            $this->setVersionSelectWidget($versionen);

            $abschnitte = StgteilAbschnitt::findByStgteilVersion($this->cur_version_id);
            $abschnitteData = array();
            $fachsemesterData = array();
            foreach ($abschnitte as $abschnitt) {
                $abschnitteData[$abschnitt->id] = array(
                    'name' => $abschnitt->getDisplayName(),
                    'creditPoints' => $abschnitt->kp,
                    'zwischenUeberschrift' => $abschnitt->ueberschrift,
                    'module' => array(),
                    'rowspan' => 0,
                    'kommentar' => $abschnitt->kommentar
                );
                //$module = Modul::findByStgteilAbschnitt($abschnitt->getId());
                $abschnitt_module = $abschnitt->getModulAssignments();
                foreach ($abschnitt_module as $abschnitt_modul) {
                    
                    // module is not public visible
                    if (!$abschnitt_modul->modul->hasPublicStatus()) {
                        continue;
                    }
                    
                    $start_sem = Semester::find($abschnitt_modul->modul->start);
                    $end_sem = Semester::find($abschnitt_modul->modul->end);
                    if ($start_sem->beginn > $this->active_sem->beginn || ($this->active_sem->ende > $end_sem->ende && $end_sem != null)) {
                       continue;
                    }

                    $abschnitteData[$abschnitt->id]['module'][$abschnitt_modul->modul->id] = array(
                        'name' => $abschnitt_modul->getDisplayName(),
                        'modulTeile' => array()
                    );
                    $countcourses = 0;
                    foreach ($abschnitt_modul->modul->modulteile as $teil) {
                        $lvg = Lvgruppe::findByModulteil($teil->id);
                        if ($lvg) {
                            foreach ($lvg as $lv) {
                                $courses = $lv->getAssignedCoursesBySemester($this->active_sem->id, $GLOBALS['user']->id);
                                $countcourses += count($courses);
                            }
                        }

                        $fachSemester = $abschnitt_modul->getAllFachSemester($teil->id);

                        $abschnitteData[$abschnitt->id]['module'][$abschnitt_modul->modul->id]['modulTeile'][$teil->id] = array(
                            'name' => $teil->getDisplayName(),
                            'position' => $teil->position,
                            'fachsemester' => array()
                        );
                        $abschnitteData[$abschnitt->id]['rowspan']++;
                        foreach ($fachSemester as $fachsem) {
                            $fachsemesterData[$fachsem->fachsemester] = $fachsem->fachsemester;
                            $abschnitteData[$abschnitt->getId()]['module'][$abschnitt_modul->modul->getId()]['modulTeile'][$teil->getId()]['fachsemester'][$fachsem->fachsemester] = $fachsem->differenzierung;
                        }
                    }
                    $abschnitteData[$abschnitt->id]['module'][$abschnitt_modul->modul->id]['veranstaltungen'] = $countcourses;
                }
            }

            if ($studiengang_id) {
                if ($stgteil_bez_id) {
                    $this->stgTeilBez = StgteilBezeichnung::get($stgteil_bez_id);
                    $this->breadCrumb->append($this->stgTeilBez->getDisplayName() . ': ' . $studiengangTeil->getDisplayName());
                } else {
                    $this->stgTeilBez = StgteilBezeichnung::get($stgteil_bez_id);
                    $this->breadCrumb->append($studiengangTeil->getDisplayName());
                }
                $this->studiengang = Studiengang::get($studiengang_id);
            }
            ksort($fachsemesterData);
            $this->fachsemesterData = $fachsemesterData;
            $this->abschnitteData = $abschnitteData;
            $this->versionen = $versionen;
            // Augsburg
            // Ausgabe des Namens ohne Fach (dieses ist im Zusatz bereits enthalten)
            // $this->studiengangTeilName = $studiengangTeil->getDisplayName(0);
            $this->studiengangTeilName = $studiengangTeil->getDisplayName();
            $this->self_url = $this->url_for('search/studiengaenge/verlauf/' . $stgteil_id . '/');
            $this->modul_url = $this->url_for('search/module/detail/');
            $this->modulTeil_url = $this->url_for('modulteil/show/');
        }
    }

    public function kommentar_action($abschnitt_id)
    {
        $this->abschnitt = StgteilAbschnitt::find($abschnitt_id);
        if (!$this->abschnitt) {
            throw new Trails_Exception(404);
        }
    }

    private function getSemester($version)
    {
        if (!$version) {
            return [];
        }
        $start_sem = Semester::find($version->start_sem);
        $end_sem = Semester::find($version->end_sem);
        $start = (int) ($start_sem ? $start_sem->beginn : 0);
        $end = (int) ($end_sem ? $end_sem->beginn : PHP_INT_MAX);
        $semester = [];
        $sql = 'SELECT 1
                FROM mvv_stgteilabschnitt
                INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id)
                INNER JOIN mvv_modul mm USING(modul_id)
                INNER JOIN mvv_modulteil USING(modul_id)
                LEFT JOIN semester_data mm_start_sem ON mm.start = mm_start_sem.semester_id 
                LEFT JOIN semester_data mm_end_sem ON mm.end = mm_end_sem.semester_id
                WHERE mvv_stgteilabschnitt.version_id = :version
                AND ((mm_start_sem.beginn IS NULL AND mm_end_sem.ende IS NULL)
                OR (mm_start_sem.beginn <= :beginn
                AND (mm_end_sem.ende >= :ende OR mm_end_sem.ende IS NULL)))
                LIMIT 1';
        $stmt = DBManager::get()->prepare($sql);
        foreach (Semester::getAll() as $one) {
            if ($one->beginn >= $start && $one->beginn <= $end) {
                $stmt->execute([':version' => $version->getId(),
                         ':beginn' => $one->beginn,
                         ':ende' => $one->ende]);
                if ($stmt->fetchColumn()) {
                    $semester[$one->id] = $one;
                }
            }
        }
        return array_reverse($semester);
    }
    
    private function findCurrentVersion($versions)
    {
        $semester_data = Semester::getAll();
        $current_semester = Semester::findCurrent();
        $cur_version_id = null;
        if (count($versions)) {
            foreach ($versions as $version) {
                if ((!$version->start_sem && !$version->end_sem)
                    || ($semester_data[$version->start_sem]->beginn <= $current_semester->beginn
                            && !$version->end_sem)
                    || ($semester_data[$version->start_sem]->beginn <= $current_semester->beginn
                            && $semester_data[$version->end_sem]->beginn >= $current_semester->beginn)) {
                    $cur_version_id = $version->getId();
                }
            }
            // no start or end semester for versions, take the last one
            if (!$cur_version_id) {
                $cur_version_id = $versions->last()->id;
            }
        }
        return $cur_version_id;
    }

    /**
     * Adds a widget to select versions of Studiengang-Teile
     *
     * @param string $selected
     */
    private function setVersionSelectWidget($versions)
    {

        $semester_time_switch = (int) Config::get()->getValue('SEMESTER_TIME_SWITCH');
        $cur_semester = Semester::findByTimestamp(time()
            + $semester_time_switch * 7 * 24 * 60 * 60);
        
        $sidebar = Sidebar::get();

        $widget = new SelectWidget(_('Versionen-Auswahl'),
                '', 'version');
        $options = [];
        foreach ($versions as $version) {
            $options[$version->id] = $version->getDisplayName(0);
        }
        $widget->setOptions($options, $this->cur_version_id);
        $widget->setMaxLength(100);
        $sidebar->addWidget($widget, 'version_filter');

        $widget = new SelectWidget(_('Semesterauswahl'),
            '', 'semester');
        $options = [];
        foreach ($this->semesters as $sem) {
            $options[$sem['semester_id']] = $sem['name'];
        }
        $widget->setOptions($options, $this->active_sem->id);
        $widget->setMaxLength(100);
        $sidebar->addWidget($widget, 'sem_filter');
    }

}
