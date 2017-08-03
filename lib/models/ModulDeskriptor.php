<?php
/**
 * ModulDeskriptor.php
 * Model class for Moduldeskriptoren (table mvv_modul_deskriptor)
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

class ModulDeskriptor extends ModuleManagementModel
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_modul_deskriptor';
    
        $config['belongs_to']['modul'] = array(
            'class_name' => 'Modul',
            'foreign_key' => 'modul_id'
        );
        
        $config['has_many']['datafields'] = array(
            'class_name' => 'DatafieldEntryModel',
            'assoc_foreign_key' =>
                function($model, $params) {
                    $model->setValue('range_id', $params[0]->id);
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
        $this->object_real_name = _('Modul-Deskriptor');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Modul-Deskriptor');
    }
    
    /**
     * Retrieves all descriptors for the given module.
     * 
     * @param string $modul_id The id of a module.
     * @return object A SimpleORMapcollection of module descriptors.
     */
    public static function findByModul($modul_id)
    {
        return parent::getEnrichedByQuery('SELECT mmd.* '
                . 'FROM mvv_modul_deskriptor mmd '
                . 'WHERE mmd.modul_id = ? '
                , array($modul_id));
    }
    
    /**
     * Inherits the status of the parent module.
     * 
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        
        $modul = Modul::find($this->modul_id);
        if ($modul) {
            return $modul->getStatus();
        } elseif ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }
    
    public function getResponsibleInstitutes()
    {
        $institutes = array();
        $modul_insts = ModulInst::findByModul($this->modul_id, 'hauptverantwortlich');
        foreach ($modul_insts as $modul_inst) {
            $institute = Institute::find($modul_inst->institut_id);
            if ($institute) {
                $institutes[] = $institute;
            }
        }
        return $institutes;
    }

    /**
     * Returns the language identifier as the variant of the descriptor object.
     * 
     * @see ModuleManagementModel::getVariant()
     * @return string The language identifier.
     */
    public function getVariant()
    {
        if ($this->sprache == $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['default']) {
            return '';
        }
        return $this->sprache;
    }

}