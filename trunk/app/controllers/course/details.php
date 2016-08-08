<?php
/*
 * details.php - realises a redirector for administrative pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-oldenburg.de>
 * @copyright   2014
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.1
 */

require_once 'lib/dates.inc.php'; //Funktionen zum Anzeigen der Terminstruktur

class Course_DetailsController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $course_id = Request::option('sem_id', $args[0]);
        if (empty($course_id)) {
            checkObject(); //wirft Exception, wenn $SessionSeminar leer ist
            $course_id = $GLOBALS['SessionSeminar'];
        }

        $this->course                = Course::find($course_id);
        if (!$this->course) {
            throw new Trails_Exception(400);
        }
        $this->send_from_search_page = Request::get('send_from_search_page');

        if ($GLOBALS['SessionSeminar'] != $this->course->id
            && !(int)$this->course->visible
            && !($GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM)
                || $GLOBALS['perm']->have_studip_perm('user', $this->course->id))) {
            throw new AccessDeniedException(_('Diese Veranstaltung ist versteckt. Hier gibt es nichts zu sehen.'));
        }

        if (!preg_match('/^(' . preg_quote($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'], '/') . ')?([a-zA-Z0-9_-]+\.php)([a-zA-Z0-9_?&=-]*)$/', $this->send_from_search_page)) {
            $this->send_from_search_page = '';
        }

        if ($this->course->getSemClass()->offsetGet('studygroup_mode')) {
            if ($GLOBALS['perm']->have_studip_perm('autor', $this->course->id)) { // participants may see seminar_main
                $link = URLHelper::getUrl('seminar_main.php', array('auswahl' => $this->course->id));
            } else {
                $link = URLHelper::getUrl('dispatch.php/course/studygroup/details/' . $this->course->id, array('send_from_search_page' => $this->send_from_search_page));
            }
            $this->redirect($link);
            return;
        }

    }

    public function index_action()
    {

        $this->prelim_discussion = vorbesprechung($this->course->id);
        $this->title             = $this->course->getFullname();
        $this->course_domains    = UserDomain::getUserDomainsForSeminar($this->course->id);
        $this->sem = new Seminar($this->course);
        if ($studienmodulmanagement = PluginEngine::getPlugin('StudienmodulManagement')) {
            foreach ($this->course->study_areas->filter(function ($m) {
                return $m->isModule();
            }) as $module) {
                $this->studymodules[] = array('nav'   => $studienmodulmanagement->getModuleInfoNavigation($module->id, $this->course->start_semester->id),
                                              'title' => $studienmodulmanagement->getModuleTitle($module->id, $this->course->start_semester->id));
            }
        }

        // Retrive display of sem_tree
        if (Config::get()->COURSE_SEM_TREE_DISPLAY) {
            $this->studyAreaTree = StudipStudyArea::backwards($this->course->study_areas);
        } else {
            $this->study_areas = $this->course->study_areas->filter(function ($m) {
                return !$m->isModule();
            });
        }

        // Ausgabe der Modulzuordnung MVV
        $mvv_plugin = PluginEngine::getPlugin('MVVPlugin');
        if ($mvv_plugin) {
            if ($this->course->getSemClass()->offsetGet('module')) {

                // set filter to show only pathes with valid semester data
                ModuleManagementModelTreeItem::setObjectFilter('Modul', function ($modul, $parameter) {
                    $modul_start_sem = Semester::find($modul->start);
                    $modul_end_sem = Semester::find($modul->end);
                    $course_start_sem = Semester::find($parameter[0]);
                    $course_end_sem = Semester::find($parameter[1]);

                    if (($modul_start_sem->beginn <= $course_end_sem->beginn && $modul_end_sem->beginn >= $course_start_sem->beginn)
                        || ($course_end_sem == null && $modul_end_sem->beginn >= $course_start_sem->beginn)
                        || ($modul_end_sem == null && $modul_start_sem->beginn <= $course_end_sem->beginn)
                        || ($course_end_sem == null && $modul_end_sem == null)) {
                        return true;
                    } else {
                        return false;
                    }
                }, array(
                    $this->course->start_semester->id,
                    $this->course->end_semester ? $this->course->end_semester->id : null
                ));

                $trail_classes = array('Modulteil', 'StgteilabschnittModul', 'StgteilAbschnitt', 'StgteilVersion');
                $mvv_object_pathes = MvvCourse::get($this->course->getId())->getTrails($trail_classes);
                if ($mvv_object_pathes) {
                    foreach ($mvv_object_pathes as $mvv_object_path) {
                        // show only complete pathes
                        if (count($mvv_object_path) == 4) {
                            $mvv_object_names = array();
                            $modul_id = '';
                            foreach ($mvv_object_path as $mvv_object) {
                                if ($mvv_object instanceof StgteilabschnittModul) {
                                    $modul_id = $mvv_object->modul_id;
                                }
                                $mvv_object_names[] = $mvv_object->getDisplayName();
                            }
                            $this->mvv_pathes[] = array($modul_id => $mvv_object_names);
                        }
                    }
                }
            }
        }

        if (Request::isXhr()) {
            $this->set_layout(null);
            $this->response->add_header('Content-Type', 'text/html;charset=Windows-1252');
            header('X-Title: ' . $this->title);
        } else {
            PageLayout::setHelpKeyword("Basis.InVeranstaltungDetails");
            PageLayout::setTitle($this->title . " - " . _("Details"));
            PageLayout::addSqueezePackage('admission');
            PageLayout::addSqueezePackage('enrolment');
            if ($GLOBALS['SessionSeminar'] == $this->course->id) {
                Navigation::activateItem('/course/main/details');
                SkipLinks::addIndex(Navigation::getItem('/course/main/details')->getTitle(), 'main_content', 100);
            } else {
                $sidebarlink = true;
                $enrolment_info = $this->sem->getEnrolmentInfo($GLOBALS['user']->id);
            }

            $sidebar = Sidebar::Get();
            if ($sidebarlink) {
                $sidebar->setContextAvatar(CourseAvatar::getAvatar($this->course->id));
            }
            $sidebar->setTitle(_('Details'));
            $links = new ActionsWidget();
            $links->addLink(_("Drucken"),
                URLHelper::getScriptLink("dispatch.php/course/details/index/" . $this->course->id), Icon::create('print', 'clickable'),
                array('class' => 'print_action', 'target' => '_blank'));
            if ($enrolment_info['enrolment_allowed'] && $sidebarlink) {
                if (in_array($enrolment_info['cause'], words('member root courseadmin'))) {
                    $abo_msg = _("direkt zur Veranstaltung");
                } else {
                    $abo_msg = _("Zugang zur Veranstaltung");
                    if ($this->sem->admission_binding) {
                        PageLayout::postMessage(MessageBox::info(_('Die Anmeldung ist verbindlich, Teilnehmende können sich nicht selbst austragen.')));
                    }
                }
                $links->addLink($abo_msg,
                    URLHelper::getScriptLink("dispatch.php/course/enrolment/apply/" . $this->course->id), Icon::create('door-enter', 'clickable'),
                    array('data-dialog' => ''));

            }


            if (Config::get()->SCHEDULE_ENABLE
                && !$GLOBALS['perm']->have_studip_perm("user", $this->course->id)
                && !$GLOBALS['perm']->have_perm('admin')
                && $this->sem->getMetaDateCount()
            ) {
                $query = "SELECT COUNT(*) FROM schedule_seminare WHERE seminar_id = ? AND user_id = ?";
                if (!DBManager::Get()->fetchColumn($query, array($this->course->id,
                    $GLOBALS['user']->id))
                ) {
                    $links->addLink(_("Nur im Stundenplan vormerken"), URLHelper::getLink("dispatch.php/calendar/schedule/addvirtual/" . $this->course->id), Icon::create('info', 'clickable'));
                }
            }

            if ($this->send_from_search_page) {
                $links->addLink(_("Zurück zur letzten Auswahl"), URLHelper::getLink($this->send_from_search_page), Icon::create('link-intern', 'clickable'));
            }

            if ($links->hasElements()) {
                $sidebar->addWidget($links);
            }
            $sidebar->setImage('sidebar/seminar-sidebar.png');
            $sidebar->setContextAvatar(CourseAvatar::getAvatar($this->course->id));


            $sidebar = Sidebar::Get();
            $sidebar->setImage('sidebar/seminar-sidebar.png');
            $sidebar->setContextAvatar(CourseAvatar::getAvatar($this->course->id));

            if ($enrolment_info['description']) {
                PageLayout::postMessage(MessageBox::info($enrolment_info['description']));
            }
        }
    }
}