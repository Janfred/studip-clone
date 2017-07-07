<?php
/**
 * abschluesse.php - Studiengaenge_AbschluesseController
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
require_once dirname(__FILE__) . '/studiengaenge.php';

class Studiengaenge_AbschluesseController
        extends Studiengaenge_StudiengaengeController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        URLHelper::bindLinkParam('parent_id', $this->parent_id);
    }
    
    /**
     * Liste der Studiengänge gruppiert nach Fachbereiche (Fachbereich ist
     * die  verantwortliche Einrichtung des zugeordneten Faches, nicht die des
     * Studiengangs!)
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Studiengänge gruppiert nach Abschlüssen'));
        
        // Nur Abschlüsse von Fachbereichen an denen der User eine Rolle hat
        $perm_institutes = MvvPerm::getOwnInstitutes();
        
        $this->initPageParams('abschluesse');
        
        $this->abschluesse = Abschluss::getAllEnriched(
                $this->sortby, $this->order, null, null,
                ['mvv_fach_inst.institut_id' => $perm_institutes]);

        $this->setSidebar();
    }
    
    /**
     * shows the studiengaenge of a fachbereich
     * 
     * @param string $fachbereich_id the id of the fachbereich
     */
    public function details_action($studiengang_id = null, $stgteil_bez_id = null)
    {
        $perm_institutes = MvvPerm::getOwnInstitutes();
        $this->abschluss_id = Request::option('parent_id');
        if (count($perm_institutes)) {
            if (!in_array($this->abschluss_id, $perm_institutes)) {
                throw new Trails_Exception(403);
            }
        }
        
        $this->parent_id = $this->abschluss_id;
        $this->studiengaenge = Studiengang::findByAbschluss($this->abschluss_id);
        if ($studiengang_id) {
            $this->studiengang_id = $studiengang_id;
            $this->set_studiengangteile($studiengang_id, $stgteil_bez_id);
        }
        
        if (Request::isXhr()) {
            if ($this->studiengang) {
                if ($this->studiengang->typ == 'einfach') {
                    $this->render_template('studiengaenge/studiengaenge/studiengangteile');
                } else {
                    if ($stgteil_bez_id) {
                        $this->render_template('studiengaenge/studiengaenge/studiengangteile');
                    } else {
                        $this->render_template('studiengaenge/studiengaenge/stgteil_bezeichnungen');
                    }
                }
            } else {
                $this->render_template('studiengaenge/studiengaenge/details');
            }
        } else {
            $this->perform_relayed('index');
        }
    }
    
}