<?php
/**
 * module.php - Module_ModuleController
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
require_once 'lib/classes/exportdocument/ExportPDF.class.php';

class Module_ModuleController extends MVVController
{

    public $filter = array();
    protected $show_sidebar_search = false;
    protected $show_sidebar_filter = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/module/module');
        $this->filter = $this->sessGet('filter', array());
        $this->action = $action;
        if (Request::isXhr()) {
            $this->response->add_header('Content-Type',
                    'text/html; charset=WINDOWS-1252');
            $this->set_layout(null);
        }
    }

    public function index_action()
    {
        //set title
        PageLayout::setTitle(_('Verwaltung der Module - Alle Module'));

        $this->initPageParams();
        $this->initSearchParams('module');
        $search_result = $this->getSearchResult('Modul');

        // set default semester filter
        if (!$this->filter['start_sem.beginn']
                || !$this->filter['end_sem.ende']) {
            $sem_time_switch = Config::get()->getValue('SEMESTER_TIME_SWITCH');
            // switch semester according to time switch
            // (n weeks before next semester)
            $current_sem = Semester::findByTimestamp(time()
                    + $sem_time_switch * 7 * 24 * 3600);
            if ($current_sem) {
                $this->filter['start_sem.beginn'] = $current_sem->beginn;
                $this->filter['end_sem.ende'] = $current_sem->beginn;
            }
        }

        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $filter = array_merge(
                array(
                    'mvv_modul.modul_id' => $search_result,
                    'mvv_modul_inst.institut_id' => MvvPerm::getOwnInstitutes()),
                (array) $this->filter);

        //get data
        $this->module = Modul::getAllEnriched(
                $this->sortby ?: 'bezeichnung,chdate',
                $this->order ?: 'DESC',
                self::$items_per_page,
                self::$items_per_page * (($this->page ?: 1) - 1),
                $filter);

        if (!empty($this->filter)) {
            $this->search_result['Modul'] = $this->module->pluck('id');
        }

        if (sizeof($this->module) == 0) {
            if (sizeof($this->filter) || $this->search_term) {
                PageLayout::postInfo(_('Es wurden keine Module gefunden.'));
            } else {
                PageLayout::postInfo(_('Es wurden noch keine Module angelegt.'));
            }
        }
        $this->count = Modul::getCount($filter);
        $this->show_sidebar_search = true;
        $this->show_sidebar_filter = true;
        $this->setSidebar();
    }

    public function modul_action($modul_id = null, $institut_id = null)
    {
        $own_institutes = MvvPerm::getOwnInstitutes();

        if (!isset($this->modul)) {
            $this->modul = Modul::find($modul_id);
            if (!$this->modul) {
                $this->modul = new Modul();
                $this->modul->setNewId();
            }
        }
        if ($institut_id) {
            $institut = new Institute($institut_id);
            if (!$institut->isNew()) {
                $this->institut_id = $institut->getId();
            }
        }

        $this->setSidebar();
        $sidebar = Sidebar::get();

        if ($this->modul->isNew()) {
            PageLayout::setTitle(_('Neues Modul anlegen'));
            $success_message = ('Das Modul "%s" wurde angelegt.');
            $sprache = $this->modul->getDefaultLanguage();
            $this->deskriptor = $this->modul->getDeskriptor($sprache, true);
            $this->reset_search('Modul');

            $helpbar = Helpbar::get();
            $widget = new HelpbarWidget();
            $widget->addElement(new WidgetElement(sprintf(_('Sie legen ein neues Modul an. Das Modul muss zunächst in der Ausgabesprache <em>%s</em> angelegt werden.'),
                        $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$sprache]['name'])));
            $helpbar->addWidget($widget);
        } else {
            PageLayout::setTitle(_('Modul bearbeiten'));
            $success_message = _('Das Modul "%s" wurde geändert.');
            $sprache = Request::option('display_language',
                    $this->modul->getDefaultLanguage());
            $this->deskriptor = $this->modul->getDeskriptor($sprache, true);

            // sidebar widget for selecting language
            $template_factory = $this->get_template_factory();
            $sidebar_template =  $template_factory->render('shared/deskriptor_language', array(
                    'modul' => $this->modul,
                    'sprache' => $sprache,
                    'link' => $this->url_for('/modul', $this->modul->id, $this->institut_id),
                    'url' => $this->url));

            $widget  = new SidebarWidget();
            $widget->setTitle(_('Ausgabesprache'));
            $widget->addElement(new WidgetElement($sidebar_template));
            $sidebar->addWidget($widget, 'language');

            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(_('Download der Modulbeschreibung'),
                $this->url_for('module/download/details', $this->modul->id, $sprache),
                Icon::create('file-word', 'clickable'));
            $action_widget->addLink( _('Modulbeschreibung als PDF'),
                $this->url_for('module/download/details/' . $this->modul->id, ['pdf' => '1']),
                Icon::create('file-pdf', 'clickable'));
            $action_widget->addLink( _('Vergleich mit anderem Modul'),
                $this->url_for('/diff_select', $this->modul->id),
                Icon::create('learnmodule', 'clickable'), ['data-dialog' => 'size=auto']);

            if ($this->modul->stat == 'planung' && MvvPerm::haveFieldPermStat($this->modul)) {
                $action_widget->addLink(_('Modul genehmigen'),
                $this->url_for('/approve', $this->modul->id),
                Icon::create('accept', 'clickable'), ['data-dialog' => 'size=auto;buttons=false']);
            }

            $action_widget->addLink( _('Log-Einträge dieses Moduls'),
                $this->url_for('shared/log_event/show/Modul/' . $this->modul->id,
                        ['object2_type' => 'ModulDeskriptor', 'object2_id' => $this->deskriptor->id]),
                Icon::create('log', 'clickable'))->asDialog();

            // list all variants
            $variants = $this->modul->getVariants();
            if (sizeof($variants)) {
                $widget = new SidebarWidget();
                $widget->setTitle(_('Varianten'));
                $widget->addElement(new WidgetElement(
                    $template_factory->render('shared/modul_variants',
                        array(
                            'variants' => $variants,
                            'link' => $this->url_for('/modul'
                        )))));
                $sidebar->addWidget($widget, 'variants');
            }
        }
        $this->semester = Semester::getAll();
        $this->def_lang =
                $this->deskriptor->sprache == $this->modul->getDefaultLanguage();

        if (!$this->def_lang) {
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(_('Alle Texte der Originalfassung anzeigen'),
                '#', Icon::create('consultation', 'clickable'),
                array('class' => 'mvv-show-all-original'));

            // transfer some values from default descriptor
            if ($this->deskriptor->isNew()) {
                $default_deskriptor = $this->modul
                        ->getDeskriptor($this->modul->getDefaultLanguage());
                $this->deskriptor->verantwortlich
                        = $default_deskriptor->verantwortlich;
            }
        }

        $this->display_language = $sprache;
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            if ($this->def_lang) {
                $this->modul->quelle = Request::option('quelle');
                $this->modul->variante = Request::option('modul_item');
                $this->modul->flexnow_modul = trim(Request::get('flexnow_modul'));
                $this->modul->code = trim(Request::get('code'));
                $this->modul->start = Request::option('start');
                $this->modul->end = Request::option('end');
                $this->modul->beschlussdatum = strtotime(trim(Request::get('beschlussdatum')));
                $this->modul->assignLanguagesOfInstruction(
                        Request::optionArray('language_items'));
                $this->modul->dauer = trim(Request::get('dauer'));
                if (Request::get('kap_unbegrenzt')) {
                    $this->modul->kapazitaet = '';
                } else {
                    $kapazitaet = trim(Request::get('kapazitaet'));
                    $this->modul->kapazitaet =
                            $kapazitaet === '' ? null : $kapazitaet;
                }
                $this->modul->kp = Request::int('kp');
                $this->modul->wl_selbst = Request::int('wl_selbst');
                $this->modul->wl_pruef = Request::int('wl_pruef');
                $this->modul->pruef_ebene = Request::option('pruef_ebene');
                $this->modul->faktor_note = StringToFloat(
                        trim(Request::get('faktor_note')));
                $this->modul->stat = Request::option('status',
                        $GLOBALS['MVV_MODUL']['STATUS']['default']);
                $this->modul->kommentar_status = trim(Request::get('kommentar_status'));
                $this->modul->assignInstitutes(Request::optionArray('institutes_items'));
                $this->modul->assignResponsibleInstitute(Request::option('responsible_item'));
                $this->modul->fassung_nr = Request::int('fassung_nr');
                $this->modul->fassung_typ = Request::option('fassung_typ');
                $this->modul->version = trim(Request::get('version'));
                foreach ($GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'] as $key => $foo) {
                    $grouped_users[$key] = Request::optionArray('users_items_' . $key);
                }
                $this->modul->assignUsers($grouped_users);
                $this->modul->verantwortlich = trim(Request::get('verantwortlich'));
            }

            // Deskriptor
            $this->deskriptor->bezeichnung = trim(Request::get('bezeichnung'));
            $this->deskriptor->verantwortlich = trim(Request::get('verantwortlich'));
            $this->deskriptor->voraussetzung = trim(Request::get('voraussetzung'));
            $this->deskriptor->kompetenzziele = trim(Request::get('kompetenzziele'));
            $this->deskriptor->inhalte = trim(Request::get('inhalte'));
            $this->deskriptor->literatur = trim(Request::get('literatur'));
            $this->deskriptor->links = trim(Request::get('links'));
            $this->deskriptor->kommentar = trim(Request::get('kommentar'));
            $this->deskriptor->turnus = trim(Request::get('turnus'));
            $this->deskriptor->kommentar_kapazitaet = trim(Request::get('kommentar_kapazitaet'));
            $this->deskriptor->kommentar_wl_selbst = trim(Request::get('kommentar_wl_selbst'));
            $this->deskriptor->kommentar_wl_pruef = trim(Request::get('kommentar_wl_pruef'));
            $this->deskriptor->kommentar_sws = trim(Request::get('kommentar_sws'));
            $this->deskriptor->kommentar_note = trim(Request::get('kommentar_note'));
            $this->deskriptor->pruef_vorleistung = trim(Request::get('pruef_vorleistung'));
            $this->deskriptor->pruef_leistung = trim(Request::get('pruef_leistung'));
            $this->deskriptor->pruef_wiederholung = trim(Request::get('pruef_wiederholung'));
            $this->deskriptor->ersatztext = trim(Request::get('ersatztext'));

            // workaround because SimpleORMap::store()
            // doesn't count changed (dirty) related Objects
            $is_dirty = $this->deskriptor->isDirty() || $this->modul->isDirty();


            // update datafields
            foreach (Request::getArray('datafields') as $df_key => $df_value) {
                $df = $this->deskriptor->datafields->findOneBy('datafield_id', $df_key);
                if ($df) {
                    $df->content = $df_value;
                }
            }
            if (Request::submitted('store')) {
                try {
                        $this->modul->verifyPermission();
                        $stored = $this->modul->store();
                } catch (InvalidValuesException $e) {
                    PageLayout::postError(htmlReady($e->getMessage()));
                }
                if ($stored !== false) {

                    PageLayout::postSuccess(sprintf($success_message,
                        htmlReady($this->modul->getDisplayName())));

                    //the if-else block was deactivated since it can't tell
                    //for sure if something was changed or not.

                    /*
                    if ($stored || $is_dirty) {
                        PageLayout::postSuccess(sprintf($success_message,
                                htmlReady($this->modul->getDisplayName())));
                    }
                    else {

                        PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                    }*/
                    $this->redirect($this->url_for('/index'));
                    return;
                }
            }
        }

        $query = "SELECT modul_id, CONCAT(mmd.bezeichnung, ' (', code, ')')"
            . 'as name FROM mvv_modul mm '
            . 'LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id) '
            . 'WHERE mmd.sprache = '
            . DBManager::get()->quote($this->modul->getDefaultLanguage())
            . ' AND (code LIKE :input OR mmd.bezeichnung LIKE :input) '
            . 'AND mm.modul_id <> '
            . DBManager::get()->quote($this->modul->id ?: '')
            . ' ORDER BY name ASC';
        $sql_search_modul = new SQLSearch($query, _('Modul suchen'));
        $this->qs_id_module = md5(serialize($sql_search_modul));
        $this->search_modul =
                QuickSearch::get('modul', $sql_search_modul)
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();


        $sql_search_user = new PermissionSearch('user', _('Person suchen'),
                'user_id',
                ['permission' => words('tutor dozent admin'), 'exclude_user' => '']);
        $this->qs_id_users = md5(serialize($sql_search_user));
        $qs_users = QuickSearch::get('users', $sql_search_user);
        $this->qs_frame_id_users = $qs_users->getId();
        $this->search_users = $qs_users
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();

        $query = 'SELECT DISTINCT Institute.Institut_id, Institute.Name AS name '
                . 'FROM Institute '
                . 'LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id) '
                . 'WHERE (Institute.Name LIKE :input '
                . 'OR range_tree.name LIKE :input ) ';
        if (count($own_institutes)) {
            $query .= 'AND Institut_id IN('
                    . DBManager::get()->quote($own_institutes) . ') ';
        }
        $query .= ' ORDER BY Institute.Name';
        $sql_search_responsible = new SQLSearch($query, _('Einrichtung suchen'),
                'Institut_id');
        $this->qs_id_responsible = md5(serialize($sql_search_responsible));
        $qs_responsible = QuickSearch::get('responsible', $sql_search_responsible);
        $this->qs_frame_id_responsible = $qs_responsible->getId();
        $this->search_responsible = $qs_responsible
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();

        $query = 'SELECT DISTINCT Institute.Institut_id, Institute.Name AS name '
                . 'FROM Institute '
                . 'LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id) '
                . 'WHERE (Institute.Name LIKE :input '
                . 'OR range_tree.name LIKE :input ) '
                . ' ORDER BY Institute.Name';
        $sql_search_institutes = new SQLSearch($query, _('Einrichtung suchen'),
                'Institut_id');
        $this->qs_id_institutes = md5(serialize($sql_search_institutes));
        $qs_institutes = QuickSearch::get('institutes', $sql_search_institutes);
        $this->qs_frame_id_institutes = $qs_institutes->getId();
        $this->search_institutes = $qs_institutes
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();

        $this->cancel_url = $this->url_for('/index');

        $this->render_template('module/module/modul', $this->layout);
    }

    /**
     * Deletes a descriptor from module
     *
     * @param type $deskriptor_id
     * @throws Trails_Exception
     */
    public function delete_modul_deskriptor_action($deskriptor_id)
    {
        $deskriptor = ModulDeskriptor::find($deskriptor_id);
        if (is_null($deskriptor)) {
            throw new Trails_Exception(404, _('Unbekannter Deskriptor.'));
        }
        $def_lang = $deskriptor->modul->getDefaultLanguage();
        if ($deskriptor->sprache == $def_lang) {
            throw new Trails_Exception(403, _('Ein Deskriptor in der Original-Sprache kann nicht gelöscht werden.'));
        }
        if (Request::submitted('delete')) {
            CSRFProtection::verifyUnsafeRequest();
            PageLayout::postSuccess(sprintf(_('Der Deskriptor "%s" des Moduls "%s" wurde gelöscht!'),
                    htmlReady($deskriptor->getDisplayName()),
                    htmlReady($deskriptor->modul->getDisplayName())));
            $deskriptor->delete();
        }
        $this->redirect($this->url_for('/index'));
    }

    public function description_action($modul_id)
    {
        $response = $this->relay('shared/modul/description', $modul_id);
        if (Request::isXhr()) {
            $this->response->add_header('Content-Type', 'text/html; charset=WINDOWS-1252');
            $this->render_text($response->body);
        } else {
            $this->render_nothing();
        }
    }

    public function approve_action($modul_id)
    {
        $this->modul_id = $modul_id;
        $this->modul = Modul::find($modul_id);

        if (Request::submitted('approval')) {
            $modul = Modul::get($modul_id);
            $modul->stat = 'genehmigt';
            $stored = $modul->store(false);
            if ($stored) {
                PageLayout::postSuccess(sprintf(_('Modul "%s" genehmigt!'),
                        htmlReady($this->modul->getDisplayName())));
                $this->redirect($this->url_for('/details', $modul_id));
                return;
            }
        }
        PageLayout::setTitle(_('Modul genehmigen'));
        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->setSidebar();
        }
        $this->render_template('module/module/approve', $this->layout);
    }

    public function copy_action($modul_id)
    {
        $modul = Modul::find($modul_id);
        if (!$modul) {
             PageLayout::postError( _('Unbekanntes Modul.'));
        } else {
            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                $copy = $modul->copy();
                // Kopie kennzeichnen (eindeutiger Code und Titel des Moduls in
                // allen Deskriptoren
                $copy->code = $modul->code . ' --KOPIE--';
                $copy->beschlussdatum = null;

                // quelle: Klammerung von (Gültigkeit-) Versionen desselben Moduls
                // Gießen: Modul-ID des ursprünglichen Moduls
                $copy->quelle = $modul->quelle ?: $modul->id;

                // don't show the new Modul
                $copy->stat = 'planung';

                // Deskriptoren als KOPIE kennzeichnen
                foreach ($copy->deskriptoren as $deskriptor) {
                    $deskriptor->bezeichnung = $deskriptor->bezeichnung . ' --KOPIE--';
                }

                // Deskriptoren der Modulteile als KOPIE kennzeichnen
                foreach ($copy->modulteile as $modulteil) {
                    foreach ($modulteil->deskriptoren as $deskriptor) {
                        $deskriptor->bezeichnung = $deskriptor->bezeichnung . ' --KOPIE--';
                    }
                }
                try {
                    $copy->store();
                    PageLayout::postSuccess(sprintf(_('Das Modul "%s" und alle zugehörigen Modulteile wurden kopiert!'),
                        htmlReady($modul->getDisplayName())));
                } catch (InvalidValuesException $e) {
                    PageLayout::postError(
                            _('Das Modul konnte nicht kopiert werden!')
                            . ' ' . htmlReady($e->getMessage()));
                } catch (Exception $e) {
                    PageLayout::postError(_('Beim Kopieren trat ein Fehler auf!'));
                }
            }
            if (!Request::isPost()) {
                $this->flash_dialog(
                        sprintf(_('Wollen Sie wirklich das Modul "%s" und alle zugehörigen Modulteile kopieren?'),
                                $modul->getDisplayName()),
                        '/copy/'. $modul->getId(), '/index');
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * Retrieves all fields from descriptor in the original language and
     * returns them as a json object.
     */
    public function show_original_action()
    {
        if (Request::isXhr()) {
            if (Request::option('type') == 'modulteil') {
                $parent = Modulteil::find(Request::option('id'));
                $formatted_fields = words('voraussetzung kommentar
                    kommentar_kapazitaet kommentar_wl_praesenz
                    kommentar_wl_bereitung kommentar_wl_selbst
                    kommentar_wl_pruef pruef_vorleistung pruef_leistung');
            } else {
                $parent = Modul::find(Request::option('id'));
                $formatted_fields = words('voraussetzung kompetenzziele inhalte
                    literatur links kommentar kommentar_kapazitaet
                    kommentar_wl_selbst
                    kommentar_wl_pruef pruef_vorleistung pruef_leistung
                    pruef_wiederholung ersatztext');
            }
            if ($parent) {
                $deskriptor_array = array();
                $deskriptor = $parent->getDeskriptor();
                foreach ($deskriptor->toArray() as $key => $value) {
                    if (MVVController::trim($value) !== '') {
                        if (in_array($key, $formatted_fields)) {
                            $deskriptor_array[$key]['value'] = formatReady($value);
                        } else {
                            $deskriptor_array[$key]['value'] = $value;
                        }
                        $deskriptor_array[$key]['empty'] = 0;
                    } else {
                        $deskriptor_array[$key]['value'] = _('Keine Angabe in Originalfassung.');
                        $deskriptor_array[$key]['empty'] = 1;
                    }
                }

                // datafields
                foreach ($deskriptor->datafields as $entry) {
                    $df = $entry->getTypedDatafield();
                    $value = $df->getDisplayValue();
                    $df_id = 'datafields_' . $entry->datafield_id;
                    if (MVVController::trim($value)) {
                        $deskriptor_array[$df_id]['value'] = $value;
                        $deskriptor_array[$df_id]['empty'] = 0;
                    } else {
                        $deskriptor_array[$df_id]['value'] = _('Keine Angabe in Originalfassung.');
                        $deskriptor_array[$df_id]['empty'] = 1;
                    }
                }
                $this->render_json($deskriptor_array);
            } else {
                $this->render_nothing();
            }
        } else {
            $this->redirect($this->url_for('/index'));
        }
    }

    public function modulteil_action($modulteil_id = null)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if (!$this->modulteil) {
            $this->modul = Modul::find(Request::option('modul_id'));
            if (!$this->modul) {
                PageLayout::postError(_('Unbekanntes Modul.'));
                $this->redirect($this->url_for('/index'));
                return;
            }
            $this->modulteil = new Modulteil();
            $this->modulteil->modul_id = $this->modul->id;
        } else {
            $this->modul = $this->modulteil->modul;
        }

        $this->formen = array();
        foreach ($GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'] as $key => $form) {
            if ($form['parent'] == '') {
                if ($form['visible'] || is_null($form['visible'])) {
                    $this->formen[$key]['group'] = array('key' => $key,
                        'name' => $form['name']);
                }
            } else {
                if ($form['visible']) {
                    $this->formen[$form['parent']]['options'][] = array('key' => $key,
                        'name' => $form['name']);
                }
            }
        }
        $this->setSidebar();

        if ($this->modulteil->isNew()) {
            PageLayout::setTitle(_('Neuen Modulteil anlegen'));
            $success_message = ('Der Modulteil "%s" wurde angelegt.');
            $language = $this->modulteil->getDefaultLanguage();
            PageLayout::postInfo(sprintf(_('Sie legen einen neuen Modulteil für das Modul <em>%s</em> an. Der Modulteil muss zunächst in der Ausgabesprache <em>%s</em> angelegt werden.'),
                htmlReady($this->modul->getDisplayName()),
                htmlReady($GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$this->modulteil->getDefaultLanguage()]['name'])));
        } else {
            PageLayout::setTitle(_('Modulteil bearbeiten'));
            $success_message = _('Der Modulteil "%s" wurde geändert.');
            $language = Request::option('display_language',
                    $this->modulteil->getDefaultLanguage());

            // sidebar widget for selecting language
            $template_factory = $this->get_template_factory();
            $sidebar = Sidebar::get();
            $widget = new ListWidget();
            $widget->setTitle(_('Ausgabesprache'));
            $widget_element = new WidgetElement(
                    $template_factory->render('shared/deskriptor_language', array(
                    'modul' => $this->modulteil,
                    'sprache' => $language,
                    'link' => $this->url_for('/modulteil', $this->modulteil->id),
                    'url' => $this->url)));
            $widget->addElement($widget_element);
            $sidebar->addWidget($widget, 'languages');
        }
        $this->deskriptor = $this->modulteil->getDeskriptor($language, true);

        $this->def_lang = $this->deskriptor->sprache == $this->modulteil->getDefaultLanguage();

        if (!$this->def_lang) {
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(_('Alle Texte der Originalfassung anzeigen'),
                '#', Icon::create('consultation', 'clickable'),
                array('class' => 'mvv-show-all-original'));
        }

        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            if ($this->def_lang) {
                $this->modulteil->modul_id = Request::option('modul_id');
                $this->modulteil->flexnow_modul = trim(Request::get('flexnow_modul'));
                $this->modulteil->nummer = Request::int('nummer');
                $this->modulteil->num_bezeichnung = Request::option('num_bezeichnung');
                $this->modulteil->lernlehrform = Request::option('lernlehrform');
                $this->modulteil->semester = Request::option('semester');
                if (Request::get('kap_unbegrenzt')) {
                    $this->modulteil->kapazitaet = '';
                } else {
                    $kapazitaet = trim(Request::get('kapazitaet'));
                    $this->modulteil->kapazitaet =
                            $kapazitaet === '' ? null : $kapazitaet;
                }
                $this->modulteil->kp = Request::int('kp');
                $this->modulteil->sws = Request::int('sws', 0);
                $this->modulteil->wl_praesenz = Request::int('wl_praesenz', 0);
                $this->modulteil->wl_bereitung = Request::int('wl_bereitung', 0);
                $this->modulteil->wl_selbst = Request::int('wl_selbst', 0);
                $this->modulteil->wl_pruef = Request::int('wl_pruef', 0);
                $this->modulteil->anteil_note = Request::int('anteil_note', 0);
                $this->modulteil->ausgleichbar = Request::int('ausgleichbar', 0);
                $this->modulteil->pflicht = Request::int('pflicht', 0);
                $this->modulteil->assignLanguagesOfInstruction(
                    Request::optionArray('language_items'));
            }

            $this->deskriptor->bezeichnung = trim(Request::get('bezeichnung'));
            $this->deskriptor->voraussetzung = trim(Request::get('voraussetzung'));
            $this->deskriptor->kommentar = trim(Request::get('kommentar'));
            $this->deskriptor->kommentar_kapazitaet = trim(Request::get('kommentar_kapazitaet'));
            $this->deskriptor->kommentar_wl_praesenz = trim(Request::get('kommentar_wl_praesenz'));
            $this->deskriptor->kommentar_wl_bereitung = trim(Request::get('kommentar_wl_bereitung'));
            $this->deskriptor->kommentar_wl_selbst = trim(Request::get('kommentar_wl_selbst'));
            $this->deskriptor->kommentar_wl_pruef = trim(Request::get('kommentar_wl_pruef'));
            $this->deskriptor->pruef_vorleistung = trim(Request::get('pruef_vorleistung'));
            $this->deskriptor->pruef_leistung = trim(Request::get('pruef_leistung'));
            $this->deskriptor->kommentar_pflicht = trim(Request::get('kommentar_pflicht'));

            // update datafields
            foreach (Request::getArray('datafields') as $df_key => $df_value) {
                $df = $this->deskriptor->datafields->findOneBy('datafield_id', $df_key);
                if ($df) {
                    $df->content = $df_value;
                }
            }

            // check permission (add a Modulteil to Modul)
            $perm = MvvPerm::get($this->modul);
            if (!$perm->haveFieldPermModulteile(MvvPerm::PERM_CREATE)) {
                throw new Exception(_('Keine Berechtigung.'));
            }

            try {
                $this->modulteil->verifyPermission();
                $stored = $this->modulteil->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message,
                            htmlReady($this->modulteil->getDisplayName())));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/details', $this->modulteil->modul_id));
                return;
            }
        }
        if ($this->deskriptor->sprache !== $this->modulteil->getDefaultLanguage()
                && $this->deskriptor->isNew()) {
            PageLayout::postInfo(sprintf(_('Neue Beschreibung zum Modulteil "%s" in der Ausgabesprache %s anlegen.'),
                    htmlReady($this->modulteil->getDisplayName()),
                    htmlReady($GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$language]['name'])));
        }
        $this->cancel_url = $this->url_for('/details', $this->modulteil->modul_id);

        $action_widget = Sidebar::get()->getWidget('actions');
        $action_widget->addLink(_('Log-Einträge dieses Modulteils'),
                $this->url_for('shared/log_event/show/Modulteil/' . $this->modulteil->id,
                ['object2_type' => 'ModulteilDeskriptor', 'object2_id' => $this->deskriptor->id]),
                Icon::create('log', 'clickable'))->asDialog();

        $this->render_template('module/module/modulteil', $this->layout);
    }

    /**
     * Deletes a descriptor from Modulteil
     *
     * @param type $deskriptor_id
     * @throws Trails_Exception
     */
    public function delete_modulteil_deskriptor_action($deskriptor_id)
    {
        $deskriptor = ModulteilDeskriptor::find($deskriptor_id);
        if (is_null($deskriptor)) {
            throw new Trails_Exception(404, _('Unbekannter Deskriptor.'));
        }
        $def_lang = $deskriptor->modulteil->getDefaultLanguage();
        if ($deskriptor->sprache == $def_lang) {
            throw new Trails_Exception(403, _('Ein Deskriptor in der Original-Sprache kann nicht gelöscht werden.'));
        }
        if (Request::submitted('delete')) {
            CSRFProtection::verifyUnsafeRequest();
            PageLayout::postSuccess(sprintf(_('Der Deskriptor "%s" des Modulteils "%s" wurde gelöscht!'),
                    htmlReady($deskriptor->getDisplayName()),
                    htmlReady($deskriptor->modulteil->getDisplayName())));
            $deskriptor->delete();
        }
        $this->redirect($this->url_for('/index'));
    }

    public function copy_modulteil_action($modulteil_id)
    {
        $modulteil = Modulteil::find($modulteil_id);
        if (!$modulteil) {
            PageLayout::postError( _('Unbekannter Modulteil.'));
            $this->redirect($this->url_for('/index'));
            return;
        }
        $copy_modulteil = $modulteil->copy();
        $copy_modulteil->store();
        PageLayout::postInfo(sprintf(_('Der Modulteil "%s" wurde kopiert. Klicken Sie auf "übernehmen", um Änderungen an der Kopie zu speichern.'),
                htmlReady($copy_modulteil->getDisplayName())));
        $this->redirect($this->url_for('/modulteil', $copy_modulteil->id));
    }

    public function modulteil_lvg_action($modulteil_id)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if (is_null($this->modulteil)) {
            PageLayout::postError( _('Unbekannter Modulteil.'));
            $this->redirect($this->url_for('/index'));
            return;
        } else {
            $this->modulteil_id = $this->modulteil->getId();
            $this->search_lvgruppe($this->modulteil->getId());
            if (Request::isXhr()) {
                $this->render_template('module/module/modulteil_lvg', null);
            } else {
                $this->modul = Modul::get($this->modulteil->modul_id);
                $this->modul_id = $this->modul->getId();
                $this->perform_relayed('details',
                        $this->modulteil->modul_id, $this->modulteil->id);
            }
        }
    }

    private function search_lvgruppe($modulteil_id)
    {
        //Quicksearch
        $query = 'SELECT lvgruppe_id, name FROM '
                . 'mvv_lvgruppe WHERE name LIKE :input '
                . ' AND lvgruppe_id NOT IN(SELECT lvgruppe_id FROM '
                . 'mvv_lvgruppe_modulteil WHERE modulteil_id = '
                . DBManager::get()->quote($modulteil_id) . ')';
        $search = new SQLSearch($query, _('LV-Gruppe suchen'));
        $this->qs_search_id = md5(serialize($search));
        $this->search = QuickSearch::get('lvgruppe_id_'
                . $modulteil_id, $search)
                ->setInputStyle('width: 240px')
                ->noSelectbox();
    }

    public function add_lvgruppe_action($modulteil_id)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if (is_null($this->modulteil)) {
            PageLayout::postError(_('Unbekannter Modulteil.'));
            $this->redirect($this->url_for('/index'));
            return;
        } else {
            $this->lvgruppe = Lvgruppe::find(Request::option(
                    'lvgruppe_id_' . $this->modulteil->getId()));
            if (!$this->lvgruppe) {
                PageLayout::postError(_('Unbekannte Lehrveranstaltungsgruppe.'));
            } else {
                $this->lvg_modulteil = LvgruppeModulteil::get(
                        array($this->lvgruppe->getId(),
                            $this->modulteil->getId()));
                if ($this->lvg_modulteil->isNew()) {
                    $this->lvg_modulteil->store();
                    PageLayout::postSuccess(sprintf(
                            _('Die Lehrveranstaltungsgruppe "%s" wurde dem Modulteil "%s" zugeordnet.'),
                            htmlReady($this->lvgruppe->getDisplayName()),
                            htmlReady($this->modulteil->getDisplayName())));
                } else {
                    PageLayout::postInfo(sprintf(
                            _('Die bestehende Zuordnung der Lehrveranstaltungsgruppe "%s" zum Modulteil "%s" wurde nicht geändert.'),
                            htmlReady($this->lvgruppe->getDisplayName()),
                            htmlReady($this->modulteil->getDisplayName())));
                }
            }
            $this->redirect($this->url_for('/details',
                    $this->modulteil->modul_id, $this->modulteil->id));
        }
    }

    public function delete_lvgruppe_action($modulteil_id, $lvgruppe_id)
    {
        $lvg_modulteil = LvgruppeModulteil::find(array($lvgruppe_id,
            $modulteil_id));
        if (!$lvg_modulteil) {
            PageLayout::postError(_('Unbekannte Zuordnung.'));
        } else {
            $lvgruppe = Lvgruppe::find($lvgruppe_id);
            $modulteil = Modulteil::find($modulteil_id);
            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                PageLayout::postSuccess(sprintf(
                        _('Die Lehrveranstaltungsgruppe "%s" wurde vom Modulteil "%s" entfernt.'),
                        htmlReady($lvgruppe->getDisplayName()),
                        htmlReady($modulteil->getDisplayName())));
                $lvg_modulteil->delete();
            }
            if (!Request::isPost()) {
                $this->flash_dialog(
                        sprintf(_('Wollen Sie wirklich die Lehrveranstaltungsgruppe "%s" vom Modulteil "%s" entfernen?'),
                                $lvgruppe->getDisplayName(),
                                $modulteil->getDisplayName()),
                        array('/delete_lvgruppe', $modulteil->getId(), $lvgruppe->getId()),
                        '/modulteil_lvg/' . $lvg_modulteil->modulteil_id);
            }
        }
        $this->redirect($this->url_for('/details', $modulteil->modul_id, $modulteil->id));
    }

    public function new_lvgruppe_action($modulteil_id, $lvgruppe_id = null)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if (is_null($this->modulteil)) {
            PageLayout::postError( _('Unbekannter Modulteil.'));
            $this->redirect($this->url_for('/index'));
            return;
        } else {
            $this->lvgruppe = Lvgruppe::get($lvgruppe_id);
            $this->cancel_url = $this->url_for('/index');
            $this->submit_url = $this->url_for('/new_lvgruppe', $this->modulteil->id,
                        $this->lvgruppe->id);
            if (Request::submitted('store')) {
                CSRFProtection::verifyUnsafeRequest();
                $stored = false;
                if (Request::isXhr()) {
                    $this->lvgruppe->name = trim(studip_utf8decode(
                            Request::get('name')));
                    $this->lvgruppe->alttext = trim(studip_utf8decode(
                            Request::get('alttext')));
                    $this->lvgruppe->alttext_en = trim(studip_utf8decode(
                            Request::get('alttext_en')));
                } else {
                    $this->lvgruppe->name = trim(Request::get('name'));
                    $this->lvgruppe->alttext = trim(Request::get('alttext'));
                    $this->lvgruppe->alttext_en = trim(Request::get('alttext_en'));
                }
                try {
                    $stored = $this->lvgruppe->store();

                    if ($stored) {
                        $this->lvg_modulteil = LvgruppeModulteil::get(
                                array($this->lvgruppe->getId(),
                                $this->modulteil->getId()));
                        $stored_modulteil = $this->lvg_modulteil->store();
                    }
                } catch (InvalidValuesException $e) {
                     PageLayout::postError(htmlReady($e->getMessage()));
                }
                if ($stored !== false && $stored_modulteil !== false) {
                    if ($stored) {
                        PageLayout::postSuccess(sprintf(_('LV-Gruppe "%s" geändert.'),
                                htmlReady($this->lvgruppe->getDisplayName())));
                    } else {
                        PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                    }
                    $this->redirect($this->url_for('/details',
                            $this->modulteil->modul_id, $this->modulteil->id));
                    return;
                }
            }
        }
        $this->render_template('module/module/lvgruppe');
    }

    public function delete_action($modul_id)
    {
        $modul = Modul::get($modul_id);
        if ($modul->isNew()) {
             PageLayout::postError( _('Unbekanntes Modul.'));
        } else {
            if (Request::submitted('yes')) {
                CSRFProtection::verifySecurityToken();
                PageLayout::postSuccess(sprintf(_('Modul "%s" gelöscht!'),
                        htmlReady($modul->getDisplayName())));
                $modul->delete();
            }
            if (!Request::isPost()) {
                $this->flash_dialog(
                        sprintf(_('Wollen Sie wirklich das Modul "%s" löschen?'),
                                $modul->getDisplayName()),
                        array('module', 'module', 'delete',
                                $modul->getId()),
                        '/details', $modul->id);
            }
        }
        $this->redirect($this->url_for('/details', $modul->id));
    }

    public function delete_modulteil_action($modulteil_id)
    {
        $modulteil = Modulteil::find($modulteil_id);
        $modul_id = $modulteil->modul_id;
        if (!$modulteil) {
             PageLayout::postError(_('Unbekannter Modulteil.'));
        } else {
            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                PageLayout::postSuccess(sprintf(_('Modulteil "%s" gelöscht!'),
                        htmlReady($modulteil->getDisplayName())));
                $modulteil->delete();
            }
            if (!Request::isPost()) {
                $this->flash_dialog(
                        sprintf(_('Wollen Sie wirklich den Modulteil "%s" löschen?'),
                                $modulteil->getDisplayName()),
                        array('/delete_modulteil',
                                $modulteil->getId()),
                        '/details', $modulteil->modul_id);
            }
        }
        $this->redirect($this->url_for('/details', $modul_id));
    }

    public function details_action($modul_id, $modulteil_id = null)
    {
        $this->modul = Modul::get($modul_id);
        $this->modul_id = $this->modul->isNew() ? null : $this->modul->getId();
        if ($modulteil_id) {
            $modulteil = Modulteil::get($modulteil_id);
            $this->modulteil_id = $modulteil->isNew() ? null : $modulteil->getId();
            $this->search_lvgruppe($this->modulteil_id);
        }
        if (Request::isXhr()) {
            $this->details_url = '/modulteil_lvg/';
        } else {
            $this->perform_relayed('index');
        }
    }

    public function diff_select_action($modul_id)
    {
        $this->modul = Modul::find($modul_id);
        if ($this->modul) {
            // Modulsuche
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
                   . 'AND mm.modul_id <> '
                   . DBManager::get()->quote($this->modul->getId())
                   . " ORDER BY modul_name";

            $this->search_modul =
                    QuickSearch::get('old_module_id', new MvvQuickSearch($query, _('Modul suchen'),
                            'modul'))
                    ->setInputStyle('width: 240px')
                    ->fireJSFunctionOnSelect('MVV.Search.getFocus')
                    ->render();

            $this->quelle = $this->modul->modul_quelle;
            $this->variante = $this->modul->modul_variante;
            if (Request::isXhr()) {
                $this->render_template('module/module/diff_select', null);
            }
        } else {
            PageLayout::postError(_('Unbekanntes Modul!'));
            $this->redirect($this->url_for('/index'));
        }
    }

    public function diff_action($new_id = null, $old_id = null)
    {
        $new_module = Modul::find(Request::option('new_id', $new_id));
        $old_module = Modul::find(Request::option('old_module_id', $old_id));

        if (!$new_module || !$old_module) {
            if ($new_module) {
                PageLayout::postError( _('Unbekanntes Modul!'));
                $this->redirect('/diff_select', $new_module->id);
                return;
            } else {
                PageLayout::postError(_('Unbekanntes Modul!'));
                $this->response->add_header('X-Location', $this->url_for('/index'));
            }
        } else {
            if (Request::isXhr()) {
                $this->response->add_header('X-Location', $this->url_for('/diff',
                        $new_module->id, $old_module->id));
            }
            $type_new = 1;
            $count_modulteile = count($new_module->modulteile);
            if ($count_modulteile == 0) {
                $type_new = 3;
            } else if ($count_modulteile == 1) {
                $type_new = 2;
            }

            $type_old = 1;
            $count_modulteile = count($old_module->modulteile);
            if ($count_modulteile == 0) {
                $type_old = 3;
            } else if ($count_modulteile == 1) {
                $type_old = 2;
            }

            PageLayout::addStylesheet($this->plugin->getPluginURL()
                    . '/public/stylesheets/mvv_difflog.css');
            PageLayout::addScript($this->plugin->getPluginURL()
                    . '/public/javascripts/mvv_difflog.js');

            PageLayout::addStylesheet('print.css');
            $factory = $this->get_template_factory();
            $template = $factory->open('module/module/diff');
            $template->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
            $template->set_attributes(
                    array(
                        'new_module' => $new_module,
                        'old_module' => $old_module,
                        'type_new'   => $type_new,
                        'type_old'   => $type_old,
                        'plugin'   => $this->plugin
                    ));

            $this->render_text($template->render());
        }
    }

    /**
     * do the search
     */
    public function search_action()
    {
        if (Request::get('reset-search')) {
            $this->reset_search('module');
            $this->reset_page();
            $this->reset_filter_action();
        } else {
            $this->reset_search('module');
            $this->reset_page();
            $this->do_search('Modul',
                    trim(Request::get('modul_suche_parameter')),
                    Request::get('modul_suche'));

            $this->redirect($this->url_for('/index'));
        }
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('module');
        $this->reset_page();
        $this->reset_filter_action();
    }

    public function sort_action()
    {
        $target = explode('_', Request::option('list_id'));
        $success = false;
        if ($target[0] == 'modulteil') {
            $success = $this->sort_lvgruppen($target[1]);
        } else {
            $success = $this->sort_modulteile($target[0]);
        }
        if ($success) {
            $this->set_status(204);
        } else {
            $this->set_status(400);
        }
        $this->render_nothing();
    }

    /**
     * sorts Modulteile
     */
    private function sort_modulteile($modul_id)
    {
        $modul = Modul::find($modul_id);
        if ($modul) {
            $orderedIds = Request::getArray('newOrder');
            if (is_array($orderedIds)) {
                $i = 1;
                foreach ($orderedIds as $modulteil_id) {
                    $modulteil = $modul->modulteile->find($modulteil_id);
                    if ($modulteil->position != $i) {
                        $modulteil->position = $i;
                        $modulteil->store(false);
                    }
                    $i++;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * sorts LVGruppen
     */
    private function sort_lvgruppen($modulteil_id)
    {
        $modulteil = Modulteil::find($modulteil_id);
        if ($modulteil) {
            $orderedIds = Request::getArray('newOrder');
            if (is_array($orderedIds)) {
                $i = 1;
                foreach ($orderedIds as $id) {
                    list($foo, $lvgruppe_id) = explode('_', $id);
                    $lvgruppe_modulteil =
                            LvgruppeModulteil::find(array($lvgruppe_id,
                                $modulteil->getId()));
                    if ($lvgruppe_modulteil && $lvgruppe_modulteil->position != $i) {
                        $lvgruppe_modulteil->position = $i;
                        $lvgruppe_modulteil->store(false);
                    }
                    $i++;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * sets filter parameters and store these in the session
     */
    public function set_filter_action()
    {
        // Semester
        $semester_id = mb_strlen(Request::get('semester_filter'))
                ? Request::option('semester_filter') : null;
        if ($semester_id) {
            $semester = Semester::find($semester_id);
            $this->filter['start_sem.beginn'] = $semester->beginn;
            $this->filter['end_sem.ende'] = $semester->beginn;
        } else {
            $this->filter['start_sem.beginn'] = 2147483647;
            $this->filter['end_sem.ende'] = 1;
        }

        // Status
        $this->filter['mvv_modul.stat']
                = mb_strlen(Request::get('status_filter'))
                ? Request::option('status_filter') : null;

        // verantwortliche Einrichtung
        $this->filter['mvv_modul_inst.institut_id']
                = mb_strlen(Request::get('institut_filter'))
                ? Request::option('institut_filter') : null;

        // store filter
        $this->reset_page();
        $this->sessSet('filter', $this->filter);
        $this->redirect($this->url_for('/index'));
    }

    public function reset_filter_action()
    {
        $this->filter = array();
        $this->reset_page();
        // all semester
        $this->filter['start_sem.beginn'] = 2147483647;
        $this->filter['end_sem.ende'] = 1;
        $this->sessSet('filter', $this->filter);
        $this->redirect($this->url_for('/index'));
    }

    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));

        $widget  = new ViewsWidget();
        $widget->addLink(_('Liste der Module'),
                $this->url_for('module/module/index'))
                ->setActive(get_called_class() == 'Module_ModuleController');
        $widget->addLink(_('Gruppiert nach verantwortlichen Einrichtungen'),
                $this->url_for('module/institute/index'))
                ->setActive(get_called_class() == 'Module_InstituteController');
        $sidebar->addWidget($widget, 'views');


        $widget  = new ActionsWidget();
        $widget->setTitle(_('Aktionen'));
        if (MvvPerm::havePermCreate('Modul')) {
            $widget->addLink(_('Neues Modul anlegen'),
                    $this->url_for('/modul'),
                    Icon::create('file+add', 'clickable'));
        }
        $sidebar->addWidget($widget, 'actions');


        if ($this->show_sidebar_search) {
            $this->sidebar_search();
        }
        if ($this->show_sidebar_filter) {
            $this->sidebar_filter();
        }
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten können Sie die Module verwalten. Ein Modul kann ein oder mehrere Modulteil(e) haben.').'</br>'));
        $widget->addElement(new WidgetElement(_('Module können nur gelöscht werden, wenn Sie noch keinem Studiengang zugeordnet sind.')));
        $helpbar->addWidget($widget);
        $this->sidebar_rendered = true;
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        $template_factory = $this->get_template_factory();
        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $own_institutes = MvvPerm::getOwnInstitutes();
        $modul_ids = null;
        if (count($own_institutes)) {
            if ($this->filter['mvv_modul_inst.institut_id']) {
                $modul_ids = Modul::findByFilter(
                    ['mvv_modul_inst.institut_id' => array_intersect($own_institutes,
                    (array) $this->filter['mvv_modul_inst.institut_id'])]);
            } else {
                $modul_ids = Modul::findByFilter(
                    ['mvv_modul_inst.institut_id' => $own_institutes]);
            }
        } else {
            if ($this->filter['mvv_modul_inst.institut_id']) {
                $modul_ids = Modul::findByFilter(
                    ['mvv_modul_inst.institut_id'
                        => $this->filter['mvv_modul_inst.institut_id']]);
            }
        }

        $institute_filter = ['mvv_modul.stat' => $this->filter['mvv_modul.stat'],
            'mvv_modul_inst.institut_id' => $own_institutes,
            'mvv_modul_inst.gruppe' => 'hauptverantwortlich'];

        $template = $template_factory->open('shared/filter');

        // Status
        $template->set_attribute('status', Modul::findStatusByIds($modul_ids));
        $template->set_attribute('selected_status',
                $this->filter['mvv_modul.stat']);
        $template->set_attribute('status_array',
                $GLOBALS['MVV_MODUL']['STATUS']['values']);

        // Institutes
        $template->set_attribute('institute',
                Modul::getAllAssignedInstitutes('name', 'ASC', $institute_filter));
        $template->set_attribute('institute_count', 'count_objects');
        $template->set_attribute('selected_institut',
                $this->filter['mvv_modul_inst.institut_id']);

        // Semesters
        $semesters = new SimpleCollection(Semester::getAll());
        $semesters = $semesters->orderBy('beginn desc');
        $selected_semester = $semesters->findOneBy('beginn',
                $this->filter['start_sem.beginn']);

        $template->set_attribute('semester', $semesters);
        $template->set_attribute('selected_semester', $selected_semester->id);

        $template->set_attribute('action',
                $this->url_for('/set_filter'));
        $template->set_attribute('action_reset',
                $this->url_for('/reset_filter'));

        $filter_template = $template->render();

        $sidebar = Sidebar::get();
        $widget  = new SidebarWidget();
        $widget->setTitle(_('Filter'));
        $widget->addElement(new WidgetElement($filter_template));
        $sidebar->addWidget($widget, 'filters');
    }

    /**
     * adds the search function to the sidebar
     */
    private function sidebar_search()
    {
        $template_factory = $this->get_template_factory();

        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $institut_ids = MvvPerm::getOwnInstitutes();
        if (count($institut_ids)) {
            $filter = array(
                'mmi.institut_id' => $institut_ids,
                'mmi.gruppe'      => 'hauptverantwortlich'
                );
        } else {
            $filter = array();
        }

        $query = "SELECT mm.modul_id, CONCAT(mmd.bezeichnung, ' (', code, ')')"
            . 'AS name FROM mvv_modul mm '
            . 'LEFT JOIN mvv_modul_deskriptor mmd ON mm.modul_id = mmd.modul_id '
            . 'LEFT JOIN mvv_modul_inst mmi ON mmd.modul_id = mmi.modul_id '
            . 'WHERE mmd.sprache = '
            . DBManager::get()->quote(
                    $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['default'])
            . ' AND (code LIKE :input OR mmd.bezeichnung LIKE :input) '
            . ModuleManagementModel::getFilterSql($filter)
            . ' ORDER BY name ASC';
        $search_term =
                $this->search_term ? $this->search_term : _('Modul suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('/search'));
        $widget->addNeedle(_('Modul suchen'), 'modul_suche', true,
                new SQLSearch($query, $search_term, 'modul_id'),
                'function () { $(this).closest("form").submit(); }',
                $this->search_term);
        $widget->setTitle('Suche');
        $sidebar->addWidget($widget, 'search');
    }

}
