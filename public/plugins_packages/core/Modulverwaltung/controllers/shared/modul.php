<?php
/**
 * modul.php - Shared_ModulController
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

class Shared_ModulController extends MVVController
{
    
    public function before_filter(&$action, &$args)
    {    
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
    
    public function overview_action($modul_id, $semester_id = null)
    {
        $modul = Modul::find($modul_id);
        if ($modul) {
            $this->details_id = $modul->getId();
            $modulTeile = Modulteil::findByModul($modul->getId());
            $type = 1;
            
            if (count($modulTeile) == 1) {
                //$modulTeile = array_values($modulTeile);
                $modulTeil = $modulTeile->first();//[0];
                $type = 3;
                if (strlen($modulTeil->getDisplayName()) > 0) {
                    $type = 2;
                }
            }
            
            if (!$semester_id) {
                $semesterSwitch = intval(get_config('SEMESTER_TIME_SWITCH'));
                $currentSemester = SemesterData::GetInstance()
                    ->getSemesterDataByDate(time() + $semesterSwitch * 7 * 24 * 60 * 60);
            } else {
                //$currentSemester = SemesterData::getSemesterData(Request::option('sem_select'));
                $currentSemester = SemesterData::GetInstance()->getSemesterData($semester_id);
            }    
            
            $modulUser = ModulUser::findByModul($modul->getId());
            $modulVerantwortung = array();

            foreach ($modulUser as $users) {
                foreach ($users as $user) {
                    if (!isset($modulVerantwortung[$user->gruppe])) {
                        $modulVerantwortung[$user->gruppe] = array(
                            'name' => $GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'][$user->gruppe]['name'],
                            'users' => array()
                        );
                    }
                    $modulVerantwortung[$user->gruppe]['users'][] = array(
                        'name' => get_fullname($user->user_id),
                        'id' => $user->user_id
                    );
                }
            }

            $sws = 0;
            $institut = new Institute($modul->responsible_institute->institut_id);
            $modulTeileData = array();
            foreach ($modulTeile as $modulTeil) {

                $modulTeilDeskriptor = $modulTeil->getDeskriptor();

                $sws += (int) $modulTeil->sws;

                $num_bezeichnung = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$modulTeil->num_bezeichnung]['name'];

                $name_kurz = sprintf('%s %d', $num_bezeichnung, $modulTeil->nummer);

                $modulTeileData[$modulTeil->getId()] = array(
                    'name' => $modulTeil->getDisplayName(),
                    'name_kurz' => $name_kurz,
                    'voraussetzung' => $modulTeilDeskriptor->voraussetzung,
                    'pruef_leistung' => $modulTeilDeskriptor->pruef_leistung,
                    'pruef_vorleistung' => $modulTeilDeskriptor->pruef_vorleistung,
                    'kommentar' => $modulTeilDeskriptor->kommentar,
                    'kapazitaet' => $modulTeil->kapazitaet,
                    'lvGruppen' => array()
                );

                $lvGruppen = Lvgruppe::findByModulteil($modulTeil->getId());
                foreach ($lvGruppen as $lvGruppe) {
                    $courses = array();
                    foreach ($lvGruppe->getAssignedCoursesBySemester($currentSemester['semester_id']) as $seminar) {
                        $courses[$seminar['seminar_id']] = $seminar;
                    }
                    $modulTeileData[$modulTeil->getId()]['lvGruppen'][$lvGruppe->getId()] = array(
                        'courses' => $courses,
                        'alt_texte' => $lvGruppe->alttext
                    );
                }
            }
            $this->modulTeile = $modulTeileData;
            $this->deskriptor = $modul->getDeskriptor();
            $this->modulVerantwortung = $modulVerantwortung;
            $this->institut = $institut;
            $this->semester = $currentSemester;
            $this->sws = $sws;

            $this->pruef_ebene = $GLOBALS['MVV_MODUL']['PRUEF_EBENE']['values'][$modul->pruef_ebene]['name'];
            $this->modul = $modul;
            $this->type = $type;
            $this->self_url = $this->url_for('modul/show/' . $id);
            $this->detail_url = $this->url_for('modul/detail/' . $id);
            $this->teilnahmeVoraussetzung = $modul->getDeskriptor()->voraussetzung;
        }
    }
    
    public function description_action($id)
    {
        $modul = Modul::find($id);
        $type = 1;
        if (count($modul->modulteile) == 0) {
            $type = 3;
        } else if (count($modul->modulteile) == 1) {
            $type = 2;
        }

        if (!Request::get('sem_select')) {
            $currentSemester = SemesterData::getCurrentSemesterData();
        } else {
            $currentSemester = SemesterData::getSemesterData(Request::get('sem_select'));
        }
        
        $display_language = Request::get('display_language', null);
        
        $modulVerantwortung = array();
        foreach ($modul->assigned_users as $users) {
            foreach ($users as $user) {
                if (!isset($modulVerantwortung[$user->gruppe])) {
                    $modulVerantwortung[$user->gruppe] = array(
                        'name' => $GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'][$user->gruppe]['name'],
                        'users' => array()
                    );
                }
                $modulVerantwortung[$user->gruppe]['users'][$user->user_id] = array(
                    'name' => get_fullname($user->user_id),
                    'id' => $user->user_id
                );
            }
        }

        $modulTeilData = array();
        $nummer_modulteil = 1;
        foreach ($modul->modulteile as $modulTeil) {

            $deskriptor = $modulTeil->getDeskriptor($display_language);
            // Für die Kenntlichmachung der Modulteile in Listen die Nummer des
            // Modulteils und den ausgewählten Namen verwenden.
            // Ist keine Nummer vorhanden, dann Durchnummerieren und Standard-
            // Bezeichnung verwenden.
            if (trim($modulTeil->nummer)) {
                $num_bezeichnung = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$modulTeil->num_bezeichnung]['name'];
                $name_kurz = sprintf('%s %d', $num_bezeichnung, $modulTeil->nummer);
            } else {
                $num_bezeichnung_default = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['default'];
                $name_kurz = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$num_bezeichnung_default]['name']
                        . ' ' . $nummer_modulteil;
                $nummer_modulteil++;
            }
            $modulTeilData[$modulTeil->getId()] = array(
                'lernform' => $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$modulTeil->lernlehrform]['name'],
                'sws' => $modulTeil->sws,
                'name_kurz' => $name_kurz,
                'bezeichnung' => $deskriptor->bezeichnung,
                'anteil_note' => $modulTeil->anteil_note,
                'modulteil' => $modulTeil->getDisplayName(),
                'wl_preasenz' => $modulTeil->wl_praesenz,
                'wl_bereitung' => $modulTeil->wl_bereitung,
                'wl_selbst' => $modulTeil->wl_selbst,
                'wl_pruef' => $modulTeil->wl_pruef,
                'kommentar_wl_preasenz' => $deskriptor->kommentar_wl_praesenz,
                'kommentar_wl_bereitung' => $deskriptor->kommentar_wl_bereitung,
                'kommentar_wl_selbst' => $deskriptor->kommentar_wl_selbst,
                'kommentar_wl_pruef' => $deskriptor->kommentar_wl_pruef,
                'pruef_vorleistung' => $deskriptor->pruef_vorleistung,
                'pruef_leistung' => $deskriptor->pruef_leistung,
                'pflicht' => $modulTeil->pflicht ? _('Ja') : _('Nein'),
                'kommentar_pflicht' => $deskriptor->kommentar_pflicht,
                'kapazitaet' => $modulTeil->kapazitaet,
                'voraussetzung' => $deskriptor->voraussetzung,
                'kommentar_kapazitaet' => $deskriptor->kommentar_kapazitaet,
                'lvGruppen' => array()
            );
            $lvGruppen = Lvgruppe::findByModulteil($modulTeil->getId());

            foreach ($lvGruppen as $lvGruppe) {

                $courses = array();
                foreach ($lvGruppe->getAssignedCoursesBySemester($currentSemester['semester_id']) as $seminar) {

                    $courses[$seminar['seminar_id']] = $seminar;
                }
                $modulTeilData[$modulTeil->getId()]['lvGruppen'][$lvGruppe->getId()] = array(
                    'courses' => $courses,
                    'alt_texte' => $lvGruppe->alttext
                );
            }
        }
        $this->semesterSelector = SemesterData::GetSemesterSelector(null, $currentSemester['semester_id'], 'semester_id', false);
        $this->modul = $modul;
        $this->pruefungsEbene = $GLOBALS['MVV_MODUL']['PRUEF_EBENE']['values'][$modul->pruef_ebene]['name'];
        $this->modulDeskriptor = $modul->getDeskriptor($display_language);
        $this->startSemester = SemesterData::getSemesterData($modul->start);
        if ($modul->responsible_institute) {
            if ($modul->responsible_institute->institute) {
                $this->instituteName = $modul->responsible_institute->institute->getValue('name');
            } else {
                $this->instituteName = _('unbekannte Einrichtung');
            }
        }
        $this->modulVerantwortung = $modulVerantwortung;
        $this->modulTeilData = $modulTeilData;
        $this->type = $type;
        $this->modulTeile = $modul->modulteile;
        $this->modulUser = $modul->assigned_users;
        $this->semester = $currentSemester;
        $this->display_language = $display_language;
    }
    
}
