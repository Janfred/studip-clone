<?php
/**
 * angebot.php - Search_AngebotController
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

class Search_AngebotController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        ModuleManagementModel::setLanguage($_SESSION['_language']);
        
        // set navigation
        Navigation::activateItem('/search/module/angebot');
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
        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }
        $filter = ['mvv_studiengang.stat' => $status_filter];
        $faecher = Fach::getAllEnriched('name', 'ASC', null, null, $filter);
        
        // sort by display name
        $faecher_sort = [];
        foreach ($faecher as $key => $fach) {
            $faecher_sort[$fach->getDisplayName() . $key] = $fach;
        }
        ksort($faecher_sort, SORT_LOCALE_STRING);
        
        $result = array();
        $chars = array();
        foreach ($faecher_sort as $fach) {
            $char = $fach->name[0];

            foreach ($fach->abschluesse as $abschluss) {
                $result [$char][] = array(
                    'abschluss_id' => $abschluss->getId(),
                    'fach_id' => $fach->getId(),
                    'name' => sprintf('%s - %s', $fach->name,
                            $abschluss->getDisplayName())
                );
            }

            $chars[$char] = $char;
        }

        function link_chars(&$char,
                $key,
                $pattern) {
            $char = sprintf($pattern, $key, ucfirst($key));
        }

        array_walk($chars, 'link_chars', '<a href="#%s">%s</a>');

        $this->breadCrumb->init();
        $this->breadCrumb->append(_('Studienangebot von A bis Z'), 'A bis Z');
        $this->faecher = $result;
        $this->chars = $chars;
        $this->name = _('Studienangebot von A bis Z');
        $this->url = 'search/angebot/detail';
    }

    public function matrix_action($kategorie_id)
    {
        $abschluss_kategorie = AbschlussKategorie::find($kategorie_id);
        if (!$abschluss_kategorie) {
            PageLayout::postError(_('Unbekannte Abschluss-Kategorie'));
            $this->redirect('search/angebot/index');
            return null;
        }
        $name = $abschluss_kategorie->name;
        $faecher = array();

        $result = array();
        foreach ($faecher as $fach) {
            foreach ($fach->abschluesse as $abschluss) {
                $kategorie = AbschlussKategorie::findByAbschluss($abschluss->getId());
                $result [$fach->name][$kategorie->getId()] = array(
                    'abschluss_id' => $abschluss->getId(),
                    'fach_id' => $fach->getId(),
                    'name' => sprintf('%s - %s (%s)', $fach->name, $abschluss->name, $abschluss->name_kurz)
                );
            }
        }

        $this->abschluesse = $abschluss_kategorie;
        $this->faecher = $result;
        $this->name = $name;
        $this->url = $this->url_for('fach/detail/');
    }

    public function detail_action($fach_id, $abschluss_id, $studiengang_id = null)
    {
        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }
        $studiengaenge = Studiengang::findByFachAbschluss($fach_id, $abschluss_id);
        $studiengaenge = $studiengaenge->filter(function ($stg) use ($status_filter) {
            return in_array($stg->stat, $status_filter);
        });
        $this->fach = Fach::find($fach_id);
        $this->abschluss = Abschluss::find($abschluss_id);
        $this->studiengaenge = $studiengaenge;
        $this->breadCrumb->append([$this->fach, $this->abschluss]);
        $this->url = 'search/angebot/studiengang';

        if ($this->abschluss && $studiengang_id) {
            $studiengang = Studiengang::get($studiengang_id);
            $this->studiengangName = $studiengang->getDisplayName();
            $this->studiengang_id = $studiengang->getId();
            $this->info = $studiengang->beschreibung;
        }
        if(count($studiengaenge) == 1) {
            foreach($studiengaenge as $studiengang) {
                $this->redirect($this->url . '/' . $studiengang->id);
                return;
            }
        }
    }

    public function studiengang_action($studiengang_id)
    {
        $this->verlauf_url = 'search/angebot/verlauf';
        $response = $this->relay('search/studiengaenge/studiengang', $studiengang_id);
        $this->content = $response->body;
        $this->render_template('shared/content', $this->layout);
    }

    public function info_action($studiengang_id)
    {
        $this->studiengang = Studiengang::find($studiengang_id);
        if (!$this->studiengang || !$this->studiengang->getPublicStatus()) {
            throw new Exception('Unbekannter Studiengang!');
        }
    }

    public function verlauf_action($stgteil_id, $stgteil_bez_id = null,
            $studiengang_id = null)
    {
        $response = $this->relay('search/studiengaenge/verlauf', $stgteil_id,
                $stgteil_bez_id, $studiengang_id);
        $this->content = $response->body;
        $this->render_template('shared/content', $this->layout);
    }
}
