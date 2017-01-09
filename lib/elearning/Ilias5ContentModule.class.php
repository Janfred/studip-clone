<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * class to handle ILIAS 5.2 learning modules and tests
 *
 * This class contains methods to handle ILIAS 5 learning modules and tests.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias5ContentModule
 * @package    ELearning-Interface
 */
class Ilias5ContentModule extends Ilias4ContentModule
{
    var $object_id;

    /**
     * constructor
     *
     * init class.
     * @access public
     * @param string $module_id module-id
     * @param string $module_type module-type
     * @param string $cms_type system-type
     */
    function __construct($module_id = "", $module_type, $cms_type)
    {
        parent::__construct($module_id, $module_type, $cms_type);
    }
}