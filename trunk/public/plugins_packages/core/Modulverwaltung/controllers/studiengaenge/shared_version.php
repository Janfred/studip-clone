<?php
/**
 * shared_version.php - SharedVersionController
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

class SharedVersionController extends MVVController
{

    public function version_action($stgteil_id, $version_id = null)
    {
        $this->stgteil = StudiengangTeil::find($stgteil_id);
        if (!$this->stgteil) {
            throw new Trails_Exception(404);
        }
        if (!MvvPerm::haveFieldPermVersionen($this->stgteil, MvvPerm::PERM_READ)) {
            throw new Trails_Exception(403);
        }
        if (!isset($this->version)) {
            $this->version = StgteilVersion::find($version_id);
            if (!$this->version) {
                if (!MvvPerm::haveFieldPermVersionen($this->stgteil,
                        MvvPerm::PERM_CREATE)) {
                    throw new Trails_Exception(403);
                }
                $this->version = new StgteilVersion();
            }
        }

        if ($this->version->isNew()) {
            $this->version->stat = 'planung';
            PageLayout::setTitle(_('Neue Version des Studiengangteils anlegen'));
            $success_message = ('Die Version "%s" des Studiengangteils wurde angelegt.');
        } else {
            PageLayout::setTitle(_('Version des Studiengangteils bearbeiten'));
            $success_message = _('Die Version "%s" des Studiengangteils wurde geändert.');
        }
        $this->semester = Semester::getAll();
        $this->dokumente = $this->version->document_assignments;
        $this->sessSet('dokument_target', array($this->version->getId(),
            'StgteilVersion'));
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            if (!MvvPerm::haveFieldPermVersionen($this->stgteil)) {
                throw new Trails_Exception(403);
            }
            $stored = false;
            $this->version->stgteil_id = $this->stgteil->getId();
            $this->version->start_sem = Request::option('start_sem');
            $this->version->end_sem = Request::option('end_sem');
            $this->version->code = trim(Request::get('code'));
            $this->version->beschlussdatum =
                    strtotime(trim(Request::get('beschlussdatum')));
            $this->version->fassung_nr = Request::int('fassung_nr');
            $this->version->fassung_typ = Request::option('fassung_typ');
            $this->version->beschreibung = trim(Request::get('beschreibung'));
            $this->version->beschreibung_en = trim(Request::get('beschreibung_en'));
            $this->version->stat = Request::option('status', 'planung');
            $this->version->kommentar_status = trim(Request::get('kommentar_status'));
            Dokument::updateDocuments($this->version,
                    Request::optionArray('dokumente_items'),
                    Request::getArray('dokumente_properties'));
            try {
                $stored = $this->version->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message,
                            htmlReady($this->version->getDisplayName())));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/abschnitte', $this->version->id));
                return;
            }
        }
        $this->search_dokumente =
                Dokument::getQuickSearch($this->dokumente->pluck('dokument_id'));
        $this->cancel_url = $this->url_for('/index');

        $this->setSidebar();
        if (!$this->version->isNew()) {
            $sidebar = Sidebar::Get();
            $action_widget  = $sidebar->getWidget('actions');
            $action_widget->addLink( _('Download der Version'),
                    $this->url_for('/export', $this->version->getId()),
                    Icon::create('file-word', 'clickable'));
            $action_widget->addLink( _('Version als PDF'),
                    $this->url_for('/export', $this->version->getId(), 'pdf'),
                    Icon::create('file-pdf', 'clickable'));
            $action_widget->addLink( _('Vergleich mit anderer Version'),
                    $this->url_for('/diff_select', $this->version->getId()),
                    Icon::create('module', 'clickable'), array('data-dialog' => 'size=auto'));
            if ($this->version->stat == 'planung' && MvvPerm::haveFieldPermStat($this->version)) {
                $action_widget->addLink(_('Version genehmigen'),
                        $this->url_for('/approve', $this->stgteil->getId(), $this->version->getId()),
                        Icon::create('accept', 'clickable'), array('data-dialog' => 'size=auto;buttons=false'));
            }
            $action_widget->addLink( _('Log-Einträge dieser Studiengangteilversion'),
                    $this->url_for('shared/log_event/show/StgteilVersion', $this->version->getId()),
                    Icon::create('log', 'clickable'))->asDialog();
        }

        $this->render_template('studiengaenge/versionen/version', $this->layout);
    }

    public function diff_select_action ($version_id)
    {

        $this->version = StgteilVersion::get($version_id);
        if ($this->version){
           $query = "SELECT version_id, CONCAT(fach.name, ' ', stgt.kp, ' CP ', stgt.zusatz,  ' (', code, ')')"
                    . 'as name FROM mvv_stgteilversion stgtv '
                    . 'LEFT JOIN mvv_stgteil stgt USING(stgteil_id) '
                    . 'LEFT JOIN fach fach USING(fach_id) '
                    . 'WHERE ( '
                    . 'fach.name LIKE :input '
                    . 'OR fach.name_en LIKE :input '
                    . 'OR code LIKE :input '
                    . 'OR stgt.zusatz LIKE :input '
                    . 'OR stgt.zusatz_en LIKE :input '
                    . ')'
                    .'AND stgtv.version_id <> '
                    . DBManager::get()->quote($this->version->getId())
                    . ' ORDER BY name ASC';
            $this->search_version =
                    QuickSearch::get('old_id', new MvvQuickSearch($query, _('Version suchen'),
                            'version'))
                    ->setInputStyle('width: 240px')
                    ->fireJSFunctionOnSelect('MVV.Search.getFocus')
                    ->render();

            if (Request::isXhr()) {
                $this->render_template('studiengaenge/versionen/diff_select');
            }
        } else {
            PageLayout::postError( _('Unbekannte Version!'));
            $this->redirect('studiengaenge/versionen');
        }
    }

    public function diff_action ($new_id = null, $old_id = null)
    {
        $new_version = StgteilVersion::find(Request::option('new_id', $new_id));
        $old_version = StgteilVersion::find(Request::option('old_id', $old_id));
       // var_dump($new_version, $old_version); exit;
        if (!$new_version || !$old_version) {
            if ($new_version) {
                PageLayout::postError( _('Unbekannte Version!'));
                $this->redirect($this->url_for('/diff_select', $new_version->id));
            } else {
                PageLayout::postError( _('Unbekannte Version!'));
                $this->relocate('studiengaenge/versionen/index');
            }
        } else {
            /*
            if (Request::isXhr()) {
                $this->redirect($this->url_for('/diff', $new_version->id,
                        $old_version->id));
            }
             *
             */

            PageLayout::addStylesheet($this->plugin->getPluginURL() . '/public/stylesheets/mvv_difflog.css');
            PageLayout::addScript($this->plugin->getPluginURL() . '/public/javascripts/mvv_difflog.js');

            PageLayout::addStylesheet('print.css');
            $factory = $this->get_template_factory();
            $template = $factory->open('studiengaenge/versionen/diff');
            $template->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
            $template->set_attributes(
                    array(
                        'new_version' => $new_version,
                        'old_version' => $old_version,
                        'plugin'   => $this->plugin
                    ));

            $this->render_text($template->render());
        }
    }

    public function export_action ($version_id = null, $type = null)
    {
        $version = StgteilVersion::find($version_id ?: Request::option('version_id'));
        $type = Request::option('type', $type);

        if (!$version) {
           PageLayout::postError( _('Unbekannte Version!'));
           $this->response->add_header('X-Location', $this->url_for('/'));
        } else {
            if (Request::isXhr()) {
                $this->response->add_header('X-Location', $this->url_for('/export', $version->id));
            }

            PageLayout::addStylesheet('print.css');
            $factory = $this->get_template_factory();
            $template = $factory->open('studiengaenge/versionen/export');
            $template->set_attributes(
                    array(
                            'stgversion' => $version,
                            'plugin'   => $this->plugin
                    ));

            if ($type == 'pdf') {
                $template->set_attribute('image_style', 'height: 6px; width: 8px;');

                $doc = new ExportPDF();

                $doc->addPage();
                $doc->SetFont('helvetica', '', 8);
                $doc->writeHTML($template->render(), false, false, true);

                $doc->Output($version->getDisplayName() . '.pdf', 'D');

                $this->render_nothing();
            } else {
                $content = studip_utf8encode($template->render());
                $this->response->add_header('Content-type', 'application/msword');
                $this->response->add_header('Content-Disposition', 'attachment; filename="' . $version->getDisplayName() . '.doc"');
                $this->render_text($content);
            }
        }
    }

    public function delete_version_action($version_id)
    {
        $version = StgteilVersion::find($version_id);
        if (!$version) {
             throw new Trails_Exception(404, _('Unbekannte Version'));
        }
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            PageLayout::postSuccess(sprintf(_('Version "%s" des Studiengangteils gelöscht!'),
                    htmlReady($version->getDisplayName())));
            $version->delete();
        }
        $this->redirect($this->url_for('/index'));
    }

    public function abschnitt_action($abschnitt_id = null)
    {
        if (!isset($this->abschnitt)) {
            $this->abschnitt = StgteilAbschnitt::find($abschnitt_id);
        }
        if (!$this->abschnitt) {
            $this->abschnitt = new StgteilAbschnitt();
            if (!isset($this->version)) {
                $this->version = StgteilVersion::find(Request::option('version_id'));
            }
            PageLayout::setTitle(_('Neuen Studiengangteil-Abschnitt anlegen'));
            $success_message = ('Der Studiengangteil-Abschnitt "%s" wurde angelegt.');
        } else {
            $this->version = $this->abschnitt->version;
            PageLayout::setTitle(_('Studiengangteil-Abschnitt bearbeiten'));
            $success_message = _('Der Studiengangteil-Abschnitt "%s" wurde geändert.');
        }
        if (!$this->version) {
            PageLayout::postError(_('Unbekannte Version.'));
            $this->redirect($this->url_for('/index'));
        }
        $perm = MvvPerm::get($this->version);
        if (!$perm->haveFieldPerm('abschnitte', MvvPerm::PERM_READ)) {
            throw new Trails_Exception(403);
        }
        if ($this->abschnitt->isNew()
                && !$perm->haveFieldPerm('abschnitte', MvvPerm::PERM_CREATE)) {
            throw new Trails_Exception(403);
        }
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            if (!$perm->haveFieldPerm('abschnitte', MvvPerm::PERM_WRITE)) {
                throw new Trails_Exception(403);
            }
            $stored = false;
            $this->abschnitt->version_id = $this->version->getId();
            $this->abschnitt->name = trim(Request::get('name'));
            $this->abschnitt->name_en = trim(Request::get('name_en'));
            $this->abschnitt->kommentar = trim(Request::get('kommentar'));
            $this->abschnitt->kommentar_en = trim(Request::get('kommentar_en'));
            $this->abschnitt->kp = trim(Request::int('kp'));
            $this->abschnitt->ueberschrift = trim(Request::get('ueberschrift'));
            $this->abschnitt->ueberschrift_en = trim(Request::get('ueberschrift_en'));
            try {
                $this->abschnitt->verifyPermission();
                $stored = $this->abschnitt->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message,
                            htmlReady($this->abschnitt->name)));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/details',
                        $this->abschnitt->id));
                return;
            }
        }
        $this->cancel_url = $this->url_for('/details', $this->version->id);
        $this->render_template('studiengaenge/versionen/abschnitt');
    }

    protected function abschnitte($version_id)
    {
        $this->version = StgteilVersion::find($version_id);

        $query = "SELECT mm.modul_id, CONCAT(mmd.bezeichnung, ', ',"
                . "IF(ISNULL(mm.code), '', mm.code),"
                . "IF(ISNULL(sd1.name), '', CONCAT(', ', IF(ISNULL(sd2.name),"
                . "CONCAT('ab ', sd1.name),CONCAT(sd1.name, ' - ', sd2.name))))) AS modul_name "
                . 'FROM mvv_modul mm LEFT JOIN mvv_modul_deskriptor mmd '
                . "ON mm.modul_id = mmd.modul_id AND mmd.sprache = '"
                . $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['default']
                . "' LEFT JOIN semester_data sd1 ON mm.start = sd1.semester_id "
                . 'LEFT JOIN semester_data sd2 ON mm.end = sd2.semester_id '
                . "WHERE (mm.code LIKE :input "
                . 'OR mmd.bezeichnung LIKE :input) '
                . "ORDER BY modul_name";
        $search = new SQLSearch($query, _('Modul suchen'),
                'modul_id_' . $this->version->id);
        $this->qs_search_modul_version_id = md5(serialize($search));
        $this->search_modul_version =
                QuickSearch::get('modul_id_' . $this->version->id, $search);

        if (!$this->version) {
            PageLayout::postError( _('Unbekannte Version.'));
            $this->redirect($this->url_for('/index'));
            return;
        } else {
            $this->version_id = $this->version->id;
            $this->abschnitte = $this->version->abschnitte;
        }
    }

    public function abschnitte_action($version_id)
    {
        $this->abschnitte($version_id);
        if (Request::isXhr()) {
            $this->render_template('studiengaenge/versionen/abschnitte');
        } else {
            $this->stgteil = $this->version->studiengangteil;
            $this->stgteil_id = $this->stgteil->id;
            $this->perform_relayed('index');
        }
    }

    public function delete_abschnitt_action($abschnitt_id, $version_id = null)
    {
        $abschnitt = StgteilAbschnitt::find($abschnitt_id);
        if (!$abschnitt && is_null($version_id)) {
            PageLayout::postError( _('Unbekannter Studiengangteil-Abschnitt'));
            $this->redirect($this->url_for('/index'));
            return;
        } else {
            $version_id = $abschnitt->version_id;
            if (!Request::isPost()) {
                $this->flash_set('dialog',
                    sprintf(_('Wollen Sie den Studiengangteil-Abschnitt "%s" wirklich löschen?'),
                        $abschnitt->getDisplayName()),
                        array('/delete_abschnitt', $abschnitt->id, $version_id),
                        array('/details_abschnitt', $abschnitt->id));
            }
            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                $abschnitt_name = $abschnitt->getDisplayName();
                if ($abschnitt->delete()) {
                    PageLayout::postSuccess(
                        sprintf(_('Der Studiengangteil-Abschnitt "%s" wurde glöscht.'),
                                htmlReady($abschnitt_name)));
                } else {
                    PageLayout::postError( sprintf(
                            _('Der Studiengangteil-Abschnitt "%s" konnte nicht gelöscht werden.'),
                            htmlReady($abschnitt_name)));
                }
            }
        }
        if ($abschnitt && !Request::submitted('yes')) {
            $this->redirect($this->url_for('/details_abschnitt', $abschnitt->getId()));
        } else {
            $this->redirect($this->url_for('/abschnitte', $version_id));
        }
    }

    public function add_modul_action($version_id)
    {
        $version = StgteilVersion::find($version_id);
        if ($version) {
            $this->version_id = $version->id;
            $abschnitt = StgteilAbschnitt::find(Request::option('abschnitt_id'));
            $modul = Modul::find(
                    Request::option('modul_id_' . $abschnitt->id)
                            ? Request::option('modul_id_' . $abschnitt->id)
                            : Request::option('modul_id_' . $version->id));
            if (Request::submitted('add_modul')) {
                CSRFProtection::verifyUnsafeRequest();
                if (!$modul) {
                    PageLayout::postError( _('Unbekanntes Modul.'));
                    $this->redirect($this->url_for('/details_abschnitt',
                            $abschnitt->id));
                    return;
                } else if ($abschnitt) {
                    if ($abschnitt->addModul($modul)) {
                        try {
                            $abschnitt->store();
                            PageLayout::postSuccess(
                                sprintf(_('Das Modul "%s" wurde dem Abschnitt "%s" hinzugefügt.'),
                                        htmlReady($modul->getDisplayName()),
                                        htmlReady($abschnitt->name)));
                        } catch (InvalidValuesException $e) {
                            PageLayout::postError(htmlReady($e->getMessage()));
                        }
                    } else {
                        PageLayout::postError(
                                sprintf(_('Das Modul "%s" wurde bereits zugordnet.'),
                                        htmlReady($modul->getDisplayName())));
                    }
                    $this->redirect($this->url_for('/details_abschnitt', $abschnitt->id));
                } else {
                    PageLayout::postError( _('Unbekannter Abschnitt.'));
                    $this->redirect($this->url_for('/index'));
                }
            } else {
                if ($abschnitt) {
                    $this->redirect($this->url_for('/details_abschnitt', $abschnitt->id));
                } else {
                    $this->redirect($this->url_for('/abschnitte', $version->id));
                }
            }
        } else {
            PageLayout::postError( _('Unbekannte Version.'));
            $this->redirect($this->url_for('/index'));
        }
    }

    public function modul_zuordnung_action($abschnitt_id, $modul_id)
    {
        $this->abschnitt = StgteilAbschnitt::find($abschnitt_id);
        if ($this->abschnitt) {
            $this->modul = Modul::find($modul_id);
            if (!$this->modul) {
                PageLayout::postError( _('Unbekanntes Modul.'));
                $this->redirect($this->url_for('/index'));
                return;
            } else {
                $this->zuordnung =
                        StgteilabschnittModul::find(array($this->abschnitt->getId(),
                            $this->modul->getId()));
                if ($this->zuordnung->isNew()) {
                    PageLayout::postError( _('Unbekannte Zuordnung.'));
                    $this->redirect($this->url_for('/index'));
                    return;
                } else {
                    PageLayout::setTitle(_('Modulzuordnung bearbeiten'));
                    $success_message = _('Die Modulzuordnung "%s" wurde geändert.');
                }
                $success = false;
                if (Request::submitted('store')) {
                    CSRFProtection::verifyUnsafeRequest();
                    $stored = false;
                    $this->zuordnung->bezeichnung =
                            trim(Request::get('bezeichnung'));
                    $this->zuordnung->flexnow_modul =
                            trim(Request::get('flexnow_modul'));
                    $this->zuordnung->modulcode =
                            trim(Request::get('modulcode'));
                    try {
                        $stored = $this->zuordnung->store();
                    } catch (InvalidValuesException $e) {
                        PageLayout::postError(htmlReady($e->getMessage()));
                    }
                    if ($stored !== false) {
                        $success = true;
                        if (!Request::isXhr()) {
                            if ($stored) {
                                PageLayout::postSuccess(sprintf(
                                        $success_message,
                                        htmlReady($this->zuordnung->getDisplayName())));
                            } else {
                                PageLayout::postInfo(
                                        _('Es wurden keine Änderungen vorgenommen.'));
                            }
                            $this->redirect($this->url_for('/details_abschnitt',
                                    $this->abschnitt->getId()));
                        }
                    }
                }
                if (Request::isXhr()) {
                    if ($success) {
                        $this->details_abschnitt_action($this->abschnitt->getId());
                    } else {
                        $this->render_template('studiengaenge/versionen/modul_zuordnung');
                    }
                }
            }
        }
    }

    public function delete_modul_action($abschnitt_id, $modul_id)
    {
        $abschnitt = StgteilAbschnitt::find($abschnitt_id);
        if ($abschnitt) {
            if (!MvvPerm::haveFieldPermModul_zuordnungen(
                    $abschnitt, MvvPerm::PERM_CREATE)) {
                throw new Trails_Exception(403);
            }
            $modul = Modul::find($modul_id);
            if (!$modul) {
                PageLayout::postError( _('Unbekanntes Modul.'));
            } else {
                if (!Request::isPost()) {
                    $this->flash_set('dialog',
                        sprintf(_('Wollen Sie die Zuordnung des Moduls "%s" zum Studiengangteil-Abschnitt "%s" wirklich löschen?'),
                            $modul->getDisplayName(),
                            $abschnitt->getDisplayName()),
                        array('/delete_modul', $abschnitt->id,
                            $modul->getId()),
                        array('/details_abschnitt', $abschnitt->id));
                }
                if (Request::submitted('yes')) {
                    CSRFProtection::verifyUnsafeRequest();
                    if ($abschnitt->removeModul($modul)) {
                        $modul_name = $modul->getDisplayName();
                        $abschnitt_name = $abschnitt->getDisplayName();
                        try {
                            $stored = $abschnitt->store();
                            PageLayout::postSuccess(
                                sprintf(_('Die Zuordnung des Moduls "%s" zum Studiengangteil-Abschnitt "%s" wurde gelöscht.'),
                                        htmlReady($modul_name), htmlReady($abschnitt_name)));
                        } catch (InvalidValuesException $e) {
                            PageLayout::postError(htmlReady($e->getMessage()));
                        } catch (Exception $e) {
                            PageLayout::postError( _('Beim Speichern trat ein Fehler auf!'));
                        }
                    } else {
                        PageLayout::postError( _('Die Zuordnung des Moduls konnte nicht gelöscht werden.'));
                    }
                }
            }
            $this->redirect($this->url_for('/details_abschnitt', $abschnitt->id));
        } else {
            PageLayout::postError(
                    _('Unbekannter Studiengangteilabschnitt.'));
            $this->redirect('/index');
        }
    }

    public function modulteile_action($abschnitt_id, $modul_id)
    {
        $this->assignment = StgteilabschnittModul::find(
                array($abschnitt_id, $modul_id));
        if ($this->assignment) {
            $this->abschnitt_id = $abschnitt_id;
            $this->modul = $this->assignment->modul;
            if (!Request::isXhr()) {
                $this->modul_id = $this->assignment->modul->id;
                $this->abschnitt_id = $this->assignment->abschnitt->id;
                $this->abschnitte = $this->assignment->abschnitt->version->abschnitte;
                $this->version_id = $this->assignment->abschnitt->version->id;
                $this->stgteil_id = $this->assignment->abschnitt->version->studiengangteil->id;
                $this->perform_relayed('index');
                return;
            }
        }
        $this->render_template('studiengaenge/versionen/modulteile');
    }

    public function modulteil_semester_action($abschnitt_id, $modulteil_id)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if ($this->modulteil) {
            $this->abschnitt_modul = StgteilabschnittModul::find(
                    [$abschnitt_id, $this->modulteil->modul_id]);
            if ($this->abschnitt_modul) {
                if (Request::submitted('store')) {
                    CSRFProtection::verifyUnsafeRequest();
                    $fachsem = $this->abschnitt_modul->getAllFachsemester(
                            $this->modulteil->id);
                    $status = Request::optionArray('status');
                    $is_modified = false;
                    foreach (array_keys(Request::intArray('fachsemester')) as $i) {
                        if ($fachsem[$i]) {
                            $fachsem[$i]->differenzierung = $status[$i];
                            $is_modified = $fachsem[$i]->isDirty();
                            $fachsem[$i]->store();
                            $fachsem[$i] = null;
                        } else {
                            $new_fachsem = new ModulteilStgteilabschnitt();
                            $new_fachsem->setId([$this->modulteil->id,
                                        $this->abschnitt_modul->abschnitt_id, $i]);
                            $new_fachsem->differenzierung = $status[$i];
                            $new_fachsem->store();
                            $is_modified = true;
                        }
                    }
                    foreach ($fachsem as $del_fachsem) {
                        if (!is_null($del_fachsem)) {
                            $del_fachsem->delete();
                            $is_modified = true;
                        }
                    }
                    if ($is_modified) {
                        PageLayout::postSuccess(
                                sprintf(_('Die Zuordnung der Fachsemester zum Modulteil "%s" im Abschnitt "%s" wurde geändert.'),
                                htmlReady($this->modulteil->getDisplayName()),
                                htmlReady($this->abschnitt_modul->abschnitt->getDisplayName())));
                    } else {
                        PageLayout::postInfo(
                                _('Es wurden keine Änderungen an der Zuordnung der Fachsemester vorgenommen.'));
                    }
                    /*$this->relocate('/modulteile',
                            $this->abschnitt_modul->abschnitt_id,
                            $this->abschnitt_modul->modul_id);*/
                    $this->relocate('/index');
                    return;
                }
                $this->render_template('studiengaenge/versionen/modulteil_semester', $this->layout);
            } else {
                $this->render_nothing();
            }
        } else {
            $this->render_nothing();
        }
    }

    public function dokumente_properties_action($dokument_id)
    {
        $target = $this->sessGet('dokument_target');
        if ($target) {
            $this->redirect('materialien/dokumente/ref_properties/' . $dokument_id
                . '/' . join('/', $target));
        }
    }

    /**
     * copy a version
     */
    public function copy_action($version_id)
    {
        $version = StgteilVersion::find($version_id);
        if (!$version) {
             throw new Trails_Exception(404, _('Unbekannte Version'));
        } else {
            if (Request::isPost()) {
                CSRFProtection::verifyUnsafeRequest();
                $version->copy();
                PageLayout::postSuccess(sprintf(_('Version "%s" des Studiengangteils kopiert!'),
                        htmlReady($version->getDisplayName())));
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * sorts studiengaenge
     */
    public function sort_action()
    {
        list($object_class, $id) = explode('_', Request::option('list_id'));
        $orderedIds = Request::getArray('newOrder');
        if (is_array($orderedIds)) {
            if ($object_class == 'abschnitte') {
                $version = StgteilVersion::find($id);
                if ($version) {
                    $i = 1;
                    foreach ($orderedIds as $abschnitt_id) {
                        $abschnitt = StgteilAbschnitt::find($abschnitt_id);
                        if ($abschnitt) {
                            if ($abschnitt->position != $i) {
                                $abschnitt->position = $i;
                                $abschnitt->store();
                            }
                            $i++;
                        }
                    }
                }
            } else if ($object_class == 'module') {
                $abschnitt = StgteilAbschnitt::find($id);
                if ($abschnitt) {
                    $i = 1;
                    foreach ($orderedIds as $modul_zuord_id) {
                        $modul_id = mb_substr($modul_zuord_id,
                                mb_strpos($modul_zuord_id, '_') + 1);
                        $abschnitt_modul = StgteilabschnittModul::find(
                                array($abschnitt->getId(), $modul_id));
                        if ($abschnitt_modul) {
                            if ($abschnitt_modul->position != $i) {
                                $abschnitt_modul->position = $i;
                                $abschnitt_modul->store();
                            }
                            $i++;
                        }
                    }
                }
            }
        }
        $this->set_status(200);
        $this->render_nothing();
    }

    public function details_abschnitt_action($abschnitt_id)
    {
        $this->abschnitt = StgteilAbschnitt::find($abschnitt_id);
        if (!$this->abschnitt) {
            PageLayout::postError( _('Unbekannter Abschnitt.'));
            $this->redirect($this->url_for('/index'));
            return;
        }
        $this->abschnitt_id = $this->abschnitt->id;
        if(!$this->version) {
            $this->version = $this->abschnitt->getVersion();
        }
        $this->version_id = $this->version->getId();
        $this->assignments = StgteilabschnittModul::findByStgteilAbschnitt(
                $this->abschnitt->getId(),
                $this->filter);
        $query = "SELECT mvv_modul.modul_id, CONCAT(mvv_modul_deskriptor.bezeichnung, ', ',"
                . "IF(ISNULL(mvv_modul.code), '', mvv_modul.code),"
                . "IF(ISNULL(start_sem.name), '', CONCAT(', ', IF(ISNULL(end_sem.name),"
                . "CONCAT('ab ', start_sem.name),CONCAT(start_sem.name, ' - ', end_sem.name))))) AS modul_name "
                . 'FROM mvv_modul LEFT JOIN mvv_modul_deskriptor '
                . "ON mvv_modul.modul_id = mvv_modul_deskriptor.modul_id AND mvv_modul_deskriptor.sprache = '"
                . $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['default']
                . "' LEFT JOIN semester_data start_sem ON mvv_modul.start = start_sem.semester_id "
                . 'LEFT JOIN semester_data end_sem ON mvv_modul.end = end_sem.semester_id '
                . "WHERE (mvv_modul.code LIKE :input "
                . 'OR mvv_modul_deskriptor.bezeichnung LIKE :input) '
                . 'AND mvv_modul.modul_id NOT IN(SELECT msm.modul_id FROM '
                . 'mvv_stgteilabschnitt_modul msm WHERE abschnitt_id = '
                . DBManager::get()->quote($this->abschnitt_id)
                . ') ' . ModuleManagementModel::getFilterSql($this->filter)
                . ' ORDER BY modul_name';
        $search = new SQLSearch($query, _('Modul suchen'));
        $this->qs_search_modul_abschnitt_id = md5(serialize($search));
        $this->search_modul_abschnitt =
                QuickSearch::get('modul_id_' . $this->abschnitt->id, $search)
                ->setInputStyle('width: 240px')
                ->fireJSFunctionOnSelect('MVV.Search.getFocus');
        if (Request::isXhr()) {
            $this->render_template('studiengaenge/versionen/details_abschnitt');
        } else {
            $this->abschnitte_action($this->version_id);
        }
    }

    public function approve_action($version_id)
    {
        $this->version = StgteilVersion::get($version_id);
        $this->stgteil_id = $this->version->stgteil_id;
        $this->version_id = $this->version->id;

        if (Request::submitted('approval')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($this->version->stat == 'planung'
                    && MvvPerm::haveFieldPermStat($this->version)) {
                $stored = false;
                $this->version->stat = 'genehmigt';
                try {
                    $stored = $this->version->store(false);
                } catch (InvalidValuesException $e) {
                    PageLayout::postError(htmlReady($e->getMessage()));
                }
                if ($stored) {
                    PageLayout::postSuccess(sprintf(_('Version "%s" genehmigt!'),
                            htmlReady($this->version->getDisplayName())));
                    $this->redirect($this->url_for('/abschnitte', $version_id));
                }
            } else {
                throw new Trails_Exception(403);
            }
        }
        if (Request::isXhr()) {
            $this->render_template('studiengaenge/versionen/approve');
        }
    }

}
