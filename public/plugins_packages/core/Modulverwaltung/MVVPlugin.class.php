<?php
/**
 * MVVPlugin.class.php - Main plugin class
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


require dirname(__FILE__) . '/mvv_config.php';

class MVVPlugin extends StudipPlugin implements SystemPlugin, Loggable {

    const CACHE_KEY = 'plugins/MVVPlugin/';

    public $config = array();

    function __construct() {
        parent::__construct();

        $this->restoreConfig();
        $this->me = strtolower(__CLASS__);
        if ($this->isVisibleSearch()) {
            $this->setNavigationSearch();
        }

        if ($this->isVisible()) {
            $this->setNavigationAdminCourse();
            $this->setNavigation();
        }
        PageLayout::addScript($this->getPluginUrl() . '/public/javascripts/coursewizard.js');
    }

    private function setNavigation()
    {
        $navigation = new AutoNavigation($this->getDisplayTitle());
        $navigation->setUrl(PluginEngine::getURL($this->me
                . '/studiengaenge/studiengaenge/'), array());
        $navigation->setImage(Icon::create('learnmodule', 'navigation',
                ['title' => $this->getDisplayTitle()]));

        $stg_navigation = new AutoNavigation(_('Studiengänge'));

        $stg_navigation->addSubNavigation('studiengaenge',
                new AutoNavigation(_('Studiengänge'),
                PluginEngine::getURL($this->me, array(),
                        'studiengaenge/studiengaenge', true), array()));
        $stg_navigation->addSubNavigation('studiengangteile',
                new AutoNavigation(_('Studiengangteile'),
                PluginEngine::getURL($this->me, array(),
                        'studiengaenge/studiengangteile', true), array()));
        $stg_navigation->addSubNavigation('versionen',
                new AutoNavigation(_('Versionen'),
                PluginEngine::getUrl($this->me, array(),
                        'studiengaenge/versionen', true), array()));
        $stg_navigation->addSubNavigation('stgteilbezeichnungen',
                new AutoNavigation(_('Studiengangteil-Bezeichnungen'),
                PluginEngine::getURL($this->me, array(),
                        'studiengaenge/stgteilbezeichnungen', true), array()));
        $navigation->addSubNavigation('studiengaenge', $stg_navigation);

        $modul_navigation = new AutoNavigation(_('Module'));
        $modul_navigation->addSubNavigation('module', new AutoNavigation(_('Module'),
                PluginEngine::getURL($this->me, array(), 'module/module/', true), array()));

        $navigation->addSubNavigation('module', $modul_navigation);

        $lvg_navigation = new AutoNavigation(_('LV-Gruppen'));
        $lvg_navigation->addSubNavigation('lvgruppen', new AutoNavigation(_('Lehrveranstaltungsgruppen'),
                PluginEngine::getURL($this->me, array(), 'lvgruppen/lvgruppen', true), array()));
        $navigation->addSubNavigation('lvgruppen', $lvg_navigation);

        $fa_navigation = new AutoNavigation(_('Fächer/Abschlüsse'));
        $fa_navigation->addSubNavigation(
                'faecher', new AutoNavigation(_('Fächer'),
                PluginEngine::getUrl(
                        $this->me, array(), 'fachabschluss/faecher', true),
                        array()));
        $fa_navigation->addSubNavigation(
                'abschluesse', new AutoNavigation(_('Abschlüsse'),
                PluginEngine::getUrl(
                        $this->me, array(), 'fachabschluss/abschluesse', true),
                        array()));
        $fa_navigation->addSubNavigation(
                'kategorien', new AutoNavigation(_('Abschluss-Kategorien'),
                PluginEngine::getUrl(
                        $this->me, array(), 'fachabschluss/kategorien', true),
                        array()));

        $dok_navigation = new AutoNavigation(_('Materialien/Dokumente'));
        $dok_navigation->addSubNavigation(
                'dokumente', new AutoNavigation(_('Materialien/Dokumente'),
                PluginEngine::getUrl($this->me, array(), 'materialien/dokumente', true), array()));

        $navigation->addSubNavigation('fachabschluss', $fa_navigation);
        $navigation->addSubNavigation('materialien', $dok_navigation);

        Navigation::addItem('/' . $this->me, $navigation);
        Navigation::addItem('/start/' . $this->me, $navigation);

    }

    private function setNavigationAdminCourse()
    {
        // add navigation to admin area
        if (Course::findCurrent() && Navigation::hasItem('/course/admin') && Course::findCurrent()->getSemClass()->offsetget('module')) {
            $nav = new Navigation(_('LV-Gruppen'), PluginEngine::getUrl(
                    $this->me, array(), 'lvgselector',
                    array('list' => 'TRUE')));
            $nav->setDescription(_('Zuordnung der Veranstaltung zu Lehrveranstaltungsgruppen um die Einordnung innerhalb des Modulverzeichnisses festzulegen.'));
            $nav->setImage(Icon::create('learnmodule', 'clickable'));
            Navigation::insertItem('/course/admin/lvgruppen', $nav, 'dates');
        }
    }

    private function setNavigationSearch()
    {
        // add navigation to search
        if (Navigation::hasItem('/search/archive')) {
            $search_navigation = new AutoNavigation(_('Modulverzeichnis'));
            $search_navigation->addSubNavigation('modulsuche',
                    new AutoNavigation(_('Module'),
                    PluginEngine::getUrl($this->me . '/search/module'),
                        array()));
            $search_navigation->addSubNavigation('angebot',
                    new AutoNavigation(_('Studienangebot'),
                    PluginEngine::getUrl($this->me . '/search/angebot'),
                        array()));
            $search_navigation->addSubNavigation('studiengaenge',
                    new AutoNavigation(_('Studiengänge'),
                    PluginEngine::getUrl($this->me . '/search/studiengaenge'),
                        array()));
            Navigation::insertItem('/search/module', $search_navigation,
                    'archive');
        }
    }

    public function isVisible() {
        if ($GLOBALS['perm']->have_perm('root') || ($GLOBALS['perm']->have_perm('admin')
                && RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVAdmin'))) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVEntwickler')) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVRedakteur')) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVTranslator')) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVFreigabe')) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVLvGruppenAdmin')) {
            return true;
        }
        return false;
    }

    public function isVisibleCourse()
    {
        return $GLOBALS['perm']->
                have_studip_perm('tutor', $GLOBALS['SessSemName'][1]);
    }

    public function isVisibleSearch()
    {
        return $GLOBALS['perm']->have_perm('autor') && Modul::publicModulesAvailable();
    }

    public function isVisibleAdminCourse()
    {
        return $GLOBALS['perm']->
                have_studip_perm('tutor', $GLOBALS['SessSemName'][1]);
    }

    public function isVisibleSeminareAssi() {
        if ($this->isVisible()) {
            $page = basename($_SERVER['PHP_SELF']);
            if ($page == 'admin_seminare_assi.php') {
                return true;
            }
        }
        return false;
    }

    public function getDisplayTitle() {
        return _('Module');
    }

    public function restoreConfig() {

    }

    private function init()
    {
        $this->getPluginPath();
        PageLayout::addScript('ui.multiselect.js');
        PageLayout::addScript($this->getPluginURL()
                . '/public/javascripts/mvv.js');

        $this->addStylesheet('public/stylesheets/less/mvv.less');
    }

    /**
     * This method dispatches and displays all actions. It uses the template
     * method design pattern, so you may want to implement the methods #route
     * and/or #display to adapt to your needs.
     *
     * @param  string  the part of the dispatch path, that were not consumed yet
     *
     * @return void
     */
    public function perform($unconsumed_path) {
        if (!($this->isVisible() || $this->isVisibleCourse()
                || $this->isVisibleAdminCourse() || $this->isVisibleSearch())) {
            throw new AccessDeniedException();
        }
        $this->init();
        if (strpos($unconsumed_path, 'lvgselector') === false) {
            // get rid of cid!
            UrlHelper::removeLinkParam('cid');
        }
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null, true), '/'),
            'studiengaenge/studiengaenge'
        );
        $dispatcher->current_plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    /**
     * This method enriches the logentry templates with data of the mvv classes
     *
     * @param  LogEvent  log event entry
     *
     * @return void
     */
    public static function logFormat(LogEvent $event)
    {
        $templ = $event->action->info_template;

        $mvv_plugin = PluginManager::getInstance()->getPlugin('MVVPlugin');

        $table = explode('.', $event->info);

        switch ($table[0]) {

            case 'abschluss':
                $abschluss = Abschluss::find($event->affected_range_id);
                if ($abschluss) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'fachabschluss/abschluesse/details/' . $abschluss->getId(), true);
                    $templ = str_replace('%abschluss(%affected)', '<a href="' . $url . '">' . htmlReady($abschluss->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_abschl_kategorie':
                $abskategorie = AbschlussKategorie::find($event->affected_range_id);
                if ($abskategorie) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'fachabschluss/kategorien/details/' . $abskategorie->getId(), true);
                    $templ = str_replace('%abskategorie(%affected)', '<a href="' . $url . '">' . htmlReady($abskategorie->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_abschl_zuord':
                $abschluss = Abschluss::find($event->affected_range_id);
                if ($abschluss) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'fachabschluss/abschluesse/details/' . $abschluss->getId(), true);
                    $templ = str_replace('%abschluss(%affected)', '<a href="' . $url . '">' . htmlReady($abschluss->getDisplayName()) . '</a>', $templ);
                }
                $co_kategorie = AbschlussKategorie::find($event->coaffected_range_id);
                if ($co_kategorie) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'fachabschluss/kategorien/details/' . $co_kategorie->getId(), true);
                    $templ = str_replace('%abskategorie(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_kategorie->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_dokument':
            case 'mvv_dokument_zuord':
                $dokument = Dokument::find($event->affected_range_id);
                if ($dokument) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'materialien/dokumente/details/' . $dokument->getId(), true);
                    $templ = str_replace('%dokument(%affected)', '<a href="' . $url . '">' . htmlReady($dokument->getDisplayName()) . '</a>', $templ);
                    if ($event->coaffected_range_id) {
                        $mmv_object = call_user_func([$event->dbg_info, 'find'], $event->coaffected_range_id);
                        if ($mmv_object) {
                            $templ = str_replace('%object_type(%coaffected)', 'in ' . htmlReady($mmv_object->getDisplayName()), $templ);
                        } else {
                            $templ = str_replace('%object_type(%coaffected)', '', $templ);
                        }
                    }
                }
                break;

            case 'fach':
            case 'mvv_fach_inst':
                $fach = Fach::find($event->affected_range_id);
                if ($fach) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'fachabschluss/faecher/details/' . $fach->getId(), true);
                    $templ = str_replace('%fach(%affected)', '<a href="' . $url . '">' . htmlReady($fach->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_lvgruppe':
            case 'mvv_lvgruppe_seminar':
                $lvgruppe = Lvgruppe::find($event->affected_range_id);
                if ($lvgruppe) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'lvgruppen/lvgruppen/details/' . $lvgruppe->getId(), true);
                    $templ = str_replace('%lvgruppe(%affected)', '<a href="' . $url . '">' . htmlReady($lvgruppe->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_lvgruppe_modulteil':
                $lvgruppe = Lvgruppe::find($event->affected_range_id);
                if ($lvgruppe) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'lvgruppen/lvgruppen/details/' . $lvgruppe->getId(), true);
                    $templ = str_replace('%lv(%affected)', '<a href="' . $url . '">' . htmlReady($lvgruppe->getDisplayName()) . '</a>', $templ);
                }
                $co_modulteil = Modulteil::find($event->coaffected_range_id);
                if ($co_modulteil) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/modulteil_lvg/' . $co_modulteil->getId(), true);
                    $templ = str_replace('%modulteil(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_modulteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modul':
            case 'mvv_modul_user':
            case 'mvv_modul_inst':
                $modul = Modul::find($event->affected_range_id);
                if ($modul) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/details_modul/' . $modul->getId(), true);
                    $templ = str_replace('%modul(%affected)', '<a href="' . $url . '">' . htmlReady($modul->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modulteil':
                $modulteil = Modulteil::find($event->affected_range_id);
                if ($modulteil) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/modulteil_lvg/' . $modulteil->getId(), true);
                    $templ = str_replace('%modulteil(%affected)', '<a href="' . $url . '">' . htmlReady($modulteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modulteil_deskriptor':
                $modulteil_desk = ModulteilDeskriptor::find($event->affected_range_id);
                if ($modulteil_desk) {
                    $modteil = Modulteil::find($modulteil_desk->modulteil_id);
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/modulteil_lvg/' . $modteil->getId(), true);
                    $templ = str_replace('%modulteildesk(%affected)', 'in Modulteil <a href="' . $url . '">' . htmlReady($modteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modulteil_language':
                $modulteil = Modulteil::find($event->affected_range_id);
                if ($modulteil) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/modulteil_lvg/' . $modulteil->getId(), true);
                    $templ = str_replace('%modulteil(%affected)', '<a href="' . $url . '">' . htmlReady($modulteil->getDisplayName()) . '</a>', $templ);
                }
                $co_mtlanguage = ModulteilLanguage::find(array(
                    $event->affected_range_id,
                    $event->coaffected_range_id
                ));
                if ($co_mtlanguage) {
                    $templ = str_replace('%language(%coaffected)', htmlReady($co_mtlanguage->getDisplayName()), $templ);
                }
                break;

            case 'mvv_modulteil_stgteilabschnitt':
                $modulteil = Modulteil::find($event->affected_range_id);
                if ($modulteil) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/modulteil_lvg/' . $modulteil->getId(), true);
                    $templ = str_replace('%modulteil(%affected)', '<a href="' . $url . '">' . htmlReady($modulteil->getDisplayName()) . '</a>', $templ);
                }
                $co_stgteilabs = StgteilAbschnitt::find($event->coaffected_range_id);
                if ($co_stgteilabs) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/versionen/details_abschnitt/' . $co_stgteilabs->getId(), true);
                    $templ = str_replace('%stgteilabs(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_stgteilabs->getDisplayName()) . '</a>', $templ);
                    $templ = str_replace('%fachsem', $event->dbg_info, $templ);
                }
                break;

            case 'mvv_modul_deskriptor':
                $modul_desk = ModulDeskriptor::find($event->affected_range_id);
                if ($modul_desk) {
                    $mod = Modul::find($modul_desk->modul_id);
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/details_modul/' . $mod->getId(), true);
                    $templ = str_replace('%moduldesk(%affected)', 'in Modul <a href="' . $url . '">' . htmlReady($mod->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modul_language':
                $modul = Modul::find($event->affected_range_id);
                if ($modul) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/details_modul/' . $modul->getId(), true);
                    $templ = str_replace('%modul(%affected)', '<a href="' . $url . '">' . htmlReady($modul->getDisplayName()) . '</a>', $templ);
                }
                $co_mlanguage = ModulLanguage::find(array(
                    $event->affected_range_id,
                    $event->coaffected_range_id
                ));
                if ($co_mlanguage) {
                    $templ = str_replace('%language(%coaffected)', htmlReady($co_mlanguage->getDisplayName()), $templ);
                }
                break;

            case 'mvv_stgteil':
            case 'mvv_fachberater':
                $stgteil = StudiengangTeil::find($event->affected_range_id);
                if ($stgteil) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/studiengangteile/details_versionen/' . $stgteil->getId(), true);
                    $templ = str_replace('%stgteil(%affected)', '<a href="' . $url . '">' . htmlReady($stgteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stgteilabschnitt':
                $stgteilabs = StgteilAbschnitt::find($event->affected_range_id);
                if ($stgteilabs) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/versionen/details_abschnitt/' . $stgteilabs->getId(), true);
                    $templ = str_replace('%stgteilabs(%affected)', '<a href="' . $url . '">' . htmlReady($stgteilabs->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stgteilabschnitt_modul':
                $stgteilabs = StgteilAbschnitt::find($event->affected_range_id);
                if ($stgteilabs) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/versionen/details_abschnitt/' . $stgteilabs->getId(), true);
                    $templ = str_replace('%stgteilabs(%affected)', '<a href="' . $url . '">' . htmlReady($stgteilabs->getDisplayName()) . '</a>', $templ);
                }
                $co_modul = Modul::find($event->coaffected_range_id);
                if ($co_modul) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'module/module/details_modul/' . $co_modul->getId(), true);
                    $templ = str_replace('%modul(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_modul->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stgteilversion':
                $version = StgteilVersion::find($event->affected_range_id);
                if ($version) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/versionen/abschnitte/' . $version->getId(), true);
                    $templ = str_replace('%version(%affected)', '<a href="' . $url . '">' . htmlReady($version->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stgteil_bez':
                $stgteilbez = StgteilBezeichnung::find($event->affected_range_id);
                if ($stgteilbez) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/stgteilbezeichnungen/details/' . $stgteilbez->getId(), true);
                    $templ = str_replace('%stgteilbez(%affected)', '<a href="' . $url . '">' . htmlReady($stgteilbez->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stg_stgteil':
                $stg = Studiengang::find($event->affected_range_id);
                if ($stg) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/studiengaenge/details_studiengang/' . $stg->getId(), true);
                    $templ = str_replace('%stg(%affected)', '<a href="' . $url . '">' . htmlReady($stg->getDisplayName()) . '</a>', $templ);
                }
                $co_stgteil = StudiengangTeil::find($event->coaffected_range_id);
                if ($co_stgteil) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/studiengangteile/details_versionen/' . $co_stgteil->getId(), true);
                    $templ = str_replace('%stgteil(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_stgteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_studiengang':
                $stg = Studiengang::find($event->affected_range_id);
                if ($stg) {
                    $url = PluginEngine::getURL($mvv_plugin, array(), 'studiengaenge/studiengaenge/details_studiengang/' . $stg->getId(), true);
                    $templ = str_replace('%stg(%affected)', '<a href="' . $url . '">' . htmlReady($stg->getDisplayName()) . '</a>', $templ);
                }
                break;

            default:
                break;
        }

        // specials
        // Benutzergruppen im Modul z.B. Modulverantwortlicher
        $templ = str_replace('%gruppe', $event->dbg_info, $templ);
        // Objekt konnte nicht eingesetzt werden da es vermutlich nicht mehr existiert, beim delete landet der alte Bezeichner im debug
        $templ = str_replace('%dokument(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%modul(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%modulteil(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%modulteildesk(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%moduldesk(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%stg(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteil(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteilabs(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%version(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteilbez(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%lvgruppe(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%fach(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%abschluss(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%abskategorie(%affected)', $event->dbg_info, $templ);

        $templ = str_replace('%abskategorie(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%modulteil(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%modul(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%language(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteilabs(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteil(%coaffected)', $event->dbg_info, $templ);

        return $templ;
    }


    /**
     * This method searches the log-entries for log-actions of the mvv classes
     *
     * @param  string  searchterm
     * @param  string  LogAction name
     *
     * @return array   found LogEvents
     */
    public static function logSearch($needle, $action_name = null)
    {
        $result = array();
        $sql_needle = DBManager::get()->quote($needle);

        $modul_actions = array(
            'MVV_MODUL_NEW',
            'MVV_MODUL_UPDATE',
            'MVV_MODUL_DEL',
            'MVV_MODUL_DESK_NEW',
            'MVV_MODUL_DESK_UPDATE',
            'MVV_MODUL_DESK_DEL',
            'MVV_MODULINST_NEW',
            'MVV_MODULINST_DEL',
            'MVV_MODULINST_UPDATE',
            'MVV_MODUL_USER_NEW',
            'MVV_MODUL_USER_DEL',
            'MVV_MODUL_USER_UPDATE',
            'MVV_MODUL_LANG_NEW',
            'MVV_MODUL_LANG_DEL',
            'MVV_MODUL_LANG_UPDATE',
            'MVV_MODULTEIL_STGTEILABS_NEW',
            'MVV_MODULTEIL_STGTEILABS_DEL',
            'MVV_MODULTEIL_STGTEILABS_UPDATE',
            'MVV_STGTEILABS_MODUL_NEW',
            'MVV_STGTEILABS_MODUL_DEL',
            'MVV_STGTEILABS_MODUL_UPDATE'
        );

        if (in_array($action_name, $modul_actions)) {
            $module = Modul::findBySQL("code LIKE CONCAT('%', " . $sql_needle . ", '%') OR modul_id = " . $sql_needle);
            $deskriptoren = ModulDeskriptor::findBySql("bezeichnung LIKE CONCAT('%', " . $sql_needle . ", '%') OR deskriptor_id = " . $sql_needle);
            foreach ($module as $modul) {
                $result[] = array(
                    $modul->getId(),
                    $modul->getDisplayName(true)
                );
            }
            foreach ($deskriptoren as $desk) {
                $modul = Modul::find($desk->modul_id);
                $result[] = array(
                    $modul->getId(),
                    $modul->getDisplayName(true)
                );
            }
        }

        $modulteile_actions = array(
            'MVV_MODULTEIL_NEW',
            'MVV_MODULTEIL_UPDATE',
            'MVV_MODULTEIL_DEL',
            'MVV_MODULTEIL_DESK_NEW',
            'MVV_MODULTEIL_DESK_UPDATE',
            'MVV_MODULTEIL_DESK_DEL',
            'MVV_MODULTEIL_LANG_NEW',
            'MVV_MODULTEIL_LANG_DEL',
            'MVV_MODULTEIL_LANG_UPDATE',
            'MVV_LVMODULTEIL_NEW',
            'MVV_LVMODULTEIL_DEL',
            'MVV_LVMODULTEIL_UPDATE'
        );

        if (in_array($action_name, $modulteile_actions)) {
            $deskriptoren = ModulDeskriptor::findBySql("bezeichnung LIKE CONCAT('%', " . $sql_needle . ", '%') OR deskriptor_id = " . $sql_needle);
            foreach ($deskriptoren as $desk) {
                $modulteil = Modulteil::find($desk->modulteil_id);
                $result[] = array(
                    $modulteil->getId(),
                    $modulteil->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_STUDIENGANG_NEW',
            'MVV_STUDIENGANG_UPDATE',
            'MVV_STUDIENGANG_DEL',
            'MVV_STG_STGTEIL_NEW',
            'MVV_STG_STGTEIL_DEL',
            'MVV_STG_STGTEIL_UPDATE'
        ))) {
            $stg = Studiengang::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR studiengang_id = " . $sql_needle);
            foreach ($stg as $studiengang) {
                $result[] = array(
                    $studiengang->getId(),
                    $studiengang->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_STGTEIL_NEW',
            'MVV_STGTEIL_UPDATE',
            'MVV_STGTEIL_DEL',
            'MVV_FACHBERATER_NEW',
            'MVV_FACHBERATER_UPDATE',
            'MVV_FACHBERATER_DEL'
        ))) {
            $stgteile = StudiengangTeil::findBySQL("zusatz LIKE CONCAT('%', " . $sql_needle . ", '%') OR zusatz_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR stgteil_id = " . $sql_needle);
            foreach ($stgteile as $stgteil) {
                $result[] = array(
                    $stgteil->getId(),
                    $stgteil->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_STGTEILVERSION_NEW',
            'MVV_STGTEILVERSION_UPDATE',
            'MVV_STGTEILVERSION_DEL'
        ))) {
            $versionen = StgteilVersion::findBySQL("code LIKE CONCAT('%', " . $sql_needle . ", '%') OR version_id = " . $sql_needle . " OR stgteil_id = " . $sql_needle);
            foreach ($versionen as $version) {
                $result[] = array(
                    $version->getId(),
                    $version->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_STGTEILBEZ_NEW',
            'MVV_STGTEILBEZ_UPDATE',
            'MVV_STGTEILBEZ_DEL'
        ))) {
            $stgbez = StgteilBezeichnung::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR stgteil_bez_id = " . $sql_needle);
            foreach ($stgbez as $bez) {
                $result[] = array(
                    $bez->getId(),
                    $bez->getDisplayName()
                );
            }
        }

        $stgteil_actions = array(
            'MVV_STGTEILABS_NEW',
            'MVV_STGTEILABS_UPDATE',
            'MVV_STGTEILABS_DEL',
            'MVV_MODULTEIL_STGTEILABS_NEW',
            'MVV_MODULTEIL_STGTEILABS_DEL',
            'MVV_MODULTEIL_STGTEILABS_UPDATE',
            'MVV_STG_STGTEIL_NEW',
            'MVV_STG_STGTEIL_DEL',
            'MVV_STG_STGTEIL_UPDATE',
            'MVV_STGTEILABS_MODUL_NEW',
            'MVV_STGTEILABS_MODUL_DEL',
            'MVV_STGTEILABS_MODUL_UPDATE'
        );

        if (in_array($action_name, $stgteil_actions)) {
            $stgteilabs = Lvgruppe::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR abschnitt_id = " . $sql_needle);
            foreach ($stgteilabs as $abschnitt) {
                $result[] = array(
                    $abschnitt->getId(),
                    $abschnitt->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_LVGRUPPE_NEW',
            'MVV_LVGRUPPE_DEL',
            'MVV_LVGRUPPE_UPDATE',
            'MVV_LVMODULTEIL_NEW',
            'MVV_LVMODULTEIL_DEL',
            'MVV_LVMODULTEIL_UPDATE',
            'MVV_LVSEMINAR_NEW',
            'MVV_LVSEMINAR_DEL',
            'MVV_LVSEMINAR_UPDATE'
        ))) {
            $lvgruppen = Lvgruppe::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR lvgruppe_id = " . $sql_needle);
            foreach ($lvgruppen as $lvgruppe) {
                $result[] = array(
                    $lvgruppe->getId(),
                    $lvgruppe->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_FACH_NEW',
            'MVV_FACH_UPDATE',
            'MVV_FACH_DEL',
            'MVV_FACHINST_NEW',
            'MVV_FACHINST_DEL',
            'MVV_FACHINST_UPDATE'
        ))) {
            $faecher = Fach::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR fach_id = " . $sql_needle);
            foreach ($faecher as $fach) {
                $result[] = array(
                    $fach->getId(),
                    $fach->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_ABSCHLUSS_NEW',
            'MVV_ABSCHLUSS_UPDATE',
            'MVV_ABSCHLUSS_DEL',
            'MVV_ABS_ZUORD_NEW',
            'MVV_ABS_ZUORD_DEL',
            'MVV_ABS_ZUORD_UPDATE'
        ))) {
            $abschluesse = Abschluss::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR abschluss_id = " . $sql_needle);
            foreach ($abschluesse as $abschluss) {
                $result[] = array(
                    $abschluss->getId(),
                    $abschluss->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_KATEGORIE_NEW',
            'MVV_KATEGORIE_UPDATE',
            'MVV_KATEGORIE_DEL',
            'MVV_ABS_ZUORD_NEW',
            'MVV_ABS_ZUORD_DEL',
            'MVV_ABS_ZUORD_UPDATE'
        ))) {
            $abskategorien = AbschlussKategorie::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR kategorie_id = " . $sql_needle);
            foreach ($abskategorien as $abskategorie) {
                $result[] = array(
                    $abskategorie->getId(),
                    $abskategorie->getDisplayName()
                );
            }
        }

        if (in_array($action_name, array(
            'MVV_DOKUMENT_NEW',
            'MVV_DOKUMENT_UPDATE',
            'MVV_DOKUMENT_DEL',
            'MVV_DOK_ZUORD_NEW',
            'MVV_DOK_ZUORD_DEL',
            'MVV_DOK_ZUORD_UPDATE'
        ))) {
            $dokumente = Dokument::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR dokument_id = " . $sql_needle);
            foreach ($dokumente as $dokument) {
                $result[] = array(
                    $dokument->getId(),
                    $dokument->getDisplayName()
                );
            }
        }

        return $result;
    }

}
