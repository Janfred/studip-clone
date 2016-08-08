<?php
/**
 * ModulteilDeskriptor.php
 * Model class for Modulteil-Deskriptoren (table mvv_modulteil_deskriptor)
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

class ModulteilDeskriptor extends ModuleManagementModel
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_modulteil_deskriptor';
        
        $config['belongs_to']['modulteil'] = array(
            'class_name' => 'Modulteil',
            'foreign_key' => 'modulteil_id'
        );
        
        $config['has_many']['datafields'] = array(
            'class_name' => 'DatafieldEntryModel',
            'assoc_foreign_key' =>
                function($model, $params) {
                    $model->setValue('range_id', $params[0]->deskriptor_id);
                },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'foreign_key' =>
                function($m) {
                    return array($m);
                }
        );
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Modulteil-Deskriptor');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Modulteil-Deskriptor');
    }
    
    /**
     * Retrieves all descriptors of the given Modulteil. Optional restricted to
     * a language.
     * 
     * @see mvv_config.php for defined languages.
     * @param type $modulteil_id The id of a Modulteil.
     * @param type $language The key of a language.
     * @return SimpleORMapCollection A collection of descriptors.
     */
    public static function findByModulteil($modulteil_id, $language = null)
    {
        if ($language) {
            $params = array($modulteil_id, $language);
        } else {
            $params = array($modulteil_id);
        }
        return parent::getEnrichedByQuery('SELECT mtd.* '
                . 'FROM mvv_modulteil_deskriptor mtd '
                . 'WHERE mtd.modulteil_id = ? '
                . ($language ? 'AND sprache = ? ' : '')
                . 'ORDER BY sprache', $params);
    }
    
    /**
     * Inherits the status of the parent modulteil.
     * 
     * @see ModuleManagementModel::getStatus()
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        $modulteil = Modulteil::find($this->modulteil_id);
        if ($modulteil) {
            return $modulteil->getStatus();
        } elseif ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }
}