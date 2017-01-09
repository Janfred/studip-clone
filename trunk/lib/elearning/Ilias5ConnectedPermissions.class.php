<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

DEFINE (OPERATION_COPY, "copy");

/**
 * class to handle ILIAS 5.2 access controls
 *
 * This class contains methods to handle permissions on connected objects.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias4ConnectedPermission
 * @package    ELearning-Interface
 */
class Ilias5ConnectedPermissions extends Ilias4ConnectedPermissions
{
    var $operations;
    var $allowed_operations;
    var $tree_allowed_operations;

    var $USER_OPERATIONS;
    var $AUTHOR_OPERATIONS;
    /**
     * constructor
     *
     * init class.
     * @access
     * @param string $cms system-type
     */
    function __construct($cms)
    {
        parent::__construct($cms);
    }
}
?>